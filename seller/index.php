<?php
require_once __DIR__ . '/../api/config.php';
$seller = requireLogin('../login.php');
if (!in_array($seller['role'], ['seller','admin'])) {
    header('Location: ../index.php?error=not_seller');
    exit;
}
$pdo = getDB();

// Produk milik seller
$stmt = $pdo->prepare('SELECT * FROM products WHERE user_id = ? OR seller_name LIKE ? ORDER BY created_at DESC');
$stmt->execute([$seller['id'], '%' . $seller['name'] . '%']);
$myProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CrownMart — Seller Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: #0f1111; }

    /* LAYOUT */
    .wrap { display: flex; min-height: 100vh; }

    /* SIDEBAR */
    .sidebar {
      width: 220px; flex-shrink: 0;
      background: #131921;
      display: flex; flex-direction: column;
      position: sticky; top: 0; height: 100vh; overflow-y: auto;
    }
    .sidebar-logo { padding: 18px 16px 14px; border-bottom: 1px solid #1e2d3d; }
    .logo-text { font-size: 19px; font-weight: 900; color: white; }
    .logo-text span { color: #f0c14b; text-decoration: underline; }
    .seller-pill {
      display: inline-block; margin-top: 6px;
      background: #dbeafe; color: #1e40af;
      font-size: 10px; font-weight: 800;
      padding: 2px 8px; border-radius: 20px; text-transform: uppercase;
    }

    .sidebar-nav { padding: 12px 10px; flex: 1; }
    .nav-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #475569; padding: 0 8px; margin: 12px 0 5px; }
    .nav-item {
      display: flex; align-items: center; gap: 9px;
      padding: 8px 10px; border-radius: 6px;
      color: #94a3b8; font-size: 13px; font-weight: 600;
      text-decoration: none; transition: all .15s; cursor: pointer;
      border: none; background: none; width: 100%; text-align: left;
    }
    .nav-item:hover { background: #1e2d3d; color: white; }
    .nav-item.active { background: #1e2d3d; color: #f0c14b; }
    .nav-item .icon { font-size: 15px; width: 20px; text-align: center; }

    .sidebar-footer { padding: 14px; border-top: 1px solid #1e2d3d; }
    .seller-profile { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .seller-name { font-size: 12px; font-weight: 700; color: white; }
    .seller-role { font-size: 10px; color: #64748b; }
    .btn-logout {
      width: 100%; padding: 7px; background: #1e2d3d; border: 1px solid #334155;
      color: #94a3b8; border-radius: 6px; font-size: 12px; font-weight: 600;
      cursor: pointer; transition: all .15s;
    }
    .btn-logout:hover { background: #dc2626; color: white; border-color: #dc2626; }

    /* MAIN */
    .main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

    .topbar {
      background: white; border-bottom: 1px solid #e2e8f0;
      padding: 12px 24px; display: flex; align-items: center;
      justify-content: space-between; gap: 12px; flex-wrap: wrap;
      position: sticky; top: 0; z-index: 10;
    }
    .topbar-title { font-size: 16px; font-weight: 800; }
    .topbar-sub { font-size: 12px; color: #9ca3af; margin-top: 1px; }

    .content { padding: 22px; flex: 1; }

    /* SECTIONS */
    .section { display: none; animation: fadeIn .2s ease; }
    .section.active { display: block; }
    @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

    /* STATS */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap: 14px; margin-bottom: 22px; }
    .stat-card {
      background: white; border: 1px solid #e2e8f0;
      border-radius: 10px; padding: 16px 18px;
      box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .stat-icon { font-size: 26px; margin-bottom: 6px; }
    .stat-num  { font-size: 24px; font-weight: 900; color: #0f1111; font-family: monospace; }
    .stat-label{ font-size: 11px; color: #6b7280; margin-top: 3px; font-weight: 600; }
    .stat-card.green  { border-left: 4px solid #10b981; }
    .stat-card.blue   { border-left: 4px solid #3b82f6; }
    .stat-card.yellow { border-left: 4px solid #f0c14b; }
    .stat-card.purple { border-left: 4px solid #8b5cf6; }
    .stat-card.orange { border-left: 4px solid #f97316; }
    .stat-card.red    { border-left: 4px solid #ef4444; }

    /* PANEL */
    .panel { background: white; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.05); margin-bottom: 18px; }
    .panel-head { padding: 12px 18px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
    .panel-head h3 { font-size: 13px; font-weight: 800; }
    .panel-body { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th { background: #f8fafc; padding: 9px 14px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
    tbody td { padding: 10px 14px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #fafbfc; }

    .badge { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; }
    .badge-pending    { background: #fef3c7; color: #d97706; }
    .badge-processing { background: #dbeafe; color: #1e40af; }
    .badge-shipped    { background: #fef9c3; color: #854d0e; }
    .badge-delivered  { background: #dcfce7; color: #166534; }
    .badge-fixed      { background: #e0f2fe; color: #0369a1; }
    .badge-auction    { background: #fef9c3; color: #854d0e; }

    .prod-img { width: 38px; height: 38px; object-fit: cover; border-radius: 5px; }

    /* FORM */
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .form-input, .form-select, .form-textarea {
      width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;
      font-size: 13px; outline: none; transition: border-color .2s; font-family: inherit;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #f0c14b; }
    .form-textarea { min-height: 80px; resize: vertical; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .btn-submit {
      background: #f0c14b; border: 1px solid #a88734; color: #0f1111;
      font-size: 13px; font-weight: 800; padding: 10px 20px;
      border-radius: 6px; cursor: pointer; transition: all .15s;
    }
    .btn-submit:hover { background: #f5d060; }
    .btn-danger-sm {
      background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b;
      font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 4px;
      cursor: pointer; transition: all .15s;
    }
    .btn-danger-sm:hover { background: #fecaca; }
    .btn-edit-sm {
      background: #dbeafe; border: 1px solid #93c5fd; color: #1e40af;
      font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 4px;
      cursor: pointer; transition: all .15s; margin-right: 4px;
    }

    /* TOAST */
    #seller-toast { display:none; position:fixed; bottom:20px; right:20px; z-index:999; background:#0f1111; color:white; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:600; box-shadow:0 8px 24px rgba(0,0,0,.3); }

    @media(max-width:768px) {
      .sidebar { display: none; }
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<div class="wrap">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-text">Crown<span>Mart</span></div>
      <div><span class="seller-pill">🏪 Seller Center</span></div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-label">Overview</div>
      <button class="nav-item active" onclick="showSec('dashboard',this)"><span class="icon">📊</span> Dashboard</button>

      <div class="nav-label">My Store</div>
      <button class="nav-item" onclick="showSec('products',this)"><span class="icon">📦</span> My Products</button>
      <button class="nav-item" onclick="showSec('add-product',this)"><span class="icon">➕</span> Add Product</button>
      <button class="nav-item" onclick="showSec('orders',this)"><span class="icon">🚚</span> My Orders</button>

      <div class="nav-label">Analytics</div>
      <button class="nav-item" onclick="showSec('reports',this)"><span class="icon">📈</span> Sales Reports</button>

      <div class="nav-label">Links</div>
      <button class="nav-item" onclick="window.location.href='../index.php'"><span class="icon">🛒</span> Marketplace</button>
    </nav>
    <div class="sidebar-footer">
      <div class="seller-profile">
        <span style="font-size:24px"><?= $seller['avatar'] ?></span>
        <div>
          <div class="seller-name"><?= htmlspecialchars($seller['name']) ?></div>
          <div class="seller-role"><?= ucfirst($seller['role']) ?></div>
        </div>
      </div>
      <button class="btn-logout" onclick="doLogout()">🚪 Logout</button>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title" id="topbar-title">📊 Seller Dashboard</div>
        <div class="topbar-sub" id="topbar-sub">Welcome back, <?= htmlspecialchars($seller['name']) ?></div>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <span style="font-size:12px;color:#6b7280"><?= date('D, d M Y') ?></span>
        <a href="../index.php" style="background:#dbeafe;border:1px solid #93c5fd;color:#1e40af;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none">🛒 View Store</a>
      </div>
    </div>

    <div class="content">

      <!-- ====== DASHBOARD ====== -->
      <div class="section active" id="sec-dashboard">
        <div class="stats-grid" id="dash-stats">
          <div class="stat-card green"><div class="stat-icon">💰</div><div class="stat-num" id="d-revenue">—</div><div class="stat-label">Total Revenue</div></div>
          <div class="stat-card blue"><div class="stat-icon">🚚</div><div class="stat-num" id="d-orders">—</div><div class="stat-label">Total Orders</div></div>
          <div class="stat-card yellow"><div class="stat-icon">📦</div><div class="stat-num" id="d-sold">—</div><div class="stat-label">Items Sold</div></div>
          <div class="stat-card purple"><div class="stat-icon">🏪</div><div class="stat-num" id="d-products">—</div><div class="stat-label">My Products</div></div>
          <div class="stat-card orange"><div class="stat-icon">⏳</div><div class="stat-num" id="d-pending">—</div><div class="stat-label">Pending Orders</div></div>
          <div class="stat-card red"><div class="stat-icon">✅</div><div class="stat-num" id="d-delivered">—</div><div class="stat-label">Delivered</div></div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px">
          <div class="panel">
            <div class="panel-head"><h3>📈 Revenue Last 30 Days</h3></div>
            <div style="padding:14px"><canvas id="dash-revenue-chart" height="180"></canvas></div>
          </div>
          <div class="panel">
            <div class="panel-head"><h3>💳 Payment Methods</h3></div>
            <div style="padding:14px"><canvas id="dash-payment-chart" height="180"></canvas></div>
          </div>
        </div>

        <div class="panel" style="margin-top:18px">
          <div class="panel-head"><h3>🕐 Recent Orders</h3><button class="btn-submit" style="font-size:11px;padding:5px 12px" onclick="showSec('orders',null)">View All</button></div>
          <div class="panel-body"><table><thead><tr><th>Order ID</th><th>Buyer</th><th>Total</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
          <tbody id="dash-recent-orders"></tbody></table></div>
        </div>
      </div>

      <!-- ====== MY PRODUCTS ====== -->
      <div class="section" id="sec-products">
        <div class="panel">
          <div class="panel-head">
            <h3>📦 My Products (<?= count($myProducts) ?>)</h3>
            <button class="btn-submit" style="font-size:11px;padding:5px 12px" onclick="showSec('add-product',null)">+ Add New</button>
          </div>
          <div class="panel-body">
            <table>
              <thead><tr><th>Product</th><th>Category</th><th>Type</th><th>Price</th><th>Stock</th><th>Rating</th><th>Actions</th></tr></thead>
              <tbody>
                <?php if (empty($myProducts)): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:#9ca3af">No products yet. <a href="#" onclick="showSec('add-product',null)" style="color:#f0c14b">Add your first product →</a></td></tr>
                <?php else: ?>
                <?php foreach ($myProducts as $p): ?>
                <tr id="prod-row-<?= $p['id'] ?>">
                  <td>
                    <div style="display:flex;align-items:center;gap:8px">
                      <img class="prod-img" src="<?= htmlspecialchars($p['image']) ?>" referrerpolicy="no-referrer">
                      <span style="font-size:12px;font-weight:600;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($p['title']) ?></span>
                    </div>
                  </td>
                  <td style="font-size:12px"><?= htmlspecialchars($p['category']) ?></td>
                  <td><span class="badge badge-<?= $p['type'] ?>"><?= ucfirst($p['type']) ?></span></td>
                  <td style="font-family:monospace;font-weight:700">$<?= number_format($p['price'],2) ?></td>
                  <td style="text-align:center"><?= $p['stock'] ?? '—' ?></td>
                  <td>⭐ <?= $p['rating'] ?></td>
                  <td style="white-space:nowrap">
                    <button class="btn-edit-sm" onclick="openEditModal('<?= $p['id'] ?>')">✏️ Edit</button>
                    <button class="btn-danger-sm" onclick="deleteMyProduct('<?= $p['id'] ?>',this)">🗑 Delete</button>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ====== ADD PRODUCT ====== -->
      <div class="section" id="sec-add-product">
        <div class="panel" style="max-width:700px">
          <div class="panel-head"><h3>➕ Add New Product</h3></div>
          <div style="padding:20px">
            <div id="add-alert" style="display:none;margin-bottom:14px;padding:10px 14px;border-radius:6px;font-size:13px;font-weight:600"></div>
            <form id="add-product-form" onsubmit="submitProduct(event)">
              <div class="form-group">
                <label>Product Title *</label>
                <input type="text" id="ap-title" class="form-input" placeholder="e.g. Sony WH-1000XM5 Headphones" required>
              </div>
              <div class="form-group">
                <label>Description *</label>
                <textarea id="ap-desc" class="form-textarea" placeholder="Detailed product description..." required></textarea>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Category *</label>
                  <select id="ap-category" class="form-select">
                    <option value="Electronics">Electronics</option>
                    <option value="Fashion">Fashion</option>
                    <option value="Home &amp; Kitchen">Home &amp; Kitchen</option>
                    <option value="Collectibles">Collectibles</option>
                    <option value="Sports &amp; Outdoors">Sports &amp; Outdoors</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Listing Type *</label>
                  <select id="ap-type" class="form-select" onchange="toggleAddFields()">
                    <option value="fixed">Buy It Now (Retail)</option>
                    <option value="auction">Live Auction</option>
                  </select>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label id="ap-price-label">Price ($) *</label>
                  <input type="number" id="ap-price" class="form-input" placeholder="0.00" min="0.01" step="0.01" style="font-family:monospace" required>
                </div>
                <div class="form-group" id="ap-stock-group">
                  <label>Stock Quantity</label>
                  <input type="number" id="ap-stock" class="form-input" value="10" min="1" style="font-family:monospace">
                </div>
                <div class="form-group" id="ap-hours-group" style="display:none">
                  <label>Auction Duration (hours)</label>
                  <select id="ap-hours" class="form-select">
                    <option value="1">1 Hour</option>
                    <option value="12">12 Hours</option>
                    <option value="24" selected>24 Hours</option>
                    <option value="72">72 Hours (3 Days)</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label>Shipping</label>
                <select id="ap-shipping" class="form-select">
                  <option value="free">Free Shipping</option>
                  <option value="standard">Standard Shipping</option>
                </select>
              </div>
              <button type="submit" class="btn-submit" style="width:100%;padding:11px;font-size:14px">🚀 Publish Product</button>
            </form>
          </div>
        </div>
      </div>

      <!-- ====== MY ORDERS ====== -->
      <div class="section" id="sec-orders">
        <div class="panel">
          <div class="panel-head">
            <h3>🚚 Orders Containing My Products</h3>
            <div style="display:flex;gap:8px">
              <select id="order-filter-status" class="form-select" style="width:auto;padding:5px 10px;font-size:12px" onchange="filterMyOrders()">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
              </select>
            </div>
          </div>
          <div class="panel-body">
            <table id="my-orders-table">
              <thead><tr><th>Order ID</th><th>Buyer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
              <tbody id="my-orders-tbody">
                <tr><td colspan="6" style="text-align:center;padding:30px;color:#9ca3af">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ====== REPORTS ====== -->
      <div class="section" id="sec-reports">
        <div class="stats-grid">
          <div class="stat-card green"><div class="stat-icon">💰</div><div class="stat-num" id="r-revenue">—</div><div class="stat-label">Total Revenue</div></div>
          <div class="stat-card blue"><div class="stat-icon">🛒</div><div class="stat-num" id="r-orders">—</div><div class="stat-label">Total Orders</div></div>
          <div class="stat-card yellow"><div class="stat-icon">📦</div><div class="stat-num" id="r-sold">—</div><div class="stat-label">Items Sold</div></div>
          <div class="stat-card purple"><div class="stat-icon">🏪</div><div class="stat-num" id="r-products">—</div><div class="stat-label">Active Products</div></div>
          <div class="stat-card orange"><div class="stat-icon">⏳</div><div class="stat-num" id="r-pending">—</div><div class="stat-label">Pending</div></div>
          <div class="stat-card red"><div class="stat-icon">✅</div><div class="stat-num" id="r-delivered">—</div><div class="stat-label">Delivered</div></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
          <div class="panel">
            <div class="panel-head">
              <h3>📈 Daily Revenue (30 days)</h3>
              <button class="btn-submit" style="font-size:11px;padding:5px 12px" onclick="exportSellerCSV()">⬇ Export CSV</button>
            </div>
            <div style="padding:14px"><canvas id="seller-revenue-chart" height="200"></canvas></div>
          </div>
          <div class="panel">
            <div class="panel-head"><h3>💳 Payment Methods</h3></div>
            <div style="padding:14px"><canvas id="seller-payment-chart" height="200"></canvas></div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3>🏆 Top Products by Sales</h3></div>
          <div class="panel-body">
            <table>
              <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Sold</th><th>Revenue</th></tr></thead>
              <tbody id="report-top-products"></tbody>
            </table>
          </div>
        </div>
      </div>

    </div><!-- /content -->
  </div><!-- /main -->
</div><!-- /wrap -->

<!-- Edit Modal -->
<div id="edit-modal" style="display:none;position:fixed;inset:0;z-index:400;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;padding:20px">
  <div style="background:white;border-radius:12px;width:100%;max-width:560px;overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,.3)">
    <div style="background:#131921;color:white;padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
      <h3 style="font-size:15px;font-weight:700">✏️ Edit Product</h3>
      <button onclick="closeEditModal()" style="background:none;border:none;color:white;font-size:20px;cursor:pointer">✕</button>
    </div>
    <div style="padding:20px">
      <div id="edit-alert" style="display:none;margin-bottom:12px;padding:8px 12px;border-radius:6px;font-size:13px;font-weight:600"></div>
      <input type="hidden" id="edit-id">
      <div class="form-group"><label>Title</label><input type="text" id="edit-title" class="form-input"></div>
      <div class="form-group"><label>Description</label><textarea id="edit-desc" class="form-textarea"></textarea></div>
      <div class="form-row">
        <div class="form-group"><label>Price ($)</label><input type="number" id="edit-price" class="form-input" step="0.01" style="font-family:monospace"></div>
        <div class="form-group"><label>Stock</label><input type="number" id="edit-stock" class="form-input" style="font-family:monospace"></div>
      </div>
      <div class="form-group">
        <label>Shipping</label>
        <select id="edit-shipping" class="form-select">
          <option value="free">Free Shipping</option>
          <option value="standard">Standard Shipping</option>
        </select>
      </div>
      <button onclick="saveEdit()" class="btn-submit" style="width:100%;padding:10px;font-size:14px;margin-top:4px">💾 Save Changes</button>
    </div>
  </div>
</div>

<div id="seller-toast"></div>

<script>
const SELLER_ID = <?= $seller['id'] ?>;
const BASE_URL  = '../';

let sellerData     = null;
let revChart       = null;
let payChart       = null;
let dashRevChart   = null;
let dashPayChart   = null;

// ============================================================
// NAVIGATION
// ============================================================
function showSec(id, btn) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById('sec-' + id).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  const map = {
    'dashboard':   ['📊 Seller Dashboard',   'Your store overview'],
    'products':    ['📦 My Products',         'Manage your listings'],
    'add-product': ['➕ Add Product',          'Create a new listing'],
    'orders':      ['🚚 My Orders',            'Orders containing your products'],
    'reports':     ['📈 Sales Reports',        'Revenue analytics'],
  };
  if (map[id]) {
    document.getElementById('topbar-title').textContent = map[id][0];
    document.getElementById('topbar-sub').textContent   = map[id][1];
  }

  if (id === 'dashboard' || id === 'reports') loadSellerReport(id);
  if (id === 'orders') renderOrdersTable();
}

// ============================================================
// LOAD REPORT DATA
// ============================================================
async function loadSellerReport(target) {
  const res = await fetch(BASE_URL + 'api/seller_report.php').then(r => r.json()).catch(() => null);
  if (!res || res.error) { toast(res?.error || 'Failed to load data', false); return; }
  sellerData = res;

  fillStats(res.summary, target);

  if (target === 'dashboard' || target === 'reports') {
    buildRevenueChart(res.dailyRevenue, target === 'dashboard' ? 'dash-revenue-chart' : 'seller-revenue-chart', target === 'dashboard' ? dashRevChart : revChart, c => { if(target==='dashboard') dashRevChart=c; else revChart=c; });
    buildPaymentChart(res.paymentStats, target === 'dashboard' ? 'dash-payment-chart' : 'seller-payment-chart', target === 'dashboard' ? dashPayChart : payChart, c => { if(target==='dashboard') dashPayChart=c; else payChart=c; });
  }

  if (target === 'dashboard') renderDashRecentOrders(res.recentOrders);
  if (target === 'reports')   renderTopProducts(res.topProducts);
}

function fillStats(s, target) {
  const fmt = n => '$' + parseFloat(n||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
  const prefix = target === 'dashboard' ? 'd-' : 'r-';
  const set = (id, val) => { const el=document.getElementById(id); if(el) el.textContent=val; };
  set(prefix+'revenue',  fmt(s.total_revenue));
  set(prefix+'orders',   s.total_orders);
  set(prefix+'sold',     s.total_sold);
  set(prefix+'products', s.products);
  set(prefix+'pending',  s.pending);
  set(prefix+'delivered',s.delivered);
}

function buildRevenueChart(data, canvasId, existing, setter) {
  const ctx = document.getElementById(canvasId)?.getContext('2d');
  if (!ctx) return;
  if (existing) existing.destroy();
  const chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels:   data.map(d => d.date),
      datasets: [{ label: 'Revenue ($)', data: data.map(d => parseFloat(d.revenue||0)),
        backgroundColor: 'rgba(240,193,75,.75)', borderColor: '#f0c14b', borderWidth:1, borderRadius:4 }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, ticks:{callback:v=>'$'+v}}} }
  });
  setter(chart);
}

function buildPaymentChart(data, canvasId, existing, setter) {
  const ctx = document.getElementById(canvasId)?.getContext('2d');
  if (!ctx) return;
  if (existing) existing.destroy();
  if (!data.length) return;
  const labels = data.map(d => ({wallet:'💳 Wallet',card:'💳 Card',cod:'🚚 COD'}[d.payment_method]||d.payment_method));
  const chart = new Chart(ctx, {
    type: 'doughnut',
    data: { labels, datasets:[{data:data.map(d=>parseInt(d.cnt)), backgroundColor:['#f0c14b','#3b82f6','#10b981'], borderWidth:2}] },
    options: { responsive:true, plugins:{legend:{position:'bottom'}} }
  });
  setter(chart);
}

function renderDashRecentOrders(orders) {
  const tbody = document.getElementById('dash-recent-orders');
  if (!tbody) return;
  const statusColors = {Pending:'badge-pending',Processing:'badge-processing',Shipped:'badge-shipped',Delivered:'badge-delivered'};
  const methodLabel  = {wallet:'💳',card:'💳',cod:'🚚'};
  tbody.innerHTML = orders.map(o => `
    <tr>
      <td><code style="font-size:11px">${o.id}</code></td>
      <td style="font-weight:600">${o.buyer_name||'Guest'}</td>
      <td style="font-family:monospace;font-weight:700;color:#059669">$${parseFloat(o.total).toFixed(2)}</td>
      <td>${methodLabel[o.payment_method]||''} ${o.payment_method}</td>
      <td><span class="badge ${statusColors[o.status]||'badge-pending'}">${o.status}</span></td>
      <td style="font-size:11px;color:#9ca3af">${o.date_str}</td>
    </tr>`).join('') || '<tr><td colspan="6" style="text-align:center;padding:20px;color:#9ca3af">No orders yet</td></tr>';
}

function renderTopProducts(products) {
  const tbody = document.getElementById('report-top-products');
  if (!tbody) return;
  tbody.innerHTML = products.map((p,i) => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:8px">
          <span style="font-size:16px;font-weight:800;color:#9ca3af">#${i+1}</span>
          <img src="${p.image}" class="prod-img" referrerpolicy="no-referrer">
          <span style="font-size:12px;font-weight:600">${p.title}</span>
        </div>
      </td>
      <td style="font-size:12px">${p.category}</td>
      <td style="font-family:monospace">$${parseFloat(p.price).toFixed(2)}</td>
      <td style="text-align:center;font-weight:700">${p.total_sold}</td>
      <td style="font-family:monospace;font-weight:700;color:#059669">$${parseFloat(p.revenue||0).toFixed(2)}</td>
    </tr>`).join('') || '<tr><td colspan="5" style="text-align:center;padding:20px;color:#9ca3af">No sales data yet</td></tr>';
}

// ============================================================
// ORDERS TABLE
// ============================================================
async function renderOrdersTable() {
  if (!sellerData) await loadSellerReport('orders');
  if (!sellerData) return;

  const statusFilter = document.getElementById('order-filter-status')?.value || '';
  const statusColors = {Pending:'badge-pending',Processing:'badge-processing',Shipped:'badge-shipped',Delivered:'badge-delivered'};
  const methodLabel  = {wallet:'💳 Wallet',card:'💳 Card',cod:'🚚 COD'};

  let orders = sellerData.recentOrders || [];
  if (statusFilter) orders = orders.filter(o => o.status === statusFilter);

  const tbody = document.getElementById('my-orders-tbody');
  tbody.innerHTML = orders.map(o => `
    <tr>
      <td><code style="font-size:11px">${o.id}</code></td>
      <td style="font-weight:600">${o.buyer_name||'Guest'}</td>
      <td style="font-family:monospace;font-weight:700;color:#059669">$${parseFloat(o.total).toFixed(2)}</td>
      <td>${methodLabel[o.payment_method]||o.payment_method}</td>
      <td><span class="badge ${statusColors[o.status]||'badge-pending'}">${o.status}</span></td>
      <td style="font-size:11px;color:#9ca3af">${o.date_str}</td>
    </tr>`).join('') || '<tr><td colspan="6" style="text-align:center;padding:30px;color:#9ca3af">No orders yet</td></tr>';
}

function filterMyOrders() { renderOrdersTable(); }

// ============================================================
// ADD PRODUCT
// ============================================================
function toggleAddFields() {
  const type = document.getElementById('ap-type').value;
  document.getElementById('ap-stock-group').style.display  = type === 'fixed'   ? '' : 'none';
  document.getElementById('ap-hours-group').style.display  = type === 'auction' ? '' : 'none';
  document.getElementById('ap-price-label').textContent    = type === 'fixed' ? 'Price ($) *' : 'Starting Bid ($) *';
}

async function submitProduct(e) {
  e.preventDefault();
  const type = document.getElementById('ap-type').value;
  const res = await fetch(BASE_URL + 'api/products.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      title:       document.getElementById('ap-title').value,
      description: document.getElementById('ap-desc').value,
      price:       document.getElementById('ap-price').value,
      category:    document.getElementById('ap-category').value,
      type,
      stock:       document.getElementById('ap-stock').value || '10',
      hours:       document.getElementById('ap-hours').value || '24',
      sellerName:  '<?= addslashes($seller['name']) ?>',
    })
  }).then(r => r.json());

  const alert = document.getElementById('add-alert');
  if (res.error) {
    alert.textContent = '❌ ' + res.error;
    alert.style.cssText = 'display:block;background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:10px 14px;border-radius:6px;font-size:13px;font-weight:600;margin-bottom:14px';
    return;
  }
  alert.textContent = '✅ Product published! Redirecting...';
  alert.style.cssText = 'display:block;background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:10px 14px;border-radius:6px;font-size:13px;font-weight:600;margin-bottom:14px';
  e.target.reset();
  setTimeout(() => { window.location.reload(); }, 1200);
}

// ============================================================
// EDIT PRODUCT
// ============================================================
async function openEditModal(productId) {
  const res = await fetch(BASE_URL + 'api/products.php?id=' + productId).then(r => r.json());
  if (res.error) return toast(res.error, false);

  document.getElementById('edit-id').value       = res.id;
  document.getElementById('edit-title').value    = res.title;
  document.getElementById('edit-desc').value     = res.description;
  document.getElementById('edit-price').value    = res.price;
  document.getElementById('edit-stock').value    = res.stock ?? '';
  document.getElementById('edit-shipping').value = res.shipping;
  document.getElementById('edit-alert').style.display = 'none';

  const modal = document.getElementById('edit-modal');
  modal.style.display = 'flex';
}

function closeEditModal() {
  document.getElementById('edit-modal').style.display = 'none';
}

async function saveEdit() {
  const id = document.getElementById('edit-id').value;
  const res = await fetch(BASE_URL + 'api/products.php', {
    method: 'PUT',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      id,
      title:       document.getElementById('edit-title').value,
      description: document.getElementById('edit-desc').value,
      price:       document.getElementById('edit-price').value,
      stock:       document.getElementById('edit-stock').value,
      shipping:    document.getElementById('edit-shipping').value,
    })
  }).then(r => r.json());

  const alert = document.getElementById('edit-alert');
  if (res.error) {
    alert.textContent = '❌ ' + res.error;
    alert.style.cssText = 'display:block;background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:8px 12px;border-radius:6px;font-size:13px;font-weight:600;margin-bottom:12px';
    return;
  }
  toast('Product updated!');
  closeEditModal();
  setTimeout(() => window.location.reload(), 800);
}

// ============================================================
// DELETE PRODUCT
// ============================================================
async function deleteMyProduct(productId, btn) {
  if (!confirm('Delete this product? This cannot be undone.')) return;
  const res = await fetch(BASE_URL + 'api/products.php', {
    method: 'DELETE',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ id: productId })
  }).then(r => r.json());
  if (!res.error) {
    document.getElementById('prod-row-' + productId)?.remove();
    toast('Product deleted!');
  } else {
    toast(res.error, false);
  }
}

// ============================================================
// EXPORT CSV
// ============================================================
function exportSellerCSV() {
  if (!sellerData) return toast('Load reports first', false);
  let csv = 'Date,Orders,Revenue\n';
  sellerData.dailyRevenue.forEach(d => { csv += `${d.date},${d.order_count},${parseFloat(d.revenue||0).toFixed(2)}\n`; });
  csv += '\nProduct,Category,Sold,Revenue\n';
  sellerData.topProducts.forEach(d => { csv += `"${d.title}",${d.category},${d.total_sold},${parseFloat(d.revenue||0).toFixed(2)}\n`; });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
  a.download = `seller-report-${new Date().toISOString().slice(0,10)}.csv`;
  a.click();
  toast('CSV exported!');
}

// ============================================================
// UTILS
// ============================================================
function toast(msg, ok = true) {
  const el = document.getElementById('seller-toast');
  el.textContent = (ok ? '✅ ' : '❌ ') + msg;
  el.style.cssText = `display:block;position:fixed;bottom:20px;right:20px;z-index:999;background:${ok?'#0f1111':'#dc2626'};color:white;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.3);border-left:4px solid ${ok?'#10b981':'#fca5a5'}`;
  setTimeout(() => { el.style.display = 'none'; }, 3500);
}

async function doLogout() {
  await fetch(BASE_URL + 'api/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'logout'}) });
  window.location.href = BASE_URL + 'login.php';
}

// Load dashboard on page open
document.addEventListener('DOMContentLoaded', () => loadSellerReport('dashboard'));
</script>
</body>
</html>
