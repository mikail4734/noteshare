<?php
/**
 * NoteShare - Gizlilik Sözleşmesi Sayfası
 */

// Sayfa Ayarları
$sayfa_basligi = "Gizlilik Sözleşmesi";
$versiyon = "1.0.2";
$son_guncelleme = "10 Nisan 2026";
$site_adi = "NoteShare";

// İleride bu metinleri veritabanından çekmek istersen bu yapıyı kullanabilirsin
$sozlesme_maddeleri = [
    [
        "icon" => "fa-user-secret",
        "baslik" => "1. Veri Toplama",
        "icerik" => "{$site_adi}, kayıt sırasında verdiğiniz isim, e-posta adresi ve eğitim seviyesi gibi bilgileri hizmet kalitesini artırmak amacıyla saklar. Şifreleriniz sisteme kriptolanarak kaydedilir ve bizim tarafımızdan dahi görüntülenemez."
    ],
    [
        "icon" => "fa-eye",
        "baslik" => "2. Verilerin Kullanımı",
        "icerik" => "Toplanan veriler şu amaçlarla kullanılır:",
        "liste" => [
            "Hesabınızın güvenliğini sağlamak.",
            "Size özel ders notu önerileri sunmak.",
            "Platform üzerindeki teknik hataları tespit etmek ve gidermek."
        ]
    ],
    [
        "icon" => "fa-shield-alt",
        "baslik" => "3. Üçüncü Taraflarla Paylaşım",
        "icerik" => "Kişisel verileriniz, yasal zorunluluklar haricinde asla üçüncü şahıslarla paylaşılmaz veya ticari amaçla satılmaz. Google Analytics gibi araçlar anonim olarak trafik analizi yapmak için kullanılabilir."
    ],
    [
        "icon" => "fa-cookie-bite",
        "baslik" => "4. Çerezler (Cookies)",
        "icerik" => "Sitemiz, giriş bilgilerinizi hatırlamak ve deneyiminizi kişiselleştirmek için tarayıcı çerezlerini kullanır. Tarayıcı ayarlarınızdan çerezleri reddetme hakkına sahipsiniz."
    ],
    [
        "icon" => "fa-trash-alt",
        "baslik" => "5. Veri Silme Hakkı",
        "icerik" => "Kullanıcılar diledikleri zaman hesaplarını silme ve verilerinin sistemden kalıcı olarak kaldırılmasını talep etme hakkına sahiptir. Bunun için profil ayarlarınızı kullanabilir veya destek ekibine yazabilirsiniz."
    ]
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sayfa_basligi; ?> | <?php echo $site_adi; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b px-8 py-4 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="javascript:history.back()" class="text-slate-400 hover:text-indigo-600 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="font-bold text-xl text-indigo-600 italic"><?php echo $site_adi; ?></h1>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Versiyon: <?php echo $versiyon; ?></span>
        </div>
    </nav>

    <main class="container mx-auto max-w-4xl py-12 px-6">
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-10 border border-slate-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 border-b pb-4 gap-4">
                <h2 class="text-3xl font-black text-slate-900"><?php echo $sayfa_basligi; ?></h2>
                <span class="text-[10px] bg-indigo-50 text-indigo-600 font-bold px-3 py-1 rounded-full uppercase">
                    Son Güncelleme: <?php echo $son_guncelleme; ?>
                </span>
            </div>
            
            <div class="space-y-8 text-slate-600 leading-relaxed">
                
                <?php foreach($sozlesme_maddeleri as $madde): ?>
                <section>
                    <h3 class="text-lg font-bold text-slate-800 mb-3 flex items-center">
                        <i class="fas <?php echo $madde['icon']; ?> text-indigo-500 mr-2"></i> 
                        <?php echo $madde['baslik']; ?>
                    </h3>
                    <p><?php echo $madde['icerik']; ?></p>
                    
                    <?php if(isset($madde['liste'])): ?>
                    <ul class="list-disc ml-6 mt-4 space-y-2">
                        <?php foreach($madde['liste'] as $madde_icerik): ?>
                        <li><?php echo $madde_icerik; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </section>
                <?php endforeach; ?>

                <div class="mt-12 p-6 bg-slate-50 rounded-2xl border border-slate-200">
                    <p class="text-sm text-slate-500 font-medium text-center italic">
                        Gizliliğiniz bizim için önemlidir. Bu sözleşme üzerinde zaman zaman güncellemeler yapılabilir. 
                        Lütfen belirli aralıklarla bu sayfayı kontrol ediniz.
                    </p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>