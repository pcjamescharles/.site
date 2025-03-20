-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 21, 2024 at 02:25 AM
-- Server version: 10.4.33-MariaDB-log
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventotrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `notification_date` datetime DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `message`, `recipient`, `notification_date`, `status`) VALUES
(1, 'sad', 'sad', '2024-11-21 10:23:38', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `stock` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `image`, `name`, `stock`, `description`, `category`, `supplier`, `created_at`, `updated_at`) VALUES
(1, 'image1.jpg', 'Product A', 100, 'Description of Product A', 'Category 1', 'Supplier 1', '2024-11-14 05:12:36', '2024-11-14 05:12:36'),
(2, 'image2.jpg', 'Product B', 50, 'Description of Product B', 'Category 2', 'Supplier 2', '2024-11-14 05:12:36', '2024-11-14 05:12:36'),
(3, 'image3.jpg', 'Product C', 200, 'Description of Product C', 'Category 3', 'Supplier 3', '2024-11-14 05:12:36', '2024-11-14 05:12:36'),
(4, 'Revision-cutout.png', 'Tests', 12312, 'test', 'test', 'test', '2024-11-14 05:34:31', '2024-11-14 05:34:57');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `contact_number`, `email`, `address`, `notes`) VALUES
(1, 'Supplier 1', 'John Doe', '1234567890', 'supplier1@example.com', '1234 Elm Street, City', 'Notes for Supplier 1'),
(2, 'Supplier 2', 'Jane Smith', '0987654321', 'supplier2@example.com', '5678 Oak Avenue, City', 'Notes for Supplier 2'),
(3, 'Supplier 3', 'Alice Johnson', '1122334455', 'supplier3@example.com', '9101 Pine Road, City', 'Notes for Supplier 3'),
(4, '639533180925', '639533180925', '639533180925', 'seancvpugosa@gmail.com', '639533180925', '639533180925');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `type` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `type`, `username`, `email`, `department`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 1, 'admin', 'admin@gmail.com', 'Admin Department', '$2y$10$u2PHOpmKp2VonpCXQEQwmOaGuU.PELr0.Bo8jD3DOUkHOdYCeMuyC', '2024-11-14 05:12:36', '2024-11-14 05:25:00'),
(2, 'Regular User', 2, 'user1', 'user1@example.com', 'Sales', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '2024-11-14 05:12:36', '2024-11-14 05:12:36'),
(5, 'seancvpugosa@gmail.com', 2, 'seancvpugosa@gmail.com', 'seancvpugosa@gmail.com', 'seancvpugosa@gmail.com', '$2y$10$u2PHOpmKp2VonpCXQEQwmOaGuU.PELr0.Bo8jD3DOUkHOdYCeMuyC', '2024-11-14 05:24:54', '2024-11-14 05:24:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
