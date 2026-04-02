-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2026 at 07:33 PM
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
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sitters`
--

CREATE TABLE `sitters` (
  `sitterID` int(11) NOT NULL,
  `uID` int(11) NOT NULL,
  `hourlyRate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bio` text DEFAULT NULL,
  `experience` int(11) NOT NULL DEFAULT 0,
  `isAvailable` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sitters`
--

INSERT INTO `sitters` (`sitterID`, `uID`, `hourlyRate`, `bio`, `experience`, `isAvailable`) VALUES
(1, 2, 12.50, 'hahays', 2, 1),
(2, 3, 1232.00, 'asdasdds', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uID` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `birthdate` date NOT NULL,
  `sex` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guardian','sitter','admin') NOT NULL,
  `country` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `street` varchar(255) NOT NULL,
  `contactNumber` varchar(20) NOT NULL,
  `profilePic` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uID`, `firstName`, `lastName`, `email`, `birthdate`, `sex`, `password`, `role`, `country`, `city`, `street`, `contactNumber`, `profilePic`, `dateCreated`) VALUES
(1, 'sean', 'torres', 'sean@gmail.com', '2005-12-29', 'male', '$2y$10$Jc9F2Q4zCk7MC9r7q5slzOxZETuVfEZW1i8gPPWgtbSJ911p6niTW', 'admin', '', '', 'N/A', '', '1775018243_69cca1035bf0c.png', '2026-04-01 04:37:23'),
(2, 'SEAN', 'TORRES', 'torres@gmail.com', '2005-12-29', 'male', '$2y$10$0Mh3Nt4evajU/0DTTS4BHeWrKJT5sFh1WQ3flgbAMYJCyzvptWzim', 'sitter', '', '', 'N/A', '', '1775026698_69ccc20a8a705.jpg', '2026-04-01 06:58:18'),
(3, 'rem', 'fugnit', 'rem@gmail.com', '2312-12-31', 'female', '$2y$10$j0IHzGs/y6FqkVvDDpAEC.oCIeT4KqvFaAERsCf5opeAs.yudIIUG', 'sitter', '', '', 'N/A', '', '1775026977_69ccc321219e5.jpg', '2026-04-01 07:02:57');

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
  MODIFY `guardianID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sitters`
--
ALTER TABLE `sitters`
  MODIFY `sitterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
