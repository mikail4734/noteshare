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

// Sıralama
$k = $db->prepare("SELECT * FROM canli_sinav_katilim WHERE sinav_id = ? ORDER BY puan DESC, bitis_zamani ASC");
$k->execute([$sinav_id]);
$siralama = $k->fetchAll(PDO::FETCH_ASSOC);

// Birinciye rozet ver
if (!empty($siralama) && time() > strtotime($sinav['bitis'])) {
    rozetKazandi($db, $siralama[0]['kullanici_email'], 'sinav_kazanan');
}

$benimSira = null;
foreach ($siralama as $i => $r) {
    if ($r['kullanici_email'] === $email) { $benimSira = $i + 1; break; }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <title><?= htmlspecialchars($sinav['baslik']) ?> - Sonuçlar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body>

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <h1 class="font-extrabold text-2xl flex items-center">
        <span class="bg-yellow-50 text-yellow-500 p-2.5 rounded-xl mr-3 border border-yellow-100"><i class="fas fa-trophy"></i></span>
        Sıralama: <?= htmlspecialchars($sinav['baslik']) ?>
    </h1>
    <a href="canli_sinavlar.php" class="text-sm text-slate-500 hover:text-rose-600 font-bold">← Tüm Sınavlar</a>
</nav>

<main class="container mx-auto px-6 py-10 max-w-3xl">

    <?php if ($benimSira): ?>
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-3xl p-6 text-white mb-6 text-center">
            <p class="text-sm opacity-80 mb-1">Senin Sıralaman</p>
            <p class="text-6xl font-black">#<?= $benimSira ?></p>
            <p class="text-sm mt-2 opacity-90"><?= count($siralama) ?> kişi arasından</p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <?php foreach ($siralama as $i => $r):
            $emoji = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#'.($i+1)));
            $bg = $i === 0 ? 'from-yellow-50 to-amber-50' : ($i === 1 ? 'from-slate-50 to-gray-50' : ($i === 2 ? 'from-orange-50 to-red-50' : ''));
            $bana = $r['kullanici_email'] === $email;
        ?>
            <div class="flex items-center p-4 border-b border-slate-100 last:border-0 <?= $i < 3 ? 'bg-gradient-to-r ' . $bg : '' ?> <?= $bana ? 'ring-2 ring-indigo-300' : '' ?>">
                <span class="text-2xl font-black w-12 text-center"><?= $emoji ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($r['kullanici_ad']) ?>&background=4f46e5&color=fff" class="w-10 h-10 rounded-full mx-3">
                <div class="flex-1 min-w-0">
                    <p class="font-bold truncate"><?= htmlspecialchars($r['kullanici_ad']) ?>
                        <?php if ($bana): ?><span class="ml-1 text-[9px] bg-indigo-500 text-white px-1.5 py-0.5 rounded font-black">SEN</span><?php endif; ?>
                    </p>
                    <p class="text-[11px] text-slate-400">
                        ✓ <?= $r['dogru_sayisi'] ?> · ✗ <?= $r['yanlis_sayisi'] ?> · ○ <?= $r['bos_sayisi'] ?>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-black <?= $r['puan'] >= 70 ? 'text-emerald-500' : ($r['puan'] >= 40 ? 'text-amber-500' : 'text-rose-500') ?>"><?= $r['puan'] ?></p>
                    <p class="text-[10px] text-slate-400">puan</p>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($siralama)): ?>
            <p class="p-12 text-center text-slate-400">Henüz katılan yok.</p>
        <?php endif; ?>
    </div>
</main>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
