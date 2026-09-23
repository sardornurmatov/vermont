// DASTA backend — buyurtmalar API
const express = require("express");
const { pool } = require("../db");
const { requireAdmin, attachCustomerIfPresent } = require("../middleware/auth");

const router = express.Router();

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
    items: items.map(i => ({
      id: i.product_id, name: i.name, price: Number(i.price), qty: i.qty, location: i.location,
    })),
  };
}

async function loadItemsFor(orderIds) {
  if (orderIds.length === 0) return {};
  const { rows } = await pool.query(
    "SELECT * FROM order_items WHERE order_id = ANY($1)",
    [orderIds]
  );
  const map = {};
  rows.forEach(r => {
    if (!map[r.order_id]) map[r.order_id] = [];
    map[r.order_id].push(r);
  });
  return map;
}

router.get("/", requireAdmin, async (req, res) => {
  try {
    const { rows } = await pool.query("SELECT * FROM orders ORDER BY created_at DESC");
    const itemsMap = await loadItemsFor(rows.map(r => r.id));
    res.json(rows.map(r => toOrder(r, itemsMap[r.id] || [])));
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Buyurtmalarni olishda xatolik" });
  }
});

router.get("/mine", attachCustomerIfPresent, async (req, res) => {
  if (!req.customerId) return res.status(401).json({ error: "Hisobga kiring" });
  try {
    const { rows } = await pool.query(
      "SELECT * FROM orders WHERE customer_id = $1 ORDER BY created_at DESC",
      [req.customerId]
    );
    const itemsMap = await loadItemsFor(rows.map(r => r.id));
    res.json(rows.map(r => toOrder(r, itemsMap[r.id] || [])));
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Buyurtmalarni olishda xatolik" });
  }
});

router.post("/", attachCustomerIfPresent, async (req, res) => {
  const client = await pool.connect();
  try {
    const { id, customer, pickupLocations, items, total, receipt, paidToCard } = req.body;
    if (!items || items.length === 0) return res.status(400).json({ error: "Savat bo'sh" });

    await client.query("BEGIN");

    await client.query(
      `INSERT INTO orders (id, customer_id, customer_name, customer_phone, pickup_locations, total, status, receipt, paid_to_card)
       VALUES ($1,$2,$3,$4,$5,$6,'tekshirilmoqda',$7,$8)`,
      [id, req.customerId || null, customer.name, customer.phone, pickupLocations || [], total, receipt || null, paidToCard || null]
    );

    for (const item of items) {
      await client.query(
        `INSERT INTO order_items (order_id, product_id, name, price, qty, location)
         VALUES ($1,$2,$3,$4,$5,$6)`,
        [id, item.id, item.name, item.price, item.qty, item.location || null]
      );
    }

    await client.query("COMMIT");
    res.status(201).json({ ok: true, id });
  } catch (err) {
    await client.query("ROLLBACK");
    console.error(err);
    res.status(500).json({ error: "Buyurtma yaratishda xatolik" });
  } finally {
    client.release();
  }
});

router.patch("/:id/status", requireAdmin, async (req, res) => {
  const client = await pool.connect();
  try {
    const { status } = req.body;
    await client.query("BEGIN");

    const { rows } = await client.query(
      "UPDATE orders SET status = $1 WHERE id = $2 RETURNING *",
      [status, req.params.id]
    );
    if (rows.length === 0) {
      await client.query("ROLLBACK");
      return res.status(404).json({ error: "Buyurtma topilmadi" });
    }

    const order = rows[0];

    if (status === "tasdiqlandi" && !order.sold_counted) {
      const { rows: items } = await client.query(
        "SELECT product_id, qty FROM order_items WHERE order_id = $1",
        [order.id]
      );
      for (const item of items) {
        await client.query(
          "UPDATE products SET sold = sold + $1, stock = GREATEST(stock - $1, 0) WHERE id = $2",
          [item.qty, item.product_id]
        );
      }
      await client.query("UPDATE orders SET sold_counted = true WHERE id = $1", [order.id]);
    }

    await client.query("COMMIT");
    res.json({ ok: true });
  } catch (err) {
    await client.query("ROLLBACK");
    console.error(err);
    res.status(500).json({ error: "Holatni yangilashda xatolik" });
  } finally {
    client.release();
  }
});

module.exports = router;
