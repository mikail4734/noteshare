<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/baglan.php';

require_once __DIR__ . '/oturum_baslat.php';

$client = new Google_Client();
$client->setClientId($config['GOOGLE_CLIENT_ID']);
$client->setClientSecret($config['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($config['GOOGLE_REDIRECT_URI']);
$client->addScope("email");
$client->addScope("profile");

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
