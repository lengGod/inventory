-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: gudangku_deploy
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `batches`
--

DROP TABLE IF EXISTS `batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `batch_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `manufactured_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `batches_product_id_batch_number_unique` (`product_id`,`batch_number`),
  KEY `batches_supplier_id_foreign` (`supplier_id`),
  KEY `batches_expiry_date_index` (`expiry_date`),
  CONSTRAINT `batches_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `batches_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batches`
--

LOCK TABLES `batches` WRITE;
/*!40000 ALTER TABLE `batches` DISABLE KEYS */;
INSERT INTO `batches` VALUES (1,1,'B-260904-01',3,'2026-09-04','2027-03-03','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(2,1,'B-261004-02',1,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(3,1,'B-260507-03',2,'2026-05-07','2026-10-29','expired',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(4,2,'B-260904-01',1,'2026-09-04','2027-05-02','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(5,2,'B-261004-02',2,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(6,2,'B-260308-04',1,'2026-03-08','2026-10-04','depleted',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(7,3,'B-260904-01',2,'2026-09-04','2027-03-03','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(8,3,'B-261004-02',3,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(9,4,'B-260904-01',3,'2026-09-04','2027-05-02','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(10,4,'B-261004-02',1,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(11,4,'B-260507-03',2,'2026-05-07','2026-10-29','expired',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(12,5,'B-260904-01',1,'2026-09-04','2027-03-03','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(13,5,'B-261004-02',2,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(14,6,'B-260904-01',2,'2026-09-04','2027-05-02','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(15,6,'B-261004-02',3,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(16,7,'B-260904-01',3,'2026-09-04','2027-03-03','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(17,7,'B-261004-02',1,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(18,8,'B-260904-01',1,'2026-09-04','2027-05-02','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(19,8,'B-261004-02',2,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(20,8,'B-260507-03',3,'2026-05-07','2026-10-29','expired',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(21,10,'B-260904-01',3,'2026-09-04','2027-03-03','active',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(22,10,'B-261004-02',1,'2026-10-04','2026-11-18','near_expiry',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35');
/*!40000 ALTER TABLE `batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Obat-obatan','2026-11-03 04:06:35','2026-11-03 04:06:35'),(2,'Vitamin','2026-11-03 04:06:35','2026-11-03 04:06:35'),(3,'Alat Kesehatan','2026-11-03 04:06:35','2026-11-03 04:06:35'),(4,'Makanan & Minuman','2026-11-03 04:06:35','2026-11-03 04:06:35'),(5,'Perlengkapan Kantor','2026-11-03 04:06:35','2026-11-03 04:06:35'),(6,'Elektronik','2026-11-03 04:06:35','2026-11-03 04:06:35'),(7,'Pakaian','2026-11-03 04:06:35','2026-11-03 04:06:35'),(8,'Produk Rumah Tangga','2026-11-03 04:06:35','2026-11-03 04:06:35');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `label_print_logs`
--

DROP TABLE IF EXISTS `label_print_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `label_print_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint unsigned NOT NULL,
  `copies` int unsigned NOT NULL DEFAULT '1',
  `printed_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label_print_logs_printed_by_foreign` (`printed_by`),
  KEY `label_print_logs_label_type_index` (`label_type`),
  CONSTRAINT `label_print_logs_printed_by_foreign` FOREIGN KEY (`printed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `label_print_logs`
--

LOCK TABLES `label_print_logs` WRITE;
/*!40000 ALTER TABLE `label_print_logs` DISABLE KEYS */;
INSERT INTO `label_print_logs` VALUES (1,'product',1,12,1,'2026-11-03 03:51:37','2026-11-03 04:06:37'),(2,'batch',1,8,2,'2026-11-03 03:36:37','2026-11-03 04:06:37'),(3,'location',1,4,2,'2026-11-03 03:21:37','2026-11-03 04:06:37');
/*!40000 ALTER TABLE `label_print_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locations_code_unique` (`code`),
  UNIQUE KEY `locations_barcode_unique` (`barcode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES (1,'RAK-A1','Rak A Baris 1','LOC001','2026-11-03 04:06:35','2026-11-03 04:06:35'),(2,'RAK-A2','Rak A Baris 2','LOC002','2026-11-03 04:06:35','2026-11-03 04:06:35'),(3,'RAK-B1','Rak B Baris 1','LOC003','2026-11-03 04:06:35','2026-11-03 04:06:35'),(4,'RAK-B2','Rak B Baris 2','LOC004','2026-11-03 04:06:35','2026-11-03 04:06:35'),(5,'ZONA-C','Zona Dingin','LOC005','2026-11-03 04:06:35','2026-11-03 04:06:35');
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_01_045929_create_categories_table',1),(5,'2026_11_01_045941_create_units_table',1),(6,'2026_11_01_045942_create_suppliers_table',1),(7,'2026_11_01_045943_create_locations_table',1),(8,'2026_11_01_045951_create_products_table',1),(9,'2026_11_01_050106_create_batches_table',1),(10,'2026_11_01_050107_create_stocks_table',1),(11,'2026_11_01_050137_create_stock_movements_table',1),(12,'2026_11_01_050138_create_stock_ins_table',1),(13,'2026_11_01_050139_create_stock_outs_table',1),(14,'2026_11_01_050225_create_stock_in_items_table',1),(15,'2026_11_01_050226_create_stock_out_items_table',1),(16,'2026_11_01_050227_create_stock_adjustments_table',1),(17,'2026_11_01_050308_create_stock_adjustment_items_table',1),(18,'2026_11_01_050309_create_label_print_logs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `min_stock` decimal(15,3) NOT NULL DEFAULT '0.000',
  `purchase_price` decimal(15,2) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `track_batch` tinyint(1) NOT NULL DEFAULT '0',
  `track_expiry` tinyint(1) NOT NULL DEFAULT '0',
  `expiry_alert_days` int unsigned DEFAULT '30',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_unit_id_foreign` (`unit_id`),
  KEY `products_sku_index` (`sku`),
  KEY `products_barcode_index` (`barcode`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'VITMK001','8991001234001','Vitamin C 500mg Tablet',2,1,100.000,2500.00,NULL,1,1,30,1,'Suplemen vitamin C 500mg per tablet, kemasan botol 100 tablet.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(2,'VITMK002','8991001234002','Vitamin D3 1000 IU',2,1,40.000,42000.00,NULL,1,1,30,1,'Suplemen vitamin D3 1000 IU kemasan botol 60 kapsul.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(3,'OBAT001','8991001234003','Paracetamol 500mg Tablet',1,1,150.000,1200.00,NULL,1,1,30,1,'Obat penurun panas dan penghilang nyeri, 1 strip = 10 tablet.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(4,'OBAT002','8991001234004','Amoxicillin 500mg Kapsul',1,1,120.000,2500.00,NULL,1,1,30,1,'Antibiotik amoxicillin 500mg, kemasan strip 10 kapsul.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(5,'ALS001','8991001234005','Hand Sanitizer 500ml',3,1,30.000,22000.00,NULL,1,1,60,1,'Desinfektan tangan 500ml, gel alkohol 70%.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(6,'ALS002','8991001234006','Alkohol 70% 1 Liter',3,6,25.000,30000.00,NULL,1,1,60,1,'Cairan antiseptik alkohol 70% kemasan 1 liter.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(7,'MKNN001','8991001234007','Masker Medis 3ply (50 pcs)',3,2,100.000,38000.00,NULL,1,1,45,1,'Masker medis 3 lapis, earloop, kemasan 50 pcs per box.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(8,'MNM001','8991001234008','Air Mineral 600ml',4,3,60.000,55000.00,NULL,1,1,30,1,'Air mineral kemasan 600ml, 1 dus isi 24 botol.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(9,'PRT001','8991001234009','Tisu Gulung',8,3,40.000,27000.00,NULL,0,0,NULL,1,'Tisu gulung isi 10 roll per dus.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(10,'PRT002','8991001234010','Sabun Cair 500ml',8,1,30.000,17000.00,NULL,1,1,60,1,'Sabun cair antibakteri kemasan 500ml.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(11,'KAN001','8991001234011','Kantong Plastik Putih (Kecil)',8,3,200.000,45000.00,NULL,0,0,NULL,1,'1 dus berisi 1000 lembar kantong plastik ukuran 20x30cm.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(12,'ATK001','8991001234012','Pena Gel Hitam',5,2,40.000,18000.00,NULL,0,0,NULL,1,'Pena gel tinta hitam, 1 box isi 12 pcs.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(13,'ELK001','8991001234013','Kabel HDMI 2 Meter',6,1,12.000,70000.00,NULL,0,0,NULL,1,'Kabel HDMI versi 2.0 panjang 2 meter.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(14,'PKN001','8991001234014','Kemeja Seragam',7,1,25.000,110000.00,NULL,0,0,NULL,1,'Kemeja seragam kerja lengan panjang.',NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_adjustment_items`
--

DROP TABLE IF EXISTS `stock_adjustment_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_adjustment_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stock_adjustment_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `location_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `system_qty` decimal(15,3) NOT NULL,
  `physical_qty` decimal(15,3) NOT NULL,
  `diff` decimal(15,3) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_adjustment_items_stock_adjustment_id_foreign` (`stock_adjustment_id`),
  KEY `stock_adjustment_items_product_id_foreign` (`product_id`),
  KEY `stock_adjustment_items_location_id_foreign` (`location_id`),
  KEY `stock_adjustment_items_batch_id_foreign` (`batch_id`),
  CONSTRAINT `stock_adjustment_items_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`),
  CONSTRAINT `stock_adjustment_items_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stock_adjustment_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_adjustment_items_stock_adjustment_id_foreign` FOREIGN KEY (`stock_adjustment_id`) REFERENCES `stock_adjustments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustment_items`
--

LOCK TABLES `stock_adjustment_items` WRITE;
/*!40000 ALTER TABLE `stock_adjustment_items` DISABLE KEYS */;
INSERT INTO `stock_adjustment_items` VALUES (1,1,1,1,1,740.000,745.000,5.000,'Koreksi Sistem','2026-11-03 04:06:36','2026-11-03 04:06:36'),(2,1,2,2,4,140.000,142.000,2.000,'Koreksi Sistem','2026-11-03 04:06:36','2026-11-03 04:06:36'),(3,2,7,2,16,350.000,347.000,-3.000,'Barang Rusak','2026-11-03 04:06:36','2026-11-03 04:06:36'),(4,2,6,3,14,90.000,89.000,-1.000,'Barang Rusak','2026-11-03 04:06:37','2026-11-03 04:06:37'),(5,3,12,3,NULL,55.000,50.000,-5.000,'Koreksi Opname','2026-11-03 04:06:37','2026-11-03 04:06:37'),(6,3,9,4,NULL,20.000,17.000,-3.000,'Koreksi Opname','2026-11-03 04:06:37','2026-11-03 04:06:37'),(7,4,10,4,21,25.000,35.000,10.000,'Koreksi Opname','2026-11-03 04:06:37','2026-11-03 04:06:37');
/*!40000 ALTER TABLE `stock_adjustment_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_adjustments`
--

DROP TABLE IF EXISTS `stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doc_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_adjustments_doc_number_unique` (`doc_number`),
  KEY `stock_adjustments_created_by_foreign` (`created_by`),
  KEY `stock_adjustments_approved_by_foreign` (`approved_by`),
  KEY `stock_adjustments_doc_number_index` (`doc_number`),
  KEY `stock_adjustments_status_index` (`status`),
  CONSTRAINT `stock_adjustments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_adjustments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustments`
--

LOCK TABLES `stock_adjustments` WRITE;
/*!40000 ALTER TABLE `stock_adjustments` DISABLE KEYS */;
INSERT INTO `stock_adjustments` VALUES (1,'ADJ-202609-0001','2026-09-04','posted','Koreksi Sistem',1,1,'2026-09-04 03:00:00','2026-09-04 03:00:00','2026-09-04 03:00:00'),(2,'ADJ-202610-0001','2026-10-01','posted','Barang Rusak',1,1,'2026-10-01 03:00:00','2026-10-01 03:00:00','2026-10-01 03:00:00'),(3,'ADJ-202610-0002','2026-10-24','posted','Koreksi Opname',1,1,'2026-10-24 03:00:00','2026-10-24 03:00:00','2026-10-24 03:00:00'),(4,'ADJ-202611-0001','2026-11-03','draft','Koreksi Opname',1,NULL,NULL,'2026-11-03 03:00:00','2026-11-03 03:00:00');
/*!40000 ALTER TABLE `stock_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_in_items`
--

DROP TABLE IF EXISTS `stock_in_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_in_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stock_in_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `location_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `qty` decimal(15,3) NOT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_in_items_stock_in_id_foreign` (`stock_in_id`),
  KEY `stock_in_items_product_id_foreign` (`product_id`),
  KEY `stock_in_items_location_id_foreign` (`location_id`),
  KEY `stock_in_items_batch_id_foreign` (`batch_id`),
  CONSTRAINT `stock_in_items_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`),
  CONSTRAINT `stock_in_items_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stock_in_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_in_items_stock_in_id_foreign` FOREIGN KEY (`stock_in_id`) REFERENCES `stock_ins` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_in_items`
--

LOCK TABLES `stock_in_items` WRITE;
/*!40000 ALTER TABLE `stock_in_items` DISABLE KEYS */;
INSERT INTO `stock_in_items` VALUES (1,1,1,1,1,400.000,2500.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(2,1,3,1,7,200.000,1200.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(3,1,4,2,9,120.000,2500.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(4,1,5,1,12,48.000,22000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(5,1,7,2,16,150.000,38000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(6,1,11,3,NULL,300.000,45000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(7,2,2,2,4,60.000,42000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(8,2,10,4,21,60.000,17000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(9,2,6,3,14,60.000,30000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(10,2,9,4,NULL,40.000,27000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(11,2,8,3,18,24.000,55000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(12,3,11,3,NULL,400.000,45000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(13,3,12,3,NULL,60.000,18000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(14,3,13,3,NULL,12.000,70000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(15,3,14,4,NULL,30.000,110000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(16,4,1,1,1,500.000,2500.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(17,4,3,1,7,150.000,1200.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(18,4,5,1,12,48.000,22000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(19,4,7,2,16,200.000,38000.00,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(20,4,4,2,9,150.000,2500.00,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(21,5,1,1,2,150.000,2500.00,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(22,5,3,2,8,60.000,1200.00,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(23,5,2,2,4,80.000,42000.00,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(24,5,6,3,14,40.000,30000.00,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(25,5,8,3,19,12.000,55000.00,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(26,6,7,2,17,120.000,38000.00,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(27,6,1,1,3,50.000,2500.00,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(28,6,4,2,11,40.000,2500.00,'2026-11-03 04:06:36','2026-11-03 04:06:36');
/*!40000 ALTER TABLE `stock_in_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_ins`
--

DROP TABLE IF EXISTS `stock_ins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_ins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doc_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_ins_doc_number_unique` (`doc_number`),
  KEY `stock_ins_supplier_id_foreign` (`supplier_id`),
  KEY `stock_ins_created_by_foreign` (`created_by`),
  KEY `stock_ins_doc_number_index` (`doc_number`),
  KEY `stock_ins_status_index` (`status`),
  CONSTRAINT `stock_ins_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_ins_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_ins`
--

LOCK TABLES `stock_ins` WRITE;
/*!40000 ALTER TABLE `stock_ins` DISABLE KEYS */;
INSERT INTO `stock_ins` VALUES (1,'IN-202607-0001','2026-07-06',1,'posted','Pembelian awal untuk stok gudang',1,'2026-07-06 02:00:00','2026-07-06 02:00:00','2026-07-06 02:00:00'),(2,'IN-202608-0001','2026-08-05',2,'posted','Pengadaan produk farmasi dan rumah tangga',1,'2026-08-05 02:00:00','2026-08-05 02:00:00','2026-08-05 02:00:00'),(3,'IN-202609-0001','2026-09-19',3,'posted','Penerimaan barang non-farmasi rutin',1,'2026-09-19 02:00:00','2026-09-19 02:00:00','2026-09-19 02:00:00'),(4,'IN-202610-0001','2026-10-09',1,'posted','Penerimaan rutin bulan ini',1,'2026-10-09 02:00:00','2026-10-09 02:00:00','2026-10-09 02:00:00'),(5,'IN-202610-0002','2026-10-22',2,'posted','Penerimaan suplemen dan obat jangka pendek',1,'2026-10-22 02:00:00','2026-10-22 02:00:00','2026-10-22 02:00:00'),(6,'IN-202610-0003','2026-10-27',3,'posted','Penerimaan campuran disertai penggantian batch lama',1,'2026-10-27 02:00:00','2026-10-27 02:00:00','2026-10-27 02:00:00');
/*!40000 ALTER TABLE `stock_ins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `location_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` decimal(15,3) NOT NULL,
  `balance_after` decimal(15,3) NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_location_id_foreign` (`location_id`),
  KEY `stock_movements_batch_id_foreign` (`batch_id`),
  KEY `stock_movements_created_by_foreign` (`created_by`),
  KEY `stock_movements_product_id_index` (`product_id`),
  KEY `stock_movements_created_at_index` (`created_at`),
  CONSTRAINT `stock_movements_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`),
  CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_movements_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,1,1,'in',400.000,400.000,'StockIn',1,1,'2026-07-06 02:00:00','2026-07-06 02:00:00'),(2,3,1,7,'in',200.000,200.000,'StockIn',1,1,'2026-07-06 02:00:00','2026-07-06 02:00:00'),(3,4,2,9,'in',120.000,120.000,'StockIn',1,1,'2026-07-06 02:00:00','2026-07-06 02:00:00'),(4,5,1,12,'in',48.000,48.000,'StockIn',1,1,'2026-07-06 02:00:00','2026-07-06 02:00:00'),(5,7,2,16,'in',150.000,150.000,'StockIn',1,1,'2026-07-06 02:00:00','2026-07-06 02:00:00'),(6,11,3,NULL,'in',300.000,300.000,'StockIn',1,1,'2026-07-06 02:00:00','2026-07-06 02:00:00'),(7,2,2,4,'in',60.000,60.000,'StockIn',2,1,'2026-08-05 02:00:00','2026-08-05 02:00:00'),(8,10,4,21,'in',60.000,60.000,'StockIn',2,1,'2026-08-05 02:00:00','2026-08-05 02:00:00'),(9,6,3,14,'in',60.000,60.000,'StockIn',2,1,'2026-08-05 02:00:00','2026-08-05 02:00:00'),(10,9,4,NULL,'in',40.000,40.000,'StockIn',2,1,'2026-08-05 02:00:00','2026-08-05 02:00:00'),(11,8,3,18,'in',24.000,24.000,'StockIn',2,1,'2026-08-05 02:00:00','2026-08-05 02:00:00'),(12,11,3,NULL,'in',400.000,700.000,'StockIn',3,1,'2026-09-19 02:00:00','2026-09-19 02:00:00'),(13,12,3,NULL,'in',60.000,60.000,'StockIn',3,1,'2026-09-19 02:00:00','2026-09-19 02:00:00'),(14,13,3,NULL,'in',12.000,12.000,'StockIn',3,1,'2026-09-19 02:00:00','2026-09-19 02:00:00'),(15,14,4,NULL,'in',30.000,30.000,'StockIn',3,1,'2026-09-19 02:00:00','2026-09-19 02:00:00'),(16,1,1,1,'in',500.000,900.000,'StockIn',4,1,'2026-10-09 02:00:00','2026-10-09 02:00:00'),(17,3,1,7,'in',150.000,350.000,'StockIn',4,1,'2026-10-09 02:00:00','2026-10-09 02:00:00'),(18,5,1,12,'in',48.000,96.000,'StockIn',4,1,'2026-10-09 02:00:00','2026-10-09 02:00:00'),(19,7,2,16,'in',200.000,350.000,'StockIn',4,1,'2026-10-09 02:00:00','2026-10-09 02:00:00'),(20,4,2,9,'in',150.000,270.000,'StockIn',4,1,'2026-10-09 02:00:00','2026-10-09 02:00:00'),(21,1,1,2,'in',150.000,150.000,'StockIn',5,1,'2026-10-22 02:00:00','2026-10-22 02:00:00'),(22,3,2,8,'in',60.000,60.000,'StockIn',5,1,'2026-10-22 02:00:00','2026-10-22 02:00:00'),(23,2,2,4,'in',80.000,140.000,'StockIn',5,1,'2026-10-22 02:00:00','2026-10-22 02:00:00'),(24,6,3,14,'in',40.000,100.000,'StockIn',5,1,'2026-10-22 02:00:00','2026-10-22 02:00:00'),(25,8,3,19,'in',12.000,12.000,'StockIn',5,1,'2026-10-22 02:00:00','2026-10-22 02:00:00'),(26,7,2,17,'in',120.000,120.000,'StockIn',6,1,'2026-10-27 02:00:00','2026-10-27 02:00:00'),(27,1,1,3,'in',50.000,50.000,'StockIn',6,1,'2026-10-27 02:00:00','2026-10-27 02:00:00'),(28,4,2,11,'in',40.000,40.000,'StockIn',6,1,'2026-10-27 02:00:00','2026-10-27 02:00:00'),(29,1,1,2,'out',-120.000,30.000,'StockOut',1,1,'2026-07-16 07:00:00','2026-07-16 07:00:00'),(30,3,2,8,'out',-60.000,0.000,'StockOut',1,1,'2026-07-16 07:00:00','2026-07-16 07:00:00'),(31,3,1,7,'out',-20.000,330.000,'StockOut',1,1,'2026-07-16 07:00:00','2026-07-16 07:00:00'),(32,11,3,NULL,'out',-100.000,600.000,'StockOut',1,1,'2026-07-16 07:00:00','2026-07-16 07:00:00'),(33,4,2,9,'out',-50.000,220.000,'StockOut',1,1,'2026-07-16 07:00:00','2026-07-16 07:00:00'),(34,1,1,2,'out',-30.000,0.000,'StockOut',2,1,'2026-08-10 07:00:00','2026-08-10 07:00:00'),(35,1,1,1,'out',-70.000,830.000,'StockOut',2,1,'2026-08-10 07:00:00','2026-08-10 07:00:00'),(36,7,2,17,'out',-30.000,90.000,'StockOut',2,1,'2026-08-10 07:00:00','2026-08-10 07:00:00'),(37,5,1,12,'out',-20.000,76.000,'StockOut',2,1,'2026-08-10 07:00:00','2026-08-10 07:00:00'),(38,10,4,21,'out',-15.000,45.000,'StockOut',2,1,'2026-08-10 07:00:00','2026-08-10 07:00:00'),(39,1,1,1,'out',-90.000,740.000,'StockOut',3,1,'2026-09-24 07:00:00','2026-09-24 07:00:00'),(40,3,1,7,'out',-70.000,260.000,'StockOut',3,1,'2026-09-24 07:00:00','2026-09-24 07:00:00'),(41,8,3,19,'out',-12.000,0.000,'StockOut',3,1,'2026-09-24 07:00:00','2026-09-24 07:00:00'),(42,11,3,NULL,'out',-150.000,450.000,'StockOut',3,1,'2026-09-24 07:00:00','2026-09-24 07:00:00'),(43,11,3,NULL,'out',-150.000,300.000,'StockOut',4,1,'2026-10-19 07:00:00','2026-10-19 07:00:00'),(44,9,4,NULL,'out',-20.000,20.000,'StockOut',4,1,'2026-10-19 07:00:00','2026-10-19 07:00:00'),(45,13,3,NULL,'out',-3.000,9.000,'StockOut',4,1,'2026-10-19 07:00:00','2026-10-19 07:00:00'),(46,10,4,21,'out',-20.000,25.000,'StockOut',4,1,'2026-10-19 07:00:00','2026-10-19 07:00:00'),(47,6,3,14,'out',-10.000,90.000,'StockOut',5,1,'2026-10-29 07:00:00','2026-10-29 07:00:00'),(48,12,3,NULL,'out',-5.000,55.000,'StockOut',5,1,'2026-10-29 07:00:00','2026-10-29 07:00:00'),(49,1,1,3,'out',-20.000,30.000,'StockOut',6,1,'2026-11-01 07:00:00','2026-11-01 07:00:00'),(50,4,2,11,'out',-20.000,20.000,'StockOut',6,1,'2026-11-01 07:00:00','2026-11-01 07:00:00'),(51,1,1,1,'adjust',5.000,745.000,'StockAdjustment',1,1,'2026-09-04 03:00:00','2026-09-04 03:00:00'),(52,2,2,4,'adjust',2.000,142.000,'StockAdjustment',1,1,'2026-09-04 03:00:00','2026-09-04 03:00:00'),(53,7,2,16,'adjust',-3.000,347.000,'StockAdjustment',2,1,'2026-10-01 03:00:00','2026-10-01 03:00:00'),(54,6,3,14,'adjust',-1.000,89.000,'StockAdjustment',2,1,'2026-10-01 03:00:00','2026-10-01 03:00:00'),(55,12,3,NULL,'adjust',-5.000,50.000,'StockAdjustment',3,1,'2026-10-24 03:00:00','2026-10-24 03:00:00'),(56,9,4,NULL,'adjust',-3.000,17.000,'StockAdjustment',3,1,'2026-10-24 03:00:00','2026-10-24 03:00:00');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_out_items`
--

DROP TABLE IF EXISTS `stock_out_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_out_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stock_out_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `location_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `qty` decimal(15,3) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_out_items_stock_out_id_foreign` (`stock_out_id`),
  KEY `stock_out_items_product_id_foreign` (`product_id`),
  KEY `stock_out_items_location_id_foreign` (`location_id`),
  KEY `stock_out_items_batch_id_foreign` (`batch_id`),
  CONSTRAINT `stock_out_items_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`),
  CONSTRAINT `stock_out_items_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stock_out_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_out_items_stock_out_id_foreign` FOREIGN KEY (`stock_out_id`) REFERENCES `stock_outs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_out_items`
--

LOCK TABLES `stock_out_items` WRITE;
/*!40000 ALTER TABLE `stock_out_items` DISABLE KEYS */;
INSERT INTO `stock_out_items` VALUES (1,1,1,1,2,120.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(2,1,3,2,8,60.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(3,1,3,1,7,20.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(4,1,11,3,NULL,100.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(5,1,4,2,9,50.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(6,2,1,1,2,30.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(7,2,1,1,1,70.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(8,2,7,2,17,30.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(9,2,5,1,12,20.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(10,2,10,4,21,15.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(11,3,1,1,1,90.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(12,3,3,1,7,70.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(13,3,8,3,19,12.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(14,3,11,3,NULL,150.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(15,4,11,3,NULL,150.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(16,4,9,4,NULL,20.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(17,4,13,3,NULL,3.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(18,4,10,4,21,20.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(19,5,6,3,14,10.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(20,5,12,3,NULL,5.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(21,6,1,1,3,20.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(22,6,4,2,11,20.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(23,7,10,4,NULL,10.000,'2026-11-03 04:06:36','2026-11-03 04:06:36');
/*!40000 ALTER TABLE `stock_out_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_outs`
--

DROP TABLE IF EXISTS `stock_outs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_outs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doc_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usage',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_outs_doc_number_unique` (`doc_number`),
  KEY `stock_outs_created_by_foreign` (`created_by`),
  KEY `stock_outs_doc_number_index` (`doc_number`),
  KEY `stock_outs_status_index` (`status`),
  CONSTRAINT `stock_outs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_outs`
--

LOCK TABLES `stock_outs` WRITE;
/*!40000 ALTER TABLE `stock_outs` DISABLE KEYS */;
INSERT INTO `stock_outs` VALUES (1,'OUT-202607-0001','2026-07-16','Apotek Sehat Sejahtera','sale','posted','Penjualan rutin bulanan awal periode',1,'2026-07-16 07:00:00','2026-07-16 07:00:00','2026-07-16 07:00:00'),(2,'OUT-202608-0001','2026-08-10','Klinik Bersama Medika','sale','posted','Pesanan langganan klinik mitra',1,'2026-08-10 07:00:00','2026-08-10 07:00:00','2026-08-10 07:00:00'),(3,'OUT-202609-0001','2026-09-24','Apotek Sehat Sejahtera','sale','posted','Penjualan rutin bulanan',1,'2026-09-24 07:00:00','2026-09-24 07:00:00','2026-09-24 07:00:00'),(4,'OUT-202610-0001','2026-10-19','Toko Kelontong Berkah','sale','posted','Pesanan ritel mingguan',1,'2026-10-19 07:00:00','2026-10-19 07:00:00','2026-10-19 07:00:00'),(5,'OUT-202610-0002','2026-10-29','Ruang Perawatan','usage','posted','Pemakaian internal unit layanan',1,'2026-10-29 07:00:00','2026-10-29 07:00:00','2026-10-29 07:00:00'),(6,'OUT-202611-0001','2026-11-01','Pemusnahan','expired','posted','Pemusnahan batch kedaluwarsa sesuai prosedur',1,'2026-11-01 07:00:00','2026-11-01 07:00:00','2026-11-01 07:00:00'),(7,'OUT-202611-0002','2026-11-03','Gudang Transit','usage','draft','Draft permintaan barang untuk besok',1,NULL,'2026-11-03 07:00:00','2026-11-03 07:00:00');
/*!40000 ALTER TABLE `stock_outs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stocks`
--

DROP TABLE IF EXISTS `stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `location_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stocks_product_id_location_id_batch_id_unique` (`product_id`,`location_id`,`batch_id`),
  KEY `stocks_location_id_foreign` (`location_id`),
  KEY `stocks_batch_id_foreign` (`batch_id`),
  CONSTRAINT `stocks_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`),
  CONSTRAINT `stocks_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stocks`
--

LOCK TABLES `stocks` WRITE;
/*!40000 ALTER TABLE `stocks` DISABLE KEYS */;
INSERT INTO `stocks` VALUES (1,1,1,1,745.000,'2026-11-03 04:06:35','2026-11-03 04:06:36'),(2,3,1,7,260.000,'2026-11-03 04:06:35','2026-11-03 04:06:36'),(3,4,2,9,220.000,'2026-11-03 04:06:35','2026-11-03 04:06:36'),(4,5,1,12,76.000,'2026-11-03 04:06:35','2026-11-03 04:06:36'),(5,7,2,16,347.000,'2026-11-03 04:06:35','2026-11-03 04:06:37'),(6,11,3,NULL,300.000,'2026-11-03 04:06:35','2026-11-03 04:06:36'),(7,2,2,4,142.000,'2026-11-03 04:06:35','2026-11-03 04:06:36'),(8,10,4,21,25.000,'2026-11-03 04:06:35','2026-11-03 04:06:36'),(9,6,3,14,89.000,'2026-11-03 04:06:35','2026-11-03 04:06:37'),(10,9,4,NULL,17.000,'2026-11-03 04:06:35','2026-11-03 04:06:37'),(11,8,3,18,24.000,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(12,12,3,NULL,50.000,'2026-11-03 04:06:35','2026-11-03 04:06:37'),(13,13,3,NULL,9.000,'2026-11-03 04:06:35','2026-11-03 04:06:36'),(14,14,4,NULL,30.000,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(15,1,1,2,0.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(16,3,2,8,0.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(17,8,3,19,0.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(18,7,2,17,90.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(19,1,1,3,30.000,'2026-11-03 04:06:36','2026-11-03 04:06:36'),(20,4,2,11,20.000,'2026-11-03 04:06:36','2026-11-03 04:06:36');
/*!40000 ALTER TABLE `stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'PT Medika Nusantara','082112345678','sales@medikanusantara.co.id','Jl. Mangga Dua Raya No. 12, Jakarta Utara','2026-11-03 04:06:35','2026-11-03 04:06:35'),(2,'CV Farma Sehat Mandiri','082223456789','halo@farmasehatmandiri.id','Jl. ABC No. 45, Bandung','2026-11-03 04:06:35','2026-11-03 04:06:35'),(3,'PT Anugerah Niaga Supply','082334567890','purchasing@anugerahniaga.co.id','Jl. Rungkut Industri III No. 8, Surabaya','2026-11-03 04:06:35','2026-11-03 04:06:35');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_name_unique` (`name`),
  UNIQUE KEY `units_short_name_unique` (`short_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'Pcs','Pcs','2026-11-03 04:06:35','2026-11-03 04:06:35'),(2,'Box','Box','2026-11-03 04:06:35','2026-11-03 04:06:35'),(3,'Dus','Dus','2026-11-03 04:06:35','2026-11-03 04:06:35'),(4,'Kilogram','Kg','2026-11-03 04:06:35','2026-11-03 04:06:35'),(5,'Gram','G','2026-11-03 04:06:35','2026-11-03 04:06:35'),(6,'Liter','L','2026-11-03 04:06:35','2026-11-03 04:06:35'),(7,'Mililiter','Ml','2026-11-03 04:06:35','2026-11-03 04:06:35'),(8,'Meter','M','2026-11-03 04:06:35','2026-11-03 04:06:35'),(9,'Jam','Jam','2026-11-03 04:06:35','2026-11-03 04:06:35');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@gudangku.test',NULL,'$2y$12$RNbPiQzXw4ZKSNxZyisV3Ob.0K065e5dkut1l5HPw/eqeAqUlulrG','admin',1,NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(2,'Staf Gudang','staff@gudangku.test',NULL,'$2y$12$mkPu5yOrsWqcaUUZeZs26eXffgYc4ccr1eXwPzdH7VSGmtNnKFQ8C','staff',1,NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35'),(3,'Viewer','viewer@gudangku.test',NULL,'$2y$12$lO.MtmUPuM45msp.YNB3f.L1tcFDcADTYGcakzlVfS8k7jZawmRD.','viewer',1,NULL,'2026-11-03 04:06:35','2026-11-03 04:06:35');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'gudangku_deploy'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-03 18:06:43
