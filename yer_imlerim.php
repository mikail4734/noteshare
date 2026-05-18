<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['user_email'])) { header("Location: giris.php"); exit; }
$email = $_SESSION['user_email'];

$s = $db->prepare("SELECT n.*, yi.tarih AS imleme_tarihi
                   FROM yer_imleri yi
                   JOIN notes n ON yi.note_id = n.id
                   WHERE yi.kullanici_email = ?
                   ORDER BY yi.tarih DESC");
$s->execute([$email]);
$notlar = $s->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <title>Yer İmlerim | notewarehouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body>

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-amber-500"><i class="fas fa-chevron-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-amber-50 text-amber-500 p-2.5 rounded-xl mr-3 border border-amber-100"><i class="fas fa-bookmark"></i></span>
            Yer İmlerim · Sonra Oku
            <span class="ml-3 text-xs text-slate-400 font-normal">(<?= count($notlar) ?>)</span>
        </h1>
    </div>
</nav>

<main class="container mx-auto px-6 py-10 max-w-5xl">
    <?php if (empty($notlar)): ?>
        <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100">
            <div class="text-5xl mb-3">🔖</div>
            <h3 class="text-xl font-bold text-slate-700">Henüz yer imi yok</h3>
            <p class="text-slate-400 text-sm mt-2">Bir notu açıp 🔖 simgesine basarak buraya ekleyebilirsin.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($notlar as $n): ?>
                <a href="notlar.php?id=<?= $n['id'] ?>" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition group block">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-[10px] bg-amber-100 text-amber-700 font-black px-2 py-1 rounded uppercase">📌 İmlendi</span>
                        <span class="text-[10px] text-slate-400"><?= date('d M', strtotime($n['imleme_tarihi'])) ?></span>
                    </div>
                    <h4 class="font-extrabold text-slate-800 text-lg mb-1 line-clamp-2 group-hover:text-amber-600 transition"><?= htmlspecialchars($n['title']) ?></h4>
                    <p class="text-xs text-slate-400 font-medium mb-3">
                        @<?= htmlspecialchars($n['author']) ?> · <?= htmlspecialchars($n['category']) ?>
                    </p>
                    <p class="text-sm text-slate-500 line-clamp-2"><?= htmlspecialchars(strip_tags($n['content'])) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
