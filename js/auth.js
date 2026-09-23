// DASTA — admin panel uchun parol himoyasi
//
// Endi parol serverda (backend/.env dagi ADMIN_PASSWORD) tekshiriladi —
// bu HAQIQIY xavfsizlik, chunki parol brauzer kodida umuman yo'q. Server
// to'g'ri parolni tasdiqlagach JWT token qaytaradi, u sessionStorage'da
// saqlanadi va admin API so'rovlarida Authorization header sifatida
// yuboriladi (buni js/store.js o'z ichida avtomatik qiladi).

const ADMIN_TOKEN_KEY = "dasta_admin_token";

function isAdminAuthed() {
  return Boolean(sessionStorage.getItem(ADMIN_TOKEN_KEY));
}

function logoutAdmin() {
  sessionStorage.removeItem(ADMIN_TOKEN_KEY);
  location.reload();
}

function initAdminGate() {
  const gate = document.getElementById("adminGate");
  const shell = document.querySelector(".app-shell");
  const form = document.getElementById("gateForm");
  const errorMsg = document.getElementById("gateError");
  const submitBtn = form.querySelector('button[type="submit"]');

  function unlock() {
    gate.hidden = true;
    shell.hidden = false;
    if (typeof window.initAdminPanel === "function") window.initAdminPanel();
  }

  if (isAdminAuthed()) {
    unlock();
    return;
  }

  form.addEventListener("submit", async e => {
    e.preventDefault();
    errorMsg.hidden = true;
    const password = document.getElementById("gatePassword").value;

    submitBtn.disabled = true;
    submitBtn.textContent = "Tekshirilmoqda...";

    try {
      const res = await fetch(`${API_BASE}/auth/admin/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ password }),
      });
      const data = await res.json();

      if (!res.ok) {
        errorMsg.textContent = data.error || "Parol noto'g'ri. Qayta urinib ko'ring.";
        errorMsg.hidden = false;
        document.getElementById("gatePassword").value = "";
        document.getElementById("gatePassword").focus();
        return;
      }

      sessionStorage.setItem(ADMIN_TOKEN_KEY, data.token);
      unlock();
    } catch {
      errorMsg.textContent = "Serverga ulanib bo'lmadi. Backend ishga tushirilganini tekshiring.";
      errorMsg.hidden = false;
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Kirish";
    }
  });
}

document.addEventListener("DOMContentLoaded", initAdminGate);
