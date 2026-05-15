<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['user_email'])) { header("Location: giris.php"); exit; }
$email = $_SESSION['user_email'];
$rol = $_SESSION['rol'] ?? 'user';

// Sınavları al
$simdi = date('Y-m-d H:i:s');
$sinavlar = $db->query("
    SELECT cs.*,
        CASE
            WHEN '$simdi' < cs.baslangic THEN 'beklemede'
            WHEN '$simdi' BETWEEN cs.baslangic AND cs.bitis THEN 'aktif'
            ELSE 'bitti'
        END AS canli_durum,
        (SELECT COUNT(*) FROM canli_sinav_katilim WHERE sinav_id = cs.id) AS katilimci_sayisi
    FROM canli_sinavlar cs
    ORDER BY cs.baslangic DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Canlı Sınavlar | NoteShare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
</head>
<body>

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-rose-500"><i class="fas fa-chevron-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <span class="bg-rose-50 text-rose-500 p-2.5 rounded-xl mr-3 border border-rose-100"><i class="fas fa-trophy"></i></span>
            🎯 Canlı Sınavlar
        </h1>
    </div>
    <?php if ($rol === 'admin'): ?>
        <a href="canli_sinav_olustur.php" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-rose-700 shadow-md">
            <i class="fas fa-plus mr-2"></i> Sınav Oluştur
        </a>
    <?php endif; ?>
</nav>

<main class="container mx-auto px-6 py-10 max-w-4xl">

    <?php if (empty($sinavlar)): ?>
        <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100">
            <div class="text-5xl mb-3">🎯</div>
            <h3 class="text-xl font-bold text-slate-700">Henüz canlı sınav yok</h3>
            <p class="text-slate-400 text-sm mt-2">Admin'in yeni bir sınav planlamasını bekle.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($sinavlar as $s):
                $renk = $s['canli_durum'] === 'aktif' ? 'emerald' : ($s['canli_durum'] === 'beklemede' ? 'amber' : 'slate');
                $etiketTxt = $s['canli_durum'] === 'aktif' ? '🔴 CANLI' : ($s['canli_durum'] === 'beklemede' ? '⏰ YAKLAŞIYOR' : '✓ BİTTİ');
                $kKontrol = $db->prepare("SELECT puan FROM canli_sinav_katilim WHERE sinav_id = ? AND kullanici_email = ?");
                $kKontrol->execute([$s['id'], $email]);
                $katildi = $kKontrol->fetch(PDO::FETCH_ASSOC);
            ?>
                <div class="bg-white rounded-3xl shadow-sm border border-<?= $renk ?>-200 p-6 <?= $s['canli_durum'] === 'aktif' ? 'ring-2 ring-emerald-200 animate-pulse-slow' : '' ?>">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-<?= $renk ?>-100 text-<?= $renk ?>-700 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-wider"><?= $etiketTxt ?></span>
                                <span class="text-[10px] text-slate-400"><?= $s['katilimci_sayisi'] ?> katılımcı</span>
                            </div>
                            <h3 class="font-black text-xl text-slate-800"><?= htmlspecialchars($s['baslik']) ?></h3>
                            <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($s['aciklama']) ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mb-4 text-center">
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Başlangıç</p>
                            <p class="font-bold text-sm"><?= date('d M H:i', strtotime($s['baslangic'])) ?></p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Bitiş</p>
                            <p class="font-bold text-sm"><?= date('d M H:i', strtotime($s['bitis'])) ?></p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Süre</p>
                            <p class="font-bold text-sm"><?= $s['sure_dakika'] ?> dk</p>
                        </div>
                    </div>

                    <?php if ($katildi): ?>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mb-3 flex justify-between items-center">
                            <p class="text-sm text-emerald-700 font-bold">✓ Katıldın! Puanın: <?= $katildi['puan'] ?></p>
                            <a href="canli_sinav_sonuc.php?id=<?= $s['id'] ?>" class="text-emerald-600 font-bold text-xs hover:underline">Sıralamayı Gör →</a>
                        </div>
                    <?php elseif ($s['canli_durum'] === 'aktif'): ?>
                        <a href="canli_sinav.php?id=<?= $s['id'] ?>" class="block w-full bg-emerald-500 text-white text-center py-3 rounded-xl font-black uppercase tracking-widest hover:bg-emerald-600 transition shadow-md">
                            <i class="fas fa-play mr-2"></i> Şimdi Katıl
                        </a>
                    <?php elseif ($s['canli_durum'] === 'beklemede'): ?>
                        <div class="text-center py-3 bg-amber-50 text-amber-700 rounded-xl font-bold text-sm">
                            <i class="fas fa-clock mr-2"></i> <span id="countdown-<?= $s['id'] ?>" data-bas="<?= strtotime($s['baslangic']) ?>"></span>
                        </div>
                    <?php else: ?>
                        <a href="canli_sinav_sonuc.php?id=<?= $s['id'] ?>" class="block w-full bg-slate-100 text-slate-600 text-center py-3 rounded-xl font-bold text-sm hover:bg-slate-200">
                            <i class="fas fa-chart-bar mr-2"></i> Sonuçları Gör
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
// Geri sayım
document.querySelectorAll('[id^=countdown-]').forEach(el => {
    const bas = parseInt(el.dataset.bas) * 1000;
    function tick() {
        const fark = bas - Date.now();
        if (fark <= 0) { location.reload(); return; }
        const s = Math.floor(fark / 1000);
        const gun = Math.floor(s / 86400), saat = Math.floor((s % 86400) / 3600);
        const dk = Math.floor((s % 3600) / 60), sn = s % 60;
        el.innerText = (gun > 0 ? gun + ' gün ' : '') + (saat > 0 ? saat + ' saat ' : '') + dk + ' dk ' + sn + ' sn sonra başlıyor';
    }
    tick(); setInterval(tick, 1000);
});
</script>
</body>
</html>
