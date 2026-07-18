<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';  // Explicitly require functions

if (isLoggedIn()) redirect('/pages/dashboard.php');
$ref = isset($_GET['ref']) ? intval($_GET['ref']) : 0;
?>
<!DOCTYPE html>
<html>
<head><title>Register – <?php echo SITE_NAME; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#f4f7fc; padding:1.5rem 0;">
    <div class="container" style="max-width:480px; padding:1rem;">
        <div class="card shadow-sm border-0 p-4 p-sm-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="color:#0d1a2b;"><?php echo SITE_NAME; ?></h2>
                <p class="text-muted">Create your investment account</p>
            </div>
            <?php displayFlash('error'); displayFlash('success'); ?>
            <form method="POST" action="/includes/auth.php">
                <input type="hidden" name="register" value="1">
                <input type="hidden" name="ref" value="<?php echo $ref; ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Choose a username">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="your@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required placeholder="+234 800 000 0000">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min 8 characters">
                </div>
                <button type="submit" class="btn btn-success w-100 py-2">Create Account</button>
            </form>
            <p class="mt-4 text-center text-muted">
                Already have an account? <a href="/pages/login.php" class="fw-bold">Sign In</a>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
