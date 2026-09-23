const express = require('express');
const cors = require('cors');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 5000;

app.use(cors());
app.use(express.json());

// Marshrutlar (Routes)
app.use('/api/customers', require('./routes/customers'));
app.use('/api/products', require('./routes/products'));
app.use('/api/orders', require('./routes/orders'));
app.use('/api/stats', require('./routes/stats'));
app.use('/api/admin', require('./routes/adminAuth'));
app.use('/api/payment', require('./routes/paymentInfo'));

app.get('/', (req, res) => {
  res.send('Vermont Market API Server 2026 ishlab turibdi');
});

app.listen(PORT, () => {
  console.log(`Vermont API Server http://localhost:${PORT} portida ishga tushdi`);
});