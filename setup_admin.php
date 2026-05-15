<?php
/**
 * ADMIN KURULUM
 * Bu dosyayı sadece BİR KEZ çalıştır:
 *   http://localhost/norwarhouse.php/setup_admin.php
 *
 * mikailcelik4734@gmail.com hesabını admin'e yükseltir.
 * Hesap önce kaydol.php'den oluşturulmuş olmalı.
 */

require_once 'baglan.php';

$admin_email = 'mikailcelik4734@gmail.com';

echo "<style>body{font-family:Arial;max-width:600px;margin:50px auto;padding:30px;background:#f5f5f5;}
.box{background:#fff;padding:30px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);}
.ok{color:#059669;font-weight:bold;}.err{color:#dc2626;font-weight:bold;}
.btn{display:inline-block;margin-top:20px;background:#4f46e5;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;}
</style><div class='box'>";

echo "<h1>🛡️ Admin Kurulumu</h1>";

try {
    // Kullanıcı var mı?
    $kontrol = $db->prepare("SELECT id, ad, rol FROM users WHERE email = ?");
    $kontrol->execute([$admin_email]);
    $kullanici = $kontrol->fetch(PDO::FETCH_ASSOC);

    if (!$kullanici) {
        echo "<p class='err'>❌ <b>$admin_email</b> hesabı bulunamadı.</p>";
        echo "<p>Önce <a href='kaydol.php'>kaydol.php</a> sayfasından bu e-posta ile hesap aç, sonra bu sayfayı tekrar yenile.</p>";
        echo "<a href='kaydol.php' class='btn'>Kaydol Sayfasına Git</a>";
    } elseif ($kullanici['rol'] === 'admin') {
        echo "<p class='ok'>✅ <b>{$kullanici['ad']}</b> zaten admin yetkisine sahip.</p>";
        echo "<p>Bu dosyayı silebilirsin (güvenlik için):</p>";
        echo "<code>C:\\xampp\\htdocs\\norwarhouse.php\\setup_admin.php</code>";
        echo "<br><a href='index.php' class='btn'>Anasayfaya Dön</a>";
    } else {
        // Admin yap
        $guncelle = $db->prepare("UPDATE users SET rol = 'admin', durum = 1 WHERE email = ?");
        $guncelle->execute([$admin_email]);
        echo "<p class='ok'>✅ Başarılı! <b>{$kullanici['ad']}</b> ({$admin_email}) artık ADMIN.</p>";
        echo "<p>Şimdi:</p><ol>";
        echo "<li>Çıkış yap → tekrar giriş yap (oturum bilgisi yenilensin)</li>";
        echo "<li>Bu dosyayı sil: <code>setup_admin.php</code></li>";
        echo "</ol>";
        echo "<a href='cikis.php' class='btn'>Çıkış Yap ve Tekrar Giriş Yap</a>";
    }
} catch (PDOException $e) {
    echo "<p class='err'>❌ Veritabanı hatası: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?>
