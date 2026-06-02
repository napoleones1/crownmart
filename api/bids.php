<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

$pdo         = getDB();
$sessionUser = getSessionUser();

// Wajib login untuk bid
if (!$sessionUser) {
    jsonResponse(['error' => 'Login required to place a bid', 'loginRequired' => true], 401);
}
$userId = $sessionUser['id'];

// ============================================================
// POST /api/bids.php  -> tempatkan bid baru
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data      = getRequestBody();
$productId = $data['productId'] ?? '';
$amount    = isset($data['amount']) ? (float)$data['amount'] : 0;

if (!$productId || $amount <= 0) {
    jsonResponse(['error' => 'productId dan amount wajib diisi'], 422);
}

// Ambil produk
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND type = "auction"');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    jsonResponse(['error' => 'Produk auction tidak ditemukan'], 404);
}

// Cek waktu auction
$nowMs = (int)(microtime(true) * 1000);
if ($product['end_time'] !== null && (int)$product['end_time'] <= $nowMs) {
    jsonResponse(['error' => 'Auction sudah berakhir'], 400);
}

// Cek minimum bid
$currentPrice = (float)$product['price'];
$minRequired  = $currentPrice > 0 ? $currentPrice + 5.00 : (float)($product['starting_price'] ?? 5.00);

if ($amount < $minRequired) {
    jsonResponse([
        'error'       => 'Bid terlalu rendah',
        'minRequired' => $minRequired,
        'message'     => "Bid minimum adalah $" . number_format($minRequired, 2)
    ], 400);
}

// Cek saldo user
$stmt = $pdo->prepare('SELECT balance, name FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ((float)$user['balance'] < $amount) {
    jsonResponse(['error' => 'Saldo wallet tidak cukup'], 400);
}

// Simpan bid
$pdo->prepare('INSERT INTO bids (product_id, bidder, amount, timestamp) VALUES (?,?,?,?)')
    ->execute([$productId, $user['name'] . ' (You)', $amount, $nowMs]);

// Update harga produk ke bid tertinggi
$pdo->prepare('UPDATE products SET price = ? WHERE id = ?')
    ->execute([$amount, $productId]);

// Auto-add ke watchlist
$stmt = $pdo->prepare('SELECT id FROM watchlist WHERE user_id = ? AND product_id = ?');
$stmt->execute([$userId, $productId]);
if (!$stmt->fetch()) {
    $pdo->prepare('INSERT INTO watchlist (user_id, product_id) VALUES (?,?)')
        ->execute([$userId, $productId]);
}

// Return data terbaru
$stmt = $pdo->prepare('SELECT bidder, amount, timestamp FROM bids WHERE product_id = ? ORDER BY timestamp DESC');
$stmt->execute([$productId]);
$bids = $stmt->fetchAll();
foreach ($bids as &$b) {
    $b['amount']    = (float)$b['amount'];
    $b['timestamp'] = (int)$b['timestamp'];
}

jsonResponse([
    'success'      => true,
    'newPrice'     => $amount,
    'bids'         => $bids,
    'message'      => "Berhasil! Kamu sekarang penawar tertinggi ($" . number_format($amount, 2) . ")"
]);
