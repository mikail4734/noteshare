<?php
session_start();
require_once 'baglan.php'; 

// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['user_email'])) {
    header("Location: giris.php");
    exit;
}

$user_email = $_SESSION['user_email'];
$user_role = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'user';

try {
    // Kullanıcının e-posta adresiyle eşleşen beğendiği notları en yeniden eskiye sıralayarak getir
    $sorgu = $db->prepare("
        SELECT notes.* FROM notes 
        INNER JOIN begeniler ON notes.id = begeniler.note_id 
        WHERE begeniler.kullanici_email = ? 
        ORDER BY begeniler.islem_tarihi DESC
    ");
    $sorgu->execute([$user_email]);
    $notlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
    $jsonNotes = json_encode($notlar);

} catch (PDOException $e) {
    // Eğer begeniler tablosu yoksa hata vermesin, boş döndürsün
    $jsonNotes = json_encode([]);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beğendiğim Notlar | NoteShare</title>
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
            <a href="index.php" class="text-slate-400 hover:text-rose-500 transition"><i class="fas fa-arrow-left text-lg"></i></a>
            <h1 class="font-extrabold text-2xl tracking-tight flex items-center text-slate-900">
                <span class="bg-rose-50 text-rose-500 p-2.5 rounded-xl mr-3 shadow-sm border border-rose-100">
                    <i class="fas fa-heart"></i>
                </span>
                Beğendiğim Notlar
            </h1>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-bold text-slate-500 bg-slate-100 px-4 py-2 rounded-xl">
                <i class="fas fa-user-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>
            </span>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-10 max-w-6xl">
        
        <div class="relative mb-8">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 text-lg"></i>
            </div>
            <input type="text" id="searchInput" onkeyup="araNotlari()" placeholder="Beğendiğin notlar arasında ara..." 
                   class="w-full pl-14 pr-6 py-5 bg-white border border-slate-200 rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] text-slate-700 font-medium focus:outline-none focus:ring-4 focus:ring-rose-50 focus:border-rose-400 transition-all text-lg placeholder-slate-300">
        </div>

        <div class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-slate-200">
            <div class="grid grid-cols-4 bg-slate-50/50 px-6 py-5 border-b border-slate-200">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider col-span-2">Doküman</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Yazar</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">İşlem</div>
            </div>

            <div id="noteTableBody" class="divide-y divide-slate-100"></div>
        </div>
    </main>

    <div id="hoverPreviewBox" class="fixed hidden bg-white/95 backdrop-blur-md rounded-2xl shadow-[0_20px_50px_rgba(244,63,94,0.15)] border border-rose-50 p-5 w-80 z-[9999] pointer-events-none transition-opacity duration-200 opacity-0">
        <h3 class="font-extrabold text-slate-800 text-[15px] mb-2 leading-tight" id="previewTitle">Başlık</h3>
        <div class="text-xs text-slate-500 font-medium leading-relaxed line-clamp-4" id="previewContent">İçerik yükleniyor...</div>
    </div>

<script>
let allNotes = <?php echo $jsonNotes; ?>;

window.onload = () => {
    ilkKontrol(allNotes);
};

function ilkKontrol(liste) {
    if(liste.length === 0) {
        document.getElementById("noteTableBody").innerHTML = `
            <div class="p-16 text-center text-slate-400 font-bold">
                <i class="far fa-heart text-5xl mb-4 opacity-50"></i>
                <p class="text-lg">Henüz hiçbir notu beğenmedin.</p>
                <p class="text-xs mt-2 font-normal">Faydalı bulduğun notları beğenerek kendi arşivini oluşturabilirsin.</p>
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
                <p>Arama kriterlerine uygun not bulunamadı.</p>
            </div>
        `;
        return;
    }

    const previewBox = document.getElementById('hoverPreviewBox');
    let hideTimeout;

    notesList.forEach(note => {
        const noteRow = document.createElement("div");
        noteRow.className = "grid grid-cols-4 items-center px-6 py-5 hover:bg-rose-50/30 transition-all group cursor-pointer relative";
        
        noteRow.onclick = () => window.location.href = `notlar.php?id=${note.id}`;

        noteRow.onmouseenter = (e) => {
            if(!previewBox) return; 
            clearTimeout(hideTimeout);
            document.getElementById('previewTitle').innerText = note.title;
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

        noteRow.innerHTML = `
            <div class="min-w-0 flex items-center col-span-2">
                <i class="fas fa-heart text-rose-500 mr-4 text-xl drop-shadow-sm"></i>
                <div>
                    <h4 class="font-bold text-slate-900 group-hover:text-rose-600 text-[15px] truncate">${note.title}</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">${note.category || 'Genel'}</p>
                </div>
            </div>
            <div class="text-xs font-bold text-slate-500 text-center">@${note.author || 'Anonim'}</div>
            
            <div class="text-right flex justify-end">
                <button onclick="event.stopPropagation(); window.location.href='notlar.php?id=${note.id}'" class="text-[10px] font-extrabold text-rose-600 bg-rose-50 px-4 py-2 rounded-lg hover:bg-rose-600 hover:text-white transition-all uppercase shadow-sm">
                    İncele
                </button>
            </div>
        `;
        tableBody.appendChild(noteRow);
    });
}
</script>
</body>
</html>