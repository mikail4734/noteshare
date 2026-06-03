<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';

$sinavSlug = strtolower(trim($_GET['sinav'] ?? ''));
$altSlug   = strtolower(trim($_GET['alt'] ?? ''));

$sinavlar = [
    'yks' => [
        'ad' => 'YKS', 'tam_ad' => 'Yükseköğretim Kurumları Sınavı',
        'icon' => '🎯', 'renk' => 'indigo',
        'aciklama' => 'Türkiye\'nin üniversite giriş sınavı. TYT, AYT ve YDT\'den oluşur.',
        'altlar' => [
            'tyt'  => ['ad' => 'TYT',  'tam' => 'Temel Yeterlilik Testi',      'icon' => '📐'],
            'ayt'  => ['ad' => 'AYT',  'tam' => 'Alan Yeterlilik Testi',       'icon' => '🔬'],
            'ydt'  => ['ad' => 'YDT',  'tam' => 'Yabancı Dil Testi',          'icon' => '🌐'],
            'say'  => ['ad' => 'Sayısal','tam'=> 'Matematik & Fen Bilimleri',   'icon' => '🧮'],
            'ea'   => ['ad' => 'EA',    'tam' => 'Eşit Ağırlık',               'icon' => '⚖️'],
            'soz'  => ['ad' => 'Sözel', 'tam' => 'Türkçe & Sosyal Bilimler',   'icon' => '📚'],
        ],
        'konular' => ['Matematik','Türkçe','Tarih','Coğrafya','Fizik','Kimya','Biyoloji','Felsefe','Din Kültürü'],
    ],
    'lgs' => [
        'ad' => 'LGS', 'tam_ad' => 'Liselere Geçiş Sınavı',
        'icon' => '📐', 'renk' => 'blue',
        'aciklama' => '8. sınıf öğrencilerinin liseye geçiş için girdigi merkezi sınav.',
        'altlar' => [
            'turkce'     => ['ad' => 'Türkçe',          'tam' => 'Okuma & Anlama',          'icon' => '📖'],
            'matematik'  => ['ad' => 'Matematik',        'tam' => 'Temel Matematik',          'icon' => '🔢'],
            'fen'        => ['ad' => 'Fen Bilimleri',   'tam' => 'Fizik, Kimya, Biyoloji',   'icon' => '🔬'],
            'inkilap'    => ['ad' => 'İnkılap Tarihi',  'tam' => 'Atatürk ve Cumhuriyet',   'icon' => '🏛️'],
            'din'        => ['ad' => 'Din Kültürü',     'tam' => 'Din ve Ahlak Bilgisi',    'icon' => '📿'],
            'ingilizce'  => ['ad' => 'İngilizce',       'tam' => 'Yabancı Dil',              'icon' => '🌍'],
        ],
        'konular' => ['Denklemler','Üslü Sayılar','Veri Analizi','Kuvvet ve Hareket','Hücre','Okuma Anlama'],
    ],
    'dgs' => [
        'ad' => 'DGS', 'tam_ad' => 'Dikey Geçiş Sınavı',
        'icon' => '🏛️', 'renk' => 'purple',
        'aciklama' => 'Ön lisans mezunlarının lisans programlarına geçişi için yapılan sınav.',
        'altlar' => [
            'say'   => ['ad' => 'Sayısal',  'tam' => 'Matematik & Geometri',  'icon' => '🧮'],
            'sozel' => ['ad' => 'Sözel',    'tam' => 'Türkçe & Sosyal',      'icon' => '📚'],
        ],
        'konular' => ['Temel Matematik','Geometri','Türkçe Dil Bilgisi','Atatürk İlkeleri'],
    ],
    'kpss' => [
        'ad' => 'KPSS', 'tam_ad' => 'Kamu Personeli Seçme Sınavı',
        'icon' => '🏛️', 'renk' => 'amber',
        'aciklama' => 'Kamu kurum ve kuruluşlarına personel alımında kullanılan sınav.',
        'altlar' => [
            'gk'      => ['ad' => 'Genel Kültür',      'tam' => 'Tarih, Coğrafya, Vatandaşlık', 'icon' => '🌍'],
            'gy'      => ['ad' => 'Genel Yetenek',     'tam' => 'Türkçe & Matematik',            'icon' => '🧠'],
            'egitim'  => ['ad' => 'Eğitim Bilimleri', 'tam' => 'Pedagoji ve Öğretmenlik',       'icon' => '🎓'],
            'hukuk'   => ['ad' => 'Hukuk',            'tam' => 'Anayasa & Medeni Hukuk',        'icon' => '⚖️'],
            'iktisat' => ['ad' => 'İktisat',          'tam' => 'Ekonomi & Maliye',              'icon' => '💰'],
        ],
        'konular' => ['Türk Tarihi','Coğrafya','Anayasa','Vatandaşlık','Türkçe','Matematik'],
    ],
    'yds' => [
        'ad' => 'YDS', 'tam_ad' => 'Yabancı Dil Bilgisi Seviye Tespit Sınavı',
        'icon' => '🌐', 'renk' => 'emerald',
        'aciklama' => 'Kamu personeli ve akademisyenler için İngilizce, Fransızca, Almanca sınavı.',
        'altlar' => [
            'ingilizce'  => ['ad' => 'İngilizce',  'tam' => 'English Proficiency',    'icon' => '🇬🇧'],
            'almanca'    => ['ad' => 'Almanca',    'tam' => 'Deutsch Kenntnistest',    'icon' => '🇩🇪'],
            'fransizca'  => ['ad' => 'Fransızca',  'tam' => 'Épreuve de Français',    'icon' => '🇫🇷'],
        ],
        'konular' => ['Reading Comprehension','Vocabulary','Grammar','Cloze Test','Translation'],
    ],
    'ales' => [
        'ad' => 'ALES', 'tam_ad' => 'Akademik Lisansüstü Eğitim Sınavı',
        'icon' => '🎓', 'renk' => 'rose',
        'aciklama' => 'Yüksek lisans ve doktora programlarına giriş için gerekli sınav.',
        'altlar' => [
            'say'   => ['ad' => 'Sayısal', 'tam' => 'Matematik Bölümü', 'icon' => '🔢'],
            'sozel' => ['ad' => 'Sözel',   'tam' => 'Türkçe Bölümü',   'icon' => '📖'],
            'esit'  => ['ad' => 'EA',      'tam' => 'Eşit Ağırlık',    'icon' => '⚖️'],
        ],
        'konular' => ['Matematiksel Akıl','Mantık','Sayı Dizileri','Kelime Bilgisi','Paragraf'],
    ],
    'ekys' => [
        'ad' => 'EKYS', 'tam_ad' => 'Engelli Kamu Personeli Seçme Sınavı',
        'icon' => '⚖️', 'renk' => 'teal',
        'aciklama' => 'Engelli bireylerin kamu hizmetlerine alımında kullanılan özel sınav.',
        'altlar' => [
            'gk' => ['ad' => 'Genel Kültür',  'tam' => 'Temel Bilgiler', 'icon' => '📚'],
            'gy' => ['ad' => 'Genel Yetenek', 'tam' => 'Mantık & Dil',  'icon' => '🧠'],
        ],
        'konular' => ['Genel Kültür','Türkçe','Matematik','Atatürk İlkeleri'],
    ],
    'meb' => [
        'ad' => 'MEB', 'tam_ad' => 'Öğretmenlik Sınavları',
        'icon' => '📚', 'renk' => 'orange',
        'aciklama' => 'MEB bünyesindeki öğretmen atama sınavları (ÖABT ve diğerleri).',
        'altlar' => [
            'oabt'     => ['ad' => 'ÖABT',        'tam' => 'Öğretmenlik Alan Bilgisi', 'icon' => '📋'],
            'turkce'   => ['ad' => 'Türkçe',       'tam' => 'Türkçe Öğretmenliği',     'icon' => '📖'],
            'matematik'=> ['ad' => 'Matematik',    'tam' => 'Matematik Öğretmenliği',   'icon' => '🔢'],
            'sinif'    => ['ad' => 'Sınıf Öğr.', 'tam' => 'Sınıf Öğretmenliği',      'icon' => '🏫'],
        ],
        'konular' => ['Eğitim Bilimleri','Alan Bilgisi','Öğretim Yöntemleri','Rehberlik'],
    ],
];

if (!isset($sinavlar[$sinavSlug])) {
    header("Location: index.php");
    exit;
}

$sinav = $sinavlar[$sinavSlug];

$renkMap = [
    'indigo'  => ['bg'=>'bg-indigo-600',  'light'=>'bg-indigo-50',   'text'=>'text-indigo-600',   'border'=>'border-indigo-500',  'hover'=>'hover:bg-indigo-700'],
    'blue'    => ['bg'=>'bg-blue-600',    'light'=>'bg-blue-50',     'text'=>'text-blue-600',     'border'=>'border-blue-500',    'hover'=>'hover:bg-blue-700'],
    'purple'  => ['bg'=>'bg-purple-600',  'light'=>'bg-purple-50',   'text'=>'text-purple-600',   'border'=>'border-purple-500',  'hover'=>'hover:bg-purple-700'],
    'amber'   => ['bg'=>'bg-amber-500',   'light'=>'bg-amber-50',    'text'=>'text-amber-600',    'border'=>'border-amber-500',   'hover'=>'hover:bg-amber-600'],
    'emerald' => ['bg'=>'bg-emerald-600', 'light'=>'bg-emerald-50',  'text'=>'text-emerald-600',  'border'=>'border-emerald-500', 'hover'=>'hover:bg-emerald-700'],
    'rose'    => ['bg'=>'bg-rose-600',    'light'=>'bg-rose-50',     'text'=>'text-rose-600',     'border'=>'border-rose-500',    'hover'=>'hover:bg-rose-700'],
    'teal'    => ['bg'=>'bg-teal-600',    'light'=>'bg-teal-50',     'text'=>'text-teal-600',     'border'=>'border-teal-500',    'hover'=>'hover:bg-teal-700'],
    'orange'  => ['bg'=>'bg-orange-500',  'light'=>'bg-orange-50',   'text'=>'text-orange-600',   'border'=>'border-orange-500',  'hover'=>'hover:bg-orange-600'],
    'violet'  => ['bg'=>'bg-violet-600',  'light'=>'bg-violet-50',   'text'=>'text-violet-600',   'border'=>'border-violet-500',  'hover'=>'hover:bg-violet-700'],
];
$r = $renkMap[$sinav['renk']] ?? $renkMap['indigo'];

// Seçili alt sınav varsa notları getir
$notlar = [];
if ($altSlug && isset($sinav['altlar'][$altSlug])) {
    try {
        $st = $db->prepare("SELECT n.*, u.ad as yazar FROM notlar n
                            LEFT JOIN users u ON n.kullanici_email = u.email
                            WHERE n.kategori = ? AND n.onaylandi = 1
                            ORDER BY n.created_at DESC LIMIT 20");
        $st->execute([$sinavSlug . '-' . $altSlug]);
        $notlar = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $notlar = []; }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $sinav['icon'] ?> <?= $sinav['ad'] ?> Hazırlık | notewarehouse</title>
    <meta name="description" content="<?= $sinav['tam_ad'] ?> için ücretsiz sınav notları, çıkmış sorular, konu özetleri ve çalışma rehberi.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50">

<!-- NAV -->
<nav class="<?= $r['bg'] ?> px-6 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-white/70 hover:text-white transition text-xl"><i class="fas fa-arrow-left"></i></a>
        <span class="text-white font-black text-lg"><?= $sinav['icon'] ?> <?= $sinav['ad'] ?> Hazırlık</span>
    </div>
    <a href="notlar.php?kategori=sinav-<?= $sinavSlug ?>" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
        <i class="fas fa-plus mr-2"></i> Not Ekle
    </a>
</nav>

<!-- HERO -->
<div class="<?= $r['bg'] ?> text-white py-16 px-6 text-center">
    <div class="text-7xl mb-4"><?= $sinav['icon'] ?></div>
    <h1 class="text-4xl font-black mb-2"><?= $sinav['ad'] ?></h1>
    <p class="text-white/60 text-sm mb-1 uppercase tracking-widest font-semibold"><?= $sinav['tam_ad'] ?></p>
    <p class="text-white/80 max-w-lg mx-auto mt-3 text-base"><?= $sinav['aciklama'] ?></p>
</div>

<main class="container mx-auto max-w-5xl py-14 px-6">

    <!-- ALT SINAV / BÖLÜM SEÇİMİ -->
    <?php if (!empty($sinav['altlar'])): ?>
    <div class="mb-12">
        <h2 class="text-2xl font-black text-slate-800 mb-2">Bölüm Seç</h2>
        <p class="text-slate-500 text-sm mb-7">Çalışmak istediğin bölümü seç</p>
        <div class="flex flex-wrap gap-4">
            <!-- Tümü butonu -->
            <a href="?sinav=<?= $sinavSlug ?>"
               class="flex flex-col items-center p-5 rounded-2xl border-2 transition-all text-center min-w-[110px]
                      <?= !$altSlug ? $r['bg'].' text-white border-transparent shadow-lg' : 'bg-white border-slate-200 hover:border-slate-300 text-slate-700' ?>">
                <span class="text-3xl mb-1">📋</span>
                <span class="font-black text-sm">Tümü</span>
            </a>
            <?php foreach ($sinav['altlar'] as $slug => $alt):
                $aktif = $altSlug === $slug;
            ?>
            <a href="?sinav=<?= $sinavSlug ?>&alt=<?= $slug ?>"
               class="flex flex-col items-center p-5 rounded-2xl border-2 transition-all text-center min-w-[110px]
                      <?= $aktif ? $r['bg'].' text-white border-transparent shadow-lg scale-105' : 'bg-white border-slate-200 hover:border-slate-300 text-slate-700 hover:shadow-md' ?>">
                <span class="text-3xl mb-1"><?= $alt['icon'] ?></span>
                <span class="font-black text-sm"><?= $alt['ad'] ?></span>
                <span class="text-[10px] mt-0.5 font-medium <?= $aktif ? 'text-white/70' : 'text-slate-400' ?>"><?= $alt['tam'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- HIZLI KONULAR -->
    <div class="mb-10">
        <h3 class="text-lg font-black text-slate-800 mb-4">Popüler Konular</h3>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($sinav['konular'] as $konu): ?>
            <a href="arama.php?q=<?= urlencode($sinav['ad'].' '.$konu) ?>"
               class="px-4 py-1.5 bg-white border border-slate-200 rounded-full text-sm font-semibold text-slate-600
                      hover:<?= $r['bg'] ?> hover:text-white hover:border-transparent transition-all">
                <?= htmlspecialchars($konu) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- HAZIRLIK ARAÇLARI -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-12">
        <a href="arama.php?q=<?= urlencode($sinav['ad'].' çıkmış soru') ?>"
           class="bg-white rounded-2xl p-5 border border-slate-100 hover:shadow-md hover:border-indigo-200 transition group">
            <div class="text-3xl mb-3">📝</div>
            <h4 class="font-black text-slate-800 group-hover:text-indigo-600 transition text-sm">Çıkmış Sorular</h4>
            <p class="text-xs text-slate-400 mt-1">Geçmiş yılların soruları ve çözümleri</p>
        </a>
        <a href="arama.php?q=<?= urlencode($sinav['ad'].' özet') ?>"
           class="bg-white rounded-2xl p-5 border border-slate-100 hover:shadow-md hover:border-indigo-200 transition group">
            <div class="text-3xl mb-3">📋</div>
            <h4 class="font-black text-slate-800 group-hover:text-indigo-600 transition text-sm">Konu Özetleri</h4>
            <p class="text-xs text-slate-400 mt-1">Hızlı tekrar için özet notlar</p>
        </a>
        <a href="canli_sinavlar.php?kategori=<?= $sinavSlug ?>"
           class="bg-white rounded-2xl p-5 border border-slate-100 hover:shadow-md hover:border-indigo-200 transition group">
            <div class="text-3xl mb-3">🎯</div>
            <h4 class="font-black text-slate-800 group-hover:text-indigo-600 transition text-sm">Deneme Sınavı</h4>
            <p class="text-xs text-slate-400 mt-1">Canlı sınav simülasyonu</p>
        </a>
    </div>

    <!-- NOTLAR -->
    <h3 class="text-xl font-black text-slate-800 mb-5">
        <?= $sinav['icon'] ?> <?= $sinav['ad'] ?>
        <?= ($altSlug && isset($sinav['altlar'][$altSlug])) ? '— ' . $sinav['altlar'][$altSlug]['ad'] : '' ?> Notları
    </h3>

    <?php if (count($notlar) > 0): ?>
    <div class="grid gap-4">
        <?php foreach ($notlar as $not): ?>
        <a href="not_detay.php?id=<?= $not['id'] ?>"
           class="bg-white rounded-2xl p-5 border border-slate-100 hover:border-indigo-200 hover:shadow-md transition-all flex items-center justify-between group">
            <div>
                <h4 class="font-bold text-slate-800 group-hover:text-indigo-600 transition"><?= htmlspecialchars($not['baslik']) ?></h4>
                <p class="text-sm text-slate-400 mt-1">
                    <i class="fas fa-user mr-1"></i><?= htmlspecialchars($not['yazar'] ?? 'Anonim') ?>
                    <span class="mx-2">·</span>
                    <i class="fas fa-heart mr-1"></i><?= (int)($not['begeni'] ?? 0) ?>
                </p>
            </div>
            <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500 transition"></i>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-200">
        <div class="text-5xl mb-4">📝</div>
        <h4 class="text-lg font-bold text-slate-600 mb-2">Henüz bu bölümde not yok</h4>
        <p class="text-slate-400 text-sm mb-6">İlk notu paylaşarak diğer adaylara yardımcı ol!</p>
        <a href="notlar.php?kategori=sinav-<?= $sinavSlug ?>"
           class="inline-block <?= $r['bg'] ?> text-white px-6 py-3 rounded-xl font-bold <?= $r['hover'] ?> transition">
            <i class="fas fa-plus mr-2"></i> Not Ekle
        </a>
    </div>
    <?php endif; ?>

    <!-- DİĞER SINAVLAR -->
    <div class="mt-16 pt-10 border-t border-slate-100">
        <h3 class="text-lg font-black text-slate-700 mb-5">Diğer Sınav Hazırlık Sayfaları</h3>
        <div class="flex flex-wrap gap-3">
            <?php foreach ($sinavlar as $slug => $s):
                if ($slug === $sinavSlug) continue;
                $sr = $renkMap[$s['renk']] ?? $renkMap['indigo'];
            ?>
            <a href="?sinav=<?= $slug ?>"
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:shadow-md transition">
                <span><?= $s['icon'] ?></span>
                <span><?= $s['ad'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

</main>

<?php include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
