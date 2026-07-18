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

// --- NEW: Profit Calculation Functions ---
function getTotalDeposits($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT SUM(amount) as total FROM deposits WHERE user_id = ? AND status = 'confirmed'");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

function getTotalWithdrawals($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT SUM(amount) as total FROM withdrawals WHERE user_id = ? AND status = 'confirmed'");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

function calculateProfit($user_id) {
    $total_deposits = getTotalDeposits($user_id);
    return $total_deposits * 0.15; // 15% profit
}

function getLastProfitTime($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT MAX(created_at) as last FROM transactions WHERE user_id = ? AND type = 'profit'");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['last'] ?? null;
}

function shouldAddProfit($user_id) {
    $last_time = getLastProfitTime($user_id);
    if (!$last_time) return true; // No profit ever added
    
    $last = new DateTime($last_time);
    $now = new DateTime();
    $diff = $now->getTimestamp() - $last->getTimestamp();
    return $diff >= 43200; // 12 hours = 43200 seconds
}

function addProfitIfNeeded($user_id) {
    if (shouldAddProfit($user_id)) {
        $profit = calculateProfit($user_id);
        if ($profit > 0) {
            updateBalance($user_id, $profit);
            addTransaction($user_id, 'credit', $profit, 'Profit (15%)');
            return true;
        }
    }
    return false;
}
?>
