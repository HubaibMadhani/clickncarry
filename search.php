<?php
include 'includes/db.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$q = $conn->real_escape_string($q);
$has_deleted = $GLOBALS['has_deleted_column'] ?? false;
if ($has_deleted) {
    $result = $conn->query("SELECT id,name FROM products WHERE name LIKE '%$q%' AND deleted = 0 LIMIT 10");
} else {
    $result = $conn->query("SELECT id,name FROM products WHERE name LIKE '%$q%' LIMIT 10");
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['migration_notice'])) {
        $_SESSION['migration_notice'] = 'Database migration required: `deleted` column not found. Run migrations/migrate.php or apply migrations/002_add_deleted_flag.sql.';
    }
}

$products = [];
while($row = $result->fetch_assoc()){
    $products[] = $row;
}

echo json_encode($products);
?>
