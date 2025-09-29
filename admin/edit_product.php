<?php
session_start();
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit();
}
include '../includes/db.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: view_products.php');
    exit();
}
$id = intval($_GET['id']);
$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
if (!$product) {
    header('Location: view_products.php');
    exit();
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    // Handle image deletion (only if a non-empty value was posted)
    $images = isset($product['images']) ? explode(',', $product['images']) : [];
    if (!empty($_POST['delete_image'])) {
        $del_img = $_POST['delete_image'];
        $images = array_filter($images, function($img) use ($del_img) { return $img !== $del_img; });
        $images_field = implode(',', $images);
        $stmt = $conn->prepare('UPDATE products SET images=? WHERE id=?');
        $stmt->bind_param('si', $images_field, $id);
        $stmt->execute();
        $stmt->close();
        // Optionally delete file from server: @unlink('../assets/images/' . $del_img);
        $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
        $message = 'Image deleted.';
    } else {
        // Handle up to 5 images
        $image_names = [];
        for ($i = 1; $i <= 5; $i++) {
            $input = 'image' . $i;
            if (isset($_FILES[$input]) && $_FILES[$input]['error'] === UPLOAD_ERR_OK) {
                $imgName = basename($_FILES[$input]['name']);
                $targetDir = '../assets/images/';
                $targetFile = $targetDir . uniqid('prod_') . '_' . $imgName;
                if (move_uploaded_file($_FILES[$input]['tmp_name'], $targetFile)) {
                    $image_names[] = basename($targetFile);
                }
            }
        }
        $images = isset($product['images']) ? explode(',', $product['images']) : [];
        if (count($image_names) > 0) {
            $images = array_merge($images, $image_names);
            $images = array_slice($images, 0, 5); // max 5
        }
        $images_field = implode(',', $images);
        // Only update images if new images were uploaded, otherwise keep existing images
        if (count($image_names) > 0) {
            $stmt = $conn->prepare('UPDATE products SET name=?, description=?, price=?, stock=?, images=? WHERE id=?');
            $stmt->bind_param('ssdisi', $name, $description, $price, $stock, $images_field, $id);
        } else {
            $stmt = $conn->prepare('UPDATE products SET name=?, description=?, price=?, stock=? WHERE id=?');
            // types: name (s), description (s), price (d), stock (i), id (i)
            $stmt->bind_param('ssdii', $name, $description, $price, $stock, $id);
        }
        if ($stmt->execute()) {
            $message = 'Product updated successfully!';
            $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
        } else {
            $message = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    }
}
$images = isset($product['images']) ? explode(',', $product['images']) : [];
?>
<?php include '../includes/header.php'; ?>
<div style="max-width:500px;margin:40px auto;background:var(--card-bg);border-radius:16px;box-shadow:0 2px 16px var(--border);padding:32px 28px 24px 28px;">
    <h2 style="text-align:center;color:var(--primary-dark);margin-bottom:24px;">Edit Product</h2>
    <?php if($message): ?><div style="text-align:center;color:var(--primary);margin-bottom:10px;font-weight:bold;"> <?php echo $message; ?> </div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required style="width:100%;padding:8px 10px;margin-bottom:10px;">
        <label>Description</label>
        <textarea name="description" required style="width:100%;padding:8px 10px;margin-bottom:10px;"><?php echo htmlspecialchars($product['description']); ?></textarea>
        <label>Price (LKR)</label>
        <input type="number" name="price" value="<?php echo $product['price']; ?>" min="0" step="0.01" required style="width:100%;padding:8px 10px;margin-bottom:10px;">
        <label>Stock</label>
        <input type="number" name="stock" value="<?php echo $product['stock']; ?>" min="0" required style="width:100%;padding:8px 10px;margin-bottom:10px;">
        <label>Product Images (max 5)</label><br>
        <div style="margin-bottom:16px;">
        <?php foreach($images as $img): ?>
            <div style="display:inline-block;position:relative;margin:4px 4px 10px 0;vertical-align:top;">
                <img src="../assets/images/<?php echo $img; ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                <button type="button" class="delete-image-btn" data-img="<?php echo $img; ?>" style="position:absolute;top:0;right:0;background:#b22222;color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:14px;cursor:pointer;">&times;</button>
            </div>
        <?php endforeach; ?>
        <input type="hidden" name="delete_image" id="delete_image_input" value="">
        </div>
        <?php for($i=1;$i<=5;$i++): ?>
            <input type="file" name="image<?php echo $i; ?>" accept="image/*"><br>
        <?php endfor; ?>
    <div style="clear:both;"></div>
    <button type="submit" style="margin:24px auto 0 auto;background:var(--primary);color:#fff;padding:14px 0;border:none;border-radius:16px;font-size:1.3em;font-weight:bold;display:block;width:90%;max-width:600px;box-shadow:0 2px 8px var(--border);text-align:center;">Update Product</button>
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-image-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('delete_image_input').value = btn.getAttribute('data-img');
                btn.form.submit();
            });
        });
    });
    </script>
</div>
<?php include '../includes/footer.php'; ?>
