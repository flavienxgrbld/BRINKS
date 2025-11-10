// MySQL connection using mysql2 (promise pool)
const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
  host: process.env.MYSQL_HOST || '127.0.0.1',
  user: process.env.MYSQL_USER || 'root',
  password: process.env.MYSQL_PASSWORD || '',
  database: process.env.MYSQL_DATABASE || 'brinks_db',
  port: process.env.MYSQL_PORT ? parseInt(process.env.MYSQL_PORT) : 3306,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

async function init() {
  // Create tables if they don't exist
  await pool.query(`
    CREATE TABLE IF NOT EXISTS products (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name TEXT NOT NULL,
      price DOUBLE NOT NULL
    ) ENGINE=InnoDB;
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS customers (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name TEXT NOT NULL,
      email TEXT
    ) ENGINE=InnoDB;
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS invoices (
      id INT AUTO_INCREMENT PRIMARY KEY,
      customer_id INT,
      date DATE,
      items TEXT,
      FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;
  `);

  await pool.query(`
    CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(64) UNIQUE NOT NULL,
      password_hash VARCHAR(255) NOT NULL,
      role VARCHAR(32) DEFAULT 'user'
    ) ENGINE=InnoDB;
  `);

  // Seed minimal data if empty
  const [pRows] = await pool.query('SELECT COUNT(*) as c FROM products');
  if (pRows[0].c === 0) {
    await pool.query('INSERT INTO products (name, price) VALUES (?, ?), (?, ?)', ['Exemple Produit A', 19.9, 'Exemple Produit B', 49.5]);
  }

  const [cRows] = await pool.query('SELECT COUNT(*) as c FROM customers');
  if (cRows[0].c === 0) {
    await pool.query('INSERT INTO customers (name, email) VALUES (?, ?)', ['Client Demo', 'client@demo.test']);
  }

  // Seed admin user if none
  const [uRows] = await pool.query('SELECT COUNT(*) as c FROM users');
  if (uRows[0].c === 0) {
    // mot de passe par défaut : admin123 (hashé)
    const bcrypt = require('bcryptjs');
    const hash = await bcrypt.hash('admin123', 10);
    await pool.query('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)', ['admin', hash, 'admin']);
  }
}

module.exports = { pool, init };
