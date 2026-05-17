-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 17, 2026 at 04:33 AM
-- Server version: 11.8.6-MariaDB-0+deb13u1 from Debian
-- PHP Version: 8.4.21

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
  `startDateTime` datetime NOT NULL,
  `endDateTime` datetime NOT NULL,
  `hoursRequested` decimal(5,2) DEFAULT NULL,
  `totalAmount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','accepted','declined','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`bookingID`, `uuid`, `userID`, `sitterID`, `startDateTime`, `endDateTime`, `hoursRequested`, `totalAmount`, `status`, `notes`, `createdAt`) VALUES
(2, '45125b35-4b51-49ed-9099-754e0e97bade', 5, 3, '2026-12-03 07:00:00', '2026-12-06 07:00:00', 72.00, 1440.00, 'cancelled', 'My kid is autistic and MY MOM IS KINDA HOMELESSS', '2026-05-09 03:26:42'),
(3, '250ec7f9-6869-4b90-b822-51f536c92c0a', 7, 3, '2026-12-30 00:00:00', '2027-01-06 12:00:00', 180.00, 3600.00, 'pending', 'my son is autistic and MY MOM IS KINDA HOMELESS PLEASE SPEED I NEED THIS', '2026-05-09 04:44:48'),
(4, 'd2bee5e7-fdf2-46ad-a908-2d5b4915096c', 2, 5, '2026-12-31 00:00:00', '2027-01-03 12:00:00', 84.00, 1050.00, 'declined', 'MY MOM IS KINDA HOMELESS', '2026-05-10 15:20:24'),
(5, 'ed5779cb-546c-4262-9c3f-6e3b4ef22ac4', 3, 5, '2026-12-12 12:00:00', '2026-12-14 00:00:00', 36.00, 450.00, 'completed', '', '2026-05-11 03:16:54'),
(6, 'fdb54300-b4e0-4fc1-97c9-8e972f06b25b', 5, 3, '2026-12-12 12:00:00', '2026-12-14 00:00:00', 36.00, 720.00, 'completed', '', '2026-05-11 03:40:39'),
(7, 'b0db7f67-c919-435b-aa85-836106352139', 2, 5, '2026-12-04 12:00:00', '2026-12-06 00:00:00', 36.00, 450.00, 'completed', 'YAWA', '2026-05-12 12:58:51'),
(8, '6497e60a-72d0-48dc-b98d-f5a3ebd4a356', 3, 5, '2026-12-12 12:00:00', '2026-12-14 00:00:00', 36.00, 450.00, 'accepted', 'HAHAHAH', '2026-05-12 13:12:03');

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

--
-- Dumping data for table `favourites`
--

INSERT INTO `favourites` (`id`, `guardian_id`, `sitter_id`, `created_at`) VALUES
(4, 5, 3, '2026-05-09 03:53:01'),
(6, 7, 5, '2026-05-09 04:33:57'),
(8, 7, 3, '2026-05-09 04:52:21'),
(10, 2, 5, '2026-05-10 15:19:44'),
(11, 3, 5, '2026-05-11 03:16:29');

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

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`reviewID`, `uuid`, `bookingID`, `userID`, `sitterID`, `rating`, `comment`, `createdAt`) VALUES
(2, '6ffcb99c-beb1-8bd8-682a-1630f5a343e6', 6, 5, 3, 5, 'YAWA', '2026-05-11 03:43:03'),
(3, '38079210-4b7a-cbe7-dc69-fd56e256443c', 5, 3, 5, 5, 'fuck u gay ass bitch', '2026-05-12 13:11:30');

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
(3, '0fe63d8db80e820aa406e20b1b190b76', 3, 20.00, 2, 1, 5.00, 'verified', '2026-05-02 13:00:27'),
(4, '11700876-6e0f-4b69-a1b0-132472db5c82', 6, 12.00, 5, 1, NULL, 'verified', '2026-05-08 13:58:40'),
(5, '1f11d6b3-da95-44fe-aa3c-1582c0a87ef5', 5, 12.50, 7, 1, 5.00, 'verified', '2026-05-08 14:07:49');

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
  `bio` text DEFAULT NULL,
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
  `deactivatedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `firstName`, `middleName`, `lastName`, `bio`, `suffix`, `birthDate`, `sex`, `role`, `contactNumber`, `emailAddress`, `username`, `password`, `streetAddress`, `barangay`, `cityMunicipality`, `province`, `country`, `zipCode`, `dateCreated`, `profilePic`, `isActive`, `deactivatedAt`, `deletedAt`, `createdAt`) VALUES
(1, '22951542-4596-11f1-8ef7-d822244c147e', 'Nea', '', 'Satunero', '', '', '2005-10-11', 'female', 'admin', '0912 345 6789', 'sean@gmail.com', 'aengela', '$2y$12$yv4Rg3TArLLCjSR6CCRw5OWEKHyFyANk13EuKZoBEG/vuijeJkJBu', 'Agora', 'Lapasan', 'Cagayan de Oro', 'Misamis Oriental', 'Philippines', '9000', '2026-05-02 03:09:32', '1777699320_572852305076.jpeg', 1, NULL, NULL, '2026-05-08 20:01:52'),
(2, '87cf60a9-45b0-11f1-8ef7-d822244c147e', 'Remiel', '', 'Fugnit', '', '', '2003-12-30', 'male', 'guardian', 'asdasdasd', 'rem@gmail.com', 'remyel', '$2y$12$SBRHnm.AG8arUdRMSxd35OcPi0Xa7pI7wNTQ83SlcNNdE8woIE0ly', 'asdad', 'asdasd', 'asdasd', 'dsadsad', 'asdasd', 'asdasd', '2026-05-02 06:18:29', 'default.jpg', 1, NULL, NULL, '2026-05-08 20:01:52'),
(3, 'c40cfb24-45fe-11f1-8c32-d0008dc532ac', 'Clark', '', 'Galleon', '', '', '2003-12-06', 'male', 'guardian', '092323453', 'clark@gmail.com', 'clarkbayot', '$2y$12$a5psCRFkmqApl45QE5plreX15kBMw3PKtw2LSDbc0mKTXV07e4Qb6', 'Burgos', 'Consolacion', 'Cagayan de Oro', 'Misamis Oriental', 'Philippines', '9000', '2026-05-02 12:51:59', 'default.jpg', 1, NULL, NULL, '2026-05-08 20:01:52'),
(5, 'd22e0cbe-6806-4dcd-b451-b7a32a9b383b', 'asdadasd', 'ss', 'asdadasda', 'I am gay', 'assd', '2003-06-28', 'other', 'guardian', '09230923', 'hays@gmail.com', 'hays', '$2y$12$9C7Kv7lAU/h5gHvWe2xorONCp51IcZOC2BrRUVCivy9ip5qoE/BO6', 'asdadasd', 'asdadsasd', 'asdasdasd', 'asdasdasd', 'asdasdasda', '12341', '2026-05-08 12:31:31', '1778243491_69fdd7a3b77bb.jpeg', 1, NULL, NULL, '2026-05-08 20:31:31'),
(6, '31b86a09-43b5-447f-be9a-498fba574eca', 'asdsad', 'asdasd', 'asdasd', 'HAHAHAHAHA', 'asd', '2003-12-31', 'other', 'guardian', '12312313', 'user@gmail.com', 'username', '$2y$12$yyXtLTE2pmaXTlluKKyA8.p9KpV8yIwofT90t9bANC.Xz9P/elsnq', 'asdadasd', 'adad', 'asdasd', 'asd', 'asdadads', '1234', '2026-05-08 13:39:05', 'default.jpg', 0, NULL, '2026-05-08 22:08:35', '2026-05-08 21:39:05'),
(7, 'fc7852c2-9dc6-4133-bbb1-db004476effe', 'Trisha', 'C.', 'Torres', '', 'Jr.', '2013-06-28', 'female', 'guardian', '09568358923', 'trisha@gmail.com', 'trisha', '$2y$12$Of8aoD7xFOfvX/1VKXX.VuavVEHR0VKkATEXt0hwB80OQIPAtfQ1K', 'Parola', 'Macabalan', 'Cagayan de Oro City', 'Misamis Oriental', 'Philippines', '9000', '2026-05-09 02:15:58', 'default.jpg', 0, NULL, '2026-05-10 23:11:55', '2026-05-09 10:15:58'),
(8, 'e35ebdc2-07ad-4ab1-9845-51fec3a2dce3', 'Gojo', '', 'Satoru', '', '', '2002-12-12', 'male', 'guardian', '111111111111', 'gojo@example.com', 'gojo', '$2y$12$ePMQF.RouU/vWTat53j/teD9sLM5/FhtyIWCsF4sxPOamoMOwnxra', 'Gojo', 'Satoru', 'Satoru', 'Gojo', 'Satoru', '9999', '2026-05-16 05:28:45', 'default.jpg', 1, NULL, NULL, '2026-05-16 13:28:45');

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
  MODIFY `bookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `favourites`
--
ALTER TABLE `favourites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `reviewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sitters`
--
ALTER TABLE `sitters`
  MODIFY `sitterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
