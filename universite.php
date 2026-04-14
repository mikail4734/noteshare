<?php
/**
 * NoteShare - Üniversite Akademik Arşiv (PHP)
 */

$sayfa_basligi = "Üniversite Notları | Bölümünü Seç";
$site_adi = "NotDeposu";
$versiyon = "v2.0 Academia";

// Bölümler ve Fakülteler Verisi
$bolumler = [
    [
        'ad' => 'Bilgisayar & Yazılım',
        'dersler' => 'Veri Yapıları, Algoritmalar, İşletim Sistemleri...',
        'ikon' => 'fas fa-code',
        'renk' => 'indigo',
        'link' => 'dersler.php?cat=yazilim'
    ],
    [
        'ad' => 'Tıp Fakültesi',
        'dersler' => 'Anatomi, Fizyoloji, Farmakoloji, Patoloji...',
        'ikon' => 'fas fa-stethoscope',
        'renk' => 'red',
        'link' => 'dersler.php?cat=tip'
    ],
    [
        'ad' => 'Hukuk',
        'dersler' => 'Anayasa, Borçlar Hukuku, Medeni Hukuk...',
        'ikon' => 'fas fa-gavel',
        'renk' => 'amber',
        'link' => 'dersler.php?cat=hukuk'
    ],
    [
        'ad' => 'Mimarlık & Tasarım',
        'dersler' => 'Yapı Bilgisi, Teknik Resim, Restorasyon...',
        'ikon' => 'fas fa-drafting-compass',
        'renk' => 'teal',
        'link' => 'dersler.php?cat=mimarlik'
    ],
    [
        'ad' => 'İşletme & İktisat',
        'dersler' => 'Makro-Mikro İktisat, Muhasebe, Pazarlama...',
        'ikon' => 'fas fa-chart-line',
        'renk' => 'blue',
        'link' => 'dersler.php?cat=isletme'
    ],
    [
        'ad' => 'Psikoloji & PDR',
        'dersler' => 'Klinik Psikoloji, Gelişim Kuramları...',
        'ikon' => 'fas fa-brain',
        'renk' => 'pink',
        'link' => 'dersler.php?cat=psikoloji'
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
<body class="bg-slate-100 font-sans text-slate-900">

    <nav class="bg-slate-900 p-4 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center cursor-pointer" onclick="window.location.href='index.php'">
                <i class="fas fa-university mr-3 text-indigo-400"></i> <?php echo $site_adi; ?> 
                <span class="ml-2 text-xs font-mono text-indigo-300"><?php echo $versiyon; ?></span>
            </h1>
            <div class="flex items-center space-x-6">
                <a href="index.php" class="text-sm hover:text-indigo-400 transition">Ana Sayfa</a>
                <button onclick="window.location.href='notlar.php'" class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-xs font-bold uppercase tracking-widest transition">
                    Not Yükle
                </button>
            </div>
        </div>
    </nav>

    <header class="py-20 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white text-center">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl md:text-5xl font-light mb-4 uppercase tracking-tighter">Akademik Arşiv</h2>
            <div class="h-1 w-20 bg-indigo-500 mx-auto mb-6"></div>
            <p class="text-slate-400 text-lg max-w-2xl mx-auto font-light italic">
                Fakülte ve bölümlere göre ayrılmış, akademik standartlarda ders notları ve çıkmış sorular.
            </p>
        </div>
    </header>

    <main class="container mx-auto py-16 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <?php foreach ($bolumler as $bolum): ?>
            <a href="<?php echo $bolum['link']; ?>" 
               class="bg-white group border border-slate-200 rounded-lg p-8 hover:border-<?php echo $bolum['renk']; ?>-500 transition-all shadow-sm hover:shadow-2xl relative overflow-hidden">
                
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-20 transition-opacity">
                    <i class="<?php echo $bolum['ikon']; ?> text-6xl text-slate-900"></i>
                </div>
                
                <h3 class="text-xl font-bold mb-2"><?php echo $bolum['ad']; ?></h3>
                <p class="text-slate-500 text-sm mb-4"><?php echo $bolum['dersler']; ?></p>
                <span class="text-<?php echo $bolum['renk'] === 'amber' ? 'amber-600' : ($bolum['renk'] . '-600'); ?> text-xs font-bold group-hover:underline">
                    Dökümanları İncele &rarr;
                </span>
            </a>
            <?php endforeach; ?>

        </div>
    </main>

    <footer class="bg-slate-900 text-slate-500 py-12 border-t border-slate-800">
        <div class="container mx-auto text-center px-4">
            <p class="text-sm mb-4 font-mono">system.academia.<?php echo date("Y"); ?></p>
            <p class="text-xs uppercase tracking-widest">&copy; <?php echo date("Y"); ?> NoteShare University Network</p>
        </div>
    </footer>

</body>
</html>