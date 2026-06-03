<?php
// Oturum ve veritabanı bağlantısı en başta
require_once __DIR__ . '/oturum_baslat.php';
require_once 'baglan.php';
require_once 'helpers.php';

// Her giriş yapan kullanıcının streak'ini güncelle
if (isset($_SESSION['user_email'])) {
    streakGuncelle($db, $_SESSION['user_email']);
}

// Kullanıcı bilgilerini çek
$kullaniciVerisi = null;
if (isset($_SESSION['user_email'])) {
    try {
        $st = $db->prepare("SELECT xp, seviye, streak FROM users WHERE email = ?");
        $st->execute([$_SESSION['user_email']]);
        $kullaniciVerisi = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

// Engellenmiş kullanıcı kontrolü
if (isset($_SESSION['user_email'])) {
    try {
        $durum_sorgu = $db->prepare("SELECT durum FROM users WHERE email = ?");
        $durum_sorgu->execute([$_SESSION['user_email']]);
        $guncel_durum = $durum_sorgu->fetchColumn();

        if ($guncel_durum !== false && $guncel_durum == 0) {
            session_destroy();
            header("Location: giris.php?hata=engellendiniz");
            exit;
        }
    } catch (PDOException $e) { /* sessiz geç */ }
}

$profil_resmi = (isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture']))
    ? $_SESSION['user_picture']
    : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name'] ?? 'User') . '&background=4f46e5&color=fff';

// Okunmamış bildirim sayısı
$okunmamisBildirim = 0;
if (isset($_SESSION['user_email'])) {
    try {
        $s = $db->prepare("SELECT COUNT(*) FROM bildirimler WHERE kullanici_email = ? AND okundu = 0");
        $s->execute([$_SESSION['user_email']]);
        $okunmamisBildirim = (int)$s->fetchColumn();
    } catch (Exception $e) {}
}

// Aktif site duyurusu
$siteDuyurusu = null;
try {
    $siteDuyurusu = $db->query("SELECT * FROM site_duyurulari WHERE aktif=1 ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$site_title = "notewarehouse | Not Deposu";
$current_year = date("Y");
$search_query = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';


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

    <!-- ANA SEO -->
    <title>notewarehouse - Ücretsiz Ders Notu Paylaşım Platformu | Üniversite, Lise, Ortaokul Notları</title>
    <meta name="description" content="notewarehouse; üniversite, lise, ortaokul ve ilkokul öğrencilerinin ders notlarını ücretsiz paylaştığı, yapay zeka destekli not deposu ve test çözüm platformudur. AYT, TYT, LGS, üniversite ders notları, çıkmış sorular ve özetler.">
    <meta name="keywords" content="notewarehouse, not paylaşımı, ders notları, ücretsiz not, üniversite notları, lise notları, ortaokul notları, AYT notları, TYT notları, LGS notları, ders özeti, konu anlatımı, soru çözümü, online not deposu, öğrenci notu, yapay zeka ders, bilgisayar mühendisliği notları, matematik notları, fizik notları">
    <meta name="author" content="notewarehouse - Mikail Çelik & Mustafa Kabataş">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="googlebot" content="index, follow">
    <meta name="language" content="Turkish">
    <meta name="revisit-after" content="3 days">

    <!-- CANONICAL -->
    <link rel="canonical" href="https://notewarehouse.com/">

    <!-- OPEN GRAPH (WhatsApp, Facebook, LinkedIn) -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="notewarehouse">
    <meta property="og:title" content="notewarehouse - Türkiye'nin Ücretsiz Ders Notu Paylaşım Platformu">
    <meta property="og:description" content="20.000+ ders notu, AI destekli özet/anlatım, çoktan seçmeli test çözücü, çalışma grupları ve canlı sınav simülasyonları. Ücretsiz kaydol, hemen başla!">
    <meta property="og:url" content="https://notewarehouse.com/">
    <meta property="og:image" content="https://notewarehouse.com/og-image.png">
    <meta property="og:locale" content="tr_TR">

    <!-- TWITTER CARD -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="notewarehouse - Ücretsiz Ders Notu Paylaşım Platformu">
    <meta name="twitter:description" content="Üniversite, lise, ortaokul notları + AI özet + test çözücü + canlı sınav. Hemen ücretsiz kaydol!">
    <meta name="twitter:image" content="https://notewarehouse.com/og-image.png">

    <!-- Eski emoji favicon kaldırıldı, yeni profesyonel favicon yukarıda -->


    <!-- JSON-LD Structured Data (Google için zengin sonuçlar) -->
    <!-- JSON-LD: WebSite -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "notewarehouse",
      "alternateName": ["notewarehouse.com", "Not Deposu"],
      "url": "https://notewarehouse.com/",
      "description": "Türkiye'nin ücretsiz ders notu paylaşım platformu",
      "inLanguage": "tr-TR",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://notewarehouse.com/arama.php?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>

    <!-- JSON-LD: Organization -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "EducationalOrganization",
      "name": "notewarehouse",
      "url": "https://notewarehouse.com/",
      "logo": "https://notewarehouse.com/logo.png",
      "description": "Öğrenci ders notu paylaşım platformu",
      "sameAs": [
        "https://instagram.com/note_warehouse/",
        "https://www.facebook.com/profile.php?id=61590216140180"
      ]
    }
    </script>

    <!-- JSON-LD: FAQ (Google'da SSS kutusu) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "notewarehouse nedir?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "notewarehouse, öğrencilerin ücretsiz ders notu paylaştığı, yapay zeka destekli not deposu ve test çözücü platformudur. İlkokul, ortaokul, lise ve üniversite seviyelerinde 20+ kategori sunar."
          }
        },
        {
          "@type": "Question",
          "name": "notewarehouse ücretsiz mi?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Evet, notewarehouse tamamen ücretsizdir. Kayıt olmak, not paylaşmak, not okumak, AI özelliklerini kullanmak — hepsi ücretsizdir."
          }
        },
        {
          "@type": "Question",
          "name": "Notlarımı kimler görür?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Paylaştığın notları tüm kayıtlı kullanıcılar görebilir. Sadece çalışma grubuna özel notlar oluşturduğunda yalnızca grup üyeleri görür."
          }
        },
        {
          "@type": "Question",
          "name": "AI özet ve soru üretme nasıl çalışır?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Anthropic Claude AI ile entegre olan notewarehouse, notunun anahtar noktalarını özetler, öğretmen gibi anlatım yapar ve otomatik çoktan seçmeli test soruları üretir."
          }
        }
      ]
    }
    </script>

    <!-- Favicon (profesyonel) -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <link rel="shortcut icon" href="/favicon-32.png" type="image/png">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .search-focus:focus {
            background-color: white !important;
            color: #1e293b !important;
        }
        /* DARK MODE */
        html.dark body { background: #0f172a !important; color: #e2e8f0 !important; }
        html.dark .bg-white, html.dark .bg-gray-50, html.dark .bg-slate-50, html.dark .bg-slate-100 { background: #1e293b !important; color: #e2e8f0 !important; }
        html.dark .bg-slate-50\/50 { background: #1e293b !important; }
        html.dark .border-gray-100, html.dark .border-slate-100, html.dark .border-slate-200 { border-color: #334155 !important; }
        html.dark .text-gray-800, html.dark .text-slate-700, html.dark .text-slate-800, html.dark .text-slate-900 { color: #e2e8f0 !important; }
        html.dark .text-gray-500, html.dark .text-slate-400, html.dark .text-slate-500 { color: #94a3b8 !important; }
        html.dark .text-gray-400 { color: #64748b !important; }
        html.dark footer { background: #020617 !important; }
    </style>
    <script>
    // Dark mode başlangıç
    (function() {
        const tema = localStorage.getItem('tema') || 'light';
        if (tema === 'dark') document.documentElement.classList.add('dark');
    })();
    function temaDegistir() {
        const yeni = document.documentElement.classList.toggle('dark') ? 'dark' : 'light';
        localStorage.setItem('tema', yeni);
        const ikon = document.getElementById('temaIkon');
        if (ikon) ikon.className = yeni === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    window.addEventListener('DOMContentLoaded', () => {
        const ikon = document.getElementById('temaIkon');
        if (ikon && document.documentElement.classList.contains('dark')) ikon.className = 'fas fa-sun';
    });
    </script>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
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
            <h2 class="text-2xl font-black text-slate-800 mb-2">notewarehouse Premium'a Geç!</h2>
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
            <span class="text-white font-black tracking-tighter text-xl hidden sm:block">notewarehouse</span>
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
            <?php if ($kullaniciVerisi): ?>
                <!-- Streak -->
                <a href="liderlik.php" title="<?= $kullaniciVerisi['streak'] ?> gündür üst üste" class="hidden md:flex items-center bg-white/10 hover:bg-white/20 text-white px-3 h-10 rounded-xl border border-white/30 transition text-xs font-bold">
                    <span class="mr-1">🔥</span> <?= $kullaniciVerisi['streak'] ?>
                </a>
                <!-- XP & Seviye -->
                <a href="rozetlerim.php" title="Seviye <?= $kullaniciVerisi['seviye'] ?> · <?= $kullaniciVerisi['xp'] ?> XP" class="hidden md:flex items-center bg-white/10 hover:bg-white/20 text-white px-3 h-10 rounded-xl border border-white/30 transition text-xs font-bold">
                    <span class="mr-1">⭐</span> Lv<?= $kullaniciVerisi['seviye'] ?>
                </a>
            <?php endif; ?>

            <!-- Dark mode -->
            <button onclick="temaDegistir()" id="temaBtn" title="Tema" class="bg-white/10 hover:bg-white/20 text-white w-10 h-10 rounded-xl border border-white/30 flex items-center justify-center transition">
                <i id="temaIkon" class="fas fa-moon"></i>
            </button>

            <a href="bildirimlerim.php" title="Bildirimlerim" class="relative bg-white/10 hover:bg-white/20 text-white w-10 h-10 rounded-xl border border-white/30 flex items-center justify-center transition">
                <i class="fas fa-bell"></i>
                <?php if ($okunmamisBildirim > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-black rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 border-2 border-[#4f46e5]">
                        <?= $okunmamisBildirim > 9 ? '9+' : $okunmamisBildirim ?>
                    </span>
                <?php endif; ?>
            </a>
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


<?php if ($siteDuyurusu):
    $renkler = ['info' => 'amber', 'warning' => 'rose', 'success' => 'emerald'];
    $r = $renkler[$siteDuyurusu['tip']] ?? 'amber';
?>
<div class="bg-<?= $r ?>-50 border-b-2 border-<?= $r ?>-200 px-6 py-3 text-center">
    <p class="text-sm font-bold text-<?= $r ?>-700">
        <i class="fas fa-bullhorn mr-2"></i> <?= htmlspecialchars($siteDuyurusu['mesaj']) ?>
    </p>
</div>
<?php endif; ?>

<main class="py-16 px-6">

    <!-- ═══ EĞİTİM SEVİYESİ ═══ -->
    <div class="container mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-gray-800">Eğitim Seviyesi Seçimi Yapın</h1>
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
    </div>

    <!-- ═══ DİL SEVİYESİ ═══ -->
    <?php
    $diller = [
        ['link' => 'dil.php?dil=ingilizce', 'bayrak' => '🇬🇧', 'ad' => 'İngilizce',  'renk' => 'border-blue-500',   'bg' => 'from-blue-50 to-blue-100'],
        ['link' => 'dil.php?dil=almanca',   'bayrak' => '🇩🇪', 'ad' => 'Almanca',    'renk' => 'border-yellow-500', 'bg' => 'from-yellow-50 to-yellow-100'],
        ['link' => 'dil.php?dil=fransizca', 'bayrak' => '🇫🇷', 'ad' => 'Fransızca',  'renk' => 'border-red-500',    'bg' => 'from-red-50 to-red-100'],
        ['link' => 'dil.php?dil=arapca',    'bayrak' => '🇸🇦', 'ad' => 'Arapça',     'renk' => 'border-green-600',  'bg' => 'from-green-50 to-green-100'],
        ['link' => 'dil.php?dil=cince',     'bayrak' => '🇨🇳', 'ad' => 'Çince',      'renk' => 'border-rose-500',   'bg' => 'from-rose-50 to-rose-100'],
        ['link' => 'dil.php?dil=ispanyolca','bayrak' => '🇪🇸', 'ad' => 'İspanyolca', 'renk' => 'border-orange-500', 'bg' => 'from-orange-50 to-orange-100'],
        ['link' => 'dil.php?dil=italyanca', 'bayrak' => '🇮🇹', 'ad' => 'İtalyanca',  'renk' => 'border-emerald-500','bg' => 'from-emerald-50 to-emerald-100'],
        ['link' => 'dil.php?dil=rusca',     'bayrak' => '🇷🇺', 'ad' => 'Rusça',      'renk' => 'border-indigo-500', 'bg' => 'from-indigo-50 to-indigo-100'],
    ];
    ?>
    <div class="mt-20">
        <div class="container mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-800">🌍 Dil Seviyesi Seçin</h2>
                    <p class="text-gray-500 mt-1">Öğrenmek istediğin dili seç, seviyene göre notları incele</p>
                </div>
                <button onclick="scrollRow('dilRow','sag')" class="hidden sm:flex items-center bg-white border border-gray-200 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 shadow-sm transition">
                    Tümü <i class="fas fa-chevron-right ml-2"></i>
                </button>
            </div>
        </div>
        <!-- Kaydırmalı satır -->
        <div class="relative">
            <button onclick="scrollRow('dilRow','sol')" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition border border-gray-100 hidden sm:flex">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
            <div id="dilRow" class="flex gap-5 overflow-x-auto scroll-smooth pb-4 px-6 scrollbar-hide"
                 style="scrollbar-width:none; -ms-overflow-style:none;">
                <?php foreach ($diller as $dil): ?>
                <a href="<?= $dil['link'] ?>"
                   class="group flex-none w-40 bg-gradient-to-b <?= $dil['bg'] ?> p-6 rounded-3xl shadow-sm border-b-8 <?= $dil['renk'] ?> hover:shadow-xl transition-all text-center">
                    <div class="text-5xl mb-3 group-hover:scale-110 transition-transform"><?= $dil['bayrak'] ?></div>
                    <h3 class="text-base font-bold text-gray-700"><?= $dil['ad'] ?></h3>
                    <p class="text-xs text-gray-400 mt-1 font-medium">A1 → C2</p>
                </a>
                <?php endforeach; ?>
            </div>
            <button onclick="scrollRow('dilRow','sag')" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition border border-gray-100 hidden sm:flex">
                <i class="fas fa-chevron-right text-sm"></i>
            </button>
        </div>
    </div>

    <!-- ═══ SINAVLARA HAZIRLIK ═══ -->
    <?php
    $sinavlar = [
        ['link'=>'sinav.php?sinav=yks',  'icon'=>'🎯', 'ad'=>'YKS',   'alt'=>'TYT · AYT · YDT',          'renk'=>'border-indigo-600', 'bg'=>'from-indigo-50 to-indigo-100'],
        ['link'=>'sinav.php?sinav=lgs',  'icon'=>'📐', 'ad'=>'LGS',   'alt'=>'8. Sınıf Merkezi Sınav',    'renk'=>'border-blue-500',   'bg'=>'from-blue-50 to-blue-100'],
        ['link'=>'sinav.php?sinav=dgs',  'icon'=>'🏛️', 'ad'=>'DGS',   'alt'=>'Dikey Geçiş Sınavı',        'renk'=>'border-purple-500', 'bg'=>'from-purple-50 to-purple-100'],
        ['link'=>'sinav.php?sinav=kpss', 'icon'=>'🏛️', 'ad'=>'KPSS',  'alt'=>'Kamu Personeli Seçme',      'renk'=>'border-amber-500',  'bg'=>'from-amber-50 to-amber-100'],
        ['link'=>'sinav.php?sinav=yds',  'icon'=>'🌐', 'ad'=>'YDS',   'alt'=>'Yabancı Dil Sınavı',        'renk'=>'border-emerald-500','bg'=>'from-emerald-50 to-emerald-100'],
        ['link'=>'sinav.php?sinav=ales', 'icon'=>'🎓', 'ad'=>'ALES',  'alt'=>'Akademik Lisan. Sınavı',    'renk'=>'border-rose-500',   'bg'=>'from-rose-50 to-rose-100'],
        ['link'=>'sinav.php?sinav=ekys', 'icon'=>'⚖️', 'ad'=>'EKYS',  'alt'=>'Engelli Kamu Personeli',    'renk'=>'border-teal-500',   'bg'=>'from-teal-50 to-teal-100'],
        ['link'=>'sinav.php?sinav=meb',  'icon'=>'📚', 'ad'=>'MEB',   'alt'=>'Öğretmenlik Sınavları',     'renk'=>'border-orange-500', 'bg'=>'from-orange-50 to-orange-100'],
    ];
    ?>
    <div class="mt-20">
        <div class="container mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-800">📋 Sınavlara Hazırlık</h2>
                    <p class="text-gray-500 mt-1">Hedeflediğin sınava özel notlar, çıkmış sorular ve özet kaynaklar</p>
                </div>
            </div>
        </div>
        <div class="relative">
            <button onclick="scrollRow('sinavRow','sol')" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition border border-gray-100 hidden sm:flex">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
            <div id="sinavRow" class="flex gap-5 overflow-x-auto scroll-smooth pb-4 px-6"
                 style="scrollbar-width:none; -ms-overflow-style:none;">
                <?php foreach ($sinavlar as $sinav): ?>
                <a href="<?= $sinav['link'] ?>"
                   class="group flex-none w-44 bg-gradient-to-b <?= $sinav['bg'] ?> p-6 rounded-3xl shadow-sm border-b-8 <?= $sinav['renk'] ?> hover:shadow-xl transition-all text-center">
                    <div class="text-5xl mb-3 group-hover:scale-110 transition-transform"><?= $sinav['icon'] ?></div>
                    <h3 class="text-lg font-black text-gray-800"><?= $sinav['ad'] ?></h3>
                    <p class="text-xs text-gray-400 mt-1 font-medium leading-snug"><?= $sinav['alt'] ?></p>
                </a>
                <?php endforeach; ?>
            </div>
            <button onclick="scrollRow('sinavRow','sag')" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition border border-gray-100 hidden sm:flex">
                <i class="fas fa-chevron-right text-sm"></i>
            </button>
        </div>
    </div>

</main>

<script>
function scrollRow(id, yon) {
    const el = document.getElementById(id);
    el.scrollBy({ left: yon === 'sag' ? 320 : -320, behavior: 'smooth' });
}
</script>

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
                    <strong>notewarehouse</strong>, öğrencilerin bilgiye daha hızlı ve ücretsiz bir şekilde ulaşması amacıyla kurulmuş bir yardımlaşma platformudur.
                </p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer_partial.php'; ?>
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

        <a href="calisma_alani.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-folder-open text-indigo-500 group-hover:scale-110 transition"></i></div>
            Kişisel Çalışmalarım
        </a>
        <a href="yer_imlerim.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-bookmark text-amber-500 group-hover:scale-110 transition"></i></div>
            Yer İmlerim
        </a>
        <a href="rozetlerim.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-medal text-amber-500 group-hover:scale-110 transition"></i></div>
            Rozetlerim
        </a>
        <a href="liderlik.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-trophy text-yellow-500 group-hover:scale-110 transition"></i></div>
            Liderlik Tablosu
        </a>
        <a href="canli_sinavlar.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-trophy text-rose-500 group-hover:scale-110 transition"></i></div>
            🎯 Canlı Sınavlar
        </a>
        <a href="gruplarim.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-users text-purple-500 group-hover:scale-110 transition"></i></div>
            Gruplarım
        </a>
        <a href="bildirimlerim.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-bell text-amber-500 group-hover:scale-110 transition"></i></div>
            Bildirimlerim
        </a>
        <a href="begendigim_notlar.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-heart text-red-500 group-hover:scale-110 transition"></i></div>
            Beğendiğim Notlar
        </a>
        <a href="dersbotu.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-robot text-emerald-500 group-hover:scale-110 transition"></i></div>
            DersBotu (AI)
        </a>
        <a href="ayarlar.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-cog text-slate-500 group-hover:rotate-90 transition duration-300"></i></div>
            Ayarlar
        </a>

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'):
            // Bekleyen şikayet sayısı
            $bekleyenSikayet = 0;
            try { $bekleyenSikayet = (int)$db->query("SELECT COUNT(*) FROM sikayetler WHERE durum='bekliyor'")->fetchColumn(); } catch (Exception $e) {}
        ?>
        <div class="h-px bg-slate-100 my-4 mx-3"></div>
        <p class="text-xs font-bold text-red-500 uppercase tracking-wider mb-2 ml-3"><i class="fas fa-shield-alt mr-1"></i> Yönetim Paneli</p>

        <a href="admin_panel.php" class="flex items-center p-3 text-white bg-gradient-to-r from-rose-500 to-red-500 hover:from-rose-600 hover:to-red-600 rounded-xl font-bold transition shadow-md mb-2">
            <div class="w-8 flex justify-center"><i class="fas fa-tachometer-alt"></i></div>
            🎛️ Admin Komuta Merkezi
        </a>
        <a href="kullanicilar.php" class="flex items-center p-3 text-slate-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-users-cog text-red-500 group-hover:scale-110 transition"></i></div>
            Kullanıcılar
        </a>
        <a href="notlar_admin.php" class="flex items-center p-3 text-slate-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-clipboard-list text-red-500 group-hover:scale-110 transition"></i></div>
            Notlar
        </a>
        <a href="sikayetler_admin.php" class="flex items-center p-3 text-slate-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-semibold transition group justify-between">
            <div class="flex items-center">
                <div class="w-8 flex justify-center"><i class="fas fa-inbox text-red-500 group-hover:scale-110 transition"></i></div>
                Mesajlar
            </div>
            <?php if ($bekleyenSikayet > 0): ?>
                <span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-0.5 rounded-full"><?= $bekleyenSikayet ?></span>
            <?php endif; ?>
        </a>
        <a href="bildirim_gonder.php" class="flex items-center p-3 text-slate-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-paper-plane text-red-500 group-hover:scale-110 transition"></i></div>
            Bildirim Gönder
        </a>
        <a href="haberler_admin.php" class="flex items-center p-3 text-slate-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-newspaper text-red-500 group-hover:scale-110 transition"></i></div>
            Haberler Yönetimi
        </a>
        <?php endif; ?>

        <div class="h-px bg-slate-100 my-4 mx-3"></div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-3">Kurumsal</p>

        <a href="hakkimizda.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-info-circle text-cyan-500 group-hover:scale-110 transition"></i></div>
            Hakkımızda
        </a>
        <a href="haberler.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-newspaper text-pink-500 group-hover:scale-110 transition"></i></div>
            Haberler & Blog
        </a>

        <div class="h-px bg-slate-100 my-4 mx-3"></div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-3">Destek</p>

        <a href="sss.php" class="flex items-center p-3 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-question-circle text-blue-500 group-hover:scale-110 transition"></i></div>
            Sıkça Sorulan Sorular
        </a>
        <a href="sikayet_olustur.php" class="flex items-center p-3 text-slate-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-exclamation-triangle text-red-500 group-hover:scale-110 transition"></i></div>
            Şikayet / Bildirim
        </a>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <a href="sikayetler_admin.php" class="flex items-center p-3 text-slate-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-semibold transition group">
            <div class="w-8 flex justify-center"><i class="fas fa-clipboard-list text-red-600 group-hover:scale-110 transition"></i></div>
            Gelen Şikayetler (Admin)
        </a>
        <?php endif; ?>
    </div>
</div>

<script>
   
    document.addEventListener('DOMContentLoaded', function() {
        
      
        if (!sessionStorage.getItem('adShown')) {
            
           
            setTimeout(() => {
                const modal = document.getElementById('adModal');
                const content = document.getElementById('adContent');
                
                modal.classList.remove('hidden');
                
                
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    content.classList.remove('scale-95');
                }, 50);
                
                
                sessionStorage.setItem('adShown', 'true');
            }, 1500); 
        }
    });

   
    function closeAd() {
        const modal = document.getElementById('adModal');
        const content = document.getElementById('adContent');
        
        
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        
       
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

function toggleSidebar() {
        const sidebar = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('sidebarOverlay');
        
       
        sidebar.classList.toggle('translate-x-full');
        
        
        if (overlay.classList.contains('hidden')) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            document.body.style.overflow = 'hidden'; 
        } else {
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
            document.body.style.overflow = ''; 
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