<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
$data   = getRequestBody();
$action = $data['action'] ?? ($_GET['action'] ?? '');

// ============================================================
// LOGIN
// ============================================================
if ($action === 'login') {
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (!$username || !$password) {
        jsonResponse(['error' => 'Username dan password wajib diisi.'], 422);
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE (username = ? OR email = ?) AND status = "active"');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(['error' => 'Username atau password salah.'], 401);
    }

    // Simpan ke session
    $_SESSION['cm_user'] = [
        'id'        => (int)$user['id'],
        'name'      => $user['name'],
        'email'     => $user['email'],
        'username'  => $user['username'],
        'role'      => $user['role'],
        'is_seller' => (bool)$user['is_seller'],
        'avatar'    => $user['avatar'],
        'balance'   => (float)$user['balance'],
    ];

    jsonResponse([
        'success' => true,
        'user'    => $_SESSION['cm_user'],
        'message' => 'Login berhasil! Selamat datang, ' . $user['name'],
    ]);
}

// ============================================================
// REGISTER
// ============================================================
if ($action === 'register') {
    $name     = trim($data['name']     ?? '');
    $username = trim($data['username'] ?? '');
    $email    = trim($data['email']    ?? '');
    $password = $data['password']      ?? '';
    $role     = in_array($data['role'] ?? '', ['buyer','seller']) ? $data['role'] : 'buyer';

    if (!$name || !$username || !$email || !$password) {
        jsonResponse(['error' => 'Semua field wajib diisi.'], 422);
    }
    if (strlen($password) < 6) {
        jsonResponse(['error' => 'Password minimal 6 karakter.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Format email tidak valid.'], 422);
    }

    // Cek username/email sudah ada
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Username atau email sudah digunakan.'], 409);
    }

    $hash      = password_hash($password, PASSWORD_BCRYPT);
    $isSeller  = $role === 'seller' ? 1 : 0;
    $avatar    = $role === 'seller' ? '🏪' : '👤';

    $pdo->prepare('
        INSERT INTO users (name, email, username, password, balance, is_seller, role, avatar, status)
        VALUES (?,?,?,?,500.00,?,?,?,?)
    ')->execute([$name, $email, $username, $hash, $isSeller, $role, $avatar, 'active']);

    $newId = $pdo->lastInsertId();

    $_SESSION['cm_user'] = [
        'id'        => (int)$newId,
        'name'      => $name,
        'email'     => $email,
        'username'  => $username,
        'role'      => $role,
        'is_seller' => (bool)$isSeller,
        'avatar'    => $avatar,
        'balance'   => 500.00,
    ];

    jsonResponse([
        'success' => true,
        'user'    => $_SESSION['cm_user'],
        'message' => 'Registrasi berhasil! Selamat datang, ' . $name,
    ]);
}

// ============================================================
// LOGOUT
// ============================================================
if ($action === 'logout') {
    session_destroy();
    jsonResponse(['success' => true, 'message' => 'Logout berhasil.']);
}

// ============================================================
// CEK STATUS LOGIN
// ============================================================
if ($action === 'check' || $method === 'GET') {
    $user = getSessionUser();
    if ($user) {
        // Refresh balance dari DB
        $stmt = $pdo->prepare('SELECT balance, is_seller, role FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $fresh = $stmt->fetch();
        if ($fresh) {
            $user['balance']   = (float)$fresh['balance'];
            $user['is_seller'] = (bool)$fresh['is_seller'];
            $user['role']      = $fresh['role'];
            $_SESSION['cm_user'] = $user;
        }
        jsonResponse(['loggedIn' => true, 'user' => $user]);
    }
    jsonResponse(['loggedIn' => false, 'user' => null]);
}

jsonResponse(['error' => 'Action tidak dikenali'], 400);
