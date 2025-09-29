<?php
session_start();
include 'includes/db.php';
$user_id = $_SESSION['user_id'] ?? 0;

if(isset($_POST['add_to_cart'])){
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    $conn->query("INSERT INTO cart(user_id, product_id, quantity) VALUES('$user_id','$product_id','$quantity')
                  ON DUPLICATE KEY UPDATE quantity=quantity+'$quantity'");
}

$cart_items = $conn->query("SELECT c.*, p.name, p.price FROM cart c 
                            JOIN products p ON c.product_id=p.id
                            WHERE c.user_id='$user_id'");
$total = 0;
?>
<h2>Your Cart</h2>
<a href="index.php">Continue Shopping</a>
<form method="POST" action="checkout.php">
<table border="1">
<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
<?php while($item = $cart_items->fetch_assoc()){
    $item_total = $item['price'] * $item['quantity'];
    $total += $item_total;
?>
<tr>
    <td><?php echo $item['name']; ?></td>
    <td><?php echo $item['price']; ?></td>
    <td><?php echo $item['quantity']; ?></td>
    <td><?php echo $item_total; ?></td>
</tr>
<?php } ?>
<tr>
    <td colspan="3">Delivery</td>
    <td>500</td>
</tr>
<tr>
    <td colspan="3"><strong>Total</strong></td>
    <td><strong><?php echo $total + 500; ?></strong></td>
</tr>
</table>
<input type="submit" value="Checkout">
</form>
