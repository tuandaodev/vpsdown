-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2018 at 11:06 AM
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
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `id` int(11) NOT NULL,
  `uid` varchar(100) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `updated` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`id`, `uid`, `name`, `type`, `updated`, `status`) VALUES
(1, '5bd14dc7f421a', 'clean-code-a-handbook-of-agile-software-craftmanship_1540458115.pdf', 1, 1540458150, 1),
(2, '5bd17e5aa4d7a', 'win10_1803_english_x64_1540458166.iso', 1, 1540458166, 0);

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
(11, '5bc556c371209', 'https%3A%2F%2Fdrive.google.com%2Ffile%2Fd%2F1MV9XqLbEvkSXT9MnJ5-bfsC3GaXnDohR%2Fview', 2, 1539659459),
(14, '5bc55f6aeb720', 'https%3A%2F%2Fdrive.google.com%2Ffile%2Fd%2F1bvSt-BaGrGAINSUaCTzlaaIjD23_PJQI%2Fview%3Fusp%3Dsharing', 2, 1539661674),
(15, '5bd12eaeac21e', 'https%3A%2F%2Fdrive.google.com%2Ffile%2Fd%2F0B6uqdCPXiGykT0ZLWUNBeHpSbEk%2Fview%3Fusp%3Dsharing', 2, 1540435630),
(16, '5bd14dc7f421a', 'https%3A%2F%2Fdrive.google.com%2Ffile%2Fd%2F0B-hV1HrMP8j1ZURvZW10UmVSbUU%2Fview%3Fusp%3Dsharing', 2, 1540443591),
(17, '5bd17e5aa4d7a', 'https%3A%2F%2Fsoftware-download.microsoft.com%2Fdb%2FWin10_1803_English_x64.iso%3Ft%3Def6b4f95-fd00-4b68-83f3-c93f4928930e%26e%3D1540460023%26h%3D41db480e083dd292f0e270a9dfce6377', 1, 1540456026);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(100) NOT NULL,
  `active` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `active`) VALUES
(1, 'admin', 'ecd00aa1acd325ba7575cb0f638b04a5', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`id`,`uid`);

--
-- Indexes for table `url`
--
ALTER TABLE `url`
  ADD PRIMARY KEY (`id`,`uid`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_UNIQUE` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cache`
--
ALTER TABLE `cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `url`
--
ALTER TABLE `url`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
