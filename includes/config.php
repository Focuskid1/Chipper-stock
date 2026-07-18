<?php
// Start session first, before ANY output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'Chipper Stock');
define('SITE_TAGLINE', 'AI-Powered Stock Investment');
define('SITE_URL', 'http://chipper-stock.onrender.com');
define('ADMIN_EMAIL', 'support@chippersstock.com');
define('DAILY_RETURN_PERCENT', 10);
define('REFERRAL_BONUS_PERCENT', 5);
define('MINIMUM_DEPOSIT', 10);
define('MINIMUM_WITHDRAWAL', 5);

define('BANK_NAME', 'Moniepoint MFB');
define('ACCOUNT_NAME', 'Chibuike Paul Edomani');
define('ACCOUNT_NUMBER', '5894666591');
define('BANK_SWIFT', 'MNIEUS33');

define('ALLOW_REGISTRATION', true);
define('ALLOW_DEPOSIT', true);
define('ALLOW_WITHDRAWAL', true);
?>
