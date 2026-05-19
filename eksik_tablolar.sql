-- ============================================================
-- Eksik tabloları güvenle ekle (IF NOT EXISTS)
-- Sunucuda phpMyAdmin'den veya `mysql -u user -p notdeposu < eksik_tablolar.sql`
-- ile çalıştır. Mevcut tablolar varsa atlar.
-- ============================================================

-- ================================================
-- ALTER: Mevcut tablolara kolon ekle (MySQL + MariaDB uyumlu)
-- information_schema ile kontrol ederek ekler, hata vermez
-- ================================================

-- notes tablosuna durum kolonu ekle (varsa atla)
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'notes'
      AND COLUMN_NAME = 'durum'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE `notes` ADD COLUMN `durum` ENUM('beklemede','onayli','reddedildi') DEFAULT 'onayli' AFTER `created_at`",
    "SELECT 'durum kolonu zaten var' AS bilgi"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index ekle (varsa atla)
SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'notes'
      AND INDEX_NAME = 'idx_notes_durum'
);
SET @sql = IF(@idx_exists = 0,
    "CREATE INDEX `idx_notes_durum` ON `notes`(`durum`)",
    "SELECT 'index zaten var' AS bilgi"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tum eski notlari 'onayli' yap (yeni durum kolonu icin)
UPDATE `notes` SET `durum` = 'onayli' WHERE `durum` IS NULL OR `durum` = '';

-- Newsletter aboneleri (Haberdar Ol formu için)
CREATE TABLE IF NOT EXISTS `newsletter_aboneleri` (
  `id`    INT(11)      NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `ad`    VARCHAR(100) DEFAULT NULL,
  `aktif` TINYINT(1)   DEFAULT 1,
  `tarih` TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sohbet geçmişi (Dersbotu için)
CREATE TABLE IF NOT EXISTS `sohbet_gecmisi` (
  `id`               INT(11)      NOT NULL AUTO_INCREMENT,
  `kullanici_email`  VARCHAR(255) DEFAULT NULL,
  `session_id`       VARCHAR(100) DEFAULT NULL,
  `kullanici_mesaji` TEXT         DEFAULT NULL,
  `bot_cevabi`       TEXT         DEFAULT NULL,
  `tarih`            TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Quiz sonuçları
CREATE TABLE IF NOT EXISTS `quiz_sonuclari` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `note_id`         INT(11)      NOT NULL,
  `dogru_sayisi`    INT(11)      DEFAULT 0,
  `toplam_soru`     INT(11)      DEFAULT 0,
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kullanici_email` (`kullanici_email`),
  KEY `note_id` (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bildirimler
CREATE TABLE IF NOT EXISTS `bildirimler` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `baslik`          VARCHAR(255) DEFAULT NULL,
  `mesaj`           TEXT         DEFAULT NULL,
  `gonderen`        VARCHAR(100) DEFAULT NULL,
  `okundu`          TINYINT(1)   DEFAULT 0,
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kullanici_email` (`kullanici_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Yer imleri
CREATE TABLE IF NOT EXISTS `yer_imleri` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `note_id`         INT(11)      NOT NULL,
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_note` (`kullanici_email`, `note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Etiketler
CREATE TABLE IF NOT EXISTS `etiketler` (
  `id`      INT(11)      NOT NULL AUTO_INCREMENT,
  `note_id` INT(11)      NOT NULL,
  `etiket`  VARCHAR(60)  NOT NULL,
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  KEY `etiket` (`etiket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Yorumlar
CREATE TABLE IF NOT EXISTS `yorumlar` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `note_id`         INT(11)      NOT NULL,
  `kullanici_email` VARCHAR(255) DEFAULT NULL,
  `kullanici_ad`    VARCHAR(100) DEFAULT NULL,
  `icerik`          TEXT         DEFAULT NULL,
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Beğeniler
CREATE TABLE IF NOT EXISTS `begeniler` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `note_id`         INT(11)      NOT NULL,
  `kullanici_email` VARCHAR(255) NOT NULL,
  `tip`             ENUM('like','dislike') DEFAULT 'like',
  `tarih`           TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `note_user` (`note_id`, `kullanici_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
