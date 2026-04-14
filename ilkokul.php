<?php
/**
 * NoteShare - İlkokul Sayfası (PHP)
 */

$sayfa_basligi = "İlkokul Notları | Eğlenerek Öğren";
$site_adi = "NoteShare";

// Dersler Verisi - PHP dizisi ile yönetimi kolaylaştırıyoruz
$dersler = [
    [
        'ad' => 'Hayat Bilgisi',
        'alt_baslik' => 'Çevremiz ve Dünyamız',
        'ikon' => 'fas fa-sun',
        'renk' => 'orange', // hover:border-orange-400
        'bg_ikon' => 'bg-yellow-100',
        'text_ikon' => 'text-yellow-600'
    ],
    [
        'ad' => 'Türkçe',
        'alt_baslik' => 'Okuma ve Yazma',
        'ikon' => 'fas fa-book',
        'renk' => 'blue',
        'bg_ikon' => 'bg-blue-100',
        'text_ikon' => 'text-blue-600'
    ],
    [
        'ad' => 'Matematik',
        'alt_baslik' => 'Sayılar ve İşlemler',
        'ikon' => 'fas fa-plus-circle',
        'renk' => 'red',
        'bg_ikon' => 'bg-red-100',
        'text_ikon' => 'text-red-600'
    ],
    [
        'ad' => 'Fen Bilimleri',
        'alt_baslik' => 'Doğa ve Deneyler',
        'ikon' => 'fas fa-leaf',
        'renk' => 'green',
        'bg_ikon' => 'bg-green-100',
        'text_ikon' => 'text-green-600'
    ],
    [
        'ad' => 'Sosyal Bilgiler',
        'alt_baslik' => 'Tarihimiz ve Kültürümüz',
        'ikon' => 'fas fa-landmark',
        'renk' => 'indigo',
        'bg_ikon' => 'bg-indigo-100',
        'text_ikon' => 'text-indigo-600'
    ],
    [
        'ad' => 'İngilizce',
        'alt_baslik' => 'Hello! Welcome!',
        'ikon' => 'fas fa-smile-beam',
        'renk' => 'purple',
        'bg_ikon' => 'bg-purple-100',
        'text_ikon' => 'text-purple-600'
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
<body class="bg-orange-50 font-sans">

    <nav class="bg-orange-500 p-5 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center tracking-wide cursor-pointer" onclick="window.location.href='index.php'">
                <i class="fas fa-pencil-alt mr-3 text-yellow-200"></i> <?php echo $site_adi; ?> <span class="ml-2 text-sm font-light opacity-80">İlkokul</span>
            </h1>
            <a href="index.php" class="bg-white text-orange-600 px-5 py-2 rounded-full font-bold text-sm hover:bg-orange-100 transition shadow-md">
                Ana Sayfa
            </a>
        </div>
    </nav>

    <header class="py-20 bg-orange-400 text-white text-center rounded-b-[3rem] shadow-inner">
        <div class="container mx-auto px-4">
            <h2 class="text-5xl font-black mb-4 drop-shadow-md">Merhaba Arkadaşlar! 👋</h2>
            <p class="text-orange-100 text-xl max-w-xl mx-auto font-medium">
                Derslerine yardımcı olacak en eğlenceli ve renkli notlar burada seni bekliyor.
            </p>
        </div>
    </header>

    <main class="container mx-auto py-16 px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
            
            <?php foreach ($dersler as $ders): ?>
            <a href="dersler.php?ders=<?php echo urlencode($ders['ad']); ?>" 
               class="bg-white rounded-[2.5rem] p-10 shadow-sm border-4 border-transparent hover:border-<?php echo $ders['renk']; ?>-400 hover:shadow-2xl hover:-translate-y-3 transition-all group text-center">
                
                <div class="<?php echo $ders['bg_ikon'] . ' ' . $ders['text_ikon']; ?> w-24 h-24 rounded-full flex items-center justify-center text-4xl mb-6 mx-auto group-hover:scale-110 transition-transform shadow-md">
                    <i class="<?php echo $ders['ikon']; ?>"></i>
                </div>
                
                <h3 class="font-black text-gray-800 text-2xl tracking-tight"><?php echo $ders['ad']; ?></h3>
                <p class="text-gray-400 text-sm mt-2 font-medium"><?php echo $ders['alt_baslik']; ?></p>
            </a>
            <?php endforeach; ?>

        </div>
    </main>

    <footer class="bg-orange-600 text-white py-12 mt-10">
        <div class="container mx-auto px-4 text-center">
            <p class="text-lg font-bold mb-2"><?php echo $site_adi; ?> Çocuk Akademisi</p>
            <p class="opacity-75 text-sm italic">Geleceğin yıldızları için en güzel notlar...</p>
            <p class="mt-4 text-xs opacity-50">&copy; <?php echo date("Y"); ?> Tüm Hakları Saklıdır.</p>
        </div>
    </footer>

</body>
</html>