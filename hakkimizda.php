<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/seo.php';

// İstatistikler
$stat = [
    'kullanici' => 0, 'not' => 0, 'beğeni' => 0, 'grup' => 0
];
try {
    $stat['kullanici'] = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stat['not'] = (int)$db->query("SELECT COUNT(*) FROM notes")->fetchColumn();
    $stat['beğeni'] = (int)$db->query("SELECT COALESCE(SUM(likes),0) FROM notes")->fetchColumn();
    $stat['grup'] = (int)$db->query("SELECT COUNT(*) FROM gruplar")->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php seoMeta('Hakkımızda', "notewarehouse hakkında: kim olduğumuz, neden bu platformu kurduğumuz, ekibimiz ve vizyonumuz. Türkiye'nin ücretsiz öğrenci not paylaşım platformu."); ?>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50">

<nav class="bg-indigo-600 px-8 py-4 shadow-lg flex justify-between items-center sticky top-0 z-50">
    <a href="index.php" class="flex items-center text-white font-black text-xl">
        <img src="/favicon-180.png" class="w-8 h-8 rounded-lg mr-2"> notewarehouse
    </a>
    <a href="index.php" class="text-white/90 hover:text-white text-sm">← Anasayfa</a>
</nav>

<!-- HERO -->
<header class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 text-white py-20 px-6 text-center">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl md:text-6xl font-black mb-6">Bilgi, paylaşılınca çoğalır.</h1>
        <p class="text-xl text-indigo-100 leading-relaxed">
            notewarehouse, Türkiye'nin öğrencileri için yapay zeka destekli, ücretsiz ders notu paylaşım platformudur.
            Bilginin demokratikleşmesi gerektiğine inanıyoruz.
        </p>
    </div>
</header>

<!-- İSTATİSTİKLER -->
<section class="py-16 px-6 bg-white">
    <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div><p class="text-5xl font-black text-indigo-600"><?= $stat['kullanici'] ?>+</p><p class="text-slate-500 mt-2 font-bold">Kayıtlı Öğrenci</p></div>
        <div><p class="text-5xl font-black text-purple-600"><?= $stat['not'] ?>+</p><p class="text-slate-500 mt-2 font-bold">Paylaşılan Not</p></div>
        <div><p class="text-5xl font-black text-pink-600"><?= $stat['beğeni'] ?>+</p><p class="text-slate-500 mt-2 font-bold">Toplam Beğeni</p></div>
        <div><p class="text-5xl font-black text-amber-500"><?= $stat['grup'] ?>+</p><p class="text-slate-500 mt-2 font-bold">Çalışma Grubu</p></div>
    </div>
</section>

<!-- HİKAYE -->
<section class="py-16 px-6 bg-slate-50">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-4xl font-black text-slate-800 mb-6">📖 Hikayemiz</h2>
        <div class="prose prose-lg text-slate-700 leading-relaxed space-y-4">
            <p>
                <strong>2026 yılında</strong>, üniversite öğrencileri olarak bir gerçeği fark ettik:
                Türkiye'de milyonlarca öğrenci sınavlara hazırlanırken **kaliteli ve sınıflandırılmış ders notu**na erişmekte zorlanıyor.
            </p>
            <p>
                Kütüphanede yer bulamayan, arkadaşından not isteyen, internette saatlerce arayan öğrenciler için bir çözüm tasarladık.
                Hem teknik beceri hem de eğitim sevgimizi birleştirdiğimiz bu projeye <strong>notewarehouse</strong> adını verdik —
                "notların deposu".
            </p>
            <p>
                Projemiz sadece bir not arşivi değil; <strong>yapay zeka destekli özet</strong>,
                <strong>otomatik soru üretimi</strong>, <strong>quiz çözücü</strong>, <strong>canlı sınav simülasyonu</strong> ve
                <strong>oyunlaştırma (XP, rozet)</strong> sistemleriyle eğitimi keyifli hale getiren bir ekosistem.
            </p>
        </div>
    </div>
</section>

<!-- ÖZELLİKLER -->
<section class="py-16 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-4xl font-black text-center text-slate-800 mb-12">🚀 notewarehouse'da Neler Var?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            $ozellikler = [
                ['icon'=>'📚','baslik'=>'Ücretsiz Not Paylaşımı','aciklama'=>'İlkokuldan üniversiteye 4 seviye, 100+ ders. Hepsi ücretsiz, hepsi açık.'],
                ['icon'=>'🤖','baslik'=>'AI Asistan','aciklama'=>'Notunu özetler, öğretmen gibi anlatır, otomatik test soruları üretir.'],
                ['icon'=>'✏️','baslik'=>'Quiz Çözücü','aciklama'=>'Çoktan seçmeli testleri gerçek sınav arayüzünde çöz, puanını gör.'],
                ['icon'=>'🏆','baslik'=>'XP & Rozet Sistemi','aciklama'=>'Her aksiyon XP kazandırır. 14 farklı rozet, streak, liderlik tablosu.'],
                ['icon'=>'👥','baslik'=>'Çalışma Grupları','aciklama'=>'Arkadaşlarını davet et, ortak notlar yazın, birlikte hazırlanın.'],
                ['icon'=>'🎯','baslik'=>'Canlı Sınav','aciklama'=>'Belirlenen saatte tüm site katılır, sıralama açıklanır.'],
            ];
            foreach ($ozellikler as $o): ?>
                <div class="bg-slate-50 rounded-3xl p-8 hover:shadow-xl transition">
                    <div class="text-5xl mb-4"><?= $o['icon'] ?></div>
                    <h3 class="text-xl font-black text-slate-800 mb-2"><?= $o['baslik'] ?></h3>
                    <p class="text-slate-600 leading-relaxed"><?= $o['aciklama'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- EKİP -->
<section class="py-16 px-6 bg-gradient-to-b from-slate-50 to-white">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-4xl font-black text-center text-slate-800 mb-12">👨‍💻 Ekibimiz</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100 text-center">
                <img src="https://ui-avatars.com/api/?name=Mikail+Celik&background=4f46e5&color=fff&size=200&bold=true"
                     class="w-32 h-32 rounded-full mx-auto mb-4 border-4 border-indigo-100">
                <h3 class="text-2xl font-black text-slate-800">Mikail ÇELİK</h3>
                <p class="text-indigo-600 font-bold text-sm mb-3">Backend & DevOps</p>
                <p class="text-slate-600 text-sm">Sunucu mimarisi, veritabanı tasarımı, AI entegrasyonu ve AWS deployment.</p>
            </div>

            <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100 text-center">
                <img src="https://ui-avatars.com/api/?name=Mustafa+Kabatas&background=9333ea&color=fff&size=200&bold=true"
                     class="w-32 h-32 rounded-full mx-auto mb-4 border-4 border-purple-100">
                <h3 class="text-2xl font-black text-slate-800">Mustafa KABATAŞ</h3>
                <p class="text-purple-600 font-bold text-sm mb-3">Frontend & UX</p>
                <p class="text-slate-600 text-sm">Kullanıcı arayüzü tasarımı, responsive geliştirme, karanlık mod ve etkileşim.</p>
            </div>

        </div>
    </div>
</section>

<!-- MİSYON & VİZYON -->
<section class="py-16 px-6 bg-indigo-950 text-white">
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12">
        <div>
            <h3 class="text-3xl font-black mb-4 text-indigo-300">🎯 Misyonumuz</h3>
            <p class="text-indigo-100 leading-relaxed">
                Türkiye'deki her öğrencinin <strong>ücretsiz</strong>, <strong>kaliteli</strong> ve <strong>modern</strong> eğitim araçlarına erişebileceği bir platform sunmak. Bilginin paylaşılması ve modern teknolojilerle (yapay zeka, oyunlaştırma) desteklenmesi.
            </p>
        </div>
        <div>
            <h3 class="text-3xl font-black mb-4 text-pink-300">🌟 Vizyonumuz</h3>
            <p class="text-indigo-100 leading-relaxed">
                2030 yılına kadar Türkiye'nin <strong>1 numaralı öğrenci platformu</strong> olmak. Sonra Balkanlar ve Orta Asya'ya yayılarak Türk dilinde eğitim alan 100 milyon öğrenciye ulaşmak.
            </p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 px-6 bg-white text-center">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-4xl font-black text-slate-800 mb-4">Hemen Başla 🚀</h2>
        <p class="text-slate-600 text-lg mb-8">Ücretsiz kayıt ol, ilk notunu paylaş, XP kazanmaya başla.</p>
        <a href="kaydol.php" class="inline-block bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-10 py-4 rounded-2xl font-black text-lg shadow-xl hover:shadow-2xl transition">
            ÜCRETSİZ KAYIT OL
        </a>
    </div>
</section>

<?php include __DIR__ . '/footer_partial.php'; ?>

</body>
</html>
