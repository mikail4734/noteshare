<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: giris.php");
    exit;
}

$email = $_SESSION['user_email'];
$sorgu = $db->prepare("SELECT * FROM bildirimler WHERE kullanici_email = ? ORDER BY tarih DESC LIMIT 50");
$sorgu->execute([$email]);
$bildirimler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Hepsini okundu olarak işaretle (sayfayı açtıysa görmüş demektir)
$db->prepare("UPDATE bildirimler SET okundu = 1 WHERE kullanici_email = ?")->execute([$email]);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bildirimlerim | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen">

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-indigo-600"><i class="fas fa-chevron-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-indigo-50 text-indigo-600 p-2.5 rounded-xl mr-3 border border-indigo-100"><i class="fas fa-bell"></i></span>
            Bildirimlerim
        </h1>
    </div>
</nav>

<main class="container mx-auto px-6 py-10 max-w-3xl">
    <?php if (empty($bildirimler)): ?>
        <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100">
            <div class="w-20 h-20 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                <i class="far fa-bell-slash"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-700">Henüz bildirim yok</h3>
            <p class="text-slate-400 text-sm mt-2">Yeni bildirimler burada görünecek.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($bildirimler as $b): ?>
                <div class="bg-white rounded-2xl p-6 shadow-sm border <?= $b['okundu'] ? 'border-slate-100' : 'border-indigo-300 bg-indigo-50/30' ?>">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-slate-900 text-lg"><?= htmlspecialchars($b['baslik'] ?: 'Bildirim') ?></h3>
                        <span class="text-[10px] text-slate-400 font-bold uppercase"><?= date('d M H:i', strtotime($b['tarih'])) ?></span>
                    </div>
                    <p class="text-slate-600 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($b['mesaj']) ?></p>
                    <p class="text-[10px] text-slate-400 mt-4 font-bold">
                        <i class="fas fa-user-shield mr-1"></i> Gönderen: <?= htmlspecialchars($b['gonderen'] ?: 'Sistem') ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
