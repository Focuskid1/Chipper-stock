<?php
// ============================================================
// DATABASE CONNECTION (PostgreSQL on Render, SQLite locally)
// ============================================================

$db_url = getenv('DATABASE_URL');

try {
    if ($db_url) {
        // --- PostgreSQL (Render) ---
        $db = new PDO($db_url);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Create tables if they don't exist
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

        $db->exec("CREATE TABLE IF NOT EXISTS reviews (
            id SERIAL PRIMARY KEY,
            name TEXT NOT NULL,
            email TEXT,
            review TEXT NOT NULL,
            rating INTEGER DEFAULT 5,
            status TEXT DEFAULT 'approved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Indexes
        $db->exec("CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_users_referral_code ON users(referral_code)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_deposits_user_id ON deposits(user_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id)");

    } else {
        // --- SQLite (local development) ---
        $db = new PDO('sqlite:' . __DIR__ . '/../database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // SQLite tables
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

        $db->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT,
            review TEXT NOT NULL,
            rating INTEGER DEFAULT 5,
            status TEXT DEFAULT 'approved',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // SQLite indexes (ignore errors if they exist)
        @$db->exec("CREATE INDEX idx_users_username ON users(username)");
        @$db->exec("CREATE INDEX idx_users_referral_code ON users(referral_code)");
        @$db->exec("CREATE INDEX idx_deposits_user_id ON deposits(user_id)");
        @$db->exec("CREATE INDEX idx_transactions_user_id ON transactions(user_id)");
    }

} catch (PDOException $e) {
    // Log the error and show a user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    die("Database error. Please check your configuration.");
}

// Make $db available globally
?>
