<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'baglan.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: giris.php");
    exit;
}

$userEmail = $_SESSION['user_email'];

// Kullanıcı bilgilerini DB'den çek
$u = $db->prepare("SELECT * FROM users WHERE email = ?");
$u->execute([$userEmail]);
$kullanici = $u->fetch(PDO::FETCH_ASSOC);

if (!$kullanici) {
    session_destroy();
    header("Location: giris.php");
    exit;
}

$kullanici_adi   = htmlspecialchars($kullanici['ad']);
$kullanici_eposta = htmlspecialchars($kullanici['email']);
$kullanici_rol   = $kullanici['rol'] ?? 'user';
$kayit_tarihi    = isset($kullanici['kayit_tarihi']) ? date('d M Y', strtotime($kullanici['kayit_tarihi'])) : '—';

// Profil resmi
$profil_resmi = (isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture']))
    ? $_SESSION['user_picture']
    : 'https://ui-avatars.com/api/?name=' . urlencode($kullanici_adi) . '&background=4f46e5&color=fff&size=256&bold=true';

// Notları çek
$n = $db->prepare("SELECT * FROM notes WHERE kullanici_email = ? ORDER BY created_at DESC");
$n->execute([$userEmail]);
$notlar = $n->fetchAll(PDO::FETCH_ASSOC);

// İstatistikler
$toplamNot = count($notlar);
$toplamBegeni = array_sum(array_column($notlar, 'likes'));
$toplamDislike = array_sum(array_column($notlar, 'dislikes'));

// Beğendiği not sayısı
$b = $db->prepare("SELECT COUNT(*) FROM begeniler WHERE kullanici_email = ?");
$b->execute([$userEmail]);
$begendiklerim = (int)$b->fetchColumn();

// Quiz sonuçları
$q = $db->prepare("SELECT AVG(puan) FROM quiz_sonuclari WHERE kullanici_email = ?");
$q->execute([$userEmail]);
$ortalamaPuan = round((float)$q->fetchColumn(), 1);

// Kategori → ikon eşleme
$katIkon = [
    'Konu Anlatımı' => '📖', 'Soru Çözümü' => '✏️',
    'Özet' => '📝', 'Kod' => '💻', 'Formül' => '🧮', 'Deney' => '🔬'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <title>Hesabım | notewarehouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .smooth { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
    </style>
</head>
<body class="bg-slate-50 flex flex-col min-h-screen">

<nav class="bg-[#4f46e5] px-8 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center space-x-2 cursor-pointer" onclick="window.location.href='index.php'">
        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center"><i class="fas fa-book-open text-white"></i></div>
        <span class="text-white font-black tracking-tighter text-xl">notewarehouse</span>
    </div>
    <div class="flex items-center space-x-4">
        <a href="notlar.php" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl border border-white/30 text-sm font-bold smooth flex items-center">
            <i class="fas fa-plus mr-2"></i> Not Ekle
        </a>
        <a href="cikis.php" class="text-white/80 hover:text-white text-sm font-medium">Çıkış Yap</a>
    </div>
</nav>

<div class="w-full h-64 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
</div>

<main class="container mx-auto px-6 pb-16 flex-grow -mt-24 relative z-10">
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- SOL: Profil kartı -->
        <div class="w-full lg:w-1/3">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 text-center relative pt-20">
                <div class="absolute -top-16 left-1/2 transform -translate-x-1/2">
                    <div class="p-2 bg-white rounded-full shadow-lg">
                        <img src="<?= $profil_resmi ?>" alt="Profil" class="w-32 h-32 rounded-full object-cover border-4 border-slate-50">
                    </div>
                </div>

                <div class="flex items-center justify-center gap-2 mt-2">
                    <h2 class="text-2xl font-extrabold text-slate-800"><?= $kullanici_adi ?></h2>
                    <?php if ($kullanici_rol === 'admin'): ?>
                        <span title="Yönetici" class="bg-red-500 text-white text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-full">ADMİN</span>
                    <?php endif; ?>
                </div>
                <p class="text-slate-500 text-sm mt-1 mb-6"><i class="fas fa-envelope mr-2 opacity-70"></i><?= $kullanici_eposta ?></p>

                <!-- 4 istatistik kutusu -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-indigo-50 rounded-2xl p-4 text-center">
                        <p class="text-3xl font-black text-indigo-600"><?= $toplamNot ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1">Not Paylaştı</p>
                    </div>
                    <div class="bg-emerald-50 rounded-2xl p-4 text-center">
                        <p class="text-3xl font-black text-emerald-500"><?= $toplamBegeni ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1">Aldığı Beğeni</p>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-4 text-center">
                        <p class="text-3xl font-black text-rose-500"><?= $begendiklerim ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1">Beğendiklerim</p>
                    </div>
                    <div class="bg-amber-50 rounded-2xl p-4 text-center">
                        <p class="text-3xl font-black text-amber-500"><?= $ortalamaPuan ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1">Test Ortalaması</p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 text-xs text-slate-400">
                    <i class="far fa-calendar-alt mr-1"></i> Üyelik: <?= $kayit_tarihi ?>
                </div>

                <div class="space-y-3 mt-6">
                    <a href="calisma_alani.php" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl smooth shadow-md">
                        <i class="fas fa-folder-open mr-2"></i> Çalışma Alanım
                    </a>
                    <a href="sifrem.php" class="block w-full bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-bold py-3 rounded-xl smooth">
                        <i class="fas fa-key mr-2"></i> Şifre Değiştir
                    </a>
                </div>
            </div>
        </div>

        <!-- SAĞ: Notlar -->
        <div class="w-full lg:w-2/3">
            <div class="flex items-center justify-between mb-6 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h3 class="text-xl font-extrabold flex items-center">
                    <i class="fas fa-layer-group text-indigo-500 mr-3 text-2xl"></i> Paylaştığım Notlar
                </h3>
                <span class="bg-indigo-50 text-indigo-600 py-1 px-4 rounded-full text-sm font-bold border border-indigo-100">
                    Toplam <?= $toplamNot ?>
                </span>
            </div>

            <?php if ($toplamNot === 0): ?>
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm">
                    <div class="w-20 h-20 bg-indigo-50 text-indigo-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-700 mb-2">Henüz not yok</h4>
                    <p class="text-slate-400 text-sm mb-6">İlk notunu paylaşarak başla.</p>
                    <a href="notlar.php" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold">İlk Notumu Ekle</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?php foreach ($notlar as $not):
                        $ikon = $katIkon[$not['category']] ?? '📄';
                    ?>
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 smooth group">
                            <div class="flex justify-between items-start mb-3">
                                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-2xl border border-slate-100 group-hover:scale-110 smooth">
                                    <?= $ikon ?>
                                </div>
                                <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-3 py-1.5 rounded-full uppercase">
                                    <?= htmlspecialchars($not['edu_level']) ?>
                                </span>
                            </div>
                            <h4 class="font-extrabold text-slate-800 text-lg mb-1 line-clamp-2 leading-tight group-hover:text-indigo-600 smooth">
                                <?= htmlspecialchars($not['title']) ?>
                            </h4>
                            <p class="text-xs text-slate-400 font-medium mb-4">
                                <?= htmlspecialchars($not['subject']) ?> · <?= date('d M Y', strtotime($not['created_at'])) ?>
                            </p>
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                <div class="flex space-x-3 text-xs">
                                    <span class="text-emerald-500 font-bold"><i class="fas fa-thumbs-up mr-1"></i><?= $not['likes'] ?></span>
                                    <span class="text-rose-400 font-bold"><i class="fas fa-thumbs-down mr-1"></i><?= $not['dislikes'] ?></span>
                                </div>
                                <a href="notlar.php?id=<?= $not['id'] ?>" class="text-indigo-600 hover:text-indigo-700 text-xs font-bold">
                                    Aç <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <a href="notlar.php" class="bg-indigo-50/50 p-6 rounded-3xl border-2 border-dashed border-indigo-200 hover:border-indigo-400 hover:bg-indigo-50 smooth flex flex-col items-center justify-center text-center min-h-[180px] group">
                        <div class="w-14 h-14 bg-indigo-100 text-indigo-500 rounded-full flex items-center justify-center text-2xl mb-3 group-hover:scale-110 smooth group-hover:bg-indigo-500 group-hover:text-white">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h4 class="font-bold text-indigo-800">Yeni Not Ekle</h4>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer class="bg-white border-t border-slate-200 text-slate-400 py-8 text-center text-sm">
    &copy; <?= date("Y") ?> notewarehouse
</footer>
</body>
</html>
