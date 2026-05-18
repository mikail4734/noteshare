<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/seo.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { header("Location: haberler.php"); exit; }

$s = $db->prepare("SELECT * FROM haberler WHERE slug = ? AND yayinda = 1");
$s->execute([$slug]);
$haber = $s->fetch(PDO::FETCH_ASSOC);

if (!$haber) {
    http_response_code(404);
    include '404.php';
    exit;
}

// Görüntülenme arttır
try {
    $db->prepare("UPDATE haberler SET goruntulenme = goruntulenme + 1 WHERE id = ?")->execute([$haber['id']]);
} catch (Exception $e) {}

// Diğer haberler
$digerler = $db->prepare("SELECT slug, baslik, kategori FROM haberler WHERE yayinda=1 AND id != ? ORDER BY RAND() LIMIT 3");
$digerler->execute([$haber['id']]);
$digerler = $digerler->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <?php seoMeta($haber['baslik'], $haber['ozet']); ?>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif}
        .prose h2{font-size:1.8rem;font-weight:800;margin:2rem 0 1rem;color:#1e293b}
        .prose h3{font-size:1.4rem;font-weight:700;margin:1.5rem 0 0.5rem;color:#334155}
        .prose p{font-family:'Merriweather',serif;font-size:1.1rem;line-height:1.8;margin-bottom:1.2rem;color:#475569}
        .prose ul{margin:1rem 0 1.5rem 1.5rem;list-style:disc}
        .prose ul li{margin-bottom:0.5rem;line-height:1.7}
        .prose strong{color:#1e293b}
    </style>

    <!-- BlogPosting Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "<?= htmlspecialchars($haber['baslik']) ?>",
      "description": "<?= htmlspecialchars($haber['ozet']) ?>",
      "author": { "@type": "Organization", "name": "<?= htmlspecialchars($haber['yazar']) ?>" },
      "publisher": {
        "@type": "Organization",
        "name": "notewarehouse",
        "logo": { "@type": "ImageObject", "url": "https://notewarehouse.com/logo.png" }
      },
      "datePublished": "<?= date('c', strtotime($haber['tarih'])) ?>",
      "mainEntityOfPage": "https://notewarehouse.com/haber.php?slug=<?= urlencode($haber['slug']) ?>"
    }
    </script>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50">

<nav class="bg-indigo-600 px-8 py-4 shadow-lg flex justify-between items-center sticky top-0 z-50">
    <a href="index.php" class="flex items-center text-white font-black text-xl">
        <img src="/favicon-180.png" class="w-8 h-8 rounded-lg mr-2"> notewarehouse
    </a>
    <a href="haberler.php" class="text-white/90 hover:text-white text-sm">← Tüm Haberler</a>
</nav>

<article class="max-w-3xl mx-auto px-6 py-12">

    <span class="text-xs bg-indigo-100 text-indigo-700 font-black px-3 py-1.5 rounded-full uppercase tracking-wider">
        <?= htmlspecialchars($haber['kategori']) ?>
    </span>

    <h1 class="text-4xl md:text-5xl font-black text-slate-900 mt-4 mb-4 leading-tight">
        <?= htmlspecialchars($haber['baslik']) ?>
    </h1>

    <div class="flex items-center gap-4 text-sm text-slate-500 mb-8 pb-8 border-b border-slate-200">
        <span><i class="fas fa-user mr-1"></i> <strong><?= htmlspecialchars($haber['yazar']) ?></strong></span>
        <span><i class="far fa-calendar mr-1"></i> <?= date('d F Y', strtotime($haber['tarih'])) ?></span>
        <span><i class="far fa-eye mr-1"></i> <?= $haber['goruntulenme'] + 1 ?> görüntülenme</span>
    </div>

    <?php if ($haber['ozet']): ?>
    <p class="text-lg text-slate-600 italic border-l-4 border-indigo-500 pl-4 mb-8">
        <?= htmlspecialchars($haber['ozet']) ?>
    </p>
    <?php endif; ?>

    <div class="prose max-w-none">
        <?= $haber['icerik'] ?>
    </div>

    <!-- Paylaş -->
    <div class="mt-12 pt-8 border-t border-slate-200 flex items-center gap-3">
        <span class="font-bold text-slate-700">Paylaş:</span>
        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($haber['baslik']) ?>&url=https://notewarehouse.com/haber.php?slug=<?= urlencode($haber['slug']) ?>" target="_blank"
           class="w-10 h-10 bg-black text-white rounded-xl flex items-center justify-center hover:scale-110 transition">
            <i class="fab fa-x-twitter"></i>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=https://notewarehouse.com/haber.php?slug=<?= urlencode($haber['slug']) ?>" target="_blank"
           class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center hover:scale-110 transition">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://api.whatsapp.com/send?text=<?= urlencode($haber['baslik'].' https://notewarehouse.com/haber.php?slug='.$haber['slug']) ?>" target="_blank"
           class="w-10 h-10 bg-green-500 text-white rounded-xl flex items-center justify-center hover:scale-110 transition">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <!-- Diğer haberler -->
    <?php if (!empty($digerler)): ?>
    <div class="mt-16">
        <h3 class="text-2xl font-black mb-6">📚 Diğer Haberler</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($digerler as $d): ?>
                <a href="haber.php?slug=<?= urlencode($d['slug']) ?>"
                   class="bg-white p-5 rounded-2xl border border-slate-100 hover:border-indigo-300 hover:shadow-md transition block">
                    <span class="text-[10px] text-indigo-600 font-black uppercase"><?= htmlspecialchars($d['kategori']) ?></span>
                    <h4 class="font-bold text-slate-800 text-sm mt-2 leading-tight"><?= htmlspecialchars($d['baslik']) ?></h4>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</article>

<?php include __DIR__ . '/footer_partial.php'; ?>

</body>
</html>
