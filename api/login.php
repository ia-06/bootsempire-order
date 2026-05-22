<?php
session_start();
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$inputId = trim($_POST['id'] ?? '');
$inputPass = trim($_POST['password'] ?? '');

if ($inputId === '') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'ID cannot be empty.']);
    exit;
}

// Check Master Admin first
if ($inputId === MASTER_ID && $inputPass === MASTER_PASS) {
    session_regenerate_id(true);
    $_SESSION['admin_authed'] = true;
    $_SESSION['is_master'] = true;
    echo json_encode(['ok' => true, 'is_master' => true]);
    exit;
}

// Check regular employee credentials against database
$pdo = getPDO();
$stmt = $pdo->prepare('SELECT password FROM `admins` WHERE id = ? LIMIT 1');
$stmt->execute([$inputId]);
$admin = $stmt->fetch();

if ($admin && $inputPass === $admin['password']) {
    session_regenerate_id(true);
    $_SESSION['admin_authed'] = true;
    $_SESSION['is_master'] = false;
    echo json_encode(['ok' => true, 'is_master' => false]);
} else {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Incorrect ID or password.']);
}