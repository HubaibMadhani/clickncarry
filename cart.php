<?php
session_start();
include 'includes/db.php';
$user_id = $_SESSION['user_id'] ?? 0;

if(isset($_POST['add_to_cart'])){
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    if ($quantity < 1) $quantity = 1;

    // Check current stock and ensure product isn't archived (if column exists)
    $has_deleted = $GLOBALS['has_deleted_column'] ?? false;
    if ($has_deleted) {
        $stmt = $conn->prepare('SELECT stock FROM products WHERE id = ? AND deleted = 0');
    } else {
        $stmt = $conn->prepare('SELECT stock FROM products WHERE id = ?');
        // set a notice for admins that migrations should be run
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

    if (!$prod) {
        $_SESSION['cart_message'] = 'Product not found.';
    } else {
        // existing quantity in cart
        $stmt = $conn->prepare('SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->bind_param('ii', $user_id, $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        $existing = $row['quantity'] ?? 0;
        if ($existing + $quantity > $prod['stock']) {
            $_SESSION['cart_message'] = 'Cannot add to cart: not enough stock. Available: ' . $prod['stock'] . ' (in cart: ' . $existing . ')';
        } else {
            $conn->query("INSERT INTO cart(user_id, product_id, quantity) VALUES('$user_id','$product_id','$quantity') ON DUPLICATE KEY UPDATE quantity=quantity+'$quantity'");
            $_SESSION['cart_message'] = 'Added to cart.';
        }
    }
}
// Handle remove from cart
if (isset($_POST['remove_from_cart'])) {
    $remove_id = intval($_POST['remove_from_cart']);
    $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
    $stmt->bind_param('ii', $user_id, $remove_id);
    $stmt->execute();
    $stmt->close();
}

// If this was a POST action, redirect to avoid form resubmission and refresh view
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: cart.php');
    exit();
}

$cart_items = $conn->query("SELECT c.*, p.name, p.price FROM cart c 
                            JOIN products p ON c.product_id=p.id
                            WHERE c.user_id='".intval($user_id)."'");
$total = 0;
?>
<?php include 'includes/header.php'; ?>
<div class="cart-container">
<h2>Your Cart</h2>
<a href="index.php" style="margin-bottom:10px; display:inline-block;">Continue Shopping</a>
<?php if (!empty($_SESSION['cart_message'])): ?>
    <div style="margin:12px 0;padding:10px;border-radius:8px;background:#eef;"><?php echo htmlspecialchars($_SESSION['cart_message']); ?></div>
    <?php unset($_SESSION['cart_message']); ?>
<?php endif; ?>

<form method="POST" action="checkout.php">
<table>
<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th></th></tr>
<?php while($item = $cart_items->fetch_assoc()){
    $item_total = $item['price'] * $item['quantity'];
    $total += $item_total;
?>
<tr>
    <td><?php echo $item['name']; ?></td>
    <td><?php echo $item['price']; ?></td>
    <td><?php echo $item['quantity']; ?></td>
    <td><?php echo $item_total; ?></td>
    <td>
        <form method="POST" style="display:inline;" class="remove-form">
            <input type="hidden" name="remove_from_cart" value="<?php echo $item['product_id']; ?>">
            <button type="button" class="remove-btn" data-product="<?php echo $item['product_id']; ?>" style="background:#b22222;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;">Remove</button>
        </form>
    </td>
</tr>
<?php } ?>
<tr>
    <td colspan="3">Delivery</td>
    <td>500</td>
    <td></td>
</tr>
<tr>
    <td colspan="3"><strong>Total</strong></td>
    <td><strong><?php echo $total + 500; ?></strong></td>
    <td></td>
</tr>
</table>
<input type="submit" value="Checkout">
</form>

<!-- Confirmation modal -->
<div id="confirm-modal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
    <div style="background:var(--card-bg);padding:20px;border-radius:10px;max-width:400px;margin:0 auto;text-align:center;">
        <p id="confirm-text">Are you sure you want to remove this item from your cart?</p>
        <div style="margin-top:12px;">
            <button id="confirm-yes" style="background:#b22222;color:#fff;border:none;padding:8px 14px;border-radius:6px;margin-right:8px;">Yes, remove</button>
            <button id="confirm-no" style="background:#ddd;color:#222;border:none;padding:8px 14px;border-radius:6px;">Cancel</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let targetForm = null;
    document.querySelectorAll('.remove-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            // find closest form and store it
            targetForm = btn.closest('.remove-form');
            document.getElementById('confirm-modal').style.display = 'flex';
        });
    });
    document.getElementById('confirm-no').addEventListener('click', function() {
        document.getElementById('confirm-modal').style.display = 'none';
        targetForm = null;
    });
    document.getElementById('confirm-yes').addEventListener('click', function() {
        if (targetForm) targetForm.submit();
    });
});
</script>
</div>
<?php include 'includes/footer.php'; ?>
