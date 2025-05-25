-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 25 May 2025, 15:43:51
-- Sunucu sürümü: 9.1.0
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `cinema_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Aksiyon', 'Aksiyon filmleri'),
(2, 'Komedi', 'Komedi filmleri'),
(3, 'Drama', 'Drama filmleri'),
(4, 'Bilim Kurgu', 'Bilim kurgu filmleri'),
(5, 'Korku', 'Korku filmleri');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `movies`
--

CREATE TABLE `movies` (
  `movie_id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `duration` int NOT NULL,
  `release_date` date DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `poster_url` varchar(255) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `status` enum('active','coming_soon','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Tablo döküm verisi `movies`
--

INSERT INTO `movies` (`movie_id`, `title`, `description`, `duration`, `release_date`, `category_id`, `poster_url`, `trailer_url`, `status`) VALUES
(1, 'The Matrix 2', 'Rüyaların derinliklerine inen profesyonel bir hırsızın, kurumsal casusluk dünyasında son bir işi. Zihnin en savunmasız olduğu rüya anında, bilinçaltının en değerli sırları çalınabilir mi? Christopher Nolanın yazıp yönettiği film, rüya içinde rüya konseptini benzersiz bir görsel şölenle sunuyor. Karmaşık hikayesi ve etkileyici görsel efektleriyle sinema tarihinin en önemli bilim kurgu filmlerinden biri.', 215, '2023-12-31', 1, 'uploads/poster_682a558b5ee04.jpg', '', 'active'),
(2, 'Inception', 'Rüyaların derinliklerine inen profesyonel bir hırsızın, kurumsal casusluk dünyasında son bir işi. Zihnin en savunmasız olduğu rüya anında, bilinçaltının en değerli sırları çalınabilir mi? Christopher Nolanın yazıp yönettiği film, rüya içinde rüya konseptini benzersiz bir görsel şölenle sunuyor. Karmaşık hikayesi ve etkileyici görsel efektleriyle sinema tarihinin en önemli bilim kurgu filmlerinden biri.', 210, '2025-12-31', 2, 'uploads/movie-4.jpg', '', 'active'),
(3, 'The Warrious', 'Rüyaların derinliklerine inen profesyonel bir hırsızın, kurumsal casusluk dünyasında son bir işi. Zihnin en savunmasız olduğu rüya anında, bilinçaltının en değerli sırları çalınabilir mi? Christopher Nolanın yazıp yönettiği film, rüya içinde rüya konseptini benzersiz bir görsel şölenle sunuyor. Karmaşık hikayesi ve etkileyici görsel efektleriyle sinema tarihinin en önemli bilim kurgu filmlerinden biri.', 210, '2025-12-31', 2, 'uploads/movie-5.jpg', '', 'archived'),
(4, 'Jhon Wick', 'Film aciklamasi...', 85, '2025-12-31', 4, 'uploads/movie-3.jpg', '', 'archived'),
(5, 'La casa de Papel', 'Bir hırsızlık filmi...', 195, '2025-05-22', 1, 'uploads/poster_682a5761252da.jpg', '', 'active');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payments`
--

CREATE TABLE `payments` (
  `payment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `card_number` varchar(19) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `card_full_name` varchar(255) NOT NULL,
  `card_expiration_date` varchar(5) NOT NULL,
  `card_CVV` varchar(3) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Tablo döküm verisi `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `card_number`, `card_full_name`, `card_expiration_date`, `card_CVV`, `total_amount`) VALUES
(1, 1, '1458 9654 1256 9875', 'deneme kullanici', '12/26', '564', 99.98),
(2, 1, '8745 8965 4123 6547', 'veb kullanicisi', '09/26', '124', 99.98),
(3, 1, '8965 4181 8185 6589', 'cankiri karatekin', '06/18', '365', 183.96),
(4, 1, '4569 8745 2236 5896', 'kart kullanicisi', '12/26', '789', 149.97),
(5, 1, '4569 8745 2236 5896', 'kart kullanicisi', '12/26', '789', 149.97),
(6, 1, '5698 9745 6123 4678', 'cankiri karatekin', '16/09', '239', 199.96),
(7, 1, '4569 8789 4654 3213', 'merhaba dünya', '16/29', '325', 199.98),
(8, 4, '4567 8945 6123 4658', 'user user', '12/29', '123', 450.00);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int NOT NULL,
  `movie_id` int DEFAULT NULL,
  `theater_id` int DEFAULT NULL,
  `session_date` date NOT NULL,
  `session_time` time NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('available','full','cancelled') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Tablo döküm verisi `sessions`
--

INSERT INTO `sessions` (`session_id`, `movie_id`, `theater_id`, `session_date`, `session_time`, `price`, `status`) VALUES
(1, 1, 1, '2025-12-31', '14:30:00', 45.99, 'available'),
(2, 1, 1, '2025-12-31', '16:30:00', 45.99, 'available'),
(3, 1, 1, '2025-12-31', '17:30:00', 45.99, 'available'),
(4, 1, 2, '2025-12-31', '20:30:00', 49.99, 'available'),
(5, 2, 1, '2025-11-05', '15:30:00', 99.99, 'available'),
(6, 5, 3, '2025-05-22', '18:00:00', 150.00, 'available');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `theaters`
--

CREATE TABLE `theaters` (
  `theater_id` int NOT NULL,
  `theater_name` varchar(50) NOT NULL,
  `capacity` int NOT NULL,
  `theater_type` enum('2D','3D','IMAX') DEFAULT '2D',
  `status` enum('active','maintenance','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Tablo döküm verisi `theaters`
--

INSERT INTO `theaters` (`theater_id`, `theater_name`, `capacity`, `theater_type`, `status`) VALUES
(1, 'Salon 1', 100, '2D', 'active'),
(2, 'Salon 2', 120, '3D', 'active'),
(3, 'Salon 3', 150, 'IMAX', 'active');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int NOT NULL,
  `session_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `payment_id` int NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `purchase_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','used','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Tablo döküm verisi `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `session_id`, `user_id`, `payment_id`, `seat_number`, `purchase_date`, `status`) VALUES
(4, 4, 1, 5, '1-2', '2025-05-18 09:49:18', 'active'),
(5, 4, 1, 5, '1-3', '2025-05-18 09:49:18', 'active'),
(6, 4, 1, 5, '1-4', '2025-05-18 09:49:18', 'active'),
(7, 4, 1, 6, '6-4', '2025-05-18 19:45:16', 'active'),
(8, 4, 1, 6, '6-5', '2025-05-18 19:45:16', 'active'),
(9, 4, 1, 6, '6-6', '2025-05-18 19:45:16', 'active'),
(10, 4, 1, 6, '6-7', '2025-05-18 19:45:16', 'active'),
(11, 5, 1, 7, '9-5', '2025-05-18 20:14:57', 'active'),
(12, 5, 1, 7, '9-6', '2025-05-18 20:14:57', 'active'),
(13, 6, 4, 8, '1-1', '2025-05-19 17:28:53', 'active'),
(14, 6, 4, 8, '1-2', '2025-05-19 17:28:53', 'active'),
(15, 6, 4, 8, '1-3', '2025-05-19 17:28:53', 'active');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `phone`, `full_name`, `role`, `created_at`) VALUES
(1, 'deneme', '$2y$10$BIGAN9xMorrQfWG9GU.XaeNVSh13NErGFt/2GRocWGBMfpxjKkANK', 'deneme@g.com', '5478965412', 'Deneme Deneme', 'user', '2025-05-17 19:13:35'),
(3, 'admin', '$2y$10$ol8OuTycD2VXA0sZ9iSQzOcq8.AL42PSpTvnPkeCkjzxappZIXra.', 'admin@gmail.com', '5478946513', 'Admin Admin', 'admin', '2025-05-18 21:02:53'),
(4, 'user', '$2y$10$fbkXQaLz91dw6bW4gEaupupfVj5tifnAY5RDZfA8ZOEFKxT5bEqhq', 'user@gmail.com', '5896541256', 'user user', 'user', '2025-05-19 17:27:58');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Tablo için indeksler `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Tablo için indeksler `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Tablo için indeksler `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `theater_id` (`theater_id`);

--
-- Tablo için indeksler `theaters`
--
ALTER TABLE `theaters`
  ADD PRIMARY KEY (`theater_id`);

--
-- Tablo için indeksler `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `theaters`
--
ALTER TABLE `theaters`
  MODIFY `theater_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `movies`
--
ALTER TABLE `movies`
  ADD CONSTRAINT `movies_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Tablo kısıtlamaları `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`),
  ADD CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`theater_id`) REFERENCES `theaters` (`theater_id`);

--
-- Tablo kısıtlamaları `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
