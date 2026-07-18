<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);
$ref_count = getReferralCount($user['id']);
$ref_bonus_total = getReferralBonusEarned($user['id']);

// Add profit ONLY if 24 hours have passed
addProfitIfNeeded($user['id']);

// Check for referral bonus
checkAndApplyReferralBonus($user['id']);

// Refresh user data after updates
$user = getUser($_SESSION['user_id']);
$next_profit_time = getLastProfitTime($user['id']);

if ($next_profit_time) {
    $next = new DateTime($next_profit_time);
    $next->modify('+24 hours');
    $next_profit = $next->format('Y-m-d H:i:s');
} else {
    $total_deposits = getTotalDeposits($user['id']);
    if ($total_deposits > 0) {
        $next_profit = 'Profit will be added now (first deposit)';
    } else {
        $next_profit = 'Make a deposit to start earning';
    }
}

$display_name = $user['username'];
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard – <?php echo SITE_NAME; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .modern-navbar {
        background: linear-gradient(135deg, #0a1628 0%, #1a2a4a 50%, #0d1f3c 100%) !important;
        padding: 12px 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.25);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .modern-navbar .navbar-brand {
        font-weight: 700;
        font-size: 1.4rem;
        letter-spacing: 0.5px;
        color: #00f5a0 !important;
        text-shadow: 0 0 30px rgba(0,245,160,0.15);
    }
    .modern-navbar .navbar-brand i { margin-right: 8px; color: #00f5a0; }
    .user-badge {
        background: rgba(0, 245, 160, 0.12);
        border: 1px solid rgba(0, 245, 160, 0.2);
        color: #00f5a0 !important;
        padding: 6px 16px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .user-badge i { font-size: 0.9rem; color: #00f5a0; }
    .nav-btn {
        padding: 8px 18px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.25s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
    }
    .nav-btn i { font-size: 0.9rem; }
    .nav-btn-deposit {
        background: linear-gradient(135deg, #00f5a0, #00d9f5);
        color: #0a1628 !important;
        box-shadow: 0 4px 15px rgba(0,245,160,0.25);
    }
    .nav-btn-deposit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,245,160,0.35);
        color: #0a1628 !important;
    }
    .nav-btn-withdraw {
        background: rgba(255,255,255,0.08);
        color: #ffffff !important;
        border: 1px solid rgba(255,255,255,0.12);
    }
    .nav-btn-withdraw:hover {
        background: rgba(255,255,255,0.15);
        color: #ffffff !important;
    }
    .nav-btn-logout {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171 !important;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .nav-btn-logout:hover {
        background: rgba(239, 68, 68, 0.25);
        color: #f87171 !important;
    }
    @media (max-width: 768px) {
        .modern-navbar .navbar-brand { font-size: 1.1rem; }
        .user-badge { font-size: 0.8rem; padding: 4px 12px; }
        .nav-btn { padding: 6px 14px; font-size: 0.8rem; }
    }
</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg modern-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-line"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <li class="nav-item">
                        <span class="user-badge">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($display_name); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="deposit.php" class="nav-btn nav-btn-deposit">
                            <i class="fas fa-plus-circle"></i> Deposit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="withdraw.php" class="nav-btn nav-btn-withdraw">
                            <i class="fas fa-arrow-right"></i> Withdraw
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../logout.php" class="nav-btn nav-btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Profit Info Alert -->
        <div class="alert alert-success" role="alert">
            <strong>💡 15% profit every 24 hours!</strong> 
            <?php if ($next_profit_time && strpos($next_profit, 'Make a deposit') === false): ?>
                Your next profit will be added at: <strong><?php echo $next_profit; ?></strong>
            <?php else: ?>
                <?php echo $next_profit; ?>
            <?php endif; ?>
        </div>

        <!-- Referral Bonus Alert -->
        <?php if ($ref_count > 0 && $ref_count % 10 == 0): ?>
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-gift"></i> <strong>Congratulations!</strong> You've reached <?php echo $ref_count; ?> referrals! You earned a 20% bonus on your balance.
            </div>
        <?php endif; ?>

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
                        <p class="small">Earn 20% bonus every 10 referrals</p>
                        <p class="small">Total referral bonuses: <strong>$<?php echo number_format($ref_bonus_total, 2); ?></strong></p>
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
                                        <td class="<?php echo ($row['type'] == 'credit' || $row['type'] == 'profit' || $row['type'] == 'referral_bonus') ? 'text-success' : 'text-danger'; ?>">
                                            <?php if ($row['type'] == 'credit' || $row['type'] == 'profit' || $row['type'] == 'referral_bonus'): ?>+<?php else: ?>-<?php endif; ?>$<?php echo number_format($row['amount'], 2); ?>
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
