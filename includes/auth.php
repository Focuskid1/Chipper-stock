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
        
        if (strtolower($user['username']) == 'admin') {
            $_SESSION['is_admin'] = true;
            redirect('/pages/admin.php');
        } else {
            $_SESSION['is_admin'] = false;
            redirect('/pages/dashboard.php');
        }
    } else {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Invalid credentials'];
        redirect('/pages/login.php');
    }
}

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = md5($_POST['password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $ref = isset($_POST['ref']) ? intval($_POST['ref']) : 0;
    
    // Check if username exists
    if (getUserByUsername($username)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Username already taken'];
        redirect('/pages/register.php' . ($ref > 0 ? '?ref=' . $ref : ''));
    }
    
    // Validate referrer exists
    if ($ref > 0) {
        $referrer = getUser($ref);
        if (!$referrer) {
            $ref = 0;
        }
    }
    
    // Generate referral code
    $code = generateReferralCode();
    
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (username, password, email, phone, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $email, $phone, $code, $ref]);
    $new_user_id = $db->lastInsertId();
    
    // If referred, add referral record and give $1 bonus instantly
    if ($ref > 0 && $new_user_id) {
        // Add referral record
        $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_id, bonus) VALUES (?, ?, 1)");
        $stmt->execute([$ref, $new_user_id]);
        
        // Give $1 referral bonus instantly
        addReferralBonus($ref);
    }
    
    $_SESSION['success'] = ['type' => 'success', 'message' => 'Registration successful! Login now.'];
    redirect('/pages/login.php');
}
?>
