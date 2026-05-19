<?php

require_once __DIR__ . '/oturum_baslat.php'; 
require_once 'baglan.php'; 

$noteId = isset($_GET['id']) ? intval($_GET['id']) : null;
$mevcutNot = null;


if ($noteId) {
    $sorgu = $db->prepare("SELECT * FROM notes WHERE id = ?");
    $sorgu->execute([$noteId]);
    $mevcutNot = $sorgu->fetch(PDO::FETCH_ASSOC);
}



$user_role = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'user';


// URL parametreleri: ders, seviye/edu (edu_level), okul (school_name), kategori
$secilenDers     = isset($_GET['ders']) ? $_GET['ders'] : null;
$secilenSeviye   = $_GET['seviye'] ?? $_GET['edu'] ?? null; // edu = alias
$secilenOkul     = isset($_GET['okul']) ? $_GET['okul'] : null;
$secilenKategori = isset($_GET['kategori']) ? $_GET['kategori'] : null;

try {
    $where = [];
    $params = [];
    if ($secilenDers)     { $where[] = "subject = ?";     $params[] = $secilenDers; }
    if ($secilenSeviye)   { $where[] = "edu_level = ?";   $params[] = $secilenSeviye; }
    if ($secilenOkul)     { $where[] = "school_name = ?"; $params[] = $secilenOkul; }
    if ($secilenKategori) { $where[] = "category = ?";    $params[] = $secilenKategori; }

    // MODERASYON: Public listede sadece onayli notlar gozukur
    // Sahip kendi bekleyen notlarini "Calisma Alanim" / "Profilim"den gorebilir
    // Sadece admin tum durumlari gorur
    $kullaniciEmail = $_SESSION['user_email'] ?? null;
    $kullaniciRol   = $_SESSION['rol'] ?? 'guest';
    if ($kullaniciRol !== 'admin') {
        $where[] = "(durum IS NULL OR durum = 'onayli')";
    }

    $sql = "SELECT * FROM notes";
    if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY id DESC";

    $sorgu = $db->prepare($sql);
    $sorgu->execute($params);
    $notlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    $baslikParcalari = array_filter([$secilenSeviye, $secilenOkul, $secilenDers, $secilenKategori]);
    $sayfaBaslik = !empty($baslikParcalari)
        ? htmlspecialchars(implode(" · ", $baslikParcalari)) . " Notları"
        : "Tüm Notlar";

    $jsonNotes = json_encode($notlar);
} catch (PDOException $e) {
    $jsonNotes = json_encode([]);
    $sayfaBaslik = "Hata Oluştu";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
   <title><?php echo $sayfaBaslik; ?> | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .serif-font { font-family: 'Merriweather', serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-[#F9FAFB] text-[#1F2937]">

    <div id="noteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-3xl max-h-[80vh] rounded-[2rem] shadow-2xl overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white sticky top-0">
                <div>
                    <h2 id="modalTitle" class="text-xl font-extrabold text-slate-900 leading-tight"></h2>
                    <p id="modalCategory" class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-1"></p>
                </div>
                <button onclick="closeNote()" class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div id="modalContent" class="p-8 overflow-y-auto serif-font leading-relaxed text-slate-700 text-lg no-scrollbar"></div>
            <div class="p-6 border-t border-slate-50 bg-slate-50/50 flex justify-end">
                <button onclick="closeNote()" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold text-sm hover:opacity-90 transition">Kapat</button>
            </div>
        </div>
    </div>

    <nav class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
<?php
                // Akıllı geri navigasyon: seviyeye göre üst sayfa
                $geriUrl = 'index.php';
                if ($secilenSeviye === 'Üniversite') $geriUrl = 'universite.php';
                elseif ($secilenSeviye === 'Lise') $geriUrl = 'lise.php';
                elseif ($secilenSeviye === 'Orta Okul' || $secilenSeviye === 'Ortaokul') $geriUrl = 'ortaokul.php';
                elseif ($secilenSeviye === 'İlkokul') $geriUrl = 'ilkokul.php';
            ?>
            <a href="<?= $geriUrl ?>" class="text-slate-400 hover:text-indigo-600 transition" title="Geri">
                <i class="fas fa-chevron-left text-lg"></i>
            </a>
           <h1 class="font-extrabold text-2xl tracking-tight flex items-center text-slate-900">
    <span class="bg-indigo-50 text-indigo-600 p-2.5 rounded-xl mr-3 shadow-sm border border-indigo-100">
        <i class="fas fa-book-open"></i>
    </span>
    <?php echo $sayfaBaslik; ?>
</h1>
        </div>
        <div class="flex items-center space-x-4">
            <a href="notlar.php" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                <i class="fas fa-plus mr-2 text-xs"></i> Not Oluştur
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-10 flex flex-col lg:flex-row gap-10">
        <div class="flex-1">
            <div class="flex space-x-3 mb-8 overflow-x-auto pb-2 no-scrollbar">
                <button onclick="filterNotes('Tümü')" class="bg-slate-900 text-white px-5 py-2 rounded-xl text-xs font-bold shadow-lg">Tümü</button>
                <button onclick="filterNotes('Konu Anlatımı')" class="bg-white text-slate-600 border border-slate-200 px-5 py-2 rounded-xl text-xs font-bold hover:bg-slate-50 transition">Konu Anlatımı</button>
               <button onclick="filterNotes('Soru Çözümü')" class="bg-white text-slate-600 border border-slate-200 px-5 py-2 rounded-xl text-xs font-bold hover:bg-slate-50 transition">Soru Çözümü</button>
                <button onclick="filterNotes('Özet')" class="bg-white text-slate-600 border border-slate-200 px-5 py-2 rounded-xl text-xs font-bold hover:bg-slate-50 transition">Özetler</button>
            </div>

            <div class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-slate-200">
                <div class="grid grid-cols-5 bg-slate-50/50 px-6 py-5 border-b border-slate-200">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Doküman</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Yazar</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Düzenleme</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Etkileşim</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">Durum</div>
                </div>

                <div id="noteTableBody" class="divide-y divide-slate-100">
                    </div>
            </div>
        </div>

        <div class="w-full lg:w-[420px] shrink-0">
            <div class="bg-white rounded-[2rem] shadow-2xl border border-slate-200 flex flex-col h-[750px] sticky top-28 overflow-hidden">

                <!-- TAB HEADER -->
                <div class="grid grid-cols-3 border-b border-slate-100 bg-slate-50/50">
                    <button onclick="tabAc('not')" id="tab-not" class="tabBtn py-4 text-xs font-bold text-slate-400 hover:text-indigo-600 transition border-b-2 border-transparent">
                        <i class="fas fa-file-alt block mb-1"></i> Not
                    </button>
                    <button onclick="tabAc('yorum')" id="tab-yorum" class="tabBtn py-4 text-xs font-bold text-slate-400 hover:text-indigo-600 transition border-b-2 border-transparent">
                        <i class="fas fa-comments block mb-1"></i> <span>Yorumlar</span>
                        <span id="yorumSayisi" class="hidden ml-1 bg-rose-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="tabAc('ai')" id="tab-ai" class="tabBtn py-4 text-xs font-bold text-indigo-600 border-b-2 border-indigo-600 transition">
                        <i class="fas fa-robot block mb-1"></i> AI
                    </button>
                </div>

                <!-- TAB İÇERİKLERİ -->

                <!-- 1) NOT ÖNİZLEME -->
                <div id="content-not" class="hidden flex-1 overflow-y-auto p-6 no-scrollbar bg-white">
                    <div id="notIcerik" class="text-slate-400 text-center italic py-12">
                        <i class="fas fa-arrow-left text-3xl mb-3"></i><br>
                        Önce sol taraftan bir not seç ve <b>Analiz Et</b>'e bas
                    </div>
                </div>

                <!-- 2) YORUMLAR -->
                <div id="content-yorum" class="hidden flex-1 overflow-y-auto p-4 no-scrollbar bg-white">
                    <div id="yorumListesi" class="text-slate-400 text-center italic py-12">
                        Bir not seçtikten sonra yorumlar burada görünecek
                    </div>
                    <div id="yorumFormKutu" class="hidden mt-4 pt-4 border-t border-slate-100">
                        <textarea id="yorumInput" rows="2" placeholder="Yorum yaz..."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-indigo-400 mb-2"></textarea>
                        <button onclick="yorumGonder()" class="w-full bg-indigo-600 text-white py-2 rounded-xl text-sm font-bold hover:bg-indigo-700">
                            <i class="fas fa-paper-plane mr-1"></i> Gönder
                        </button>
                    </div>
                </div>

                <!-- 3) AI CHAT -->
                <div id="content-ai" class="flex-1 flex flex-col overflow-hidden">
                    <div id="chatMessages" class="flex-1 p-6 overflow-y-auto space-y-4 no-scrollbar bg-[#FDFDFD]">
                        <div class="bg-slate-100/70 p-4 rounded-2xl text-[13px] text-slate-700 italic">
                            "Selam! Bir not seçip <b>Analiz Et</b>'e bas, sana özetleyeyim, soru hazırlayayım veya anlatayım."
                        </div>
                    </div>
                    <div class="p-4 bg-white border-t border-slate-100">
                        <form onsubmit="event.preventDefault(); sendMessage();" class="relative">
                            <input type="text" id="aiInput" placeholder="Soru sor..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 pl-5 pr-12 text-sm outline-none">
                            <button type="submit" class="absolute right-2 top-1.5 bg-indigo-600 text-white w-9 h-9 rounded-xl flex items-center justify-center">
                                <i class="fas fa-arrow-up text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div id="hoverPreviewBox" class="fixed hidden bg-white/95 backdrop-blur-md rounded-2xl shadow-[0_20px_50px_rgba(79,70,229,0.15)] border border-indigo-50 p-5 w-80 z-[9999] pointer-events-none transition-opacity duration-200 opacity-0">
    <div class="flex items-center mb-2">
        <div class="w-2 h-2 rounded-full bg-indigo-500 mr-2 animate-pulse"></div>
        <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest" id="previewCategory">Kategori</span>
    </div>
    <h3 class="font-extrabold text-slate-800 text-[15px] mb-2 leading-tight" id="previewTitle">Başlık</h3>
    
    <div class="text-xs text-slate-500 font-medium leading-relaxed line-clamp-4" id="previewContent">
        İçerik yükleniyor...
    </div>
    
    <div class="mt-3 pt-3 border-t border-slate-100 text-[9px] font-bold text-slate-400 text-right">
        Okumak için tıklayın <i class="fas fa-arrow-right ml-1"></i>
    </div>
</div>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
<script>

let allNotes = <?php echo $jsonNotes; ?>;
const currentUserRole = '<?php echo $user_role; ?>'; 

window.onload = () => {
    displayNotes(allNotes);
};

function displayNotes(notesList) {
    const tableBody = document.getElementById("noteTableBody");
    if (!tableBody) return;
    tableBody.innerHTML = ""; 

   
    const previewBox = document.getElementById('hoverPreviewBox');
    let hideTimeout; 

    notesList.forEach(note => {
        const dateStr = new Date(note.created_at).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long' });
        const noteRow = document.createElement("div");
        noteRow.className = "grid grid-cols-5 items-center px-6 py-6 hover:bg-indigo-50/30 transition-all group cursor-pointer border-b border-slate-100 relative";
        
       
        noteRow.onclick = () => {
            window.location.href = `notlar.php?id=${note.id}`;
        };

        
        noteRow.onmouseenter = (e) => {
            if(!previewBox) return; 
            clearTimeout(hideTimeout); 
            
            document.getElementById('previewTitle').innerText = note.title;
            document.getElementById('previewCategory').innerText = note.category || 'Genel';
            
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = note.content || 'Bu notun içeriği henüz boş...';
            const plainText = tempDiv.textContent || tempDiv.innerText || "";
            document.getElementById('previewContent').innerText = plainText.substring(0, 150) + '...';

            
            previewBox.style.pointerEvents = 'none';

            
            previewBox.style.top = (e.clientY + 20) + 'px';
            previewBox.style.left = (e.clientX + 20) + 'px';

            previewBox.classList.remove('hidden');
            setTimeout(() => previewBox.classList.remove('opacity-0'), 10);
        };

        
        noteRow.onmousemove = (e) => {
            if(!previewBox) return;
           
            previewBox.style.top = (e.clientY + 20) + 'px';
            previewBox.style.left = (e.clientX + 20) + 'px';
        };

       
        noteRow.onmouseleave = () => {
            if(!previewBox) return;
            previewBox.classList.add('opacity-0');
          
            hideTimeout = setTimeout(() => previewBox.classList.add('hidden'), 200);
        };

        let adminButtons = '';
        if (typeof currentUserRole !== 'undefined' && currentUserRole === 'admin') {
            adminButtons = `<button onclick="event.stopPropagation(); deleteNote(${note.id})" class="text-[10px] font-extrabold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg hover:bg-red-600 hover:text-white transition-all uppercase ml-2">Sil</button>`;
        }

        // Soru Çözümü notuysa "Testi Çöz" butonu göster
        let quizBtn = '';
        if (note.category === 'Soru Çözümü' || note.category === 'soru_cozumu') {
            quizBtn = `<button onclick="event.stopPropagation(); window.location.href='quiz_coz.php?id=${note.id}'" class="text-[10px] font-extrabold text-white bg-emerald-500 px-3 py-1.5 rounded-lg hover:bg-emerald-600 transition-all uppercase shadow-md ml-2"><i class="fas fa-play mr-1"></i>Testi Çöz</button>`;
        }

        noteRow.innerHTML = `
            <div class="min-w-0">
                <h4 class="font-bold text-slate-900 group-hover:text-indigo-600 text-[14px] truncate">${note.title}</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase">${note.category || 'Genel'}</p>
            </div>
            <div class="text-xs font-semibold text-center">${note.kullanici_email ? `<a href="profil.php?email=${encodeURIComponent(note.kullanici_email)}" onclick="event.stopPropagation()" class="text-indigo-600 hover:text-indigo-800 hover:underline">@${note.author || 'Anonim'}</a>` : `<span class="text-slate-500">@${note.author || 'Anonim'}</span>`}</div>
            <div class="text-[11px] font-medium text-slate-400 text-center">${dateStr}</div>

            <div class="flex items-center justify-center space-x-3">
                <button onclick="event.stopPropagation(); updateReaction(${note.id}, 'like')" class="flex items-center space-x-1 text-slate-400 hover:text-emerald-500"><i class="far fa-thumbs-up text-xs"></i><span id="like-count-${note.id}" class="text-[11px] font-bold">${note.likes || 0}</span></button>
                <button onclick="event.stopPropagation(); updateReaction(${note.id}, 'dislike')" class="flex items-center space-x-1 text-slate-400 hover:text-rose-500"><i class="far fa-thumbs-down text-xs"></i><span id="dislike-count-${note.id}" class="text-[11px] font-bold">${note.dislikes || 0}</span></button>
            </div>

            <div class="text-right flex justify-end items-center">
                <button onclick="event.stopPropagation(); sendToAI(${note.id})" class="text-[10px] font-extrabold text-white bg-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-800 transition-all uppercase shadow-md">
                    Analiz Et
                </button>
                ${quizBtn}
                ${adminButtons}
            </div>
        `;
        tableBody.appendChild(noteRow);
    });
}

let aktifNotId = null;

// TAB YÖNETİMİ
function tabAc(tab) {
    ['not', 'yorum', 'ai'].forEach(t => {
        const btn = document.getElementById('tab-' + t);
        const cnt = document.getElementById('content-' + t);
        if (t === tab) {
            btn.classList.add('text-indigo-600', 'border-indigo-600');
            btn.classList.remove('text-slate-400', 'border-transparent');
            cnt.classList.remove('hidden');
            if (cnt.id === 'content-ai') cnt.classList.add('flex');
        } else {
            btn.classList.remove('text-indigo-600', 'border-indigo-600');
            btn.classList.add('text-slate-400', 'border-transparent');
            cnt.classList.add('hidden');
            if (cnt.id === 'content-ai') cnt.classList.remove('flex');
        }
    });
}

// NOT İÇERİĞİNİ SAĞ PANELE YÜKLE
function notuPanelGoster(note) {
    aktifNotId = note.id;
    const kutu = document.getElementById('notIcerik');
    const dateStr = new Date(note.created_at).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' });

    kutu.innerHTML = `
        <div class="mb-4 pb-4 border-b border-slate-100">
            <span class="text-[10px] bg-indigo-100 text-indigo-700 font-black px-2 py-1 rounded uppercase">${note.category || 'Genel'}</span>
            <h2 class="font-extrabold text-xl text-slate-800 mt-2 leading-tight">${note.title}</h2>
            <p class="text-xs text-slate-500 mt-2 font-medium">
                <i class="fas fa-user mr-1"></i>${note.kullanici_email ? `<a href="profil.php?email=${encodeURIComponent(note.kullanici_email)}" class="text-indigo-600 hover:text-indigo-800 hover:underline font-bold">@${note.author || 'Anonim'}</a>` : `@${note.author || 'Anonim'}`} · ${dateStr}
            </p>
            <div class="flex gap-3 mt-3 text-xs">
                <span class="text-emerald-600 font-bold"><i class="fas fa-thumbs-up mr-1"></i>${note.likes || 0}</span>
                <span class="text-rose-400 font-bold"><i class="fas fa-thumbs-down mr-1"></i>${note.dislikes || 0}</span>
                <a href="notlar.php?id=${note.id}" class="ml-auto text-indigo-600 font-bold hover:underline">
                    Tam sayfa <i class="fas fa-external-link-alt text-[10px]"></i>
                </a>
            </div>
        </div>
        <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed">
            ${note.content || '<p class="text-slate-400 italic">İçerik boş.</p>'}
        </div>
    `;
}

// YORUMLARI YÜKLE
async function yorumlariYukle(noteId) {
    const liste = document.getElementById('yorumListesi');
    const sayisi = document.getElementById('yorumSayisi');
    liste.innerHTML = '<div class="text-center py-6 text-slate-400 text-sm"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>';

    try {
        const r = await fetch('islem.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ islem: 'yorumlari_getir', note_id: noteId })
        });
        const yorumlar = await r.json();

        if (!Array.isArray(yorumlar) || yorumlar.length === 0) {
            liste.innerHTML = '<p class="text-center text-slate-400 text-sm py-6 italic">Henüz yorum yok. İlk yorumu sen yap! 💬</p>';
            sayisi.classList.add('hidden');
        } else {
            sayisi.classList.remove('hidden');
            sayisi.innerText = yorumlar.length;
            liste.innerHTML = yorumlar.map(y => `
                <div class="bg-slate-50 rounded-2xl p-3 mb-2">
                    <div class="flex items-center mb-1">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(y.kullanici_ad)}&background=4f46e5&color=fff&size=64" class="w-7 h-7 rounded-full mr-2">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-xs">@${y.kullanici_ad}</p>
                            <p class="text-[10px] text-slate-400">${new Date(y.tarih).toLocaleDateString('tr-TR', {day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'})}</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-700 ml-9">${y.mesaj.replace(/\n/g, '<br>')}</p>
                </div>
            `).join('');
        }

        document.getElementById('yorumFormKutu').classList.remove('hidden');
    } catch(e) {
        liste.innerHTML = '<p class="text-rose-500 text-sm text-center py-6">Yorumlar yüklenemedi</p>';
    }
}

// YORUM GÖNDER
async function yorumGonder() {
    if (!aktifNotId) return;
    const inp = document.getElementById('yorumInput');
    const mesaj = inp.value.trim();
    if (mesaj.length < 2) { alert("Çok kısa!"); return; }

    const r = await fetch('islem.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ islem: 'yorum_ekle', note_id: aktifNotId, mesaj })
    });
    const data = await r.json();
    if (data.success) {
        inp.value = '';
        yorumlariYukle(aktifNotId);
    } else {
        alert(data.error || 'Hata');
    }
}

function sendToAI(noteId) {

    const selectedNote = allNotes.find(n => n.id == noteId);
    if (!selectedNote) return;

    // 1) Sağ panele not içeriğini göster
    notuPanelGoster(selectedNote);

    // 2) Yorumları yükle
    yorumlariYukle(noteId);

    // 3) "Not" sekmesini aç (kullanıcı içeriği görsün)
    tabAc('not');

    // 4) AI chat alanını da hazırla
    const chat = document.getElementById('chatMessages');
    chat.innerHTML = `<div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl text-[13px] text-indigo-700 font-bold mb-4">
        "${selectedNote.title}" adlı dökümanı seçtiniz. Ne yapmak istersiniz?
    </div>`;

   
    const optionsHTML = `
        <div class="flex flex-col space-y-2 mt-4" id="aiOptions">
            <button onclick="processAINote(${noteId}, 'anlat')" class="bg-white border border-slate-200 p-3 rounded-xl text-[12px] font-bold text-slate-700 hover:bg-indigo-50 hover:border-indigo-200 transition text-left flex items-center">
                <i class="fas fa-volume-up mr-2 text-indigo-500"></i> İçeriği sesli anlat (Sesli Özet)
            </button>
            <button onclick="processAINote(${noteId}, 'ozet')" class="bg-white border border-slate-200 p-3 rounded-xl text-[12px] font-bold text-slate-700 hover:bg-indigo-50 hover:border-indigo-200 transition text-left flex items-center">
                <i class="fas fa-compress-alt mr-2 text-indigo-500"></i> Notun özetini çıkart
            </button>
            <button onclick="processAINote(${noteId}, 'soru')" class="bg-white border border-slate-200 p-3 rounded-xl text-[12px] font-bold text-slate-700 hover:bg-indigo-50 hover:border-indigo-200 transition text-left flex items-center">
                <i class="fas fa-question-circle mr-2 text-indigo-500"></i> Bu nottan 20 soru hazırla
            </button>
        </div>
    `;
    chat.innerHTML += optionsHTML;
    chat.scrollTop = chat.scrollHeight;
}

async function processAINote(noteId, type) {
    const selectedNote = allNotes.find(n => n.id == noteId);
    const optionsDiv = document.getElementById('aiOptions');
    if (optionsDiv) optionsDiv.remove();

    addMessageToChat(type === 'anlat' ? "Sesli anlatım hazırla." : (type === 'ozet' ? "Özet çıkar." : "20 soru hazırla."), 'user');
    addMessageToChat("Hazırlıyorum, lütfen bekleyin...", 'ai');

    try {
        // Soru üretimi farklı endpoint kullanır
        if (type === 'soru') {
            const response = await fetch('islem.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    islem: 'ai_soru_uret',
                    icerik: selectedNote.content,
                    adet: 10
                })
            });
            const data = await response.json();
            if (data.success && Array.isArray(data.sorular)) {
                let html = '<strong>📝 ' + data.sorular.length + ' soru üretildi:</strong><br><br>';
                data.sorular.forEach((s, i) => {
                    html += `<div style="margin-bottom:12px;padding:8px;background:#f8fafc;border-radius:6px;">
                        <strong>${i+1}. ${s.soru_metni}</strong><br>
                        A) ${s.secenek_a}<br>B) ${s.secenek_b}<br>C) ${s.secenek_c}<br>D) ${s.secenek_d}<br>
                        <span style="color:#22c55e;font-weight:bold;">✓ Doğru: ${s.dogru_cevap}</span>
                    </div>`;
                });
                addMessageToChat(html, 'ai');
            } else {
                addMessageToChat("❌ " + (data.error || "Soru üretilemedi"), 'ai');
            }
            return;
        }

        // Özet ve Anlat için ai_ozet endpoint'i
        const response = await fetch('islem.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                islem: 'ai_ozet',
                icerik: selectedNote.content,
                mod: type === 'anlat' ? 'anlat' : 'ozet'
            })
        });
        const data = await response.json();

        if (data.success) {
            addMessageToChat(data.sonuc.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>'), 'ai');

            // Sesli anlatım için TTS
            if (type === 'anlat') {
                const utterance = new SpeechSynthesisUtterance(data.sonuc.replace(/<[^>]*>/g, ''));
                utterance.lang = 'tr-TR';
                window.speechSynthesis.speak(utterance);
            }
        } else {
            addMessageToChat("❌ " + (data.error || "AI yanıt vermedi"), 'ai');
        }

    } catch (error) {
        addMessageToChat("❌ Sunucu hatası: " + error.message, 'ai');
    }
}


function deleteNote(noteId) {
    if (confirm("Bu notu tamamen silmek istediğinize emin misiniz? Bu işlem geri alınamaz!")) {
        fetch('islem.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                islem: 'not_sil', 
                note_id: noteId 
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Not başarıyla silindi!");
                location.reload(); 
            } else {
                alert("Hata: " + (data.error || "Silinemedi."));
            }
        })
        .catch(err => console.error("Silme hatası:", err));
    }
}




function filterNotes(category) {
    if (category === 'Tümü') {
        displayNotes(allNotes);
    } else {
        const filtered = allNotes.filter(n => n.category === category);
        displayNotes(filtered);
    }
}


function updateReaction(noteId, type) {
    fetch('islem.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify({ 
            islem: 'reaksiyon', 
            note_id: noteId, 
            tip: type 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const span = document.getElementById(`${type}-count-${noteId}`);
            span.innerText = data.new_count;
        } else {
            alert(data.error || "Bir hata oluştu");
        }
    })
    .catch(err => console.error("Bağlantı hatası:", err));
}
async function sendMessage() {
    const input = document.getElementById('aiInput');
    if(!input.value.trim()) return;

    const msg = input.value;
    input.value = '';

    addMessageToChat(msg, 'user');

    // Yazıyor göstergesi
    addMessageToChat('<i class="fas fa-spinner fa-spin"></i> AI düşünüyor...', 'ai');

    try {
        // session_id (sohbet geçmişi için)
        if (!window._aiSessionId) {
            window._aiSessionId = 'dersler-' + Date.now();
        }

        const response = await fetch('islem.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                islem: 'dersbotu',
                mesaj: msg,
                session_id: window._aiSessionId
            })
        });

        const data = await response.json();

        // Spinner mesajını kaldır
        const chat = document.getElementById('chatMessages');
        if (chat && chat.lastChild) chat.removeChild(chat.lastChild);

        if (data.success) {
            addMessageToChat(data.cevap.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>'), 'ai');
        } else {
            addMessageToChat("❌ " + (data.error || "AI yanıt vermedi"), 'ai');
        }

    } catch (error) {
        console.error("İletişim Hatası:", error);
        const chat = document.getElementById('chatMessages');
        if (chat && chat.lastChild) chat.removeChild(chat.lastChild);
        addMessageToChat("❌ Sunucu hatası: " + error.message, 'ai');
    }
}

function addMessageToChat(text, sender) {
    const chat = document.getElementById('chatMessages');
    const isUser = sender === 'user';
    chat.innerHTML += `
        <div class="flex ${isUser ? 'justify-end' : 'justify-start'}">
            <div class="${isUser ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-800'} p-4 rounded-2xl text-[13px] max-w-[85%] shadow-sm">
                ${text}
            </div>
        </div>`;
    chat.scrollTop = chat.scrollHeight;
}

function closeNote() {
    document.getElementById('noteModal').classList.add('hidden');
}
</script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>