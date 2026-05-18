<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matematik Notları | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="html2.html" class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-chevron-left"></i></a>
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
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">324 Not Paylaşıldı</span>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
        
        <div class="flex-1">
            <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
              <a href="dersler.html">  <button class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Tümü</button></a>
                <a href="konuanlatımı.html"><button class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Konu Anlatımı</button></a>
                <a href="soruçozumu.html"><button class="bg-white text-slate-500 border px-4 py-1.5 rounded-full text-xs font-bold hover:border-indigo-400 transition">Soru Çözümü</button></a>
                <button class="bg-indigo-600 text-white px-4 py-1.5 rounded-full text-xs font-bold">Özetler</button>
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
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700">Türev ve Süreklilik Özeti</div>
                                <div class="text-[10px] text-indigo-500 font-bold uppercase">AYT Matematik</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 italic">@muhendis_adayı</td>
                            <td class="px-6 py-4 text-sm text-slate-400">12 Mart 2026</td>
                            <td class="px-6 py-4">
                                <button onclick="selectNote('Türev Özeti')" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-tighter">İncele & Özetle</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700">Trigonometri Formülleri</div>
                                <div class="text-[10px] text-green-500 font-bold uppercase">11. Sınıf</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 italic">@hoca_hanim</td>
                            <td class="px-6 py-4 text-sm text-slate-400">10 Mart 2026</td>
                            <td class="px-6 py-4">
                                <button onclick="selectNote('Trigonometri')" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-tighter">İncele & Özetle</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="w-full lg:w-96">
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

                <div id="chatMessages" class="flex-1 p-6 overflow-y-auto space-y-4">
                    <div class="bg-slate-100 p-3 rounded-2xl rounded-tl-none text-sm text-slate-600">
                        Selam! Uzun notları senin için **tek paragrafta** özetleyebilirim veya önemli maddeleri çıkartabilirim. 🚀
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100">
                    <div class="relative">
                        <input type="text" id="aiInput" placeholder="Neyi özetlememi istersin?" 
                               class="w-full bg-slate-50 border-none rounded-2xl py-3 pl-4 pr-12 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <button onclick="sendMessage()" class="absolute right-2 top-1.5 bg-indigo-600 text-white p-2 rounded-xl hover:bg-indigo-700 transition">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function selectNote(noteName) {
            const chat = document.getElementById('chatMessages');
            chat.innerHTML += `
                <div class="bg-indigo-600 text-white p-3 rounded-2xl rounded-tr-none text-sm ml-auto max-w-[80%]">
                    "${noteName}" notunun en önemli 5 maddesini çıkarır mısın?
                </div>
                <div class="bg-slate-100 p-3 rounded-2xl rounded-tl-none text-sm text-slate-600 italic">
                    <i class="fas fa-spinner animate-spin mr-2"></i> Önemli noktaları süzüyorum...
                </div>
            `;
            chat.scrollTop = chat.scrollHeight;
        }

        function sendMessage() {
            const input = document.getElementById('aiInput');
            if(!input.value) return;

            const chat = document.getElementById('chatMessages');
            chat.innerHTML += `
                <div class="bg-indigo-600 text-white p-3 rounded-2xl rounded-tr-none text-sm ml-auto max-w-[80%]">
                    ${input.value}
                </div>
            `;
            input.value = '';
            chat.scrollTop = chat.scrollHeight;
        }
    </script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>