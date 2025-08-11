<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'canaco_eventos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App configuration
define('APP_NAME', 'CANACO Eventos');
define('APP_URL', 'http://localhost');
define('UPLOAD_PATH', '/storage/uploads/');
define('LOG_PATH', '/storage/logs/');

// Security
define('SECRET_KEY', 'canaco_secret_key_2024');
define('PASSWORD_SALT', 'canaco_salt_2024');

// Email configuration
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('FROM_EMAIL', 'eventos@canaco.org');
define('FROM_NAME', 'CANACO Eventos');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
ini_set('session.use_strict_mode', 1);
session_start();