<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

$method      = $_SERVER['REQUEST_METHOD'];
$pdo         = getDB();
$sessionUser = getSessionUser();

// Cart butuh login untuk POST/PUT/DELETE, GET bisa tanpa login (return kosong)
if (!$sessionUser && $method !== 'GET') {
    jsonResponse(['error' => 'Login required', 'loginRequired' => true], 401);
}
$userId = $sessionUser ? $sessionUser['id'] : 0;

// Helper: ambil isi cart lengkap
function getCartItems(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare('
        SELECT c.id, c.quantity, p.*
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ');
    $stmt->execute([$userId]);
    $rows  = $stmt->fetchAll();
    $items = [];
    foreach ($rows as $r) {
        $items[] = [
            'cartId'   => (int)$r['id'],
            'quantity' => (int)$r['quantity'],
            'product'  => [
                'id'           => $r['product_id'] ?? $r['id'],
                'title'        => $r['title'],
                'price'        => (float)$r['price'],
                'image'        => $r['image'],
                'category'     => $r['category'],
                'type'         => $r['type'],
                'shipping'     => $r['shipping'],
                'delivery_days'=> (int)$r['delivery_days'],
                'stock'        => $r['stock'] !== null ? (int)$r['stock'] : null,
                'seller'       => [
                    'name'       => $r['seller_name'],
                    'rating'     => (float)$r['seller_rating'],
                    'salesCount' => (int)$r['seller_sales'],
                ],
            ],
        ];
    }
    return $items;
}

// ============================================================
// GET  -> ambil isi cart
// POST -> tambah ke cart
// PUT  -> update quantity
// DELETE -> hapus dari cart
// ============================================================

if ($method === 'GET') {
    jsonResponse(getCartItems($pdo, $userId));
}

if ($method === 'POST') {
    $data      = getRequestBody();
    $productId = $data['productId'] ?? '';

    if (!$productId) jsonResponse(['error' => 'productId wajib'], 422);

    // Cek produk ada dan bukan auction
    $stmt = $pdo->prepare('SELECT type, stock FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) jsonResponse(['error' => 'Produk tidak ditemukan'], 404);
    if ($product['type'] === 'auction') {
        jsonResponse(['error' => 'Produk auction tidak bisa ditambah ke keranjang'], 400);
    }

    // Cek apakah sudah ada di cart
    $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $pdo->prepare('UPDATE cart SET quantity = quantity + 1 WHERE id = ?')
            ->execute([$existing['id']]);
    } else {
        $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,1)')
            ->execute([$userId, $productId]);
    }

    jsonResponse(['success' => true, 'cart' => getCartItems($pdo, $userId)]);
}

if ($method === 'PUT') {
    $data      = getRequestBody();
    $productId = $data['productId'] ?? '';
    $qty       = (int)($data['quantity'] ?? 0);

    if (!$productId) jsonResponse(['error' => 'productId wajib'], 422);

    if ($qty <= 0) {
        $pdo->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?')
            ->execute([$userId, $productId]);
    } else {
        $pdo->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?')
            ->execute([$qty, $userId, $productId]);
    }

    jsonResponse(['success' => true, 'cart' => getCartItems($pdo, $userId)]);
}

if ($method === 'DELETE') {
    $productId = $_GET['productId'] ?? (getRequestBody()['productId'] ?? '');
    if (!$productId) jsonResponse(['error' => 'productId wajib'], 422);

    $pdo->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?')
        ->execute([$userId, $productId]);

    jsonResponse(['success' => true, 'cart' => getCartItems($pdo, $userId)]);
}
