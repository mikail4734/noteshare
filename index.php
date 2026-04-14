<?php
/**
 * NoteShare - PHP Versiyonu
 */
// Giriş yapmış bir kullanıcı varsa durumunu kontrol et
if (isset($_SESSION['user_email'])) {
    $durum_sorgu = $db->prepare("SELECT durum FROM users WHERE email = ?");
    $durum_sorgu->execute([$_SESSION['user_email']]);
    $guncel_durum = $durum_sorgu->fetchColumn();

    if ($guncel_durum == 0) {
        // Eğer kullanıcı o an engellendiyse oturumu sonlandır ve kov
        session_destroy();
        header("Location: giris.php?hata=engellendiniz");
        exit;
    }
}

$profil_resmi = (isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture'])) ? $_SESSION['user_picture'] : 'https://ui-avatars.com/api/?name=User&background=random';
// Oturumu başlat (Profil resmini ve adını çekebilmek için şart)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dinamik Değişkenler
$site_title = "NoteShare | Not Deposu";
$current_year = date("Y");
$search_query = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';

// Eğitim Seviyeleri Verisi
$levels = [
    [
        'link' => './universite.php', 
        'icon' => '🎓', 
        'name' => 'Üniversite', 
        'color' => 'border-blue-500',
    ],
    [
        'link' => './lise.php', 
        'icon' => '🏫', 
        'name' => 'Lise', 
        'color' => 'border-red-500',
    ],
    [
        'link' => './ortaokul.php', 
        'icon' => '🎒', 
        'name' => 'Ortaokul', 
        'color' => 'border-green-500',
    ],
    [
        'link' => './ilkokul.php', 
        'icon' => '📜', 
        'name' => 'İlkokul', 
        'color' => 'border-amber-600',
    ]
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NoteShare, üniversite öğrencilerinin ders notlarını paylaştığı bir yardımlaşma platformudur.">
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏗️</text></svg>">
    
    <title><?php echo $site_title; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .search-focus:focus {
            background-color: white !important;
            color: #1e293b !important;
        }
    </style>
</head>
<body class="bg-gray-50">

<div id="adModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAd()"></div>

    <div class="relative bg-white rounded-[2rem] shadow-2xl max-w-md w-full mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="adContent">
        
        <button onclick="closeAd()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-black/20 hover:bg-red-500 text-white rounded-full transition-all z-10 backdrop-blur-md">
            <i class="fas fa-times"></i>
        </button>

        <img src="notwarehouse.jpg" alt="Kampanya" class="w-full h-48 object-cover">

        <div class="p-8 text-center bg-white">
            <span class="inline-block px-3 py-1 bg-indigo-100 text-indigo-600 font-black text-[10px] rounded-full mb-3 uppercase tracking-widest">Özel Kampanya</span>
            <h2 class="text-2xl font-black text-slate-800 mb-2">NoteShare Premium'a Geç!</h2>
            <p class="text-sm text-slate-500 mb-6 font-medium">Reklamsız deneyim, sınırsız soru indirme ve öncelikli destek için Premium'u keşfet. İlk ay %50 indirimli!</p>
            
            <div class="flex flex-col space-y-3">
                <a href="premium.php" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-indigo-200">
                    Hemen İncele
                </a>
                <button onclick="closeAd()" class="text-xs text-slate-400 hover:text-slate-600 font-bold transition">
                    Hayır, teşekkürler
                </button>
            </div>
        </div>
    </div>
</div>

<nav class="bg-[#4f46e5] px-8 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center space-x-6 flex-1">
        <div class="flex items-center space-x-2 cursor-pointer" onclick="window.location.href='index.php'">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-book-open text-white"></i>
            </div>
            <span class="text-white font-black tracking-tighter text-xl hidden sm:block">NoteShare</span>
        </div>

        <form action="arama.php" method="GET" class="relative w-full max-w-xs group flex items-center">
            <input type="text" name="q" id="searchInput" placeholder="Notlarda ara..." onkeyup="toggleX()"
                   value="<?php echo isset($search_query) ? $search_query : ''; ?>"
                   class="w-full pl-10 pr-10 py-2 rounded-full bg-white/10 border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all text-sm shadow-inner search-focus">
            
            <div class="absolute left-3 top-2.5 text-white/50 pointer-events-none">
                <i class="fas fa-search"></i>
            </div>

            <button type="button" id="clearBtn" onclick="clearInput()" 
                    class="absolute right-3 top-2.5 text-slate-400 hover:text-red-500 transition-all <?php echo (!empty($search_query)) ? '' : 'hidden'; ?>">
                <i class="fas fa-times-circle"></i>
            </button>
        </form>
    </div>

    <div class="flex items-center space-x-4">
        <a href="notlar.php" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl border border-white/30 text-sm font-bold transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Not Ekle
        </a>

        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <div class="flex items-center space-x-3 bg-white/10 p-1 pr-4 rounded-full border border-white/20">
               <img src="<?php echo $profil_resmi; ?>" alt="Profil" class="w-8 h-8 rounded-full border border-white/50 shadow-sm object-cover">
                <div class="flex flex-col">
                    

<a href="hesabım.php" class="text-white text-xs font-bold leading-none hover:text-indigo-200 hover:underline transition"><?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                    <a href="cikis.php" class="text-[10px] text-indigo-200 hover:text-white transition font-medium">Çıkış Yap</a>
                </div>
            </div>
        <?php else: ?>
            <a href="kaydol.php" class="bg-white text-[#4f46e5] px-5 py-2 rounded-xl text-sm font-black hover:bg-slate-100 transition shadow-md">
                Giriş / Kayıt
            </a>

        <?php endif; ?>
        <button onclick="toggleSidebar()" class="text-white hover:text-indigo-200 ml-2 text-2xl focus:outline-none transition-transform hover:scale-110">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>


<main class="container mx-auto py-16 px-6">
    <div class="text-center mb-12">
        <h2 class="text-4xl font-extrabold text-gray-800">Eğitim Seviyesi Seçimi Yapın</h2>
        <p class="text-gray-500 mt-2">Hangi alanda notlara göz atmak istersiniz?</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php foreach ($levels as $level): ?>
        <a href="<?php echo $level['link']; ?>" class="group bg-white p-10 rounded-3xl shadow-sm border-b-8 <?php echo $level['color']; ?> hover:shadow-2xl transition-all text-center">
            <div class="text-6xl group-hover:scale-110 transition-transform mb-4"><?php echo $level['icon']; ?></div>
            <h3 class="text-xl font-bold text-gray-700 uppercase"><?php echo $level['name']; ?></h3>
        </a>
        <?php endforeach; ?>
    </div>
</main>

<section class="bg-white mt-20 py-16 border-t border-gray-100">
    <div class="container mx-auto px-6">
        <div class="flex flex-wrap items-center">
            <div class="w-full md:w-1/2 mb-10 md:mb-0">
                <div class="relative">
                    <div class="absolute -top-4 -left-4 w-64 h-64 bg-indigo-100 rounded-full mix-blend-multiply filter blur-xl opacity-70"></div>
                    <div class="relative bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
                        <h3 class="text-5xl font-bold text-indigo-600 mb-4">10k+</h3>
                        <p class="text-gray-600 font-medium">Paylaşılan ders notu ve döküman ile Türkiye'nin en aktif öğrenci platformu.</p>
                        <div class="mt-6 flex -space-x-2">
                            <div class="w-10 h-10 rounded-full bg-blue-400 border-2 border-white"></div>
                            <div class="w-10 h-10 rounded-full bg-red-400 border-2 border-white"></div>
                            <div class="w-10 h-10 rounded-full bg-green-400 border-2 border-white"></div>
                            <div class="w-10 h-10 rounded-full bg-yellow-400 border-2 border-white text-xs flex items-center justify-center font-bold">+99</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-1/2 md:pl-12">
                <h2 class="text-3xl font-extrabold text-gray-800 mb-6 relative inline-block">
                    Hakkımızda
                    <span class="absolute bottom-0 left-0 w-1/2 h-1 bg-indigo-600"></span>
                </h2>
                <p class="text-gray-600 leading-relaxed mb-6">
                    <strong>NoteShare</strong>, öğrencilerin bilgiye daha hızlı ve ücretsiz bir şekilde ulaşması amacıyla kurulmuş bir yardımlaşma platformudur.
                </p>
            </div>
        </div>
    </div>
</section>

<footer class="bg-gray-900 text-gray-400 py-8 text-center text-sm">
    <p>&copy; <?php echo $current_year; ?> NoteShare - Tüm Hakları Saklıdır.</p>
    <div class="mt-2 space-x-4">
        <a href="kosullar.php" class="hover:text-white transition">Kullanım Koşulları</a>
        <a href="bizeUlasın.php" class="hover:text-white transition">Bize Ulaşın</a>
        <a href="sozlesme.php" class="hover:text-white transition">Sözleşme</a>
    </div>
</footer>
<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-[60] hidden backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>

<div id="sidebarMenu" class="fixed top-0 right-0 w-80 h-full bg-white shadow-2xl z-[70] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    
    <div class="p-6 bg-[#4f46e5] flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-2">
            <i class="fas fa-layer-group text-white text-xl"></i>
            <h2 class="text-white font-extrabold text-xl tracking-wide">Menü</h2>
        </div>
        <button onclick="toggleSidebar()" class="text-white hover:text-indigo-200 text-2xl focus:outline-none transition-transform hover:rotate-90">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-1">
        
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-3 mt-2">Keşfet</p>
        
        <a href="en_cok_begenilenler.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-fire text-orange-500 group-hover:scale-110 transition"></i></div>
            En Popüler Notlar
        </a>
        <a href="en-cok-paylasanlar.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-trophy text-yellow-500 group-hover:scale-110 transition"></i></div>
            En Çok Paylaşanlar
        </a>

        <div class="h-px bg-slate-100 my-4 mx-3"></div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-3">Kişisel</p>

        <a href="begendigim_notlar.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-heart text-red-500 group-hover:scale-110 transition"></i></div>
            Beğendiğim Notlar
        </a>
        <a href="ayarlar.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-cog text-slate-500 group-hover:rotate-90 transition duration-300"></i></div>
            Ayarlar
        </a>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
    <div class="mb-8">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 ml-2">Yönetim Paneli</p>
        <div class="space-y-1">
            <a href="kullanicilar.php" class="sidebar-item flex items-center px-4 py-3 rounded-xl text-sm font-bold transition-all text-slate-600 hover:text-red-600 hover:bg-red-50">
                <i class="fas fa-users-cog mr-3 w-5 text-center text-red-500"></i> Kullanıcı Yönetimi
            </a>
        </div>
    </div>
<?php endif; ?>

        <div class="h-px bg-slate-100 my-4 mx-3"></div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-3">Destek</p>

        <a href="sss.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-question-circle text-blue-500 group-hover:scale-110 transition"></i></div>
            Sıkça Sorulan Sorular
        </a>
        <a href="sikayet.php" class="flex items-center p-3 text-slate-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-exclamation-triangle text-red-500 group-hover:scale-110 transition"></i></div>
            Şikayet / Bildirim
        </a>
    </div>
</div>

<script>
    // Sayfa yüklendiğinde çalışacak fonksiyon
    document.addEventListener('DOMContentLoaded', function() {
        
        // Kullanıcı bu oturumda reklamı daha önce gördü mü kontrol et
        // (Eğer her sayfa yenilemede çıksın istersen if satırını silebilirsin)
        if (!sessionStorage.getItem('adShown')) {
            
            // Sayfa açıldıktan 1.5 saniye sonra reklamı göster (Daha doğal bir hissiyat verir)
            setTimeout(() => {
                const modal = document.getElementById('adModal');
                const content = document.getElementById('adContent');
                
                modal.classList.remove('hidden');
                
                // Küçük bir gecikme ile opacity ve scale animasyonlarını tetikle
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    content.classList.remove('scale-95');
                }, 50);
                
                // Reklamın gösterildiğini tarayıcı hafızasına kaydet
                sessionStorage.setItem('adShown', 'true');
            }, 1500); 
        }
    });

    // Çarpıya veya arka plana basınca reklamı kapatan fonksiyon
    function closeAd() {
        const modal = document.getElementById('adModal');
        const content = document.getElementById('adContent');
        
        // Önce animasyonla yavaşça kaybolmasını sağla
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        
        // Animasyon bitince (300ms sonra) tamamen gizle
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

function toggleSidebar() {
        const sidebar = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('sidebarOverlay');
        
        // Menüyü aç/kapat
        sidebar.classList.toggle('translate-x-full');
        
        // Arka plan karartmasını aç/kapat
        if (overlay.classList.contains('hidden')) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            document.body.style.overflow = 'hidden'; // Sayfanın arkada kaymasını engelle
        } else {
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
            document.body.style.overflow = ''; // Kaymayı geri aç
        }
    }

    const input = document.getElementById('searchInput');
    const btn = document.getElementById('clearBtn');

    function toggleX() {
        if (input.value.length > 0) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    }

    function clearInput() {
        input.value = "";
        btn.classList.add('hidden');
        input.focus();
    }
</script>

</body>
</html>