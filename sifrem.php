<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/baglan.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/oturum_baslat.php';

$mesaj = "";
$mesaj_turu = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mesaj = "Lütfen geçerli bir e-posta adresi girin.";
        $mesaj_turu = "hata";
    } else {
        // E-postanın veritabanında var olup olmadığını kontrol et
        $kontrol = $db->prepare("SELECT id, ad FROM users WHERE email = ?");
        $kontrol->execute([$email]);
        $kullanici = $kontrol->fetch(PDO::FETCH_ASSOC);

        if (!$kullanici) {
            $mesaj = "Bu e-posta ile kayıtlı bir hesap bulunamadı.";
            $mesaj_turu = "hata";
        } else {
            // 6 haneli güvenli kod
            $onay_kodu = str_pad(random_int(0, 999999), 6, "0", STR_PAD_LEFT);
            $_SESSION['sifre_sifirlama'] = [
                'kod'         => $onay_kodu,
                'email'       => $email,
                'kullanici_ad'=> $kullanici['ad'],
                'gecerlilik'  => time() + 600, // 10 dakika
                'denemeler'   => 0,
            ];

            // Veritabanına da kaydet (yedek)
            try {
                $db->prepare("UPDATE users SET reset_code = ? WHERE email = ?")
                   ->execute([$onay_kodu, $email]);
            } catch (Exception $e) {}

            // E-posta gönder
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mikailcelik4734@gmail.com';
                $mail->Password   = 'vbhh kzlr ucpl swkq';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('mikailcelik4734@gmail.com', 'notewarehouse Destek');
                $mail->addAddress($email, $kullanici['ad']);

                $mail->isHTML(true);
                $mail->Subject = 'notewarehouse - Şifre Sıfırlama Kodun';
                $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #4f46e5, #9333ea); padding: 30px; text-align: center; border-radius: 12px 12px 0 0; color: white;'>
                        <h1 style='margin: 0; font-size: 24px;'>📚 notewarehouse</h1>
                    </div>
                    <div style='background: #f8fafc; padding: 30px; border-radius: 0 0 12px 12px; border: 1px solid #e2e8f0; border-top: none;'>
                        <h2 style='color: #1e293b; margin-top: 0;'>Merhaba {$kullanici['ad']},</h2>
                        <p style='color: #475569; line-height: 1.6;'>
                            Hesabın için şifre sıfırlama isteğin aldık. Aşağıdaki 6 haneli kodu sıfırlama sayfasına gir:
                        </p>
                        <div style='background: white; padding: 25px; text-align: center; border-radius: 10px; border: 2px dashed #6366f1; margin: 20px 0;'>
                            <div style='font-size: 38px; font-weight: 900; color: #4f46e5; letter-spacing: 12px; font-family: monospace;'>
                                $onay_kodu
                            </div>
                        </div>
                        <p style='color: #64748b; font-size: 13px; margin: 20px 0 0;'>
                            ⏱️ Bu kod <strong>10 dakika</strong> geçerlidir.
                        </p>
                        <p style='color: #64748b; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;'>
                            Bu işlemi sen yapmadıysan bu e-postayı görmezden gelebilirsin. Şifren güvende.
                        </p>
                    </div>
                    <p style='text-align: center; color: #94a3b8; font-size: 11px; margin-top: 15px;'>
                        notewarehouse · <a href='https://notewarehouse.com' style='color: #4f46e5;'>notewarehouse.com</a>
                    </p>
                </div>";

                $mail->send();

                header("Location: sifrem1.php");
                exit;

            } catch (Exception $e) {
                $mesaj = "E-posta gönderilemedi. Lütfen daha sonra tekrar dene.";
                $mesaj_turu = "hata";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #4f46e5 0%, #9333ea 50%, #ec4899 100%); }
        .glass { background: rgba(255, 255, 255, 0.97); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">

        <!-- Adım göstergesi -->
        <div class="flex items-center justify-center mb-6 gap-2">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-white text-indigo-600 flex items-center justify-center font-black text-sm shadow-lg">1</div>
                <span class="ml-2 text-white text-xs font-bold">E-posta</span>
            </div>
            <div class="w-8 h-0.5 bg-white/30"></div>
            <div class="flex items-center opacity-50">
                <div class="w-8 h-8 rounded-full bg-white/30 text-white flex items-center justify-center font-black text-sm">2</div>
                <span class="ml-2 text-white/70 text-xs font-bold">Kod</span>
            </div>
            <div class="w-8 h-0.5 bg-white/30"></div>
            <div class="flex items-center opacity-50">
                <div class="w-8 h-8 rounded-full bg-white/30 text-white flex items-center justify-center font-black text-sm">3</div>
                <span class="ml-2 text-white/70 text-xs font-bold">Yeni Şifre</span>
            </div>
        </div>

        <a href="giris.php" class="inline-flex items-center text-white/80 hover:text-white mb-4 transition text-sm font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> Giriş Ekranına Dön
        </a>

        <div class="glass rounded-3xl shadow-2xl p-10">
            <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mb-5">
                <i class="fas fa-envelope text-3xl text-indigo-600"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-2">Şifreni mi Unuttun?</h2>
            <p class="text-slate-500 text-sm mb-7 leading-relaxed">
                Kayıtlı e-posta adresini gir, sana 6 haneli onay kodu gönderelim.
            </p>

            <?php if($mesaj): ?>
                <div class="mb-5 p-4 rounded-xl text-sm font-bold <?= $mesaj_turu == 'basari' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                    <i class="fas <?= $mesaj_turu == 'basari' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= $mesaj ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">E-posta Adresin</label>
                    <div class="relative">
                        <i class="far fa-envelope absolute left-4 top-4 text-slate-400"></i>
                        <input type="email" name="email" placeholder="ornek@mail.com" required autofocus
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3.5 pl-12 pr-4 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm font-medium">
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl active:scale-[0.98] transition-all">
                    <i class="fas fa-paper-plane mr-2"></i> Kodu Gönder
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="bg-indigo-50 rounded-xl p-3 flex items-start gap-3">
                    <i class="fas fa-info-circle text-indigo-600 mt-0.5"></i>
                    <p class="text-xs text-indigo-800 leading-relaxed">
                        E-postan gelmezse <strong>Spam (Gereksiz)</strong> klasörünü kontrol et. Kod 10 dakika geçerlidir.
                    </p>
                </div>
            </div>
        </div>

        <p class="text-center text-white/60 text-[10px] mt-6 font-bold uppercase tracking-widest">
            &copy; <?= date("Y") ?> notewarehouse · Güvenlik
        </p>
    </div>
</body>
</html>
