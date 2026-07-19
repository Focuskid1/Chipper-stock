<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $usdt_address = trim($_POST['usdt_address']);
    $currency = $_POST['currency'] ?? 'USD';

    if ($currency == 'NGN') {
        $amount_usd = $amount / getExchangeRate();
    } else {
        $amount_usd = $amount;
    }

    if ($amount_usd < MINIMUM_WITHDRAWAL) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Minimum withdrawal is $' . MINIMUM_WITHDRAWAL];
    } elseif ($amount_usd > $user['balance']) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Insufficient balance'];
    } elseif (empty($usdt_address)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Please enter your USDT TRC20 address'];
    } else {
        $account_details = "USDT TRC20: $usdt_address";
        $stmt = $db->prepare("INSERT INTO withdrawals (user_id, amount, method, account_details, status) VALUES (?, ?, 'USDT TRC20', ?, 'pending')");
        $stmt->execute([$user['id'], $amount_usd, $account_details]);
        
        addTransaction($user['id'], 'pending', -$amount_usd, 'Withdrawal pending approval: $' . $amount_usd);
        
        $_SESSION['success'] = ['type' => 'success', 'message' => 'Withdrawal request submitted for approval. You will receive USDT once confirmed.'];
        redirect('/pages/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Withdraw – <?php echo SITE_NAME; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/chat.js"></script>
<style>
    .currency-converter {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px;
        margin: 15px 0;
        border-left: 4px solid #0d6efd;
    }
    .currency-converter .rate {
        font-size: 0.9rem;
        color: #6b7a93;
    }
    .currency-selector {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .currency-selector .btn-currency {
        padding: 6px 16px;
        border-radius: 30px;
        border: 2px solid #dce3ec;
        background: #fff;
        color: #3a4b5e;
        font-weight: 600;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .currency-selector .btn-currency.active {
        border-color: #0d6efd;
        background: #e8f4fd;
        color: #0d6efd;
    }
    .currency-selector .btn-currency:hover {
        border-color: #0d6efd;
    }
    .balance-display {
        background: #e8f4fd;
        border-radius: 12px;
        padding: 12px 20px;
        text-align: center;
        margin-bottom: 20px;
    }
    .balance-display .amount {
        font-size: 1.8rem;
        font-weight: 700;
        color: #0d1a2b;
    }
    .balance-display .label {
        font-size: 0.9rem;
        color: #6b7a93;
    }
</style>
</head>
<body>
<div class="container" style="max-width:500px; margin-top:5vh;">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">💵 Withdraw Your Earnings</h2>
        
        <div class="balance-display">
            <div class="label">Available Balance</div>
            <div class="amount" id="balanceDisplay">$<?php echo number_format($user['balance'], 2); ?></div>
            <div class="label" id="balanceNgn" style="font-size:0.9rem;">≈ ₦<?php echo number_format($user['balance'] * getExchangeRate(), 2); ?></div>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Withdrawals are processed in <strong>USDT (TRC20)</strong> only.
        </div>

        <div class="currency-converter">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>💱 Live Exchange Rate</strong>
                    <div class="rate">1 USD = <strong>₦<?php echo number_format(getExchangeRate(), 2); ?></strong></div>
                </div>
                <div class="currency-selector" id="currencySelector">
                    <button class="btn-currency active" data-currency="USD" onclick="setCurrency('USD')">USD ($)</button>
                    <button class="btn-currency" data-currency="NGN" onclick="setCurrency('NGN')">NGN (₦)</button>
                </div>
            </div>
        </div>

        <?php displayFlash('error'); displayFlash('success'); ?>
        <form method="POST" id="withdrawForm">
            <input type="hidden" name="currency" id="selectedCurrency" value="USD">
            
            <div class="mb-3">
                <label class="form-label" id="amountLabel">Amount ($)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="<?php echo MINIMUM_WITHDRAWAL; ?>" max="<?php echo $user['balance']; ?>" required>
                <small class="text-muted" id="amountHelp">Minimum withdrawal: $<?php echo MINIMUM_WITHDRAWAL; ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Your USDT TRC20 Address</label>
                <input type="text" name="usdt_address" class="form-control" placeholder="Enter your USDT TRC20 wallet address" required>
                <small class="text-muted">Make sure this is a valid TRC20 address</small>
            </div>

            <button type="submit" class="btn btn-warning w-100">Submit Withdrawal Request</button>
        </form>
        <p class="mt-3"><a href="/pages/dashboard.php">← Back to Dashboard</a></p>
    </div>
</div>

<script>
function setCurrency(currency) {
    document.getElementById('selectedCurrency').value = currency;
    
    document.querySelectorAll('.btn-currency').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.currency === currency) {
            btn.classList.add('active');
        }
    });
    
    var label = document.getElementById('amountLabel');
    var help = document.getElementById('amountHelp');
    var balanceDisplay = document.getElementById('balanceDisplay');
    var balanceNgn = document.getElementById('balanceNgn');
    var rate = <?php echo getExchangeRate(); ?>;
    var balance = <?php echo $user['balance']; ?>;
    var minWithdraw = <?php echo MINIMUM_WITHDRAWAL; ?>;
    
    if (currency === 'NGN') {
        label.textContent = 'Amount (₦)';
        help.textContent = 'Minimum withdrawal: ₦' + (minWithdraw * rate).toFixed(2);
        balanceDisplay.textContent = '₦' + (balance * rate).toFixed(2);
        balanceNgn.textContent = '≈ $' + balance.toFixed(2) + ' USD';
    } else {
        label.textContent = 'Amount ($)';
        help.textContent = 'Minimum withdrawal: $' + minWithdraw;
        balanceDisplay.textContent = '$' + balance.toFixed(2);
        balanceNgn.textContent = '≈ ₦' + (balance * rate).toFixed(2);
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
