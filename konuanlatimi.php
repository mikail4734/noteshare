<?php

$notlar = [
    [
        'id' => 1,
        'baslik' => 'Türev ve Süreklilik Özeti',
        'kategori' => 'AYT Matematik',
        'yazar' => 'muhendis_adayi',
        'tarih' => '12 Mart 2026',
        'renk' => 'indigo'
    ],
    [
        'id' => 2,
        'baslik' => 'Trigonometri Formülleri',
        'kategori' => '11. Sınıf',
        'yazar' => 'hoca_hanim',
        'tarih' => '10 Mart 2026',
        'renk' => 'green'
    ],
    [
        'id' => 3,
        'baslik' => 'Logaritma Kuralları',
        'kategori' => 'AYT Matematik',
        'yazar' => 'mikail',
        'tarih' => '05 Nisan 2026',
        'renk' => 'indigo'
    ]
];

$toplamNot = 324; 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matematik Notları | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="dersler.php" class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-chevron-left"></i></a>
            <h1 class="font-bold text-xl flex items-center text-slate-900">
                <span class="bg-red-100 text-red-600 p-2 rounded-lg mr-3"><i class="fas fa-plus-circle"></i></span>
                Matematik Notları
            </h1>
        </div>

        <div class="hidden md:flex items-center bg-slate-100 rounded-full px-4 py-2 w-96 border border-transparent focus-within:border-indigo-300 focus-within:bg-white transition-all">
            <i class="fas fa-search text-slate-400 mr-2"></i>
            <input type="text" id="searchInput" placeholder="Bu ders içindeki notlarda ara..." class="bg-transparent outline-none text-sm w-full">
        </div>

        <div class="flex items-center space-x-4">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?php echo $toplamNot; ?> Not Paylaşıldı</span>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
        
        <div class="flex-1">
            <div class="flex space-x-2 mb-6 overflow-x-auto pb-2 no-scrollbar">
                <button onclick="filterType('Tümü')" class="bg-indigo-600 text-white px-4 py-1.5 rounded-full text-xs font-bold shadow-md shadow-indigo-100">Tümü</button>
                <button onclick="filterType('Konu Anlatımı')" class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Konu Anlatımı</button>
                <button onclick="filterType('Soru Çözümü')" class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Soru Çözümü</button>
                <button onclick="filterType('Özetler')" class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Özetler</button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Not Başlığı</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-wider text-center">Paylaşan</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-wider text-center">Tarih</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-wider text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($notlar as $not): ?>
                        <tr class="hover:bg-slate-50 transition group cursor-pointer" onclick="selectNote('<?php echo $not['baslik']; ?>')">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700 group-hover:text-indigo-600 transition"><?php echo $not['baslik']; ?></div>
                                <div class="text-[10px] text-<?php echo $not['renk']; ?>-500 font-bold uppercase"><?php echo $not['kategori']; ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 italic text-center">@<?php echo $not['yazar']; ?></td>
                            <td class="px-6 py-4 text-sm text-slate-400 text-center"><?php echo $not['tarih']; ?></td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-tighter bg-indigo-50 px-3 py-1.5 rounded-lg transition-all group-hover:bg-indigo-600 group-hover:text-white">
                                    İncele & Özetle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="w-full lg:w-96 shrink-0">
            <div class="bg-white rounded-3xl shadow-xl border border-indigo-100 flex flex-col h-[600px] sticky top-24">
                <div class="p-5 border-b border-indigo-50 bg-indigo-50 rounded-t-3xl flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white mr-3">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-indigo-900">notewarehouse AI</h3>
                            <p class="text-[10px] text-indigo-400 font-bold">Özetleyici & Yardımcı</p>
                        </div>
                    </div>
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                </div>

                <div id="chatMessages" class="flex-1 p-6 overflow-y-auto space-y-4 no-scrollbar">
                    <div class="bg-slate-100 p-3 rounded-2xl rounded-tl-none text-sm text-slate-600 shadow-sm border border-slate-200/50">
                        Selam! Bugün hangi konuyu hızlıca öğrenmek istersin? Tablodan bir not seçebilirsin. 🚀
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100">
                    <form onsubmit="event.preventDefault(); sendMessage();" class="relative">
                        <input type="text" id="aiInput" placeholder="Not hakkında soru sor..." 
                               class="w-full bg-slate-50 border-none rounded-2xl py-3 pl-4 pr-12 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <button type="submit" class="absolute right-2 top-1.5 bg-indigo-600 text-white p-2 rounded-xl hover:bg-indigo-700 transition shadow-md shadow-indigo-100">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        function selectNote(noteName) {
            const chat = document.getElementById('chatMessages');
            
           
            chat.innerHTML += `
                <div class="flex justify-end">
                    <div class="bg-indigo-600 text-white p-3 rounded-2xl rounded-tr-none text-sm max-w-[80%] shadow-lg shadow-indigo-100">
                        "${noteName}" notunu benim için özetleyebilir misin?
                    </div>
                </div>
            `;

           
            const loadingId = 'loading-' + Date.now();
            chat.innerHTML += `
                <div id="${loadingId}" class="bg-slate-50 p-3 rounded-2xl rounded-tl-none text-sm text-slate-600 italic border border-slate-100 shadow-sm">
                    <i class="fas fa-spinner animate-spin mr-2"></i> "${noteName}" dokümanı analiz ediliyor...
                </div>
            `;
            
            chat.scrollTop = chat.scrollHeight;

          
        }

        function sendMessage() {
            const input = document.getElementById('aiInput');
            const chat = document.getElementById('chatMessages');
            if(!input.value.trim()) return;

            chat.innerHTML += `
                <div class="flex justify-end">
                    <div class="bg-indigo-600 text-white p-3 rounded-2xl rounded-tr-none text-sm max-w-[80%] shadow-lg shadow-indigo-100">
                        ${input.value}
                    </div>
                </div>
            `;
            
            input.value = '';
            chat.scrollTop = chat.scrollHeight;
        }

        function filterType(type) {
        
            console.log("Filtrelenen kategori: " + type);
        }
    </script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>