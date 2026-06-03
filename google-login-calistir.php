<?php
require_once __DIR__ . '/config.php';

// ── Auth akışında hatayı ASLA boş sayfa olarak gösterme ──
// (Giriş kritik; boş ekran yerine anlamlı mesaj + log)
ini_set('display_errors', '0');
error_reporting(E_ALL);
$LOG = __DIR__ . '/google_login_error.log';

function googleHata(string $baslik, string $detay, string $log): void {
    @file_put_contents($log, '[' . date('Y-m-d H:i:s') . "] $baslik :: $detay\n", FILE_APPEND);
    $devMode = (($GLOBALS['config']['APP_ENV'] ?? '') !== 'production');
    http_response_code(500);
    echo "<div style='font-family:sans-serif;max-width:560px;margin:60px auto;padding:32px;border:1px solid #eee;border-radius:16px;text-align:center'>
        <div style='font-size:48px'>⚠️</div>
        <h2 style='color:#dc2626'>Google ile giriş yapılamadı</h2>
        <p style='color:#475569'>" . htmlspecialchars($baslik) . "</p>"
        . ($devMode ? "<pre style='text-align:left;background:#f8fafc;padding:12px;border-radius:8px;font-size:12px;overflow:auto'>" . htmlspecialchars($detay) . "</pre>" : "")
        . "<a href='giris.php' style='display:inline-block;margin-top:16px;background:#4f46e5;color:#fff;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:bold'>← Giriş sayfasına dön</a>
    </div>";
    exit;
}

// vendor/autoload.php kontrolü
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    googleHata("Sunucu kurulumu eksik (vendor yok).",
        "SSH: cd /var/www/html && sudo -u www-data composer install", $LOG);
}
require_once $autoload;
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/oturum_baslat.php';

// Google_Client sınıfı kontrolü
if (!class_exists('Google_Client')) {
    googleHata("Google API Client paketi yüklü değil.",
        "SSH: cd /var/www/html && sudo -u www-data composer require google/apiclient:^2.15", $LOG);
}

// Config kontrolü
if (empty($config['GOOGLE_CLIENT_ID']) || empty($config['GOOGLE_CLIENT_SECRET'])) {
    googleHata("Google OAuth yapılandırılmamış.",
        ".env dosyasında GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET tanımlı değil.", $LOG);
}

// ── Redirect URI: .env'deki localhost ise ve canlıdaysak otomatik düzelt ──
$redirectUri = $config['GOOGLE_REDIRECT_URI'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
$canliMi = ($host !== '' && stripos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false);
if ($redirectUri === '' || ($canliMi && stripos($redirectUri, 'localhost') !== false)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $redirectUri = $scheme . '://' . $host . '/google-login-calistir.php';
}

try {
    $client = new Google_Client();
    $client->setClientId($config['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($config['GOOGLE_CLIENT_SECRET']);
    $client->setRedirectUri($redirectUri);
    $client->addScope('openid');
    $client->addScope('email');
    $client->addScope('profile');
} catch (Throwable $e) {
    googleHata("Google Client oluşturulamadı.", $e->getMessage(), $LOG);
}

// ── Google'dan hata ile döndüyse (kullanıcı reddetti vb.) ──
if (isset($_GET['error'])) {
    googleHata("Google girişi iptal edildi veya reddedildi.",
        "error=" . $_GET['error'], $LOG);
}

// ── 1. AŞAMA: code yoksa Google'a yönlendir ──
if (!isset($_GET['code'])) {
    try {
        header('Location: ' . $client->createAuthUrl());
        exit;
    } catch (Throwable $e) {
        googleHata("Google yönlendirme adresi oluşturulamadı.", $e->getMessage(), $LOG);
    }
}

// ── 2. AŞAMA: code ile geri döndü → token al, kullanıcı bilgisi çek ──
try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        googleHata("Token alınamadı (redirect_uri uyuşmazlığı olabilir).",
            ($token['error'] ?? '') . ' :: ' . ($token['error_description'] ?? '') . "\nKullanılan redirect_uri: " . $redirectUri, $LOG);
    }

    $client->setAccessToken($token);

    // Önce id_token'dan bilgi al (en sağlam yol, ek servis gerekmez)
    $email = $name = $picture = null;
    $payload = null;
    try { $payload = $client->verifyIdToken(); } catch (Throwable $e) { $payload = null; }

    if (is_array($payload) && !empty($payload['email'])) {
        $email   = $payload['email'];
        $name    = $payload['name'] ?? (explode('@', $email)[0]);
        $picture = $payload['picture'] ?? null;
    } else {
        // Yedek: Oauth2 servisinden çek
        if (class_exists('Google_Service_Oauth2')) {
            $oauth = new Google_Service_Oauth2($client);
            $info  = $oauth->userinfo->get();
            $email   = $info->email;
            $name    = $info->name ?: (explode('@', $email)[0]);
            $picture = $info->picture ?? null;
        }
    }

    if (empty($email)) {
        googleHata("Google'dan e-posta bilgisi alınamadı.",
            "Token alındı ama userinfo boş. id_token payload: " . json_encode($payload), $LOG);
    }

    // ── Kullanıcıyı bul / oluştur ──
    $sorgu = $db->prepare("SELECT * FROM users WHERE email = ?");
    $sorgu->execute([$email]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    $SUPER_ADMINS = ['mikailcelik4734@gmail.com'];
    $rol = in_array(strtolower($email), $SUPER_ADMINS) ? 'admin' : 'user';

    if (!$kullanici) {
        $kaydet = $db->prepare("INSERT INTO users (ad, email, password, rol, durum) VALUES (?, ?, ?, ?, 1)");
        $kaydet->execute([$name, $email, password_hash('google_' . bin2hex(random_bytes(8)), PASSWORD_DEFAULT), $rol]);
        $sorgu->execute([$email]);
        $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
    } else if (($kullanici['rol'] ?? '') !== 'admin' && in_array(strtolower($email), $SUPER_ADMINS)) {
        $db->prepare("UPDATE users SET rol = 'admin' WHERE email = ?")->execute([$email]);
        $kullanici['rol'] = 'admin';
    }

    // Engellenmiş mi?
    if (isset($kullanici['durum']) && (int)$kullanici['durum'] === 0) {
        session_unset();
        session_destroy();
        header('Location: giris.php?hata=engellendiniz');
        exit;
    }

    // ── Oturum aç ──
    $_SESSION['user_id']      = $kullanici['id'];
    $_SESSION['user_email']   = $email;
    $_SESSION['user_name']    = $kullanici['ad'] ?? $name;
    $_SESSION['user_picture'] = $picture;
    $_SESSION['logged_in']    = true;
    $_SESSION['rol']          = $kullanici['rol'] ?? 'user';

    // 30 günlük kalıcı cookie
    $sessionOmru = 30 * 24 * 60 * 60;
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie(session_name(), session_id(), [
        'expires'  => time() + $sessionOmru,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    header('Location: index.php');
    exit;

} catch (Throwable $e) {
    googleHata("Giriş işlenirken beklenmeyen bir hata oluştu.",
        $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine(), $LOG);
}
