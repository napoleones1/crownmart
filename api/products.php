<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();

// ============================================================
// GET /api/products.php?id=...   -> detail produk
// GET /api/products.php          -> semua produk + filter
// POST /api/products.php         -> buat produk baru (seller)
// ============================================================

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        // Detail satu produk
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            jsonResponse(['error' => 'Product not found'], 404);
        }

        // Ambil bids
        $stmt = $pdo->prepare('SELECT bidder, amount, timestamp FROM bids WHERE product_id = ? ORDER BY timestamp DESC');
        $stmt->execute([$id]);
        $product['bids'] = $stmt->fetchAll();

        // Ambil reviews
        $stmt = $pdo->prepare('SELECT id, user_name AS user, rating, comment, date FROM reviews WHERE product_id = ? ORDER BY date DESC');
        $stmt->execute([$id]);
        $product['reviews'] = $stmt->fetchAll();

        // Format seller
        $product['seller'] = [
            'name'       => $product['seller_name'],
            'rating'     => (float)$product['seller_rating'],
            'salesCount' => (int)$product['seller_sales'],
        ];

        $product = castProductTypes($product);
        jsonResponse($product);
    }

    // Semua produk dengan filter
    $sql    = 'SELECT * FROM products WHERE 1=1';
    $params = [];

    if (!empty($_GET['category']) && $_GET['category'] !== 'All') {
        $sql .= ' AND category = ?';
        $params[] = $_GET['category'];
    }
    if (!empty($_GET['type']) && in_array($_GET['type'], ['fixed','auction'])) {
        $sql .= ' AND type = ?';
        $params[] = $_GET['type'];
    }
    if (!empty($_GET['q'])) {
        $sql .= ' AND (title LIKE ? OR description LIKE ? OR category LIKE ?)';
        $q = '%' . $_GET['q'] . '%';
        $params = array_merge($params, [$q, $q, $q]);
    }

    // Sorting
    $sort = $_GET['sort'] ?? 'relevance';
    switch ($sort) {
        case 'priceAsc':   $sql .= ' ORDER BY price ASC'; break;
        case 'priceDesc':  $sql .= ' ORDER BY price DESC'; break;
        case 'rating':     $sql .= ' ORDER BY rating DESC'; break;
        case 'endingSoon': $sql .= ' ORDER BY CASE WHEN end_time IS NULL THEN 1 ELSE 0 END, end_time ASC'; break;
        default:           $sql .= ' ORDER BY is_featured DESC, created_at DESC'; break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    foreach ($products as &$p) {
        // Bids ringkas
        $s = $pdo->prepare('SELECT bidder, amount, timestamp FROM bids WHERE product_id = ? ORDER BY timestamp DESC');
        $s->execute([$p['id']]);
        $p['bids'] = $s->fetchAll();

        // Reviews ringkas
        $s = $pdo->prepare('SELECT id, user_name AS user, rating, comment, date FROM reviews WHERE product_id = ?');
        $s->execute([$p['id']]);
        $p['reviews'] = $s->fetchAll();

        $p['seller'] = [
            'name'       => $p['seller_name'],
            'rating'     => (float)$p['seller_rating'],
            'salesCount' => (int)$p['seller_sales'],
        ];
        $p = castProductTypes($p);
    }

    jsonResponse($products);
}

// ============================================================
// POST - Buat listing baru
// ============================================================
if ($method === 'POST') {
    $data = getRequestBody();

    $required = ['title','description','price','category','type','sellerName'];
    foreach ($required as $f) {
        if (empty($data[$f])) {
            jsonResponse(['error' => "Field '$f' wajib diisi."], 422);
        }
    }

    $id       = 'prod-custom-' . time() . rand(100,999);
    $price    = (float)$data['price'];
    $type     = $data['type'] === 'auction' ? 'auction' : 'fixed';
    $stock    = $type === 'fixed' ? (int)($data['stock'] ?? 5) : null;
    $endTime  = null;
    $startingPrice = null;

    if ($type === 'auction') {
        $hours    = (float)($data['hours'] ?? 24);
        $endTime  = (int)(microtime(true) * 1000) + (int)($hours * 3600000);
        $startingPrice = $price;
        $stock    = null;
    }

    // Pilih gambar berdasarkan kategori
    $images = [
        'Electronics'    => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&auto=format&fit=crop&q=80',
        'Collectibles'   => 'https://images.unsplash.com/photo-1560942485-b2a11cc13456?w=600&auto=format&fit=crop&q=80',
        'Fashion'        => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=600&auto=format&fit=crop&q=80',
        'Home & Kitchen' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=600&auto=format&fit=crop&q=80',
    ];
    $image = $images[$data['category']] ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600';

    $stmt = $pdo->prepare('
        INSERT INTO products
        (id, title, description, price, starting_price, category, image, type, rating, review_count,
         is_featured, stock, shipping, delivery_days, end_time, seller_name, seller_rating, seller_sales)
        VALUES (?,?,?,?,?,?,?,?,5.0,0,0,?,?,2,?,?,100.0,0)
    ');
    $stmt->execute([
        $id, $data['title'], $data['description'], $price, $startingPrice,
        $data['category'], $image, $type,
        $stock, 'free', $endTime,
        $data['sellerName'] . ' (You)',
    ]);

    jsonResponse(['success' => true, 'id' => $id]);
}

// ============================================================
// Helper: cast tipe data produk
// ============================================================
function castProductTypes(array $p): array {
    $p['price']          = (float)$p['price'];
    $p['starting_price'] = $p['starting_price'] !== null ? (float)$p['starting_price'] : null;
    $p['rating']         = (float)$p['rating'];
    $p['review_count']   = (int)$p['review_count'];
    $p['is_featured']    = (bool)$p['is_featured'];
    $p['stock']          = $p['stock'] !== null ? (int)$p['stock'] : null;
    $p['delivery_days']  = (int)$p['delivery_days'];
    $p['end_time']       = $p['end_time'] !== null ? (int)$p['end_time'] : null;

    foreach ($p['bids'] as &$b) {
        $b['amount']    = (float)$b['amount'];
        $b['timestamp'] = (int)$b['timestamp'];
    }
    foreach ($p['reviews'] as &$r) {
        $r['rating'] = (int)$r['rating'];
    }
    return $p;
}

// ============================================================
// PUT - Update produk (seller yang memiliki produk)
// ============================================================
if ($method === 'PUT') {
    $data      = getRequestBody();
    $productId = $data['id'] ?? '';
    $user      = getSessionUser();

    if (!$productId) jsonResponse(['error' => 'id wajib'], 422);
    if (!$user)      jsonResponse(['error' => 'Login required'], 401);

    // Cek ownership
    $stmt = $pdo->prepare('SELECT user_id, seller_name FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $prod = $stmt->fetch();

    if (!$prod) jsonResponse(['error' => 'Produk tidak ditemukan'], 404);

    $isOwner = ($prod['user_id'] === (int)$user['id'])
            || (strpos($prod['seller_name'], $user['name']) !== false)
            || $user['role'] === 'admin';

    if (!$isOwner) jsonResponse(['error' => 'Tidak diizinkan'], 403);

    $fields  = [];
    $params  = [];

    if (isset($data['title']))       { $fields[] = 'title = ?';       $params[] = $data['title']; }
    if (isset($data['description'])) { $fields[] = 'description = ?'; $params[] = $data['description']; }
    if (isset($data['price']))       { $fields[] = 'price = ?';       $params[] = (float)$data['price']; }
    if (isset($data['stock']))       { $fields[] = 'stock = ?';       $params[] = (int)$data['stock']; }
    if (isset($data['shipping']))    { $fields[] = 'shipping = ?';    $params[] = $data['shipping']; }

    if (empty($fields)) jsonResponse(['error' => 'Tidak ada field yang diubah'], 422);

    $params[] = $productId;
    $pdo->prepare('UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?')
        ->execute($params);

    jsonResponse(['success' => true, 'message' => 'Produk berhasil diupdate']);
}

// ============================================================
// DELETE - Hapus produk
// ============================================================
if ($method === 'DELETE') {
    $data      = getRequestBody();
    $productId = $data['id'] ?? ($_GET['id'] ?? '');
    $user      = getSessionUser();

    if (!$productId) jsonResponse(['error' => 'id wajib'], 422);
    if (!$user)      jsonResponse(['error' => 'Login required'], 401);

    // Cek ownership
    $stmt = $pdo->prepare('SELECT user_id, seller_name FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $prod = $stmt->fetch();

    if (!$prod) jsonResponse(['error' => 'Produk tidak ditemukan'], 404);

    $isOwner = ($prod['user_id'] === (int)$user['id'])
            || (strpos($prod['seller_name'], $user['name']) !== false)
            || $user['role'] === 'admin';

    if (!$isOwner) jsonResponse(['error' => 'Tidak diizinkan'], 403);

    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$productId]);
    jsonResponse(['success' => true]);
}
