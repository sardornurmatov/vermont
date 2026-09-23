const jwt = require('jsonwebtoken');

function getToken(req) {
  const header = req.headers.authorization || '';
  if (!header.startsWith('Bearer ')) return null;
  return header.slice(7);
}

function verifyToken(token) {
  if (!token || !process.env.JWT_SECRET) return null;
  try {
    return jwt.verify(token, process.env.JWT_SECRET);
  } catch {
    return null;
  }
}

function requireAdmin(req, res, next) {
  const payload = verifyToken(getToken(req));
  if (!payload || payload.role !== 'admin') {
    return res.status(401).json({ error: 'Admin autentifikatsiyasi talab qilinadi' });
  }
  req.auth = payload;
  next();
}

function attachCustomerIfPresent(req, _res, next) {
  const payload = verifyToken(getToken(req));
  if (payload && payload.role === 'customer' && payload.sub) {
    req.customerId = payload.sub;
    req.auth = payload;
  }
  next();
}

module.exports = { requireAdmin, attachCustomerIfPresent, verifyToken };
