-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2025 at 12:48 AM
-- Server version: 8.0.30
-- PHP Version: 8.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simada-kp`
--

-- --------------------------------------------------------

--
-- Table structure for table `biodata`
--

CREATE TABLE `biodata` (
  `id_biodata` int NOT NULL,
  `id_pengguna` int NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `no_hp` varchar(30) DEFAULT NULL,
  `nip_nik` varchar(50) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `foto_profile` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `biodata`
--

INSERT INTO `biodata` (`id_biodata`, `id_pengguna`, `nama_lengkap`, `no_hp`, `nip_nik`, `jabatan`, `foto_profile`, `created_at`, `updated_at`) VALUES
(1, 2, 'Admin Aplikasi', '08123222222', '', 'Administrator', 'u2_4f88907cd964.png', '2025-08-27 13:24:05', '2025-11-19 17:25:24'),
(63, 4, 'Farros Atiqi', '0812345678', '123456789', 'Kepala Bidang Komunikasi Publik', 'u4_eb4e9372b881.jpg', '2025-11-27 08:50:28', '2025-11-28 07:17:08');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_clustering`
--

CREATE TABLE `hasil_clustering` (
  `id_clustering` int NOT NULL,
  `id_kliping` int NOT NULL,
  `metode` enum('KMeans','DBSCAN') NOT NULL,
  `cluster` int NOT NULL,
  `silhouette_score` decimal(5,3) DEFAULT NULL,
  `tanggal_proses` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `iklan_koran`
--

CREATE TABLE `iklan_koran` (
  `id_iklan` int NOT NULL,
  `judul_iklan` text NOT NULL,
  `nama_media` varchar(150) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `kategori_iklan` enum('Galeri Foto','Adventorial','Layanan Masyarakat') NOT NULL,
  `foto_iklan` varchar(255) DEFAULT NULL COMMENT 'Path file; buat tabel iklan_foto jika butuh multi-foto',
  `created_at` timestamp NULL DEFAULT (now()),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `iklan_koran`
--

INSERT INTO `iklan_koran` (`id_iklan`, `judul_iklan`, `nama_media`, `tanggal_terbit`, `kategori_iklan`, `foto_iklan`, `created_at`, `updated_at`) VALUES
(25, 'HUR RI', 'jurnaldaily.com', '2025-12-10', 'Adventorial', 'uploads/iklan_koran/2025/12/WhatsApp-Image-2025-12-04-at-13-24-35-1-fc9f1cfa.jpeg', '2025-12-10 06:56:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kliping_berita`
--

CREATE TABLE `kliping_berita` (
  `id_kliping` int NOT NULL,
  `nama_media` varchar(150) NOT NULL,
  `judul_berita` text NOT NULL,
  `predicted_label` varchar(50) DEFAULT NULL,
  `sentimen` enum('Positif','Negatif') NOT NULL,
  `kategori_program` enum('Prioritas','Non Prioritas') NOT NULL,
  `is_training` tinyint(1) DEFAULT '0',
  `verified_by` varchar(100) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `tgl_input` date DEFAULT (curdate()),
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kliping_berita`
--

INSERT INTO `kliping_berita` (`id_kliping`, `nama_media`, `judul_berita`, `predicted_label`, `sentimen`, `kategori_program`, `is_training`, `verified_by`, `verified_at`, `tgl_input`, `created_at`, `updated_at`) VALUES
(1, 'akunberita.id', 'Jawab Pendapat Fraksi, Rico Waas Sebut Tetap Komitmen Tingkatkan Layanan Publik dan Kesehatan', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-10-29', '2025-10-29 14:52:59', NULL),
(3, 'agiodeli.id', 'Pimpinan DPRD Medan Kecewa, Wali Kota Medan Terkesan Biarkan', NULL, 'Negatif', 'Non Prioritas', 0, NULL, NULL, '2025-10-29', '2025-10-29 14:54:43', NULL),
(4, 'agiodeli.id', 'Wali Kota Medan Rico Waas Pastikan Tertibkan dan Lelang Mobil Dinas yang Tak Sesuai Aturan', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-10-29', '2025-10-29 15:00:41', NULL),
(5, 'agiodeli.id', 'Pemko Medan Dukung Pelaksanaan Peringatan Hari Jadi Kota Medan', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-10-29', '2025-10-29 15:44:36', NULL),
(7, 'agiodeli.id', 'Rico waas sedang bermain dengan keluarga nya', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-10-30', '2025-10-30 02:37:12', NULL),
(8, 'agiodeli.id', 'Pemko medan mngadakan makan bergizi gratis', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-10-30', '2025-10-30 06:49:00', NULL),
(9, 'agiodeli.id', 'Rico Waas Saksikan Penyembelihan Hewan Kurban di Medan Sunggal', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-16', '2025-11-16 02:39:12', NULL),
(10, 'agiodeli.id', 'wali kota medan hadiri  maulid nabi', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-19', '2025-11-19 17:34:20', NULL),
(11, 'agiodeli.id', 'Wali kota medan merenovasi taman cadika', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-19', '2025-11-19 17:35:34', NULL),
(12, 'sumutpos', 'Rico Waas Deklarasikan Gerakan Literasi Digital di Sekolah Parulian', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:29:43', NULL),
(13, 'neraca.news', 'Safari Isya di Masjid Al Arif Billah, Rico Waas Memberdayakan Masjid di Dalam Gang', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:33:59', NULL),
(14, 'neraca.news', 'Kunjungi Korban Kebakaran di Kelurahan Kesawan, Rico Waas Salurkan Bantuan Logistik', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:36:00', NULL),
(15, 'sumutnews', 'Rico Waas Hadiri Tahlilan Malam 40 Hari Wafatnya Kakak Zakiyuddin Harahap', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:43:19', NULL),
(16, 'sumutnews', 'Tinjau Eks Perisan Plaza yang Terbengkalai, Rico Waas Bilang Begini', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:44:01', NULL),
(17, 'sumutnews', 'Rico Waas Saksikan Penyembelihan Sapi Kurban Prabowo di Masjd Raya Al Osmani', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:44:21', NULL),
(18, 'sumutnews', 'Rico Waas Ingin Pendirian Sekolah Unggul Bukan Hanya Fasilitas', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:44:42', NULL),
(19, 'jurnaldaily.com', 'Tinjau Kolam Detensi Selayang, Rico Waas Targetkan dapat Mereduksi 10 -15 Persen Banjir', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:48:02', NULL),
(20, 'jurnaldaily.com', 'Di Rapat Paripurna, Rico Waas Tegaskan Komitmen Tingkatkan Pelayanan Publik dan Kesehatan', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:48:49', NULL),
(21, 'jurnaldaily.com', 'Rico Waas Pimpin Upacara Hari Kesaktian Pancasila 2025', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:49:17', NULL),
(22, 'jurnaldaily.com', 'Rico Waas Hadiri Apel Gelar Pasukan Patroli Gabungan Simpatik untuk Indonesia Damai', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:49:42', NULL),
(23, 'jurnaldaily.com', 'Rico Waas Ingin Bela Diri Tarung Derajat Terus Dikembangkan dan Jadi Kebanggaan Masyarakat', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:50:28', NULL),
(24, 'jurnaldaily.com', 'Rico Waas Bahas Peluang Investasi dan Kerjasama Dengan Konsul Amerika Serikat', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:50:55', NULL),
(25, 'jurnaldaily.com', 'Audiensi pada Rico Waas, Keuskupan Agung Medan Sampaikan tentang Daya Tampung Gereja Katedral', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:51:27', NULL),
(26, 'jurnaldaily.com', 'Rico Waas: Pemko Medan Atasi Permasalahan Sampah Mulai dari Hulu, Tengah dan Hilir', NULL, 'Positif', 'Prioritas', 0, 'adminaplikasi', '2025-11-29 15:45:56', '2025-11-28', '2025-11-28 08:52:03', NULL),
(27, 'jurnaldaily.com', 'Rico Waas Kukuhkan Paskibraka Kota Medan 2025', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:52:51', NULL),
(28, 'jurnaldaily.com', 'Rico Waas Minta Satker PPS Sumut Update Progres Pekerjaan Stadion Teladan', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:53:19', NULL),
(29, 'jurnaldaily.com', 'Tinjau Sekolah Rakyat Kota Medan, Rico Waas Ingatkan Siswa Rajin Belajar untuk Menggapai Cita-cita', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:54:02', NULL),
(30, 'jurnaldaily.com', 'Sambut HUT ke-435 Medan, Rico Waas Pimpin Ziarah ke Taman Makam Pahlawan Bukit Barisan', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 08:54:27', NULL),
(31, 'kedannews', 'Wali Kota Medan Rico Waas Tinjau Gotong Royong dan Tanggapi Keluhan Drainase Warga', NULL, 'Positif', 'Prioritas', 0, 'adminaplikasi', '2025-11-28 17:21:16', '2025-11-27', '2025-11-28 08:59:47', NULL),
(32, 'kedannews', 'Wali Kota Medan Rico Waas Tinjau Normalisasi Parit Gonggong Sepanjang 1.250 Meter', NULL, 'Positif', 'Prioritas', 1, 'adminaplikasi', '2025-11-29 15:45:32', '2025-11-28', '2025-11-28 09:00:12', NULL),
(33, 'kedannews', 'Rico Waas Hadiri Apel Patroli Gabungan HUT ke-80 TNI, 28 Ton Beras Dibagikan untuk Warga', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 09:00:39', NULL),
(34, 'kedannews', 'Rico Waas Ingin Taman Lili Suheri Jadi Pusat Anak Muda dan UMKM Kreatif di Medan', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-11-28', '2025-11-28 09:01:44', NULL),
(36, 'kedannews', 'Wali Kota Medan Rico Waas Tekankan Pentingnya Transformasi Digital bagi Pedagang Pasar', NULL, 'Positif', 'Prioritas', 1, 'adminaplikasi', '2025-11-28 16:21:56', '2025-11-28', '2025-11-28 09:13:41', NULL),
(37, 'Tribun news', 'rico waas solat jumat di masjid al maidah dan menyapa warga', NULL, 'Positif', 'Non Prioritas', 1, 'adminaplikasi', '2025-11-28 23:11:12', '2025-11-28', '2025-11-28 16:10:44', NULL),
(38, 'Tribun news', 'wali kota medan membenahi taman cadika', NULL, 'Positif', 'Prioritas', 0, 'adminaplikasi', '2025-12-10 13:32:00', '2025-12-09', '2025-12-09 07:48:24', NULL),
(39, 'Tribun news', 'Rico waas benahi taman cadika', NULL, 'Positif', 'Prioritas', 0, NULL, NULL, '2025-12-10', '2025-12-10 06:59:56', NULL),
(40, 'Tribunmedan', 'Wali Kota Medan Rico Minta Layanan dan Perlindungan Anak di Tingkatkan saat Diskusi Publik di MPP', NULL, 'Positif', 'Prioritas', 1, 'farros', '2025-12-10 14:26:56', '2025-12-10', '2025-12-10 07:17:28', NULL),
(41, 'kedannews', 'Rico waas mengunjungi korban banjir di medan marelan', NULL, 'Positif', 'Non Prioritas', 0, NULL, NULL, '2025-12-10', '2025-12-10 07:21:48', NULL),
(42, 'jurnaldaily.com', 'Wali Kota Medan Rico Minta Layanan dan Perlindungan Anak di Tingkatkan saat Diskusi Publik di MPP', NULL, 'Negatif', 'Prioritas', 0, NULL, NULL, '2025-12-10', '2025-12-10 07:27:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `level` enum('Admin','Staf') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Staf',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT (now()),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `username`, `password_hash`, `level`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'adminaplikasi', '$2y$10$cQJKD7m2ibGwbEmO11XsBuAhz70mpfup4PR0PoUlIVjoaqJQUTvtu', 'Admin', 1, '2025-08-27 13:24:05', NULL),
(4, 'farros', '$2y$12$UXil7w1TcuF4t.xS3AvjZOmvuvpDtndqgDtvpWQYAua/Z0tuj164q', 'Staf', 1, '2025-11-27 08:50:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `spanduk`
--

CREATE TABLE `spanduk` (
  `id_spanduk` int NOT NULL,
  `judul_kegiatan` text NOT NULL,
  `tanggal_pasang` date NOT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `spanduk`
--

INSERT INTO `spanduk` (`id_spanduk`, `judul_kegiatan`, `tanggal_pasang`, `created_at`, `updated_at`) VALUES
(2, 'HUT RI 80', '2025-11-28', '2025-11-28 15:40:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `spanduk_foto`
--

CREATE TABLE `spanduk_foto` (
  `id_foto` int NOT NULL,
  `id_lokasi` int NOT NULL,
  `file_path` varchar(255) NOT NULL COMMENT 'Path/URL foto',
  `keterangan` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `spanduk_foto`
--

INSERT INTO `spanduk_foto` (`id_foto`, `id_lokasi`, `file_path`, `keterangan`, `created_at`, `updated_at`) VALUES
(2, 2, 'uploads/spanduk/2025/11/1764344408_hero - Copy.jpg', 'depan stasiun kereta api', '2025-11-28 15:40:08', NULL),
(3, 2, 'uploads/spanduk/2025/11/1764344408_jancuk.jpg', 'arah barat ', '2025-11-28 15:40:08', NULL),
(4, 2, 'uploads/spanduk/2025/11/1764345117_1.png', 'Arah timur', '2025-11-28 15:51:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `spanduk_lokasi`
--

CREATE TABLE `spanduk_lokasi` (
  `id_lokasi` int NOT NULL,
  `id_spanduk` int NOT NULL,
  `lokasi_pasang` varchar(200) NOT NULL COMMENT 'Contoh: Jl. Medan',
  `created_at` timestamp NULL DEFAULT (now()),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `spanduk_lokasi`
--

INSERT INTO `spanduk_lokasi` (`id_lokasi`, `id_spanduk`, `lokasi_pasang`, `created_at`, `updated_at`) VALUES
(2, 2, 'Jl Lapangan Merdeka', '2025-11-28 15:40:08', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `biodata`
--
ALTER TABLE `biodata`
  ADD PRIMARY KEY (`id_biodata`),
  ADD UNIQUE KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `hasil_clustering`
--
ALTER TABLE `hasil_clustering`
  ADD PRIMARY KEY (`id_clustering`),
  ADD KEY `id_kliping` (`id_kliping`);

--
-- Indexes for table `iklan_koran`
--
ALTER TABLE `iklan_koran`
  ADD PRIMARY KEY (`id_iklan`),
  ADD KEY `iklan_koran_index_3` (`nama_media`),
  ADD KEY `iklan_koran_index_4` (`tanggal_terbit`);

--
-- Indexes for table `kliping_berita`
--
ALTER TABLE `kliping_berita`
  ADD PRIMARY KEY (`id_kliping`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `spanduk`
--
ALTER TABLE `spanduk`
  ADD PRIMARY KEY (`id_spanduk`),
  ADD KEY `spanduk_index_5` (`tanggal_pasang`);

--
-- Indexes for table `spanduk_foto`
--
ALTER TABLE `spanduk_foto`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `spanduk_foto_index_8` (`id_lokasi`);

--
-- Indexes for table `spanduk_lokasi`
--
ALTER TABLE `spanduk_lokasi`
  ADD PRIMARY KEY (`id_lokasi`),
  ADD KEY `spanduk_lokasi_index_6` (`id_spanduk`),
  ADD KEY `spanduk_lokasi_index_7` (`lokasi_pasang`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `biodata`
--
ALTER TABLE `biodata`
  MODIFY `id_biodata` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `hasil_clustering`
--
ALTER TABLE `hasil_clustering`
  MODIFY `id_clustering` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `iklan_koran`
--
ALTER TABLE `iklan_koran`
  MODIFY `id_iklan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `kliping_berita`
--
ALTER TABLE `kliping_berita`
  MODIFY `id_kliping` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `spanduk`
--
ALTER TABLE `spanduk`
  MODIFY `id_spanduk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `spanduk_foto`
--
ALTER TABLE `spanduk_foto`
  MODIFY `id_foto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `spanduk_lokasi`
--
ALTER TABLE `spanduk_lokasi`
  MODIFY `id_lokasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `biodata`
--
ALTER TABLE `biodata`
  ADD CONSTRAINT `fk_biodata_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Constraints for table `hasil_clustering`
--
ALTER TABLE `hasil_clustering`
  ADD CONSTRAINT `hasil_clustering_ibfk_1` FOREIGN KEY (`id_kliping`) REFERENCES `kliping_berita` (`id_kliping`);

--
-- Constraints for table `spanduk_foto`
--
ALTER TABLE `spanduk_foto`
  ADD CONSTRAINT `fk_foto_lokasi` FOREIGN KEY (`id_lokasi`) REFERENCES `spanduk_lokasi` (`id_lokasi`);

--
-- Constraints for table `spanduk_lokasi`
--
ALTER TABLE `spanduk_lokasi`
  ADD CONSTRAINT `fk_lokasi_spanduk` FOREIGN KEY (`id_spanduk`) REFERENCES `spanduk` (`id_spanduk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
