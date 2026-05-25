<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getPDO();

// --- FIXED AUTO MIGRATIONS (Wrapped carefully to prevent 500 runtime execution crashes) ---
try {
    // Check if configuration table primary key update is required safely
    $pdo->exec("ALTER TABLE `config` DROP PRIMARY KEY");
} catch (Exception $e) {
}

try {
    $pdo->exec("ALTER TABLE `config` MODIFY COLUMN `id` VARCHAR(20) NOT NULL PRIMARY KEY");
} catch (Exception $e) {
}

try {
    // Standard ALTER check using safe fallback attributes handling
    $pdo->exec("ALTER TABLE `config` ADD COLUMN `addons_price` INT UNSIGNED NOT NULL DEFAULT 0");
} catch (Exception $e) {
}

try {
    $pdo->exec("ALTER TABLE `order_links` ADD COLUMN `addons_price` INT UNSIGNED NOT NULL DEFAULT 0");
} catch (Exception $e) {
}

try {
    $pdo->exec("ALTER TABLE `config` ADD COLUMN `upi_number` VARCHAR(32) NOT NULL DEFAULT ''");
} catch (Exception $e) {
}

// ----------------------------------------------------------------------

// Helper to safely get config values
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
    // Public Slug Lookup
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
            'upiNumber' => $cfg['upi_number'] ?? '',
            'qrDataUrl' => $qrUrl,
            'whatsappLink' => $cfg['whatsapp_link'] ?? '',
            'instagramLink' => $cfg['instagram_link'] ?? ''
        ]);
        exit;
    }

    // Admin Config & Employee accounts data payload
    session_start();
    if (empty($_SESSION['admin_authed']) || empty($_SESSION['admin_channel'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }
    $channel = $_SESSION['admin_channel'];
    $cfg = getConfig($pdo, $channel);
    $qrUrl = $cfg['qr_image'] ? '../uploads/' . basename($cfg['qr_image']) : null;

    $response = [
        'upiId' => $cfg['upi_id'],
        'upiNumber' => $cfg['upi_number'] ?? '',
        'qrImageUrl' => $qrUrl,
        'totalPrice' => (int) $cfg['total_price'],
        'advanceAmount' => (int) $cfg['advance_amount'],
        'addonsPrice' => (int) $cfg['addons_price'],
        'whatsappLink' => $cfg['whatsapp_link'] ?? '',
        'instagramLink' => $cfg['instagram_link'] ?? ''
    ];

    // If the master admin requested data, append current standard employee profiles
    if (!empty($_SESSION['is_master'])) {
        $empStmt = $pdo->query('SELECT id, password FROM `admins` ORDER BY id ASC');
        $response['employees'] = $empStmt->fetchAll();
    }

    echo json_encode($response);
    exit;
}

if ($method === 'POST') {
    session_start();
    if (empty($_SESSION['admin_authed'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }

    // INTERCEPT: Target processing routing for Master Managing Employees
    if (isset($_POST['action']) && $_POST['action'] === 'updateEmployee') {
        if (empty($_SESSION['is_master'])) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Only Master Admin can modify employees.']);
            exit;
        }

        $empId = trim($_POST['employeeId'] ?? '');
        $empPass = trim($_POST['employeePassword'] ?? '');

        if ($empId === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Employee username/ID cannot be empty.']);
            exit;
        }

        // Check if employee already exists to route between registration vs modification
        $check = $pdo->prepare('SELECT id FROM `admins` WHERE id = ?');
        $check->execute([$empId]);

        if ($check->fetch()) {
            if ($empPass === '') {
                // If password left empty, drop employee account entirely
                $del = $pdo->prepare('DELETE FROM `admins` WHERE id = ?');
                $del->execute([$empId]);
                echo json_encode(['ok' => true, 'message' => 'Employee removed.']);
            } else {
                // Update credentials
                $upd = $pdo->prepare('UPDATE `admins` SET password = ? WHERE id = ?');
                $upd->execute([$empPass, $empId]);
                echo json_encode(['ok' => true, 'message' => 'Employee credentials modified.']);
            }
        } else {
            // New entry creation
            if ($empPass === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Password required for new employee accounts.']);
                exit;
            }
            $ins = $pdo->prepare('INSERT INTO `admins` (id, password) VALUES (?, ?)');
            $ins->execute([$empId, $empPass]);
            echo json_encode(['ok' => true, 'message' => 'New employee profile provisioned.']);
        }
        exit;
    }

    // Standard channel updates (for both master and employee sessions)
    if (empty($_SESSION['admin_channel'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'No active channel context.']);
        exit;
    }

    $channel = $_SESSION['admin_channel'];
    $existing = getConfig($pdo, $channel);
    $upiId = trim($_POST['upiId'] ?? $existing['upi_id'] ?? '');
    $upiNumber = trim($_POST['upiNumber'] ?? $existing['upi_number'] ?? '');
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

    $stmt = $pdo->prepare('UPDATE `config` SET upi_id=?, upi_number=?, qr_image=?, total_price=?, advance_amount=?, addons_price=?, whatsapp_link=?, instagram_link=? WHERE id=?');
    $stmt->execute([$upiId, $upiNumber, $qrImage, $totalPrice, $advanceAmount, $addonsPrice, $whatsappLink, $instagramLink, $channel]);

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);