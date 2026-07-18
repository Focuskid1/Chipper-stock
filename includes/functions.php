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
    $stmt = $db->prepare("INSERT INTO transactions (user_id, type, amount, description, created_at) VALUES (?, ?, ?, ?, NOW())");
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

// --- Profit Calculation Functions ---
function getTotalDeposits($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE user_id = ? AND status = 'confirmed'");
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
    $stmt = $db->prepare("SELECT created_at FROM transactions WHERE user_id = ? AND type = 'profit' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['created_at'] ?? null;
}

function shouldAddProfit($user_id) {
    $last_time = getLastProfitTime($user_id);
    
    // If no profit has ever been added, allow it
    if (!$last_time) {
        // Check if user has any confirmed deposits
        $total_deposits = getTotalDeposits($user_id);
        if ($total_deposits > 0) {
            return true; // First profit addition
        }
        return false; // No deposits yet
    }
    
    $last = new DateTime($last_time);
    $now = new DateTime();
    $diff = $now->getTimestamp() - $last->getTimestamp();
    
    // Return true only if 12 hours (43200 seconds) have passed
    return $diff >= 43200;
}

function addProfitIfNeeded($user_id) {
    // Check if we should add profit (only once per 12 hours)
    if (shouldAddProfit($user_id)) {
        $profit = calculateProfit($user_id);
        if ($profit > 0) {
            // Update balance
            updateBalance($user_id, $profit);
            // Record transaction
            addTransaction($user_id, 'profit', $profit, 'Profit (15%) added');
            return true;
        }
    }
    return false;
}
?>
