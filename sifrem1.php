<?php
session_start();

// Veritabanı bağlantısını dahil ediyoruz
require_once 'baglan.php';

// Eğer e-posta girilmeden (direkt linkle) bu sayfaya gelinmişse ilk sayfaya geri gönder
if (!isset($_SESSION['onay_kodu']) || !isset($_SESSION['sifirlama_email'])) {
    header("Location: sifre-sifirla.php");
    exit();
}

$mesaj = "";
$mesaj_turu = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $girilen_kod = $_POST['kod'];
    $yeni_sifre = $_POST['yeni_sifre'];

    // Session'da sakladığımız kod ile kullanıcının yazdığı kodu karşılaştırıyoruz
    if ($girilen_kod == $_SESSION['onay_kodu']) {
        
        // 1. Yeni şifreyi güvenli hale getir (Hashleme)
        $guvenli_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        $email = $_SESSION['sifirlama_email'];

        try {
            // 2. Veritabanını Güncelle
            // users tablosundaki password sütununu yeni şifreyle değiştiriyoruz
            $guncelleSorgu = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $guncelleSorgu->execute([$guvenli_sifre, $email]);

            $mesaj = "Şifreniz başarıyla güncellendi! Giriş sayfasına yönlendiriliyorsunuz...";
            $mesaj_turu = "basari";
            
            // İşlem bittiği için verileri temizle
            session_destroy(); 
            
            // 3 saniye sonra otomatik olarak giriş sayfasına at
            header("Refresh: 3; url=giris.php");
        } catch(PDOException $e) {
            $mesaj = "Veritabanı güncellenirken hata oluştu: " . $e->getMessage();
            $mesaj_turu = "hata";
        }

    } else {
        $mesaj = "Girdiğiniz onay kodu hatalı!";
        $mesaj_turu = "hata";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteShare | Kodu Doğrula</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <div class="glass rounded-[2rem] shadow-2xl p-10 relative overflow-hidden">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Kodu Doğrula</h2>
            <p class="text-slate-500 text-sm mb-8">
                <b><?php echo $_SESSION['sifirlama_email']; ?></b> adresine gelen 4 haneli kodu ve yeni şifrenizi girin.
            </p>

            <?php if($mesaj): ?>
                <div class="mb-6 p-4 rounded-xl text-xs font-bold <?php echo $mesaj_turu == 'basari' ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100'; ?>">
                    <i class="fas <?php echo $mesaj_turu == 'basari' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo $mesaj; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Onay Kodu</label>
                    <input type="text" name="kod" maxlength="4" placeholder="0000" required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-4 px-4 text-center text-3xl font-bold tracking-[0.5em] outline-none focus:ring-2 focus:ring-indigo-500 text-indigo-600 transition-all">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Yeni Şifre</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="yeni_sifre" placeholder="••••••••" required 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-4 pl-12 px-4 outline-none focus:ring-2 focus:ring-indigo-500 font-medium transition-all">
                    </div>
                </div>

                <button type="submit" class="w-full bg-green-600 text-white py-4 rounded-xl font-bold shadow-lg hover:bg-green-700 active:scale-[0.98] transition-all flex items-center justify-center">
                    <i class="fas fa-check-circle mr-2"></i> Şifreyi Güncelle
                </button>
                
                <a href="sifre-sifirla.php" class="block text-center text-xs text-indigo-600 font-bold mt-4 hover:underline">
                    <i class="fas fa-redo-alt mr-1"></i> Kodu tekrar gönder / E-posta değiştir
                </a>
            </form>
        </div>
    </div>

</body>
</html>