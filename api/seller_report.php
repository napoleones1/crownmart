<?php
require_once __DIR__ . '/config.php';

$user = getSessionUser();
if (!$user || !in_array($user['role'], ['seller', 'admin'])) {
    jsonResponse(['error' => 'Unauthorized — seller only'], 403);
}

$pdo      = getDB();
$sellerId = $user['id'];
$sellerName = $user['name'];

// Produk milik seller ini (berdasarkan user_id atau seller_name mengandung nama)
$stmt = $pdo->prepare('SELECT id FROM products WHERE user_id = ? OR seller_name LIKE ?');
$stmt->execute([$sellerId, '%' . $sellerName . '%']);
$myProductIds = array_column($stmt->fetchAll(), 'id');

if (empty($myProductIds)) {
    jsonResponse([
        'summary'      => ['total_revenue'=>0,'total_orders'=>0,'total_sold'=>0,'pending'=>0,'delivered'=>0,'products'=>0],
        'dailyRevenue' => [],
        'topProducts'  => [],
        'recentOrders' => [],
        'paymentStats' => [],
    ]);
}

$placeholders = implode(',', array_fill(0, count($myProductIds), '?'));

// ============================================================
// Summary stats
// ============================================================
$stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(oi.price_at_purchase * oi.quantity), 0) AS total_revenue,
        COUNT(DISTINCT oi.order_id)                          AS total_orders,
        COALESCE(SUM(oi.quantity), 0)                        AS total_sold
    FROM order_items oi
    WHERE oi.product_id IN ($placeholders)
");
$stmt->execute($myProductIds);
$salesStats = $stmt->fetch();

// Status breakdown
$stmt = $pdo->prepare("
    SELECT o.status, COUNT(DISTINCT o.id) AS cnt
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    WHERE oi.product_id IN ($placeholders)
    GROUP BY o.status
");
$stmt->execute($myProductIds);
$statusRows = $stmt->fetchAll();
$statusMap  = array_column($statusRows, 'cnt', 'status');

// Produk count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE user_id = ? OR seller_name LIKE ?");
$stmt->execute([$sellerId, '%' . $sellerName . '%']);
$prodCount = (int)$stmt->fetchColumn();

$summary = [
    'total_revenue' => (float)$salesStats['total_revenue'],
    'total_orders'  => (int)$salesStats['total_orders'],
    'total_sold'    => (int)$salesStats['total_sold'],
    'pending'       => (int)($statusMap['Pending']   ?? 0),
    'processing'    => (int)($statusMap['Processing'] ?? 0),
    'delivered'     => (int)($statusMap['Delivered']  ?? 0),
    'products'      => $prodCount,
];

// ============================================================
// Revenue harian 30 hari terakhir
// ============================================================
$stmt = $pdo->prepare("
    SELECT DATE(o.created_at) AS date,
           COUNT(DISTINCT o.id) AS order_count,
           SUM(oi.price_at_purchase * oi.quantity) AS revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE oi.product_id IN ($placeholders)
      AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(o.created_at)
    ORDER BY date ASC
");
$stmt->execute($myProductIds);
$dailyRevenue = $stmt->fetchAll();

// ============================================================
// Top produk terlaris
// ============================================================
$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.image, p.category, p.price,
           COALESCE(SUM(oi.quantity), 0) AS total_sold,
           COALESCE(SUM(oi.price_at_purchase * oi.quantity), 0) AS revenue
    FROM products p
    LEFT JOIN order_items oi ON oi.product_id = p.id
    WHERE p.id IN ($placeholders)
    GROUP BY p.id, p.title, p.image, p.category, p.price
    ORDER BY total_sold DESC, p.created_at DESC
    LIMIT 5
");
$stmt->execute($myProductIds);
$topProducts = $stmt->fetchAll();

// ============================================================
// Pesanan terbaru yang mengandung produk seller ini
// ============================================================
$stmt = $pdo->prepare("
    SELECT DISTINCT o.id, o.total, o.date_str, o.status,
                    o.payment_method, o.address,
                    u.name AS buyer_name
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN users u ON o.user_id = u.id
    WHERE oi.product_id IN ($placeholders)
    ORDER BY o.created_at DESC
    LIMIT 10
");
$stmt->execute($myProductIds);
$recentOrders = $stmt->fetchAll();
foreach ($recentOrders as &$o) { $o['total'] = (float)$o['total']; }

// ============================================================
// Metode pembayaran breakdown
// ============================================================
$stmt = $pdo->prepare("
    SELECT o.payment_method, COUNT(DISTINCT o.id) AS cnt,
           SUM(oi.price_at_purchase * oi.quantity) AS revenue
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    WHERE oi.product_id IN ($placeholders)
    GROUP BY o.payment_method
");
$stmt->execute($myProductIds);
$paymentStats = $stmt->fetchAll();

jsonResponse([
    'summary'      => $summary,
    'dailyRevenue' => $dailyRevenue,
    'topProducts'  => $topProducts,
    'recentOrders' => $recentOrders,
    'paymentStats' => $paymentStats,
]);
