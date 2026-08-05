<?php
// Temporary cleanup script - DELETE AFTER USE
$host = '127.0.0.1';
$port = 3307;
$db = 'ezpaizy';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Show current orphaned 'asdasd' records
    $stmt = $pdo->query("SELECT id, student_id, title, topic, score, created_at FROM progress WHERE title = 'asdasd'");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Found " . count($records) . " asdasd progress records:</h3><pre>";
    print_r($records);
    echo "</pre>";
    
    // Delete them
    $deleted = $pdo->exec("DELETE FROM progress WHERE title = 'asdasd'");
    echo "<h3 style='color:green'>✅ Deleted $deleted record(s) with title = 'asdasd'</h3>";
    
    // Verify
    $remaining = $pdo->query("SELECT COUNT(*) FROM progress WHERE title = 'asdasd'")->fetchColumn();
    echo "<p>Remaining 'asdasd' records: $remaining</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
