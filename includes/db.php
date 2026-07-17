<?php
// Detect environment: use DATABASE_URL if set (Render), otherwise fallback to SQLite (local)
$db_url = getenv('DATABASE_URL');

if ($db_url) {
    // --- PostgreSQL (Render) ---
    try {
        $db = new PDO($db_url);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Create all tables (PostgreSQL syntax)
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT,
            phone TEXT,
            balance REAL DEFAULT 0,
            referral_code TEXT UNIQUE,
            referred_by INTEGER DEFAULT 0,
            total_deposits REAL DEFAULT 0,
            total_withdrawals REAL DEFAULT 0,
            daily_profit REAL DEFAULT 0,
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS deposits (
            id SERIAL PRIMARY KEY,
            user_id INTEGER,
            amount REAL,
            method TEXT,
            status TEXT DEFAULT 'pending',
            depositor_name TEXT,
            depositor_phone TEXT,
            depositor_bank TEXT,
            transaction_ref TEXT,
            proof TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS withdrawals (
            id SERIAL PRIMARY KEY,
            user_id INTEGER,
            amount REAL,
            method TEXT,
            account_details TEXT,
            status TEXT DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS transactions (
            id SERIAL PRIMARY KEY,
            user_id INTEGER,
            type TEXT,
            amount REAL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS referrals (
            id SERIAL PRIMARY KEY,
            referrer_id INTEGER,
            referred_id INTEGER,
            bonus REAL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Indexes for performance
        $db->exec("CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_users_referral_code ON users(referral_code)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_deposits_user_id ON deposits(user_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id)");

    } catch (PDOException $e) {
        // Show the real error message (for debugging – remove later!)
        die("PostgreSQL connection error: " . $e->getMessage());
    }
} else {
    // --- SQLite (local development) ---
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/../database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Create all tables (SQLite syntax)
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT,
            phone TEXT,
            balance REAL DEFAULT 0,
            referral_code TEXT UNIQUE,
            referred_by INTEGER DEFAULT 0,
            total_deposits REAL DEFAULT 0,
            total_withdrawals REAL DEFAULT 0,
            daily_profit REAL DEFAULT 0,
            registered_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS deposits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            amount REAL,
            method TEXT,
            status TEXT DEFAULT 'pending',
            depositor_name TEXT,
            depositor_phone TEXT,
            depositor_bank TEXT,
            transaction_ref TEXT,
            proof TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS withdrawals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            amount REAL,
            method TEXT,
            account_details TEXT,
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            type TEXT,
            amount REAL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS referrals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            referrer_id INTEGER,
            referred_id INTEGER,
            bonus REAL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Indexes (SQLite – ignore errors if they already exist)
        @$db->exec("CREATE INDEX idx_users_username ON users(username)");
        @$db->exec("CREATE INDEX idx_users_referral_code ON users(referral_code)");
        @$db->exec("CREATE INDEX idx_deposits_user_id ON deposits(user_id)");
        @$db->exec("CREATE INDEX idx_transactions_user_id ON transactions(user_id)");

    } catch (PDOException $e) {
        die("SQLite connection error: " . $e->getMessage());
    }
}

// Make $db available globally
?>
