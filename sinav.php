<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';

$sinavSlug = strtolower(trim($_GET['sinav'] ?? ''));

$sinavlar = [
    'yks' => [
        'ad' => 'YKS', 'tam_ad' => 'Yükseköğretim Kurumları Sınavı',
        'icon' => '🎯', 'renk' => 'indigo',
        'aciklama' => 'Türkiye\'nin üniversite giriş sınavı. TYT, AYT ve YDT bölümlerinden oluşur.',
        'altlar' => [
            'tyt' => ['ad' => 'TYT',     'tam' => 'Temel Yeterlilik Testi',   'icon' => '📐'],
            'ayt' => ['ad' => 'AYT',     'tam' => 'Alan Yeterlilik Testi',    'icon' => '🔬'],
            'ydt' => ['ad' => 'YDT',     'tam' => 'Yabancı Dil Testi',        'icon' => '🌐'],
            'say' => ['ad' => 'Sayısal', 'tam' => 'Matematik & Fen',          'icon' => '🧮'],
            'ea'  => ['ad' => 'Eşit Ağırlık', 'tam' => 'Mat & Sosyal',        'icon' => '⚖️'],
            'soz' => ['ad' => 'Sözel',   'tam' => 'Türkçe & Sosyal',          'icon' => '📚'],
        ],
    ],
    'lgs' => [
        'ad' => 'LGS', 'tam_ad' => 'Liselere Geçiş Sınavı',
        'icon' => '📐', 'renk' => 'blue',
        'aciklama' => '8. sınıf öğrencilerinin liseye geçiş için girdiği merkezi sınav.',
        'altlar' => [
            'turkce'    => ['ad' => 'Türkçe',         'tam' => 'Okuma & Anlama',        'icon' => '📖'],
            'matematik' => ['ad' => 'Matematik',      'tam' => 'Temel Matematik',       'icon' => '🔢'],
            'fen'       => ['ad' => 'Fen Bilimleri',  'tam' => 'Fizik, Kimya, Biyoloji','icon' => '🔬'],
            'inkilap'   => ['ad' => 'İnkılap',        'tam' => 'T.C. İnkılap Tarihi',   'icon' => '🏛️'],
            'din'       => ['ad' => 'Din Kültürü',    'tam' => 'Din ve Ahlak',          'icon' => '📿'],
            'ingilizce' => ['ad' => 'İngilizce',      'tam' => 'Yabancı Dil',           'icon' => '🌍'],
        ],
    ],
    'dgs' => [
        'ad' => 'DGS', 'tam_ad' => 'Dikey Geçiş Sınavı',
        'icon' => '🏛️', 'renk' => 'purple',
        'aciklama' => 'Ön lisans mezunlarının lisans programlarına geçişi için yapılan sınav.',
        'altlar' => [
            'say'   => ['ad' => 'Sayısal', 'tam' => 'Matematik & Geometri', 'icon' => '🧮'],
            'sozel' => ['ad' => 'Sözel',   'tam' => 'Türkçe & Sosyal',     'icon' => '📚'],
        ],
    ],
    'kpss' => [
        'ad' => 'KPSS', 'tam_ad' => 'Kamu Personeli Seçme Sınavı',
        'icon' => '🏛️', 'renk' => 'amber',
        'aciklama' => 'Kamu kurum ve kuruluşlarına personel alımında kullanılan sınav.',
        'altlar' => [
            'gy'      => ['ad' => 'Genel Yetenek',   'tam' => 'Türkçe & Matematik',     'icon' => '🧠'],
            'gk'      => ['ad' => 'Genel Kültür',    'tam' => 'Tarih, Coğrafya, Vatan.', 'icon' => '🌍'],
            'egitim'  => ['ad' => 'Eğitim Bil.',   'tam' => 'Pedagoji',               'icon' => '🎓'],
            'hukuk'   => ['ad' => 'Hukuk',           'tam' => 'Anayasa & Medeni',       'icon' => '⚖️'],
            'iktisat' => ['ad' => 'İktisat',         'tam' => 'Ekonomi & Maliye',       'icon' => '💰'],
        ],
    ],
    'yds' => [
        'ad' => 'YDS', 'tam_ad' => 'Yabancı Dil Bilgisi Seviye Tespit Sınavı',
        'icon' => '🌐', 'renk' => 'emerald',
        'aciklama' => 'Akademisyen ve kamu personeli için yabancı dil yeterlilik sınavı.',
        'altlar' => [
            'ingilizce' => ['ad' => 'İngilizce', 'tam' => 'English',  'icon' => '🇬🇧'],
            'almanca'   => ['ad' => 'Almanca',   'tam' => 'Deutsch',  'icon' => '🇩🇪'],
            'fransizca' => ['ad' => 'Fransızca', 'tam' => 'Français', 'icon' => '🇫🇷'],
            'arapca'    => ['ad' => 'Arapça',    'tam' => 'Arabic',   'icon' => '🇸🇦'],
        ],
    ],
    'ales' => [
        'ad' => 'ALES', 'tam_ad' => 'Akademik Lisansüstü Eğitim Sınavı',
        'icon' => '🎓', 'renk' => 'rose',
        'aciklama' => 'Yüksek lisans ve doktora programlarına giriş için gerekli sınav.',
        'altlar' => [
            'say'   => ['ad' => 'Sayısal', 'tam' => 'Matematik Bölümü', 'icon' => '🔢'],
            'sozel' => ['ad' => 'Sözel',   'tam' => 'Türkçe Bölümü',   'icon' => '📖'],
            'ea'    => ['ad' => 'Eşit Ağırlık', 'tam' => 'Karma',       'icon' => '⚖️'],
        ],
    ],
    'ekys' => [
        'ad' => 'EKYS', 'tam_ad' => 'Engelli Kamu Personeli Seçme Sınavı',
        'icon' => '⚖️', 'renk' => 'teal',
        'aciklama' => 'Engelli bireylerin kamu hizmetlerine alımında kullanılan özel sınav.',
        'altlar' => [
            'gy' => ['ad' => 'Genel Yetenek', 'tam' => 'Mantık & Dil',  'icon' => '🧠'],
            'gk' => ['ad' => 'Genel Kültür',  'tam' => 'Temel Bilgiler', 'icon' => '📚'],
        ],
    ],
    'meb' => [
        'ad' => 'MEB', 'tam_ad' => 'Öğretmenlik Sınavları (ÖABT)',
        'icon' => '📚', 'renk' => 'orange',
        'aciklama' => 'MEB öğretmen atama sınavları: ÖABT ve alan bilgisi sınavları.',
        'altlar' => [
            'oabt'      => ['ad' => 'ÖABT',       'tam' => 'Alan Bilgisi',         'icon' => '📋'],
            'turkce'    => ['ad' => 'Türkçe',     'tam' => 'Türkçe Öğretmenliği',  'icon' => '📖'],
            'matematik' => ['ad' => 'Matematik',  'tam' => 'Mat. Öğretmenliği',    'icon' => '🔢'],
            'sinif'     => ['ad' => 'Sınıf Öğr.', 'tam' => 'Sınıf Öğretmenliği',  'icon' => '🏫'],
        ],
    ],
];

if (!isset($sinavlar[$sinavSlug])) {
    header("Location: index.php");
    exit;
}
$sinav = $sinavlar[$sinavSlug];

$renkMap = [
    'indigo'  => ['bg'=>'bg-indigo-600',  'hover'=>'hover:border-indigo-500',  'shadow'=>'rgba(79,70,229,0.18)',  'text'=>'text-indigo-600',  'soft'=>'bg-indigo-50'],
    'blue'    => ['bg'=>'bg-blue-600',    'hover'=>'hover:border-blue-500',    'shadow'=>'rgba(37,99,235,0.18)',  'text'=>'text-blue-600',    'soft'=>'bg-blue-50'],
    'purple'  => ['bg'=>'bg-purple-600',  'hover'=>'hover:border-purple-500',  'shadow'=>'rgba(147,51,234,0.18)', 'text'=>'text-purple-600',  'soft'=>'bg-purple-50'],
    'amber'   => ['bg'=>'bg-amber-500',   'hover'=>'hover:border-amber-500',   'shadow'=>'rgba(245,158,11,0.18)', 'text'=>'text-amber-600',   'soft'=>'bg-amber-50'],
    'emerald' => ['bg'=>'bg-emerald-600', 'hover'=>'hover:border-emerald-500', 'shadow'=>'rgba(16,185,129,0.18)', 'text'=>'text-emerald-600', 'soft'=>'bg-emerald-50'],
    'rose'    => ['bg'=>'bg-rose-600',    'hover'=>'hover:border-rose-500',    'shadow'=>'rgba(244,63,94,0.18)',  'text'=>'text-rose-600',    'soft'=>'bg-rose-50'],
    'teal'    => ['bg'=>'bg-teal-600',    'hover'=>'hover:border-teal-500',    'shadow'=>'rgba(20,184,166,0.18)', 'text'=>'text-teal-600',    'soft'=>'bg-teal-50'],
    'orange'  => ['bg'=>'bg-orange-500',  'hover'=>'hover:border-orange-500',  'shadow'=>'rgba(249,115,22,0.18)', 'text'=>'text-orange-600',  'soft'=>'bg-orange-50'],
];
$r = $renkMap[$sinav['renk']] ?? $renkMap['indigo'];

$araclar = [
    ['q' => $sinav['ad'].' çıkmış soru', 'icon' => '📝', 'baslik' => 'Çıkmış Sorular',  'aciklama' => 'Geçmiş yılların soruları'],
    ['q' => $sinav['ad'].' özet',        'icon' => '📋', 'baslik' => 'Konu Özetleri',   'aciklama' => 'Hızlı tekrar notları'],
    ['q' => $sinav['ad'].' deneme',      'icon' => '🎯', 'baslik' => 'Deneme Sınavı',   'aciklama' => 'Soru çözüm pratiği'],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $sinav['icon'] ?> <?= $sinav['ad'] ?> Hazırlık | notewarehouse</title>
    <meta name="description" content="<?= $sinav['tam_ad'] ?> için ücretsiz sınav notları, çıkmış sorular ve konu özetleri.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sv-card { transition: all 0.25s; }
        .sv-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px <?= $r['shadow'] ?>; }

        /* Yatay kaydırma — çubuk gizli, yanlardan oklar */
        .kaydir-satir {
            display: flex;
            gap: 2rem;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding: 0.5rem 0.25rem 1rem;
            scroll-behavior: smooth;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .kaydir-satir::-webkit-scrollbar { display: none; }
        .kaydir-satir > * { scroll-snap-align: start; }
        .kaydir-ok {
            position: absolute; top: 50%; transform: translateY(-50%); z-index: 20;
            width: 3rem; height: 3rem; border-radius: 9999px;
            background:#fff; color:#4f46e5;
            box-shadow: 0 8px 22px rgba(15,23,42,.14);
            display:flex; align-items:center; justify-content:center;
            border: 1px solid #eef2ff; cursor: pointer; transition: all .2s;
        }
        .kaydir-ok:hover { background:#0f172a; color:#fff; transform: translateY(-50%) scale(1.08); }
        .kaydir-ok-sol { left: -0.75rem; }
        .kaydir-ok-sag { right: -0.75rem; }
        @media (max-width: 640px) { .kaydir-ok { display: none; } }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 text-slate-900">

<!-- NAV -->
<nav class="bg-slate-900 px-6 py-4 text-white shadow-xl sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold cursor-pointer" onclick="window.location.href='index.php'">
            <i class="fas fa-clipboard-check mr-3 <?= $r['text'] ?>"></i> notewarehouse
        </h1>
        <a href="index.php" class="text-sm hover:opacity-70 transition">← Geri Dön</a>
    </div>
</nav>

<!-- HERO -->
<header class="<?= $r['bg'] ?> text-white py-16 px-6 text-center shadow-inner">
    <div class="text-7xl mb-4"><?= $sinav['icon'] ?></div>
    <h2 class="text-4xl md:text-5xl font-black mb-2"><?= $sinav['ad'] ?></h2>
    <p class="text-white/60 text-xs mb-3 uppercase tracking-widest font-bold"><?= $sinav['tam_ad'] ?></p>
    <p class="text-white/80 max-w-xl mx-auto"><?= $sinav['aciklama'] ?></p>
</header>

<main class="container mx-auto py-14 px-4">

    <div class="text-center mb-10">
        <h3 class="text-3xl font-extrabold text-slate-800">Bölüm Seç</h3>
        <p class="text-slate-500 mt-2">Çalışmak istediğin bölümü seç, o bölümün notlarına ulaş</p>
    </div>

    <!-- BÖLÜM KARTLARI — okullarla aynı boyut, yan oklarla yatay kaydırma -->
    <div class="relative max-w-6xl mx-auto">
        <button type="button" onclick="kaydir('bolumRow',-1)" class="kaydir-ok kaydir-ok-sol" aria-label="Sola"><i class="fas fa-chevron-left"></i></button>
        <div id="bolumRow" class="kaydir-satir">
            <?php foreach ($sinav['altlar'] as $slug => $alt):
                $kategori = $sinavSlug . '-' . $slug;
            ?>
            <a href="dersler.php?kategori=<?= urlencode($kategori) ?>"
               class="sv-card group flex-none w-72 bg-white p-10 rounded-3xl shadow-sm border-b-8 <?= $r['hover'] ?> hover:shadow-2xl transition-all text-center"
               style="border-bottom-color: currentColor;">
                <div class="text-6xl group-hover:scale-110 transition-transform mb-4"><?= $alt['icon'] ?></div>
                <h3 class="text-xl font-black text-slate-800"><?= $alt['ad'] ?></h3>
                <p class="text-xs text-slate-400 mt-2 font-semibold leading-snug"><?= $alt['tam'] ?></p>
            </a>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="kaydir('bolumRow',1)" class="kaydir-ok kaydir-ok-sag" aria-label="Sağa"><i class="fas fa-chevron-right"></i></button>
    </div>

    <script>
    function kaydir(id, yon) {
        const el = document.getElementById(id);
        if (!el) return;
        el.scrollBy({ left: yon * (el.clientWidth * 0.8), behavior: 'smooth' });
    }
    </script>

    <!-- HAZIRLIK ARAÇLARI -->
    <div class="max-w-5xl mx-auto mt-14">
        <h3 class="text-xl font-black text-slate-800 mb-5 text-center">Hazırlık Araçları</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <?php foreach ($araclar as $a): ?>
            <a href="arama.php?q=<?= urlencode($a['q']) ?>"
               class="bg-white rounded-2xl p-6 border border-slate-100 hover:shadow-md <?= $r['hover'] ?> hover:border-2 transition text-center group">
                <div class="text-4xl mb-3 group-hover:scale-110 transition-transform"><?= $a['icon'] ?></div>
                <h4 class="font-black text-slate-800 text-sm"><?= $a['baslik'] ?></h4>
                <p class="text-xs text-slate-400 mt-1"><?= $a['aciklama'] ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- DİĞER SINAVLAR -->
    <div class="max-w-5xl mx-auto mt-14 pt-10 border-t border-slate-200">
        <h3 class="text-base font-black text-slate-600 mb-4 text-center">Diğer Sınavlar</h3>
        <div class="flex flex-wrap justify-center gap-3">
            <?php foreach ($sinavlar as $slug => $s):
                if ($slug === $sinavSlug) continue;
            ?>
            <a href="?sinav=<?= $slug ?>"
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:shadow-md transition">
                <span><?= $s['icon'] ?></span><span><?= $s['ad'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

</main>

<?php include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
