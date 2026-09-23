// DASTA backend — to'lov karta ma'lumotlari API
const express = require("express");
const { pool } = require("../db");
const { requireAdmin } = require("../middleware/auth");

const router = express.Router();

router.get("/", async (req, res) => {
  try {
    const { rows } = await pool.query("SELECT * FROM payment_info WHERE id = 1");
    if (rows.length === 0) return res.status(404).json({ error: "To'lov ma'lumoti topilmadi" });
    res.json({
      cardNumber: rows[0].card_number,
      cardHolder: rows[0].card_holder,
      bank: rows[0].bank,
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "To'lov ma'lumotini olishda xatolik" });
  }
});

router.put("/", requireAdmin, async (req, res) => {
  try {
    const { cardNumber, cardHolder, bank } = req.body;
    await pool.query(
      `UPDATE payment_info SET card_number = $1, card_holder = $2, bank = $3 WHERE id = 1`,
      [cardNumber, cardHolder, bank]
    );
    res.json({ ok: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "To'lov ma'lumotini saqlashda xatolik" });
  }
});

module.exports = router;
