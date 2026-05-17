<?php
session_start();
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false]);
    exit;
}

$inputId   = trim($_POST['id']       ?? '');
$inputPass = trim($_POST['password'] ?? '');

if ($inputId === ADMIN_ID && $inputPass === ADMIN_PASS) {
    session_regenerate_id(true);
    $_SESSION['admin_authed'] = true;
    echo json_encode(['ok'=>true]);
} else {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'Incorrect ID or password.']);
}
