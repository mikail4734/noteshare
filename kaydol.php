<?php
/**
 * NoteShare - Kayıt Ol Sayfası (PHP)
 */
session_start();
require_once 'baglan.php'; // Veritabanı bağlantını dahil ediyoruz

// Giriş yapmış bir kullanıcı varsa durumunu kontrol et
if (isset($_SESSION['user_email'])) {
    $durum_sorgu = $db->prepare("SELECT durum FROM users WHERE email = ?");
    $durum_sorgu->execute([$_SESSION['user_email']]);
    $guncel_durum = $durum_sorgu->fetchColumn();

    if ($guncel_durum == 0) {
        // Eğer kullanıcı o an engellendiyse oturumu sonlandır ve kov
        session_destroy();
        header("Location: giris.php?hata=engellendiniz");
        exit;
    }
}

$hata_mesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Formdan gelen verileri al ve boşlukları temizle
    $kullanici_adi = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['password'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;

    // Basit doğrulama
    if (empty($kullanici_adi) || empty($email) || empty($sifre)) {
        $hata_mesaji = "Lütfen tüm alanları doldurun.";
    } elseif (!$terms) {
        $hata_mesaji = "Lütfen gizlilik sözleşmesini kabul edin.";
    } else {
        try {
            // 1. Bu e-posta ile daha önce kayıt olunmuş mu kontrol et
            $kontrolSorgu = $db->prepare("SELECT id FROM users WHERE email = ?");
            $kontrolSorgu->execute([$email]);
            
            if ($kontrolSorgu->rowCount() > 0) {
                $hata_mesaji = "Bu e-posta adresi zaten kullanılıyor. Lütfen giriş yapmayı deneyin.";
            } else {
                // 2. Şifreyi güvenli hale getir (Hashleme)
                $guvenli_sifre = password_hash($sifre, PASSWORD_DEFAULT);

                // 3. Veritabanına Kaydet
                // Formdaki username'i tablodaki 'ad' sütununa yazıyoruz. Varsayılan rol 'user', durum '1' (aktif).
                $kayitSorgu = $db->prepare("INSERT INTO users (ad, email, password, rol, durum) VALUES (?, ?, ?, 'user', 1)");
                $kayitSorgu->execute([$kullanici_adi, $email, $guvenli_sifre]);

                // 4. Kayıt başarılıysa anında giriş yapmış say (Session tanımlama)
                $_SESSION['logged_in'] = true;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $kullanici_adi;
                $_SESSION['rol'] = 'user'; // Yeni kayıt olan herkes standart kullanıcıdır

                // 5. Ana sayfaya yönlendir
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            $hata_mesaji = "Kayıt sırasında bir hata oluştu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol | NoteShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Sosyal butonlar için özel hover efekti */
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-100 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-100 rounded-full blur-3xl opacity-50"></div>
    </div>

    <div class="bg-white w-full max-w-[1100px] rounded-[3rem] shadow-2xl shadow-indigo-200/50 overflow-hidden flex flex-col md:flex-row border border-white">
        
        <div class="md:w-5/12 bg-[#4f46e5] p-12 text-white flex flex-col justify-between relative">
            <div class="relative z-10">
                <div class="flex items-center space-x-2 mb-12">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-book-reader text-xl"></i>
                    </div>
                    <span class="font-black text-2xl tracking-tighter">NoteShare</span>
                </div>
                <h1 class="text-4xl font-bold leading-tight mb-6">Geleceğin Notlarını Birlikte Tutalım.</h1>
                <p class="text-indigo-100 text-sm leading-relaxed opacity-90">
                    Binlerce öğrenciyle notlarını paylaş, çalışma gruplarına katıl ve hedeflerine bir adım daha yaklaş.
                </p>
            </div>

            <div class="relative z-10 pt-10">
                <div class="flex -space-x-3 mb-4">
                    <img class="w-10 h-10 rounded-full border-2 border-[#4f46e5] bg-slate-200" src="https://ui-avatars.com/api/?name=Ali+Veli&background=random" alt="user">
                    <img class="w-10 h-10 rounded-full border-2 border-[#4f46e5] bg-slate-200" src="https://ui-avatars.com/api/?name=Ayşe+Yılmaz&background=random" alt="user">
                    <img class="w-10 h-10 rounded-full border-2 border-[#4f46e5] bg-slate-200" src="https://ui-avatars.com/api/?name=Can+Su&background=random" alt="user">
                    <div class="w-10 h-10 rounded-full border-2 border-[#4f46e5] bg-indigo-400 flex items-center justify-center text-[10px] font-bold">+500</div>
                </div>
                <p class="text-xs font-medium text-indigo-200 uppercase tracking-widest">Sana katılmayı bekleyen büyük bir topluluk var.</p>
            </div>

            <div class="absolute bottom-0 left-0 w-full opacity-10 pointer-events-none">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#FFFFFF" d="M47.5,-62.1C60.4,-53.4,68.9,-38.4,72.3,-22.8C75.7,-7.1,74,9.2,67.6,23.5C61.2,37.8,50.1,50.1,36.5,58.3C22.9,66.4,6.7,70.5,-9.7,68.9C-26.1,67.3,-42.7,60,-54.6,47.9C-66.5,35.8,-73.7,18.9,-73.8,2.1C-73.9,-14.7,-66.8,-31.4,-55,-41.8C-43.2,-52.2,-26.6,-56.3,-10.6,-60.8C5.5,-65.3,21,-70.2,34.6,-70.8C48.2,-71.4,60,-67.7,47.5,-62.1Z" transform="translate(100 100)" />
                </svg>
            </div>
        </div>

        <div class="md:w-7/12 p-8 md:p-16 flex flex-col justify-center bg-white">
            <div class="mb-10 text-center md:text-left">
                <h2 class="text-3xl font-black text-slate-800 mb-2">Hesap Oluştur</h2>
                <p class="text-slate-400 font-medium">Zaten üye misin? <a href="giris.php" class="text-indigo-600 font-bold hover:underline">Giriş Yap</a></p>
            </div>
       
            <?php if(!empty($hata_mesaji)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-sm font-bold border border-red-100 italic">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $hata_mesaji; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kullanıcı Adı</label>
                        <div class="relative">
                            <i class="fas fa-at absolute left-4 top-3.5 text-slate-300"></i>
                            <input type="text" name="username" required placeholder="yazilimci_ali" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-100 focus:bg-white focus:border-indigo-400 outline-none transition-all text-sm font-medium">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">E-posta</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-3.5 text-slate-300"></i>
                            <input type="email" name="email" required placeholder="ali@gmail.com" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-100 focus:bg-white focus:border-indigo-400 outline-none transition-all text-sm font-medium">
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Şifre Oluştur</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-3.5 text-slate-300"></i>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-100 focus:bg-white focus:border-indigo-400 outline-none transition-all text-sm font-medium">
                    </div>
                </div>

                <div class="flex items-start space-x-3 py-1">
                    <input type="checkbox" name="terms" id="termsCheck" required class="mt-1 w-4 h-4 border-slate-300 rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <label for="termsCheck" class="text-[10px] text-slate-400 font-medium leading-relaxed cursor-pointer">
                      Kaydolarak <a href="sozlesme.php" class="text-slate-600 font-bold underline">Gizlilik Sözleşmesini</a> kabul etmiş sayılırsın.
                    </label>
                </div>

                <button type="submit" class="w-full bg-[#4f46e5] text-white py-4 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95">
                 Aramıza Katıl
               </button>
            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-100"></div>
                </div>
                <div class="relative flex justify-center text-[10px] uppercase tracking-widest">
                    <span class="bg-white px-4 text-slate-400 font-bold">Veya şununla kayıt ol</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <a href="google-login-calistir.php" class="social-btn flex items-center ...">
    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" class="w-5 h-5" alt="Google">
    <span class="text-xs font-bold text-slate-600">Google</span>
</a>

                <a href="fb-login-calistir.php" class="social-btn flex items-center justify-center space-x-3 py-3 bg-[#1877F2] rounded-2xl hover:bg-blue-700 transition-all duration-300 shadow-sm shadow-blue-100">
                    <i class="fab fa-facebook-f text-white"></i>
                    <span class="text-xs font-bold text-white">Facebook</span>
                </a>
            </div>
        </div>
    </div>

</body>
</html>