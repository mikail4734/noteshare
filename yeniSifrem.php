<?php
session_start();
require_once __DIR__ . '/baglan.php';

// Önceki adımlar tamamlanmadıysa baştan başla
if (!isset($_SESSION['sifre_sifirlama']) || empty($_SESSION['sifre_sifirlama']['dogrulandi'])) {
    header("Location: sifrem.php");
    exit;
}

$data = $_SESSION['sifre_sifirlama'];
$mesaj = "";
$mesaj_turu = "";

// Süre kontrolü
if (time() > $data['gecerlilik']) {
    unset($_SESSION['sifre_sifirlama']);
    header("Location: sifrem.php?hata=sure_doldu");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $yeni_sifre   = $_POST['yeni_sifre'] ?? '';
    $sifre_tekrar = $_POST['sifre_tekrar'] ?? '';

    // Doğrulamalar
    if (strlen($yeni_sifre) < 6) {
        $mesaj = "Şifre en az 6 karakter olmalı.";
        $mesaj_turu = "hata";
    } elseif ($yeni_sifre !== $sifre_tekrar) {
        $mesaj = "Şifreler eşleşmiyor.";
        $mesaj_turu = "hata";
    } else {
        $guvenli_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        $email = $data['email'];

        try {
            $g = $db->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE email = ?");
            $g->execute([$guvenli_sifre, $email]);

            // Oturumu temizle
            unset($_SESSION['sifre_sifirlama']);

            // Başarı mesajı + 3 saniye sonra giriş sayfası
            $mesaj = "Şifren başarıyla güncellendi! Giriş sayfasına yönlendiriliyorsun...";
            $mesaj_turu = "basari";

            header("Refresh: 3; url=giris.php");
        } catch (PDOException $e) {
            $mesaj = "Veritabanı hatası. Lütfen tekrar dene.";
            $mesaj_turu = "hata";
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
    <title>Yeni Şifre Belirle | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #4f46e5 0%, #9333ea 50%, #ec4899 100%); }
        .glass { background: rgba(255, 255, 255, 0.97); backdrop-filter: blur(10px); }
        .strength-bar { height: 4px; border-radius: 2px; transition: all 0.3s; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Adım göstergesi -->
        <div class="flex items-center justify-center mb-6 gap-2">
            <div class="flex items-center opacity-50">
                <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-black text-xs"><i class="fas fa-check"></i></div>
                <span class="ml-2 text-white/70 text-xs font-bold">E-posta</span>
            </div>
            <div class="w-8 h-0.5 bg-white"></div>
            <div class="flex items-center opacity-50">
                <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-black text-xs"><i class="fas fa-check"></i></div>
                <span class="ml-2 text-white/70 text-xs font-bold">Kod</span>
            </div>
            <div class="w-8 h-0.5 bg-white"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-white text-pink-600 flex items-center justify-center font-black text-sm shadow-lg">3</div>
                <span class="ml-2 text-white text-xs font-bold">Yeni Şifre</span>
            </div>
        </div>

        <div class="glass rounded-3xl shadow-2xl p-10">
            <div class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center mb-5">
                <i class="fas fa-lock text-3xl text-pink-600"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-2">Yeni Şifre Belirle</h2>
            <p class="text-slate-500 text-sm mb-7 leading-relaxed">
                <span class="font-bold"><?= htmlspecialchars($data['email']) ?></span> için yeni bir şifre belirle.
            </p>

            <?php if($mesaj): ?>
                <div class="mb-5 p-4 rounded-xl text-sm font-bold <?= $mesaj_turu == 'basari' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                    <i class="fas <?= $mesaj_turu == 'basari' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= $mesaj ?>
                </div>
            <?php endif; ?>

            <?php if ($mesaj_turu !== 'basari'): ?>
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Yeni Şifre</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-4 text-slate-400"></i>
                        <input type="password" name="yeni_sifre" id="yeniSifre" placeholder="En az 6 karakter" required
                               minlength="6"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3.5 pl-12 pr-12 outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm font-medium">
                        <button type="button" onclick="sifreGoster('yeniSifre', this)" class="absolute right-4 top-4 text-slate-400 hover:text-slate-700">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <!-- Şifre güçlülük göstergesi -->
                    <div class="mt-2 flex gap-1">
                        <div id="bar1" class="flex-1 strength-bar bg-slate-200"></div>
                        <div id="bar2" class="flex-1 strength-bar bg-slate-200"></div>
                        <div id="bar3" class="flex-1 strength-bar bg-slate-200"></div>
                        <div id="bar4" class="flex-1 strength-bar bg-slate-200"></div>
                    </div>
                    <p id="strengthText" class="text-xs text-slate-400 mt-1 font-bold"></p>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Şifre Tekrarı</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-4 text-slate-400"></i>
                        <input type="password" name="sifre_tekrar" id="sifreTekrar" placeholder="Şifrenizi tekrar girin" required
                               minlength="6"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3.5 pl-12 pr-12 outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm font-medium">
                        <button type="button" onclick="sifreGoster('sifreTekrar', this)" class="absolute right-4 top-4 text-slate-400 hover:text-slate-700">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p id="matchText" class="text-xs mt-1 font-bold"></p>
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-pink-600 to-rose-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl active:scale-[0.98] transition-all">
                    <i class="fas fa-save mr-2"></i> Şifreyi Güncelle
                </button>
            </form>
            <?php else: ?>
                <a href="giris.php" class="block w-full bg-emerald-500 text-white py-4 rounded-xl font-bold text-center hover:bg-emerald-600 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i> Hemen Giriş Yap
                </a>
            <?php endif; ?>
        </div>

        <p class="text-center text-white/60 text-[10px] mt-6 font-bold uppercase tracking-widest">
            &copy; <?= date("Y") ?> notewarehouse · Güvenlik
        </p>
    </div>

<script>
function sifreGoster(id, btn) {
    const inp = document.getElementById(id);
    if (inp.type === 'password') {
        inp.type = 'text';
        btn.querySelector('i').className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password';
        btn.querySelector('i').className = 'fas fa-eye';
    }
}

// Şifre güçlülük kontrolü
const yeniSifre = document.getElementById('yeniSifre');
const tekrar = document.getElementById('sifreTekrar');
const bars = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3'), document.getElementById('bar4')];
const strengthText = document.getElementById('strengthText');
const matchText = document.getElementById('matchText');

yeniSifre?.addEventListener('input', () => {
    const s = yeniSifre.value;
    let puan = 0;
    if (s.length >= 6) puan++;
    if (s.length >= 10) puan++;
    if (/[A-Z]/.test(s) && /[a-z]/.test(s)) puan++;
    if (/[0-9]/.test(s) || /[^A-Za-z0-9]/.test(s)) puan++;

    const renkler = ['bg-rose-500', 'bg-amber-500', 'bg-yellow-500', 'bg-emerald-500'];
    const metinler = ['Çok zayıf', 'Zayıf', 'Orta', 'Güçlü'];
    bars.forEach((b, i) => {
        b.className = 'flex-1 strength-bar ' + (i < puan ? renkler[puan-1] : 'bg-slate-200');
    });
    strengthText.innerText = s ? metinler[puan-1] || '' : '';
    strengthText.className = 'text-xs mt-1 font-bold ' + (puan >= 3 ? 'text-emerald-600' : puan >= 2 ? 'text-amber-600' : 'text-rose-600');

    eslesmeKontrol();
});

tekrar?.addEventListener('input', eslesmeKontrol);

function eslesmeKontrol() {
    if (!tekrar.value) { matchText.innerText = ''; return; }
    if (yeniSifre.value === tekrar.value) {
        matchText.innerText = '✓ Şifreler eşleşiyor';
        matchText.className = 'text-xs mt-1 font-bold text-emerald-600';
    } else {
        matchText.innerText = '✗ Şifreler eşleşmiyor';
        matchText.className = 'text-xs mt-1 font-bold text-rose-600';
    }
}
</script>
</body>
</html>
