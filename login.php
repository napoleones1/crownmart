<?php
require_once __DIR__ . '/api/config.php';
// Kalau sudah login, redirect ke halaman utama
if (getSessionUser()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CrownMart — Login / Register</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #0f1923 0%, #1a2535 50%, #0a1628 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .card {
      background: white;
      border-radius: 16px;
      width: 100%;
      max-width: 440px;
      overflow: hidden;
      box-shadow: 0 32px 80px rgba(0,0,0,.5);
    }
    .card-header {
      background: #131921;
      padding: 28px 32px 24px;
      text-align: center;
    }
    .logo {
      font-size: 26px;
      font-weight: 900;
      color: white;
      letter-spacing: -.5px;
    }
    .logo span { color: #f0c14b; text-decoration: underline; }
    .logo .badge {
      font-size: 9px;
      font-family: monospace;
      background: #0d1b2a;
      color: #f0c14b;
      padding: 2px 6px;
      border-radius: 3px;
      font-weight: 800;
      text-transform: uppercase;
      vertical-align: middle;
      margin-left: 6px;
    }
    .card-header p { color: #94a3b8; font-size: 13px; margin-top: 6px; }

    /* Tabs */
    .tabs { display: flex; border-bottom: 2px solid #f1f5f9; }
    .tab-btn {
      flex: 1; padding: 14px; font-size: 13px; font-weight: 700;
      background: none; border: none; cursor: pointer;
      color: #9ca3af; border-bottom: 3px solid transparent;
      margin-bottom: -2px; transition: all .2s;
    }
    .tab-btn.active { color: #131921; border-bottom-color: #f0c14b; }

    .tab-pane { display: none; padding: 28px 32px 32px; }
    .tab-pane.active { display: block; }

    .form-group { margin-bottom: 16px; }
    label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
    input[type=text], input[type=email], input[type=password] {
      width: 100%; padding: 10px 14px;
      border: 1px solid #d1d5db; border-radius: 6px;
      font-size: 14px; outline: none;
      transition: border-color .2s;
    }
    input:focus { border-color: #f0c14b; box-shadow: 0 0 0 3px rgba(240,193,75,.15); }

    .role-picker { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
    .role-option {
      border: 2px solid #e5e7eb; border-radius: 8px;
      padding: 12px; cursor: pointer; transition: all .2s; text-align: center;
    }
    .role-option input { display: none; }
    .role-option.selected { border-color: #f0c14b; background: #fffbeb; }
    .role-icon { font-size: 24px; display: block; margin-bottom: 4px; }
    .role-label { font-size: 12px; font-weight: 700; }
    .role-desc { font-size: 10px; color: #9ca3af; margin-top: 2px; }

    .btn {
      width: 100%; padding: 12px;
      background: #f0c14b; border: 1px solid #a88734;
      color: #0f1111; font-size: 14px; font-weight: 800;
      border-radius: 6px; cursor: pointer; transition: all .15s;
    }
    .btn:hover { background: #f5d060; }
    .btn:active { transform: scale(.99); }
    .btn:disabled { opacity: .6; cursor: not-allowed; }

    .alert {
      padding: 10px 14px; border-radius: 6px;
      font-size: 13px; font-weight: 600; margin-bottom: 14px;
      display: none;
    }
    .alert.error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
    .alert.success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
    .alert.show { display: block; }

    .forgot-link {
      display: block; text-align: center; margin-top: 14px;
      font-size: 12px; color: #9ca3af; text-decoration: none;
      cursor: pointer;
    }
    .forgot-link:hover { color: #374151; }

    .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(0,0,0,.2); border-top-color: #0f1111; border-radius: 50%; animation: spin .6s linear infinite; vertical-align: middle; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

<div class="card">
  <div class="card-header">
    <div class="logo">Crown<span>Mart</span><span class="badge">Hybrid</span></div>
    <p>Sign in to your account or create a new one</p>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab-btn active" onclick="switchAuthTab('login')">🔑 Sign In</button>
    <button class="tab-btn" onclick="switchAuthTab('register')">✨ Create Account</button>
  </div>

  <!-- LOGIN PANE -->
  <div class="tab-pane active" id="pane-login">
    <div class="alert" id="login-alert"></div>
    <form id="login-form" onsubmit="doLogin(event)">
      <div class="form-group">
        <label>Username or Email</label>
        <input type="text" id="login-username" placeholder="Enter your username or email" autocomplete="username" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="login-password" placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn" id="login-btn">Sign In to CrownMart</button>
    </form>
    <a class="forgot-link" href="index.php">← Back to marketplace without signing in</a>
  </div>

  <!-- REGISTER PANE -->
  <div class="tab-pane" id="pane-register">
    <div class="alert" id="reg-alert"></div>
    <form id="reg-form" onsubmit="doRegister(event)">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" id="reg-name" placeholder="e.g. John Smith" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" id="reg-username" placeholder="e.g. johnsmith99" required>
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" id="reg-email" placeholder="john@example.com" required>
      </div>
      <div class="form-group">
        <label>Password <span style="color:#9ca3af;font-weight:400">(min. 6 characters)</span></label>
        <input type="password" id="reg-password" placeholder="••••••••" required>
      </div>
      <div class="form-group">
        <label>Account Type</label>
        <div class="role-picker">
          <label class="role-option selected" id="opt-buyer" onclick="selectRole('buyer')">
            <input type="radio" name="role" value="buyer" checked>
            <span class="role-icon">🛒</span>
            <span class="role-label">Buyer</span>
            <span class="role-desc">Shop & bid on products</span>
          </label>
          <label class="role-option" id="opt-seller" onclick="selectRole('seller')">
            <input type="radio" name="role" value="seller">
            <span class="role-icon">🏪</span>
            <span class="role-label">Seller</span>
            <span class="role-desc">List & sell products</span>
          </label>
        </div>
      </div>
      <button type="submit" class="btn" id="reg-btn">Create My Account</button>
    </form>
  </div>
</div>

<script>
  let selectedRole = 'buyer';

  function switchAuthTab(tab) {
    document.querySelectorAll('.tab-btn').forEach((b,i) => b.classList.toggle('active', (i===0&&tab==='login')||(i===1&&tab==='register')));
    document.getElementById('pane-login').classList.toggle('active', tab === 'login');
    document.getElementById('pane-register').classList.toggle('active', tab === 'register');
  }

  function selectRole(role) {
    selectedRole = role;
    document.getElementById('opt-buyer').classList.toggle('selected', role === 'buyer');
    document.getElementById('opt-seller').classList.toggle('selected', role === 'seller');
  }

  function showAlert(id, msg, type) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.className = `alert ${type} show`;
    setTimeout(() => el.classList.remove('show'), 5000);
  }

  async function doLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('login-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Signing in...';

    const res = await fetch('api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action:   'login',
        username: document.getElementById('login-username').value,
        password: document.getElementById('login-password').value,
      })
    }).then(r => r.json()).catch(() => ({ error: 'Network error' }));

    if (res.error) {
      showAlert('login-alert', '❌ ' + res.error, 'error');
      btn.disabled = false;
      btn.textContent = 'Sign In to CrownMart';
      return;
    }

    showAlert('login-alert', '✅ ' + res.message, 'success');
    // Redirect berdasarkan role
    setTimeout(() => {
      if (res.user.role === 'admin') {
        window.location.href = 'admin/index.php';
      } else {
        window.location.href = 'index.php';
      }
    }, 800);
  }

  async function doRegister(e) {
    e.preventDefault();
    const btn = document.getElementById('reg-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Creating account...';

    const res = await fetch('api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action:   'register',
        name:     document.getElementById('reg-name').value,
        username: document.getElementById('reg-username').value,
        email:    document.getElementById('reg-email').value,
        password: document.getElementById('reg-password').value,
        role:     selectedRole,
      })
    }).then(r => r.json()).catch(() => ({ error: 'Network error' }));

    if (res.error) {
      showAlert('reg-alert', '❌ ' + res.error, 'error');
      btn.disabled = false;
      btn.textContent = 'Create My Account';
      return;
    }

    showAlert('reg-alert', '✅ ' + res.message, 'success');
    setTimeout(() => { window.location.href = 'index.php'; }, 800);
  }
</script>

</body>
</html>
