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
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if ($username && $email && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, 1)');
        $stmt->bind_param('sss', $username, $email, $hash);
        if ($stmt->execute()) {
            $message = 'Admin registered successfully!';
        } else {
            $message = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = 'Please fill all fields.';
    }
}
?>
<?php include '../includes/header.php'; ?>
<style>
.admin-register-container {
    max-width: 400px;
    margin: 40px auto;
    /* background: #fff; */
    border-radius: 16px;
    box-shadow: 0 2px 16px #e5e7eb;
    padding: 32px 28px 24px 28px;
}
.admin-register-container h2 {
    text-align: center;
    color: var(--primary-dark);
    margin-bottom: 24px;
}
.admin-register-container label {
    font-weight: 500;
    color: var(--primary);
    display: block;
    margin-top: 16px;
    margin-bottom: 6px;
}
.admin-register-container input {
    width: 100%;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-bottom: 10px;
    font-size: 1em;
    background: var(--bg);
    color: var(--text-main);
}
.admin-register-container button {
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
.admin-register-container button:hover {
    background: var(--accent);
}
.admin-register-container .message {
    text-align: center;
    color: var(--primary-dark);
    margin-bottom: 10px;
    font-weight: bold;
}
</style>
<div class="admin-register-container">
    <h2>Register New Admin</h2>
    <?php if($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
    <form method="POST">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Register Admin</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
