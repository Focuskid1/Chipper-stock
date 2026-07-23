<?php
echo "pdo_pgsql loaded: " . (extension_loaded('pdo_pgsql') ? '✅ Yes' : '❌ No') . "<br>";
$url = getenv('DATABASE_URL');
echo "DATABASE_URL set: " . ($url ? '✅ Yes' : '❌ No') . "<br>";
if ($url) {
    try {
        $db = new PDO($url);
        echo "✅ Connected successfully!";
    } catch (PDOException $e) {
        echo "❌ Connection failed: " . $e->getMessage();
    }
}
?>
