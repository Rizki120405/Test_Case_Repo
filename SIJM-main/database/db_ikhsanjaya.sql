-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 02, 2026 at 12:10 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ikhsanjaya`
--

-- --------------------------------------------------------

--
-- Table structure for table `arus_kas`
--

CREATE TABLE `arus_kas` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('Pemasukan','Pengeluaran') NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `arus_kas`
--

INSERT INTO `arus_kas` (`id`, `tanggal`, `jenis`, `keterangan`, `nominal`, `created_at`) VALUES
(4, '2026-02-24', 'Pengeluaran', 'beli jajan', 5000.00, '2026-02-24 16:44:47'),
(5, '2026-02-24', 'Pengeluaran', 'beli jajan', 50000.00, '2026-02-24 16:54:34');

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `harga_modal` decimal(10,2) NOT NULL,
  `harga_jual` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id`, `kode_barang`, `nama_barang`, `kategori`, `gambar`, `deskripsi`, `stok`, `harga_modal`, `harga_jual`, `created_at`) VALUES
(28, '5011987085961', 'SHELL ADVANCE 0.8', 'OLI', '1771822712_699bde7808f8a.jpg', '', 0, 45000.00, 50000.00, '2026-02-23 04:58:32'),
(29, '5011987085947', 'SHELL ADVANCE 1L', 'OLI', NULL, '', 1, 70000.00, 75000.00, '2026-02-23 05:00:03'),
(30, '01022094', 'MINYAK REM 946ML', 'CAIRAN', NULL, '', 2, 50000.00, 60000.00, '2026-02-23 05:01:30'),
(31, '8992408015316', 'EVALUBE PRO 2T 0.7L', 'OLI', NULL, '', 1, 30000.00, 35000.00, '2026-02-23 05:02:52'),
(32, '8886351382000', 'MXR MATIC 800ML', 'OLI', '1771823165_699be03daf20d.jpg', '', 12, 50000.00, 55000.00, '2026-02-23 05:06:05'),
(33, '8991011328073', 'ENDURO 1L', 'OLI', '1771823413_699be135330f6.jpg', '', 3, 60000.00, 65000.00, '2026-02-23 05:10:13'),
(34, '8991011104929', 'MESRAN 1L', 'OLI', '1771823533_699be1adb6ee8.jpg', '', 2, 40000.00, 45000.00, '2026-02-23 05:12:13'),
(35, '01323506', 'DELTALUBE 1L', 'OLI', '1771823671_699be2370b5ca.jpg', '', 2, 90000.00, 95000.00, '2026-02-23 05:14:31'),
(36, '8997208770106', 'FEDERAL ULTRATEC 1L', 'OLI', '1771823959_699be357d5e3f.jpg', '', 4, 55000.00, 60000.00, '2026-02-23 05:19:19'),
(37, '082322MAK1LN5', 'MPX1 1L', 'OLI', '1771824140_699be40c1c9a7.jpg', '', 1, 60000.00, 65000.00, '2026-02-23 05:22:20'),
(38, '8997208770090', 'FEDERAL ULTRATEC', 'OLI', '1771824312_699be4b804bac.jpg', '', 1, 45000.00, 50000.00, '2026-02-23 05:25:12'),
(39, '70202847', 'YAMALUBE MATIC 0.8L', 'OLI', '1771824452_699be544a07af.jpg', '', 8, 45000.00, 50000.00, '2026-02-23 05:27:32'),
(40, '8992408015514', 'EVALUBE 2T 0.7L', 'OLI', '1771824549_699be5a51fe44.jpg', '', 2, 25000.00, 30000.00, '2026-02-23 05:29:09'),
(41, '90793AJA95', 'YAMALUBE SUPER MATIC 1L', 'OLI', '1771824882_699be6f226ba8.jpg', '', 2, 67000.00, 72000.00, '2026-02-23 05:34:42'),
(42, '90703AJ922', 'YAMALUBE SILVER 0.8L', 'OLI', '1771825066_699be7aa441a9.jpg', '', 6, 45000.00, 50000.00, '2026-02-23 05:37:46'),
(43, '082322MAK0LN9', 'MPX1 0.8L', 'OLI', '1771825208_699be838d0233.jpg', '', 3, 50000.00, 55000.00, '2026-02-23 05:40:08'),
(44, '08232M99K0LN5', 'MPX2 0.8L', 'OLI', '1771825315_699be8a3aeaef.jpg', '', 2, 49000.00, 56000.00, '2026-02-23 05:41:55'),
(45, '8997007852515', 'M-ONE 350ML', 'CAIRAN', '1771825451_699be92bd1712.jpg', '', 14, 30000.00, 40000.00, '2026-02-23 05:44:11'),
(47, '8997015880081', 'MEGACOOL CHAIN LUBE 300ML', 'CAIRAN', '1771826013_699beb5dc8b8a.jpg', '', 7, 25000.00, 35000.00, '2026-02-23 05:53:33'),
(48, '12-2502751400000', 'Ban Dalam Aspira R14 80/90', 'Ban', '1771912466_699d3d12d3caa.jpg', 'Cocok untuk semua jenis motor yang menggunakan ban R 14 80/90 90/90', 2, 23000.00, 45000.00, '2026-02-24 05:54:26');

-- --------------------------------------------------------

--
-- Table structure for table `cucian_karpet`
--

CREATE TABLE `cucian_karpet` (
  `id` int(11) NOT NULL,
  `nama_pemilik` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `tanggal_antar` date NOT NULL,
  `jumlah_karpet` int(11) NOT NULL,
  `deskripsi` text NOT NULL,
  `total_harga` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status_pembayaran` enum('Belum Bayar','Sudah Bayar') NOT NULL DEFAULT 'Belum Bayar',
  `status_cucian` enum('Antre','Dicuci','Selesai','Diambil') NOT NULL DEFAULT 'Antre',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('antri','dicuci','selesai','diambil') DEFAULT 'antri',
  `tanggal_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cucian_karpet`
--

INSERT INTO `cucian_karpet` (`id`, `nama_pemilik`, `no_hp`, `tanggal_antar`, `jumlah_karpet`, `deskripsi`, `total_harga`, `status_pembayaran`, `status_cucian`, `created_at`, `status`, `tanggal_selesai`) VALUES
(3, 'Maxim', '-', '2026-02-24', 3, '1 karpet 3x4 jumbo merah tebal\r\n1 karpet 2x3 sedang hitam tebal tanpa rumbai\r\n1 karpet sejadah hijau ada rumbai putih', 175000.00, 'Sudah Bayar', 'Diambil', '2026-02-24 05:38:00', 'antri', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `jenis_item` varchar(50) DEFAULT NULL,
  `id_item` int(11) NOT NULL,
  `nama_item` varchar(255) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `id_transaksi`, `jenis_item`, `id_item`, `nama_item`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(39, 39, 'layanan', 1, 'Cuci Motor Biasa Kecil', 1, 12000.00, 12000.00),
(40, 40, 'layanan', 3, 'Service Ringan', 1, 50000.00, 50000.00),
(41, 41, 'layanan', 2, 'Cuci Motor Biasa Besar', 1, 15000.00, 15000.00),
(42, 42, 'layanan', 1, 'Cuci Motor Biasa Kecil', 1, 12000.00, 12000.00),
(43, 43, 'layanan', 1, 'Cuci Motor Biasa Kecil', 1, 12000.00, 12000.00),
(44, 44, 'layanan', 2, 'Cuci Motor Biasa Besar', 1, 15000.00, 15000.00),
(45, 45, 'layanan', 1, 'Cuci Motor Biasa Kecil', 1, 12000.00, 12000.00),
(46, 46, 'layanan', 1, 'Cuci Motor Biasa Kecil', 1, 12000.00, 12000.00),
(47, 47, 'barang', 29, 'SHELL ADVANCE 1L', 1, 75000.00, 75000.00),
(48, 48, 'layanan', 1, 'Cuci Motor Biasa Kecil', 1, 12000.00, 12000.00),
(49, 49, 'barang', 31, 'EVALUBE PRO 2T 0.7L', 1, 35000.00, 35000.00),
(50, 50, 'layanan', 2, 'Cuci Motor Biasa Besar', 1, 15000.00, 15000.00),
(51, 51, 'layanan', 1, 'Cuci Motor Biasa Kecil', 1, 12000.00, 12000.00);

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id` int(11) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`id`, `nama_layanan`, `harga`) VALUES
(1, 'Cuci Motor Biasa Kecil', 12000.00),
(2, 'Cuci Motor Biasa Besar', 15000.00),
(3, 'Service Ringan', 50000.00);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_stok`
--

CREATE TABLE `riwayat_stok` (
  `id` int(11) NOT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_stok`
--

INSERT INTO `riwayat_stok` (`id`, `id_barang`, `jenis`, `jumlah`, `keterangan`, `tanggal`) VALUES
(21, 28, 'keluar', 1, 'Terjual di kasir (Struk: TRX-20260224054734)', '2026-02-24 11:47:34'),
(22, 29, 'keluar', 1, 'Terjual di kasir (Struk: TRX-20260224094024)', '2026-02-24 15:40:24'),
(23, 31, 'keluar', 1, 'Terjual di kasir (Struk: TRX-20260224094059)', '2026-02-24 15:40:59');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(20) NOT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `plat_nomor` varchar(20) DEFAULT NULL,
  `total_bayar` decimal(10,2) NOT NULL,
  `status` enum('selesai','dibatalkan') DEFAULT 'selesai',
  `tanggal` datetime DEFAULT current_timestamp(),
  `kasir_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `nama_pelanggan`, `plat_nomor`, `total_bayar`, `status`, `tanggal`, `kasir_id`) VALUES
(39, 'TRX-20260224093849', 'vario', '', 12000.00, 'selesai', '2026-02-24 15:38:49', 1),
(40, 'TRX-20260224093855', 'beat', '', 50000.00, 'selesai', '2026-02-24 15:38:55', 1),
(41, 'TRX-20260224093902', 'genio', '', 15000.00, 'selesai', '2026-02-24 15:39:02', 1),
(42, 'TRX-20260224093919', 'scoopy', '', 12000.00, 'selesai', '2026-02-24 15:39:19', 1),
(43, 'TRX-20260224093926', 'supra', '', 12000.00, 'selesai', '2026-02-24 15:39:26', 1),
(44, 'TRX-20260224093935', 'maxim', '', 15000.00, 'selesai', '2026-02-24 15:39:35', 1),
(45, 'TRX-20260224093942', 'supra', '', 12000.00, 'selesai', '2026-02-24 15:39:42', 1),
(46, 'TRX-20260224093959', 'vario', '', 12000.00, 'selesai', '2026-02-24 15:39:59', 1),
(47, 'TRX-20260224094024', 'genio', '', 75000.00, 'selesai', '2026-02-24 15:40:24', 1),
(48, 'TRX-20260224094040', 'next', '', 12000.00, 'selesai', '2026-02-24 15:40:40', 1),
(49, 'TRX-20260224094059', 'Supra', '', 35000.00, 'selesai', '2026-02-24 15:40:59', 1),
(50, 'TRX-20260224094109', 'Budi', '', 15000.00, 'dibatalkan', '2026-02-24 15:41:09', 1),
(51, 'TRX-20260224105800', 'suzuki', '', 12000.00, 'selesai', '2026-02-24 16:58:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir') DEFAULT 'kasir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', 'admin123', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `arus_kas`
--
ALTER TABLE `arus_kas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`);

--
-- Indexes for table `cucian_karpet`
--
ALTER TABLE `cucian_karpet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `kasir_id` (`kasir_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `arus_kas`
--
ALTER TABLE `arus_kas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `cucian_karpet`
--
ALTER TABLE `cucian_karpet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`);

--
-- Constraints for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD CONSTRAINT `riwayat_stok_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
