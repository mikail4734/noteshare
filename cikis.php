<?php
require_once __DIR__ . '/oturum_baslat.php';

// Oturum verilerini temizle
$_SESSION = [];

// Session cookie'sini geçmişe at (tarayıcıdan temizle)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_unset();
session_destroy();

header("Location: index.php");
exit();
?>
