<?php
session_start();
require_once 'baglan.php'; 

$user_role = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'user';

try {
  
    $sorgu = $db->prepare("SELECT * FROM notes WHERE likes >= 50 ORDER BY likes DESC");
    $sorgu->execute();
    $notlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
    $jsonNotes = json_encode($notlar);

} catch (PDOException $e) {
    $jsonNotes = json_encode([]);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>En Çok Beğenilenler | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-arrow-left text-lg"></i></a>
            <h1 class="font-extrabold text-2xl tracking-tight flex items-center text-slate-900">
                <span class="bg-orange-50 text-orange-500 p-2.5 rounded-xl mr-3 shadow-sm border border-orange-100">
                    <i class="fas fa-fire"></i>
                </span>
                Top 50+ Beğeni Kulübü
            </h1>
        </div>
        <div class="flex items-center space-x-4">
            <a href="notlar.php" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                <i class="fas fa-plus mr-2 text-xs"></i> Yeni Not Oluştur
            </a>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-10 max-w-6xl">
        
        <div class="relative mb-8">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 text-lg"></i>
            </div>
            <input type="text" id="searchInput" onkeyup="araNotlari()" placeholder="En çok beğenilen notlar arasında ara (Örn: Matematik, Olasılık...)" 
                   class="w-full pl-14 pr-6 py-5 bg-white border border-slate-200 rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] text-slate-700 font-medium focus:outline-none focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 transition-all text-lg placeholder-slate-300">
        </div>

        <div class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-slate-200">
            <div class="grid grid-cols-5 bg-slate-50/50 px-6 py-5 border-b border-slate-200">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Doküman & Sıra</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Yazar</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Tarih</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Beğeni Sayısı</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">İşlem</div>
            </div>

            <div id="noteTableBody" class="divide-y divide-slate-100">
                </div>
        </div>
    </main>

    <div id="hoverPreviewBox" class="fixed hidden bg-white/95 backdrop-blur-md rounded-2xl shadow-[0_20px_50px_rgba(79,70,229,0.2)] border border-indigo-100 p-5 w-80 z-[9999] pointer-events-none transition-opacity duration-200 opacity-0">
        <div class="flex items-center mb-2">
            <div class="w-2 h-2 rounded-full bg-indigo-500 mr-2 animate-pulse"></div>
            <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest" id="previewCategory">Kategori</span>
        </div>
        <h3 class="font-extrabold text-slate-800 text-[15px] mb-2 leading-tight" id="previewTitle">Başlık</h3>
        <div class="text-xs text-slate-500 font-medium leading-relaxed line-clamp-4" id="previewContent">İçerik yükleniyor...</div>
    </div>

<script>
let allNotes = <?php echo $jsonNotes; ?>;
const currentUserRole = '<?php echo $user_role; ?>';

window.onload = () => {
   
    ilkKontrol(allNotes);
};


function ilkKontrol(liste) {
    if(liste.length === 0) {
        document.getElementById("noteTableBody").innerHTML = `
            <div class="p-16 text-center text-slate-400 font-bold">
                <i class="fas fa-sad-tear text-5xl mb-4 opacity-50"></i>
                <p class="text-lg">Henüz 50 beğeniye ulaşan bir not bulunamadı.</p>
                <p class="text-xs mt-2 font-normal">İlk 50 beğeniye ulaşan yazar olmak için hemen içerik paylaş!</p>
            </div>
        `;
    } else {
        displayNotes(liste);
    }
}
function araNotlari() {
    const inputVal = document.getElementById('searchInput').value.toLowerCase();
    
    
    const filteredNotes = allNotes.filter(note => {
        return (note.title && note.title.toLowerCase().includes(inputVal)) || 
               (note.category && note.category.toLowerCase().includes(inputVal)) ||
               (note.author && note.author.toLowerCase().includes(inputVal));
    });

    displayNotes(filteredNotes);
}


function displayNotes(notesList) {
    const tableBody = document.getElementById("noteTableBody");
    if (!tableBody) return;
    tableBody.innerHTML = ""; 

    if(notesList.length === 0) {
        tableBody.innerHTML = `
            <div class="p-10 text-center text-slate-400 font-bold">
                <i class="fas fa-search text-4xl mb-4 opacity-50"></i>
                <p>Arama kriterlerine uygun not bulunamadı.</p>
            </div>
        `;
        return;
    }

    const previewBox = document.getElementById('hoverPreviewBox');
    let hideTimeout;

    notesList.forEach((note, index) => {
        const dateStr = new Date(note.created_at).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long' });
        const noteRow = document.createElement("div");
        noteRow.className = "grid grid-cols-5 items-center px-6 py-5 hover:bg-indigo-50/30 transition-all group cursor-pointer relative";
        
        noteRow.onclick = () => window.location.href = `notlar.php?id=${note.id}`;

        noteRow.onmouseenter = (e) => {
            if(!previewBox) return; 
            clearTimeout(hideTimeout);
            document.getElementById('previewTitle').innerText = note.title;
            document.getElementById('previewCategory').innerText = note.category || 'Genel';
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = note.content || 'Bu notun içeriği henüz boş...';
            document.getElementById('previewContent').innerText = (tempDiv.textContent || tempDiv.innerText || "").substring(0, 150) + '...';
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

        // Admin Silme Butonu
        let adminButtons = '';
        if (currentUserRole === 'admin') {
            adminButtons = `<button onclick="event.stopPropagation(); deleteNote(${note.id})" class="text-[10px] font-extrabold text-red-600 bg-red-50 px-3 py-2 rounded-lg hover:bg-red-600 hover:text-white transition-all uppercase ml-2">Sil</button>`;
        }

        // Sıralama (1. Tacı, 2. ve 3. Madalyası)
        let rankBadge = index === 0 ? '<i class="fas fa-crown text-yellow-400 mr-3 text-2xl drop-shadow-md"></i>' : 
                        index === 1 ? '<i class="fas fa-medal text-slate-400 mr-3 text-2xl drop-shadow-md"></i>' : 
                        index === 2 ? '<i class="fas fa-medal text-amber-600 mr-3 text-2xl drop-shadow-md"></i>' : 
                        `<span class="text-xs font-black text-slate-300 w-8 inline-block text-center mr-2">${index + 1}</span>`;

        noteRow.innerHTML = `
            <div class="min-w-0 flex items-center col-span-1">
                ${rankBadge}
                <div class="ml-1">
                    <h4 class="font-bold text-slate-900 group-hover:text-indigo-600 text-[15px] truncate">${note.title}</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">${note.category || 'Genel'}</p>
                </div>
            </div>
            <div class="text-xs font-bold text-slate-500 text-center">@${note.author || 'Anonim'}</div>
            <div class="text-[11px] font-medium text-slate-400 text-center">${dateStr}</div>
            
            <div class="flex items-center justify-center space-x-3">
                <div class="flex items-center space-x-1 text-red-500 bg-red-50 px-4 py-1.5 rounded-full border border-red-100 shadow-sm">
                    <i class="fas fa-fire text-sm mr-1"></i>
                    <span class="text-sm font-black">${note.likes || 0}</span>
                </div>
            </div>

            <div class="text-right flex justify-end">
                <button onclick="event.stopPropagation(); window.location.href='notlar.php?id=${note.id}'" class="text-[10px] font-extrabold text-indigo-600 bg-indigo-50 px-4 py-2 rounded-lg hover:bg-indigo-600 hover:text-white transition-all uppercase shadow-sm">
                    İncele
                </button>
                ${adminButtons}
            </div>
        `;
        tableBody.appendChild(noteRow);
    });
}
</script>
</body>
</html>