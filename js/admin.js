// DASTA — admin panel mantiqi (backend API bilan)

const fmt = (num) => new Intl.NumberFormat("uz-UZ").format(Math.round(num)) + " so'm";

let orderStatusFilter = "all";
let ORDERS_CACHE = [];
let PRODUCTS_CACHE = [];

const STATUS_CLASS = {
  "tekshirilmoqda": "status-tekshirilmoqda",
  "tasdiqlandi": "status-tasdiqlandi",
  "topshirildi": "status-topshirildi",
  "bekor qilindi": "status-bekor-qilindi",
};

// ---------- STATS ----------
async function renderStats() {
  let s;
  try {
    s = await Store.getStats();
  } catch (err) {
    showToast(err.message);
    return;
  }

  const cards = [
    {
      label: "Jami tushum",
      value: fmt(s.revenue),
      sub: `${s.totalOrders} ta buyurtmadan`,
      icon: `<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>`,
    },
    {
      label: "Buyurtmalar",
      value: s.totalOrders,
      sub: `${s.pending} ta tekshirilmoqda`,
      icon: `<path d="M3 4h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6"/>`,
    },
    {
      label: "Sotilgan mahsulotlar",
      value: s.totalSold,
      sub: s.topProduct ? `Eng ko'p: ${s.topProduct.name}` : "",
      icon: `<path d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>`,
    },
    {
      label: "Kam qolgan mahsulotlar",
      value: s.lowStock.length,
      sub: s.lowStock.length ? s.lowStock.map(p => p.name).slice(0, 2).join(", ") : "Hammasi yetarli",
      icon: `<path d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z"/>`,
    },
  ];

  document.getElementById("statGrid").innerHTML = cards.map(c => `
    <div class="stat-card">
      <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${c.icon}</svg></div>
      <span class="stat-label">${c.label}</span>
      <span class="stat-value">${c.value}</span>
      <span class="stat-sub">${c.sub}</span>
    </div>
  `).join("");
}

// ---------- ORDERS ----------
function statusClass(status) {
  return STATUS_CLASS[status] || "status-tekshirilmoqda";
}

async function loadOrders() {
  try {
    ORDERS_CACHE = await Store.getOrders();
  } catch (err) {
    showToast(err.message);
  }
}

function renderOrders() {
  const tbody = document.getElementById("ordersBody");
  let orders = ORDERS_CACHE;
  if (orderStatusFilter !== "all") {
    orders = orders.filter(o => o.status === orderStatusFilter);
  }

  if (orders.length === 0) {
    tbody.innerHTML = `<tr class="empty-row"><td colspan="9">Hozircha buyurtmalar yo'q.</td></tr>`;
    return;
  }

  tbody.innerHTML = orders.map(o => {
    const itemsSummary = o.items.map(i => `${i.name} ×${i.qty}`).join(", ");
    const date = new Date(o.createdAt).toLocaleString("uz-UZ", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" });

    let actions = "";
    if (o.status === "tekshirilmoqda") {
      actions = `<button class="accept" data-action="tasdiqlandi">To'lovni tasdiqlash</button>
                 <button class="cancel" data-action="bekor qilindi">Rad etish</button>`;
    } else if (o.status === "tasdiqlandi") {
      actions = `<button class="ship" data-action="topshirildi">Topshirildi deb belgilash</button>
                 <button class="cancel" data-action="bekor qilindi">Bekor qilish</button>`;
    } else {
      actions = `<span class="cell-muted">—</span>`;
    }

    const receiptCell = o.receipt
      ? `<button class="receipt-thumb"><img src="${o.receipt}" alt="Chek"></button>`
      : `<span class="cell-muted">—</span>`;

    return `
      <tr data-order="${o.id}">
        <td data-label="Buyurtma"><span class="cell-strong">${o.id}</span></td>
        <td data-label="Mijoz">
          <div class="cell-strong">${o.customer.name}</div>
          <div class="cell-muted">${o.customer.phone}</div>
        </td>
        <td data-label="Mahsulotlar"><span class="cell-muted">${itemsSummary}</span></td>
        <td data-label="Olib ketish"><span class="cell-muted">${(o.pickupLocations || []).join(", ") || "—"}</span></td>
        <td data-label="Chek">${receiptCell}</td>
        <td data-label="Summa"><span class="cell-strong">${fmt(o.total)}</span></td>
        <td data-label="Sana"><span class="cell-muted">${date}</span></td>
        <td data-label="Holati"><span class="status-badge ${statusClass(o.status)}">${o.status}</span></td>
        <td data-label="Amallar"><div class="row-actions">${actions}</div></td>
      </tr>
    `;
  }).join("");

  tbody.querySelectorAll("[data-action]").forEach(btn => {
    btn.addEventListener("click", async () => {
      const orderId = btn.closest("tr").dataset.order;
      const newStatus = btn.dataset.action;
      btn.disabled = true;
      try {
        await Store.updateOrderStatus(orderId, newStatus);
        await Promise.all([loadOrders(), loadProducts()]);
        renderOrders();
        renderProducts();
        await renderStats();
        showToast(`Buyurtma holati: "${newStatus}"`);
      } catch (err) {
        showToast(err.message);
        btn.disabled = false;
      }
    });
  });

  tbody.querySelectorAll(".receipt-thumb").forEach(btn => {
    btn.addEventListener("click", () => {
      const orderId = btn.closest("tr").dataset.order;
      const order = ORDERS_CACHE.find(o => o.id === orderId);
      if (order?.receipt) openLightbox(order.receipt);
    });
  });
}

// ---------- receipt lightbox ----------
function openLightbox(src) {
  document.getElementById("lightboxImage").src = src;
  document.getElementById("receiptLightbox").classList.add("open");
}
function closeLightbox() {
  document.getElementById("receiptLightbox").classList.remove("open");
}

// ---------- PRODUCTS ----------
async function loadProducts() {
  try {
    PRODUCTS_CACHE = await Store.getProducts();
  } catch (err) {
    showToast(err.message);
  }
}

function renderProducts() {
  const tbody = document.getElementById("productsBody");

  tbody.innerHTML = PRODUCTS_CACHE.map(p => {
    const low = (p.stock ?? 0) <= 5;
    return `
      <tr data-id="${p.id}">
        <td data-label="Mahsulot">
          <div class="name-cell">
            <div class="mini-thumb">${p.image ? `<img src="${p.image}" alt="${p.name}">` : ICONS[p.cat]}</div>
            <span class="cell-strong">${p.name}</span>
            ${p.isCustom ? '<span class="badge-custom">yangi</span>' : ""}
          </div>
        </td>
        <td data-label="Kategoriya"><span class="cell-muted">${CATEGORIES.find(c => c.id === p.cat)?.label || p.cat}</span></td>
        <td data-label="Narx">
          <input class="editable price-input" type="number" min="0" step="1000" value="${p.price}">
        </td>
        <td data-label="Ombor">
          <input class="editable stock-input ${low ? 'stock-cell low' : ''}" type="number" min="0" value="${p.stock ?? 0}">
        </td>
        <td data-label="Sotilgan"><span class="cell-muted">${p.sold || 0}</span></td>
        <td data-label="Reyting"><span class="cell-muted">★ ${p.rating}</span></td>
        <td data-label="">
          ${p.isCustom ? `<button class="delete-btn" data-del="${p.id}">o'chirish</button>` : `<span class="cell-muted">—</span>`}
        </td>
      </tr>
    `;
  }).join("");

  tbody.querySelectorAll(".price-input").forEach(input => {
    input.addEventListener("change", async () => {
      const id = input.closest("tr").dataset.id;
      try {
        await Store.updateProduct(id, { price: Number(input.value) || 0 });
        await loadProducts();
        showToast("Narx yangilandi");
      } catch (err) {
        showToast(err.message);
      }
    });
  });

  tbody.querySelectorAll(".stock-input").forEach(input => {
    input.addEventListener("change", async () => {
      const id = input.closest("tr").dataset.id;
      try {
        await Store.updateProduct(id, { stock: Number(input.value) || 0 });
        await loadProducts();
        renderProducts();
        await renderStats();
        showToast("Ombor yangilandi");
      } catch (err) {
        showToast(err.message);
      }
    });
  });

  tbody.querySelectorAll("[data-del]").forEach(btn => {
    btn.addEventListener("click", async () => {
      if (!confirm("Ushbu mahsulotni o'chirishni tasdiqlaysizmi?")) return;
      try {
        await Store.deleteProduct(btn.dataset.del);
        await loadProducts();
        renderProducts();
        await renderStats();
        showToast("Mahsulot o'chirildi");
      } catch (err) {
        showToast(err.message);
      }
    });
  });
}

// ---------- toast ----------
let toastTimer;
function showToast(msg) {
  const toast = document.getElementById("toast");
  toast.textContent = msg;
  toast.classList.add("show");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove("show"), 1800);
}

// ---------- tabs ----------
function initTabs() {
  document.querySelectorAll(".admin-tab").forEach(tab => {
    tab.addEventListener("click", () => {
      document.querySelectorAll(".admin-tab").forEach(t => t.classList.remove("active"));
      document.querySelectorAll(".tab-panel").forEach(p => p.classList.remove("active"));
      tab.classList.add("active");
      document.querySelector(`.tab-panel[data-panel="${tab.dataset.tab}"]`).classList.add("active");
    });
  });
}

// ---------- order filter tabs ----------
function initOrderFilters() {
  document.getElementById("orderFilterTabs").querySelectorAll("button").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll("#orderFilterTabs button").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      orderStatusFilter = btn.dataset.status;
      renderOrders();
    });
  });
}

// ---------- image upload: compress before sending to backend ----------
let pendingImage = null;

function setImagePreview(dataUrl) {
  pendingImage = dataUrl;
  const preview = document.getElementById("imagePreview");
  const empty = document.getElementById("imageUploadEmpty");
  const removeBtn = document.getElementById("removeImage");
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

function initImageUpload() {
  const box = document.getElementById("imageUploadBox");
  const fileInput = document.getElementById("newImage");
  const removeBtn = document.getElementById("removeImage");

  box.addEventListener("click", () => fileInput.click());

  fileInput.addEventListener("change", async () => {
    const file = fileInput.files[0];
    if (!file) return;
    if (!file.type.startsWith("image/")) {
      showToast("Faqat rasm fayli tanlang");
      return;
    }
    try {
      const compressed = await compressImage(file, 500, 0.8);
      setImagePreview(compressed);
    } catch (err) {
      showToast("Rasmni yuklashda xatolik yuz berdi");
    }
  });

  removeBtn.addEventListener("click", e => {
    e.stopPropagation();
    fileInput.value = "";
    setImagePreview(null);
  });
}

// ---------- add product modal ----------
function openAddProductModal() {
  document.getElementById("addProductOverlay").classList.add("open");
}
function closeAddProductModal() {
  document.getElementById("addProductOverlay").classList.remove("open");
  document.getElementById("newImage").value = "";
  setImagePreview(null);
}

function initAddProductModal() {
  const select = document.getElementById("newCat");
  select.innerHTML = CATEGORIES.map(c => `<option value="${c.id}">${c.label}</option>`).join("");

  initImageUpload();

  document.getElementById("openAddProduct").addEventListener("click", openAddProductModal);
  document.getElementById("railAddProduct").addEventListener("click", openAddProductModal);
  document.getElementById("closeAddProduct").addEventListener("click", closeAddProductModal);
  document.getElementById("addProductOverlay").addEventListener("click", e => {
    if (e.target.id === "addProductOverlay") closeAddProductModal();
  });

  document.getElementById("addProductForm").addEventListener("submit", async e => {
    e.preventDefault();
    const product = {
      id: "custom-" + Date.now(),
      cat: document.getElementById("newCat").value,
      name: document.getElementById("newName").value.trim(),
      desc: document.getElementById("newDesc").value.trim(),
      price: Number(document.getElementById("newPrice").value) || 0,
      stock: Number(document.getElementById("newStock").value) || 0,
      location: document.getElementById("newLocation").value.trim(),
      image: pendingImage || null,
      rating: 5.0,
      reviews: 0,
      sold: 0,
    };

    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    try {
      await Store.addProduct(product);
      e.target.reset();
      setImagePreview(null);
      closeAddProductModal();
      await loadProducts();
      renderProducts();
      await renderStats();
      showToast("Yangi mahsulot qo'shildi");
    } catch (err) {
      showToast(err.message);
    } finally {
      submitBtn.disabled = false;
    }
  });
}

// ---------- settings: payment card ----------
async function initSettings() {
  try {
    const info = await Store.getPaymentInfo();
    document.getElementById("settingsCardNumber").value = info.cardNumber;
    document.getElementById("settingsCardHolder").value = info.cardHolder;
    document.getElementById("settingsBank").value = info.bank;
  } catch (err) {
    showToast(err.message);
  }

  document.getElementById("paymentSettingsForm").addEventListener("submit", async e => {
    e.preventDefault();
    try {
      await Store.setPaymentInfo({
        cardNumber: document.getElementById("settingsCardNumber").value.trim(),
        cardHolder: document.getElementById("settingsCardHolder").value.trim(),
        bank: document.getElementById("settingsBank").value.trim(),
      });
      showToast("To'lov ma'lumotlari saqlandi");
    } catch (err) {
      showToast(err.message);
    }
  });
}

// ---------- init ----------
async function init() {
  initTabs();
  initOrderFilters();
  initAddProductModal();

  document.getElementById("closeLightbox").addEventListener("click", closeLightbox);
  document.getElementById("receiptLightbox").addEventListener("click", e => {
    if (e.target.id === "receiptLightbox") closeLightbox();
  });

  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) logoutBtn.addEventListener("click", () => {
    if (confirm("Admin panelidan chiqishni tasdiqlaysizmi?")) logoutAdmin();
  });

  await Promise.all([loadOrders(), loadProducts()]);
  renderOrders();
  renderProducts();
  await renderStats();
  await initSettings();
}

// Diqqat: bu yerda DOMContentLoaded orqali avtomatik chaqirilmaydi —
// chunki bu funksiya admin-only API'larni chaqiradi va parol
// tasdiqlanmaguncha ishga tushmasligi kerak. Buni js/auth.js ichidagi
// parol tekshiruvi muvaffaqiyatli bo'lganda chaqiradi (window.initAdminPanel).
window.initAdminPanel = init;
