const adminLoginForm = document.getElementById('adminLoginForm');
const loginPanel = document.getElementById('loginPanel');
const dashboardPanel = document.getElementById('dashboardPanel');
const adminError = document.getElementById('adminError');
const moneyAdmin = (value) => `${new Intl.NumberFormat('uz-UZ').format(Number(value) || 0)} so‘m`;

async function loadDashboard() {
  try {
    const [stats, products] = await Promise.all([Store.getStats(), Store.getProducts()]);
    document.getElementById('stats').innerHTML = [
      ['Tushum', moneyAdmin(stats.revenue)],
      ['Buyurtmalar', stats.totalOrders],
      ['Tekshirilmoqda', stats.pending],
      ['Sotilgan', stats.totalSold],
    ].map(([label, value]) => `<div class="stat"><span>${label}</span><strong>${value}</strong></div>`).join('');
    document.getElementById('productsBody').innerHTML = products.map((product) => `
      <tr><td>${product.name}</td><td><input data-id="${product.id}" data-field="price" type="number" value="${product.price}"></td><td><input data-id="${product.id}" data-field="stock" type="number" value="${product.stock}"></td><td>${product.sold || 0}</td></tr>
    `).join('');
    document.querySelectorAll('#productsBody input').forEach((input) => input.addEventListener('change', async () => {
      try { await Store.updateProduct(input.dataset.id, { [input.dataset.field]: Number(input.value) }); }
      catch (error) { alert(error.message); }
    }));
  } catch (error) {
    alert(error.message);
  }
}

async function unlock(password) {
  const data = await Store.api('/auth/admin/login', { method: 'POST', body: { password } });
  Store.setAdminToken(data.token);
  loginPanel.hidden = true;
  dashboardPanel.hidden = false;
  await loadDashboard();
}

adminLoginForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  adminError.textContent = '';
  try { await unlock(document.getElementById('adminPassword').value); }
  catch (error) { adminError.textContent = error.message; }
});

document.getElementById('refreshProducts')?.addEventListener('click', loadDashboard);
document.getElementById('logoutAdmin')?.addEventListener('click', () => { Store.logoutAdmin(); location.reload(); });
if (Store.getAdminToken()) { loginPanel.hidden = true; dashboardPanel.hidden = false; loadDashboard(); }
