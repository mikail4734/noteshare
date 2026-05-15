<?php
require_once __DIR__ . '/config.php';

try {
    $db = new PDO(
        "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4",
        $config['DB_USER'],
        $config['DB_PASS'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    if (($config['APP_ENV'] ?? 'production') === 'development') {
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
    error_log("DB hatası: " . $e->getMessage());
    die("Servis geçici olarak kullanılamıyor. Lütfen daha sonra tekrar deneyin.");
}
?>
