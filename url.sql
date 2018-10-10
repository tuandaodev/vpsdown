-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2018 at 12:07 PM
-- Server version: 10.1.31-MariaDB
-- PHP Version: 5.6.35

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vps_down`
--

-- --------------------------------------------------------

--
-- Table structure for table `url`
--

CREATE TABLE `url` (
  `id` int(11) NOT NULL,
  `uid` varchar(30) NOT NULL,
  `url` varchar(512) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `created` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `url`
--

INSERT INTO `url` (`id`, `uid`, `url`, `type`, `created`) VALUES
(1, '5bbdca1432ef1', 'http%3A%2F%2Ftuandaoit.me%2Falbum', 1, 2147483647),
(2, '5bbdcc81afa0e', 'https%3A%2F%2Farchive.org%2Fdownload%2Fapkmodeio%2F14182-MORTAL-KOMBAT-X-v1-19-0-cache-Tegra.zip', 1, 2147483647),
(3, '5bbdce66bda2a', 'https%3A%2F%2Fdrive.google.com%2Ffile%2Fd%2F1kZitY5JudMvaLS3-7VNk2D1B9Uuis-5U%2Fview%3Fusp%3Dsharing', 2, 2147483647),
(4, '5bbdcecdb0f5e', 'http%3A%2F%2Fdemo.quynhon.gov.vn%2Falbum', 1, 2147483647);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `url`
--
ALTER TABLE `url`
  ADD PRIMARY KEY (`id`,`uid`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `url`
--
ALTER TABLE `url`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
