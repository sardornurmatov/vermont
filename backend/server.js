const express = require('express');
const cors = require('cors');
require('dotenv').config();

const app = express();
const PORT = Number(process.env.PORT || 4000);
const allowedOrigin = process.env.FRONTEND_ORIGIN;

app.use(allowedOrigin ? cors({ origin: allowedOrigin }) : cors());
app.use(express.json({ limit: '3mb' }));

app.use('/api/auth', require('./routes/adminAuth'));
app.use('/api/customers', require('./routes/customers'));
app.use('/api/products', require('./routes/products'));
app.use('/api/orders', require('./routes/orders'));
app.use('/api/stats', require('./routes/stats'));
app.use('/api/payment-info', require('./routes/paymentInfo'));

app.get('/', (_req, res) => {
  res.json({ ok: true, name: 'Vermont Market API' });
});

app.use((err, _req, res, _next) => {
  console.error('Unhandled server error:', err);
  res.status(500).json({ error: 'Serverda kutilmagan xatolik yuz berdi' });
});

app.listen(PORT, () => {
  console.log(`Vermont API Server http://localhost:${PORT} portida ishga tushdi`);
});
