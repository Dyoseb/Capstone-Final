-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 01, 2025 at 03:39 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `age` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `full_name`, `address`, `contact_number`, `visit_date`, `visit_time`, `created_at`, `age`) VALUES
(1, 'Joseph R. Galang Jr.', 'Blk 17 Lot 13 Centennial Town Homes, San Isidro, Cabuyao, Laguna', '09129089007', '2025-02-18', '07:00 AM - 08:00 AM', '2025-02-01 13:23:42', 0),
(2, 'Julie Ann Largim', 'Mamatid Cabuyao Laguna', '0919899781', '2025-02-18', '08:00 AM - 09:00 AM', '2025-02-01 19:30:29', 0),
(3, 'Cristine Joy Dingas', 'Banlic Cabuyao Laguna', '09291817791', '2025-02-05', '09:00 AM - 10:00 AM', '2025-02-01 19:53:31', 0);

-- --------------------------------------------------------

--
-- Table structure for table `medicalrecords`
--

CREATE TABLE `medicalrecords` (
  `record_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `record_date` date NOT NULL,
  `age` int(2) NOT NULL,
  `lmp` date DEFAULT NULL,
  `edd` date NOT NULL,
  `aog` varchar(50) DEFAULT NULL,
  `tetanus_toxoid` int(11) DEFAULT NULL,
  `past_history` text DEFAULT NULL,
  `vs_aog` varchar(50) DEFAULT NULL,
  `gravida` int(5) NOT NULL,
  `para` int(5) NOT NULL,
  `abortus` int(5) NOT NULL,
  `vs_bp` varchar(50) DEFAULT NULL,
  `vs_pr` varchar(50) DEFAULT NULL,
  `vs_rr` varchar(50) DEFAULT NULL,
  `vs_fht` varchar(50) DEFAULT NULL,
  `vs_fhr` varchar(50) DEFAULT NULL,
  `vs_wht` varchar(50) DEFAULT NULL,
  `vs_ie` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicalrecords`
--

INSERT INTO `medicalrecords` (`record_id`, `appointment_id`, `patient_name`, `address`, `record_date`, `age`, `lmp`, `edd`, `aog`, `tetanus_toxoid`, `past_history`, `vs_aog`, `gravida`, `para`, `abortus`, `vs_bp`, `vs_pr`, `vs_rr`, `vs_fht`, `vs_fhr`, `vs_wht`, `vs_ie`, `remarks`, `created_at`) VALUES
(2, 1, 'Joseph R. Galang Jr.', 'Blk 17 Lot 13 Centennial Town Homes, San Isidro, Cabuyao, Laguna', '2025-02-04', 22, '2025-02-19', '2025-01-27', '1', 3, NULL, '', 1, 1, 1, '', '', '', '', '', '', '', '', '2025-02-04 12:26:58'),
(3, 3, 'Cristine Joy Dingas', 'Banlic Cabuyao Laguna', '2025-02-04', 18, '2025-01-27', '2025-01-27', '1', 3, 'UTI', '', 2, 0, 1, '', '', '', '', '', '', '', '', '2025-02-04 15:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `patient_used_products`
--

CREATE TABLE `patient_used_products` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `date_used` date NOT NULL,
  `quantity_used` int(10) DEFAULT NULL,
  `usage_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_used_products`
--

INSERT INTO `patient_used_products` (`id`, `patient_id`, `product_id`, `date_used`, `quantity_used`, `usage_notes`, `created_at`) VALUES
(1, 2, 1, '2025-02-07', 3, '', '2025-02-07 00:23:07'),
(2, 3, 1, '2025-02-07', 15, '', '2025-02-07 00:44:52'),
(3, 2, 1, '2025-02-07', 30, '', '2025-02-07 00:52:01'),
(4, 2, 1, '2025-02-07', 22, '', '2025-02-07 17:03:45'),
(19, 3, 2, '2025-02-09', 24, '', '2025-02-09 01:56:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(10) NOT NULL,
  `expiration` int(10) NOT NULL,
  `status` enum('Active','Archived','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `sku`, `name`, `quantity`, `price`, `expiration`, `status`) VALUES
(1, 'VIT-MOM-001', 'Prenatal Multivitamin', 20, 300, 2029, 'Archived'),
(2, 'VIT-MOM-002', 'Iron + Folic Acid Tablets', 6, 0, 0, 'Active'),
(3, 'VIT-MOM-003', 'Calcium + Vitamin D', 50, 0, 0, 'Archived');

-- --------------------------------------------------------

--
-- Table structure for table `restock`
--

CREATE TABLE `restock` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date_restocked` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restock`
--

INSERT INTO `restock` (`id`, `product_id`, `quantity`, `date_restocked`) VALUES
(1, 1, 19, '2025-02-07 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','midwife','utility') NOT NULL,
  `status` varchar(50) NOT NULL,
  `failed_attempts` int(11) NOT NULL,
  `lock_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `username`, `email`, `password`, `role`, `status`, `failed_attempts`, `lock_until`) VALUES
(1, 'Admin', 'admin@gmail.com', 'password1', 'admin', 'Active', 0, NULL),
(2, 'Doctor', 'doctor@gmail.com', 'doctor1', 'doctor', 'Active', 0, NULL),
(3, 'Staff', 'staff@gmail.com', 'staff1', 'midwife', 'Active', 0, NULL),
(4, 'Utility', 'Utility@gmail.com', 'utility1', 'utility', 'Active', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicalrecords`
--
ALTER TABLE `medicalrecords`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `patient_used_products`
--
ALTER TABLE `patient_used_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `restock`
--
ALTER TABLE `restock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `medicalrecords`
--
ALTER TABLE `medicalrecords`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient_used_products`
--
ALTER TABLE `patient_used_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `restock`
--
ALTER TABLE `restock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `medicalrecords`
--
ALTER TABLE `medicalrecords`
  ADD CONSTRAINT `medicalrecords_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_used_products`
--
ALTER TABLE `patient_used_products`
  ADD CONSTRAINT `patient_used_products_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `medicalrecords` (`record_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patient_used_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `restock`
--
ALTER TABLE `restock`
  ADD CONSTRAINT `restock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
