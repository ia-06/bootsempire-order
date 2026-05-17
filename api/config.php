<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getPDO();

function getConfig(PDO $pdo): array
{
    $row = $pdo->query('SELECT * FROM `config` WHERE id=1 LIMIT 1')->fetch();
    if (!$row) {
        $pdo->exec('INSERT IGNORE INTO `config` (`id`) VALUES (1)');
        $row = $pdo->query('SELECT * FROM `config` WHERE id=1 LIMIT 1')->fetch();
    }
    return $row;
}

if ($method === 'GET') {
    // Slug lookup (public) — used by order.php
    if (isset($_GET['slug'])) {
        $slug = $_GET['slug'];
        $stmt = $pdo->prepare('SELECT * FROM `order_links` WHERE slug=?');
        $stmt->execute([$slug]);
        $link = $stmt->fetch();
        if (!$link) {
            http_response_code(404);
            echo json_encode(['ok' => false]);
            exit;
        }
        $cfg = getConfig($pdo);
        $qrUrl = null;
        if (!empty($cfg['qr_image'])) {
            $qrPath = __DIR__ . '/../uploads/' . basename($cfg['qr_image']);
            if (file_exists($qrPath)) {
                $mime = mime_content_type($qrPath);
                $data = base64_encode(file_get_contents($qrPath));
                $qrUrl = "data:{$mime};base64,{$data}";
            }
        }
        echo json_encode([
            'ok' => true,
            'slug' => $link['slug'],
            'channel' => $link['channel'],
            'qty' => (int) $link['qty'],
            'totalPrice' => (int) $link['total_price'],
            'advanceAmount' => (int) $link['advance_amount'],
            'upiId' => $cfg['upi_id'],
            'qrDataUrl' => $qrUrl,
            'whatsappLink' => $cfg['whatsapp_link'] ?? '',
            'instagramLink' => $cfg['instagram_link'] ?? ''
        ]);
        exit;
    }

    // Admin config fetch
    session_start();
    if (empty($_SESSION['admin_authed'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }
    $cfg = getConfig($pdo);
    $qrUrl = $cfg['qr_image'] ? '../uploads/' . basename($cfg['qr_image']) : null;
    echo json_encode([
        'upiId' => $cfg['upi_id'],
        'qrImageUrl' => $qrUrl,
        'totalPrice' => (int) $cfg['total_price'],
        'advanceAmount' => (int) $cfg['advance_amount'],
        'whatsappLink' => $cfg['whatsapp_link'] ?? '',
        'instagramLink' => $cfg['instagram_link'] ?? ''
    ]);
    exit;
}

if ($method === 'POST') {
    session_start();
    if (empty($_SESSION['admin_authed'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }

    $existing = getConfig($pdo);
    $upiId = trim($_POST['upiId'] ?? $existing['upi_id'] ?? '');
    $totalPrice = (int) ($_POST['totalPrice'] ?? $existing['total_price'] ?? 6700);
    $advanceAmount = (int) ($_POST['advanceAmount'] ?? $existing['advance_amount'] ?? 2700);
    $whatsappLink = trim($_POST['whatsappLink'] ?? $existing['whatsapp_link'] ?? '');
    $instagramLink = trim($_POST['instagramLink'] ?? $existing['instagram_link'] ?? '');

    $qrImage = $existing['qr_image'];

    if (!empty($_FILES['qrImage']['tmp_name']) && $_FILES['qrImage']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['qrImage']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($ext, $allowed, true)) {
            $filename = 'qr_' . time() . '.' . $ext;
            $dest = __DIR__ . '/../uploads/' . $filename;
            if (move_uploaded_file($_FILES['qrImage']['tmp_name'], $dest)) {
                if ($qrImage && file_exists(__DIR__ . '/../uploads/' . basename($qrImage)))
                    @unlink(__DIR__ . '/../uploads/' . basename($qrImage));
                $qrImage = $filename;
            }
        }
    }

    if (isset($_POST['removeQr']) && $_POST['removeQr'] === '1') {
        if ($qrImage && file_exists(__DIR__ . '/../uploads/' . basename($qrImage)))
            @unlink(__DIR__ . '/../uploads/' . basename($qrImage));
        $qrImage = null;
    }

    $stmt = $pdo->prepare('UPDATE `config` SET upi_id=?, qr_image=?, total_price=?, advance_amount=?, whatsapp_link=?, instagram_link=? WHERE id=1');
    $stmt->execute([$upiId, $qrImage, $totalPrice, $advanceAmount, $whatsappLink, $instagramLink]);

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);