<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cegah re-declare
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);

    // Koneksi ke database
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'db_gudang';
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Konfigurasi Email
    defined('MAIL_HOST')        || define('MAIL_HOST', 'smtp.gmail.com');
    defined('MAIL_USERNAME')    || define('MAIL_USERNAME', '15anasanjiabrori@gmail.com');
    defined('MAIL_PASSWORD')    || define('MAIL_PASSWORD', 'bpqw upgd bdji iogz');
    defined('MAIL_FROM')        || define('MAIL_FROM', 'anasanji27@gmail.com');
    defined('MAIL_FROM_NAME')   || define('MAIL_FROM_NAME', 'Admin Gudang');
    defined('MAIL_PORT')        || define('MAIL_PORT', 587);
    defined('MAIL_ENCRYPTION')  || define('MAIL_ENCRYPTION', 'tls');

    // URL dasar aplikasi (pastikan sesuai folder)
    defined('BASE_URL')         || define('BASE_URL', 'http://localhost/user_management/');
}
