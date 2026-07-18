<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);

// Get exchange rate
$exchange_rate = getExchangeRate();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $depositor_name = trim($_POST['depositor_name']);
    $depositor_phone = trim($_POST['depositor_phone']);
    $depositor_bank = trim($_POST['depositor_bank']);
    $transaction_ref = trim($_POST['transaction_ref']);
    $currency = $_POST['currency'] ?? 'USD';

    if ($amount < MINIMUM_DEPOSIT) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Minimum investment is $' . MINIMUM_DEPOSIT];
    } elseif (empty($depositor_name) || empty($depositor_phone) || empty($depositor_bank) || empty($transaction_ref)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'All fields are required.'];
    } else {
        // Store amount in USD (convert if NGN was selected)
        if ($currency == 'NGN') {
            $amount_usd = $amount / $exchange_rate;
        } else {
            $amount_usd = $amount;
        }
        
        $stmt = $db->prepare("INSERT INTO deposits (user_id, amount, method, status, depositor_name, depositor_phone, depositor_bank, transaction_ref) VALUES (?, ?, 'bank_transfer', 'pending', ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $amount_usd, $depositor_name, $depositor_phone, $depositor_bank, $transaction_ref]);
        
        $_SESSION['success'] = ['type' => 'success', 'message' => 'Deposit submitted for approval. You will be credited once confirmed.'];
        redirect('/pages/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Deposit – <?php echo SITE_NAME; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
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
    .currency-converter .rate strong {
        color: #0d1a2b;
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
    .min-deposit-badge {
        background: #e8f4fd;
        border-radius: 30px;
        padding: 4px 16px;
        font-size: 0.85rem;
        color: #0d6efd;
        font-weight: 600;
        display: inline-block;
    }
</style>
</head>
<body>
<div class="container" style="max-width:600px; margin-top:5vh;">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">💰 Fund Your AI Trading Account</h2>
        <?php displayFlash('error'); displayFlash('success'); ?>
        
        <!-- Minimum Deposit Badge -->
        <div class="text-center mb-3">
            <span class="min-deposit-badge">
                <i class="fas fa-info-circle"></i> Minimum Deposit: <strong>$<?php echo MINIMUM_DEPOSIT; ?></strong>
            </span>
        </div>
        
        <div class="card bg-light mb-4">
            <div class="card-body">
                <h5 class="card-title">Make a bank transfer to:</h5>
                <p><strong>Bank:</strong> <?php echo BANK_NAME; ?></p>
                <p><strong>Account Name:</strong> <?php echo ACCOUNT_NAME; ?></p>
                <p><strong>Account Number:</strong> <?php echo ACCOUNT_NUMBER; ?></p>
                <p><strong>Swift:</strong> <?php echo BANK_SWIFT; ?></p>
                <p class="text-muted">After sending, fill in the details below to confirm your investment.</p>
            </div>
        </div>

        <!-- Currency Converter -->
        <div class="currency-converter">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>💱 Live Exchange Rate</strong>
                    <div class="rate">1 USD = <strong>₦<?php echo number_format($exchange_rate, 2); ?></strong></div>
                </div>
                <div class="currency-selector" id="currencySelector">
                    <button class="btn-currency active" data-currency="USD" onclick="setCurrency('USD')">USD ($)</button>
                    <button class="btn-currency" data-currency="NGN" onclick="setCurrency('NGN')">NGN (₦)</button>
                </div>
            </div>
        </div>

        <form method="POST" id="depositForm">
            <input type="hidden" name="currency" id="selectedCurrency" value="USD">
            
            <div class="mb-3">
                <label class="form-label">Your Full Name (as on bank)</label>
                <input type="text" name="depositor_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Your Phone Number</label>
                <input type="text" name="depositor_phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Your Bank</label>
                <input type="text" name="depositor_bank" class="form-control" placeholder="e.g. GTBank, OPay" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Transaction Reference / Payment ID</label>
                <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. 123456789" required>
            </div>
            <div class="mb-3">
                <label class="form-label" id="amountLabel">Amount ($)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="<?php echo MINIMUM_DEPOSIT; ?>" required>
                <small class="text-muted" id="amountHelp">Minimum investment: $<?php echo MINIMUM_DEPOSIT; ?></small>
            </div>
            <button type="submit" class="btn btn-success w-100">Submit for Approval</button>
        </form>
        <p class="mt-3"><a href="/pages/dashboard.php">← Back to Dashboard</a></p>
    </div>
</div>

<script>
function setCurrency(currency) {
    document.getElementById('selectedCurrency').value = currency;
    
    // Update active button
    document.querySelectorAll('.btn-currency').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.currency === currency) {
            btn.classList.add('active');
        }
    });
    
    // Update label
    var label = document.getElementById('amountLabel');
    var help = document.getElementById('amountHelp');
    var rate = <?php echo $exchange_rate; ?>;
    var minDeposit = <?php echo MINIMUM_DEPOSIT; ?>;
    
    if (currency === 'NGN') {
        label.textContent = 'Amount (₦)';
        help.textContent = 'Minimum investment: ₦' + (minDeposit * rate).toFixed(2);
    } else {
        label.textContent = 'Amount ($)';
        help.textContent = 'Minimum investment: $' + minDeposit;
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
