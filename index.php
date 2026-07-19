<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> – <?php echo SITE_TAGLINE; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ─── MODERN NAVBAR ─── */
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
        .modern-navbar .navbar-brand i {
            margin-right: 8px;
            color: #00f5a0;
        }
        .nav-btn {
            padding: 8px 20px;
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
        .nav-btn-login {
            background: rgba(255,255,255,0.08);
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,0.12);
        }
        .nav-btn-login:hover {
            background: rgba(255,255,255,0.15);
            color: #ffffff !important;
        }
        .nav-btn-register {
            background: linear-gradient(135deg, #00f5a0, #00d9f5);
            color: #0a1628 !important;
            box-shadow: 0 4px 15px rgba(0,245,160,0.25);
        }
        .nav-btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,245,160,0.35);
            color: #0a1628 !important;
        }

        /* ─── HERO SECTION ─── */
        .hero-section {
            background: linear-gradient(145deg, #f0f5ff, #ffffff);
            padding: 5rem 1.5rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e9edf2;
            text-align: center;
        }
        .hero-section h1 {
            font-size: clamp(2rem, 6vw, 3.5rem);
            font-weight: 800;
            color: #0d1a2b;
        }
        .hero-section .lead {
            font-size: clamp(1rem, 2.5vw, 1.5rem);
            color: #3a4b5e;
        }
        .hero-section .btn {
            padding: 0.75rem 2.5rem;
            font-size: clamp(1rem, 1.5vw, 1.25rem);
            border-radius: 50px;
            background: #0d6efd;
            border: none;
            color: #fff;
            box-shadow: 0 8px 24px rgba(13, 110, 253, 0.2);
        }
        .hero-section .btn:hover {
            background: #0b5ed7;
            box-shadow: 0 12px 32px rgba(13, 110, 253, 0.35);
            transform: translateY(-2px);
        }

        /* ─── TRUST BADGE ─── */
        .trust-badge {
            background: #e8f4fd;
            border: 1px solid #b8d9f5;
            color: #0d6efd;
            border-radius: 40px;
            padding: 0.5rem 1.8rem;
            display: inline-block;
            font-weight: 600;
        }

        /* ─── CARDS ─── */
        .card {
            background: #ffffff;
            border: 1px solid #e9edf2;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            transition: transform 0.2s, box-shadow 0.3s;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.06);
        }
        .card .card-title {
            font-weight: 600;
            color: #0d1a2b;
        }
        .card .card-text {
            color: #3a4b5e;
        }

        /* ─── EXAMPLE CARDS ─── */
        .example-card {
            background: #ffffff;
            border: 1px solid #e9edf2;
            border-radius: 16px;
            padding: 1.2rem;
            height: 100%;
            transition: all 0.3s ease;
        }
        .example-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border-color: #0d6efd;
        }

        /* ─── TESTIMONIAL CARDS ─── */
        .testimonial-card {
            background: #ffffff;
            border: 1px solid #e9edf2;
            border-left: 4px solid #0d6efd;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 16px;
            height: 100%;
        }

        /* ─── HOW IT WORKS ─── */
        .step-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 12px;
            box-shadow: 0 4px 16px rgba(13, 110, 253, 0.3);
        }

        /* ─── FOOTER ─── */
        .footer {
            margin-top: auto;
            background: #ffffff !important;
            border-top: 1px solid #e9edf2;
            color: #6b7a93;
            padding: 1.5rem 0;
            text-align: center;
        }
        .footer span { color: #6b7a93; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 768px) {
            .modern-navbar .navbar-brand { font-size: 1.1rem; }
            .hero-section { padding: 2.5rem 1rem; }
            .container-fluid { padding-left: 1rem; padding-right: 1rem; }
            .card { margin-bottom: 1rem; }
            .step-circle { width: 48px; height: 48px; font-size: 1.2rem; }
        }
    </style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- ============================================================
    NAVBAR
    ============================================================ -->
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
                        <a href="pages/login.php" class="nav-btn nav-btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pages/register.php" class="nav-btn nav-btn-register">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ============================================================
    HERO SECTION
    ============================================================ -->
    <div class="hero-section text-center">
        <div class="container-fluid">
            <span class="trust-badge">🔥 Over 50,000 investors trust us</span>
            <h1 class="display-4 fw-bold mt-3"><?php echo SITE_NAME; ?></h1>
            <p class="lead"><?php echo SITE_TAGLINE; ?></p>
            <p class="lead">Earn <strong>15% every 24 hours</strong> on your capital – withdraw anytime.</p>
            <p class="text-muted">Refer friends and earn <strong>5% bonus</strong> on every deposit they make.</p>
            <hr class="my-4">
            <p>Start with as little as $10 – no hidden fees.</p>
            <a href="pages/register.php" class="btn btn-success btn-lg">Start Investing Now</a>
            <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
                <a href="pages/faq.php" class="btn btn-outline-primary"><i class="fas fa-question-circle"></i> FAQ</a>
                <a href="pages/reviews.php" class="btn btn-outline-success"><i class="fas fa-star"></i> Read Reviews</a>
            </div>
        </div>
    </div>

    <!-- ============================================================
    FEATURES CARDS
    ============================================================ -->
    <div class="container-fluid my-5">
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="step-circle" style="background: linear-gradient(135deg, #00f5a0, #00d9f5);">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h5 class="card-title">AI Stock Predictions</h5>
                        <p class="card-text">Our proprietary AI scans global markets and executes trades on your behalf, generating consistent daily profits.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="step-circle" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Referral Rewards</h5>
                        <p class="card-text">Every investor you bring earns you 5% of their deposit – and they also earn the daily returns, making it a win-win.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="step-circle" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h5 class="card-title">Instant Withdrawals</h5>
                        <p class="card-text">Request a withdrawal and receive your funds within 24 hours – no locks, no penalties.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
    HOW IT WORKS (Ponzi Explanation)
    ============================================================ -->
    <div class="container-fluid my-5 bg-light p-5 rounded">
        <h2 class="text-center mb-4">How <?php echo SITE_NAME; ?> Works</h2>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <p class="lead text-center">We pool funds from thousands of investors to trade on high-volatility stocks and crypto assets. The profits are distributed daily to all participants.</p>
                <div class="row mt-4">
                    <div class="col-6 col-md-3 text-center">
                        <div class="step-circle">1</div>
                        <p class="fw-bold">Deposit</p>
                        <p class="small text-muted">Fund your account</p>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <div class="step-circle">2</div>
                        <p class="fw-bold">AI Trades</p>
                        <p class="small text-muted">Automated investing</p>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <div class="step-circle">3</div>
                        <p class="fw-bold">Daily Returns</p>
                        <p class="small text-muted">15% every 24 hours</p>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <div class="step-circle">4</div>
                        <p class="fw-bold">Withdraw</p>
                        <p class="small text-muted">Instant withdrawals</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
    TRUSTED PLATFORMS (Examples)
    ============================================================ -->
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

    <!-- ============================================================
    TESTIMONIALS
    ============================================================ -->
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

    <!-- ============================================================
    CALL TO ACTION
    ============================================================ -->
    <div class="container-fluid my-5 text-center" style="background: linear-gradient(135deg, #0a1628, #1a2a4a); padding: 4rem 2rem; border-radius: 20px;">
        <h2 class="text-white">Ready to Start Earning?</h2>
        <p class="text-light opacity-75">Join thousands of investors already making 15% daily returns.</p>
        <a href="pages/register.php" class="btn btn-success btn-lg mt-3" style="background: linear-gradient(135deg, #00f5a0, #00d9f5); border: none; color: #0a1628; font-weight: 700; padding: 12px 40px; border-radius: 50px; box-shadow: 0 8px 30px rgba(0,245,160,0.3);">
            Create Your Account Now
        </a>
    </div>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <footer class="footer mt-auto">
        <div class="container-fluid">
            <span>&copy; 2026 <?php echo SITE_NAME; ?> – All rights reserved.</span>
            <span class="float-end">📧 <?php echo ADMIN_EMAIL; ?></span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
