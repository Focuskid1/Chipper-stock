<?php
require_once 'functions.php';
require_once 'config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $user = getUserByUsername($username);
    if ($user && $user['password'] == $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = ($user['username'] == 'admin');
        redirect('/pages/dashboard.php');
    } else {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Invalid credentials'];
        redirect('/pages/login.php');
    }
}
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $ref = isset($_POST['ref']) ? $_POST['ref'] : 0;
    if (getUserByUsername($username)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Username already taken'];
        redirect('/pages/register.php');
    }
    $code = generateReferralCode();
    $stmt = $db->prepare("INSERT INTO users (username, password, email, phone, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $username, SQLITE3_TEXT);
    $stmt->bindValue(2, $password, SQLITE3_TEXT);
    $stmt->bindValue(3, $email, SQLITE3_TEXT);
    $stmt->bindValue(4, $phone, SQLITE3_TEXT);
    $stmt->bindValue(5, $code, SQLITE3_TEXT);
    $stmt->bindValue(6, $ref, SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $_SESSION['success'] = ['type' => 'success', 'message' => 'Registration successful. Login now.'];
        redirect('/pages/login.php');
    } else {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Registration failed'];
        redirect('/pages/register.php');
    }
}
?>
