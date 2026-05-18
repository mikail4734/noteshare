<?php
/**
 * Merkezi Oturum Başlatıcı
 *
 * - Cookie ömrü 30 gün (Beni hatırla varsayılan olarak aktif)
 * - Her sayfa açılışında cookie yenilenir (kayan oturum / rolling session)
 * - Kullanıcı 30 gün boyunca siteye uğradığı sürece girişli kalır
 */

if (session_status() === PHP_SESSION_NONE) {

    $sessionOmru = 30 * 24 * 60 * 60; // 30 gün (saniye)

    // PHP'ye 30 gün boyunca session dosyasını silme
    ini_set('session.gc_maxlifetime', $sessionOmru);
    ini_set('session.cookie_lifetime', $sessionOmru);

    // Cookie parametrelerini ayarla
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
              (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => $sessionOmru,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();

    // Kayan oturum: Kullanıcı giriş yapmışsa her sayfa açılışında cookie'yi yenile
    // Böylece son aktivitesinden itibaren 30 gün daha girişli kalır
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        setcookie(session_name(), session_id(), [
            'expires'  => time() + $sessionOmru,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}
