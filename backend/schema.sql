-- DASTA — PostgreSQL sxemasi
-- Ishga tushirish: psql -U postgres -d dasta -f schema.sql
-- (yoki hosting'ning SQL konsoli/migratsiya vositasi orqali)

-- ============ MAHSULOTLAR ============
CREATE TABLE IF NOT EXISTS products (
  id            TEXT PRIMARY KEY,
  cat           TEXT NOT NULL,
  name          TEXT NOT NULL,
  description   TEXT NOT NULL DEFAULT '',
  price         NUMERIC(12,2) NOT NULL DEFAULT 0,
  stock         INTEGER NOT NULL DEFAULT 0,
  location      TEXT NOT NULL DEFAULT '',
  rating        NUMERIC(3,2) NOT NULL DEFAULT 5.0,
  reviews       INTEGER NOT NULL DEFAULT 0,
  sold          INTEGER NOT NULL DEFAULT 0,
  image         TEXT,
  is_custom     BOOLEAN NOT NULL DEFAULT false,
  created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ============ MIJOZLAR ============
CREATE TABLE IF NOT EXISTS customers (
  id            TEXT PRIMARY KEY,
  name          TEXT NOT NULL,
  phone         TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ============ TO'LOV MA'LUMOTI (bitta qator — singleton) ============
CREATE TABLE IF NOT EXISTS payment_info (
  id            INTEGER PRIMARY KEY DEFAULT 1,
  card_number   TEXT NOT NULL,
  card_holder   TEXT NOT NULL,
  bank          TEXT NOT NULL,
  CONSTRAINT single_row CHECK (id = 1)
);

-- ============ BUYURTMALAR ============
CREATE TABLE IF NOT EXISTS orders (
  id               TEXT PRIMARY KEY,
  customer_id      TEXT REFERENCES customers(id) ON DELETE SET NULL,
  customer_name    TEXT NOT NULL,
  customer_phone   TEXT NOT NULL,
  pickup_locations TEXT[] NOT NULL DEFAULT '{}',
  total            NUMERIC(12,2) NOT NULL DEFAULT 0,
  status           TEXT NOT NULL DEFAULT 'tekshirilmoqda',
  receipt          TEXT,
  paid_to_card     TEXT,
  sold_counted     BOOLEAN NOT NULL DEFAULT false,
  created_at       TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS order_items (
  id          SERIAL PRIMARY KEY,
  order_id    TEXT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
  product_id  TEXT NOT NULL,
  name        TEXT NOT NULL,
  price       NUMERIC(12,2) NOT NULL,
  qty         INTEGER NOT NULL,
  location    TEXT
);

CREATE INDEX IF NOT EXISTS idx_orders_customer ON orders(customer_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id);

-- ============ BOSHLANG'ICH MA'LUMOT (seed) ============
INSERT INTO payment_info (id, card_number, card_holder, bank)
VALUES (1, '8600 1234 5678 9012', 'DASTA MCHJ', 'Uzcard / Humo')
ON CONFLICT (id) DO NOTHING;

INSERT INTO products (id, cat, name, description, price, stock, location, rating, reviews, sold, is_custom) VALUES
('p1',  'soat',      'Nordwerk Chrono',        'Charxli po''lat korpus, sapfir shisha, 44mm.',      1890000, 18, 'Toshkent',   4.8, 214, 214, false),
('p2',  'soat',      'Meridian Steel',         'Klassik minimal siferblat, teri kamarli.',           1240000, 24, 'Samarqand',  4.6, 132, 132, false),
('p3',  'kamar',     'Toskana Full-Grain',     'Italyan charmidan, jez tokali klassik kamar.',        320000, 60, 'Toshkent',   4.9, 401, 401, false),
('p4',  'kamar',     'Reversible Onyx',        'Ikki tomonlama — qora va jigarrang, bitta kamar.',    275000, 33, 'Farg''ona',   4.5, 178, 178, false),
('p5',  'hamyon',    'Cardholder Slim',        'Yupqa, 6 karta bo''limi, RFID himoya.',               210000,  5, 'Buxoro',     4.7, 356, 356, false),
('p6',  'hamyon',    'Bifold Heritage',        'An''anaviy bifold, tabiiy teri, qo''l tikuvi.',        280000, 41, 'Toshkent',   4.6, 189, 189, false),
('p7',  'koinok',    'Aviator Matte',          'UV400 himoya, titanium ramka.',                       340000, 22, 'Namangan',   4.7, 267, 267, false),
('p8',  'koinok',    'Wayfarer Classic',       'Polarizatsiyalangan linza, doimiy klassika.',         295000,  3, 'Toshkent',   4.4, 143, 143, false),
('p9',  'aksessuar', 'Silk Tie — Bordo',       '100% tabiiy ipak, qo''lda tikilgan.',                 185000, 15, 'Andijon',    4.8,  98,  98, false),
('p10', 'aksessuar', 'Cufflinks Steel',        'Zanglamaydigan po''lat manjet tugmalari, juft.',      165000, 28, 'Toshkent',   4.5,  76,  76, false),
('p11', 'aksessuar', 'Leather Keychain',       'Qo''lda ishlangan teri kalit ilgichi.',                85000, 70, 'Samarqand',  4.6, 210, 210, false),
('p12', 'soat',      'Field Watch Olive',      'Harbiy uslub, NATO tasma, avtomatik mexanizm.',       1560000,  9, 'Xorazm',     4.9,  87,  87, false)
ON CONFLICT (id) DO NOTHING;
