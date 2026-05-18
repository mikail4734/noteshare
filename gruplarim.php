<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['user_email'])) { header("Location: giris.php"); exit; }
$userEmail = $_SESSION['user_email'];
$userName = $_SESSION['user_name'] ?? 'Kullanıcı';

$mesaj = '';
$mesajTip = 'info';

// Grup Oluştur
if (isset($_POST['grup_kur'])) {
    $grup_adi = trim($_POST['grup_adi']);
    $aciklama = trim($_POST['aciklama'] ?? '');
    if (!empty($grup_adi)) {
        $db->beginTransaction();
        $kur = $db->prepare("INSERT INTO gruplar (grup_adi, olusturan_email, aciklama) VALUES (?, ?, ?)");
        $kur->execute([$grup_adi, $userEmail, $aciklama]);
        $yeni_id = $db->lastInsertId();
        $db->prepare("INSERT INTO grup_uyeleri (grup_id, kullanici_email) VALUES (?, ?)")->execute([$yeni_id, $userEmail]);
        $db->commit();
        $mesaj = "🎉 Grup oluşturuldu!";
        $mesajTip = 'ok';
    }
}

// Üye Davet Et (e-posta ile)
if (isset($_POST['uye_davet'])) {
    $gId = intval($_POST['grup_id']);
    $uEmail = strtolower(trim($_POST['uye_email']));

    // Kullanıcı var mı?
    $kontrol = $db->prepare("SELECT ad FROM users WHERE email = ?");
    $kontrol->execute([$uEmail]);
    $hedef = $kontrol->fetch(PDO::FETCH_ASSOC);

    // Grup var mı + ben üye miyim?
    $sahip = $db->prepare("SELECT 1 FROM grup_uyeleri WHERE grup_id = ? AND kullanici_email = ?");
    $sahip->execute([$gId, $userEmail]);

    if (!$hedef) {
        $mesaj = "❌ Bu e-posta ile kayıtlı kullanıcı yok.";
        $mesajTip = 'err';
    } elseif ($uEmail === $userEmail) {
        $mesaj = "❌ Kendine davet gönderemezsin.";
        $mesajTip = 'err';
    } elseif (!$sahip->rowCount()) {
        $mesaj = "❌ Bu grubun üyesi değilsin.";
        $mesajTip = 'err';
    } else {
        // Zaten üye mi?
        $uyeMi = $db->prepare("SELECT 1 FROM grup_uyeleri WHERE grup_id = ? AND kullanici_email = ?");
        $uyeMi->execute([$gId, $uEmail]);
        if ($uyeMi->rowCount()) {
            $mesaj = "ℹ️ Bu kullanıcı zaten üye.";
            $mesajTip = 'info';
        } else {
            // Davet kaydı
            try {
                $db->prepare("INSERT INTO grup_davetleri (grup_id, davet_eden, davet_edilen) VALUES (?, ?, ?)")
                   ->execute([$gId, $userEmail, $uEmail]);

                // Bildirim oluştur
                $grupAdi = $db->prepare("SELECT grup_adi FROM gruplar WHERE id = ?");
                $grupAdi->execute([$gId]);
                $gAdi = $grupAdi->fetchColumn();

                $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen, tip) VALUES (?, ?, ?, ?, 'grup_davet')")
                   ->execute([
                       $uEmail,
                       "📨 Grup Daveti: $gAdi",
                       "$userName seni '$gAdi' grubuna davet etti. Kabul etmek için 'Gruplarım' sayfasına git.",
                       $userName
                   ]);
                $mesaj = "✅ Davet gönderildi (" . htmlspecialchars($hedef['ad']) . ")";
                $mesajTip = 'ok';
            } catch (PDOException $e) {
                $mesaj = "❌ Daha önce davet edilmiş olabilir.";
                $mesajTip = 'err';
            }
        }
    }
}

// Daveti Kabul Et
if (isset($_GET['kabul'])) {
    $davetId = intval($_GET['kabul']);
    $d = $db->prepare("SELECT * FROM grup_davetleri WHERE id = ? AND davet_edilen = ? AND durum='bekliyor'");
    $d->execute([$davetId, $userEmail]);
    $davet = $d->fetch(PDO::FETCH_ASSOC);
    if ($davet) {
        $db->beginTransaction();
        $db->prepare("INSERT IGNORE INTO grup_uyeleri (grup_id, kullanici_email) VALUES (?, ?)")
           ->execute([$davet['grup_id'], $userEmail]);
        $db->prepare("UPDATE grup_davetleri SET durum='kabul' WHERE id = ?")->execute([$davetId]);
        $db->commit();
        $mesaj = "🎉 Gruba katıldın!";
        $mesajTip = 'ok';
    }
}

// Daveti Reddet
if (isset($_GET['red'])) {
    $db->prepare("UPDATE grup_davetleri SET durum='red' WHERE id = ? AND davet_edilen = ?")
       ->execute([intval($_GET['red']), $userEmail]);
    $mesaj = "Davet reddedildi.";
    $mesajTip = 'info';
}

// Gruptan Ayrıl
if (isset($_GET['ayril'])) {
    $gId = intval($_GET['ayril']);
    $kontrol = $db->prepare("SELECT olusturan_email FROM gruplar WHERE id = ?");
    $kontrol->execute([$gId]);
    $kurucu = $kontrol->fetchColumn();
    if ($kurucu === $userEmail) {
        // Kurucu silerse grup tamamen silinsin
        $db->prepare("DELETE FROM gruplar WHERE id = ?")->execute([$gId]);
        $mesaj = "Grup silindi.";
    } else {
        $db->prepare("DELETE FROM grup_uyeleri WHERE grup_id = ? AND kullanici_email = ?")
           ->execute([$gId, $userEmail]);
        $mesaj = "Gruptan ayrıldın.";
    }
}

// Kullanıcının üye olduğu gruplar
$gruplar = $db->prepare("
    SELECT g.*, (SELECT COUNT(*) FROM grup_uyeleri WHERE grup_id = g.id) AS uye_sayisi
    FROM gruplar g
    INNER JOIN grup_uyeleri gu ON g.id = gu.grup_id
    WHERE gu.kullanici_email = ?
    ORDER BY g.id DESC
");
$gruplar->execute([$userEmail]);
$gruplar = $gruplar->fetchAll(PDO::FETCH_ASSOC);

// Bekleyen davetler
$davetler = $db->prepare("
    SELECT d.*, g.grup_adi, u.ad AS eden_ad
    FROM grup_davetleri d
    JOIN gruplar g ON d.grup_id = g.id
    JOIN users u ON d.davet_eden = u.email
    WHERE d.davet_edilen = ? AND d.durum = 'bekliyor'
    ORDER BY d.tarih DESC
");
$davetler->execute([$userEmail]);
$davetler = $davetler->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Gruplarım | notewarehouse</title>
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
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-purple-50 text-purple-600 p-2.5 rounded-xl mr-3 border border-purple-100"><i class="fas fa-users"></i></span>
            Gruplarım
        </h1>
    </div>
    <a href="calisma_alani.php" class="text-sm text-slate-500 hover:text-indigo-600 font-bold">← Kişisel</a>
</nav>

<main class="container mx-auto px-6 py-8 max-w-5xl">

    <?php if ($mesaj): ?>
        <div class="mb-6 p-4 rounded-xl font-bold text-sm <?= $mesajTip === 'ok' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($mesajTip === 'err' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200') ?>">
            <?= $mesaj ?>
        </div>
    <?php endif; ?>

    <!-- BEKLEYEN DAVETLER -->
    <?php if (!empty($davetler)): ?>
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-200 rounded-3xl p-6 mb-8">
            <h2 class="font-black text-amber-800 text-lg mb-4">📨 Bekleyen Davetler (<?= count($davetler) ?>)</h2>
            <div class="space-y-3">
                <?php foreach ($davetler as $d): ?>
                    <div class="bg-white rounded-2xl p-4 flex items-center justify-between shadow-sm">
                        <div>
                            <p class="font-bold text-slate-800"><?= htmlspecialchars($d['grup_adi']) ?></p>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($d['eden_ad']) ?> davet etti · <?= date('d M H:i', strtotime($d['tarih'])) ?></p>
                        </div>
                        <div class="flex gap-2">
                            <a href="?kabul=<?= $d['id'] ?>" class="bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold text-xs hover:bg-emerald-600"><i class="fas fa-check mr-1"></i>Kabul Et</a>
                            <a href="?red=<?= $d['id'] ?>" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-300"><i class="fas fa-times mr-1"></i>Reddet</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- GRUP OLUŞTUR -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 mb-8">
        <h2 class="font-bold text-lg mb-4"><i class="fas fa-plus-circle text-indigo-500 mr-2"></i>Yeni Grup Oluştur</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input type="text" name="grup_adi" placeholder="Grup Adı (Örn: Proje Ekibi)" required class="md:col-span-1 bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none focus:border-indigo-400 text-sm">
            <input type="text" name="aciklama" placeholder="Kısa açıklama (opsiyonel)" class="md:col-span-1 bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none focus:border-indigo-400 text-sm">
            <button name="grup_kur" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-indigo-700 transition">Grup Oluştur</button>
        </form>
    </div>

    <!-- GRUPLARIM -->
    <?php if (empty($gruplar)): ?>
        <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-slate-100">
            <div class="text-5xl mb-3">👥</div>
            <h3 class="text-xl font-bold text-slate-700">Henüz hiç gruptasın değilsin</h3>
            <p class="text-slate-400 text-sm mt-2">Yukarıdan grup oluştur ya da arkadaşlarının davetini bekle.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($gruplar as $g):
                $kurucuMu = $g['olusturan_email'] === $userEmail;
                // Üyeleri çek
                $u = $db->prepare("SELECT u.ad, u.email FROM grup_uyeleri gu JOIN users u ON gu.kullanici_email = u.email WHERE gu.grup_id = ?");
                $u->execute([$g['id']]);
                $uyeler = $u->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-black text-xl text-purple-600"><?= htmlspecialchars($g['grup_adi']) ?></h3>
                                <?php if ($kurucuMu): ?>
                                    <span class="bg-amber-100 text-amber-700 text-[9px] font-black px-2 py-0.5 rounded-full uppercase">Kurucu</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($g['aciklama'] ?: 'Açıklama yok') ?></p>
                            <p class="text-[10px] text-slate-400 mt-1">#<?= $g['id'] ?> · <?= $g['uye_sayisi'] ?> üye</p>
                        </div>
                    </div>

                    <!-- Üyeler -->
                    <div class="flex flex-wrap gap-2 mb-4 pb-4 border-b border-slate-100">
                        <?php foreach ($uyeler as $uye): ?>
                            <span class="inline-flex items-center bg-slate-100 rounded-full pl-1 pr-3 py-1 text-xs">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($uye['ad']) ?>&background=8b5cf6&color=fff" class="w-5 h-5 rounded-full mr-2">
                                <?= htmlspecialchars($uye['ad']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <!-- Davet formu -->
                    <form method="POST" class="flex gap-2 mb-3">
                        <input type="hidden" name="grup_id" value="<?= $g['id'] ?>">
                        <input type="email" name="uye_email" placeholder="Arkadaşının e-postası..." required class="text-sm bg-slate-50 border border-slate-200 p-2.5 rounded-xl flex-1 outline-none focus:border-indigo-400">
                        <button name="uye_davet" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-indigo-700">
                            <i class="fas fa-paper-plane mr-1"></i>Davet
                        </button>
                    </form>

                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <a href="grup_notlari.php?id=<?= $g['id'] ?>" class="text-center py-2.5 bg-purple-50 text-purple-600 rounded-xl font-bold text-xs hover:bg-purple-600 hover:text-white transition">
                            <i class="fas fa-folder-open mr-1"></i> Grup Notları
                        </a>
                        <a href="notlar.php?grup_id=<?= $g['id'] ?>" class="text-center py-2.5 bg-emerald-50 text-emerald-600 rounded-xl font-bold text-xs hover:bg-emerald-600 hover:text-white transition">
                            <i class="fas fa-plus mr-1"></i> Yeni Grup Notu
                        </a>
                    </div>

                    <a href="?ayril=<?= $g['id'] ?>" onclick="return confirm('<?= $kurucuMu ? 'Bu grubu tamamen silmek istiyor musun?' : 'Gruptan ayrılmak istiyor musun?' ?>');"
                       class="block w-full text-center py-2 bg-rose-50 text-rose-600 rounded-xl font-bold text-xs hover:bg-rose-500 hover:text-white transition">
                        <i class="fas fa-sign-out-alt mr-1"></i> <?= $kurucuMu ? 'Grubu Sil' : 'Ayrıl' ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
