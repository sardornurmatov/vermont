# DASTA backend — Express + PostgreSQL

Bu backend `dasta-market` do'kon saytining haqiqiy "miyasi": mahsulotlar, buyurtmalar, mijozlar va to'lov ma'lumotlari endi shu server orqali PostgreSQL bazasida saqlanadi (avvalgi `localStorage` versiyasi o'rnida).

## 1. Talab qilinadigan narsalar

- Node.js 18+ (`node -v` bilan tekshiring)
- PostgreSQL bazasi — **mahalliy** (kompyuteringizda) yoki **bulutda** (pastda 3 ta bepul variant ko'rsatilgan)

## 2. O'rnatish (mahalliy PostgreSQL bilan)

### 2.1. PostgreSQL o'rnatish

**Windows:** https://www.postgresql.org/download/windows/ dan yuklab o'rnating.
**macOS:** `brew install postgresql@16 && brew services start postgresql@16`
**Ubuntu/Debian:** `sudo apt install postgresql postgresql-contrib`

O'rnatgach, baza yarating:
```bash
psql -U postgres -c "CREATE DATABASE dasta;"
```

### 2.2. Backendni sozlash

```bash
cd backend
npm install
cp .env.example .env
```

`.env` faylini oching va `DATABASE_URL`, `ADMIN_PASSWORD`, `JWT_SECRET` qiymatlarini o'zingizga moslang (misollar fayl ichida izohlangan).

### 2.3. Jadvallarni yaratish va boshlang'ich mahsulotlarni yuklash

```bash
npm run db:migrate
```

Bu buyruq `schema.sql` faylini bazaga qo'llaydi — jadvallar yaratiladi va 12 ta namuna mahsulot qo'shiladi. Xavfsiz: qayta ishga tushirsangiz ham mavjud ma'lumotni buzmaydi (`IF NOT EXISTS` / `ON CONFLICT DO NOTHING`).

### 2.4. Serverni ishga tushirish

```bash
npm start
```

Konsolda `DASTA backend http://localhost:4000 da ishlayapti` chiqishi kerak.

### 2.5. Frontendni ulash

`../js/config.js` faylida `API_BASE` manzili `http://localhost:4000/api` bo'lishi kerak (standart holatda shunday). So'ng frontendni odatdagidek Live Server orqali oching — endi u to'g'ridan-to'g'ri shu backendga ulanadi.

> **Eslatma:** frontend va backend ikkisi ham ishga tushirilgan bo'lishi kerak — ikkisi alohida jarayon. Backend to'xtatilsa, sayt "Serverga ulanib bo'lmadi" xabarini beradi.

---

## 3. Bulutda joylashtirish (deploy)

Agar saytni haqiqiy foydalanuvchilar ishlatadigan qilib qo'ymoqchi bo'lsangiz, backend va baza internetda joylashgan bo'lishi kerak. Uchta bepul variant:

### Variant A — Supabase (eng oson, faqat baza uchun)

Supabase — bepul PostgreSQL taqdim etadi, backend kodini esa boshqa joyda (masalan Railway yoki Render) joylashtirasiz.

1. https://supabase.com da ro'yxatdan o'ting, yangi loyiha yarating
2. Project Settings → Database → "Connection string" (URI) ni nusxalang
3. Buni backend'ning `.env` faylidagi `DATABASE_URL`ga qo'ying, va `DATABASE_SSL=true` qiling
4. `npm run db:migrate` ni **shu connection string bilan** ishga tushiring (mahalliy kompyuterdan ham ishlaydi)
5. Backend kodini Render/Railway'ga joylashtiring (pastga qarang)

### Variant B — Railway (baza + backend, hammasi bir joyda)

1. https://railway.app da ro'yxatdan o'ting
2. "New Project" → "Deploy PostgreSQL" — bu sizga baza uchun `DATABASE_URL` beradi
3. Yana bitta xizmat qo'shing: "New" → "GitHub Repo" (backend kodingizni GitHub'ga yuklab, shu yerdan ulang) yoki "Empty Service" orqali qo'lda deploy qiling
4. Xizmat sozlamalarida Environment Variables bo'limiga `.env` dagi barcha qiymatlarni qo'shing (`DATABASE_URL` avtomatik beriladi, qolganlarini o'zingiz kiritasiz)
5. Start Command: `npm start`, va migratsiyani bir marta "Railway CLI" orqali yoki `npm run db:migrate` ni shu muhitda ishga tushirib bajaring

### Variant C — Neon (baza) + Render (backend)

1. https://neon.tech da bepul baza yarating, connection stringni oling (`DATABASE_SSL=true` kerak bo'ladi)
2. https://render.com da "New Web Service" → GitHub repo'ni ulang → Build Command: `npm install`, Start Command: `npm start`
3. Environment Variables bo'limida `.env` dagi barcha qiymatlarni kiriting
4. Deploy bo'lgach, bir marta terminal/shell orqali (Render "Shell" tab) `npm run db:migrate` ni ishga tushiring

### Deploy qilgandan keyin

`../js/config.js` dagi `API_BASE`ni serveringizning haqiqiy manziliga o'zgartiring:
```js
const API_BASE = "https://sizning-backend-manzilingiz.com/api";
```

Va backend `.env` dagi `FRONTEND_ORIGIN`ni saytingiz joylashgan haqiqiy manzilga o'zgartiring (CORS uchun).

---

## 4. API endpointlari (qisqacha)

| Method | Yo'l | Kimga ochiq | Vazifasi |
|---|---|---|---|
| GET | `/api/products` | Hammaga | Mahsulotlar ro'yxati |
| POST | `/api/products` | Admin | Yangi mahsulot qo'shish |
| PATCH | `/api/products/:id` | Admin | Narx/ombor/boshqa maydonlarni yangilash |
| DELETE | `/api/products/:id` | Admin | Faqat admin qo'shgan mahsulotni o'chirish |
| GET | `/api/payment-info` | Hammaga | To'lov karta ma'lumoti |
| PUT | `/api/payment-info` | Admin | Karta ma'lumotini o'zgartirish |
| POST | `/api/customers/register` | Hammaga | Mijoz ro'yxatdan o'tishi |
| POST | `/api/customers/login` | Hammaga | Mijoz kirishi |
| GET | `/api/orders` | Admin | Barcha buyurtmalar |
| GET | `/api/orders/mine` | Mijoz | O'z buyurtmalari |
| POST | `/api/orders` | Hammaga (mehmon ham) | Buyurtma yaratish |
| PATCH | `/api/orders/:id/status` | Admin | Buyurtma holatini o'zgartirish |
| GET | `/api/stats` | Admin | Dashboard statistikasi |
| POST | `/api/auth/admin/login` | Hammaga | Admin login (parolni tekshiradi) |

## 5. Xavfsizlik haqida

- Mijoz parollari **bcrypt** bilan xeshlanadi — bazada hech qachon ochiq matn holida saqlanmaydi
- Admin paroli faqat serverning `.env` faylida — brauzer kodida umuman yo'q
- Barcha yozish amallari (mahsulot qo'shish/o'zgartirish, buyurtma holatini o'zgartirish) JWT token orqali tekshiriladi
- **Productionga chiqarishdan oldin albatta**: `.env` dagi `JWT_SECRET`ni uzun, tasodifiy qatorga o'zgartiring (`openssl rand -hex 32` orqali yaratish mumkin), va `ADMIN_PASSWORD`ni kuchli parolga almashtiring
