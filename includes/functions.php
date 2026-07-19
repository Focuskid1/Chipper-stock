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
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'referral_bonus'");
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
    return $total_deposits * 0.15;
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
    
    return $diff >= 86400;
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

// --- REFERRAL BONUS SYSTEM ($1 per referral instantly) ---
function addReferralBonus($referrer_id) {
    $bonus_amount = 1.00; // $1 per referral
    
    // Add to balance
    updateBalance($referrer_id, $bonus_amount);
    
    // Record transaction
    addTransaction($referrer_id, 'referral_bonus', $bonus_amount, 'Referral bonus: $1');
    
    return true;
}

// --- CURRENCY CONVERSION FUNCTIONS ---
function getExchangeRate() {
    $api_url = "https://api.exchangerate-api.com/v4/latest/USD";
    $cache_file = __DIR__ . '/../cache/exchange_rate.json';
    $cache_time = 3600;
    
    if (!file_exists(__DIR__ . '/../cache')) {
        mkdir(__DIR__ . '/../cache', 0777, true);
    }
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data && isset($data['rate'])) {
            return $data['rate'];
        }
    }
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['rates']['NGN'])) {
                $rate = $data['rates']['NGN'];
                file_put_contents($cache_file, json_encode(['rate' => $rate, 'updated' => time()]));
                return $rate;
            }
        }
    } catch (Exception $e) {
        return 1550;
    }
    
    return 1550;
}

function formatCurrency($amount, $currency = 'USD') {
    if ($currency == 'NGN') {
        return '₦' . number_format($amount, 2);
    }
    return '$' . number_format($amount, 2);
}

function convertCurrency($amount, $from = 'USD', $to = 'NGN') {
    if ($from == $to) return $amount;
    
    $rate = getExchangeRate();
    if ($from == 'USD' && $to == 'NGN') {
        return $amount * $rate;
    } elseif ($from == 'NGN' && $to == 'USD') {
        return $amount / $rate;
    }
    return $amount;
}
?>

// --- REFERRAL BONUS FUNCTIONS ---
function getReferralBonusBalance($user_id) {
    global $db;
    // Get total referral bonuses earned
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'referral_bonus'");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

function getReferralBonusWithdrawable($user_id) {
    $ref_count = getReferralCount($user_id);
    if ($ref_count >= 20) {
        return getReferralBonusBalance($user_id);
    }
    return 0;
}

function processReferralBonusToBalance($user_id) {
    $ref_count = getReferralCount($user_id);
    if ($ref_count >= 20) {
        $bonus_amount = getReferralBonusBalance($user_id);
        if ($bonus_amount > 0) {
            // Add to balance
            updateBalance($user_id, $bonus_amount);
            // Record transaction
            addTransaction($user_id, 'credit', $bonus_amount, 'Referral bonus released (20+ referrals)');
            return true;
        }
    }
    return false;
}
