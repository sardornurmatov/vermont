// DASTA backend — schema.sql faylini bazaga qo'llash uchun yordamchi skript
// Ishlatish: npm run db:migrate  (yoki: node migrate.js)
require("dotenv").config();
const fs = require("fs");
const path = require("path");
const { pool } = require("./db");

async function migrate() {
  const sql = fs.readFileSync(path.join(__dirname, "schema.sql"), "utf-8");
  console.log("Sxema qo'llanmoqda...");
  await pool.query(sql);
  console.log("Tayyor! Jadvallar va boshlang'ich mahsulotlar yaratildi.");
  await pool.end();
}

migrate().catch((err) => {
  console.error("Migratsiya xatosi:", err.message);
  process.exit(1);
});
