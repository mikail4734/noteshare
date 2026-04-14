<?php
/**
 * NoteShare - Matematik Notları Portalı (PHP Versiyonu)
 */

// 1. Örnek Veri Seti (Gerçek uygulamada burası MySQL'den gelecek)
$notlar = [
    [
        'id' => 1,
        'baslik' => 'Türev ve Süreklilik Özeti',
        'etiket' => 'AYT Matematik',
        'etiket_renk' => 'text-indigo-500',
        'etiket_bg' => 'bg-indigo-100',
        'paylasan' => '@muhendis_adayi',
        'tarih' => '12 Mart 2026'
    ],
    [
        'id' => 2,
        'baslik' => 'Trigonometri Formülleri',
        'etiket' => '11. Sınıf',
        'etiket_renk' => 'text-green-500',
        'etiket_bg' => 'bg-green-100',
        'paylasan' => '@hoca_hanim',
        'tarih' => '10 Mart 2026'
    ],
    [
        'id' => 3,
        'baslik' => 'Logaritma Kuralları ve Soru Çözümü',
        'etiket' => 'AYT Matematik',
        'etiket_renk' => 'text-indigo-500',
        'etiket_bg' => 'bg-indigo-100',
        'paylasan' => '@sayisalci_01',
        'tarih' => '05 Mart 2026'
    ]
];

$toplamNot = 324; // Statik sayaç veya count($notlar)
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matematik Notları | NoteShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Kaydırma çubuğunu gizle ama işlevini koru */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="dersler.php" class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-chevron-left"></i></a>
            <h1 class="font-bold text-xl flex items-center">
                <span class="bg-red-100 text-red-600 p-2 rounded-lg mr-3"><i class="fas fa-plus-circle"></i></span>
                Matematik Notları
            </h1>
        </div>

        <div class="hidden md:flex items-center bg-slate-100 rounded-full px-4 py-2 w-96 border border-transparent focus-within:border-indigo-300 focus-within:bg-white transition-all">
            <i class="fas fa-search text-slate-400 mr-2"></i>
            <input type="text" placeholder="Bu ders içindeki notlarda ara..." class="bg-transparent outline-none text-sm w-full">
        </div>

        <div class="flex items-center space-x-4">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?php echo $toplamNot; ?> Not Paylaşıldı</span>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
        
        <div class="flex-1">
            <div class="flex space-x-2 mb-6 overflow-x-auto pb-2 no-scrollbar">
                <button class="bg-indigo-600 text-white px-4 py-1.5 rounded-full text-xs font-bold shadow-md shadow-indigo-100">Tümü</button>
                <button class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Konu Anlatımı</button>
                <button class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Soru Çözümü</button>
                <button class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Özetler</button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase">Not Başlığı</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase">Paylaşan</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase">Tarih</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($notlar as $not): ?>
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700 group-hover:text-indigo-600 transition"><?php echo $not['baslik']; ?></div>
                                <div class="text-[10px] <?php echo $not['etiket_renk']; ?> font-bold uppercase"><?php echo $not['etiket']; ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 italic"><?php echo $not['paylasan']; ?></td>
                            <td class="px-6 py-4 text-sm text-slate-400"><?php echo $not['tarih']; ?></td>
                            <td class="px-6 py-4">
                                <button onclick="selectNote('<?php echo addslashes($not['baslik']); ?>')" 
                                        class="bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg hover:bg-indigo-600 hover:text-white transition font-bold text-[10px] uppercase">
                                    İncele & Özetle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="w-full lg:w-96">
            <div class="bg-white rounded-3xl shadow-xl border border-indigo-100 flex flex-col h-[600px] sticky top-24">
                <div class="p-5 border-b border-indigo-50 bg-indigo-50/50 rounded-t-3xl flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-3 shadow-lg shadow-indigo-200">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-indigo-900 leading-tight">NoteShare AI</h3>
                            <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-tighter">Matematik Modeli v2.0</p>
                        </div>
                    </div>
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                </div>

                <div id="chatMessages" class="flex-1 p-6 overflow-y-auto space-y-4">
                    <div class="flex items-start space-x-2">
                        <div class="bg-slate-100 p-4 rounded-2xl rounded-tl-none text-xs leading-relaxed text-slate-600 border border-slate-200">
                            Selam! Matematik notlarını analiz etmemi ister misin? Özellikle takıldığın bir formül veya çözüm yöntemi varsa sormaktan çekinme. 🚀
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-b-3xl">
                    <div class="relative flex items-center">
                        <input type="text" id="aiInput" placeholder="Soru sor veya notu incelet..." 
                               class="w-full bg-white border border-slate-200 rounded-2xl py-3 pl-4 pr-12 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none shadow-sm transition-all">
                        <button onclick="sendMessage()" class="absolute right-2 bg-indigo-600 text-white w-9 h-9 rounded-xl hover:bg-indigo-700 transition flex items-center justify-center">
                            <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const chat = document.getElementById('chatMessages');

        function selectNote(noteName) {
            chat.innerHTML += `
                <div class="flex justify-end">
                    <div class="bg-indigo-600 text-white p-4 rounded-2xl rounded-tr-none text-xs max-w-[80%] shadow-lg shadow-indigo-100">
                        <p class="font-bold mb-1"><i class="fas fa-file-alt mr-2"></i> ${noteName}</p>
                        Bu notu benim için özetleyebilir misin?
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <div class="bg-slate-100 p-4 rounded-2xl rounded-tl-none text-xs text-slate-500 italic border border-slate-200">
                        <i class="fas fa-spinner animate-spin mr-2"></i> "${noteName}" içeriği analiz ediliyor...
                    </div>
                </div>
            `;
            scrollChat();
            
            // AI Cevap Simülasyonu
            setTimeout(() => {
                const lastMsg = chat.lastElementChild;
                lastMsg.innerHTML = `
                    <div class="bg-slate-100 p-4 rounded-2xl rounded-tl-none text-xs leading-relaxed text-slate-600 border border-slate-200">
                        <strong class="block text-indigo-600 mb-1">Analiz Tamamlandı:</strong>
                        Bu notta temel olarak 3 ana konu işlenmiş. İlgili formülleri senin için sadeleştirdim. Detaylara bakmak ister misin?
                    </div>
                `;
                scrollChat();
            }, 1500);
        }

        function sendMessage() {
            const input = document.getElementById('aiInput');
            if(!input.value.trim()) return;

            chat.innerHTML += `
                <div class="flex justify-end">
                    <div class="bg-indigo-600 text-white p-4 rounded-2xl rounded-tr-none text-xs max-w-[80%]">
                        ${input.value}
                    </div>
                </div>
            `;
            input.value = '';
            scrollChat();
        }

        function scrollChat() {
            chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });
        }
    </script>
</body>
</html>