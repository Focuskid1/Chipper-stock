<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';  // Explicitly require functions

if (isLoggedIn()) redirect('/pages/dashboard.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../assets/js/chat.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="../assets/js/chat.js"></script>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#f4f7fc;">
    <div class="container" style="max-width:440px; padding:1.5rem;">
        <div class="card shadow-sm border-0 p-4 p-sm-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="color:#0d1a2b;"><?php echo SITE_NAME; ?></h2>
                <p class="text-muted">Sign in to your investment account</p>
            </div>
            <?php displayFlash('error'); displayFlash('success'); ?>
            <form method="POST" action="/includes/auth.php">
                <input type="hidden" name="login" value="1">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Enter your username">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-success w-100 py-2">Sign In</button>
            </form>
            <p class="mt-4 text-center text-muted">
                Don't have an account? <a href="/pages/register.php" class="fw-bold">Register</a>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
