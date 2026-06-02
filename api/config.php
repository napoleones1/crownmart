<?php
// ============================================================
// CrownMart - Konfigurasi Database & Auth
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crownmart');

// Mulai session di semua halaman yang include config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

// Header JSON untuk semua API response
function jsonResponse(mixed $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Ambil body JSON dari request
function getRequestBody(): array {
    $body = file_get_contents('php://input');
    return json_decode($body, true) ?? [];
}

// Ambil user yang sedang login (dari session)
function getSessionUser(): ?array {
    return $_SESSION['cm_user'] ?? null;
}

// Wajib login - redirect ke login.php jika belum
function requireLogin(string $redirect = '../login.php'): array {
    $user = getSessionUser();
    if (!$user) {
        header("Location: $redirect");
        exit;
    }
    return $user;
}

// Wajib admin
function requireAdmin(string $redirect = '../login.php'): array {
    $user = requireLogin($redirect);
    if ($user['role'] !== 'admin') {
        header("Location: ../index.php?error=unauthorized");
        exit;
    }
    return $user;
}
