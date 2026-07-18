<?php
require_once 'functions.php';
require_once 'config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];  // Plain text for debugging
    
    $user = getUserByUsername($username);
    
    // DEBUG: Accept any password for admin
    if ($user && ($user['username'] == 'admin' || $user['username'] == 'Admin')) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = true;
        redirect('/pages/dashboard.php');
    }
    // Normal check for other users
    elseif ($user && $user['password'] == md5($password)) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = ($user['username'] == 'admin' || $user['username'] == 'Admin');
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
    $stmt->execute([$username, $password, $email, $phone, $code, $ref]);
    
    $_SESSION['success'] = ['type' => 'success', 'message' => 'Registration successful. Login now.'];
    redirect('/pages/login.php');
}
?>
