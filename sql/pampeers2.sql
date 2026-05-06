-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 06, 2026 at 05:24 AM
-- Server version: 11.8.6-MariaDB-0+deb13u1 from Debian
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pampeers2`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `bookingID` int(11) NOT NULL,
  `uuid` char(36) NOT NULL,
  `userID` int(11) NOT NULL,
  `sitterID` int(11) NOT NULL,
  `bookingDate` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `hoursRequested` decimal(5,2) DEFAULT NULL,
  `totalAmount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','accepted','declined','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`bookingID`, `uuid`, `userID`, `sitterID`, `bookingDate`, `startTime`, `endTime`, `hoursRequested`, `totalAmount`, `status`, `notes`, `createdAt`) VALUES
(1, '54c7deaf-29b1-49dd-a33f-886e3c246059', 4, 3, '2026-12-31', '00:31:00', '12:31:00', 12.00, 0.00, 'accepted', '', '2026-05-05 08:45:50');

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE `favourites` (
  `id` int(11) NOT NULL,
  `guardian_id` int(11) NOT NULL,
  `sitter_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `reviewID` int(11) NOT NULL,
  `uuid` char(36) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `sitterID` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sitters`
--

CREATE TABLE `sitters` (
  `sitterID` int(11) NOT NULL,
  `uuid` char(36) NOT NULL,
  `userID` int(11) NOT NULL,
  `hourlyRate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `experience` int(11) NOT NULL DEFAULT 0,
  `isAvailable` tinyint(1) NOT NULL DEFAULT 1,
  `ratingAverage` decimal(3,2) DEFAULT NULL,
  `verificationStatus` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sitters`
--

INSERT INTO `sitters` (`sitterID`, `uuid`, `userID`, `hourlyRate`, `experience`, `isAvailable`, `ratingAverage`, `verificationStatus`, `createdAt`) VALUES
(2, '460b5d85478ef4979c30cc96c1e63413', 2, 0.00, 0, 0, NULL, 'verified', '2026-05-02 06:18:52'),
(3, '0fe63d8db80e820aa406e20b1b190b76', 3, 20.00, 2, 1, NULL, 'verified', '2026-05-02 13:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uuid` char(36) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) NOT NULL,
  `bio` text NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `birthDate` date NOT NULL,
  `sex` enum('male','female','other') NOT NULL,
  `role` enum('guardian','sitter','admin') NOT NULL DEFAULT 'guardian',
  `contactNumber` varchar(20) NOT NULL,
  `emailAddress` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `streetAddress` varchar(255) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `cityMunicipality` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `zipCode` char(10) NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `profilePic` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `isActive` tinyint(1) NOT NULL DEFAULT 1,
  `deactivatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `firstName`, `middleName`, `lastName`, `bio`, `suffix`, `birthDate`, `sex`, `role`, `contactNumber`, `emailAddress`, `username`, `password`, `streetAddress`, `barangay`, `cityMunicipality`, `province`, `country`, `zipCode`, `dateCreated`, `profilePic`, `isActive`, `deactivatedAt`) VALUES
(1, '22951542-4596-11f1-8ef7-d822244c147e', 'Nea', '', 'Satunero', '', '', '2005-10-11', 'female', 'admin', '0912 345 6789', 'sean@gmail.com', 'aengela', '$2y$12$yv4Rg3TArLLCjSR6CCRw5OWEKHyFyANk13EuKZoBEG/vuijeJkJBu', 'Agora', 'Lapasan', 'Cagayan de Oro', 'Misamis Oriental', 'Philippines', '9000', '2026-05-02 03:09:32', '1777699320_572852305076.jpeg', 1, NULL),
(2, '87cf60a9-45b0-11f1-8ef7-d822244c147e', 'Remiel', '', 'Fugnit', '', '', '2003-12-30', 'male', 'guardian', 'asdasdasd', 'rem@gmail.com', 'remyel', '$2y$12$SBRHnm.AG8arUdRMSxd35OcPi0Xa7pI7wNTQ83SlcNNdE8woIE0ly', 'asdad', 'asdasd', 'asdasd', 'dsadsad', 'asdasd', 'asdasd', '2026-05-02 06:18:29', 'default.jpg', 1, NULL),
(3, 'c40cfb24-45fe-11f1-8c32-d0008dc532ac', 'Clark', '', 'Galleon', '', '', '2003-12-06', 'male', 'guardian', '092323453', 'clark@gmail.com', 'clarkbayot', '$2y$12$a5psCRFkmqApl45QE5plreX15kBMw3PKtw2LSDbc0mKTXV07e4Qb6', 'Burgos', 'Consolacion', 'Cagayan de Oro', 'Misamis Oriental', '', '9000', '2026-05-02 12:51:59', 'default.jpg', 1, NULL),
(4, 'c15e5eb1-46bf-11f1-9f98-08f3aa7c6ca8', 'Nea', NULL, 'Satunero', '', NULL, '2005-10-11', 'female', 'guardian', '0912 435 3456', 'nea@gmail.com', 'satunero', '$2y$12$8RVv0.8oHWy2DGzl4Zvw7u7nPQVconOPdqWGBNJNySxtkQoWahp0K', 'Agora', 'Lapasan', 'Cagayan de Oro', 'Misamis Oriental', 'Philippines', '9000', '2026-05-03 10:53:17', 'default.jpg', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`bookingID`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `fk_bookings_user` (`userID`),
  ADD KEY `fk_bookings_sitter` (`sitterID`);

--
-- Indexes for table `favourites`
--
ALTER TABLE `favourites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`guardian_id`,`sitter_id`),
  ADD KEY `sitter_id` (`sitter_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`reviewID`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `fk_reviews_booking` (`bookingID`),
  ADD KEY `fk_reviews_user` (`userID`),
  ADD KEY `fk_reviews_sitter` (`sitterID`);

--
-- Indexes for table `sitters`
--
ALTER TABLE `sitters`
  ADD PRIMARY KEY (`sitterID`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `userID` (`userID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `bookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `favourites`
--
ALTER TABLE `favourites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `reviewID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sitters`
--
ALTER TABLE `sitters`
  MODIFY `sitterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_sitter` FOREIGN KEY (`sitterID`) REFERENCES `sitters` (`sitterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `favourites`
--
ALTER TABLE `favourites`
  ADD CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`guardian_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favourites_ibfk_2` FOREIGN KEY (`sitter_id`) REFERENCES `sitters` (`sitterID`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`bookingID`) REFERENCES `bookings` (`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_sitter` FOREIGN KEY (`sitterID`) REFERENCES `sitters` (`sitterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sitters`
--
ALTER TABLE `sitters`
  ADD CONSTRAINT `fk_sitters_user` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
