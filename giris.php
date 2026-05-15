<?php
session_start();
require_once 'baglan.php';


if (isset($_SESSION['user_email'])) {
    $durum_sorgu = $db->prepare("SELECT durum FROM users WHERE email = ?");
    $durum_sorgu->execute([$_SESSION['user_email']]);
    $guncel_durum = $durum_sorgu->fetchColumn();

    if ($guncel_durum == 0) {
        
        session_destroy();
        header("Location: giris.php?hata=engellendiniz");
        exit;
    }
}


if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$hata_mesaji = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loginsubmit'])) {
    
    // Formdan gelen verileri al
    $kullanici_bilgisi = trim($_POST['username'] ?? ''); 
    $sifre = $_POST['password'] ?? '';

    if (empty($kullanici_bilgisi) || empty($sifre)) {
        $hata_mesaji = "Lütfen e-posta/kullanıcı adı ve şifrenizi girin.";
    } else {
        try {
           
            $sorgu = $db->prepare("SELECT * FROM users WHERE email = ? OR ad = ?");
            $sorgu->execute([$kullanici_bilgisi, $kullanici_bilgisi]);
            $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

            
            if ($kullanici) {
                
               
                if (isset($kullanici['durum']) && $kullanici['durum'] == 0) {
                    $hata_mesaji = "Hesabınız yönetici tarafından engellenmiştir.";
                } else {
                    
                    
                    if (password_verify($sifre, $kullanici['password'])) {

                        // SÜPER ADMIN OTOMATİK YETKİLENDİRME
                        $SUPER_ADMINS = ['mikailcelik4734@gmail.com'];
                        if (in_array(strtolower($kullanici['email']), $SUPER_ADMINS) && $kullanici['rol'] !== 'admin') {
                            $db->prepare("UPDATE users SET rol = 'admin' WHERE email = ?")->execute([$kullanici['email']]);
                            $kullanici['rol'] = 'admin';
                        }

                        // Şifre doğru! Oturum bilgilerini kaydet
                        $_SESSION['logged_in'] = true;
                        $_SESSION['user_id'] = $kullanici['id'];
                        $_SESSION['user_email'] = $kullanici['email'];
                        $_SESSION['user_name'] = $kullanici['ad'];
                        $_SESSION['rol'] = $kullanici['rol'];

                        header("Location: index.php");
                        exit;

                    } else {
                        $hata_mesaji = "Hatalı şifre girdiniz.";
                    }
                }
            } else {
                $hata_mesaji = "Bu bilgilere ait bir hesap bulunamadı.";
            }
        } catch (PDOException $e) {
            $hata_mesaji = "Bağlantı hatası: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteShare | Giriş Yap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-xl mb-4 text-indigo-600 text-3xl">
                <i class="fas fa-file-signature"></i>
            </div>
            <h1 class="text-white text-3xl font-extrabold tracking-tight">NoteShare Pro</h1>
            <p class="text-indigo-100 text-sm mt-2 font-medium">Bilgi paylaştıkça çoğalır.</p>
        </div>

        <div class="glass rounded-[2rem] shadow-2xl p-10">
            <h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Tekrar Hoş Geldin!</h2>
            
            <?php if(!empty($hata_mesaji)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-bold border border-red-100 italic text-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $hata_mesaji; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Kullanıcı Adı veya E-posta</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <i class="far fa-user"></i>
                        </span>
                        <input type="text" name="username" placeholder="mikail_06" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-11 pr-4 text-sm outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-medium text-slate-700">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Şifre</label>
                        <a href="sifrem.php" class="text-xs font-bold text-indigo-600 hover:underline">Şifremi Unuttum</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" placeholder="••••••••" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-11 pr-4 text-sm outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-medium text-slate-700">
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm font-semibold text-slate-600">Beni hatırla</label>
                </div>

                <button type="submit" name="loginsubmit"
                    class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-[0.98] transition-all">
                    Oturum Aç
                </button>
            </form>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white px-4 text-slate-400 font-bold">Veya şununla devam et</span>
                </div>
            </div>

            <a href="fb-login-calistir.php" 
                class="w-full flex items-center justify-center bg-[#1877F2] text-white py-4 rounded-xl font-bold text-sm shadow-lg hover:bg-[#166fe5] active:scale-[0.98] transition-all">
                <i class="fab fa-facebook text-lg mr-3"></i>
                Facebook ile Giriş Yap
            </a>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500 font-medium">
                    Henüz hesabın yok mu? 
                    <a href="kaydol.php" class="text-indigo-600 font-bold hover:underline ml-1">Kayıt Ol</a>
                </p>
            </div>
        </div>

        <p class="text-center text-indigo-200 text-xs mt-8 font-medium">
            &copy; <?php echo date("Y"); ?> NoteShare Engineering. Tüm hakları saklıdır.
        </p>
    </div>

</body>
</html>