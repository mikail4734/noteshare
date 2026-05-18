<?php
$jsonPath = __DIR__ . '/egitim_verileri.json';
$dersler = [];
$okullar = [];
if (file_exists($jsonPath)) {
    $data = json_decode(file_get_contents($jsonPath), true);
    $dersler = $data['lise']['dersler'] ?? [];
    $okullar = $data['lise']['okullar'] ?? [];
    sort($dersler);
    sort($okullar);
}
$site_adi = "notewarehouse";

// İkon eşleştirmesi
$dersIkon = [
    'Matematik' => 'fa-calculator', 'Geometri' => 'fa-shapes',
    'Fizik' => 'fa-atom', 'Kimya' => 'fa-flask', 'Biyoloji' => 'fa-dna',
    'Türk Dili ve Edebiyatı' => 'fa-feather-alt', 'Türk Edebiyatı' => 'fa-feather-alt',
    'Tarih' => 'fa-landmark', 'Coğrafya' => 'fa-globe-africa',
    'Felsefe' => 'fa-brain', 'Psikoloji' => 'fa-brain',
    'İngilizce' => 'fa-language', 'Almanca' => 'fa-language',
    'Din Kültürü ve Ahlak Bilgisi' => 'fa-mosque', 'Beden Eğitimi' => 'fa-futbol',
    'Müzik' => 'fa-music', 'Görsel Sanatlar' => 'fa-palette',
    'Bilgisayar Bilimi' => 'fa-laptop-code',
];
$defaultIkon = 'fa-book';
?>
<?php require_once __DIR__ . '/seo.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <?php seoMeta('Lise Notları', 'TYT, AYT ve 9-12. sınıf lise ders notları. Matematik, Fizik, Kimya, Biyoloji, Edebiyat, Tarih ve 40+ ders için ücretsiz notlar.'); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .ders-card { transition: all 0.25s; }
        .ders-card:hover { transform: translateY(-4px); border-color: #ef4444; box-shadow: 0 12px 24px rgba(239,68,68,0.15); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

<nav class="bg-slate-900 p-4 text-white shadow-xl sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold cursor-pointer" onclick="window.location.href='index.php'">
            <i class="fas fa-graduation-cap mr-3 text-red-500"></i> <?= $site_adi ?>
        </h1>
        <a href="index.php" class="text-sm hover:text-red-400 transition">← Geri Dön</a>
    </div>
</nav>

<header class="py-16 bg-gradient-to-br from-red-600 to-rose-700 text-white text-center shadow-inner">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl md:text-5xl font-black mb-3 uppercase tracking-tight">Lise Notları</h2>
        <p class="text-red-100 text-lg opacity-90">TYT, AYT, 9-10-11-12. sınıf · <?= count($dersler) ?> ders</p>

        <!-- Lise türü filtresi -->
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3 max-w-3xl mx-auto">
            <select id="liseTuruFilt" class="bg-white/10 border border-white/20 text-white rounded-full py-2 px-5 text-sm font-medium outline-none">
                <option value="">📚 Tüm Lise Türleri</option>
                <?php foreach ($okullar as $o): ?>
                    <option value="<?= htmlspecialchars($o) ?>" class="text-slate-800"><?= htmlspecialchars($o) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="dersAra" placeholder="Ders ara..." class="bg-white/10 border border-white/20 rounded-full py-2 px-5 text-sm text-white placeholder-white/40 outline-none w-56">
        </div>
    </div>
</header>

<main class="container mx-auto py-12 px-4">
    <div id="dersGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <?php foreach ($dersler as $d):
            $ikon = $dersIkon[$d] ?? $defaultIkon;
        ?>
            <a href="#" onclick="goDers('<?= htmlspecialchars(addslashes($d)) ?>'); return false;"
               class="ders-card bg-white border-2 border-slate-100 rounded-2xl p-5 text-center block ders-item"
               data-ad="<?= mb_strtolower(htmlspecialchars($d)) ?>">
                <div class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <i class="fas <?= $ikon ?> text-lg"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-xs leading-tight"><?= htmlspecialchars($d) ?></h3>
            </a>
        <?php endforeach; ?>
    </div>

    <p id="bosUyari" class="hidden text-center text-slate-400 py-12">Sonuç bulunamadı.</p>
</main>

<footer class="bg-slate-900 text-slate-400 py-8 text-center text-xs">
    &copy; <?= date("Y") ?> notewarehouse Lise Akademisi
</footer>

<script>
function goDers(ders) {
    const liseTuru = document.getElementById('liseTuruFilt').value;
    const url = new URLSearchParams({ seviye: 'Lise', ders: ders });
    if (liseTuru) url.set('okul', liseTuru);
    window.location.href = 'dersler.php?' + url.toString();
}

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
