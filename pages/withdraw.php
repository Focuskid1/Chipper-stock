<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);

// Get exchange rate
$exchange_rate = getExchangeRate();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $account_name = trim($_POST['account_name']);
    $account_number = trim($_POST['account_number']);
    $bank_name = trim($_POST['bank_name']);
    $currency = $_POST['currency'] ?? 'USD';
    $account_details = "Bank: $bank_name | Account: $account_number | Name: $account_name";

    // Convert amount to USD if NGN was selected
    if ($currency == 'NGN') {
        $amount_usd = $amount / $exchange_rate;
    } else {
        $amount_usd = $amount;
    }

    if ($amount_usd < MINIMUM_WITHDRAWAL) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Minimum withdrawal is $' . MINIMUM_WITHDRAWAL];
    } elseif ($amount_usd > $user['balance']) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Insufficient balance'];
    } elseif (empty($account_name) || empty($account_number) || empty($bank_name)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'All fields are required.'];
    } else {
        $stmt = $db->prepare("INSERT INTO withdrawals (user_id, amount, method, account_details, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$user['id'], $amount_usd, $method, $account_details]);
        
        addTransaction($user['id'], 'pending', -$amount_usd, 'Withdrawal pending approval: $' . $amount_usd);
        
        $_SESSION['success'] = ['type' => 'success', 'message' => 'Withdrawal request submitted for approval.'];
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../assets/js/chat.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../assets/js/chat.js"></script>
</head>
<body>
<div class="container" style="max-width:500px; margin-top:5vh;">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">💵 Withdraw Your Earnings</h2>
        
        <div class="balance-display">
            <div class="label">Available Balance</div>
            <div class="amount" id="balanceDisplay">$<?php echo number_format($user['balance'], 2); ?></div>
            <div class="label" id="balanceNgn" style="font-size:0.9rem;">≈ ₦<?php echo number_format($user['balance'] * $exchange_rate, 2); ?></div>
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

        <?php displayFlash('error'); displayFlash('success'); ?>
        <form method="POST" id="withdrawForm">
            <input type="hidden" name="currency" id="selectedCurrency" value="USD">
            
            <div class="mb-3">
                <label class="form-label" id="amountLabel">Amount ($)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="<?php echo MINIMUM_WITHDRAWAL; ?>" max="<?php echo $user['balance']; ?>" required>
                <small class="text-muted" id="amountHelp">Minimum withdrawal: $<?php echo MINIMUM_WITHDRAWAL; ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Account Holder Name</label>
                <input type="text" name="account_name" class="form-control" placeholder="Enter full name on bank account" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Account Number</label>
                <input type="text" name="account_number" class="form-control" placeholder="Enter 10-digit account number" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Bank Name</label>
                <select name="bank_name" class="form-control" required>
                    <option value="">Select your bank...</option>
                    <option value="Access Bank">Access Bank</option>
                    <option value="Access Bank (Diamond)">Access Bank (Diamond)</option>
                    <option value="ALAT by Wema">ALAT by Wema</option>
                    <option value="Citibank Nigeria">Citibank Nigeria</option>
                    <option value="Ecobank Nigeria">Ecobank Nigeria</option>
                    <option value="Fidelity Bank">Fidelity Bank</option>
                    <option value="First Bank of Nigeria">First Bank of Nigeria</option>
                    <option value="First City Monument Bank (FCMB)">First City Monument Bank (FCMB)</option>
                    <option value="Globus Bank">Globus Bank</option>
                    <option value="Guaranty Trust Bank (GTBank)">Guaranty Trust Bank (GTBank)</option>
                    <option value="Heritage Bank">Heritage Bank</option>
                    <option value="Jaiz Bank">Jaiz Bank</option>
                    <option value="Keystone Bank">Keystone Bank</option>
                    <option value="Kuda Bank">Kuda Bank</option>
                    <option value="Moniepoint MFB">Moniepoint MFB</option>
                    <option value="OPay">OPay</option>
                    <option value="PalmPay">PalmPay</option>
                    <option value="Parallex Bank">Parallex Bank</option>
                    <option value="Polaris Bank">Polaris Bank</option>
                    <option value="Premium Trust Bank">Premium Trust Bank</option>
                    <option value="Providus Bank">Providus Bank</option>
                    <option value="Renaissance Capital">Renaissance Capital</option>
                    <option value="Stanbic IBTC Bank">Stanbic IBTC Bank</option>
                    <option value="Standard Chartered">Standard Chartered</option>
                    <option value="Sterling Bank">Sterling Bank</option>
                    <option value="SunTrust Bank">SunTrust Bank</option>
                    <option value="Taj Bank">Taj Bank</option>
                    <option value="Titan Trust Bank">Titan Trust Bank</option>
                    <option value="Union Bank">Union Bank</option>
                    <option value="United Bank for Africa (UBA)">United Bank for Africa (UBA)</option>
                    <option value="Unity Bank">Unity Bank</option>
                    <option value="VFD Microfinance Bank">VFD Microfinance Bank</option>
                    <option value="Wema Bank">Wema Bank</option>
                    <option value="Zenith Bank">Zenith Bank</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Withdrawal Method</label>
                <select name="method" class="form-control">
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="PayPal">PayPal</option>
                    <option value="Cryptocurrency">Cryptocurrency</option>
                </select>
            </div>

            <button type="submit" class="btn btn-warning w-100">Submit Withdrawal Request</button>
        </form>
        <p class="mt-3"><a href="/pages/dashboard.php">← Back to Dashboard</a></p>
    </div>
</div>

<script>
function setCurrency(currency) {
    document.getElementById('selectedCurrency').value = currency;
    
    // Update active button
    document.querySelectorAll('.btn-currency').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.currency === currency) {
            btn.classList.add('active');
        }
    });
    
    // Update labels
    const label = document.getElementById('amountLabel');
    const help = document.getElementById('amountHelp');
    const balanceDisplay = document.getElementById('balanceDisplay');
    const balanceNgn = document.getElementById('balanceNgn');
    const rate = <?php echo $exchange_rate; ?>;
    const balance = <?php echo $user['balance']; ?>;
    const minWithdraw = <?php echo MINIMUM_WITHDRAWAL; ?>;
    
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
