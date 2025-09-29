<?php
// Web migration runner (SAFE): only accessible from localhost and by logged-in admin.
// Usage: Open http://localhost/trade%20zone/migrations/run.php in your browser while logged in as admin.

session_start();
// Only allow localhost requests
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, ['127.0.0.1', '::1', 'localhost'])) {
    http_response_code(403);
    echo "Access denied. This migration runner can only be used from the server (localhost).\n";
    exit();
}

// Require admin session
if (empty($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo "Access denied. Please login as an admin to run migrations.\n";
    exit();
}

require_once __DIR__ . '/../includes/db.php';

// Ensure migrations table exists
$conn->query("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    applied_at DATETIME NOT NULL
)");

$applied = [];
$res = $conn->query("SELECT filename FROM migrations");
if ($res) {
    while ($r = $res->fetch_assoc()) $applied[$r['filename']] = true;
}

$files = glob(__DIR__ . '/*.sql');
sort($files);
$output = [];
$applied_any = false;
foreach ($files as $file) {
    $fname = basename($file);
    if (isset($applied[$fname])) {
        $output[] = "Skipping already-applied: $fname";
        continue;
    }
    $output[] = "Applying $fname...";
    $sql = file_get_contents($file);
    if ($sql === false) {
        $output[] = "Failed to read $fname";
        continue;
    }
    if ($conn->multi_query($sql)) {
        // consume results
        do { while ($conn->more_results() && $conn->next_result()) { } } while ($conn->more_results());
        $stmt = $conn->prepare("INSERT INTO migrations (filename, applied_at) VALUES (?, NOW())");
        $stmt->bind_param('s', $fname);
        $stmt->execute();
        $output[] = "Applied $fname";
        $applied_any = true;
    } else {
        $output[] = "Failed to apply $fname: " . $conn->error;
        break;
    }
}

if (!$applied_any) $output[] = "No new migrations to apply.";
else $output[] = "Migrations complete.";

// Simple HTML output
echo "<h2>Migration runner results</h2>";
echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
echo "<p>Tip: delete this file after use for safety.</p>";

?>
