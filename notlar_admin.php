<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Filtreler
$seviye = $_GET['seviye'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$arama = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($seviye)   { $where[] = "edu_level = ?"; $params[] = $seviye; }
if ($kategori) { $where[] = "category = ?";  $params[] = $kategori; }
if ($arama)    { $where[] = "(title LIKE ? OR author LIKE ? OR subject LIKE ?)";
                 $params[] = "%$arama%"; $params[] = "%$arama%"; $params[] = "%$arama%"; }

$sql = "SELECT * FROM notes";
if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY id DESC";

$s = $db->prepare($sql);
$s->execute($params);
$notlar = $s->fetchAll(PDO::FETCH_ASSOC);

$toplamSayi = (int)$db->query("SELECT COUNT(*) FROM notes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <title>Not Yönetimi | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="text-slate-800">

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-red-500"><i class="fas fa-arrow-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-red-50 text-red-500 p-2.5 rounded-xl mr-3 border border-red-100"><i class="fas fa-clipboard-list"></i></span>
            Not Yönetimi <span class="ml-3 text-sm text-slate-400 font-normal">(<?= $toplamSayi ?> not)</span>
        </h1>
    </div>
    <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg uppercase">Admin</span>
</nav>

<main class="container mx-auto px-6 py-8 max-w-6xl">
    <!-- Filtreler -->
    <form method="GET" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="q" value="<?= htmlspecialchars($arama) ?>" placeholder="Başlık / Yazar / Ders ara..." class="bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 outline-none focus:border-indigo-400 text-sm">
        <select name="seviye" class="bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 outline-none text-sm">
            <option value="">Tüm Seviyeler</option>
            <?php foreach (['Üniversite','Lise','Orta Okul','İlkokul'] as $v): ?>
                <option value="<?= $v ?>" <?= $seviye === $v ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <select name="kategori" class="bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 outline-none text-sm">
            <option value="">Tüm Kategoriler</option>
            <?php foreach (['Konu Anlatımı','Soru Çözümü','Özet','Kod','Formül','Deney'] as $k): ?>
                <option value="<?= $k ?>" <?= $kategori === $k ? 'selected' : '' ?>><?= $k ?></option>
            <?php endforeach; ?>
        </select>
        <button class="bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700"><i class="fas fa-filter mr-2"></i>Filtrele</button>
    </form>

    <!-- Notlar tablosu -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="grid grid-cols-12 bg-slate-50 px-5 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b">
            <div class="col-span-4">Başlık</div>
            <div class="col-span-2">Yazar</div>
            <div class="col-span-2">Seviye/Ders</div>
            <div class="col-span-2">Kategori</div>
            <div class="col-span-1 text-center">Beğeni</div>
            <div class="col-span-1 text-right">İşlem</div>
        </div>

        <?php if (empty($notlar)): ?>
            <p class="p-16 text-center text-slate-400">Bu filtreyle eşleşen not yok.</p>
        <?php else: ?>
            <?php foreach ($notlar as $n): ?>
                <div class="grid grid-cols-12 px-5 py-4 items-center border-b border-slate-100 hover:bg-slate-50/50 transition">
                    <div class="col-span-4 min-w-0">
                        <h4 class="font-bold text-sm truncate"><?= htmlspecialchars($n['title']) ?></h4>
                        <p class="text-[10px] text-slate-400">#<?= $n['id'] ?> · <?= date('d M Y', strtotime($n['created_at'])) ?></p>
                    </div>
                    <div class="col-span-2 text-xs text-slate-600 truncate"><?= htmlspecialchars($n['author'] ?: '—') ?></div>
                    <div class="col-span-2 text-[10px]">
                        <span class="font-bold text-slate-700"><?= htmlspecialchars($n['edu_level'] ?: '—') ?></span><br>
                        <span class="text-slate-400"><?= htmlspecialchars($n['subject'] ?: '') ?></span>
                    </div>
                    <div class="col-span-2"><span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-1 rounded-md font-bold uppercase"><?= htmlspecialchars($n['category'] ?: 'Genel') ?></span></div>
                    <div class="col-span-1 text-center text-xs">
                        <span class="text-emerald-600 font-bold"><?= $n['likes'] ?></span>
                        <span class="text-rose-400 ml-1">/<?= $n['dislikes'] ?></span>
                    </div>
                    <div class="col-span-1 text-right space-x-1">
                        <a href="notlar.php?id=<?= $n['id'] ?>" title="Gör/Düzenle" class="inline-block w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white text-center leading-8"><i class="fas fa-eye text-xs"></i></a>
                        <button onclick="notSil(<?= $n['id'] ?>)" title="Sil" class="w-8 h-8 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function notSil(id) {
    if (!confirm("Bu notu KALICI olarak silmek istediğinden emin misin?")) return;
    fetch('islem.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ islem: 'not_sil', note_id: id })
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert("Hata: " + (d.error || "silinemedi"));
    });
}
</script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
