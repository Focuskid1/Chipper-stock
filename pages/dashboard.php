<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) redirect('/pages/login.php');
$user = getUser($_SESSION['user_id']);
$ref_count = getReferralCount($user['id']);
$ref_bonus_earned = getReferralBonusEarned($user['id']);

// Add profit ONLY if 24 hours have passed
addProfitIfNeeded($user['id']);

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

// Get pending deposits for this user
$stmt = $db->prepare("SELECT * FROM deposits WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$pending_deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get confirmed deposits for this user
$stmt = $db->prepare("SELECT * FROM deposits WHERE user_id = ? AND status = 'confirmed' ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user['id']]);
$confirmed_deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending withdrawals
$stmt = $db->prepare("SELECT * FROM withdrawals WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$pending_withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get confirmed withdrawals
$stmt = $db->prepare("SELECT * FROM withdrawals WHERE user_id = ? AND status = 'confirmed' ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user['id']]);
$confirmed_withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    .deposit-card {
        background: #fff;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 8px;
        border: 1px solid #e9edf2;
        transition: all 0.2s ease;
    }
    .deposit-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .deposit-card.pending-deposit {
        border-left: 4px solid #ffc107;
    }
    .deposit-card.confirmed-deposit {
        border-left: 4px solid #198754;
    }
    .deposit-card.pending-withdrawal {
        border-left: 4px solid #ffc107;
    }
    .deposit-card.confirmed-withdrawal {
        border-left: 4px solid #dc3545;
    }
    .deposit-card .amount-positive {
        color: #198754;
        font-weight: 700;
    }
    .deposit-card .amount-negative {
        color: #dc3545;
        font-weight: 700;
    }
    .deposit-card .method-badge {
        font-size: 0.75rem;
        padding: 2px 10px;
        border-radius: 30px;
        font-weight: 600;
    }
    .method-badge.usdt {
        background: #e8f5e9;
        color: #198754;
    }
    
    .classy-tabs {
        border-bottom: 2px solid #e9edf2;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .classy-tabs .tab-btn {
        padding: 10px 20px;
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
    .classy-tabs .tab-btn:hover {
        color: #0d1a2b;
        background: #f8faff;
    }
    .classy-tabs .tab-btn.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        background: #f8faff;
    }
    .classy-tabs .tab-btn .badge-count {
        background: #e8f4fd;
        color: #0d6efd;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 30px;
        font-weight: 600;
    }
    .classy-tabs .tab-btn.active .badge-count {
        background: #0d6efd;
        color: #fff;
    }
    .classy-tabs .tab-btn .tab-icon {
        font-size: 1rem;
    }
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .tab-content.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6b7a93;
    }
    .empty-state i {
        font-size: 3rem;
        color: #dce3ec;
        margin-bottom: 12px;
    }
    .empty-state h5 {
        color: #3a4b5e;
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
    .referral-bonus-badge {
        background: linear-gradient(135deg, #00f5a0, #00d9f5);
        color: #0a1628;
        padding: 4px 12px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../assets/js/chat.js"></script>
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
        <?php if ($ref_count > 0): ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-gift"></i> <strong>$1 Referral Bonus!</strong> You've earned <strong>$<?php echo number_format($ref_bonus_earned, 2); ?></strong> from <?php echo $ref_count; ?> referral(s). Each referral gives you $1 instantly!
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
                        <p class="small">Earn <strong>$1</strong> per referral instantly</p>
                        <p class="small">Referral bonuses earned: <strong>$<?php echo number_format($ref_bonus_earned, 2); ?></strong></p>
                        <p class="small">Your referral link: <br><code><?php echo SITE_URL; ?>/pages/register.php?ref=<?php echo $user['id']; ?></code></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CLASSY TABS -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Recent Activities
                    </div>
                    <div class="card-body">
                        <div class="classy-tabs" id="activityTabs">
                            <button class="tab-btn active" data-tab="pending-deposits">
                                <i class="fas fa-clock tab-icon text-warning"></i> Pending Deposits
                                <span class="badge-count"><?php echo count($pending_deposits); ?></span>
                            </button>
                            <button class="tab-btn" data-tab="confirmed-deposits">
                                <i class="fas fa-check-circle tab-icon text-success"></i> Confirmed Deposits
                                <span class="badge-count"><?php echo count($confirmed_deposits); ?></span>
                            </button>
                            <button class="tab-btn" data-tab="pending-withdrawals">
                                <i class="fas fa-clock tab-icon text-warning"></i> Pending Withdrawals
                                <span class="badge-count"><?php echo count($pending_withdrawals); ?></span>
                            </button>
                            <button class="tab-btn" data-tab="confirmed-withdrawals">
                                <i class="fas fa-check-circle tab-icon text-success"></i> Confirmed Withdrawals
                                <span class="badge-count"><?php echo count($confirmed_withdrawals); ?></span>
                            </button>
                        </div>

                        <!-- Tab Content: Pending Deposits -->
                        <div class="tab-content active" id="tab-pending-deposits">
                            <?php if (!empty($pending_deposits)): ?>
                                <?php foreach($pending_deposits as $deposit): ?>
                                    <div class="deposit-card pending-deposit">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            <div>
                                                <span class="amount-positive">+ $<?php echo number_format($deposit['amount'], 2); ?></span>
                                                <span class="method-badge usdt">
                                                    <i class="fas fa-coins"></i> USDT TRC20
                                                </span>
                                            </div>
                                            <div>
                                                <span class="status-badge pending">Pending</span>
                                                <small class="text-muted ms-2"><?php echo date('M d, Y H:i', strtotime($deposit['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($deposit['depositor_name']); ?> 
                                                | <i class="fas fa-phone"></i> <?php echo htmlspecialchars($deposit['depositor_phone']); ?>
                                                | <i class="fas fa-link"></i> <span class="text-truncate d-inline-block" style="max-width: 200px;"><?php echo htmlspecialchars($deposit['transaction_ref']); ?></span>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h5>No pending deposits</h5>
                                    <p class="text-muted">You don't have any pending deposits at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Confirmed Deposits -->
                        <div class="tab-content" id="tab-confirmed-deposits">
                            <?php if (!empty($confirmed_deposits)): ?>
                                <?php foreach($confirmed_deposits as $deposit): ?>
                                    <div class="deposit-card confirmed-deposit">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            <div>
                                                <span class="amount-positive">+ $<?php echo number_format($deposit['amount'], 2); ?></span>
                                                <span class="method-badge usdt">
                                                    <i class="fas fa-coins"></i> USDT TRC20
                                                </span>
                                            </div>
                                            <div>
                                                <span class="status-badge confirmed">Confirmed</span>
                                                <small class="text-muted ms-2"><?php echo date('M d, Y H:i', strtotime($deposit['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($deposit['depositor_name']); ?>
                                                | <i class="fas fa-link"></i> <span class="text-truncate d-inline-block" style="max-width: 200px;"><?php echo htmlspecialchars($deposit['transaction_ref']); ?></span>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <h5>No confirmed deposits</h5>
                                    <p class="text-muted">You haven't made any deposits yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Pending Withdrawals -->
                        <div class="tab-content" id="tab-pending-withdrawals">
                            <?php if (!empty($pending_withdrawals)): ?>
                                <?php foreach($pending_withdrawals as $withdrawal): ?>
                                    <div class="deposit-card pending-withdrawal">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            <div>
                                                <span class="amount-negative">- $<?php echo number_format($withdrawal['amount'], 2); ?></span>
                                                <span class="method-badge usdt">
                                                    <i class="fas fa-coins"></i> USDT TRC20
                                                </span>
                                            </div>
                                            <div>
                                                <span class="status-badge pending">Pending</span>
                                                <small class="text-muted ms-2"><?php echo date('M d, Y H:i', strtotime($withdrawal['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($withdrawal['account_details']); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h5>No pending withdrawals</h5>
                                    <p class="text-muted">You don't have any pending withdrawal requests.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Confirmed Withdrawals -->
                        <div class="tab-content" id="tab-confirmed-withdrawals">
                            <?php if (!empty($confirmed_withdrawals)): ?>
                                <?php foreach($confirmed_withdrawals as $withdrawal): ?>
                                    <div class="deposit-card confirmed-withdrawal">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            <div>
                                                <span class="amount-negative">- $<?php echo number_format($withdrawal['amount'], 2); ?></span>
                                                <span class="method-badge usdt">
                                                    <i class="fas fa-coins"></i> USDT TRC20
                                                </span>
                                            </div>
                                            <div>
                                                <span class="status-badge confirmed">Confirmed</span>
                                                <small class="text-muted ms-2"><?php echo date('M d, Y H:i', strtotime($withdrawal['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($withdrawal['account_details']); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <h5>No confirmed withdrawals</h5>
                                    <p class="text-muted">You haven't made any withdrawals yet.</p>
                                </div>
                            <?php endif; ?>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
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
                const targetContent = document.getElementById('tab-' + tabId);
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
