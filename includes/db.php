<?php
$db_url = getenv('DATABASE_URL');

if ($db_url) {
    // Parse the PostgreSQL URL (postgres://user:pass@host:port/db)
    $parsed = parse_url($db_url);

    if ($parsed === false) {
        die("Invalid DATABASE_URL format.");
    }

    $host = $parsed['host'];
    $port = $parsed['port'] ?? '5432';
    $dbname = ltrim($parsed['path'] ?? '', '/');
    $user = $parsed['user'] ?? '';
    $pass = $parsed['pass'] ?? '';

    if (empty($dbname) || empty($user)) {
        die("Missing database name or user in DATABASE_URL.");
    }

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$pass";

    try {
        $db = new PDO($dsn);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Create tables (PostgreSQL syntax)
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

        // Indexes
        $db->exec("CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_users_referral_code ON users(referral_code)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_deposits_user_id ON deposits(user_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id)");

    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
} else {
    // --- SQLite fallback for local development ---
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/../database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // SQLite tables (same schema, different syntax)
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

        // Indexes
        @$db->exec("CREATE INDEX idx_users_username ON users(username)");
        @$db->exec("CREATE INDEX idx_users_referral_code ON users(referral_code)");
        @$db->exec("CREATE INDEX idx_deposits_user_id ON deposits(user_id)");
        @$db->exec("CREATE INDEX idx_transactions_user_id ON transactions(user_id)");

    } catch (PDOException $e) {
        die("SQLite connection failed: " . $e->getMessage());
    }
}

// Make $db globally accessible
?>

    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT,
    review TEXT NOT NULL,
    rating INTEGER DEFAULT 5,
    status TEXT DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
