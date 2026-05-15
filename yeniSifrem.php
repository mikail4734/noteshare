<?php
session_start();

if (!isset($_SESSION['onay_kodu'])) {
    header("Location: sifre-sifirla.php");
    exit();
}

$mesaj = "";
$mesaj_turu = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $girilen_kod = $_POST['kod'];
    $yeni_sifre = $_POST['yeni_sifre'];

    if ($girilen_kod == $_SESSION['onay_kodu']) {
     

        $mesaj = "Şifreniz başarıyla güncellendi!";
        $mesaj_turu = "basari";
        
        session_destroy(); 
        header("Refresh: 3; url=giris.php");
    } else {
        $mesaj = "Onay kodu hatalı!";
        $mesaj_turu = "hata";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>NoteShare | Yeni Şifre Oluştur</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-family: 'Inter', sans-serif;">
    <div class="max-w-md w-full bg-white/95 backdrop-blur rounded-[2rem] shadow-2xl p-10">
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Kodu Doğrula</h2>
        <p class="text-slate-500 text-sm mb-8"><b><?php echo $_SESSION['sifirlama_email']; ?></b> adresine gelen kodu ve yeni şifrenizi girin.</p>
        
        <?php if($mesaj): ?>
            <div class="mb-4 p-3 rounded-lg <?php echo $mesaj_turu == 'basari' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'; ?> text-xs font-bold">
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <input type="text" name="kod" maxlength="4" placeholder="4 Haneli Kod" required 
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-4 px-4 text-center text-2xl font-bold tracking-[0.5em] focus:ring-2 focus:ring-indigo-500 outline-none">
            
            <input type="password" name="yeni_sifre" placeholder="Yeni Güçlü Şifreniz" required 
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-4 px-4 focus:ring-2 focus:ring-indigo-500 outline-none">

            <button type="submit" class="w-full bg-green-600 text-white py-4 rounded-xl font-bold hover:bg-green-700 transition-all">
                Şifreyi Güncelle
            </button>
            
            <a href="sifre-sifirla.php" class="block text-center text-xs text-indigo-600 font-bold mt-4 hover:underline">Vazgeç ve Başa Dön</a>
        </form>
    </div>
</body>
</html>