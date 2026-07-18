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
    $stmt = $db->prepare("SELECT COALESCE(SUM(bonus), 0) as total FROM referrals WHERE referrer_id = ? AND bonus > 0");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

function getReferralBonusPending($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT COALESCE(SUM(bonus), 0) as total FROM referrals WHERE referrer_id = ? AND bonus = 0");
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
function getReferralMilestone($ref_count) {
    if ($ref_count >= 10) {
        return 10;
    }
    return 0;
}

function checkAndApplyReferralBonus($referrer_id) {
    global $db;
    $ref_count = getReferralCount($referrer_id);
    
    $milestone = getReferralMilestone($ref_count);
    
    if ($milestone > 0) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND type = 'referral_bonus' AND description LIKE ?");
        $stmt->execute([$referrer_id, '%' . $milestone . ' referrals%']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['count'] == 0) {
            $user = getUser($referrer_id);
            $bonus_amount = $user['balance'] * 0.20;
            
            if ($bonus_amount > 0) {
                $stmt = $db->prepare("UPDATE referrals SET bonus = ? WHERE referrer_id = ? AND bonus = 0");
                $stmt->execute([$bonus_amount, $referrer_id]);
                
                addTransaction($referrer_id, 'referral_bonus_pending', $bonus_amount, "Pending Referral Bonus (" . $milestone . " referrals) - 20% of balance (will credit in 24 hours)");
                
                return true;
            }
        }
    }
    return false;
}

function processPendingReferralBonuses($user_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT SUM(bonus) as total FROM referrals WHERE referrer_id = ? AND bonus > 0");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pending_total = $row['total'] ?? 0;
    
    if ($pending_total > 0) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND type = 'referral_bonus_credit' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['count'] == 0) {
            updateBalance($user_id, $pending_total);
            addTransaction($user_id, 'referral_bonus_credit', $pending_total, "Referral Bonus Credited (20% of balance)");
            
            $stmt = $db->prepare("UPDATE referrals SET bonus = 0 WHERE referrer_id = ? AND bonus > 0");
            $stmt->execute([$user_id]);
            
            return true;
        }
    }
    return false;
}

// --- CURRENCY CONVERSION FUNCTIONS ---
function getExchangeRate() {
    // Try to get live rate from API
    $api_url = "https://api.exchangerate-api.com/v4/latest/USD";
    
    // Cache the rate for 1 hour to avoid hitting API limits
    $cache_file = __DIR__ . '/../cache/exchange_rate.json';
    $cache_time = 3600; // 1 hour
    
    // Create cache directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../cache')) {
        mkdir(__DIR__ . '/../cache', 0777, true);
    }
    
    // Check if cache exists and is still valid
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data && isset($data['rate'])) {
            return $data['rate'];
        }
    }
    
    // Fetch live rate
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
                // Save to cache
                file_put_contents($cache_file, json_encode(['rate' => $rate, 'updated' => time()]));
                return $rate;
            }
        }
    } catch (Exception $e) {
        // Fallback to a default rate if API fails
        return 1550;
    }
    
    return 1550; // Fallback rate
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
