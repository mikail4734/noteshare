<?php
/**
 * Facebook OAuth 2.0 — SDK kullanmadan saf cURL ile
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/baglan.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Yapılandırma .env'den
$fb_app_id     = $config['FB_APP_ID'] ?? '';
$fb_app_secret = $config['FB_APP_SECRET'] ?? '';
$fb_redirect   = $config['FB_REDIRECT_URI'] ?? 'https://notewarehouse.com/fb-login-calistir.php';

if (empty($fb_app_id) || empty($fb_app_secret)) {
    die("Facebook giriş yapılandırılmamış. <a href='giris.php'>← Giriş</a>");
}

// ----- AŞAMA 1: Kod yoksa → Facebook'a yönlendir -----
if (!isset($_GET['code'])) {
    // CSRF için state
    $state = bin2hex(random_bytes(16));
    $_SESSION['fb_state'] = $state;

    $auth_url = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
        'client_id'     => $fb_app_id,
        'redirect_uri'  => $fb_redirect,
        'state'         => $state,
        'scope'         => 'email,public_profile',
        'response_type' => 'code',
    ]);

    header("Location: $auth_url");
    exit;
}

// ----- AŞAMA 2: Kod var → Token al -----
if (!isset($_GET['state']) || !isset($_SESSION['fb_state']) || $_GET['state'] !== $_SESSION['fb_state']) {
    die("Geçersiz oturum. Tekrar deneyin. <a href='giris.php'>← Giriş</a>");
}
unset($_SESSION['fb_state']);

$code = $_GET['code'];

// Access token al
$token_url = "https://graph.facebook.com/v18.0/oauth/access_token?" . http_build_query([
    'client_id'     => $fb_app_id,
    'client_secret' => $fb_app_secret,
    'redirect_uri'  => $fb_redirect,
    'code'          => $code,
]);

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    die("Token alınamadı. " . ($token_data['error']['message'] ?? '') . " <a href='giris.php'>← Giriş</a>");
}

$access_token = $token_data['access_token'];

// ----- AŞAMA 3: Kullanıcı bilgilerini al -----
$user_url = "https://graph.facebook.com/me?" . http_build_query([
    'fields' => 'id,name,email,picture.width(256).height(256)',
    'access_token' => $access_token,
]);

$ch = curl_init($user_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$user_response = curl_exec($ch);
curl_close($ch);

$user = json_decode($user_response, true);

if (!isset($user['id'])) {
    die("Kullanıcı bilgileri alınamadı. <a href='giris.php'>← Giriş</a>");
}

// Facebook bazen e-posta vermez (kullanıcı izin vermemişse)
$email = $user['email'] ?? null;
$name  = $user['name']  ?? 'Facebook Kullanıcısı';
$fb_id = $user['id'];
$picture = $user['picture']['data']['url'] ?? null;

if (!$email) {
    die("Facebook hesabınızın e-postasına erişim izni vermediniz. <a href='giris.php'>← Tekrar Dene</a><br><br>Lütfen Facebook izin ekranında e-posta iznini de onaylayın.");
}

// ----- AŞAMA 4: DB'de kullanıcıyı kontrol et / oluştur -----
try {
    $sorgu = $db->prepare("SELECT * FROM users WHERE email = ?");
    $sorgu->execute([$email]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    $SUPER_ADMINS = ['mikailcelik4734@gmail.com'];
    $rol = in_array(strtolower($email), $SUPER_ADMINS) ? 'admin' : 'user';

    if (!$kullanici) {
        // Yeni kullanıcı oluştur
        $kaydet = $db->prepare("INSERT INTO users (ad, email, password, rol, durum) VALUES (?, ?, ?, ?, 1)");
        $kaydet->execute([
            $name,
            $email,
            password_hash('fb_user_' . uniqid(), PASSWORD_DEFAULT),
            $rol
        ]);
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
        exit;
    }

    $_SESSION['user_id']      = $kullanici['id'];
    $_SESSION['user_email']   = $email;
    $_SESSION['user_name']    = $name;
    $_SESSION['user_picture'] = $picture;
    $_SESSION['logged_in']    = true;
    $_SESSION['rol']          = $kullanici['rol'] ?? 'user';

    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    die("Veritabanı hatası. <a href='giris.php'>← Giriş</a>");
}
?>
