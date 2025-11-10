const path = require('path');
const express = require('express');
const cors = require('cors');
require('dotenv').config();
const db = require('./db');
const jwt = require('jsonwebtoken');
const cookieParser = require('cookie-parser');
const bcrypt = require('bcryptjs');

const app = express();
const PORT = process.env.PORT || 3000;
const JWT_SECRET = process.env.JWT_SECRET || 'devsecret';

app.use(cors({ origin: true, credentials: true }));
app.use(express.json());
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));

// Auth middleware
function authRequired(req, res, next) {
  const token = req.cookies.token || req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).json({ error: 'Non authentifié' });
  try {
    req.user = jwt.verify(token, JWT_SECRET);
    next();
  } catch (e) {
    res.status(401).json({ error: 'Token invalide' });
  }
}

// Login endpoint
app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;
  if (!username || !password) return res.status(400).json({ error: 'Champs requis' });
  try {
    const [rows] = await db.pool.query('SELECT * FROM users WHERE username = ?', [username]);
    const user = rows[0];
    if (!user) return res.status(401).json({ error: 'Utilisateur inconnu' });
    const ok = await bcrypt.compare(password, user.password_hash);
    if (!ok) return res.status(401).json({ error: 'Mot de passe incorrect' });
    const token = jwt.sign({ id: user.id, username: user.username, role: user.role }, JWT_SECRET, { expiresIn: '2h' });
    res.cookie('token', token, { httpOnly: true, sameSite: 'lax' });
    res.json({ success: true, user: { id: user.id, username: user.username, role: user.role } });
  } catch (e) {
    res.status(500).json({ error: e.message });
  }
});

// Logout endpoint
app.post('/api/logout', (req, res) => {
  res.clearCookie('token');
  res.json({ success: true });
});

// Current user endpoint
app.get('/api/me', authRequired, (req, res) => {
  res.json({ user: req.user });
});

// Products
app.get('/api/products', authRequired, async (req, res) => {
  try {
    const [products] = await db.pool.query('SELECT * FROM products');
    res.json(products);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.post('/api/products', authRequired, async (req, res) => {
  try {
    const { name, price } = req.body;
    const [result] = await db.pool.query('INSERT INTO products (name, price) VALUES (?, ?)', [name, price]);
    const [rows] = await db.pool.query('SELECT * FROM products WHERE id = ?', [result.insertId]);
    res.status(201).json(rows[0]);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.put('/api/products/:id', authRequired, async (req, res) => {
  try {
    const id = req.params.id;
    const { name, price } = req.body;
    await db.pool.query('UPDATE products SET name = ?, price = ? WHERE id = ?', [name, price, id]);
    const [rows] = await db.pool.query('SELECT * FROM products WHERE id = ?', [id]);
    res.json(rows[0]);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.delete('/api/products/:id', authRequired, async (req, res) => {
  try {
    const id = req.params.id;
    await db.pool.query('DELETE FROM products WHERE id = ?', [id]);
    res.status(204).end();
  } catch (err) { res.status(500).json({ error: err.message }); }
});

// Customers
app.get('/api/customers', authRequired, async (req, res) => {
  try {
    const [customers] = await db.pool.query('SELECT * FROM customers');
    res.json(customers);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.post('/api/customers', authRequired, async (req, res) => {
  try {
    const { name, email } = req.body;
    const [result] = await db.pool.query('INSERT INTO customers (name, email) VALUES (?, ?)', [name, email]);
    const [rows] = await db.pool.query('SELECT * FROM customers WHERE id = ?', [result.insertId]);
    res.status(201).json(rows[0]);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.put('/api/customers/:id', authRequired, async (req, res) => {
  try {
    const id = req.params.id;
    const { name, email } = req.body;
    await db.pool.query('UPDATE customers SET name = ?, email = ? WHERE id = ?', [name, email, id]);
    const [rows] = await db.pool.query('SELECT * FROM customers WHERE id = ?', [id]);
    res.json(rows[0]);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.delete('/api/customers/:id', authRequired, async (req, res) => {
  try {
    const id = req.params.id;
    await db.pool.query('DELETE FROM customers WHERE id = ?', [id]);
    res.status(204).end();
  } catch (err) { res.status(500).json({ error: err.message }); }
});

// Invoices
app.get('/api/invoices', authRequired, async (req, res) => {
  try {
    const [invoices] = await db.pool.query('SELECT * FROM invoices');
    invoices.forEach(i => { try { i.items = JSON.parse(i.items); } catch(e){ i.items = []; } });
    res.json(invoices);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.post('/api/invoices', authRequired, async (req, res) => {
  try {
    const { customer_id, date, items } = req.body;
    const itemsJson = JSON.stringify(items || []);
    const [result] = await db.pool.query('INSERT INTO invoices (customer_id, date, items) VALUES (?, ?, ?)', [customer_id, date, itemsJson]);
    const [rows] = await db.pool.query('SELECT * FROM invoices WHERE id = ?', [result.insertId]);
    const invoice = rows[0];
    try { invoice.items = JSON.parse(invoice.items); } catch(e){ invoice.items = []; }
    res.status(201).json(invoice);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.put('/api/invoices/:id', authRequired, async (req, res) => {
  try {
    const id = req.params.id;
    const { customer_id, date, items } = req.body;
    const itemsJson = JSON.stringify(items || []);
    await db.pool.query('UPDATE invoices SET customer_id = ?, date = ?, items = ? WHERE id = ?', [customer_id, date, itemsJson, id]);
    const [rows] = await db.pool.query('SELECT * FROM invoices WHERE id = ?', [id]);
    const invoice = rows[0];
    try { invoice.items = JSON.parse(invoice.items); } catch(e){ invoice.items = []; }
    res.json(invoice);
  } catch (err) { res.status(500).json({ error: err.message }); }
});

app.delete('/api/invoices/:id', authRequired, async (req, res) => {
  try {
    const id = req.params.id;
    await db.pool.query('DELETE FROM invoices WHERE id = ?', [id]);
    res.status(204).end();
  } catch (err) { res.status(500).json({ error: err.message }); }
});

// Fallback to index.html for SPA
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

(async () => {
  try {
    await db.init();
    app.listen(PORT, () => {
      console.log(`Server started on http://localhost:${PORT}`);
    });
  } catch (err) {
    console.error('Failed to initialize database:', err);
    process.exit(1);
  }
})();
