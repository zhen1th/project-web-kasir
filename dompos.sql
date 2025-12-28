-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 29, 2025 at 12:27 AM
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
  `Kode_Pemasukkan` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `HIstori` datetime NOT NULL DEFAULT current_timestamp(),
  `Nominal` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemasukkan`
--

INSERT INTO `pemasukkan` (`Kode_Pemasukkan`, `user_id`, `HIstori`, `Nominal`) VALUES
(1, 1, '2025-09-19 15:24:09', 113000),
(2, 2, '2025-09-19 15:29:09', 58000),
(3, 1, '2025-09-19 15:45:06', 163000),
(4, 1, '2025-09-19 16:07:50', 120000),
(5, 2, '2025-07-08 16:11:46', 200000),
(6, 1, '2025-05-14 16:12:46', 130000),
(7, 1, '2025-07-03 16:13:47', 250000),
(8, 1, '2025-08-07 16:13:47', 57000),
(9, 1, '2025-06-04 16:15:12', 470000),
(10, 1, '2025-02-27 16:15:12', 23000),
(11, 1, '2025-09-19 19:39:29', 77000),
(12, 1, '2025-10-16 12:05:23', 77000);

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
-- AUTO_INCREMENT for table `pemasukkan`
--
ALTER TABLE `pemasukkan`
  MODIFY `Kode_Pemasukkan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
