const express = require('express');
const jwt = require('jsonwebtoken');

const router = express.Router();

router.post('/admin/login', (req, res) => {
  const password = String(req.body.password || '');
  if (!process.env.ADMIN_PASSWORD || !process.env.JWT_SECRET) {
    return res.status(500).json({ error: 'Admin autentifikatsiyasi serverda sozlanmagan' });
  }
  if (password !== process.env.ADMIN_PASSWORD) {
    return res.status(401).json({ error: 'Parol noto‘g‘ri' });
  }

  res.json({
    token: jwt.sign({ role: 'admin' }, process.env.JWT_SECRET, { expiresIn: '12h' }),
  });
});

module.exports = router;
