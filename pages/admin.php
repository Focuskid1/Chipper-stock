<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

// Redirect if not admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirect('/pages/dashboard.php');
}

// Handle deposit confirmation
if (isset($_GET['confirm_deposit'])) {
    $id = intval($_GET['confirm_deposit']);
    $stmt = $db->prepare("SELECT * FROM deposits WHERE id = ? AND status = 'pending'");
    $stmt->execute([$id]);
    $deposit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($deposit) {
        $stmt = $db->prepare("UPDATE deposits SET status = 'confirmed' WHERE id = ?");
        $stmt->execute([$id]);
        updateBalance($deposit['user_id'], $deposit['amount']);
        addTransaction($deposit['user_id'], 'credit', $deposit['amount'], 'Deposit confirmed: $' . $deposit['amount']);
        $stmt = $db->prepare("UPDATE users SET total_deposits = total_deposits + ? WHERE id = ?");
        $stmt->execute([$deposit['amount'], $deposit['user_id']]);
        $_SESSION['success'] = ['type' => 'success', 'message' => 'Deposit confirmed and credited to user.'];
    }
    redirect('/pages/admin.php');
}

// Handle withdrawal confirmation
if (isset($_GET['confirm_withdrawal'])) {
    $id = intval($_GET['confirm_withdrawal']);
    $stmt = $db->prepare("SELECT * FROM withdrawals WHERE id = ? AND status = 'pending'");
    $stmt->execute([$id]);
    $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($withdrawal) {
        $stmt = $db->prepare("UPDATE withdrawals SET status = 'confirmed' WHERE id = ?");
        $stmt->execute([$id]);
        updateBalance($withdrawal['user_id'], -$withdrawal['amount']);
        addTransaction($withdrawal['user_id'], 'debit', $withdrawal['amount'], 'Withdrawal confirmed: $' . $withdrawal['amount']);
        $stmt = $db->prepare("UPDATE users SET total_withdrawals = total_withdrawals + ? WHERE id = ?");
        $stmt->execute([$withdrawal['amount'], $withdrawal['user_id']]);
        $_SESSION['success'] = ['type' => 'success', 'message' => 'Withdrawal confirmed and debited from user.'];
    }
    redirect('/pages/admin.php');
}

$users = $db->query("SELECT * FROM users ORDER BY id DESC");
$deposits = $db->query("SELECT * FROM deposits ORDER BY id DESC");
$withdrawals = $db->query("SELECT * FROM withdrawals ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Admin – <?php echo SITE_NAME; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container-fluid mt-4">
    <h1>Admin Panel</h1>
    <?php displayFlash('success'); ?>
    
    <div class="card shadow p-3 mb-4">
        <h3>Users</h3>
        <div class="table-wrapper">
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Username</th><th>Balance</th><th>Referrals</th><th>Registered</th></tr></thead>
                <tbody>
                <?php while($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr><td><?php echo $row['id']; ?></td><td><?php echo $row['username']; ?></td><td>$<?php echo number_format($row['balance'], 2); ?></td><td><?php echo getReferralCount($row['id']); ?></td><td><?php echo $row['registered_at']; ?></td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card shadow p-3 mb-4">
        <h3>Pending Deposits</h3>
        <div class="table-wrapper">
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Depositor</th><th>Phone</th><th>Bank</th><th>Ref</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php while($row = $deposits->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td>$<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo $row['depositor_name']; ?></td>
                        <td><?php echo $row['depositor_phone']; ?></td>
                        <td><?php echo $row['depositor_bank']; ?></td>
                        <td><?php echo $row['transaction_ref']; ?></td>
                        <td><span class="badge bg-<?php echo $row['status'] == 'pending' ? 'warning' : 'success'; ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <a href="?confirm_deposit=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Confirm this deposit?')">Confirm</a>
                            <?php else: ?>
                                ✅ Done
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card shadow p-3">
        <h3>Pending Withdrawals</h3>
        <div class="table-wrapper">
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Method</th><th>Account Details</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php while($row = $withdrawals->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td>$<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo $row['method']; ?></td>
                        <td><?php echo $row['account_details']; ?></td>
                        <td><span class="badge bg-<?php echo $row['status'] == 'pending' ? 'warning' : 'danger'; ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <a href="?confirm_withdrawal=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Confirm this withdrawal?')">Confirm</a>
                            <?php else: ?>
                                ✅ Done
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
