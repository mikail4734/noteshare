<?php
$host    = "localhost";
$db_name = "notdeposu";
$user    = "root";
$pass    = "";

try {
    // PDO ile bağlantı kuruyoruz (Önceki kodlarınla uyumlu olan budur)
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $pass);
    
    // Hata raporlamayı aktif ediyoruz (try-catch'in çalışması için şart)
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}
?>