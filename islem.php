<?php
// Oturumu en başta başlatıyoruz
session_start();
require_once 'baglan.php';

// Gelen JSON veya POST verisini al
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data && !empty($_POST)) {
    $data = $_POST;
}

// 1. İŞLEM: NOT SİLME (Sadece Admin)
if (isset($data['islem']) && $data['islem'] === 'not_sil') {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
        $sil_sorgu = $db->prepare("DELETE FROM notes WHERE id = ?");
        $sil_sorgu->execute([$data['note_id']]);
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Yetkisiz işlem!"]);
    }
    exit;
}

if ($data && isset($db)) {
    try {
        // --- 2. İŞLEM: KULLANICI ENGELLEME / KİLİT AÇMA (DÜZELTİLDİ) ---
        if (isset($data['islem']) && $data['islem'] === 'kullanici_durum') {
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Bu işlem için yetkiniz yok!']);
                exit;
            }

            $user_id = intval($data['user_id']);
            $yeni_durum = intval($data['durum']); // 0 (engelle) veya 1 (kaldır)

            $db->beginTransaction(); // İşlemi güvenli başlat

            // Kullanıcı durumunu güncelle
            $guncelle = $db->prepare("UPDATE users SET durum = ? WHERE id = ?");
            $guncelle->execute([$yeni_durum, $user_id]);

            // Eğer engelleniyorsa beğenilerini de sil
            if ($yeni_durum === 0) {
                $userSorgu = $db->prepare("SELECT email FROM users WHERE id = ?");
                $userSorgu->execute([$user_id]);
                $userEmail = $userSorgu->fetchColumn();

                if ($userEmail) {
                    $silBegeniler = $db->prepare("DELETE FROM begeniler WHERE kullanici_email = ?");
                    $silBegeniler->execute([$userEmail]);
                }
            }

            $db->commit();
            echo json_encode(['success' => true]);
            exit;
        }

        // --- 3. İŞLEM: BEĞENİ / REAKSİYON SİSTEMİ ---
        if (isset($data['islem']) && $data['islem'] === 'reaksiyon') {
            if (!isset($_SESSION['user_email'])) {
                echo json_encode(['success' => false, 'error' => 'Giriş yapmalısınız!']);
                exit;
            }

            $noteId = $data['note_id'];
            $tip = $data['tip']; 
            $kullanici_email = $_SESSION['user_email'];

            if ($tip === 'like') {
                $kontrol = $db->prepare("SELECT id FROM begeniler WHERE kullanici_email = ? AND note_id = ?");
                $kontrol->execute([$kullanici_email, $noteId]);
                
                if ($kontrol->rowCount() > 0) {
                    echo json_encode(['success' => false, 'error' => 'Zaten beğendiniz.']);
                    exit;
                }

                $ekle = $db->prepare("INSERT INTO begeniler (kullanici_email, note_id) VALUES (?, ?)");
                $ekle->execute([$kullanici_email, $noteId]);
                
                $guncelle = $db->prepare("UPDATE notes SET likes = likes + 1 WHERE id = ?");
                $guncelle->execute([$noteId]);

                $sayiSorgu = $db->prepare("SELECT likes FROM notes WHERE id = ?");
                $sayiSorgu->execute([$noteId]);
                echo json_encode(['success' => true, 'new_count' => $sayiSorgu->fetchColumn()]);
                exit;

            } elseif ($tip === 'dislike') {
                $guncelle = $db->prepare("UPDATE notes SET dislikes = dislikes + 1 WHERE id = ?");
                $guncelle->execute([$noteId]);
                
                $sayiSorgu = $db->prepare("SELECT dislikes FROM notes WHERE id = ?");
                $sayiSorgu->execute([$noteId]);
                echo json_encode(['success' => true, 'new_count' => $sayiSorgu->fetchColumn()]);
                exit;
            }
        }

        // --- 4. İŞLEM: YENİ NOT KAYDETME ---
        if (isset($data['title']) && !isset($data['islem'])) {
            $sorgu = $db->prepare("INSERT INTO notes (title, content, category, edu_level, school_name, subject, author) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $title = $data['title'] ?? 'Başlıksız';
            $content = $data['content'] ?? '';
            $category = $data['category'] ?? 'Genel';
            $edu_level = $data['eduLevel'] ?? '';
            $school_name = $data['schoolName'] ?? '';
            $subject = $data['subjectName'] ?? '';
            $author = $_SESSION['user_name'] ?? 'Anonim'; 

            $sonuc = $sorgu->execute([$title, $content, $category, $edu_level, $school_name, $subject, $author]);

            echo json_encode(['success' => $sonuc, 'inserted_id' => $db->lastInsertId()]);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Tanımsız işlem.']);

    } catch (PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Veri ulaşmadı.']);
}
?>