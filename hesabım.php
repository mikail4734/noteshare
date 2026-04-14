<?php
// Oturumu başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: kaydol.php"); 
    exit;
}

$site_title = "Hesabım | NoteShare";
$current_year = date("Y");

// Profil bilgilerini çek
$kullanici_adi = htmlspecialchars($_SESSION['user_name']);
$kullanici_eposta = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : "ogrenci@universite.edu.tr"; 
$profil_resmi = (isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture'])) ? $_SESSION['user_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($kullanici_adi) . '&background=4f46e5&color=fff&size=256';

// --- ÖRNEK VERİLER (Sadece kendi notların) ---
$benim_notlarim = [
    ['baslik' => 'Algoritma ve Programlama Vize Özeti', 'tarih' => '14 Nis 2026', 'kategori' => 'Üniversite', 'icon' => '💻', 'goruntulenme' => 342, 'indirme' => 56],
    ['baslik' => 'C# Windows Forms Veritabanı Bağlantısı', 'tarih' => '10 Nis 2026', 'kategori' => 'Üniversite', 'icon' => '⚙️', 'goruntulenme' => 890, 'indirme' => 124],
    ['baslik' => 'Matematik 2 Final Çalışma Soruları', 'tarih' => '05 Nis 2026', 'kategori' => 'Üniversite', 'icon' => '📐', 'goruntulenme' => 156, 'indirme' => 30],
];
$paylasilan_not_sayisi = count($benim_notlarim);
// ---------------------------------------------------------------------
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏗️</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ekstra yumuşak geçişler için */
        .smooth-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-slate-50 flex flex-col min-h-screen font-sans">

<nav class="bg-[#4f46e5] px-8 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center space-x-6 flex-1">
        <div class="flex items-center space-x-2 cursor-pointer" onclick="window.location.href='index.php'">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-book-open text-white"></i>
            </div>
            <span class="text-white font-black tracking-tighter text-xl hidden sm:block">NoteShare</span>
        </div>
    </div>
    <div class="flex items-center space-x-4">
        <a href="notlar.php" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl border border-white/30 text-sm font-bold smooth-transition flex items-center shadow-sm">
            <i class="fas fa-plus mr-2"></i> Not Ekle
        </a>
        <div class="flex items-center space-x-3 bg-white/10 p-1 pr-4 rounded-full border border-white/20">
            <img src="<?php echo $profil_resmi; ?>" alt="Profil" class="w-8 h-8 rounded-full border border-white/50 shadow-sm object-cover">
            <div class="flex flex-col">
                <span class="text-white text-xs font-bold leading-none"><?php echo $kullanici_adi; ?></span>
                <a href="cikis.php" class="text-[10px] text-indigo-200 hover:text-white smooth-transition font-medium">Çıkış Yap</a>
            </div>
        </div>
    </div>
</nav>

<div class="w-full h-64 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+CjxjaXJjbGUgY3g9IjIiIGN5PSIyIiByPSIyIiBmaWxsPSIjZmZmIi8+Cjwvc3ZnPg==')]"></div>
</div>

<main class="container mx-auto px-6 pb-16 flex-grow -mt-24 relative z-10">
    
    <div class="flex flex-col lg:flex-row gap-8">
        
        <div class="w-full lg:w-1/3">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 text-center relative pt-20">
                <div class="absolute -top-16 left-1/2 transform -translate-x-1/2">
                    <div class="p-2 bg-white rounded-full shadow-lg">
                        <img src="<?php echo $profil_resmi; ?>" alt="Profil Fotoğrafı" class="w-32 h-32 rounded-full object-cover border-4 border-slate-50">
                    </div>
                </div>
                
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2"><?php echo $kullanici_adi; ?></h2>
                <p class="text-slate-500 text-sm font-medium mt-1 mb-8"><i class="fas fa-envelope mr-2 opacity-70"></i><?php echo $kullanici_eposta; ?></p>
                
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-center">
                        <p class="text-3xl font-black text-indigo-600"><?php echo $paylasilan_not_sayisi; ?></p>
                        <p class="text-xs text-slate-400 font-bold uppercase mt-1">Not Paylaştı</p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-center">
                        <p class="text-3xl font-black text-indigo-600">1.3k</p>
                        <p class="text-xs text-slate-400 font-bold uppercase mt-1">Toplam İndirme</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl smooth-transition shadow-md shadow-indigo-200">
                        Profili Düzenle
                    </button>
                    <button class="w-full bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-bold py-3 rounded-xl smooth-transition">
                        Şifre Değiştir
                    </button>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-2/3 mt-8 lg:mt-0">
            
            <div class="flex items-center justify-between mb-8 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center">
                    <i class="fas fa-layer-group text-indigo-500 mr-3 text-2xl"></i> Paylaştığım Notlar
                </h3>
                <span class="bg-indigo-50 text-indigo-600 py-1 px-4 rounded-full text-sm font-bold border border-indigo-100">
                    Toplam <?php echo $paylasilan_not_sayisi; ?> Not
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($benim_notlarim as $not): ?>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 smooth-transition group relative overflow-hidden">
                    
                    <div class="flex justify-between items-start mb-5">
                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-2xl border border-slate-100 group-hover:scale-110 smooth-transition">
                            <?php echo $not['icon']; ?>
                        </div>
                        <span class="text-xs font-bold bg-slate-100 text-slate-500 px-3 py-1.5 rounded-full uppercase tracking-wide">
                            <?php echo $not['kategori']; ?>
                        </span>
                    </div>
                    
                    <h4 class="font-extrabold text-slate-800 text-lg mb-2 leading-tight group-hover:text-indigo-600 smooth-transition line-clamp-2">
                        <?php echo $not['baslik']; ?>
                    </h4>
                    <p class="text-xs text-slate-400 font-medium mb-6 flex items-center">
                        <i class="far fa-calendar-alt mr-1.5"></i> <?php echo $not['tarih']; ?>
                    </p>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex space-x-4">
                            <span class="text-xs text-slate-500 flex items-center" title="Görüntülenme">
                                <i class="far fa-eye mr-1 text-slate-400"></i> <?php echo $not['goruntulenme']; ?>
                            </span>
                            <span class="text-xs text-slate-500 flex items-center" title="İndirme">
                                <i class="fas fa-download mr-1 text-slate-400"></i> <?php echo $not['indirme']; ?>
                            </span>
                        </div>
                        
                        <button class="text-slate-400 hover:text-indigo-600 smooth-transition w-8 h-8 rounded-full hover:bg-indigo-50 flex items-center justify-center" title="Düzenle / Sil">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

                <a href="notlar.php" class="bg-indigo-50/50 p-6 rounded-3xl border-2 border-dashed border-indigo-200 hover:border-indigo-400 hover:bg-indigo-50 smooth-transition flex flex-col items-center justify-center text-center min-h-[220px] group cursor-pointer">
                    <div class="w-14 h-14 bg-indigo-100 text-indigo-500 rounded-full flex items-center justify-center text-2xl mb-4 group-hover:scale-110 smooth-transition group-hover:bg-indigo-500 group-hover:text-white">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h4 class="font-bold text-indigo-800 text-lg">Yeni Not Paylaş</h4>
                    <p class="text-sm text-indigo-500/70 mt-1">Bildiklerini diğer öğrencilerle paylaş.</p>
                </a>

            </div>
        </div>
    </div>
</main>

<footer class="bg-white border-t border-slate-200 text-slate-400 py-8 text-center text-sm mt-auto">
    <p>&copy; <?php echo $current_year; ?> NoteShare - Tüm Hakları Saklıdır.</p>
</footer>

</body>
</html>