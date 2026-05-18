<?php
/**
 * Global Assets - tüm sayfalarda kullanılır
 * <head> bölümünün sonuna include edilir:
 *   <?php require_once 'global_assets.php'; ?>
 *
 * Otomatik olarak ekler:
 * - Dark mode CSS + JS
 * - Sağ alt köşede sabit "Dark mode" toggle butonu
 * - Streak/XP rozeti (giriş yapan kullanıcılar için)
 */

// Streak/XP verisi (varsa)
$gaKullaniciVerisi = null;
if (isset($_SESSION['user_email']) && isset($db)) {
    try {
        $s = $db->prepare("SELECT xp, seviye, streak FROM users WHERE email = ?");
        $s->execute([$_SESSION['user_email']]);
        $gaKullaniciVerisi = $s->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
?>

<!-- DARK MODE STYLES -->
<style>
    html.dark body { background: #0f172a !important; color: #e2e8f0 !important; }
    html.dark .bg-white,
    html.dark .bg-gray-50,
    html.dark .bg-slate-50,
    html.dark .bg-slate-100 { background: #1e293b !important; color: #e2e8f0 !important; }
    html.dark .bg-slate-50\/50, html.dark .bg-slate-50\/30 { background: #1e293b !important; }
    html.dark .border-gray-100,
    html.dark .border-slate-100,
    html.dark .border-slate-200 { border-color: #334155 !important; }
    html.dark .text-gray-800,
    html.dark .text-slate-700,
    html.dark .text-slate-800,
    html.dark .text-slate-900 { color: #e2e8f0 !important; }
    html.dark .text-gray-500,
    html.dark .text-slate-400,
    html.dark .text-slate-500 { color: #94a3b8 !important; }
    html.dark .text-gray-400 { color: #64748b !important; }
    html.dark input, html.dark textarea, html.dark select {
        background: #0f172a !important; color: #e2e8f0 !important; border-color: #334155 !important;
    }

    /* Sabit dark mode butonu */
    #globalThemeToggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #9333ea);
        color: white;
        border: none;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.35);
        font-size: 20px;
        transition: all 0.3s;
    }
    #globalThemeToggle:hover { transform: scale(1.1) rotate(15deg); }

    /* Streak / XP göstergesi */
    #globalUserBadge {
        position: fixed;
        bottom: 20px;
        right: 84px;
        z-index: 9999;
        display: flex;
        gap: 8px;
        font-family: 'Inter', sans-serif;
    }
    #globalUserBadge a {
        background: linear-gradient(135deg, #4f46e5, #9333ea);
        color: white;
        padding: 12px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    #globalUserBadge a:hover { transform: translateY(-2px); }

    @media (max-width: 640px) {
        #globalUserBadge { display: none; }
    }
</style>

<!-- DARK MODE JS - sayfa yüklenmeden uygula (FOUC önle) -->
<script>
(function() {
    const tema = localStorage.getItem('tema') || 'light';
    if (tema === 'dark') document.documentElement.classList.add('dark');
})();

function globalTemaDegistir() {
    const yeni = document.documentElement.classList.toggle('dark') ? 'dark' : 'light';
    localStorage.setItem('tema', yeni);
    const ikon = document.getElementById('globalThemeIkon');
    if (ikon) ikon.className = yeni === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}
</script>

<?php // Sayfanın sonuna kadar bekleyip butonları oluşturalım ?>
<script>
window.addEventListener('DOMContentLoaded', function() {
    // Dark mode butonu
    if (!document.getElementById('globalThemeToggle')) {
        const btn = document.createElement('button');
        btn.id = 'globalThemeToggle';
        btn.onclick = globalTemaDegistir;
        btn.title = 'Karanlık Mod';
        const ikonClass = document.documentElement.classList.contains('dark') ? 'fas fa-sun' : 'fas fa-moon';
        btn.innerHTML = '<i id="globalThemeIkon" class="' + ikonClass + '"></i>';
        document.body.appendChild(btn);
    }

    <?php if ($gaKullaniciVerisi): ?>
    // Streak/XP göstergesi
    if (!document.getElementById('globalUserBadge')) {
        const badge = document.createElement('div');
        badge.id = 'globalUserBadge';
        badge.innerHTML = `
            <a href="/liderlik.php" title="<?= $gaKullaniciVerisi['streak'] ?> gündür üst üste">🔥 <?= $gaKullaniciVerisi['streak'] ?></a>
            <a href="/rozetlerim.php" title="Seviye <?= $gaKullaniciVerisi['seviye'] ?> · <?= $gaKullaniciVerisi['xp'] ?> XP">⭐ Lv<?= $gaKullaniciVerisi['seviye'] ?></a>
        `;
        document.body.appendChild(badge);
    }
    <?php endif; ?>
});
</script>
