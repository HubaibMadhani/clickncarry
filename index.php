<?php
session_start();
include 'includes/db.php';
// Only show products that are not archived/deleted. If the DB hasn't been migrated yet
// (so the `deleted` column doesn't exist), fall back to selecting all products and
// show an admin-visible message recommending running migrations.
try {
    // Prefer showing only available (non-deleted, in-stock) products on the storefront
    $products = $conn->query("SELECT * FROM products WHERE deleted = 0 AND stock > 0");
} catch (Throwable $e) {
    // older DB without deleted column: fall back to selecting only in-stock products
    try {
        $products = $conn->query("SELECT * FROM products WHERE stock > 0");
    } catch (Throwable $e2) {
        // ultimate fallback to selecting all products
        $products = $conn->query("SELECT * FROM products");
    }
    if (session_status() === PHP_SESSION_NONE) session_start();
    // only show a short admin hint
    if (empty($_SESSION['migration_notice'])) {
        $_SESSION['migration_notice'] = 'Database migration recommended: `deleted` column not found. Run migrations/migrate.php or apply migrations/002_add_deleted_flag.sql.';
    }
}
?>
<?php include 'includes/header.php'; ?>
</style>
<style>
:root {
    --primary: #6366f1;
    --primary-dark: #4338ca;
    --accent: #06b6d4;
    --accent-dark: #0e7490;
    --bg: #f4f6fb;
    --card-bg: #fff;
    --border: #e5e7eb;
    --text-main: #22223b;
    --text-light: #6b7280;
    --white: #fff;
}
.dark-mode {
    --primary: #6366f1;
    --primary-dark: #a5b4fc;
    --accent: #06b6d4;
    --accent-dark: #67e8f9;
    --bg: #181a20;
    --card-bg: #23263a;
    --border: #23263a;
    --text-main: #f3f4f6;
    --text-light: #a1a1aa;
    --white: #23263a;
}
body {
    background: var(--bg);
    color: var(--text-main);
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    padding: 0;
    transition: background 0.3s, color 0.3s;
}
header, .header {
    background: linear-gradient(90deg, var(--primary), var(--accent));
    color: #fff;
    padding: 28px 0 18px 0;
    text-align: center;
    border-bottom: 4px solid var(--primary-dark);
    box-shadow: 0 2px 12px var(--border);
}
.logo-round {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid var(--accent);
    background: #fff;
    margin-bottom: 10px;
    box-shadow: 0 2px 12px var(--border);
}
h1, h2, h3 {
    color: var(--primary-dark);
}
.search-bar-container {
    position: relative;
    width: 350px;
    margin: 0 auto 20px auto;
    background: var(--white);
    border-radius: 25px;
    box-shadow: 0 2px 8px var(--border);
    padding: 8px 0;
         cursor: pointer;
    border: 1.5px solid var(--accent);
}
#search {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border-radius: 25px;
    border: 1px solid var(--border);
    font-size: 16px;
    outline: none;
    background: var(--bg);
    color: var(--text-main);
}
.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-dark);
    font-size: 20px;
    cursor: pointer;
    transition: color 0.3s;
}
#search-results {
    text-align: center;
    margin-top: 10px;
    font-weight: bold;
    color: var(--accent-dark);
}
.products {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    justify-content: center;
    padding: 32px 0 20px 0;
    background: var(--card-bg);
    border-radius: 18px;
    margin-bottom: 30px;
    box-shadow: 0 2px 16px var(--border);
    border: 1.5px solid var(--primary);
}
.product-card {
    background: linear-gradient(135deg, var(--white) 70%, var(--bg) 100%);
    border: 2px solid var(--border);
    border-radius: 18px;
    box-shadow: 0 2px 8px var(--border);
    width: 260px;
    padding: 18px 16px 16px 16px;
    text-align: center;
    transition: box-shadow 0.2s, border 0.2s;
}
.product-card.fade-out {
    animation: fadeOut 0.45s ease forwards;
}
@keyframes fadeOut {
    from { opacity: 1; transform: translateY(0); }
    to { opacity: 0; transform: translateY(-8px); }
}
.product-card:hover {
    border: 2px solid var(--accent);
    box-shadow: 0 4px 16px var(--accent);
    background: var(--white);
}
.product-card img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 12px;
    background: var(--white);
    border: 2px solid var(--accent);
    margin-bottom: 10px;
}
.product-card h3 {
    color: var(--primary);
    margin: 10px 0 6px 0;
}
.product-card p {
    color: var(--text-light);
    font-size: 15px;
    min-height: 36px;
}
.product-card strong {
    color: var(--accent-dark);
    font-size: 17px;
    display: block;
    margin: 8px 0;
}
.product-card form {
    margin: 6px 0;
}
.product-card input[type="number"] {
    width: 50px;
    border-radius: 8px;
    border: 1px solid var(--border);
    padding: 4px 6px;
    background: var(--bg);
    color: var(--text-main);
}
.product-card input[type="submit"] {
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 18px;
    margin-left: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.2s, color 0.2s;
    box-shadow: 0 1px 4px var(--border);
}
.product-card input[type="submit"]:hover {
    background: var(--accent);
    color: var(--white);
}
footer, .footer {
    background: var(--card-bg);
    color: var(--primary-dark);
    text-align: center;
    padding: 18px 0 10px 0;
    border-top: 4px solid var(--primary);
    margin-top: 30px;
    box-shadow: 0 -2px 12px var(--border);
}
</style>
<div style="padding:20px 40px;">
    <div class="search-bar-container">
        <input type="text" id="search" placeholder="Search products...">
        <span class="search-icon" id="search-icon" style="cursor:pointer;">
            <!-- Modern SVG magnifying glass icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </span>
    </div>
    <div id="search-results"></div>
</div>

<div class="products" id="products-list">
<?php while($row = $products->fetch_assoc()){ ?>
        <div class="product-card" data-id="<?php echo intval($row['id']); ?>" data-name="<?php echo strtolower($row['name']); ?>" data-stock="<?php echo intval($row['stock']); ?>">
    <?php
    $imgs = isset($row['images']) ? explode(',', $row['images']) : [];
    $firstImg = isset($imgs[0]) && $imgs[0] ? $imgs[0] : 'default.png'; // fallback image
    ?>
    <img src="assets/images/<?php echo $firstImg; ?>" alt="<?php echo $row['name']; ?>">
        <h3><?php echo $row['name']; ?></h3>
        <p><?php echo $row['description']; ?></p>
        <strong>LKR <?php echo $row['price']; ?></strong>
    <?php $stock = intval($row['stock']); ?>
        <?php if ($stock <= 0): ?>
            <div style="margin:8px 0;color:#b22222;font-weight:bold;">Out of stock</div>
        <?php else: ?>
            <div style="margin:8px 0;color:var(--text-light);font-size:0.95em;">In stock: <?php echo $stock; ?></div>
        <?php endif; ?>
        <form method="POST" action="cart.php">
            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
            <input type="hidden" name="quantity" value="1">
            <input class="add-btn" type="submit" name="add_to_cart" value="Add to Cart">
        </form>
        <form method="POST" action="buy_now.php">
            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
            <input type="hidden" name="quantity" value="1">
            <input class="buy-btn" type="submit" name="buy_now" value="Buy Now">
        </form>
    </div>
<?php } ?>
</div>
<script>
// Theme toggle is handled in includes/header.php
const searchInput = document.getElementById('search');
const products = document.querySelectorAll('.product-card');
const searchResults = document.getElementById('search-results');
const searchIcon = document.getElementById('search-icon');

// Client-side stock enforcement: disable buttons if quantity > stock
function updateStockControls(card) {
    const stock = parseInt(card.getAttribute('data-stock') || '0', 10);
    const addBtn = card.querySelector('.add-btn');
    const buyBtn = card.querySelector('.buy-btn');
    if (stock <= 0) {
        if (addBtn) addBtn.disabled = true;
        if (buyBtn) buyBtn.disabled = true;
    } else {
        if (addBtn) addBtn.disabled = false;
        if (buyBtn) buyBtn.disabled = false;
    }
}
products.forEach(updateStockControls);

// Poll the server periodically for product stock changes and remove cards for sold-out items.
// This keeps the storefront current without a full page refresh.
function pollStocks() {
    const ids = Array.from(document.querySelectorAll('.product-card')).map(c => c.getAttribute('data-id'));
    if (!ids.length) return;
    fetch('api/product_stocks.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids })
    }).then(r => r.json()).then(data => {
        // data: { id: stock, ... }
        document.querySelectorAll('.product-card').forEach(card => {
            const id = card.getAttribute('data-id');
            const stock = parseInt(card.getAttribute('data-stock') || '0', 10);
            const serverStock = data && (id in data) ? parseInt(data[id], 10) : null;
            if (serverStock === null) return; // unknown, skip
            // update data-stock attribute
            card.setAttribute('data-stock', serverStock);
            // update stock display and controls
            const stockDiv = card.querySelector('div');
            if (serverStock <= 0) {
                // show out of stock and animate-removal
                if (stockDiv) stockDiv.textContent = 'Out of stock';
                if (!card.classList.contains('fade-out')) {
                    card.classList.add('fade-out');
                    // remove from layout after animation
                    setTimeout(() => { card.style.display = 'none'; }, 480);
                }
            } else {
                if (stockDiv) stockDiv.textContent = 'In stock: ' + serverStock;
                // if previously hidden/animated, restore it
                if (card.classList.contains('fade-out')) {
                    card.classList.remove('fade-out');
                }
                card.style.display = '';
            }
            updateStockControls(card);
        });
    }).catch(err => {
        // ignore transient errors
        console.warn('stock poll failed', err);
    });
}

// Poll every 10 seconds
setInterval(pollStocks, 10000);
// Also run once shortly after load
setTimeout(pollStocks, 1500);

function doSearch() {
    const query = searchInput.value.trim().toLowerCase();
    let found = false;
    let visibleCount = 0;
    products.forEach(card => {
        const name = card.getAttribute('data-name');
        if (query && name.includes(query)) {
            card.style.display = '';
            found = true;
            visibleCount++;
        } else if (!query) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    if (query) {
        if (found) {
            searchResults.textContent = 'Product is available.';
            searchResults.style.color = '#2a7a2a';
        } else {
            searchResults.textContent = 'Product not found.';
            searchResults.style.color = '#b22222';
        }
    } else {
        searchResults.textContent = '';
    }
}
searchInput.addEventListener('input', doSearch);
searchIcon.addEventListener('click', function(e) {
    e.preventDefault();
    doSearch();
});
searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') doSearch();
});
</script>
<?php include 'includes/footer.php'; ?>
