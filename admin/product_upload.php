<?php
session_start();
// Only allow admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit();
}
include '../includes/db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $count = intval($_POST['count']);
    $price = floatval($_POST['price']);
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
    $images_field = implode(',', $image_names);
    if ($title && $description && $count >= 0 && $price >= 0 && count($image_names) > 0) {
    $stmt = $conn->prepare('INSERT INTO products (name, description, images, stock, price) VALUES (?, ?, ?, ?, ?)');
    // types: name(s), description(s), images(s), stock(i), price(d)
    $stmt->bind_param('sssid', $title, $description, $images_field, $count, $price);
        if ($stmt->execute()) {
            $message = 'Product uploaded successfully!';
        } else {
            $message = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    } elseif (!$message) {
        $message = 'Please fill all fields and upload at least one image.';
    }
}
?>
<?php include '../includes/header.php'; ?>
<style>
.admin-upload-container {
    max-width: 480px;
    margin: 40px auto;
    /* background: #fff; */
    border-radius: 16px;
    box-shadow: 0 2px 16px #e5e7eb;
    padding: 32px 28px 24px 28px;
}
.admin-upload-container h2 {
    text-align: center;
    color: var(--primary-dark);
    margin-bottom: 24px;
}
.admin-upload-container label {
    font-weight: 500;
    color: var(--primary);
    display: block;
    margin-top: 16px;
    margin-bottom: 6px;
}
.admin-upload-container input,
.admin-upload-container textarea {
    width: 100%;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-bottom: 10px;
    font-size: 1em;
    background: var(--bg);
    color: var(--text-main);
}
.admin-upload-container input[type="file"] {
    background: #fff;
    color: var(--text-main);
    border: none;
}
.admin-upload-container button {
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 24px;
    font-size: 1.1em;
    font-weight: bold;
    margin-top: 10px;
    cursor: pointer;
    transition: background 0.2s;
}
.admin-upload-container button:hover {
    background: var(--accent);
}
.admin-upload-container .message {
    text-align: center;
    color: var(--primary-dark);
    margin-bottom: 10px;
    font-weight: bold;
}
</style>
<div class="admin-upload-container">
    <h2>Upload New Product</h2>
    <?php if($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Product Images (max 5)</label><br>
        <?php for($i=1;$i<=5;$i++): ?>
            <input type="file" name="image<?php echo $i; ?>" accept="image/*" <?php echo $i==1 ? 'required' : ''; ?>><br>
        <?php endfor; ?>

        <label for="title">Title</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Description</label>
        <textarea name="description" id="description" rows="3" required></textarea>

        <label for="count">Count in Stock</label>
        <input type="number" name="count" id="count" min="0" required>

        <label for="price">Price (LKR)</label>
        <input type="number" name="price" id="price" min="0" step="0.01" required>

        <button type="submit">Upload Product</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
