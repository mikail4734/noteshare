<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';

// Sadece admin
if (($_SESSION['rol'] ?? 'user') !== 'admin') {
    header("Location: index.php");
    exit;
}

// Bekleyen sayisi
$bekleyenSayi = (int)$db->query("SELECT COUNT(*) FROM notes WHERE durum = 'beklemede'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bekleyen Notlar — Admin | notewarehouse</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50">

<nav class="bg-slate-900 text-white px-6 py-3 flex justify-between items-center sticky top-0 z-50 shadow-lg">
    <div class="flex items-center gap-4">
        <a href="admin_panel.php" class="text-slate-400 hover:text-white"><i class="fas fa-arrow-left"></i></a>
        <h1 class="font-black text-lg">🛡️ Bekleyen Notlar (Moderasyon)</h1>
    </div>
    <div class="flex items-center gap-3">
        <span class="bg-amber-500 text-slate-900 font-black px-3 py-1.5 rounded-lg text-sm" id="bekleyenSayac">
            <i class="fas fa-hourglass-half mr-1"></i> <?= $bekleyenSayi ?> bekliyor
        </span>
        <a href="admin_panel.php" class="bg-slate-800 hover:bg-slate-700 px-4 py-1.5 rounded-lg text-sm font-bold">
            Admin Paneli
        </a>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 py-8">

    <div id="bosMesaj" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl p-8 text-center">
        <i class="fas fa-check-circle text-5xl text-emerald-500 mb-3"></i>
        <h2 class="text-2xl font-black mb-2">Tüm notlar onaylandı! 🎉</h2>
        <p>Şu an inceleme bekleyen not yok. İyi iş!</p>
    </div>

    <div id="yukleniyor" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
        <p class="mt-2 text-slate-500 text-sm">Bekleyen notlar yükleniyor...</p>
    </div>

    <div id="notListesi" class="space-y-4 hidden"></div>
</main>

<!-- Detay Modal -->
<div id="detayModal" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-black text-xl text-slate-800" id="modalBaslik">Not Detayı</h3>
            <button onclick="modalKapat()" class="text-slate-400 hover:text-slate-700 text-2xl">&times;</button>
        </div>
        <div id="modalIcerik" class="p-6 overflow-y-auto flex-1 prose max-w-none"></div>
        <div class="p-6 border-t border-slate-100 flex gap-3 justify-end">
            <button onclick="reddetModal()" class="bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold px-5 py-2.5 rounded-xl">
                <i class="fas fa-times mr-1"></i> Reddet
            </button>
            <button onclick="onaylaModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl">
                <i class="fas fa-check mr-1"></i> Onayla & Yayınla
            </button>
        </div>
    </div>
</div>

<script>
let bekleyen = [];
let aktifNotId = null;

async function yukle() {
    try {
        const r = await fetch('islem.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ islem: 'bekleyen_notlar' })
        });
        const d = await r.json();
        document.getElementById('yukleniyor').classList.add('hidden');
        if (!d.success) {
            alert('Hata: ' + (d.error || 'bilinmiyor'));
            return;
        }
        bekleyen = d.notlar || [];
        cizdir();
    } catch (e) {
        alert('Bağlantı hatası: ' + e.message);
    }
}

function cizdir() {
    document.getElementById('bekleyenSayac').innerHTML =
        `<i class="fas fa-hourglass-half mr-1"></i> ${bekleyen.length} bekliyor`;

    const list = document.getElementById('notListesi');
    const bos = document.getElementById('bosMesaj');

    if (bekleyen.length === 0) {
        list.classList.add('hidden');
        bos.classList.remove('hidden');
        return;
    }
    bos.classList.add('hidden');
    list.classList.remove('hidden');

    list.innerHTML = bekleyen.map(n => `
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:border-amber-300 transition p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="text-[10px] bg-amber-100 text-amber-700 font-black px-2 py-1 rounded uppercase">${n.category || 'Genel'}</span>
                        <span class="text-[10px] bg-slate-100 text-slate-600 font-bold px-2 py-1 rounded">${n.edu_level || ''}</span>
                        <span class="text-[10px] bg-indigo-100 text-indigo-700 font-bold px-2 py-1 rounded">${n.subject || ''}</span>
                    </div>
                    <h3 class="font-extrabold text-xl text-slate-800 mb-2">${escape(n.title)}</h3>
                    <p class="text-sm text-slate-500 mb-2">
                        <i class="fas fa-user mr-1"></i> <a href="profil.php?email=${encodeURIComponent(n.kullanici_email || '')}" class="text-indigo-600 hover:underline font-bold">${n.yazar_ad || n.author || 'Anonim'}</a>
                        · ${formatDate(n.created_at)}
                    </p>
                    <p class="text-sm text-slate-600 leading-relaxed">${escape((n.onizleme || '').replace(/<[^>]*>/g, '').substring(0, 200))}...</p>
                </div>
                <div class="flex flex-col gap-2 shrink-0">
                    <button onclick="detayAc(${n.id})" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-eye mr-1"></i> İncele
                    </button>
                    <button onclick="hemenOnayla(${n.id})" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-check mr-1"></i> Onayla
                    </button>
                    <button onclick="hemenReddet(${n.id})" class="bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-times mr-1"></i> Reddet
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

async function detayAc(id) {
    // Tam icerigi getir
    aktifNotId = id;
    const not = bekleyen.find(n => n.id === id);
    if (!not) return;
    document.getElementById('modalBaslik').innerText = not.title;
    document.getElementById('modalIcerik').innerHTML = `
        <p class="text-sm text-slate-500 mb-4">
            <strong>Yazar:</strong> <a href="profil.php?email=${encodeURIComponent(not.kullanici_email)}" class="text-indigo-600 hover:underline">${not.yazar_ad || not.author}</a> ·
            <strong>Ders:</strong> ${not.subject} ·
            <strong>Seviye:</strong> ${not.edu_level}
        </p>
        <p>${not.onizleme || ''}</p>
        <p class="text-xs text-slate-400 italic mt-4">
            <i class="fas fa-info-circle"></i> Bu önizleme. Tam içerik için <a href="notlar.php?id=${not.id}" target="_blank" class="text-indigo-600 underline">notlar sayfasında</a> aç.
        </p>
    `;
    document.getElementById('detayModal').classList.remove('hidden');
}

function modalKapat() {
    document.getElementById('detayModal').classList.add('hidden');
    aktifNotId = null;
}

async function hemenOnayla(id) {
    if (!confirm('Bu notu onaylayıp yayınlamak istediğinden emin misin?')) return;
    await islem('not_onayla', { note_id: id });
}

async function hemenReddet(id) {
    const sebep = prompt('Red sebebi (opsiyonel):');
    if (sebep === null) return;
    await islem('not_reddet', { note_id: id, sebep });
}

async function onaylaModal() {
    if (!aktifNotId) return;
    if (!confirm('Bu notu onaylayıp yayınla?')) return;
    await islem('not_onayla', { note_id: aktifNotId });
    modalKapat();
}

async function reddetModal() {
    if (!aktifNotId) return;
    const sebep = prompt('Red sebebi (opsiyonel):');
    if (sebep === null) return;
    await islem('not_reddet', { note_id: aktifNotId, sebep });
    modalKapat();
}

async function islem(islemAdi, params) {
    try {
        const r = await fetch('islem.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ islem: islemAdi, ...params })
        });
        const d = await r.json();
        if (d.success) {
            // Listeden cikar
            bekleyen = bekleyen.filter(n => n.id !== params.note_id);
            cizdir();
        } else {
            alert('Hata: ' + (d.error || 'bilinmiyor'));
        }
    } catch (e) {
        alert('Bağlantı hatası');
    }
}

function escape(s) {
    return (s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
}

function formatDate(s) {
    if (!s) return '';
    const d = new Date(s);
    return d.toLocaleDateString('tr-TR', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'});
}

yukle();
</script>

<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
