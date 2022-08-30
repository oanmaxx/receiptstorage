-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2022 at 12:01 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `receiptstorage`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
                            `id_product` int(11) NOT NULL,
                            `id_receipt` int(11) NOT NULL,
                            `description` varchar(250) NOT NULL,
                            `quantity` int(11) NOT NULL,
                            `unit_price` DECIMAL(9, 2) NOT NULL,
                            `total_price` DECIMAL(9, 2) NOT NULL,
                            `category` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
                            `id_receipt` int(11) NOT NULL,
                            `id_store` int(11) NOT NULL,
                            `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
                          `id_store` int(11) NOT NULL,
                          `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
    ADD PRIMARY KEY (`id_product`),
  ADD KEY `id_receipt` (`id_receipt`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
    ADD PRIMARY KEY (`id_receipt`),
  ADD KEY `id_receipt` (`id_store`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
    ADD PRIMARY KEY (`id_store`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
    ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`id_receipt`) REFERENCES `receipts` (`id_receipt`);

--
-- Constraints for table `stores`
--
ALTER TABLE `receipts`
    ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`id_store`) REFERENCES `stores` (`id_store`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
