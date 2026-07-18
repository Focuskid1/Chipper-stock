<?php
require_once 'db.php';
require_once 'config.php';

function getUser($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserByUsername($username) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateBalance($user_id, $amount) {
    global $db;
    $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    return $stmt->execute([$amount, $user_id]);
}

function addTransaction($user_id, $type, $amount, $description) {
    global $db;
    $stmt = $db->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $type, $amount, $description]);
}

function getReferralCount($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE referred_by = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['count'] : 0;
}

function generateReferralCode() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function displayFlash($key) {
    if (isset($_SESSION[$key])) {
        echo '<div class="alert alert-' . $_SESSION[$key]['type'] . '">' . $_SESSION[$key]['message'] . '</div>';
        unset($_SESSION[$key]);
    }
}
?>
