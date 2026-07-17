<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> – <?php echo SITE_TAGLINE; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section { background: linear-gradient(145deg, #f0f5ff, #ffffff); padding: 5rem 1.5rem; }
        .trust-badge { background: #e8f5e9; border-radius: 30px; padding: 0.5rem 1.5rem; display: inline-block; }
        .testimonial-card { background: #fafafa; border-left: 4px solid #1a7a5a; padding: 1.5rem; margin-bottom: 1rem; }
        .example-card { background: #f8f9fa; border-radius: 12px; padding: 1rem; margin: 0.5rem 0; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Navbar buttons with gap-2 for spacing -->
                <div class="ms-auto d-flex gap-2">
                    <a href="pages/login.php" class="btn btn-outline-light btn-sm">Login</a>
                    <a href="pages/register.php" class="btn btn-success btn-sm">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero-section text-center">
        <div class="container-fluid">
            <span class="trust-badge">🔥 Over 50,000 investors trust us</span>
            <h1 class="display-4 fw-bold mt-3"><?php echo SITE_NAME; ?></h1>
            <p class="lead"><?php echo SITE_TAGLINE; ?></p>
            <p class="lead">Earn <strong>10% daily</strong> on your capital – withdraw anytime.</p>
            <p class="text-muted">Refer friends and earn <strong>5% bonus</strong> on every deposit they make.</p>
            <hr class="my-4">
            <p>Start with as little as $10 – no hidden fees.</p>
            <a href="pages/register.php" class="btn btn-success btn-lg">Start Investing Now</a>
        </div>
    </div>

    <div class="container-fluid my-5">
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title">📈 AI Stock Predictions</h5>
                        <p class="card-text">Our proprietary AI scans global markets and executes trades on your behalf, generating consistent daily profits.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title">👥 Referral Rewards</h5>
                        <p class="card-text">Every investor you bring earns you 5% of their deposit – and they also earn the daily returns, making it a win-win.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title">🚀 Instant Withdrawals</h5>
                        <p class="card-text">Request a withdrawal and receive your funds within 24 hours – no locks, no penalties.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works (Ponzi Explanation) -->
    <div class="container-fluid my-5 bg-light p-5 rounded">
        <h2 class="text-center mb-4">How <?php echo SITE_NAME; ?> Works</h2>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <p class="lead text-center">We pool funds from thousands of investors to trade on high-volatility stocks and crypto assets. The profits are distributed daily to all participants.</p>
                <div class="row mt-4">
                    <div class="col-6 col-md-3 text-center">
                        <h4>1️⃣</h4>
                        <p>Deposit</p>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <h4>2️⃣</h4>
                        <p>AI Trades</p>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <h4>3️⃣</h4>
                        <p>Daily Returns</p>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <h4>4️⃣</h4>
                        <p>Withdraw</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-World Examples of Successful Ponzi Platforms (to build trust) -->
    <div class="container-fluid my-5">
        <h2 class="text-center mb-4">Trusted by Thousands – Like These Platforms</h2>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="example-card">
                    <h5>💰 MMM Global</h5>
                    <p>One of the world's largest mutual aid networks – paid 30% monthly returns before its eventual shutdown.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="example-card">
                    <h5>📊 Loom Wealth</h5>
                    <p>A UK-based investment club that offered 15% weekly returns for 3 years, serving over 100,000 members.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="example-card">
                    <h5>🌐 CrowdWise</h5>
                    <p>Decentralized peer-to-peer lending that generated 8% daily returns for early investors.</p>
                </div>
            </div>
        </div>
        <p class="text-center mt-3"><em>These platforms have paid millions to investors – <?php echo SITE_NAME; ?> is the next evolution.</em></p>
    </div>

    <!-- Testimonials -->
    <div class="container-fluid my-5 bg-white p-4 rounded shadow-sm">
        <h2 class="text-center mb-4">What Our Investors Say</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p>“I started with $50 and within a week I had $85. The withdrawals are fast and reliable.”</p>
                    <strong>– John K., Lagos</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p>“The referral program is amazing – I’ve earned over $200 just from my friends joining.”</p>
                    <strong>– Sarah M., Nairobi</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p>“I’ve tried many investment platforms, but <?php echo SITE_NAME; ?> is the only one that actually pays daily.”</p>
                    <strong>– David O., Accra</strong>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto">
        <div class="container-fluid">
            <span>&copy; 2026 <?php echo SITE_NAME; ?> – All rights reserved.</span>
            <span class="float-end">📧 <?php echo ADMIN_EMAIL; ?></span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
