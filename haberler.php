<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/seo.php';

$haberler = $db->query("SELECT * FROM haberler WHERE yayinda=1 ORDER BY tarih DESC")->fetchAll(PDO::FETCH_ASSOC);

$katIkon = [
    'duyuru'=>'📢', 'rehber'=>'📘', 'haber'=>'📰', 'genel'=>'📝', 'guncelleme'=>'🚀'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <?php seoMeta('Haberler & Blog', 'notewarehouse blog: eğitim rehberleri, sınav stratejileri, platform güncellemeleri ve öğrenci hikayeleri.'); ?>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50">

<nav class="bg-indigo-600 px-8 py-4 shadow-lg flex justify-between items-center sticky top-0 z-50">
    <a href="index.php" class="flex items-center text-white font-black text-xl">
        <img src="/favicon-180.png" class="w-8 h-8 rounded-lg mr-2"> notewarehouse
    </a>
    <a href="index.php" class="text-white/90 hover:text-white text-sm">← Anasayfa</a>
</nav>

<header class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-12 px-6 text-center">
    <h1 class="text-5xl font-black mb-3">📰 Haberler & Blog</h1>
    <p class="text-indigo-100">Eğitim rehberleri, ipuçları, sınav stratejileri</p>
</header>

<main class="container mx-auto px-6 py-12 max-w-5xl">
    <?php if (empty($haberler)): ?>
        <p class="text-center text-slate-400 py-16">Henüz yayınlanmış haber yok.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($haberler as $h):
                $ikon = $katIkon[$h['kategori']] ?? '📝';
            ?>
                <a href="haber.php?slug=<?= urlencode($h['slug']) ?>"
                   class="bg-white rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition overflow-hidden group block">
                    <div class="h-48 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-7xl">
                        <?= $ikon ?>
                    </div>
                    <div class="p-6">
                        <span class="text-[10px] bg-indigo-100 text-indigo-700 font-black px-2 py-1 rounded-md uppercase tracking-wider"><?= htmlspecialchars($h['kategori']) ?></span>
                        <h3 class="font-extrabold text-xl text-slate-800 mt-3 mb-2 group-hover:text-indigo-600 transition leading-tight">
                            <?= htmlspecialchars($h['baslik']) ?>
                        </h3>
                        <p class="text-sm text-slate-500 leading-relaxed line-clamp-3 mb-3"><?= htmlspecialchars($h['ozet']) ?></p>
                        <div class="flex items-center justify-between text-xs text-slate-400 font-medium border-t border-slate-100 pt-3">
                            <span><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($h['yazar']) ?></span>
                            <span><?= date('d M Y', strtotime($h['tarih'])) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/footer_partial.php'; ?>

</body>
</html>
