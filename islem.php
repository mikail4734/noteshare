<?php

session_start();
require_once 'baglan.php';
require_once 'helpers.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data && !empty($_POST)) {
    $data = $_POST;
}

// Not Silme İşlemi (Admin tümünü, kullanıcı sadece kendisininkini silebilir)
if (isset($data['islem']) && $data['islem'] === 'not_sil') {
    if (!isset($_SESSION['user_email'])) {
        echo json_encode(["success" => false, "error" => "Giriş yapmalısınız!"]);
        exit;
    }
    $note_id = intval($data['note_id'] ?? 0);
    $rol = $_SESSION['rol'] ?? 'user';
    $email = $_SESSION['user_email'];

    if ($rol === 'admin') {
        $sil = $db->prepare("DELETE FROM notes WHERE id = ?");
        $sil->execute([$note_id]);
        echo json_encode(["success" => true]);
    } else {
        // Sadece kendi notunu silebilir
        $sil = $db->prepare("DELETE FROM notes WHERE id = ? AND kullanici_email = ?");
        $sil->execute([$note_id, $email]);
        if ($sil->rowCount() > 0) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Bu notu silme yetkin yok!"]);
        }
    }
    exit;
}

if ($data && isset($db)) {
    try {
        // Kullanıcı Durum Güncelleme (BAN/UNBAN)
        if (isset($data['islem']) && $data['islem'] === 'kullanici_durum') {
            if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Bu işlem için yetkiniz yok!']);
                exit;
            }

            $user_id = intval($data['user_id']);
            $yeni_durum = intval($data['durum']);

            $db->beginTransaction();
            $guncelle = $db->prepare("UPDATE users SET durum = ? WHERE id = ?");
            $guncelle->execute([$yeni_durum, $user_id]);

            $silinenNotSayisi = 0;
            if ($yeni_durum === 0) {
                // BAN: Kullanıcının tüm verisini temizle
                $userSorgu = $db->prepare("SELECT email FROM users WHERE id = ?");
                $userSorgu->execute([$user_id]);
                $userEmail = $userSorgu->fetchColumn();

                if ($userEmail) {
                    // Beğenileri sil
                    $db->prepare("DELETE FROM begeniler WHERE kullanici_email = ?")->execute([$userEmail]);
                    // Notlarını ve onlara bağlı soruları sil (CASCADE FK ile not_sorulari otomatik)
                    $silNotlar = $db->prepare("DELETE FROM notes WHERE kullanici_email = ?");
                    $silNotlar->execute([$userEmail]);
                    $silinenNotSayisi = $silNotlar->rowCount();
                    // Grup üyeliklerini sil
                    $db->prepare("DELETE FROM grup_uyeleri WHERE kullanici_email = ?")->execute([$userEmail]);
                    // Bildirimleri sil
                    $db->prepare("DELETE FROM bildirimler WHERE kullanici_email = ?")->execute([$userEmail]);
                }
            }

            $db->commit();
            echo json_encode(['success' => true, 'silinen_not' => $silinenNotSayisi]);
            exit;
        }

        // Beğeni/Reaksiyon İşlemi
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

                // Beğenen kişiye 2 XP, not sahibine 3 XP
                xpVer($db, $kullanici_email, 2);
                $sahipSorgu = $db->prepare("SELECT kullanici_email FROM notes WHERE id = ?");
                $sahipSorgu->execute([$noteId]);
                $sahipEmail = $sahipSorgu->fetchColumn();
                if ($sahipEmail && $sahipEmail !== $kullanici_email) {
                    xpVer($db, $sahipEmail, 3);
                    rozetKontrol($db, $sahipEmail);
                }

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

        // Not Kaydetme/Güncelleme İşlemi
        if (isset($data['title']) && !isset($data['islem'])) {
            $db->beginTransaction(); 
            try {
                $title = $data['title'] ?? 'Başlıksız';
                $content = $data['content'] ?? '';
                $category = $data['category'] ?? 'Genel';
                $edu_level = $data['eduLevel'] ?? '';
                $school_name = $data['schoolName'] ?? '';
                $subject = $data['subjectName'] ?? '';
                $dosya_yolu = $data['dosya_yolu'] ?? null;
                $grup_id = !empty($data['grup_id']) ? intval($data['grup_id']) : null;
                $author = $_SESSION['user_name'] ?? 'Anonim';
                $kullanici_email = $_SESSION['user_email'] ?? null;

                // Grup not'u ise kullanıcının üye olduğunu doğrula
                if ($grup_id) {
                    $uyeMi = $db->prepare("SELECT 1 FROM grup_uyeleri WHERE grup_id = ? AND kullanici_email = ?");
                    $uyeMi->execute([$grup_id, $kullanici_email]);
                    if (!$uyeMi->rowCount()) {
                        throw new Exception("Bu grubun üyesi değilsin.");
                    }
                }

                $note_id = !empty($data['id']) ? intval($data['id']) : null;

                $yeniMiNot = empty($note_id);
                if ($note_id) {
                    // Mevcut notu çek
                    $kontrol = $db->prepare("SELECT kullanici_email, grup_id FROM notes WHERE id = ?");
                    $kontrol->execute([$note_id]);
                    $mevcut = $kontrol->fetch(PDO::FETCH_ASSOC);

                    if (($_SESSION['rol'] ?? 'user') !== 'admin') {
                        $sahibi = $mevcut['kullanici_email'] === $kullanici_email;
                        $grupUyesi = false;
                        if ($mevcut['grup_id']) {
                            $u = $db->prepare("SELECT 1 FROM grup_uyeleri WHERE grup_id = ? AND kullanici_email = ?");
                            $u->execute([$mevcut['grup_id'], $kullanici_email]);
                            $grupUyesi = $u->rowCount() > 0;
                        }
                        if (!$sahibi && !$grupUyesi) {
                            throw new Exception("Bu notu düzenleme yetkin yok.");
                        }
                    }
                    if ($dosya_yolu) {
                        $sorgu = $db->prepare("UPDATE notes SET title=?, content=?, category=?, edu_level=?, school_name=?, subject=?, dosya_yolu=? WHERE id=?");
                        $sorgu->execute([$title, $content, $category, $edu_level, $school_name, $subject, $dosya_yolu, $note_id]);
                    } else {
                        $sorgu = $db->prepare("UPDATE notes SET title=?, content=?, category=?, edu_level=?, school_name=?, subject=? WHERE id=?");
                        $sorgu->execute([$title, $content, $category, $edu_level, $school_name, $subject, $note_id]);
                    }
                    $silSorgu = $db->prepare("DELETE FROM not_sorulari WHERE note_id = ?");
                    $silSorgu->execute([$note_id]);
                } else {
                    $sorgu = $db->prepare("INSERT INTO notes (title, content, category, edu_level, school_name, subject, author, kullanici_email, dosya_yolu, grup_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $sorgu->execute([$title, $content, $category, $edu_level, $school_name, $subject, $author, $kullanici_email, $dosya_yolu, $grup_id]);
                    $note_id = $db->lastInsertId();
                }

                if (isset($data['sorular']) && is_array($data['sorular']) && count($data['sorular']) > 0) {
                    $soruSorgu = $db->prepare("INSERT INTO not_sorulari (note_id, soru_metni, secenek_a, secenek_b, secenek_c, secenek_d, dogru_cevap) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    foreach ($data['sorular'] as $soru) {
                        $soruSorgu->execute([
                            $note_id, 
                            htmlspecialchars($soru['soru_metni']),
                            htmlspecialchars($soru['secenek_a']),
                            htmlspecialchars($soru['secenek_b']),
                            htmlspecialchars($soru['secenek_c']),
                            htmlspecialchars($soru['secenek_d']),
                            $soru['dogru_cevap']
                        ]);
                    }
                }
                $db->commit();
                // Yeni not paylaştıysa 50 XP, güncellemese 5 XP
                if ($kullanici_email) {
                    xpVer($db, $kullanici_email, $yeniMiNot ? 50 : 5);
                    rozetKontrol($db, $kullanici_email);
                }
                echo json_encode(['success' => true, 'inserted_id' => $note_id]);
            } catch (Exception $e) {
                $db->rollBack(); 
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }

        // --- CLAUDE API (DERSBOTU) KISMI ---
        if (isset($data['islem']) && $data['islem'] === 'dersbotu') {
            $gelenMesaj = trim($data['mesaj'] ?? '');
            $sessionId = trim($data['session_id'] ?? '');

            if (empty($gelenMesaj) || empty($sessionId)) {
                echo json_encode(['success' => false, 'error' => 'Mesaj veya oturum bilgisi eksik.']);
                exit;
            }

            // BURAYI KENDİ ANAHTARINLA DOLDUR
            $anthropic_api_key = $config['ANTHROPIC_API_KEY'] ?? '';

            $url = 'https://api.anthropic.com/v1/messages';
            $postData = [
                "model" => "claude-3-haiku-20240307", // En stabil model ismi
                "max_tokens" => 1024,
                "system" => "Sen DersBotu adında, üniversite öğrencilerine yardım eden uzman bir eğitmensin.",
                "messages" => [
                    ["role" => "user", "content" => $gelenMesaj]
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // XAMPP SSL Hatasını önlemek için
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-api-key: ' . $anthropic_api_key,
                'anthropic-version: 2023-06-01'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                echo json_encode(['success' => false, 'error' => 'CURL Hatası: ' . $err]);
                exit;
            }

            $responseData = json_decode($response, true);

            if ($httpCode === 200 && isset($responseData['content'][0]['text'])) {
                $botCevabi = $responseData['content'][0]['text'];
                
                if (isset($_SESSION['user_email'])) {
                    $kaydet = $db->prepare("INSERT INTO sohbet_gecmisi (kullanici_email, session_id, kullanici_mesaji, bot_cevabi) VALUES (?, ?, ?, ?)");
                    $kaydet->execute([$_SESSION['user_email'], $sessionId, $gelenMesaj, $botCevabi]);
                }
                echo json_encode(['success' => true, 'cevap' => $botCevabi]);
            } else {
                $apiError = $responseData['error']['message'] ?? 'Claude hata döndürdü veya bakiye henüz aktifleşmedi.';
                echo json_encode(['success' => false, 'error' => "API Hatası ($httpCode): " . $apiError]);
            }
            exit;
        }

        // Geçmiş Başlıklarını Getir
        if (isset($data['islem']) && $data['islem'] === 'gecmis_basliklari_getir') {
            if (!isset($_SESSION['user_email'])) { echo json_encode([]); exit; }
            $sorgu = $db->prepare("SELECT session_id, kullanici_mesaji 
                                   FROM sohbet_gecmisi 
                                   WHERE kullanici_email = ? AND id IN (
                                       SELECT MIN(id) FROM sohbet_gecmisi GROUP BY session_id
                                   ) ORDER BY id DESC LIMIT 20");
            $sorgu->execute([$_SESSION['user_email']]);
            echo json_encode($sorgu->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // Sohbet Detaylarını Getir
        if (isset($data['islem']) && $data['islem'] === 'sohbet_detay_getir') {
            if (!isset($_SESSION['user_email']) || empty($data['session_id'])) { echo json_encode([]); exit; }
            $sorgu = $db->prepare("SELECT kullanici_mesaji, bot_cevabi FROM sohbet_gecmisi WHERE kullanici_email = ? AND session_id = ? ORDER BY id ASC");
            $sorgu->execute([$_SESSION['user_email'], $data['session_id']]);
            echo json_encode($sorgu->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // --- ADMIN BİLDİRİM GÖNDER ---
        if (isset($data['islem']) && $data['islem'] === 'bildirim_gonder') {
            if (($_SESSION['rol'] ?? 'user') !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Yetkisiz!']);
                exit;
            }
            $baslik = trim($data['baslik'] ?? '');
            $mesaj = trim($data['mesaj'] ?? '');
            $hedef = $data['hedef'] ?? 'all';  // 'all' veya email
            $gonderen = $_SESSION['user_name'] ?? 'Admin';

            if (empty($mesaj)) {
                echo json_encode(['success' => false, 'error' => 'Mesaj boş!']);
                exit;
            }

            if ($hedef === 'all') {
                $emails = $db->query("SELECT email FROM users")->fetchAll(PDO::FETCH_COLUMN);
                $ekle = $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen) VALUES (?, ?, ?, ?)");
                foreach ($emails as $em) {
                    $ekle->execute([$em, $baslik, $mesaj, $gonderen]);
                }
                echo json_encode(['success' => true, 'gonderildi' => count($emails)]);
            } else {
                $ekle = $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen) VALUES (?, ?, ?, ?)");
                $ekle->execute([$hedef, $baslik, $mesaj, $gonderen]);
                echo json_encode(['success' => true, 'gonderildi' => 1]);
            }
            exit;
        }

        // --- BİLDİRİMLERİ GETİR ---
        if (isset($data['islem']) && $data['islem'] === 'bildirimleri_getir') {
            if (!isset($_SESSION['user_email'])) { echo json_encode([]); exit; }
            $s = $db->prepare("SELECT * FROM bildirimler WHERE kullanici_email = ? ORDER BY tarih DESC LIMIT 30");
            $s->execute([$_SESSION['user_email']]);
            echo json_encode($s->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // --- BİLDİRİM OKUNDU İŞARETLE ---
        if (isset($data['islem']) && $data['islem'] === 'bildirim_okundu') {
            if (!isset($_SESSION['user_email'])) { echo json_encode(['success'=>false]); exit; }
            $g = $db->prepare("UPDATE bildirimler SET okundu = 1 WHERE id = ? AND kullanici_email = ?");
            $g->execute([intval($data['id']), $_SESSION['user_email']]);
            echo json_encode(['success' => true]);
            exit;
        }

        // --- AI'DAN ÖZET ÇIKART ---
        if (isset($data['islem']) && $data['islem'] === 'ai_ozet') {
            $icerik = trim($data['icerik'] ?? '');
            $mod = $data['mod'] ?? 'ozet'; // 'ozet' veya 'anlat'

            if (mb_strlen($icerik) < 30) {
                echo json_encode(['success'=>false, 'error'=>'En az 30 karakterlik içerik gerekli.']);
                exit;
            }

            $anthropic_api_key = $config['ANTHROPIC_API_KEY'] ?? '';

            if ($mod === 'anlat') {
                $prompt = "Aşağıdaki ders notunu bir öğretmen gibi öğrenciye anlatır gibi açıkla. Doğal bir dille, akıcı bir paragraf halinde yaz. Maksimum 300 kelime kullan.\n\nNOT:\n" . substr(strip_tags($icerik), 0, 6000);
            } else {
                $prompt = "Aşağıdaki ders notunun en önemli noktalarını içeren kısa ve öz bir özet çıkar. Madde madde, anahtar kelimeleri **bold** olarak işaretle. HTML formatında dön.\n\nNOT:\n" . substr(strip_tags($icerik), 0, 6000);
            }

            $ch = curl_init('https://api.anthropic.com/v1/messages');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-api-key: ' . $anthropic_api_key,
                    'anthropic-version: 2023-06-01'
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 1500,
                    'messages' => [['role'=>'user','content'=>$prompt]]
                ])
            ]);
            $resp = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $r = json_decode($resp, true);
            if ($http === 200 && isset($r['content'][0]['text'])) {
                echo json_encode(['success'=>true, 'sonuc'=>$r['content'][0]['text']]);
            } else {
                echo json_encode(['success'=>false, 'error'=>'AI hatası: '.($r['error']['message'] ?? "HTTP $http")]);
            }
            exit;
        }

        // --- AI'DAN SORU ÜRET ---
        if (isset($data['islem']) && $data['islem'] === 'ai_soru_uret') {
            $icerik = trim($data['icerik'] ?? '');
            $adet = max(1, min(20, intval($data['adet'] ?? 5)));

            if (mb_strlen($icerik) < 50) {
                echo json_encode(['success'=>false, 'error'=>'En az 50 karakterlik içerik gerekli.']);
                exit;
            }

            $anthropic_api_key = $config['ANTHROPIC_API_KEY'] ?? '';
            $prompt = "Aşağıdaki metinden $adet adet çoktan seçmeli test sorusu hazırla.\n".
                      "SADECE geçerli JSON dizisi döndür. Şu formatta:\n".
                      "[{\"soru_metni\":\"...\",\"secenek_a\":\"...\",\"secenek_b\":\"...\",\"secenek_c\":\"...\",\"secenek_d\":\"...\",\"dogru_cevap\":\"A\"}]\n".
                      "Başka hiçbir açıklama yazma, sadece JSON dön.\n\nMETİN:\n".substr(strip_tags($icerik), 0, 8000);

            $ch = curl_init('https://api.anthropic.com/v1/messages');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-api-key: ' . $anthropic_api_key,
                    'anthropic-version: 2023-06-01'
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 2048,
                    'messages' => [['role'=>'user','content'=>$prompt]]
                ])
            ]);
            $resp = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $r = json_decode($resp, true);
            if ($http !== 200 || !isset($r['content'][0]['text'])) {
                echo json_encode(['success'=>false, 'error'=>'AI cevap vermedi: '.($r['error']['message'] ?? 'bilinmeyen')]);
                exit;
            }
            $cevap = $r['content'][0]['text'];

            // JSON'u çıkar
            if (preg_match('/\[.*\]/s', $cevap, $m)) {
                $sorular = json_decode($m[0], true);
                if (is_array($sorular)) {
                    echo json_encode(['success'=>true, 'sorular'=>$sorular]);
                    exit;
                }
            }
            echo json_encode(['success'=>false, 'error'=>'AI cevabı parse edilemedi.', 'raw'=>$cevap]);
            exit;
        }

        // --- QUIZ SONUÇ KAYDET ---
        if (isset($data['islem']) && $data['islem'] === 'quiz_sonuc_kaydet') {
            if (!isset($_SESSION['user_email'])) { echo json_encode(['success'=>false,'error'=>'Giriş yap']); exit; }
            $note_id = intval($data['note_id']);
            $dogru = intval($data['dogru_sayisi']);
            $toplam = intval($data['toplam_soru']);
            $puan = $toplam > 0 ? round(($dogru / $toplam) * 100, 2) : 0;
            $kaydet = $db->prepare("INSERT INTO quiz_sonuclari (note_id, kullanici_email, dogru_sayisi, toplam_soru, puan) VALUES (?, ?, ?, ?, ?)");
            $kaydet->execute([$note_id, $_SESSION['user_email'], $dogru, $toplam, $puan]);

            // Quiz XP: temel 20 XP + bonus
            $xpKazanc = 20 + intval($puan / 10); // 100 puan = 30 XP
            xpVer($db, $_SESSION['user_email'], $xpKazanc);
            rozetKontrol($db, $_SESSION['user_email']);

            echo json_encode(['success' => true, 'puan' => $puan, 'xp_kazandin' => $xpKazanc]);
            exit;
        }

        // ==========================================
        // YER İMLERİ (BOOKMARK)
        // ==========================================
        if (isset($data['islem']) && $data['islem'] === 'imle_toggle') {
            if (!isset($_SESSION['user_email'])) { echo json_encode(['success'=>false,'error'=>'Giriş yap']); exit; }
            $note_id = intval($data['note_id']);
            $email = $_SESSION['user_email'];
            $k = $db->prepare("SELECT id FROM yer_imleri WHERE kullanici_email = ? AND note_id = ?");
            $k->execute([$email, $note_id]);
            if ($k->rowCount()) {
                $db->prepare("DELETE FROM yer_imleri WHERE kullanici_email = ? AND note_id = ?")->execute([$email, $note_id]);
                echo json_encode(['success'=>true, 'durum'=>'silindi']);
            } else {
                $db->prepare("INSERT INTO yer_imleri (kullanici_email, note_id) VALUES (?, ?)")->execute([$email, $note_id]);
                echo json_encode(['success'=>true, 'durum'=>'eklendi']);
            }
            exit;
        }

        // ==========================================
        // YORUM SİSTEMİ
        // ==========================================
        if (isset($data['islem']) && $data['islem'] === 'yorum_ekle') {
            if (!isset($_SESSION['user_email'])) { echo json_encode(['success'=>false,'error'=>'Giriş yap']); exit; }
            $note_id = intval($data['note_id']);
            $mesaj = trim($data['mesaj'] ?? '');
            $parent_id = !empty($data['parent_id']) ? intval($data['parent_id']) : null;
            if (mb_strlen($mesaj) < 2) { echo json_encode(['success'=>false,'error'=>'Çok kısa']); exit; }
            $db->prepare("INSERT INTO yorumlar (note_id, kullanici_email, kullanici_ad, mesaj, parent_id) VALUES (?, ?, ?, ?, ?)")
               ->execute([$note_id, $_SESSION['user_email'], $_SESSION['user_name'] ?? 'Kullanıcı', $mesaj, $parent_id]);
            // XP + rozet
            xpVer($db, $_SESSION['user_email'], 5);
            rozetKontrol($db, $_SESSION['user_email']);
            // Not sahibine bildirim
            try {
                $s = $db->prepare("SELECT kullanici_email, title FROM notes WHERE id = ?");
                $s->execute([$note_id]);
                $not = $s->fetch(PDO::FETCH_ASSOC);
                if ($not && $not['kullanici_email'] && $not['kullanici_email'] !== $_SESSION['user_email']) {
                    $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen, tip) VALUES (?, ?, ?, ?, 'yorum')")
                       ->execute([
                           $not['kullanici_email'],
                           "💬 Yeni yorum: " . $not['title'],
                           ($_SESSION['user_name'] ?? 'Biri') . ": " . mb_substr($mesaj, 0, 100),
                           $_SESSION['user_name'] ?? 'Kullanıcı'
                       ]);
                }
            } catch (Exception $e) {}
            echo json_encode(['success'=>true]);
            exit;
        }

        // YORUMLARI GETİR (dersler.php sağ panel için)
        if (isset($data['islem']) && $data['islem'] === 'yorumlari_getir') {
            $note_id = intval($data['note_id'] ?? 0);
            if (!$note_id) { echo json_encode([]); exit; }
            try {
                $s = $db->prepare("SELECT id, kullanici_ad, mesaj, tarih FROM yorumlar WHERE note_id = ? ORDER BY tarih DESC LIMIT 30");
                $s->execute([$note_id]);
                echo json_encode($s->fetchAll(PDO::FETCH_ASSOC));
            } catch (Exception $e) {
                echo json_encode([]);
            }
            exit;
        }

        if (isset($data['islem']) && $data['islem'] === 'yorum_sil') {
            if (!isset($_SESSION['user_email'])) { echo json_encode(['success'=>false]); exit; }
            $id = intval($data['id']);
            $rol = $_SESSION['rol'] ?? 'user';
            if ($rol === 'admin') {
                $db->prepare("DELETE FROM yorumlar WHERE id = ?")->execute([$id]);
            } else {
                $db->prepare("DELETE FROM yorumlar WHERE id = ? AND kullanici_email = ?")->execute([$id, $_SESSION['user_email']]);
            }
            echo json_encode(['success'=>true]);
            exit;
        }

        // ==========================================
        // KULLANICI TAKİP
        // ==========================================
        if (isset($data['islem']) && $data['islem'] === 'takip_toggle') {
            if (!isset($_SESSION['user_email'])) { echo json_encode(['success'=>false,'error'=>'Giriş yap']); exit; }
            $hedef = trim($data['hedef_email']);
            $email = $_SESSION['user_email'];
            if ($hedef === $email) { echo json_encode(['success'=>false,'error'=>'Kendini takip edemezsin']); exit; }

            $k = $db->prepare("SELECT id FROM takipler WHERE takip_eden = ? AND takip_edilen = ?");
            $k->execute([$email, $hedef]);
            if ($k->rowCount()) {
                $db->prepare("DELETE FROM takipler WHERE takip_eden = ? AND takip_edilen = ?")->execute([$email, $hedef]);
                echo json_encode(['success'=>true, 'durum'=>'cikti']);
            } else {
                $db->prepare("INSERT INTO takipler (takip_eden, takip_edilen) VALUES (?, ?)")->execute([$email, $hedef]);
                // Hedefe bildirim
                try {
                    $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen, tip) VALUES (?, ?, ?, ?, 'takip')")
                       ->execute([$hedef, "⭐ Yeni Takipçi", ($_SESSION['user_name'] ?? 'Biri') . " seni takip etmeye başladı!", $_SESSION['user_name'] ?? 'Kullanıcı']);
                } catch (Exception $e) {}
                rozetKontrol($db, $hedef);
                echo json_encode(['success'=>true, 'durum'=>'takip']);
            }
            exit;
        }

        // ==========================================
        // NOT'A ETİKET (HASHTAG) EKLE/GÜNCELLE
        // ==========================================
        if (isset($data['islem']) && $data['islem'] === 'etiket_guncelle') {
            if (!isset($_SESSION['user_email'])) { echo json_encode(['success'=>false]); exit; }
            $note_id = intval($data['note_id']);
            $etiketler = $data['etiketler'] ?? [];

            // Sahip mi?
            $s = $db->prepare("SELECT kullanici_email FROM notes WHERE id = ?");
            $s->execute([$note_id]);
            $sahip = $s->fetchColumn();
            if ($sahip !== $_SESSION['user_email'] && ($_SESSION['rol'] ?? '') !== 'admin') {
                echo json_encode(['success'=>false,'error'=>'Yetki yok']); exit;
            }

            $db->prepare("DELETE FROM etiketler WHERE note_id = ?")->execute([$note_id]);
            $ins = $db->prepare("INSERT INTO etiketler (note_id, etiket) VALUES (?, ?)");
            foreach ($etiketler as $e) {
                $e = strtolower(trim(str_replace('#', '', $e)));
                if ($e && mb_strlen($e) <= 60) $ins->execute([$note_id, $e]);
            }
            echo json_encode(['success'=>true]);
            exit;
        }

        // ==========================================
        // NEWSLETTER ABONELİĞİ
        // ==========================================
        if (isset($data['islem']) && $data['islem'] === 'newsletter_abone') {
            $email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $ad = trim($data['ad'] ?? '');
            if (!$email) {
                echo json_encode(['success'=>false,'error'=>'Geçersiz e-posta']); exit;
            }
            try {
                $db->prepare("INSERT INTO newsletter_aboneleri (email, ad) VALUES (?, ?) ON DUPLICATE KEY UPDATE aktif = 1, ad = VALUES(ad)")
                   ->execute([$email, $ad]);
                echo json_encode(['success'=>true, 'msg'=>'Abone oldun! Teşekkürler 🎉']);
            } catch (Exception $e) {
                echo json_encode(['success'=>false,'error'=>'Kayıt başarısız']);
            }
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Tanımsız işlem.']);

    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Veritabanı Hatası: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Veri veya veritabanı bağlantısı eksik.']);
}
?>