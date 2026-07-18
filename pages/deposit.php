<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $depositor_name = trim($_POST['depositor_name']);
    $depositor_phone = trim($_POST['depositor_phone']);
    $depositor_bank = trim($_POST['depositor_bank']);
    $transaction_ref = trim($_POST['transaction_ref']);

    if ($amount < MINIMUM_DEPOSIT) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Minimum investment is $' . MINIMUM_DEPOSIT];
    } elseif (empty($depositor_name) || empty($depositor_phone) || empty($depositor_bank) || empty($transaction_ref)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'All fields are required.'];
    } else {
        // Save deposit request with status 'pending'
        $stmt = $db->prepare("INSERT INTO deposits (user_id, amount, method, status, depositor_name, depositor_phone, depositor_bank, transaction_ref) VALUES (?, ?, 'bank_transfer', 'pending', ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $amount, $depositor_name, $depositor_phone, $depositor_bank, $transaction_ref]);
        
        // Add transaction record (pending)
        addTransaction($user['id'], 'pending', $amount, 'Deposit pending approval: $' . $amount);
        
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
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:600px; margin-top:5vh;">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">💰 Fund Your AI Trading Account</h2>
        <?php displayFlash('error'); displayFlash('success'); ?>
        
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

        <form method="POST">
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
                <label class="form-label">Amount ($)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="<?php echo MINIMUM_DEPOSIT; ?>" required>
                <small class="text-muted">Minimum investment: $<?php echo MINIMUM_DEPOSIT; ?></small>
            </div>
            <button type="submit" class="btn btn-success w-100">Submit for Approval</button>
        </form>
        <p class="mt-3"><a href="/pages/dashboard.php">← Back to Dashboard</a></p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
