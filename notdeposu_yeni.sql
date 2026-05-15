-- ============================================================
-- NOTDEPOSU - TAM VERİTABANI (Tüm tablolar + admin kullanıcı)
-- Kullanım: phpMyAdmin > İçe Aktar > Bu dosyayı seç > Git
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `notdeposu`;
CREATE DATABASE `notdeposu` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `notdeposu`;

-- --------------------------------------------------------
-- TABLO: `users`  (ad, rol, durum sütunları dahil)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `ad`            VARCHAR(100) DEFAULT NULL,
  `email`         VARCHAR(255) NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `reset_code`    VARCHAR(10)  DEFAULT NULL,
  `rol`           VARCHAR(20)  DEFAULT 'user',
  `durum`         INT(11)      DEFAULT 1,
  `xp`            INT(11)      DEFAULT 0,
  `seviye`        INT(11)      DEFAULT 1,
  `streak`        INT(11)      DEFAULT 0,
  `son_aktivite`  DATE         DEFAULT NULL,
  `bio`           VARCHAR(255) DEFAULT NULL,
  `tema`          VARCHAR(10)  DEFAULT 'light',
  `kayit_tarihi`  TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- NOT: Admin için önce kaydol.php'den hesabını oluştur
-- Sonra tarayıcıdan setup_admin.php'yi aç → mikailcelik4734@gmail.com otomatik admin olacak

-- --------------------------------------------------------
-- TABLO: `notes`  (kullanici_email EKLENDİ - "benim notlarım" için)
-- --------------------------------------------------------
CREATE TABLE `notes` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(255) DEFAULT NULL,
  `content`         LONGTEXT     DEFAULT NULL,
  `category`        VARCHAR(100) DEFAULT NULL,
  `edu_level`       VARCHAR(100) DEFAULT NULL,
  `school_name`     VARCHAR(255) DEFAULT NULL,
  `subject`         VARCHAR(255) DEFAULT NULL,
  `author`          VARCHAR(255) DEFAULT 'Anonim',
  `kullanici_email` VARCHAR(255) DEFAULT NULL,
  `likes`           INT(11)      DEFAULT 0,
  `dislikes`        INT(11)      DEFAULT 0,
  `goruntulenme`    INT(11)      DEFAULT 0,
  `dosya_yolu`      VARCHAR(500) DEFAULT NULL,
  `grup_id`         INT(11)      DEFAULT NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kullanici_email` (`kullanici_email`),
  KEY `category` (`category`),
  KEY `edu_level` (`edu_level`),
  KEY `grup_id` (`grup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `not_sorulari`  (KRİTİK - hata mesajındaki tablo)
-- --------------------------------------------------------
CREATE TABLE `not_sorulari` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `note_id`     INT(11) NOT NULL,
  `soru_metni`  TEXT    DEFAULT NULL,
  `secenek_a`   VARCHAR(500) DEFAULT NULL,
  `secenek_b`   VARCHAR(500) DEFAULT NULL,
  `secenek_c`   VARCHAR(500) DEFAULT NULL,
  `secenek_d`   VARCHAR(500) DEFAULT NULL,
  `dogru_cevap` VARCHAR(5)   DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  CONSTRAINT `fk_not_sorulari` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `begeniler`  (beğeni sistemi için)
-- --------------------------------------------------------
CREATE TABLE `begeniler` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `note_id`         INT(11)      NOT NULL,
  `islem_tarihi`    TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tek_begeni` (`kullanici_email`, `note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `begeni_verileri`  (eski tablo, geriye uyumluluk için)
-- --------------------------------------------------------
CREATE TABLE `begeni_verileri` (
  `note_id`  INT(11) NOT NULL,
  `likes`    INT(11) DEFAULT 0,
  `dislikes` INT(11) DEFAULT 0,
  PRIMARY KEY (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `etkilesim_kayitlari`
-- --------------------------------------------------------
CREATE TABLE `etkilesim_kayitlari` (
  `id`          INT(11)     NOT NULL AUTO_INCREMENT,
  `note_id`     INT(11)     DEFAULT NULL,
  `user_ip`     VARCHAR(45) DEFAULT NULL,
  `action_type` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `gruplar`  (gruplarim.php için)
-- --------------------------------------------------------
CREATE TABLE `gruplar` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `grup_adi`        VARCHAR(255) NOT NULL,
  `olusturan_email` VARCHAR(255) NOT NULL,
  `aciklama`        TEXT         DEFAULT NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `grup_uyeleri`
-- --------------------------------------------------------
CREATE TABLE `grup_uyeleri` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `grup_id`         INT(11)      NOT NULL,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `katilma_tarihi`  TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tek_uye` (`grup_id`, `kullanici_email`),
  CONSTRAINT `fk_grup` FOREIGN KEY (`grup_id`) REFERENCES `gruplar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `grup_davetleri`  (Grup davet sistemi)
-- --------------------------------------------------------
CREATE TABLE `grup_davetleri` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `grup_id`         INT(11)      NOT NULL,
  `davet_eden`      VARCHAR(255) NOT NULL,
  `davet_edilen`    VARCHAR(255) NOT NULL,
  `durum`           VARCHAR(20)  DEFAULT 'bekliyor',
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tek_davet` (`grup_id`, `davet_edilen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `site_duyurulari`  (Admin'in siteye duyuru asması)
-- --------------------------------------------------------
CREATE TABLE `site_duyurulari` (
  `id`     INT(11)      NOT NULL AUTO_INCREMENT,
  `mesaj`  TEXT         NOT NULL,
  `tip`    VARCHAR(20)  DEFAULT 'info',
  `aktif`  TINYINT(1)   DEFAULT 1,
  `tarih`  TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `bildirimler`  (Admin → Kullanıcı bildirim sistemi)
-- --------------------------------------------------------
CREATE TABLE `bildirimler` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `kullanici_email` VARCHAR(255) DEFAULT NULL,
  `baslik`          VARCHAR(255) DEFAULT NULL,
  `mesaj`           TEXT         DEFAULT NULL,
  `gonderen`        VARCHAR(255) DEFAULT NULL,
  `okundu`          TINYINT(1)   DEFAULT 0,
  `tip`             VARCHAR(50)  DEFAULT 'genel',
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kullanici_email` (`kullanici_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `sohbet_gecmisi`  (DersBotu)
-- --------------------------------------------------------
CREATE TABLE `sohbet_gecmisi` (
  `id`               INT(11)      NOT NULL AUTO_INCREMENT,
  `kullanici_email`  VARCHAR(255) DEFAULT NULL,
  `session_id`       VARCHAR(100) DEFAULT NULL,
  `kullanici_mesaji` TEXT         DEFAULT NULL,
  `bot_cevabi`       TEXT         DEFAULT NULL,
  `tarih`            TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `dersbotu_sessions`
-- --------------------------------------------------------
CREATE TABLE `dersbotu_sessions` (
  `session_id` VARCHAR(100) NOT NULL,
  `baslik`     VARCHAR(255) DEFAULT NULL,
  `guncelleme` TIMESTAMP    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `dersbotu_mesajlar`
-- --------------------------------------------------------
CREATE TABLE `dersbotu_mesajlar` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `rol`        VARCHAR(20)  DEFAULT NULL,
  `icerik`     TEXT         DEFAULT NULL,
  `zaman`      TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `yer_imleri`  (Bookmark)
-- --------------------------------------------------------
CREATE TABLE `yer_imleri` (
  `id`              INT(11) NOT NULL AUTO_INCREMENT,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `note_id`         INT(11) NOT NULL,
  `tarih`           TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tek_imleme` (`kullanici_email`, `note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `yorumlar`
-- --------------------------------------------------------
CREATE TABLE `yorumlar` (
  `id`              INT(11) NOT NULL AUTO_INCREMENT,
  `note_id`         INT(11) NOT NULL,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `kullanici_ad`    VARCHAR(100) DEFAULT NULL,
  `mesaj`           TEXT NOT NULL,
  `parent_id`       INT(11) DEFAULT NULL,
  `tarih`           TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `etiketler`  (Hashtag)
-- --------------------------------------------------------
CREATE TABLE `etiketler` (
  `id`      INT(11) NOT NULL AUTO_INCREMENT,
  `note_id` INT(11) NOT NULL,
  `etiket`  VARCHAR(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `etiket` (`etiket`),
  KEY `note_id` (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `takipler`
-- --------------------------------------------------------
CREATE TABLE `takipler` (
  `id`             INT(11) NOT NULL AUTO_INCREMENT,
  `takip_eden`     VARCHAR(255) NOT NULL,
  `takip_edilen`   VARCHAR(255) NOT NULL,
  `tarih`          TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tek_takip` (`takip_eden`, `takip_edilen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `rozetler`  (Achievement log)
-- --------------------------------------------------------
CREATE TABLE `rozetler` (
  `id`              INT(11) NOT NULL AUTO_INCREMENT,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `rozet_kod`       VARCHAR(50) NOT NULL,
  `tarih`           TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tek_rozet` (`kullanici_email`, `rozet_kod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `canli_sinavlar`
-- --------------------------------------------------------
CREATE TABLE `canli_sinavlar` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `baslik`       VARCHAR(255) NOT NULL,
  `aciklama`     TEXT DEFAULT NULL,
  `baslangic`    DATETIME NOT NULL,
  `bitis`        DATETIME NOT NULL,
  `sure_dakika`  INT(11) DEFAULT 60,
  `olusturan`    VARCHAR(255) DEFAULT NULL,
  `durum`        VARCHAR(20) DEFAULT 'planlandi',
  `created_at`   TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `canli_sinav_sorulari`
-- --------------------------------------------------------
CREATE TABLE `canli_sinav_sorulari` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `sinav_id`    INT(11) NOT NULL,
  `soru_metni`  TEXT,
  `secenek_a`   VARCHAR(500),
  `secenek_b`   VARCHAR(500),
  `secenek_c`   VARCHAR(500),
  `secenek_d`   VARCHAR(500),
  `dogru_cevap` VARCHAR(5),
  PRIMARY KEY (`id`),
  KEY `sinav_id` (`sinav_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `canli_sinav_katilim`
-- --------------------------------------------------------
CREATE TABLE `canli_sinav_katilim` (
  `id`               INT(11) NOT NULL AUTO_INCREMENT,
  `sinav_id`         INT(11) NOT NULL,
  `kullanici_email`  VARCHAR(255) NOT NULL,
  `kullanici_ad`     VARCHAR(100) DEFAULT NULL,
  `dogru_sayisi`     INT(11) DEFAULT 0,
  `yanlis_sayisi`    INT(11) DEFAULT 0,
  `bos_sayisi`       INT(11) DEFAULT 0,
  `puan`             DECIMAL(5,2) DEFAULT 0.00,
  `bitis_zamani`     DATETIME DEFAULT NULL,
  `cevaplar_json`    TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tek_katilim` (`sinav_id`, `kullanici_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `sikayetler`  (Şikayet/Bildirim sistemi)
-- --------------------------------------------------------
CREATE TABLE `sikayetler` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `konu`            VARCHAR(255) DEFAULT NULL,
  `mesaj`           TEXT         DEFAULT NULL,
  `cevap`           TEXT         DEFAULT NULL,
  `cevaplayan`      VARCHAR(255) DEFAULT NULL,
  `cevap_tarihi`    TIMESTAMP    NULL DEFAULT NULL,
  `durum`           VARCHAR(20)  DEFAULT 'bekliyor',
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kullanici_email` (`kullanici_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLO: `quiz_sonuclari`  (Kullanıcının çözdüğü testler)
-- --------------------------------------------------------
CREATE TABLE `quiz_sonuclari` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `note_id`         INT(11)      NOT NULL,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `dogru_sayisi`    INT(11)      DEFAULT 0,
  `toplam_soru`     INT(11)      DEFAULT 0,
  `puan`            DECIMAL(5,2) DEFAULT 0.00,
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kullanici_email` (`kullanici_email`),
  KEY `note_id` (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ÖRNEK VERİLER - Her seviyeden ve kategoriden örnek not
-- --------------------------------------------------------

INSERT INTO `notes` (`title`, `content`, `category`, `edu_level`, `school_name`, `subject`, `author`, `kullanici_email`, `likes`, `dislikes`) VALUES
-- ÜNİVERSİTE
('Veri Yapıları - Linked List Anlatımı',
 '<h2>Bağlı Liste (Linked List) Nedir?</h2><p>Linked List, her bir elemanın bir sonraki elemanın adresini tuttuğu doğrusal veri yapısıdır. Dizilerden farkı, bellekte ardışık olmak zorunda olmamasıdır.</p><h3>Avantajları</h3><ul><li>Dinamik boyut</li><li>Hızlı ekleme/silme</li></ul><h3>Dezavantajları</h3><ul><li>Rastgele erişim yavaş O(n)</li><li>Ekstra bellek (pointer)</li></ul>',
 'Konu Anlatımı', 'Üniversite', 'Bilgisayar Mühendisliği', 'Veri Yapıları', 'Mikail Celik', 'mikailcelik4734@gmail.com', 12, 0),

('Kalkülüs 1 - Limit Çözümlü Sorular',
 '<p>Bu sette 10 adet limit problemi çözümlü olarak sunulmuştur. L Hospital kuralı, eşlenik çarpma ve özel limitler kullanılmıştır.</p>',
 'Soru Çözümü', 'Üniversite', 'Matematik', 'Kalkülüs 1', 'Mikail Celik', 'mikailcelik4734@gmail.com', 8, 1),

('Yapay Zeka Final Özet',
 '<h2>YZ Final Özeti</h2><p><b>Search:</b> BFS, DFS, A*, Greedy</p><p><b>Machine Learning:</b> Supervised, Unsupervised, Reinforcement</p><p><b>Neural Networks:</b> Perceptron, MLP, CNN, RNN</p>',
 'Özet', 'Üniversite', 'Bilgisayar Mühendisliği', 'Yapay Zeka', 'Mikail Celik', 'mikailcelik4734@gmail.com', 25, 0),

-- LİSE
('AYT Matematik - Türev Konu Anlatımı',
 '<h2>Türev Nedir?</h2><p>Bir fonksiyonun bir noktasındaki anlık değişim oranıdır.</p><p>f\'(x) = lim h→0 [f(x+h) - f(x)] / h</p><h3>Türev Kuralları</h3><ul><li>(c)\' = 0</li><li>(x^n)\' = n·x^(n-1)</li><li>(sin x)\' = cos x</li></ul>',
 'Konu Anlatımı', 'Lise', 'Fen Lisesi', 'AYT Matematik', 'Mikail Celik', 'mikailcelik4734@gmail.com', 18, 0),

('Lise Matematik - Polinomlar Test',
 '<p>Polinomlar konusundan 10 soruluk hazırlık testi.</p>',
 'Soru Çözümü', 'Lise', 'Anadolu Lisesi', 'Matematik', 'Mikail Celik', 'mikailcelik4734@gmail.com', 15, 0),

('Fizik - Newton Yasaları Özeti',
 '<h2>Newton 3 Yasası</h2><p><b>1.</b> Eylemsizlik Prensibi: Bir cisme net kuvvet etki etmiyorsa hareket durumunu korur.</p><p><b>2.</b> F = m · a</p><p><b>3.</b> Etki-Tepki: Her etkiye eşit ve zıt yönde bir tepki vardır.</p>',
 'Özet', 'Lise', 'Anadolu Lisesi', 'Fizik', 'Mikail Celik', 'mikailcelik4734@gmail.com', 9, 0),

('Kimya - Asit Baz Deneyi',
 '<h2>Kırmızı Lahana ile Asit-Baz Deneyi</h2><p>Kırmızı lahanadan elde edilen sıvı doğal bir indikatördür.</p><p><b>Asitte:</b> Kırmızı / Pembe</p><p><b>Bazda:</b> Yeşil / Sarı</p>',
 'Deney', 'Lise', 'Fen Lisesi', 'Kimya', 'Mikail Celik', 'mikailcelik4734@gmail.com', 6, 0),

-- ORTAOKUL
('8. Sınıf Matematik - Üslü Sayılar',
 '<h2>Üslü Sayılar</h2><p>a^n ifadesinde a tabandır, n üstür. a^n = a · a · a ... (n kez)</p><h3>Kurallar</h3><ul><li>a^0 = 1</li><li>a^m · a^n = a^(m+n)</li><li>(a^m)^n = a^(m·n)</li></ul>',
 'Konu Anlatımı', 'Orta Okul', 'Ortaokul', 'Matematik', 'Mikail Celik', 'mikailcelik4734@gmail.com', 14, 0),

('Fen Bilimleri - DNA ve Kalıtım Özet',
 '<p>DNA, canlıların genetik bilgilerini taşıyan moleküldür. Genler DNA üzerinde bulunur. Mendel\'in kalıtım yasaları temel prensiptir.</p>',
 'Özet', 'Orta Okul', 'Ortaokul', 'Fen Bilimleri', 'Mikail Celik', 'mikailcelik4734@gmail.com', 11, 0),

-- İLKOKUL
('4. Sınıf Matematik - Kesirler',
 '<h2>Kesirler</h2><p>Bir bütünün eşit parçalara bölünmüş halini gösterir. Pay/Payda olarak yazılır.</p><p>Örnek: Pizzayı 4 eşit parçaya böldük, 1 dilimi yedik → 1/4</p>',
 'Konu Anlatımı', 'İlkokul', 'İlkokul', 'Matematik', 'Mikail Celik', 'mikailcelik4734@gmail.com', 5, 0),

-- KOD ÖRNEĞİ
('JavaScript - İlk HTML Sayfan',
 '{"html":"<h1>Merhaba Dünya</h1>\\n<button onclick=\\"selamla()\\">Tıkla</button>","css":"h1{color:purple;font-family:Arial;}\\nbutton{background:#4f46e5;color:white;padding:10px 20px;border:none;border-radius:8px;cursor:pointer;}","js":"function selamla(){alert(\\"Hoşgeldin!\\");}"}',
 'Kod', 'Lise', 'Anadolu Lisesi', 'Bilgisayar Bilimi', 'Mikail Celik', 'mikailcelik4734@gmail.com', 20, 0);


-- ÖRNEK QUIZ SORULARI (Lise Matematik - Polinomlar)
INSERT INTO `not_sorulari` (`note_id`, `soru_metni`, `secenek_a`, `secenek_b`, `secenek_c`, `secenek_d`, `dogru_cevap`) VALUES
(5, 'P(x) = x² - 4 polinomunun kökleri hangileridir?', '-2 ve 2', '0 ve 4', '-4 ve 4', '1 ve -1', 'A'),
(5, 'P(x) = 2x + 6 polinomunun derecesi kaçtır?', '0', '1', '2', '6', 'B'),
(5, 'P(x) = x³ - 8 polinomunun P(2) değeri nedir?', '-8', '0', '8', '16', 'B'),
(5, '(x+1)(x-2) çarpımının açılımı nedir?', 'x² - x - 2', 'x² + x - 2', 'x² - 2', 'x² + x + 2', 'A'),
(5, 'P(x) = x² + 3x + 2 polinomunun çarpanlara ayrılmış hali?', '(x+1)(x+2)', '(x-1)(x-2)', '(x+3)(x+2)', '(x+1)(x+3)', 'A');

-- KALKÜLÜS QUIZ
INSERT INTO `not_sorulari` (`note_id`, `soru_metni`, `secenek_a`, `secenek_b`, `secenek_c`, `secenek_d`, `dogru_cevap`) VALUES
(2, 'lim (x→0) (sin x)/x değeri nedir?', '0', '1', '∞', 'Tanımsız', 'B'),
(2, 'lim (x→2) (x²-4)/(x-2) değeri nedir?', '0', '2', '4', '8', 'C'),
(2, 'lim (x→∞) (3x² + 2x)/(x² + 1) değeri?', '0', '1', '2', '3', 'D');

COMMIT;
