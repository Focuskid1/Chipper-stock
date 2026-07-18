<?php
require_once 'includes/db.php';

// Check user
$username = 'admin';
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "User found!<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Password hash: " . $user['password'] . "<br>";
    echo "MD5('focus123'): " . md5('focus123') . "<br>";
    echo "Match? " . ($user['password'] == md5('focus123') ? "YES ✅" : "NO ❌");
} else {
    echo "User not found!";
}
?>
