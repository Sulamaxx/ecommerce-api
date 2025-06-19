# ************************************************************
# Sequel Ace SQL dump
# Version 20094
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: localhost (MySQL 8.0.28)
# Database: mr_beard
# Generation Time: 2025-06-19 7:57:15 PM +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table cache
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table cache_locks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cache_locks`;

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table carts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `carts`;

CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_foreign` (`user_id`),
  KEY `carts_product_id_foreign` (`product_id`),
  CONSTRAINT `carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;

INSERT INTO `carts` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`)
VALUES
	(1,1,1,10,'2025-04-26 08:08:48','2025-04-26 08:11:58'),
	(45,2,1,2,'2025-05-21 16:25:52','2025-05-21 16:25:52'),
	(50,5,13,1,'2025-06-18 16:38:34','2025-06-18 16:38:34'),
	(51,5,8,1,'2025-06-18 16:38:40','2025-06-18 16:38:40');

/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table failed_jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table job_batches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `job_batches`;

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table migrations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;

INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES
	(12,'0001_01_01_000000_create_users_table',1),
	(13,'0001_01_01_000001_create_cache_table',1),
	(14,'0001_01_01_000002_create_jobs_table',1),
	(15,'2025_04_18_151851_create_personal_access_tokens_table',1),
	(16,'2025_04_18_152543_create_products_table',1),
	(17,'2025_04_18_152544_create_carts_table',1),
	(18,'2025_04_18_152544_create_orders_table',1),
	(19,'2025_04_18_152923_create_product_images_table',1),
	(20,'2025_04_18_154346_create_order_items_table',1),
	(21,'2025_04_18_154618_add_new_data_to_user_table',1),
	(22,'2025_04_18_163611_add_mobile_to_user_table',1),
	(23,'2025_04_26_154618_add_new_data_to_orders_table',2),
	(24,'2025_05_09_145619_add_user_type_to_user_table',3),
	(25,'2025_05_17_00000_create_ password_reset_tokens_table',4);

/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table order_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `total` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `total`, `discount`, `created_at`, `updated_at`)
VALUES
	(1,6,3,2,4000.00,800.00,'2025-04-26 18:40:06','2025-04-26 18:40:06'),
	(2,7,3,2,4000.00,800.00,'2025-04-26 18:53:26','2025-04-26 18:53:26'),
	(3,7,1,1,2000.00,400.00,'2025-04-26 18:53:26','2025-04-26 18:53:26'),
	(4,8,1,3,6000.00,1200.00,'2025-04-27 10:49:17','2025-04-27 10:49:17'),
	(5,8,7,4,8000.00,1600.00,'2025-04-27 10:49:17','2025-04-27 10:49:17'),
	(6,8,4,2,4000.00,800.00,'2025-04-27 10:49:17','2025-04-27 10:49:17'),
	(7,9,1,1,2000.00,400.00,'2025-04-27 10:50:21','2025-04-27 10:50:21'),
	(8,10,7,1,2000.00,400.00,'2025-04-27 14:34:25','2025-04-27 14:34:25'),
	(9,10,1,1,2000.00,400.00,'2025-04-27 14:34:25','2025-04-27 14:34:25'),
	(10,11,1,1,2000.00,400.00,'2025-04-27 15:05:30','2025-04-27 15:05:30'),
	(11,11,7,1,2000.00,400.00,'2025-04-27 15:05:30','2025-04-27 15:05:30'),
	(12,12,13,1,2000.00,400.00,'2025-04-27 15:47:49','2025-04-27 15:47:49'),
	(13,13,8,1,1600.00,400.00,'2025-04-28 02:58:57','2025-04-28 02:58:57'),
	(14,13,14,1,1600.00,400.00,'2025-04-28 02:58:57','2025-04-28 02:58:57'),
	(15,14,7,2,3200.00,800.00,'2025-04-29 11:50:14','2025-04-29 11:50:14'),
	(16,14,8,1,1600.00,400.00,'2025-04-29 11:50:14','2025-04-29 11:50:14'),
	(17,15,1,2,3200.00,800.00,'2025-04-29 11:54:18','2025-04-29 11:54:18'),
	(18,15,8,1,1600.00,400.00,'2025-04-29 11:54:18','2025-04-29 11:54:18'),
	(19,16,7,1,1600.00,400.00,'2025-04-29 12:05:18','2025-04-29 12:05:18'),
	(20,17,7,1,1600.00,400.00,'2025-04-29 12:12:18','2025-04-29 12:12:18'),
	(21,18,8,1,1600.00,400.00,'2025-04-29 12:14:41','2025-04-29 12:14:41'),
	(22,18,13,1,1600.00,400.00,'2025-04-29 12:14:41','2025-04-29 12:14:41'),
	(23,19,18,2,4500.00,500.00,'2025-04-30 12:33:07','2025-04-30 12:33:07'),
	(24,20,18,1,2250.00,250.00,'2025-05-01 01:45:30','2025-05-01 01:45:30'),
	(25,20,13,1,1600.00,400.00,'2025-05-01 01:45:30','2025-05-01 01:45:30'),
	(26,21,18,1,2250.00,250.00,'2025-05-01 02:00:31','2025-05-01 02:00:31'),
	(27,22,7,1,1600.00,400.00,'2025-05-01 04:40:34','2025-05-01 04:40:34'),
	(28,23,13,1,1600.00,400.00,'2025-05-01 04:53:06','2025-05-01 04:53:06'),
	(29,24,13,1,1600.00,400.00,'2025-05-01 04:57:21','2025-05-01 04:57:21'),
	(30,25,1,1,1600.00,400.00,'2025-05-01 04:58:03','2025-05-01 04:58:03'),
	(31,26,2,1,1600.00,400.00,'2025-05-01 05:03:09','2025-05-01 05:03:09'),
	(32,27,16,2,3200.00,800.00,'2025-05-01 05:05:39','2025-05-01 05:05:39'),
	(33,27,18,1,2250.00,250.00,'2025-05-01 05:05:39','2025-05-01 05:05:39'),
	(34,27,15,1,1600.00,400.00,'2025-05-01 05:05:39','2025-05-01 05:05:39'),
	(35,28,3,1,1600.00,400.00,'2025-05-01 05:17:44','2025-05-01 05:17:44'),
	(36,28,18,2,4500.00,500.00,'2025-05-01 05:17:44','2025-05-01 05:17:44'),
	(37,28,16,1,1600.00,400.00,'2025-05-01 05:17:44','2025-05-01 05:17:44'),
	(38,28,13,1,1600.00,400.00,'2025-05-01 05:17:44','2025-05-01 05:17:44'),
	(39,29,1,2,3200.00,800.00,'2025-05-02 15:46:39','2025-05-02 15:46:39'),
	(40,29,18,1,2250.00,250.00,'2025-05-02 15:46:39','2025-05-02 15:46:39'),
	(41,30,1,2,3200.00,800.00,'2025-05-21 16:45:48','2025-05-21 16:45:48'),
	(42,30,7,1,1600.00,400.00,'2025-05-21 16:45:48','2025-05-21 16:45:48'),
	(43,30,8,1,1600.00,400.00,'2025-05-21 16:45:48','2025-05-21 16:45:48'),
	(44,30,18,1,2250.00,250.00,'2025-05-21 16:45:48','2025-05-21 16:45:48');

/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table orders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `shipping_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `shipping_rate` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apartment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_foreign` (`user_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;

INSERT INTO `orders` (`id`, `user_id`, `shipping_address`, `payment_method`, `status`, `total`, `discount`, `tax`, `shipping_rate`, `created_at`, `updated_at`, `first_name`, `last_name`, `country`, `company`, `address`, `apartment`, `city`, `state`, `postal_code`, `phone`)
VALUES
	(6,2,X'4E6F2E3133332C204B616E647920526F61642C2047616D70616861','COD','processing',4302.00,200.00,2.00,500.00,'2025-04-26 18:40:06','2025-04-26 18:40:06','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(7,2,X'4E6F2E3133332C204B616E647920526F61642C2047616D70616861','COD','processing',6302.00,200.00,2.00,500.00,'2025-04-26 18:53:26','2025-04-26 18:53:26','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(8,2,X'4E6F2E3133332C2044656C6B616E646861204A756E6374696F6E2C204D616861726167616D61','COD','processing',18302.00,200.00,2.00,500.00,'2025-04-27 10:49:17','2025-04-27 11:17:41','David','Clork','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Western Province','80300','0762002003'),
	(9,2,X'4E6F2E3133332C204B616E647920526F61642C2047616D70616861','COD','processing',2302.00,200.00,2.00,500.00,'2025-04-27 10:50:21','2025-04-27 10:50:21','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(10,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','COD','canceled',4005.00,0.00,0.00,5.00,'2025-04-27 14:34:25','2025-06-16 15:41:01','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(11,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','CARD','delivered',3205.00,0.00,0.00,5.00,'2025-04-27 15:05:30','2025-04-29 08:36:05','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(12,2,X'4E6F2E38312F412C2047616C6C6520526F61642C204B616C75746172612E','COD','processing',1605.00,0.00,0.00,5.00,'2025-04-27 15:47:49','2025-04-27 15:47:49','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(13,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','COD','shipped',3700.00,800.00,0.00,500.00,'2025-04-28 02:58:57','2025-04-29 08:36:38','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(14,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','CARD','delivered',1700.00,3200.00,0.00,500.00,'2025-04-29 11:50:14','2025-04-30 12:20:12','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(15,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','CARD','processing',1700.00,3200.00,0.00,500.00,'2025-04-29 11:54:18','2025-04-29 11:54:18','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(16,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','COD','shipped',900.00,-3198000.00,0.00,500.00,'2025-04-29 12:05:18','2025-04-30 12:20:03','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(17,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','COD','processing',2100.00,400.00,0.00,500.00,'2025-04-29 12:12:18','2025-04-29 12:12:18','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(18,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','COD','delivered',3700.00,800.00,0.00,500.00,'2025-04-29 12:14:41','2025-04-30 12:31:00','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(19,2,X'4E4F2E38322F20412047616C6C6520526F61642C20436F6C6F6D626F2030332E','COD','shipped',5000.00,500.00,0.00,500.00,'2025-04-30 12:33:07','2025-04-30 12:34:23','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(20,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','COD','processing',4350.00,650.00,0.00,500.00,'2025-05-01 01:45:30','2025-05-01 01:45:30','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(21,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','CARD','processing',2750.00,250.00,0.00,500.00,'2025-05-01 02:00:31','2025-05-01 02:00:31','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(22,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','COD','processing',2100.00,400.00,0.00,500.00,'2025-05-01 04:40:34','2025-05-01 04:40:34','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(23,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','CARD','processing',2100.00,400.00,0.00,500.00,'2025-05-01 04:53:06','2025-05-01 04:53:06','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(24,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','CARD','processing',2100.00,400.00,0.00,500.00,'2025-05-01 04:57:21','2025-05-01 04:57:21','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(25,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','CARD','processing',2100.00,400.00,0.00,500.00,'2025-05-01 04:58:03','2025-05-01 04:58:03','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(26,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','CARD','processing',2100.00,400.00,0.00,500.00,'2025-05-01 05:03:09','2025-05-01 05:03:09','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(27,2,X'4E6F2E2038332F4120476F616420526F61642C20436F6C6F6D626F2033','COD','shipped',7550.00,1450.00,0.00,500.00,'2025-05-01 05:05:39','2025-05-21 16:51:38','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(28,2,X'4E6F2E3132382C2047616C6C6520526F61642C20416D62616C616E676F6461','COD','processing',9800.00,1700.00,0.00,500.00,'2025-05-01 05:17:44','2025-06-16 15:34:41','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(29,2,X'4E6F2E2033342C20436F6C6F6D626F2033','COD','shipped',5950.00,1050.00,0.00,500.00,'2025-05-02 15:46:39','2025-06-18 16:04:22','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003'),
	(30,3,X'4E6F2E2032332C2047616C6C6520526F61642C','COD','shipped',9150.00,1850.00,0.00,500.00,'2025-05-21 16:45:48','2025-06-18 16:26:24','Senura','Chamod','Sri Lanka',NULL,'No. 23, Galle Road,','Ambalangoda','Ambalangoda','Sothern Province','80200','0762002349');

/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table password_reset_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`)
VALUES
	('sula1@gmail.com','$2y$12$E.dsx.ntJ1KL1vrmpTmsB.KOngPTH/JzpGONNxIqqFWSBaQZWJCSC','2025-05-17 17:33:18');

/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table personal_access_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `personal_access_tokens`;

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`)
VALUES
	(1,'App\\Models\\User',1,'auth_token','41a80579e1128fc018011fbb1d3817e433a95132676d6d2eabcfeb1167d74271','[\"*\"]',NULL,NULL,'2025-04-19 18:29:16','2025-04-19 18:29:16'),
	(2,'App\\Models\\User',1,'auth_token','65e0f0738c9fe5fb3ece88c64a639642a6536968a757862ff45bab0d596dc1c7','[\"*\"]','2025-05-17 14:50:47',NULL,'2025-04-19 18:53:32','2025-05-17 14:50:47'),
	(3,'App\\Models\\User',1,'auth_token','fe3e21f33c9e68ee5cc7893a23016f121a18a998b8be0a298daf8ab6104316f4','[\"*\"]',NULL,NULL,'2025-04-19 19:14:34','2025-04-19 19:14:34'),
	(4,'App\\Models\\User',1,'auth_token','db70adbb029d5eb6c20c21afbe509f2c5f8ab78d238e7ed7bc6b46d2b762ffb7','[\"*\"]',NULL,NULL,'2025-04-19 20:04:07','2025-04-19 20:04:07'),
	(5,'App\\Models\\User',1,'auth_token','2b6d8995e985802fff3bd38c79d81a79b3c93652cc8368edc7d7cdc6922cf2cb','[\"*\"]',NULL,NULL,'2025-04-19 20:04:40','2025-04-19 20:04:40'),
	(6,'App\\Models\\User',1,'auth_token','56d6385845d8a1ca2ece7b527b143ca906cecc02a55794dc887fcbad850d9ff7','[\"*\"]',NULL,NULL,'2025-04-19 20:04:54','2025-04-19 20:04:54'),
	(7,'App\\Models\\User',1,'auth_token','b14e2c74542e236d35965bab7e346d6fd2bc7ece79884288d0c9002642e2902a','[\"*\"]','2025-04-19 23:25:20',NULL,'2025-04-19 23:02:15','2025-04-19 23:25:20'),
	(8,'App\\Models\\User',1,'auth_token','3f2cfd1815879b7f6e8eae0bd70a51affb057d9d09f60edd179ba0cb8d1fff25','[\"*\"]',NULL,NULL,'2025-04-19 23:25:48','2025-04-19 23:25:48'),
	(9,'App\\Models\\User',1,'auth_token','2a14eee6faf9f83e2c4aba51ffda9d719f5e690e6a10ff8265c7ec5cc5b0458f','[\"*\"]',NULL,NULL,'2025-04-23 13:46:53','2025-04-23 13:46:53'),
	(10,'App\\Models\\User',1,'auth_token','14afe8e703137889d45b9ee2a38f02a0f00f00a58b73b82477311ff7132fc6e7','[\"*\"]',NULL,NULL,'2025-04-23 13:48:43','2025-04-23 13:48:43'),
	(11,'App\\Models\\User',2,'auth_token','bd060681110271a1888760657e0fb6581593bbab8daea84a046592b3dbdcf257','[\"*\"]',NULL,NULL,'2025-04-23 21:37:42','2025-04-23 21:37:42'),
	(12,'App\\Models\\User',2,'auth_token','6435b7fce1edb8137e292b32d01606a9cc9a1856b7b5a785c92cd5ad4e072239','[\"*\"]','2025-04-23 23:25:38',NULL,'2025-04-23 21:37:42','2025-04-23 23:25:38'),
	(13,'App\\Models\\User',3,'auth_token','5bc3f9c3d565ef68eff2a69f758c357ec9df077355a3af22907b3ced1ec2cf10','[\"*\"]',NULL,NULL,'2025-04-23 23:29:16','2025-04-23 23:29:16'),
	(14,'App\\Models\\User',3,'auth_token','c43b2364b099fbd6d642f22b019cdda4f1773d6f8520a467ab94af18594683e2','[\"*\"]','2025-04-23 23:35:34',NULL,'2025-04-23 23:29:16','2025-04-23 23:35:34'),
	(15,'App\\Models\\User',3,'auth_token','83d909c47bc34bb7c0eedf977b0093805f231e0456a9a262163943c850cfb4c9','[\"*\"]',NULL,NULL,'2025-04-24 11:00:27','2025-04-24 11:00:27'),
	(16,'App\\Models\\User',3,'auth_token','1e212183d2775beb83b11ced57efdc528bafd93fb7b645ce3bc13020ab45d9da','[\"*\"]',NULL,NULL,'2025-04-24 11:00:42','2025-04-24 11:00:42'),
	(17,'App\\Models\\User',3,'auth_token','66c8dceee33ca920c6bcf9543f7b02ba141f2026b3cdb23e6b1fcacb97c4dc23','[\"*\"]','2025-04-26 15:18:57',NULL,'2025-04-24 11:02:50','2025-04-26 15:18:57'),
	(18,'App\\Models\\User',1,'auth_token','c7f6756a7cf938751e7ac4674ce00287c277894817bc2a5befb604b5b605e2c3','[\"*\"]','2025-04-26 08:11:58',NULL,'2025-04-26 08:06:06','2025-04-26 08:11:58'),
	(19,'App\\Models\\User',2,'auth_token','6fd3a5444a43529e44b1f0dc6ba76c932f8059e60e7c6db664e0e5f4e78fa3f8','[\"*\"]','2025-04-26 09:59:53',NULL,'2025-04-26 08:13:26','2025-04-26 09:59:53'),
	(20,'App\\Models\\User',2,'auth_token','44fa7d78121219248a92fafb7954927d34946b3286df788a8e0040aaa19ab153','[\"*\"]','2025-04-28 08:05:02',NULL,'2025-04-26 10:01:59','2025-04-28 08:05:02'),
	(21,'App\\Models\\User',4,'auth_token','9ab6799dda38571234831f33e0e9f585c82d0c222af687726e315e1043920783','[\"*\"]',NULL,NULL,'2025-04-26 17:26:55','2025-04-26 17:26:55'),
	(22,'App\\Models\\User',2,'auth_token','81f8d80030c31e4604be2523bdf9d0b9f9588daa265867b2aeb619bdca94cbf3','[\"*\"]','2025-04-27 18:32:48',NULL,'2025-04-27 05:02:33','2025-04-27 18:32:48'),
	(23,'App\\Models\\User',2,'auth_token','bca9fc51b6352c7fbfe9b57b45d629810e7677a942717d6c255ee2d5c30aa7f6','[\"*\"]','2025-05-02 15:44:06',NULL,'2025-04-27 18:43:15','2025-05-02 15:44:06'),
	(24,'App\\Models\\User',2,'auth_token','e5981dfbe3f567fe57fd4497e0b8829089c553a4deee7ce4b3249fd7434e40f5','[\"*\"]','2025-05-02 17:28:19',NULL,'2025-05-02 15:45:28','2025-05-02 17:28:19'),
	(25,'App\\Models\\User',2,'auth_token','04fc8e536450f92af1dc9e16bb574b504d959fde1db008865c17e8e144cc494b','[\"*\"]','2025-05-09 16:39:04',NULL,'2025-05-09 15:13:47','2025-05-09 16:39:04'),
	(26,'App\\Models\\User',4,'auth_token','77ca40f131d995f6343f44a9fb7d8a3da7f4a863011ade08a3b5689430342161','[\"*\"]',NULL,NULL,'2025-05-17 12:39:16','2025-05-17 12:39:16'),
	(27,'App\\Models\\User',2,'auth_token','b00f2b80e92608947506db3345efe2bf6a5edb7c6a239f8d9e99b91866c3ef1c','[\"*\"]','2025-05-17 12:42:16',NULL,'2025-05-17 12:40:19','2025-05-17 12:42:16'),
	(28,'App\\Models\\User',2,'auth_token','1539d7c7f4bf195d719dbb8c68830c14d49c2a777ffda1c830b27024a881344b','[\"*\"]',NULL,NULL,'2025-05-17 13:15:56','2025-05-17 13:15:56'),
	(29,'App\\Models\\User',2,'auth_token','2c6393fe5727209573d6e0664d38c6d9c3ff2964afe8d748e1a11a6187f20863','[\"*\"]',NULL,NULL,'2025-05-17 13:17:25','2025-05-17 13:17:25'),
	(30,'App\\Models\\User',2,'auth_token','89c3c5c571ebdcea2a1406a5d985be6457c426ddf95d6889564941ec236922ed','[\"*\"]',NULL,NULL,'2025-05-17 13:19:27','2025-05-17 13:19:27'),
	(31,'App\\Models\\User',2,'auth_token','d45484928acf50b4ef1703e7522f607fcc3c6607124dcf7b262eb75f7c1f4b9e','[\"*\"]',NULL,NULL,'2025-05-17 13:20:32','2025-05-17 13:20:32'),
	(32,'App\\Models\\User',2,'auth_token','698c1218f128e5ee009e3ee4e2e751609908ae83b0bb9c3bf56a90135592db90','[\"*\"]',NULL,NULL,'2025-05-17 13:21:25','2025-05-17 13:21:25'),
	(33,'App\\Models\\User',1,'auth_token','ea1f788d7537193d44c542a4e55ff3334563dce79a103aaf98119b80b03718ef','[\"*\"]',NULL,NULL,'2025-05-17 13:21:35','2025-05-17 13:21:35'),
	(34,'App\\Models\\User',2,'auth_token','69e058aef47f0f24e67170c2699f1cc0cd34f55670e7db9f70ae8eb9e747ed26','[\"*\"]',NULL,NULL,'2025-05-17 13:25:07','2025-05-17 13:25:07'),
	(35,'App\\Models\\User',2,'auth_token','e1d308addfc6a13ca1e226e3990685b46edc73ed42453c14e812c5b1c98f6bf7','[\"*\"]',NULL,NULL,'2025-05-17 13:25:53','2025-05-17 13:25:53'),
	(36,'App\\Models\\User',1,'auth_token','1d7c8fbd57bdbb76ae7f2bd3643dbe36fc6fc83c96114d2b48a27edb5359c4a1','[\"*\"]',NULL,NULL,'2025-05-17 13:27:32','2025-05-17 13:27:32'),
	(37,'App\\Models\\User',2,'auth_token','fcc75e32a2badc87a81e59b6b983840dd422a636c73f4157adae1b8ec9763cae','[\"*\"]',NULL,NULL,'2025-05-17 13:27:56','2025-05-17 13:27:56'),
	(38,'App\\Models\\User',2,'auth_token','7aa9b0df85bf93be3ceb5f19c0d1fde7cbe9ce816c0212eea92ea29c8e16b083','[\"*\"]',NULL,NULL,'2025-05-17 13:30:47','2025-05-17 13:30:47'),
	(39,'App\\Models\\User',4,'auth_token','d8dada5a05dd973913ef9877db3ac59c8959551459d857046cb063b067f8d463','[\"*\"]',NULL,NULL,'2025-05-17 13:31:50','2025-05-17 13:31:50'),
	(40,'App\\Models\\User',2,'auth_token','46658a85a31bcc801b34631860f9b693618c21c7246a6f55666791c397f929f2','[\"*\"]','2025-05-17 13:38:23',NULL,'2025-05-17 13:38:23','2025-05-17 13:38:23'),
	(41,'App\\Models\\User',1,'auth_token','af1ecf520ab1e3baea7f4140709767247b8fa48a922634beb8883530739d9668','[\"*\"]','2025-05-17 13:41:19',NULL,'2025-05-17 13:40:09','2025-05-17 13:41:19'),
	(42,'App\\Models\\User',1,'auth_token','7b16b0322edf57f0a3c0438b14e2c7496325e16cd479a59130241dd4485463ef','[\"*\"]',NULL,NULL,'2025-05-17 13:45:46','2025-05-17 13:45:46'),
	(43,'App\\Models\\User',2,'auth_token','61630c7ee9f0c1f547649783bff1617fe2bf8842f9ac309105dcb968095fe0fb','[\"*\"]','2025-05-17 13:56:26',NULL,'2025-05-17 13:45:54','2025-05-17 13:56:26'),
	(44,'App\\Models\\User',2,'auth_token','51a74bf6b17ce914b9713a322ec5d9a62300a40571958d306597ef88ce1b36af','[\"*\"]','2025-05-17 14:00:23',NULL,'2025-05-17 14:00:23','2025-05-17 14:00:23'),
	(45,'App\\Models\\User',2,'auth_token','0f69736067a57221b3735dcf5baf8aeeb2d4dbad55d509b0639a2e9abf140b20','[\"*\"]','2025-05-17 14:03:59',NULL,'2025-05-17 14:03:59','2025-05-17 14:03:59'),
	(46,'App\\Models\\User',2,'auth_token','1930ad64fc5d25cb13ef38202a471f1c8cd06383c07776b4f54a9e47497f80ec','[\"*\"]','2025-05-17 14:04:04',NULL,'2025-05-17 14:04:04','2025-05-17 14:04:04'),
	(47,'App\\Models\\User',1,'auth_token','3dae44a86dc657bea68491146a4a35934aceeb709a2f4b61d3b890381f754754','[\"*\"]',NULL,NULL,'2025-05-17 14:04:16','2025-05-17 14:04:16'),
	(48,'App\\Models\\User',2,'auth_token','6853a87658f6850cfcb78252e330dd64417ad8f36c369a6a737a832ca710f562','[\"*\"]','2025-05-17 14:07:26',NULL,'2025-05-17 14:05:15','2025-05-17 14:07:26'),
	(49,'App\\Models\\User',2,'auth_token','5825cb95dd4ad235b381efaf3e138b08d0faca3cdbf280be6bd344830520316e','[\"*\"]','2025-05-17 14:54:38',NULL,'2025-05-17 14:45:32','2025-05-17 14:54:38'),
	(50,'App\\Models\\User',2,'auth_token','ecbefcb70bf5e0c32bf608a88a745d4fe1e31fde10999bbaeb4241f88562461c','[\"*\"]','2025-05-17 15:25:42',NULL,'2025-05-17 14:57:08','2025-05-17 15:25:42'),
	(51,'App\\Models\\User',2,'auth_token','7dd3509e0d540e59ab2fb757cfacd93ba42d0d27eae5151df47b98be01e46311','[\"*\"]','2025-05-17 15:25:47',NULL,'2025-05-17 15:25:47','2025-05-17 15:25:47'),
	(52,'App\\Models\\User',1,'auth_token','51a015cd86edf3c9c172ab6762ac9c915bc9072f506be495ffe1c8ebeff03b87','[\"*\"]','2025-05-17 15:26:16',NULL,'2025-05-17 15:26:09','2025-05-17 15:26:16'),
	(53,'App\\Models\\User',2,'auth_token','f4aa5551f1a8cff3feab210bb4793d020090bf74ce874589e5bf0effe516af66','[\"*\"]','2025-05-17 15:51:23',NULL,'2025-05-17 15:51:23','2025-05-17 15:51:23'),
	(54,'App\\Models\\User',3,'auth_token','6f1fbfb58374207622ec2ef02f3e30921a3b7520d94bc0e7db01142a7617c499','[\"*\"]',NULL,NULL,'2025-05-17 17:45:20','2025-05-17 17:45:20'),
	(55,'App\\Models\\User',3,'auth_token','52045a173b4aa7ebbb0cce130460fe9ee51ecf4b32277bc0610cc6cf900fa65a','[\"*\"]',NULL,NULL,'2025-05-17 18:11:21','2025-05-17 18:11:21'),
	(56,'App\\Models\\User',2,'auth_token','eda6997ac56e6759da0a31e9b74814453b467f06590c0d01f9732cbe501515cd','[\"*\"]','2025-05-17 18:11:44',NULL,'2025-05-17 18:11:43','2025-05-17 18:11:44'),
	(57,'App\\Models\\User',2,'auth_token','0a404712ad0891eb24d6e7e34535a109280068ff84dbb5405face22e68f70482','[\"*\"]','2025-05-17 18:12:28',NULL,'2025-05-17 18:12:28','2025-05-17 18:12:28'),
	(58,'App\\Models\\User',2,'auth_token','bf1c246b62d18b7d88f85b8156c3e82e1ad0464992546e6c50d6f423f2f9980e','[\"*\"]','2025-05-21 16:25:52',NULL,'2025-05-17 18:15:38','2025-05-21 16:25:52'),
	(59,'App\\Models\\User',3,'auth_token','1198778c344720f5bc34544207aa86ca28dc3f68249961596f892b36fd70965e','[\"*\"]','2025-05-21 16:46:06',NULL,'2025-05-21 16:29:08','2025-05-21 16:46:06'),
	(60,'App\\Models\\User',2,'auth_token','7aa51843d842e745c9a2e4511e375db557bfbcf86897ed2469b49f97646c94e7','[\"*\"]','2025-05-21 17:17:36',NULL,'2025-05-21 16:48:22','2025-05-21 17:17:36'),
	(61,'App\\Models\\User',1,'auth_token','0e275ad5c28ab8a31fab9da09d65b1fdf03cb2c71bf1090953e6ce99950f06bf','[\"*\"]',NULL,NULL,'2025-05-28 16:24:16','2025-05-28 16:24:16'),
	(62,'App\\Models\\User',2,'auth_token','bd6516a25c565e13647c01cbb4b8236556bbdadb0991d783272e5fada2c0e300','[\"*\"]','2025-06-16 17:08:15',NULL,'2025-06-15 20:08:21','2025-06-16 17:08:15'),
	(63,'App\\Models\\User',2,'auth_token','b5e200fe93ad533e529de5bbfa40c6bf29d79deef27d448a2e502e3f4dfaeffc','[\"*\"]','2025-06-17 12:27:30',NULL,'2025-06-16 17:08:23','2025-06-17 12:27:30'),
	(64,'App\\Models\\User',5,'auth_token','8d4aab43f984c496bfe8448281472342a4309e85d0d51643cc7fc7247dea4026','[\"*\"]',NULL,NULL,'2025-06-17 12:40:58','2025-06-17 12:40:58'),
	(65,'App\\Models\\User',5,'auth_token','a8a618edee62316ae885f4338631f73fc9cd7c6854d4d1de9a22454c3d7323b3','[\"*\"]','2025-06-17 14:13:14',NULL,'2025-06-17 12:40:58','2025-06-17 14:13:14'),
	(66,'App\\Models\\User',6,'auth_token','cc8028d45e0e6e2cb6a18c3becc382e20176f415d0c90e4b01be9b9f86e533ba','[\"*\"]',NULL,NULL,'2025-06-17 14:05:55','2025-06-17 14:05:55'),
	(67,'App\\Models\\User',5,'auth_token','62fb69026befa7c99fb9a5918c79d5fd9b147d4ca0e48bbfbd566e92a394a684','[\"*\"]',NULL,NULL,'2025-06-17 14:13:18','2025-06-17 14:13:18'),
	(68,'App\\Models\\User',5,'auth_token','8012a6c79374ebda245cab4a3e739a96006c18a01ac361840e7036289fc70c4f','[\"*\"]',NULL,NULL,'2025-06-17 14:13:46','2025-06-17 14:13:46'),
	(69,'App\\Models\\User',2,'auth_token','43eadb481c59c2b4b9b955a20deaa19bdd58f8b336c78b33789730dbb19f9b16','[\"*\"]','2025-06-18 10:35:54',NULL,'2025-06-17 14:13:56','2025-06-18 10:35:54'),
	(70,'App\\Models\\User',2,'auth_token','8ef3c2a8560f6346a53bfca3cec2e5cfdcff0feacda7cd71ea8616c4f2d3f629','[\"*\"]','2025-06-18 10:37:33',NULL,'2025-06-18 10:37:24','2025-06-18 10:37:33'),
	(71,'App\\Models\\User',8,'auth_token','b7c9d0b334dda6a1adf421967bdbc4f5a259c90d57e86f25bb2f298e1e504f3e','[\"*\"]',NULL,NULL,'2025-06-18 10:38:43','2025-06-18 10:38:43'),
	(72,'App\\Models\\User',8,'auth_token','b307e576819ca81e115839cc5e35e0a7ebd4b4d4e459f2d4c1bf0d12bb4acb9a','[\"*\"]',NULL,NULL,'2025-06-18 10:43:16','2025-06-18 10:43:16'),
	(73,'App\\Models\\User',8,'auth_token','2a21466e29dfb627ecd8dd6ef8b13161fbe2bc52f7f5d9901e35f3e4b3043a18','[\"*\"]',NULL,NULL,'2025-06-18 10:43:40','2025-06-18 10:43:40'),
	(74,'App\\Models\\User',8,'auth_token','c13fd44d5500e405ed235e92066e139655e17e85e2ab4c5e0948cee2b3fc491a','[\"*\"]',NULL,NULL,'2025-06-18 10:44:17','2025-06-18 10:44:17'),
	(75,'App\\Models\\User',8,'auth_token','66bb1b6da4a0b95ace66fa8c7693b92ff18e43fb8f010bd9eb75f33f2da4cc0e','[\"*\"]',NULL,NULL,'2025-06-18 10:44:33','2025-06-18 10:44:33'),
	(76,'App\\Models\\User',2,'auth_token','81ac2faa1e7b6d1b4b6bf93c1036097e278c3ea13c649aa1b2b65685a3b5a2f1','[\"*\"]','2025-06-18 10:57:44',NULL,'2025-06-18 10:49:56','2025-06-18 10:57:44'),
	(77,'App\\Models\\User',8,'auth_token','d9afdfa84410c0ade67f61f7084decbfb3b3c8ceb114316560c5f4a59544fab8','[\"*\"]','2025-06-18 11:04:56',NULL,'2025-06-18 10:57:58','2025-06-18 11:04:56'),
	(78,'App\\Models\\User',2,'auth_token','f5c744378b236a32f5972333922fd8a6671d39eeac4efe52658fb5aa70801b24','[\"*\"]','2025-06-18 11:07:37',NULL,'2025-06-18 11:05:11','2025-06-18 11:07:37'),
	(79,'App\\Models\\User',8,'auth_token','9fd3106b5daf98df93a3d086201ac7394485957f74d9939b917a34f98635dc30','[\"*\"]','2025-06-18 11:17:06',NULL,'2025-06-18 11:07:56','2025-06-18 11:17:06'),
	(80,'App\\Models\\User',2,'auth_token','12a87ba9fb017ed9b131b65c5e21ea2185fde67a2180f44bd03c896ea4fd1bc7','[\"*\"]','2025-06-18 11:18:05',NULL,'2025-06-18 11:17:14','2025-06-18 11:18:05'),
	(81,'App\\Models\\User',2,'auth_token','9b27cdba015af386ee1380836ad98cf0c8baf0386112f2357ea35b1a824fd55a','[\"*\"]','2025-06-18 11:18:10',NULL,'2025-06-18 11:18:10','2025-06-18 11:18:10'),
	(82,'App\\Models\\User',8,'auth_token','d2e0085d9c28ea5ae8bd7224fa12f8c80418c8aadfff8e0ff111455f9f76369c','[\"*\"]','2025-06-18 15:44:02',NULL,'2025-06-18 11:18:25','2025-06-18 15:44:02'),
	(83,'App\\Models\\User',2,'auth_token','8d6248e45fe384868c50917d4ad396df4521a150d36153ace42fc1eeee4657dc','[\"*\"]','2025-06-18 15:45:43',NULL,'2025-06-18 15:45:39','2025-06-18 15:45:43'),
	(84,'App\\Models\\User',8,'auth_token','f91011c2550c41785ff1e211051b4603b67a97e78cd09981ff584f224dfebd16','[\"*\"]','2025-06-18 16:27:37',NULL,'2025-06-18 15:45:48','2025-06-18 16:27:37'),
	(85,'App\\Models\\User',8,'auth_token','99782992fc7313d6d9fa58e7ffaa2d2509bf5ef47b3d8e6b8bd4d42b66134dc7','[\"*\"]','2025-06-18 16:37:14',NULL,'2025-06-18 16:37:14','2025-06-18 16:37:14'),
	(86,'App\\Models\\User',5,'auth_token','525e0e8753f0e98ce40826ed6d19da6417a142b34545d3b5bc3f7b4879836b9c','[\"*\"]','2025-06-19 01:52:02',NULL,'2025-06-18 16:37:26','2025-06-19 01:52:02'),
	(87,'App\\Models\\User',2,'auth_token','7766130856186f682b473080f5290cae4fd73e1ece62cf839e49ad1cb10de09e','[\"*\"]','2025-06-19 03:09:48',NULL,'2025-06-19 03:05:48','2025-06-19 03:09:48'),
	(88,'App\\Models\\User',5,'auth_token','7023ac3ec6a3b8e645944303df1d17467c03fedee9a20f3fe5135b662e8a8fe0','[\"*\"]',NULL,NULL,'2025-06-19 03:10:21','2025-06-19 03:10:21'),
	(89,'App\\Models\\User',2,'auth_token','60869eccedf5be5cdd7bf2d2ca1e8a07ee532e098aca9aa3636a2b5b37dc8de5','[\"*\"]','2025-06-19 13:49:20',NULL,'2025-06-19 13:49:08','2025-06-19 13:49:20'),
	(90,'App\\Models\\User',5,'auth_token','8e6de70c8b138cb66da3b6daea50c577173b65d8c69a7be04c856a883ae2c714','[\"*\"]','2025-06-19 14:45:51',NULL,'2025-06-19 14:08:56','2025-06-19 14:45:51'),
	(91,'App\\Models\\User',2,'auth_token','e960c2d0abff0bb9c23225bc87e1cf92a02c29375c5ca0aaae3e6e8eab0ea2da','[\"*\"]','2025-06-19 14:46:08',NULL,'2025-06-19 14:46:05','2025-06-19 14:46:08'),
	(92,'App\\Models\\User',5,'auth_token','0c8ae084a70a119c8222c4963c7ff3a6b3da28e8e0a176e9fd61dc6b412dfdbc','[\"*\"]',NULL,NULL,'2025-06-19 18:47:45','2025-06-19 18:47:45'),
	(93,'App\\Models\\User',2,'auth_token','78038543d20dd3759deb8cb0f65e43714a2bbda0e5766d2d4e8427354862f92e','[\"*\"]','2025-06-19 19:03:49',NULL,'2025-06-19 18:47:54','2025-06-19 19:03:49'),
	(94,'App\\Models\\User',5,'auth_token','3ea0f4cf7d7a29b5d57dd272b72dbd183feeb8f520f513195c6773f9a9c86206','[\"*\"]',NULL,NULL,'2025-06-19 19:04:16','2025-06-19 19:04:16');

/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table product_images
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_images`;

CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;

INSERT INTO `product_images` (`id`, `product_id`, `path`, `created_at`, `updated_at`)
VALUES
	(1,1,'product_images/UTZyqIcignHC1N5itR2rpOA9NgoBQzK6Ejv93bG0.jpg','2025-04-19 16:29:06','2025-04-19 16:29:06'),
	(2,1,'product_images/hWJBKKiNl4xEAaeDbJPTn86l1PeMbk86bKv1g84T.jpg','2025-04-19 16:29:06','2025-04-19 16:29:06'),
	(3,1,'product_images/pSkqvPPm2ig5MtVjTjuzeiLSf04hyUeFulueR1Pe.jpg','2025-04-19 16:29:06','2025-04-19 16:29:06'),
	(4,2,'product_images/eBEU9XXOs8qDXnhmz2Fs5gJ7POqaif438tKNu5cZ.jpg','2025-04-19 16:30:21','2025-04-19 16:30:21'),
	(5,2,'product_images/mU6HMTbkYgrwI65CzG0Q6qhJXDzcLsUcq80ezcgg.jpg','2025-04-19 16:30:21','2025-04-19 16:30:21'),
	(6,2,'product_images/H58samNqNuT21Ga4bcDXM7IhQ1y6ID0lY0L9Vl8L.jpg','2025-04-19 16:30:21','2025-04-19 16:30:21'),
	(7,3,'product_images/IqTU7V1sbYUtSzfWVAj3cSc8AzQijBYNOMX2VOmh.jpg','2025-04-19 16:30:35','2025-04-19 16:30:35'),
	(8,3,'product_images/gXwlNEdjfVkqabWfml6wtXGzO4lYIXRU46qHoKpP.jpg','2025-04-19 16:30:35','2025-04-19 16:30:35'),
	(9,3,'product_images/Gkr8OcUNrmgneHEhY9Jj6mEBC9YuK4NLkz5kVsde.jpg','2025-04-19 16:30:35','2025-04-19 16:30:35'),
	(10,4,'product_images/rYvNA5ym9JWU82wojr4UhedPM93vy5OmKHmOKrhv.jpg','2025-04-19 16:31:18','2025-04-19 16:31:18'),
	(11,4,'product_images/VhIhIBjHheAlwG8raFrxhgb1kqzuqRQSvbCqVSav.jpg','2025-04-19 16:31:18','2025-04-19 16:31:18'),
	(12,4,'product_images/WgbnmNA98fcxj8D0hSlgeKqfsmwFvfpPm3KroBOK.jpg','2025-04-19 16:31:18','2025-04-19 16:31:18'),
	(13,5,'product_images/IP1dAZa8zRGOCIraqmRd7fGT2FAliyRdADjn4FL7.jpg','2025-04-19 16:33:53','2025-04-19 16:33:53'),
	(14,5,'product_images/e0XKabBmvOPgSPLGwxUwjI3yBrZ6luGPkZQT8pMR.jpg','2025-04-19 16:33:53','2025-04-19 16:33:53'),
	(15,5,'product_images/bhvEzcnzdXirAUDSuqvFnKWwBV9ClgjvTUoQv9tO.jpg','2025-04-19 16:33:53','2025-04-19 16:33:53'),
	(16,6,'product_images/aW8plzQEZ0WYmWowXG9mYaLitoTMt5s7t8sLt59X.jpg','2025-04-19 16:35:36','2025-04-19 16:35:36'),
	(17,6,'product_images/cYpMN6gYN2jo68mXUfrO1QBcXUiUEi7yoESmTW0y.jpg','2025-04-19 16:35:36','2025-04-19 16:35:36'),
	(18,6,'product_images/c5K8D4uJcJvi1PtGpV8t8SlMgjSrKtIZwZvAqfCH.jpg','2025-04-19 16:35:36','2025-04-19 16:35:36'),
	(19,7,'product_images/ClIVbbY0NRHq9mmqjbIkYfgCp10m6twP7ieEg0Jv.jpg','2025-04-19 16:40:08','2025-04-19 16:40:08'),
	(20,7,'product_images/vDTOqDfsMYZys6Qq91YMkb0fBbR5V1qVKA1SO4AN.jpg','2025-04-19 16:40:08','2025-04-19 16:40:08'),
	(21,7,'product_images/l8j36tU99AfsdwqGeUEeV5WbrjNd0RPOccMS6vBl.jpg','2025-04-19 16:40:08','2025-04-19 16:40:08'),
	(22,8,'product_images/jNZNRXkrH1VSvRtegO08OVhPbCp6ugMNOXGg5iAT.jpg','2025-04-19 16:41:02','2025-04-19 16:41:02'),
	(23,8,'product_images/3AZXFfA6MXODrPvM6PofkUCLBG48r5wIzchzi3Ej.jpg','2025-04-19 16:41:02','2025-04-19 16:41:02'),
	(25,9,'product_images/YQ2l0FgmhP1sR6bF6vRzeuyBtcJomhXLzRlBnR6m.jpg','2025-04-19 16:41:22','2025-04-19 16:41:22'),
	(26,9,'product_images/HnzMVnuZN4qyj23xMdW4d8VKquMwBabTTdGffIc0.jpg','2025-04-19 16:41:22','2025-04-19 16:41:22'),
	(27,9,'product_images/o1qoWGfPOyJtfMMX5Ed0uTDxFZjYJ21tqwWCCxkV.jpg','2025-04-19 16:41:22','2025-04-19 16:41:22'),
	(28,10,'product_images/190ykzD3ypY2il9W0Mpu9yvbaytAk0cu2xz5z4s2.jpg','2025-04-19 16:41:47','2025-04-19 16:41:47'),
	(29,10,'product_images/s496IBgzwPibBgmrpd5BkV9Ywt7xdXvAnUOEwtdV.jpg','2025-04-19 16:41:47','2025-04-19 16:41:47'),
	(30,10,'product_images/zgSCZLa0jhhAoyzKpBpwZ9kTZsyLFRFkAKt6SQK3.jpg','2025-04-19 16:41:47','2025-04-19 16:41:47'),
	(31,11,'product_images/3m2eGdtRLy2F8EUEio5okaxiXEbm2OslzPOEtOTx.jpg','2025-04-19 17:44:50','2025-04-19 17:44:50'),
	(32,11,'product_images/MHlBEUjJwJOzQzEJlhJrwGGoOy66HmNInwZSZSde.jpg','2025-04-19 17:44:50','2025-04-19 17:44:50'),
	(33,11,'product_images/2JphRLwGd5dsctBiGQYu686kHIPJBblClLi1UO1W.jpg','2025-04-19 17:44:50','2025-04-19 17:44:50'),
	(34,12,'product_images/3vy8h9Ec5i2hNZ6QGHcMzfwXxAxvPHmOja3Zxb1s.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(35,12,'product_images/tVpABh55feeg3beaJacXhhSpUZ4ra43nhigItA9P.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(36,12,'product_images/P2PRxMDxv7F8TT5ivgJscm73Kcvq7UBbIgIqU5Pw.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(37,13,'product_images/yLjJKiD1czCMmEBKmW1ToxRKQQ8oMyaysSD3l0du.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(38,13,'product_images/uH2EhNiqydomd1OmRpCBkyLxL7rNFsqRiAqn3IUu.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(39,13,'product_images/WFg65664k7dkRCVh2UMTR5P3xaQgBV8e7JBk2mNb.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(40,14,'product_images/5IhXlvdV8wfTnq2KCCxOY6c20iFfHCIDKxL0Vh8C.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(41,14,'product_images/GC5E4ZVqBggOjeSVyx2TWF2D3S3Ae77xRr4E6EBD.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(42,14,'product_images/fht2xo7fl93U00BIwrcMRsndmQuHc6T09lvL6L2Y.jpg','2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(43,15,'product_images/AXHeHemrz8Q9ALPxLdNP2yvk9PAZ95i33TXH6GUX.jpg','2025-04-19 17:44:52','2025-04-19 17:44:52'),
	(44,15,'product_images/vlCM9UCmgrlgifbHTm2YmQhbQsvjZNllEIM6xD1H.jpg','2025-04-19 17:44:52','2025-04-19 17:44:52'),
	(45,15,'product_images/onSpmuSOAIDqTHLwxTrIDzhmaGnoSpW74plV6BAM.jpg','2025-04-19 17:44:52','2025-04-19 17:44:52'),
	(46,16,'product_images/HzGo8QaSelCTzp7uzDz2EIahAjFR3t3phaQ5zvHV.png','2025-04-29 10:28:19','2025-04-29 10:28:19'),
	(47,16,'product_images/Z5XNhZ8XHRsLK3Bjyj77PRJKXR783NgofWVTIVYN.jpg','2025-04-29 10:28:19','2025-04-29 10:28:19'),
	(48,16,'product_images/XDjneHjOnxF8tFx7ZsFZJXG3DpgmgsXc2oM0Ufgo.jpg','2025-04-29 10:28:19','2025-04-29 10:28:19'),
	(49,17,'product_images/9WeJv54NaQP1EGy6k2ZCgPM2JJ0CpfO4LwmHy5Ho.png','2025-04-29 10:36:18','2025-04-29 10:36:18'),
	(50,8,'product_images/AjJKne5GQjEClzwtWlKh9gBVgugGUJM3c1zMe3vk.jpg','2025-04-30 10:42:04','2025-04-30 10:42:04'),
	(51,18,'product_images/oFjADRSxax7aPvsSeuZhwoc7o279jD9Hi7cwkE8k.jpg','2025-04-30 12:25:44','2025-04-30 12:25:44'),
	(53,18,'product_images/yX4mONfFmznKJXPj2SQX8J2hvn1YwWYy7wCMYQk9.jpg','2025-04-30 12:25:44','2025-04-30 12:25:44'),
	(54,18,'product_images/oQgCjoTaaty2Z7sB4Y2uMnyplHocsRkREp0Mvppx.jpg','2025-04-30 12:30:30','2025-04-30 12:30:30'),
	(55,19,'product_images/ZICZ220bRK7S1ZL7WPd6eWK8zXsVdW63kA5czW95.jpg','2025-05-21 16:57:59','2025-05-21 16:57:59'),
	(56,19,'product_images/OdpYYGLykV8YjCSMp0YhDqQd8q6gbRbDfEOhWzxr.jpg','2025-05-21 16:57:59','2025-05-21 16:57:59'),
	(57,19,'product_images/NowQb29hlUpASAzwbJh7OACXR1PmVi81rYGMyjbM.jpg','2025-05-21 16:57:59','2025-05-21 16:57:59'),
	(58,20,'product_images/kN7EJARQfQb6bmTNaMMrqvtsViyns28FMvrORjLy.jpg','2025-06-19 18:57:35','2025-06-19 18:57:35'),
	(59,20,'product_images/z9J9DUYDZdyX2Xhi6TJ4nqrbTgUj5qNRMSJYHtVN.jpg','2025-06-19 18:57:35','2025-06-19 18:57:35'),
	(60,20,'product_images/pQoNQ7rLXlvtyBY77vkUsJtzm8pAf1cAryRyh1Vb.jpg','2025-06-19 18:57:35','2025-06-19 18:57:35');

/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table products
# ------------------------------------------------------------

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_guide_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',
  `is_new` tinyint(1) NOT NULL DEFAULT '1',
  `rating` int NOT NULL DEFAULT '5',
  `initial_stock` int NOT NULL,
  `stock` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;

INSERT INTO `products` (`id`, `name`, `user_guide_pdf`, `description`, `price`, `discount`, `category`, `currency`, `is_new`, `rating`, `initial_stock`, `stock`, `created_at`, `updated_at`)
VALUES
	(1,'Beard Oil',NULL,'✔ Size: 30ml (Lasts 2-3 months) \r\n✔ Smell: Apple Fragrance\r\n✔ 100% Made in Sri Lanka\r\n✔ Ingredients: Castor Oil, Virgin Coconut Oil, Vitamin E, Blackseed Oil, Avocado Oil, Almond Oil, Jojoba Oil \r\n✔ 100% Natural | No Artificial Additives',2000.00,20.00,'Beard','LKR',1,5,100,89,'2025-04-19 16:29:06','2025-06-19 03:08:47'),
	(2,'Beard Oil',NULL,'✔ Size: 30ml (Lasts 2-3 months) \r\n✔ Smell: Apple Fragrance\r\n✔ 100% Made in Sri Lanka\r\n✔ Ingredients: Castor Oil, Virgin Coconut Oil, Vitamin E, Blackseed Oil, Avocado Oil, Almond Oil, Jojoba Oil \r\n✔ 100% Natural | No Artificial Additives',2000.00,20.00,'Beard','LKR',1,5,100,99,'2025-04-19 16:30:21','2025-06-19 03:06:53'),
	(3,'Beard Oil',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Beard','LKR',1,5,100,97,'2025-04-19 16:30:35','2025-05-01 05:17:44'),
	(4,'Beard Oil',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Beard','LKR',1,5,100,98,'2025-04-19 16:31:18','2025-04-27 10:49:17'),
	(5,'Beard Oil',NULL,'✔ Size: 30ml (Lasts 2-3 months) \r\n✔ Smell: Apple Fragrance\r\n✔ 100% Made in Sri Lanka\r\n✔ Ingredients: Castor Oil, Virgin Coconut Oil, Vitamin E, Blackseed Oil, Avocado Oil, Almond Oil, Jojoba Oil \r\n✔ 100% Natural | No Artificial Additives',2000.00,20.00,'Beard','LKR',1,5,100,100,'2025-04-19 16:33:53','2025-06-19 03:09:28'),
	(6,'Beard Oil',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Beard','LKR',1,5,100,100,'2025-04-19 16:35:36','2025-04-19 16:35:36'),
	(7,'Hair Oil',NULL,'✔ Size: 30ml (Lasts 2-3 months) \r\n✔ Smell: Apple Fragrance\r\n✔ 100% Made in Sri Lanka\r\n✔ Ingredients: Castor Oil, Virgin Coconut Oil, Vitamin E, Blackseed Oil, Avocado Oil, Almond Oil, Jojoba Oil \r\n✔ 100% Natural | No Artificial Additives',2000.00,20.00,'Hair','LKR',1,5,100,90,'2025-04-19 16:40:08','2025-06-19 03:09:46'),
	(8,'Hair Oil',NULL,'✔ Size: 30ml (Lasts 2-3 months) \r\n✔ Smell: Apple Fragrance\r\n✔ 100% Made in Sri Lanka\r\n✔ Ingredients: Castor Oil, Virgin Coconut Oil, Vitamin E, Blackseed Oil, Avocado Oil, Almond Oil, Jojoba Oil \r\n✔ 100% Natural | No Artificial Additives',2000.00,20.00,'Hair','LKR',1,5,100,96,'2025-04-19 16:41:02','2025-06-19 03:09:38'),
	(9,'Hair Oil',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Hair','LKR',1,5,100,100,'2025-04-19 16:41:22','2025-04-19 16:41:22'),
	(10,'Beard Oil',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Beard','LKR',1,5,100,100,'2025-04-19 16:41:47','2025-04-19 16:41:47'),
	(11,'Beard Oil',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Beard','LKR',1,5,100,100,'2025-04-19 17:44:50','2025-04-19 17:44:50'),
	(12,'Beard Oil',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Beard','LKR',1,5,100,100,'2025-04-19 17:44:51','2025-04-19 17:44:51'),
	(13,'Hair Comb',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Accessories','LKR',1,5,100,94,'2025-04-19 17:44:51','2025-05-01 05:17:44'),
	(14,'Hair Comb',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Accessories','LKR',1,5,100,99,'2025-04-19 17:44:51','2025-04-28 02:58:57'),
	(15,'Hair Comb',NULL,'Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world. Best Beard Oil in the world.',2000.00,20.00,'Accessories','LKR',1,5,100,97,'2025-04-19 17:44:52','2025-05-01 05:05:39'),
	(16,'Hair Cream 345','product_guides/ZKrtuGa6VSeWht56FLXx9cOOVfaJ9a4QfQADNaYV.pdf','Hair Cream to make your hair super cool. 345677 test Update',2000.00,20.00,'Hair','LKR',1,5,200,1997,'2025-04-29 10:28:19','2025-05-01 05:17:44'),
	(17,'Test Product',NULL,'Test Product Test Product Test Product Test Product Test Product',1000.00,10.00,'Beard','LKR',1,5,20,20,'2025-04-29 10:36:18','2025-04-29 10:36:18'),
	(18,'Hair Gel 2','product_guides/cQf9cB7In8Lnv5HeZsvn7hxF0Ue7bmGg1ptlnI3b.pdf','Best Hair Product in the world. Test Update',2500.00,10.00,'Hair','LKR',1,5,200,194,'2025-04-30 12:25:44','2025-06-18 16:26:22'),
	(19,'Beard Wax','product_guides/PdvVH1FpmZ1UezMjFnVI96Jv2cQdyW50baSfK8sL.pdf','Best Beard Wax in the world',2000.00,15.00,'Beard','LKR',1,5,200,200,'2025-05-21 16:57:59','2025-05-21 16:57:59'),
	(20,'Polo T-shirt',NULL,'POlo T-shirts are the most elegant and comfortable t-shirt s in the Sri Lankan Market.',3000.00,2.00,'Apparel','LKR',1,5,20,20,'2025-06-19 18:57:35','2025-06-19 18:57:35');

/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apartment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `first_name`, `last_name`, `country`, `company`, `address`, `apartment`, `city`, `state`, `postal_code`, `phone`, `mobile`, `user_type`)
VALUES
	(1,'Sulochana','sula@gmail.com',NULL,'$2y$12$E2sAeMtxXGNd1H2T/wgUgOIbPjkDpxmH51SyN.iLKls4jhah3Me7m',NULL,'2025-04-19 18:29:16','2025-04-19 18:29:16',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0769865321','customer'),
	(2,'Senoo','senooedu@gmail.com',NULL,'$2y$12$XB16SzQ/yFpqMK1NV40EYuZL1QQ3YAUwrK0wKmLAg8UM6j1o3Pi.u',NULL,'2025-04-23 21:37:42','2025-06-15 20:07:53','Senura','Chamod','Sri Lanka',NULL,'No.128, Galle Road, Ambalangoda',NULL,'Ambalangoda','Southern Province','80300','0762002003','0761231234','admin'),
	(3,'Senura','kpsenurachamod1@gmail.com',NULL,'$2y$12$gZ7u5GxrW2EF3ufUMSSKK.m5tsiP3uKSqHqNNytwRDoH3/1/2wR8m',NULL,'2025-04-23 23:29:16','2025-05-21 16:45:12','Senura','Chamod','Sri Lanka',NULL,'No. 23, Galle Road,','Ambalangoda','Ambalangoda','Sothern Province','80200','0762002349','0762002007','customer'),
	(4,'Sulochana','sula1@gmail.com',NULL,'$2y$12$FUydGUUu/YTEDMgVUKUl8epTuR2EEij3G3GTJzJoBfUcDaF4aKuN.',NULL,'2025-04-26 17:26:55','2025-04-26 17:26:55',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0769865321','customer'),
	(5,'Senura1','senoocoding@gmail.com',NULL,'$2y$12$nxKNjFeF2yEV/NGz7MV1sOOxSv/8xzj/anX2FnUB6KbEnh3dWkgUS',NULL,'2025-06-17 12:40:58','2025-06-17 12:40:58',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0766084567','customer'),
	(6,'Sula','sula2@gmail.com',NULL,'$2y$12$HvY1Q2Spl8ikoRBpaejOWeivW41oh/CH15Q5GacAdYkhcvDizsm9q',NULL,'2025-06-17 14:05:55','2025-06-18 11:17:26','Sula','Max',NULL,'Not provided',NULL,NULL,NULL,NULL,NULL,NULL,'0769865321','staff'),
	(7,'Sanath','sanath123@gmail.com',NULL,'$2y$12$QlxVX.fZH2.WCAz4na7tKuQmiOsVtkrQ.SKndmsGL1TInb4qj46/q',NULL,'2025-06-17 18:25:13','2025-06-18 11:17:48','Sanath','Nandasiri',NULL,'PromtExpress',NULL,NULL,NULL,NULL,NULL,NULL,'0778008002','staff'),
	(8,'Jessy','jessmrq123@gmail.com',NULL,'$2y$12$dFAdJKmDA/1k.K6voqL1LOxg3B.YAucVyHwMecPFOzmeiuFTgGqby',NULL,'2025-06-17 19:00:40','2025-06-18 11:17:57','Jessy','Marq',NULL,'Koombiyo Delivery',NULL,NULL,NULL,NULL,NULL,NULL,'0785205203','staff');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
