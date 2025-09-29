<?php
// Simple migration runner for this project.
// It applies SQL files in the migrations/ directory in alphabetical order
// and records applied files in a migrations table.

$basedir = __DIR__;
require_once $basedir . '/includes/db.php';

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

$files = glob($basedir . '/migrations/*.sql');
sort($files);
$applied_any = false;
foreach ($files as $file) {
    $fname = basename($file);
    if (isset($applied[$fname])) continue;

    echo "Applying $fname...\n";
    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "Failed to read $fname\n";
        continue;
    }

    if ($conn->multi_query($sql)) {
        // consume results
        do { while ($conn->more_results() && $conn->next_result()) { } } while ($conn->more_results());
        $stmt = $conn->prepare("INSERT INTO migrations (filename, applied_at) VALUES (?, NOW())");
        $stmt->bind_param('s', $fname);
        $stmt->execute();
        echo "Applied $fname\n";
        $applied_any = true;
    } else {
        echo "Failed to apply $fname: " . $conn->error . "\n";
        break;
    }
}

if (!$applied_any) echo "No new migrations to apply.\n";
else echo "Migrations complete.\n";

?>