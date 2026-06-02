<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { jsonResponse([]); }

// Hanya admin yang boleh akses
$admin = getSessionUser();
if (!$admin || $admin['role'] !== 'admin') {
    jsonResponse(['error' => 'Unauthorized — admin only'], 403);
}

$pdo  = getDB();
$data = getRequestBody();
$action = $data['action'] ?? '';

// ============================================================
// UPDATE ORDER STATUS
// ============================================================
if ($action === 'update_order_status') {
    $orderId = $data['orderId'] ?? '';
    $status  = $data['status']  ?? '';
    $allowed = ['Processing','Shipped','Out for Delivery','Delivered'];

    if (!$orderId || !in_array($status, $allowed)) {
        jsonResponse(['error' => 'Data tidak valid'], 422);
    }

    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')
        ->execute([$status, $orderId]);

    jsonResponse(['success' => true, 'status' => $status]);
}

// ============================================================
// UPDATE USER ROLE
// ============================================================
if ($action === 'update_user_role') {
    $userId = (int)($data['userId'] ?? 0);
    $role   = $data['role'] ?? '';
    $allowed = ['buyer','seller','admin'];

    if (!$userId || !in_array($role, $allowed)) {
        jsonResponse(['error' => 'Data tidak valid'], 422);
    }

    // Jangan ubah role admin sendiri
    if ($userId === $admin['id']) {
        jsonResponse(['error' => 'Tidak bisa mengubah role sendiri'], 400);
    }

    $isSeller = $role !== 'buyer' ? 1 : 0;
    $pdo->prepare('UPDATE users SET role = ?, is_seller = ? WHERE id = ?')
        ->execute([$role, $isSeller, $userId]);

    jsonResponse(['success' => true]);
}

// ============================================================
// UPDATE USER STATUS (suspend / activate)
// ============================================================
if ($action === 'update_user_status') {
    $userId = (int)($data['userId'] ?? 0);
    $status = $data['status'] ?? '';
    $allowed = ['active','suspended'];

    if (!$userId || !in_array($status, $allowed)) {
        jsonResponse(['error' => 'Data tidak valid'], 422);
    }

    if ($userId === $admin['id']) {
        jsonResponse(['error' => 'Tidak bisa suspend akun sendiri'], 400);
    }

    $pdo->prepare('UPDATE users SET status = ? WHERE id = ?')
        ->execute([$status, $userId]);

    jsonResponse(['success' => true, 'status' => $status]);
}

// ============================================================
// DELETE PRODUCT
// ============================================================
if ($action === 'delete_product') {
    $productId = $data['productId'] ?? '';
    if (!$productId) jsonResponse(['error' => 'productId wajib'], 422);

    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$productId]);
    jsonResponse(['success' => true]);
}

// ============================================================
// GET STATS (untuk refresh dashboard)
// ============================================================
if ($action === 'get_stats' || ($_SERVER['REQUEST_METHOD'] === 'GET' && !$action)) {
    jsonResponse([
        'users'    => (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role != "admin"')->fetchColumn(),
        'sellers'  => (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "seller"')->fetchColumn(),
        'products' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        'orders'   => (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
        'revenue'  => (float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM orders')->fetchColumn(),
        'bids'     => (int)$pdo->query('SELECT COUNT(*) FROM bids')->fetchColumn(),
    ]);
}

// ============================================================
// GET ALL ORDERS (untuk reports)
// ============================================================
if ($action === 'get_orders') {
    $orders = $pdo->query('
        SELECT o.*, u.name as user_name, u.email as user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ')->fetchAll();
    foreach ($orders as &$o) {
        $o['total'] = (float)$o['total'];
    }
    jsonResponse($orders);
}

jsonResponse(['error' => 'Action tidak dikenali'], 400);
