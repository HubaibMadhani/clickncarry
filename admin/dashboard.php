<?php
include '../includes/db.php';
?>
<h2>Admin Dashboard</h2>
<div id="orders"></div>

<script>
function fetchOrders(){
    fetch('fetch_orders.php')
    .then(res => res.text())
    .then(data => {
        document.getElementById('orders').innerHTML = data;
    });
}

// Fetch orders every 5 seconds
setInterval(fetchOrders, 5000);
fetchOrders();
</script>
