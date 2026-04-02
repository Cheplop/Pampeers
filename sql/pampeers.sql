-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2026 at 09:54 AM
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
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `guardianID` int(11) NOT NULL,
  `uID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`guardianID`, `uID`) VALUES
(1, 1),
(2, 2),
(3, 5),
(4, 7),
(5, 8),
(6, 9),
(7, 10),
(8, 11),
(9, 12);

-- --------------------------------------------------------

--
-- Table structure for table `sitters`
--

CREATE TABLE `sitters` (
  `sitterID` int(11) NOT NULL,
  `uID` int(11) NOT NULL,
  `contactNumber` varchar(20) DEFAULT NULL,
  `hourlyRate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bio` text DEFAULT NULL,
  `experience` int(11) NOT NULL DEFAULT 0,
  `isAvailable` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sitters`
--

INSERT INTO `sitters` (`sitterID`, `uID`, `contactNumber`, `hourlyRate`, `bio`, `experience`, `isAvailable`) VALUES
(1, 3, '097564812', 555.00, 'Hi, I am fully capable.', 100, 1),
(2, 4, '09764918031', 200.00, 'Chill', 5, 1),
(3, 6, '0954267942', 450.36, 'I am a certified babysitter', 3, 1),
(4, 13, '09179876543', 150.00, 'Loving and caring sitter', 2, 1),
(5, 14, '09179876544', 200.00, 'Experienced with toddlers', 3, 1),
(6, 15, '09179876545', 180.00, 'Friendly and patient', 1, 1),
(7, 16, '09179876546', 160.00, 'Great with special needs', 4, 1),
(8, 17, '09179876547', 170.00, 'Fun and energetic', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uID` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `sex` enum('male','female','other') DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guardian','sitter','admin') NOT NULL,
  `profilePic` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `street` varchar(150) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `contactNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uID`, `firstName`, `lastName`, `email`, `birthdate`, `sex`, `password`, `role`, `profilePic`, `dateCreated`, `street`, `city`, `country`, `contactNumber`) VALUES
(1, 'Charles', 'Fugnit', 'charlesfugnit17@gmail.com', '2006-06-17', NULL, '$2y$10$NZwBX6.jIAWI5b2lHTePEu4ax9oHypsu/Do.kXv.Di7PTpbkXPzyK', 'guardian', '1774928604_69cb42dc88b41.jpg', '2026-03-31 03:43:24', 'Gusa', 'Cagayan De Oro City', 'Philippines', '09764918031'),
(2, 'Russel', 'Up', 'richc@gmail.com', '2006-03-15', 'female', '$2y$10$tY6cev/Myafz4D5tg0a6YepMtMqw4Im4UhXt5xa32n26XfNk.KOku', 'sitter', '1774932200_69cb50e8af312.jpg', '2026-03-31 04:43:20', 'Gusa', 'Cagayan De Oro City', 'Philippines', '091234678'),
(3, 'Trix', 'Villa', 'trix@gmail.com', '2006-05-05', 'male', '$2y$10$IUOnGq6SaHlwJsYHBD7CUu2HOreQMNfc7B7JAN0tIyBvicNh/8sVm', 'sitter', '78faeaa15ce3f0b1b30795dc7f1144c4.jpg', '2026-03-31 04:52:51', 'Gusa', 'Cagayan De Oro City', 'Philippines', NULL),
(4, 'Pink', 'Panther', 'pink@gmail.com', '2006-06-17', 'female', '$2y$10$C/PQcw/rtQEA4c6WQJIfHeOxiIA8LIkeh.T6B1EfqClaH2L/1k5zC', 'sitter', '1775010003_69cc80d3c696f.jpg', '2026-04-01 02:20:04', 'Gusa', 'Cagayan De Oro City', 'Philippines', NULL),
(5, 'sean', 'toress', 'sean@gmail.com', '2006-06-17', 'male', '$2y$10$Y6t6LV4EyPgNBAcvKcwDy.n2eQDFYS5AhzEQ.6ZgtEiYT1Ev/FAAm', 'guardian', '1775014465_69cc9241f0d60.jpg', '2026-04-01 03:34:26', 'Gusa', 'Cagayan De Oro City', 'Philippines', '09764918031'),
(6, 'Andree', 'Sanlayan', 'andree@gmail.com', '2001-04-18', 'male', '$2y$10$sPSeDEWmIY21hVQ9Ye/WSuz1xi6YL2p1zxRZNOdMtbDssB1vHLAza', 'sitter', '1775106805_69cdfaf509ce0.jpg', '2026-04-02 05:13:25', 'Bronx', 'New York City', 'United State of Amera', NULL),
(7, 'Jethro', 'Fuentes', 'jethro@gmail.com', '2003-04-16', 'male', '$2y$10$zmrOzRksnaM.GwDidwHVAO0sVQitnm.DT0EYwhvmha8CEBgQVnmIu', 'guardian', '1775107053_69cdfbedb63b4.jpg', '2026-04-02 05:17:33', 'Consolacion', 'Paris', 'France', '09456298516'),
(8, 'John', 'Guardian', 'john@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'default.jpg', '2026-04-02 05:39:51', '123 Main St', 'Cagayan de Oro', 'Philippines', '09171234567'),
(9, 'Maria', 'Cruz', 'maria@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'default.jpg', '2026-04-02 05:39:51', '456 River St', 'Gingoog', 'Philippines', '09171234568'),
(10, 'Paul', 'Reyes', 'paul@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'default.jpg', '2026-04-02 05:39:51', '789 Hill St', 'El Salvador', 'Philippines', '09171234569'),
(11, 'Anna', 'Santos', 'anna@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'default.jpg', '2026-04-02 05:39:51', '101 Mango St', 'Balingasag', 'Philippines', '09171234570'),
(12, 'Leo', 'Garcia', 'leo@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'default.jpg', '2026-04-02 05:39:51', '202 Pine St', 'Tagoloan', 'Philippines', '09171234571'),
(13, 'Liza', 'Sitter', 'liza@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'default.jpg', '2026-04-02 05:39:51', '11 Sitter St', 'Cagayan de Oro', 'Philippines', NULL),
(14, 'Mark', 'Dela Cruz', 'mark@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'default.jpg', '2026-04-02 05:39:51', '22 Sitter St', 'Gingoog', 'Philippines', NULL),
(15, 'Jenny', 'Lopez', 'jenny@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'default.jpg', '2026-04-02 05:39:51', '33 Sitter St', 'El Salvador', 'Philippines', NULL),
(16, 'Carlo', 'Mendoza', 'carlo@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'default.jpg', '2026-04-02 05:39:51', '44 Sitter St', 'Balingasag', 'Philippines', NULL),
(17, 'Nina', 'Flores', 'nina@gmail.com', NULL, NULL, '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'default.jpg', '2026-04-02 05:39:51', '55 Sitter St', 'Tagoloan', 'Philippines', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`guardianID`),
  ADD UNIQUE KEY `uID` (`uID`);

--
-- Indexes for table `sitters`
--
ALTER TABLE `sitters`
  ADD PRIMARY KEY (`sitterID`),
  ADD UNIQUE KEY `uID` (`uID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `guardianID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sitters`
--
ALTER TABLE `sitters`
  MODIFY `sitterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `fk_guardian_user` FOREIGN KEY (`uID`) REFERENCES `users` (`uID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sitters`
--
ALTER TABLE `sitters`
  ADD CONSTRAINT `fk_sitter_user` FOREIGN KEY (`uID`) REFERENCES `users` (`uID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
