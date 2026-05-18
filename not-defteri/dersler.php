<?php 
// Veritabanı bağlantı dosyanı buraya dahil ediyoruz
include 'baglanti.php'; 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏗️</text></svg>">
    
    <title>Matematik Notları | notewarehouse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Editörden (Word tarzı) gelen içeriklerin AI panelinde düzgün formatlanması için */
        .ai-content b, .ai-content strong { font-weight: bold; color: #1e293b; }
        .ai-content ul { list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem; }
        .ai-content p { margin-bottom: 0.75rem; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-chevron-left"></i></a>
            <h1 class="font-bold text-xl flex items-center text-slate-700">
                <span class="bg-red-100 text-red-600 p-2 rounded-lg mr-3 shadow-inner"><i class="fas fa-plus-circle"></i></span>
                Matematik Notları
            </h1>
        </div>

        <div class="hidden md:flex items-center bg-slate-100 rounded-full px-4 py-2 w-96 border border-transparent focus-within:border-indigo-300 focus-within:bg-white transition-all">
            <i class="fas fa-search text-slate-400 mr-2"></i>
            <input type="text" placeholder="Bu ders içindeki notlarda ara..." class="bg-transparent outline-none text-sm w-full">
        </div>

        <div class="flex items-center space-x-4">
            <a href="notlar.php" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition flex items-center shadow-lg shadow-indigo-100">
                <i class="fas fa-plus mr-2"></i> Yeni Not Ekle
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
        
        <div class="flex-1">
            <div class="flex space-x-2 mb-6 overflow-x-auto pb-2 no-scrollbar">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded-full text-xs font-bold shadow-md">Tümü</button>
                <button class="bg-white text-slate-500 border px-4 py-2 rounded-full text-xs font-bold hover:border-indigo-400 transition">Konu Anlatımı</button>
                <button class="bg-white text-slate-500 border px-4 py-2 rounded-full text-xs font-bold hover:border-indigo-400 transition">Soru Çözümü</button>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/60 overflow-hidden border border-slate-100">
                <div class="grid grid-cols-4 bg-slate-50/80 p-6 border-b border-slate-100">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Not Başlığı</div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Paylaşan</div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Tarih</div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">İşlem</div>
                </div>

                <div class="divide-y divide-slate-50">
                    <?php
                    // Veritabanı sorgusu
                    try {
                        $sorgu = $db->query("SELECT * FROM notlar ORDER BY id DESC");
                        $notlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

                        if(count($notlar) > 0) {
                            foreach ($notlar as $not) { ?>
                                <div class="grid grid-cols-4 items-center p-6 hover:bg-slate-50/80 transition-all group cursor-pointer" 
                                     onclick="selectNote('<?php echo base64_encode($not['icerik']); ?>', '<?php echo htmlspecialchars($not['baslik']); ?>')">
                                    <div>
                                        <h4 class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars($not['baslik']); ?></h4>
                                        <span class="text-[10px] font-black text-indigo-500 uppercase tracking-tighter">Matematik</span>
                                    </div>
                                    <div class="text-sm font-medium text-slate-500 italic"><?php echo htmlspecialchars($not['paylasan']); ?></div>
                                    <div class="text-sm font-medium text-slate-400 text-center"><?php echo $not['tarih']; ?></div>
                                    <div class="text-right">
                                        <button class="text-[10px] font-black text-indigo-600 uppercase tracking-widest border-b-2 border-transparent hover:border-indigo-600 pb-1 transition-all">İncele & Özetle</button>
                                    </div>
                                </div>
                            <?php } 
                        } else {
                            echo '<div class="p-10 text-center text-slate-400 italic">Henüz bir not eklenmemiş. "Not Ekle" butonuna basarak başlayın!</div>';
                        }
                    } catch(Exception $e) {
                        echo '<div class="p-10 text-red-500 text-center font-bold">Veritabanı bağlantısı kurulamadı!</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-96">
            <div class="bg-white rounded-[2.5rem] shadow-2xl border border-indigo-50 flex flex-col h-[650px] sticky top-24 overflow-hidden">
                <div class="p-6 border-b border-indigo-50 bg-indigo-50/50 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-4 shadow-lg shadow-indigo-200">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-indigo-900 leading-tight">notewarehouse AI</h3>
                            <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">7/24 Aktif Yardımcı</p>
                        </div>
                    </div>
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                </div>

                <div id="chatMessages" class="flex-1 p-6 overflow-y-auto space-y-4 no-scrollbar">
                    <div class="bg-slate-100 p-4 rounded-3xl rounded-tl-none text-sm text-slate-600 leading-relaxed">
                        Selam! Soldaki listeden bir nota tıkla, senin için saniyeler içinde **özetini çıkartayım** veya sorularını cevaplayayım. 🚀
                    </div>
                </div>

                <div class="p-5 border-t border-slate-50 bg-white">
                    <form onsubmit="event.preventDefault(); sendMessage();" class="relative">
                        <input type="text" id="aiInput" placeholder="Not hakkında soru sor..." 
                               class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-4 pl-5 pr-14 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition-all shadow-inner">
                        <button type="submit" class="absolute right-2.5 top-2.5 bg-indigo-600 text-white w-10 h-10 rounded-xl hover:bg-indigo-700 transition active:scale-90 flex items-center justify-center shadow-lg shadow-indigo-200">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
let currentNoteContent = "";

// Nota tıklandığında AI'yı tetikleyen fonksiyon
function selectNote(encodedContent, title) {
    // Word/Editörden gelen karmaşık HTML kodlarını hatasız çözmek için base64 kullandık
    currentNoteContent = atob(encodedContent);

    const chat = document.getElementById('chatMessages');
    chat.innerHTML += `
        <div class="flex flex-col items-center my-4">
            <div class="h-[1px] w-full bg-slate-100 relative">
                <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white px-3 text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">
                    ${title} Seçildi
                </span>
            </div>
        </div>`;
    
    // Otomatik özet talebi
    document.getElementById('aiInput').value = "Bu ders notunu benim için en önemli noktalarıyla özetle.";
    sendMessage();
}

async function sendMessage() {
    const input = document.getElementById('aiInput');
    const chat = document.getElementById('chatMessages');
    const userText = input.value.trim();

    if(!userText) return;

    // Kullanıcı Baloncuğu
    chat.innerHTML += `
        <div class="flex justify-end">
            <div class="bg-indigo-600 text-white p-4 rounded-3xl rounded-tr-none text-sm shadow-xl shadow-indigo-100 max-w-[85%]">
                ${userText}
            </div>
        </div>`;
    
    chat.scrollTop = chat.scrollHeight;
    input.value = '';

    try {
        // server.js'deki Node.js sunucuna gönderim
        const response = await fetch("http://localhost:3000/summarize", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ 
                message: `Bağlam (Ders Notu): ${currentNoteContent} \n\n Soru: ${userText}` 
            })
        });

        const data = await response.json();
        
        // AI Baloncuğu
        chat.innerHTML += `
            <div class="flex justify-start">
                <div class="bg-white border border-slate-100 p-4 rounded-3xl rounded-tl-none text-sm text-slate-700 shadow-lg shadow-slate-100 max-w-[85%] ai-content">
                    ${data.reply}
                </div>
            </div>`;
    } catch (error) {
        chat.innerHTML += `<div class="text-center text-red-400 text-[10px] font-bold">Sunucuya bağlanılamadı! (Node.js açık mı?)</div>`;
    }
    
    chat.scrollTop = chat.scrollHeight;
}
</script>

</body>
</html>