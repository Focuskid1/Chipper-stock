<?php
require_once 'includes/db.php';
$password = md5('focus123');
$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$password]);
echo "Password updated successfully!";
?>
