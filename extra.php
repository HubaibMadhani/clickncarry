

<input type="text" id="search" placeholder="Search products...">
<div id="search-results" style="border:1px solid #ccc; display:none; max-height:150px; overflow:auto;"></div>

<script>
const searchInput = document.getElementById('search');
const resultsDiv = document.getElementById('search-results');

searchInput.addEventListener('input', function() {
    const query = this.value;
    if(query.length < 1){
        resultsDiv.style.display = 'none';
        return;
    }

    fetch('search.php?q=' + query)
    .then(response => response.json())
    .then(data => {
        resultsDiv.innerHTML = '';
        if(data.length > 0){
            data.forEach(product => {
                const div = document.createElement('div');
                div.innerHTML = product.name;
                div.style.padding = "5px";
                div.style.cursor = "pointer";
                div.onclick = () => { window.location = 'product.php?id=' + product.id; }
                resultsDiv.appendChild(div);
            });
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.innerHTML = '<div style="padding:5px;">No products found</div>';
            resultsDiv.style.display = 'block';
        }
    });
});
</script>

<?php
session_start();
include 'includes/db.php';
// Only show non-archived products when the DB supports it
$has_deleted = $GLOBALS['has_deleted_column'] ?? false;
if ($has_deleted) {
    $products = $conn->query("SELECT * FROM products WHERE deleted = 0");
} else {
    $products = $conn->query("SELECT * FROM products");
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['migration_notice'])) {
        $_SESSION['migration_notice'] = 'Database migration required: `deleted` column not found. Run migrations/migrate.php or apply migrations/002_add_deleted_flag.sql.';
    }
}
?>
<h2>Welcome, <?php echo $_SESSION['name'] ?? 'Guest'; ?></h2>
<a href="cart.php">Go to Cart</a>
<?php while($row = $products->fetch_assoc()){ ?>
    <div style="border:1px solid #ccc; padding:10px; margin:10px;">
    <?php $imgs = isset($row['images']) ? explode(',', $row['images']) : []; $first = $imgs[0] ?? 'default.png'; ?>
    <img src="assets/images/<?php echo htmlspecialchars($first); ?>" width="100"><br>
        <strong><?php echo $row['name']; ?></strong><br>
        <?php echo $row['description']; ?><br>
        Price: LKR <?php echo $row['price']; ?><br>
        <form method="POST" action="cart.php">
            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
            Quantity: <input type="number" name="quantity" value="1" min="1"><br>
            <input type="submit" name="add_to_cart" value="Add to Cart">
        </form>
        <form method="POST" action="buy_now.php">
            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
            <input type="number" name="quantity" value="1" min="1">
            <input type="submit" name="buy_now" value="Buy Now">
        </form>
    </div>
<?php } ?>
/* Removed background override to use global gradient */
