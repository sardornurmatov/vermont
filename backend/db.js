const { Pool } = require('pg');
require('dotenv').config();

const sslEnabled = String(process.env.DATABASE_SSL).toLowerCase() === 'true';
const connectionString = process.env.DATABASE_URL;

const pool = new Pool(connectionString
  ? { connectionString, ssl: sslEnabled ? { rejectUnauthorized: false } : false }
  : {
      host: process.env.DB_HOST || 'localhost',
      port: Number(process.env.DB_PORT || 5432),
      user: process.env.DB_USER || 'postgres',
      password: process.env.DB_PASSWORD,
      database: process.env.DB_NAME || 'vermont_db',
      ssl: sslEnabled ? { rejectUnauthorized: false } : false,
    });

pool.on('error', (err) => {
  console.error('PostgreSQL connection error:', err.message);
});

module.exports = {
  query: (text, params) => pool.query(text, params),
  pool,
};
