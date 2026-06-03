<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';

$dilSlug = strtolower(trim($_GET['dil'] ?? ''));

$dilBilgileri = [
    'ingilizce'  => ['ad' => 'İngilizce',  'bayrak' => '🇬🇧', 'renk' => 'blue',    'aciklama' => 'Dünyada en yaygın konuşulan dil. İş, akademi ve seyahat için vazgeçilmez.'],
    'almanca'    => ['ad' => 'Almanca',    'bayrak' => '🇩🇪', 'renk' => 'yellow',  'aciklama' => 'Avrupa\'nın en çok konuşulan ana dili. Mühendislik ve bilimde önemli.'],
    'fransizca'  => ['ad' => 'Fransızca',  'bayrak' => '🇫🇷', 'renk' => 'red',     'aciklama' => 'Kültür, moda ve diplomasi dili. 29 ülkede resmi dil.'],
    'arapca'     => ['ad' => 'Arapça',     'bayrak' => '🇸🇦', 'renk' => 'green',   'aciklama' => '400 milyondan fazla konuşan. Zengin bir medeniyetin dili.'],
    'cince'      => ['ad' => 'Çince',      'bayrak' => '🇨🇳', 'renk' => 'red',     'aciklama' => 'Dünyanın en çok konuşulan anadili. Mandarin ve ticaret dili.'],
    'ispanyolca' => ['ad' => 'İspanyolca', 'bayrak' => '🇪🇸', 'renk' => 'orange',  'aciklama' => '20\'den fazla ülkede konuşulan, öğrenmesi kolay dillerden biri.'],
    'italyanca'  => ['ad' => 'İtalyanca',  'bayrak' => '🇮🇹', 'renk' => 'emerald', 'aciklama' => 'Sanat, müzik ve mutfak dili. Türkçeye benzer gramer yapısı.'],
    'rusca'      => ['ad' => 'Rusça',      'bayrak' => '🇷🇺', 'renk' => 'violet',  'aciklama' => 'Doğu Avrupa\'nın en geniş konuşulan dili. Kiril alfabesi kullanır.'],
];

if (!isset($dilBilgileri[$dilSlug])) {
    header("Location: index.php");
    exit;
}
$dil = $dilBilgileri[$dilSlug];

$seviyeler = [
    'A1' => ['icon' => '🌱', 'kisa' => 'Başlangıç',  'aciklama' => 'Temel tanışma, sayılar, günlük ifadeler.'],
    'A2' => ['icon' => '🌿', 'kisa' => 'Temel',      'aciklama' => 'Alışveriş, seyahat, kısa konuşmalar.'],
    'B1' => ['icon' => '📖', 'kisa' => 'Orta Altı',  'aciklama' => 'Günlük olaylarda bağımsız iletişim.'],
    'B2' => ['icon' => '🎓', 'kisa' => 'Orta Üstü',  'aciklama' => 'Karmaşık metinler ve akıcı konuşma.'],
    'C1' => ['icon' => '🏆', 'kisa' => 'İleri',       'aciklama' => 'Akademik ve profesyonel düzey.'],
    'C2' => ['icon' => '🌟', 'kisa' => 'Ustalık',    'aciklama' => 'Anadil düzeyinde akıcılık.'],
];

// Renk paleti
$renkMap = [
    'blue'    => ['bg'=>'bg-blue-600',    'hover'=>'hover:border-blue-500',    'shadow'=>'rgba(37,99,235,0.18)',  'text'=>'text-blue-600',    'soft'=>'bg-blue-50'],
    'yellow'  => ['bg'=>'bg-yellow-500',  'hover'=>'hover:border-yellow-500',  'shadow'=>'rgba(234,179,8,0.18)',  'text'=>'text-yellow-600',  'soft'=>'bg-yellow-50'],
    'red'     => ['bg'=>'bg-red-600',     'hover'=>'hover:border-red-500',     'shadow'=>'rgba(239,68,68,0.18)',  'text'=>'text-red-600',     'soft'=>'bg-red-50'],
    'green'   => ['bg'=>'bg-green-600',   'hover'=>'hover:border-green-500',   'shadow'=>'rgba(22,163,74,0.18)',  'text'=>'text-green-600',   'soft'=>'bg-green-50'],
    'orange'  => ['bg'=>'bg-orange-500',  'hover'=>'hover:border-orange-500',  'shadow'=>'rgba(249,115,22,0.18)', 'text'=>'text-orange-600',  'soft'=>'bg-orange-50'],
    'emerald' => ['bg'=>'bg-emerald-600', 'hover'=>'hover:border-emerald-500', 'shadow'=>'rgba(16,185,129,0.18)', 'text'=>'text-emerald-600', 'soft'=>'bg-emerald-50'],
    'violet'  => ['bg'=>'bg-violet-600',  'hover'=>'hover:border-violet-500',  'shadow'=>'rgba(124,58,237,0.18)', 'text'=>'text-violet-600',  'soft'=>'bg-violet-50'],
];
$r = $renkMap[$dil['renk']] ?? $renkMap['blue'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $dil['bayrak'] ?> <?= $dil['ad'] ?> Öğren | notewarehouse</title>
    <meta name="description" content="<?= $dil['ad'] ?> dil notları. A1'den C2'ye tüm seviyeler için ücretsiz ders notları, kelime ve gramer.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sv-card { transition: all 0.25s; }
        .sv-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px <?= $r['shadow'] ?>; }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 text-slate-900">

<!-- NAV -->
<nav class="bg-slate-900 px-6 py-4 text-white shadow-xl sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold cursor-pointer" onclick="window.location.href='index.php'">
            <i class="fas fa-language mr-3 <?= $r['text'] ?>"></i> notewarehouse
        </h1>
        <a href="index.php" class="text-sm hover:opacity-70 transition">← Geri Dön</a>
    </div>
</nav>

<!-- HERO -->
<header class="<?= $r['bg'] ?> text-white py-16 px-6 text-center shadow-inner">
    <div class="text-8xl mb-4"><?= $dil['bayrak'] ?></div>
    <h2 class="text-4xl md:text-5xl font-black mb-3"><?= $dil['ad'] ?> Öğren</h2>
    <p class="text-white/80 max-w-xl mx-auto"><?= $dil['aciklama'] ?></p>
</header>

<main class="container mx-auto py-14 px-4">

    <div class="text-center mb-10">
        <h3 class="text-3xl font-extrabold text-slate-800">Seviyeni Seç</h3>
        <p class="text-slate-500 mt-2">Avrupa Dil Çerçevesi (CEFR) standartlarına göre seviyeni seç, notlara ulaş</p>
    </div>

    <!-- SEVİYE KARTLARI → dersler.php'ye gider -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <?php foreach ($seviyeler as $kod => $sv):
            $kategori = $dilSlug . '-' . strtolower($kod);
        ?>
        <a href="dersler.php?kategori=<?= urlencode($kategori) ?>"
           class="sv-card bg-white border-2 border-slate-100 <?= $r['hover'] ?> rounded-3xl p-7 flex items-center gap-5">
            <div class="w-16 h-16 flex-none <?= $r['soft'] ?> rounded-2xl flex items-center justify-center text-4xl">
                <?= $sv['icon'] ?>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black <?= $r['text'] ?>"><?= $kod ?></span>
                    <span class="text-sm font-semibold text-slate-400"><?= $sv['kisa'] ?></span>
                </div>
                <p class="text-sm text-slate-500 mt-1 leading-snug"><?= $sv['aciklama'] ?></p>
            </div>
            <i class="fas fa-chevron-right text-slate-300"></i>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Bilgi kutusu -->
    <div class="max-w-5xl mx-auto mt-10 <?= $r['soft'] ?> rounded-2xl p-5 flex items-center gap-4">
        <i class="fas fa-lightbulb <?= $r['text'] ?> text-2xl"></i>
        <p class="text-sm text-slate-600">
            Bir seviyeye tıkladığında o seviyenin <strong><?= $dil['ad'] ?></strong> notları açılır.
            İstersen kendi notunu da ekleyerek topluluğa katkı sağlayabilirsin.
        </p>
    </div>

</main>

<?php include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
