<?php
/**
 * notewarehouse Helpers — XP / Streak / Rozet sistemi
 * baglan.php sonrasında include et: require_once 'helpers.php';
 */

// ============================================================
// XP & SEVİYE
// ============================================================

/**
 * Seviye hesaplama formülü:
 * Seviye 1 = 0 XP
 * Seviye 2 = 100 XP
 * Seviye 3 = 250 XP
 * Seviye n = 100 * n * (n-1) / 2 XP
 */
function xpToSeviye($xp) {
    if ($xp < 100) return 1;
    return (int)floor((1 + sqrt(1 + 8 * $xp / 100)) / 2);
}

function seviyeToXp($seviye) {
    return (int)(100 * $seviye * ($seviye - 1) / 2);
}

function sonrakiSeviyeIcinXp($mevcutXp) {
    $mevcutSeviye = xpToSeviye($mevcutXp);
    $sonraki = seviyeToXp($mevcutSeviye + 1);
    return $sonraki - $mevcutXp;
}

/**
 * Kullanıcıya XP ver. Yeni rozet kazanmışsa kazandığı rozetleri döner.
 */
function xpVer($db, $email, $miktar, $sebep = '') {
    if (!$email || $miktar <= 0) return [];
    try {
        $eski = $db->prepare("SELECT xp, seviye FROM users WHERE email = ?");
        $eski->execute([$email]);
        $r = $eski->fetch(PDO::FETCH_ASSOC);
        if (!$r) return [];

        $yeniXp = (int)$r['xp'] + (int)$miktar;
        $yeniSeviye = xpToSeviye($yeniXp);

        $g = $db->prepare("UPDATE users SET xp = ?, seviye = ? WHERE email = ?");
        $g->execute([$yeniXp, $yeniSeviye, $email]);

        // Seviye atladı mı?
        if ($yeniSeviye > (int)$r['seviye']) {
            try {
                $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen, tip) VALUES (?, ?, ?, ?, 'seviye')")
                   ->execute([
                       $email,
                       "🎉 Seviye Atladın!",
                       "Tebrikler! Seviye $yeniSeviye'a yükseldin. Yeni hedef: Seviye " . ($yeniSeviye + 1) . " için " . sonrakiSeviyeIcinXp($yeniXp) . " XP daha kazan!",
                       'notewarehouse Sistemi'
                   ]);
            } catch (Exception $e) {}
        }

        // Rozet kontrolü
        return rozetKontrol($db, $email);
    } catch (Exception $e) {
        return [];
    }
}

// ============================================================
// STREAK (Çalışma Serisi)
// ============================================================

/**
 * Kullanıcı her giriş yaptığında çağır. Streak'i günceller.
 * Eğer bugün ilk kez geldiyse +10 XP verir.
 */
function streakGuncelle($db, $email) {
    if (!$email) return;
    try {
        $s = $db->prepare("SELECT streak, son_aktivite FROM users WHERE email = ?");
        $s->execute([$email]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        if (!$r) return;

        $bugun = date('Y-m-d');
        $sonAktivite = $r['son_aktivite'];

        if ($sonAktivite === $bugun) return; // Aynı gün, dokunma

        $yeniStreak = 1;
        if ($sonAktivite) {
            $fark = (new DateTime($bugun))->diff(new DateTime($sonAktivite))->days;
            if ($fark === 1) {
                // Üst üste gün → streak +1
                $yeniStreak = (int)$r['streak'] + 1;
            } elseif ($fark === 0) {
                $yeniStreak = (int)$r['streak'];
            } else {
                // Streak bozuldu, 1'den başla
                $yeniStreak = 1;
            }
        }

        $db->prepare("UPDATE users SET streak = ?, son_aktivite = ? WHERE email = ?")
           ->execute([$yeniStreak, $bugun, $email]);

        // İlk girişte 10 XP
        xpVer($db, $email, 10, 'gunluk_giris');

        // Streak milestone bildirimi
        if (in_array($yeniStreak, [3, 7, 14, 30, 50, 100])) {
            try {
                $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen, tip) VALUES (?, ?, ?, ?, 'streak')")
                   ->execute([
                       $email,
                       "🔥 $yeniStreak Gündür Üst Üste!",
                       "Harikasın! $yeniStreak gündür her gün geliyorsun. Devam et!",
                       'notewarehouse Sistemi'
                   ]);
            } catch (Exception $e) {}
        }
    } catch (Exception $e) {}
}

// ============================================================
// ROZETLER
// ============================================================

function rozetListesi() {
    return [
        'ilk_not'       => ['ad' => 'İlk Adım', 'ikon' => '🎯', 'aciklama' => 'İlk notunu paylaştın', 'xp' => 50],
        'on_not'        => ['ad' => 'Yazar',     'ikon' => '✍️', 'aciklama' => '10 not paylaştın', 'xp' => 200],
        'elli_not'      => ['ad' => 'Profesör', 'ikon' => '🎓', 'aciklama' => '50 not paylaştın', 'xp' => 1000],
        'ilk_begeni'    => ['ad' => 'İlk Beğeni', 'ikon' => '👍', 'aciklama' => 'İlk beğenini aldın', 'xp' => 25],
        'yuz_begeni'    => ['ad' => 'Sevilen',    'ikon' => '❤️', 'aciklama' => '100 beğeni aldın', 'xp' => 300],
        'streak_7'      => ['ad' => 'Kararlı',    'ikon' => '🔥', 'aciklama' => '7 gün üst üste giriş', 'xp' => 100],
        'streak_30'     => ['ad' => 'Disiplinli', 'ikon' => '⚡', 'aciklama' => '30 gün üst üste giriş', 'xp' => 500],
        'quiz_master'   => ['ad' => 'Quiz Master','ikon' => '🧠', 'aciklama' => '10 quiz tamamladın', 'xp' => 200],
        'quiz_perfect'  => ['ad' => 'Mükemmel',  'ikon' => '💯', 'aciklama' => 'Bir quiz\'i 100 puanla bitirdin', 'xp' => 150],
        'ilk_yorum'     => ['ad' => 'Sosyal',     'ikon' => '💬', 'aciklama' => 'İlk yorumunu yaptın', 'xp' => 20],
        'ilk_takipci'   => ['ad' => 'Popüler',    'ikon' => '⭐', 'aciklama' => 'İlk takipçini kazandın', 'xp' => 50],
        'on_takipci'    => ['ad' => 'Star',       'ikon' => '🌟', 'aciklama' => '10 takipçi kazandın', 'xp' => 300],
        'sinav_kazanan' => ['ad' => 'Şampiyon',  'ikon' => '🏆', 'aciklama' => 'Canlı sınavda birinci oldun', 'xp' => 500],
        'erken_kus'     => ['ad' => 'Erken Kuş',  'ikon' => '🐦', 'aciklama' => 'İlk 100 kullanıcıdan birisin', 'xp' => 100],
    ];
}

function rozetKazandi($db, $email, $kod) {
    $rozetler = rozetListesi();
    if (!isset($rozetler[$kod])) return false;

    try {
        $stmt = $db->prepare("INSERT IGNORE INTO rozetler (kullanici_email, rozet_kod) VALUES (?, ?)");
        $stmt->execute([$email, $kod]);
        if ($stmt->rowCount() > 0) {
            $r = $rozetler[$kod];
            // Rozet bildirimi
            try {
                $db->prepare("INSERT INTO bildirimler (kullanici_email, baslik, mesaj, gonderen, tip) VALUES (?, ?, ?, ?, 'rozet')")
                   ->execute([
                       $email,
                       "🎖️ Yeni Rozet: " . $r['ad'],
                       $r['ikon'] . " " . $r['aciklama'] . " — " . $r['xp'] . " XP kazandın!",
                       'notewarehouse Sistemi'
                   ]);
            } catch (Exception $e) {}
            // XP ver (rozet kontrolünü yapmadan!)
            try {
                $u = $db->prepare("SELECT xp, seviye FROM users WHERE email = ?");
                $u->execute([$email]);
                $usr = $u->fetch(PDO::FETCH_ASSOC);
                if ($usr) {
                    $yx = $usr['xp'] + $r['xp'];
                    $ys = xpToSeviye($yx);
                    $db->prepare("UPDATE users SET xp=?, seviye=? WHERE email=?")->execute([$yx, $ys, $email]);
                }
            } catch (Exception $e) {}
            return true;
        }
    } catch (Exception $e) {}
    return false;
}

/**
 * Kullanıcının durumunu kontrol et, kazandığı yeni rozetleri ver.
 * Yeni kazanılan rozet kodlarını döner.
 */
function rozetKontrol($db, $email) {
    if (!$email) return [];
    $yeni = [];
    try {
        // Not sayısı
        $notSayisi = $db->prepare("SELECT COUNT(*) FROM notes WHERE kullanici_email = ?");
        $notSayisi->execute([$email]);
        $notSayisi = (int)$notSayisi->fetchColumn();
        if ($notSayisi >= 1 && rozetKazandi($db, $email, 'ilk_not')) $yeni[] = 'ilk_not';
        if ($notSayisi >= 10 && rozetKazandi($db, $email, 'on_not')) $yeni[] = 'on_not';
        if ($notSayisi >= 50 && rozetKazandi($db, $email, 'elli_not')) $yeni[] = 'elli_not';

        // Toplam beğeni
        $begeni = $db->prepare("SELECT COALESCE(SUM(likes),0) FROM notes WHERE kullanici_email = ?");
        $begeni->execute([$email]);
        $begeni = (int)$begeni->fetchColumn();
        if ($begeni >= 1 && rozetKazandi($db, $email, 'ilk_begeni')) $yeni[] = 'ilk_begeni';
        if ($begeni >= 100 && rozetKazandi($db, $email, 'yuz_begeni')) $yeni[] = 'yuz_begeni';

        // Streak
        $s = $db->prepare("SELECT streak FROM users WHERE email = ?");
        $s->execute([$email]);
        $streak = (int)$s->fetchColumn();
        if ($streak >= 7 && rozetKazandi($db, $email, 'streak_7')) $yeni[] = 'streak_7';
        if ($streak >= 30 && rozetKazandi($db, $email, 'streak_30')) $yeni[] = 'streak_30';

        // Quiz
        try {
            $q = $db->prepare("SELECT COUNT(*) FROM quiz_sonuclari WHERE kullanici_email = ?");
            $q->execute([$email]);
            $quizSayisi = (int)$q->fetchColumn();
            if ($quizSayisi >= 10 && rozetKazandi($db, $email, 'quiz_master')) $yeni[] = 'quiz_master';

            $perf = $db->prepare("SELECT COUNT(*) FROM quiz_sonuclari WHERE kullanici_email = ? AND puan >= 100");
            $perf->execute([$email]);
            if ((int)$perf->fetchColumn() >= 1 && rozetKazandi($db, $email, 'quiz_perfect')) $yeni[] = 'quiz_perfect';
        } catch (Exception $e) {}

        // Yorum
        try {
            $y = $db->prepare("SELECT COUNT(*) FROM yorumlar WHERE kullanici_email = ?");
            $y->execute([$email]);
            if ((int)$y->fetchColumn() >= 1 && rozetKazandi($db, $email, 'ilk_yorum')) $yeni[] = 'ilk_yorum';
        } catch (Exception $e) {}

        // Takipçi
        try {
            $t = $db->prepare("SELECT COUNT(*) FROM takipler WHERE takip_edilen = ?");
            $t->execute([$email]);
            $takipci = (int)$t->fetchColumn();
            if ($takipci >= 1 && rozetKazandi($db, $email, 'ilk_takipci')) $yeni[] = 'ilk_takipci';
            if ($takipci >= 10 && rozetKazandi($db, $email, 'on_takipci')) $yeni[] = 'on_takipci';
        } catch (Exception $e) {}
    } catch (Exception $e) {}
    return $yeni;
}
?>
