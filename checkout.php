<?php
session_start();
include 'includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit();
}

// Fetch cart items
$cart_items = $conn->query("SELECT c.product_id, c.quantity, p.price, p.stock, p.name FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id='".intval($user_id)."'");
if (!$cart_items || $cart_items->num_rows === 0) {
    include 'includes/header.php';
    echo '<div style="max-width:700px;margin:40px auto;background:var(--card-bg);border-radius:12px;padding:28px;box-shadow:0 2px 12px var(--border);text-align:center;"><h2>Your cart is empty.</h2><p><a href="index.php">Shop now</a></p></div>';
    include 'includes/footer.php';
    exit();
}

$conn->begin_transaction();
try {
    while ($item = $cart_items->fetch_assoc()) {
        $product_id = intval($item['product_id']);
        $quantity = intval($item['quantity']);

        $has_deleted = $GLOBALS['has_deleted_column'] ?? false;
        if ($has_deleted) {
            $stmt = $conn->prepare('SELECT stock, price FROM products WHERE id=? AND deleted = 0 FOR UPDATE');
        } else {
            $stmt = $conn->prepare('SELECT stock, price FROM products WHERE id=? FOR UPDATE');
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['migration_notice'])) {
                $_SESSION['migration_notice'] = 'Database migration required: `deleted` column not found. Run migrations/migrate.php or apply migrations/002_add_deleted_flag.sql.';
            }
        }
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $prod = $res->fetch_assoc();
        $stmt->close();

    if (!$prod) throw new Exception('Product not found or unavailable: ' . $product_id);
        if ($prod['stock'] < $quantity) throw new Exception('Not enough stock for product id ' . $product_id);

        $total_price = $prod['price'] * $quantity;

        // Create order
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
    }

    // Clear cart
    $stmt = $conn->prepare('DELETE FROM cart WHERE user_id=?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $message = 'Checkout complete! Orders placed.';
} catch (Exception $e) {
    $conn->rollback();
    $message = 'Checkout failed: ' . $e->getMessage();
}

include 'includes/header.php';
?>
<div style="max-width:700px;margin:40px auto;background:var(--card-bg);border-radius:12px;padding:28px;box-shadow:0 2px 12px var(--border);text-align:center;">
    <h2><?php echo htmlspecialchars($message); ?></h2>
    <p><a href="index.php">Continue shopping</a></p>
</div>
<?php include 'includes/footer.php'; ?>
