-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 28, 2026 at 10:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dompos`
--

-- --------------------------------------------------------

--
-- Table structure for table `laporan_analitik`
--

CREATE TABLE `laporan_analitik` (
  `Id_Pemasukkan` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `Histori_Pemasukkan` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Nominal_Pemasukkan` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newproduct`
--

CREATE TABLE `newproduct` (
  `Id_Produk` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `Nama_Produk` varchar(100) NOT NULL,
  `Harga_Produk` int(11) NOT NULL,
  `Kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newproduct`
--

INSERT INTO `newproduct` (`Id_Produk`, `user_id`, `Nama_Produk`, `Harga_Produk`, `Kategori`) VALUES
(1, 1, 'Ayam Bekutut', 21000, 'Makanan'),
(2, 1, 'Ayam Laos', 25000, 'Makanan'),
(3, 2, 'Es Doger', 11000, 'Minuman'),
(5, 2, 'Sosis Bakar', 5000, 'Snack'),
(6, 3, 'Kepala Bayi', 9000000, 'Makanan'),
(7, 1, 'Katsu', 10000, 'Makanan'),
(8, 2, 'Cilok', 5000, 'Snack'),
(9, 1, 'Kopi', 21000, 'Minuman');

-- --------------------------------------------------------

--
-- Table structure for table `pemasukkan`
--

CREATE TABLE `pemasukkan` (
  `Kode_Pemasukkan` varchar(15) NOT NULL,
  `user_id` int(11) NOT NULL,
  `HIstori` datetime NOT NULL DEFAULT current_timestamp(),
  `Nominal` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemasukkan`
--

INSERT INTO `pemasukkan` (`Kode_Pemasukkan`, `user_id`, `HIstori`, `Nominal`) VALUES
('1', 1, '2025-09-19 15:24:09', 113000),
('D0012601001', 1, '2026-01-15 18:40:32', 46000),
('D0012601002', 1, '2026-01-15 19:06:05', 56000);

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `pengeluaran_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_pengeluaran` varchar(50) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `nominal` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal_pengeluaran` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`pengeluaran_id`, `user_id`, `jenis_pengeluaran`, `nama_barang`, `nominal`, `keterangan`, `tanggal_pengeluaran`) VALUES
(1, 1, 'Belanja', 'Bahan Baku Ayam', 500000, 'Beli ayam 5 kg untuk stok 1 minggu', '2026-01-10 09:00:00'),
(2, 1, 'Operasional', 'Listrik Bulanan', 350000, 'Tagihan listrik bulan Januari', '2026-01-05 10:30:00'),
(3, 1, 'Gaji', 'Gaji Karyawan', 1500000, 'Gaji 2 orang karyawan kasir', '2026-01-01 08:00:00'),
(4, 1, 'Belanja', 'Minuman Kemasan', 250000, 'Aqua gelas 5 dus, teh botol 2 dus', '2026-01-12 14:15:00'),
(5, 2, 'Belanja', 'Es Batu', 75000, 'Es batu 10 kg untuk es doger', '2026-01-08 11:45:00'),
(6, 2, 'Operasional', 'Air PDAM', 120000, 'Tagihan air bulan Januari', '2026-01-03 09:20:00'),
(7, 1, 'Perbaikan', 'Mesin Kasir', 300000, 'Service mesin kasir rusak', '2026-01-15 13:30:00'),
(8, 3, 'Belanja', 'Bahan Baku Kue', 600000, 'Tepung 10 kg, gula 5 kg, telur 2 kg', '2026-01-14 10:00:00'),
(9, 1, 'Transportasi', 'Bensin', 50000, 'Bensin motor untuk delivery', '2026-01-15 16:00:00'),
(10, 1, 'Lainnya', 'Kebutuhan Toko', 100000, 'Plastik kemasan, tissue, sedotan', '2026-01-13 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`) VALUES
(1, 'papalova', 'rihalwijaya@gmail.com', '081226080361', '$2b$10$xPSOJjbY.J7KK5u.NNF4U.8zhgpG2EnOJA3dzf4Xbalgi3pmjUplm'),
(2, 'mamalova', 'drivelovv2@gmail.com', '089694323148', '$2b$10$AmxAg2EuvGH3yJvGrQGr.OO6SrRMZ10O937CEQUvgGDZ6ArhmWlwi'),
(3, 'CendolHitam', 'alvinsukafoto@gmail.com', '089694323148', '$2b$10$g.L5oWy1kA9uvWWo7claTOvT6eWnePHZ1U0qX460Aw9G7r7I//nBC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `laporan_analitik`
--
ALTER TABLE `laporan_analitik`
  ADD PRIMARY KEY (`Id_Pemasukkan`),
  ADD KEY `fk_laporan_user` (`user_id`);

--
-- Indexes for table `newproduct`
--
ALTER TABLE `newproduct`
  ADD PRIMARY KEY (`Id_Produk`),
  ADD KEY `fk_product_user` (`user_id`);

--
-- Indexes for table `pemasukkan`
--
ALTER TABLE `pemasukkan`
  ADD PRIMARY KEY (`Kode_Pemasukkan`),
  ADD KEY `fk_pemasukkan_user` (`user_id`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`pengeluaran_id`),
  ADD KEY `fk_pengeluaran_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `laporan_analitik`
--
ALTER TABLE `laporan_analitik`
  MODIFY `Id_Pemasukkan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newproduct`
--
ALTER TABLE `newproduct`
  MODIFY `Id_Produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `pengeluaran_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laporan_analitik`
--
ALTER TABLE `laporan_analitik`
  ADD CONSTRAINT `fk_laporan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `newproduct`
--
ALTER TABLE `newproduct`
  ADD CONSTRAINT `fk_product_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pemasukkan`
--
ALTER TABLE `pemasukkan`
  ADD CONSTRAINT `fk_pemasukkan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `fk_pengeluaran_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
