-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2025 at 09:47 AM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `full_name`, `address`, `contact_number`, `visit_date`, `visit_time`, `created_at`) VALUES
(1, 'Joseph R. Galang Jr.', 'Blk 17 Lot 13 Centennial Town Homes, San Isidro, Cabuyao, Laguna', '09129089007', '2025-02-18', '07:00 AM - 08:00 AM', '2025-02-01 13:23:42'),
(2, 'Julie Ann Largim', 'Mamatid Cabuyao Laguna', '0919899781', '2025-02-18', '08:00 AM - 09:00 AM', '2025-02-01 19:30:29'),
(3, 'Cristine Joy Dingas', 'Banlic Cabuyao Laguna', '09291817791', '2025-02-05', '09:00 AM - 10:00 AM', '2025-02-01 19:53:31'),
(7, 'Julie Ann Forteza', 'Blk 15 lot 14 P 1 San Isidro Heights', '09129079407', '2025-03-24', '07:00 AM - 08:00 AM', '2025-03-20 07:15:53');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `deliveries_age_group_10_14` int(11) DEFAULT NULL,
  `deliveries_age_group_15_19` int(11) DEFAULT NULL,
  `deliveries_age_group_20_49` int(11) DEFAULT NULL,
  `livebirths_age_group_10_14` int(11) DEFAULT NULL,
  `livebirths_age_group_15_19` int(11) DEFAULT NULL,
  `livebirths_age_group_20_49` int(11) DEFAULT NULL,
  `total_deliveries` int(11) DEFAULT NULL,
  `total_livebirths` int(11) DEFAULT NULL,
  `normal_birth_weight` int(11) DEFAULT NULL,
  `low_birth_weight` int(11) DEFAULT NULL,
  `unknown_weight` int(11) DEFAULT NULL,
  `attended_by_doctor` int(11) DEFAULT NULL,
  `attended_by_nurse` int(11) DEFAULT NULL,
  `attended_by_midwife` int(11) DEFAULT NULL,
  `public_health_facility` int(11) DEFAULT NULL,
  `private_health_facility` int(11) DEFAULT NULL,
  `non_facility_based_deliveries` int(11) DEFAULT NULL,
  `vaginal_deliveries_age_group_10_14` int(11) DEFAULT NULL,
  `vaginal_deliveries_age_group_15_19` int(11) DEFAULT NULL,
  `vaginal_deliveries_age_group_20_49` int(11) DEFAULT NULL,
  `c_section_deliveries_age_group_10_14` int(11) DEFAULT NULL,
  `c_section_deliveries_age_group_15_19` int(11) DEFAULT NULL,
  `c_section_deliveries_age_group_20_49` int(11) DEFAULT NULL,
  `total_vaginal_deliveries` int(11) DEFAULT NULL,
  `total_c_section_deliveries` int(11) DEFAULT NULL,
  `full_term_births_age_group_10_14` int(11) DEFAULT NULL,
  `full_term_births_age_group_15_19` int(11) DEFAULT NULL,
  `full_term_births_age_group_20_49` int(11) DEFAULT NULL,
  `pre_term_births_age_group_10_14` int(11) DEFAULT NULL,
  `pre_term_births_age_group_15_19` int(11) DEFAULT NULL,
  `pre_term_births_age_group_20_49` int(11) DEFAULT NULL,
  `fetal_deaths_age_group_10_14` int(11) DEFAULT NULL,
  `fetal_deaths_age_group_15_19` int(11) DEFAULT NULL,
  `fetal_deaths_age_group_20_49` int(11) DEFAULT NULL,
  `miscarriages_age_group_10_14` int(11) DEFAULT NULL,
  `miscarriages_age_group_15_19` int(11) DEFAULT NULL,
  `miscarriages_age_group_20_49` int(11) DEFAULT NULL,
  `total_full_term_births` int(11) DEFAULT NULL,
  `total_pre_term_births` int(11) DEFAULT NULL,
  `total_fetal_deaths` int(11) DEFAULT NULL,
  `total_miscarriages` int(11) DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `facility` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `deliveries_age_group_10_14`, `deliveries_age_group_15_19`, `deliveries_age_group_20_49`, `livebirths_age_group_10_14`, `livebirths_age_group_15_19`, `livebirths_age_group_20_49`, `total_deliveries`, `total_livebirths`, `normal_birth_weight`, `low_birth_weight`, `unknown_weight`, `attended_by_doctor`, `attended_by_nurse`, `attended_by_midwife`, `public_health_facility`, `private_health_facility`, `non_facility_based_deliveries`, `vaginal_deliveries_age_group_10_14`, `vaginal_deliveries_age_group_15_19`, `vaginal_deliveries_age_group_20_49`, `c_section_deliveries_age_group_10_14`, `c_section_deliveries_age_group_15_19`, `c_section_deliveries_age_group_20_49`, `total_vaginal_deliveries`, `total_c_section_deliveries`, `full_term_births_age_group_10_14`, `full_term_births_age_group_15_19`, `full_term_births_age_group_20_49`, `pre_term_births_age_group_10_14`, `pre_term_births_age_group_15_19`, `pre_term_births_age_group_20_49`, `fetal_deaths_age_group_10_14`, `fetal_deaths_age_group_15_19`, `fetal_deaths_age_group_20_49`, `miscarriages_age_group_10_14`, `miscarriages_age_group_15_19`, `miscarriages_age_group_20_49`, `total_full_term_births`, `total_pre_term_births`, `total_fetal_deaths`, `total_miscarriages`, `submission_date`, `facility`) VALUES
(1, 3, 3, 3, 3, 3, 3, 9, 9, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 0, 0, 0, 9, 0, 0, 3, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, 6, 3, 0, 0, '2025-03-24 12:13:13', ''),
(2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-03-24 12:14:25', ''),
(3, 2, 2, 2, 2, 2, 2, 6, 6, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 0, 0, 0, 6, 0, 0, 2, 2, 2, 0, 0, 0, 0, 0, 0, 0, 0, 4, 2, 0, 0, '2025-03-24 12:27:03', ''),
(4, 5, 5, 5, 5, 5, 5, 15, 15, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 0, 0, 15, 0, 5, 0, 0, 0, 5, 0, 0, 0, 5, 0, 0, 0, 5, 5, 5, 0, '2025-03-24 12:28:04', ''),
(5, 4, 4, 4, 4, 4, 4, 12, 12, 0, 0, 12, 4, 4, 4, 4, 4, 4, 4, 4, 4, 0, 0, 0, 12, 0, 4, 4, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 12, 0, 0, 0, '2025-03-24 12:33:45', ''),
(6, 3, 3, 3, 3, 3, 3, 9, 9, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 0, 0, 0, 9, 0, 3, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 9, 0, 0, 0, '2025-03-24 12:38:58', ''),
(7, 5, 5, 5, 5, 5, 5, 15, 15, 5, 5, 5, 5, 5, 5, 0, 5, 10, 5, 0, 5, 0, 5, 0, 10, 5, 5, 5, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 15, 0, 0, 0, '2025-03-24 12:56:14', ''),
(8, 3, 2, 2, 2, 2, 2, 6, 6, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 0, 0, 0, 6, 0, 2, 2, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, '2025-03-24 13:02:41', 'Amorganda Clinic');

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
(2, 'VIT-MOM-002', 'Iron + Folic Acid Tablets', 14, 0, 0, 'Active'),
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
(1, 1, 19, '2025-02-07 16:00:00'),
(2, 2, 3, '2025-03-03 16:00:00'),
(3, 2, 5, '2025-03-03 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `facility` varchar(50) NOT NULL,
  `role` enum('admin','doctor','midwife','utility') NOT NULL,
  `status` varchar(50) NOT NULL,
  `failed_attempts` int(11) NOT NULL,
  `lock_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `username`, `email`, `password`, `facility`, `role`, `status`, `failed_attempts`, `lock_until`) VALUES
(1, 'Admin', 'admin@gmail.com', 'password1', '', 'admin', 'Active', 0, NULL),
(2, 'Doctor', 'doctor@gmail.com', 'doctor1', '', 'doctor', 'Active', 0, NULL),
(3, 'Staff', 'staff@gmail.com', 'staff1', 'Amorganda-Clinic', 'midwife', 'Active', 0, NULL),
(4, 'Utility', 'Utility@gmail.com', 'utility1', '', 'utility', 'Active', 0, NULL),
(24, 'Amor', 'Amorganda@gmail.com', 'password123', 'Amorganda-Clinic', 'midwife', 'Active', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `room_board` decimal(10,2) DEFAULT 0.00,
  `drugs_medicine` decimal(10,2) DEFAULT 0.00,
  `delivery_room_fee` decimal(10,2) DEFAULT 0.00,
  `supplies` decimal(10,2) DEFAULT 0.00,
  `professional_name` varchar(255) NOT NULL,
  `professional_fees` decimal(10,0) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('pending','partial','paid') DEFAULT 'pending',
  `amount_paid` int(11) NOT NULL,
  `note` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `record_id`, `transaction_date`, `room_board`, `drugs_medicine`, `delivery_room_fee`, `supplies`, `professional_name`, `professional_fees`, `total_amount`, `payment_status`, `amount_paid`, `note`) VALUES
(1, 3, '2025-03-27 01:07:00', '1500.00', '0.00', '3000.00', '500.00', '', '0', '5000.00', '', 5000, ''),
(2, 2, '2025-03-27 04:56:00', '1500.00', '0.00', '3000.00', '500.00', '', '0', '0.00', '', 0, ''),
(3, 2, '2025-03-27 04:58:00', '1500.00', '0.00', '3000.00', '500.00', '', '0', '0.00', '', 5000, ''),
(4, 3, '2025-03-29 05:29:00', '1500.00', '0.00', '3000.00', '500.00', '', '1420', '6420.00', '', 5000, 'note'),
(5, 2, '2025-04-08 10:49:00', '1500.00', '500.00', '3000.00', '500.00', '', '0', '6920.00', '', 5000, ''),
(6, 3, '2025-04-08 11:14:00', '1500.00', '400.00', '3000.00', '500.00', '', '0', '6820.00', '', 0, ''),
(7, 3, '2025-04-08 11:17:00', '1500.00', '0.00', '3000.00', '500.00', '', '0', '6420.00', '', 0, ''),
(8, 2, '2025-04-08 11:28:00', '1500.00', '0.00', '3000.00', '500.00', '', '0', '6200.00', '', 0, ''),
(18, 2, '2025-04-08 12:22:00', '1500.00', '0.00', '3000.00', '500.00', '', '0', '6000.00', '', 0, ''),
(20, 2, '2025-04-08 12:34:00', '1500.00', '0.00', '3000.00', '500.00', '', '0', '5000.00', '', 0, ''),
(21, 2, '2025-04-08 13:49:00', '1500.00', '0.00', '3000.00', '500.00', '', '500', '0.00', '', 0, ''),
(23, 2, '2025-04-08 13:53:00', '1500.00', '0.00', '3000.00', '500.00', '0', '500', '0.00', '', 0, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
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
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `record_id` (`record_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `medicalrecords` (`record_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
