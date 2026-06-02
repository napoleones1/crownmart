<?php
require_once __DIR__ . '/../api/config.php';
$admin = requireAdmin('../login.php');
$pdo   = getDB();

// Stats
$stats = [];
$stats['users']    = $pdo->query('SELECT COUNT(*) FROM users WHERE role != "admin"')->fetchColumn();
$stats['sellers']  = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "seller"')->fetchColumn();
$stats['products'] = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$stats['orders']   = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$stats['revenue']  = $pdo->query('SELECT COALESCE(SUM(total),0) FROM orders')->fetchColumn();
$stats['bids']     = $pdo->query('SELECT COUNT(*) FROM bids')->fetchColumn();

// Recent orders (last 10)
$recentOrders = $pdo->query('
  SELECT o.*, u.name as user_name, u.username
  FROM orders o LEFT JOIN users u ON o.user_id = u.id
  ORDER BY o.created_at DESC LIMIT 10
')->fetchAll();

// Recent users (last 10)
$recentUsers = $pdo->query('
  SELECT * FROM users ORDER BY created_at DESC LIMIT 10
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CrownMart Admin Dashboard</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: #0f1111; }

    /* LAYOUT */
    .admin-wrap { display: flex; min-height: 100vh; }

    /* SIDEBAR */
    .sidebar {
      width: 240px; flex-shrink: 0;
      background: #0f1923;
      display: flex; flex-direction: column;
      position: sticky; top: 0; height: 100vh;
      overflow-y: auto;
    }
    .sidebar-logo {
      padding: 20px 20px 16px;
      border-bottom: 1px solid #1e2d3d;
    }
    .sidebar-logo .name { font-size: 20px; font-weight: 900; color: white; }
    .sidebar-logo .name span { color: #f0c14b; text-decoration: underline; }
    .sidebar-logo .badge {
      font-size: 9px; background: #1e2d3d; color: #f0c14b;
      padding: 2px 6px; border-radius: 3px; font-weight: 800;
      text-transform: uppercase; font-family: monospace;
    }
    .sidebar-logo .admin-pill {
      display: inline-block; margin-top: 8px;
      background: #fef9c3; color: #854d0e;
      font-size: 10px; font-weight: 800; padding: 3px 10px;
      border-radius: 20px; text-transform: uppercase;
    }

    .sidebar-nav { padding: 16px 12px; flex: 1; }
    .nav-section-label {
      font-size: 9px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .08em; color: #475569; padding: 0 8px;
      margin: 16px 0 6px;
    }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 12px; border-radius: 6px;
      color: #94a3b8; font-size: 13px; font-weight: 600;
      text-decoration: none; transition: all .15s; cursor: pointer;
      border: none; background: none; width: 100%; text-align: left;
    }
    .nav-item:hover { background: #1e2d3d; color: white; }
    .nav-item.active { background: #1e2d3d; color: #f0c14b; }
    .nav-item .icon { font-size: 16px; width: 20px; text-align: center; }

    .sidebar-footer {
      padding: 16px; border-top: 1px solid #1e2d3d;
    }
    .admin-profile { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .admin-avatar { font-size: 28px; }
    .admin-name { font-size: 13px; font-weight: 700; color: white; }
    .admin-role { font-size: 10px; color: #64748b; }
    .btn-logout {
      width: 100%; padding: 8px; background: #1e2d3d; border: 1px solid #334155;
      color: #94a3b8; border-radius: 6px; font-size: 12px; font-weight: 600;
      cursor: pointer; transition: all .15s;
    }
    .btn-logout:hover { background: #dc2626; color: white; border-color: #dc2626; }

    /* MAIN */
    .admin-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

    /* TOP BAR */
    .admin-topbar {
      background: white; border-bottom: 1px solid #e2e8f0;
      padding: 14px 24px; display: flex; align-items: center;
      justify-content: space-between; gap: 12px; flex-wrap: wrap;
      position: sticky; top: 0; z-index: 10;
    }
    .topbar-title { font-size: 16px; font-weight: 800; color: #0f1111; }
    .topbar-subtitle { font-size: 12px; color: #9ca3af; margin-top: 1px; }
    .topbar-actions { display: flex; align-items: center; gap: 8px; }
    .btn-sm {
      padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700;
      cursor: pointer; transition: all .15s; border: 1px solid transparent;
    }
    .btn-primary { background: #f0c14b; border-color: #a88734; color: #0f1111; }
    .btn-primary:hover { background: #f5d060; }
    .btn-danger { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
    .btn-danger:hover { background: #fecaca; }
    .btn-info { background: #dbeafe; border-color: #93c5fd; color: #1e40af; }

    /* CONTENT */
    .admin-content { padding: 24px; flex: 1; }

    /* SECTION (tab panels) */
    .section { display: none; }
    .section.active { display: block; animation: fadeIn .2s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    /* STATS GRID */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .stat-card {
      background: white; border: 1px solid #e2e8f0;
      border-radius: 10px; padding: 18px 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .stat-icon { font-size: 28px; margin-bottom: 8px; }
    .stat-num { font-size: 26px; font-weight: 900; color: #0f1111; font-family: monospace; }
    .stat-label { font-size: 12px; color: #6b7280; margin-top: 3px; font-weight: 600; }
    .stat-card.green { border-left: 4px solid #10b981; }
    .stat-card.blue  { border-left: 4px solid #3b82f6; }
    .stat-card.yellow{ border-left: 4px solid #f0c14b; }
    .stat-card.purple{ border-left: 4px solid #8b5cf6; }
    .stat-card.red   { border-left: 4px solid #ef4444; }
    .stat-card.orange{ border-left: 4px solid #f97316; }

    /* PANEL */
    .panel {
      background: white; border: 1px solid #e2e8f0;
      border-radius: 10px; overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,.05);
      margin-bottom: 20px;
    }
    .panel-head {
      padding: 14px 20px; border-bottom: 1px solid #f1f5f9;
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
    }
    .panel-head h3 { font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
    .panel-body { padding: 0; overflow-x: auto; }

    /* TABLE */
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th {
      background: #f8fafc; padding: 10px 16px;
      text-align: left; font-size: 11px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .04em; color: #6b7280;
      border-bottom: 1px solid #f1f5f9; white-space: nowrap;
    }
    tbody td { padding: 11px 16px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #fafbfc; }

    /* BADGES */
    .badge {
      display: inline-block; font-size: 10px; font-weight: 700;
      padding: 2px 8px; border-radius: 20px; text-transform: uppercase;
    }
    .badge-admin  { background: #fef9c3; color: #854d0e; }
    .badge-seller { background: #dbeafe; color: #1e40af; }
    .badge-buyer  { background: #f1f5f9; color: #374151; }
    .badge-active    { background: #dcfce7; color: #166534; }
    .badge-suspended { background: #fee2e2; color: #991b1b; }
    .badge-processing{ background: #dbeafe; color: #1e40af; }
    .badge-shipped   { background: #fef9c3; color: #854d0e; }
    .badge-delivered { background: #dcfce7; color: #166534; }
    .badge-fixed     { background: #e0f2fe; color: #0369a1; }
    .badge-auction   { background: #fef9c3; color: #854d0e; }
    .badge-approved  { background: #dcfce7; color: #166534; }
    .badge-pending   { background: #fef3c7; color: #d97706; }

    /* PRODUCT IMG */
    .prod-img { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; }

    /* FORM */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; padding: 20px; }
    .form-full { grid-column: 1 / -1; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .form-input, .form-select {
      width: 100%; padding: 8px 12px;
      border: 1px solid #d1d5db; border-radius: 6px;
      font-size: 13px; outline: none;
    }
    .form-input:focus, .form-select:focus { border-color: #f0c14b; }

    /* SEARCH INPUT */
    .search-input {
      padding: 7px 12px; border: 1px solid #e2e8f0; border-radius: 6px;
      font-size: 12px; outline: none; width: 220px;
    }
    .search-input:focus { border-color: #f0c14b; }

    /* Empty state */
    .empty-row td { text-align: center; padding: 40px; color: #9ca3af; }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .form-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="admin-wrap">

  <!-- ============================================================
       SIDEBAR
       ============================================================ -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="name">Crown<span>Mart</span> <span class="badge">Admin</span></div>
      <div><span class="admin-pill">👑 Admin Panel</span></div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <button class="nav-item active" onclick="showSection('dashboard',this)">
        <span class="icon">📊</span> Dashboard
      </button>

      <div class="nav-section-label">Marketplace</div>
      <button class="nav-item" onclick="showSection('products',this)">
        <span class="icon">📦</span> Products
      </button>
      <button class="nav-item" onclick="showSection('orders',this)">
        <span class="icon">🚚</span> Orders
      </button>
      <button class="nav-item" onclick="showSection('bids',this)">
        <span class="icon">🔨</span> Bids & Auctions
      </button>

      <div class="nav-section-label">Users</div>
      <button class="nav-item" onclick="showSection('users',this)">
        <span class="icon">👥</span> All Users
      </button>
      <button class="nav-item" onclick="showSection('sellers',this)">
        <span class="icon">🏪</span> Sellers
      </button>

      <div class="nav-section-label">Settings</div>
      <button class="nav-item" onclick="showSection('reports',this)">
        <span class="icon">📈</span> Sales Reports
      </button>
      <button class="nav-item" onclick="window.location.href='../index.php'">
        <span class="icon">🛒</span> View Marketplace
      </button>
    </nav>

    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar"><?= $admin['avatar'] ?></div>
        <div>
          <div class="admin-name"><?= htmlspecialchars($admin['name']) ?></div>
          <div class="admin-role">Administrator</div>
        </div>
      </div>
      <button class="btn-logout" onclick="doLogout()">🚪 Logout</button>
    </div>
  </aside>

  <!-- ============================================================
       MAIN CONTENT
       ============================================================ -->
  <div class="admin-main">

    <!-- Top Bar -->
    <div class="admin-topbar">
      <div>
        <div class="topbar-title" id="topbar-title">📊 Dashboard Overview</div>
        <div class="topbar-subtitle" id="topbar-sub">CrownMart Marketplace Administration</div>
      </div>
      <div class="topbar-actions">
        <span style="font-size:12px;color:#6b7280"><?= date('D, d M Y H:i') ?></span>
        <a href="../index.php" class="btn-sm btn-info" style="text-decoration:none">🛒 Marketplace</a>
      </div>
    </div>

    <div class="admin-content">

      <!-- ======================================================
           SECTION: DASHBOARD
           ====================================================== -->
      <div class="section active" id="section-dashboard">

        <!-- Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card green">
            <div class="stat-icon">💰</div>
            <div class="stat-num">$<?= number_format($stats['revenue'], 2) ?></div>
            <div class="stat-label">Total Revenue</div>
          </div>
          <div class="stat-card blue">
            <div class="stat-icon">🚚</div>
            <div class="stat-num"><?= $stats['orders'] ?></div>
            <div class="stat-label">Total Orders</div>
          </div>
          <div class="stat-card yellow">
            <div class="stat-icon">📦</div>
            <div class="stat-num"><?= $stats['products'] ?></div>
            <div class="stat-label">Active Products</div>
          </div>
          <div class="stat-card purple">
            <div class="stat-icon">👥</div>
            <div class="stat-num"><?= $stats['users'] ?></div>
            <div class="stat-label">Registered Users</div>
          </div>
          <div class="stat-card orange">
            <div class="stat-icon">🏪</div>
            <div class="stat-num"><?= $stats['sellers'] ?></div>
            <div class="stat-label">Active Sellers</div>
          </div>
          <div class="stat-card red">
            <div class="stat-icon">🔨</div>
            <div class="stat-num"><?= $stats['bids'] ?></div>
            <div class="stat-label">Total Bids Placed</div>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="panel">
          <div class="panel-head">
            <h3>🚚 Recent Orders</h3>
            <button class="btn-sm btn-info" onclick="showSection('orders',null)">View All</button>
          </div>
          <div class="panel-body">
            <table>
              <thead><tr>
                <th>Order ID</th><th>Customer</th><th>Total</th>
                <th>Status</th><th>Date</th><th>Actions</th>
              </tr></thead>
              <tbody>
                <?php if (empty($recentOrders)): ?>
                <tr class="empty-row"><td colspan="6">No orders yet.</td></tr>
                <?php else: ?>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                  <td><code style="font-size:11px">#<?= htmlspecialchars($o['id']) ?></code></td>
                  <td><strong><?= htmlspecialchars($o['user_name'] ?? 'Guest') ?></strong></td>
                  <td style="font-family:monospace;font-weight:700">$<?= number_format($o['total'],2) ?></td>
                  <td><span class="badge badge-<?= strtolower(str_replace(' ','',$o['status'])) ?>"><?= $o['status'] ?></span></td>
                  <td style="color:#9ca3af;font-size:12px"><?= $o['date_str'] ?></td>
                  <td>
                    <select class="form-select" style="font-size:11px;padding:3px 6px;width:auto"
                            onchange="updateOrderStatus('<?= $o['id'] ?>',this.value)">
                      <?php foreach (['Processing','Shipped','Out for Delivery','Delivered'] as $s): ?>
                        <option <?= $o['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Users -->
        <div class="panel">
          <div class="panel-head">
            <h3>👥 Recent Users</h3>
            <button class="btn-sm btn-info" onclick="showSection('users',null)">View All</button>
          </div>
          <div class="panel-body">
            <table>
              <thead><tr>
                <th>User</th><th>Email</th><th>Role</th><th>Balance</th><th>Joined</th><th>Status</th>
              </tr></thead>
              <tbody>
                <?php foreach ($recentUsers as $u): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($u['avatar'] ?? '👤') ?> <?= htmlspecialchars($u['name']) ?></strong>
                    <div style="font-size:11px;color:#9ca3af">@<?= htmlspecialchars($u['username'] ?? '—') ?></div>
                  </td>
                  <td style="font-size:12px"><?= htmlspecialchars($u['email']) ?></td>
                  <td><span class="badge badge-<?= $u['role'] ?? 'buyer' ?>"><?= ucfirst($u['role'] ?? 'buyer') ?></span></td>
                  <td style="font-family:monospace;font-size:12px">$<?= number_format($u['balance'],2) ?></td>
                  <td style="color:#9ca3af;font-size:11px"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                  <td><span class="badge badge-<?= $u['status'] ?? 'active' ?>"><?= ucfirst($u['status'] ?? 'active') ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div><!-- /dashboard -->

      <!-- ======================================================
           SECTION: PRODUCTS
           ====================================================== -->
      <div class="section" id="section-products">
        <div class="panel">
          <div class="panel-head">
            <h3>📦 All Products</h3>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" class="search-input" id="prod-search" placeholder="Search products..." oninput="filterTable('prod-table',this.value)">
            </div>
          </div>
          <div class="panel-body">
            <table id="prod-table">
              <thead><tr>
                <th>Product</th><th>Category</th><th>Type</th><th>Price</th>
                <th>Stock</th><th>Rating</th><th>Seller</th><th>Actions</th>
              </tr></thead>
              <tbody>
                <?php
                $products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
                foreach ($products as $p):
                ?>
                <tr>
                  <td style="max-width:220px">
                    <div style="display:flex;align-items:center;gap:10px">
                      <img class="prod-img" src="<?= htmlspecialchars($p['image']) ?>" referrerpolicy="no-referrer">
                      <span style="font-size:12px;font-weight:600;line-height:1.3"><?= htmlspecialchars($p['title']) ?></span>
                    </div>
                  </td>
                  <td style="font-size:12px"><?= htmlspecialchars($p['category']) ?></td>
                  <td><span class="badge badge-<?= $p['type'] ?>"><?= ucfirst($p['type']) ?></span></td>
                  <td style="font-family:monospace;font-weight:700">$<?= number_format($p['price'],2) ?></td>
                  <td style="text-align:center"><?= $p['stock'] ?? '—' ?></td>
                  <td>⭐ <?= $p['rating'] ?></td>
                  <td style="font-size:11px;color:#6b7280"><?= htmlspecialchars($p['seller_name']) ?></td>
                  <td>
                    <button class="btn-sm btn-danger" onclick="deleteProduct('<?= $p['id'] ?>',this)">🗑 Delete</button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div><!-- /products -->

      <!-- ======================================================
           SECTION: ORDERS
           ====================================================== -->
      <div class="section" id="section-orders">
        <div class="panel">
          <div class="panel-head">
            <h3>🚚 All Orders</h3>
            <input type="text" class="search-input" placeholder="Search..." oninput="filterTable('orders-table',this.value)">
          </div>
          <div class="panel-body">
            <table id="orders-table">
              <thead><tr>
                <th>Order ID</th><th>Customer</th><th>Total</th><th>Items</th>
                <th>Address</th><th>Status</th><th>Date</th><th>Update Status</th>
              </tr></thead>
              <tbody>
                <?php
                $allOrders = $pdo->query('
                  SELECT o.*, u.name as uname, u.email as uemail,
                         COUNT(oi.id) as item_count
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id
                  LEFT JOIN order_items oi ON oi.order_id = o.id
                  GROUP BY o.id
                  ORDER BY o.created_at DESC
                ')->fetchAll();
                foreach ($allOrders as $o):
                ?>
                <tr>
                  <td><code style="font-size:11px">#<?= htmlspecialchars($o['id']) ?></code></td>
                  <td>
                    <strong><?= htmlspecialchars($o['uname'] ?? 'Guest') ?></strong>
                    <div style="font-size:10px;color:#9ca3af"><?= htmlspecialchars($o['uemail'] ?? '') ?></div>
                  </td>
                  <td style="font-family:monospace;font-weight:700;color:#059669">$<?= number_format($o['total'],2) ?></td>
                  <td style="text-align:center"><?= $o['item_count'] ?> item(s)</td>
                  <td style="font-size:11px;color:#6b7280;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    <?= htmlspecialchars($o['address']) ?>
                  </td>
                  <td><span class="badge badge-<?= strtolower(str_replace(' ','',$o['status'])) ?>"><?= $o['status'] ?></span></td>
                  <td style="font-size:11px;color:#9ca3af;white-space:nowrap"><?= $o['date_str'] ?></td>
                  <td>
                    <select class="form-select" style="font-size:11px;padding:3px 6px;width:auto"
                            onchange="updateOrderStatus('<?= $o['id'] ?>',this.value)">
                      <?php foreach (['Processing','Shipped','Out for Delivery','Delivered'] as $s): ?>
                        <option <?= $o['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div><!-- /orders -->

      <!-- ======================================================
           SECTION: BIDS
           ====================================================== -->
      <div class="section" id="section-bids">
        <div class="panel">
          <div class="panel-head">
            <h3>🔨 All Bids & Auctions</h3>
            <input type="text" class="search-input" placeholder="Search..." oninput="filterTable('bids-table',this.value)">
          </div>
          <div class="panel-body">
            <table id="bids-table">
              <thead><tr>
                <th>Product</th><th>Bidder</th><th>Amount</th><th>Time</th>
              </tr></thead>
              <tbody>
                <?php
                $allBids = $pdo->query('
                  SELECT b.*, p.title as ptitle, p.image as pimage
                  FROM bids b
                  LEFT JOIN products p ON b.product_id = p.id
                  ORDER BY b.timestamp DESC
                ')->fetchAll();
                foreach ($allBids as $b):
                ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:8px">
                      <img class="prod-img" src="<?= htmlspecialchars($b['pimage'] ?? '') ?>" referrerpolicy="no-referrer">
                      <span style="font-size:12px;font-weight:600"><?= htmlspecialchars($b['ptitle'] ?? $b['product_id']) ?></span>
                    </div>
                  </td>
                  <td style="font-weight:600"><?= htmlspecialchars($b['bidder']) ?></td>
                  <td style="font-family:monospace;font-weight:800;color:#059669">$<?= number_format($b['amount'],2) ?></td>
                  <td style="font-size:11px;color:#9ca3af"><?= date('d M Y H:i', intval($b['timestamp']/1000)) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div><!-- /bids -->

      <!-- ======================================================
           SECTION: USERS
           ====================================================== -->
      <div class="section" id="section-users">
        <div class="panel">
          <div class="panel-head">
            <h3>👥 All Users</h3>
            <input type="text" class="search-input" placeholder="Search users..." oninput="filterTable('users-table',this.value)">
          </div>
          <div class="panel-body">
            <table id="users-table">
              <thead><tr>
                <th>User</th><th>Email</th><th>Username</th><th>Role</th>
                <th>Balance</th><th>Joined</th><th>Status</th><th>Actions</th>
              </tr></thead>
              <tbody>
                <?php
                $allUsers = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
                foreach ($allUsers as $u):
                ?>
                <tr id="user-row-<?= $u['id'] ?>">
                  <td>
                    <div style="display:flex;align-items:center;gap:8px">
                      <span style="font-size:22px"><?= htmlspecialchars($u['avatar'] ?? '👤') ?></span>
                      <div>
                        <div style="font-weight:700;font-size:13px"><?= htmlspecialchars($u['name']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td style="font-size:12px"><?= htmlspecialchars($u['email']) ?></td>
                  <td style="font-family:monospace;font-size:12px">@<?= htmlspecialchars($u['username'] ?? '—') ?></td>
                  <td>
                    <select class="form-select" style="font-size:11px;padding:3px 6px;width:auto"
                            onchange="updateUserRole(<?= $u['id'] ?>,this.value)">
                      <?php foreach (['buyer','seller','admin'] as $r): ?>
                        <option <?= ($u['role'] ?? 'buyer') === $r ? 'selected' : '' ?>><?= $r ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td style="font-family:monospace;font-size:12px">$<?= number_format($u['balance'],2) ?></td>
                  <td style="font-size:11px;color:#9ca3af"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                  <td>
                    <span class="badge badge-<?= $u['status'] ?? 'active' ?>" id="status-badge-<?= $u['id'] ?>">
                      <?= ucfirst($u['status'] ?? 'active') ?>
                    </span>
                  </td>
                  <td style="white-space:nowrap">
                    <?php if (($u['status'] ?? 'active') === 'active' && $u['role'] !== 'admin'): ?>
                      <button class="btn-sm btn-danger" onclick="toggleUserStatus(<?= $u['id'] ?>,'suspended')">🚫 Suspend</button>
                    <?php elseif ($u['role'] !== 'admin'): ?>
                      <button class="btn-sm btn-primary" style="background:#dcfce7;border-color:#86efac;color:#166534" onclick="toggleUserStatus(<?= $u['id'] ?>,'active')">✅ Activate</button>
                    <?php else: ?>
                      <span style="font-size:11px;color:#9ca3af">Protected</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div><!-- /users -->

      <!-- ======================================================
           SECTION: SELLERS
           ====================================================== -->
      <div class="section" id="section-sellers">
        <div class="panel">
          <div class="panel-head">
            <h3>🏪 Seller Accounts</h3>
          </div>
          <div class="panel-body">
            <table>
              <thead><tr>
                <th>Seller</th><th>Email</th><th>Balance</th><th>Products</th><th>Status</th><th>Actions</th>
              </tr></thead>
              <tbody>
                <?php
                $sellers = $pdo->query('SELECT * FROM users WHERE role IN ("seller","admin") ORDER BY created_at DESC')->fetchAll();
                foreach ($sellers as $u):
                  $pcount = $pdo->prepare('SELECT COUNT(*) FROM products WHERE seller_name LIKE ?');
                  $pcount->execute(['%' . $u['name'] . '%']);
                  $prodCount = $pcount->fetchColumn();
                ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($u['avatar'] ?? '🏪') ?> <?= htmlspecialchars($u['name']) ?></strong>
                    <div style="font-size:11px;color:#9ca3af">@<?= htmlspecialchars($u['username'] ?? '—') ?></div>
                  </td>
                  <td style="font-size:12px"><?= htmlspecialchars($u['email']) ?></td>
                  <td style="font-family:monospace">$<?= number_format($u['balance'],2) ?></td>
                  <td style="text-align:center;font-weight:700"><?= $prodCount ?></td>
                  <td><span class="badge badge-<?= $u['status'] ?? 'active' ?>"><?= ucfirst($u['status'] ?? 'active') ?></span></td>
                  <td>
                    <?php if ($u['role'] !== 'admin'): ?>
                    <button class="btn-sm btn-danger" onclick="toggleUserStatus(<?= $u['id'] ?>,'suspended')">🚫 Suspend</button>
                    <?php else: ?>
                    <span style="font-size:11px;color:#9ca3af">Protected</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div><!-- /sellers -->

      <!-- ======================================================
           SECTION: SALES REPORTS
           ====================================================== -->
      <div class="section" id="section-reports">

        <!-- Summary Cards -->
        <div class="stats-grid" id="report-summary-grid">
          <div class="stat-card green"><div class="stat-icon">💰</div><div class="stat-num" id="rpt-revenue">—</div><div class="stat-label">Total Revenue</div></div>
          <div class="stat-card blue"><div class="stat-icon">🚚</div><div class="stat-num" id="rpt-orders">—</div><div class="stat-label">Total Orders</div></div>
          <div class="stat-card yellow"><div class="stat-icon">⏳</div><div class="stat-num" id="rpt-pending">—</div><div class="stat-label">Pending Orders</div></div>
          <div class="stat-card purple"><div class="stat-icon">🏦</div><div class="stat-num" id="rpt-card">—</div><div class="stat-label">Transfer Orders</div></div>
          <div class="stat-card orange"><div class="stat-icon">🚚</div><div class="stat-num" id="rpt-cod">—</div><div class="stat-label">COD Orders</div></div>
          <div class="stat-card red"><div class="stat-icon">💳</div><div class="stat-num" id="rpt-wallet">—</div><div class="stat-label">Wallet Orders</div></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

          <!-- Revenue Chart -->
          <div class="panel">
            <div class="panel-head">
              <h3>📈 Daily Revenue (30 days)</h3>
              <button class="btn-sm btn-primary" onclick="exportCSV()">⬇ Export CSV</button>
            </div>
            <div style="padding:16px">
              <canvas id="revenue-chart" height="200"></canvas>
            </div>
          </div>

          <!-- Payment Methods Pie -->
          <div class="panel">
            <div class="panel-head"><h3>💳 Payment Methods</h3></div>
            <div style="padding:16px">
              <canvas id="payment-chart" height="200"></canvas>
            </div>
          </div>

        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

          <!-- Category Revenue -->
          <div class="panel">
            <div class="panel-head"><h3>📦 Revenue by Category</h3></div>
            <div class="panel-body">
              <table id="cat-table">
                <thead><tr><th>Category</th><th>Items Sold</th><th>Revenue</th></tr></thead>
                <tbody id="cat-tbody"></tbody>
              </table>
            </div>
          </div>

          <!-- Top Products -->
          <div class="panel">
            <div class="panel-head"><h3>🏆 Top Selling Products</h3></div>
            <div class="panel-body">
              <table>
                <thead><tr><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead>
                <tbody id="top-products-tbody"></tbody>
              </table>
            </div>
          </div>

        </div>

        <!-- Full Order History Table -->
        <div class="panel" style="margin-top:20px">
          <div class="panel-head">
            <h3>📋 Full Transaction History</h3>
            <div style="display:flex;gap:8px;align-items:center">
              <select id="filter-payment" class="form-select" style="width:auto;padding:5px 10px;font-size:12px" onchange="filterOrders()">
                <option value="">All Methods</option>
                <option value="wallet">Wallet</option>
                <option value="card">Card</option>
                <option value="cod">COD</option>
              </select>
              <select id="filter-status" class="form-select" style="width:auto;padding:5px 10px;font-size:12px" onchange="filterOrders()">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
              </select>
              <button class="btn-sm btn-primary" onclick="exportCSV()">⬇ Export CSV</button>
            </div>
          </div>
          <div class="panel-body">
            <table id="full-order-table">
              <thead><tr>
                <th>Order ID</th><th>Customer</th><th>Total</th>
                <th>Method</th><th>Status</th><th>Date</th><th>Update Status</th>
              </tr></thead>
              <tbody id="full-order-tbody"></tbody>
            </table>
          </div>
        </div>

      </div><!-- /reports -->

    </div><!-- /admin-content -->
  </div><!-- /admin-main -->
</div><!-- /admin-wrap -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Navigation
function showSection(id, btn) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById('section-' + id).classList.add('active');

  document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  const titles = {
    dashboard: ['📊 Dashboard Overview',     'CrownMart Marketplace Administration'],
    products:  ['📦 Product Management',     'Manage marketplace listings'],
    orders:    ['🚚 Order Management',        'Track and update order statuses'],
    bids:      ['🔨 Bids & Auctions',         'Monitor all auction activity'],
    users:     ['👥 User Management',         'Manage buyer & seller accounts'],
    sellers:   ['🏪 Seller Management',       'Review and manage seller accounts'],
    reports:   ['📈 Sales Reports',           'Revenue analytics & transaction history'],
  };
  if (titles[id]) {
    document.getElementById('topbar-title').textContent = titles[id][0];
    document.getElementById('topbar-sub').textContent   = titles[id][1];
  }

  // Load report data when opening reports
  if (id === 'reports') loadReports();
}

// Toast
function toast(msg, ok = true) {
  const el = document.getElementById('admin-toast');
  el.textContent = (ok ? '✅ ' : '❌ ') + msg;
  el.style.borderLeft = `4px solid ${ok ? '#10b981' : '#ef4444'}`;
  el.style.display = 'block';
  setTimeout(() => { el.style.display = 'none'; }, 3500);
}

// Filter table rows
function filterTable(tableId, query) {
  const q = query.toLowerCase();
  document.querySelectorAll(`#${tableId} tbody tr`).forEach(tr => {
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// Update order status
async function updateOrderStatus(orderId, status) {
  const res = await fetch('../api/admin.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'update_order_status', orderId, status})
  }).then(r => r.json());
  toast(res.error ? res.error : 'Order status updated!', !res.error);
}

// Update user role
async function updateUserRole(userId, role) {
  const res = await fetch('../api/admin.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'update_user_role', userId, role})
  }).then(r => r.json());
  toast(res.error ? res.error : 'Role updated!', !res.error);
}

// Suspend / activate user
async function toggleUserStatus(userId, status) {
  if (!confirm(`${status === 'suspended' ? 'Suspend' : 'Activate'} user ini?`)) return;
  const res = await fetch('../api/admin.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'update_user_status', userId, status})
  }).then(r => r.json());
  if (!res.error) {
    const badge = document.getElementById('status-badge-' + userId);
    if (badge) {
      badge.textContent = status === 'suspended' ? 'Suspended' : 'Active';
      badge.className = `badge badge-${status}`;
    }
  }
  toast(res.error ? res.error : 'User status updated!', !res.error);
}

// Delete product
async function deleteProduct(productId, btn) {
  if (!confirm('Hapus produk ini? Tindakan tidak bisa dibatalkan.')) return;
  const res = await fetch('../api/admin.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'delete_product', productId})
  }).then(r => r.json());
  if (!res.error) btn.closest('tr').remove();
  toast(res.error ? res.error : 'Product deleted!', !res.error);
}

// ============================================================
// REPORTS
// ============================================================
let revenueChart = null;
let paymentChart = null;
let reportData   = null;

async function loadReports() {
  const res = await fetch('../api/report.php').then(r => r.json());
  if (res.error) return toast(res.error, false);
  reportData = res;
  renderReportSummary(res.summary);
  renderRevenueChart(res.dailyRevenue);
  renderPaymentChart(res.paymentStats);
  renderCategoryTable(res.categoryStats);
  renderTopProducts(res.topProducts);
  renderFullOrderTable();
}

function renderReportSummary(s) {
  const fmt = n => '$' + parseFloat(n||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
  document.getElementById('rpt-revenue').textContent  = fmt(s.total_revenue);
  document.getElementById('rpt-orders').textContent   = s.total_orders;
  document.getElementById('rpt-pending').textContent  = s.pending_orders;
  document.getElementById('rpt-card').textContent = s.card_orders;
  document.getElementById('rpt-cod').textContent      = s.cod_orders;
  document.getElementById('rpt-wallet').textContent   = s.wallet_orders;
}

function renderRevenueChart(data) {
  const ctx = document.getElementById('revenue-chart').getContext('2d');
  if (revenueChart) revenueChart.destroy();
  revenueChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels:   data.map(d => d.date),
      datasets: [{
        label: 'Revenue ($)',
        data:  data.map(d => parseFloat(d.revenue||0)),
        backgroundColor: 'rgba(240,193,75,.7)',
        borderColor:     '#f0c14b',
        borderWidth: 1,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { callback: v => '$'+v } }
      }
    }
  });
}

function renderPaymentChart(data) {
  const ctx = document.getElementById('payment-chart').getContext('2d');
  if (paymentChart) paymentChart.destroy();

  const labels = data.map(d => ({wallet:'💳 Wallet',card:'💳 Card',cod:'🚚 COD'}[d.payment_method] || d.payment_method));
  const colors = ['#f0c14b','#3b82f6','#10b981'];

  paymentChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data:            data.map(d => parseInt(d.count)),
        backgroundColor: colors,
        borderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label}: ${ctx.raw} orders ($${parseFloat(data[ctx.dataIndex]?.total||0).toFixed(2)})`
          }
        }
      }
    }
  });
}

function renderCategoryTable(data) {
  const tbody = document.getElementById('cat-tbody');
  if (!tbody) return;
  tbody.innerHTML = data.map(d => `
    <tr>
      <td><strong>${d.category}</strong></td>
      <td style="text-align:center;font-weight:700">${d.total_sold}</td>
      <td style="font-family:monospace;font-weight:700;color:#059669">$${parseFloat(d.revenue||0).toFixed(2)}</td>
    </tr>`).join('') || '<tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:20px">No data yet</td></tr>';
}

function renderTopProducts(data) {
  const tbody = document.getElementById('top-products-tbody');
  if (!tbody) return;
  tbody.innerHTML = data.map((d,i) => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:8px">
          <span style="font-size:16px;font-weight:800;color:#9ca3af">#${i+1}</span>
          <img src="${d.image}" style="width:36px;height:36px;object-fit:cover;border-radius:4px" referrerpolicy="no-referrer">
          <span style="font-size:12px;font-weight:600">${d.title}</span>
        </div>
      </td>
      <td style="text-align:center;font-weight:700">${d.total_sold}</td>
      <td style="font-family:monospace;font-weight:700;color:#059669">$${parseFloat(d.revenue||0).toFixed(2)}</td>
    </tr>`).join('') || '<tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:20px">No data yet</td></tr>';
}

// Full order table with filter
function renderFullOrderTable() {
  const payFilter    = document.getElementById('filter-payment')?.value || '';
  const statusFilter = document.getElementById('filter-status')?.value  || '';
  const tbody = document.getElementById('full-order-tbody');
  if (!tbody) return;

  fetch(`../api/report.php?full=1`)
    .then(r => r.json())
    .then(res => {
      // We'll use the orders API instead
      fetch('../api/orders.php?all=1')
        .then(r => r.json())
        .catch(() => []);
    });

  // Reuse orders from the orders section via PHP-rendered data
  // Fetch all orders for admin
  fetch('../api/admin.php?action=get_orders')
    .then(r => r.json())
    .then(orders => {
      if (!Array.isArray(orders)) return;
      let filtered = orders;
      if (payFilter)    filtered = filtered.filter(o => o.payment_method === payFilter);
      if (statusFilter) filtered = filtered.filter(o => o.status === statusFilter);

      const methodLabel = {wallet:'💳 Wallet', card:'💳 Card', cod:'🚚 COD'};
      const statusColors = {
        Pending:    'badge-pending',
        Processing: 'badge-processing',
        Shipped:    'badge-shipped',
        Delivered:  'badge-delivered',
      };

      tbody.innerHTML = filtered.map(o => `
        <tr>
          <td><code style="font-size:11px">${o.id}</code></td>
          <td style="font-size:12px;font-weight:600">${o.user_name||'Guest'}</td>
          <td style="font-family:monospace;font-weight:700;color:#059669">$${parseFloat(o.total).toFixed(2)}</td>
          <td><span class="badge" style="font-size:10px">${methodLabel[o.payment_method]||o.payment_method}</span></td>
          <td><span class="badge ${statusColors[o.status]||'badge-pending'}">${o.status}</span></td>
          <td style="font-size:11px;color:#9ca3af">${o.date_str}</td>
          <td>
            <select class="form-select" style="font-size:11px;padding:3px 6px;width:auto"
                    onchange="updateOrderStatus('${o.id}',this.value)">
              ${['Pending','Processing','Shipped','Out for Delivery','Delivered']
                .map(s => `<option ${o.status===s?'selected':''}>${s}</option>`).join('')}
            </select>
          </td>
        </tr>`).join('') || '<tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:20px">No orders found</td></tr>';
    })
    .catch(() => {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:20px">Loading...</td></tr>';
    });
}

function filterOrders() { renderFullOrderTable(); }

// Export CSV
function exportCSV() {
  if (!reportData) return toast('Load reports first', false);

  let csv = 'Date,Orders,Revenue\n';
  reportData.dailyRevenue.forEach(d => {
    csv += `${d.date},${d.order_count},${parseFloat(d.revenue||0).toFixed(2)}\n`;
  });

  csv += '\nCategory,Items Sold,Revenue\n';
  reportData.categoryStats.forEach(d => {
    csv += `${d.category},${d.total_sold},${parseFloat(d.revenue||0).toFixed(2)}\n`;
  });

  csv += '\nPayment Method,Count,Total\n';
  reportData.paymentStats.forEach(d => {
    csv += `${d.payment_method},${d.count},${parseFloat(d.total||0).toFixed(2)}\n`;
  });

  const blob = new Blob([csv], {type:'text/csv'});
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = `crownmart-report-${new Date().toISOString().slice(0,10)}.csv`;
  a.click();
  toast('CSV exported!');
}

// Logout
async function doLogout() {
  await fetch('../api/auth.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'logout'})
  });
  window.location.href = '../login.php';
}
</script>

</body>
</html>
