// DASTA — kategoriyalar va SVG ikonkalar
// Diqqat: haqiqiy mahsulot ma'lumotlari endi PostgreSQL bazasida
// (backend/schema.sql) turadi. Bu fayl faqat frontendda kerak bo'ladigan
// statik narsalarni — kategoriya nomlari va rasm o'rnini bosuvchi
// ikonkalarni — saqlaydi.

const CATEGORIES = [
  { id: "soat", label: "Soatlar" },
  { id: "kamar", label: "Kamarlar" },
  { id: "hamyon", label: "Hamyonlar" },
  { id: "koinok", label: "Ko'zoynaklar" },
  { id: "aksessuar", label: "Boshqa aksessuar" },
];

const ICONS = {
  soat: `<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2">
    <circle cx="32" cy="32" r="20"/>
    <path d="M32 20v12l8 6" stroke-linecap="round"/>
    <path d="M26 6h12M26 58h12" stroke-linecap="round"/>
  </svg>`,
  kamar: `<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2">
    <rect x="6" y="26" width="52" height="12" rx="2"/>
    <rect x="24" y="22" width="16" height="20" rx="2"/>
    <circle cx="32" cy="32" r="3" fill="currentColor" stroke="none"/>
  </svg>`,
  hamyon: `<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2">
    <rect x="8" y="18" width="48" height="32" rx="4"/>
    <path d="M8 28h48"/>
    <circle cx="44" cy="34" r="3" fill="currentColor" stroke="none"/>
  </svg>`,
  koinok: `<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2">
    <circle cx="18" cy="34" r="10"/>
    <circle cx="46" cy="34" r="10"/>
    <path d="M28 32h8M8 30l6-8h4M56 30l-6-8h-4"/>
  </svg>`,
  aksessuar: `<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M20 44c0-10 6-20 12-20s12 10 12 20" stroke-linecap="round"/>
    <circle cx="32" cy="14" r="6"/>
    <path d="M20 44h24" stroke-linecap="round"/>
  </svg>`,
};
