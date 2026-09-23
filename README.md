# Vermont Frontend

Vermont marketplace’ning alohida frontend loyihasi. Backend Java Spring Boot asosida boshqa repository’da joylashgan:

- Frontend: https://github.com/sardornurmatov/vermont
- Backend: https://github.com/sardornurmatov/vermont-backend

## Tuzilishi

```text
vermont/
├── index.html       # Do‘kon sahifasi
├── admin.html       # Admin panel
├── css/             # Dizayn va responsive uslublar
└── js/
    ├── config.js    # Java backend API manzili
    ├── store.js     # REST API mijoz
    ├── auth.js      # Login va registration
    ├── script.js    # Do‘kon sahifasi
    ├── admin.js     # Admin panel
    └── products.js  # Kategoriyalar va ikonkalar
```

Bu repository’da backend kodi, PostgreSQL sxemasi, `.env` yoki server dependency’lari saqlanmaydi.

## Ishga tushirish

Avval Java backendni ishga tushiring. Standart API manzili:

```text
http://localhost:8080/api
```

Frontendni VS Code Live Server orqali `http://localhost:5500` manzilida oching. Yoki loyiha papkasida:

```bash
python -m http.server 5500
```

So‘ng brauzerda oching:

```text
http://localhost:5500
```

## Backend manzilini o‘zgartirish

`js/config.js` faylida:

```js
const CONFIG = {
  API_BASE_URL: 'http://localhost:8080/api',
  STORE_NAME: 'Vermont Shop',
};
```

Production serverga joylashtirganda `API_BASE_URL` qiymatini Java backendning haqiqiy HTTPS manziliga almashtiring.

## Admin panel

```text
http://localhost:5500/admin.html
```

Admin paroli frontendda saqlanmaydi. U Java backendning `ADMIN_PASSWORD` muhit o‘zgaruvchisida belgilanadi.
