<?php
session_start();
require_once 'baglan.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$kullanicilar = $db->query("SELECT email, ad FROM users ORDER BY ad")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bildirim Gönder | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen">

<nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-slate-400 hover:text-red-500"><i class="fas fa-arrow-left"></i></a>
        <h1 class="font-extrabold text-2xl flex items-center">
            <span class="bg-red-50 text-red-500 p-2.5 rounded-xl mr-3 border border-red-100"><i class="fas fa-bell"></i></span>
            Bildirim Gönder
        </h1>
    </div>
    <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg uppercase">Admin</span>
</nav>

<main class="container mx-auto px-6 py-10 max-w-2xl">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
        <form id="bildirimForm" class="space-y-5">
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Hedef</label>
                <select id="hedef" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 outline-none focus:border-indigo-400">
                    <option value="all">📢 Tüm Kullanıcılara</option>
                    <?php foreach ($kullanicilar as $k): ?>
                        <option value="<?= htmlspecialchars($k['email']) ?>">👤 <?= htmlspecialchars($k['ad']) ?> (<?= htmlspecialchars($k['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Başlık</label>
                <input type="text" id="baslik" placeholder="Örn: Yeni Özellik Geldi!" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 outline-none focus:border-indigo-400">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Mesaj *</label>
                <textarea id="mesaj" rows="6" required placeholder="Bildirim mesajını yaz..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 outline-none focus:border-indigo-400"></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-black uppercase tracking-widest hover:bg-indigo-700 shadow-lg">
                <i class="fas fa-paper-plane mr-2"></i> Gönder
            </button>
        </form>

        <div id="sonuc" class="hidden mt-6 p-4 rounded-xl"></div>
    </div>
</main>

<script>
document.getElementById('bildirimForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
        islem: 'bildirim_gonder',
        hedef: document.getElementById('hedef').value,
        baslik: document.getElementById('baslik').value,
        mesaj: document.getElementById('mesaj').value
    };
    if (!data.mesaj.trim()) { alert("Mesaj boş olamaz!"); return; }

    const r = await fetch('islem.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    const result = await r.json();

    const kutu = document.getElementById('sonuc');
    kutu.classList.remove('hidden');
    if (result.success) {
        kutu.className = "mt-6 p-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold";
        kutu.innerHTML = `<i class="fas fa-check-circle mr-2"></i> ${result.gonderildi} kişiye gönderildi!`;
        document.getElementById('bildirimForm').reset();
    } else {
        kutu.className = "mt-6 p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 font-bold";
        kutu.innerHTML = `<i class="fas fa-times-circle mr-2"></i> Hata: ${result.error}`;
    }
});
</script>
</body>
</html>
