<?php include 'baglanti.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Not Ekle - Mikail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">

    <div class="max-w-2xl mx-auto mt-10 p-8 bg-white rounded-2xl shadow-sm border border-slate-100">
        <h2 class="text-2xl font-bold text-slate-800 mb-6">Yeni Ders Notu Ekle</h2>

        <form action="ekleme.php" method="POST" class="space-y-4">
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Not Başlığı</label>
                <input type="text" name="baslik" required
                       class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                       placeholder="Örn: Türev ve İntegral Giriş">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Ders İçeriği / Notlar</label>
                <textarea name="icerik" required rows="6"
                          class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                          placeholder="Buraya ders notlarını yapıştır..."></textarea>
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="dersler.php" class="text-slate-500 text-sm hover:underline">Vazgeç ve Geri Dön</a>
                <button type="submit" 
                        class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 transition-colors">
                    Kaydet ve Veritabanına Gönder
                </button>
            </div>

        </form>
    </div>

</body>
</html>