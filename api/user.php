<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();

// Gunakan user dari session jika ada, fallback ke id=1 untuk kompatibilitas
$sessionUser = getSessionUser();
$userId      = $sessionUser ? $sessionUser['id'] : 1;

// ============================================================
// GET  /api/user.php  -> data profil user
// POST /api/user.php  -> update balance (topup) atau toggle seller
// ============================================================

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT id, name, email, balance, is_seller FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    // Ambil watchlist
    $stmt = $pdo->prepare('SELECT product_id FROM watchlist WHERE user_id = ?');
    $stmt->execute([$userId]);
    $watchList = array_column($stmt->fetchAll(), 'product_id');

    $user['balance']    = (float)$user['balance'];
    $user['is_seller']  = (bool)$user['is_seller'];
    $user['watchList']  = $watchList;

    jsonResponse($user);
}

if ($method === 'POST') {
    $data   = getRequestBody();
    $action = $data['action'] ?? '';

    if ($action === 'topup') {
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            jsonResponse(['error' => 'Jumlah topup tidak valid'], 422);
        }
        $stmt = $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
        $stmt->execute([$amount, $userId]);

        $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        jsonResponse(['success' => true, 'balance' => (float)$row['balance']]);
    }

    if ($action === 'upgrade_seller') {
        $stmt = $pdo->prepare('UPDATE users SET is_seller = 1 WHERE id = ?');
        $stmt->execute([$userId]);
        jsonResponse(['success' => true]);
    }

    if ($action === 'toggle_watchlist') {
        $productId = $data['productId'] ?? '';
        if (!$productId) jsonResponse(['error' => 'productId wajib'], 422);

        // Cek apakah sudah ada
        $stmt = $pdo->prepare('SELECT id FROM watchlist WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare('DELETE FROM watchlist WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$userId, $productId]);
            $isWatched = false;
        } else {
            $stmt = $pdo->prepare('INSERT INTO watchlist (user_id, product_id) VALUES (?,?)');
            $stmt->execute([$userId, $productId]);
            $isWatched = true;
        }

        jsonResponse(['success' => true, 'isWatched' => $isWatched]);
    }

    jsonResponse(['error' => 'Action tidak dikenali'], 400);
}
