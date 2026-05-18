<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once 'baglan.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: giris.php");
    exit;
}

$userEmail = $_SESSION['user_email'];
$userName  = $_SESSION['user_name'] ?? 'Kullanıcı';

// Kullanıcının kendi notlarını getir
$sorgu = $db->prepare("SELECT * FROM notes WHERE kullanici_email = ? ORDER BY created_at DESC");
$sorgu->execute([$userEmail]);
$notlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// İstatistikler
$toplam = count($notlar);
$toplamBegeni = array_sum(array_column($notlar, 'likes'));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <title>Kişisel Çalışmalarım | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #F8FAFC; }
        .note-row { transition: all 0.2s ease; }
        .note-row:hover { background: rgba(79,70,229,0.05); }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="text-slate-800">

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-indigo-600"><i class="fas fa-chevron-left text-lg"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center text-slate-900">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-indigo-50 text-indigo-600 p-2.5 rounded-xl mr-3 border border-indigo-100"><i class="fas fa-folder-open"></i></span>
            Kişisel Çalışmalarım
        </h1>
    </div>
    <a href="notlar.php" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-md">
        <i class="fas fa-plus mr-2"></i> Yeni Not Ekle
    </a>
</nav>

<main class="container mx-auto px-6 py-10 max-w-6xl">

    <!-- İstatistikler -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Toplam Notum</p>
            <p class="text-4xl font-black text-indigo-600 mt-2"><?= $toplam ?></p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Toplam Beğeni</p>
            <p class="text-4xl font-black text-emerald-500 mt-2"><?= $toplamBegeni ?></p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Yazar</p>
            <p class="text-2xl font-bold text-slate-700 mt-2 truncate">@<?= htmlspecialchars($userName) ?></p>
        </div>
    </div>

    <!-- Notlar -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden border border-slate-100">
        <div class="px-6 py-4 border-b bg-slate-50/50 flex items-center justify-between">
            <h2 class="font-bold text-lg">Notlarım</h2>
            <span class="text-xs text-slate-500 font-medium"><?= $toplam ?> kayıt</span>
        </div>

        <?php if ($toplam === 0): ?>
            <div class="p-16 text-center">
                <div class="w-20 h-20 bg-indigo-50 text-indigo-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Henüz not yok</h3>
                <p class="text-slate-400 text-sm mb-6">İlk notunu oluşturarak başla.</p>
                <a href="notlar.php" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold inline-block">
                    <i class="fas fa-plus mr-2"></i> İlk Notumu Oluştur
                </a>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($notlar as $not): ?>
                    <div class="note-row px-6 py-5 grid grid-cols-12 items-center gap-4">
                        <div class="col-span-5 cursor-pointer" onclick="window.location.href='notlar.php?id=<?= $not['id'] ?>'">
                            <h4 class="font-bold text-slate-900 truncate"><?= htmlspecialchars($not['title']) ?></h4>
                            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-1">
                                <?= htmlspecialchars($not['category'] ?: 'Genel') ?>
                                <?php if ($not['edu_level']): ?>
                                    · <?= htmlspecialchars($not['edu_level']) ?>
                                <?php endif; ?>
                                <?php if ($not['subject']): ?>
                                    · <?= htmlspecialchars($not['subject']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-span-3 text-xs text-slate-500 font-medium">
                            <?= date('d M Y, H:i', strtotime($not['created_at'])) ?>
                        </div>
                        <div class="col-span-2 flex items-center space-x-3 text-xs">
                            <span class="text-emerald-500 font-bold"><i class="fas fa-thumbs-up mr-1"></i> <?= $not['likes'] ?></span>
                            <span class="text-rose-400 font-bold"><i class="fas fa-thumbs-down mr-1"></i> <?= $not['dislikes'] ?></span>
                        </div>
                        <div class="col-span-2 text-right space-x-2">
                            <a href="notlar.php?id=<?= $not['id'] ?>" class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-3 py-2 rounded-lg hover:bg-indigo-600 hover:text-white inline-block">
                                <i class="fas fa-edit mr-1"></i> Düzenle
                            </a>
                            <button onclick="notSil(<?= $not['id'] ?>)" class="text-[10px] font-bold text-rose-600 bg-rose-50 px-3 py-2 rounded-lg hover:bg-rose-600 hover:text-white">
                                <i class="fas fa-trash mr-1"></i> Sil
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function notSil(id) {
    if (!confirm("Bu notu silmek istediğine emin misin? Bu işlem geri alınamaz!")) return;

    fetch('islem.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ islem: 'not_sil', note_id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { location.reload(); }
        else { alert("Hata: " + (data.error || "silinemedi")); }
    });
}
</script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
