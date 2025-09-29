<?php
session_start();
// Only allow admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit();
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
</style>
<div class="admin-upload-container">
    <h2>Upload New Product</h2>
    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <label for="image">Product Image</label>
        <input type="file" name="image" id="image" accept="image/*" required>

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
