document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.getElementById('authOverlay');
  const card = document.getElementById('authCard');
  const openButton = document.getElementById('openAuthBtn');
  const closeButton = document.getElementById('closeAuthBtn');
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  const open = () => overlay?.classList.add('active');
  const close = () => overlay?.classList.remove('active');

  openButton?.addEventListener('click', () => {
    if (Store.getCurrentCustomer()) {
      if (confirm('Hisobdan chiqishni xohlaysizmi?')) {
        Store.logoutCustomer();
        location.reload();
      }
      return;
    }
    open();
  });
  closeButton?.addEventListener('click', close);
  overlay?.addEventListener('click', (event) => {
    if (event.target === overlay) close();
  });
  document.getElementById('goToRegister')?.addEventListener('click', () => card?.classList.add('flipped'));
  document.getElementById('goToLogin')?.addEventListener('click', () => card?.classList.remove('flipped'));

  loginForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = loginForm.querySelector('button[type="submit"]');
    button.disabled = true;
    try {
      const data = await Store.loginCustomer({
        email: document.getElementById('loginUser').value.trim(),
        password: document.getElementById('loginPass').value,
      });
      Store.saveCustomerSession(data);
      close();
      loginForm.reset();
      updateAccountButton();
      showToast('Tizimga muvaffaqiyatli kirdingiz');
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      button.disabled = false;
    }
  });

  registerForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = registerForm.querySelector('button[type="submit"]');
    button.disabled = true;
    try {
      const data = await Store.registerCustomer({
        name: document.getElementById('regFullName').value.trim(),
        email: document.getElementById('regEmail').value.trim(),
        password: document.getElementById('regPassword').value,
      });
      Store.saveCustomerSession(data);
      close();
      registerForm.reset();
      card?.classList.remove('flipped');
      updateAccountButton();
      showToast('Hisob muvaffaqiyatli yaratildi');
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      button.disabled = false;
    }
  });

  window.updateAccountButton = function updateAccountButton() {
    if (!openButton) return;
    const customer = Store.getCurrentCustomer();
    openButton.innerHTML = customer
      ? `<i class="bi bi-person-check-fill"></i><span>${customer.name}</span>`
      : '<i class="bi bi-person-fill"></i><span>Kirish</span>';
  };

  updateAccountButton();
});
