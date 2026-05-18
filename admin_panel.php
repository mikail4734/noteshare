<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); exit;
}

// İstatistikler
$stats = [
    'toplam_kullanici' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'banli_kullanici'  => (int)$db->query("SELECT COUNT(*) FROM users WHERE durum=0")->fetchColumn(),
    'toplam_not'       => (int)$db->query("SELECT COUNT(*) FROM notes")->fetchColumn(),
    'toplam_begeni'    => (int)$db->query("SELECT COALESCE(SUM(likes),0) FROM notes")->fetchColumn(),
    'toplam_grup'      => (int)$db->query("SELECT COUNT(*) FROM gruplar")->fetchColumn(),
    'bekleyen_sikayet' => (int)$db->query("SELECT COUNT(*) FROM sikayetler WHERE durum='bekliyor'")->fetchColumn(),
    'okunmamis_bildirim' => (int)$db->query("SELECT COUNT(*) FROM bildirimler WHERE okundu=0")->fetchColumn(),
];

// Son aktiviteler
$sonNotlar = $db->query("SELECT id, title, author, created_at FROM notes ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$sonKullanicilar = $db->query("SELECT ad, email, rol, durum, kayit_tarihi FROM users ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$enCokBegenilen = $db->query("SELECT id, title, author, likes FROM notes WHERE likes > 0 ORDER BY likes DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Aktif duyuru
$aktifDuyuru = $db->query("SELECT * FROM site_duyurulari WHERE aktif=1 ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Duyuru ekle/sil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['duyuru_kaydet'])) {
        $db->query("UPDATE site_duyurulari SET aktif=0"); // eskisini kapat
        $db->prepare("INSERT INTO site_duyurulari (mesaj, tip) VALUES (?, ?)")
           ->execute([trim($_POST['mesaj']), $_POST['tip'] ?? 'info']);
        header("Location: admin_panel.php"); exit;
    }
    if (isset($_POST['duyuru_kapat'])) {
        $db->query("UPDATE site_duyurulari SET aktif=0");
        header("Location: admin_panel.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <title>Admin Panel | notewarehouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#fef2f2 0%,#ffffff 50%,#fff7ed 100%)}</style>
</head>
<body class="min-h-screen">

<nav class="bg-gradient-to-r from-rose-600 to-red-600 px-8 py-4 shadow-xl flex justify-between items-center sticky top-0 z-50">
    <div class="flex items-center text-white">
        <i class="fas fa-shield-alt text-2xl mr-3"></i>
        <h1 class="font-black text-2xl tracking-tight">Admin Komuta Merkezi</h1>
    </div>
    <a href="index.php" class="text-white/80 hover:text-white text-sm font-bold">← Anasayfa</a>
</nav>

<main class="container mx-auto px-6 py-10 max-w-7xl">

    <!-- İstatistikler -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <a href="kullanicilar.php" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-users text-3xl text-indigo-500"></i>
                <span class="text-3xl font-black text-slate-800"><?= $stats['toplam_kullanici'] ?></span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kullanıcı</p>
            <p class="text-[10px] text-rose-500 font-bold mt-1"><?= $stats['banli_kullanici'] ?> banlı</p>
        </a>

        <a href="notlar_admin.php" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-file-alt text-3xl text-emerald-500"></i>
                <span class="text-3xl font-black text-slate-800"><?= $stats['toplam_not'] ?></span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Not</p>
            <p class="text-[10px] text-emerald-500 font-bold mt-1"><?= $stats['toplam_begeni'] ?> beğeni</p>
        </a>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-users-cog text-3xl text-purple-500"></i>
                <span class="text-3xl font-black text-slate-800"><?= $stats['toplam_grup'] ?></span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Çalışma Grubu</p>
        </div>

        <a href="sikayetler_admin.php" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition relative">
            <?php if ($stats['bekleyen_sikayet'] > 0): ?>
                <span class="absolute -top-2 -right-2 bg-amber-500 text-white text-[10px] font-black w-7 h-7 rounded-full flex items-center justify-center border-2 border-white"><?= $stats['bekleyen_sikayet'] ?></span>
            <?php endif; ?>
            <div class="flex items-center justify-between mb-2">
                <i class="fas fa-inbox text-3xl text-amber-500"></i>
                <span class="text-3xl font-black text-slate-800"><?= $stats['bekleyen_sikayet'] ?></span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Bekleyen Mesaj</p>
        </a>
    </div>

    <!-- Hızlı Eylemler -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-10">
        <a href="kullanicilar.php" class="bg-rose-500 text-white p-4 rounded-2xl font-bold text-sm text-center hover:bg-rose-600 transition shadow-md"><i class="fas fa-users-cog block text-2xl mb-2"></i> Kullanıcılar</a>
        <a href="notlar_admin.php" class="bg-emerald-500 text-white p-4 rounded-2xl font-bold text-sm text-center hover:bg-emerald-600 transition shadow-md"><i class="fas fa-file-alt block text-2xl mb-2"></i> Notlar</a>
        <a href="sikayetler_admin.php" class="bg-amber-500 text-white p-4 rounded-2xl font-bold text-sm text-center hover:bg-amber-600 transition shadow-md"><i class="fas fa-inbox block text-2xl mb-2"></i> Mesajlar</a>
        <a href="bildirim_gonder.php" class="bg-indigo-600 text-white p-4 rounded-2xl font-bold text-sm text-center hover:bg-indigo-700 transition shadow-md"><i class="fas fa-paper-plane block text-2xl mb-2"></i> Bildirim</a>
        <a href="haberler_admin.php" class="bg-pink-600 text-white p-4 rounded-2xl font-bold text-sm text-center hover:bg-pink-700 transition shadow-md"><i class="fas fa-newspaper block text-2xl mb-2"></i> Haberler</a>
    </div>

    <!-- SİTE DUYURUSU -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <h2 class="font-black text-lg mb-4"><i class="fas fa-bullhorn text-amber-500 mr-2"></i> Site Duyurusu</h2>
        <?php if ($aktifDuyuru): ?>
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl mb-3">
                <p class="font-bold text-amber-800"><?= htmlspecialchars($aktifDuyuru['mesaj']) ?></p>
                <p class="text-xs text-amber-600 mt-1"><?= date('d M H:i', strtotime($aktifDuyuru['tarih'])) ?> · Tip: <?= $aktifDuyuru['tip'] ?></p>
            </div>
            <form method="POST"><button name="duyuru_kapat" class="bg-rose-100 text-rose-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-rose-200">✕ Duyuruyu Kapat</button></form>
        <?php endif; ?>
        <form method="POST" class="mt-4 space-y-3">
            <textarea name="mesaj" rows="2" required placeholder="Site genelinde gösterilecek duyuru mesajı..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-amber-400"></textarea>
            <div class="flex gap-2">
                <select name="tip" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none">
                    <option value="info">ℹ️ Bilgi</option>
                    <option value="warning">⚠️ Uyarı</option>
                    <option value="success">✅ İyi Haber</option>
                </select>
                <button name="duyuru_kaydet" class="bg-amber-500 text-white px-5 py-2 rounded-xl font-bold text-sm hover:bg-amber-600"><i class="fas fa-bullhorn mr-2"></i> Yayınla</button>
            </div>
        </form>
    </div>

    <!-- 3 Sütun: Son notlar / kullanıcılar / popüler -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-black mb-4"><i class="fas fa-file-alt text-emerald-500 mr-2"></i> Son Notlar</h3>
            <?php foreach ($sonNotlar as $n): ?>
                <a href="notlar.php?id=<?= $n['id'] ?>" class="block py-2 border-b border-slate-100 last:border-0 hover:text-indigo-600">
                    <p class="font-bold text-sm truncate"><?= htmlspecialchars($n['title']) ?></p>
                    <p class="text-[10px] text-slate-400"><?= htmlspecialchars($n['author']) ?> · <?= date('d M', strtotime($n['created_at'])) ?></p>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-black mb-4"><i class="fas fa-user-plus text-indigo-500 mr-2"></i> Yeni Üyeler</h3>
            <?php foreach ($sonKullanicilar as $u): ?>
                <div class="flex items-center py-2 border-b border-slate-100 last:border-0">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['ad'] ?? 'U') ?>&background=4f46e5&color=fff" class="w-8 h-8 rounded-full mr-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm truncate"><?= htmlspecialchars($u['ad'] ?? '—') ?></p>
                        <p class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($u['email']) ?></p>
                    </div>
                    <?php if ($u['rol'] === 'admin'): ?><span class="text-[9px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-black">ADMIN</span><?php endif; ?>
                    <?php if ($u['durum'] == 0): ?><span class="text-[9px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-black ml-1">BANLI</span><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-black mb-4"><i class="fas fa-fire text-rose-500 mr-2"></i> En Popüler</h3>
            <?php if (empty($enCokBegenilen)): ?>
                <p class="text-slate-400 text-sm">Henüz beğeni yok.</p>
            <?php else: ?>
                <?php foreach ($enCokBegenilen as $i => $n): ?>
                    <a href="notlar.php?id=<?= $n['id'] ?>" class="flex items-center py-2 border-b border-slate-100 last:border-0 hover:text-indigo-600">
                        <span class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-rose-500 text-white font-black text-xs flex items-center justify-center mr-3">#<?= $i+1 ?></span>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm truncate"><?= htmlspecialchars($n['title']) ?></p>
                            <p class="text-[10px] text-slate-400"><?= htmlspecialchars($n['author']) ?></p>
                        </div>
                        <span class="text-emerald-500 font-black text-sm"><i class="fas fa-heart text-xs mr-1"></i><?= $n['likes'] ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
