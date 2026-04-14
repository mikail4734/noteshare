<?php
/**
 * NoteShare - Lise Sayfası (PHP)
 */

$sayfa_basligi = "Lise Notları | Dersini Seç";
$site_adi = "NotDeposu";

// Lise Dersleri Verisi (Linkleri ekledim)
$dersler = [
    [
        'ad' => 'Matematik',
        'alt' => 'Fonksiyonlar, Logaritma...',
        'ikon' => 'fas fa-calculator',
        'renk' => 'blue',
        'link' => 'dersler.php?cat=lise_matematik'
    ],
    [
        'ad' => 'Fizik',
        'alt' => 'Vektörler, Kuvvet, Optik...',
        'ikon' => 'fas fa-atom',
        'renk' => 'purple',
        'link' => 'dersler.php?cat=lise_fizik'
    ],
    [
        'ad' => 'Kimya',
        'alt' => 'Organik, Mol Kavramı...',
        'ikon' => 'fas fa-flask',
        'renk' => 'red',
        'link' => 'dersler.php?cat=lise_kimya'
    ],
    [
        'ad' => 'Biyoloji',
        'alt' => 'Hücre, Sistemler, Kalıtım...',
        'ikon' => 'fas fa-dna',
        'renk' => 'green',
        'link' => 'dersler.php?cat=lise_biyoloji'
    ],
    [
        'ad' => 'Edebiyat',
        'alt' => 'Divan, Cumhuriyet Dönemi...',
        'ikon' => 'fas fa-feather-alt',
        'renk' => 'orange',
        'link' => 'dersler.php?cat=lise_edebiyat'
    ],
    [
        'ad' => 'Tarih',
        'alt' => 'İnkılap, Osmanlı, Dünya...',
        'ikon' => 'fas fa-history',
        'renk' => 'amber',
        'link' => 'dersler.php?cat=lise_tarih'
    ],
    [
        'ad' => 'Coğrafya',
        'alt' => 'Harita Bilgisi, İklim...',
        'ikon' => 'fas fa-globe-africa',
        'renk' => 'emerald',
        'link' => 'dersler.php?cat=lise_cografya'
    ],
    [
        'ad' => 'İngilizce',
        'alt' => 'Grammar, Vocabulary...',
        'ikon' => 'fas fa-language',
        'renk' => 'indigo',
        'link' => 'dersler.php?cat=lise_ingilizce'
    ]
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sayfa_basligi; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans text-slate-900">

    <nav class="bg-slate-900 p-4 text-white shadow-xl sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold flex items-center cursor-pointer" onclick="window.location.href='index.php'">
                <i class="fas fa-graduation-cap mr-3 text-red-500"></i> <?php echo $site_adi; ?> 
            </h1>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="text-sm hover:text-red-400 transition">Geri Dön</a>
            </div>
        </div>
    </nav>

    <header class="py-16 bg-red-600 text-white text-center shadow-inner">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-black mb-2 uppercase tracking-tight">Lise Ders Notları</h2>
            <p class="text-red-100 text-lg opacity-80">9, 10, 11 ve 12. Sınıf müfredatına uygun dökümanlar.</p>
        </div>
    </header>

    <main class="container mx-auto py-12 px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <?php foreach ($dersler as $ders): ?>
            <a href="./<?php echo $ders['link']; ?>" 
               class="bg-white group border-2 border-transparent hover:border-<?php echo $ders['renk']; ?>-500 rounded-2xl p-6 transition-all shadow-md hover:shadow-xl text-center block">
                
                <div class="w-16 h-16 bg-<?php echo $ders['renk']; ?>-100 text-<?php echo $ders['renk']; ?>-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i class="<?php echo $ders['ikon']; ?> text-2xl"></i>
                </div>
                
                <h3 class="text-lg font-bold text-slate-800"><?php echo $ders['ad']; ?></h3>
                <p class="text-slate-500 text-xs mt-1"><?php echo $ders['alt']; ?></p>
                
                <div class="mt-4 text-<?php echo $ders['renk']; ?>-600 text-xs font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">
                    Notları Gör <i class="fas fa-arrow-right ml-1"></i>
                </div>
            </a>
            <?php endforeach; ?>

        </div>
    </main>

    <footer class="py-8 text-center text-slate-400 text-xs">
        &copy; <?php echo date("Y"); ?> NoteShare - Lise Akademik Birimi
    </footer>

</body>
</html>