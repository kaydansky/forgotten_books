/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(249) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `verified` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `resettable` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `roles_mask` int(10) unsigned NOT NULL DEFAULT '0',
  `registered` int(10) unsigned NOT NULL,
  `last_login` int(10) unsigned DEFAULT NULL,
  `force_logout` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users_confirmations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `email` varchar(249) COLLATE utf8mb4_unicode_ci NOT NULL,
  `selector` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `email_expires` (`email`,`expires`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users_remembered` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `selector` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users_resets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `selector` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `user_expires` (`user`,`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users_throttling` (
  `bucket` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `tokens` float unsigned NOT NULL,
  `replenished_at` int(10) unsigned NOT NULL,
  `expires_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`bucket`),
  KEY `expires_at` (`expires_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `workers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_pay_rate` decimal(13,2) unsigned NOT NULL DEFAULT '0',
  `bonuses_applied` decimal(13,2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pibn` int(10) unsigned NOT NULL,
  `isbn` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by_user` int(10) unsigned DEFAULT NULL,
  `requested_by_user` int(10) unsigned DEFAULT NULL,
  `queue_number` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `added` int(10) unsigned DEFAULT NULL,
  `assigned` int(10) unsigned DEFAULT NULL,
  `completed` int(10) unsigned DEFAULT NULL,
  `title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `volumes_total` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `word_count` int(10) unsigned NOT NULL DEFAULT '0',
  `images_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pages_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `missing_pages` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blurb` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supervisor_attention` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `coordinator_attention` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `too_many_errors` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `has_images` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pibn` (`pibn`),
  KEY `added_by_user` (`added_by_user`),
  KEY `requested_by_user` (`requested_by_user`),
  KEY `added` (`added`),
  KEY `assigned` (`assigned`),
  KEY `completed` (`completed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;