<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once 'baglan.php';


if (isset($_SESSION['user_email'])) {
    $ban_sorgu = $db->prepare("SELECT durum FROM users WHERE email = ?");
    $ban_sorgu->execute([$_SESSION['user_email']]);
    if ($ban_sorgu->fetchColumn() == 0) {
        session_destroy();
        header("Location: giris.php?hata=engellendiniz");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sıkça Sorulan Sorular | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, padding 0.3s ease; }
        .faq-item.active .faq-answer { max-height: 200px; padding-top: 1rem; }
        .faq-item.active .faq-icon { transform: rotate(180deg); color: #4f46e5; }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-arrow-left text-lg"></i></a>
            <h1 class="font-extrabold text-2xl tracking-tight flex items-center text-slate-900">
                <span class="bg-indigo-50 text-indigo-500 p-2.5 rounded-xl mr-3 shadow-sm border border-indigo-100">
                    <i class="fas fa-question-circle"></i>
                </span>
                Yardım Merkezi
            </h1>
        </div>
        <div class="text-sm font-bold text-slate-400 uppercase tracking-widest">Sıkça Sorulan Sorular</div>
    </nav>

    <main class="container mx-auto px-6 py-16 max-w-3xl">
        
        <div class="text-center mb-12">
            <h2 class="text-4xl font-black text-slate-900 mb-4">Size nasıl yardımcı olabiliriz?</h2>
            <p class="text-slate-500 font-medium">notewarehouse kullanımı, not paylaşımı ve üyelik hakkında en çok sorulan soruları aşağıda bulabilirsiniz.</p>
        </div>

        <div class="space-y-4">
            
            <div class="faq-item bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-indigo-200 shadow-sm">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-bold text-slate-700 text-lg">notewarehouse nedir ve nasıl çalışır?</span>
                    <i class="fas fa-chevron-down faq-icon transition-transform text-slate-300"></i>
                </button>
                <div class="faq-answer px-6 pb-6 text-slate-500 leading-relaxed text-sm">
                    notewarehouse, üniversite ve lise öğrencilerinin ders notlarını paylaştığı, birbirlerine yardımcı olduğu sosyal bir platformdur. Google hesabınızla giriş yaparak notlarınızı yükleyebilir, beğendiğiniz notları kendi arşivinize ekleyebilirsiniz.
                </div>
            </div>

            <div class="faq-item bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-indigo-200 shadow-sm">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-bold text-slate-700 text-lg">Notlarımı nasıl paylaşabilirim?</span>
                    <i class="fas fa-chevron-down faq-icon transition-transform text-slate-300"></i>
                </button>
                <div class="faq-answer px-6 pb-6 text-slate-500 leading-relaxed text-sm">
                    Üst menüde bulunan "Not Oluştur" butonuna basarak; başlık, kategori ve ders bilgisini girip notunuzu paylaşabilirsiniz. Paylaştığınız notlar "Tüm Notlar" listesinde anında görünür hale gelecektir.
                </div>
            </div>

            <div class="faq-item bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-indigo-200 shadow-sm">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-bold text-slate-700 text-lg">Hangi notlar "En Popüler" listesine girer?</span>
                    <i class="fas fa-chevron-down faq-icon transition-transform text-slate-300"></i>
                </button>
                <div class="faq-answer px-6 pb-6 text-slate-500 leading-relaxed text-sm">
                    Platformdaki diğer kullanıcılar tarafından en az 50 beğeni almış olan notlar otomatik olarak "En Popüler Notlar" listesine dahil edilir. Bu listedeki notlar kalite açısından topluluk tarafından onaylanmış sayılır.
                </div>
            </div>

            <div class="faq-item bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-indigo-200 shadow-sm">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-bold text-slate-700 text-lg">notewarehouse AI özelliğini nasıl kullanırım?</span>
                    <i class="fas fa-chevron-down faq-icon transition-transform text-slate-300"></i>
                </button>
                <div class="faq-answer px-6 pb-6 text-slate-500 leading-relaxed text-sm">
                    Okuduğunuz notun yanındaki "Analiz Et" butonuna basarak notewarehouse AI'ya ulaşabilirsiniz. Yapay zeka, seçtiğiniz notu sizin için özetler, anahtar kelimeleri çıkarır ve çalışma planı hazırlar.
                </div>
            </div>

            <div class="faq-item bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-indigo-200 shadow-sm">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-bold text-slate-700 text-lg">Hesabım neden engellenebilir?</span>
                    <i class="fas fa-chevron-down faq-icon transition-transform text-slate-300"></i>
                </button>
                <div class="faq-answer px-6 pb-6 text-slate-500 leading-relaxed text-sm">
                    Topluluk kurallarını ihlal eden, uygunsuz içerik paylaşan veya spam yapan kullanıcılar yöneticiler (admin) tarafından engellenebilir. Engellenen kullanıcılar siteye giriş yapamaz ve tüm beğeni verileri silinir.
                </div>
            </div>

        </div>

        <div class="mt-16 bg-indigo-600 rounded-[2rem] p-10 text-center shadow-xl shadow-indigo-100">
            <h3 class="text-2xl font-black text-white mb-2">Başka bir sorunuz mu var?</h3>
            <p class="text-indigo-100 mb-6 font-medium">Bize her zaman Şikayet / Bildirim kısmından ulaşabilirsiniz.</p>
            <a href="destek.php" class="inline-block bg-white text-indigo-600 px-10 py-4 rounded-2xl font-bold hover:bg-slate-50 transition-all shadow-lg">
                Destek Talebi Oluştur
            </a>
        </div>
    </main>

    <script>
        function toggleFaq(btn) {
            const item = btn.parentElement;
            
          
            document.querySelectorAll('.faq-item').forEach(el => {
                if (el !== item) el.classList.remove('active');
            });

           
            item.classList.toggle('active');
        }
    </script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>