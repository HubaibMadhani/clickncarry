<?php
include '../includes/db.php';
$result = $conn->query("SELECT o.id, u.name as user, p.name as product, o.quantity, o.total_price 
                        FROM orders o 
                        JOIN users u ON o.user_id=u.id 
                        JOIN products p ON o.product_id=p.id
                        WHERE o.status='Pending'
                        ORDER BY o.created_at DESC");

echo "<h3>New Orders:</h3>";
echo "<ul>";
while($row = $result->fetch_assoc()){
    echo "<li>Order #".$row['id']." by ".$row['user']." - ".$row['product']." x".$row['quantity']." (LKR ".$row['total_price'].")</li>";
}
echo "</ul>";
?>
