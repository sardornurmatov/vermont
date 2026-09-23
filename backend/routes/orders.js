const express = require('express');
const { pool } = require('../db');
const { requireAdmin, attachCustomerIfPresent } = require('../middleware/auth');

const router = express.Router();
const ALLOWED_STATUSES = new Set(['tekshirilmoqda', 'tasdiqlandi', 'topshirildi', 'bekor qilindi']);

function toOrder(row, items) {
  return {
    id: row.id,
    createdAt: new Date(row.created_at).getTime(),
    customerId: row.customer_id,
    customer: { name: row.customer_name, phone: row.customer_phone },
    pickupLocations: row.pickup_locations || [],
    total: Number(row.total),
    status: row.status,
    receipt: row.receipt,
    paidToCard: row.paid_to_card,
    items: items.map((item) => ({
      id: item.product_id,
      name: item.name,
      price: Number(item.price),
      qty: item.qty,
      location: item.location,
    })),
  };
}

async function loadItemsFor(orderIds) {
  if (!orderIds.length) return {};
  const { rows } = await pool.query('SELECT * FROM order_items WHERE order_id = ANY($1)', [orderIds]);
  return rows.reduce((map, row) => {
    (map[row.order_id] ||= []).push(row);
    return map;
  }, {});
}

router.get('/', requireAdmin, async (_req, res) => {
  try {
    const { rows } = await pool.query('SELECT * FROM orders ORDER BY created_at DESC');
    const itemsMap = await loadItemsFor(rows.map((row) => row.id));
    res.json(rows.map((row) => toOrder(row, itemsMap[row.id] || [])));
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Buyurtmalarni olishda xatolik' });
  }
});

router.get('/mine', attachCustomerIfPresent, async (req, res) => {
  if (!req.customerId) return res.status(401).json({ error: 'Hisobga kiring' });
  try {
    const { rows } = await pool.query(
      'SELECT * FROM orders WHERE customer_id = $1 ORDER BY created_at DESC',
      [req.customerId]
    );
    const itemsMap = await loadItemsFor(rows.map((row) => row.id));
    res.json(rows.map((row) => toOrder(row, itemsMap[row.id] || [])));
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Buyurtmalarni olishda xatolik' });
  }
});

router.post('/', attachCustomerIfPresent, async (req, res) => {
  let client;
  try {
    const customer = req.body.customer || {};
    const requestedItems = Array.isArray(req.body.items) ? req.body.items : [];
    const name = String(customer.name || '').trim();
    const phone = String(customer.phone || '').trim();
    if (!name || !phone || !requestedItems.length) {
      return res.status(400).json({ error: 'Mijoz ma’lumoti va mahsulotlar talab qilinadi' });
    }

    const quantities = new Map();
    for (const item of requestedItems) {
      const id = String(item.id || '');
      const qty = Number(item.qty);
      if (!id || !Number.isInteger(qty) || qty < 1 || qty > 100) {
        return res.status(400).json({ error: 'Mahsulot miqdori noto‘g‘ri' });
      }
      quantities.set(id, (quantities.get(id) || 0) + qty);
    }

    client = await pool.connect();
    await client.query('BEGIN');
    const productIds = [...quantities.keys()];
    const { rows: products } = await client.query(
      'SELECT id, name, price, stock, location FROM products WHERE id = ANY($1) FOR UPDATE',
      [productIds]
    );
    if (products.length !== productIds.length) throw Object.assign(new Error('Mahsulot topilmadi'), { status: 400 });

    const items = products.map((product) => {
      const qty = quantities.get(product.id);
      if (product.stock < qty) throw Object.assign(new Error(`${product.name} omborda yetarli emas`), { status: 409 });
      return { id: product.id, name: product.name, price: Number(product.price), qty, location: product.location };
    });
    const total = items.reduce((sum, item) => sum + item.price * item.qty, 0);
    const id = `ORD-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;

    await client.query(
      `INSERT INTO orders (id, customer_id, customer_name, customer_phone, pickup_locations, total, status, receipt, paid_to_card)
       VALUES ($1,$2,$3,$4,$5,$6,'tekshirilmoqda',$7,$8)`,
      [id, req.customerId || null, name, phone, req.body.pickupLocations || [], total, req.body.receipt || null, req.body.paidToCard || null]
    );
    for (const item of items) {
      await client.query(
        `INSERT INTO order_items (order_id, product_id, name, price, qty, location)
         VALUES ($1,$2,$3,$4,$5,$6)`,
        [id, item.id, item.name, item.price, item.qty, item.location]
      );
    }
    await client.query('COMMIT');
    res.status(201).json({ ok: true, id, total });
  } catch (err) {
    if (client) await client.query('ROLLBACK').catch(() => {});
    console.error(err);
    res.status(err.status || 500).json({ error: err.status ? err.message : 'Buyurtma yaratishda xatolik' });
  } finally {
    client?.release();
  }
});

router.patch('/:id/status', requireAdmin, async (req, res) => {
  const status = String(req.body.status || '');
  if (!ALLOWED_STATUSES.has(status)) return res.status(400).json({ error: 'Buyurtma holati noto‘g‘ri' });

  let client;
  try {
    client = await pool.connect();
    await client.query('BEGIN');
    const { rows } = await client.query(
      'UPDATE orders SET status = $1 WHERE id = $2 RETURNING *',
      [status, req.params.id]
    );
    if (!rows.length) throw Object.assign(new Error('Buyurtma topilmadi'), { status: 404 });

    const order = rows[0];
    if (status === 'tasdiqlandi' && !order.sold_counted) {
      const { rows: items } = await client.query(
        'SELECT product_id, qty FROM order_items WHERE order_id = $1',
        [order.id]
      );
      for (const item of items) {
        const result = await client.query(
          `UPDATE products SET sold = sold + $1, stock = stock - $1
           WHERE id = $2 AND stock >= $1`,
          [item.qty, item.product_id]
        );
        if (!result.rowCount) throw Object.assign(new Error('Omborda mahsulot yetarli emas'), { status: 409 });
      }
      await client.query('UPDATE orders SET sold_counted = true WHERE id = $1', [order.id]);
    }

    await client.query('COMMIT');
    res.json({ ok: true });
  } catch (err) {
    if (client) await client.query('ROLLBACK').catch(() => {});
    console.error(err);
    res.status(err.status || 500).json({ error: err.status ? err.message : 'Holatni yangilashda xatolik' });
  } finally {
    client?.release();
  }
});

module.exports = router;
