const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const db = require('../db');

// Регистрация покупателя
router.post('/register', async (req, res) => {
  try {
    const { fullName, email, password } = req.body;
    if (!fullName || !email || !password) {
      return res.status(400).json({ success: false, message: 'Barcha maydonlarni to‘ldiring' });
    }

    const checkUser = await db.query('SELECT id FROM customers WHERE email = $1', [email]);
    if (checkUser.rows.length > 0) {
      return res.status(400).json({ success: false, message: 'Bu email allaqachon ro‘yxatdan o‘tgan' });
    }

    const salt = await bcrypt.genSalt(10);
    const passwordHash = await bcrypt.hash(password, salt);

    const result = await db.query(
      'INSERT INTO customers (full_name, email, password_hash) VALUES ($1, $2, $3) RETURNING id, full_name, email',
      [fullName, email, passwordHash]
    );

    const user = result.rows[0];
    const token = jwt.sign(
      { id: user.id, email: user.email, name: user.full_name },
      process.env.JWT_SECRET || 'vermont_secret',
      { expiresIn: '7d' }
    );

    res.status(201).json({
      success: true,
      message: 'Muvaffaqiyatli ro‘yxatdan o‘tdingiz',
      token,
      user
    });
  } catch (error) {
    console.error('Register xatosi:', error);
    res.status(500).json({ success: false, message: 'Serverda xatolik yuz berdi' });
  }
});

// Авторизация покупателя
router.post('/login', async (req, res) => {
  try {
    const { usernameOrEmail, password } = req.body;
    if (!usernameOrEmail || !password) {
      return res.status(400).json({ success: false, message: 'Login va parolni kiriting' });
    }

    const result = await db.query(
      'SELECT * FROM customers WHERE email = $1 OR full_name = $1',
      [usernameOrEmail]
    );

    if (result.rows.length === 0) {
      return res.status(400).json({ success: false, message: 'Foydalanuvchi topilmadi' });
    }

    const user = result.rows[0];
    const isMatch = await bcrypt.compare(password, user.password_hash);
    if (!isMatch) {
      return res.status(400).json({ success: false, message: 'Noto‘g‘ri parol' });
    }

    const token = jwt.sign(
      { id: user.id, email: user.email, name: user.full_name },
      process.env.JWT_SECRET || 'vermont_secret',
      { expiresIn: '7d' }
    );

    res.json({
      success: true,
      message: 'Tizimga xush kelibsiz',
      token,
      user: { id: user.id, fullName: user.full_name, email: user.email }
    });
  } catch (error) {
    console.error('Login xatosi:', error);
    res.status(500).json({ success: false, message: 'Serverda xatolik yuz berdi' });
  }
});

module.exports = router;