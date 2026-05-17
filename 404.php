<?php
http_response_code(404);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/seo.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php seoMeta('Sayfa Bulunamadı (404)', 'Aradığınız sayfa mevcut değil. notewarehouse anasayfasına dönebilir veya not arayabilirsiniz.'); ?>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#6366f1 0%,#9333ea 50%,#ec4899 100%);min-height:100vh}
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        .floating { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="flex items-center justify-center p-6">

<div class="max-w-2xl w-full text-center">
    <div class="bg-white rounded-[2.5rem] shadow-2xl p-10 md:p-14">

        <div class="floating mb-6">
            <p class="text-9xl md:text-[10rem] font-black bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-none">404</p>
        </div>

        <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-3">Hop! Burada Bir Şey Yok 🤔</h1>
        <p class="text-slate-500 mb-8 text-lg">
            Aradığın sayfa silinmiş, taşınmış veya hiç var olmamış olabilir.
            Belki bir typo yaptın?
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center mb-8">
            <a href="/" class="bg-indigo-600 text-white px-8 py-4 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg">
                <i class="fas fa-home mr-2"></i> Anasayfaya Dön
            </a>
            <a href="/arama.php" class="bg-slate-100 text-slate-700 px-8 py-4 rounded-xl font-bold hover:bg-slate-200 transition">
                <i class="fas fa-search mr-2"></i> Notlarda Ara
            </a>
        </div>

        <!-- Hızlı Linkler -->
        <div class="border-t border-slate-100 pt-6">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Popüler Sayfalar</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <a href="/universite.php" class="bg-blue-50 text-blue-700 px-4 py-3 rounded-xl font-bold hover:bg-blue-100 transition">🎓 Üniversite</a>
                <a href="/lise.php" class="bg-red-50 text-red-700 px-4 py-3 rounded-xl font-bold hover:bg-red-100 transition">🏫 Lise</a>
                <a href="/ortaokul.php" class="bg-green-50 text-green-700 px-4 py-3 rounded-xl font-bold hover:bg-green-100 transition">🎒 Ortaokul</a>
                <a href="/ilkokul.php" class="bg-amber-50 text-amber-700 px-4 py-3 rounded-xl font-bold hover:bg-amber-100 transition">📜 İlkokul</a>
            </div>
        </div>

    </div>

    <p class="text-white/80 text-sm mt-6">
        <strong>notewarehouse</strong> · Bilgi paylaşılınca çoğalır
    </p>
</div>

</body>
</html>
