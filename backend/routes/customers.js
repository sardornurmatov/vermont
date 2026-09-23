const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const db = require('../db');

const router = express.Router();

function signCustomer(customer) {
  return jwt.sign(
    { sub: customer.id, role: 'customer', email: customer.email, name: customer.name },
    process.env.JWT_SECRET,
    { expiresIn: '7d' }
  );
}

function publicCustomer(row) {
  return { id: row.id, name: row.name, email: row.email, phone: row.phone || null };
}

router.post('/register', async (req, res) => {
  try {
    const name = String(req.body.name || '').trim();
    const email = String(req.body.email || '').trim().toLowerCase();
    const password = String(req.body.password || '');

    if (name.length < 2 || !email || password.length < 6) {
      return res.status(400).json({ error: 'Ism, email va kamida 6 belgili parol kiriting' });
    }
    if (!process.env.JWT_SECRET) {
      return res.status(500).json({ error: 'JWT_SECRET serverda sozlanmagan' });
    }

    const existing = await db.query('SELECT id FROM customers WHERE email = $1', [email]);
    if (existing.rows.length) {
      return res.status(409).json({ error: 'Bu email allaqachon ro‘yxatdan o‘tgan' });
    }

    const passwordHash = await bcrypt.hash(password, 12);
    const id = `customer-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
    const result = await db.query(
      `INSERT INTO customers (id, name, email, password_hash)
       VALUES ($1, $2, $3, $4)
       RETURNING id, name, email, phone`,
      [id, name, email, passwordHash]
    );

    const customer = publicCustomer(result.rows[0]);
    res.status(201).json({ token: signCustomer(customer), customer });
  } catch (err) {
    console.error('Register error:', err.message);
    res.status(500).json({ error: 'Ro‘yxatdan o‘tishda server xatosi' });
  }
});

router.post('/login', async (req, res) => {
  try {
    const email = String(req.body.email || '').trim().toLowerCase();
    const password = String(req.body.password || '');
    if (!email || !password) {
      return res.status(400).json({ error: 'Email va parolni kiriting' });
    }

    const result = await db.query('SELECT * FROM customers WHERE email = $1', [email]);
    const row = result.rows[0];
    if (!row || !(await bcrypt.compare(password, row.password_hash))) {
      return res.status(401).json({ error: 'Email yoki parol noto‘g‘ri' });
    }

    const customer = publicCustomer(row);
    res.json({ token: signCustomer(customer), customer });
  } catch (err) {
    console.error('Login error:', err.message);
    res.status(500).json({ error: 'Kirishda server xatosi' });
  }
});

module.exports = router;
