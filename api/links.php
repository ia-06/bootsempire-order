<?php
/**
 * api/links.php — Generate a new order link (admin only)
 * GET  → redirect to orders.php (unified endpoint now handles link+order list)
 * POST → create new link, return slug
 * DELETE → handled by orders.php (deletes link + order together)
 */
session_start();
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (empty($_SESSION['admin_authed'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getPDO();
$channel = $_SESSION['admin_channel'] ?? null;

// POST — create new link
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$channel && !empty($body['channel'])) {
        $ch = $body['channel'];
        if (in_array($ch, ['instagram', 'whatsapp'], true)) {
            $channel = $ch;
            $_SESSION['admin_channel'] = $channel;
        }
    }
    if (!$channel) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'No channel in session']);
        exit;
    }

    $qty = max(1, (int) ($body['qty'] ?? 1));
    $totalPrice = max(0, (int) ($body['totalPrice'] ?? 6700));
    $advanceAmount = max(0, (int) ($body['advanceAmount'] ?? 2700));

    $slug = null;
    for ($i = 0; $i < 20; $i++) {
        $candidate = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $chk = $pdo->prepare('SELECT id FROM `order_links` WHERE slug=?');
        $chk->execute([$candidate]);
        if (!$chk->fetch()) {
            $slug = $candidate;
            break;
        }
    }
    if (!$slug) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Could not generate unique slug']);
        exit;
    }

    $id = bin2hex(random_bytes(8));
    $stmt = $pdo->prepare('
        INSERT INTO `order_links` (id, slug, channel, qty, total_price, advance_amount)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$id, $slug, $channel, $qty, $totalPrice, $advanceAmount]);

    echo json_encode(['ok' => true, 'slug' => $slug, 'order_id' => 'BE-' . $slug]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
