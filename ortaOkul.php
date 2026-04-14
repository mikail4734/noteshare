<?php
/**
 * NoteShare - Ortaokul Sayfası (PHP)
 */

$sayfa_basligi = "Ortaokul Notları | Dersini Seç";
$site_adi = "NotDeposu"; // Navigasyondaki isim

// Dersler Verisi
$dersler = [
    [
        'ad' => 'Türkçe',
        'alt' => 'Dil Bilgisi & Yazım',
        'ikon' => 'fas fa-pen-nib',
        'renk_sinifi' => 'orange'
    ],
    [
        'ad' => 'Matematik',
        'alt' => 'Sayılar & Problemler',
        'ikon' => 'fas fa-calculator',
        'renk_sinifi' => 'blue'
    ],
    [
        'ad' => 'Fen Bilimleri',
        'alt' => 'Deneyler & Canlılar',
        'ikon' => 'fas fa-microscope',
        'renk_sinifi' => 'green'
    ],
    [
        'ad' => 'Sosyal Bilgiler',
        'alt' => 'Tarih & Coğrafya',
        'ikon' => 'fas fa-map-marked-alt',
        'renk_sinifi' => 'red'
    ],
    [
        'ad' => 'İnkılap Tarihi',
        'alt' => 'LGS Hazırlık',
        'ikon' => 'fas fa-star-and-crescent',
        'renk_sinifi' => 'amber'
    ],
    [
        'ad' => 'Din Kültürü',
        'alt' => 'İnanç & İbadet',
        'ikon' => 'fas fa-mosque',
        'renk_sinifi' => 'teal'
    ],
    [
        'ad' => 'İngilizce',
        'alt' => 'Words & Grammar',
        'ikon' => 'fas fa-globe',
        'renk_sinifi' => 'indigo'
    ],
    [
        'ad' => 'Bilişim',
        'alt' => 'Kodlama & Yazılım',
        'ikon' => 'fas fa-laptop-code',
        'renk_sinifi' => 'gray'
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
<body class="bg-slate-50 font-sans">

    <nav class="bg-emerald-600 p-4 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center cursor-pointer" onclick="window.location.href='index.php'">
                <i class="fas fa-book-reader mr-2 text-yellow-300"></i> <?php echo $site_adi; ?>
            </h1>
            <div class="flex space-x-4 font-medium">
                <a href="index.php" class="hover:text-emerald-200 transition">Ana Sayfa</a>
                <span class="bg-white text-emerald-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">Ortaokul</span>
            </div>
        </div>
    </nav>

    <header class="py-16 bg-emerald-500 text-white text-center">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-extrabold mb-4">Ortaokul Ders Notları</h2>
            <p class="text-emerald-50 text-lg max-w-xl mx-auto">
                LGS hazırlık ve okul sınavların için en anlaşılır, renkli ve özetlenmiş ders notları burada!
            </p>
        </div>
    </header>

    <main class="container mx-auto py-12 px-4">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            
            <?php foreach ($dersler as $ders): ?>
            <a href="dersler.php?ders=<?php echo urlencode($ders['ad']); ?>" 
               class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition-all group text-center">
                
                <div class="bg-<?php echo $ders['renk_sinifi']; ?>-100 text-<?php echo $ders['renk_sinifi']; ?>-600 w-20 h-20 rounded-2xl flex items-center justify-center text-3xl mb-6 mx-auto group-hover:bg-<?php echo $ders['renk_sinifi']; ?>-600 group-hover:text-white transition-all shadow-inner">
                    <i class="<?php echo $ders['ikon']; ?>"></i>
                </div>
                
                <h3 class="font-black text-gray-800 text-xl tracking-tight"><?php echo $ders['ad']; ?></h3>
                <p class="text-gray-400 text-xs mt-2 uppercase font-bold tracking-widest"><?php echo $ders['alt']; ?></p>
            </a>
            <?php endforeach; ?>

        </div>
    </main>

    <footer class="bg-emerald-900 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <h3 class="text-2xl font-bold mb-4">LGS mi Geliyor? Korkma!</h3>
            <p class="text-emerald-200 mb-8 max-w-lg mx-auto italic">
                Sınavlara en iyi notlarla hazırlanman için NoteShare her zaman yanında. Başarılar dileriz!
            </p>
            <div class="text-sm opacity-50">
                &copy; <?php echo date("Y"); ?> NoteShare Ortaokul Akademisi
            </div>
        </div>
    </footer>

</body>
</html>