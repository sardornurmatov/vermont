# DASTA — Erkaklar aksessuarlari marketplace (to'liq: frontend + backend)

Erkaklar aksessuarlari (soat, kamar, hamyon, ko'zoynak) sotadigan marketplace sayt, admin panel va endi **PostgreSQL bazasiga ulangan haqiqiy backend** bilan.

## Loyiha tuzilishi

```
dasta-market/
├── index.html, admin.html       # Frontend sahifalari
├── css/                          # Uslublar
├── js/
│   ├── config.js                # Backend API manzili
│   ├── store.js                  # Backend bilan gaplashuvchi API-mijoz
│   ├── auth.js                   # Admin login (backend orqali tekshiriladi)
│   ├── script.js, admin.js       # Sahifa mantiqi
│   ├── products.js               # Kategoriya nomlari va ikonkalar
│   └── utils.js                  # Rasm siqish
└── backend/                      # Express + PostgreSQL server
    ├── schema.sql                 # Baza sxemasi + boshlang'ich mahsulotlar
    ├── server.js, db.js, migrate.js
    ├── routes/                    # API endpointlari
    └── middleware/auth.js         # JWT tekshiruvi
```

## Tezkor ishga tushirish

**1-qadam — backend:**
```bash
cd backend
npm install
cp .env.example .env
# .env faylida DATABASE_URL'ni o'z PostgreSQL'ingizga moslang
npm run db:migrate
npm start
```
Batafsil (mahalliy yoki bulutda — Supabase/Railway/Neon) yo'riqnoma: **`backend/README.md`**.

**2-qadam — frontend:**
VS Code'da `dasta-market` papkasini oching, `index.html`ni Live Server bilan oching (yoki `python3 -m http.server 8000`). Backend ishlab turgan bo'lsa, sayt avtomatik ulanadi.

## Nima o'zgardi (localStorage'dan PostgreSQL'ga)

| Oldin | Endi |
|---|---|
| Ma'lumot faqat brauzerda (`localStorage`) | Ma'lumot serverda, PostgreSQL bazasida |
| Har bir qurilma o'z nusxasini ko'radi | Hamma bir xil ma'lumotni ko'radi (haqiqiy ko'p foydalanuvchili) |
| Admin/mijoz parollari ochiq matn | Admin parol serverda (`.env`); mijoz parollari bcrypt bilan xeshlangan |
| Har kim brauzer konsolidan o'zgartira oladi | Server tomonda JWT orqali tekshiriladi — haqiqiy xavfsizlik |

`js/store.js` barcha ma'lumot operatsiyalarini bitta joyda jamlaydi — shu fayl endi `fetch()` orqali backend API'siga so'rov yuboradi, qolgan kod (`script.js`, `admin.js`) esa deyarli o'zgarmadi.

## Admin panelga kirish

**Manzil:** `admin.html` | ** :** `backend/.env` dagi `ADMIN_PASSWORD` (standart: `dasta2026`)

Parol endi **serverda** tekshiriladi — brauzer kodida umuman yo'q. Bu haqiqiy xavfsizlik: birov "View Source" qilsa ham parolni ko'ra olmaydi.

## Admin panel imkoniyatlari

- **Statistika**: jami tushum, buyurtmalar soni, sotilgan mahsulotlar, kam qolgan mahsulotlar (bazadan real vaqtda hisoblanadi)
- **Buyurtmalar**: to'lov chekini ko'rish, "To'lovni tasdiqlash" / "Rad etish" / "Topshirildi" — tasdiqlangach ombor va sotilgan soni **avtomatik** yangilanadi
- **Mahsulotlar**: narx/ombor sonini jadvalda tahrirlash, rasm bilan yangi mahsulot qo'shish, o'zi qo'shgan mahsulotni o'chirish
- **Sozlamalar**: to'lov karta ma'lumotini o'zgartirish — darhol mijozlarga ko'rinadi

## Mijozlar uchun hisob va to'lov jarayoni

1. Mijoz ro'yxatdan o'tadi/kiradi (telefon + parol)
2. Mahsulot tanlaydi, savatga qo'shadi, "Buyurtma berish"
3. Ism/telefon (hisobga kirilgan bo'lsa avtomatik to'ldiriladi) + olib ketish manzili (mahsulot joylashgan shahar)
4. To'lov kartasi ko'rsatiladi → mijoz pul o'tkazadi → chek rasmini yuklaydi
5. Admin chekni ko'rib tasdiqlaydi/rad etadi
6. Mijoz "Buyurtmalarim"da holatni kuzatadi: tekshirilmoqda → tasdiqlandi → topshirildi

## Xavfsizlik bo'yicha eslatmalar

- Mijoz parollari **bcrypt** bilan xeshlanadi (bazada ochiq matn holida saqlanmaydi)
- Admin autentifikatsiyasi **JWT** token orqali — parol brauzerga hech qachon yuborilmaydi
- Productionga chiqarishdan oldin `backend/.env` dagi `JWT_SECRET` va `ADMIN_PASSWORD`ni albatta kuchli qiymatlarga almashtiring

## Keyingi qadamlar (ixtiyoriy)

- Haqiqiy to'lov tizimi (Payme/Click API) ulash — hozir "chekni qo'lda yuklash" usuli ishlatiladi
- Admin uchun ko'p foydalanuvchili login (hozir bitta umumiy parol) — `customers` jadvaliga o'xshash `admins` jadvali qo'shish orqali
- Rasm/chek fayllarini bazada emas, S3/Cloudinary kabi fayl xotirasida saqlash (hozir base64 sifatida to'g'ridan-to'g'ri bazada, kichik loyihalar uchun yetarli)
