document.addEventListener("DOMContentLoaded", () => {
  const authOverlay = document.getElementById("authOverlay");
  const openAuthBtn = document.getElementById("openAuthBtn");
  const closeAuthBtn = document.getElementById("closeAuthBtn");
  const authCard = document.getElementById("authCard");
  const goToRegister = document.getElementById("goToRegister");
  const goToLogin = document.getElementById("goToLogin");

  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");

  // Открытие / закрытие модального окна
  if (openAuthBtn && authOverlay) {
    openAuthBtn.addEventListener("click", () => authOverlay.classList.add("active"));
  }
  if (closeAuthBtn && authOverlay) {
    closeAuthBtn.addEventListener("click", () => authOverlay.classList.remove("active"));
  }
  if (authOverlay) {
    authOverlay.addEventListener("click", (e) => {
      if (e.target === authOverlay) authOverlay.classList.remove("active");
    });
  }

  // 3D переворот карточки
  if (goToRegister && authCard) {
    goToRegister.addEventListener("click", () => authCard.classList.add("flipped"));
  }
  if (goToLogin && authCard) {
    goToLogin.addEventListener("click", () => authCard.classList.remove("flipped"));
  }

  // Обработка регистрации
  if (registerForm) {
    registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fullName = document.getElementById("regFullName").value.trim();
      const email = document.getElementById("regEmail").value.trim();
      const password = document.getElementById("regPassword").value;

      try {
        const res = await fetch(`${CONFIG.API_BASE_URL}/customers/register`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ fullName, email, password })
        });
        const data = await res.json();
        if (data.success) {
          alert('Tabriklaymiz! Ro‘yxatdan o‘tdingiz.');
          localStorage.setItem('vermont_token', data.token);
          localStorage.setItem('vermont_user', JSON.stringify(data.user));
          location.reload();
        } else {
          alert(data.message || 'Ro‘yxatdan o‘tishda xatolik yuz berdi');
        }
      } catch (err) {
        alert('Server bilan bog‘lanib bo‘lmadi. Backend ishga tushirilganini tekshiring.');
      }
    });
  }

  // Обработка входа (Login)
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const usernameOrEmail = document.getElementById("loginUser").value.trim();
      const password = document.getElementById("loginPass").value;

      try {
        const res = await fetch(`${CONFIG.API_BASE_URL}/customers/login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ usernameOrEmail, password })
        });
        const data = await res.json();
        if (data.success) {
          alert('Tizimga muvaffaqiyatli kirdingiz!');
          localStorage.setItem('vermont_token', data.token);
          localStorage.setItem('vermont_user', JSON.stringify(data.user));
          location.reload();
        } else {
          alert(data.message || 'Login yoki parol noto‘g‘ri');
        }
      } catch (err) {
        alert('Server bilan bog‘lanib bo‘lmadi. Backend ishga tushirilganini tekshiring.');
      }
    });
  }

  // Если пользователь уже авторизован
  const savedUser = localStorage.getItem('vermont_user');
  if (savedUser && openAuthBtn) {
    const user = JSON.parse(savedUser);
    openAuthBtn.innerHTML = `<i class="fa-solid fa-user-check"></i> <span>${user.fullName || 'Profil'}</span>`;
    openAuthBtn.onclick = () => {
      if (confirm('Tizimdan chiqmoqchimisiz?')) {
        localStorage.removeItem('vermont_token');
        localStorage.removeItem('vermont_user');
        location.reload();
      }
    };
  }

  // Безопасный вызов админского триггера (предотвращает Uncaught Error)
  const adminTrigger = document.getElementById('adminGate') || document.querySelector('.admin-gate');
  if (adminTrigger) {
    adminTrigger.addEventListener('click', () => {
      window.location.href = 'admin.html';
    });
  }
});