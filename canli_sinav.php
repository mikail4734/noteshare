<?php
session_start();
require_once 'baglan.php';
require_once 'helpers.php';

if (!isset($_SESSION['user_email'])) { header("Location: giris.php"); exit; }
$email = $_SESSION['user_email'];

$sinav_id = intval($_GET['id'] ?? 0);
$s = $db->prepare("SELECT * FROM canli_sinavlar WHERE id = ?");
$s->execute([$sinav_id]);
$sinav = $s->fetch(PDO::FETCH_ASSOC);

if (!$sinav) die("Sınav bulunamadı.");

$simdi = time();
$bas = strtotime($sinav['baslangic']);
$bit = strtotime($sinav['bitis']);

if ($simdi < $bas) die("Sınav henüz başlamadı.");
if ($simdi > $bit) die("Sınav bitti. <a href='canli_sinav_sonuc.php?id=$sinav_id'>Sonuçlar</a>");

// Zaten katıldı mı?
$kontrol = $db->prepare("SELECT * FROM canli_sinav_katilim WHERE sinav_id = ? AND kullanici_email = ?");
$kontrol->execute([$sinav_id, $email]);
$katilim = $kontrol->fetch(PDO::FETCH_ASSOC);

// POST: Cevapları kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bitir'])) {
    if ($katilim) die("Zaten cevapladın!");

    $cevaplar = json_decode($_POST['cevaplar'], true);

    $sorular = $db->prepare("SELECT id, dogru_cevap FROM canli_sinav_sorulari WHERE sinav_id = ?");
    $sorular->execute([$sinav_id]);
    $sorular = $sorular->fetchAll(PDO::FETCH_ASSOC);

    $dogru = $yanlis = $bos = 0;
    foreach ($sorular as $sr) {
        $secim = $cevaplar[$sr['id']] ?? null;
        if (!$secim) $bos++;
        elseif ($secim === $sr['dogru_cevap']) $dogru++;
        else $yanlis++;
    }

    $toplam = count($sorular);
    $puan = $toplam > 0 ? round($dogru / $toplam * 100, 2) : 0;

    $db->prepare("INSERT INTO canli_sinav_katilim (sinav_id, kullanici_email, kullanici_ad, dogru_sayisi, yanlis_sayisi, bos_sayisi, puan, bitis_zamani, cevaplar_json) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)")
       ->execute([$sinav_id, $email, $_SESSION['user_name'] ?? 'K', $dogru, $yanlis, $bos, $puan, json_encode($cevaplar)]);

    // XP ver
    xpVer($db, $email, 30 + intval($puan / 5));
    rozetKontrol($db, $email);

    header("Location: canli_sinav_sonuc.php?id=$sinav_id");
    exit;
}

// Soruları çek
$sorular = $db->prepare("SELECT id, soru_metni, secenek_a, secenek_b, secenek_c, secenek_d FROM canli_sinav_sorulari WHERE sinav_id = ? ORDER BY id");
$sorular->execute([$sinav_id]);
$sorular = $sorular->fetchAll(PDO::FETCH_ASSOC);

$bitisZamani = min($bit, $simdi + $sinav['sure_dakika'] * 60);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <title><?= htmlspecialchars($sinav['baslik']) ?> | Canlı Sınav</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body{font-family:'Inter',sans-serif;background:#0f172a;color:#e2e8f0;}
        .opt{transition:all .15s;cursor:pointer}
        .opt:hover{background:#1e293b;border-color:#6366f1}
        .opt.secili{background:#6366f1;color:white;border-color:#4f46e5}
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="min-h-screen">

<?php if ($katilim): ?>
    <div class="max-w-2xl mx-auto p-12 text-center">
        <div class="text-5xl mb-4">✅</div>
        <h2 class="text-2xl font-bold mb-2">Sınavı zaten cevapladın</h2>
        <p class="mb-6">Puanın: <strong class="text-emerald-400 text-xl"><?= $katilim['puan'] ?></strong></p>
        <a href="canli_sinav_sonuc.php?id=<?= $sinav_id ?>" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold">Sıralamayı Gör</a>
    </div>
<?php else: ?>

<nav class="bg-rose-600 px-6 py-3 flex justify-between items-center sticky top-0 z-50 shadow-lg">
    <div>
        <h1 class="font-black text-lg">🔴 CANLI: <?= htmlspecialchars($sinav['baslik']) ?></h1>
        <p class="text-xs text-rose-200"><?= count($sorular) ?> soru · <?= $sinav['sure_dakika'] ?> dk</p>
    </div>
    <div class="bg-white/20 backdrop-blur rounded-xl px-4 py-2 text-center">
        <p class="text-[10px] uppercase font-bold opacity-70">Kalan Süre</p>
        <p id="sayac" class="text-2xl font-black">--:--</p>
    </div>
</nav>

<main class="max-w-3xl mx-auto p-6">
    <form method="POST" id="formE">
        <?php foreach ($sorular as $i => $sr): ?>
            <div class="bg-slate-800 rounded-2xl p-6 mb-5 border border-slate-700" data-soru-id="<?= $sr['id'] ?>">
                <div class="flex items-center mb-4">
                    <span class="bg-rose-500 text-white text-[10px] font-black px-2.5 py-1 rounded-full mr-2">SORU <?= $i+1 ?></span>
                </div>
                <p class="text-lg font-medium mb-4 leading-relaxed"><?= htmlspecialchars($sr['soru_metni']) ?></p>
                <?php foreach (['A','B','C','D'] as $sec): ?>
                    <div class="opt border-2 border-slate-700 rounded-xl px-4 py-3 mb-2 flex items-center" onclick="sec(this, <?= $sr['id'] ?>, '<?= $sec ?>')">
                        <span class="font-black mr-3 text-indigo-400"><?= $sec ?>)</span>
                        <span><?= htmlspecialchars($sr['secenek_' . strtolower($sec)]) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <input type="hidden" name="cevaplar" id="cevaplarInput">
        <button type="button" name="bitir" onclick="bitir()" class="w-full bg-emerald-500 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-emerald-600 shadow-xl">
            <i class="fas fa-flag-checkered mr-2"></i> Sınavı Bitir
        </button>
    </form>
</main>

<script>
const cevaplar = {};
function sec(el, soruId, secim) {
    cevaplar[soruId] = secim;
    el.closest('[data-soru-id]').querySelectorAll('.opt').forEach(o => o.classList.remove('secili'));
    el.classList.add('secili');
}
function bitir() {
    if (!confirm("Bitirmek istediğine emin misin? Cevaplar kilitlenecek.")) return;
    document.getElementById('cevaplarInput').value = JSON.stringify(cevaplar);
    const f = document.getElementById('formE');
    f.insertAdjacentHTML('beforeend', '<input type="hidden" name="bitir" value="1">');
    f.submit();
}
// Geri sayım
const bitis = <?= $bitisZamani ?> * 1000;
function tick() {
    const fark = Math.max(0, bitis - Date.now());
    const dk = Math.floor(fark / 60000);
    const sn = Math.floor((fark % 60000) / 1000);
    document.getElementById('sayac').innerText = String(dk).padStart(2,'0') + ':' + String(sn).padStart(2,'0');
    if (fark <= 0) bitir();
}
tick(); setInterval(tick, 1000);
</script>
<?php endif; ?>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
