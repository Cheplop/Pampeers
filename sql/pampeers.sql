-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2026 at 12:04 PM
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
(9, 12),
(10, 22);

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
(1, 3, 555.00, 'Hi, I am fully capable.', 100, 1),
(2, 4, 200.00, 'Chill', 5, 1),
(3, 6, 450.36, 'I am a certified babysitter', 3, 1),
(4, 13, 150.00, 'Loving and caring sitter', 2, 1),
(5, 14, 200.00, 'Experienced with toddlers', 3, 1),
(6, 15, 180.00, 'Friendly and patient', 1, 1),
(7, 16, 160.00, 'Great with special needs', 4, 1),
(8, 17, 170.00, 'Fun and energetic', 2, 1),
(9, 18, 123.23, 'asdasdd', 2, 1),
(10, 19, 2234.00, 'sadadsad', 234, 1),
(11, 20, 500.00, 'i am gwapa', 0, 1),
(12, 21, 125.00, 'HI hellooo', 6, 1);

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
  `sex` enum('male','female','other') NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guardian','sitter','admin') NOT NULL,
  `country` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `street` varchar(150) NOT NULL,
  `contactNumber` varchar(20) NOT NULL,
  `profilePic` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uID`, `firstName`, `lastName`, `email`, `birthdate`, `sex`, `password`, `role`, `country`, `city`, `street`, `contactNumber`, `profilePic`, `dateCreated`) VALUES
(1, 'Charles', 'Fugnit', 'charlesfugnit17@gmail.com', '2006-06-17', 'other', '$2y$10$NZwBX6.jIAWI5b2lHTePEu4ax9oHypsu/Do.kXv.Di7PTpbkXPzyK', 'guardian', 'Philippines', 'Cagayan De Oro City', 'Gusa', '09764918031', '1775319325_69d1391dce266.jpg', '2026-03-31 03:43:24'),
(2, 'Russel', 'Up', 'richc@gmail.com', '2006-03-15', 'female', '$2y$10$tY6cev/Myafz4D5tg0a6YepMtMqw4Im4UhXt5xa32n26XfNk.KOku', 'sitter', 'Philippines', 'Cagayan De Oro City', 'Gusa', '091234678', '1774932200_69cb50e8af312.jpg', '2026-03-31 04:43:20'),
(3, 'Trix', 'Villa', 'trix@gmail.com', '2006-05-05', 'male', '$2y$10$IUOnGq6SaHlwJsYHBD7CUu2HOreQMNfc7B7JAN0tIyBvicNh/8sVm', 'sitter', 'Philippines', 'Cagayan De Oro City', 'Gusa', '0000000000', '78faeaa15ce3f0b1b30795dc7f1144c4.jpg', '2026-03-31 04:52:51'),
(4, 'Pink', 'Panther', 'pink@gmail.com', '2006-06-17', 'female', '$2y$10$C/PQcw/rtQEA4c6WQJIfHeOxiIA8LIkeh.T6B1EfqClaH2L/1k5zC', 'sitter', 'Philippines', 'Cagayan De Oro City', 'Gusa', '0000000000', 'default.jpg', '2026-04-01 02:20:04'),
(5, 'sean', 'toress', 'sean@gmail.com', '2006-06-17', 'male', '$2y$10$Y6t6LV4EyPgNBAcvKcwDy.n2eQDFYS5AhzEQ.6ZgtEiYT1Ev/FAAm', 'admin', 'Philippines', 'Cagayan De Oro City', 'Gusa', '09764918031', '1775014465_69cc9241f0d60.jpg', '2026-04-01 03:34:26'),
(6, 'Andree', 'Sanlayan', 'andree@gmail.com', '2001-04-18', 'male', '$2y$10$NZwBX6.jIAWI5b2lHTePEu4ax9oHypsu/Do.kXv.Di7PTpbkXPzyK', 'sitter', 'United State of Amera', 'New York City', 'Bronx', '0000000000', 'ad201e26367b77453456fb33173d5e6c 1.png', '2026-04-02 05:13:25'),
(7, 'Jethro', 'Fuentes', 'jethro@gmail.com', '2003-04-16', 'male', '$2y$10$zmrOzRksnaM.GwDidwHVAO0sVQitnm.DT0EYwhvmha8CEBgQVnmIu', 'guardian', 'France', 'Paris', 'Consolacion', '09456298516', '1775107053_69cdfbedb63b4.jpg', '2026-04-02 05:17:33'),
(8, 'John', 'Guardian', 'john@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'Philippines', 'Cagayan de Oro', '123 Main St', '09171234567', 'default.jpg', '2026-04-02 05:39:51'),
(9, 'Maria', 'Cruz', 'maria@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'Philippines', 'Gingoog', '456 River St', '09171234568', 'default.jpg', '2026-04-02 05:39:51'),
(10, 'Paul', 'Reyes', 'paul@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'Philippines', 'El Salvador', '789 Hill St', '09171234569', 'default.jpg', '2026-04-02 05:39:51'),
(11, 'Anna', 'Santos', 'anna@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'Philippines', 'Balingasag', '101 Mango St', '09171234570', 'default.jpg', '2026-04-02 05:39:51'),
(12, 'Leo', 'Garcia', 'leo@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'guardian', 'Philippines', 'Tagoloan', '202 Pine St', '09171234571', 'default.jpg', '2026-04-02 05:39:51'),
(13, 'Liza', 'Sitter', 'liza@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'Philippines', 'Cagayan de Oro', '11 Sitter St', '0000000000', 'default.jpg', '2026-04-02 05:39:51'),
(14, 'Mark', 'Dela Cruz', 'mark@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'Philippines', 'Gingoog', '22 Sitter St', '0000000000', 'default.jpg', '2026-04-02 05:39:51'),
(15, 'Jenny', 'Lopez', 'jenny@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'Philippines', 'El Salvador', '33 Sitter St', '0000000000', 'default.jpg', '2026-04-02 05:39:51'),
(16, 'Carlo', 'Mendoza', 'carlo@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'Philippines', 'Balingasag', '44 Sitter St', '0000000000', 'default.jpg', '2026-04-02 05:39:51'),
(17, 'Nina', 'Flores', 'nina@gmail.com', '2000-01-01', 'other', '$2y$10$usesomesillystringforexamplehash1234567890abcd', 'sitter', 'Philippines', 'Tagoloan', '55 Sitter St', '0000000000', 'default.jpg', '2026-04-02 05:39:51'),
(18, 'Jussy', 'Jay', 'jussy@gmail.com', '1231-12-31', 'male', '$2y$10$iGFanIvULaJYeEAqJpGvAuYZBqZCBjqSxRltyuq05/NH2nwjpD2qG', 'sitter', 'adadad', 'adsdadasd', 'adsads', '0192301293', 'default.jpg', '2026-04-02 14:21:30'),
(19, 'hahaa', 'haahaha', 'haha@gmail.com', '2312-12-31', 'male', '$2y$10$TTvAiY/Hmh.dutUpQSnK.eo24Q.Ut5EMAA7OPyT5RDE9cZ2t92TB2', 'sitter', 'asdas', 'asdasd', 'asdad', '585675675', 'default.jpg', '2026-04-04 07:22:11'),
(20, 'nea', 'satunero', 'satunero@gmail.com', '2005-10-11', 'female', '$2y$10$1qwSlOY6Za3iTmZvleYKR.8ETjteyeZU7VLgw8wY8ZOcg112gFroe', 'sitter', 'Philippines', 'Cagayan de Oro City', 'Agora', '0967 016 6054', 'default.jpg', '2026-04-05 13:44:54'),
(21, 'Carl', 'Canizares', 'carl@gmail.com', '2005-12-23', 'male', '$2y$10$labsuYkcoWfhXPFKCapFrOk9XE805YM7KhT20JrIU544ZHZul4SMe', 'sitter', 'asdasadasda', 'asdasdasdasd', 'asdasd', '1231232131', '1775456000_69d34f00e1544.png', '2026-04-06 06:13:21'),
(22, 'Mark', 'Justin', 'marc@gmail.com', '2006-12-31', 'male', '$2y$10$q8SNId2r4HQNWqWMFwbkKORt0XkXVk.t8nPbP1BzZODL.qozEFmxO', 'admin', 'Philippines', 'Cagayan de Oro', 'Parola', '0978214511', '1775457063_69d3532711b06.png', '2026-04-06 06:31:03');

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
  MODIFY `guardianID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sitters`
--
ALTER TABLE `sitters`
  MODIFY `sitterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
