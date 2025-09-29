<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "myshop";

$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
// Detect whether products.deleted column exists (some environments may not have run migrations yet)
$has_deleted_column = false;
try {
    $res = $conn->query("SHOW COLUMNS FROM products LIKE 'deleted'");
    if ($res && $res->num_rows > 0) {
        $has_deleted_column = true;
    }
} catch (Throwable $e) {
    // table might not exist or other DB state; just assume false
    $has_deleted_column = false;
}

// make it available globally
$GLOBALS['has_deleted_column'] = $has_deleted_column;
?>
