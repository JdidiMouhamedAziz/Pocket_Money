<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – Budgets</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --bg: #f4f5fb;
      --sidebar-bg: #2d2d3a;
      --purple: #7c6af5;
      --purple-light: #f0edff;
      --white: #ffffff;
      --text-dark: #1a1a2e;
      --text-mid: #555770;
      --text-muted: #9295a8;
      --border: #e5e7ef;
      --teal: #00c9a7;
      --red: #ff4d6d;
      --yellow: #f5c842;
      --blue: #4c9be8;
      --green: #2ecc71;
      --input-bg: #f8f9fe;
      --card-radius: 14px;
      --shadow: 0 2px 16px rgba(0,0,0,.07);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text-dark); display: flex; min-height: 100vh; }
    h1,h2,h3,h4 { font-family: 'Sora', sans-serif; }

    /* ── SIDEBAR ── */
    .sidebar { width:200px; flex-shrink:0; background:var(--sidebar-bg); display:flex; flex-direction:column; padding:28px 0 20px; min-height:100vh; position:fixed; left:0; top:0; bottom:0; }
    .sidebar-logo { display:flex; align-items:center; gap:10px; padding:0 22px; margin-bottom:36px; }
    .logo-icon { width:40px; height:40px; border-radius:50%; flex-shrink:0; background:conic-gradient(#f5c842 0deg 90deg,#00c9a7 90deg 180deg,#ff7c3e 180deg 270deg,#7c6af5 270deg 360deg); display:flex; align-items:center; justify-content:center; position:relative; }
    .logo-icon::before { content:''; position:absolute; width:26px; height:26px; border-radius:50%; background:var(--sidebar-bg); }
    .logo-icon span { position:relative; z-index:1; font-size:.8rem; font-weight:800; color:#fff; }
    .sidebar-logo h2 { font-size:1.3rem; font-weight:800; color:#fff; }
    .sidebar-section-label { font-size:.68rem; font-weight:600; color:#6b6e80; letter-spacing:.1em; text-transform:uppercase; padding:0 22px; margin-bottom:8px; margin-top:10px; }
    .nav-item { display:flex; align-items:center; gap:11px; padding:11px 22px; cursor:pointer; color:#b0b3c6; font-size:.92rem; font-weight:500; text-decoration:none; transition:background .15s,color .15s; }
    .nav-item:hover { background:rgba(255,255,255,.06); color:#fff; }
    .nav-item.active { background:var(--purple); color:#fff; font-weight:700; border-radius:10px; margin:0 10px; padding:11px 12px; }
    .nav-icon { font-size:1rem; width:20px; text-align:center; }
    .sidebar-spacer { flex:1; }
    .sidebar-user { display:flex; align-items:center; gap:10px; padding:14px 22px; border-top:1px solid rgba(255,255,255,.08); }
    .user-ava { width:36px; height:36px; border-radius:50%; background:var(--purple); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; flex-shrink:0; }
    .user-name { font-size:.85rem; font-weight:600; color:#fff; }
    .user-plan { font-size:.72rem; color:#6b6e80; }

    /* ── MAIN ── */
    .main { margin-left:200px; flex:1; padding:28px 26px; }

    /* TOPBAR */
    .topbar { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:22px; }
    .topbar h1 { font-size:1.4rem; font-weight:800; }
    .topbar p { font-size:.82rem; color:var(--text-muted); margin-top:2px; }
    .btn-new { background:var(--purple); color:#fff; border:none; border-radius:9px; padding:10px 20px; font-family:'Sora',sans-serif; font-weight:700; font-size:.87rem; cursor:pointer; display:flex; align-items:center; gap:6px; box-shadow:0 4px 14px rgba(124,106,245,.3); transition:opacity .2s,transform .15s; }
    .btn-new:hover { opacity:.9; transform:translateY(-1px); }

    /* STAT STRIP */
    .stat-strip { display:grid; grid-template-columns:repeat(4,1fr); background:var(--white); border-radius:var(--card-radius); box-shadow:var(--shadow); margin-bottom:18px; overflow:hidden; }
    .stat-cell { padding:16px 20px; }
    .stat-cell + .stat-cell { border-left:1px solid var(--border); }
    .stat-cell .label { font-size:.78rem; color:var(--text-muted); font-weight:500; margin-bottom:5px; }
    .stat-cell .value { font-family:'Sora',sans-serif; font-size:1.25rem; font-weight:800; }
    .v-default { color:var(--text-dark); }
    .v-red     { color:var(--red); }
    .v-dark    { color:var(--text-dark); }

    /* ALERT BANNER */
    .alert-banner { background:#fff0f3; border:1.5px solid #ffc0cb; border-radius:12px; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
    .alert-left { display:flex; align-items:center; gap:12px; }
    .alert-icon { font-size:1.1rem; }
    .alert-title { font-size:.9rem; font-weight:700; color:var(--red); }
    .alert-sub   { font-size:.78rem; color:#c04060; margin-top:2px; }
    .alert-review { font-size:.85rem; font-weight:700; color:var(--red); cursor:pointer; white-space:nowrap; text-decoration:none; }
    .alert-review:hover { text-decoration:underline; }

    /* BUDGET GRID */
    .budget-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }

    /* BUDGET CARD */
    .budget-card { background:var(--white); border-radius:var(--card-radius); padding:22px; box-shadow:var(--shadow); }
    .budget-card-header { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
    .b-dot { width:13px; height:13px; border-radius:50%; flex-shrink:0; }
    .budget-card-header h4 { font-size:1.05rem; font-weight:700; }
    .b-spent { font-size:.82rem; color:var(--text-muted); margin-bottom:12px; }
    .b-spent strong { color:var(--text-dark); }
    .prog-bg { background:#ececf5; border-radius:50px; height:7px; margin-bottom:12px; }
    .prog-fill { height:7px; border-radius:50px; transition:width .4s; }
    .b-footer { display:flex; justify-content:space-between; align-items:center; }
    .b-remaining { font-size:.8rem; color:var(--text-muted); font-weight:600; }
    .b-pct { font-size:.75rem; font-weight:700; padding:3px 10px; border-radius:50px; }
    .pct-warn   { background:#fff8e6; color:#c8960c; }
    .pct-danger { background:#fff0f3; color:var(--red); }
    .pct-ok     { background:#e6faf6; color:var(--teal); }

    /* CREATE CARD */
    .create-card {
      background:var(--purple-light);
      border:2px dashed #c5bcf7;
      border-radius:var(--card-radius);
      padding:22px;
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      cursor:pointer; gap:10px; transition:background .2s, border-color .2s;
      min-height:150px;
    }
    .create-card:hover { background:#e8e3ff; border-color:var(--purple); }
    .create-plus { width:38px; height:38px; border-radius:50%; background:rgba(124,106,245,.18); display:flex; align-items:center; justify-content:center; font-size:1.4rem; color:var(--purple); }
    .create-card h4 { font-size:1rem; font-weight:700; color:var(--purple); }
    .create-card p  { font-size:.8rem; color:#9b8ff5; text-align:center; }

    /* ── MODAL ── */
    .modal-overlay { position:fixed; inset:0; background:rgba(30,28,50,.5); display:flex; align-items:center; justify-content:center; z-index:1000; opacity:0; pointer-events:none; transition:opacity .2s; backdrop-filter:blur(2px); }
    .modal-overlay.open { opacity:1; pointer-events:all; }
    .modal { background:#fff; border-radius:18px; padding:28px; width:100%; max-width:400px; box-shadow:0 24px 60px rgba(0,0,0,.18); transform:translateY(16px) scale(.97); transition:transform .2s; }
    .modal-overlay.open .modal { transform:translateY(0) scale(1); }
    .modal-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
    .modal-header h3 { font-size:1rem; font-weight:800; color:var(--text-dark); }
    .modal-header p  { font-size:.78rem; color:var(--text-muted); margin-top:2px; }
    .modal-close { background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-muted); }
    .modal-close:hover { color:var(--text-dark); }

    /* Amount display */
    .amount-display { background:var(--purple-light); border-radius:12px; display:flex; align-items:center; justify-content:space-between; padding:14px 20px; margin-bottom:20px; }
    .amount-display input { font-family:'Sora',sans-serif; font-size:1.8rem; font-weight:800; color:var(--purple); background:none; border:none; outline:none; width:160px; }
    .amount-display .currency { font-family:'Sora',sans-serif; font-size:1.2rem; font-weight:700; color:var(--purple); opacity:.6; }

    /* Form */
    .form-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px; }
    .form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
    .form-group label { font-size:.78rem; font-weight:600; color:var(--text-mid); }
    .form-group label span { color:var(--red); }
    .form-group input, .form-group select, .form-group textarea { background:var(--input-bg); border:1.5px solid var(--border); border-radius:9px; padding:10px 13px; font-family:'DM Sans',sans-serif; font-size:.88rem; color:var(--text-dark); outline:none; transition:border-color .2s; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:var(--purple); }
    .form-group input::placeholder, .form-group textarea::placeholder { color:var(--text-muted); }
    .form-group textarea { resize:none; height:70px; }
    .date-wrap { position:relative; }
    .date-wrap input { padding-right:36px; }
    .date-icon { position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none; }

    /* Category chips */
    .cat-chips { display:flex; flex-wrap:wrap; gap:8px; }
    .cat-chip { padding:7px 16px; border-radius:50px; font-size:.82rem; font-weight:600; border:1.5px solid var(--border); background:var(--input-bg); color:var(--text-mid); cursor:pointer; transition:all .15s; font-family:'DM Sans',sans-serif; }
    .cat-chip.active { background:var(--purple); border-color:var(--purple); color:#fff; }
    .cat-chip:hover:not(.active) { border-color:var(--purple); color:var(--purple); }

    /* Modal footer */
    .modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
    .btn-cancel { background:none; border:1.5px solid var(--border); border-radius:9px; padding:10px 20px; font-family:'DM Sans',sans-serif; font-weight:600; font-size:.88rem; color:var(--text-mid); cursor:pointer; }
    .btn-cancel:hover { background:var(--bg); }
    .btn-create { background:var(--purple); color:#fff; border:none; border-radius:9px; padding:10px 22px; font-family:'Sora',sans-serif; font-weight:700; font-size:.88rem; cursor:pointer; display:flex; align-items:center; gap:6px; box-shadow:0 4px 14px rgba(124,106,245,.3); transition:opacity .2s; }
    .btn-create:hover { opacity:.9; }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon"><span>$</span></div>
    <h2>Finzo</h2>
  </div>
  <div class="sidebar-section-label">Main</div>
  <a class="nav-item" href="#"><span class="nav-icon">🏠</span> Dashboard</a>
  <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions</a>
  <a class="nav-item active" href="#"><span class="nav-icon">🎯</span> Budgets</a>
  <a class="nav-item" href="#"><span class="nav-icon">🗂️</span> Categories</a>
  <div class="sidebar-section-label" style="margin-top:18px;">Collaboration</div>
  <a class="nav-item" href="#"><span class="nav-icon">👥</span> My Groups</a>
  <a class="nav-item" href="#"><span class="nav-icon">🔔</span> Alerts</a>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-user">
    <div class="user-ava">KM</div>
    <div>
      <div class="user-name">Karim M.</div>
      <div class="user-plan">Free plan</div>
    </div>
  </div>
</aside>

<!-- MAIN -->
<main class="main">

  <!-- Topbar -->
  <div class="topbar">
    <div>
      <h1>Budgets</h1>
      <p>Track your spending limits</p>
    </div>
    <button class="btn-new" onclick="openModal()">＋ New budget</button>
  </div>

  <!-- Stat Strip -->
  <div class="stat-strip">
    <div class="stat-cell">
      <div class="label">Total budgeted</div>
      <div class="value v-default">2500 TND</div>
    </div>
    <div class="stat-cell">
      <div class="label">Total spent</div>
      <div class="value v-red">500 TND</div>
    </div>
    <div class="stat-cell">
      <div class="label">Remaining</div>
      <div class="value v-dark">970 TND</div>
    </div>
    <div class="stat-cell">
      <div class="label">Budget active</div>
      <div class="value v-dark">5</div>
    </div>
  </div>

  <!-- Alert Banner -->
  <div class="alert-banner">
    <div class="alert-left">
      <div class="alert-icon">⚠️</div>
      <div>
        <div class="alert-title">Housing budget is 95% used</div>
        <div class="alert-sub">You have only $50 left for housing</div>
      </div>
    </div>
    <a class="alert-review" href="#">Review →</a>
  </div>

  <!-- Budget Grid -->
  <div class="budget-grid">

    <!-- Food -->
    <div class="budget-card">
      <div class="budget-card-header">
        <div class="b-dot" style="background:#f5c842"></div>
        <h4>Food</h4>
      </div>
      <div class="b-spent"><strong>640 TND</strong> of 800 TND</div>
      <div class="prog-bg"><div class="prog-fill" style="width:80%;background:#f5c842"></div></div>
      <div class="b-footer">
        <div class="b-remaining">160 TND</div>
        <div class="b-pct pct-warn">80% used</div>
      </div>
    </div>

    <!-- Housing -->
    <div class="budget-card">
      <div class="budget-card-header">
        <div class="b-dot" style="background:var(--red)"></div>
        <h4>Housing</h4>
      </div>
      <div class="b-spent"><strong>950 TND</strong> of 1000 TND</div>
      <div class="prog-bg"><div class="prog-fill" style="width:95%;background:var(--red)"></div></div>
      <div class="b-footer">
        <div class="b-remaining">50 TND</div>
        <div class="b-pct pct-danger">95% used</div>
      </div>
    </div>

    <!-- Transport -->
    <div class="budget-card">
      <div class="budget-card-header">
        <div class="b-dot" style="background:var(--teal)"></div>
        <h4>Transport</h4>
      </div>
      <div class="b-spent"><strong>180 TND</strong> of 400 TND</div>
      <div class="prog-bg"><div class="prog-fill" style="width:30%;background:var(--teal)"></div></div>
      <div class="b-footer">
        <div class="b-remaining">160 TND</div>
        <div class="b-pct pct-ok">30% used</div>
      </div>
    </div>

    <!-- Health -->
    <div class="budget-card">
      <div class="budget-card-header">
        <div class="b-dot" style="background:var(--purple)"></div>
        <h4>Health</h4>
      </div>
      <div class="b-spent"><strong>150 TND</strong> of 500 TND</div>
      <div class="prog-bg"><div class="prog-fill" style="width:30%;background:var(--purple)"></div></div>
      <div class="b-footer">
        <div class="b-remaining">350 TND</div>
        <div class="b-pct pct-ok">30% used</div>
      </div>
    </div>

    <!-- Create new budget -->
    <div class="create-card" onclick="openModal()">
      <div class="create-plus">＋</div>
      <h4>Create new budget</h4>
      <p>Set a spending limit for a new category</p>
    </div>

  </div>
</main>

<!-- ── MODAL ── -->
<div class="modal-overlay" id="modalOverlay" onclick="handleOverlay(event)">
  <div class="modal">
    <div class="modal-header">
      <div>
        <h3>Create New Budget</h3>
        <p>Set a spending limit for a category</p>
      </div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>

    <!-- Budget Limit Amount -->
    <div class="form-group">
      <label>Budget Limit <span>*</span></label>
    </div>
    <div class="amount-display">
      <input type="number" placeholder="0.00" step="0.01" min="0"/>
      <span class="currency">TND</span>
    </div>

    <!-- Period + Start Date -->
    <div class="form-row-2">
      <div class="form-group" style="margin-bottom:0">
        <label>Period <span>*</span></label>
        <select>
          <option>Monthly</option>
          <option>Weekly</option>
          <option>Yearly</option>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Start Date <span>*</span></label>
        <div class="date-wrap">
          <input type="date" id="budgetDate"/>
          <span class="date-icon">📅</span>
        </div>
      </div>
    </div>

    <!-- Category -->
    <div class="form-group" style="margin-top:14px;">
      <label>Category <span>*</span></label>
      <div class="cat-chips">
        <button class="cat-chip active" onclick="setChip(this)">Food</button>
        <button class="cat-chip" onclick="setChip(this)">Transport</button>
        <button class="cat-chip" onclick="setChip(this)">Housing</button>
        <button class="cat-chip" onclick="setChip(this)">Health</button>
        <button class="cat-chip" onclick="setChip(this)">Shopping</button>
        <button class="cat-chip" onclick="setChip(this)">Other</button>
      </div>
    </div>

    <!-- Note -->
    <div class="form-group" style="margin-top:14px;">
      <label>Note (optional)</label>
      <textarea placeholder="Add a short note about this transaction…"></textarea>
    </div>

    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Cancel</button>
      <button class="btn-create">➕ Create Budget</button>
    </div>
  </div>
</div>

<script>
  document.getElementById('budgetDate').valueAsDate = new Date();

  function openModal()  { document.getElementById('modalOverlay').classList.add('open'); }
  function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }
  function handleOverlay(e) { if (e.target === e.currentTarget) closeModal(); }
  function setChip(el) {
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>