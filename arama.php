<?php
session_start();
require_once 'baglan.php';

$q = trim($_GET['q'] ?? '');
$sonuclar = [];
$kullanicilar = [];

if (mb_strlen($q) >= 2) {
    // Notlarda ara
    $like = "%$q%";
    $s = $db->prepare("
        SELECT id, title, content, category, edu_level, school_name, subject, author, likes, dislikes, created_at
        FROM notes
        WHERE title LIKE ? OR content LIKE ? OR subject LIKE ? OR school_name LIKE ? OR author LIKE ?
        ORDER BY likes DESC, created_at DESC
        LIMIT 50
    ");
    $s->execute([$like, $like, $like, $like, $like]);
    $sonuclar = $s->fetchAll(PDO::FETCH_ASSOC);

    // Kullanıcılarda ara (sadece admin için)
    if (($_SESSION['rol'] ?? '') === 'admin') {
        $u = $db->prepare("SELECT id, ad, email, rol, durum FROM users WHERE ad LIKE ? OR email LIKE ? LIMIT 20");
        $u->execute([$like, $like]);
        $kullanicilar = $u->fetchAll(PDO::FETCH_ASSOC);
    }
}

function vurgula($metin, $kelime) {
    if (empty($kelime) || empty($metin)) return htmlspecialchars($metin);
    $e = htmlspecialchars($metin);
    return preg_replace('/('.preg_quote($kelime,'/').')/iu', '<mark class="bg-yellow-200 text-slate-900 rounded px-0.5">$1</mark>', $e);
}

function ozet($html, $uzunluk = 150) {
    $temiz = trim(strip_tags($html));
    return mb_strlen($temiz) > $uzunluk ? mb_substr($temiz, 0, $uzunluk).'...' : $temiz;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Arama: <?= htmlspecialchars($q) ?> | NoteShare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;background:#F8FAFC}</style>
</head>
<body class="text-slate-800 min-h-screen">

<nav class="bg-[#4f46e5] px-8 py-4 shadow-lg flex items-center justify-between sticky top-0 z-50">
    <a href="index.php" class="text-white font-black text-xl tracking-tighter flex items-center">
        <i class="fas fa-book-open mr-2"></i> NoteShare
    </a>
    <form action="arama.php" method="GET" class="flex-1 max-w-xl mx-8 relative">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" autofocus placeholder="Notlarda ara..."
               class="w-full pl-12 pr-4 py-2.5 rounded-full bg-white/10 border border-white/20 text-white placeholder-white/60 outline-none focus:ring-2 focus:ring-white/50 text-sm">
        <i class="fas fa-search absolute left-4 top-3.5 text-white/50"></i>
    </form>
    <a href="index.php" class="text-white/80 hover:text-white text-sm font-medium">← Ana Sayfa</a>
</nav>

<main class="container mx-auto px-6 py-10 max-w-5xl">

    <?php if (mb_strlen($q) < 2): ?>
        <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100">
            <div class="text-5xl text-slate-300 mb-4"><i class="fas fa-search"></i></div>
            <h2 class="text-2xl font-bold text-slate-700 mb-2">Ne arıyorsun?</h2>
            <p class="text-slate-400">En az 2 karakter yazmalısın.</p>
        </div>
    <?php else: ?>
        <h2 class="text-2xl font-bold mb-6">
            "<span class="text-indigo-600"><?= htmlspecialchars($q) ?></span>" için sonuçlar
            <span class="text-sm font-medium text-slate-400 ml-2"><?= count($sonuclar) ?> not bulundu</span>
        </h2>

        <?php if (!empty($kullanicilar)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
                <h3 class="font-bold text-sm text-slate-500 uppercase tracking-wider mb-3"><i class="fas fa-users mr-2 text-rose-500"></i> Kullanıcılar (Admin görünümü)</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($kullanicilar as $u): ?>
                        <span class="inline-flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs">
                            <strong class="mr-2"><?= htmlspecialchars($u['ad']) ?></strong>
                            <span class="text-slate-400"><?= htmlspecialchars($u['email']) ?></span>
                            <?php if ($u['rol'] === 'admin'): ?>
                                <span class="ml-2 bg-red-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded">ADMIN</span>
                            <?php endif; ?>
                            <?php if ($u['durum'] == 0): ?>
                                <span class="ml-2 bg-rose-100 text-rose-700 text-[8px] font-black px-1.5 py-0.5 rounded">BANLI</span>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($sonuclar)): ?>
            <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100">
                <div class="text-5xl text-slate-300 mb-3">🤔</div>
                <h3 class="text-xl font-bold text-slate-700">Hiç not bulunamadı</h3>
                <p class="text-slate-400 text-sm mt-2">Farklı anahtar kelime dene veya yazımı kontrol et.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($sonuclar as $n): ?>
                    <a href="notlar.php?id=<?= $n['id'] ?>" class="block bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex justify-between items-start mb-2 gap-4">
                            <h3 class="font-bold text-lg text-slate-800 leading-tight">
                                <?= vurgula($n['title'], $q) ?>
                            </h3>
                            <span class="text-[10px] bg-indigo-50 text-indigo-600 font-black px-2 py-1 rounded-md uppercase whitespace-nowrap"><?= htmlspecialchars($n['category']) ?></span>
                        </div>
                        <p class="text-sm text-slate-500 mb-3"><?= vurgula(ozet($n['content'], 180), $q) ?></p>
                        <div class="flex items-center text-xs text-slate-400 font-medium gap-4">
                            <span><i class="fas fa-user mr-1"></i> <?= vurgula($n['author'] ?: 'Anonim', $q) ?></span>
                            <span><i class="fas fa-graduation-cap mr-1"></i> <?= htmlspecialchars($n['edu_level']) ?></span>
                            <span><i class="fas fa-book mr-1"></i> <?= vurgula($n['subject'] ?: '-', $q) ?></span>
                            <span class="ml-auto"><i class="fas fa-thumbs-up mr-1 text-emerald-500"></i> <?= $n['likes'] ?></span>
                            <span class="text-slate-300"><?= date('d M', strtotime($n['created_at'])) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>
