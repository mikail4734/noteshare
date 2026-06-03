<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';

$dilSlug = strtolower(trim($_GET['dil'] ?? ''));
$seviyeParam = strtoupper(trim($_GET['seviye'] ?? ''));

$dilBilgileri = [
    'ingilizce'  => ['ad' => 'İngilizce',  'bayrak' => '🇬🇧', 'renk' => 'indigo',  'aciklama' => 'Dünyada en yaygın konuşulan dil. İş, akademi ve seyahat için vazgeçilmez.'],
    'almanca'    => ['ad' => 'Almanca',    'bayrak' => '🇩🇪', 'renk' => 'yellow',  'aciklama' => 'Avrupa\'nın en çok konuşulan ana dili. Mühendislik ve bilim alanında önemli.'],
    'fransizca'  => ['ad' => 'Fransızca',  'bayrak' => '🇫🇷', 'renk' => 'blue',    'aciklama' => 'Kültür, moda ve diplomasi dili. 29 ülkede resmi dil.'],
    'arapca'     => ['ad' => 'Arapça',     'bayrak' => '🇸🇦', 'renk' => 'green',   'aciklama' => '400 milyondan fazla konuşan. İslam medeniyetinin temel dili.'],
    'cince'      => ['ad' => 'Çince',      'bayrak' => '🇨🇳', 'renk' => 'red',     'aciklama' => 'Dünyanın en çok konuşulan anadili. Mandarin Çinli ve ticaret dili.'],
    'ispanyolca' => ['ad' => 'İspanyolca', 'bayrak' => '🇪🇸', 'renk' => 'orange',  'aciklama' => '20\'den fazla ülkede konuşulan, öğrenmesi en kolay dillerden biri.'],
    'italyanca'  => ['ad' => 'İtalyanca',  'bayrak' => '🇮🇹', 'renk' => 'emerald', 'aciklama' => 'Sanat, müzik ve mutfak dili. Türkçeye benzer gramer yapısı.'],
    'rusca'      => ['ad' => 'Rusça',      'bayrak' => '🇷🇺', 'renk' => 'violet',  'aciklama' => 'Doğu Avrupa\'nın en geniş konuşulan dili. Kiril alfabesi kullanır.'],
];

if (!isset($dilBilgileri[$dilSlug])) {
    header("Location: index.php");
    exit;
}

$dil = $dilBilgileri[$dilSlug];

$seviyeler = [
    'A1' => ['label' => 'A1 — Başlangıç',    'renk' => 'emerald', 'icon' => '🌱', 'aciklama' => 'Temel tanışma cümleleri, sayılar, günlük ifadeler.'],
    'A2' => ['label' => 'A2 — Temel',         'renk' => 'green',   'icon' => '🌿', 'aciklama' => 'Alışveriş, seyahat, kısa konuşmalar.'],
    'B1' => ['label' => 'B1 — Orta Altı',     'renk' => 'blue',    'icon' => '📖', 'aciklama' => 'Günlük olaylarda bağımsız iletişim.'],
    'B2' => ['label' => 'B2 — Orta Üstü',     'renk' => 'indigo',  'icon' => '🎓', 'aciklama' => 'Karmaşık metinler ve akıcı konuşma.'],
    'C1' => ['label' => 'C1 — İleri',          'renk' => 'purple',  'icon' => '🏆', 'aciklama' => 'Akademik ve profesyonel düzeyde kullanım.'],
    'C2' => ['label' => 'C2 — Ustalık',        'renk' => 'rose',    'icon' => '🌟', 'aciklama' => 'Anadil düzeyinde akıcılık.'],
];

// Seçili seviye varsa notları getir
$notlar = [];
if ($seviyeParam && isset($seviyeler[$seviyeParam])) {
    try {
        $st = $db->prepare("SELECT n.*, u.ad as yazar FROM notlar n
                            LEFT JOIN users u ON n.kullanici_email = u.email
                            WHERE n.kategori = ? AND n.onaylandi = 1
                            ORDER BY n.created_at DESC LIMIT 20");
        $st->execute([$dilSlug . '-' . strtolower($seviyeParam)]);
        $notlar = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $notlar = []; }
}

$renk = $dil['renk'];
$renkMap = [
    'indigo'  => ['bg'=>'bg-indigo-600',  'light'=>'bg-indigo-50',  'text'=>'text-indigo-600',  'border'=>'border-indigo-500'],
    'yellow'  => ['bg'=>'bg-yellow-500',  'light'=>'bg-yellow-50',  'text'=>'text-yellow-600',  'border'=>'border-yellow-500'],
    'blue'    => ['bg'=>'bg-blue-600',    'light'=>'bg-blue-50',    'text'=>'text-blue-600',    'border'=>'border-blue-500'],
    'green'   => ['bg'=>'bg-green-600',   'light'=>'bg-green-50',   'text'=>'text-green-600',   'border'=>'border-green-500'],
    'red'     => ['bg'=>'bg-red-600',     'light'=>'bg-red-50',     'text'=>'text-red-600',     'border'=>'border-red-500'],
    'orange'  => ['bg'=>'bg-orange-500',  'light'=>'bg-orange-50',  'text'=>'text-orange-600',  'border'=>'border-orange-500'],
    'emerald' => ['bg'=>'bg-emerald-600', 'light'=>'bg-emerald-50', 'text'=>'text-emerald-600', 'border'=>'border-emerald-500'],
    'violet'  => ['bg'=>'bg-violet-600',  'light'=>'bg-violet-50',  'text'=>'text-violet-600',  'border'=>'border-violet-500'],
];
$r = $renkMap[$renk] ?? $renkMap['indigo'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $dil['bayrak'] ?> <?= $dil['ad'] ?> Öğren | notewarehouse</title>
    <meta name="description" content="<?= $dil['ad'] ?> dil öğrenme notları. A1'den C2'ye tüm seviyeler için ücretsiz ders notları, kelime listeleri ve gramer anlatımları.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50">

<!-- NAV -->
<nav class="<?= $r['bg'] ?> px-6 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-white/70 hover:text-white transition text-xl"><i class="fas fa-arrow-left"></i></a>
        <span class="text-white font-black text-lg"><?= $dil['bayrak'] ?> <?= $dil['ad'] ?> Dil Öğrenme</span>
    </div>
    <a href="notlar.php?kategori=dil-<?= $dilSlug ?>" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
        <i class="fas fa-plus mr-2"></i> Not Ekle
    </a>
</nav>

<!-- HERO -->
<div class="<?= $r['bg'] ?> text-white py-16 px-6 text-center">
    <div class="text-8xl mb-4"><?= $dil['bayrak'] ?></div>
    <h1 class="text-4xl font-black mb-3"><?= $dil['ad'] ?> Öğren</h1>
    <p class="text-white/80 max-w-lg mx-auto"><?= $dil['aciklama'] ?></p>
    <div class="flex justify-center gap-6 mt-8 text-sm">
        <div class="bg-white/20 rounded-2xl px-5 py-3 text-center">
            <p class="font-black text-xl">6</p>
            <p class="text-white/70">Seviye</p>
        </div>
        <div class="bg-white/20 rounded-2xl px-5 py-3 text-center">
            <p class="font-black text-xl">A1→C2</p>
            <p class="text-white/70">Kapsam</p>
        </div>
        <div class="bg-white/20 rounded-2xl px-5 py-3 text-center">
            <p class="font-black text-xl">Ücretsiz</p>
            <p class="text-white/70">Erişim</p>
        </div>
    </div>
</div>

<main class="container mx-auto max-w-5xl py-14 px-6">

    <!-- SEVİYE SEÇİMİ -->
    <div class="mb-12">
        <h2 class="text-2xl font-black text-slate-800 mb-2">Seviyeni Seç</h2>
        <p class="text-slate-500 text-sm mb-8">Dil yetkinlik çerçevesi (CEFR) standartlarına göre hazırlanmış içerikler</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($seviyeler as $kod => $sv):
                $aktif = $seviyeParam === $kod;
                $svR = $renkMap[$sv['renk']] ?? $renkMap['indigo'];
            ?>
            <a href="?dil=<?= $dilSlug ?>&seviye=<?= $kod ?>"
               class="group flex flex-col items-center p-5 rounded-2xl border-2 transition-all text-center
                      <?= $aktif ? $svR['bg'] . ' text-white border-transparent shadow-lg scale-105' : 'bg-white border-slate-200 hover:border-slate-300 hover:shadow-md text-slate-700' ?>">
                <span class="text-3xl mb-2 group-hover:scale-110 transition-transform"><?= $sv['icon'] ?></span>
                <span class="font-black text-lg"><?= $kod ?></span>
                <span class="text-[10px] mt-1 font-medium <?= $aktif ? 'text-white/80' : 'text-slate-400' ?>">
                    <?= explode(' — ', $sv['label'])[1] ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($seviyeParam && isset($seviyeler[$seviyeParam])): ?>
    <!-- SEÇİLİ SEVİYE İÇERİĞİ -->
    <?php $sv = $seviyeler[$seviyeParam]; ?>
    <div class="mb-8 p-6 <?= $r['light'] ?> rounded-3xl border <?= $r['border'] ?> border-opacity-20">
        <div class="flex items-center gap-4">
            <span class="text-5xl"><?= $sv['icon'] ?></span>
            <div>
                <h3 class="text-xl font-black <?= $r['text'] ?>"><?= $sv['label'] ?></h3>
                <p class="text-slate-500 text-sm"><?= $sv['aciklama'] ?></p>
            </div>
        </div>
    </div>

    <!-- Hızlı konu butonları -->
    <div class="mb-8 flex flex-wrap gap-2">
        <?php
        $konular = [
            'A1'=>['Alfabe','Sayılar','Selamlaşma','Renkler','Aile','Günler'],
            'A2'=>['Alışveriş','Yemek','Seyahat','Meslek','Hava Durumu','Beden'],
            'B1'=>['Haberler','Hobiler','Geçmiş Zaman','Gelecek Zaman','Fikirler','İş'],
            'B2'=>['Akademik Yazı','Tartışma','İş Hayatı','Medya','Politika','Bilim'],
            'C1'=>['Edebiyat','Hukuk','Felsefe','Ekonomi','Akademik Metin','Retorik'],
            'C2'=>['Deyimler','Atasözleri','Nüans','Üst Yazı','Analiz','Eleştiri'],
        ];
        foreach (($konular[$seviyeParam] ?? []) as $konu):
        ?>
        <a href="arama.php?q=<?= urlencode($dil['ad'].' '.$seviyeParam.' '.$konu) ?>"
           class="px-4 py-1.5 bg-white border border-slate-200 rounded-full text-sm font-semibold text-slate-600 hover:<?= $r['bg'] ?> hover:text-white hover:border-transparent transition-all">
            <?= $konu ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Notlar -->
    <h3 class="text-xl font-black text-slate-800 mb-5">
        <?= $dil['bayrak'] ?> <?= $dil['ad'] ?> <?= $seviyeParam ?> Notları
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
        <h4 class="text-lg font-bold text-slate-600 mb-2">Henüz bu seviyede not yok</h4>
        <p class="text-slate-400 text-sm mb-6">İlk notu sen ekle ve topluluğa katkı sağla!</p>
        <a href="notlar.php?kategori=<?= $dilSlug ?>-<?= strtolower($seviyeParam) ?>"
           class="inline-block <?= $r['bg'] ?> text-white px-6 py-3 rounded-xl font-bold hover:opacity-90 transition">
            <i class="fas fa-plus mr-2"></i> Not Ekle
        </a>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Seviye seçilmedi — genel bakış -->
    <div class="text-center py-10 bg-white rounded-3xl border border-slate-100 shadow-sm">
        <div class="text-5xl mb-4">👆</div>
        <h3 class="text-xl font-bold text-slate-700 mb-2">Yukarıdan bir seviye seç</h3>
        <p class="text-slate-400 text-sm">Seçtiğin seviyeye ait notlar ve kaynaklar burada görünecek.</p>
    </div>
    <?php endif; ?>

</main>

<?php include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
