<?php
$jsonPath = __DIR__ . '/egitim_verileri.json';
$dersler = [];
if (file_exists($jsonPath)) {
    $data = json_decode(file_get_contents($jsonPath), true);
    $dersler = $data['ortaokul']['dersler'] ?? [];
    sort($dersler);
}
$site_adi = "notewarehouse";

$dersIkon = [
    'Türkçe' => 'fa-pen-nib', 'Matematik' => 'fa-calculator',
    'Fen Bilimleri' => 'fa-microscope', 'Sosyal Bilgiler' => 'fa-map-marked-alt',
    'T.C. İnkılap Tarihi ve Atatürkçülük' => 'fa-star-and-crescent',
    'İngilizce' => 'fa-globe', 'Din Kültürü ve Ahlak Bilgisi' => 'fa-mosque',
    'Bilişim Teknolojileri' => 'fa-laptop-code', 'Görsel Sanatlar' => 'fa-palette',
    'Müzik' => 'fa-music', 'Beden Eğitimi ve Spor' => 'fa-futbol',
];
$defaultIkon = 'fa-book';
?>
<?php require_once __DIR__ . '/seo.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <?php seoMeta('Ortaokul Notları', 'LGS hazırlık ve 5-8. sınıf ortaokul ders notları. Türkçe, Matematik, Fen Bilimleri, Sosyal Bilgiler, İngilizce ve daha fazlası için ücretsiz notlar.'); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .ders-card { transition: all 0.25s; }
        .ders-card:hover { transform: translateY(-4px); border-color: #10b981; box-shadow: 0 12px 24px rgba(16,185,129,0.15); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

<nav class="bg-emerald-600 p-4 text-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-bold cursor-pointer" onclick="window.location.href='index.php'">
            <i class="fas fa-book-reader mr-2 text-yellow-300"></i> <?= $site_adi ?>
        </h1>
        <a href="index.php" class="hover:text-emerald-200 transition text-sm">← Ana Sayfa</a>
    </div>
</nav>

<header class="py-16 bg-gradient-to-br from-emerald-500 to-teal-600 text-white text-center">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-extrabold mb-3">Ortaokul Notları</h2>
        <p class="text-emerald-100 text-lg max-w-xl mx-auto">LGS hazırlık + 5-6-7-8. sınıf · <?= count($dersler) ?> ders</p>
        <input type="text" id="dersAra" placeholder="Ders ara..." class="mt-6 bg-white/10 border border-white/20 rounded-full py-2 px-5 text-sm text-white placeholder-white/40 outline-none w-72">
    </div>
</header>

<main class="container mx-auto py-12 px-4">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <?php foreach ($dersler as $d):
            $ikon = $dersIkon[$d] ?? $defaultIkon;
        ?>
            <a href="dersler.php?seviye=<?= urlencode('Orta Okul') ?>&ders=<?= urlencode($d) ?>"
               class="ders-card bg-white border-2 border-slate-100 rounded-2xl p-5 text-center block ders-item"
               data-ad="<?= mb_strtolower(htmlspecialchars($d)) ?>">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <i class="fas <?= $ikon ?> text-lg"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-xs leading-tight"><?= htmlspecialchars($d) ?></h3>
            </a>
        <?php endforeach; ?>
    </div>
    <p id="bosUyari" class="hidden text-center text-slate-400 py-12">Sonuç bulunamadı.</p>
</main>

<footer class="bg-emerald-900 text-emerald-200 py-8 text-center text-xs">
    &copy; <?= date("Y") ?> notewarehouse Ortaokul Akademisi
</footer>

<script>
const items = document.querySelectorAll('.ders-item');
const bos = document.getElementById('bosUyari');
document.getElementById('dersAra').addEventListener('input', e => {
    const q = e.target.value.toLowerCase().trim();
    let say = 0;
    items.forEach(el => {
        const eslesti = !q || el.dataset.ad.includes(q);
        el.style.display = eslesti ? '' : 'none';
        if (eslesti) say++;
    });
    bos.classList.toggle('hidden', say > 0);
});
</script>
</body>
</html>
