<?php
$url = getenv('DATABASE_URL');
echo "DATABASE_URL is set: " . ($url ? '✅ Yes' : '❌ No') . "<br>";
echo "URL: " . htmlspecialchars($url) . "<br>";
if ($url) {
    try {
        $db = new PDO($url);
        echo "✅ Connected successfully!";
    } catch (PDOException $e) {
        echo "❌ Connection failed: " . $e->getMessage();
        echo "<br>Code: " . $e->getCode();
    }
} else {
    echo "❌ DATABASE_URL not set!";
}
?>
