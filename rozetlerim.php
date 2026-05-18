<?php
session_start();
require_once 'baglan.php';
require_once 'helpers.php';

if (!isset($_SESSION['user_email'])) { header("Location: giris.php"); exit; }
$email = $_SESSION['user_email'];

// Kullanıcı bilgileri
$u = $db->prepare("SELECT * FROM users WHERE email = ?");
$u->execute([$email]);
$kullanici = $u->fetch(PDO::FETCH_ASSOC);

// Kazanılan rozetler
$r = $db->prepare("SELECT rozet_kod, tarih FROM rozetler WHERE kullanici_email = ? ORDER BY tarih DESC");
$r->execute([$email]);
$kazanilanlar = [];
foreach ($r->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $kazanilanlar[$row['rozet_kod']] = $row['tarih'];
}

$tumRozetler = rozetListesi();
$xp = (int)$kullanici['xp'];
$seviye = (int)$kullanici['seviye'];
$streak = (int)$kullanici['streak'];
$mevcutSeviyeXp = seviyeToXp($seviye);
$sonrakiSeviyeXp = seviyeToXp($seviye + 1);
$ilerleme = $sonrakiSeviyeXp > $mevcutSeviyeXp ? round((($xp - $mevcutSeviyeXp) / ($sonrakiSeviyeXp - $mevcutSeviyeXp)) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Rozetlerim & Seviyem | notewarehouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
</head>
<body>

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-amber-500"><i class="fas fa-chevron-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-amber-50 text-amber-500 p-2.5 rounded-xl mr-3 border border-amber-100"><i class="fas fa-medal"></i></span>
            Rozetlerim & Seviyem
        </h1>
    </div>
    <a href="liderlik.php" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-indigo-700">
        <i class="fas fa-trophy mr-2"></i> Liderlik Tablosu
    </a>
</nav>

<main class="container mx-auto px-6 py-10 max-w-5xl">

    <!-- SEVİYE KARTI -->
    <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-3xl p-8 shadow-xl text-white mb-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <p class="text-xs font-bold text-indigo-100 uppercase tracking-widest mb-2">Seviye</p>
                <h2 class="text-6xl font-black mb-1"><?= $seviye ?></h2>
                <p class="text-indigo-100">⭐ <?= $xp ?> XP toplam</p>
            </div>

            <div class="flex-1 w-full max-w-md">
                <div class="flex justify-between text-xs font-bold text-indigo-100 mb-2">
                    <span>Lv <?= $seviye ?></span>
                    <span><?= sonrakiSeviyeIcinXp($xp) ?> XP kaldı</span>
                    <span>Lv <?= $seviye + 1 ?></span>
                </div>
                <div class="h-4 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full transition-all" style="width: <?= $ilerleme ?>%"></div>
                </div>
            </div>

            <div class="text-center">
                <p class="text-xs font-bold text-indigo-100 uppercase tracking-widest mb-2">Streak</p>
                <p class="text-5xl font-black">🔥 <?= $streak ?></p>
                <p class="text-indigo-100 text-xs">gün üst üste</p>
            </div>
        </div>
    </div>

    <!-- ROZETLER -->
    <h3 class="text-xl font-black text-slate-800 mb-4">🏆 Rozetler (<?= count($kazanilanlar) ?>/<?= count($tumRozetler) ?>)</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php foreach ($tumRozetler as $kod => $r):
            $kazandi = isset($kazanilanlar[$kod]);
        ?>
            <div class="bg-white rounded-2xl p-5 text-center shadow-sm border <?= $kazandi ? 'border-amber-200 ring-2 ring-amber-100' : 'border-slate-100 opacity-50' ?> hover:scale-105 transition">
                <div class="text-5xl mb-2 <?= $kazandi ? '' : 'grayscale' ?>"><?= $r['ikon'] ?></div>
                <h4 class="font-black text-sm text-slate-800 mb-1"><?= htmlspecialchars($r['ad']) ?></h4>
                <p class="text-[10px] text-slate-500 mb-2"><?= htmlspecialchars($r['aciklama']) ?></p>
                <?php if ($kazandi): ?>
                    <span class="text-[9px] bg-amber-100 text-amber-700 font-black px-2 py-0.5 rounded uppercase">✓ Kazanıldı</span>
                    <p class="text-[9px] text-slate-400 mt-1"><?= date('d M Y', strtotime($kazanilanlar[$kod])) ?></p>
                <?php else: ?>
                    <span class="text-[9px] bg-slate-100 text-slate-500 font-black px-2 py-0.5 rounded uppercase">🔒 Kilitli</span>
                    <p class="text-[9px] text-indigo-500 font-bold mt-1">+<?= $r['xp'] ?> XP</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
