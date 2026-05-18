<?php
session_start();
require_once 'baglan.php';

$mod = $_GET['mod'] ?? 'xp'; // xp, streak, takipci

if ($mod === 'streak') {
    $s = $db->query("SELECT ad, email, streak, xp, seviye FROM users WHERE durum = 1 ORDER BY streak DESC, xp DESC LIMIT 50");
    $baslik = "🔥 En Uzun Streak'ler";
} elseif ($mod === 'takipci') {
    $s = $db->query("SELECT u.ad, u.email, u.xp, u.seviye, u.streak,
                            (SELECT COUNT(*) FROM takipler t WHERE t.takip_edilen = u.email) AS takipci
                     FROM users u
                     WHERE u.durum = 1
                     ORDER BY takipci DESC, u.xp DESC LIMIT 50");
    $baslik = "⭐ En Çok Takipçi";
} else {
    $s = $db->query("SELECT ad, email, xp, seviye, streak FROM users WHERE durum = 1 ORDER BY xp DESC LIMIT 50");
    $baslik = "👑 En Yüksek XP";
}
$siralama = $s->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <title>Liderlik Tablosu | notewarehouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
</head>
<body>

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-indigo-600"><i class="fas fa-chevron-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-yellow-50 text-yellow-500 p-2.5 rounded-xl mr-3 border border-yellow-100"><i class="fas fa-trophy"></i></span>
            <?= $baslik ?>
        </h1>
    </div>
    <a href="rozetlerim.php" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-indigo-700">
        <i class="fas fa-medal mr-2"></i> Rozetlerim
    </a>
</nav>

<main class="container mx-auto px-6 py-10 max-w-3xl">

    <!-- Filtre -->
    <div class="flex gap-2 mb-6">
        <a href="?mod=xp" class="<?= $mod === 'xp' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600' ?> px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm border border-slate-100">👑 XP</a>
        <a href="?mod=streak" class="<?= $mod === 'streak' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600' ?> px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm border border-slate-100">🔥 Streak</a>
        <a href="?mod=takipci" class="<?= $mod === 'takipci' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600' ?> px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm border border-slate-100">⭐ Takipçi</a>
    </div>

    <!-- Liderlik tablosu -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <?php foreach ($siralama as $i => $u):
            $emoji = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#'.($i+1)));
            $bgRenk = $i === 0 ? 'from-yellow-50 to-amber-50 border-amber-200' :
                      ($i === 1 ? 'from-slate-50 to-gray-50 border-slate-200' :
                      ($i === 2 ? 'from-orange-50 to-red-50 border-orange-200' : ''));
            $kendiM = ($_SESSION['user_email'] ?? '') === $u['email'];
        ?>
            <div class="flex items-center p-4 border-b border-slate-100 last:border-0 <?= $i < 3 ? 'bg-gradient-to-r ' . $bgRenk : '' ?> <?= $kendiM ? 'ring-2 ring-indigo-300' : '' ?>">
                <span class="text-2xl font-black w-12 text-center"><?= $emoji ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['ad']) ?>&background=4f46e5&color=fff" class="w-10 h-10 rounded-full mx-3">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 truncate">
                        <?= htmlspecialchars($u['ad']) ?>
                        <?php if ($kendiM): ?><span class="ml-1 text-[9px] bg-indigo-500 text-white px-1.5 py-0.5 rounded font-black uppercase">SEN</span><?php endif; ?>
                    </p>
                    <p class="text-[11px] text-slate-400">Seviye <?= $u['seviye'] ?> · <?= $u['streak'] ?> gün streak</p>
                </div>
                <div class="text-right">
                    <?php if ($mod === 'streak'): ?>
                        <p class="text-2xl font-black text-orange-500">🔥 <?= $u['streak'] ?></p>
                        <p class="text-[10px] text-slate-400">gün</p>
                    <?php elseif ($mod === 'takipci'): ?>
                        <p class="text-2xl font-black text-purple-500">⭐ <?= $u['takipci'] ?></p>
                        <p class="text-[10px] text-slate-400">takipçi</p>
                    <?php else: ?>
                        <p class="text-2xl font-black text-indigo-600"><?= number_format($u['xp']) ?></p>
                        <p class="text-[10px] text-slate-400">XP</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
