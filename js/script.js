const state = {
  products: [],
  category: 'all',
  query: '',
  cart: JSON.parse(localStorage.getItem('vermont_cart') || '{}'),
};

const money = (value) => `${new Intl.NumberFormat('uz-UZ').format(Number(value) || 0)} so‘m`;
const saveCart = () => localStorage.setItem('vermont_cart', JSON.stringify(state.cart));

function showToast(message, type = 'info') {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = message;
  toast.className = `toast show ${type}`;
  clearTimeout(showToast.timer);
  showToast.timer = setTimeout(() => { toast.className = 'toast'; }, 2600);
}

function visibleProducts() {
  const query = state.query.toLowerCase();
  return state.products.filter((product) => {
    const categoryMatch = state.category === 'all' || product.cat === state.category;
    const textMatch = !query || `${product.name} ${product.description || ''}`.toLowerCase().includes(query);
    return categoryMatch && textMatch;
  });
}

function renderCategories() {
  const container = document.getElementById('categoryChips');
  container.innerHTML = CATEGORIES.map((category) => `
    <button class="chip ${state.category === category.id ? 'active' : ''}" data-category="${category.id}">
      ${category.label}
    </button>
  `).join('');
  container.querySelectorAll('[data-category]').forEach((button) => {
    button.addEventListener('click', () => {
      state.category = button.dataset.category;
      renderCategories();
      renderProducts();
    });
  });
}

function cartEntries() {
  return Object.entries(state.cart)
    .map(([id, quantity]) => ({ product: state.products.find((item) => item.id === id), quantity }))
    .filter((entry) => entry.product);
}

function renderProducts() {
  const container = document.getElementById('productsGrid');
  const products = visibleProducts();
  container.innerHTML = products.length ? products.map((product) => `
    <article class="product-card">
      <div class="product-image">${product.image ? `<img src="${product.image}" alt="${product.name}">` : ICONS[product.cat] || ICONS.aksessuar}</div>
      <div class="product-card-body">
        <span class="product-category">${CATEGORIES.find((item) => item.id === product.cat)?.label || product.cat}</span>
        <h3>${product.name}</h3>
        <p>${product.description || ''}</p>
        <div class="product-meta"><strong>${money(product.price)}</strong><span>★ ${product.rating}</span></div>
        <button class="add-product" data-id="${product.id}" ${product.stock <= 0 ? 'disabled' : ''}>
          ${product.stock <= 0 ? 'Tugagan' : 'Savatga qo‘shish'}
        </button>
      </div>
    </article>
  `).join('') : '<p class="empty-state">Mahsulot topilmadi.</p>';

  container.querySelectorAll('.add-product:not(:disabled)').forEach((button) => {
    button.addEventListener('click', () => {
      const id = button.dataset.id;
      state.cart[id] = (state.cart[id] || 0) + 1;
      saveCart();
      renderCart();
      showToast('Mahsulot savatga qo‘shildi');
    });
  });
}

function renderCart() {
  const items = document.getElementById('cartItems');
  const entries = cartEntries();
  document.getElementById('cartCount').textContent = entries.reduce((sum, item) => sum + item.quantity, 0);
  document.getElementById('cartTotal').textContent = money(entries.reduce((sum, item) => sum + item.product.price * item.quantity, 0));
  items.innerHTML = entries.length ? entries.map(({ product, quantity }) => `
    <div class="cart-item">
      <div><strong>${product.name}</strong><small>${money(product.price)}</small></div>
      <div class="quantity-controls">
        <button data-action="dec" data-id="${product.id}">−</button><span>${quantity}</span><button data-action="inc" data-id="${product.id}">+</button>
      </div>
    </div>
  `).join('') : '<p class="empty-state">Savat hozircha bo‘sh.</p>';
  items.querySelectorAll('[data-action]').forEach((button) => {
    button.addEventListener('click', () => {
      const id = button.dataset.id;
      state.cart[id] += button.dataset.action === 'inc' ? 1 : -1;
      if (state.cart[id] <= 0) delete state.cart[id];
      saveCart();
      renderCart();
    });
  });
}

async function init() {
  try {
    state.products = await Store.getProducts();
    renderCategories();
    renderProducts();
    renderCart();
  } catch (error) {
    showToast(error.message, 'error');
  }

  document.getElementById('searchInput')?.addEventListener('input', (event) => {
    state.query = event.target.value.trim();
    renderProducts();
  });
  document.getElementById('cartBtn')?.addEventListener('click', () => document.getElementById('cartModal').classList.add('active'));
  document.getElementById('closeCart')?.addEventListener('click', () => document.getElementById('cartModal').classList.remove('active'));
  document.getElementById('cartModal')?.addEventListener('click', (event) => {
    if (event.target.id === 'cartModal') event.currentTarget.classList.remove('active');
  });
  document.getElementById('checkoutBtn')?.addEventListener('click', () => {
    if (!Store.getCurrentCustomer()) {
      document.getElementById('cartModal').classList.remove('active');
      document.getElementById('authOverlay').classList.add('active');
      showToast('Buyurtma berish uchun avval kiring');
    } else {
      showToast('Buyurtma jarayoni keyingi bosqichda ulanadi');
    }
  });
}

document.addEventListener('DOMContentLoaded', init);
