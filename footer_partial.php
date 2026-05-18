<!-- Footer (her sayfada include edilebilir) -->
<footer class="bg-slate-900 text-slate-300 pt-16 pb-8 px-6">
    <div class="max-w-6xl mx-auto">

        <!-- NEWSLETTER -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl p-8 md:p-12 mb-12 text-center shadow-2xl">
            <h3 class="text-3xl font-black text-white mb-3">📬 Haberdar Ol</h3>
            <p class="text-indigo-100 mb-6 max-w-xl mx-auto">
                Yeni özellikler, çalışma stratejileri ve sınav rehberleri için bültenimize abone ol.
            </p>
            <form id="newsletterForm" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                <input type="email" id="nlEmail" required placeholder="ornek@email.com"
                       class="flex-1 px-5 py-3 rounded-xl text-slate-800 outline-none">
                <button type="submit" class="bg-white text-indigo-600 px-6 py-3 rounded-xl font-bold hover:bg-slate-100 transition">
                    Abone Ol
                </button>
            </form>
            <p id="nlMsg" class="text-white text-sm mt-3 hidden"></p>
        </div>

        <!-- 4 KOLON -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">

            <div>
                <div class="flex items-center mb-4">
                    <img src="/favicon-180.png" alt="notewarehouse" class="w-10 h-10 rounded-lg mr-3">
                    <span class="text-xl font-black text-white">notewarehouse</span>
                </div>
                <p class="text-sm leading-relaxed">
                    Türkiye'nin ücretsiz öğrenci not paylaşım platformu. Bilgi paylaşılınca çoğalır.
                </p>
            </div>

            <div>
                <h4 class="text-white font-black mb-4 uppercase tracking-wider text-xs">Keşfet</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/universite.php" class="hover:text-white transition">🎓 Üniversite</a></li>
                    <li><a href="/lise.php" class="hover:text-white transition">🏫 Lise</a></li>
                    <li><a href="/ortaokul.php" class="hover:text-white transition">🎒 Ortaokul</a></li>
                    <li><a href="/ilkokul.php" class="hover:text-white transition">📜 İlkokul</a></li>
                    <li><a href="/canli_sinavlar.php" class="hover:text-white transition">🎯 Canlı Sınavlar</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-black mb-4 uppercase tracking-wider text-xs">Kurumsal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/hakkimizda.php" class="hover:text-white transition">Hakkımızda</a></li>
                    <li><a href="/haberler.php" class="hover:text-white transition">Haberler & Blog</a></li>
                    <li><a href="/sss.php" class="hover:text-white transition">SSS</a></li>
                    <li><a href="/kosullar.php" class="hover:text-white transition">Kullanım Koşulları</a></li>
                    <li><a href="/sozlesme.php" class="hover:text-white transition">Sözleşme</a></li>
                    <li><a href="/sikayet_olustur.php" class="hover:text-white transition">Şikayet / İletişim</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-black mb-4 uppercase tracking-wider text-xs">Bizi Takip Et</h4>
                <div class="flex gap-3 mb-4">
                    <a href="https://instagram.com/note_warehouse/" target="_blank" rel="noopener" title="Instagram: @note_warehouse"
                       class="w-12 h-12 bg-gradient-to-tr from-purple-600 via-pink-500 to-orange-400 rounded-xl flex items-center justify-center text-white hover:scale-110 hover:rotate-3 transition shadow-lg">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a href="https://www.facebook.com/profile.php?id=61590216140180" target="_blank" rel="noopener" title="Facebook: Notewarehouse"
                       class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-white hover:scale-110 hover:rotate-3 transition shadow-lg">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                </div>
                <p class="text-xs leading-relaxed">
                    📧 <a href="mailto:notewarehouses@gmail.com?subject=notewarehouse%20Hakk%C4%B1nda" class="hover:text-white underline">notewarehouses@gmail.com</a>
                </p>
            </div>

        </div>

        <!-- ALT -->
        <div class="border-t border-slate-800 pt-6 flex flex-col md:flex-row justify-between items-center gap-4 text-xs">
            <p>&copy; <?= date("Y") ?> <strong>notewarehouse</strong>. Tüm hakları saklıdır.</p>
            <p>Geliştiren: <strong>Mikail Çelik</strong> & <strong>Mustafa Kabataş</strong> · İstanbul Gelişim Üniversitesi</p>
        </div>

    </div>
</footer>

<script>
// LAZY LOAD - Tüm img etiketlerine native lazy loading ekle
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('img').forEach(img => {
        // Logoları ve favicon'u hariç tut (above-the-fold)
        if (!img.hasAttribute('loading') && !img.src.includes('favicon') && !img.src.includes('logo')) {
            img.setAttribute('loading', 'lazy');
            img.setAttribute('decoding', 'async');
        }
    });

    // Iframe'ler için de
    document.querySelectorAll('iframe').forEach(f => {
        if (!f.hasAttribute('loading')) f.setAttribute('loading', 'lazy');
    });
});

// Newsletter
document.getElementById('newsletterForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('nlEmail').value.trim();
    const msg = document.getElementById('nlMsg');
    if (!email) return;
    try {
        const r = await fetch('/islem.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ islem: 'newsletter_abone', email })
        });
        const data = await r.json();
        msg.classList.remove('hidden');
        if (data.success) {
            msg.innerText = '✅ ' + (data.msg || 'Abone oldun!');
            document.getElementById('nlEmail').value = '';
        } else {
            msg.innerText = '❌ ' + (data.error || 'Hata');
        }
    } catch (e) {
        msg.classList.remove('hidden');
        msg.innerText = '❌ Bağlantı hatası';
    }
});
</script>
