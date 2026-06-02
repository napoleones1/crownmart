<?php
require_once __DIR__ . '/config.php';

// Hanya admin
$admin = getSessionUser();
if (!$admin || $admin['role'] !== 'admin') {
    jsonResponse(['error' => 'Unauthorized'], 403);
}

$pdo    = getDB();
$period = $_GET['period'] ?? '30'; // hari

// ============================================================
// Revenue per hari (30 hari terakhir)
// ============================================================
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date,
           COUNT(*) as order_count,
           SUM(total) as revenue
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
      AND payment_status IN ('paid', 'pending')
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$period]);
$dailyRevenue = $stmt->fetchAll();

// ============================================================
// Penjualan per kategori
// ============================================================
$categoryStats = $pdo->query("
    SELECT p.category,
           COUNT(oi.id) as total_sold,
           SUM(oi.price_at_purchase * oi.quantity) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    GROUP BY p.category
    ORDER BY revenue DESC
")->fetchAll();

// ============================================================
// Metode pembayaran
// ============================================================
$paymentStats = $pdo->query("
    SELECT payment_method,
           COUNT(*) as count,
           SUM(total) as total
    FROM orders
    GROUP BY payment_method
")->fetchAll();

// ============================================================
// Produk terlaris
// ============================================================
$topProducts = $pdo->query("
    SELECT p.id, p.title, p.image, p.category,
           SUM(oi.quantity) as total_sold,
           SUM(oi.price_at_purchase * oi.quantity) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id, p.title, p.image, p.category
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();

// ============================================================
// Summary stats
// ============================================================
$summary = [
    'total_revenue'  => (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status IN ('paid','pending')")->fetchColumn(),
    'total_orders'   => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn(),
    'total_users'    => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'buyer'")->fetchColumn(),
    'total_products' => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'cod_orders'     => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_method = 'cod'")->fetchColumn(),
    'card_orders'=> (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_method = 'card'")->fetchColumn(),
    'wallet_orders'  => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_method = 'wallet'")->fetchColumn(),
];

jsonResponse([
    'summary'       => $summary,
    'dailyRevenue'  => $dailyRevenue,
    'categoryStats' => $categoryStats,
    'paymentStats'  => $paymentStats,
    'topProducts'   => $topProducts,
]);
