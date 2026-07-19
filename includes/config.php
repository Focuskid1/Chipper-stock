<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'Chipper Stock');
define('SITE_TAGLINE', 'AI-Powered Stock Investment');
define('SITE_URL', 'https://chipper-stock.onrender.com');
define('ADMIN_EMAIL', 'support@chippersstock.com');
define('DAILY_RETURN_PERCENT', 10);
define('REFERRAL_BONUS_PERCENT', 5);
define('MINIMUM_DEPOSIT', 5);
define('MINIMUM_WITHDRAWAL', 5);

// USDT TRC20 Wallet Address
define('USDT_WALLET', 'TCizbVxST3g4WkTzJiPuPXnyhHAerMEzjR');

define('ALLOW_REGISTRATION', true);
define('ALLOW_DEPOSIT', true);
define('ALLOW_WITHDRAWAL', true);
?>
