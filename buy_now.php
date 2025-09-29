<?php
session_start();
include 'includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
if ($quantity < 1) $quantity = 1;

// Start transaction
$conn->begin_transaction();
try {
    $has_deleted = $GLOBALS['has_deleted_column'] ?? false;
    if ($has_deleted) {
        $stmt = $conn->prepare('SELECT name, price, stock FROM products WHERE id=? AND deleted = 0 FOR UPDATE');
    } else {
        $stmt = $conn->prepare('SELECT name, price, stock FROM products WHERE id=? FOR UPDATE');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['migration_notice'])) {
            $_SESSION['migration_notice'] = 'Database migration required: `deleted` column not found. Run migrations/migrate.php or apply migrations/002_add_deleted_flag.sql.';
        }
    }
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();
    $stmt->close();

    if (!$product) throw new Exception('Product not found or unavailable.');
    if ($product['stock'] < $quantity) throw new Exception('Not enough stock available.');

    $total_price = $product['price'] * $quantity;

    // Insert order
    $status = 'Pending';
    $stmt = $conn->prepare('INSERT INTO orders (user_id, product_id, quantity, total_price, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->bind_param('iiids', $user_id, $product_id, $quantity, $total_price, $status);
    if (!$stmt->execute()) throw new Exception('Failed to create order: ' . $stmt->error);
    $stmt->close();

    // Decrement stock
    $stmt = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
    $stmt->bind_param('ii', $quantity, $product_id);
    if (!$stmt->execute()) throw new Exception('Failed to update stock: ' . $stmt->error);
    $stmt->close();

    $conn->commit();
    $message = 'Purchase successful! Order placed.';
} catch (Exception $e) {
    $conn->rollback();
    $message = 'Purchase failed: ' . $e->getMessage();
}
include 'includes/header.php';
?>
<div style="max-width:700px;margin:40px auto;background:var(--card-bg);border-radius:12px;padding:28px;box-shadow:0 2px 12px var(--border);text-align:center;">
    <h2><?php echo htmlspecialchars($message); ?></h2>
    <p><a href="index.php">Continue shopping</a></p>
</div>
<?php include 'includes/footer.php'; ?>
