-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for myabsen
DROP DATABASE IF EXISTS `myabsen`;
CREATE DATABASE IF NOT EXISTS `myabsen` /*!40100 DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `myabsen`;

-- Dumping structure for table myabsen.cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.cache: ~0 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel_cache_198001012000011001|127.0.0.1', 'i:1;', 1790128649),
	('laravel_cache_198001012000011001|127.0.0.1:timer', 'i:1790128649;', 1790128649);

-- Dumping structure for table myabsen.cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.cache_locks: ~0 rows (approximately)

-- Dumping structure for table myabsen.detail_absensi
DROP TABLE IF EXISTS `detail_absensi`;
CREATE TABLE IF NOT EXISTS `detail_absensi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sesi_absensi_id` bigint unsigned NOT NULL,
  `siswa_id` bigint unsigned NOT NULL,
  `status` enum('hadir','izin','sakit','alpa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detail_absensi_sesi_absensi_id_siswa_id_unique` (`sesi_absensi_id`,`siswa_id`),
  KEY `detail_absensi_siswa_id_index` (`siswa_id`),
  CONSTRAINT `detail_absensi_sesi_absensi_id_foreign` FOREIGN KEY (`sesi_absensi_id`) REFERENCES `sesi_absensi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_absensi_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.detail_absensi: ~30 rows (approximately)
INSERT INTO `detail_absensi` (`id`, `sesi_absensi_id`, `siswa_id`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'hadir', NULL, '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(2, 1, 2, 'hadir', NULL, '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(3, 1, 3, 'hadir', NULL, '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(4, 1, 4, 'hadir', NULL, '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(5, 1, 5, 'hadir', NULL, '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(6, 2, 1, 'hadir', NULL, '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(7, 2, 2, 'sakit', 'Keterangan demo', '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(8, 2, 3, 'alpa', 'Keterangan demo', '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(9, 2, 4, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(10, 2, 5, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(11, 3, 1, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(12, 3, 2, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(13, 3, 3, 'izin', 'Keterangan demo', '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(14, 3, 4, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(15, 3, 5, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(16, 4, 1, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(17, 4, 2, 'alpa', 'Keterangan demo', '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(18, 4, 3, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(19, 4, 4, 'hadir', NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(20, 4, 5, 'izin', 'Keterangan demo', '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(21, 5, 1, 'hadir', NULL, '2026-09-21 06:06:14', '2026-09-21 06:06:14'),
	(22, 5, 2, 'izin', 'izin mau nonton bigmo', '2026-09-21 06:06:14', '2026-09-21 06:06:14'),
	(23, 5, 3, 'hadir', NULL, '2026-09-21 06:06:14', '2026-09-21 06:06:14'),
	(24, 5, 4, 'hadir', NULL, '2026-09-21 06:06:14', '2026-09-21 06:06:14'),
	(25, 5, 5, 'hadir', NULL, '2026-09-21 06:06:14', '2026-09-21 06:06:14'),
	(26, 6, 1, 'alpa', 'bolos', '2026-09-21 10:01:47', '2026-09-21 10:01:47'),
	(27, 6, 2, 'hadir', NULL, '2026-09-21 10:01:47', '2026-09-21 10:01:47'),
	(28, 6, 3, 'hadir', NULL, '2026-09-21 10:01:47', '2026-09-21 10:01:47'),
	(29, 6, 4, 'hadir', NULL, '2026-09-21 10:01:47', '2026-09-21 10:01:47'),
	(30, 6, 5, 'hadir', NULL, '2026-09-21 10:01:47', '2026-09-21 10:01:47');

-- Dumping structure for table myabsen.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
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

-- Dumping data for table myabsen.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table myabsen.guru
DROP TABLE IF EXISTS `guru`;
CREATE TABLE IF NOT EXISTS `guru` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guru_user_id_foreign` (`user_id`),
  CONSTRAINT `guru_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.guru: ~2 rows (approximately)
INSERT INTO `guru` (`id`, `user_id`, `nip`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 2, '198001012000011001', '2026-09-21 05:09:12', '2026-09-21 05:09:12', NULL),
	(2, 8, '12345678910', '2026-09-22 07:43:18', '2026-09-22 07:43:18', NULL);

-- Dumping structure for table myabsen.jadwal
DROP TABLE IF EXISTS `jadwal`;
CREATE TABLE IF NOT EXISTS `jadwal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint unsigned NOT NULL,
  `mapel_id` bigint unsigned NOT NULL,
  `guru_id` bigint unsigned NOT NULL,
  `hari` enum('senin','selasa','rabu','kamis','jumat','sabtu') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `tahun_ajaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwal_mapel_id_foreign` (`mapel_id`),
  KEY `jadwal_guru_id_hari_index` (`guru_id`,`hari`),
  KEY `jadwal_kelas_id_hari_index` (`kelas_id`,`hari`),
  CONSTRAINT `jadwal_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.jadwal: ~7 rows (approximately)
INSERT INTO `jadwal` (`id`, `kelas_id`, `mapel_id`, `guru_id`, `hari`, `jam_mulai`, `jam_selesai`, `tahun_ajaran`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, 'senin', '07:00:00', '09:00:00', '2026/2027', '2026-09-21 05:09:15', '2026-09-21 05:09:15'),
	(2, 1, 1, 1, 'selasa', '07:00:00', '09:00:00', '2026/2027', '2026-09-21 05:09:15', '2026-09-21 05:09:15'),
	(3, 1, 1, 1, 'rabu', '07:00:00', '09:00:00', '2026/2027', '2026-09-21 05:09:15', '2026-09-21 05:09:15'),
	(4, 1, 1, 1, 'kamis', '07:00:00', '09:00:00', '2026/2027', '2026-09-21 05:09:15', '2026-09-21 05:09:15'),
	(5, 1, 1, 1, 'jumat', '07:00:00', '09:00:00', '2026/2027', '2026-09-21 05:09:15', '2026-09-21 05:09:15'),
	(6, 1, 1, 1, 'sabtu', '07:00:00', '09:00:00', '2026/2027', '2026-09-21 05:09:15', '2026-09-21 05:09:15'),
	(7, 2, 2, 2, 'rabu', '10:00:00', '12:00:00', '2026/2027', '2026-09-22 08:07:13', '2026-09-22 08:07:13');

-- Dumping structure for table myabsen.jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
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

-- Dumping data for table myabsen.jobs: ~0 rows (approximately)

-- Dumping structure for table myabsen.job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
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

-- Dumping data for table myabsen.job_batches: ~0 rows (approximately)

-- Dumping structure for table myabsen.jurusan
DROP TABLE IF EXISTS `jurusan`;
CREATE TABLE IF NOT EXISTS `jurusan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.jurusan: ~1 rows (approximately)
INSERT INTO `jurusan` (`id`, `nama`, `kode`, `created_at`, `updated_at`) VALUES
	(1, 'Teknik Komputer dan Jaringan', 'TKJ', '2026-09-21 05:09:12', '2026-09-21 05:09:12'),
	(2, 'Multimedia', 'MM', '2026-09-22 07:26:37', '2026-09-22 07:26:37');

-- Dumping structure for table myabsen.kelas
DROP TABLE IF EXISTS `kelas`;
CREATE TABLE IF NOT EXISTS `kelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jurusan_id` bigint unsigned NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_ajaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kelas_jurusan_id_foreign` (`jurusan_id`),
  CONSTRAINT `kelas_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.kelas: ~1 rows (approximately)
INSERT INTO `kelas` (`id`, `jurusan_id`, `nama`, `tingkat`, `tahun_ajaran`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'XII TKJ 1', '10', '2026/2027', '2026-09-21 05:09:12', '2026-09-21 05:09:12', NULL),
	(2, 2, 'X MM 1', '10', '2026/2027', '2026-09-22 07:27:52', '2026-09-22 07:27:52', NULL);

-- Dumping structure for table myabsen.mapel
DROP TABLE IF EXISTS `mapel`;
CREATE TABLE IF NOT EXISTS `mapel` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.mapel: ~2 rows (approximately)
INSERT INTO `mapel` (`id`, `nama`, `kode`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Administrasi Sistem Jaringan', 'ASJ', '2026-09-21 05:09:15', '2026-09-22 07:29:12', NULL),
	(2, 'Pemrograman Dasar', 'PD', '2026-09-22 07:29:18', '2026-09-22 07:29:18', NULL);

-- Dumping structure for table myabsen.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.migrations: ~0 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_09_21_074953_create_jurusans_table', 1),
	(5, '2026_09_21_074954_create_kelas_table', 1),
	(6, '2026_09_21_074956_create_gurus_table', 1),
	(7, '2026_09_21_074957_create_siswas_table', 1),
	(8, '2026_09_21_074959_create_mapels_table', 1),
	(9, '2026_09_21_075000_create_jadwals_table', 1),
	(10, '2026_09_21_075001_create_sesi_absensis_table', 1),
	(11, '2026_09_21_075003_create_detail_absensis_table', 1);

-- Dumping structure for table myabsen.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table myabsen.sesi_absensi
DROP TABLE IF EXISTS `sesi_absensi`;
CREATE TABLE IF NOT EXISTS `sesi_absensi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `diabsen_oleh` bigint unsigned NOT NULL,
  `diubah_oleh` bigint unsigned DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sesi_absensi_jadwal_id_tanggal_unique` (`jadwal_id`,`tanggal`),
  KEY `sesi_absensi_diabsen_oleh_foreign` (`diabsen_oleh`),
  KEY `sesi_absensi_diubah_oleh_foreign` (`diubah_oleh`),
  KEY `sesi_absensi_tanggal_index` (`tanggal`),
  CONSTRAINT `sesi_absensi_diabsen_oleh_foreign` FOREIGN KEY (`diabsen_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sesi_absensi_diubah_oleh_foreign` FOREIGN KEY (`diubah_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sesi_absensi_jadwal_id_foreign` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.sesi_absensi: ~4 rows (approximately)
INSERT INTO `sesi_absensi` (`id`, `jadwal_id`, `tanggal`, `diabsen_oleh`, `diubah_oleh`, `catatan`, `created_at`, `updated_at`) VALUES
	(1, 6, '2026-09-19', 2, NULL, NULL, '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(2, 5, '2026-09-18', 2, NULL, NULL, '2026-09-21 05:09:16', '2026-09-21 05:09:16'),
	(3, 4, '2026-09-17', 2, NULL, NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(4, 3, '2026-09-16', 2, NULL, NULL, '2026-09-21 05:09:17', '2026-09-21 05:09:17'),
	(5, 1, '2026-09-21', 2, NULL, NULL, '2026-09-21 06:06:14', '2026-09-21 06:06:14'),
	(6, 2, '2026-09-22', 2, NULL, NULL, '2026-09-21 10:01:47', '2026-09-21 10:01:47');

-- Dumping structure for table myabsen.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
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

-- Dumping data for table myabsen.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('3f54YPzwS6eftpCPGEmvaRhn0OoUAZ2twW6ajrrj', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUlZ3Z0hhN2NsNHVRMTE2WUdyd2c0bWo5V3U5QkpxTGZRcnkxaUh4UCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sYXBvcmFuIjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5sYXBvcmFuLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1790128942),
	('sBpoJyMFe7XjfSdVAALwl3tHFeiSWWHZogZY4Q6D', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUVV3dEZmR0tLdVdzSDNVZEZuZXlWaFA3UGlmUWpXYlE0T0dSanVMUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9ndXJ1IjtzOjU6InJvdXRlIjtzOjE2OiJhZG1pbi5ndXJ1LmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1790131155);

-- Dumping structure for table myabsen.siswa
DROP TABLE IF EXISTS `siswa`;
CREATE TABLE IF NOT EXISTS `siswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `kelas_id` bigint unsigned NOT NULL,
  `nis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `siswa_nis_unique` (`nis`),
  KEY `siswa_user_id_foreign` (`user_id`),
  KEY `siswa_kelas_id_index` (`kelas_id`),
  CONSTRAINT `siswa_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `siswa_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.siswa: ~9 rows (approximately)
INSERT INTO `siswa` (`id`, `user_id`, `kelas_id`, `nis`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 3, 1, '1001', '2026-09-21 05:09:12', '2026-09-21 05:09:12', NULL),
	(2, 4, 1, '1002', '2026-09-21 05:09:13', '2026-09-21 05:09:13', NULL),
	(3, 5, 1, '1003', '2026-09-21 05:09:14', '2026-09-21 05:09:14', NULL),
	(4, 6, 1, '1004', '2026-09-21 05:09:14', '2026-09-21 05:09:14', NULL),
	(5, 7, 1, '1005', '2026-09-21 05:09:15', '2026-09-21 05:09:15', NULL),
	(6, 9, 2, '1010', '2026-09-22 07:44:03', '2026-09-22 07:44:03', NULL),
	(7, 10, 2, '12345', '2026-09-22 08:12:07', '2026-09-22 08:12:07', NULL),
	(8, 11, 2, '123456', '2026-09-22 08:12:07', '2026-09-22 08:12:07', NULL),
	(9, 12, 2, '1234567', '2026-09-22 08:12:08', '2026-09-22 08:12:08', NULL);

-- Dumping structure for table myabsen.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','guru','siswa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'siswa',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.users: ~12 rows (approximately)
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Administrator', 'admin', NULL, '$2y$12$UQE7i.obR1gJkxFR06.j0eYpA/TUuVHhfiOGiOPgH7han7r.xORnC', 'admin', NULL, '2026-09-21 05:09:11', '2026-09-21 05:09:11'),
	(2, 'Ahmad Reza, S.Kom', 'guru1', NULL, '$2y$12$PNb4nKG8.7f5wS7quob0RuJGDOdNM4k14GjLaJSHqtE0xrdCqdaoS', 'guru', NULL, '2026-09-21 05:09:11', '2026-09-21 05:09:11'),
	(3, 'Andi Pratama', 'siswa1', NULL, '$2y$12$J914OynuzbQ7FIr2gic.Cuwph.0i0a.iJlALWrhu7rzpYtu0osglm', 'siswa', NULL, '2026-09-21 05:09:12', '2026-09-21 05:09:12'),
	(4, 'Bunga Citra', 'siswa2', NULL, '$2y$12$8ruxiDFjS3gEMG70rM9KiO842qoM01oIXVpTMYzBJqr6YeUAYfcRK', 'siswa', NULL, '2026-09-21 05:09:13', '2026-09-21 05:09:13'),
	(5, 'Cahyo Utomo', 'siswa3', NULL, '$2y$12$uOSWO6ofvMBs9KHeB7iS6e75WqnUqthUqMhmw3UVpHlRa5OVCC3xG', 'siswa', NULL, '2026-09-21 05:09:14', '2026-09-21 05:09:14'),
	(6, 'Muhammad Irfan Fauzi', 'siswa4', NULL, '$2y$12$qi4haoI62Ab/z.jLlUkTp.v6BMQ2cPomRR/2z0BN/dkU3eXCF16cm', 'siswa', NULL, '2026-09-21 05:09:14', '2026-09-21 05:09:14'),
	(7, 'Bigmo', 'siswa5', NULL, '$2y$12$WqdfPUsNJuljSJjwDsW11uYBKWdAxm6glfdeDvH7tNLzQK05Pg3AK', 'siswa', NULL, '2026-09-21 05:09:15', '2026-09-21 05:09:15'),
	(8, 'Dimas S.Kom M.Kom', '12345678910', NULL, '$2y$12$oFM25YoUiY1QkcoEb7f2X.NurEh9tvaR5.5QmG5HuDVQOWh9IM6YW', 'guru', NULL, '2026-09-22 07:43:18', '2026-09-22 07:43:18'),
	(9, 'Ambarawi', '1010', NULL, '$2y$12$JsoNlX6rHjyHZ55hbeGKaOaUs.teD/RIo8fE3Ilb/sxXtP/VkzWY2', 'siswa', NULL, '2026-09-22 07:44:03', '2026-09-22 07:44:03'),
	(10, 'Prabowi', '12345', NULL, '$2y$12$uCz9BHquws5zPBY4.iRv0uHbRDKoVWdXTPt0wA9VsqEmD/ulg1zVG', 'siswa', NULL, '2026-09-22 08:12:07', '2026-09-22 08:12:07'),
	(11, 'Gibrent', '123456', NULL, '$2y$12$Z27gv9HOc4nN9FbF4IbnhOF1lZM/9vewdvz2sN2gvbjbLQtmhufui', 'siswa', NULL, '2026-09-22 08:12:07', '2026-09-22 08:12:07'),
	(12, 'Andis', '1234567', NULL, '$2y$12$tlEX4fwpjwtRRoLLfFRp0O3TzpkqA0Ba2kM8FnIzaEMSefXqlWx6y', 'siswa', NULL, '2026-09-22 08:12:08', '2026-09-22 08:12:08');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
