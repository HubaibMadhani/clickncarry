<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$user_id = intval($_SESSION['user_id']);
$theme = $_POST['theme'] ?? '';
if ($theme !== 'light' && $theme !== 'dark') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid theme']);
    exit();
}

$stmt = $conn->prepare('UPDATE users SET theme = ? WHERE id = ?');
$stmt->bind_param('si', $theme, $user_id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
