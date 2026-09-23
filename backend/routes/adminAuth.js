// DASTA backend — admin login API
const express = require("express");
const jwt = require("jsonwebtoken");

const router = express.Router();

router.post("/admin/login", (req, res) => {
  const { password } = req.body;
  if (password !== process.env.ADMIN_PASSWORD) {
    return res.status(401).json({ error: "Parol noto'g'ri" });
  }
  const token = jwt.sign({ role: "admin" }, process.env.JWT_SECRET, { expiresIn: "12h" });
  res.json({ token });
});

module.exports = router;
