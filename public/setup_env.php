<?php
// ONE-TIME SETUP SCRIPT - DELETE AFTER USE
$secret = 'ezpaizy_setup_2026_xK9m';

if (!isset($_GET['token']) || $_GET['token'] !== $secret) {
    http_response_code(403);
    die('Forbidden');
}

$action = $_GET['action'] ?? 'check';
$envPath = __DIR__ . '/../.env';

if ($action === 'add_key') {
    $keyValue = $_POST['api_key'] ?? '';
    if (empty($keyValue)) {
        die('No API key provided.');
    }
    $envContent = file_get_contents($envPath);
    if (strpos($envContent, 'OPENROUTER_API_KEY') !== false) {
        $envContent = preg_replace('/^OPENROUTER_API_KEY=.*$/m', '', $envContent);
    }
    $newKey = "\nOPENROUTER_API_KEY=\"" . $keyValue . "\"\n";
    $envContent = rtrim($envContent) . $newKey;
    file_put_contents($envPath, $envContent);
    $cachePath = __DIR__ . '/../bootstrap/cache/config.php';
    if (file_exists($cachePath)) unlink($cachePath);
    echo "<h2 style='color:green'>SUCCESS! Key added and cache cleared.</h2>";
    echo "<p><a href='?token=$secret&action=delete'>Click here to delete this setup file</a></p>";
} elseif ($action === 'delete') {
    unlink(__FILE__);
    echo "<h2 style='color:green'>Setup file deleted. Done!</h2>";
} else {
    $envContent = file_get_contents($envPath);
    $hasKey = strpos($envContent, 'OPENROUTER_API_KEY') !== false;
    echo "<h2>EzPAIzy Setup</h2>";
    echo "<p>OPENROUTER_API_KEY: <strong>" . ($hasKey ? 'EXISTS' : 'MISSING') . "</strong></p>";
    if (!$hasKey) {
        echo "<form method='POST' action='?token=$secret&action=add_key'>";
        echo "<input type='text' name='api_key' placeholder='Paste API key here' style='width:500px;padding:8px'><br><br>";
        echo "<button type='submit' style='background:green;color:white;padding:10px 20px'>Add Key</button>";
        echo "</form>";
    } else {
        echo "<p><a href='?token=$secret&action=delete'>Delete this file</a></p>";
    }
}
?>
