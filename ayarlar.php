<?php

$user = [
    'ad' => 'Mikail',
    'email' => 'mikail@example.com',
    'egitim' => 'Üniversite',
    'bas harf' => 'M'
];

$sayfa_basligi = "Ayarlar | NoteShare";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sayfa_basligi; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white border-b px-6 py-4 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="index.php" class="text-slate-400 hover:text-indigo-600 transition text-xl">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="font-bold text-xl text-indigo-600 italic">NoteShare</h1>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-slate-500 hidden md:block"><?php echo $user['ad']; ?></span>
                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold">
                    <?php echo $user['bas harf']; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto max-w-4xl mt-10 mb-20 px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="space-y-2">
                <button onclick="showTab('profile')" class="tab-btn w-full text-left px-6 py-3 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 transition">
                    <i class="fas fa-user-circle mr-2"></i> Profil Bilgileri
                </button>
                <button onclick="showTab('security')" class="tab-btn w-full text-left px-6 py-3 rounded-2xl bg-white text-slate-500 font-bold hover:bg-slate-100 transition border border-transparent">
                    <i class="fas fa-shield-alt mr-2"></i> Güvenlik
                </button>
                <button onclick="showTab('notifications')" class="tab-btn w-full text-left px-6 py-3 rounded-2xl bg-white text-slate-500 font-bold hover:bg-slate-100 transition border border-transparent">
                    <i class="fas fa-bell mr-2"></i> Bildirimler
                </button>
                <hr class="my-4 border-slate-200">
                <button class="w-full text-left px-6 py-3 rounded-2xl bg-red-50 text-red-600 font-bold hover:bg-red-100 transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                </button>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8 border border-slate-100">
                    
                    <div id="profile-tab" class="settings-content">
                        <h2 class="text-2xl font-black text-slate-900 mb-6">Profil Bilgileri</h2>
                        <div class="space-y-6">
                            <div class="flex items-center space-x-6 mb-8">
                                <div class="relative">
                                    <div class="w-24 h-24 bg-slate-100 rounded-3xl flex items-center justify-center text-3xl font-bold text-indigo-600 border-2 border-dashed border-slate-300">
                                        <?php echo $user['bas harf']; ?>
                                    </div>
                                    <button class="absolute -bottom-2 -right-2 bg-indigo-600 text-white p-2 rounded-xl shadow-lg hover:scale-110 transition">
                                        <i class="fas fa-camera text-xs"></i>
                                    </button>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800">Profil Fotoğrafı</h4>
                                    <p class="text-xs text-slate-400">JPG, PNG (Max 2MB)</p>
                                </div>
                            </div>

                            <form action="islem.php" method="POST" class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Ad Soyad</label>
                                        <input type="text" name="ad" value="<?php echo $user['ad']; ?>" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">E-Posta</label>
                                        <input type="email" name="email" value="<?php echo $user['email']; ?>" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Eğitim Seviyesi</label>
                                    <select name="egitim" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                                        <option <?php echo $user['egitim'] == 'Üniversite' ? 'selected' : ''; ?>>Üniversite</option>
                                        <option <?php echo $user['egitim'] == 'Lise' ? 'selected' : ''; ?>>Lise</option>
                                        <option <?php echo $user['egitim'] == 'Ortaokul' ? 'selected' : ''; ?>>Ortaokul</option>
                                    </select>
                                </div>

                                <button type="button" onclick="saveSettings()" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition active:scale-95">
                                    Değişiklikleri Kaydet
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="security-tab" class="settings-content hidden">
                        <h2 class="text-2xl font-black text-slate-900 mb-6">Güvenlik Ayarları</h2>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Mevcut Şifre</label>
                                <input type="password" placeholder="••••••••" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Yeni Şifre</label>
                                <input type="password" placeholder="Yeni şifreniz" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <button type="button" onclick="saveSettings()" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-black transition">
                                Şifreyi Güncelle
                            </button>
                        </div>
                    </div>

                    <div id="notifications-tab" class="settings-content hidden">
                        <h2 class="text-2xl font-black text-slate-900 mb-6">Bildirim Ayarları</h2>
                        <p class="text-slate-500 text-sm">Bildirim tercihlerinizi buradan yönetebilirsiniz.</p>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabName) {
           
            document.querySelectorAll('.settings-content').forEach(content => {
                content.classList.add('hidden');
            });
          
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-lg', 'shadow-indigo-100');
                btn.classList.add('bg-white', 'text-slate-500');
            });

           
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
          
            const activeBtn = event.currentTarget;
            activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-lg', 'shadow-indigo-100');
            activeBtn.classList.remove('bg-white', 'text-slate-500');
        }

        function saveSettings() {
           
            alert("✅ Ayarlarınız başarıyla güncellendi!");
        }
    </script>
</body>
</html>