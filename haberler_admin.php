<?php
session_start();
require_once __DIR__ . '/baglan.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); exit;
}

// Slug oluşturma yardımcısı
function slugYap($s) {
    $tr = ['ı','İ','ğ','Ğ','ü','Ü','ş','Ş','ö','Ö','ç','Ç',' ','/','&','?','!','.',','];
    $en = ['i','i','g','g','u','u','s','s','o','o','c','c','-','-','ve','','','','-'];
    $s = str_replace($tr, $en, $s);
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9\-]/', '', $s);
    $s = preg_replace('/-+/', '-', $s);
    return trim($s, '-');
}

// Sil
if (isset($_GET['sil'])) {
    $db->prepare("DELETE FROM haberler WHERE id = ?")->execute([intval($_GET['sil'])]);
    header("Location: haberler_admin.php"); exit;
}

// Kaydet (ekle veya güncelle)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $baslik = trim($_POST['baslik']);
    $slug = trim($_POST['slug']) ?: slugYap($baslik);
    $ozet = trim($_POST['ozet']);
    $icerik = $_POST['icerik']; // HTML
    $kategori = $_POST['kategori'] ?: 'genel';
    $yazar = trim($_POST['yazar']) ?: 'notewarehouse';
    $yayinda = isset($_POST['yayinda']) ? 1 : 0;

    if ($id) {
        $db->prepare("UPDATE haberler SET baslik=?, slug=?, ozet=?, icerik=?, kategori=?, yazar=?, yayinda=? WHERE id=?")
           ->execute([$baslik, $slug, $ozet, $icerik, $kategori, $yazar, $yayinda, $id]);
    } else {
        $db->prepare("INSERT INTO haberler (baslik, slug, ozet, icerik, kategori, yazar, yayinda) VALUES (?, ?, ?, ?, ?, ?, ?)")
           ->execute([$baslik, $slug, $ozet, $icerik, $kategori, $yazar, $yayinda]);
    }
    header("Location: haberler_admin.php?ok=1"); exit;
}

// Düzenleme için kayıt çek
$duzenle = null;
if (isset($_GET['duzenle'])) {
    $s = $db->prepare("SELECT * FROM haberler WHERE id = ?");
    $s->execute([intval($_GET['duzenle'])]);
    $duzenle = $s->fetch(PDO::FETCH_ASSOC);
}

$haberler = $db->query("SELECT * FROM haberler ORDER BY tarih DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Haberler Yönetimi | Admin</title>
    <link rel="icon" type="image/png" href="/favicon-32.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}#editor{min-height:300px;background:white}</style>
</head>
<body>

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-red-500"><i class="fas fa-arrow-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <img src="/favicon-180.png" alt="notewarehouse" class="w-8 h-8 rounded-lg mr-2 inline-block"><span class="bg-red-50 text-red-500 p-2.5 rounded-xl mr-3 border border-red-100"><i class="fas fa-newspaper"></i></span>
            Haberler Yönetimi
        </h1>
    </div>
    <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg uppercase">Admin</span>
</nav>

<main class="container mx-auto px-6 py-8 max-w-5xl">

    <?php if (isset($_GET['ok'])): ?>
        <div class="bg-emerald-50 text-emerald-700 p-3 rounded-xl mb-4 font-bold">✅ Başarıyla kaydedildi.</div>
    <?php endif; ?>

    <!-- FORM -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 mb-8">
        <h2 class="font-black text-lg mb-4">
            <?= $duzenle ? '✏️ Haberi Düzenle' : '➕ Yeni Haber Ekle' ?>
        </h2>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="id" value="<?= $duzenle['id'] ?? '' ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Başlık *</label>
                    <input type="text" name="baslik" required value="<?= htmlspecialchars($duzenle['baslik'] ?? '') ?>"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none focus:border-indigo-400">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Slug (URL, boş bırakırsan otomatik üretilir)</label>
                    <input type="text" name="slug" value="<?= htmlspecialchars($duzenle['slug'] ?? '') ?>"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none focus:border-indigo-400">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Kategori</label>
                    <select name="kategori" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none">
                        <?php foreach (['duyuru','rehber','haber','genel','guncelleme'] as $k): ?>
                            <option value="<?= $k ?>" <?= ($duzenle['kategori'] ?? '') === $k ? 'selected' : '' ?>><?= ucfirst($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Yazar</label>
                    <input type="text" name="yazar" value="<?= htmlspecialchars($duzenle['yazar'] ?? 'notewarehouse Ekibi') ?>"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none focus:border-indigo-400">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer bg-emerald-50 px-4 py-3 rounded-xl border border-emerald-200">
                        <input type="checkbox" name="yayinda" <?= (!$duzenle || $duzenle['yayinda']) ? 'checked' : '' ?> class="w-5 h-5">
                        <span class="font-bold text-emerald-700">Yayında</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Özet (kart önizlemesi)</label>
                <textarea name="ozet" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none focus:border-indigo-400"><?= htmlspecialchars($duzenle['ozet'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">İçerik (HTML destekler)</label>
                <div id="editor"><?= $duzenle['icerik'] ?? '' ?></div>
                <input type="hidden" name="icerik" id="icerikInput">
            </div>

            <div class="flex gap-3">
                <button type="submit" onclick="document.getElementById('icerikInput').value = quill.root.innerHTML"
                        class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i> <?= $duzenle ? 'Güncelle' : 'Yayınla' ?>
                </button>
                <?php if ($duzenle): ?>
                    <a href="haberler_admin.php" class="bg-slate-200 text-slate-700 px-6 py-3 rounded-xl font-bold hover:bg-slate-300">İptal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- LİSTE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="grid grid-cols-12 bg-slate-50 px-5 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b">
            <div class="col-span-5">Başlık</div>
            <div class="col-span-2">Kategori</div>
            <div class="col-span-2">Tarih</div>
            <div class="col-span-1 text-center">Görnt.</div>
            <div class="col-span-2 text-right">İşlem</div>
        </div>
        <?php foreach ($haberler as $h): ?>
            <div class="grid grid-cols-12 px-5 py-4 items-center border-b border-slate-100 hover:bg-slate-50/50 <?= $h['yayinda'] ? '' : 'opacity-50' ?>">
                <div class="col-span-5">
                    <p class="font-bold text-sm"><?= htmlspecialchars($h['baslik']) ?> <?= $h['yayinda'] ? '' : '<span class="text-xs text-rose-500 ml-2">[TASLAK]</span>' ?></p>
                    <p class="text-[10px] text-slate-400">/<?= htmlspecialchars($h['slug']) ?></p>
                </div>
                <div class="col-span-2"><span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded font-bold uppercase"><?= htmlspecialchars($h['kategori']) ?></span></div>
                <div class="col-span-2 text-xs text-slate-500"><?= date('d M Y', strtotime($h['tarih'])) ?></div>
                <div class="col-span-1 text-center text-xs"><?= $h['goruntulenme'] ?></div>
                <div class="col-span-2 text-right space-x-1">
                    <a href="haber.php?slug=<?= urlencode($h['slug']) ?>" target="_blank" title="Görüntüle" class="inline-block w-8 h-8 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 text-center leading-8"><i class="fas fa-eye text-xs"></i></a>
                    <a href="?duzenle=<?= $h['id'] ?>" title="Düzenle" class="inline-block w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white text-center leading-8"><i class="fas fa-edit text-xs"></i></a>
                    <a href="?sil=<?= $h['id'] ?>" onclick="return confirm('Sil?')" title="Sil" class="inline-block w-8 h-8 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white text-center leading-8"><i class="fas fa-trash text-xs"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Haber içeriğini buraya yazın...',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['link', 'image', 'video', 'blockquote', 'code-block'],
            ['clean']
        ]
    }
});
</script>
</body>
</html>
