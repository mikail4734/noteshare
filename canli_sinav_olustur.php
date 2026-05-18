<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once 'baglan.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: canli_sinavlar.php"); exit;
}

$mesaj = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baslik = trim($_POST['baslik']);
    $aciklama = trim($_POST['aciklama']);
    $baslangic = $_POST['baslangic'];
    $bitis = $_POST['bitis'];
    $sure = intval($_POST['sure_dakika']);
    $sorular_json = $_POST['sorular_json'] ?? '[]';

    if (empty($baslik) || empty($baslangic) || empty($bitis)) {
        $mesaj = "Tüm zorunlu alanları doldur!";
    } else {
        $sorular = json_decode($sorular_json, true);
        if (!is_array($sorular) || count($sorular) === 0) {
            $mesaj = "En az 1 soru ekle!";
        } else {
            $db->beginTransaction();
            try {
                $db->prepare("INSERT INTO canli_sinavlar (baslik, aciklama, baslangic, bitis, sure_dakika, olusturan) VALUES (?, ?, ?, ?, ?, ?)")
                   ->execute([$baslik, $aciklama, $baslangic, $bitis, $sure, $_SESSION['user_email']]);
                $sinav_id = $db->lastInsertId();

                $ekle = $db->prepare("INSERT INTO canli_sinav_sorulari (sinav_id, soru_metni, secenek_a, secenek_b, secenek_c, secenek_d, dogru_cevap) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($sorular as $s) {
                    $ekle->execute([$sinav_id, $s['soru_metni'], $s['secenek_a'], $s['secenek_b'], $s['secenek_c'], $s['secenek_d'], $s['dogru_cevap']]);
                }

                // Tüm kullanıcılara bildirim
                $emails = $db->query("SELECT email FROM users WHERE durum = 1")->fetchAll(PDO::FETCH_COLUMN);
                $bildirimEkle = $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen, tip) VALUES (?, ?, ?, ?, 'canli_sinav')");
                foreach ($emails as $em) {
                    $bildirimEkle->execute([
                        $em,
                        "🎯 Yeni Canlı Sınav: $baslik",
                        "Tarih: " . date('d M Y H:i', strtotime($baslangic)) . " — Süre: $sure dakika. Hazır ol!",
                        $_SESSION['user_name'] ?? 'Admin'
                    ]);
                }
                $db->commit();
                header("Location: canli_sinavlar.php?olusturuldu=1");
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                $mesaj = "Hata: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <title>Canlı Sınav Oluştur | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body>

<nav class="bg-rose-600 px-8 py-4 flex justify-between items-center text-white sticky top-0 z-50 shadow-lg">
    <h1 class="font-black text-xl flex items-center"><i class="fas fa-plus-circle mr-3"></i> Canlı Sınav Oluştur</h1>
    <a href="canli_sinavlar.php" class="text-white/80 hover:text-white text-sm">← Geri</a>
</nav>

<main class="container mx-auto px-6 py-10 max-w-3xl">
    <?php if ($mesaj): ?><div class="mb-4 p-4 bg-rose-50 text-rose-700 rounded-xl"><?= htmlspecialchars($mesaj) ?></div><?php endif; ?>

    <form method="POST" id="sinavForm" class="space-y-5">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h2 class="font-black mb-4">📋 Sınav Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="baslik" required placeholder="Sınav Başlığı (örn: AYT Matematik Deneme)" class="md:col-span-2 bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none focus:border-rose-400">
                <textarea name="aciklama" rows="2" placeholder="Açıklama..." class="md:col-span-2 bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none focus:border-rose-400"></textarea>
                <label class="block">
                    <span class="text-xs font-bold text-slate-500 uppercase">Başlangıç</span>
                    <input type="datetime-local" name="baslangic" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none mt-1">
                </label>
                <label class="block">
                    <span class="text-xs font-bold text-slate-500 uppercase">Bitiş</span>
                    <input type="datetime-local" name="bitis" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none mt-1">
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-bold text-slate-500 uppercase">Süre (dakika)</span>
                    <input type="number" name="sure_dakika" value="60" min="5" max="300" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none mt-1">
                </label>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-black">📝 Sorular (<span id="soruSayisi">0</span>)</h2>
                <button type="button" onclick="soruEkle()" class="bg-rose-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-rose-700">+ Soru Ekle</button>
            </div>
            <div id="sorular" class="space-y-4"></div>
        </div>

        <input type="hidden" name="sorular_json" id="sorularJson">
        <button type="button" onclick="kaydetVeGonder()" class="w-full bg-gradient-to-r from-rose-500 to-pink-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg hover:shadow-xl">
            <i class="fas fa-rocket mr-2"></i> Sınavı Yayınla
        </button>
    </form>
</main>

<script>
let count = 0;
function soruEkle() {
    count++;
    const id = 'soru-' + count;
    document.getElementById('sorular').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="bg-slate-50 p-5 rounded-2xl border border-slate-200">
            <div class="flex justify-between mb-3">
                <span class="font-black text-rose-600">Soru ${count}</span>
                <button type="button" onclick="document.getElementById('${id}').remove(); guncelle();" class="text-rose-400 hover:text-rose-600 text-sm"><i class="fas fa-trash"></i></button>
            </div>
            <textarea class="w-full bg-white border border-slate-200 rounded-xl p-3 mb-3 text-sm" placeholder="Soru metni..." rows="2"></textarea>
            <div class="grid grid-cols-2 gap-2 mb-3">
                <input type="text" placeholder="A şıkkı" class="bg-white border border-slate-200 rounded-lg p-2 text-sm">
                <input type="text" placeholder="B şıkkı" class="bg-white border border-slate-200 rounded-lg p-2 text-sm">
                <input type="text" placeholder="C şıkkı" class="bg-white border border-slate-200 rounded-lg p-2 text-sm">
                <input type="text" placeholder="D şıkkı" class="bg-white border border-slate-200 rounded-lg p-2 text-sm">
            </div>
            <select class="bg-white border border-slate-200 rounded-lg p-2 text-sm w-full">
                <option value="A">Doğru Cevap: A</option>
                <option value="B">Doğru Cevap: B</option>
                <option value="C">Doğru Cevap: C</option>
                <option value="D">Doğru Cevap: D</option>
            </select>
        </div>
    `);
    guncelle();
}
function guncelle() {
    document.getElementById('soruSayisi').innerText = document.querySelectorAll('#sorular > div').length;
}
function kaydetVeGonder() {
    const sorular = [];
    document.querySelectorAll('#sorular > div').forEach(d => {
        const t = d.querySelector('textarea').value;
        const inps = d.querySelectorAll('input');
        const sel = d.querySelector('select').value;
        if (t.trim()) {
            sorular.push({
                soru_metni: t, secenek_a: inps[0].value, secenek_b: inps[1].value,
                secenek_c: inps[2].value, secenek_d: inps[3].value, dogru_cevap: sel
            });
        }
    });
    if (sorular.length === 0) { alert("En az 1 soru ekle!"); return; }
    document.getElementById('sorularJson').value = JSON.stringify(sorular);
    document.getElementById('sinavForm').submit();
}
soruEkle();
</script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
