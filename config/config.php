<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);


$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
      
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . "=" . trim($parts[1]));
            $_ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}


define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', 'jwt_api'); 
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'super_secret_key_123');