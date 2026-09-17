<?php
/**
 * Configuration Settings
 * Database credentials and Application settings
 */

// Database Configuration (Default settings for XAMPP)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'love_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Fallback to SQLite if MySQL is unavailable
define('ENABLE_SQLITE_FALLBACK', true);
define('SQLITE_FILE', __DIR__ . '/database.sqlite');

// Admin Dashboard Credentials
define('ADMIN_PASSWORD', 'love123'); // Change this to your preferred admin password

// Timezone
date_default_timezone_set('Asia/Dhaka');
