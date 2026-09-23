// DASTA backend — admin statistika API
const express = require("express");
const { pool } = require("../db");
const { requireAdmin } = require("../middleware/auth");

const router = express.Router();

router.get("/", requireAdmin, async (req, res) => {
  try {
    const revenueQ = await pool.query(
      `SELECT COALESCE(SUM(total), 0) AS revenue FROM orders WHERE status IN ('tasdiqlandi', 'topshirildi')`
    );
    const ordersCountQ = await pool.query(`SELECT COUNT(*) AS count FROM orders`);
    const pendingQ = await pool.query(`SELECT COUNT(*) AS count FROM orders WHERE status = 'tekshirilmoqda'`);
    const soldQ = await pool.query(`SELECT COALESCE(SUM(sold), 0) AS total FROM products`);
    const topProductQ = await pool.query(`SELECT id, name, sold FROM products ORDER BY sold DESC LIMIT 1`);
    const lowStockQ = await pool.query(`SELECT id, name, stock FROM products WHERE stock <= 5 ORDER BY stock ASC`);

    res.json({
      revenue: Number(revenueQ.rows[0].revenue),
      totalOrders: Number(ordersCountQ.rows[0].count),
      pending: Number(pendingQ.rows[0].count),
      totalSold: Number(soldQ.rows[0].total),
      topProduct: topProductQ.rows[0] || null,
      lowStock: lowStockQ.rows,
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Statistikani olishda xatolik" });
  }
});

module.exports = router;
