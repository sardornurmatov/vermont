// DASTA — backend API bilan ishlaydigan "Store"
//
// Eslatma: bu fayl endi ma'lumotni o'zi saqlamaydi — barcha mahsulot,
// buyurtma va to'lov ma'lumotlari PostgreSQL bazasida (backend orqali)
// turadi. Faqat quyidagilar hamon brauzerda (localStorage) qoladi, chunki
// ular haqiqatan ham "shu qurilma/sessiya"ga tegishli narsalar:
//   - savat (checkout qilinmagunча vaqtinchalik)
//   - admin JWT tokeni (sessionStorage — auth.js boshqaradi)
//   - mijoz JWT tokeni + profili (login/registerdan keyin keshlanadi)

const LS_KEYS = {
  cart: "dasta_cart",
  customerSession: "dasta_customer_session", // { token, customer: {id,name,phone} }
};

function readLS(key, fallback) {
  try {
    const raw = localStorage.getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch {
    return fallback;
  }
}
function writeLS(key, value) {
  localStorage.setItem(key, JSON.stringify(value));
}

function getAdminToken() {
  return sessionStorage.getItem("dasta_admin_token");
}
function getCustomerToken() {
  const session = readLS(LS_KEYS.customerSession, null);
  return session ? session.token : null;
}

/**
 * fetch() ustidan yupqa o'ram: JSON yuboradi/qabul qiladi, xato bo'lsa
 * serverning { error: "..." } xabarini Error sifatida tashlaydi.
 * `auth: "admin" | "customer"` berilsa, tegishli tokenni Authorization
 * header'iga avtomatik qo'shadi.
 */
async function api(path, { method = "GET", body, auth } = {}) {
  const headers = { "Content-Type": "application/json" };
  if (auth === "admin") {
    const token = getAdminToken();
    if (token) headers.Authorization = `Bearer ${token}`;
  } else if (auth === "customer") {
    const token = getCustomerToken();
    if (token) headers.Authorization = `Bearer ${token}`;
  }

  let res;
  try {
    res = await fetch(`${API_BASE}${path}`, {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
    });
  } catch {
    throw new Error("Serverga ulanib bo'lmadi. Backend ishga tushirilganini tekshiring.");
  }

  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.error || "Noma'lum server xatosi");
  return data;
}

const Store = {
  // ---------- PRODUCTS ----------
  async getProducts() {
    return api("/products");
  },

  async addProduct(product) {
    return api("/products", { method: "POST", body: product, auth: "admin" });
  },

  async updateProduct(id, changes) {
    return api(`/products/${id}`, { method: "PATCH", body: changes, auth: "admin" });
  },

  async deleteProduct(id) {
    return api(`/products/${id}`, { method: "DELETE", auth: "admin" });
  },

  // ---------- CART (faqat shu brauzerda, checkout qilinmagunча) ----------
  getCart() {
    return readLS(LS_KEYS.cart, {});
  },
  saveCart(cart) {
    writeLS(LS_KEYS.cart, cart);
  },

  // ---------- PAYMENT INFO ----------
  async getPaymentInfo() {
    return api("/payment-info");
  },
  async setPaymentInfo(info) {
    return api("/payment-info", { method: "PUT", body: info, auth: "admin" });
  },

  // ---------- CUSTOMER ACCOUNTS ----------
  async registerCustomer({ name, phone, password }) {
    try {
      const data = await api("/customers/register", { method: "POST", body: { name, phone, password } });
      writeLS(LS_KEYS.customerSession, data);
      return { ok: true, customer: data.customer };
    } catch (err) {
      return { ok: false, error: err.message };
    }
  },

  async loginCustomer(phone, password) {
    try {
      const data = await api("/customers/login", { method: "POST", body: { phone, password } });
      writeLS(LS_KEYS.customerSession, data);
      return { ok: true, customer: data.customer };
    } catch (err) {
      return { ok: false, error: err.message };
    }
  },

  logoutCustomer() {
    localStorage.removeItem(LS_KEYS.customerSession);
  },

  // Sinxron — chunki login/registerdan keyin natija shu yerda keshlanadi.
  // Bu ko'plab joylarda (renderAccountUI va h.k.) darhol, tarmoqqa
  // so'rov yubormasdan ishlatilishi uchun ataylab qilingan.
  getCurrentCustomer() {
    const session = readLS(LS_KEYS.customerSession, null);
    return session ? session.customer : null;
  },

  // ---------- ORDERS ----------
  async getOrders() {
    return api("/orders", { auth: "admin" });
  },

  async getOrdersByCustomer() {
    return api("/orders/mine", { auth: "customer" });
  },

  async addOrder(order) {
    return api("/orders", { method: "POST", body: order, auth: "customer" });
  },

  async updateOrderStatus(orderId, status) {
    return api(`/orders/${orderId}/status`, { method: "PATCH", body: { status }, auth: "admin" });
  },

  // ---------- STATS (admin dashboard) ----------
  async getStats() {
    return api("/stats", { auth: "admin" });
  },
};
