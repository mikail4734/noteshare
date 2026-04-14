-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 09 Nis 2026, 23:54:36
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `notdeposu`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `begeni_verileri`
--

CREATE TABLE `begeni_verileri` (
  `note_id` int(11) NOT NULL,
  `likes` int(11) DEFAULT 0,
  `dislikes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `begeni_verileri`
--

INSERT INTO `begeni_verileri` (`note_id`, `likes`, `dislikes`) VALUES
(1, 2, 0),
(2, 1, 0),
(3, 3, 0),
(4, 0, 1),
(5, 1, 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `etkilesim_kayitlari`
--

CREATE TABLE `etkilesim_kayitlari` (
  `id` int(11) NOT NULL,
  `note_id` int(11) DEFAULT NULL,
  `user_ip` varchar(45) DEFAULT NULL,
  `action_type` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `etkilesim_kayitlari`
--

INSERT INTO `etkilesim_kayitlari` (`id`, `note_id`, `user_ip`, `action_type`) VALUES
(1, 4, '::1', 'dislike');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `notes`
--

INSERT INTO `notes` (`id`, `title`, `content`, `created_at`) VALUES
(1, 'v dxvx', '<p>vxdf</p>', '2026-04-05 18:22:45'),
(2, 'ders2', '<p>meraba</p>', '2026-04-05 18:49:01'),
(3, 'csa', '<p>csacdsa</p>', '2026-04-05 18:50:58'),
(4, 'facfzs', '<p>szacfaszfc</p>', '2026-04-06 12:39:18'),
(5, 'swqedxq', '<p>dwedcw</p>', '2026-04-08 11:24:20'),
(6, 'vfdv', '<p>veeevve</p>', '2026-04-09 00:25:43'),
(7, 'vfdv', '<p>veeevve</p>', '2026-04-09 00:25:43');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `reset_code`) VALUES
(1, 'mikailcelik4734@gmail.com', '4747', NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `begeni_verileri`
--
ALTER TABLE `begeni_verileri`
  ADD PRIMARY KEY (`note_id`);

--
-- Tablo için indeksler `etkilesim_kayitlari`
--
ALTER TABLE `etkilesim_kayitlari`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `etkilesim_kayitlari`
--
ALTER TABLE `etkilesim_kayitlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
