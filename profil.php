<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';

$hedefEmail = $_GET['email'] ?? '';
$benEmail = $_SESSION['user_email'] ?? null;

if (!$hedefEmail) {
    http_response_code(400);
    die("<div style='text-align:center;padding:50px;font-family:sans-serif'>Kullanıcı bulunamadı. <a href='index.php'>← Anasayfa</a></div>");
}

// Hedef kullaniciyi cek
$s = $db->prepare("SELECT id, ad, email, rol, xp, seviye, streak, bio, kayit_tarihi FROM users WHERE email = ?");
$s->execute([$hedefEmail]);
$kullanici = $s->fetch(PDO::FETCH_ASSOC);

if (!$kullanici) {
    http_response_code(404);
    die("<div style='text-align:center;padding:50px;font-family:sans-serif'>Bu kullanıcı bulunamadı. <a href='index.php'>← Anasayfa</a></div>");
}

// Istatistikler
$notSayisi = (int)$db->prepare("SELECT COUNT(*) FROM notes WHERE kullanici_email = ? AND (durum IS NULL OR durum = 'onayli')")
    ->execute([$hedefEmail]) ?: 0;
$s = $db->prepare("SELECT COUNT(*) FROM notes WHERE kullanici_email = ? AND (durum IS NULL OR durum = 'onayli')");
$s->execute([$hedefEmail]);
$notSayisi = (int)$s->fetchColumn();

$s = $db->prepare("SELECT COUNT(*) FROM takipler WHERE takip_edilen = ?");
$s->execute([$hedefEmail]);
$takipciSayisi = (int)$s->fetchColumn();

$s = $db->prepare("SELECT COUNT(*) FROM takipler WHERE takip_eden = ?");
$s->execute([$hedefEmail]);
$takipEttigi = (int)$s->fetchColumn();

$s = $db->prepare("SELECT COALESCE(SUM(likes),0) FROM notes WHERE kullanici_email = ? AND (durum IS NULL OR durum = 'onayli')");
$s->execute([$hedefEmail]);
$toplamBegeni = (int)$s->fetchColumn();

// Ben bu kullaniciyi takip ediyor muyum?
$takipEdiyorum = false;
if ($benEmail && $benEmail !== $hedefEmail) {
    $s = $db->prepare("SELECT 1 FROM takipler WHERE takip_eden = ? AND takip_edilen = ?");
    $s->execute([$benEmail, $hedefEmail]);
    $takipEdiyorum = $s->fetchColumn() ? true : false;
}

// Bu kullanicinin paylastigi public notlar (grup notlari hariç)
$s = $db->prepare("SELECT id, title, category, edu_level, subject, likes, goruntulenme, created_at
                   FROM notes
                   WHERE kullanici_email = ?
                     AND (durum IS NULL OR durum = 'onayli')
                     AND grup_id IS NULL
                   ORDER BY created_at DESC LIMIT 30");
$s->execute([$hedefEmail]);
$notlar = $s->fetchAll(PDO::FETCH_ASSOC);

// Avatar URL (ui-avatars)
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($kullanici['ad'] ?? 'U') . "&background=4f46e5&color=fff&size=200&bold=true";

$benim = ($benEmail === $hedefEmail);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($kullanici['ad']) ?> — notewarehouse</title>
    <meta name="description" content="<?= htmlspecialchars($kullanici['ad']) ?> notewarehouse profili. <?= $notSayisi ?> not, <?= $takipciSayisi ?> takipçi.">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50">

<nav class="bg-white border-b border-slate-200 px-6 py-3 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <a href="index.php" class="flex items-center text-indigo-600 font-black text-xl">
        <img src="/favicon-180.png" class="w-8 h-8 rounded-lg mr-2"> notewarehouse
    </a>
    <a href="javascript:history.back()" class="text-slate-500 hover:text-indigo-600 text-sm font-bold">
        <i class="fas fa-arrow-left mr-1"></i> Geri
    </a>
</nav>

<!-- KAPAK -->
<div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 h-48 relative"></div>

<main class="max-w-5xl mx-auto px-4 -mt-24 pb-16">

    <!-- PROFIL KARTI -->
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 mb-6">
        <div class="flex flex-col md:flex-row items-start gap-6">

            <!-- Avatar -->
            <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($kullanici['ad']) ?>"
                 class="w-32 h-32 rounded-3xl border-4 border-white shadow-lg -mt-16 md:-mt-20">

            <div class="flex-1">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-slate-800">
                            <?= htmlspecialchars($kullanici['ad']) ?>
                            <?php if ($kullanici['rol'] === 'admin'): ?>
                                <span class="inline-block bg-gradient-to-r from-amber-400 to-orange-500 text-white text-xs font-black px-2 py-1 rounded-md ml-1 align-middle">
                                    <i class="fas fa-crown text-[10px] mr-0.5"></i> ADMIN
                                </span>
                            <?php endif; ?>
                        </h1>
                        <p class="text-sm text-slate-500 mt-1">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            <?= date('M Y', strtotime($kullanici['kayit_tarihi'])) ?>'den beri üye
                        </p>
                        <?php if (!empty($kullanici['bio'])): ?>
                            <p class="text-slate-700 mt-3 leading-relaxed"><?= htmlspecialchars($kullanici['bio']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-2 shrink-0">
                        <?php if ($benim): ?>
                            <a href="ayarlar.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-6 py-3 rounded-xl text-base transition flex items-center gap-2 shadow-sm">
                                <i class="fas fa-cog"></i> Profili Düzenle
                            </a>
                        <?php elseif ($benEmail): ?>
                            <button id="takipBtn" onclick="takipToggle()"
                                    class="<?= $takipEdiyorum ? 'bg-white hover:bg-rose-50 text-slate-700 hover:text-rose-600 border-2 border-slate-300 hover:border-rose-400' : 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-2 border-transparent shadow-lg shadow-indigo-200' ?> font-black px-8 py-3 rounded-xl text-base transition-all flex items-center gap-2 min-w-[180px] justify-center hover:scale-105 active:scale-95">
                                <i class="fas <?= $takipEdiyorum ? 'fa-user-check' : 'fa-user-plus' ?> text-lg"></i>
                                <span id="takipText"><?= $takipEdiyorum ? 'Takiptesin' : 'Takip Et' ?></span>
                            </button>
                        <?php else: ?>
                            <a href="giris.php" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black px-8 py-3 rounded-xl text-base transition-all flex items-center gap-2 shadow-lg shadow-indigo-200 hover:scale-105">
                                <i class="fas fa-user-plus text-lg"></i> Takip Etmek için Giriş Yap
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ISTATISTIKLER -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-6 pt-6 border-t border-slate-100">
                    <div class="text-center">
                        <p class="text-2xl font-black text-indigo-600"><?= $notSayisi ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-1">Not</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-black text-pink-600" id="takipciSayisi"><?= $takipciSayisi ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-1">Takipçi</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-black text-purple-600"><?= $takipEttigi ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-1">Takip</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-black text-rose-500"><?= $toplamBegeni ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-1">Beğeni</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-black text-amber-500">Lv<?= $kullanici['seviye'] ?? 1 ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-1">Seviye</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$benim && $benEmail && !$takipEdiyorum): ?>
    <!-- TAKIP CTA Banner (büyük, dikkat çekici) -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 rounded-3xl shadow-xl p-6 md:p-8 mb-6 text-white">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-3xl backdrop-blur-sm">
                    🔔
                </div>
                <div>
                    <h3 class="text-xl font-black mb-1"><?= htmlspecialchars($kullanici['ad']) ?>'ı takip et!</h3>
                    <p class="text-indigo-100 text-sm">Yeni not paylaşımlarından anında haberdar ol.</p>
                </div>
            </div>
            <button type="button" onclick="takipToggle(event)" id="takipBtn2"
                class="bg-white text-indigo-700 hover:bg-slate-100 font-black px-8 py-3 rounded-xl text-base shadow-xl hover:scale-105 active:scale-95 transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer">
                <i class="fas fa-user-plus text-lg pointer-events-none"></i>
                <span id="takipText2" class="pointer-events-none">Takip Et</span>
            </button>
        </div>
        <!-- Inline durum mesaji -->
        <div id="takipDurum" class="hidden mt-4 bg-white/20 backdrop-blur-sm rounded-xl p-3 text-sm font-bold"></div>
    </div>
    <?php elseif (!$benim && $benEmail && $takipEdiyorum): ?>
    <!-- Takiptesin Banner -->
    <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-5 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                <i class="fas fa-check text-lg"></i>
            </div>
            <p class="text-emerald-800 font-bold">
                <?= htmlspecialchars($kullanici['ad']) ?>'ı takip ediyorsun.
                <span class="text-emerald-600 font-normal text-sm">Yeni notlarından bildirim alacaksın.</span>
            </p>
        </div>
        <button onclick="takipToggle()" id="takipBtn2"
            class="bg-white hover:bg-rose-50 text-rose-600 border-2 border-rose-200 hover:border-rose-400 font-bold px-5 py-2 rounded-xl text-sm transition">
            <span id="takipText2"><i class="fas fa-user-times mr-1"></i> Takipten Çık</span>
        </button>
    </div>
    <?php endif; ?>

    <!-- PAYLASILAN NOTLAR -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
        <h2 class="text-xl font-black text-slate-800 mb-6 flex items-center">
            <i class="fas fa-file-alt text-indigo-500 mr-2"></i> Paylaşılan Notlar
            <span class="ml-2 bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-md"><?= count($notlar) ?></span>
        </h2>

        <?php if (empty($notlar)): ?>
            <div class="text-center py-16 text-slate-400">
                <i class="fas fa-folder-open text-5xl mb-3"></i>
                <p>Henüz not paylaşılmamış.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($notlar as $n): ?>
                    <a href="notlar.php?id=<?= $n['id'] ?>"
                       class="block bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-300 rounded-2xl p-4 transition group">
                        <div class="flex items-start justify-between mb-2">
                            <span class="inline-block bg-indigo-100 text-indigo-700 text-[10px] font-black px-2 py-1 rounded-md uppercase tracking-wider">
                                <?= htmlspecialchars($n['category'] ?? 'Genel') ?>
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">
                                <?= date('d M', strtotime($n['created_at'])) ?>
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-800 group-hover:text-indigo-600 transition mb-1 line-clamp-1">
                            <?= htmlspecialchars($n['title']) ?>
                        </h3>
                        <p class="text-xs text-slate-500 mb-2">
                            <?= htmlspecialchars($n['edu_level'] ?? '') ?> · <?= htmlspecialchars($n['subject'] ?? '') ?>
                        </p>
                        <div class="flex items-center gap-3 text-xs text-slate-400 font-medium">
                            <span><i class="far fa-thumbs-up mr-1"></i> <?= $n['likes'] ?></span>
                            <span><i class="far fa-eye mr-1"></i> <?= $n['goruntulenme'] ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<script>
function showDurum(mesaj, tip = 'info') {
    const d = document.getElementById('takipDurum');
    if (!d) return;
    const colors = {
        info:    'bg-white/20',
        loading: 'bg-blue-500/30',
        success: 'bg-emerald-500/40',
        error:   'bg-rose-500/40'
    };
    d.className = 'mt-4 backdrop-blur-sm rounded-xl p-3 text-sm font-bold ' + (colors[tip] || colors.info);
    d.innerText = mesaj;
}

async function takipToggle(e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    console.log('🔔 takipToggle çağrıldı');
    console.log('  hedef:', '<?= htmlspecialchars($hedefEmail) ?>');
    console.log('  giris yapan:', '<?= htmlspecialchars($benEmail ?? "YOK") ?>');

    showDurum('İşleniyor...', 'loading');

    // Tum takip butonlarini disable et
    document.querySelectorAll('#takipBtn, #takipBtn2').forEach(b => {
        b.disabled = true;
        b.style.opacity = '0.6';
    });

    try {
        const r = await fetch('islem.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            credentials: 'same-origin',
            body: JSON.stringify({ islem: 'takip_toggle', hedef: '<?= htmlspecialchars($hedefEmail) ?>' })
        });

        console.log('  HTTP status:', r.status);
        const responseText = await r.text();
        console.log('  Yanıt:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseErr) {
            showDurum('Sunucu HTML döndü (htaccess?): ' + responseText.substring(0, 200), 'error');
            console.error('JSON parse error', parseErr);
            return;
        }

        if (data.success) {
            showDurum('✓ Başarılı! Sayfa yenileniyor...', 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            // Giriş yapılmamışsa giriş sayfasına yönlendir
            if (data.error && (data.error.toLowerCase().includes('giriş') || data.error.toLowerCase().includes('giris'))) {
                showDurum('Giriş yapmalısın → giris.php', 'error');
                setTimeout(() => { window.location.href = 'giris.php?neden=takip'; }, 1500);
                return;
            }
            showDurum('✗ ' + (data.error || 'Bilinmeyen hata'), 'error');
        }
    } catch (err) {
        console.error('Fetch hatası:', err);
        showDurum('Bağlantı hatası: ' + err.message, 'error');
    } finally {
        document.querySelectorAll('#takipBtn, #takipBtn2').forEach(b => {
            b.disabled = false;
            b.style.opacity = '1';
        });
    }
}
</script>

<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
