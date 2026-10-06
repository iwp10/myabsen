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
CREATE DATABASE IF NOT EXISTS `myabsen` /*!40100 DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `myabsen`;

-- Dumping structure for table myabsen.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.cache: ~0 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel_cache_198001012000011001|127.0.0.1', 'i:1;', 1790128649),
	('laravel_cache_198001012000011001|127.0.0.1:timer', 'i:1790128649;', 1790128649),
	('laravel_cache_aadmin|127.0.0.1', 'i:1;', 1790736291),
	('laravel_cache_aadmin|127.0.0.1:timer', 'i:1790736291;', 1790736291),
	('laravel_cache_abc|127.0.0.1', 'i:1;', 1790447118),
	('laravel_cache_abc|127.0.0.1:timer', 'i:1790447118;', 1790447118),
	('laravel_cache_administrator&#039;--|10.201.64.6', 'i:1;', 1790740335),
	('laravel_cache_administrator&#039;--|10.201.64.6:timer', 'i:1790740335;', 1790740335),
	('laravel_cache_tes|127.0.0.1', 'i:5;', 1790445330),
	('laravel_cache_tes|127.0.0.1:timer', 'i:1790445330;', 1790445330),
	('laravel_cache_test-key|127.0.0.1', 'i:5;', 1790444932),
	('laravel_cache_test-key|127.0.0.1:timer', 'i:1790444932;', 1790444932);

-- Dumping structure for table myabsen.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.cache_locks: ~0 rows (approximately)

-- Dumping structure for table myabsen.detail_absensi
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
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.detail_absensi: ~47 rows (approximately)
INSERT INTO `detail_absensi` (`id`, `sesi_absensi_id`, `siswa_id`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(2, 1, 2, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(3, 1, 3, 'izin', 'Keterangan demo', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(4, 1, 4, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(5, 1, 5, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(6, 2, 1, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(7, 2, 2, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(8, 2, 3, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(9, 2, 4, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(10, 2, 5, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(11, 3, 1, 'izin', 'Keterangan demo', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(12, 3, 2, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(13, 3, 3, 'alpa', 'Keterangan demo', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(14, 3, 4, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(15, 3, 5, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(16, 4, 1, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(17, 4, 2, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(18, 4, 3, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(19, 4, 4, 'hadir', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(20, 4, 5, 'sakit', 'Keterangan demo', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(57, 11, 2, 'hadir', NULL, '2026-09-29 17:13:29', '2026-09-29 17:14:30'),
	(58, 11, 3, 'hadir', NULL, '2026-09-29 17:13:29', '2026-09-29 17:14:30'),
	(59, 11, 4, 'hadir', NULL, '2026-09-29 17:13:29', '2026-09-29 17:14:30'),
	(60, 11, 5, 'hadir', NULL, '2026-09-29 17:13:29', '2026-09-29 17:14:30'),
	(62, 10, 1, 'sakit', 'Keracunan mbg', '2026-09-30 02:51:37', '2026-09-30 02:51:37'),
	(63, 10, 2, 'hadir', NULL, '2026-09-30 02:51:37', '2026-09-30 02:51:37'),
	(64, 10, 3, 'hadir', NULL, '2026-09-30 02:51:37', '2026-09-30 02:51:37'),
	(65, 10, 4, 'hadir', NULL, '2026-09-30 02:51:37', '2026-09-30 02:51:37'),
	(66, 10, 5, 'hadir', NULL, '2026-09-30 02:51:37', '2026-09-30 02:51:37'),
	(67, 12, 6, 'hadir', NULL, '2026-09-30 03:01:13', '2026-09-30 03:01:13'),
	(68, 12, 7, 'izin', 'nyari lahan buat kopdes', '2026-09-30 03:01:13', '2026-09-30 03:01:13'),
	(69, 12, 8, 'hadir', NULL, '2026-09-30 03:01:13', '2026-09-30 03:01:13'),
	(70, 12, 9, 'hadir', NULL, '2026-09-30 03:01:13', '2026-09-30 03:01:13'),
	(71, 13, 6, 'hadir', NULL, '2026-09-30 23:03:49', '2026-09-30 23:03:49'),
	(72, 13, 7, 'alpa', 'bolos,lompat pager', '2026-09-30 23:03:49', '2026-09-30 23:03:49'),
	(73, 13, 8, 'sakit', 'keracunan mbg', '2026-09-30 23:03:49', '2026-09-30 23:03:49'),
	(74, 13, 9, 'izin', 'sedang dinas diluar kota', '2026-09-30 23:03:49', '2026-09-30 23:03:49'),
	(76, 14, 1, 'hadir', NULL, '2026-10-01 06:27:21', '2026-10-01 06:27:21'),
	(77, 14, 2, 'hadir', NULL, '2026-10-01 06:27:21', '2026-10-01 06:27:21'),
	(78, 14, 3, 'hadir', NULL, '2026-10-01 06:27:21', '2026-10-01 06:27:21'),
	(79, 14, 4, 'hadir', NULL, '2026-10-01 06:27:21', '2026-10-01 06:27:21'),
	(80, 14, 5, 'hadir', NULL, '2026-10-01 06:27:21', '2026-10-01 06:27:21'),
	(81, 15, 1, 'izin', 'nonton bigmow', '2026-10-01 07:35:43', '2026-10-01 07:35:43'),
	(82, 15, 2, 'hadir', NULL, '2026-10-01 07:35:43', '2026-10-01 07:35:43'),
	(83, 15, 3, 'hadir', NULL, '2026-10-01 07:35:43', '2026-10-01 07:35:43'),
	(84, 15, 4, 'sakit', 'panas tinggi', '2026-10-01 07:35:43', '2026-10-01 07:35:43'),
	(85, 15, 5, 'alpa', 'kesiangan', '2026-10-01 07:35:43', '2026-10-01 07:35:43');

-- Dumping structure for table myabsen.failed_jobs
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.guru: ~0 rows (approximately)
INSERT INTO `guru` (`id`, `user_id`, `nip`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 2, '198001012000011001', '2026-10-06 13:26:37', '2026-10-06 13:26:37', NULL);

-- Dumping structure for table myabsen.jadwal
CREATE TABLE IF NOT EXISTS `jadwal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint unsigned NOT NULL,
  `mapel_id` bigint unsigned NOT NULL,
  `guru_id` bigint unsigned NOT NULL,
  `hari` enum('senin','selasa','rabu','kamis','jumat','sabtu') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `tahun_ajaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` enum('Ganjil','Genap') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ganjil',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwal_mapel_id_foreign` (`mapel_id`),
  KEY `jadwal_guru_id_hari_index` (`guru_id`,`hari`),
  KEY `jadwal_kelas_id_hari_index` (`kelas_id`,`hari`),
  CONSTRAINT `jadwal_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.jadwal: ~6 rows (approximately)
INSERT INTO `jadwal` (`id`, `kelas_id`, `mapel_id`, `guru_id`, `hari`, `jam_mulai`, `jam_selesai`, `tahun_ajaran`, `semester`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 1, 'senin', '07:00:00', '09:00:00', '2026/2027', 'Ganjil', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(2, 1, 1, 1, 'selasa', '07:00:00', '09:00:00', '2026/2027', 'Ganjil', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(3, 1, 1, 1, 'rabu', '07:00:00', '09:00:00', '2026/2027', 'Ganjil', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(4, 1, 1, 1, 'kamis', '07:00:00', '09:00:00', '2026/2027', 'Ganjil', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(5, 1, 1, 1, 'jumat', '07:00:00', '09:00:00', '2026/2027', 'Ganjil', '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(6, 1, 1, 1, 'sabtu', '07:00:00', '09:00:00', '2026/2027', 'Ganjil', '2026-10-06 13:26:39', '2026-10-06 13:26:39');

-- Dumping structure for table myabsen.jobs
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
CREATE TABLE IF NOT EXISTS `jurusan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.jurusan: ~0 rows (approximately)
INSERT INTO `jurusan` (`id`, `nama`, `kode`, `created_at`, `updated_at`) VALUES
	(1, 'Rekayasa Perangkat Lunak', 'RPL', '2026-10-06 13:26:37', '2026-10-06 13:26:37');

-- Dumping structure for table myabsen.kelas
CREATE TABLE IF NOT EXISTS `kelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jurusan_id` bigint unsigned NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_ajaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` enum('Ganjil','Genap') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ganjil',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kelas_jurusan_id_foreign` (`jurusan_id`),
  CONSTRAINT `kelas_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.kelas: ~0 rows (approximately)
INSERT INTO `kelas` (`id`, `jurusan_id`, `nama`, `tingkat`, `tahun_ajaran`, `semester`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, '10 RPL 1', '10', '2026/2027', 'Ganjil', '2026-10-06 13:26:37', '2026-10-06 13:26:37', NULL);

-- Dumping structure for table myabsen.mapel
CREATE TABLE IF NOT EXISTS `mapel` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.mapel: ~0 rows (approximately)
INSERT INTO `mapel` (`id`, `nama`, `kode`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Pemrograman Dasar', 'PD', '2026-10-06 13:26:39', '2026-10-06 13:26:39', NULL);

-- Dumping structure for table myabsen.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
	(11, '2026_09_21_075003_create_detail_absensis_table', 1),
	(12, '2026_10_04_154746_add_semester_to_kelas_table', 1),
	(13, '2026_10_04_154747_add_semester_to_jadwal_table', 1),
	(14, '2026_10_05_120000_create_pengaturans_table', 1),
	(15, '2026_10_05_170000_update_fk_diabsen_oleh_and_diubah_oleh_on_sesi_absensi_table', 1);

-- Dumping structure for table myabsen.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table myabsen.pengaturan
CREATE TABLE IF NOT EXISTS `pengaturan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kunci` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengaturan_kunci_unique` (`kunci`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.pengaturan: ~2 rows (approximately)
INSERT INTO `pengaturan` (`id`, `kunci`, `nilai`, `created_at`, `updated_at`) VALUES
	(1, 'tahun_ajaran_aktif', '2026/2027', '2026-10-06 13:26:36', '2026-10-06 13:26:36'),
	(2, 'semester_aktif', 'Ganjil', '2026-10-06 13:26:36', '2026-10-06 13:26:36');

-- Dumping structure for table myabsen.sesi_absensi
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
  KEY `sesi_absensi_tanggal_index` (`tanggal`),
  KEY `sesi_absensi_diabsen_oleh_foreign` (`diabsen_oleh`),
  KEY `sesi_absensi_diubah_oleh_foreign` (`diubah_oleh`),
  CONSTRAINT `sesi_absensi_diabsen_oleh_foreign` FOREIGN KEY (`diabsen_oleh`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sesi_absensi_diubah_oleh_foreign` FOREIGN KEY (`diubah_oleh`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sesi_absensi_jadwal_id_foreign` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.sesi_absensi: ~4 rows (approximately)
INSERT INTO `sesi_absensi` (`id`, `jadwal_id`, `tanggal`, `diabsen_oleh`, `diubah_oleh`, `catatan`, `created_at`, `updated_at`) VALUES
	(1, 1, '2026-10-05', 2, NULL, NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(2, 6, '2026-10-03', 2, NULL, NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(3, 5, '2026-10-02', 2, NULL, NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(4, 4, '2026-10-01', 2, NULL, NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39');

-- Dumping structure for table myabsen.sessions
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
	('2sa42cfs5b5wYVwdD6R7nPjTeSdD3GGlfbKVrCO4', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNFg4c0dYUVlmVjVXMGpmTTNMRzVwZlJhMWRROVlWSUJiWVVndjNoZiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWRtaW4vbWFwZWwiO3M6NToicm91dGUiO3M6MTc6ImFkbWluLm1hcGVsLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1791293633);

-- Dumping structure for table myabsen.siswa
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.siswa: ~5 rows (approximately)
INSERT INTO `siswa` (`id`, `user_id`, `kelas_id`, `nis`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 3, 1, '1001', '2026-10-06 13:26:37', '2026-10-06 13:26:37', NULL),
	(2, 4, 1, '1002', '2026-10-06 13:26:38', '2026-10-06 13:26:38', NULL),
	(3, 5, 1, '1003', '2026-10-06 13:26:38', '2026-10-06 13:26:38', NULL),
	(4, 6, 1, '1004', '2026-10-06 13:26:39', '2026-10-06 13:26:39', NULL),
	(5, 7, 1, '1005', '2026-10-06 13:26:39', '2026-10-06 13:26:39', NULL);

-- Dumping structure for table myabsen.users
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table myabsen.users: ~7 rows (approximately)
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Administrator', 'admin', NULL, '$2y$12$IiBUgBg68Y6bZ7RL1aJKp.wBci41QD52NF2TAl0/BPV7Bh1tihRqG', 'admin', NULL, '2026-10-06 13:26:36', '2026-10-06 13:26:36'),
	(2, 'Guru Satu', 'guru1', NULL, '$2y$12$MzX6aIwXeJ.z4VR5b.IHP.ovyjvxmfyDYFqZZSuhM/.QGd0JNlpfy', 'guru', NULL, '2026-10-06 13:26:37', '2026-10-06 13:26:37'),
	(3, 'Siswa Satu', 'siswa1', NULL, '$2y$12$I5gFX.HihhPa2v5CvdtbvugqbafT4aEsV83tYBcLqDcH1o9OwXxR.', 'siswa', NULL, '2026-10-06 13:26:37', '2026-10-06 13:26:37'),
	(4, 'Siswa 2', 'siswa2', NULL, '$2y$12$dku5KtRxZ0EKvNsNzei8a.u/JcOm6a/ZQkNTBqO3nH23E/aINgx06', 'siswa', NULL, '2026-10-06 13:26:38', '2026-10-06 13:26:38'),
	(5, 'Siswa 3', 'siswa3', NULL, '$2y$12$iDt0Il03pvdRdd3ISULkzOrmEkZUp.tBxv1T1.KnQaigrVm43FE5W', 'siswa', NULL, '2026-10-06 13:26:38', '2026-10-06 13:26:38'),
	(6, 'Siswa 4', 'siswa4', NULL, '$2y$12$Go3SJXuzueFnjqzW9qBRruhgbQZF77Pz.PNvcRR3EzSmYccr.Rvz6', 'siswa', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39'),
	(7, 'Siswa 5', 'siswa5', NULL, '$2y$12$YmF6Z6mZgEP3fP9uMseszOx/YBIY1CTVFknNE8KiQRwvvDxDXsnRS', 'siswa', NULL, '2026-10-06 13:26:39', '2026-10-06 13:26:39');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
