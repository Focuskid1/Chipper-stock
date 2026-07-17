<?php
$db = new SQLite3('/data/data/com.termux/files/home/chipper-ponzi/database.db');
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT,
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
?>
