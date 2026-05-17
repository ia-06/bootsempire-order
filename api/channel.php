<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false]);
    exit;
}

if (empty($_SESSION['admin_authed'])) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Not authenticated']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$ch   = $body['channel'] ?? '';

if (!in_array($ch, ['instagram','whatsapp'], true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Invalid channel']);
    exit;
}

$_SESSION['admin_channel'] = $ch;
echo json_encode(['ok'=>true,'channel'=>$ch]);
