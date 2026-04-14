<?php
$host = 'localhost';
$db_adi = 'notdeposu'; 
$username = "root";    // Tanımlanan isim
$password = "";        // Tanımlanan şifre

try {
    // Burada yukarıdaki değişken isimlerini ($username ve $password) kullanmalısın
    $db = new PDO("mysql:host=$host;dbname=$db_adi;charset=utf8", $username, $password);
    
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Bağlantı başarılı!"; // Test etmek için bunu açabilirsin
} catch (PDOException $e) {
    echo "Veritabanı bağlantı hatası: " . $e->getMessage();
    die();
}
?>