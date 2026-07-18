<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $account_details = trim($_POST['account_details']);

    if ($amount < MINIMUM_WITHDRAWAL) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Minimum withdrawal is $' . MINIMUM_WITHDRAWAL];
    } elseif ($amount > $user['balance']) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Insufficient balance'];
    } elseif (empty($account_details)) {
        $_SESSION['error'] = ['type' => 'danger', 'message' => 'Please provide account details'];
    } else {
        // Save withdrawal request with status 'pending'
        $stmt = $db->prepare("INSERT INTO withdrawals (user_id, amount, method, account_details, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$user['id'], $amount, $method, $account_details]);
        
        // Add transaction record (pending)
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
                <label class="form-label">Withdrawal Method</label>
                <select name="method" class="form-control">
                    <option>Bank Transfer</option>
                    <option>PayPal</option>
                    <option>Cryptocurrency</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Account Details</label>
                <textarea name="account_details" class="form-control" rows="3" placeholder="Enter your bank/account details" required></textarea>
            </div>
            <button type="submit" class="btn btn-warning w-100">Submit Withdrawal Request</button>
        </form>
        <p class="mt-3"><a href="/pages/dashboard.php">← Back to Dashboard</a></p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
