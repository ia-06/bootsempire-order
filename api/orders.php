<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getPDO();

function saveUpload(array $file, string $prefix): ?string
{
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK)
        return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed, true))
        return null;
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = __DIR__ . '/../uploads/' . $filename;
    return move_uploaded_file($file['tmp_name'], $dest) ? $filename : null;
}

// POST — submit a new order (public, via link slug)
if ($method === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $waRaw = trim($_POST['whatsapp'] ?? '');
    $payMode = trim($_POST['payMode'] ?? 'advance');

    if (!$slug || !$name || !$waRaw) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing required fields.']);
        exit;
    }

    // Normalise whatsapp
    $waDigits = preg_replace('/\D/', '', $waRaw);
    if (strlen($waDigits) === 12 && str_starts_with($waDigits, '91')) {
        $waDigits = substr($waDigits, 2);
    }
    if (strlen($waDigits) !== 10) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid WhatsApp number.']);
        exit;
    }
    $whatsapp = '91' . $waDigits;

    // Resolve link
    $linkStmt = $pdo->prepare('SELECT * FROM `order_links` WHERE slug=?');
    $linkStmt->execute([$slug]);
    $link = $linkStmt->fetch();
    if (!$link) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Invalid order link.']);
        exit;
    }

    // Amount paid: full pay = total * qty, advance = advance_amount from link
    $paidAmount = $payMode === 'full'
        ? (int) $link['total_price'] * (int) $link['qty']
        : (int) $link['advance_amount'];

    $orderId = 'BE-' . strtoupper($slug) . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
    $id = sprintf(
        '%s-%s-%s-%s-%s',
        bin2hex(random_bytes(4)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(6))
    );

    $stmt = $pdo->prepare('
        INSERT INTO `orders`
        (id, order_id, name, whatsapp, advance_amount, status, link_slug, channel, pay_mode)
        VALUES (?,?,?,?,?,?,?,?,?)
    ');
    $stmt->execute([
        $id,
        $orderId,
        $name,
        $whatsapp,
        $paidAmount,
        'PENDING_ACTION',
        $slug,
        $link['channel'],
        $payMode
    ]);

    echo json_encode(['ok' => true, 'id' => $id, 'orderId' => $orderId, 'channel' => $link['channel']]);
    exit;
}

// All routes below require admin session
session_start();
if (empty($_SESSION['admin_authed'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

$channel = $_SESSION['admin_channel'] ?? null;

// GET — returns { orders: [...], links: [...] } for admin panel
if ($method === 'GET') {
    if (!$channel) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'No channel in session']);
        exit;
    }

    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/') . '/uploads/';

    // All orders joined with link config
    $orderSql = '
        SELECT
            o.id          AS order_row_id,
            o.order_id,
            o.name,
            o.whatsapp,
            o.pay_mode,
            o.advance_amount AS paid_amount,
            o.status      AS raw_status,
            o.created_at  AS submitted_at,
            o.confirmed_at,
            o.remarks,
            o.screenshot,
            l.slug,
            l.qty,
            l.total_price,
            l.advance_amount AS link_advance,
            l.channel
        FROM `orders` o
        INNER JOIN `order_links` l ON l.slug = o.link_slug
        WHERE l.channel = ?
        ORDER BY o.created_at DESC
    ';
    $oStmt = $pdo->prepare($orderSql);
    $oStmt->execute([$channel]);
    $orderRows = $oStmt->fetchAll();

    $statusMap = [
        'PENDING_ACTION' => 'PENDING_ACTION',
        'PAYMENT_MARKED' => 'PENDING_ACTION',
        'PENDING' => 'PENDING_ACTION',
        'CONFIRMED' => 'CONFIRMED',
        'REJECTED' => 'ISSUE_RAISED',
        'ISSUE_RAISED' => 'ISSUE_RAISED',
    ];

    foreach ($orderRows as &$row) {
        $row['display_status'] = $statusMap[$row['raw_status']] ?? 'PENDING_ACTION';
        if (!empty($row['screenshot'])) {
            $row['screenshotUrl'] = $base . $path . basename($row['screenshot']);
        }
    }
    unset($row);

    // All links with order count
    $linkSql = '
        SELECT
            l.id          AS link_id,
            l.slug,
            l.channel,
            l.qty,
            l.total_price,
            l.advance_amount,
            l.created_at  AS link_created_at,
            COUNT(o.id)   AS order_count
        FROM `order_links` l
        LEFT JOIN `orders` o ON o.link_slug = l.slug
        WHERE l.channel = ?
        GROUP BY l.id
        ORDER BY l.created_at DESC
    ';
    $lStmt = $pdo->prepare($linkSql);
    $lStmt->execute([$channel]);
    $linkRows = $lStmt->fetchAll();

    echo json_encode(['orders' => $orderRows, 'links' => $linkRows]);
    exit;
}

// PATCH — update status
if ($method === 'PATCH') {
    $body = json_decode(file_get_contents('php://input'), true);
    $id = $body['id'] ?? null;
    $status = $body['status'] ?? null;
    $remarks = $body['remarks'] ?? null;

    $allowed = ['CONFIRMED', 'ISSUE_RAISED'];
    if (!$id || !in_array($status, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing id or invalid status']);
        exit;
    }

    $confirmedAt = $status === 'CONFIRMED' ? date('Y-m-d H:i:s') : null;
    $remarksVal = $status === 'ISSUE_RAISED' ? $remarks : null;

    $stmt = $pdo->prepare('UPDATE `orders` SET status=?, confirmed_at=?, remarks=? WHERE id=?');
    $stmt->execute([$status, $confirmedAt, $remarksVal, $id]);

    echo json_encode(['ok' => true]);
    exit;
}

// DELETE — delete a single order by order_row_id, OR a link + all its orders by slug
if ($method === 'DELETE') {
    $body = json_decode(file_get_contents('php://input'), true);
    $slug = $body['slug'] ?? null;
    $orderRowId = $body['order_row_id'] ?? null;

    if ($orderRowId) {
        // Delete single order
        $row = $pdo->prepare('SELECT screenshot FROM `orders` WHERE id=?');
        $row->execute([$orderRowId]);
        $data = $row->fetch();
        if ($data && !empty($data['screenshot'])) {
            $f = __DIR__ . '/../uploads/' . basename($data['screenshot']);
            if (file_exists($f))
                @unlink($f);
        }
        $pdo->prepare('DELETE FROM `orders` WHERE id=?')->execute([$orderRowId]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($slug) {
        // Delete entire link + all its orders
        $rows = $pdo->prepare('SELECT screenshot FROM `orders` WHERE link_slug=?');
        $rows->execute([$slug]);
        foreach ($rows->fetchAll() as $data) {
            if (!empty($data['screenshot'])) {
                $f = __DIR__ . '/../uploads/' . basename($data['screenshot']);
                if (file_exists($f))
                    @unlink($f);
            }
        }
        $pdo->prepare('DELETE FROM `orders` WHERE link_slug=?')->execute([$slug]);
        $pdo->prepare('DELETE FROM `order_links` WHERE slug=? AND channel=?')->execute([$slug, $channel]);
        echo json_encode(['ok' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing slug or order_row_id']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);