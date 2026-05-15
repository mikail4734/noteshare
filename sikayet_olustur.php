<?php
session_start();
require_once 'baglan.php';


if (!isset($_SESSION['user_email'])) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>Şikayet veya bildirim oluşturmak için giriş yapmalısınız. <a href='giris.php'>Giriş Yap</a></div>");
}

$mesaj_durum = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sikayet_gonder'])) {
    $konu = htmlspecialchars(strip_tags(trim($_POST['konu'])));
    $mesaj = htmlspecialchars(strip_tags(trim($_POST['mesaj'])));
    $email = $_SESSION['user_email'];

    if (!empty($konu) && !empty($mesaj)) {
        $kaydet = $db->prepare("INSERT INTO sikayetler (kullanici_email, konu, mesaj) VALUES (?, ?, ?)");
        if ($kaydet->execute([$email, $konu, $mesaj])) {
            $mesaj_durum = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Bildiriminiz başarıyla iletildi. Teşekkür ederiz!</div>';
        } else {
            $mesaj_durum = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Bir hata oluştu, lütfen tekrar deneyin.</div>';
        }
    } else {
        $mesaj_durum = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">Lütfen tüm alanları doldurun.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şikayet / Bildirim Oluştur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-indigo-600 px-6 py-4">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i> Bize Bildirin
            </h2>
            <p class="text-indigo-100 text-sm mt-1">Önerileriniz, şikayetleriniz veya karşılaştığınız sorunları buradan iletebilirsiniz.</p>
        </div>

        <div class="p-6">
            <?= $mesaj_durum ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Konu</label>
                    <select name="konu" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" required>
                        <option value="">Lütfen bir konu seçin...</option>
                        <option value="Teknik Sorun">Sistem / Teknik Sorun</option>
                        <option value="Kullanıcı Şikayeti">Kullanıcı Şikayeti</option>
                        <option value="İçerik Şikayeti">Uygunsuz Not / İçerik</option>
                        <option value="Öneri">Geliştirme Önerisi</option>
                        <option value="Diğer">Diğer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mesajınız</label>
                    <textarea name="mesaj" rows="5" placeholder="Detayları buraya yazın..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition resize-none" required></textarea>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a href="index.php" class="text-slate-500 hover:text-slate-800 font-medium transition">Ana Sayfaya Dön</a>
                    <button type="submit" name="sikayet_gonder" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition duration-300 shadow-md hover:shadow-lg flex items-center">
                        Gönder <i class="fas fa-paper-plane ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>