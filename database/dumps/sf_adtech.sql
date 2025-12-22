CREATE DATABASE IF NOT EXISTS `sf_adtech` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sf_adtech`;

DROP TABLE IF EXISTS `clicks`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `offer_topic`;
DROP TABLE IF EXISTS `offers`;
DROP TABLE IF EXISTS `topics`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `role` enum('advertiser','webmaster','admin') NOT NULL DEFAULT 'advertiser',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1,'Admin','admin@example.com','$2y$12$E6Y.CIGHEoopgzGDfvzdK.r/Mg2/PBnMmjUQCd84hItB9b3xJl8Oy','admin',1,NOW(),NOW()),
(2,'Advertiser','advertiser@example.com','$2y$12$E6Y.CIGHEoopgzGDfvzdK.r/Mg2/PBnMmjUQCd84hItB9b3xJl8Oy','advertiser',1,NOW(),NOW()),
(3,'Webmaster','webmaster@example.com','$2y$12$E6Y.CIGHEoopgzGDfvzdK.r/Mg2/PBnMmjUQCd84hItB9b3xJl8Oy','webmaster',1,NOW(),NOW());

CREATE TABLE `topics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `topics_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `topics` (`id`, `name`) VALUES
(1,'Финансы'),(2,'Здоровье'),(3,'Образование'),(4,'Туризм'),(5,'Развлечения');

CREATE TABLE `offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `advertiser_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `price_per_click` decimal(10,2) NOT NULL,
  `target_url` varchar(255) NOT NULL,
  `status` enum('draft','active','inactive') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `offers_advertiser_id_foreign` (`advertiser_id`),
  CONSTRAINT `offers_advertiser_id_foreign` FOREIGN KEY (`advertiser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `offers` (`id`,`advertiser_id`,`name`,`price_per_click`,`target_url`,`status`,`created_at`,`updated_at`) VALUES
(1,2,'Demo Offer',2.50,'https://example.com','active',NOW(),NOW());

CREATE TABLE `offer_topic` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` bigint unsigned NOT NULL,
  `topic_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `offer_topic_offer_id_topic_id_unique` (`offer_id`,`topic_id`),
  KEY `offer_topic_offer_id_foreign` (`offer_id`),
  KEY `offer_topic_topic_id_foreign` (`topic_id`),
  CONSTRAINT `offer_topic_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offer_topic_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `offer_topic` (`offer_id`,`topic_id`) VALUES (1,1),(1,2),(1,3),(1,4),(1,5);

CREATE TABLE `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` bigint unsigned NOT NULL,
  `webmaster_id` bigint unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `webmaster_cpc` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriptions_token_unique` (`token`),
  KEY `subscriptions_webmaster_offer_index` (`webmaster_id`,`offer_id`),
  KEY `subscriptions_offer_id_foreign` (`offer_id`),
  CONSTRAINT `subscriptions_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_webmaster_id_foreign` FOREIGN KEY (`webmaster_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subscriptions` (`id`,`offer_id`,`webmaster_id`,`token`,`webmaster_cpc`,`is_active`,`created_at`,`updated_at`) VALUES
(1,1,3,'demo-token-123',1.50,1,NOW(),NOW());

CREATE TABLE `clicks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint unsigned DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `is_successful` tinyint(1) NOT NULL DEFAULT 0,
  `redirected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `clicks_subscription_id_foreign` (`subscription_id`),
  KEY `clicks_token_index` (`token`),
  KEY `clicks_created_at_index` (`created_at`),
  CONSTRAINT `clicks_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
