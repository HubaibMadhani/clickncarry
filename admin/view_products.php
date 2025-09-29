<?php
session_start();
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit();
}
include '../includes/db.php';
// Handle delete -> archive (soft-delete) so product disappears from the default listing
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Mark as deleted (archive) and remove from carts
    $adminId = intval($_SESSION['user_id'] ?? 0);
    $conn->query("UPDATE products SET deleted = 1, archived_by = $adminId, archived_at = NOW() WHERE id = $del_id");
    $conn->query("DELETE FROM cart WHERE product_id=$del_id");
    // Optionally remove image files for this product if they exist (keep files if you prefer)
    $pRes = $conn->query("SELECT images FROM products WHERE id = $del_id");
    if ($pRes && $pRes->num_rows) {
        $imgsStr = $pRes->fetch_assoc()['images'];
        if (!empty($imgsStr)) {
            $imgs = explode(',', $imgsStr);
            foreach ($imgs as $img) {
                $img = trim($img);
                if ($img === '') continue;
                $imgPath = __DIR__ . '/../assets/images/' . $img;
                if (file_exists($imgPath)) {
                    @unlink($imgPath);
                }
            }
        }
    }
    $_SESSION['product_message'] = 'Product archived (hidden from store).';
    header('Location: view_products.php');
    exit();
}

// Handle permanent delete (only allowed when no orders reference the product)
if (isset($_GET['permanent']) && is_numeric($_GET['permanent'])) {
    $per_id = intval($_GET['permanent']);
    // If there are any orders referencing this product, block permanent deletion
    $orderCheck = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE product_id = $per_id");
    $oc = $orderCheck->fetch_assoc();
    if ($oc && intval($oc['cnt']) > 0) {
        $_SESSION['product_message'] = 'Cannot permanently delete product: there are existing orders referencing it.';
        header('Location: view_products.php');
        exit();
    }

    // Safe to delete permanently: remove any cart rows that reference the product
    $conn->query("DELETE FROM cart WHERE product_id=$per_id");

    // Remove image files for this product if they exist
    $pRes = $conn->query("SELECT images FROM products WHERE id = $per_id");
    if ($pRes && $pRes->num_rows) {
        $imgsStr = $pRes->fetch_assoc()['images'];
        if (!empty($imgsStr)) {
            $imgs = explode(',', $imgsStr);
            foreach ($imgs as $img) {
                $img = trim($img);
                if ($img === '') continue;
                $imgPath = __DIR__ . '/../assets/images/' . $img;
                if (file_exists($imgPath)) {
                    @unlink($imgPath);
                }
            }
        }
    }

    // Finally delete the product row
    $conn->query("DELETE FROM products WHERE id=$per_id");
    $_SESSION['product_message'] = 'Product permanently deleted.';
    header('Location: view_products.php');
    exit();
}
// Admin view: allow switching between all products and archived products
$has_deleted = $GLOBALS['has_deleted_column'] ?? false;
$tab = $_GET['tab'] ?? 'all';
if ($has_deleted) {
    if ($tab === 'archived') {
        $products = $conn->query("SELECT * FROM products WHERE deleted = 1");
    } else {
        // show everything to admins so they can manage restores/deletes
        $products = $conn->query("SELECT * FROM products");
    }
} else {
    // DB not migrated yet: show products but disable archive/restore options
    $products = $conn->query("SELECT * FROM products");
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['migration_notice'])) {
        $_SESSION['migration_notice'] = 'Database migration required: `deleted` column not found. Run migrations/migrate.php or apply migrations/002_add_deleted_flag.sql.';
    }
}

// Handle archive (soft-delete)
if (isset($_GET['archive']) && is_numeric($_GET['archive'])) {
    $aid = intval($_GET['archive']);
    $adminId = intval($_SESSION['user_id'] ?? 0);
    $conn->query("UPDATE products SET deleted = 1, archived_by = $adminId, archived_at = NOW() WHERE id = $aid");
    // Remove product from all user carts to avoid stale items
    $conn->query("DELETE FROM cart WHERE product_id = $aid");
    $_SESSION['product_message'] = 'Product archived (hidden from store).';
    header('Location: view_products.php');
    exit();
}

// Handle restore
if (isset($_GET['restore']) && is_numeric($_GET['restore'])) {
    $rid = intval($_GET['restore']);
    $conn->query("UPDATE products SET deleted = 0, archived_by = NULL, archived_at = NULL WHERE id = $rid");
    $_SESSION['product_message'] = 'Product restored.';
    header('Location: view_products.php');
    exit();
}
?>
<?php include '../includes/header.php'; ?>
<?php if (!empty($_SESSION['product_message'])): ?>
    <div style="max-width:900px;margin:14px auto;padding:12px;border-radius:8px;background:#ffefef;color:#7a1f1f;"><?php echo htmlspecialchars($_SESSION['product_message']); ?></div>
    <?php unset($_SESSION['product_message']); ?>
<?php endif; ?>
<div style="max-width:900px;margin:40px auto 30px auto;background:var(--card-bg);border-radius:18px;box-shadow:0 2px 16px var(--border);padding:32px 28px 24px 28px;">
    <h2 style="text-align:center;color:var(--primary-dark);margin-bottom:24px;">All Uploaded Products</h2>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:var(--primary);color:#fff;">
                <th style="padding:10px 8px;">Image</th>
                <th style="padding:10px 8px;">Name</th>
                <th style="padding:10px 8px;">Description</th>
                <th style="padding:10px 8px;">Price</th>
                <th style="padding:10px 8px;">Stock</th>
                <th style="padding:10px 8px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $products->fetch_assoc()){ ?>
            <tr style="border-bottom:1px solid var(--border);background:var(--card-bg);">
                <td style="padding:8px;text-align:center;">
                <?php 
                $imgs = isset($row['images']) ? explode(',', $row['images']) : [];
                $firstImg = isset($imgs[0]) && $imgs[0] ? $imgs[0] : 'default.png'; // fallback image
                ?>
                <img src="../assets/images/<?php echo $firstImg; ?>" alt="<?php echo $row['name']; ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:2px solid var(--accent);background:#fff;margin:2px;">
                </td>
                <td style="padding:8px;font-weight:bold;color:var(--primary);text-align:center;"> <?php echo $row['name']; ?> </td>
                <td style="padding:8px;color:var(--text-main);font-size:0.98em;"> <?php echo $row['description']; ?> </td>
                <td style="padding:8px;color:var(--accent-dark);text-align:center;">LKR <?php echo $row['price']; ?> </td>
                <td style="padding:8px;text-align:center;"> <?php echo $row['stock']; ?> </td>
                <td style="padding:8px;text-align:center;">
                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" style="color:var(--primary);font-weight:bold;margin-right:10px;">Edit</a>
                    <?php if (isset($row['deleted']) && intval($row['deleted']) === 1): ?>
                        <a href="view_products.php?restore=<?php echo $row['id']; ?>" style="color:var(--accent-dark);font-weight:bold;margin-right:10px;" onclick="return confirm('Restore this product?');">Restore</a>
                        <a href="view_products.php?permanent=<?php echo $row['id']; ?>" style="color:#b22222;font-weight:bold;" onclick="return confirm('Permanently delete this product? This will fail if orders exist.');">Permanently Delete</a>
                    <?php else: ?>
                        <a href="view_products.php?delete=<?php echo $row['id']; ?>" style="color:#f59e0b;font-weight:bold;margin-right:10px;" onclick="return confirm('Archive (hide) this product from the store?');">Archive</a>
                        <a href="view_products.php?archive=<?php echo $row['id']; ?>" style="display:none;">&nbsp;</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
