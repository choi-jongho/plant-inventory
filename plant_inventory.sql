-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 03:17 PM
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
-- Database: `plant_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(12, 'Tree'),
(13, 'Shrub'),
(14, 'Vegetable'),
(15, 'Fruit'),
(17, 'Succulent'),
(18, 'Foliage'),
(19, 'Herb');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `InventoryID` int(11) NOT NULL,
  `PlantID` int(11) NOT NULL,
  `SupplierID` int(11) NOT NULL,
  `inv_quantity` int(11) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`InventoryID`, `PlantID`, `SupplierID`, `inv_quantity`, `LastUpdated`) VALUES
(3, 13, 3, 90, '2025-06-13 21:14:24'),
(14, 24, 3, 200, '2025-06-13 20:59:26'),
(20, 25, 4, 100, '2025-06-13 05:15:18'),
(24, 25, 4, 100, '2025-06-13 20:55:13'),
(25, 14, 5, 100, '2025-06-13 21:04:07'),
(26, 26, 3, 100, '2025-06-13 21:07:35');

-- --------------------------------------------------------

--
-- Table structure for table `plants`
--

CREATE TABLE `plants` (
  `PlantID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `ScientificName` varchar(255) NOT NULL,
  `Location` varchar(255) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `ImagePath` longblob NOT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plants`
--

INSERT INTO `plants` (`PlantID`, `Name`, `ScientificName`, `Location`, `LastUpdated`, `ImagePath`, `category_id`) VALUES
(13, 'Snake Plant', 'Dracaena trifasciata', 'Tacloban City', '2025-06-12 21:20:43', 0x75706c6f6164732f313734393733333132325f536e616b655f506c616e745f2853616e73657669657269615f74726966617363696174615f274c617572656e74696927292e6a7067, 17),
(14, 'Cactus', 'Cactaceae', 'Tacloban City', '2025-06-12 21:22:01', 0x75706c6f6164732f313734393733333134385f696d616765732e6a7067, 17),
(24, 'Jackfruit', 'Artocarpus heterophyllus', 'Tacloban City', '2025-06-12 22:59:14', 0x75706c6f6164732f313734393736303936365f696d61676573202833292e6a7067, 12),
(25, 'Jade Plant', 'Crassula ovata', 'Catbalogan City', '2025-06-13 14:55:13', 0x75706c6f6164732f313734393736323839375f696d61676573202834292e6a7067, 17),
(26, 'Bamboo', 'Phyllostachys aurea', 'Borongan City', '2025-06-13 21:07:34', 0x75706c6f6164732f313734393832303035345f696d61676573202835292e6a7067, 12);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `SupplierID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `ContactEmail` varchar(255) NOT NULL,
  `PhoneNumber` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`SupplierID`, `Name`, `ContactEmail`, `PhoneNumber`) VALUES
(2, 'MY Incorporated', 'my.incorporated@gmail.com', '+639865421021'),
(3, 'MYZ Corporation', 'myz.corp@gmail.com', '+639089075432'),
(4, 'XYZ Inc.', 'xyzinc@gmail.com', '+639662985421'),
(5, 'ABCD Enterprise', 'abc.nayeon@gmail.com', '+639662985422'),
(7, 'GAR Corps.', 'corps.gar@gmail.com', '+639876543210');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `TransactionID` int(11) NOT NULL,
  `PlantID` int(11) NOT NULL,
  `TransactionType` enum('purchase','distribution') NOT NULL,
  `trans_quantity` int(11) NOT NULL,
  `TransactionDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`TransactionID`, `PlantID`, `TransactionType`, `trans_quantity`, `TransactionDate`) VALUES
(3, 14, 'purchase', 10, '2025-06-12 22:28:55'),
(4, 13, 'distribution', 20, '2025-06-13 00:58:56'),
(5, 13, 'distribution', 10, '2025-06-13 01:16:20'),
(6, 13, 'distribution', 10, '2025-06-13 01:16:47'),
(7, 14, 'purchase', 10, '2025-06-13 01:17:03'),
(8, 14, 'distribution', 10, '2025-06-13 01:17:19'),
(9, 14, 'purchase', 20, '2025-06-13 03:11:15'),
(10, 14, 'distribution', 20, '2025-06-13 03:11:38'),
(11, 14, 'purchase', 20, '2025-06-13 03:18:13'),
(12, 14, 'distribution', 10, '2025-06-13 03:18:54'),
(13, 13, 'distribution', 25, '2025-06-13 03:19:18'),
(14, 14, 'distribution', 20, '2025-06-13 03:21:42'),
(24, 24, 'purchase', 100, '2025-06-13 20:59:26'),
(25, 13, 'distribution', 10, '2025-06-13 21:14:24');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$AOatAFRCklnLwHx/3dyPXuIzcZXIm1BoGORZGha9X4w4R/j5HkmLO', 'admin'),
(2, 'admin1', '$2y$10$dDS0akkEnWHuXphCPobR/ur5lJqYifwM0GxSTszhI0FRIfGr1nvLC', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`InventoryID`),
  ADD KEY `inventory_ibfk_1` (`PlantID`),
  ADD KEY `inventory_ibfk_2` (`SupplierID`);

--
-- Indexes for table `plants`
--
ALTER TABLE `plants`
  ADD PRIMARY KEY (`PlantID`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`SupplierID`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `transactions_ibfk_1` (`PlantID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `InventoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `plants`
--
ALTER TABLE `plants`
  MODIFY `PlantID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `SupplierID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `TransactionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`PlantID`) REFERENCES `plants` (`PlantID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plants`
--
ALTER TABLE `plants`
  ADD CONSTRAINT `plants_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`PlantID`) REFERENCES `plants` (`PlantID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
