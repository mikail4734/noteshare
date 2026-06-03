<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';

// Giriş yapmamışsa yönlendir
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: giris.php?yonlendir=ayarlar");
    exit;
}

$email  = $_SESSION['user_email'];
$basari = '';
$hata   = '';

// ──────────────────────────────────────────
// Eksik kolonları otomatik ekle (self-migration)
// users tablosunda olmayan kolonları güvenle oluşturur
// ──────────────────────────────────────────
function kolonGarantile(PDO $db, string $tablo, string $kolon, string $tanim): void {
    try {
        $s = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
                           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $s->execute([$tablo, $kolon]);
        if ((int)$s->fetchColumn() === 0) {
            $db->exec("ALTER TABLE `$tablo` ADD COLUMN `$kolon` $tanim");
        }
    } catch (PDOException $e) { /* sessiz geç */ }
}
kolonGarantile($db, 'users', 'egitim_seviyesi', "VARCHAR(20) DEFAULT NULL");
kolonGarantile($db, 'users', 'email_bildirim',  "TINYINT(1) DEFAULT 1");
kolonGarantile($db, 'users', 'yorum_bildirim',  "TINYINT(1) DEFAULT 1");
kolonGarantile($db, 'users', 'begeni_bildirim', "TINYINT(1) DEFAULT 1");

// ──────────────────────────────────────────
// POST işlemleri
// ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? '';

    // 1) Profil güncelle
    if ($islem === 'profil') {
        $yeniAd     = trim($_POST['ad'] ?? '');
        $yeniBio    = trim($_POST['bio'] ?? '');
        $yeniEgitim = trim($_POST['egitim_seviyesi'] ?? '');
        if ($yeniAd === '') {
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
        $mevcut = $_POST['mevcut_sifre'] ?? '';
        $yeni   = $_POST['yeni_sifre']   ?? '';
        $yeni2  = $_POST['yeni_sifre2']  ?? '';
        if (strlen($yeni) < 6) {
            $hata = "Yeni şifre en az 6 karakter olmalıdır.";
        } elseif ($yeni !== $yeni2) {
            $hata = "Yeni şifreler birbiriyle eşleşmiyor.";
        } else {
            $st = $db->prepare("SELECT password FROM users WHERE email=?");
            $st->execute([$email]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($mevcut, $row['password'])) {
                $db->prepare("UPDATE users SET password=? WHERE email=?")
                   ->execute([password_hash($yeni, PASSWORD_DEFAULT), $email]);
                $basari = "Şifren başarıyla güncellendi.";
            } else {
                $hata = "Mevcut şifre hatalı.";
            }
        }
    }

    // 3) Bildirim tercihleri
    if ($islem === 'bildirim') {
        $e1 = isset($_POST['email_bildirim'])  ? 1 : 0;
        $e2 = isset($_POST['yorum_bildirim'])  ? 1 : 0;
        $e3 = isset($_POST['begeni_bildirim']) ? 1 : 0;
        try {
            $db->prepare("UPDATE users SET email_bildirim=?, yorum_bildirim=?, begeni_bildirim=? WHERE email=?")
               ->execute([$e1, $e2, $e3, $email]);
            $basari = "Bildirim tercihlerin güncellendi.";
        } catch (PDOException $e) {
            $hata = "Bildirim kaydedilemedi.";
        }
    }

    // 4) Tema (görünüm)
    if ($islem === 'tema') {
        $tema = ($_POST['tema'] ?? 'light') === 'dark' ? 'dark' : 'light';
        try {
            $db->prepare("UPDATE users SET tema=? WHERE email=?")->execute([$tema, $email]);
            $basari = "Tema tercihi kaydedildi.";
        } catch (PDOException $e) {
            $hata = "Tema kaydedilemedi.";
        }
    }

    // 5) Hesabı sil
    if ($islem === 'hesap_sil') {
        if (trim($_POST['sil_onay'] ?? '') === 'SİL') {
            try {
                $db->prepare("DELETE FROM users WHERE email=?")->execute([$email]);
                session_unset();
                session_destroy();
                header("Location: giris.php?hata=hesap_silindi");
                exit;
            } catch (PDOException $e) {
                $hata = "Silme sırasında hata oluştu.";
            }
        } else {
            $hata = "Hesabı silmek için kutuya 'SİL' yazmalısın.";
        }
    }
}

// ──────────────────────────────────────────
// Kullanıcıyı DB'den çek
// ──────────────────────────────────────────
try {
    $st = $db->prepare("SELECT * FROM users WHERE email=?");
    $st->execute([$email]);
    $kullanici = $st->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $kullanici = null; }

if (!$kullanici) {
    session_destroy();
    header("Location: giris.php");
    exit;
}

$profil_resmi = (!empty($_SESSION['user_picture']))
    ? $_SESSION['user_picture']
    : 'https://ui-avatars.com/api/?name=' . urlencode($kullanici['ad'] ?? 'User') . '&background=4f46e5&color=fff&size=128';

$aktifTab = $_GET['tab'] ?? 'profil';
$menuler = [
    'profil'   => ['icon' => 'fa-user-circle', 'label' => 'Profil Bilgileri'],
    'sifre'    => ['icon' => 'fa-shield-alt',  'label' => 'Güvenlik & Şifre'],
    'bildirim' => ['icon' => 'fa-bell',         'label' => 'Bildirimler'],
    'gorunum'  => ['icon' => 'fa-palette',      'label' => 'Görünüm'],
    'hesap'    => ['icon' => 'fa-user-slash',   'label' => 'Hesap Yönetimi'],
];
if (!isset($menuler[$aktifTab])) $aktifTab = 'profil';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .tab-active { background:#4f46e5; color:#fff; box-shadow:0 4px 24px rgba(79,70,229,.18); }
        html.dark body { background:#0f172a; color:#e2e8f0; }
        html.dark .kart { background:#1e293b !important; border-color:#334155 !important; }
        html.dark .alan { background:#0f172a !important; color:#e2e8f0 !important; }
    </style>
    <script>(function(){ if((localStorage.getItem('tema')||'<?= $kullanici['tema'] ?? 'light' ?>')==='dark') document.documentElement.classList.add('dark'); })();</script>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 min-h-screen">

<nav class="bg-[#4f46e5] px-6 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-white/70 hover:text-white transition text-xl"><i class="fas fa-arrow-left"></i></a>
        <span class="text-white font-black text-xl">Ayarlar</span>
    </div>
    <div class="flex items-center space-x-3">
        <span class="text-white/70 text-sm hidden md:block"><?= htmlspecialchars($kullanici['ad'] ?? '') ?></span>
        <img src="<?= $profil_resmi ?>" alt="Profil" class="w-9 h-9 rounded-full border-2 border-white/40 object-cover">
    </div>
</nav>

<main class="container mx-auto max-w-5xl mt-10 mb-24 px-4">

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

        <!-- SOL MENÜ -->
        <div class="md:col-span-1 space-y-2">
            <div class="kart bg-white rounded-2xl p-5 text-center border border-slate-100 shadow-sm mb-4">
                <img src="<?= $profil_resmi ?>" alt="Profil" class="w-16 h-16 rounded-2xl mx-auto mb-3 object-cover border-2 border-indigo-100">
                <p class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($kullanici['ad'] ?? '') ?></p>
                <p class="text-xs text-slate-400 mt-1 break-all"><?= htmlspecialchars($kullanici['email']) ?></p>
                <?php if (($kullanici['rol'] ?? '') === 'admin'): ?>
                    <span class="inline-block mt-2 px-3 py-1 bg-rose-100 text-rose-600 text-[10px] font-black rounded-full uppercase tracking-widest">Admin</span>
                <?php endif; ?>
            </div>
            <?php foreach ($menuler as $tab => $m): ?>
            <a href="?tab=<?= $tab ?>"
               class="flex items-center px-4 py-3 rounded-xl font-semibold text-sm transition <?= $aktifTab === $tab ? 'tab-active' : 'bg-white text-slate-500 hover:bg-slate-100' ?>">
                <i class="fas <?= $m['icon'] ?> mr-3 w-4 text-center"></i> <?= $m['label'] ?>
            </a>
            <?php endforeach; ?>
            <a href="cikis.php" class="flex items-center px-4 py-3 rounded-xl font-semibold text-sm text-red-500 bg-red-50 hover:bg-red-100 transition mt-2">
                <i class="fas fa-sign-out-alt mr-3 w-4 text-center"></i> Çıkış Yap
            </a>
        </div>

        <!-- SAĞ İÇERİK -->
        <div class="md:col-span-3">
            <div class="kart bg-white rounded-[2rem] shadow-sm border border-slate-100 p-8">

                <?php if ($aktifTab === 'profil'): ?>
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-user-circle text-indigo-500 mr-3"></i> Profil Bilgileri
                </h2>
                <div class="flex items-center gap-5 mb-8 p-4 bg-slate-50 rounded-2xl">
                    <img src="<?= $profil_resmi ?>" alt="Profil" class="w-20 h-20 rounded-2xl object-cover border-2 border-indigo-100 shadow">
                    <div>
                        <p class="text-sm font-bold text-slate-700">Profil Fotoğrafı</p>
                        <p class="text-xs text-slate-400 mt-1">Google ile giriş yaptıysan fotoğrafın otomatik gelir.</p>
                    </div>
                </div>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="islem" value="profil">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Ad Soyad</label>
                            <input type="text" name="ad" value="<?= htmlspecialchars($kullanici['ad'] ?? '') ?>" required
                                   class="alan w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">E-Posta</label>
                            <input type="email" value="<?= htmlspecialchars($kullanici['email']) ?>" disabled
                                   class="w-full bg-slate-100 rounded-xl p-3 text-sm ring-1 ring-slate-200 outline-none text-slate-400 cursor-not-allowed">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Eğitim Seviyesi</label>
                        <select name="egitim_seviyesi" class="alan w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Seçiniz...</option>
                            <?php foreach (['İlkokul','Ortaokul','Lise','Üniversite','Mezun'] as $s): ?>
                                <option value="<?= $s ?>" <?= (($kullanici['egitim_seviyesi'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Hakkımda / Bio</label>
                        <textarea name="bio" rows="3" maxlength="255" placeholder="Kendini kısaca tanıt..."
                                  class="alan w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition resize-none"><?= htmlspecialchars($kullanici['bio'] ?? '') ?></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-4 p-4 bg-indigo-50 rounded-2xl">
                        <div class="text-center"><p class="text-2xl font-black text-indigo-600"><?= (int)($kullanici['xp'] ?? 0) ?></p><p class="text-xs text-slate-500 font-medium">XP</p></div>
                        <div class="text-center"><p class="text-2xl font-black text-orange-500"><?= (int)($kullanici['streak'] ?? 0) ?></p><p class="text-xs text-slate-500 font-medium">Streak 🔥</p></div>
                        <div class="text-center"><p class="text-2xl font-black text-emerald-600"><?= (int)($kullanici['seviye'] ?? 1) ?></p><p class="text-xs text-slate-500 font-medium">Seviye</p></div>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition active:scale-95">
                        <i class="fas fa-save mr-2"></i> Değişiklikleri Kaydet
                    </button>
                </form>

                <?php elseif ($aktifTab === 'sifre'): ?>
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-shield-alt text-indigo-500 mr-3"></i> Güvenlik & Şifre
                </h2>
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-sm text-amber-700 font-medium">
                    <i class="fas fa-info-circle mr-2"></i> Google/Facebook ile giriş yapıyorsan şifre değiştirmen gerekmez.
                </div>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="islem" value="sifre">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mevcut Şifre</label>
                        <input type="password" name="mevcut_sifre" placeholder="••••••••" required
                               class="alan w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Yeni Şifre</label>
                        <input type="password" name="yeni_sifre" id="yeniSifre" placeholder="En az 6 karakter" required
                               class="alan w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        <div class="mt-2 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                            <div id="sifreguc" class="h-full rounded-full transition-all duration-300" style="width:0"></div>
                        </div>
                        <p id="sifregucyazi" class="text-[10px] text-slate-400 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="yeni_sifre2" placeholder="••••••••" required
                               class="alan w-full bg-slate-50 rounded-xl p-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-black transition active:scale-95">
                        <i class="fas fa-lock mr-2"></i> Şifreyi Güncelle
                    </button>
                </form>
                <script>
                document.getElementById('yeniSifre').addEventListener('input', function() {
                    const v = this.value, el = document.getElementById('sifreguc'), lbl = document.getElementById('sifregucyazi');
                    let g = 0;
                    if (v.length >= 6) g++; if (v.length >= 10) g++;
                    if (/[A-Z]/.test(v)) g++; if (/[0-9]/.test(v)) g++; if (/[^A-Za-z0-9]/.test(v)) g++;
                    const renk = ['#f87171','#fb923c','#facc15','#a3e635','#10b981'];
                    const yazi = ['Çok zayıf','Zayıf','Orta','Güçlü','Çok güçlü'];
                    el.style.width = (g*20)+'%'; el.style.background = renk[g-1] || '#e2e8f0';
                    lbl.innerText = v.length ? (yazi[g-1] || '') : '';
                });
                </script>

                <?php elseif ($aktifTab === 'bildirim'): ?>
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-bell text-indigo-500 mr-3"></i> Bildirim Ayarları
                </h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="islem" value="bildirim">
                    <?php
                    $toggles = [
                        ['name'=>'email_bildirim',  'label'=>'E-posta bildirimleri', 'aciklama'=>'Yeni etkinliklerde e-posta al'],
                        ['name'=>'yorum_bildirim',  'label'=>'Yorum bildirimleri',    'aciklama'=>'Notlarına yorum yapıldığında bildirim al'],
                        ['name'=>'begeni_bildirim', 'label'=>'Beğeni bildirimleri',   'aciklama'=>'Notların beğenildiğinde bildirim al'],
                    ];
                    foreach ($toggles as $t):
                        $acik = (int)($kullanici[$t['name']] ?? 1);
                    ?>
                    <label class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl cursor-pointer hover:bg-indigo-50 transition">
                        <div>
                            <p class="font-semibold text-slate-700 text-sm"><?= $t['label'] ?></p>
                            <p class="text-xs text-slate-400 mt-0.5"><?= $t['aciklama'] ?></p>
                        </div>
                        <div class="relative ml-4">
                            <input type="checkbox" name="<?= $t['name'] ?>" <?= $acik ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-300 peer-checked:bg-indigo-500 rounded-full transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow peer-checked:translate-x-6 transition-transform"></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold hover:bg-indigo-700 transition mt-2 active:scale-95">
                        <i class="fas fa-save mr-2"></i> Tercihleri Kaydet
                    </button>
                </form>

                <?php elseif ($aktifTab === 'gorunum'): ?>
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-palette text-indigo-500 mr-3"></i> Görünüm
                </h2>
                <p class="text-sm text-slate-500 mb-5">Tema tercihin hem hesabına hem tarayıcına kaydedilir.</p>
                <form method="POST">
                    <input type="hidden" name="islem" value="tema">
                    <input type="hidden" name="tema" id="temaInput" value="<?= htmlspecialchars($kullanici['tema'] ?? 'light') ?>">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <button type="button" onclick="secTema('light')" id="kartLight"
                                class="flex flex-col items-center p-6 rounded-2xl border-2 transition cursor-pointer bg-white <?= ($kullanici['tema'] ?? 'light')==='light' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-slate-200' ?>">
                            <i class="fas fa-sun text-3xl text-amber-400 mb-2"></i>
                            <span class="font-bold text-slate-700 text-sm">Açık Tema</span>
                        </button>
                        <button type="button" onclick="secTema('dark')" id="kartDark"
                                class="flex flex-col items-center p-6 rounded-2xl border-2 transition cursor-pointer bg-slate-800 <?= ($kullanici['tema'] ?? 'light')==='dark' ? 'border-indigo-400 ring-2 ring-indigo-300' : 'border-slate-700' ?>">
                            <i class="fas fa-moon text-3xl text-indigo-300 mb-2"></i>
                            <span class="font-bold text-slate-200 text-sm">Koyu Tema</span>
                        </button>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold hover:bg-indigo-700 transition active:scale-95">
                        <i class="fas fa-save mr-2"></i> Temayı Kaydet
                    </button>
                </form>
                <script>
                function secTema(t){
                    document.getElementById('temaInput').value = t;
                    localStorage.setItem('tema', t);
                    document.documentElement.classList.toggle('dark', t === 'dark');
                    document.getElementById('kartLight').classList.toggle('border-indigo-500', t==='light');
                    document.getElementById('kartLight').classList.toggle('ring-2', t==='light');
                    document.getElementById('kartDark').classList.toggle('border-indigo-400', t==='dark');
                    document.getElementById('kartDark').classList.toggle('ring-2', t==='dark');
                }
                </script>

                <?php elseif ($aktifTab === 'hesap'): ?>
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-user-slash text-red-500 mr-3"></i> Hesap Yönetimi
                </h2>
                <div class="space-y-6">
                    <div class="p-5 bg-slate-50 rounded-2xl">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Hesap Bilgileri</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div><span class="text-slate-400">Üyelik tarihi:</span><br>
                                <strong><?= isset($kullanici['kayit_tarihi']) ? date('d.m.Y', strtotime($kullanici['kayit_tarihi'])) : '—' ?></strong></div>
                            <div><span class="text-slate-400">Hesap türü:</span><br>
                                <strong><?= (($kullanici['rol'] ?? '') === 'admin') ? '👑 Admin' : '👤 Kullanıcı' ?></strong></div>
                        </div>
                    </div>
                    <div class="p-5 bg-red-50 rounded-2xl border border-red-200">
                        <p class="font-bold text-red-700 mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>Hesabı Kalıcı Olarak Sil</p>
                        <p class="text-xs text-red-400 mb-4">Bu işlem geri alınamaz. Tüm verilerin silinir.</p>
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

            </div>
        </div>
    </div>
</main>

<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
