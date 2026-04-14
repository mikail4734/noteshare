<?php

// Veritabanı bağlantısını dahil ediyoruz
include 'baglanti.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Formdan veri gelip gelmediğini kontrol ediyoruz
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Formdaki 'name' değerlerine göre verileri alıyoruz
    $baslik = $_POST['baslik'];
    $icerik = $_POST['icerik'];
    $paylasan = "@mikail"; // Şimdilik sabit, istersen değiştirebilirsin
    $tarih = date("d M Y"); // Güncel tarihi formatlı alıyoruz

    try {
        // Veritabanına kayıt sorgusu (stoksayımprojesi veritabanı için)
        $sorgu = $db->prepare("INSERT INTO notlar (baslik, icerik, paylasan, tarih) VALUES (?, ?, ?, ?)");
        $kaydet = $sorgu->execute([$baslik, $icerik, $paylasan, $tarih]);

        if ($kaydet) {
            // Başarılıysa dersler sayfasına yönlendir
            header("Location: dersler.html");
            exit();
        } else {
            echo "Kayıt sırasında bir hata oluştu.";
        }
    } catch (PDOException $e) {
        // Hata varsa ekrana yazdır (Tablo yoksa veya sütun isimleri yanlışsa burası uyarır)
        echo "Veritabanı Hatası: " . $e->getMessage();
    }
} else {
    // Form dışı erişimi engelle
    echo "Lütfen formu kullanarak veri gönderin.";
}
?>