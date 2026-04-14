<?php

session_start(); // Oturumu başlat (En üstte olmalı)
require_once 'baglan.php'; 

$noteId = isset($_GET['id']) ? intval($_GET['id']) : null;
$mevcutNot = null;

// Eğer URL'den bir ID geldiyse, notun bilgilerini veritabanından çek
if ($noteId) {
    $sorgu = $db->prepare("SELECT * FROM notes WHERE id = ?");
    $sorgu->execute([$noteId]);
    $mevcutNot = $sorgu->fetch(PDO::FETCH_ASSOC);
}


// İŞTE EKSİK OLAN VE SİSTEMİ ÇÖKERTEN SATIR BURASI:
$user_role = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'user';

// URL'den gelen ders adını alıyoruz (Örn: dersler.php?ders=Algoritmalar)
$secilenDers = isset($_GET['ders']) ? $_GET['ders'] : null;

try {
    if ($secilenDers) {
        // Ders seçildiyse SADECE o dersi getir
        $sorgu = $db->prepare("SELECT * FROM notes WHERE subject = ? ORDER BY id DESC");
        $sorgu->execute([$secilenDers]);
        $sayfaBaslik = htmlspecialchars($secilenDers) . " Notları";
    } else {
        // Ders seçilmediyse TÜMÜNÜ getir
        $sorgu = $db->prepare("SELECT * FROM notes ORDER BY id DESC");
        $sorgu->execute();
        $sayfaBaslik = "Tüm Notlar";
    }
    
    $notlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏗️</text></svg>">
    
   <title><?php echo $sayfaBaslik; ?> | NoteShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .serif-font { font-family: 'Merriweather', serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
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
            <a href="index.php" class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-chevron-left text-lg"></i></a>
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

        <div class="w-full lg:w-[380px] shrink-0">
            <div class="bg-white rounded-[2rem] shadow-2xl border border-slate-200 flex flex-col h-[700px] sticky top-28 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-white">
                    <h3 class="font-bold text-[15px] text-slate-900 tracking-tight">NoteShare AI</h3>
                </div>
                <div id="chatMessages" class="flex-1 p-8 overflow-y-auto space-y-6 no-scrollbar bg-[#FDFDFD]">
                    <div class="bg-slate-100/70 p-5 rounded-2xl text-[14px] text-slate-700 italic serif-font">
                        "Selam Mikail! Bir doküman seçtiğinde onu senin için özetleyebilirim."
                    </div>
                </div>
                <div class="p-6 bg-white border-t border-slate-100">
                    <form onsubmit="event.preventDefault(); sendMessage();" class="relative">
                        <input type="text" id="aiInput" placeholder="Soru sor..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 pl-6 pr-14 text-sm outline-none">
                        <button type="submit" class="absolute right-3 top-2.5 bg-indigo-600 text-white w-10 h-10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-arrow-up text-sm"></i>
                        </button>
                    </form>
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
</body>
<script>
// PHP'den gelen veriyi JS değişkenine aktarıyoruz
let allNotes = <?php echo $jsonNotes; ?>;
const currentUserRole = '<?php echo $user_role; ?>'; // PHP'deki admin bilgisini JS'ye aktardık

window.onload = () => {
    displayNotes(allNotes);
};

function displayNotes(notesList) {
    const tableBody = document.getElementById("noteTableBody");
    if (!tableBody) return;
    tableBody.innerHTML = ""; 

    // Önizleme kutusunu sayfadan seçiyoruz
    const previewBox = document.getElementById('hoverPreviewBox');
    let hideTimeout; // Kapanma süresini kontrol etmek için değişken

    notesList.forEach(note => {
        const dateStr = new Date(note.created_at).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long' });
        const noteRow = document.createElement("div");
        noteRow.className = "grid grid-cols-5 items-center px-6 py-6 hover:bg-indigo-50/30 transition-all group cursor-pointer border-b border-slate-100 relative";
        
        // 1. Satırın tamamına tıklama özelliği (Notu açma)
        noteRow.onclick = () => {
            window.location.href = `notlar.php?id=${note.id}`;
        };

        // --- 2. FARE ÜZERİNE İLK GELDİĞİ AN ---
        noteRow.onmouseenter = (e) => {
            if(!previewBox) return; 
            clearTimeout(hideTimeout); // Eğer daha önce gizlenmek üzereyse iptal et
            
            document.getElementById('previewTitle').innerText = note.title;
            document.getElementById('previewCategory').innerText = note.category || 'Genel';
            
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = note.content || 'Bu notun içeriği henüz boş...';
            const plainText = tempDiv.textContent || tempDiv.innerText || "";
            document.getElementById('previewContent').innerText = plainText.substring(0, 150) + '...';

            // KRİTİK: Kutu hiçbir şekilde farenin tıklamasını veya varlığını algılamasın
            previewBox.style.pointerEvents = 'none';

            // Kutunun pozisyonu
            previewBox.style.top = (e.clientY + 20) + 'px';
            previewBox.style.left = (e.clientX + 20) + 'px';

            previewBox.classList.remove('hidden');
            setTimeout(() => previewBox.classList.remove('opacity-0'), 10);
        };

        // --- 3. FARE SATIR İÇİNDE HAREKET ETTİKÇE (KUTU TAKİP ETSİN) ---
        noteRow.onmousemove = (e) => {
            if(!previewBox) return;
            // Fare hareket ettikçe kutuyu farenin ucundan ayırma
            previewBox.style.top = (e.clientY + 20) + 'px';
            previewBox.style.left = (e.clientX + 20) + 'px';
        };

        // --- 4. FARE SATIRDAN TAMAMEN ÇIKINCA ---
        noteRow.onmouseleave = () => {
            if(!previewBox) return;
            previewBox.classList.add('opacity-0');
            // Anında gizlemek yerine 200 milisaniye animasyonu bekle
            hideTimeout = setTimeout(() => previewBox.classList.add('hidden'), 200);
        };

        let adminButtons = '';
        if (typeof currentUserRole !== 'undefined' && currentUserRole === 'admin') {
            adminButtons = `<button onclick="event.stopPropagation(); deleteNote(${note.id})" class="text-[10px] font-extrabold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg hover:bg-red-600 hover:text-white transition-all uppercase ml-2">Sil</button>`;
        }

        noteRow.innerHTML = `
            <div class="min-w-0">
                <h4 class="font-bold text-slate-900 group-hover:text-indigo-600 text-[14px] truncate">${note.title}</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase">${note.category || 'Genel'}</p>
            </div>
            <div class="text-xs font-semibold text-slate-600 text-center">@${note.author || 'Anonim'}</div>
            <div class="text-[11px] font-medium text-slate-400 text-center">${dateStr}</div>
            
            <div class="flex items-center justify-center space-x-3">
                <button onclick="event.stopPropagation(); updateReaction(${note.id}, 'like')" class="flex items-center space-x-1 text-slate-400 hover:text-emerald-500"><i class="far fa-thumbs-up text-xs"></i><span id="like-count-${note.id}" class="text-[11px] font-bold">${note.likes || 0}</span></button>
                <button onclick="event.stopPropagation(); updateReaction(${note.id}, 'dislike')" class="flex items-center space-x-1 text-slate-400 hover:text-rose-500"><i class="far fa-thumbs-down text-xs"></i><span id="dislike-count-${note.id}" class="text-[11px] font-bold">${note.dislikes || 0}</span></button>
            </div>

            <div class="text-right flex justify-end">
                <button onclick="event.stopPropagation(); sendToAI(${note.id})" class="text-[10px] font-extrabold text-white bg-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-800 transition-all uppercase shadow-md">
                    Analiz Et
                </button>
                ${adminButtons}
            </div>
        `;
        tableBody.appendChild(noteRow);
    });
}

function sendToAI(noteId) {
    // Listeden ilgili notu bul
    const selectedNote = allNotes.find(n => n.id == noteId);
    if (!selectedNote) return;

    // Chat alanını temizle ve başlangıç mesajını ver
    const chat = document.getElementById('chatMessages');
    chat.innerHTML = `<div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl text-[13px] text-indigo-700 font-bold mb-4">
        "${selectedNote.title}" adlı dökümanı seçtiniz. Ne yapmak istersiniz?
    </div>`;

    // Seçenek butonlarını oluştur
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
    if (optionsDiv) optionsDiv.remove(); // Butonları gizle

    let prompt = "";
    if (type === 'anlat') prompt = `Aşağıdaki ders notunu bir öğretmen gibi sesli anlatıma uygun şekilde açıkla: ${selectedNote.content}`;
    if (type === 'ozet') prompt = `Aşağıdaki ders notunun en önemli yerlerini içeren kısa ve öz bir özet çıkar: ${selectedNote.content}`;
    if (type === 'soru') prompt = `Aşağıdaki ders notundan 20 adet çoktan seçmeli soru hazırla (Cevap anahtarı ile birlikte): ${selectedNote.content}`;

    addMessageToChat(type === 'anlat' ? "Sesli anlatım hazırla." : (type === 'ozet' ? "Özet çıkar." : "20 soru hazırla."), 'user');
    
    // Yükleniyor mesajı
    addMessageToChat("Hazırlıyorum, lütfen bekleyin...", 'ai');

    try {
        const response = await fetch('http://localhost:3000/askAI', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mesaj: prompt })
        });
        const data = await response.json();
        
        // Chat alanındaki son "Hazırlıyorum" mesajını silip cevabı yazdırabilirsin veya üstüne ekle:
        addMessageToChat(data.reply, 'ai');
        
        // Eğer 'anlat' seçildiyse tarayıcının seslendirme özelliğini kullanabiliriz
        if(type === 'anlat') {
            const utterance = new SpeechSynthesisUtterance(data.reply);
            utterance.lang = 'tr-TR';
            window.speechSynthesis.speak(utterance);
        }

    } catch (error) {
        addMessageToChat("Hata oluştu, Node.js sunucusunu kontrol et.", 'ai');
    }
}

// Yeni: Silme İşlemini Yapan Fonksiyon
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
                location.reload(); // Sayfayı yenileyerek notun kaybolmasını sağla
            } else {
                alert("Hata: " + (data.error || "Silinemedi."));
            }
        })
        .catch(err => console.error("Silme hatası:", err));
    }
}
// ... (Diğer filterNotes, updateReaction fonksiyonları aynı kalacak)



function filterNotes(category) {
    if (category === 'Tümü') {
        displayNotes(allNotes);
    } else {
        const filtered = allNotes.filter(n => n.category === category);
        displayNotes(filtered);
    }
}

// Tepki (like/dislike) sistemi güncellendi
function updateReaction(noteId, type) {
    fetch('islem.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }, // Veriyi JSON olarak gönderiyoruz
        body: JSON.stringify({ 
            islem: 'reaksiyon', 
            note_id: noteId, 
            tip: type 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // İşlem başarılıysa sayacı güncelle
            const span = document.getElementById(`${type}-count-${noteId}`);
            span.innerText = data.new_count;
        } else {
            // Hata varsa ekrana 'undefined' yerine gerçek hatayı yazdır
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
    
    // Kullanıcının mesajını ekrana yazdır
    addMessageToChat(msg, 'user');
    
    try {
        // Node.js sunucusundaki /askAI rotasına JSON olarak istek atıyoruz
        const response = await fetch('http://localhost:3000/askAI', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mesaj: msg })
        });

        const data = await response.json();
        
        // Yapay zekadan gelen cevabı ekrana yazdır
        addMessageToChat(data.reply, 'ai');

    } catch (error) {
        console.error("İletişim Hatası:", error);
        addMessageToChat("Sunucuya ulaşılamıyor. Lütfen Node.js sunucusunun açık olduğundan emin ol.", 'ai');
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
</body>
</html>