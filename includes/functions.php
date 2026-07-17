<?php
require_once 'db.php';
require_once 'config.php';

function getUser($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}
function getUserByUsername($username) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bindValue(1, $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}
function updateBalance($user_id, $amount) {
    global $db;
    $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->bindValue(1, $amount, SQLITE3_FLOAT);
    $stmt->bindValue(2, $user_id, SQLITE3_INTEGER);
    return $stmt->execute();
}
function addTransaction($user_id, $type, $amount, $description) {
    global $db;
    $stmt = $db->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(2, $type, SQLITE3_TEXT);
    $stmt->bindValue(3, $amount, SQLITE3_FLOAT);
    $stmt->bindValue(4, $description, SQLITE3_TEXT);
    return $stmt->execute();
}
function getReferralCount($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE referred_by = ?");
    $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray();
    return $row['count'];
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
