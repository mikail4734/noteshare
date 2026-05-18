<?php

$site_adi = "notewarehouse";
$sayfa_basligi = "Bize Ulaşın | " . $site_adi;
$admin_email = "mikailcelik4734@gmail.com";
$telefon = "+90 (212) 555 01 01";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sayfa_basligi; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-[#4f46e5] px-8 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50 text-white">
        <div class="font-black text-xl tracking-tighter cursor-pointer" onclick="window.location.href='index.php'">
            <?php echo $site_adi; ?>
        </div>
        <a href="index.php" class="text-sm font-bold bg-white/10 px-4 py-2 rounded-xl hover:bg-white/20 transition">
            Ana Sayfa'ya Dön
        </a>
    </nav>

    <div class="container mx-auto px-6 py-16">
        <div class="max-w-5xl mx-auto">
            
            <div class="text-center mb-16">
                <h1 class="text-4xl font-black text-slate-900 mb-4 tracking-tight">Bizimle İletişime Geçin</h1>
                <p class="text-slate-500 max-w-lg mx-auto font-medium">Platformu geliştirmemize yardımcı olacak fikirlerini veya karşılaştığın sorunları bize iletebilirsin.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
                
                <div class="space-y-8">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800 mb-6">İletişim Bilgileri</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">E-posta Adresimiz</p>
                                    <p class="text-sm font-bold text-slate-700"><?php echo $admin_email; ?></p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Telefon</p>
                                    <p class="text-sm font-bold text-slate-700"><?php echo $telefon; ?></p>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-slate-50">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Sosyal Medyada Biz</p>
                                <div class="flex space-x-3">
                                    <a href="https://www.instagram.com/celikmikail47/" target="_blank" class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500 hover:bg-indigo-600 hover:text-white transition-all"><i class="fab fa-instagram"></i></a>
                                    <a href="https://www.linkedin.com/in/mikail-%C3%A7elik-002640391/" target="_blank" class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500 hover:bg-indigo-600 hover:text-white transition-all"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="https://github.com/" target="_blank" class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500 hover:bg-indigo-600 hover:text-white transition-all"><i class="fab fa-github"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-indigo-100/40 border border-slate-100 relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 text-indigo-50 opacity-50 transform rotate-12">
                            <i class="fas fa-paper-plane text-[150px]"></i>
                        </div>

                        <form id="contact-form" action="https://formspree.io/f/mvzwyebd" method="POST" class="space-y-6 relative z-10">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Adınız Soyadınız</label>
                                    <input type="text" name="Ad Soyad" placeholder="John Doe" required
                                           class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-3 px-5 text-sm font-medium focus:ring-4 focus:ring-indigo-100 focus:bg-white focus:border-indigo-400 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">E-posta Adresiniz</label>
                                    <input type="email" name="Email" placeholder="ornek@gmail.com" required
                                           class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-3 px-5 text-sm font-medium focus:ring-4 focus:ring-indigo-100 focus:bg-white focus:border-indigo-400 outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Telefon Numaranız</label>
                                <input type="tel" name="Telefon" placeholder="0 (5xx) xxx xx xx"
                                       class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-3 px-5 text-sm font-medium focus:ring-4 focus:ring-indigo-100 focus:bg-white focus:border-indigo-400 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Mesajınız veya Önerileriniz</label>
                                <textarea name="Mesaj" rows="5" placeholder="Bize ne söylemek istersiniz?" required
                                          class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-3 px-5 text-sm font-medium focus:ring-4 focus:ring-indigo-100 focus:bg-white focus:border-indigo-400 outline-none transition-all resize-none"></textarea>
                            </div>

                            <button type="submit" id="submit-btn" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 transition-all flex items-center justify-center">
                                <i class="fas fa-paper-plane mr-3"></i> <span id="btn-text">Mesajı Gönder</span>
                            </button>
                            <p id="status" class="text-center text-xs font-bold mt-2"></p>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById("contact-form");
        const status = document.getElementById("status");
        const btnText = document.getElementById("btn-text");

        async function handleSubmit(event) {
          event.preventDefault();
          btnText.innerText = "Gönderiliyor...";
          
          const data = new FormData(event.target);
          fetch(event.target.action, {
            method: form.method,
            body: data,
            headers: {
                'Accept': 'application/json'
            }
          }).then(response => {
            if (response.ok) {
              status.innerHTML = "✅ Teşekkürler! Mesajın başarıyla bize ulaştı.";
              status.className = "text-center text-xs font-bold mt-2 text-emerald-500";
              form.reset();
              btnText.innerText = "Mesajı Gönder";
            } else {
              response.json().then(data => {
                if (Object.hasOwn(data, 'errors')) {
                  status.innerHTML = data["errors"].map(error => error["message"]).join(", ");
                } else {
                  status.innerHTML = "❌ Bir sorun oluştu, lütfen tekrar dene.";
                }
                status.className = "text-center text-xs font-bold mt-2 text-red-500";
                btnText.innerText = "Tekrar Dene";
              })
            }
          }).catch(error => {
            status.innerHTML = "❌ Bir hata oluştu. İnternet bağlantını kontrol et.";
            status.className = "text-center text-xs font-bold mt-2 text-red-500";
            btnText.innerText = "Tekrar Dene";
          });
        }
        form.addEventListener("submit", handleSubmit)
    </script>

<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>