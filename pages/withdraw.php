<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $account_name = trim($_POST['account_name']);
    $account_number = trim($_POST['account_number']);
    $bank_name = trim($_POST['bank_name']);
    $account_details = "Bank: $bank_name | Account: $account_number | Name: $account_name";

    if ($amount < MINIMUM_WITHDRAWAL) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Minimum withdrawal is $' . MINIMUM_WITHDRAWAL];
    } elseif ($amount > $user['balance']) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Insufficient balance'];
    } elseif (empty($account_name) || empty($account_number) || empty($bank_name)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'All fields are required.'];
    } else {
        // Save withdrawal request with status 'pending'
        $stmt = $db->prepare("INSERT INTO withdrawals (user_id, amount, method, account_details, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$user['id'], $amount, $method, $account_details]);
        
        addTransaction($user['id'], 'pending', -$amount, 'Withdrawal pending approval: $' . $amount);
        
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
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:500px; margin-top:5vh;">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">💵 Withdraw Your Earnings</h2>
        <p class="text-center">Available balance: <strong>$<?php echo number_format($user['balance'], 2); ?></strong></p>
        <?php displayFlash('error'); displayFlash('success'); ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Amount ($)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="<?php echo MINIMUM_WITHDRAWAL; ?>" max="<?php echo $user['balance']; ?>" required>
                <small>Minimum withdrawal: $<?php echo MINIMUM_WITHDRAWAL; ?></small>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
