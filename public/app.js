// Simple SPA router + auth
const content = document.getElementById('content');
let currentUser = null;

async function fetchMe() {
  try {
    const res = await fetch('/api/me');
    if (!res.ok) throw new Error('Non connecté');
    const data = await res.json();
    currentUser = data.user;
  } catch (e) {
    currentUser = null;
  }
}

function navTo(hash) {
  if (!hash) hash = '#products';
  if (!currentUser && hash !== '#login') {
    location.hash = '#login';
    return;
  }
  if (hash === '#products') renderProducts();
  else if (hash === '#customers') renderCustomers();
  else if (hash === '#invoices') renderInvoices();
  else if (hash === '#login') renderLogin();
}

window.addEventListener('hashchange', () => navTo(location.hash));
window.addEventListener('load', async () => {
  await fetchMe();
  navTo(location.hash);
});

function renderUserBar() {
  const bar = document.getElementById('userbar');
  if (!bar) return;
  if (currentUser) {
    bar.innerHTML = `<span>Connecté : <b>${currentUser.username}</b> (${currentUser.role}) <button id="logoutBtn" class="btn btn-sm btn-outline-secondary ms-2">Déconnexion</button></span>`;
    document.getElementById('logoutBtn').onclick = async () => {
      await fetch('/api/logout', { method: 'POST' });
      currentUser = null;
      location.hash = '#login';
    };
  } else {
    bar.innerHTML = '';
  }
}

// Login page
function renderLogin() {
  content.innerHTML = `
    <h2>Connexion</h2>
    <form id="loginForm" class="mb-3" style="max-width:350px">
      <div class="mb-2"><input name="username" required class="form-control" placeholder="Nom d'utilisateur"></div>
      <div class="mb-2"><input name="password" required type="password" class="form-control" placeholder="Mot de passe"></div>
      <button class="btn btn-primary w-100">Se connecter</button>
    </form>
    <div id="loginError" class="text-danger"></div>
  `;
  document.getElementById('loginForm').onsubmit = async (e) => {
    e.preventDefault();
    const form = e.target;
    const username = form.username.value.trim();
    const password = form.password.value;
    const res = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });
    if (res.ok) {
      await fetchMe();
      renderUserBar();
      location.hash = '#products';
    } else {
      const err = await res.json();
      document.getElementById('loginError').textContent = err.error || 'Erreur de connexion';
    }
  };
  renderUserBar();
}

// Products
async function renderProducts(){
  renderUserBar();
  const res = await fetch('/api/products');
  if (res.status === 401) { location.hash = '#login'; return; }
  const products = await res.json();
  content.innerHTML = `
    <h2>Produits</h2>
    <form id="productForm" class="row g-2 mb-3">
      <div class="col-auto"><input required name="name" placeholder="Nom" class="form-control"></div>
      <div class="col-auto"><input required name="price" type="number" step="0.01" placeholder="Prix" class="form-control"></div>
      <div class="col-auto"><button class="btn btn-primary">Ajouter</button></div>
    </form>
    <table class="table table-sm">
      <thead><tr><th>#</th><th>Nom</th><th>Prix</th><th></th></tr></thead>
      <tbody>
        ${products.map(p=>`<tr><td>${p.id}</td><td>${p.name}</td><td>${p.price.toFixed(2)}</td><td><button data-id="${p.id}" class="btn btn-sm btn-danger del">Suppr</button></td></tr>`).join('')}
      </tbody>
    </table>
  `;

  document.getElementById('productForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const form = e.target;
    const name = form.name.value.trim();
    const price = parseFloat(form.price.value);
    await fetch('/api/products', { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name, price}) });
    navTo('#products');
  });

  document.querySelectorAll('.del').forEach(b=> b.addEventListener('click', async (e)=>{
    const id = e.target.dataset.id;
    if (!confirm('Supprimer ce produit ?')) return;
    await fetch('/api/products/'+id, { method: 'DELETE' });
    navTo('#products');
  }));
}

// Customers
async function renderCustomers(){
  renderUserBar();
  const res = await fetch('/api/customers');
  if (res.status === 401) { location.hash = '#login'; return; }
  const customers = await res.json();
  content.innerHTML = `
    <h2>Clients</h2>
    <form id="customerForm" class="row g-2 mb-3">
      <div class="col-auto"><input required name="name" placeholder="Nom" class="form-control"></div>
      <div class="col-auto"><input name="email" type="email" placeholder="Email" class="form-control"></div>
      <div class="col-auto"><button class="btn btn-primary">Ajouter</button></div>
    </form>
    <table class="table table-sm">
      <thead><tr><th>#</th><th>Nom</th><th>Email</th><th></th></tr></thead>
      <tbody>
        ${customers.map(c=>`<tr><td>${c.id}</td><td>${c.name}</td><td>${c.email||''}</td><td><button data-id="${c.id}" class="btn btn-sm btn-danger del">Suppr</button></td></tr>`).join('')}
      </tbody>
    </table>
  `;

  document.getElementById('customerForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const form = e.target;
    const name = form.name.value.trim();
    const email = form.email.value.trim();
    await fetch('/api/customers', { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name, email}) });
    navTo('#customers');
  });

  document.querySelectorAll('.del').forEach(b=> b.addEventListener('click', async (e)=>{
    const id = e.target.dataset.id;
    if (!confirm('Supprimer ce client ?')) return;
    await fetch('/api/customers/'+id, { method: 'DELETE' });
    navTo('#customers');
  }));
}

// Invoices (simple)
async function renderInvoices(){
  renderUserBar();
  const res = await fetch('/api/invoices');
  if (res.status === 401) { location.hash = '#login'; return; }
  const invoices = await res.json();
  // fetch customers and products for form
  const [custRes, prodRes] = await Promise.all([fetch('/api/customers'), fetch('/api/products')]);
  const customers = await custRes.json();
  const products = await prodRes.json();

  content.innerHTML = `
    <h2>Factures</h2>
    <form id="invoiceForm" class="mb-3">
      <div class="row g-2">
        <div class="col-md-4">
          <select class="form-select" name="customer_id" required>
            <option value="">Choisir un client</option>
            ${customers.map(c=>`<option value="${c.id}">${c.name}</option>`).join('')}
          </select>
        </div>
        <div class="col-md-3"><input name="date" type="date" class="form-control" required></div>
        <div class="col-md-2"><button class="btn btn-primary">Créer</button></div>
      </div>
    </form>

    <table class="table table-sm">
      <thead><tr><th>#</th><th>Client</th><th>Date</th><th>Lignes</th><th></th></tr></thead>
      <tbody>
        ${invoices.map(i=>`<tr><td>${i.id}</td><td>${(customers.find(c=>c.id===i.customer_id)||{}).name||i.customer_id}</td><td>${i.date}</td><td>${(i.items||[]).length}</td><td><button data-id="${i.id}" class="btn btn-sm btn-danger del">Suppr</button></td></tr>`).join('')}
      </tbody>
    </table>
  `;

  document.getElementById('invoiceForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const form = e.target;
    const customer_id = parseInt(form.customer_id.value);
    const date = form.date.value;
    // simple invoice without items
    await fetch('/api/invoices', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ customer_id, date, items: [] }) });
    navTo('#invoices');
  });

  document.querySelectorAll('.del').forEach(b=> b.addEventListener('click', async (e)=>{
    const id = e.target.dataset.id;
    if (!confirm('Supprimer cette facture ?')) return;
    await fetch('/api/invoices/'+id, { method: 'DELETE' });
    navTo('#invoices');
  }));
}
