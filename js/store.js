const SESSION_KEY = 'vermont_customer_session';
const ADMIN_TOKEN_KEY = 'vermont_admin_token';

function readSession() {
  try {
    return JSON.parse(localStorage.getItem(SESSION_KEY) || 'null');
  } catch {
    return null;
  }
}

function saveSession(value) {
  localStorage.setItem(SESSION_KEY, JSON.stringify(value));
}

function getCustomerToken() {
  return readSession()?.token || null;
}

function getAdminToken() {
  return sessionStorage.getItem(ADMIN_TOKEN_KEY);
}

async function api(path, { method = 'GET', body, auth } = {}) {
  const headers = { 'Content-Type': 'application/json' };
  const token = auth === 'admin' ? getAdminToken() : auth === 'customer' ? getCustomerToken() : null;
  if (token) headers.Authorization = `Bearer ${token}`;

  let response;
  try {
    response = await fetch(`${API_BASE}${path}`, {
      method,
      headers,
      body: body === undefined ? undefined : JSON.stringify(body),
    });
  } catch {
    throw new Error('Serverga ulanib bo‘lmadi. Backend ishga tushirilganini tekshiring.');
  }

  const data = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(data.error || 'Server xatosi');
  return data;
}

const Store = {
  api,
  getProducts: () => api('/products'),
  getPaymentInfo: () => api('/payment-info'),
  registerCustomer: (payload) => api('/customers/register', { method: 'POST', body: payload }),
  loginCustomer: (payload) => api('/customers/login', { method: 'POST', body: payload }),
  getOrdersByCustomer: () => api('/orders/mine', { auth: 'customer' }),
  addOrder: (payload) => api('/orders', { method: 'POST', body: payload, auth: 'customer' }),
  getOrders: () => api('/orders', { auth: 'admin' }),
  getStats: () => api('/stats', { auth: 'admin' }),
  addProduct: (payload) => api('/products', { method: 'POST', body: payload, auth: 'admin' }),
  updateProduct: (id, payload) => api(`/products/${id}`, { method: 'PATCH', body: payload, auth: 'admin' }),
  deleteProduct: (id) => api(`/products/${id}`, { method: 'DELETE', auth: 'admin' }),
  getCurrentCustomer: () => readSession()?.customer || null,
  saveCustomerSession: saveSession,
  logoutCustomer: () => localStorage.removeItem(SESSION_KEY),
  getAdminToken,
  setAdminToken: (token) => sessionStorage.setItem(ADMIN_TOKEN_KEY, token),
  logoutAdmin: () => sessionStorage.removeItem(ADMIN_TOKEN_KEY),
};
