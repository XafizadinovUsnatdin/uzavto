-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 25, 2025 at 10:40 AM
-- Server version: 5.6.41
-- PHP Version: 5.5.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uzauto`
--

-- --------------------------------------------------------

--
-- Table structure for table `car_models`
--

CREATE TABLE `car_models` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `car_models`
--

INSERT INTO `car_models` (`model_id`, `model_name`) VALUES
(1, 'Spark'),
(2, 'Nexia'),
(3, 'Cobalt'),
(4, 'Lacetti'),
(5, 'Tracker'),
(6, 'Malibu'),
(7, 'Onix'),
(8, 'Captiva');

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `color_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`color_id`, `color_name`) VALUES
(1, 'Oq'),
(2, 'Qora'),
(3, 'Kumush'),
(4, 'Qizil'),
(5, 'Ko‘k'),
(6, 'Kulrang'),
(7, 'Yashil'),
(8, 'Oltin');

-- --------------------------------------------------------

--
-- Table structure for table `dealers`
--

CREATE TABLE `dealers` (
  `dealer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `economic_index` enum('high','medium','low') NOT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `branch_location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dealers`
--

INSERT INTO `dealers` (`dealer_id`, `name`, `location`, `latitude`, `longitude`, `economic_index`, `branch_name`, `branch_location`) VALUES
(1, 'UzAuto Toshkent Shahri', 'Toshkent Shahri', '41.299500', '69.240100', 'high', NULL, NULL),
(2, 'UzAuto Samarqand', 'Samarqand', '39.654200', '66.959700', 'high', NULL, NULL),
(3, 'UzAuto Andijon', 'Andijon', '40.781300', '72.344200', 'high', NULL, NULL),
(4, 'UzAuto Buxoro', 'Buxoro', '39.774700', '64.428600', 'medium', NULL, NULL),
(5, 'UzAuto Namangan', 'Namangan', '40.998300', '71.672600', 'medium', NULL, NULL),
(6, 'UzAuto Farg‘ona', 'Farg‘ona', '40.373400', '71.797900', 'medium', NULL, NULL),
(7, 'UzAuto Xorazm', 'Xorazm', '41.553700', '60.627700', 'medium', NULL, NULL),
(8, 'UzAuto Qashqadaryo', 'Qashqadaryo', '38.860600', '65.791700', 'medium', NULL, NULL),
(9, 'UzAuto Navoiy', 'Navoiy', '40.103300', '65.371500', 'medium', NULL, NULL),
(10, 'UzAuto Jizzax', 'Jizzax', '40.115700', '67.842200', 'low', NULL, NULL),
(11, 'UzAuto Surxondaryo', 'Surxondaryo', '37.236100', '67.278300', 'low', NULL, NULL),
(12, 'UzAuto Sirdaryo', 'Sirdaryo', '40.803700', '68.661700', 'low', NULL, NULL),
(13, 'UzAuto Toshkent Viloyati', 'Toshkent Viloyati', '41.266700', '69.216700', 'low', NULL, NULL),
(14, 'UzAuto Qoraqalpog‘iston', 'Qoraqalpog‘iston', '43.804100', '56.249100', 'low', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `dealer_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `income` decimal(15,2) NOT NULL,
  `jins` enum('Erkak','Ayol') NOT NULL,
  `yosh` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `dealer_id`, `model_id`, `color_id`, `year`, `month`, `quantity_sold`, `income`, `jins`, `yosh`) VALUES
(1, 1, 1, 1, 2024, 1, 15, '150000.00', 'Erkak', 35),
(2, 1, 2, 2, 2024, 2, 12, '144000.00', 'Ayol', 40),
(3, 1, 3, 3, 2024, 3, 14, '210000.00', 'Erkak', 28),
(4, 1, 4, 4, 2024, 4, 10, '180000.00', 'Ayol', 45),
(5, 1, 5, 5, 2024, 5, 9, '180000.00', 'Erkak', 32),
(6, 1, 6, 6, 2024, 6, 8, '240000.00', 'Ayol', 50),
(7, 1, 7, 7, 2024, 7, 13, '208000.00', 'Erkak', 30),
(8, 1, 8, 8, 2024, 8, 11, '275000.00', 'Ayol', 38),
(9, 1, 1, 1, 2024, 9, 16, '160000.00', 'Erkak', 36),
(10, 1, 2, 2, 2024, 10, 13, '156000.00', 'Ayol', 41),
(11, 1, 3, 3, 2024, 11, 15, '225000.00', 'Erkak', 29),
(12, 1, 4, 4, 2024, 12, 11, '198000.00', 'Ayol', 46),
(13, 1, 1, 1, 2025, 1, 17, '170000.00', 'Erkak', 36),
(14, 1, 2, 2, 2025, 2, 14, '168000.00', 'Ayol', 41),
(15, 1, 3, 3, 2025, 3, 16, '240000.00', 'Erkak', 29),
(16, 1, 4, 4, 2025, 4, 12, '216000.00', 'Ayol', 46),
(17, 1, 5, 5, 2025, 5, 10, '210000.00', 'Erkak', 33),
(18, 1, 6, 6, 2025, 6, 9, '279000.00', 'Ayol', 51),
(19, 1, 7, 7, 2025, 7, 14, '224000.00', 'Erkak', 31),
(20, 1, 8, 8, 2025, 8, 12, '312000.00', 'Ayol', 39),
(21, 2, 1, 2, 2024, 1, 14, '140000.00', 'Erkak', 34),
(22, 2, 2, 3, 2024, 2, 11, '132000.00', 'Ayol', 42),
(23, 2, 3, 4, 2024, 3, 13, '195000.00', 'Erkak', 27),
(24, 2, 4, 5, 2024, 4, 9, '162000.00', 'Ayol', 48),
(25, 2, 5, 6, 2024, 5, 8, '152000.00', 'Erkak', 31),
(26, 2, 6, 7, 2024, 6, 7, '203000.00', 'Ayol', 52),
(27, 2, 7, 8, 2024, 7, 12, '180000.00', 'Erkak', 29),
(28, 2, 8, 1, 2024, 8, 10, '240000.00', 'Ayol', 37),
(29, 2, 1, 2, 2024, 9, 15, '150000.00', 'Erkak', 35),
(30, 2, 2, 3, 2024, 10, 12, '144000.00', 'Ayol', 43),
(31, 2, 3, 4, 2024, 11, 14, '210000.00', 'Erkak', 28),
(32, 2, 4, 5, 2024, 12, 10, '180000.00', 'Ayol', 49),
(33, 2, 1, 2, 2025, 1, 15, '150000.00', 'Erkak', 35),
(34, 2, 2, 3, 2025, 2, 12, '144000.00', 'Ayol', 43),
(35, 2, 3, 4, 2025, 3, 14, '210000.00', 'Erkak', 28),
(36, 2, 4, 5, 2025, 4, 10, '180000.00', 'Ayol', 49),
(37, 2, 5, 6, 2025, 5, 9, '171000.00', 'Erkak', 32),
(38, 2, 6, 7, 2025, 6, 8, '240000.00', 'Ayol', 53),
(39, 2, 7, 8, 2025, 7, 13, '208000.00', 'Erkak', 30),
(40, 2, 8, 1, 2025, 8, 11, '275000.00', 'Ayol', 38),
(41, 2, 1, 2, 2025, 9, 16, '160000.00', 'Erkak', 36),
(42, 2, 2, 3, 2025, 10, 13, '156000.00', 'Ayol', 44),
(43, 2, 3, 4, 2025, 11, 15, '225000.00', 'Erkak', 29),
(44, 2, 4, 5, 2025, 12, 12, '216000.00', 'Ayol', 50),
(45, 3, 1, 3, 2024, 1, 13, '130000.00', 'Erkak', 33),
(46, 3, 2, 4, 2024, 2, 10, '120000.00', 'Ayol', 41),
(47, 3, 3, 5, 2024, 3, 12, '156000.00', 'Erkak', 26),
(48, 3, 4, 6, 2024, 4, 8, '144000.00', 'Ayol', 47),
(49, 3, 5, 7, 2024, 5, 7, '126000.00', 'Erkak', 30),
(50, 3, 6, 8, 2024, 6, 6, '168000.00', 'Ayol', 51),
(51, 3, 7, 1, 2024, 7, 11, '154000.00', 'Erkak', 28),
(52, 3, 8, 2, 2024, 8, 9, '207000.00', 'Ayol', 36),
(53, 3, 1, 3, 2024, 9, 14, '140000.00', 'Erkak', 34),
(54, 3, 2, 4, 2024, 10, 11, '132000.00', 'Ayol', 42),
(55, 3, 3, 5, 2024, 11, 13, '169000.00', 'Erkak', 27),
(56, 3, 4, 6, 2024, 12, 9, '162000.00', 'Ayol', 48),
(57, 3, 1, 3, 2025, 1, 14, '140000.00', 'Erkak', 34),
(58, 3, 2, 4, 2025, 2, 11, '132000.00', 'Ayol', 42),
(59, 3, 3, 5, 2025, 3, 13, '169000.00', 'Erkak', 27),
(60, 3, 4, 6, 2025, 4, 9, '162000.00', 'Ayol', 48),
(61, 3, 5, 7, 2025, 5, 8, '144000.00', 'Erkak', 31),
(62, 3, 6, 8, 2025, 6, 7, '189000.00', 'Ayol', 52),
(63, 3, 7, 1, 2025, 7, 12, '168000.00', 'Erkak', 29),
(64, 3, 8, 2, 2025, 8, 10, '230000.00', 'Ayol', 37),
(65, 3, 1, 3, 2025, 9, 15, '150000.00', 'Erkak', 35),
(66, 3, 2, 4, 2025, 10, 12, '144000.00', 'Ayol', 43),
(67, 3, 3, 5, 2025, 11, 14, '210000.00', 'Erkak', 28),
(68, 3, 4, 6, 2025, 12, 10, '180000.00', 'Ayol', 49);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `car_models`
--
ALTER TABLE `car_models`
  ADD PRIMARY KEY (`model_id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`color_id`);

--
-- Indexes for table `dealers`
--
ALTER TABLE `dealers`
  ADD PRIMARY KEY (`dealer_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `dealer_id` (`dealer_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `color_id` (`color_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`dealer_id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `car_models` (`model_id`),
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`color_id`) REFERENCES `colors` (`color_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
