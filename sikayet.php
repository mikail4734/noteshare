<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


$dosya_adi = basename($_SERVER['PHP_SELF'], ".php");
$sayfa_basliklari = [
    'populer-notlar' => ['baslik' => 'En Popüler Notlar', 'ikon' => 'fa-fire', 'renk' => 'text-orange-500'],
    'en-cok-paylasanlar' => ['baslik' => 'En Çok Paylaşanlar', 'ikon' => 'fa-trophy', 'renk' => 'text-yellow-500'],
    'begendigim-notlar' => ['baslik' => 'Beğendiğim Notlar', 'ikon' => 'fa-heart', 'renk' => 'text-red-500'],
    'ayarlar' => ['baslik' => 'Ayarlar', 'ikon' => 'fa-cog', 'renk' => 'text-slate-500'],
    'sss' => ['baslik' => 'Sıkça Sorulan Sorular', 'ikon' => 'fa-question-circle', 'renk' => 'text-blue-500'],
    'sikayet' => ['baslik' => 'Şikayet ve Bildirim', 'ikon' => 'fa-exclamation-triangle', 'renk' => 'text-red-500']
];

$aktif_sayfa = isset($sayfa_basliklari[$dosya_adi]) ? $sayfa_basliklari[$dosya_adi] : ['baslik' => 'Sayfa', 'ikon' => 'fa-file', 'renk' => 'text-indigo-500'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $aktif_sayfa['baslik']; ?> | notewarehouse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50">

    <nav class="bg-[#4f46e5] px-8 py-4 shadow-lg flex items-center justify-between">
        <a href="index.php" class="text-white hover:text-indigo-200 transition flex items-center font-bold">
            <i class="fas fa-arrow-left mr-2"></i> Ana Sayfaya Dön
        </a>
        <span class="text-white font-black tracking-tighter text-xl">notewarehouse</span>
    </nav>

    <main class="container mx-auto py-16 px-6 max-w-4xl">
        <div class="bg-white p-10 rounded-3xl shadow-sm border border-slate-100 text-center">
            
            <i class="fas <?php echo $aktif_sayfa['ikon']; ?> <?php echo $aktif_sayfa['renk']; ?> text-6xl mb-6"></i>
            <h1 class="text-4xl font-extrabold text-slate-800 mb-4"><?php echo $aktif_sayfa['baslik']; ?></h1>
            
            <p class="text-slate-500 text-lg">
                Bu sayfanın tasarımı ve veritabanı bağlantıları yakında eklenecektir.
            </p>
            <div class="mt-8">
                <i class="fas fa-tools text-slate-300 text-8xl"></i>
            </div>
            
        </div>
    </main>

<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>