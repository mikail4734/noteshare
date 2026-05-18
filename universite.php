<?php
// Tüm bölümler egitim_verileri.json'dan dinamik geliyor
$jsonPath = __DIR__ . '/egitim_verileri.json';
$bolumler = [];
if (file_exists($jsonPath)) {
    $data = json_decode(file_get_contents($jsonPath), true);
    $bolumler = $data['universite']['bolumler'] ?? [];
    sort($bolumler);
}
$site_adi = "notewarehouse";
?>
<?php require_once __DIR__ . '/seo.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php seoMeta('Üniversite Notları', 'Tüm üniversite bölümlerine ait ders notları. Bilgisayar Mühendisliği, Tıp, Hukuk, İşletme ve 80+ bölümden ücretsiz notlar.'); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bolum-card { transition: all 0.25s ease; }
        .bolum-card:hover { transform: translateY(-3px); border-color: #6366f1; box-shadow: 0 12px 24px rgba(99, 102, 241, 0.15); }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 text-slate-900">

<nav class="bg-slate-900 p-4 text-white shadow-2xl sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-bold flex items-center cursor-pointer" onclick="window.location.href='index.php'">
            <i class="fas fa-university mr-3 text-indigo-400"></i> <?= $site_adi ?>
            <span class="ml-2 text-xs font-mono text-indigo-300">v2.1 Academia</span>
        </h1>
        <div class="flex items-center space-x-6">
            <a href="index.php" class="text-sm hover:text-indigo-400 transition">Ana Sayfa</a>
            <a href="notlar.php" class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-xs font-bold uppercase tracking-widest transition">Not Yükle</a>
        </div>
    </div>
</nav>

<header class="py-16 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white text-center">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl md:text-5xl font-light mb-4 uppercase tracking-tighter">Akademik Arşiv</h2>
        <div class="h-1 w-20 bg-indigo-500 mx-auto mb-6"></div>
        <p class="text-slate-400 text-lg max-w-2xl mx-auto font-light italic">
            <?= count($bolumler) ?>+ bölüme ait ders notları, çıkmış sorular ve özetler.
        </p>
        <div class="mt-8 max-w-md mx-auto relative">
            <input type="text" id="bolumAra" placeholder="Bölüm ara... (örn: Bilgisayar)"
                   class="w-full bg-white/10 border border-white/20 rounded-full py-3 px-6 pl-12 text-white placeholder-white/40 outline-none focus:ring-2 focus:ring-indigo-500">
            <i class="fas fa-search absolute left-5 top-4 text-white/40"></i>
        </div>
    </div>
</header>

<main class="container mx-auto py-12 px-4">
    <div id="bolumGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($bolumler as $b):
            $renkler = ['indigo','blue','purple','teal','rose','amber','emerald','cyan','pink','sky'];
            $renk = $renkler[crc32($b) % count($renkler)];
        ?>
            <a href="dersler.php?seviye=<?= urlencode('Üniversite') ?>&okul=<?= urlencode($b) ?>"
               class="bolum-card bg-white border-2 border-slate-100 rounded-2xl p-5 block bolum-item"
               data-ad="<?= mb_strtolower(htmlspecialchars($b)) ?>">
                <div class="w-12 h-12 bg-<?= $renk ?>-50 text-<?= $renk ?>-600 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-sm leading-tight"><?= htmlspecialchars($b) ?></h3>
                <p class="text-xs text-<?= $renk ?>-600 font-bold mt-2 uppercase tracking-wider">Notları Gör →</p>
            </a>
        <?php endforeach; ?>
    </div>

    <p id="sonucBos" class="hidden text-center text-slate-400 py-12">Aramana uygun bölüm bulunamadı.</p>
</main>

<footer class="bg-slate-900 text-slate-500 py-12 border-t border-slate-800">
    <div class="container mx-auto text-center px-4">
        <p class="text-xs uppercase tracking-widest">&copy; <?= date("Y") ?> notewarehouse University Network</p>
    </div>
</footer>

<script>
const aramaInput = document.getElementById('bolumAra');
const items = document.querySelectorAll('.bolum-item');
const bos = document.getElementById('sonucBos');
aramaInput.addEventListener('input', () => {
    const q = aramaInput.value.toLowerCase().trim();
    let bulundu = 0;
    items.forEach(el => {
        const ad = el.dataset.ad || '';
        const eslesti = !q || ad.includes(q);
        el.style.display = eslesti ? '' : 'none';
        if (eslesti) bulundu++;
    });
    bos.classList.toggle('hidden', bulundu > 0);
});
</script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
