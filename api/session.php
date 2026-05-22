<?php
session_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

echo json_encode([
    'authed' => !empty($_SESSION['admin_authed']),
    'channel' => $_SESSION['admin_channel'] ?? null,
    'is_master' => !empty($_SESSION['is_master'])
]);