<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['user_email'])) { header("Location: giris.php"); exit; }
$userEmail = $_SESSION['user_email'];

$grupId = intval($_GET['id'] ?? 0);
if (!$grupId) { header("Location: gruplarim.php"); exit; }

// Grup ve üyelik kontrolü
$gs = $db->prepare("SELECT g.*, (SELECT COUNT(*) FROM grup_uyeleri WHERE grup_id = g.id) AS uye_sayisi
                    FROM gruplar g
                    JOIN grup_uyeleri gu ON gu.grup_id = g.id
                    WHERE g.id = ? AND gu.kullanici_email = ?");
$gs->execute([$grupId, $userEmail]);
$grup = $gs->fetch(PDO::FETCH_ASSOC);

if (!$grup) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Bu gruba erişim yetkin yok. <a href='gruplarim.php'>← Gruplarım</a></div>");
}

// Grup üyeleri
$u = $db->prepare("SELECT u.ad, u.email FROM grup_uyeleri gu JOIN users u ON gu.kullanici_email = u.email WHERE gu.grup_id = ?");
$u->execute([$grupId]);
$uyeler = $u->fetchAll(PDO::FETCH_ASSOC);

// Grup notları
$n = $db->prepare("SELECT * FROM notes WHERE grup_id = ? ORDER BY created_at DESC");
$n->execute([$grupId]);
$notlar = $n->fetchAll(PDO::FETCH_ASSOC);

$katIkon = [
    'Konu Anlatımı' => '📖', 'Soru Çözümü' => '✏️',
    'Özet' => '📝', 'Kod' => '💻', 'Formül' => '🧮', 'Deney' => '🔬'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($grup['grup_adi']) ?> - Grup Notları</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body>

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="gruplarim.php" class="text-slate-400 hover:text-purple-600"><i class="fas fa-chevron-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-purple-50 text-purple-600 p-2.5 rounded-xl mr-3 border border-purple-100"><i class="fas fa-folder-open"></i></span>
            <?= htmlspecialchars($grup['grup_adi']) ?>
            <span class="ml-3 text-xs text-slate-400 font-normal"><?= $grup['uye_sayisi'] ?> üye · <?= count($notlar) ?> not</span>
        </h1>
    </div>
    <a href="notlar.php?grup_id=<?= $grupId ?>" class="bg-purple-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-purple-700 shadow-md">
        <i class="fas fa-plus mr-2"></i> Grup Notu Oluştur
    </a>
</nav>

<main class="container mx-auto px-6 py-8 max-w-6xl">

    <!-- Grup üyeleri -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 mb-6">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Üyeler</h3>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($uyeler as $uye): ?>
                <span class="inline-flex items-center bg-slate-50 rounded-full pl-1 pr-3 py-1 text-xs border border-slate-200">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($uye['ad']) ?>&background=8b5cf6&color=fff" class="w-6 h-6 rounded-full mr-2">
                    <?= htmlspecialchars($uye['ad']) ?>
                    <?php if ($uye['email'] === $grup['olusturan_email']): ?>
                        <span class="ml-2 bg-amber-100 text-amber-700 text-[8px] font-black px-1.5 py-0.5 rounded uppercase">Kurucu</span>
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php if ($grup['aciklama']): ?>
            <p class="text-sm text-slate-500 mt-3 italic">"<?= htmlspecialchars($grup['aciklama']) ?>"</p>
        <?php endif; ?>
    </div>

    <!-- Notlar -->
    <?php if (empty($notlar)): ?>
        <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100">
            <div class="w-20 h-20 bg-purple-50 text-purple-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-700 mb-2">Henüz grup notu yok</h3>
            <p class="text-slate-400 text-sm mb-6">İlk ortak notu oluştur, grup arkadaşların da düzenleyebilsin.</p>
            <a href="notlar.php?grup_id=<?= $grupId ?>" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-xl font-bold">
                <i class="fas fa-plus mr-2"></i> İlk Notu Oluştur
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($notlar as $not):
                $ikon = $katIkon[$not['category']] ?? '📄';
            ?>
                <a href="notlar.php?id=<?= $not['id'] ?>" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition group block">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-2xl border border-purple-100">
                            <?= $ikon ?>
                        </div>
                        <span class="text-[10px] bg-purple-100 text-purple-700 font-black px-2 py-1 rounded uppercase"><?= htmlspecialchars($not['category'] ?: 'Genel') ?></span>
                    </div>
                    <h4 class="font-extrabold text-slate-800 text-lg mb-1 line-clamp-2 leading-tight group-hover:text-purple-600 transition">
                        <?= htmlspecialchars($not['title']) ?>
                    </h4>
                    <p class="text-xs text-slate-400 font-medium mb-4">
                        <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($not['author']) ?> ·
                        <?= date('d M Y', strtotime($not['created_at'])) ?>
                    </p>
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
                        <span class="text-emerald-500 font-bold"><i class="fas fa-thumbs-up mr-1"></i><?= $not['likes'] ?></span>
                        <span class="text-slate-400"><i class="far fa-eye mr-1"></i><?= $not['goruntulenme'] ?? 0 ?> görüntüleme</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
