<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif; color:red;'>Yetkin yok!</div>");
}

// ADMIN CEVABI GÖNDER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cevapla'])) {
    $sikayet_id = intval($_POST['sikayet_id']);
    $cevap = trim($_POST['cevap']);

    if (!empty($cevap)) {
        // Şikayetin kullanıcısını bul
        $bul = $db->prepare("SELECT kullanici_email, konu FROM sikayetler WHERE id = ?");
        $bul->execute([$sikayet_id]);
        $sik = $bul->fetch(PDO::FETCH_ASSOC);

        if ($sik) {
            // Şikayete cevap kaydet
            $g = $db->prepare("UPDATE sikayetler SET cevap = ?, cevaplayan = ?, cevap_tarihi = NOW(), durum = 'cozuldu' WHERE id = ?");
            $g->execute([$cevap, $_SESSION['user_name'] ?? 'Admin', $sikayet_id]);

            // Kullanıcıya bildirim oluştur
            $bildir = $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen, tip) VALUES (?, ?, ?, ?, 'sikayet_cevap')");
            $bildir->execute([
                $sik['kullanici_email'],
                'Şikayetinize Cevap Geldi: ' . $sik['konu'],
                "Konu: " . $sik['konu'] . "\n\n--- Yöneticinin Cevabı ---\n" . $cevap,
                $_SESSION['user_name'] ?? 'Admin'
            ]);
        }
    }
    header("Location: sikayetler_admin.php");
    exit;
}

// Şikayet sil
if (isset($_GET['sil'])) {
    $db->prepare("DELETE FROM sikayetler WHERE id = ?")->execute([intval($_GET['sil'])]);
    header("Location: sikayetler_admin.php");
    exit;
}

$sikayetler = $db->query("SELECT * FROM sikayetler ORDER BY durum ASC, tarih DESC")->fetchAll(PDO::FETCH_ASSOC);
$bekleyen = count(array_filter($sikayetler, fn($s) => $s['durum'] === 'bekliyor'));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <title>Şikayet Yönetimi | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
</head>
<body class="text-slate-800">

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-red-500"><i class="fas fa-arrow-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-red-50 text-red-500 p-2.5 rounded-xl mr-3 border border-red-100"><i class="fas fa-inbox"></i></span>
            Şikayet Yönetimi
            <?php if ($bekleyen > 0): ?>
                <span class="ml-3 bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full"><?= $bekleyen ?> bekliyor</span>
            <?php endif; ?>
        </h1>
    </div>
    <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg uppercase">Admin</span>
</nav>

<main class="container mx-auto px-6 py-8 max-w-4xl space-y-5">

    <?php if (empty($sikayetler)): ?>
        <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100">
            <div class="text-5xl mb-3">🎉</div>
            <h3 class="text-xl font-bold text-slate-700">Hiç şikayet yok!</h3>
            <p class="text-slate-400 text-sm mt-2">Tüm kullanıcılar memnun görünüyor.</p>
        </div>
    <?php else: ?>

        <?php foreach ($sikayetler as $s): ?>
            <div class="bg-white rounded-2xl shadow-sm border <?= $s['durum'] === 'bekliyor' ? 'border-amber-200 ring-2 ring-amber-100' : 'border-slate-100' ?> overflow-hidden">
                <!-- Üst kısım: gönderici bilgisi -->
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-900"><?= htmlspecialchars($s['konu']) ?></h3>
                            <?php if ($s['durum'] === 'bekliyor'): ?>
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full"><i class="fas fa-clock mr-1"></i>BEKLİYOR</span>
                            <?php else: ?>
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full"><i class="fas fa-check mr-1"></i>ÇÖZÜLDÜ</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($s['kullanici_email']) ?>
                            · <i class="fas fa-clock mr-1 ml-2"></i> <?= date('d M H:i', strtotime($s['tarih'])) ?>
                        </p>
                    </div>
                    <a href="?sil=<?= $s['id'] ?>" onclick="return confirm('Şikayeti sil?')" class="text-rose-400 hover:text-rose-600 text-sm"><i class="fas fa-trash"></i></a>
                </div>

                <!-- Mesaj -->
                <div class="px-6 py-5 bg-slate-50/30">
                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($s['mesaj']) ?></p>
                </div>

                <!-- Cevap varsa göster -->
                <?php if (!empty($s['cevap'])): ?>
                    <div class="px-6 py-5 border-t border-emerald-100 bg-emerald-50">
                        <div class="flex items-center mb-2">
                            <span class="bg-emerald-500 text-white text-[10px] font-black px-2 py-1 rounded-md uppercase tracking-wider mr-2">CEVAP</span>
                            <span class="text-xs text-emerald-700 font-bold"><?= htmlspecialchars($s['cevaplayan']) ?> · <?= date('d M H:i', strtotime($s['cevap_tarihi'])) ?></span>
                        </div>
                        <p class="text-sm text-emerald-900 whitespace-pre-line"><?= htmlspecialchars($s['cevap']) ?></p>
                    </div>
                <?php else: ?>
                    <!-- Cevap formu -->
                    <form method="POST" class="px-6 py-5 border-t border-slate-100 bg-indigo-50/30">
                        <input type="hidden" name="sikayet_id" value="<?= $s['id'] ?>">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2 block">Cevabını yaz</label>
                        <textarea name="cevap" rows="3" required placeholder="Kullanıcıya gönderilecek cevap..." class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-indigo-400 mb-3"></textarea>
                        <button type="submit" name="cevapla" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-indigo-700">
                            <i class="fas fa-paper-plane mr-2"></i> Cevabı Gönder + Bildirim Bırak
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
</body>
</html>
