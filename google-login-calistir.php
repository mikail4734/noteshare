<?php
// .env dosyasını okuyan basit bir fonksiyon
$env = parse_ini_file('.env');
$google_secret = $env['GOOGLE_CLIENT_SECRET'];

/** @var \Google_Service_Oauth2 $google_oauth */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';

// --- VERİTABANI BAĞLANTISI ---
$host = 'localhost';
$db_adi = 'notdeposu';
$kullanici_adi = 'root';
$sifre = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$db_adi;charset=utf8", $kullanici_adi, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Google Bilgilerin
$clientID = '1007793617267-ts0rc8mvntlg4hkgc9103bu424uss51r.apps.googleusercontent.com';
$clientSecret = 'anahtarınız'; 
$redirectUri = 'http://localhost/norwarhouse.php/google-login-calistir.php'; 

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if(!isset($token['error'])){
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $_SESSION['user_email'] = $email; // Beğeni sistemi bu satır sayesinde çalışacak
        $name  = $google_account_info->name;

        // 1. Kontrol: Bu mail veritabanında var mı?
     // 1. Kontrol: Veritabanında bu kullanıcı var mı bakıyoruz
        $sorgu = $db->prepare("SELECT * FROM users WHERE email = ?");
        $sorgu->execute([$email]);
        $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

        if (!$kullanici) {
            // 2. Kayıt: Kullanıcı yoksa oluştur
            $kaydet = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $kaydet->execute([$email, 'google_user']);
            
            // Yeni kayıt olduğu için bilgilerini (ve otomatik 'user' rolünü) çekiyoruz
            $sorgu->execute([$email]);
            $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
        }
        if (isset($kullanici['durum']) && (int)$kullanici['durum'] === 0) {
            // Kullanıcı engelliyse oturum bilgilerini sil ve hata sayfasına gönder
            session_unset();
            session_destroy();
            header("Location: ../giris.php?hata=engellendiniz");
            exit();
        }

        // 3. Oturum Bilgilerini Hafızaya Al
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name']  = $name;
        $_SESSION['logged_in']  = true;
        
        // KRİTİK NOKTA: Veritabanındaki 'admin' veya 'user' bilgisini buraya yazıyoruz
        $_SESSION['rol']        = $kullanici['rol']; 

        header("Location: index.php"); 
        exit();
    } else {
        die("Token Hatası: " . $token['error_description']);
    }
} else {
    header("Location: " . $client->createAuthUrl());
    exit();
}