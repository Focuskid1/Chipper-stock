<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .method-badge {
        font-size: 0.75rem;
        padding: 2px 10px;
        border-radius: 30px;
        font-weight: 600;
    }
    .method-badge.bank {
        background: #e8f4fd;
        color: #0d6efd;
    }
    .method-badge.crypto {
        background: #e8f5e9;
        color: #198754;
    }
    .table td {
        vertical-align: middle;
    }
    .amount-positive {
        color: #198754;
        font-weight: 700;
    }
    .amount-negative {
        color: #dc3545;
        font-weight: 700;
    }
    .status-badge {
        font-size: 0.7rem;
        padding: 2px 10px;
        border-radius: 30px;
        font-weight: 600;
    }
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-badge.confirmed {
        background: #d1e7dd;
        color: #0a3622;
    }
    
    /* ─── CLASSY ADMIN TABS ─── */
    .admin-tabs {
        border-bottom: 2px solid #e9edf2;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .admin-tabs .tab-btn {
        padding: 12px 24px;
        border: none;
        background: transparent;
        font-weight: 600;
        font-size: 0.9rem;
        color: #6b7a93;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .admin-tabs .tab-btn:hover {
        color: #0d1a2b;
        background: #f8faff;
    }
    .admin-tabs .tab-btn.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        background: #f8faff;
    }
    .admin-tabs .tab-btn .badge-count {
        background: #e8f4fd;
        color: #0d6efd;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 30px;
        font-weight: 600;
    }
    .admin-tabs .tab-btn.active .badge-count {
        background: #0d6efd;
        color: #fff;
    }
    .admin-tabs .tab-btn .tab-icon {
        font-size: 1rem;
    }
    .admin-tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .admin-tab-content.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../assets/js/chat.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../assets/js/chat.js"></script>
</head>
<body>
<div class="container-fluid mt-4">
    <h1><i class="fas fa-shield-alt text-primary"></i> Admin Panel</h1>
    <?php displayFlash('success'); ?>
    
    <!-- ============================================================ -->
    <!-- CLASSY ADMIN TABS -->
    <!-- ============================================================ -->
    <div class="card shadow p-3">
        <!-- Tab Buttons -->
        <div class="admin-tabs" id="adminTabs">
            <button class="tab-btn active" data-tab="users">
                <i class="fas fa-users tab-icon"></i> Users
                <span class="badge-count"><?php echo $users->rowCount(); ?></span>
            </button>
            <button class="tab-btn" data-tab="pending-deposits">
                <i class="fas fa-arrow-up tab-icon text-success"></i> Pending Deposits
                <?php 
                    $pending_deposits_count = $db->query("SELECT COUNT(*) as count FROM deposits WHERE status = 'pending'")->fetch(PDO::FETCH_ASSOC)['count'];
                ?>
                <span class="badge-count"><?php echo $pending_deposits_count; ?></span>
            </button>
            <button class="tab-btn" data-tab="pending-withdrawals">
                <i class="fas fa-arrow-down tab-icon text-danger"></i> Pending Withdrawals
                <?php 
                    $pending_withdrawals_count = $db->query("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'pending'")->fetch(PDO::FETCH_ASSOC)['count'];
                ?>
                <span class="badge-count"><?php echo $pending_withdrawals_count; ?></span>
            </button>
        </div>

        <!-- Tab Content: Users -->
        <div class="admin-tab-content active" id="admin-tab-users">
            <div class="table-wrapper">
                <table class="table table-striped">
                    <thead><tr><th>ID</th><th>Username</th><th>Balance</th><th>Referrals</th><th>Registered</th></tr></thead>
                    <tbody>
                    <?php 
                    $users->execute();
                    while($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr><td><?php echo $row['id']; ?></td><td><?php echo $row['username']; ?></td><td>$<?php echo number_format($row['balance'], 2); ?></td><td><?php echo getReferralCount($row['id']); ?></td><td><?php echo $row['registered_at']; ?></td></tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Content: Pending Deposits (Green / Positive) -->
        <div class="admin-tab-content" id="admin-tab-pending-deposits">
            <div class="table-wrapper">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Depositor</th>
                            <th>Phone</th>
                            <th>Bank</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $deposits->execute();
                    while($row = $deposits->fetch(PDO::FETCH_ASSOC)): 
                        if ($row['status'] != 'pending') continue;
                        $user = getUser($row['user_id']);
                    ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $user ? $user['username'] : 'Unknown'; ?></td>
                            <td><span class="amount-positive">+ $<?php echo number_format($row['amount'], 2); ?></span></td>
                            <td>
                                <span class="method-badge <?php echo ($row['method'] == 'ETH Transfer') ? 'crypto' : 'bank'; ?>">
                                    <i class="fas <?php echo ($row['method'] == 'ETH Transfer') ? 'fa-coins' : 'fa-university'; ?>"></i>
                                    <?php echo $row['method']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['depositor_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['depositor_phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['depositor_bank']); ?></td>
                            <td>
                                <?php if ($row['method'] == 'ETH Transfer'): ?>
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['transaction_ref']); ?>">
                                        <i class="fas fa-link text-success"></i> 
                                        <?php echo htmlspecialchars(substr($row['transaction_ref'], 0, 20)) . '...'; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-truncate d-inline-block" style="max-width: 120px;" title="<?php echo htmlspecialchars($row['transaction_ref']); ?>">
                                        <i class="fas fa-receipt"></i> 
                                        <?php echo htmlspecialchars($row['transaction_ref']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge pending">Pending</span></td>
                            <td>
                                <a href="?confirm_deposit=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Confirm this deposit?')">
                                    <i class="fas fa-check"></i> Confirm
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Content: Pending Withdrawals (Red / Negative) -->
        <div class="admin-tab-content" id="admin-tab-pending-withdrawals">
            <div class="table-wrapper">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Account Details</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $withdrawals->execute();
                    while($row = $withdrawals->fetch(PDO::FETCH_ASSOC)): 
                        if ($row['status'] != 'pending') continue;
                        $user = getUser($row['user_id']);
                    ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $user ? $user['username'] : 'Unknown'; ?></td>
                            <td><span class="amount-negative">- $<?php echo number_format($row['amount'], 2); ?></span></td>
                            <td><?php echo htmlspecialchars($row['method']); ?></td>
                            <td><?php echo htmlspecialchars($row['account_details']); ?></td>
                            <td><span class="status-badge pending">Pending</span></td>
                            <td>
                                <a href="?confirm_withdrawal=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Confirm this withdrawal?')">
                                    <i class="fas fa-check"></i> Confirm
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('#adminTabs .tab-btn');
    const tabContents = document.querySelectorAll('.admin-tab-content');
    
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            tabButtons.forEach(function(btn) {
                btn.classList.remove('active');
            });
            tabContents.forEach(function(content) {
                content.classList.remove('active');
            });
            
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            const targetContent = document.getElementById('admin-tab-' + tabId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
