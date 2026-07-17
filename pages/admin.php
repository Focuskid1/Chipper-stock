<?php
require_once __DIR__ . "/../includes/functions.php";
require_once '../includes/auth.php';
if (!isLoggedIn() || $_SESSION['username'] != 'admin') redirect('login.php');
$users = $db->query("SELECT * FROM users ORDER BY id DESC");
$deposits = $db->query("SELECT * FROM deposits ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Admin - <?php echo SITE_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container mt-4">
    <h1>Admin Panel</h1>
    <div class="card shadow p-3 mb-4">
        <h3>Users</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Username</th><th>Balance</th><th>Referrals</th><th>Registered</th></tr></thead>
                <tbody>
                <?php while($row = $users->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr><td><?php echo $row['id']; ?></td><td><?php echo $row['username']; ?></td><td>$<?php echo number_format($row['balance'], 2); ?></td><td><?php echo getReferralCount($row['id']); ?></td><td><?php echo $row['registered_at']; ?></td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card shadow p-3">
        <h3>Deposits</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Depositor</th><th>Phone</th><th>Bank</th><th>Ref</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php while($row = $deposits->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td>$<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo $row['depositor_name']; ?></td>
                        <td><?php echo $row['depositor_phone']; ?></td>
                        <td><?php echo $row['depositor_bank']; ?></td>
                        <td><?php echo $row['transaction_ref']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
