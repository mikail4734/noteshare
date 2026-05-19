<?php
/**
 * Merkezi konfigürasyon yükleyici.
 * .env dosyasını okur, $config dizisine doldurur.
 *
 * Tüm dosyalardan: require_once 'config.php'; sonra $config['DB_HOST'] gibi kullan
 */

// .env dosyasını oku
function envYukle($yol) {
    if (!file_exists($yol)) return [];
    $env = [];
    foreach (file($yol, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $satir) {
        $satir = trim($satir);
        if ($satir === '' || $satir[0] === '#') continue;
        if (strpos($satir, '=') === false) continue;
        list($k, $v) = explode('=', $satir, 2);
        $k = trim($k);
        $v = trim($v);
        // Tırnakları temizle
        if ((strlen($v) >= 2) && ($v[0] === '"' || $v[0] === "'") && $v[0] === substr($v, -1)) {
            $v = substr($v, 1, -1);
        }
        $env[$k] = $v;
        if (!getenv($k)) putenv("$k=$v");
    }
    return $env;
}

$envYol = __DIR__ . '/.env';
$envData = envYukle($envYol);

// Helper: önce env, sonra default
function cfg($key, $varsayilan = null) {
    global $envData;
    $val = getenv($key);
    if ($val !== false) return $val;
    if (isset($envData[$key])) return $envData[$key];
    return $varsayilan;
}

// Yapılandırma dizisi
$config = [
    'DB_HOST'     => cfg('DB_HOST', 'localhost'),
    'DB_NAME'     => cfg('DB_NAME', 'notdeposu'),
    'DB_USER'     => cfg('DB_USER', 'root'),
    'DB_PASS'     => cfg('DB_PASS', ''),

    'APP_URL'     => cfg('APP_URL', 'http://localhost/norwarhouse.php'),
    'APP_ENV'     => cfg('APP_ENV', 'production'), // 'production' veya 'development'

    'ANTHROPIC_API_KEY'    => cfg('ANTHROPIC_API_KEY', ''),
    'OPENAI_API_KEY'       => cfg('OPENAI_API_KEY', ''),
    'AI_ENABLED'           => cfg('AI_ENABLED', 'true'), // 'true' veya 'false'

    'GOOGLE_CLIENT_ID'     => cfg('GOOGLE_CLIENT_ID', ''),
    'GOOGLE_CLIENT_SECRET' => cfg('GOOGLE_CLIENT_SECRET', ''),
    'GOOGLE_REDIRECT_URI'  => cfg('GOOGLE_REDIRECT_URI', 'http://localhost/norwarhouse.php/google-login-calistir.php'),

    'FB_APP_ID'        => cfg('FB_APP_ID', ''),
    'FB_APP_SECRET'    => cfg('FB_APP_SECRET', ''),
    'FB_REDIRECT_URI'  => cfg('FB_REDIRECT_URI', 'https://notewarehouse.com/fb-login-calistir.php'),
];

// Production'da hata gösterimi kapalı
if ($config['APP_ENV'] === 'production') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
?>
