<?php
$url = getenv('DATABASE_URL');
echo "DATABASE_URL is set: " . ($url ? '✅ Yes' : '❌ No') . "<br>";
if ($url) {
    try {
        $db = new PDO($url);
        echo "✅ Connected successfully!";
    } catch (PDOException $e) {
        echo "❌ Connection failed: " . $e->getMessage();
    }
} else {
    echo "❌ DATABASE_URL not set!";
}
?>
