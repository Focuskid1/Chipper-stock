<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);
$ref_count = getReferralCount($user['id']);

// Add profit ONLY if 12 hours have passed (fixed)
addProfitIfNeeded($user['id']);

// Refresh user data after profit addition
$user = getUser($_SESSION['user_id']);
$next_profit_time = getLastProfitTime($user['id']);

if ($next_profit_time) {
    $next = new DateTime($next_profit_time);
    $next->modify('+12 hours');
    $next_profit = $next->format('Y-m-d H:i:s');
} else {
    // If no profit yet, show message based on deposits
    $total_deposits = getTotalDeposits($user['id']);
    if ($total_deposits > 0) {
        $next_profit = 'Profit will be added now (first deposit)';
    } else {
        $next_profit = 'Make a deposit to start earning';
    }
}

// Get user's registration name (username from signup)
$display_name = $user['username'];
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard – <?php echo SITE_NAME; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold"><?php echo SITE_NAME; ?></span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto d-flex flex-wrap gap-2">
                    <li class="nav-item"><span class="text-white me-2">👋 <?php echo htmlspecialchars($display_name); ?></span></li>
                    <li class="nav-item"><a href="deposit.php" class="btn btn-success btn-sm">➕ Deposit</a></li>
                    <li class="nav-item"><a href="withdraw.php" class="btn btn-warning btn-sm">💳 Withdraw</a></li>
                    <li class="nav-item"><a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Profit Info Alert -->
        <div class="alert alert-success" role="alert">
            <strong>💡 15% profit every 12 hours!</strong> <?php if ($next_profit_time && strpos($next_profit, 'Make a deposit') === false): ?>
                Your next profit will be added at: <strong><?php echo $next_profit; ?></strong>
            <?php else: ?>
                <?php echo $next_profit; ?>
            <?php endif; ?>
        </div>

        <div class="row g-3 dashboard-cards">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5>💰 Current Balance</h5>
                        <h2>$<?php echo number_format($user['balance'], 2); ?></h2>
                        <p class="small">Available for withdrawal</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5>📈 Total Invested</h5>
                        <h2>$<?php echo number_format($user['total_deposits'], 2); ?></h2>
                        <p class="small">All-time deposits</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5>👥 Referrals</h5>
                        <h2><?php echo $ref_count; ?></h2>
                        <p class="small">Earn 5% of their deposits</p>
                        <p class="small">Your referral link: <br><code><?php echo SITE_URL; ?>/pages/register.php?ref=<?php echo $user['id']; ?></code></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">📋 Recent Activity</div>
                    <div class="card-body">
                        <div class="table-wrapper">
                            <table class="table table-striped">
                                <thead><tr><th>Description</th><th>Amount</th></tr></thead>
                                <tbody>
                                <?php
                                $stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                                $stmt->execute([$user['id']]);
                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td class="<?php echo ($row['type'] == 'credit' || $row['type'] == 'profit') ? 'text-success' : 'text-danger'; ?>">
                                            <?php if ($row['type'] == 'credit' || $row['type'] == 'profit'): ?>+<?php else: ?>-<?php endif; ?>$<?php echo number_format($row['amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto">
        <div class="container-fluid">
            <span>&copy; 2026 <?php echo SITE_NAME; ?></span>
            <span class="float-end">📧 <?php echo ADMIN_EMAIL; ?></span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
