<?php
session_start();
require_once __DIR__ . '/baglan.php';

// Oturum bilgisi yoksa baştan başla
if (!isset($_SESSION['sifre_sifirlama'])) {
    header("Location: sifrem.php");
    exit;
}

$data = $_SESSION['sifre_sifirlama'];
$mesaj = "";
$mesaj_turu = "";

// Süre dolmuş mu?
if (time() > $data['gecerlilik']) {
    unset($_SESSION['sifre_sifirlama']);
    header("Location: sifrem.php?hata=sure_doldu");
    exit;
}

// 5 deneme limiti
if (($data['denemeler'] ?? 0) >= 5) {
    unset($_SESSION['sifre_sifirlama']);
    header("Location: sifrem.php?hata=cok_deneme");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $girilen_kod = trim($_POST['kod'] ?? '');

    if ($girilen_kod === $data['kod']) {
        // Kod doğru → onay flag'ı bas, yeni şifre sayfasına yönlendir
        $_SESSION['sifre_sifirlama']['dogrulandi'] = true;
        header("Location: yeniSifrem.php");
        exit;
    } else {
        $_SESSION['sifre_sifirlama']['denemeler'] = ($data['denemeler'] ?? 0) + 1;
        $kalan = 5 - $_SESSION['sifre_sifirlama']['denemeler'];
        $mesaj = "Kod hatalı! Kalan deneme hakkı: $kalan";
        $mesaj_turu = "hata";
    }
}

// Süreyi göstermek için kalan saniye
$kalanSaniye = max(0, $data['gecerlilik'] - time());
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kodu Doğrula | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #4f46e5 0%, #9333ea 50%, #ec4899 100%); }
        .glass { background: rgba(255, 255, 255, 0.97); backdrop-filter: blur(10px); }
        .otp-input { width: 50px; height: 60px; text-align: center; font-size: 28px; font-weight: 900; border: 2px solid #e2e8f0; border-radius: 12px; outline: none; transition: all 0.2s; }
        .otp-input:focus { border-color: #4f46e5; transform: scale(1.05); background: #eef2ff; }
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
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-white text-indigo-600 flex items-center justify-center font-black text-sm shadow-lg">2</div>
                <span class="ml-2 text-white text-xs font-bold">Kod</span>
            </div>
            <div class="w-8 h-0.5 bg-white/30"></div>
            <div class="flex items-center opacity-50">
                <div class="w-8 h-8 rounded-full bg-white/30 text-white flex items-center justify-center font-black text-sm">3</div>
                <span class="ml-2 text-white/70 text-xs font-bold">Yeni Şifre</span>
            </div>
        </div>

        <a href="sifrem.php" class="inline-flex items-center text-white/80 hover:text-white mb-4 transition text-sm font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> E-posta değiştir
        </a>

        <div class="glass rounded-3xl shadow-2xl p-10">
            <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-5">
                <i class="fas fa-key text-3xl text-purple-600"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-2">Kodu Gir</h2>
            <p class="text-slate-500 text-sm mb-2 leading-relaxed">
                <span class="font-bold text-slate-700"><?= htmlspecialchars($data['email']) ?></span> adresine 6 haneli kod gönderildi.
            </p>
            <p id="sayacBilgi" class="text-xs text-indigo-600 font-bold mb-7">
                <i class="far fa-clock mr-1"></i> <span id="sayac"></span>
            </p>

            <?php if($mesaj): ?>
                <div class="mb-5 p-4 rounded-xl text-sm font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= $mesaj ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="kodForm">
                <div class="flex justify-center gap-2 mb-6" id="otpKutular">
                    <input type="text" maxlength="1" class="otp-input" data-i="0" autofocus>
                    <input type="text" maxlength="1" class="otp-input" data-i="1">
                    <input type="text" maxlength="1" class="otp-input" data-i="2">
                    <input type="text" maxlength="1" class="otp-input" data-i="3">
                    <input type="text" maxlength="1" class="otp-input" data-i="4">
                    <input type="text" maxlength="1" class="otp-input" data-i="5">
                </div>
                <input type="hidden" name="kod" id="kodGizli">

                <button type="submit"
                        class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl active:scale-[0.98] transition-all">
                    <i class="fas fa-check-circle mr-2"></i> Doğrula ve İlerle
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <a href="sifrem.php" class="text-xs text-indigo-600 font-bold hover:underline">
                    <i class="fas fa-redo-alt mr-1"></i> Kod gelmediyse tekrar gönder
                </a>
            </div>
        </div>

        <p class="text-center text-white/60 text-[10px] mt-6 font-bold uppercase tracking-widest">
            &copy; <?= date("Y") ?> notewarehouse · Güvenlik
        </p>
    </div>

<script>
// OTP kutusu yönetimi
const inputs = document.querySelectorAll('.otp-input');
inputs.forEach((inp, idx) => {
    inp.addEventListener('input', (e) => {
        const val = e.target.value.replace(/\D/g, '');
        e.target.value = val.slice(-1);
        if (val && idx < 5) inputs[idx+1].focus();
        kontrolEt();
    });
    inp.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !inp.value && idx > 0) inputs[idx-1].focus();
    });
    inp.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
        for (let i = 0; i < paste.length; i++) {
            if (inputs[i]) inputs[i].value = paste[i];
        }
        inputs[Math.min(paste.length, 5)].focus();
        kontrolEt();
    });
});

function kontrolEt() {
    const kod = [...inputs].map(i => i.value).join('');
    document.getElementById('kodGizli').value = kod;
    if (kod.length === 6) {
        document.getElementById('kodForm').submit();
    }
}

// Geri sayım
const sayac = document.getElementById('sayac');
let kalan = <?= $kalanSaniye ?>;
function tick() {
    if (kalan <= 0) {
        sayac.innerText = "Süre doldu! Tekrar deneyin.";
        sayac.parentElement.classList.add('text-rose-600');
        return;
    }
    const dk = Math.floor(kalan / 60);
    const sn = kalan % 60;
    sayac.innerText = `Kod ${dk}:${sn.toString().padStart(2,'0')} dakika geçerli`;
    kalan--;
}
tick();
setInterval(tick, 1000);
</script>
</body>
</html>
