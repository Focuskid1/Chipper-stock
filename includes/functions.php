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

function getReferralBonusEarned($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT COALESCE(SUM(bonus), 0) as total FROM referrals WHERE referrer_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
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
    
    if (!$last_time) {
        $total_deposits = getTotalDeposits($user_id);
        if ($total_deposits > 0) {
            return true;
        }
        return false;
    }
    
    $last = new DateTime($last_time);
    $now = new DateTime();
    $diff = $now->getTimestamp() - $last->getTimestamp();
    
    return $diff >= 86400; // 24 hours
}

function addProfitIfNeeded($user_id) {
    if (shouldAddProfit($user_id)) {
        $profit = calculateProfit($user_id);
        if ($profit > 0) {
            updateBalance($user_id, $profit);
            addTransaction($user_id, 'profit', $profit, 'Profit (15%) added');
            return true;
        }
    }
    return false;
}

// --- REFERRAL BONUS SYSTEM ---
function checkAndApplyReferralBonus($user_id) {
    $ref_count = getReferralCount($user_id);
    
    // Check if referral count is a multiple of 10 (10, 20, 30, etc.)
    if ($ref_count > 0 && $ref_count % 10 == 0) {
        // Check if bonus was already given for this milestone
        global $db;
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND type = 'referral_bonus' AND description LIKE ?");
        $stmt->execute([$user_id, 'Referral Bonus (10 referrals)%']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no bonus for this milestone, give it
        if ($row['count'] == 0) {
            $bonus_amount = getUser($user_id)['balance'] * 0.20; // 20% of current balance
            if ($bonus_amount > 0) {
                updateBalance($user_id, $bonus_amount);
                addTransaction($user_id, 'referral_bonus', $bonus_amount, "Referral Bonus (10 referrals) - 20% of balance");
                return true;
            }
        }
    }
    return false;
}
?>
