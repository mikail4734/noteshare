<?php
require_once __DIR__ . '/config.php';

// vendor/autoload.php yoksa anlamlı hata göster
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    die("<div style='font-family:sans-serif;padding:50px;text-align:center'>
        <h2>Composer kurulumu eksik</h2>
        <p>Sunucuda <code>vendor/autoload.php</code> bulunamadı.</p>
        <p>SSH'tan şu komutu çalıştırın:<br>
        <code>cd /var/www/html && sudo -u www-data composer install</code></p>
        <a href='giris.php'>← Giriş</a>
    </div>");
}
require_once $autoload;
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/oturum_baslat.php';

// Google_Client sınıfı yoksa
if (!class_exists('Google_Client')) {
    http_response_code(500);
    die("<div style='font-family:sans-serif;padding:50px;text-align:center'>
        <h2>Google API Client paketi eksik</h2>
        <p>vendor/ var ama Google API Client paketi yüklü değil.</p>
        <p>SSH'tan:<br>
        <code>cd /var/www/html && sudo -u www-data composer require google/apiclient</code></p>
        <a href='giris.php'>← Giriş</a>
    </div>");
}

// Config kontrolü
if (empty($config['GOOGLE_CLIENT_ID']) || empty($config['GOOGLE_CLIENT_SECRET'])) {
    http_response_code(500);
    die("<div style='font-family:sans-serif;padding:50px;text-align:center'>
        <h2>Google OAuth yapılandırılmamış</h2>
        <p>.env dosyasında <code>GOOGLE_CLIENT_ID</code> veya <code>GOOGLE_CLIENT_SECRET</code> tanımlı değil.</p>
        <a href='giris.php'>← Giriş</a>
    </div>");
}

try {
    $client = new Google_Client();
    $client->setClientId($config['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($config['GOOGLE_CLIENT_SECRET']);
    $client->setRedirectUri($config['GOOGLE_REDIRECT_URI'] ?? 'https://notewarehouse.com/google-login-calistir.php');
    $client->addScope("email");
    $client->addScope("profile");
} catch (Throwable $e) {
    http_response_code(500);
    die("<div style='font-family:sans-serif;padding:50px;text-align:center'>
        <h2>Google Client oluşturulamadı</h2>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
        <a href='giris.php'>← Giriş</a>
    </div>");
}

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $email   = $google_account_info->email;
        $name    = $google_account_info->name;
        $picture = $google_account_info->picture ?? null;

        $sorgu = $db->prepare("SELECT * FROM users WHERE email = ?");
        $sorgu->execute([$email]);
        $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

        $SUPER_ADMINS = ['mikailcelik4734@gmail.com'];
        $rol = in_array(strtolower($email), $SUPER_ADMINS) ? 'admin' : 'user';

        if (!$kullanici) {
            $kaydet = $db->prepare("INSERT INTO users (ad, email, password, rol, durum) VALUES (?, ?, ?, ?, 1)");
            $kaydet->execute([$name, $email, password_hash('google_user_' . uniqid(), PASSWORD_DEFAULT), $rol]);
            $sorgu->execute([$email]);
            $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
        } else if ($kullanici['rol'] !== 'admin' && in_array(strtolower($email), $SUPER_ADMINS)) {
            $db->prepare("UPDATE users SET rol = 'admin' WHERE email = ?")->execute([$email]);
            $kullanici['rol'] = 'admin';
        }

        if (isset($kullanici['durum']) && (int)$kullanici['durum'] === 0) {
            session_unset();
            session_destroy();
            header("Location: giris.php?hata=engellendiniz");
            exit();
        }

        $_SESSION['user_id']      = $kullanici['id'];
        $_SESSION['user_email']   = $email;
        $_SESSION['user_name']    = $name;
        $_SESSION['user_picture'] = $picture;
        $_SESSION['logged_in']    = true;
        $_SESSION['rol']          = $kullanici['rol'] ?? 'user';

        // 30 günlük kalıcı cookie (Beni hatırla)
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

        header("Location: index.php");
        exit();
    } else {
        die("Google girişi başarısız. Lütfen tekrar dene. <a href='giris.php'>← Giriş</a>");
    }
} else {
    header("Location: " . $client->createAuthUrl());
    exit();
}
?>
