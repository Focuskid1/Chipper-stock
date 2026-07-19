<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $depositor_name = trim($_POST['depositor_name']);
    $depositor_phone = trim($_POST['depositor_phone']);
    $transaction_ref = trim($_POST['transaction_ref']);
    $currency = $_POST['currency'] ?? 'USD';

    if ($amount < MINIMUM_DEPOSIT) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Minimum deposit is $' . MINIMUM_DEPOSIT];
    } elseif (empty($depositor_name) || empty($depositor_phone) || empty($transaction_ref)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'All fields are required.'];
    } else {
        if ($currency == 'NGN') {
            $amount_usd = $amount / getExchangeRate();
        } else {
            $amount_usd = $amount;
        }
        
        $stmt = $db->prepare("INSERT INTO deposits (user_id, amount, method, status, depositor_name, depositor_phone, depositor_bank, transaction_ref) VALUES (?, ?, 'USDT TRC20', 'pending', ?, ?, 'USDT TRC20', ?)");
        $stmt->execute([$user['id'], $amount_usd, $depositor_name, $depositor_phone, $transaction_ref]);
        
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
    .wallet-address {
        background: #1a1a2e;
        color: #00f5a0;
        padding: 16px 20px;
        border-radius: 12px;
        font-family: 'Courier New', monospace;
        font-size: 1rem;
        word-break: break-all;
        border: 1px solid rgba(0, 245, 160, 0.2);
        cursor: pointer;
        transition: all 0.3s ease;
        user-select: all;
    }
    .wallet-address:hover {
        background: #0d1a2b;
        border-color: #00f5a0;
        box-shadow: 0 0 30px rgba(0, 245, 160, 0.1);
    }
    .wallet-address .copy-icon {
        color: #6b7a93;
        font-size: 0.9rem;
        margin-left: 8px;
        transition: color 0.2s;
    }
    .wallet-address:hover .copy-icon {
        color: #00f5a0;
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
    .eth-warning {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.85rem;
        color: #856404;
        margin-top: 8px;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../assets/js/chat.js"></script>
</head>
<body>
<div class="container" style="max-width:600px; margin-top:3vh; margin-bottom:3vh;">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">💰 Fund Your AI Trading Account</h2>
        <?php displayFlash('error'); displayFlash('success'); ?>
        
        <div class="text-center mb-3">
            <span class="min-deposit-badge">
                <i class="fas fa-info-circle"></i> Minimum Deposit: <strong>$<?php echo MINIMUM_DEPOSIT; ?></strong>
            </span>
        </div>

        <!-- USDT TRC20 Wallet Details -->
        <div class="card bg-light mb-4">
            <div class="card-body">
                <h5 class="card-title">🪙 Send USDT (TRC20) to this address:</h5>
                <div class="wallet-address" onclick="copyAddress()">
                    <i class="fas fa-copy copy-icon" style="float: right;"></i>
                    <?php echo USDT_WALLET; ?>
                </div>
                <p class="eth-info mt-2" style="font-size:0.85rem; color:#6b7a93;">
                    ⚠️ Send only <strong>USDT (TRC20)</strong> to this address. Minimum: $<?php echo MINIMUM_DEPOSIT; ?> equivalent.
                </p>
                <div class="eth-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Important:</strong> After sending, fill in the details below with your transaction hash as the reference.
                </div>
                <p class="mt-2 text-muted" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle"></i> Your deposit will be credited after confirmation on the blockchain.
                </p>
            </div>
        </div>

        <!-- Currency Converter -->
        <div class="currency-converter">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>💱 Live Exchange Rate</strong>
                    <div class="rate">1 USD = <strong>₦<?php echo number_format(getExchangeRate(), 2); ?></strong></div>
                </div>
                <div class="currency-selector" id="currencySelector">
                    <button type="button" class="btn-currency active" data-currency="USD" onclick="setCurrency('USD')">USD ($)</button>
                    <button type="button" class="btn-currency" data-currency="NGN" onclick="setCurrency('NGN')">NGN (₦)</button>
                </div>
            </div>
        </div>

        <form method="POST" id="depositForm">
            <input type="hidden" name="currency" id="selectedCurrency" value="USD">
            
            <div class="mb-3">
                <label class="form-label">Your Full Name</label>
                <input type="text" name="depositor_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Your Phone Number</label>
                <input type="text" name="depositor_phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Transaction Hash / Reference</label>
                <input type="text" name="transaction_ref" class="form-control" placeholder="Enter your USDT transaction hash" required>
                <small class="text-muted">Enter your USDT TRC20 transaction hash (e.g., 0x...)</small>
            </div>
            <div class="mb-3">
                <label class="form-label" id="amountLabel">Amount ($)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="<?php echo MINIMUM_DEPOSIT; ?>" required>
                <small class="text-muted" id="amountHelp">Minimum deposit: $<?php echo MINIMUM_DEPOSIT; ?></small>
            </div>
            <button type="submit" class="btn btn-success w-100">Submit for Approval</button>
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
    var rate = <?php echo getExchangeRate(); ?>;
    var minDeposit = <?php echo MINIMUM_DEPOSIT; ?>;
    
    if (currency === 'NGN') {
        label.textContent = 'Amount (₦)';
        help.textContent = 'Minimum deposit: ₦' + (minDeposit * rate).toFixed(2);
    } else {
        label.textContent = 'Amount ($)';
        help.textContent = 'Minimum deposit: $' + minDeposit;
    }
}

function copyAddress() {
    var address = '<?php echo USDT_WALLET; ?>';
    if (navigator.clipboard) {
        navigator.clipboard.writeText(address).then(function() {
            var el = document.querySelector('.wallet-address');
            var original = el.innerHTML;
            el.innerHTML = '✅ Copied!';
            setTimeout(function() {
                el.innerHTML = original;
            }, 1500);
        });
    } else {
        var textarea = document.createElement('textarea');
        textarea.value = address;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        var el = document.querySelector('.wallet-address');
        var original = el.innerHTML;
        el.innerHTML = '✅ Copied!';
        setTimeout(function() {
            el.innerHTML = original;
        }, 1500);
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
