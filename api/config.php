<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getPDO();

// --- AUTO MIGRATIONS (Runs safely on every config call to patch DB) ---
try {
    $pdo->exec("ALTER TABLE `config` DROP PRIMARY KEY, MODIFY COLUMN `id` VARCHAR(20) NOT NULL PRIMARY KEY");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE `config` ADD COLUMN IF NOT EXISTS `addons_price` INT UNSIGNED NOT NULL DEFAULT 0");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE `order_links` ADD COLUMN IF NOT EXISTS `addons_price` INT UNSIGNED NOT NULL DEFAULT 0");
} catch (Exception $e) {
}
// ----------------------------------------------------------------------

function getConfig(PDO $pdo, string $channel): array
{
    $stmt = $pdo->prepare('SELECT * FROM `config` WHERE id=? LIMIT 1');
    $stmt->execute([$channel]);
    $row = $stmt->fetch();
    if (!$row) {
        $pdo->prepare('INSERT IGNORE INTO `config` (`id`) VALUES (?)')->execute([$channel]);
        $stmt->execute([$channel]);
        $row = $stmt->fetch();
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

        $channel = $link['channel'];
        $cfg = getConfig($pdo, $channel);
        $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

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
    if (empty($_SESSION['admin_authed']) || empty($_SESSION['admin_channel'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }
    $channel = $_SESSION['admin_channel'];
    $cfg = getConfig($pdo, $channel);
    $qrUrl = $cfg['qr_image'] ? '../uploads/' . basename($cfg['qr_image']) : null;

    echo json_encode([
        'upiId' => $cfg['upi_id'],
        'qrImageUrl' => $qrUrl,
        'totalPrice' => (int) $cfg['total_price'],
        'advanceAmount' => (int) $cfg['advance_amount'],
        'addonsPrice' => (int) $cfg['addons_price'],
        'whatsappLink' => $cfg['whatsapp_link'] ?? '',
        'instagramLink' => $cfg['instagram_link'] ?? ''
    ]);
    exit;
}

if ($method === 'POST') {
    session_start();
    if (empty($_SESSION['admin_authed']) || empty($_SESSION['admin_channel'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }

    $channel = $_SESSION['admin_channel'];
    $existing = getConfig($pdo, $channel);
    $upiId = trim($_POST['upiId'] ?? $existing['upi_id'] ?? '');
    $totalPrice = (int) ($_POST['totalPrice'] ?? $existing['total_price'] ?? 6700);
    $advanceAmount = (int) ($_POST['advanceAmount'] ?? $existing['advance_amount'] ?? 2700);
    $addonsPrice = (int) ($_POST['addonsPrice'] ?? $existing['addons_price'] ?? 0);
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

    $stmt = $pdo->prepare('UPDATE `config` SET upi_id=?, qr_image=?, total_price=?, advance_amount=?, addons_price=?, whatsapp_link=?, instagram_link=? WHERE id=?');
    $stmt->execute([$upiId, $qrImage, $totalPrice, $advanceAmount, $addonsPrice, $whatsappLink, $instagramLink, $channel]);

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);