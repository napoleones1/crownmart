<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$pdo    = getDB();
$userId = 1;
$data   = getRequestBody();

$productId = $data['productId'] ?? '';
$rating    = (int)($data['rating'] ?? 5);
$comment   = trim($data['comment'] ?? '');

if (!$productId || !$comment) {
    jsonResponse(['error' => 'productId dan comment wajib diisi'], 422);
}
if ($rating < 1 || $rating > 5) {
    jsonResponse(['error' => 'Rating harus 1-5'], 422);
}

// Ambil nama user
$stmt = $pdo->prepare('SELECT name FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$reviewId = 'rev-' . time() . rand(100,999);
$dateStr  = date('M j, Y');

$pdo->prepare('INSERT INTO reviews (id, product_id, user_name, rating, comment, date) VALUES (?,?,?,?,?,?)')
    ->execute([$reviewId, $productId, $user['name'], $rating, $comment, $dateStr]);

// Recalculate rating produk
$stmt = $pdo->prepare('SELECT AVG(rating) AS avg_r, COUNT(*) AS cnt FROM reviews WHERE product_id = ?');
$stmt->execute([$productId]);
$stat = $stmt->fetch();

$pdo->prepare('UPDATE products SET rating = ?, review_count = ? WHERE id = ?')
    ->execute([round((float)$stat['avg_r'], 1), (int)$stat['cnt'], $productId]);

jsonResponse([
    'success' => true,
    'review'  => [
        'id'      => $reviewId,
        'user'    => $user['name'],
        'rating'  => $rating,
        'comment' => $comment,
        'date'    => $dateStr,
    ],
    'newRating'      => round((float)$stat['avg_r'], 1),
    'newReviewCount' => (int)$stat['cnt'],
]);
