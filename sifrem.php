<?php
/**
 * NoteShare - Şifre Sıfırlama Sayfası (PHPMailer Entegreli)
 */

// 1. PHPMailer Kütüphanesini Dahil Et
require 'vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$mesaj = "";
$mesaj_turu = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        // Onay kodu üret (Örn: 5821)
        $onay_kodu = rand(1000, 9999);
        $_SESSION['onay_kodu'] = $onay_kodu;
        $_SESSION['sifirlama_email'] = $email;

        $mail = new PHPMailer(true);

        try {
            // --- SMTP AYARLARI ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'mikailcelik4734@gmail.com'; // Kendi Gmail adresini yaz
            $mail->Password   = 'vbhh kzlr ucpl swkq'; // Google'dan aldığın 16 haneli Uygulama Şifresi
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // --- ALICI AYARLARI ---
            $mail->setFrom('mikailcelik4734@gmail.com', 'NoteShare Destek');
            $mail->addAddress($email);

            // --- İÇERİK ---
            $mail->isHTML(true);
            $mail->Subject = 'NoteShare Şifre Sıfırlama Kodu';
            $mail->Body    = "
                <div style='font-family: sans-serif; border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #4f46e5;'>NoteShare Güvenlik</h2>
                    <p>Merhaba, şifreni sıfırlamak için kullanman gereken onay kodu:</p>
                    <div style='background: #f8fafc; padding: 15px; font-size: 24px; font-weight: bold; text-align: center; color: #1e293b; letter-spacing: 5px; border: 2px dashed #cbd5e1;'>
                        $onay_kodu
                    </div>
                    <p style='font-size: 12px; color: #64748b; margin-top: 20px;'>Eğer bu işlemi sen yapmadıysan bu maili görmezden gelebilirsin.</p>
                </div>";

            $mail->send();
            
            // Başarılıysa yönlendir (Önceki hatayı düzelttik: Buton içindeki <a> kaldırıldı)
            header("Location: sifrem1.php"); 
            exit();

        } catch (Exception $e) {
            $mesaj = "Hata oluştu: Mail gönderilemedi.";
            $mesaj_turu = "hata";
        }
    } else {
        $mesaj = "Lütfen geçerli bir e-posta adresi giriniz.";
        $mesaj_turu = "hata";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteShare | Şifremi Unuttum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <a href="giris.php" class="inline-flex items-center text-indigo-100 hover:text-white mb-6 transition-colors font-semibold text-sm">
            <i class="fas fa-arrow-left mr-2"></i> Giriş Ekranına Dön
        </a>

        <div class="glass rounded-[2rem] shadow-2xl p-10 relative overflow-hidden">
            <i class="fas fa-key absolute -right-4 -top-4 text-slate-100 text-8xl opacity-50 rotate-12"></i>

            <div class="relative z-10">
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Şifreni mi Unuttun?</h2>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">
                    Kayıtlı e-posta adresini gir, sana 4 haneli bir onay kodu gönderelim.
                </p>

                <?php if($mesaj): ?>
                    <div class="mb-6 p-4 rounded-xl text-xs font-bold <?php echo $mesaj_turu == 'basari' ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100'; ?>">
                        <i class="fas <?php echo $mesaj_turu == 'basari' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                        <?php echo $mesaj; ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">E-posta Adresin</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="far fa-envelope"></i>
                            </span>
                            <input type="email" name="email" placeholder="ornek@mail.com" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-4 pl-12 pr-4 text-sm outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-medium text-slate-700">
                        </div>
                    </div>

                    <button type="submit" 
                        class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-[0.98] transition-all flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2 text-xs"></i> Onay Kodu Gönder
                    </button>
                </form>

                <div class="mt-10 pt-6 border-t border-slate-100">
                    <div class="bg-indigo-50 rounded-2xl p-4 flex items-start space-x-3">
                        <i class="fas fa-info-circle text-indigo-600 mt-1"></i>
                        <p class="text-[11px] text-indigo-800 font-medium leading-normal">
                            Onay kodun birkaç dakika içinde gelmezse lütfen Spam (Gereksiz) klasörünü kontrol et.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-indigo-200 text-[10px] mt-8 font-black uppercase tracking-widest">
            &copy; <?php echo date("Y"); ?> NoteShare Güvenlik Katmanı v2.0
        </p>
    </div>

</body>
</html>