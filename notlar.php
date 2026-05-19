<?php


require_once __DIR__ . '/oturum_baslat.php';
require_once 'baglan.php';
require_once 'helpers.php';

$noteId = isset($_GET['id']) ? intval($_GET['id']) : null;
$grupId = isset($_GET['grup_id']) ? intval($_GET['grup_id']) : null;
$mevcutNot = null;
$mevcutSorular = [];
$mevcutGrup = null;
$kullaniciEmail = $_SESSION['user_email'] ?? null;
$kullaniciRol = $_SESSION['rol'] ?? 'guest';

// GIRIS KONTROLU — Yeni not olusturma anonim erisime kapali
// Mevcut bir not goruntulemek serbest
if (!$noteId && !$kullaniciEmail) {
    header("Location: giris.php?neden=not_yazmak");
    exit;
}

// Erişim ve düzenleme yetkisi
$duzenleyebilir = !$noteId; // Yeni not oluşturuyorsa düzenleyebilir
$sahibi = false;
$grupUyesi = false;

// Grup notu oluşturma modu
if ($grupId && !$noteId) {
    $g = $db->prepare("SELECT g.* FROM gruplar g
                       JOIN grup_uyeleri gu ON gu.grup_id = g.id
                       WHERE g.id = ? AND gu.kullanici_email = ?");
    $g->execute([$grupId, $kullaniciEmail]);
    $mevcutGrup = $g->fetch(PDO::FETCH_ASSOC);
    if (!$mevcutGrup) {
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Bu gruba üye değilsiniz veya grup mevcut değil. <a href='gruplarim.php'>← Gruplarım</a></div>");
    }
}

if ($noteId) {
    $sorgu = $db->prepare("SELECT * FROM notes WHERE id = ?");
    $sorgu->execute([$noteId]);
    $mevcutNot = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($mevcutNot) {
        // Görüntülenme sayacını arttır (kendi notu değilse)
        if ($mevcutNot['kullanici_email'] !== $kullaniciEmail) {
            try {
                $db->prepare("UPDATE notes SET goruntulenme = goruntulenme + 1 WHERE id = ?")->execute([$noteId]);
            } catch (Exception $e) {}
        }

        // Grup üyesi miyim?
        if (!empty($mevcutNot['grup_id'])) {
            $u = $db->prepare("SELECT 1 FROM grup_uyeleri WHERE grup_id = ? AND kullanici_email = ?");
            $u->execute([$mevcutNot['grup_id'], $kullaniciEmail]);
            $grupUyesi = $u->rowCount() > 0;

            // Grup bilgisini de çek
            $gs = $db->prepare("SELECT * FROM gruplar WHERE id = ?");
            $gs->execute([$mevcutNot['grup_id']]);
            $mevcutGrup = $gs->fetch(PDO::FETCH_ASSOC);

            // ERIsIM KONTROLU: Grup notu sadece grup uyeleri/sahibi/admin gorebilir
            $sahibi = ($mevcutNot['kullanici_email'] === $kullaniciEmail);
            if (!$grupUyesi && !$sahibi && $kullaniciRol !== 'admin') {
                http_response_code(403);
                die("<div style='text-align:center;padding:80px;font-family:sans-serif;'>
                    <h1 style='color:#dc2626;font-size:3em;margin:0'>🔒</h1>
                    <h2 style='color:#1e293b'>Bu not özel bir gruba ait</h2>
                    <p style='color:#64748b;margin:20px 0'>Sadece grup üyeleri bu notu görüntüleyebilir.</p>
                    <a href='index.php' style='background:#4f46e5;color:white;padding:12px 24px;border-radius:12px;text-decoration:none;font-weight:bold;display:inline-block;margin-top:20px'>← Anasayfaya Dön</a>
                </div>");
            }
        }

        // Düzenleme yetkisi: sahip, admin VEYA grup üyesi
        $sahibi = ($mevcutNot['kullanici_email'] === $kullaniciEmail);
        $duzenleyebilir = $sahibi || $kullaniciRol === 'admin' || $grupUyesi;

        $soruSorgu = $db->prepare("SELECT * FROM not_sorulari WHERE note_id = ? ORDER BY id ASC");
        $soruSorgu->execute([$noteId]);
        $mevcutSorular = $soruSorgu->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Yer imi durumu
$imlediMi = false;
if ($noteId && $kullaniciEmail) {
    try {
        $k = $db->prepare("SELECT 1 FROM yer_imleri WHERE kullanici_email = ? AND note_id = ?");
        $k->execute([$kullaniciEmail, $noteId]);
        $imlediMi = $k->rowCount() > 0;
    } catch (Exception $e) {}
}

// Yorumlar
$yorumlar = [];
if ($noteId) {
    try {
        $yc = $db->prepare("SELECT * FROM yorumlar WHERE note_id = ? ORDER BY tarih DESC LIMIT 100");
        $yc->execute([$noteId]);
        $yorumlar = $yc->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

// Mevcut etiketler
$mevcutEtiketler = [];
if ($noteId) {
    try {
        $et = $db->prepare("SELECT etiket FROM etiketler WHERE note_id = ?");
        $et->execute([$noteId]);
        $mevcutEtiketler = $et->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
}

// Yazarı takip ediyor muyum
$takipEdiyorum = false;
$yazarTakipciSayisi = 0;
if ($noteId && $mevcutNot && $mevcutNot['kullanici_email'] && $kullaniciEmail && $mevcutNot['kullanici_email'] !== $kullaniciEmail) {
    try {
        $t = $db->prepare("SELECT 1 FROM takipler WHERE takip_eden = ? AND takip_edilen = ?");
        $t->execute([$kullaniciEmail, $mevcutNot['kullanici_email']]);
        $takipEdiyorum = $t->rowCount() > 0;
        $tc = $db->prepare("SELECT COUNT(*) FROM takipler WHERE takip_edilen = ?");
        $tc->execute([$mevcutNot['kullanici_email']]);
        $yazarTakipciSayisi = (int)$tc->fetchColumn();
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mevcutNot ? htmlspecialchars($mevcutNot['title']) . ' | notewarehouse' : 'notewarehouse | Profesyonel Not Editörü' ?></title>

    <?php if ($mevcutNot): ?>
        <?php
            $ogOzet = trim(strip_tags($mevcutNot['content'] ?? ''));
            $ogOzet = mb_strlen($ogOzet) > 200 ? mb_substr($ogOzet, 0, 197) . '...' : $ogOzet;
            $ogUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        ?>
        <meta property="og:type" content="article">
        <meta property="og:title" content="<?= htmlspecialchars($mevcutNot['title']) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($ogOzet) ?>">
        <meta property="og:url" content="<?= htmlspecialchars($ogUrl) ?>">
        <meta property="og:site_name" content="notewarehouse">
        <meta property="article:author" content="<?= htmlspecialchars($mevcutNot['author'] ?? 'notewarehouse') ?>">
        <meta property="article:section" content="<?= htmlspecialchars($mevcutNot['category'] ?? '') ?>">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= htmlspecialchars($mevcutNot['title']) ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($ogOzet) ?>">
        <meta name="description" content="<?= htmlspecialchars($ogOzet) ?>">
    <?php endif; ?>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- KaTeX (matematik formülleri için) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item:hover { background-color: rgba(79, 70, 229, 0.1); color: #4f46e5; }
        .sidebar-active { background-color: #4f46e5; color: white !important; box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.3); }
        .ql-toolbar.ql-snow {
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            position: sticky; top: 0;
            background: linear-gradient(to bottom, #ffffff, #fafbff);
            z-index: 40; padding: 1rem !important;
            box-shadow: 0 1px 3px rgba(99,102,241,0.05);
        }
        .ql-toolbar button:hover, .ql-toolbar .ql-picker-label:hover { color: #4f46e5 !important; }
        .ql-toolbar button.ql-active, .ql-toolbar .ql-picker-label.ql-active { color: #4f46e5 !important; background: rgba(99,102,241,0.1); border-radius: 4px; }
        .ql-container.ql-snow { border: none !important; font-size: 1.05rem; font-family: 'Inter', sans-serif; }
        .ql-editor { min-height: 550px; padding: 3rem !important; background: white; line-height: 1.7; }
        .ql-editor h1 { font-size: 2em; font-weight: 800; margin-bottom: 0.5em; color: #1e293b; }
        .ql-editor h2 { font-size: 1.5em; font-weight: 700; margin-bottom: 0.4em; color: #334155; }
        .ql-editor blockquote { border-left: 4px solid #6366f1; padding: 1rem 1.5rem; background: #eef2ff; border-radius: 0 12px 12px 0; margin: 1em 0; }
        .ql-editor pre.ql-syntax { background: #1e293b !important; color: #e2e8f0 !important; border-radius: 12px; padding: 1rem !important; }
        body.focus-mode aside, body.focus-mode nav { display: none !important; }
        body.focus-mode main { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
        body.focus-mode .editor-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; }
        .lang-bridge { padding-top: 12px; margin-top: -4px; }
        .note-card { transition: all 0.3s ease; border: 1px solid #eee !important; }
        .note-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; border-color: #c7d2fe !important; }
        .code-textarea { font-family: 'Courier New', Courier, monospace; tab-size: 4; }
        .code-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }

        /* Sayfa giriş animasyonu */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .editor-card { animation: fadeUp 0.5s ease-out; }

        /* Sadece okuma modu */
        body.readonly .ql-toolbar { display: none !important; }
        body.readonly #title, body.readonly select, body.readonly input { pointer-events: none; background: #f8fafc; }
        body.readonly .note-card { opacity: 0.85; }

        /* Sayfa ayırıcı */
        .sayfa-ayirici {
            border: none;
            border-top: 2px dashed #c7d2fe;
            margin: 3rem 0;
            position: relative;
            page-break-after: always;
        }
        .sayfa-ayirici::after {
            content: "📄 Yeni Sayfa";
            position: absolute;
            top: -12px; left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 4px 14px;
            font-size: 11px;
            font-weight: 700;
            color: #6366f1;
            border: 2px dashed #c7d2fe;
            border-radius: 999px;
        }

        /* Select görsel iyileştirmesi */
        select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M2 4l4 4 4-4' stroke='%23818cf8' stroke-width='2' fill='none'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.5rem center; padding-right: 1.5rem; }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 text-slate-800 transition-all duration-300 <?= $duzenleyebilir ? '' : 'readonly' ?>">

    <nav id="mainNav" class="bg-white border-b px-6 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="font-black text-xl text-indigo-600 tracking-tighter">notewarehouse</div>
            <div class="h-6 w-px bg-slate-200 mx-2"></div>
            <h1 id="navTitle" class="font-bold text-slate-500 text-sm italic uppercase tracking-wider">Editor Mode</h1>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="relative inline-block text-left group">
                <button class="flex items-center space-x-2 bg-slate-100 px-3 py-2 rounded-xl hover:bg-slate-200 transition border border-transparent">
                    <i class="fas fa-globe text-indigo-600 text-xs"></i>
                    <span id="currentLangDisplay" class="text-[10px] font-black uppercase">TR</span>
                </button>
                <div class="absolute right-0 top-full hidden group-hover:block z-[60] lang-bridge w-32">
                    <div class="bg-white rounded-xl shadow-2xl border border-slate-100 py-1 overflow-hidden">
                        <button onclick="changeLanguage('tr')" class="w-full text-left px-4 py-2 text-xs hover:bg-indigo-50 transition border-b border-slate-50 font-bold">🇹🇷 Türkçe</button>
                        <button onclick="changeLanguage('en')" class="w-full text-left px-4 py-2 text-xs hover:bg-indigo-50 transition border-b border-slate-50 font-bold">🇺🇸 English</button>
                    </div>
                </div>
            </div>

            <?php if ($noteId): ?>
                <button onclick="imleToggle()" id="imleBtn" title="Yer İmle / Sonra Oku" class="text-slate-500 hover:text-amber-500 transition p-2">
                    <i id="imleIkon" class="<?= $imlediMi ? 'fas text-amber-500' : 'far' ?> fa-bookmark text-lg"></i>
                </button>
            <?php endif; ?>

            <?php if ($noteId): ?>
                <a href="not_pdf.php?id=<?= $noteId ?>" target="_blank" title="Profesyonel PDF İndir (Kapaklı + Filigranlı)" class="bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white px-4 py-2 rounded-xl font-bold shadow-md transition active:scale-95 text-sm flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    <span class="hidden sm:inline">PDF</span>
                </a>
            <?php else: ?>
                <button onclick="downloadNote()" title="Hızlı PDF (kaydetmeden)" class="text-slate-500 hover:text-indigo-600 transition p-2"><i class="fas fa-file-download"></i></button>
            <?php endif; ?>

            <?php if ($noteId && $mevcutNot && ($mevcutNot['category'] === 'Soru Çözümü' || $mevcutNot['category'] === 'soru_cozumu')): ?>
                <a href="quiz_coz.php?id=<?= $noteId ?>" class="bg-amber-500 text-white px-5 py-2 rounded-xl font-bold hover:bg-amber-600 shadow-md transition active:scale-95 text-sm">
                    <i class="fas fa-play mr-2"></i> Testi Çöz
                </a>
            <?php endif; ?>

            <?php if ($duzenleyebilir): ?>
                <button onclick="saveToDatabase()" class="bg-emerald-500 text-white px-5 py-2 rounded-xl font-bold hover:bg-emerald-600 shadow-md transition active:scale-95 text-sm">
                    <i class="fas fa-save mr-2"></i> <span id="btnSaveText"><?= $noteId ? 'Güncelle' : 'Kaydet' ?></span>
                </button>
            <?php else: ?>
                <span class="bg-slate-100 text-slate-500 px-4 py-2 rounded-xl text-sm font-bold flex items-center">
                    <i class="fas fa-eye mr-2"></i> Görüntüleme Modu
                </span>
                <?php if ($mevcutNot['kullanici_email']): ?>
                    <span class="text-xs text-slate-400">
                        Yazar: <strong class="text-slate-600">@<?= htmlspecialchars($mevcutNot['author'] ?: 'Anonim') ?></strong>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
            
            <button onclick="openShareModal()" class="bg-indigo-600 text-white px-5 py-2 rounded-xl font-bold hover:bg-indigo-700 shadow-md transition active:scale-95 text-sm">
                <i class="fas fa-share-alt mr-2"></i> <span id="btnShareText">Paylaş</span>
            </button>
        </div>
    </nav>

    <div class="flex min-h-[calc(100vh-68px)]">
        <aside class="w-72 bg-white border-r border-slate-200 p-6 flex flex-col sticky top-[68px] h-[calc(100vh-68px)] overflow-y-auto">
            <div class="mb-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Organizasyon</p>
                <div class="space-y-2">
                    <a href="calisma_alani.php" class="sidebar-item w-full flex items-center px-4 py-3 rounded-2xl text-sm font-bold transition-all text-slate-500">
                        <i class="fas fa-folder-open mr-3"></i> kişisel çalışmalarım
                    </a>
                    <a href="gruplarim.php" class="sidebar-item w-full flex items-center px-4 py-3 rounded-2xl text-sm font-bold transition-all text-slate-500">
                        <i class="fas fa-users mr-3"></i> grup çalışmalarım
                    </a>
                    <a href="bildirimlerim.php" class="sidebar-item w-full flex items-center px-4 py-3 rounded-2xl text-sm font-bold transition-all text-slate-500">
                        <i class="fas fa-bell mr-3"></i> bildirimlerim
                    </a>
                    <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                    <a href="kullanicilar.php" class="sidebar-item w-full flex items-center px-4 py-3 rounded-2xl text-sm font-bold transition-all text-red-500">
                        <i class="fas fa-shield-alt mr-3"></i> kullanıcı yönetimi
                    </a>
                    <a href="bildirim_gonder.php" class="sidebar-item w-full flex items-center px-4 py-3 rounded-2xl text-sm font-bold transition-all text-red-500">
                        <i class="fas fa-paper-plane mr-3"></i> bildirim gönder
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-auto pt-6 border-t border-slate-100">
                <a href="ayarlar.php" class="sidebar-item w-full flex items-center px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-indigo-600 transition-all cursor-pointer">
                    <i class="fas fa-cog mr-3"></i> Ayarlar
                </a>
            </div>
        </aside>

        <main class="flex-1 p-8 lg:p-12 overflow-y-auto relative">
            <button onclick="toggleFocusMode()" class="fixed right-10 bottom-10 z-[45] bg-white text-slate-500 hover:text-indigo-600 w-12 h-12 rounded-full shadow-2xl border border-slate-100 transition-all flex items-center justify-center hover:scale-110">
                <i id="focusIcon" class="fas fa-expand-arrows-alt"></i>
            </button>

            <!-- AI ASİSTAN PANELİ (SAĞ) -->
            <div id="aiPanel" class="hidden xl:flex fixed right-6 top-24 w-72 max-h-[calc(100vh-7rem)] bg-white rounded-3xl shadow-2xl shadow-indigo-100 border border-slate-100 flex-col z-30">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-t-3xl text-white">
                    <h3 class="font-black text-sm flex items-center"><i class="fas fa-robot mr-2"></i> AI Asistan</h3>
                    <p class="text-[10px] text-indigo-100">Bu not için araçlar</p>
                </div>

                <div class="p-4 space-y-2">
                    <button onclick="aiAraci('ozet')" class="w-full bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-700 px-4 py-3 rounded-xl font-bold text-sm transition flex items-center group">
                        <div class="w-9 h-9 bg-emerald-500 group-hover:bg-white text-white group-hover:text-emerald-600 rounded-xl flex items-center justify-center mr-3 transition"><i class="fas fa-compress-alt"></i></div>
                        <div class="text-left flex-1">
                            <p class="font-black">Özetle</p>
                            <p class="text-[10px] font-normal opacity-70 group-hover:opacity-100">Anahtar noktaları çıkar</p>
                        </div>
                    </button>

                    <button onclick="aiAraci('anlat')" class="w-full bg-purple-50 hover:bg-purple-500 hover:text-white text-purple-700 px-4 py-3 rounded-xl font-bold text-sm transition flex items-center group">
                        <div class="w-9 h-9 bg-purple-500 group-hover:bg-white text-white group-hover:text-purple-600 rounded-xl flex items-center justify-center mr-3 transition"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div class="text-left flex-1">
                            <p class="font-black">Bana Anlat</p>
                            <p class="text-[10px] font-normal opacity-70 group-hover:opacity-100">Öğretmen gibi açıklasın</p>
                        </div>
                    </button>

                    <button id="ttsBtn" onclick="seslendir()" class="w-full bg-amber-50 hover:bg-amber-500 hover:text-white text-amber-700 px-4 py-3 rounded-xl font-bold text-sm transition flex items-center group">
                        <div class="w-9 h-9 bg-amber-500 group-hover:bg-white text-white group-hover:text-amber-600 rounded-xl flex items-center justify-center mr-3 transition"><i id="ttsIcon" class="fas fa-volume-up"></i></div>
                        <div class="text-left flex-1">
                            <p id="ttsLabel" class="font-black">Sesli Dinle</p>
                            <p class="text-[10px] font-normal opacity-70 group-hover:opacity-100">Tarayıcı sesi okuyacak</p>
                        </div>
                    </button>

                    <button onclick="aiAraci('soru')" class="w-full bg-rose-50 hover:bg-rose-500 hover:text-white text-rose-700 px-4 py-3 rounded-xl font-bold text-sm transition flex items-center group">
                        <div class="w-9 h-9 bg-rose-500 group-hover:bg-white text-white group-hover:text-rose-600 rounded-xl flex items-center justify-center mr-3 transition"><i class="fas fa-question-circle"></i></div>
                        <div class="text-left flex-1">
                            <p class="font-black">Soru Oluştur</p>
                            <p class="text-[10px] font-normal opacity-70 group-hover:opacity-100">AI'dan 5 soru üret</p>
                        </div>
                    </button>
                </div>

                <!-- Sonuç alanı -->
                <div id="aiSonucKutu" class="hidden mx-4 mb-4 bg-slate-50 border border-slate-200 rounded-2xl p-4 overflow-y-auto flex-1 min-h-[200px]">
                    <div class="flex items-center justify-between mb-2">
                        <span id="aiSonucBaslik" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Sonuç</span>
                        <button onclick="aiSonucKapat()" class="text-slate-400 hover:text-rose-500 text-xs"><i class="fas fa-times"></i></button>
                    </div>
                    <div id="aiSonucIcerik" class="text-sm text-slate-700 leading-relaxed prose prose-sm max-w-none"></div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex gap-2">
                        <button onclick="aiSonucKopyala()" class="bg-white border border-slate-200 hover:border-indigo-300 text-slate-600 px-3 py-1.5 rounded-lg text-[10px] font-bold flex-1"><i class="fas fa-copy mr-1"></i> Kopyala</button>
                        <button onclick="aiSonucEklEditore()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold flex-1"><i class="fas fa-plus mr-1"></i> Nota Ekle</button>
                    </div>
                </div>
            </div>

            <!-- AI panel toggle (mobil için) -->
            <button onclick="document.getElementById('aiPanel').classList.toggle('hidden')" class="xl:hidden fixed right-6 bottom-24 z-[44] bg-gradient-to-br from-indigo-500 to-purple-600 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center hover:scale-110 transition">
                <i class="fas fa-robot text-xl"></i>
            </button>

            <div class="max-w-5xl mx-auto xl:mr-80">
                <div class="editor-card bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/20 overflow-hidden border border-slate-100">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-8 bg-slate-50/50 border-b border-slate-100">
                        <div class="note-card p-4 rounded-2xl bg-white">
                            <label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Eğitim Seviyesi</label>
                            <select id="eduLevel" class="w-full bg-transparent font-bold text-sm outline-none appearance-none cursor-pointer">
                                <option value="Üniversite">Üniversite</option>
                                <option value="Lise">Lise</option>
                                <option value="Orta Okul">Orta Okul</option>
                                <option value="İlkokul">İlkokul</option>
                            </select>
                        </div>
                        
                        <div id="schoolContainer" class="note-card p-4 rounded-2xl bg-white"></div>
                        <div id="subjectContainer" class="note-card p-4 rounded-2xl bg-white"></div>

                        <div class="note-card p-4 rounded-2xl bg-white">
                            <label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Kategori</label>
                            <select id="noteCategory" class="w-full bg-transparent font-bold text-sm outline-none appearance-none cursor-pointer">
                                <option value="">Kategori Seçin</option>
                                <option value="Konu Anlatımı">Konu Anlatımı</option>
                                <option value="Soru Çözümü">Soru Çözümü</option>
                                <option value="Özet">Özet</option>
                                <option value="Kod">Kod (HTML/CSS/JS)</option> </select>
                        </div>
                    </div>

                    <?php if ($mevcutGrup): ?>
                    <!-- GRUP NOT BANNER -->
                    <div class="px-10 pt-6">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl mr-4">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-purple-100 uppercase tracking-widest">Grup Notu</p>
                                    <h3 class="font-black text-lg"><?= htmlspecialchars($mevcutGrup['grup_adi']) ?></h3>
                                </div>
                            </div>
                            <a href="gruplarim.php" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-xs font-bold transition">
                                <i class="fas fa-arrow-left mr-1"></i> Gruba Dön
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- DOSYA YÜKLEME ALANI (ÜSTTE) -->
                    <div class="px-10 pt-8">
                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100 rounded-2xl p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center min-w-0">
                                    <div class="w-12 h-12 bg-white text-indigo-500 rounded-xl flex items-center justify-center text-xl mr-4 shadow-sm shrink-0">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-700">Dosya Ekle <span class="text-[10px] font-normal text-slate-400 ml-1">opsiyonel</span></h4>
                                        <p class="text-xs text-slate-500 truncate" id="dosyaInfo">PDF · Word · Resim · PPTX · Max 20MB</p>
                                    </div>
                                </div>
                                <input type="file" id="dosyaInput" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.pptx,.xlsx" onchange="dosyaYukle(this)">
                                <button type="button" onclick="document.getElementById('dosyaInput').click()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-indigo-700 transition shadow-md whitespace-nowrap">
                                    <i class="fas fa-upload mr-2"></i> Yükle
                                </button>
                            </div>
                            <div id="dosyaPreview" class="hidden mt-4 p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between">
                                <a href="#" id="dosyaLink" target="_blank" class="text-indigo-600 font-bold text-sm flex items-center min-w-0">
                                    <i class="fas fa-file-pdf mr-2 text-rose-500 shrink-0"></i>
                                    <span id="dosyaAdi" class="truncate"></span>
                                </a>
                                <button type="button" onclick="dosyaSil()" class="text-rose-500 hover:text-rose-700 text-sm shrink-0 ml-3"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="px-10 pt-8">
                        <input type="text" id="title" placeholder="Not Başlığını Buraya Girin..."
                               class="w-full text-4xl font-black placeholder-slate-200 focus:outline-none border-b-2 border-transparent focus:border-indigo-200 pb-6 transition-all text-slate-800">
                    </div>

                    <div id="standardEditorContainer" class="block">
                        <div id="editor"></div>
                        <?php if ($duzenleyebilir): ?>
                        <div class="px-10 pb-6 -mt-2 flex items-center justify-between">
                            <button onclick="sayfaEkle()" type="button" class="bg-white border-2 border-dashed border-indigo-300 text-indigo-600 px-5 py-3 rounded-xl font-bold text-sm hover:bg-indigo-50 transition flex items-center">
                                <i class="fas fa-file-alt mr-2"></i> Yeni Sayfa Ekle
                            </button>
                            <span id="otomatikKaydet" class="text-xs text-slate-400 font-medium hidden">
                                <i class="fas fa-check-circle text-emerald-500 mr-1"></i> Otomatik kaydedildi
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div id="codeBuilderContainer" class="hidden px-10 pb-10 mt-6">
                        <div class="bg-slate-800 rounded-2xl p-6 shadow-inner">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-black text-white text-lg"><i class="fas fa-code text-indigo-400 mr-2"></i> Web Playground</h3>
                                <button onclick="runCode()" class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold transition shadow-md flex items-center">
                                    <i class="fas fa-play mr-2"></i> Çalıştır
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 mb-2"><i class="fab fa-html5 text-orange-500"></i> HTML</label>
                                    <textarea id="codeHtml" class="code-textarea w-full h-64 bg-slate-900 text-slate-100 p-4 rounded-xl border border-slate-700 resize-none text-sm" placeholder="<h1>Merhaba Dünya</h1>"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 mb-2"><i class="fab fa-css3-alt text-blue-500"></i> CSS</label>
                                    <textarea id="codeCss" class="code-textarea w-full h-64 bg-slate-900 text-slate-100 p-4 rounded-xl border border-slate-700 resize-none text-sm" placeholder="h1 { color: red; }"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 mb-2"><i class="fab fa-js-square text-yellow-400"></i> JavaScript</label>
                                    <textarea id="codeJs" class="code-textarea w-full h-64 bg-slate-900 text-slate-100 p-4 rounded-xl border border-slate-700 resize-none text-sm" placeholder="console.log('Çalıştı');"></textarea>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl border-4 border-slate-700 overflow-hidden h-96 relative">
                                <div class="absolute top-0 left-0 w-full bg-slate-100 border-b border-slate-200 px-4 py-2 flex items-center">
                                    <div class="flex space-x-2">
                                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                    </div>
                                    <span class="ml-4 text-xs font-bold text-slate-400 uppercase">Canlı Çıktı</span>
                                </div>
                                <iframe id="codePreview" class="w-full h-full bg-white mt-8"></iframe>
                            </div>
                        </div>
                    </div>

                    <div id="questionBuilderContainer" class="hidden px-10 pb-10 mt-6">
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 rounded-2xl p-6 mb-6 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-indigo-500 text-xl shadow-sm mr-4">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div>
                                    <h3 class="font-black text-indigo-800 text-lg">Soru Çözümü Modu Aktif</h3>
                                    <p class="text-sm text-indigo-600/80">Test sorularını manuel ekle veya AI'dan üret.</p>
                                </div>
                            </div>
                            <button onclick="openAIQuizModal()" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-5 py-3 rounded-xl font-bold text-sm shadow-md hover:shadow-lg transition active:scale-95">
                                <i class="fas fa-magic mr-2"></i> AI ile Soru Üret
                            </button>
                        </div>

                        <div id="questionsList" class="space-y-6 mb-6"></div>

                        <button onclick="addQuestion()" class="w-full bg-white border-2 border-dashed border-slate-300 text-slate-500 font-bold py-5 rounded-2xl hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all flex items-center justify-center group cursor-pointer">
                            <i class="fas fa-plus-circle mr-2 text-2xl group-hover:scale-110 transition-transform"></i> Yeni Soru Ekle
                        </button>
                    </div>

                    <!-- AI SORU ÜRETME MODAL -->
                    <div id="aiQuizModal" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
                        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-black text-slate-800">
                                    <i class="fas fa-magic text-purple-500 mr-2"></i> AI ile Soru Üret
                                </h3>
                                <button onclick="closeAIQuizModal()" class="text-slate-400 hover:text-rose-500"><i class="fas fa-times text-xl"></i></button>
                            </div>
                            <p class="text-sm text-slate-500 mb-5">Aşağıya konu metnini yapıştır, kaç soru üretileceğini seç. AI senin için çoktan seçmeli sorular hazırlasın.</p>
                            <textarea id="aiQuizText" rows="8" placeholder="Konu metnini buraya yapıştır..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm outline-none focus:border-purple-400 mb-4"></textarea>
                            <div class="flex items-center justify-between mb-5">
                                <label class="text-xs font-bold text-slate-500 uppercase">Soru Sayısı</label>
                                <input type="number" id="aiQuizCount" value="5" min="1" max="20" class="w-20 bg-slate-50 border border-slate-200 rounded-lg p-2 text-center font-bold outline-none focus:border-purple-400">
                            </div>
                            <button id="aiQuizBtn" onclick="aiSoruUret()" class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white py-4 rounded-xl font-black uppercase tracking-widest hover:shadow-xl">
                                <i class="fas fa-bolt mr-2"></i> Sorulari Üret
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($noteId && $mevcutNot): ?>

                    <!-- ETİKETLER -->
                    <div class="mt-8 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">
                            <i class="fas fa-hashtag text-indigo-500 mr-1"></i> Etiketler
                        </h3>
                        <div class="flex flex-wrap gap-2 mb-3" id="etiketListesi">
                            <?php foreach ($mevcutEtiketler as $e): ?>
                                <a href="dersler.php?etiket=<?= urlencode($e) ?>" class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-bold hover:bg-indigo-600 hover:text-white transition">#<?= htmlspecialchars($e) ?></a>
                            <?php endforeach; ?>
                            <?php if (empty($mevcutEtiketler) && !$duzenleyebilir): ?>
                                <span class="text-slate-400 text-xs italic">Etiket eklenmemiş.</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($duzenleyebilir): ?>
                            <div class="flex gap-2">
                                <input type="text" id="etiketInput" placeholder="örn: trigonometri, ayt2026..." class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-indigo-400 flex-1">
                                <button onclick="etiketEkle()" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700">Ekle</button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2">Virgülle birden fazla ekleyebilirsin: <code>trigonometri, ayt, matematik</code></p>
                        <?php endif; ?>
                    </div>

                    <!-- YAZAR + TAKİP ET -->
                    <?php if ($mevcutNot['kullanici_email'] && $mevcutNot['kullanici_email'] !== $kullaniciEmail): ?>
                        <div class="mt-6 bg-gradient-to-br from-slate-50 to-indigo-50 rounded-3xl border border-slate-100 p-6 flex items-center justify-between">
                            <div class="flex items-center">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($mevcutNot['author']) ?>&background=4f46e5&color=fff&size=128" class="w-14 h-14 rounded-full mr-4 border-2 border-white shadow-md">
                                <div>
                                    <p class="font-black text-slate-800">@<?= htmlspecialchars($mevcutNot['author']) ?></p>
                                    <p class="text-xs text-slate-500"><?= $yazarTakipciSayisi ?> takipçi</p>
                                </div>
                            </div>
                            <?php if ($kullaniciEmail): ?>
                                <button onclick="takipToggle('<?= htmlspecialchars($mevcutNot['kullanici_email']) ?>')" id="takipBtn" class="<?= $takipEdiyorum ? 'bg-slate-200 text-slate-700' : 'bg-indigo-600 text-white hover:bg-indigo-700' ?> px-5 py-2.5 rounded-xl font-bold text-sm transition">
                                    <i class="fas <?= $takipEdiyorum ? 'fa-user-check' : 'fa-user-plus' ?> mr-2"></i>
                                    <span id="takipText"><?= $takipEdiyorum ? 'Takipten Çık' : 'Takip Et' ?></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- YORUMLAR -->
                    <div class="mt-6 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center justify-between">
                            <span><i class="fas fa-comments text-indigo-500 mr-2"></i> Yorumlar (<?= count($yorumlar) ?>)</span>
                        </h3>

                        <?php if ($kullaniciEmail): ?>
                            <div class="bg-slate-50 rounded-2xl p-4 mb-5">
                                <textarea id="yorumInput" rows="2" placeholder="Düşüncelerini paylaş..." class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-indigo-400 mb-2"></textarea>
                                <button onclick="yorumGonder()" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700">
                                    <i class="fas fa-paper-plane mr-1"></i> Yorum Yap
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="bg-slate-50 rounded-xl p-4 text-center text-sm text-slate-500 mb-5">
                                Yorum yapmak için <a href="giris.php" class="text-indigo-600 font-bold">giriş yap</a>
                            </div>
                        <?php endif; ?>

                        <div id="yorumListesi" class="space-y-4">
                            <?php if (empty($yorumlar)): ?>
                                <p class="text-center text-slate-400 text-sm py-8">İlk yorumu sen yap!</p>
                            <?php else: foreach ($yorumlar as $y): ?>
                                <div class="bg-slate-50 rounded-2xl p-4 group" id="yorum-<?= $y['id'] ?>">
                                    <div class="flex items-start gap-3">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($y['kullanici_ad']) ?>&background=4f46e5&color=fff" class="w-10 h-10 rounded-full">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="font-bold text-sm text-slate-800">@<?= htmlspecialchars($y['kullanici_ad']) ?></p>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] text-slate-400"><?= date('d M H:i', strtotime($y['tarih'])) ?></span>
                                                    <?php if ($y['kullanici_email'] === $kullaniciEmail || $kullaniciRol === 'admin'): ?>
                                                        <button onclick="yorumSil(<?= $y['id'] ?>)" class="text-rose-400 hover:text-rose-600 text-xs opacity-0 group-hover:opacity-100 transition"><i class="fas fa-trash"></i></button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <p class="text-sm text-slate-700 whitespace-pre-line"><?= htmlspecialchars($y['mesaj']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="shareModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-800 tracking-tight">Notu Paylaş</h3>
                <button onclick="closeShareModal()" class="text-slate-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-8 text-center">
                <button onclick="shareOnWhatsApp()" class="group">
                    <div class="w-14 h-14 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-green-500 group-hover:text-white mx-auto transition-all"><i class="fab fa-whatsapp"></i></div>
                    <span class="text-[9px] mt-2 block font-black text-slate-400">WHATSAPP</span>
                </button>
                <button onclick="shareOnX()" class="group">
                    <div class="w-14 h-14 bg-slate-50 text-slate-900 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-black group-hover:text-white mx-auto transition-all"><i class="fab fa-x-twitter"></i></div>
                    <span class="text-[9px] mt-2 block font-black text-slate-400">X</span>
                </button>
                <button onclick="copyLink()" class="group">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-indigo-500 group-hover:text-white mx-auto transition-all"><i class="fas fa-link"></i></div>
                    <span class="text-[9px] mt-2 block font-black text-slate-400">KOPYALA</span>
                </button>
            </div>
            <input type="text" id="shareLink" readonly class="w-full bg-slate-50 border rounded-xl py-3 px-4 text-[10px] text-slate-500 outline-none font-mono">
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <script>
        const translations = {
            tr: { navTitle: "EDİTÖR MODU", btnSave: "Kaydet", phTitle: "Not Başlığını Buraya Girin..." },
            en: { navTitle: "EDITOR MODE", btnSave: "Save", phTitle: "Enter Note Title Here..." }
        };

        let egitimData = null;
        let questionCount = 0;

        const SADECE_OKUMA = <?= $duzenleyebilir ? 'false' : 'true' ?>;
        var quill = new Quill('#editor', {
            theme: 'snow',
            readOnly: SADECE_OKUMA,
            placeholder: SADECE_OKUMA ? '' : 'Notlarını buraya yaz... (zengin metin, formül, kod, resim, video destekler)',
            modules: {
                toolbar: [
                    [{ 'font': [] }, { 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'align': [] }, { 'direction': 'rtl' }],
                    ['blockquote', 'code-block'],
                    ['link', 'image', 'video', 'formula'],
                    ['clean']
                ]
            }
        });

        async function fetchEgitimData() {
            try {
                const res = await fetch('egitim_verileri.json');
                if (!res.ok) throw new Error();
                egitimData = await res.json();
            } catch (e) {
                console.error("egitim_verileri.json yüklenemedi:", e);
                // Yedek minimum veri
                egitimData = {
                    ilkokul:    { okullar: ["İlkokul"], dersler: ["Matematik","Türkçe","Hayat Bilgisi"] },
                    ortaokul:   { okullar: ["Ortaokul"], dersler: ["Matematik","Fen Bilimleri","Türkçe"] },
                    lise:       { okullar: ["Anadolu Lisesi","Fen Lisesi"], dersler: ["Matematik","Fizik","Türkçe","Biyoloji","Kimya"] },
                    universite: { bolumler: ["Bilgisayar Mühendisliği","Tıp","Hukuk"], dersler_ornekleri: ["Matematik","Fizik"] }
                };
            }
            updateFieldsByEduLevel();
        }

        function buildSelect(id, options, placeholder) {
            const opts = options.map(o => `<option value="${o}">${o}</option>`).join('');
            return `<select id="${id}" class="w-full bg-transparent font-bold text-sm outline-none cursor-pointer">
                        <option value="">${placeholder}</option>
                        ${opts}
                    </select>`;
        }

        function updateFieldsByEduLevel() {
            const level = document.getElementById('eduLevel').value;
            const sCont = document.getElementById('schoolContainer');
            const bCont = document.getElementById('subjectContainer');
            if (!egitimData) return;

            let okulList = [], dersList = [], okulLabel = "Okul Türü", dersLabel = "Ders";

            if (level === 'Üniversite') {
                okulList = (egitimData.universite.bolumler || []).slice().sort();
                dersList = (egitimData.universite.dersler_ornekleri || []).slice().sort();
                okulLabel = "Bölüm";
                dersLabel = "Ders";
            } else if (level === 'Lise') {
                okulList = (egitimData.lise.okullar || []).slice().sort();
                dersList = (egitimData.lise.dersler || []).slice().sort();
                okulLabel = "Lise Türü";
            } else if (level === 'Orta Okul' || level === 'Ortaokul') {
                okulList = (egitimData.ortaokul.okullar || []).slice().sort();
                dersList = (egitimData.ortaokul.dersler || []).slice().sort();
                okulLabel = "Okul Türü";
            } else if (level === 'İlkokul' || level === 'Ilkokul') {
                okulList = (egitimData.ilkokul.okullar || []).slice().sort();
                dersList = (egitimData.ilkokul.dersler || []).slice().sort();
            }

            sCont.innerHTML = `<label class="text-[9px] font-black text-slate-400 uppercase block mb-1">${okulLabel}</label>` +
                              buildSelect('schoolName', okulList, okulLabel + ' seçin');
            bCont.innerHTML = `<label class="text-[9px] font-black text-slate-400 uppercase block mb-1">${dersLabel}</label>` +
                              buildSelect('subjectName', dersList, dersLabel + ' seçin');
        }

        function downloadNote() {
            <?php if ($noteId): ?>
            // Mevcut not açıksa: Profesyonel sunucu tabanlı PDF (kapak + filigran + sayfa numaraları)
            window.open('not_pdf.php?id=<?= $noteId ?>', '_blank');
            <?php else: ?>
            // Yeni not (henüz kaydedilmemiş): Basit istemci tabanlı PDF
            const title = document.getElementById('title').value || 'Not';
            const content = quill.root.innerHTML;
            const element = document.createElement('div');
            element.innerHTML = `<div style="padding:40px;font-family:sans-serif;"><h1 style="color:#4f46e5;">${title}</h1><hr>${content}<p style="margin-top:30px;color:#94a3b8;font-size:11px;">notewarehouse.com — Profesyonel PDF için notu önce kaydet.</p></div>`;
            html2pdf().from(element).save(`${title}.pdf`);
            alert('Notu kaydettikten sonra profesyonel kapaklı PDF indirebilirsin!');
            <?php endif; ?>
        }
        function shareOnWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const titleText = document.getElementById('title').value || "notewarehouse'daki bu nota göz at!";
            const text = encodeURIComponent(titleText + " - ");
            window.open(`https://api.whatsapp.com/send?text=${text}${url}`, '_blank');
        }

        function shareOnX() {
            const url = encodeURIComponent(window.location.href);
            const titleText = encodeURIComponent(document.getElementById('title').value || "Harika bir not buldum!");
            window.open(`https://twitter.com/intent/tweet?text=${titleText}&url=${url}`, '_blank');
        }

        
        function runCode() {
            const html = document.getElementById('codeHtml').value;
            const css = `<style>${document.getElementById('codeCss').value}</style>`;
            const js = `<script>${document.getElementById('codeJs').value}<\/script>`;
            
            const previewFrame = document.getElementById('codePreview');
            const preview = previewFrame.contentWindow.document;
            
            preview.open();
            preview.write(html + css + js);
            preview.close();
        }

        
        async function saveToDatabase(sessiz = false) {
            // GIRIS KONTROLU — Anonim kullanici not paylasamaz
            <?php if (empty($_SESSION['user_email'])): ?>
            if (confirm("Not paylaşmak için giriş yapmalısın. Şimdi giriş sayfasına gidelim mi?")) {
                window.location.href = 'giris.php';
            }
            return;
            <?php endif; ?>

            const btn = document.getElementById('btnSaveText');
            const original = btn ? btn.innerText : 'Kaydet';
            const category = document.getElementById('noteCategory').value;
            
            let questionsArray = [];
            let finalContent = quill.root.innerHTML;

            if (category === 'Soru Çözümü' || category === 'soru_cozumu') {
                const questionCards = document.getElementById('questionsList').children;
                for (let card of questionCards) {
                    const textareas = card.querySelectorAll('textarea');
                    const inputs = card.querySelectorAll('input[type="text"]');
                    const selects = card.querySelectorAll('select');

                    if(textareas[0].value.trim() !== '') {
                        questionsArray.push({
                            soru_metni: textareas[0].value,
                            secenek_a: inputs[0].value,
                            secenek_b: inputs[1].value,
                            secenek_c: inputs[2].value,
                            secenek_d: inputs[3].value,
                            dogru_cevap: selects[0].value
                        });
                    }
                }
            } 
            else if (category === 'Kod') {
                
                finalContent = JSON.stringify({
                    html: document.getElementById('codeHtml').value,
                    css: document.getElementById('codeCss').value,
                    js: document.getElementById('codeJs').value
                });
            }

            const payload = {
                id: "<?php echo $noteId; ?>",
                title: document.getElementById('title').value,
                content: finalContent,
                eduLevel: document.getElementById('eduLevel').value,
                schoolName: document.getElementById('schoolName').value,
                subjectName: document.getElementById('subjectName').value,
                category: category,
                sorular: questionsArray,
                dosya_yolu: yuklenenDosya,
                grup_id: <?= $grupId ?: ($mevcutNot['grup_id'] ?? 'null') ?>
            };

            if(!payload.title) {
                if (!sessiz) alert("Başlık girin!");
                return;
            }

            if (btn && !sessiz) btn.innerText = "Kaydediliyor...";
            try {
                const res = await fetch('islem.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (result.success) {
                    if (!sessiz) alert("Başarıyla Kaydedildi!");
                    // Yeni notsa URL'i güncelle ki sonraki kaydetmeler UPDATE olsun
                    if (!payload.id && result.inserted_id) {
                        history.replaceState({}, '', 'notlar.php?id=' + result.inserted_id);
                    }
                } else {
                    if (!sessiz) alert("Hata: " + result.error);
                }
            } catch (e) { if (!sessiz) alert("Sunucu hatası!"); }
            if (btn && !sessiz) btn.innerText = original;
        }

        function addQuestion(mevcutVeri = null) {
            questionCount++;
            const qList = document.getElementById('questionsList');
            const qId = 'question_' + Date.now() + Math.floor(Math.random() * 1000);

            const sMetin = mevcutVeri ? mevcutVeri.soru_metni : '';
            const sA = mevcutVeri ? mevcutVeri.secenek_a : '';
            const sB = mevcutVeri ? mevcutVeri.secenek_b : '';
            const sC = mevcutVeri ? mevcutVeri.secenek_c : '';
            const sD = mevcutVeri ? mevcutVeri.secenek_d : '';
            const dogruCevap = mevcutVeri ? mevcutVeri.dogru_cevap : 'A';

            const questionHTML = `
                <div id="${qId}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative group transition-all hover:shadow-md">
                    <button onclick="removeQuestion('${qId}')" title="Soruyu Sil" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-400 hover:bg-red-500 hover:text-white transition-colors cursor-pointer">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                    <div class="mb-5 pr-8">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Soru ${questionCount}</label>
                        <textarea class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm focus:outline-none focus:border-indigo-400 focus:bg-white transition-all resize-none shadow-inner" rows="3" placeholder="Sorunuzu buraya yazın...">${sMetin}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 focus-within:border-indigo-400 focus-within:bg-white transition-all focus-within:shadow-sm">
                            <span class="text-indigo-500 font-black mr-3">A)</span><input type="text" value="${sA}" class="w-full bg-transparent py-3 text-sm focus:outline-none" placeholder="A şıkkı">
                        </div>
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 focus-within:border-indigo-400 focus-within:bg-white transition-all focus-within:shadow-sm">
                            <span class="text-indigo-500 font-black mr-3">B)</span><input type="text" value="${sB}" class="w-full bg-transparent py-3 text-sm focus:outline-none" placeholder="B şıkkı">
                        </div>
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 focus-within:border-indigo-400 focus-within:bg-white transition-all focus-within:shadow-sm">
                            <span class="text-indigo-500 font-black mr-3">C)</span><input type="text" value="${sC}" class="w-full bg-transparent py-3 text-sm focus:outline-none" placeholder="C şıkkı">
                        </div>
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 focus-within:border-indigo-400 focus-within:bg-white transition-all focus-within:shadow-sm">
                            <span class="text-indigo-500 font-black mr-3">D)</span><input type="text" value="${sD}" class="w-full bg-transparent py-3 text-sm focus:outline-none" placeholder="D şıkkı">
                        </div>
                    </div>
                    <div class="flex items-center bg-indigo-50/50 p-3 rounded-xl border border-indigo-100 w-max">
                        <label class="text-[10px] font-black text-indigo-400 uppercase tracking-wider mr-3">Doğru Cevap:</label>
                        <select class="bg-white border border-indigo-200 rounded-lg px-4 py-1 text-sm font-bold text-indigo-600 focus:outline-none focus:border-indigo-500 cursor-pointer shadow-sm">
                            <option value="A" ${dogruCevap === 'A' ? 'selected' : ''}>A Şıkkı</option>
                            <option value="B" ${dogruCevap === 'B' ? 'selected' : ''}>B Şıkkı</option>
                            <option value="C" ${dogruCevap === 'C' ? 'selected' : ''}>C Şıkkı</option>
                            <option value="D" ${dogruCevap === 'D' ? 'selected' : ''}>D Şıkkı</option>
                        </select>
                    </div>
                </div>
            `;
            qList.insertAdjacentHTML('beforeend', questionHTML);
        }

        function removeQuestion(questionId) {
            const questionElement = document.getElementById(questionId);
            if (questionElement) {
                questionElement.remove();
            }
        }

        // ---------- BOOKMARK ----------
        async function imleToggle() {
            <?php if (!$kullaniciEmail): ?>
            alert("Giriş yapmalısın!"); window.location='giris.php'; return;
            <?php endif; ?>
            const r = await fetch('islem.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ islem: 'imle_toggle', note_id: <?= $noteId ?: 0 ?> })
            });
            const data = await r.json();
            if (data.success) {
                const ikon = document.getElementById('imleIkon');
                if (data.durum === 'eklendi') {
                    ikon.className = 'fas text-amber-500 fa-bookmark text-lg';
                } else {
                    ikon.className = 'far fa-bookmark text-lg';
                }
            }
        }

        // ---------- TAKİP ----------
        async function takipToggle(hedef) {
            const r = await fetch('islem.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ islem: 'takip_toggle', hedef_email: hedef })
            });
            const data = await r.json();
            if (data.success) {
                const btn = document.getElementById('takipBtn');
                const txt = document.getElementById('takipText');
                if (data.durum === 'takip') {
                    btn.className = 'bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-sm transition';
                    txt.innerText = 'Takipten Çık';
                    btn.querySelector('i').className = 'fas fa-user-check mr-2';
                } else {
                    btn.className = 'bg-indigo-600 text-white hover:bg-indigo-700 px-5 py-2.5 rounded-xl font-bold text-sm transition';
                    txt.innerText = 'Takip Et';
                    btn.querySelector('i').className = 'fas fa-user-plus mr-2';
                }
            } else {
                alert(data.error || 'Hata');
            }
        }

        // ---------- ETİKET ----------
        async function etiketEkle() {
            const inp = document.getElementById('etiketInput');
            const yeniler = inp.value.split(',').map(s => s.toLowerCase().replace(/#/g, '').trim()).filter(s => s.length > 0);
            if (yeniler.length === 0) return;

            // Mevcut etiketleri al
            const liste = document.getElementById('etiketListesi');
            const mevcut = Array.from(liste.querySelectorAll('a')).map(a => a.innerText.replace(/^#/, ''));
            const birlesik = [...new Set([...mevcut, ...yeniler])];

            const r = await fetch('islem.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ islem: 'etiket_guncelle', note_id: <?= $noteId ?: 0 ?>, etiketler: birlesik })
            });
            const data = await r.json();
            if (data.success) {
                inp.value = '';
                location.reload();
            } else {
                alert(data.error || 'Hata');
            }
        }

        // ---------- YORUM ----------
        async function yorumGonder() {
            const inp = document.getElementById('yorumInput');
            const mesaj = inp.value.trim();
            if (mesaj.length < 2) { alert("Çok kısa!"); return; }
            const r = await fetch('islem.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ islem: 'yorum_ekle', note_id: <?= $noteId ?: 0 ?>, mesaj })
            });
            const data = await r.json();
            if (data.success) {
                inp.value = '';
                location.reload();
            } else {
                alert(data.error || 'Hata');
            }
        }
        async function yorumSil(id) {
            if (!confirm("Yorumu sil?")) return;
            const r = await fetch('islem.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ islem: 'yorum_sil', id })
            });
            const data = await r.json();
            if (data.success) document.getElementById('yorum-'+id).remove();
        }

        // ---------- AI ASİSTAN (Özetle / Anlat / Soru / Sesli) ----------
        let aktifSesleme = null;

        function notIcerigiAl() {
            const baslik = document.getElementById('title').value.trim();
            const govde = quill.getText().trim();
            return (baslik ? `Başlık: ${baslik}\n\n` : '') + govde;
        }

        async function aiAraci(mod) {
            const icerik = notIcerigiAl();
            if (icerik.length < 30) {
                alert("Önce yeterli içerik yaz! (en az 30 karakter)");
                return;
            }

            // Soru üretmeyse mevcut modalı aç
            if (mod === 'soru') {
                document.getElementById('aiQuizText').value = quill.getText();
                document.getElementById('aiQuizModal').classList.remove('hidden');
                // Kategoriyi otomatik Soru Çözümü yap
                const cat = document.getElementById('noteCategory');
                if (cat && cat.value !== 'Soru Çözümü') {
                    cat.value = 'Soru Çözümü';
                    cat.dispatchEvent(new Event('change'));
                }
                return;
            }

            // Özet / Anlat
            const kutu = document.getElementById('aiSonucKutu');
            const icerikKutu = document.getElementById('aiSonucIcerik');
            const baslikSpan = document.getElementById('aiSonucBaslik');
            kutu.classList.remove('hidden');
            baslikSpan.innerText = mod === 'anlat' ? '🎓 ÖĞRETMEN AÇIKLAMASI' : '✨ ÖZET';
            icerikKutu.innerHTML = '<div class="text-slate-400 text-center py-6"><i class="fas fa-spinner fa-spin text-2xl"></i><br><span class="text-xs mt-2 block">AI düşünüyor...</span></div>';

            try {
                const r = await fetch('islem.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ islem:'ai_ozet', icerik, mod })
                });
                const data = await r.json();
                if (data.success) {
                    // HTML olarak göster
                    icerikKutu.innerHTML = data.sonuc.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                } else {
                    icerikKutu.innerHTML = '<p class="text-rose-600 font-bold">❌ Hata: ' + (data.error || 'bilinmiyor') + '</p>';
                }
            } catch (e) {
                icerikKutu.innerHTML = '<p class="text-rose-600">Sunucu hatası: ' + e.message + '</p>';
            }
        }

        function aiSonucKapat() {
            document.getElementById('aiSonucKutu').classList.add('hidden');
            document.getElementById('aiSonucIcerik').innerHTML = '';
        }

        function aiSonucKopyala() {
            const el = document.getElementById('aiSonucIcerik');
            const text = el.innerText;
            navigator.clipboard.writeText(text);
            alert("📋 Kopyalandı!");
        }

        function aiSonucEklEditore() {
            <?php if ($duzenleyebilir): ?>
            const el = document.getElementById('aiSonucIcerik');
            const html = '<hr><h3>✨ AI ' + document.getElementById('aiSonucBaslik').innerText + '</h3>' + el.innerHTML + '<hr>';
            const len = quill.getLength();
            quill.clipboard.dangerouslyPasteHTML(len, html);
            alert("✅ Nota eklendi!");
            <?php else: ?>
            alert("Görüntüleme modunda eklenemez.");
            <?php endif; ?>
        }

        function seslendir() {
            const btn = document.getElementById('ttsBtn');
            const icon = document.getElementById('ttsIcon');
            const label = document.getElementById('ttsLabel');

            // Eğer şu an konuşuyorsa, durdur
            if (window.speechSynthesis.speaking) {
                window.speechSynthesis.cancel();
                icon.className = 'fas fa-volume-up';
                label.innerText = 'Sesli Dinle';
                aktifSesleme = null;
                return;
            }

            const metin = notIcerigiAl();
            if (metin.length < 5) { alert("Önce içerik yaz!"); return; }

            const utter = new SpeechSynthesisUtterance(metin);
            utter.lang = 'tr-TR';
            utter.rate = 1.0;
            utter.pitch = 1.0;

            // Türkçe ses bulmaya çalış
            const sesler = window.speechSynthesis.getVoices();
            const tr = sesler.find(s => s.lang.startsWith('tr'));
            if (tr) utter.voice = tr;

            utter.onstart = () => {
                icon.className = 'fas fa-stop';
                label.innerText = 'Durdur';
            };
            utter.onend = () => {
                icon.className = 'fas fa-volume-up';
                label.innerText = 'Sesli Dinle';
                aktifSesleme = null;
            };
            utter.onerror = () => {
                icon.className = 'fas fa-volume-up';
                label.innerText = 'Sesli Dinle';
            };

            aktifSesleme = utter;
            window.speechSynthesis.speak(utter);
        }

        // Sayfa kapanırken sesi durdur
        window.addEventListener('beforeunload', () => {
            if (window.speechSynthesis.speaking) window.speechSynthesis.cancel();
        });

        // ---------- SAYFA EKLE (page break) ----------
        function sayfaEkle() {
            const range = quill.getSelection(true);
            // Önce yeni satır, sonra HR (page break), sonra yeni satır
            quill.insertText(range.index, '\n', Quill.sources.USER);
            quill.clipboard.dangerouslyPasteHTML(range.index + 1, '<hr class="sayfa-ayirici">');
            quill.setSelection(range.index + 2, 0, Quill.sources.SILENT);
        }

        // ---------- OTOMATİK KAYDET ----------
        <?php if ($duzenleyebilir): ?>
        let autoSaveTimer = null;
        let lastSavedContent = '';
        const autoSaveIndicator = document.getElementById('otomatikKaydet');

        function triggerAutoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(async () => {
                const t = document.getElementById('title').value.trim();
                if (!t) return; // Başlık yoksa kaydetme
                const content = quill.root.innerHTML;
                if (content === lastSavedContent) return;
                try {
                    await saveToDatabase(true);
                    lastSavedContent = content;
                    if (autoSaveIndicator) {
                        autoSaveIndicator.classList.remove('hidden');
                        setTimeout(() => autoSaveIndicator.classList.add('hidden'), 2500);
                    }
                } catch (e) {}
            }, 5000); // 5 saniye sonra otomatik kaydet
        }
        quill.on('text-change', triggerAutoSave);
        ['title','eduLevel','noteCategory'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', triggerAutoSave);
        });
        <?php endif; ?>

        // ---------- DOSYA YÜKLEME ----------
        let yuklenenDosya = null;
        async function dosyaYukle(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            if (file.size > 20*1024*1024) { alert("Dosya 20MB'tan büyük!"); return; }

            document.getElementById('dosyaInfo').innerText = "⏳ Yükleniyor: " + file.name;
            const formData = new FormData();
            formData.append('file', file);
            const noteId = "<?php echo $noteId ?? ''; ?>";
            if (noteId) formData.append('note_id', noteId);

            try {
                const res = await fetch('dosya_yukle.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    yuklenenDosya = data.dosya_yolu;
                    document.getElementById('dosyaPreview').classList.remove('hidden');
                    document.getElementById('dosyaAdi').innerText = data.orjinal_ad;
                    document.getElementById('dosyaLink').href = data.dosya_yolu;
                    document.getElementById('dosyaInfo').innerText = "✅ Yüklendi (" + Math.round(data.boyut/1024) + " KB)";
                } else {
                    alert("Yükleme hatası: " + data.error);
                    document.getElementById('dosyaInfo').innerText = "PDF, Word, Resim · Max 20MB";
                }
            } catch (e) {
                alert("Sunucu hatası: " + e);
            }
        }
        function dosyaSil() {
            yuklenenDosya = null;
            document.getElementById('dosyaPreview').classList.add('hidden');
            document.getElementById('dosyaInput').value = '';
            document.getElementById('dosyaInfo').innerText = "PDF, Word, Resim · Max 20MB";
        }

        // ---------- AI SORU ÜRETME ----------
        function openAIQuizModal() {
            document.getElementById('aiQuizText').value = quill.getText() || '';
            document.getElementById('aiQuizModal').classList.remove('hidden');
        }
        function closeAIQuizModal() {
            document.getElementById('aiQuizModal').classList.add('hidden');
        }
        async function aiSoruUret() {
            const icerik = document.getElementById('aiQuizText').value.trim();
            const adet = parseInt(document.getElementById('aiQuizCount').value) || 5;
            if (icerik.length < 50) { alert("En az 50 karakterlik metin gerekli!"); return; }

            const btn = document.getElementById('aiQuizBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> AI Düşünüyor... (15-30sn)';
            btn.disabled = true;

            try {
                const r = await fetch('islem.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ islem:'ai_soru_uret', icerik, adet })
                });
                const data = await r.json();
                if (data.success && Array.isArray(data.sorular)) {
                    data.sorular.forEach(s => addQuestion(s));
                    closeAIQuizModal();
                    alert("✨ " + data.sorular.length + " soru üretildi!");
                } else {
                    alert("Hata: " + (data.error || "AI başarısız"));
                }
            } catch (e) {
                alert("İstek hatası: " + e);
            }
            btn.innerHTML = '<i class="fas fa-bolt mr-2"></i> Soruları Üret';
            btn.disabled = false;
        }

       
        document.getElementById('noteCategory').addEventListener('change', function(e) {
            const val = e.target.value;
            const standardEditor = document.getElementById('standardEditorContainer');
            const questionBuilder = document.getElementById('questionBuilderContainer');
            const codeBuilder = document.getElementById('codeBuilderContainer');
            const quillToolbar = document.querySelector('.ql-toolbar');

      
            standardEditor.classList.add('hidden');
            questionBuilder.classList.add('hidden');
            codeBuilder.classList.add('hidden');
            if(quillToolbar) quillToolbar.classList.add('hidden');

           
            if (val === 'Soru Çözümü' || val === 'soru_cozumu') {
                questionBuilder.classList.remove('hidden');
            } else if (val === 'Kod') {
                codeBuilder.classList.remove('hidden');
            } else {
                standardEditor.classList.remove('hidden');
                if(quillToolbar) quillToolbar.classList.remove('hidden');
            }
        });

        function changeLanguage(lang) {
            const t = translations[lang];
            document.getElementById('navTitle').innerText = t.navTitle;
            document.getElementById('btnSaveText').innerText = t.btnSave;
            document.getElementById('title').placeholder = t.phTitle;
            document.getElementById('currentLangDisplay').innerText = lang.toUpperCase();
            localStorage.setItem('prefLang', lang);
        }

        function toggleFocusMode() { document.body.classList.toggle('focus-mode'); }
        function openShareModal() { 
            document.getElementById('shareModal').classList.remove('hidden'); 
            document.getElementById('shareLink').value = window.location.href;
        }
        function closeShareModal() { document.getElementById('shareModal').classList.add('hidden'); }
        function copyLink() { navigator.clipboard.writeText(window.location.href); alert("Kopyalandı!"); }

        document.getElementById('eduLevel').addEventListener('change', updateFieldsByEduLevel);
        
        window.onload = async () => {
            const savedLang = localStorage.getItem('prefLang') || 'tr';
            changeLanguage(savedLang);
            await fetchEgitimData();

            const mevcutNot = <?php echo $mevcutNot ? json_encode($mevcutNot) : 'null'; ?>;
            const mevcutSorularDB = <?php echo isset($mevcutSorular) ? json_encode($mevcutSorular) : '[]'; ?>;

            if (mevcutNot) {
                document.getElementById('title').value = mevcutNot.title;
                
                const cat = document.getElementById('noteCategory');
                if(cat) {
                    cat.value = mevcutNot.category;
                    cat.dispatchEvent(new Event('change'));
                }

                const edu = document.getElementById('eduLevel');
                if(edu) {
                    edu.value = mevcutNot.edu_level;
                    updateFieldsByEduLevel(); 
                    
                    setTimeout(() => {
                        const schoolInput = document.getElementById('schoolName');
                        const subjectInput = document.getElementById('subjectName');
                        if(schoolInput) schoolInput.value = mevcutNot.school_name;
                        if(subjectInput) subjectInput.value = mevcutNot.subject;
                    }, 200);
                }

                
                if (mevcutNot.category === 'Kod') {
                    try {
                        const codeData = JSON.parse(mevcutNot.content);
                        const cH = document.getElementById('codeHtml');
                        const cC = document.getElementById('codeCss');
                        const cJ = document.getElementById('codeJs');
                        if (cH) cH.value = codeData.html || '';
                        if (cC) cC.value = codeData.css || '';
                        if (cJ) cJ.value = codeData.js || '';
                        if (typeof runCode === 'function') runCode();
                    } catch (e) {
                        console.error("Kod verisi parse edilemedi");
                    }
                }
                else if (mevcutNot.content) {
                    // Readonly modunda da çalışan güvenli yöntem
                    try {
                        quill.root.innerHTML = mevcutNot.content;
                    } catch (e) {
                        try { quill.clipboard.dangerouslyPasteHTML(mevcutNot.content); } catch (e2) {}
                    }
                }
                
                if ((mevcutNot.category === 'Soru Çözümü' || mevcutNot.category === 'soru_cozumu') && mevcutSorularDB.length > 0) {
                    mevcutSorularDB.forEach(soru => {
                        addQuestion(soru);
                    });
                }

                // Eklenmiş dosya varsa önizlemede göster
                if (mevcutNot.dosya_yolu) {
                    yuklenenDosya = mevcutNot.dosya_yolu;
                    const ad = mevcutNot.dosya_yolu.split('/').pop();
                    document.getElementById('dosyaPreview').classList.remove('hidden');
                    document.getElementById('dosyaAdi').innerText = ad;
                    document.getElementById('dosyaLink').href = mevcutNot.dosya_yolu;
                    document.getElementById('dosyaInfo').innerText = "📎 Dosya bağlı";
                }

                const saveBtn = document.getElementById('btnSaveText');
                if (saveBtn) saveBtn.innerText = "Güncelle";
            }
        };
    </script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>