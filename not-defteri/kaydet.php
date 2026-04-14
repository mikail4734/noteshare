<?php
// 1. Veritabanına bağlan
$baglanti = mysqli_connect("localhost", "root", "", "not_deposu");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Formdan gelen verileri yakala
    $baslik = $_POST['baslik'];
    $ders = $_POST['ders'];

    // 3. Veritabanına ekle
    $sorgu = "INSERT INTO notlar (baslik, ders_adi) VALUES ('$baslik', '$ders')";
    
    if (mysqli_query($baglanti, $sorgu)) {
        // 4. İşlem bitince seni tekrar tabloya (matematik.html) geri göndersin
        header("Location: dersler.php"); 
    } else {
        echo "Hata oluştu: " . mysqli_error($baglanti);
    }
}
?>