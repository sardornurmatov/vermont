// DASTA — do'kon sahifasi mantiqi

const state = {
  categories: new Set(),   // tanlangan kategoriyalar
  priceRange: null,        // "min-max" satr yoki null
  minRating: 0,
  inStockOnly: false,
  query: "",
  sort: "rating",
  cart: Store.getCart(),
};

// Mahsulotlar backend'dan bir marta olinadi va shu yerda keshlanadi —
// filtr/qidiruv/saralash kabi tez-tez chaqiriladigan funksiyalar har safar
// tarmoqqa so'rov yubormasdan, shu massiv ustida ishlaydi.
let PRODUCTS_CACHE = [];
let currentPaymentInfo = null;

const fmt = (num) => new Intl.NumberFormat("uz-UZ").format(Math.round(num)) + " so'm";

function starString(rating) {
  const full = Math.round(rating);
  return "★★★★★".slice(0, full) + "☆☆☆☆☆".slice(0, 5 - full);
}

// ---------- render: category checkboxes ----------
function renderCategoryFilters() {
  const el = document.getElementById("catFilters");
  el.innerHTML = CATEGORIES.map(c => `
    <label class="checkbox-row">
      <input type="checkbox" data-cat="${c.id}" ${state.categories.has(c.id) ? "checked" : ""}>
      <span>${c.label}</span>
    </label>
  `).join("");

  el.querySelectorAll("input").forEach(input => {
    input.addEventListener("change", () => {
      if (input.checked) state.categories.add(input.dataset.cat);
      else state.categories.delete(input.dataset.cat);
      renderActiveChips();
      renderProducts();
    });
  });
}

// ---------- active filter chips ----------
function renderActiveChips() {
  const el = document.getElementById("activeChips");
  const chips = [];

  state.categories.forEach(catId => {
    const label = CATEGORIES.find(c => c.id === catId)?.label;
    chips.push({ key: `cat:${catId}`, label });
  });

  if (state.priceRange) {
    chips.push({ key: "price", label: "Narx oralig'i" });
  }

  if (state.minRating > 0) {
    chips.push({ key: "rating", label: `${state.minRating}★+` });
  }

  if (state.inStockOnly) {
    chips.push({ key: "stock", label: "Omborda bor" });
  }

  el.innerHTML = chips.map(c => `
    <span class="chip" data-key="${c.key}">${c.label} <button>&times;</button></span>
  `).join("");

  el.querySelectorAll(".chip button").forEach(btn => {
    btn.addEventListener("click", () => {
      const key = btn.parentElement.dataset.key;
      if (key.startsWith("cat:")) state.categories.delete(key.slice(4));
      if (key === "price") { state.priceRange = null; document.querySelectorAll('input[name="price"]').forEach(r => r.checked = false); }
      if (key === "rating") setRating(0);
      if (key === "stock") { state.inStockOnly = false; document.getElementById("inStockOnly").checked = false; }
      renderCategoryFilters();
      renderActiveChips();
      renderProducts();
    });
  });
}

function setRating(value) {
  state.minRating = value;
  document.querySelectorAll(".rating-pills .pill").forEach(p => {
    p.classList.toggle("active", parseFloat(p.dataset.rating) === value);
  });
}

// ---------- filter + sort pipeline ----------
function getVisibleProducts() {
  let list = PRODUCTS_CACHE.filter(p => {
    if (state.categories.size && !state.categories.has(p.cat)) return false;
    if (state.priceRange) {
      const [min, max] = state.priceRange.split("-").map(Number);
      if (p.price < min || p.price > max) return false;
    }
    if (p.rating < state.minRating) return false;
    if (state.inStockOnly && (p.stock ?? 0) <= 0) return false;
    if (state.query) {
      const hay = (p.name + " " + p.desc).toLowerCase();
      if (!hay.includes(state.query)) return false;
    }
    return true;
  });

  switch (state.sort) {
    case "price-asc": list.sort((a, b) => a.price - b.price); break;
    case "price-desc": list.sort((a, b) => b.price - a.price); break;
    case "sold": list.sort((a, b) => (b.sold || 0) - (a.sold || 0)); break;
    default: list.sort((a, b) => b.rating - a.rating);
  }
  return list;
}

// ---------- render product grid ----------
function renderProducts() {
  const grid = document.getElementById("productGrid");
  const empty = document.getElementById("emptyState");
  const list = getVisibleProducts();

  document.getElementById("resultCount").textContent =
    `${PRODUCTS_CACHE.length} tadan ${list.length} ta mahsulot ko'rsatilmoqda`;

  if (list.length === 0) {
    grid.innerHTML = "";
    empty.hidden = false;
    return;
  }
  empty.hidden = true;

  grid.innerHTML = list.map(p => {
    const outOfStock = (p.stock ?? 0) <= 0;
    const lowStock = !outOfStock && p.stock <= 5;
    return `
    <article class="product-card">
      <div class="product-thumb">
        ${outOfStock ? '<span class="stock-badge">Tugagan</span>' : lowStock ? `<span class="stock-badge" style="background:var(--danger-light); color:var(--ink)">Kam qoldi</span>` : ""}
        <button class="fav-btn" data-fav="${p.id}" aria-label="Sevimlilarga qo'shish">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 20s-7-4.35-9.5-8.5C.9 8.1 2.5 5 5.8 5c1.9 0 3.3 1 4.2 2.4C11 6 12.3 5 14.2 5c3.3 0 4.9 3.1 3.3 6.5C19 15.65 12 20 12 20Z"/></svg>
        </button>
        ${p.image ? `<img src="${p.image}" alt="${p.name}">` : ICONS[p.cat]}
      </div>
      <div class="product-title-row">
        <h3 class="product-name">${p.name}</h3>
      </div>
      <div class="product-rating">
        <span class="stars">${starString(p.rating)}</span>
        <span>${p.reviews} sharh</span>
      </div>
      <div class="product-sub">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
        ${p.location}
      </div>
      <div class="product-footer">
        <span class="product-price">${fmt(p.price)}</span>
        <span class="product-sold">${p.sold} sotilgan</span>
      </div>
      <button class="add-btn" data-id="${p.id}" ${outOfStock ? "disabled" : ""}>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6"/></svg>
        ${outOfStock ? "Tugagan" : "Savatga qo'shish"}
      </button>
    </article>
  `;
  }).join("");

  grid.querySelectorAll(".add-btn:not(:disabled)").forEach(btn => {
    btn.addEventListener("click", () => {
      addToCart(btn.dataset.id);
      const original = btn.innerHTML;
      btn.classList.add("added");
      btn.innerHTML = "Qo'shildi ✓";
      setTimeout(() => { btn.classList.remove("added"); btn.innerHTML = original; }, 1100);
    });
  });

  grid.querySelectorAll(".fav-btn").forEach(btn => {
    btn.addEventListener("click", () => btn.classList.toggle("active"));
  });
}

// ---------- cart ----------
function addToCart(id) {
  state.cart[id] = (state.cart[id] || 0) + 1;
  Store.saveCart(state.cart);
  renderCartCount();
  renderDrawer();
  showToast("Savatga qo'shildi");
}

function changeQty(id, delta) {
  if (!state.cart[id]) return;
  state.cart[id] += delta;
  if (state.cart[id] <= 0) delete state.cart[id];
  Store.saveCart(state.cart);
  renderCartCount();
  renderDrawer();
}

function removeFromCart(id) {
  delete state.cart[id];
  Store.saveCart(state.cart);
  renderCartCount();
  renderDrawer();
}

function cartEntries() {
  return Object.entries(state.cart)
    .map(([id, qty]) => ({ product: PRODUCTS_CACHE.find(p => p.id === id), qty }))
    .filter(e => e.product);
}

function cartTotal() {
  return cartEntries().reduce((sum, e) => sum + e.product.price * e.qty, 0);
}

function renderCartCount() {
  document.getElementById("cartCount").textContent =
    Object.values(state.cart).reduce((a, b) => a + b, 0);
}

function renderDrawer() {
  const container = document.getElementById("drawerItems");
  const entries = cartEntries();

  if (entries.length === 0) {
    container.innerHTML = `<p class="drawer-empty">Savat bo'sh. Katalogdan mahsulot qo'shing.</p>`;
    document.getElementById("drawerTotal").textContent = fmt(0);
    return;
  }

  container.innerHTML = entries.map(({ product, qty }) => `
    <div class="drawer-item">
      <div class="product-thumb">${product.image ? `<img src="${product.image}" alt="${product.name}">` : ICONS[product.cat]}</div>
      <div class="drawer-item-info">
        <p class="drawer-item-name">${product.name}</p>
        <p class="drawer-item-price">${fmt(product.price)}</p>
        <div class="qty-controls">
          <button data-action="dec" data-id="${product.id}">−</button>
          <span>${qty}</span>
          <button data-action="inc" data-id="${product.id}">+</button>
          <button class="remove-item" data-action="remove" data-id="${product.id}">o'chirish</button>
        </div>
      </div>
    </div>
  `).join("");

  document.getElementById("drawerTotal").textContent = fmt(cartTotal());

  container.querySelectorAll("[data-action]").forEach(btn => {
    const id = btn.dataset.id, action = btn.dataset.action;
    btn.addEventListener("click", () => {
      if (action === "inc") changeQty(id, 1);
      if (action === "dec") changeQty(id, -1);
      if (action === "remove") removeFromCart(id);
    });
  });
}

// ---------- toast ----------
let toastTimer;
function showToast(message, type = 'info') {
  let toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'toast';
    document.body.appendChild(toast);
  }

  toast.textContent = message;
  toast.className = `toast show ${type}`;
  setTimeout(() => {
    toast.className = toast.className.replace('show', '');
  }, 3000);
}

// ---------- drawer / modal open-close ----------
function openDrawer() {
  document.getElementById("cartDrawer").classList.add("open");
  document.getElementById("drawerOverlay").classList.add("open");
}
function closeDrawer() {
  document.getElementById("cartDrawer").classList.remove("open");
  document.getElementById("drawerOverlay").classList.remove("open");
}
function renderPickupLocations() {
  const locations = [...new Set(cartEntries().map(e => e.product.location).filter(Boolean))];
  const box = document.getElementById("pickupLocations");
  const pinIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>`;
  box.innerHTML = locations.length
    ? locations.map(loc => `<span class="loc-chip">${pinIcon} ${loc}</span>`).join("")
    : `<span class="loc-chip">${pinIcon} Toshkent (asosiy filial)</span>`;
  return locations;
}

function openCheckout() {
  if (cartEntries().length === 0) { showToast("Savat bo'sh"); return; }
  goToStep(1);
  document.getElementById("modalTotalStep1").textContent = fmt(cartTotal());
  renderPickupLocations();
  document.getElementById("checkoutOverlay").classList.add("open");
}
function closeCheckout() {
  document.getElementById("checkoutOverlay").classList.remove("open");
}

// ---------- checkout step wizard ----------
let receiptImage = null;

function goToStep(stepNum) {
  document.getElementById("checkoutStep1").hidden = stepNum !== 1;
  document.getElementById("checkoutStep2").hidden = stepNum !== 2;
  document.querySelectorAll(".step-dot").forEach(dot => {
    dot.classList.toggle("active", Number(dot.dataset.step) === stepNum);
  });
  document.getElementById("checkoutTitle").textContent =
    stepNum === 1 ? "Buyurtmani rasmiylashtirish" : "To'lovni amalga oshiring";
}

async function renderPaymentCard() {
  currentPaymentInfo = await Store.getPaymentInfo();
  document.getElementById("payCardNumber").textContent = currentPaymentInfo.cardNumber;
  document.getElementById("payCardHolder").textContent = currentPaymentInfo.cardHolder;
  document.getElementById("payCardBank").textContent = currentPaymentInfo.bank;
  document.getElementById("modalTotalStep2").textContent = fmt(cartTotal());
}

function setReceiptPreview(dataUrl) {
  receiptImage = dataUrl;
  const preview = document.getElementById("receiptPreview");
  const empty = document.getElementById("receiptUploadEmpty");
  const removeBtn = document.getElementById("removeReceipt");
  if (dataUrl) {
    preview.src = dataUrl;
    preview.hidden = false;
    empty.hidden = true;
    removeBtn.hidden = false;
  } else {
    preview.hidden = true;
    empty.hidden = false;
    removeBtn.hidden = true;
  }
}

function initReceiptUpload() {
  const box = document.getElementById("receiptUploadBox");
  const fileInput = document.getElementById("receiptInput");
  const removeBtn = document.getElementById("removeReceipt");

  box.addEventListener("click", () => fileInput.click());

  fileInput.addEventListener("change", async () => {
    const file = fileInput.files[0];
    if (!file) return;
    if (!file.type.startsWith("image/")) {
      showToast("Faqat rasm fayli tanlang");
      return;
    }
    try {
      const compressed = await compressImage(file, 700, 0.75);
      setReceiptPreview(compressed);
    } catch (err) {
      showToast("Rasmni yuklashda xatolik yuz berdi");
    }
  });

  removeBtn.addEventListener("click", e => {
    e.stopPropagation();
    fileInput.value = "";
    setReceiptPreview(null);
  });
}

// ---------- init ----------
async function init() {
  try {
    PRODUCTS_CACHE = await Store.getProducts();
  } catch (err) {
    showToast(err.message);
  }

  renderCategoryFilters();
  renderActiveChips();
  renderProducts();
  renderCartCount();
  renderDrawer();
  initReceiptUpload();
  initAuth();
  renderAccountUI();

  document.getElementById("searchInput").addEventListener("input", e => {
    state.query = e.target.value.trim().toLowerCase();
    renderProducts();
  });

  document.querySelectorAll('input[name="price"]').forEach(radio => {
    radio.addEventListener("change", () => {
      state.priceRange = radio.value;
      renderActiveChips();
      renderProducts();
    });
  });

  document.querySelectorAll(".rating-pills .pill").forEach(pill => {
    pill.addEventListener("click", () => {
      setRating(parseFloat(pill.dataset.rating));
      renderActiveChips();
      renderProducts();
    });
  });

  document.getElementById("inStockOnly").addEventListener("change", e => {
    state.inStockOnly = e.target.checked;
    renderActiveChips();
    renderProducts();
  });

  document.getElementById("sortSelect").addEventListener("change", e => {
    state.sort = e.target.value;
    renderProducts();
  });

  document.getElementById("clearFilters").addEventListener("click", () => {
    state.categories.clear();
    state.priceRange = null;
    state.inStockOnly = false;
    setRating(0);
    state.query = "";
    document.getElementById("searchInput").value = "";
    document.querySelectorAll('input[name="price"]').forEach(r => r.checked = false);
    document.getElementById("inStockOnly").checked = false;
    renderCategoryFilters();
    renderActiveChips();
    renderProducts();
  });

  document.getElementById("cartBtn").addEventListener("click", openDrawer);
  document.getElementById("railCart").addEventListener("click", openDrawer);
  document.getElementById("closeDrawer").addEventListener("click", closeDrawer);
  document.getElementById("drawerOverlay").addEventListener("click", closeDrawer);

  document.getElementById("checkoutBtn").addEventListener("click", openCheckout);
  document.getElementById("closeCheckout").addEventListener("click", closeCheckout);

  // STEP 1 -> STEP 2
  document.getElementById("checkoutStep1").addEventListener("submit", async e => {
    e.preventDefault();
    goToStep(2);
    await renderPaymentCard();
  });

  document.getElementById("backToStep1").addEventListener("click", () => goToStep(1));

  // STEP 2 -> yakuniy buyurtma
  document.getElementById("checkoutStep2").addEventListener("submit", async e => {
    e.preventDefault();
    if (!receiptImage) {
      showToast("Iltimos, to'lov chekini yuklang");
      return;
    }
    const submitBtn = document.getElementById("submitOrderBtn");
    submitBtn.disabled = true;
    submitBtn.textContent = "Yuborilmoqda...";

    const order = {
      id: "ORD-" + Date.now().toString().slice(-6),
      customer: {
        name: document.getElementById("custName").value.trim(),
        phone: document.getElementById("custPhone").value.trim(),
      },
      pickupLocations: renderPickupLocations(),
      items: cartEntries().map(({ product, qty }) => ({
        id: product.id, name: product.name, price: product.price, qty, location: product.location,
      })),
      total: cartTotal(),
      receipt: receiptImage,
      paidToCard: currentPaymentInfo ? currentPaymentInfo.cardNumber : null,
    };

    try {
      await Store.addOrder(order);
      state.cart = {};
      Store.saveCart(state.cart);
      renderCartCount();
      renderDrawer();
      closeCheckout();
      closeDrawer();
      document.getElementById("checkoutStep1").reset();
      document.getElementById("checkoutStep2").reset();
      setReceiptPreview(null);
      goToStep(1);
      showToast("Buyurtma qabul qilindi! To'lovingiz tekshirilmoqda.");
    } catch (err) {
      showToast(err.message);
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Buyurtmani yakunlash";
    }
  });
}

// =========================================================
// HISOB: login / ro'yxatdan o'tish / buyurtmalarim
// =========================================================

function renderAccountUI() {
  const customer = Store.getCurrentCustomer();
  const initial = customer ? customer.name.trim().charAt(0).toUpperCase() : "?";
  const label = customer ? customer.name.split(" ")[0] : "Kirish";

  document.getElementById("accountInitial").textContent = initial;
  document.getElementById("accountLabel").textContent = label;
  document.getElementById("railAccountInitial").textContent = initial;

  // agar hisobga kirilgan bo'lsa, checkout formasini avtomatik to'ldiramiz
  if (customer) {
    const nameInput = document.getElementById("custName");
    const phoneInput = document.getElementById("custPhone");
    if (nameInput && !nameInput.value) nameInput.value = customer.name;
    if (phoneInput && !phoneInput.value) phoneInput.value = customer.phone;
  }
}

function openAuthModal() {
  document.getElementById("authOverlay").classList.add("open");
}
function closeAuthModal() {
  document.getElementById("authOverlay").classList.remove("open");
}

function switchAuthTab(tab) {
  document.querySelectorAll(".auth-tab").forEach(t => t.classList.toggle("active", t.dataset.authTab === tab));
  document.getElementById("loginForm").hidden = tab !== "login";
  document.getElementById("registerForm").hidden = tab !== "register";
  document.getElementById("authTitle").textContent = tab === "login" ? "Hisobga kirish" : "Ro'yxatdan o'tish";
  document.getElementById("loginError").hidden = true;
  document.getElementById("registerError").hidden = true;
}

async function openMyOrders() {
  const customer = Store.getCurrentCustomer();
  const body = document.getElementById("myOrdersBody");
  const footer = document.getElementById("myOrdersFooter");

  if (!customer) {
    body.innerHTML = `<div class="my-orders-empty">
      <p>Buyurtmalar tarixini ko'rish uchun avval hisobga kiring.</p>
      <button class="checkout-btn" id="myOrdersLoginBtn">Hisobga kirish</button>
    </div>`;
    footer.hidden = true;
    document.getElementById("myOrdersOverlay").classList.add("open");
    document.getElementById("myOrdersLoginBtn").addEventListener("click", () => {
      closeMyOrdersModal();
      switchAuthTab("login");
      openAuthModal();
    });
    return;
  }

  body.innerHTML = `<div class="my-orders-empty"><p>Yuklanmoqda...</p></div>`;
  document.getElementById("myOrdersOverlay").classList.add("open");

  let orders;
  try {
    orders = await Store.getOrdersByCustomer();
  } catch (err) {
    body.innerHTML = `<div class="my-orders-empty"><p>${err.message}</p></div>`;
    footer.hidden = false;
    return;
  }

  if (orders.length === 0) {
    body.innerHTML = `<div class="my-orders-empty"><p>Hozircha buyurtmalaringiz yo'q.</p></div>`;
  } else {
    body.innerHTML = orders.map(o => {
      const date = new Date(o.createdAt).toLocaleDateString("uz-UZ", { day: "2-digit", month: "2-digit", year: "numeric" });
      const itemsSummary = o.items.map(i => `${i.name} ×${i.qty}`).join(", ");
      return `
        <div class="my-order-card">
          <div class="my-order-top">
            <span class="cell-strong">${o.id}</span>
            <span class="status-badge status-${o.status.replace(/\s+/g, "-")}">${o.status}</span>
          </div>
          <p class="cell-muted" style="margin:6px 0;">${itemsSummary}</p>
          <div class="my-order-bottom">
            <span class="cell-muted">${date}</span>
            <span class="cell-strong">${fmt(o.total)}</span>
          </div>
        </div>
      `;
    }).join("");
  }
  footer.hidden = false;
}
function closeMyOrdersModal() {
  document.getElementById("myOrdersOverlay").classList.remove("open");
}

function initAuth() {
  document.getElementById("accountBtn").addEventListener("click", () => {
    if (Store.getCurrentCustomer()) openMyOrders();
    else { switchAuthTab("login"); openAuthModal(); }
  });
  document.getElementById("railAccount").addEventListener("click", () => {
    if (Store.getCurrentCustomer()) openMyOrders();
    else { switchAuthTab("login"); openAuthModal(); }
  });
  document.getElementById("topOrdersBtn").addEventListener("click", openMyOrders);
  document.getElementById("railOrders").addEventListener("click", openMyOrders);

  document.getElementById("closeAuth").addEventListener("click", closeAuthModal);
  document.getElementById("authOverlay").addEventListener("click", e => {
    if (e.target.id === "authOverlay") closeAuthModal();
  });
  document.getElementById("closeMyOrders").addEventListener("click", closeMyOrdersModal);
  document.getElementById("myOrdersOverlay").addEventListener("click", e => {
    if (e.target.id === "myOrdersOverlay") closeMyOrdersModal();
  });

  document.querySelectorAll(".auth-tab").forEach(tab => {
    tab.addEventListener("click", () => switchAuthTab(tab.dataset.authTab));
  });

  document.getElementById("loginForm").addEventListener("submit", async e => {
    e.preventDefault();
    const phone = document.getElementById("loginPhone").value.trim();
    const password = document.getElementById("loginPassword").value;
    const result = await Store.loginCustomer(phone, password);
    if (!result.ok) {
      document.getElementById("loginError").textContent = result.error;
      document.getElementById("loginError").hidden = false;
      return;
    }
    closeAuthModal();
    e.target.reset();
    renderAccountUI();
    showToast(`Xush kelibsiz, ${result.customer.name.split(" ")[0]}!`);
  });

  document.getElementById("registerForm").addEventListener("submit", async e => {
    e.preventDefault();
    const result = await Store.registerCustomer({
      name: document.getElementById("regName").value.trim(),
      phone: document.getElementById("regPhone").value.trim(),
      password: document.getElementById("regPassword").value,
    });
    if (!result.ok) {
      document.getElementById("registerError").textContent = result.error;
      document.getElementById("registerError").hidden = false;
      return;
    }
    closeAuthModal();
    e.target.reset();
    renderAccountUI();
    showToast(`Xush kelibsiz, ${result.customer.name.split(" ")[0]}! Ro'yxatdan muvaffaqiyatli o'tdingiz.`);
  });

  document.getElementById("accountLogoutBtn").addEventListener("click", () => {
    Store.logoutCustomer();
    closeMyOrdersModal();
    renderAccountUI();
    showToast("Hisobdan chiqdingiz");
  });
}

document.addEventListener("DOMContentLoaded", init);
