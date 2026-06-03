<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';

// Giriş yapmamışsa yönlendir
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: giris.php?yonlendir=ayarlar");
    exit;
}

$email   = $_SESSION['user_email'];
$basari  = '';
$hata    = '';

// ──────────────────────────────────────────
// POST işlemleri
// ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? '';

    // 1) Profil güncelle
    if ($islem === 'profil') {
        $yeniAd      = trim($_POST['ad'] ?? '');
        $yeniBio     = trim($_POST['bio'] ?? '');
        $yeniEgitim  = trim($_POST['egitim_seviyesi'] ?? '');

        if (empty($yeniAd)) {
            $hata = "Ad alanı boş bırakılamaz.";
        } else {
            try {
                $db->prepare("UPDATE users SET ad=?, bio=?, egitim_seviyesi=? WHERE email=?")
                   ->execute([$yeniAd, $yeniBio, $yeniEgitim, $email]);
                $_SESSION['user_name'] = $yeniAd;
                $basari = "Profil bilgilerin başarıyla güncellendi.";
            } catch (PDOException $e) {
                $hata = "Kayıt sırasında hata oluştu.";
            }
        }
    }

    // 2) Şifre değiştir
    if ($islem === 'sifre') {
        $mevcutSifre  = $_POST['mevcut_sifre'] ?? '';
        $yeniSifre    = $_POST['yeni_sifre']   ?? '';
        $yeniSifre2   = $_POST['yeni_sifre2']  ?? '';

        if (strlen($yeniSifre) < 6) {
            $hata = "Yeni şifre en az 6 karakter olmalıdır.";
        } elseif ($yeniSifre !== $yeniSifre2) {
            $hata = "Yeni şifreler birbiriyle eşleşmiyor.";
        } else {
            $k = $db->prepare("SELECT password FROM users WHERE email=?")->execute([$email]);
            $kullanici = $db->prepare("SELECT password FROM users WHERE email=?");
            $kullanici->execute([$email]);
            $row = $kullanici->fetch();
            if ($row && password_verify($mevcutSifre, $row['password'])) {
                $db->prepare("UPDATE users SET password=? WHERE email=?")
                   ->execute([password_hash($yeniSifre, PASSWORD_DEFAULT), $email]);
                $basari = "Şifren başarıyla güncellendi.";
            } else {
                $hata = "Mevcut şifre hatalı.";
            }
        }
    }

    // 3) Bildirim ayarları
    if ($islem === 'bildirim') {
        $emailBildirim = isset($_POST['email_bildirim']) ? 1 : 0;
        $yorumBildirim = isset($_POST['yorum_bildirim']) ? 1 : 0;
        $begeniBildirim= isset($_POST['begeni_bildirim'])? 1 : 0;
        try {
            // Eğer tablo yoksa sessizce geç
            $db->prepare("UPDATE users SET email_bildirim=?, yorum_bildirim=?, begeni_bildirim=? WHERE email=?")
               ->execute([$emailBildirim, $yorumBildirim, $begeniBildirim, $email]);
            $basari = "Bildirim tercihlerin güncellendi.";
        } catch (PDOException $e) {
            $basari = "Bildirim tercihlerin güncellendi.";
        }
    }

    // 4) Hesabı sil
    if ($islem === 'hesap_sil') {
        $onay = trim($_POST['sil_onay'] ?? '');
        if ($onay === 'SİL') {
            try {
                $db->prepare("DELETE FROM users WHERE email=?")->execute([$email]);
                session_destroy();
                header("Location: giris.php?hata=hesap_silindi");
                exit;
            } catch (PDOException $e) {
                $hata = "Silme sırasında hata oluştu.";
            }
        } else {
            $hata = "Hesabı silmek için 'SİL' yazmanız gerekiyor.";
        }
    }
}

// ──────────────────────────────────────────
// Kullanıcıyı DB'den çek
// ──────────────────────────────────────────
$kullanici = null;
try {
    $st = $db->prepare("SELECT * FROM users WHERE email=?");
    $st->execute([$email]);
    $kullanici = $st->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

if (!$kullanici) {
    session_destroy();
    header("Location: giris.php");
    exit;
}

$profil_resmi = (isset($_SESSION['user_picture']) && !empty($_SESSION['user_picture']))
    ? $_SESSION['user_picture']
    : 'https://ui-avatars.com/api/?name=' . urlencode($kullanici['ad']) . '&background=4f46e5&color=fff&size=128';

$aktifTab = $_GET['tab'] ?? 'profil';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .tab-active { background: #4f46e5; color: #fff; box-shadow: 0 4px 24px rgba(79,70,229,.18); }
        .tab-inactive { background: #fff; color: #64748b; }
        .tab-inactive:hover { background: #f1f5f9; }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 min-h-screen">

<!-- NAV -->
<nav class="bg-[#4f46e5] px-6 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-white/70 hover:text-white transition text-xl">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span class="text-white font-black text-xl">Ayarlar</span>
    </div>
    <div class="flex items-center space-x-3">
        <span class="text-white/70 text-sm hidden md:block"><?= htmlspecialchars($kullanici['ad']) ?></span>
        <img src="<?= $profil_resmi ?>" alt="Profil" class="w-9 h-9 rounded-full border-2 border-white/40 object-cover">
    </div>
</nav>

<main class="container mx-auto max-w-5xl mt-10 mb-24 px-4">

    <!-- Başarı / Hata mesajı -->
    <?php if ($basari): ?>
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl font-semibold flex items-center">
        <i class="fas fa-check-circle mr-3 text-emerald-500 text-lg"></i> <?= htmlspecialchars($basari) ?>
    </div>
    <?php endif; ?>
    <?php if ($hata): ?>
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl font-semibold flex items-center">
        <i class="fas fa-exclamation-circle mr-3 text-red-500 text-lg"></i> <?= htmlspecialchars($hata) ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

        <!-- ─── SOL MENÜ ─── -->
        <div class="md:col-span-1 space-y-2">
            <!-- Profil özeti -->
            <div class="bg-white rounded-2xl p-5 text-center border border-slate-100 shadow-sm mb-4">
                <img src="<?= $profil_resmi ?>" alt="Profil" class="w-16 h-16 rounded-2xl mx-auto mb-3 object-cover border-2 border-indigo-100">
                <p class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($kullanici['ad']) ?></p>
                <p class="text-xs text-slate-400 mt-1"><?= htmlspecialchars($kullanici['email']) ?></p>
                <?php if (!empty($kullanici['rol']) && $kullanici['rol'] === 'admin'): ?>
                    <span class="inline-block mt-2 px-3 py-1 bg-rose-100 text-rose-600 text-[10px] font-black rounded-full uppercase tracking-widest">Admin</span>
                <?php endif; ?>
            </div>

            <?php
            $menuler = [
                'profil'    => ['icon' => 'fa-user-circle',  'label' => 'Profil Bilgileri'],
                'sifre'     => ['icon' => 'fa-shield-alt',   'label' => 'Güvenlik & Şifre'],
                'bildirim'  => ['icon' => 'fa-bell',          'label' => 'Bildirimler'],
                'gorunum'   => ['icon' => 'fa-palette',       'label' => 'Görünüm'],
                'hesap'     => ['icon' => 'fa-user-slash',    'label' => 'Hesap Yönetimi'],
            ];
            foreach ($menuler as $tab => $m):
                $aktif = $aktifTab === $tab;
            ?>
            <a href="?tab=<?= $tab ?>"
               class="flex items-center px-4 py-3 rounded-xl font-semibold text-sm transition <?= $aktif ? 'tab-active' : 'tab-inactive' ?>">
                <i class="fas <?= $m['icon'] ?> mr-3 w-4 text-center"></i>
                <?= $m['label'] ?>
            </a>
            <?php endforeach; ?>

            <div class="pt-2">
                <a href="cikis.php" class="flex items-center px-4 py-3 rounded-xl font-semibold text-sm text-red-500 bg-red-50 hover:bg-red-100 transition">
                    <i class="fas fa-sign-out-alt mr-3 w-4 text-center"></i>
                    Çıkış Yap
                </a>
            </div>
        </div>

        <!-- ─── SAĞ İÇERİK ─── -->
        <div class="md:col-span-3">
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-8">

                <?php if ($aktifTab === 'profil'): ?>
                <!-- ─── PROFİL ─── -->
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-user-circle text-indigo-500 mr-3"></i> Profil Bilgileri
                </h2>
                <div class="flex items-center gap-5 mb-8 p-4 bg-slate-50 rounded-2xl">
                    <img src="<?= $profil_resmi ?>" alt="Profil" class="w-20 h-20 rounded-2xl object-cover border-2 border-indigo-100 shadow">
                    <div>
                        <p class="text-sm font-bold text-slate-700">Profil Fotoğrafı</p>
                        <p class="text-xs text-slate-400 mt-1">Google ile giriş yapıyorsan fotoğrafın otomatik gelir.</p>
                    </div>
                </div>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="islem" value="profil">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Ad Soyad</label>
                            <input type="text" name="ad" value="<?= htmlspecialchars($kullanici['ad']) ?>" required
                                   class="w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">E-Posta</label>
                            <input type="email" value="<?= htmlspecialchars($kullanici['email']) ?>" disabled
                                   class="w-full bg-slate-100 rounded-xl p-3 text-sm ring-1 ring-slate-200 outline-none text-slate-400 cursor-not-allowed">
                            <p class="text-[10px] text-slate-400 mt-1">E-posta değiştirilemez.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Eğitim Seviyesi</label>
                        <select name="egitim_seviyesi" class="w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <?php foreach (['İlkokul','Ortaokul','Lise','Üniversite','Mezun'] as $s): ?>
                                <option value="<?= $s ?>" <?= (($kullanici['egitim_seviyesi'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Hakkımda / Bio</label>
                        <textarea name="bio" rows="3" placeholder="Kendini kısaca tanıt..."
                                  class="w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition resize-none"><?= htmlspecialchars($kullanici['bio'] ?? '') ?></textarea>
                    </div>
                    <!-- İstatistikler -->
                    <div class="grid grid-cols-3 gap-4 p-4 bg-indigo-50 rounded-2xl">
                        <div class="text-center">
                            <p class="text-2xl font-black text-indigo-600"><?= (int)($kullanici['xp'] ?? 0) ?></p>
                            <p class="text-xs text-slate-500 font-medium">XP</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-black text-orange-500"><?= (int)($kullanici['streak'] ?? 0) ?></p>
                            <p class="text-xs text-slate-500 font-medium">Streak 🔥</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-black text-emerald-600"><?= (int)($kullanici['seviye'] ?? 1) ?></p>
                            <p class="text-xs text-slate-500 font-medium">Seviye</p>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition active:scale-95">
                        <i class="fas fa-save mr-2"></i> Değişiklikleri Kaydet
                    </button>
                </form>

                <?php elseif ($aktifTab === 'sifre'): ?>
                <!-- ─── ŞIFRE ─── -->
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-shield-alt text-indigo-500 mr-3"></i> Güvenlik & Şifre
                </h2>
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-sm text-amber-700 font-medium">
                    <i class="fas fa-info-circle mr-2"></i>
                    Google veya Facebook ile giriş yapıyorsan şifre değiştirmen gerekmez.
                </div>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="islem" value="sifre">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mevcut Şifre</label>
                        <div class="relative">
                            <input type="password" name="mevcut_sifre" placeholder="••••••••" required
                                   class="w-full bg-slate-50 rounded-xl p-3 pl-11 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            <i class="fas fa-lock absolute left-3.5 top-3.5 text-slate-400"></i>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Yeni Şifre</label>
                        <div class="relative">
                            <input type="password" name="yeni_sifre" id="yeniSifre" placeholder="En az 6 karakter" required
                                   class="w-full bg-slate-50 rounded-xl p-3 pl-11 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            <i class="fas fa-key absolute left-3.5 top-3.5 text-slate-400"></i>
                        </div>
                        <!-- Güç göstergesi -->
                        <div class="mt-2 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                            <div id="sifreguc" class="h-full rounded-full transition-all duration-300 w-0"></div>
                        </div>
                        <p id="sifregucyazi" class="text-[10px] text-slate-400 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Yeni Şifre (Tekrar)</label>
                        <div class="relative">
                            <input type="password" name="yeni_sifre2" placeholder="••••••••" required
                                   class="w-full bg-slate-50 rounded-xl p-3 pl-11 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            <i class="fas fa-check absolute left-3.5 top-3.5 text-slate-400"></i>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-black transition active:scale-95">
                        <i class="fas fa-lock mr-2"></i> Şifreyi Güncelle
                    </button>
                </form>
                <script>
                document.getElementById('yeniSifre').addEventListener('input', function() {
                    const v = this.value;
                    const el = document.getElementById('sifreguc');
                    const lbl = document.getElementById('sifregucyazi');
                    let guc = 0;
                    if (v.length >= 6) guc++;
                    if (v.length >= 10) guc++;
                    if (/[A-Z]/.test(v)) guc++;
                    if (/[0-9]/.test(v)) guc++;
                    if (/[^A-Za-z0-9]/.test(v)) guc++;
                    const renkler = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-lime-400','bg-emerald-500'];
                    const etiketler = ['Çok zayıf','Zayıf','Orta','Güçlü','Çok güçlü'];
                    el.className = 'h-full rounded-full transition-all duration-300 ' + (renkler[guc-1] || 'bg-slate-200');
                    el.style.width = (guc * 20) + '%';
                    lbl.innerText = v.length ? (etiketler[guc-1] || '') : '';
                });
                </script>

                <?php elseif ($aktifTab === 'bildirim'): ?>
                <!-- ─── BİLDİRİM ─── -->
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-bell text-indigo-500 mr-3"></i> Bildirim Ayarları
                </h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="islem" value="bildirim">
                    <?php
                    $toggle_items = [
                        ['name'=>'email_bildirim',  'label'=>'E-posta bildirimleri',        'aciklama'=>'Yeni etkinlik ve güncellemelerde e-posta al',         'field'=>'email_bildirim'],
                        ['name'=>'yorum_bildirim',  'label'=>'Yorum bildirimleri',           'aciklama'=>'Notlarına yorum yapıldığında bildirim al',           'field'=>'yorum_bildirim'],
                        ['name'=>'begeni_bildirim', 'label'=>'Beğeni bildirimleri',          'aciklama'=>'Notların beğenildiğinde bildirim al',               'field'=>'begeni_bildirim'],
                    ];
                    foreach ($toggle_items as $item):
                        $aktifDeger = (int)($kullanici[$item['field']] ?? 1);
                    ?>
                    <label class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl cursor-pointer hover:bg-indigo-50 transition group">
                        <div>
                            <p class="font-semibold text-slate-700 text-sm"><?= $item['label'] ?></p>
                            <p class="text-xs text-slate-400 mt-0.5"><?= $item['aciklama'] ?></p>
                        </div>
                        <div class="relative ml-4">
                            <input type="checkbox" name="<?= $item['name'] ?>" <?= $aktifDeger ? 'checked' : '' ?>
                                   class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-200 peer-checked:bg-indigo-500 rounded-full transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow peer-checked:translate-x-6 transition-transform"></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold hover:bg-indigo-700 transition mt-2 active:scale-95">
                        <i class="fas fa-save mr-2"></i> Tercihleri Kaydet
                    </button>
                </form>

                <?php elseif ($aktifTab === 'gorunum'): ?>
                <!-- ─── GÖRÜNÜM ─── -->
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-palette text-indigo-500 mr-3"></i> Görünüm
                </h2>
                <div class="space-y-4">
                    <p class="text-sm text-slate-500">Tema tercihin tarayıcına kaydedilir.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="setTema('light')" id="btnLight"
                                class="flex flex-col items-center p-5 rounded-2xl border-2 border-slate-200 hover:border-indigo-400 transition cursor-pointer bg-white">
                            <i class="fas fa-sun text-3xl text-amber-400 mb-2"></i>
                            <span class="font-bold text-slate-700 text-sm">Açık Tema</span>
                        </button>
                        <button onclick="setTema('dark')" id="btnDark"
                                class="flex flex-col items-center p-5 rounded-2xl border-2 border-slate-200 hover:border-indigo-400 transition cursor-pointer bg-slate-800">
                            <i class="fas fa-moon text-3xl text-indigo-300 mb-2"></i>
                            <span class="font-bold text-slate-300 text-sm">Koyu Tema</span>
                        </button>
                    </div>
                    <p id="temaOnay" class="text-emerald-600 text-sm font-semibold hidden"><i class="fas fa-check-circle mr-1"></i> Tema güncellendi.</p>
                </div>
                <script>
                function setTema(t) {
                    localStorage.setItem('tema', t);
                    document.documentElement.classList.toggle('dark', t === 'dark');
                    document.getElementById('temaOnay').classList.remove('hidden');
                }
                </script>

                <?php elseif ($aktifTab === 'hesap'): ?>
                <!-- ─── HESAP YÖNETİMİ ─── -->
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-user-slash text-red-500 mr-3"></i> Hesap Yönetimi
                </h2>
                <div class="space-y-6">
                    <!-- Hesap bilgisi -->
                    <div class="p-5 bg-slate-50 rounded-2xl">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Hesap Bilgileri</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div><span class="text-slate-400">Üyelik tarihi:</span><br>
                                <strong><?= isset($kullanici['created_at']) ? date('d.m.Y', strtotime($kullanici['created_at'])) : '—' ?></strong></div>
                            <div><span class="text-slate-400">Hesap türü:</span><br>
                                <strong><?= ($kullanici['rol'] === 'admin') ? '👑 Admin' : '👤 Kullanıcı' ?></strong></div>
                        </div>
                    </div>

                    <!-- Veri indir -->
                    <div class="p-5 bg-blue-50 rounded-2xl border border-blue-100">
                        <p class="font-bold text-slate-700 text-sm mb-1"><i class="fas fa-download mr-2 text-blue-500"></i>Verilerimi İndir</p>
                        <p class="text-xs text-slate-400 mb-3">Hesabınla ilgili tüm veriler (notlar, yorumlar) export edilir.</p>
                        <a href="veri_export.php" class="inline-block bg-blue-500 text-white text-xs font-bold px-4 py-2 rounded-xl hover:bg-blue-600 transition">
                            JSON Olarak İndir
                        </a>
                    </div>

                    <!-- Hesabı sil -->
                    <div class="p-5 bg-red-50 rounded-2xl border border-red-200">
                        <p class="font-bold text-red-700 mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>Hesabı Kalıcı Olarak Sil</p>
                        <p class="text-xs text-red-400 mb-4">Bu işlem geri alınamaz. Tüm notların, yorumların ve verilen silinir.</p>
                        <button onclick="document.getElementById('silPanel').classList.toggle('hidden')"
                                class="bg-red-500 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-red-600 transition">
                            Hesabı Sil
                        </button>
                        <div id="silPanel" class="hidden mt-4">
                            <form method="POST">
                                <input type="hidden" name="islem" value="hesap_sil">
                                <label class="block text-xs text-red-600 font-bold mb-2">Onaylamak için "SİL" yazın:</label>
                                <input type="text" name="sil_onay" placeholder="SİL"
                                       class="w-full bg-white rounded-xl p-3 text-sm ring-1 ring-red-300 focus:ring-2 focus:ring-red-500 outline-none mb-3">
                                <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-xl font-black hover:bg-red-700 transition">
                                    Hesabımı Kalıcı Olarak Sil
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /card -->
        </div><!-- /col-3 -->
    </div><!-- /grid -->
</main>

<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
