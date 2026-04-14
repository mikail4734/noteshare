<?php
session_start(); // Mevcut oturuma eriş
session_unset(); // Tüm oturum değişkenlerini temizle
session_destroy(); // Oturumu tamamen yok et

// Ana sayfaya geri gönder
header("Location: index.php");
exit();
?>