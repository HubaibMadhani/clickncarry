<?php
// Accepts a JSON body with { ids: [1,2,3] } and returns a JSON map of id -> stock
// Respects deleted column if present.
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data) || empty($data['ids']) || !is_array($data['ids'])) {
    echo json_encode(new stdClass());
    exit;
}
$ids = array_map('intval', $data['ids']);
if (!count($ids)) {
    echo json_encode(new stdClass());
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$placeholders = implode(',', array_fill(0, count($ids), '?'));
// Build query carefully: if deleted column exists, filter deleted=0
$query = "SELECT id, stock FROM products WHERE id IN ($placeholders)";
if (!empty($GLOBALS['has_deleted_column'])) {
    $query .= " AND deleted = 0";
}

$stmt = $conn->prepare($query);
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();
$out = [];
while ($r = $result->fetch_assoc()) {
    $out[intval($r['id'])] = intval($r['stock']);
}

echo json_encode($out);
