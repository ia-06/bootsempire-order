<?php
/**
 * api/session.php — returns current session state (authed + channel)
 * Used by index.php on page load to skip login screen if already authed.
 */
session_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

echo json_encode([
    'authed'  => !empty($_SESSION['admin_authed']),
    'channel' => $_SESSION['admin_channel'] ?? null,
]);
