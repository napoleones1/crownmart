<!DOCTYPE html>
<?php
require_once __DIR__ . '/api/config.php';
$sessionUser = getSessionUser();
$isLoggedIn  = $sessionUser !== null;
$isAdmin     = $isLoggedIn && $sessionUser['role'] === 'admin';
$isSeller    = $isLoggedIn && in_array($sessionUser['role'], ['seller','admin']);
?>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CrownMart — Hybrid Marketplace</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👑</text></svg>">
  <!-- Session data untuk JS (dibaca sebelum app.js) -->
  <div id="cm-session-data" style="display:none"
       data-user-id="<?= $isLoggedIn ? $sessionUser['id'] : 0 ?>"
       data-role="<?= $isLoggedIn ? htmlspecialchars($sessionUser['role']) : '' ?>"
       data-is-seller="<?= $isSeller ? '1' : '0' ?>">
  </div>
</head>
<body>

<!-- ============================================================
     ANNOUNCEMENT TICKER
     ============================================================ -->
<div class="ticker-bar">
  <span class="ticker-badge">MEMBER BENEFIT</span>
  <span>Get 10% Cash Back on all winning auction lots this week! Global fast shipping applies to all secure checkout transactions.</span>
</div>

<!-- ============================================================
     AUTH USER BAR
     ============================================================ -->
<div style="background:#0a1628;border-bottom:1px solid #1e293b;padding:5px 16px;font-size:12px">
  <div style="max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
    <span style="color:#64748b;font-size:11px">👑 CrownMart Hybrid Marketplace</span>
    <div style="display:flex;align-items:center;gap:10px">
      <?php if ($isLoggedIn): ?>
        <span style="color:#94a3b8;font-size:11px">
          <?= htmlspecialchars($sessionUser['avatar']) ?>
          <strong style="color:white"><?= htmlspecialchars($sessionUser['name']) ?></strong>
          <span style="background:<?= $isAdmin ? '#fef9c3' : ($isSeller ? '#dbeafe' : '#f1f5f9') ?>;
                       color:<?= $isAdmin ? '#854d0e' : ($isSeller ? '#1e40af' : '#374151') ?>;
                       padding:1px 7px;border-radius:10px;font-size:9px;font-weight:800;text-transform:uppercase;margin-left:4px">
            <?= ucfirst($sessionUser['role']) ?>
          </span>
        </span>
        <?php if ($isAdmin): ?>
          <a href="admin/index.php" style="color:#f0c14b;font-size:11px;font-weight:700;text-decoration:none;background:rgba(240,193,75,.1);border:1px solid rgba(240,193,75,.3);padding:3px 10px;border-radius:4px">
            📊 Admin Dashboard
          </a>
        <?php endif; ?>
        <button onclick="doLogout()" style="background:none;border:1px solid #334155;color:#94a3b8;padding:3px 10px;border-radius:4px;font-size:11px;cursor:pointer">
          Logout
        </button>
      <?php else: ?>
        <span style="color:#64748b;font-size:11px">Belum login</span>
        <a href="login.php" style="background:#f0c14b;color:#0f1111;font-weight:800;font-size:11px;padding:4px 12px;border-radius:4px;text-decoration:none">
          🔑 Login / Register
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============================================================
     TOP BAR
     ============================================================ -->
<div class="top-bar">
  <div class="top-bar-inner">
    <div class="top-bar-left">
      <span>📍 Deliver to <strong class="location-label" id="location-label">Detecting...</strong></span>
      <button class="gps-btn" id="gps-btn" onclick="detectLocation()">📍 GPS</button>
      <span class="location-text" style="color:#fbbf24;font-weight:600;font-size:11px">⚡ Weekend Lightning Deal: Bids Increment Extra 5%!</span>
    </div>
    <div class="top-bar-right">
      <select class="lang-select" id="lang-select" onchange="changeLang(this.value)">
        <option value="EN">🇺🇸 EN</option>
        <option value="ID">🇮🇩 ID</option>
        <option value="DE">🇩🇪 DE</option>
        <option value="JA">🇯🇵 JA</option>
      </select>
      <select class="currency-select" id="currency-select" onchange="changeCurrency(this.value)">
        <option value="USD">USD</option>
        <option value="IDR">IDR</option>
        <option value="EUR">EUR</option>
        <option value="GBP">GBP</option>
        <option value="JPY">JPY</option>
      </select>
      <div class="wallet-badge">
        💰 Wallet: <strong id="wallet-balance">$5,000.00</strong>
      </div>
      <button class="btn-topup" onclick="topUp(500)">+ $500 Free Cash</button>
      <button class="btn-topup green" onclick="topUp(2000)">+ $2,000 Premium</button>
    </div>
  </div>
</div>

<!-- ============================================================
     STICKY HEADER
     ============================================================ -->
<header class="site-header">
  <div class="header-inner">
    <!-- Row 1 -->
    <div class="header-row1">
      <!-- Logo -->
      <a href="#" class="logo" onclick="switchTab('home');return false;">
        Crown<span class="accent">Mart</span>
        <span class="badge">Hybrid</span>
      </a>

      <!-- Search -->
      <div class="search-bar">
        <select class="search-category" id="search-cat-select">
          <option value="All">All Categories</option>
          <option value="Electronics">Electronics</option>
          <option value="Fashion">Fashion</option>
          <option value="Home &amp; Kitchen">Home &amp; Kitchen</option>
          <option value="Collectibles">Collectibles</option>
          <option value="Sports &amp; Outdoors">Sports &amp; Outdoors</option>
        </select>
        <input type="text" class="search-input" id="search-input" placeholder="Search over 82,000 products or premium auctions...">
        <button class="search-btn" id="search-btn">🔍</button>
      </div>

      <!-- Cart -->
      <button class="cart-btn" onclick="openSidebar()">
        🛒 Cart
        <span class="count cart-count">0</span>
      </button>
    </div>

    <!-- Row 2: Nav Tabs -->
    <div class="nav-tabs">
      <div class="tabs-left">
        <button class="tab-btn active" data-tab="home" onclick="switchTab('home')">Marketplace Feed</button>
        <button class="tab-btn" data-tab="bids" onclick="switchTab('bids')">
          Watch List &amp; Bid Center
          <span class="tab-count" id="tab-watchlist-count">0</span>
        </button>
        <button class="tab-btn" data-tab="orders" onclick="switchTab('orders')">
          My Purchases
          <span class="tab-count" id="tab-orders-count"></span>
        </button>
      </div>
      <?php if ($isSeller): ?>
      <button class="btn-sell" onclick="switchTab('seller')">
        ➕ Sell an Item
      </button>
      <?php if ($isLoggedIn): ?>
      <a href="seller/index.php" style="
        display:inline-flex;align-items:center;gap:5px;
        background:#dbeafe;border:1px solid #93c5fd;color:#1e40af;
        padding:5px 10px;border-radius:4px;font-size:11px;font-weight:700;
        text-decoration:none;transition:all .15s;white-space:nowrap;
      ">📊 My Dashboard</a>
      <?php endif; ?>
      <?php else: ?>
      <button class="btn-sell" onclick="requireLoginPrompt('You need a Seller account to list products.','🏪')" style="opacity:.8">
        ➕ Sell an Item
      </button>
      <?php endif; ?>
    </div>

    <!-- Trust Bar -->
    <div class="trust-bar">
      <span>🏆 CrownMart Guarantee: Certified Merchant Partners</span>
      <span>🔒 CrownMart Protection: Secure Bid Escrows</span>
      <span>🚚 Standard 2-Day Air Shipping</span>
    </div>
  </div>
</header>

<!-- ============================================================
     HERO BANNER
     ============================================================ -->
<section class="hero" id="hero-section">
  <div class="hero-bg"></div>
  <div class="hero-glow"></div>
  <div class="hero-inner">
    <!-- Slide 1: Auctions -->
    <div class="hero-slide active" id="slide-0">
      <span class="hero-badge" style="background:#f59e0b;color:#0f1111">Live Auctions</span>
      <h1>CrownMart Auctions: Mints, Rarities &amp; Classics</h1>
      <p>Explore CrownMart-style mint condition collectibles, from original 1989 GameBoys to 1st Edition PSA Charizards. Slam the bid buttons to secure top placement before the countdown ends!</p>
      <div class="hero-btns">
        <button class="hero-btn primary" onclick="document.getElementById('fmt-auction').click()">Live Auctions</button>
        <button class="hero-btn secondary" onclick="STATE.filterCat='Collectibles';document.querySelector('.cat-btn[data-cat=Collectibles]').click()">Collectibles</button>
      </div>
    </div>
    <!-- Slide 2: Retail -->
    <div class="hero-slide" id="slide-1">
      <span class="hero-badge" style="background:#60a5fa;color:#0f1111">Buy It Now</span>
      <h1>CrownMart Direct: Super Express Retail Hub</h1>
      <p>Browse premium, high-fidelity daily consumer products from first-party merchants. Authentic items backed by secure multi-channel logistics, lightning-fast courier routing, and a 100% satisfaction guarantee.</p>
      <div class="hero-btns">
        <button class="hero-btn primary" style="background:#60a5fa" onclick="document.getElementById('fmt-fixed').click()">Buy It Now</button>
        <button class="hero-btn secondary" onclick="STATE.filterCat='Electronics';document.querySelector('.cat-btn[data-cat=Electronics]').click()">Electronics</button>
      </div>
    </div>
  </div>
  <!-- Dots -->
  <div class="hero-dots">
    <button class="hero-dot active" onclick="setSlide(0)"></button>
    <button class="hero-dot" onclick="setSlide(1)"></button>
  </div>
</section>

<!-- ============================================================
     MAIN CONTENT AREA
     ============================================================ -->
<main class="main-wrap">

  <!-- ========== TAB: HOME / MARKETPLACE ========== -->
  <div class="tab-content active" id="tab-home">

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div>
        <span class="filter-label">Quick Filters:</span>
        <div class="filter-cats">
          <?php
          $categories = ['All','Electronics','Fashion','Home & Kitchen','Collectibles','Sports & Outdoors'];
          foreach ($categories as $cat):
            $label = $cat === 'All' ? 'All Categories' : htmlspecialchars($cat);
          ?>
          <button class="cat-btn <?= $cat === 'All' ? 'active' : '' ?>"
                  data-cat="<?= htmlspecialchars($cat) ?>"
                  onclick="STATE.filterCat='<?= addslashes($cat) ?>';
                           document.querySelectorAll('.cat-btn').forEach(b=>b.classList.toggle('active',b.dataset.cat==='<?= addslashes($cat) ?>'));
                           document.getElementById('search-cat-select').value='<?= addslashes($cat) ?>';
                           loadProducts()">
            <?= $label ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="filter-right">
        <!-- Format Buttons -->
        <div class="format-btns">
          <button id="fmt-all"     class="fmt-btn active-all"  data-type="all"
                  onclick="STATE.filterType='all';document.querySelectorAll('.fmt-btn').forEach(b=>{b.className='fmt-btn'});this.classList.add('active-all');loadProducts()">
            All Items
          </button>
          <button id="fmt-fixed"   class="fmt-btn" data-type="fixed"
                  onclick="STATE.filterType='fixed';document.querySelectorAll('.fmt-btn').forEach(b=>{b.className='fmt-btn'});this.classList.add('active-fixed');loadProducts()">
            🏷 Buy Now
          </button>
          <button id="fmt-auction" class="fmt-btn" data-type="auction"
                  onclick="STATE.filterType='auction';document.querySelectorAll('.fmt-btn').forEach(b=>{b.className='fmt-btn'});this.classList.add('active-auction');loadProducts()">
            🔨 Auctions
          </button>
        </div>

        <!-- Sort -->
        <div class="sort-wrap">
          <label>Sort by:</label>
          <select class="sort-select" id="sort-select">
            <option value="relevance">Relevance</option>
            <option value="priceAsc">Price: Low to High</option>
            <option value="priceDesc">Price: High to Low</option>
            <option value="rating">Top Rated</option>
            <option value="endingSoon">Ending Soon</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Section Heading -->
    <div class="section-heading">
      <h2 id="section-cat-label">All Marketplace Catalog</h2>
      <span class="count" id="product-count"></span>
    </div>

    <!-- Product Grid -->
    <div class="product-grid" id="product-grid">
      <!-- Products rendered by JS -->
      <?php for ($i = 0; $i < 8; $i++): ?>
      <div class="product-card" style="pointer-events:none">
        <div class="card-img-wrap skeleton" style="height:180px"></div>
        <div class="card-body">
          <div class="skeleton" style="height:12px;width:60%;margin-bottom:8px"></div>
          <div class="skeleton" style="height:14px;width:90%;margin-bottom:6px"></div>
          <div class="skeleton" style="height:14px;width:70%;margin-bottom:10px"></div>
          <div class="skeleton" style="height:20px;width:40%;margin-bottom:10px"></div>
          <div class="skeleton" style="height:32px;width:100%"></div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div><!-- /tab-home -->

  <!-- ========== TAB: BIDS / WATCHLIST ========== -->
  <div class="tab-content" id="tab-bids">
    <div style="background:white;border:1px solid var(--border);border-radius:8px;padding:24px;box-shadow:var(--shadow);margin-bottom:20px">
      <div style="display:flex;align-items:center;gap:12px;border-bottom:1px solid #f1f5f9;padding-bottom:16px;margin-bottom:20px">
        <div style="background:#f0c14b;color:#0f1111;padding:8px;border-radius:8px;font-size:20px">🔨</div>
        <div>
          <h2 style="font-size:18px;font-weight:800;text-transform:uppercase">Watch List &amp; Bid Center</h2>
          <p style="font-size:12px;color:#9ca3af;margin-top:2px">Monitor auction lots, active bids, and outbid statuses in real time.</p>
        </div>
      </div>
      <div id="watchlist-content">
        <!-- Rendered by JS -->
      </div>
    </div>
  </div>

  <!-- ========== TAB: ORDERS ========== -->
  <div class="tab-content" id="tab-orders">
    <div class="section-heading">
      <h2>📋 Your CrownMart Orders Dashboard</h2>
    </div>
    <div class="orders-list" id="orders-list">
      <div class="empty-state">
        <div class="empty-icon">📋</div>
        <h3>No orders yet</h3>
        <p>Your order history will appear here after your first purchase.</p>
      </div>
    </div>
  </div>

  <!-- ========== TAB: SELLER ========== -->
  <div class="tab-content" id="tab-seller">
    <div class="section-heading">
      <h2>🏪 Professional Seller Center</h2>
    </div>
    <div class="seller-portal" id="seller-content">
      <!-- Rendered by JS -->
    </div>
  </div>

</main>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer style="background:#030712;color:#64748b;font-size:12px;padding:48px 16px 24px;margin-top:48px;border-top:1px solid #111827">
  <div style="max-width:1280px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:32px">
    <div>
      <h4 style="color:white;font-weight:700;font-size:13px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">
        Crown<span style="color:#f0c14b">Mart</span> Marketplace
      </h4>
      <p style="line-height:1.7;color:#64748b">
        A high-precision global unified e-commerce network merging seamless instant checkout operations and secure fast shipping with an exciting real-time bidding ledger system.
      </p>
    </div>
    <div>
      <h4 style="color:#f0c14b;font-weight:700;font-size:13px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;font-family:monospace">Helpful Services</h4>
      <ul style="list-style:none;line-height:2;color:#64748b;font-family:monospace;font-size:11px">
        <li>📦 Real-Time Order &amp; Transit Tracking</li>
        <li>🛡️ Multi-Channel Secure Escrow Protection</li>
        <li>🤝 Genuine First-Party Partner Verifications</li>
        <li>🌟 Secured Instant Balance Integrations</li>
      </ul>
    </div>
    <div>
      <h4 style="color:white;font-weight:700;font-size:13px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">Customer Guarantee</h4>
      <p style="line-height:1.7;color:#64748b">
        Every item is backed by our full transaction protection. Place live bids, compile shopping baskets, and preview checkout configurations with direct confidence.
      </p>
    </div>
  </div>
  <div style="max-width:1280px;margin:32px auto 0;padding-top:20px;border-top:1px solid #111827;text-align:center;color:#475569;font-family:monospace;font-size:11px">
    &copy; <?= date('Y') ?> CrownMart Hybrid Inc. All Rights Reserved.
  </div>
</footer>

<!-- ============================================================
     CART SIDEBAR
     ============================================================ -->
<div id="cart-sidebar" class="cart-sidebar">
  <div class="cart-head">
    <h3>🛒 Your CrownMart Cart</h3>
    <button class="cart-close" onclick="closeSidebar()">✕</button>
  </div>
  <div class="cart-items" id="cart-items">
    <div class="cart-empty">
      <div class="big-icon">🛒</div>
      <p>Your cart is empty</p>
    </div>
  </div>
  <div class="cart-footer" id="cart-footer">
    <div class="cart-total-row">
      <span>Total</span>
      <span id="cart-total">$0.00</span>
    </div>

    <!-- Alamat -->
    <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:4px">📍 Shipping Address</label>
    <input type="text" class="address-input" id="checkout-address"
           placeholder="Enter full shipping address...">

    <!-- Metode Pengiriman -->
    <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin:10px 0 4px">🚚 Shipping Method</label>
    <select id="shipping-method" style="width:100%;padding:7px 10px;border:1px solid var(--border);border-radius:4px;font-size:12px;margin-bottom:10px;outline:none">
      <option value="regular">Regular (3-5 days) — Free</option>
      <option value="express">Express (1-2 days) — $5</option>
      <option value="same_day">Same Day — $10</option>
    </select>

    <!-- Metode Pembayaran -->
    <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:6px">💳 Payment Method</label>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:10px" id="payment-method-picker">
      <label id="pay-wallet" onclick="selectPayment('wallet')"
             style="border:2px solid #f0c14b;background:#fffbeb;border-radius:6px;padding:8px 4px;text-align:center;cursor:pointer;transition:all .2s">
        <input type="radio" name="payMethod" value="wallet" checked style="display:none">
        <div style="font-size:18px">💰</div>
        <div style="font-size:10px;font-weight:700;color:#0f1111;margin-top:2px">Wallet</div>
        <div style="font-size:9px;color:#6b7280" id="wallet-bal-label">${fmt(STATE.user.balance)}</div>
      </label>
      <label id="pay-transfer" onclick="selectPayment('transfer')"
             style="border:2px solid #e5e7eb;background:#f9fafb;border-radius:6px;padding:8px 4px;text-align:center;cursor:pointer;transition:all .2s">
        <input type="radio" name="payMethod" value="transfer" style="display:none">
        <div style="font-size:18px">🏦</div>
        <div style="font-size:10px;font-weight:700;color:#0f1111;margin-top:2px">Transfer</div>
        <div style="font-size:9px;color:#6b7280">Bank BCA</div>
      </label>
      <label id="pay-cod" onclick="selectPayment('cod')"
             style="border:2px solid #e5e7eb;background:#f9fafb;border-radius:6px;padding:8px 4px;text-align:center;cursor:pointer;transition:all .2s">
        <input type="radio" name="payMethod" value="cod" style="display:none">
        <div style="font-size:18px">🚚</div>
        <div style="font-size:10px;font-weight:700;color:#0f1111;margin-top:2px">COD</div>
        <div style="font-size:9px;color:#6b7280">Bayar di tempat</div>
      </label>
    </div>

    <!-- Info Transfer Bank (muncul jika pilih transfer) -->
    <div id="transfer-info" style="display:none;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px;margin-bottom:10px;font-size:12px">
      <div style="font-weight:700;color:#1e40af;margin-bottom:4px">🏦 Transfer ke:</div>
      <div style="font-family:monospace;color:#1e3a8a">Bank BCA — 1234567890</div>
      <div style="color:#374151">a/n CrownMart Indonesia</div>
      <div style="color:#6b7280;font-size:11px;margin-top:4px">Pesanan akan diproses setelah pembayaran dikonfirmasi admin.</div>
    </div>

    <!-- Info COD -->
    <div id="cod-info" style="display:none;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:10px;margin-bottom:10px;font-size:12px">
      <div style="font-weight:700;color:#166534;margin-bottom:2px">🚚 Bayar di Tempat (COD)</div>
      <div style="color:#374151">Siapkan uang tunai saat kurir tiba. Pesanan akan diproses segera.</div>
    </div>

    <button class="btn-checkout" onclick="cartCheckout()">
      🔒 Confirm &amp; Place Order
    </button>
  </div>
</div>

<!-- Backdrop for cart -->
<div id="cart-backdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:250"
     onclick="closeSidebar()"></div>

<!-- ============================================================
     PRODUCT DETAIL MODAL
     ============================================================ -->
<div class="overlay" id="modal-overlay">
  <div class="modal" id="modal-content">
    <!-- Filled by JS -->
  </div>
</div>

<!-- ============================================================
     JAVASCRIPT
     ============================================================ -->
<!-- ============================================================
     LOGIN REQUIRED MODAL
     ============================================================ -->
<div id="login-modal-overlay" style="
  display:none; position:fixed; inset:0; z-index:600;
  background:rgba(0,0,0,.65); backdrop-filter:blur(4px);
  align-items:center; justify-content:center; padding:20px;
">
  <div id="login-modal" style="
    background:white; border-radius:16px; width:100%; max-width:400px;
    overflow:hidden; box-shadow:0 32px 80px rgba(0,0,0,.5);
    animation:slideUp .25s ease;
  ">
    <!-- Header -->
    <div style="background:#131921; padding:24px 28px 20px; text-align:center; position:relative;">
      <button onclick="closeLoginModal()" style="
        position:absolute; top:14px; right:14px;
        background:rgba(255,255,255,.1); border:none; color:white;
        width:28px; height:28px; border-radius:50%; font-size:16px;
        cursor:pointer; display:flex; align-items:center; justify-content:center;
      ">✕</button>
      <div style="font-size:32px; margin-bottom:8px;" id="modal-auth-icon">🔒</div>
      <div style="font-size:20px; font-weight:900; color:white; letter-spacing:-.3px;">
        Crown<span style="color:#f0c14b; text-decoration:underline;">Mart</span>
      </div>
      <p id="modal-auth-msg" style="color:#94a3b8; font-size:13px; margin-top:6px; line-height:1.5;">
        Sign in to continue
      </p>
    </div>

    <!-- Body -->
    <div style="padding:24px 28px 28px;">
      <div id="modal-auth-alert" style="
        display:none; padding:10px 14px; border-radius:6px;
        font-size:13px; font-weight:600; margin-bottom:14px;
      "></div>

      <div style="margin-bottom:14px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px;">Username or Email</label>
        <input type="text" id="modal-username" placeholder="Enter your username or email"
               style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px;
                      font-size:14px; outline:none; transition:border-color .2s;"
               onfocus="this.style.borderColor='#f0c14b'" onblur="this.style.borderColor='#d1d5db'">
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px;">Password</label>
        <input type="password" id="modal-password" placeholder="••••••••"
               style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px;
                      font-size:14px; outline:none; transition:border-color .2s;"
               onfocus="this.style.borderColor='#f0c14b'" onblur="this.style.borderColor='#d1d5db'"
               onkeydown="if(event.key==='Enter')doModalLogin()">
      </div>

      <button id="modal-login-btn" onclick="doModalLogin()" style="
        width:100%; padding:12px; background:#f0c14b; border:1px solid #a88734;
        color:#0f1111; font-size:14px; font-weight:800; border-radius:6px;
        cursor:pointer; transition:all .15s; margin-bottom:10px;
      ">Sign In</button>

      <button onclick="window.location.href='login.php?tab=register'" style="
        width:100%; padding:11px; background:white; border:1px solid #e5e7eb;
        color:#374151; font-size:13px; font-weight:600; border-radius:6px;
        cursor:pointer; transition:all .15s;
      ">Create New Account</button>

      <p style="text-align:center; font-size:11px; color:#9ca3af; margin-top:14px;">
        Browse freely — login only needed to buy, bid, or sell.
      </p>
    </div>
  </div>
</div>

<!-- Session inject SEBELUM app.js agar isLoggedIn() langsung benar -->
<script>
var CM_SESSION = {
  userId:   <?= $isLoggedIn ? (int)$sessionUser['id'] : 0 ?>,
  role:     "<?= $isLoggedIn ? htmlspecialchars($sessionUser['role']) : '' ?>",
  isSeller: <?= $isSeller ? 'true' : 'false' ?>,
  name:     "<?= $isLoggedIn ? addslashes($sessionUser['name']) : '' ?>"
};
</script>
<script src="js/app.js"></script>

<script>
// Set session data SEBELUM app.js init (dibaca dari meta tag)
// Ini memastikan isLoggedIn() bekerja saat DOM ready
<?php if ($isLoggedIn): ?>
if (typeof STATE !== 'undefined') {
  STATE._sessionUserId = <?= $sessionUser['id'] ?>;
}
<?php endif; ?>

// Extra: show/hide cart backdrop
const _origOpen  = openSidebar;
const _origClose = closeSidebar;
openSidebar  = () => { _origOpen();  document.getElementById('cart-backdrop').style.display='block'; };
closeSidebar = () => { _origClose(); document.getElementById('cart-backdrop').style.display='none'; };
async function doLogout() {
  await fetch('api/auth.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'logout'})
  });
  window.location.href = 'login.php';
}

// ============================================================
// LOGIN MODAL — muncul saat aksi butuh login
// ============================================================
let _pendingAction = null; // simpan aksi yang akan dijalankan setelah login

function showLoginModal(message, icon, pendingCallback) {
  _pendingAction = pendingCallback || null;
  document.getElementById('modal-auth-icon').textContent = icon || '🔒';
  document.getElementById('modal-auth-msg').textContent  = message || 'Please sign in to continue.';
  document.getElementById('modal-auth-alert').style.display = 'none';
  document.getElementById('modal-username').value = '';
  document.getElementById('modal-password').value = '';
  const overlay = document.getElementById('login-modal-overlay');
  overlay.style.display = 'flex';
  setTimeout(() => document.getElementById('modal-username').focus(), 100);
}

function closeLoginModal() {
  document.getElementById('login-modal-overlay').style.display = 'none';
  _pendingAction = null;
}

// Close on backdrop click
document.getElementById('login-modal-overlay').addEventListener('click', function(e) {
  if (e.target === this) closeLoginModal();
});

async function doModalLogin() {
  const btn  = document.getElementById('modal-login-btn');
  const user = document.getElementById('modal-username').value.trim();
  const pass = document.getElementById('modal-password').value;
  const alert = document.getElementById('modal-auth-alert');

  if (!user || !pass) {
    alert.textContent = 'Please enter your username and password.';
    alert.style.cssText = 'display:block; padding:10px 14px; border-radius:6px; font-size:13px; font-weight:600; margin-bottom:14px; background:#fef2f2; border:1px solid #fca5a5; color:#991b1b;';
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(0,0,0,.2);border-top-color:#0f1111;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:6px"></span> Signing in...';

  const res = await fetch('api/auth.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ action:'login', username:user, password:pass })
  }).then(r => r.json()).catch(() => ({ error:'Network error' }));

  btn.disabled = false;
  btn.textContent = 'Sign In';

  if (res.error) {
    alert.textContent = '❌ ' + res.error;
    alert.style.cssText = 'display:block; padding:10px 14px; border-radius:6px; font-size:13px; font-weight:600; margin-bottom:14px; background:#fef2f2; border:1px solid #fca5a5; color:#991b1b;';
    return;
  }

  // Login berhasil
  alert.textContent = '✅ ' + res.message;
  alert.style.cssText = 'display:block; padding:10px 14px; border-radius:6px; font-size:13px; font-weight:600; margin-bottom:14px; background:#f0fdf4; border:1px solid #86efac; color:#166534;';

  // Admin langsung ke dashboard
  if (res.user.role === 'admin') {
    setTimeout(() => { window.location.href = 'admin/index.php'; }, 700);
    return;
  }

  // Reload halaman agar session PHP aktif
  setTimeout(() => {
    if (_pendingAction) {
      // Setelah reload, aksi akan dieksekusi ulang — simpan ke sessionStorage
      sessionStorage.setItem('pendingAction', _pendingAction);
    }
    window.location.reload();
  }, 700);
}

// Jalankan pending action setelah reload (jika ada)
window.addEventListener('load', () => {
  const pending = sessionStorage.getItem('pendingAction');
  if (pending) {
    sessionStorage.removeItem('pendingAction');
    // Trigger aksi setelah halaman load
    setTimeout(() => {
      try { eval(pending); } catch(e) {}
    }, 500);
  }
});

function requireLoginPrompt(message, icon, pendingCallback) {
  showLoginModal(
    message || 'You need to sign in to perform this action.',
    icon || '🔒',
    pendingCallback
  );
}

<?php if ($isLoggedIn): ?>
STATE._sessionUserId = <?= $sessionUser['id'] ?>;
<?php endif; ?>
</script>

</body>
</html>
