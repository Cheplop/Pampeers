-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 08:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pampeers`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `sitter_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `parent_id`, `sitter_id`, `booking_date`, `status`) VALUES
(1, 2, 1, '2026-03-22', 'confirmed'),
(2, 2, 1, '2026-03-22', 'pending'),
(3, 2, 1, '2026-03-22', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rID` int(11) NOT NULL,
  `uID` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rID`, `uID`, `rating`, `comment`, `created_at`) VALUES
(1, 0, 5, 'wow', '2026-03-22 09:41:00');

-- --------------------------------------------------------

--
-- Table structure for table `sitterprofile`
--

CREATE TABLE `sitterprofile` (
  `sitterID` int(11) NOT NULL,
  `hourlyRate` decimal(10,2) NOT NULL,
  `bio` text NOT NULL,
  `uID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uID` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `country` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `street` varchar(100) NOT NULL,
  `birthDate` date NOT NULL,
  `password` varchar(150) NOT NULL,
  `sex` enum('male','female','prefer not say') NOT NULL,
  `role` enum('parent','sitter') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uID`, `firstName`, `lastName`, `email`, `country`, `city`, `street`, `birthDate`, `password`, `sex`, `role`) VALUES
(1, '', '', 'sean@gmail.com', '', '', '', '0000-00-00', '$2y$10$wDH2YlIJhe1iyInpciUtDe9fO7o5thpZEOJfCTfZmwoTCJyGQfKtu', 'male', 'sitter'),
(2, '', '', 'seyan@gmail.com', '', '', '', '0000-00-00', '$2y$10$qFtsMSVbSDI9PQqbqASgQOABGaZnv1BU/9KK/y2pLe0piHQx.0NvC', 'male', 'parent'),
(3, '', '', 'rem@gmail.com', '', '', '', '0000-00-00', '$2y$10$XlLuhMzFP19rWHP3KU9nK.0Lv2CZ9qBWiGTIrH60mi1xEijkkV1iS', 'male', 'sitter');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `sitter_id` (`sitter_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rID`),
  ADD KEY `uID` (`uID`);

--
-- Indexes for table `sitterprofile`
--
ALTER TABLE `sitterprofile`
  ADD PRIMARY KEY (`sitterID`),
  ADD KEY `fk_user` (`uID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sitterprofile`
--
ALTER TABLE `sitterprofile`
  MODIFY `sitterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sitterprofile`
--
ALTER TABLE `sitterprofile`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`uID`) REFERENCES `users` (`uid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
