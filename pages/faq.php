<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html>
<head><title>FAQ – <?php echo SITE_NAME; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .faq-section { background: #f8faff; padding: 40px 0; min-height: 80vh; }
    .faq-item { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 12px; border: 1px solid #e9edf2; transition: all 0.2s; }
    .faq-item:hover { box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
    .faq-item .question { font-weight: 700; color: #0d1a2b; font-size: 1.1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none; }
    .faq-item .answer { margin-top: 12px; color: #3a4b5e; display: none; line-height: 1.6; }
    .faq-item .answer.show { display: block; }
    .faq-item .toggle-icon { transition: transform 0.3s; }
    .faq-item .toggle-icon.rotated { transform: rotate(180deg); }
    .faq-item .answer ul { padding-left: 20px; margin-top: 8px; }
    .faq-item .answer ul li { margin-bottom: 6px; }
    .faq-badge { background: #e8f4fd; padding: 2px 12px; border-radius: 30px; font-size: 0.75rem; color: #0d6efd; }
</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg modern-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="fas fa-chart-line"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <li class="nav-item"><a href="/" class="nav-btn nav-btn-login"><i class="fas fa-home"></i> Home</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item"><a href="/pages/dashboard.php" class="nav-btn nav-btn-login"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a href="/pages/login.php" class="nav-btn nav-btn-login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li class="nav-item"><a href="/pages/register.php" class="nav-btn nav-btn-register"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="faq-section">
        <div class="container" style="max-width:800px;">
            <div class="text-center mb-4">
                <h1>Frequently Asked Questions</h1>
                <p class="text-muted">Find answers to the most common questions about <?php echo SITE_NAME; ?></p>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>What is Chipper Stock? <span class="faq-badge">New</span></span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">Chipper Stock is an AI-powered investment platform that uses advanced algorithms to trade stocks and cryptocurrencies on your behalf, generating consistent daily returns.</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>How much profit can I earn?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">You earn <strong>15% profit every 24 hours</strong> on your total deposits. The more you invest, the more you earn!</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>What is the minimum deposit?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">The minimum deposit is <strong>$5 USDT (TRC20)</strong>. You can start investing with as little as $5.</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>How do I withdraw my earnings?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">
                    <p>You can withdraw your earnings anytime via <strong>USDT (TRC20)</strong>. Withdrawals are processed within 24 hours after admin confirmation.</p>
                    <p><strong>Withdrawal Rules:</strong></p>
                    <ul>
                        <li>You must have made at least one deposit of <strong>$5</strong> to withdraw your profits.</li>
                        <li>You can withdraw your referral bonuses after reaching <strong>20 referrals</strong>.</li>
                        <li>Minimum withdrawal amount is <strong>$5</strong>.</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>How does the referral program work?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">
                    <p>You earn <strong>$1 instantly</strong> for every person who registers using your referral link!</p>
                    <p><strong>Referral Bonus Rules:</strong></p>
                    <ul>
                        <li>Earn $1 per referral instantly.</li>
                        <li>Referral bonuses can be withdrawn after reaching <strong>20 referrals</strong>.</li>
                        <li>There's no limit to how many people you can refer!</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>Is my money safe?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">We use advanced security measures and AI-driven trading strategies to protect your investments. All transactions are recorded on the blockchain for transparency.</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>How do I deposit funds?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">
                    <p>Simply send <strong>USDT (TRC20)</strong> to our wallet address:</p>
                    <p><code style="background:#f8faff; padding:8px 12px; border-radius:6px; display:block; word-break:break-all;"><?php echo USDT_WALLET; ?></code></p>
                    <p>Then submit your transaction hash on the deposit page for confirmation.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>What are the withdrawal requirements?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">
                    <p><strong>To withdraw your profits:</strong></p>
                    <ul>
                        <li>You must have made at least one deposit of <strong>$5</strong>.</li>
                        <li>Minimum withdrawal amount: <strong>$5</strong>.</li>
                    </ul>
                    <p><strong>To withdraw referral bonuses:</strong></p>
                    <ul>
                        <li>You must have reached <strong>20 referrals</strong>.</li>
                        <li>After 20 referrals, all your referral bonuses become withdrawable.</li>
                    </ul>
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
    function toggleFaq(element) {
        const answer = element.parentElement.querySelector('.answer');
        const icon = element.querySelector('.toggle-icon');
        answer.classList.toggle('show');
        icon.classList.toggle('rotated');
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
