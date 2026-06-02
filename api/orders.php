<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

$method      = $_SERVER['REQUEST_METHOD'];
$pdo         = getDB();
$sessionUser = getSessionUser();
$userId      = $sessionUser ? $sessionUser['id'] : 1;

// Helper: format order dengan items
function formatOrder(PDO $pdo, array $order): array {
    $stmt = $pdo->prepare('
        SELECT oi.quantity, oi.price_at_purchase, p.id, p.title, p.image,
               p.category, p.type, p.shipping, p.delivery_days,
               p.seller_name, p.seller_rating, p.seller_sales
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ');
    $stmt->execute([$order['id']]);
    $rawItems = $stmt->fetchAll();

    $order['items'] = array_map(fn($r) => [
        'quantity' => (int)$r['quantity'],
        'product'  => [
            'id'            => $r['id'],
            'title'         => $r['title'],
            'price'         => (float)$r['price_at_purchase'],
            'image'         => $r['image'],
            'category'      => $r['category'],
            'type'          => $r['type'],
            'shipping'      => $r['shipping'],
            'delivery_days' => (int)$r['delivery_days'],
            'seller'        => [
                'name'       => $r['seller_name'],
                'rating'     => (float)$r['seller_rating'],
                'salesCount' => (int)$r['seller_sales'],
            ],
        ],
    ], $rawItems);

    $order['total'] = (float)$order['total'];
    return $order;
}

// ============================================================
// GET — riwayat orders user
// ============================================================
if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
    jsonResponse(array_map(fn($o) => formatOrder($pdo, $o), $orders));
}

// ============================================================
// POST — checkout
// ============================================================
if ($method === 'POST') {
    $data            = getRequestBody();
    $mode            = $data['mode']            ?? 'cart';
    $address         = trim($data['address']    ?? '');
    $paymentMethod   = $data['paymentMethod']   ?? 'wallet';   // wallet | transfer | cod
    $shippingMethod  = $data['shippingMethod']  ?? 'regular';  // regular | express | same_day
    $notes           = trim($data['notes']      ?? '');

    // Validasi metode bayar
    if (!in_array($paymentMethod, ['wallet','transfer','cod'])) {
        jsonResponse(['error' => 'Metode pembayaran tidak valid'], 422);
    }
    if (!$address) {
        jsonResponse(['error' => 'Alamat pengiriman wajib diisi'], 422);
    }

    // Ambil data user
    $stmt = $pdo->prepare('SELECT balance, name FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) jsonResponse(['error' => 'User tidak ditemukan'], 404);

    $orderId  = 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $dateStr  = date('d M Y, H:i');
    $total    = 0.0;
    $itemsArr = [];

    // ---- Mode: Buy Now ----
    if ($mode === 'buynow') {
        $productId = $data['productId'] ?? '';
        if (!$productId) jsonResponse(['error' => 'productId wajib'], 422);

        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND type = "fixed"');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) jsonResponse(['error' => 'Produk tidak ditemukan'], 404);

        $total    = (float)$product['price'];
        $itemsArr = [['productId' => $productId, 'quantity' => 1, 'price' => $total]];

    // ---- Mode: Cart Checkout ----
    } elseif ($mode === 'cart') {
        $stmt = $pdo->prepare('
            SELECT c.quantity, p.id AS product_id, p.price, p.type, p.stock
            FROM cart c JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ');
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll();

        if (empty($cartItems)) jsonResponse(['error' => 'Keranjang kosong'], 400);

        foreach ($cartItems as $ci) {
            // Cek stok
            if ($ci['stock'] !== null && $ci['stock'] < (int)$ci['quantity']) {
                jsonResponse(['error' => "Stok tidak cukup untuk salah satu produk"], 400);
            }
            $total     += (float)$ci['price'] * (int)$ci['quantity'];
            $itemsArr[] = ['productId' => $ci['product_id'], 'quantity' => (int)$ci['quantity'], 'price' => (float)$ci['price']];
        }
    } else {
        jsonResponse(['error' => 'Mode tidak valid'], 400);
    }

    // ---- Tentukan status & payment_status berdasarkan metode ----
    if ($paymentMethod === 'wallet') {
        // Cek saldo cukup
        if ((float)$user['balance'] < $total) {
            jsonResponse([
                'error'   => 'Saldo wallet tidak cukup',
                'balance' => (float)$user['balance'],
                'total'   => $total,
            ], 400);
        }
        $orderStatus   = 'Processing';
        $paymentStatus = 'paid';
        // Potong saldo
        $pdo->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')
            ->execute([$total, $userId]);

    } elseif ($paymentMethod === 'transfer') {
        // Transfer bank — menunggu konfirmasi
        $orderStatus   = 'Pending';
        $paymentStatus = 'pending';

    } elseif ($paymentMethod === 'cod') {
        // Bayar di tempat — langsung proses
        $orderStatus   = 'Processing';
        $paymentStatus = 'pending'; // bayar nanti saat tiba
    }

    // ---- Simpan order ----
    $pdo->prepare('
        INSERT INTO orders (id, user_id, total, date_str, status, address,
                            payment_method, payment_status, shipping_method, notes)
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ')->execute([
        $orderId, $userId, $total, $dateStr, $orderStatus, $address,
        $paymentMethod, $paymentStatus, $shippingMethod, $notes ?: null
    ]);

    // ---- Simpan order items ----
    foreach ($itemsArr as $item) {
        $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?,?,?,?)')
            ->execute([$orderId, $item['productId'], $item['quantity'], $item['price']]);

        // Kurangi stok untuk wallet & COD (transfer tunggu konfirmasi)
        if ($paymentMethod !== 'transfer') {
            $pdo->prepare('UPDATE products SET stock = GREATEST(0, COALESCE(stock,0) - ?) WHERE id = ?')
                ->execute([$item['quantity'], $item['productId']]);
        }
    }

    // ---- Simpan ke tabel payments ----
    $pdo->prepare('
        INSERT INTO payments (order_id, user_id, amount, method, status)
        VALUES (?,?,?,?,?)
    ')->execute([$orderId, $userId, $total, $paymentMethod, $paymentStatus]);

    // ---- Kosongkan cart jika mode cart ----
    if ($mode === 'cart') {
        $pdo->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$userId]);
    }

    // ---- Ambil saldo terbaru ----
    $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $newBalance = (float)$stmt->fetchColumn();

    // ---- Pesan sukses berdasarkan metode ----
    $messages = [
        'wallet'   => "✅ Pembayaran berhasil! Pesanan #$orderId sedang diproses.",
        'transfer' => "🏦 Pesanan #$orderId dibuat! Silakan transfer ke rekening kami dan upload bukti di halaman pesanan.",
        'cod'      => "🚚 Pesanan #$orderId dikonfirmasi! Bayar saat barang tiba.",
    ];

    jsonResponse([
        'success'        => true,
        'orderId'        => $orderId,
        'total'          => $total,
        'newBalance'     => $newBalance,
        'status'         => $orderStatus,
        'paymentMethod'  => $paymentMethod,
        'paymentStatus'  => $paymentStatus,
        'message'        => $messages[$paymentMethod],
        'transferInfo'   => $paymentMethod === 'transfer' ? [
            'bank'      => 'BCA',
            'account'   => '1234567890',
            'name'      => 'CrownMart Indonesia',
            'amount'    => $total,
        ] : null,
    ]);
}
