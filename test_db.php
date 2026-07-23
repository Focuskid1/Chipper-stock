<?php
$url = getenv('DATABASE_URL');
echo "DATABASE_URL is set: " . ($url ? '✅ Yes' : '❌ No') . "<br>";
if ($url) {
    try {
        $parsed = parse_url($url);
        $host = $parsed['host'];
        $port = $parsed['port'] ?? '5432';
        $dbname = ltrim($parsed['path'], '/');
        $user = $parsed['user'];
        $pass = $parsed['pass'];
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$pass";
        echo "DSN: " . htmlspecialchars($dsn) . "<br>";
        $db = new PDO($dsn);
        echo "✅ Connected successfully!";
    } catch (PDOException $e) {
        echo "❌ Connection failed: " . $e->getMessage();
        echo "<br>Code: " . $e->getCode();
    }
} else {
    echo "❌ DATABASE_URL not set!";
}
?>
