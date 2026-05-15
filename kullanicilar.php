<?php
session_start();
require_once 'baglan.php';


if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

try {
   
    $sorgu = $db->prepare("SELECT id, ad, email, rol, durum FROM users ORDER BY rol = 'admin' DESC, id DESC");
    $sorgu->execute();
    $kullanicilar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası!");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi | NoteShare Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }</style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-slate-400 hover:text-red-500 transition"><i class="fas fa-arrow-left text-lg"></i></a>
            <h1 class="font-extrabold text-2xl tracking-tight flex items-center text-slate-900">
                <span class="bg-red-50 text-red-500 p-2.5 rounded-xl mr-3 shadow-sm border border-red-100">
                    <i class="fas fa-shield-alt"></i>
                </span>
                Kullanıcı Yönetimi
            </h1>
        </div>
        <div>
            <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg uppercase tracking-widest">Admin Yetkisi Aktif</span>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-10 max-w-5xl">
        <div class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-slate-200">
            <div class="grid grid-cols-5 bg-slate-50/50 px-6 py-5 border-b border-slate-200">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider col-span-2">Kullanıcı / E-posta</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Yetki</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Durum</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">İşlem</div>
            </div>

            <div class="divide-y divide-slate-100">
                <?php foreach ($kullanicilar as $user): ?>
                    <div class="grid grid-cols-5 items-center px-6 py-4 hover:bg-slate-50 transition-all">
                        
                        <div class="col-span-2 flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white <?php echo $user['rol'] == 'admin' ? 'bg-red-500' : 'bg-indigo-500'; ?>">
                                <?php echo strtoupper(substr($user['ad'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-[14px]"><?php echo htmlspecialchars($user['ad']); ?></h4>
                                <p class="text-[11px] text-slate-400 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>

                        <div class="text-center">
                            <?php if($user['rol'] == 'admin'): ?>
                                <span class="bg-red-50 text-red-600 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider border border-red-100">Yönetici</span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider border border-slate-200">Üye</span>
                            <?php endif; ?>
                        </div>

                        <div class="text-center">
                            <?php if($user['durum'] == 1): ?>
                                <span class="text-emerald-500 font-bold text-xs"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                            <?php else: ?>
                                <span class="text-red-500 font-bold text-xs"><i class="fas fa-ban mr-1"></i> Engelli</span>
                            <?php endif; ?>
                        </div>

                        <div class="text-right flex justify-end">
                            <?php if($user['rol'] != 'admin'):  ?>
                                <?php if($user['durum'] == 1): ?>
                                    <button onclick="kullaniciDurum(<?php echo $user['id']; ?>, 0)" class="text-[10px] font-extrabold text-white bg-red-500 px-4 py-2 rounded-lg hover:bg-red-600 transition-all uppercase shadow-sm flex items-center">
                                        <i class="fas fa-lock mr-2"></i> Engelle
                                    </button>
                                <?php else: ?>
                                    <button onclick="kullaniciDurum(<?php echo $user['id']; ?>, 1)" class="text-[10px] font-extrabold text-white bg-emerald-500 px-4 py-2 rounded-lg hover:bg-emerald-600 transition-all uppercase shadow-sm flex items-center">
                                        <i class="fas fa-unlock mr-2"></i> Kilidi Aç
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-[10px] font-bold text-slate-300 uppercase">İşlem Yapılamaz</span>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<script>
    
const currentUserRole = '<?php echo $_SESSION['rol']; ?>';

async function kullaniciDurum(userId, yeniDurum) {
    const mesaj = yeniDurum === 0
        ? "⚠️ UYARI: Bu kullanıcı banlanacak!\n\n• Tüm notları silinecek\n• Beğenileri silinecek\n• Grup üyelikleri silinecek\n• Bildirimleri silinecek\n• Siteye giriş yapamayacak\n\nDevam edilsin mi?"
        : "Bu kullanıcının banını kaldırmak istiyor musunuz?";

    if (confirm(mesaj)) {
        try {
            const response = await fetch('islem.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ islem: 'kullanici_durum', user_id: userId, durum: yeniDurum })
            });
            const result = await response.json();

            if (result.success) {
                if (yeniDurum === 0 && result.silinen_not > 0) {
                    alert(`Kullanıcı banlandı. ${result.silinen_not} notu silindi.`);
                }
                location.reload();
            } else {
                alert("Hata: " + result.error);
            }
        } catch (error) {
            alert("Sunucuya ulaşılamadı.");
        }
    }
}
</script>
</body>
</html>