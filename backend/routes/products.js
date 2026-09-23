// DASTA backend — mahsulotlar API
const express = require("express");
const { pool } = require("../db");
const { requireAdmin } = require("../middleware/auth");

const router = express.Router();

function toProduct(row) {
  return {
    id: row.id,
    cat: row.cat,
    name: row.name,
    desc: row.description,
    price: Number(row.price),
    stock: row.stock,
    location: row.location,
    rating: Number(row.rating),
    reviews: row.reviews,
    sold: row.sold,
    image: row.image,
    isCustom: row.is_custom,
  };
}

router.get("/", async (req, res) => {
  try {
    const { rows } = await pool.query("SELECT * FROM products ORDER BY created_at ASC");
    res.json(rows.map(toProduct));
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Mahsulotlarni olishda xatolik" });
  }
});

router.post("/", requireAdmin, async (req, res) => {
  try {
    const { id, cat, name, desc, price, stock, location, image, rating, reviews, sold } = req.body;
    const { rows } = await pool.query(
      `INSERT INTO products (id, cat, name, description, price, stock, location, image, rating, reviews, sold, is_custom)
       VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,true) RETURNING *`,
      [id, cat, name, desc, price, stock, location, image || null, rating ?? 5.0, reviews ?? 0, sold ?? 0]
    );
    res.status(201).json(toProduct(rows[0]));
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Mahsulot qo'shishda xatolik" });
  }
});

router.patch("/:id", requireAdmin, async (req, res) => {
  try {
    const allowed = ["price", "stock", "name", "desc", "cat", "location", "image", "sold"];
    const fields = [];
    const values = [];
    let i = 1;

    for (const key of allowed) {
      if (req.body[key] !== undefined) {
        const column = key === "desc" ? "description" : key;
        fields.push(`${column} = $${i++}`);
        values.push(req.body[key]);
      }
    }
    if (fields.length === 0) return res.status(400).json({ error: "Yangilanadigan maydon yo'q" });

    values.push(req.params.id);
    const { rows } = await pool.query(
      `UPDATE products SET ${fields.join(", ")} WHERE id = $${i} RETURNING *`,
      values
    );
    if (rows.length === 0) return res.status(404).json({ error: "Mahsulot topilmadi" });
    res.json(toProduct(rows[0]));
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Mahsulotni yangilashda xatolik" });
  }
});

router.delete("/:id", requireAdmin, async (req, res) => {
  try {
    const { rows } = await pool.query(
      "DELETE FROM products WHERE id = $1 AND is_custom = true RETURNING id",
      [req.params.id]
    );
    if (rows.length === 0) return res.status(400).json({ error: "Bu mahsulotni o'chirib bo'lmaydi" });
    res.json({ ok: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Mahsulotni o'chirishda xatolik" });
  }
});

module.exports = router;
