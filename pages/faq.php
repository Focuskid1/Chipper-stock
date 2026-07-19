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
    .faq-section { background: #f8faff; padding: 40px 0; }
    .faq-item { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 12px; border: 1px solid #e9edf2; }
    .faq-item .question { font-weight: 700; color: #0d1a2b; font-size: 1.1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    .faq-item .answer { margin-top: 12px; color: #3a4b5e; display: none; }
    .faq-item .answer.show { display: block; }
    .faq-item .toggle-icon { transition: transform 0.3s; }
    .faq-item .toggle-icon.rotated { transform: rotate(180deg); }
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
            <h1 class="text-center mb-4">Frequently Asked Questions</h1>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>What is Chipper Stock?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">Chipper Stock is an AI-powered investment platform that uses advanced algorithms to trade stocks and cryptocurrencies on your behalf, generating consistent daily returns.</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>How much profit can I earn?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">You earn 15% profit every 24 hours on your total deposits. The more you invest, the more you earn!</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>What is the minimum deposit?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">The minimum deposit is $5 USDT (TRC20). You can start investing with as little as $5.</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>How do I withdraw my earnings?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">You can withdraw your earnings anytime via USDT (TRC20). Withdrawals are processed within 24 hours after admin confirmation.</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>How does the referral program work?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">You earn $1 instantly for every person who registers using your referral link. There's no limit to how many people you can refer!</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>Is my money safe?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">We use advanced security measures and AI-driven trading strategies to protect your investments. All transactions are recorded on the blockchain.</div>
            </div>
            
            <div class="faq-item">
                <div class="question" onclick="toggleFaq(this)">
                    <span>How do I deposit funds?</span>
                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="answer">Simply send USDT (TRC20) to our wallet address: <strong><?php echo USDT_WALLET; ?></strong>. Then submit your transaction hash on the deposit page for confirmation.</div>
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
