<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BudgetPro – User Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{
      --bg:#f7f8fc;--white:#fff;--sidebar-bg:#fff;--sidebar-border:#eef0f8;
      --accent:#4f46e5;--accent-soft:#eef0ff;
      --teal:#059669;--teal-soft:#d1fae5;
      --red:#ef4444;--red-soft:#fee2e2;
      --orange:#f59e0b;--orange-soft:#fff7ed;
      --yellow:#fbbf24;--yellow-soft:#fffbeb;
      --text-dark:#111827;--text-mid:#374151;--text-muted:#6b7280;--text-light:#9ca3af;
      --border:#e5e7eb;--radius:12px;
      --shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text-dark);display:flex;min-height:100vh;font-size:14px;}
    h1,h2,h3,h4{font-family:'Sora',sans-serif;}

    /* SIDEBAR */
    .sidebar{width:220px;flex-shrink:0;background:var(--sidebar-bg);border-right:1px solid var(--sidebar-border);display:flex;flex-direction:column;padding:0;min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
    .sidebar-header{padding:20px 20px 16px;border-bottom:1px solid var(--sidebar-border);}
    .logo-row{display:flex;align-items:center;gap:10px;}
    .logo-mark{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#fff;font-family:'Sora',sans-serif;}
    .logo-text h2{font-size:.98rem;font-weight:800;color:var(--text-dark);line-height:1.1;}
    .logo-text p{font-size:.68rem;color:var(--text-muted);margin-top:1px;}
    .sidebar-nav{padding:12px 10px;flex:1;}
    .nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;cursor:pointer;color:var(--text-muted);font-size:.87rem;font-weight:500;text-decoration:none;transition:all .15s;margin-bottom:2px;}
    .nav-item:hover{background:#f3f4f6;color:var(--text-dark);}
    .nav-item.active{background:var(--accent-soft);color:var(--accent);font-weight:700;}
    .nav-item.active .nav-icon{color:var(--accent);}
    .nav-icon{font-size:1rem;width:18px;text-align:center;color:#9ca3af;}
    .sidebar-spacer{flex:1;}
    .sidebar-footer{padding:14px 16px;border-top:1px solid var(--sidebar-border);display:flex;flex-direction:column;gap:8px;}
    .btn-insights{width:100%;background:#f3f4f6;border:1px solid var(--border);border-radius:9px;padding:9px;font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:600;color:var(--text-mid);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;}
    .btn-new-report{width:100%;background:var(--accent);color:#fff;border:none;border-radius:9px;padding:10px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 4px 12px rgba(79,70,229,.3);transition:opacity .2s;}
    .btn-new-report:hover{opacity:.9;}

    /* MAIN */
    .main{margin-left:220px;flex:1;display:flex;flex-direction:column;}

    /* TOPNAV */
    .topnav{background:var(--white);border-bottom:1px solid var(--border);padding:12px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
    .topnav-search{display:flex;align-items:center;gap:8px;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:8px 14px;min-width:280px;}
    .topnav-search input{background:transparent;border:none;outline:none;font-family:'DM Sans',sans-serif;font-size:.85rem;color:var(--text-mid);width:100%;}
    .topnav-search input::placeholder{color:var(--text-light);}
    .topnav-right{display:flex;align-items:center;gap:12px;}
    .notif-btn{width:34px;height:34px;border-radius:50%;background:#f3f4f6;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.95rem;position:relative;}
    .notif-dot{position:absolute;top:5px;right:5px;width:8px;height:8px;border-radius:50%;background:var(--red);border:2px solid #fff;}
    .profile-btn{display:flex;align-items:center;gap:8px;cursor:pointer;padding:5px 12px 5px 5px;background:#f3f4f6;border:1px solid var(--border);border-radius:50px;transition:background .15s;}
    .profile-btn:hover{background:#ebebeb;}
    .profile-ava{width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff;}
    .profile-btn span{font-size:.82rem;font-weight:600;color:var(--text-mid);}
    .profile-btn .chevron{color:var(--text-light);font-size:.7rem;}

    /* CONTENT */
    .content{padding:26px 28px;}

    /* BREADCRUMB */
    .breadcrumb{display:flex;align-items:center;gap:6px;font-size:.78rem;color:var(--text-muted);margin-bottom:14px;}
    .breadcrumb a{color:var(--text-muted);text-decoration:none;}
    .breadcrumb a:hover{color:var(--accent);}
    .breadcrumb .active-crumb{color:var(--accent);font-weight:600;}
    .breadcrumb .sep{color:var(--text-light);}

    /* PAGE HEADER */
    .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;}
    .page-header h1{font-size:1.45rem;font-weight:800;}
    .page-header p{font-size:.84rem;color:var(--text-muted);margin-top:5px;}
    .btn-add-user{background:var(--accent);color:#fff;border:none;border-radius:9px;padding:10px 18px;font-family:'Sora',sans-serif;font-weight:700;font-size:.83rem;cursor:pointer;display:flex;align-items:center;gap:7px;box-shadow:0 4px 12px rgba(79,70,229,.3);transition:opacity .2s;}
    .btn-add-user:hover{opacity:.9;}

    /* ROLE MANAGEMENT */
    .role-section{background:var(--white);border-radius:var(--radius);padding:22px;box-shadow:var(--shadow);margin-bottom:22px;}
    .role-section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
    .role-section-header div h3{font-size:.95rem;font-weight:700;}
    .role-section-header div p{font-size:.78rem;color:var(--text-muted);margin-top:2px;}
    .view-tabs{display:flex;gap:4px;background:#f3f4f6;border-radius:8px;padding:3px;}
    .vtab{padding:5px 12px;border-radius:6px;font-size:.77rem;font-weight:600;cursor:pointer;color:var(--text-muted);border:none;background:transparent;transition:all .15s;}
    .vtab.active{background:var(--white);color:var(--accent);box-shadow:0 1px 4px rgba(0,0,0,.08);}
    .role-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
    .role-card{border:1px solid var(--border);border-radius:10px;padding:18px;}
    .role-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
    .role-card-icon{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
    .ri-purple{background:#f5f3ff;}
    .ri-blue{background:#eff6ff;}
    .ri-gray{background:#f9fafb;}
    .member-badge{font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:50px;display:flex;align-items:center;gap:4px;}
    .mb-purple{background:#ede9fe;color:var(--accent);}
    .mb-blue{background:#dbeafe;color:#1e40af;}
    .mb-gray{background:#f3f4f6;color:var(--text-muted);}
    .role-card h4{font-size:.88rem;font-weight:700;margin-bottom:6px;}
    .role-card p{font-size:.78rem;color:var(--text-muted);line-height:1.5;margin-bottom:12px;}
    .config-link{font-size:.78rem;color:var(--accent);font-weight:600;text-decoration:none;cursor:pointer;}
    .config-link:hover{text-decoration:underline;}

    /* USERS TABLE */
    .users-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);}
    .users-toolbar{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);}
    .toolbar-left{display:flex;gap:8px;}
    .btn-toolbar{display:flex;align-items:center;gap:6px;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:7px 14px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:600;color:var(--text-mid);cursor:pointer;transition:background .15s;}
    .btn-toolbar:hover{background:#ebebeb;}
    .showing-label{font-size:.82rem;color:var(--text-muted);}
    .showing-label strong{color:var(--text-dark);}

    /* TABLE */
    table{width:100%;border-collapse:collapse;}
    thead th{font-size:.67rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;padding:10px 20px;text-align:left;background:#fafafa;border-bottom:1px solid var(--border);}
    tbody tr{border-bottom:1px solid #f3f4f6;transition:background .12s;}
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{background:#fafbff;}
    td{padding:13px 20px;font-size:.85rem;vertical-align:middle;}

    /* User cell */
    .user-cell{display:flex;align-items:center;gap:10px;}
    .user-ava-img{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#fff;flex-shrink:0;font-family:'Sora',sans-serif;}
    .user-name{font-size:.87rem;font-weight:600;color:var(--text-dark);}
    .user-email{font-size:.75rem;color:var(--text-muted);}

    /* Role badge */
    .role-badge{font-size:.71rem;font-weight:800;padding:3px 10px;border-radius:5px;letter-spacing:.04em;}
    .rb-admin{background:var(--accent-soft);color:var(--accent);}
    .rb-user{background:#f3f4f6;color:var(--text-mid);}

    /* Status badge */
    .status-badge{display:inline-flex;align-items:center;gap:5px;font-size:.78rem;font-weight:600;padding:4px 10px;border-radius:50px;}
    .sb-active{background:var(--teal-soft);color:var(--teal);}
    .sb-pending{background:var(--yellow-soft);color:#92400e;}
    .sb-blocked{background:var(--red-soft);color:var(--red);}
    .status-dot{width:6px;height:6px;border-radius:50%;}
    .dot-teal{background:var(--teal);}
    .dot-yellow{background:var(--yellow);}
    .dot-red{background:var(--red);}

    .td-activity{color:var(--text-muted);font-size:.82rem;}

    /* Actions */
    .actions-cell{display:flex;align-items:center;gap:6px;}
    .action-btn{width:28px;height:28px;border-radius:7px;background:#f3f4f6;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.8rem;color:var(--text-muted);transition:all .15s;}
    .action-btn:hover{background:var(--accent-soft);border-color:#c7d2fe;color:var(--accent);}
    .action-btn.danger:hover{background:var(--red-soft);border-color:#fecaca;color:var(--red);}
    .btn-approve{background:var(--teal);color:#fff;border:none;border-radius:7px;padding:5px 12px;font-family:'DM Sans',sans-serif;font-size:.77rem;font-weight:700;cursor:pointer;transition:opacity .2s;}
    .btn-approve:hover{opacity:.85;}

    /* PAGINATION */
    .pagination{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid var(--border);}
    .page-btns{display:flex;gap:4px;}
    .page-btn{width:30px;height:30px;border-radius:7px;border:1px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:600;color:var(--text-mid);cursor:pointer;transition:all .15s;}
    .page-btn:hover{background:#f3f4f6;}
    .page-btn.active{background:var(--accent);border-color:var(--accent);color:#fff;}
    .page-btn.arrow{color:var(--text-light);}
    .rows-per-page{display:flex;align-items:center;gap:8px;font-size:.8rem;color:var(--text-muted);}
    .rows-select{background:#f3f4f6;border:1px solid var(--border);border-radius:6px;padding:4px 8px;font-family:'DM Sans',sans-serif;font-size:.8rem;color:var(--text-mid);outline:none;}
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="logo-row">
      <div class="logo-mark">B</div>
      <div class="logo-text">
        <h2>BudgetPro</h2>
        <p>Collaborative Finance</p>
      </div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="#"><span class="nav-icon">📊</span> Dashboard</a>
    <a class="nav-item active" href="#"><span class="nav-icon">👥</span> Users</a>
    <a class="nav-item" href="#"><span class="nav-icon">🎯</span> Budgets</a>
    <a class="nav-item" href="#"><span class="nav-icon">💳</span> Transactions</a>
    <a class="nav-item" href="#"><span class="nav-icon">🗂️</span> Categories</a>
    <a class="nav-item" href="#"><span class="nav-icon">🔔</span> Alerts</a>
    <a class="nav-item" href="#"><span class="nav-icon">⚙️</span> Settings</a>
  </nav>
  <div class="sidebar-spacer"></div>
  <div class="sidebar-footer">
    <button class="btn-insights">✦ Generate Insights</button>
    <button class="btn-new-report">New Report</button>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topnav">
    <div class="topnav-search">
      <span style="color:#9ca3af;font-size:.85rem">🔍</span>
      <input type="text" placeholder="Search users, roles, or permissions..."/>
    </div>
    <div class="topnav-right">
      <div class="notif-btn">🔔<div class="notif-dot"></div></div>
      <div class="profile-btn">
        <div class="profile-ava">AR</div>
        <span>Alex Rivera</span>
        <span class="chevron">▾</span>
      </div>
    </div>
  </div>

  <div class="content">
    <!-- BREADCRUMB -->
    <div class="breadcrumb">
      <a href="#">Management</a>
      <span class="sep">/</span>
      <span class="active-crumb">User Management</span>
    </div>

    <!-- PAGE HEADER -->
    <div class="page-header">
      <div>
        <h1>User Management</h1>
        <p>Manage organizational access, roles, and security permissions.</p>
      </div>
      <button class="btn-add-user">👤 Add New User</button>
    </div>

    <!-- ROLE MANAGEMENT -->
    <div class="role-section">
      <div class="role-section-header">
        <div>
          <h3>Role Management</h3>
          <p>Configure permission groups for your financial ecosystem.</p>
        </div>
        <div class="view-tabs">
          <button class="vtab active">Global View</button>
          <button class="vtab">Admin Only</button>
          <button class="vtab">Restricted</button>
        </div>
      </div>
      <div class="role-cards">
        <!-- Full Administrators -->
        <div class="role-card">
          <div class="role-card-header">
            <div class="role-card-icon ri-purple">🛡️</div>
            <div class="member-badge mb-purple">4 Members</div>
          </div>
          <h4>Full Administrators</h4>
          <p>Unrestricted access to all budgets, user management, and global settings.</p>
          <a class="config-link">Configure Permissions →</a>
        </div>
        <!-- Budget Managers -->
        <div class="role-card">
          <div class="role-card-header">
            <div class="role-card-icon ri-blue">🏛️</div>
            <div class="member-badge mb-blue">10 Members</div>
          </div>
          <h4>Budget Managers</h4>
          <p>Can create, edit and approve transactions for specific assigned departments.</p>
          <a class="config-link">Configure Permissions →</a>
        </div>
        <!-- Auditors -->
        <div class="role-card">
          <div class="role-card-header">
            <div class="role-card-icon ri-gray">👁️</div>
            <div class="member-badge mb-gray">38 Members</div>
          </div>
          <h4>Auditors</h4>
          <p>Read-only access to transaction history and generated financial reports.</p>
          <a class="config-link">Configure Permissions →</a>
        </div>
      </div>
    </div>

    <!-- USERS TABLE -->
    <div class="users-card">
      <div class="users-toolbar">
        <div class="toolbar-left">
          <button class="btn-toolbar">⚙ Filter</button>
          <button class="btn-toolbar">↑ Export</button>
        </div>
        <div class="showing-label">Showing <strong>54 Users</strong></div>
      </div>

      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Activity</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sarah Chen -->
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-ava-img" style="background:linear-gradient(135deg,#4f46e5,#818cf8)">SC</div>
                <div>
                  <div class="user-name">Sarah Chen</div>
                  <div class="user-email">sarah.c@budgetpro.com</div>
                </div>
              </div>
            </td>
            <td><span class="role-badge rb-admin">ADMIN</span></td>
            <td><span class="status-badge sb-active"><span class="status-dot dot-teal"></span>Active</span></td>
            <td class="td-activity">2 mins ago</td>
            <td>
              <div class="actions-cell">
                <div class="action-btn">👁</div>
                <div class="action-btn">✏️</div>
                <div class="action-btn danger">🗑</div>
              </div>
            </td>
          </tr>
          <!-- Marcus Kinsley -->
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-ava-img" style="background:linear-gradient(135deg,#059669,#34d399)">MK</div>
                <div>
                  <div class="user-name">Marcus Kinsley</div>
                  <div class="user-email">m.kinsley@partner.co</div>
                </div>
              </div>
            </td>
            <td><span class="role-badge rb-user">USER</span></td>
            <td><span class="status-badge sb-pending"><span class="status-dot dot-yellow"></span>Pending</span></td>
            <td class="td-activity">Invited 4h ago</td>
            <td>
              <div class="actions-cell">
                <button class="btn-approve">Approve</button>
                <div class="action-btn danger">🗑</div>
              </div>
            </td>
          </tr>
          <!-- David Miller -->
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-ava-img" style="background:linear-gradient(135deg,#ef4444,#f87171)">DM</div>
                <div>
                  <div class="user-name">David Miller</div>
                  <div class="user-email">david.miller@corp.com</div>
                </div>
              </div>
            </td>
            <td><span class="role-badge rb-user">USER</span></td>
            <td><span class="status-badge sb-blocked"><span class="status-dot dot-red"></span>Blocked</span></td>
            <td class="td-activity">2 days ago</td>
            <td>
              <div class="actions-cell">
                <div class="action-btn">👁</div>
                <div class="action-btn">✏️</div>
                <div class="action-btn danger">🗑</div>
              </div>
            </td>
          </tr>
          <!-- Elena Rodriguez -->
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-ava-img" style="background:linear-gradient(135deg,#f59e0b,#fcd34d)">ER</div>
                <div>
                  <div class="user-name">Elena Rodriguez</div>
                  <div class="user-email">e.rodriguez@budgetpro.com</div>
                </div>
              </div>
            </td>
            <td><span class="role-badge rb-admin">ADMIN</span></td>
            <td><span class="status-badge sb-active"><span class="status-dot dot-teal"></span>Active</span></td>
            <td class="td-activity">Now</td>
            <td>
              <div class="actions-cell">
                <div class="action-btn">👁</div>
                <div class="action-btn">✏️</div>
                <div class="action-btn danger">🗑</div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINATION -->
      <div class="pagination">
        <div class="page-btns">
          <button class="page-btn arrow">‹</button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <button class="page-btn">…</button>
          <button class="page-btn arrow">›</button>
        </div>
        <div class="rows-per-page">
          Rows per page
          <select class="rows-select">
            <option>20</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // View tab toggle
  document.querySelectorAll('.vtab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.vtab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });
</script>
</body>
</html>