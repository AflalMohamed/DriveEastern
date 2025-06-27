-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 06:18 AM
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
-- Database: `drive_eastern`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `activity_desc` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `activity_desc`, `timestamp`) VALUES
(1, 'Viewed user list', '2025-05-31 21:19:20');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `submitted_at`, `created_at`) VALUES
(11, 'Iyoob Muhammathu Aflal', 'mohamedaflal154@gmail.com', 'Too Slow', 'he is very lazy driver', '2025-06-08 08:34:48', '2025-06-08 03:04:48');

-- --------------------------------------------------------

--
-- Table structure for table `driver_locations`
--

CREATE TABLE `driver_locations` (
  `driver_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `driver_locations`
--

INSERT INTO `driver_locations` (`driver_id`, `latitude`, `longitude`, `updated_at`) VALUES
(11, 6.92707860, 79.86124300, '2025-06-27 09:01:44'),
(13, 6.92707860, 79.86124300, '2025-06-27 09:16:45');

-- --------------------------------------------------------

--
-- Table structure for table `driver_status`
--

CREATE TABLE `driver_status` (
  `driver_id` int(11) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `driver_status`
--

INSERT INTO `driver_status` (`driver_id`, `is_available`, `updated_at`) VALUES
(11, 1, '2025-06-27 03:08:19'),
(13, 0, '2025-06-27 03:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `fare_price`
--

CREATE TABLE `fare_price` (
  `id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fare_price`
--

INSERT INTO `fare_price` (`id`, `price`) VALUES
(1, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `fare_settings`
--

CREATE TABLE `fare_settings` (
  `id` int(11) NOT NULL,
  `base_fare` decimal(10,2) NOT NULL DEFAULT 100.00,
  `per_km_rate` decimal(10,2) NOT NULL DEFAULT 10.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fare_per_km` float NOT NULL DEFAULT 60,
  `minimum_fare` float NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fare_settings`
--

INSERT INTO `fare_settings` (`id`, `base_fare`, `per_km_rate`, `updated_at`, `fare_per_km`, `minimum_fare`) VALUES
(1, 150.00, 1.00, '2025-05-31 07:45:30', 60, 100);

-- --------------------------------------------------------

--
-- Table structure for table `live_locations`
--

CREATE TABLE `live_locations` (
  `user_id` int(11) NOT NULL,
  `role` enum('driver','passenger') NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `created_at`, `is_read`) VALUES
(1, 5, 'New ride requested from batticaloa to kiran. Fare: Rs.960', '2025-06-08 09:05:19', 0),
(2, 8, 'New ride requested from batticaloa to kiran. Fare: Rs.960', '2025-06-08 09:05:19', 0),
(3, 9, 'New ride requested from batticaloa to kiran. Fare: Rs.960', '2025-06-08 09:05:19', 0),
(4, 5, 'New ride requested from aaryampathy to aarayampathy. Fare: Rs.120', '2025-06-08 09:08:23', 0),
(5, 8, 'New ride requested from aaryampathy to aarayampathy. Fare: Rs.120', '2025-06-08 09:08:23', 0),
(6, 9, 'New ride requested from aaryampathy to aarayampathy. Fare: Rs.120', '2025-06-08 09:08:23', 0),
(7, 5, 'New ride requested from eravur to batticaloa. Fare: Rs.600', '2025-06-08 09:16:24', 0),
(8, 8, 'New ride requested from eravur to batticaloa. Fare: Rs.600', '2025-06-08 09:16:24', 0),
(9, 9, 'New ride requested from eravur to batticaloa. Fare: Rs.600', '2025-06-08 09:16:24', 0),
(10, 1, 'New ride requested from batticaloa to eravur. Fare: Rs.720', '2025-06-08 09:31:31', 0),
(11, 5, 'New ride requested from batticaloa to eravur. Fare: Rs.720', '2025-06-08 09:31:31', 0),
(12, 8, 'New ride requested from batticaloa to eravur. Fare: Rs.720', '2025-06-08 09:31:31', 0),
(13, 9, 'New ride requested from batticaloa to eravur. Fare: Rs.720', '2025-06-08 09:31:31', 0),
(14, 1, 'New ride requested from batticaloa to batticaloa. Fare: Rs.120', '2025-06-08 09:34:16', 0),
(15, 5, 'New ride requested from batticaloa to batticaloa. Fare: Rs.120', '2025-06-08 09:34:16', 0),
(16, 8, 'New ride requested from batticaloa to batticaloa. Fare: Rs.120', '2025-06-08 09:34:16', 0),
(17, 9, 'New ride requested from batticaloa to batticaloa. Fare: Rs.120', '2025-06-08 09:34:16', 0),
(18, 1, 'New ride: aaryampathy to kiran – Rs.1080', '2025-06-27 08:17:13', 0),
(19, 5, 'New ride: aaryampathy to kiran – Rs.1080', '2025-06-27 08:17:13', 0),
(20, 8, 'New ride: aaryampathy to kiran – Rs.1080', '2025-06-27 08:17:13', 0),
(21, 9, 'New ride: aaryampathy to kiran – Rs.1080', '2025-06-27 08:17:13', 0),
(22, 1, 'New ride requested from aaryampathy to eravur. Fare: Rs.210', '2025-06-27 08:27:33', 0),
(23, 5, 'New ride requested from aaryampathy to eravur. Fare: Rs.210', '2025-06-27 08:27:33', 0),
(24, 8, 'New ride requested from aaryampathy to eravur. Fare: Rs.210', '2025-06-27 08:27:33', 0),
(25, 9, 'New ride requested from aaryampathy to eravur. Fare: Rs.210', '2025-06-27 08:27:33', 0),
(26, 1, 'New ride requested from kalmunai to pottuvil. Fare: Rs.1400', '2025-06-27 08:28:30', 0),
(27, 5, 'New ride requested from kalmunai to pottuvil. Fare: Rs.1400', '2025-06-27 08:28:30', 0),
(28, 8, 'New ride requested from kalmunai to pottuvil. Fare: Rs.1400', '2025-06-27 08:28:30', 0),
(29, 9, 'New ride requested from kalmunai to pottuvil. Fare: Rs.1400', '2025-06-27 08:28:30', 0),
(30, 11, 'New ride requested from eravur to batticaloa. Fare: Rs.1200', '2025-06-27 08:33:00', 0),
(31, 13, 'New ride requested from eravur to batticaloa. Fare: Rs.1200', '2025-06-27 08:33:00', 0),
(32, 11, 'New ride request from batticaloa to kiran. Fare: Rs.2000. Ride ID: 37', '2025-06-27 08:38:28', 0),
(33, 13, 'New ride request from batticaloa to kiran. Fare: Rs.2000. Ride ID: 37', '2025-06-27 08:38:28', 0),
(34, 11, 'New ride request from aaryampathy to kiran. Fare: Rs.3000. Ride ID: 38', '2025-06-27 09:16:12', 0),
(35, 13, 'New ride request from aaryampathy to kiran. Fare: Rs.3000. Ride ID: 38', '2025-06-27 09:16:12', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rides`
--

CREATE TABLE `rides` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `status` enum('requested','accepted','cancelled','completed') NOT NULL DEFAULT 'requested',
  `fare` decimal(10,2) NOT NULL DEFAULT 0.00,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `driver_deleted` tinyint(1) DEFAULT 0,
  `rejected_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rides`
--

INSERT INTO `rides` (`id`, `passenger_id`, `driver_id`, `pickup_location`, `dropoff_location`, `status`, `fare`, `requested_at`, `accepted_at`, `completed_at`, `cancelled_at`, `cancellation_reason`, `rating`, `review`, `created_at`, `driver_deleted`, `rejected_at`) VALUES
(36, 12, 11, 'eravur', 'batticaloa', 'cancelled', 1200.00, '2025-06-27 03:03:00', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-27 08:33:00', 0, NULL),
(37, 12, 13, 'batticaloa', 'kiran', 'completed', 2000.00, '2025-06-27 03:08:28', '2025-06-27 03:14:17', '2025-06-27 03:32:33', NULL, NULL, 2, 'good', '2025-06-27 08:38:28', 0, NULL),
(38, 12, 13, 'aaryampathy', 'kiran', 'accepted', 3000.00, '2025-06-27 03:46:12', '2025-06-27 03:46:45', NULL, NULL, NULL, NULL, NULL, '2025-06-27 09:16:12', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('passenger','driver','admin') NOT NULL DEFAULT 'passenger',
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `phone`, `created_at`) VALUES
(7, 'Admin User', 'admin@example.com', '$2y$10$WZqRO...hashedpassword...', 'admin', NULL, '2025-05-31 13:53:20'),
(11, 'akeel', 'akl@gmail.com', '$2y$10$mdGzwTcupwybFRXdVXc99u8olN0wdvESYccDnO/nFavDY5/yKXmde', 'driver', '0778765786', '2025-06-27 03:00:04'),
(12, 'Thamis', 'th@gmail.com', '$2y$10$.mrqTFZnIi91KeH5Hmy.V.AB4VE2TgzZfxHA7OuZ7bxr3hzSKGEXC', 'passenger', '0756543453', '2025-06-27 03:00:31'),
(13, 'Issath', 'iss@gmail.com', '$2y$10$3Ujz7TP6Y7D1yocysAxYSexLvwHdhSFg4R1evgjwfDO5kM9LhujtS', 'driver', '0778965432', '2025-06-27 03:01:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`driver_id`);

--
-- Indexes for table `driver_status`
--
ALTER TABLE `driver_status`
  ADD PRIMARY KEY (`driver_id`);

--
-- Indexes for table `fare_price`
--
ALTER TABLE `fare_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fare_settings`
--
ALTER TABLE `fare_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_locations`
--
ALTER TABLE `live_locations`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `passenger_id` (`passenger_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `rides`
--
ALTER TABLE `rides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `passenger_id` (`passenger_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `fare_price`
--
ALTER TABLE `fare_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fare_settings`
--
ALTER TABLE `fare_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rides`
--
ALTER TABLE `rides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `driver_locations_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_status`
--
ALTER TABLE `driver_status`
  ADD CONSTRAINT `driver_status_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rides`
--
ALTER TABLE `rides`
  ADD CONSTRAINT `rides_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rides_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
