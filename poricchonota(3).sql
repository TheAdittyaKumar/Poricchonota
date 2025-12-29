-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 01:37 PM
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
-- Database: `poricchonota`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `user_id` int(11) NOT NULL,
  `dept_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`user_id`, `dept_id`) VALUES
(2, 1),
(5, 1),
(7, 3);

-- --------------------------------------------------------

--
-- Table structure for table `citizen`
--

CREATE TABLE `citizen` (
  `user_id` int(11) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `citizen`
--

INSERT INTO `citizen` (`user_id`, `city`, `address`) VALUES
(1, 'Dhaka', 'Kha 224, Bir Uttam Rafiqul Islam Ave, Dhaka 1212'),
(4, 'Dhaka', '9/3 New market');

-- --------------------------------------------------------

--
-- Table structure for table `complaint`
--

CREATE TABLE `complaint` (
  `Complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_engineer_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `location_text` text NOT NULL,
  `before_photo` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deadline` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cat_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `complaint_image` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `resolution_image` varchar(255) DEFAULT NULL,
  `resolution_remarks` text DEFAULT NULL,
  `vote_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint`
--

INSERT INTO `complaint` (`Complaint_id`, `user_id`, `assigned_engineer_id`, `title`, `description`, `location_text`, `before_photo`, `status`, `reported_at`, `deadline`, `created_at`, `cat_id`, `location`, `complaint_image`, `latitude`, `longitude`, `resolution_image`, `resolution_remarks`, `vote_count`) VALUES
(5, 1, NULL, '', 'Open manhole right beside Brac University so any student, animal can fall into it. Even vehicles like cars or rickshaw can get stuck\n\n[RE-OPENED by Citizen]: color pochondo hoi nai', '', NULL, 'Resolved', '2025-12-19 20:06:19', NULL, '2025-12-19 20:06:19', 10, 'Kha 224, Bir Uttam Rafiqul Islam Ave, Dhaka 1212', '1766174779_1b50ce49c2ee333cd68e1cc26be548f2.jpg', 23.77384931, 90.42408866, 'resolved_5_1767000035.jpg', 'I brought a cool dog', 0),
(7, 4, NULL, '', 'Very unclean indeed', '', NULL, 'Resolved', '2025-12-19 21:51:04', NULL, '2025-12-19 21:51:04', 12, 'asas', '1766181064_360_F_134485200_Viu4NBx3RGTlHTICyxzxHbJ2jxXewOWH.jpg', 23.81164453, 90.39807032, 'resolved_7_1766181406.jpg', 'I cleaned the toilet.', 0),
(8, 1, NULL, '', 'Lack of electric light so it dark\n\n[RE-OPENED by Citizen]: You installed the light inside the toilet itself instead of the washroom.\n\n[RE-OPENED by Citizen]: I want blue lights', '', NULL, 'In Progress', '2025-12-20 00:06:55', NULL, '2025-12-20 00:06:55', 13, 'Bashundhara city', '1766189215_dsa.jpg', 23.75089702, 90.39065746, 'resolved_8_1766258860.jpg', 'I mean you told us to install a light but did not mention where first. But okay here we have fixed it for you.', 2),
(10, 4, NULL, '', 'broken light\n\n[RE-OPENED by Citizen]: Í want yellow lights', '', NULL, 'Resolved', '2025-12-20 00:14:50', NULL, '2025-12-20 00:14:50', 18, 'Dhanmondi 8A', '1766189690_istockphoto-1076480852-612x612.jpg', 23.74875693, 90.37456051, 'resolved_10_1766352776.jpg', 'Okay sir', 2);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_category`
--

CREATE TABLE `complaint_category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `default_SLA` int(11) DEFAULT 24,
  `description` text DEFAULT NULL,
  `Ddept_id` int(11) NOT NULL,
  `dept_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_category`
--

INSERT INTO `complaint_category` (`category_id`, `name`, `default_SLA`, `description`, `Ddept_id`, `dept_id`) VALUES
(1, 'Garbage Dump / Pile', 24, NULL, 1, 2),
(2, 'Dustbins Not Cleaned', 24, NULL, 1, NULL),
(3, 'Sweeping Not Done', 24, NULL, 1, NULL),
(4, 'Garbage Vehicle Not Arrived', 12, NULL, 1, 2),
(5, 'Dead Animal(s) on Road', 12, NULL, 1, 2),
(6, 'Burning of Garbage in Open', 6, NULL, 1, 2),
(7, 'Construction Debris / Malba', 48, NULL, 1, NULL),
(8, 'Overflow of Sewerage / Storm Water', 24, NULL, 3, 1),
(9, 'Overflow of Septic Tanks', 24, NULL, 3, NULL),
(10, 'Open Manholes or Drains', 6, NULL, 3, NULL),
(11, 'Stagnant Water on Road', 48, NULL, 1, 2),
(12, 'Uncleaned Public Toilet', 12, NULL, 1, NULL),
(13, 'No Electricity in Public Toilet', 24, NULL, 1, 3),
(14, 'No Water Supply in Public Toilet', 12, NULL, 3, 1),
(15, 'Blockage in Public Toilet', 12, NULL, 1, NULL),
(16, 'Yellow Spot (Public Urination)', 48, NULL, 1, NULL),
(17, 'Mosquito Breeding Spot', 72, NULL, 1, NULL),
(18, 'Street Light Broken', 48, NULL, 5, 3);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_history`
--

CREATE TABLE `complaint_history` (
  `history_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `status_from` varchar(50) NOT NULL,
  `status_to` varchar(50) NOT NULL,
  `changed_by_user_id` int(11) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_history`
--

INSERT INTO `complaint_history` (`history_id`, `complaint_id`, `status_from`, `status_to`, `changed_by_user_id`, `changed_at`) VALUES
(1, 5, 'N/A', 'Pending', 1, '2025-12-19 20:06:19'),
(4, 7, 'Pending', 'In Progress', 6, '2025-12-19 21:55:09'),
(5, 7, 'In Progress', 'Resolved', 6, '2025-12-19 21:56:46'),
(6, 10, 'Pending', 'In Progress', 6, '2025-12-20 13:12:02'),
(7, 10, 'In Progress', 'Resolved', 6, '2025-12-20 13:12:56'),
(8, 5, 'Pending', 'In Progress', 3, '2025-12-20 13:22:15'),
(9, 5, 'In Progress', 'Resolved', 3, '2025-12-20 13:24:41'),
(11, 8, 'Pending', 'In Progress', 11, '2025-12-20 13:29:17'),
(12, 8, 'In Progress', 'Resolved', 11, '2025-12-20 13:30:24'),
(13, 8, 'Resolved', 'In Progress', 1, '2025-12-20 19:24:29'),
(14, 8, 'In Progress', 'Resolved', 11, '2025-12-20 19:27:40'),
(15, 8, 'Resolved', 'In Progress', 1, '2025-12-21 21:21:05'),
(16, 10, 'Resolved', 'In Progress', 4, '2025-12-21 21:23:41'),
(17, 10, 'In Progress', 'Resolved', 6, '2025-12-21 21:32:56'),
(18, 5, 'Resolved', 'In Progress', 1, '2025-12-29 09:18:53'),
(19, 5, 'In Progress', 'Resolved', 3, '2025-12-29 09:20:35');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_update`
--

CREATE TABLE `complaint_update` (
  `Update_ID` int(11) NOT NULL,
  `Complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_votes`
--

CREATE TABLE `complaint_votes` (
  `vote_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_votes`
--

INSERT INTO `complaint_votes` (`vote_id`, `complaint_id`, `user_id`, `vote_type`) VALUES
(2, 7, 4, -1),
(4, 5, 4, -1),
(8, 8, 4, 1),
(9, 10, 4, 1),
(12, 10, 1, 1),
(19, 8, 1, 1),
(20, 7, 1, 1),
(21, 5, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `dept_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `off_address` text DEFAULT NULL,
  `short_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`dept_id`, `name`, `contact`, `off_address`, `short_code`) VALUES
(1, 'Dhaka North City Corporation', '16106', 'Gulshan-2, Dhaka', 'DNCC'),
(2, 'Dhaka South City Corporation', '09611100097', 'Nagar Bhaban, Dhaka', 'DSCC'),
(3, 'Dhaka WASA', '16162', 'Kawran Bazar, Dhaka', 'WASA'),
(4, 'Titas Gas', '16496', 'Kazi Nazrul Islam Ave, Dhaka', 'TITAS'),
(5, 'DESCO', '16120', 'Nikunja-2, Dhaka', 'DESCO');

-- --------------------------------------------------------

--
-- Table structure for table `engineer`
--

CREATE TABLE `engineer` (
  `user_id` int(11) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `engineer`
--

INSERT INTO `engineer` (`user_id`, `dept_id`, `is_active`) VALUES
(3, 1, 1),
(6, 1, 1),
(9, 3, 1),
(10, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `user_id` int(11) NOT NULL,
  `joined_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`user_id`, `joined_at`) VALUES
(2, '2025-12-20'),
(3, '2025-12-20'),
(5, '2025-12-20'),
(6, '2025-12-20'),
(7, '2025-12-20'),
(9, '2025-12-20'),
(10, '2025-12-20');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `nid` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('citizen','admin','engineer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dept_id` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `nid`, `name`, `email`, `password`, `phone`, `role`, `created_at`, `dept_id`) VALUES
(1, '111111111111', 'Adittya Kumar', 'adittyakumar671@gmail.com', '$2y$10$yphTPn25q04kwS/djFVuR.DDwB.Lu0aeF9Vkw/UviLeoe53m7lnIO', '011111111111', 'citizen', '2025-12-19 18:36:47', 0),
(2, '222222222', 'Usaeed Rahman', 'Usaeed@gmail.com', '$2y$10$b7vtjrx1q0EfaKBNLmeYLuRQb273etzilETK4gK0OApPtPU8OBf8O', '012222222', 'admin', '2025-12-19 18:59:57', 0),
(3, '3333333333', 'Sadman Zarif Kazmi', 'Kazmi@gmail.com', '$2y$10$c/PSceK1g6Rj9toDcd.IWObd.PBljO5F3tYHmWf33XCJ8waNCXB72', '3333333333', 'engineer', '2025-12-19 19:00:39', 3),
(4, '44444444444', 'Safin Ahmed Tonmoy', 'Safin@gmail.com', '$2y$10$XyE4UFtmBe51sYAJKlVXdeu3FOc69xXdNVGh2zys.fcg3EMUkZIlC', '444444444444', 'citizen', '2025-12-19 20:35:09', 0),
(5, '66666666666', 'Ishrak Bhuiyan', 'Ishrak@gmail.com', '$2y$10$BzAXbSN7Ez62KEFcIpr5...bxcoGAM9nUzqxj9K/M5Il0PMt5RYLm', '66666666666', 'admin', '2025-12-19 20:49:38', 0),
(6, '77777777777', 'Wasif Islam', 'Wasif@gmail.com', '$2y$10$5WutRo5MuqWPicTfDOC8/OCUtUIRrrT4I1mAMs4Rgg7Yu.NKaZSUO', '7777777777', 'engineer', '2025-12-19 20:50:07', 5),
(7, '88888888888', 'Nazmus Sakib', 'nazmus@gmail.com', '$2y$10$kAA0N30zqZJG.FF5Ew1Ph.i06ghLIaPsNuWTtLpiT9O2CNovCyoze', '88888888888', 'admin', '2025-12-19 23:49:10', 0),
(9, '10000000000', 'Rafi', 'rafi@gmail.com', '$2y$10$nzlR0ivMZbKLhiPbuqfb3eKOZ07SmdexQkXvE5p.DatODeZiKQAIu', '1000000000', 'engineer', '2025-12-19 23:52:24', 4),
(10, '56455', 'Kushari', 'kushari@gmail.com', '$2y$10$BQv2/kxyIzZv7WbqWiskOe9Z4NuN5XWGwXRktAO8fHPRy1CyhKDYm', '554646', 'engineer', '2025-12-20 00:07:58', 2),
(11, '452863', 'Aninda Kumar', 'aninda@gmail.com', '$2y$10$Pwa9fwq3DruQlFu.btFxgOeZ2hMcJsp.7CtpFt6KVEbkmuJL1BSyO', '554959', 'engineer', '2025-12-20 13:28:45', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `citizen`
--
ALTER TABLE `citizen`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `complaint`
--
ALTER TABLE `complaint`
  ADD PRIMARY KEY (`Complaint_id`),
  ADD KEY `assigned_engineer_id` (`assigned_engineer_id`),
  ADD KEY `fk_complaint_category` (`cat_id`),
  ADD KEY `fk_user_complaint` (`user_id`);

--
-- Indexes for table `complaint_category`
--
ALTER TABLE `complaint_category`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `Ddept_id` (`Ddept_id`);

--
-- Indexes for table `complaint_history`
--
ALTER TABLE `complaint_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `changed_by_user_id` (`changed_by_user_id`),
  ADD KEY `fk_history_complaint` (`complaint_id`);

--
-- Indexes for table `complaint_update`
--
ALTER TABLE `complaint_update`
  ADD PRIMARY KEY (`Update_ID`),
  ADD KEY `Complaint_id` (`Complaint_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `complaint_votes`
--
ALTER TABLE `complaint_votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `unique_vote` (`complaint_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`dept_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `short_code` (`short_code`);

--
-- Indexes for table `engineer`
--
ALTER TABLE `engineer`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `nid` (`nid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaint`
--
ALTER TABLE `complaint`
  MODIFY `Complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `complaint_category`
--
ALTER TABLE `complaint_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `complaint_history`
--
ALTER TABLE `complaint_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `complaint_update`
--
ALTER TABLE `complaint_update`
  MODIFY `Update_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_votes`
--
ALTER TABLE `complaint_votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `staff` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE;

--
-- Constraints for table `citizen`
--
ALTER TABLE `citizen`
  ADD CONSTRAINT `citizen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaint`
--
ALTER TABLE `complaint`
  ADD CONSTRAINT `complaint_ibfk_4` FOREIGN KEY (`assigned_engineer_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_complaint_category` FOREIGN KEY (`cat_id`) REFERENCES `complaint_category` (`category_id`),
  ADD CONSTRAINT `fk_user_complaint` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaint_category`
--
ALTER TABLE `complaint_category`
  ADD CONSTRAINT `complaint_category_ibfk_1` FOREIGN KEY (`Ddept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaint_history`
--
ALTER TABLE `complaint_history`
  ADD CONSTRAINT `complaint_history_ibfk_2` FOREIGN KEY (`changed_by_user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `fk_history_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaint` (`Complaint_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaint_update`
--
ALTER TABLE `complaint_update`
  ADD CONSTRAINT `complaint_update_ibfk_1` FOREIGN KEY (`Complaint_id`) REFERENCES `complaint` (`Complaint_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_update_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `complaint_votes`
--
ALTER TABLE `complaint_votes`
  ADD CONSTRAINT `complaint_votes_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaint` (`Complaint_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_votes_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaint` (`Complaint_id`) ON DELETE CASCADE;

--
-- Constraints for table `engineer`
--
ALTER TABLE `engineer`
  ADD CONSTRAINT `engineer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `staff` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `engineer_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
