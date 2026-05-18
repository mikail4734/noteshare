<?php
/**
 * Dosya yükleme endpoint'i (PDF / Resim / Word)
 * FormData ile POST: file=<dosya>, note_id=<opsiyonel>
 */
require_once __DIR__ . '/oturum_baslat.php';
require_once 'baglan.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_email'])) {
    echo json_encode(['success'=>false, 'error'=>'Giriş yapmalısınız!']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false, 'error'=>'Dosya yüklenemedi.']);
    exit;
}

$file = $_FILES['file'];
$izinli = ['pdf','doc','docx','jpg','jpeg','png','txt','pptx','xlsx'];
$uzanti = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($uzanti, $izinli)) {
    echo json_encode(['success'=>false, 'error'=>'İzin verilmeyen dosya türü: '.$uzanti]);
    exit;
}

if ($file['size'] > 20 * 1024 * 1024) { // 20 MB
    echo json_encode(['success'=>false, 'error'=>'Dosya 20MB\'tan büyük olamaz.']);
    exit;
}

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

$guvenliAd = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
$yeniAd = uniqid('not_', true) . '_' . $guvenliAd;
$hedef  = $uploadDir . $yeniAd;

if (!move_uploaded_file($file['tmp_name'], $hedef)) {
    echo json_encode(['success'=>false, 'error'=>'Dosya kaydedilemedi.']);
    exit;
}

$webPath = 'uploads/' . $yeniAd;

// Eğer note_id verilmişse direkt notes tablosuna yaz
if (!empty($_POST['note_id'])) {
    try {
        $g = $db->prepare("UPDATE notes SET dosya_yolu = ? WHERE id = ?");
        $g->execute([$webPath, intval($_POST['note_id'])]);
    } catch (PDOException $e) {}
}

echo json_encode([
    'success' => true,
    'dosya_yolu' => $webPath,
    'orjinal_ad' => $file['name'],
    'boyut' => $file['size']
]);
?>
