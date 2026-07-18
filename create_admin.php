<?php
require_once 'includes/db.php';
require_once 'includes/config.php';

// Check if admin exists
$stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "Admin already exists. Updating password...<br>";
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([md5('focus123')]);
    echo "Password updated to: focus123<br>";
} else {
    echo "Creating new admin user...<br>";
    $stmt = $db->prepare("INSERT INTO users (username, password, email, phone, referral_code) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', md5('focus123'), 'admin@chipper.com', '1234567890', 'ADMIN999']);
    echo "Admin created successfully!<br>";
}

// Verify
$stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = 'admin'");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<br><strong>Verification:</strong><br>";
echo "ID: " . $user['id'] . "<br>";
echo "Username: " . $user['username'] . "<br>";
echo "Password Hash: " . $user['password'] . "<br>";
echo "MD5('focus123'): " . md5('focus123') . "<br>";
echo "Match: " . ($user['password'] == md5('focus123') ? "✅ YES" : "❌ NO");
?>
