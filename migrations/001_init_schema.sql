-- phpMyAdmin SQL Dump
-- version 5.0.4deb2+deb11u2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 07, 2026 at 12:17 PM
-- Server version: 10.5.29-MariaDB-0+deb11u1
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `c3_bot_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attribute`
--

CREATE TABLE `attribute` (
  `id` int(11) NOT NULL,
  `attribute` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attribute`
--

INSERT INTO `attribute` (`id`, `attribute`) VALUES
(1, 'Taille'),
(2, 'Volume'),
(3, 'Dessert'),
(4, 'Sandwiche');

-- --------------------------------------------------------

--
-- Table structure for table `attribute_value`
--

CREATE TABLE `attribute_value` (
  `id` int(11) NOT NULL,
  `attributeValue` varchar(100) DEFAULT NULL,
  `attribute_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attribute_value`
--

INSERT INTO `attribute_value` (`id`, `attributeValue`, `attribute_id`) VALUES
(1, 'L', 1),
(2, 'XL', 1),
(3, 'XXL', 1),
(6, '2L', 2),
(7, '1L', 2),
(8, '33cL', 2),
(9, 'Nutella', 3),
(10, 'Banane', 3),
(11, 'Fruits', 3),
(12, '3Fruits', 3),
(14, 'Maison', 3),
(16, 'Poulet', 4),
(18, 'Fromage', 4),
(20, 'Mixte', 4),
(23, 'Canette', 2),
(24, '0.5L', 2),
(25, 'Cheese', 4),
(26, 'Thon', 4),
(27, 'Special', 4);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category` varchar(250) NOT NULL,
  `prepare` tinyint(1) NOT NULL DEFAULT 0,
  `display` int(11) NOT NULL DEFAULT 1,
  `available` tinyint(4) NOT NULL DEFAULT 0,
  `supplement` tinyint(1) NOT NULL DEFAULT 0,
  `acceptSupplement` tinyint(1) NOT NULL DEFAULT 0,
  `color` varchar(7) NOT NULL DEFAULT '#2980b9',
  `categoryCover` varchar(200) DEFAULT NULL,
  `minPrice` double DEFAULT 0,
  `company_id` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_attribute`
--

CREATE TABLE `category_attribute` (
  `category_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `charge`
--

CREATE TABLE `charge` (
  `id` int(11) NOT NULL,
  `amount` double NOT NULL DEFAULT 0,
  `observation` varchar(250) NOT NULL DEFAULT '',
  `dateTime` datetime NOT NULL,
  `decaise` int(11) NOT NULL DEFAULT 0 COMMENT 'decaissement',
  `typeCharge_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `companyName` varchar(200) NOT NULL,
  `companyDescription` varchar(500) DEFAULT NULL,
  `address` varchar(250) NOT NULL,
  `wilaya_name` varchar(100) DEFAULT NULL,
  `commune_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT '',
  `gps` varchar(50) DEFAULT '',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(10,8) DEFAULT NULL,
  `day_off` enum('None','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL DEFAULT 'Friday',
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `consecutive_missed_orders` int(11) NOT NULL DEFAULT 0,
  `expires_at` datetime DEFAULT NULL,
  `subscription_status` enum('active','warning','expired') NOT NULL DEFAULT 'active',
  `companyCover` varchar(500) DEFAULT NULL,
  `logo` varchar(200) DEFAULT NULL,
  `carryCode` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `social_link` varchar(255) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 5.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_bids`
--

CREATE TABLE `delivery_bids` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `bid_amount` double NOT NULL,
  `status` enum('pending','accepted','rejected','expired') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discount`
--

CREATE TABLE `discount` (
  `id` int(11) NOT NULL,
  `discount` varchar(100) NOT NULL DEFAULT '',
  `rate` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_profiles`
--

CREATE TABLE `driver_profiles` (
  `id` int(11) NOT NULL,
  `telegram_user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `wilaya_code` int(11) NOT NULL,
  `commune_name` varchar(100) NOT NULL,
  `moto_brand` varchar(50) NOT NULL,
  `moto_color` varchar(30) NOT NULL,
  `chassis_number` varchar(100) NOT NULL,
  `photo_moto` varchar(255) NOT NULL,
  `photo_plate` varchar(255) NOT NULL,
  `photo_chassis` varchar(255) NOT NULL,
  `verification_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 5.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `licence`
--

CREATE TABLE `licence` (
  `id` int(11) NOT NULL,
  `licenceName` varchar(250) NOT NULL,
  `licence` char(128) DEFAULT NULL,
  `checked` datetime NOT NULL DEFAULT current_timestamp(),
  `adminUsers` int(11) NOT NULL DEFAULT 1,
  `chefUsers` int(11) NOT NULL DEFAULT 1,
  `waiterUsers` int(11) NOT NULL DEFAULT 1,
  `checkoutUsers` int(11) NOT NULL DEFAULT 1,
  `orderCapability` tinyint(1) NOT NULL DEFAULT 0,
  `printChef` tinyint(4) NOT NULL DEFAULT 1,
  `printClient` tinyint(4) NOT NULL DEFAULT 1,
  `printArabicRecipe` tinyint(4) NOT NULL DEFAULT 0,
  `cmsCurrency` varchar(4) NOT NULL DEFAULT 'DA',
  `cmsLanguage` varchar(3) NOT NULL DEFAULT 'en',
  `backupBasePath` varchar(255) DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `media` varchar(200) NOT NULL,
  `mediaDescription` varchar(250) NOT NULL,
  `mediaType` varchar(3) NOT NULL,
  `mediaPosition` varchar(1) NOT NULL DEFAULT 'G',
  `object_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `object`
--

CREATE TABLE `object` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `basePrice` double NOT NULL DEFAULT 0,
  `baseCost` double NOT NULL DEFAULT 0,
  `observation` text NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT 1,
  `objAvailable` tinyint(4) NOT NULL DEFAULT 1,
  `company_id` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `object`
--
DELIMITER $$
CREATE TRIGGER `categoryMinPriceOnDelete` AFTER DELETE ON `object` FOR EACH ROW BEGIN

DECLARE newMinPrice DOUBLE;



SET newMinPrice = ( SELECT MIN(object.basePrice) from object where object.category_id = OLD.category_id AND object.basePrice >0);



UPDATE

        category

    SET

        category.minPrice = newMinPrice

                           

    WHERE

        id = OLD.category_id;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `categoryMinPriceOnInsert` AFTER INSERT ON `object` FOR EACH ROW BEGIN

DECLARE newMinPrice DOUBLE;



SET newMinPrice = ( SELECT MIN(object.basePrice) from object where object.category_id = NEW.category_id AND object.basePrice >0);



UPDATE

        category

    SET

        category.minPrice = newMinPrice

                             

    WHERE

        id = NEW.category_id;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `categoryMinPriceOnUpdate` AFTER UPDATE ON `object` FOR EACH ROW BEGIN

DECLARE newMinPrice DOUBLE;



SET newMinPrice = ( SELECT MIN(object.basePrice) from object where object.category_id = NEW.category_id AND object.basePrice >0);



UPDATE

        category

    SET

        category.minPrice = newMinPrice

                             

    WHERE

        id = NEW.category_id;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ordere`
--

CREATE TABLE `ordere` (
  `id` int(11) NOT NULL,
  `place` varchar(10) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `progression` varchar(30) NOT NULL DEFAULT 'NEW',
  `comment` varchar(300) DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 0,
  `payed` tinyint(1) NOT NULL DEFAULT 0,
  `customerLeft` tinyint(1) NOT NULL DEFAULT 0,
  `table_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `customer_telegram_id` bigint(20) DEFAULT NULL,
  `driver_profile_id` int(11) DEFAULT NULL,
  `cookieID` varchar(50) DEFAULT NULL,
  `discount_id` int(11) DEFAULT NULL,
  `discountAmount` double DEFAULT 0,
  `orderePrice` double DEFAULT 0 COMMENT 'Total HT',
  `delivery_fee` double NOT NULL DEFAULT 0,
  `delivery_lat` decimal(10,8) DEFAULT NULL,
  `delivery_lon` decimal(10,8) DEFAULT NULL,
  `delivery_address_note` varchar(250) DEFAULT NULL,
  `dispatch_type` enum('direct_driver','public_pool') DEFAULT 'public_pool',
  `vat_id` int(11) DEFAULT NULL,
  `vatAmount` double DEFAULT 0,
  `totalTtc` double NOT NULL DEFAULT 0,
  `creationDate` datetime NOT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `ordere`
--
DELIMITER $$
CREATE TRIGGER `Before_Update_Ordere_VatChange` BEFORE UPDATE ON `ordere` FOR EACH ROW BEGIN
DECLARE s DOUBLE DEFAULT 0;
DECLARE vRate DOUBLE DEFAULT 0;
DECLARE vAmt DOUBLE DEFAULT 0;

SELECT COALESCE(SUM(subTotal),0) INTO s FROM suborder WHERE ordere_id = NEW.id;
SELECT rate INTO vRate FROM vat WHERE id = NEW.vat_id LIMIT 1;
IF vRate IS NULL THEN SET vRate = 0; END IF;
SET vAmt = s * vRate / 100;
SET NEW.vatAmount = vAmt;
SET NEW.orderePrice = s;
IF vRate = 0 OR NEW.vat_id IS NULL OR NEW.vat_id = 'NULL' THEN
SET NEW.totalTtc = NEW.orderePrice;
ELSE
SET NEW.totalTtc = NEW.orderePrice + NEW.vatAmount;
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ordere_archive`
--

CREATE TABLE `ordere_archive` (
  `id` int(11) NOT NULL,
  `place` varchar(10) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `progression` varchar(30) NOT NULL DEFAULT 'NEW',
  `comment` varchar(300) DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 0,
  `payed` tinyint(1) NOT NULL DEFAULT 0,
  `customerLeft` tinyint(1) NOT NULL DEFAULT 0,
  `table_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `customer_telegram_id` bigint(20) DEFAULT NULL,
  `driver_profile_id` int(11) DEFAULT NULL,
  `cookieID` varchar(50) DEFAULT NULL,
  `discount_id` int(11) DEFAULT NULL,
  `discountAmount` double DEFAULT 0,
  `orderePrice` double DEFAULT 0 COMMENT 'Total HT',
  `delivery_fee` double NOT NULL DEFAULT 0,
  `delivery_lat` decimal(10,8) DEFAULT NULL,
  `delivery_lon` decimal(10,8) DEFAULT NULL,
  `delivery_address_note` varchar(250) DEFAULT NULL,
  `dispatch_type` enum('direct_driver','public_pool') DEFAULT 'public_pool',
  `vat_id` int(11) DEFAULT NULL,
  `vatAmount` double DEFAULT 0,
  `totalTtc` double NOT NULL DEFAULT 0,
  `creationDate` datetime NOT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price`
--

CREATE TABLE `price` (
  `object_id` int(11) NOT NULL,
  `attributeValue_id` int(11) NOT NULL,
  `price` double NOT NULL DEFAULT 0,
  `cost` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `price`
--
DELIMITER $$
CREATE TRIGGER `BasePrice_After_Delete_From_Table_Price` AFTER DELETE ON `price` FOR EACH ROW BEGIN 
  DECLARE newBasePrice DOUBLE;
  DECLARE newBaseCost DOUBLE;

  SET newBasePrice = (
    SELECT p.price
    FROM price p
    WHERE p.object_id = OLD.object_id
    ORDER BY p.price ASC, p.cost ASC, p.attributeValue_id ASC
    LIMIT 1
  );

  SET newBaseCost = (
    SELECT p.cost
    FROM price p
    WHERE p.object_id = OLD.object_id
    ORDER BY p.price ASC, p.cost ASC, p.attributeValue_id ASC
    LIMIT 1
  );

  IF newBasePrice IS NULL THEN
    SET newBasePrice = OLD.price;
    SET newBaseCost = OLD.cost;
  END IF;

  UPDATE object
  SET
    basePrice = newBasePrice,
    baseCost = newBaseCost
  WHERE id = OLD.object_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `BasePrice_After_Insert_in_Table_Price` AFTER INSERT ON `price` FOR EACH ROW BEGIN
DECLARE newBasePrice DOUBLE;
  DECLARE newBaseCost DOUBLE;

  SELECT p.price, p.cost
  INTO newBasePrice, newBaseCost
  FROM price p
  WHERE p.object_id = NEW.object_id
  ORDER BY p.price ASC
  LIMIT 1;

  UPDATE object
  SET
    basePrice = newBasePrice,
    baseCost = newBaseCost
  WHERE id = NEW.object_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `BasePrice_After_Update_in_Table_Price` AFTER UPDATE ON `price` FOR EACH ROW BEGIN
 DECLARE newBasePrice DOUBLE;
  DECLARE newBaseCost DOUBLE;

  SELECT p.price, p.cost
  INTO newBasePrice, newBaseCost
  FROM price p
  WHERE p.object_id = NEW.object_id
  ORDER BY p.price ASC
  LIMIT 1;

  IF newBasePrice IS NULL THEN
    SET newBasePrice = NEW.price;
    SET newBaseCost = NEW.cost;
  END IF;

  UPDATE object
  SET
    basePrice = newBasePrice,
    baseCost = newBaseCost
  WHERE id = NEW.object_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `printer`
--

CREATE TABLE `printer` (
  `id` int(11) NOT NULL,
  `printerName` varchar(200) NOT NULL,
  `printerIP` varchar(50) NOT NULL,
  `printerPort` varchar(50) NOT NULL,
  `printerProtocole` varchar(50) NOT NULL,
  `labelSize` varchar(20) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `role`) VALUES
(1, 'admin'),
(2, 'chef'),
(3, 'checkout'),
(4, 'waiter'),
(6, 'superAdmin');

-- --------------------------------------------------------

--
-- Table structure for table `suborder`
--

CREATE TABLE `suborder` (
  `id` int(11) NOT NULL,
  `ordere_id` int(11) NOT NULL,
  `object_id` int(11) DEFAULT NULL,
  `attributeValue_id` int(11) DEFAULT NULL,
  `quantity` float NOT NULL,
  `uPrice` double NOT NULL,
  `uCost` double NOT NULL DEFAULT 0,
  `subTotal` double NOT NULL,
  `subCost` double NOT NULL,
  `subCode` varchar(50) DEFAULT NULL,
  `subProgression` varchar(25) DEFAULT NULL,
  `subComment` varchar(300) DEFAULT '',
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `suborder`
--
DELIMITER $$
CREATE TRIGGER `UPdate_Ordere_Price_After_DELETE_FROM_SUBORDER` AFTER DELETE ON `suborder` FOR EACH ROW BEGIN
DECLARE orderId INT;
DECLARE tot DECIMAL(14,2) DEFAULT 0;
DECLARE vid INT DEFAULT NULL;
DECLARE rate DECIMAL(10,4) DEFAULT 0;
DECLARE vatAmt DECIMAL(14,2) DEFAULT 0;

SET orderId = OLD.ordere_id;

SELECT IFNULL(SUM(s.subTotal),0) INTO tot
FROM suborder s
WHERE s.ordere_id = orderId;

SELECT o.vat_id INTO vid
FROM ordere o
WHERE o.id = orderId LIMIT 1;

IF vid IS NOT NULL THEN
SELECT IFNULL(v.rate,0) INTO rate FROM vat v WHERE v.id = vid LIMIT 1;
ELSE
SET rate = 0;
END IF;

SET vatAmt = tot * rate / 100;

IF vid IS NULL OR rate = 0 THEN
UPDATE ordere
SET orderePrice = tot,
vatAmount = IFNULL(vatAmt,0),
totalTtc = tot
WHERE id = orderId;
ELSE
UPDATE ordere
SET orderePrice = tot,
vatAmount = IFNULL(vatAmt,0),
totalTtc = tot + IFNULL(vatAmt,0)
WHERE id = orderId;
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Update_Ordere_Price_After_INSERT_IN_SUBORDER` AFTER INSERT ON `suborder` FOR EACH ROW BEGIN
DECLARE tot DECIMAL(14,2) DEFAULT 0;
DECLARE rate DECIMAL(10,4) DEFAULT 0;
DECLARE vatAmt DECIMAL(14,2) DEFAULT 0;
DECLARE vid INT DEFAULT NULL;

SELECT IFNULL(SUM(s.subTotal),0) INTO tot
FROM suborder s
WHERE s.ordere_id = NEW.ordere_id;

SELECT o.vat_id INTO vid
FROM ordere o
WHERE o.id = NEW.ordere_id LIMIT 1;

IF vid IS NOT NULL THEN
SELECT IFNULL(v.rate,0) INTO rate FROM vat v WHERE v.id = vid LIMIT 1;
ELSE
SET rate = 0;
END IF;

SET vatAmt = tot * rate / 100;

IF vid IS NULL OR rate = 0 THEN
UPDATE ordere
SET orderePrice = tot,
vatAmount = IFNULL(vatAmt,0),
totalTtc = tot
WHERE id = NEW.ordere_id;
ELSE
UPDATE ordere
SET orderePrice = tot,
vatAmount = IFNULL(vatAmt,0),
totalTtc = tot + IFNULL(vatAmt,0)
WHERE id = NEW.ordere_id;
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Update_Ordere_Price_After_Update_SubOrder` AFTER UPDATE ON `suborder` FOR EACH ROW BEGIN
DECLARE orderId INT;
DECLARE tot DECIMAL(14,2) DEFAULT 0;
DECLARE vid INT DEFAULT NULL;
DECLARE rate DECIMAL(10,4) DEFAULT 0;
DECLARE vatAmt DECIMAL(14,2) DEFAULT 0;

SET orderId = COALESCE(NEW.ordere_id, OLD.ordere_id);

SELECT IFNULL(SUM(s.subTotal),0) INTO tot
FROM suborder s
WHERE s.ordere_id = orderId;

SELECT o.vat_id INTO vid
FROM ordere o
WHERE o.id = orderId LIMIT 1;

IF vid IS NOT NULL THEN
SELECT IFNULL(v.rate,0) INTO rate FROM vat v WHERE v.id = vid LIMIT 1;
ELSE
SET rate = 0;
END IF;

SET vatAmt = tot * rate / 100;

IF vid IS NULL OR rate = 0 THEN
UPDATE ordere
SET orderePrice = tot,
vatAmount = IFNULL(vatAmt,0),
totalTtc = tot
WHERE id = orderId;
ELSE
UPDATE ordere
SET orderePrice = tot,
vatAmount = IFNULL(vatAmt,0),
totalTtc = tot + IFNULL(vatAmt,0)
WHERE id = orderId;
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `suborder_archive`
--

CREATE TABLE `suborder_archive` (
  `id` int(11) NOT NULL,
  `ordere_id` int(11) NOT NULL,
  `object_id` int(11) DEFAULT NULL,
  `attributeValue_id` int(11) DEFAULT NULL,
  `quantity` float NOT NULL,
  `uPrice` double NOT NULL,
  `uCost` double NOT NULL DEFAULT 0,
  `subTotal` double NOT NULL,
  `subCost` double NOT NULL,
  `subCode` varchar(50) DEFAULT NULL,
  `subProgression` varchar(25) DEFAULT NULL,
  `subComment` varchar(300) DEFAULT '',
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_payments`
--

CREATE TABLE `subscription_payments` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `receipt_photo` varchar(255) NOT NULL,
  `amount_paid` double NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplement`
--

CREATE TABLE `supplement` (
  `id` int(11) NOT NULL,
  `ordere_id` int(11) NOT NULL,
  `suborder_id` int(11) NOT NULL,
  `supplementObject_id` int(11) NOT NULL,
  `supplementSuborderID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_migrations`
--

CREATE TABLE `system_migrations` (
  `id` int(11) NOT NULL,
  `migration_name` varchar(255) NOT NULL,
  `applied_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tabl`
--

CREATE TABLE `tabl` (
  `id` int(11) NOT NULL,
  `tableName` varchar(200) NOT NULL,
  `tableCode` int(11) NOT NULL,
  `tableFree` tinyint(4) NOT NULL DEFAULT 1,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telegram_users`
--

CREATE TABLE `telegram_users` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','restaurant','driver','admin') NOT NULL DEFAULT 'customer',
  `current_mode` enum('customer','kitchen','driver','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','suspended','banned') NOT NULL DEFAULT 'active',
  `strikes_count` int(11) NOT NULL DEFAULT 0,
  `company_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `current_state` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `commune_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `type_charge`
--

CREATE TABLE `type_charge` (
  `id` int(11) NOT NULL,
  `typeCharge` varchar(100) NOT NULL,
  `company_id` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `type_charge`
--

INSERT INTO `type_charge` (`id`, `typeCharge`, `company_id`, `creationDate`, `updateDate`) VALUES
(1, 'Achat Marchandises', 6, '2022-01-27 10:50:42', '2022-01-27 09:51:42'),
(4, 'Paie Employés', 6, '2022-01-27 10:51:45', '2022-01-27 09:55:13'),
(5, 'Dépenses Locatives', 6, '2022-01-27 10:51:45', '2022-01-27 09:55:13'),
(6, 'Dépenses Employés', 6, '2022-01-27 10:55:33', '2022-01-27 09:56:23'),
(7, 'Autre Dépenses', 6, '2022-01-27 10:55:33', '2022-01-27 09:56:23'),
(8, 'Repas Employés', 6, '2022-01-27 10:55:33', '2022-01-27 09:56:23');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` char(128) NOT NULL,
  `name` varchar(50) NOT NULL,
  `familyName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `connected` int(11) NOT NULL DEFAULT 0,
  `accessErrors` int(11) NOT NULL DEFAULT 0,
  `nextAccess` datetime NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `printer_id` int(11) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_category`
--

CREATE TABLE `user_category` (
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vat`
--

CREATE TABLE `vat` (
  `id` int(11) NOT NULL,
  `vat` varchar(100) NOT NULL,
  `rate` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attribute`
--
ALTER TABLE `attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attribute_value`
--
ALTER TABLE `attribute_value`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attribute_id` (`attribute_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`company_id`);

--
-- Indexes for table `category_attribute`
--
ALTER TABLE `category_attribute`
  ADD PRIMARY KEY (`category_id`,`attribute_id`) USING BTREE,
  ADD KEY `attribute_id` (`attribute_id`) USING BTREE;

--
-- Indexes for table `charge`
--
ALTER TABLE `charge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `charges_ibfk_company` (`company_id`),
  ADD KEY `charges_ibfk_typeCharge` (`typeCharge_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_coords` (`latitude`,`longitude`),
  ADD KEY `idx_company_active_search` (`is_open`,`day_off`,`subscription_status`);

--
-- Indexes for table `delivery_bids`
--
ALTER TABLE `delivery_bids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bids_order` (`order_id`),
  ADD KEY `idx_bids_driver` (`driver_id`);

--
-- Indexes for table `discount`
--
ALTER TABLE `discount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `driver_profiles`
--
ALTER TABLE `driver_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_user_id` (`telegram_user_id`),
  ADD KEY `idx_driver_wilaya_commune` (`wilaya_code`,`commune_name`);

--
-- Indexes for table `licence`
--
ALTER TABLE `licence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_media_object_mediaPosition` (`object_id`,`mediaPosition`);

--
-- Indexes for table `object`
--
ALTER TABLE `object`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `company_id` (`company_id`) USING BTREE,
  ADD KEY `idx_object_category_available` (`category_id`,`objAvailable`);

--
-- Indexes for table `ordere`
--
ALTER TABLE `ordere`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `vat_id` (`vat_id`),
  ADD KEY `discount_id` (`discount_id`),
  ADD KEY `idx_ordere_updateDate` (`updateDate`),
  ADD KEY `idx_ordere_company_payed_updateDate` (`company_id`,`payed`,`updateDate`),
  ADD KEY `idx_ordere_progression` (`progression`),
  ADD KEY `idx_ordere_code` (`code`),
  ADD KEY `idx_ordere_table_payed_updateDate` (`table_id`,`payed`,`updateDate`),
  ADD KEY `idx_ordere_code_updateDate` (`code`,`updateDate`),
  ADD KEY `idx_ordere_cookieID` (`cookieID`),
  ADD KEY `idx_ordere_progression_time` (`progression`,`creationDate`),
  ADD KEY `idx_ordere_customer` (`customer_telegram_id`),
  ADD KEY `idx_ordere_driver` (`driver_profile_id`);

--
-- Indexes for table `ordere_archive`
--
ALTER TABLE `ordere_archive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `vat_id` (`vat_id`),
  ADD KEY `discount_id` (`discount_id`),
  ADD KEY `idx_ordere_updateDate` (`updateDate`),
  ADD KEY `idx_ordere_company_payed_updateDate` (`company_id`,`payed`,`updateDate`),
  ADD KEY `idx_ordere_progression` (`progression`),
  ADD KEY `idx_ordere_code` (`code`),
  ADD KEY `idx_ordere_table_payed_updateDate` (`table_id`,`payed`,`updateDate`),
  ADD KEY `idx_ordere_code_updateDate` (`code`,`updateDate`),
  ADD KEY `idx_ordere_cookieID` (`cookieID`),
  ADD KEY `idx_ordere_progression_time` (`progression`,`creationDate`),
  ADD KEY `idx_ordere_customer` (`customer_telegram_id`),
  ADD KEY `idx_ordere_driver` (`driver_profile_id`);

--
-- Indexes for table `price`
--
ALTER TABLE `price`
  ADD PRIMARY KEY (`object_id`,`attributeValue_id`),
  ADD KEY `attribute_value_id` (`attributeValue_id`);

--
-- Indexes for table `printer`
--
ALTER TABLE `printer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suborder`
--
ALTER TABLE `suborder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`ordere_id`),
  ADD KEY `attributeValue_id` (`attributeValue_id`),
  ADD KEY `object_id` (`object_id`) USING BTREE,
  ADD KEY `idx_suborder_updateDate` (`updateDate`),
  ADD KEY `idx_suborder_subProgression` (`subProgression`);

--
-- Indexes for table `suborder_archive`
--
ALTER TABLE `suborder_archive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`ordere_id`),
  ADD KEY `attributeValue_id` (`attributeValue_id`),
  ADD KEY `object_id` (`object_id`) USING BTREE,
  ADD KEY `idx_suborder_updateDate` (`updateDate`),
  ADD KEY `idx_suborder_subProgression` (`subProgression`);

--
-- Indexes for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_company` (`company_id`);

--
-- Indexes for table `supplement`
--
ALTER TABLE `supplement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suborder_id` (`suborder_id`),
  ADD KEY `supplementObject_id` (`supplementObject_id`),
  ADD KEY `ordere_id` (`ordere_id`),
  ADD KEY `supplementSuborderID` (`supplementSuborderID`);

--
-- Indexes for table `system_migrations`
--
ALTER TABLE `system_migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `migration_name` (`migration_name`);

--
-- Indexes for table `tabl`
--
ALTER TABLE `tabl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tabl_tableCode` (`tableCode`);

--
-- Indexes for table `telegram_users`
--
ALTER TABLE `telegram_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`),
  ADD KEY `idx_telegram_id` (`telegram_id`),
  ADD KEY `idx_role_mode` (`role`,`current_mode`),
  ADD KEY `fk_tg_company` (`company_id`),
  ADD KEY `fk_tg_user` (`user_id`);

--
-- Indexes for table `type_charge`
--
ALTER TABLE `type_charge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_charge_ibfk_1` (`company_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `idx_user_username` (`username`),
  ADD KEY `role_id` (`role_id`) USING BTREE,
  ADD KEY `company_id` (`company_id`),
  ADD KEY `printer_id` (`printer_id`) USING BTREE;

--
-- Indexes for table `user_category`
--
ALTER TABLE `user_category`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `vat`
--
ALTER TABLE `vat`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attribute`
--
ALTER TABLE `attribute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attribute_value`
--
ALTER TABLE `attribute_value`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `charge`
--
ALTER TABLE `charge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_bids`
--
ALTER TABLE `delivery_bids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discount`
--
ALTER TABLE `discount`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_profiles`
--
ALTER TABLE `driver_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `licence`
--
ALTER TABLE `licence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `object`
--
ALTER TABLE `object`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ordere`
--
ALTER TABLE `ordere`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ordere_archive`
--
ALTER TABLE `ordere_archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printer`
--
ALTER TABLE `printer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `suborder`
--
ALTER TABLE `suborder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suborder_archive`
--
ALTER TABLE `suborder_archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplement`
--
ALTER TABLE `supplement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_migrations`
--
ALTER TABLE `system_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tabl`
--
ALTER TABLE `tabl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `telegram_users`
--
ALTER TABLE `telegram_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `type_charge`
--
ALTER TABLE `type_charge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vat`
--
ALTER TABLE `vat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attribute_value`
--
ALTER TABLE `attribute_value`
  ADD CONSTRAINT `attribute_value_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `category_attribute`
--
ALTER TABLE `category_attribute`
  ADD CONSTRAINT `category_attribute_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `category_attribute_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `charge`
--
ALTER TABLE `charge`
  ADD CONSTRAINT `charge_ibfk_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `charge_ibfk_typeCharge` FOREIGN KEY (`typeCharge_id`) REFERENCES `type_charge` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `delivery_bids`
--
ALTER TABLE `delivery_bids`
  ADD CONSTRAINT `fk_bids_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bids_order` FOREIGN KEY (`order_id`) REFERENCES `ordere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `driver_profiles`
--
ALTER TABLE `driver_profiles`
  ADD CONSTRAINT `fk_driver_tg_user` FOREIGN KEY (`telegram_user_id`) REFERENCES `telegram_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `licence`
--
ALTER TABLE `licence`
  ADD CONSTRAINT `licence_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `object`
--
ALTER TABLE `object`
  ADD CONSTRAINT `object_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `object_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `ordere`
--
ALTER TABLE `ordere`
  ADD CONSTRAINT `ordere_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tabl` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ordere_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ordere_ibfk_3` FOREIGN KEY (`vat_id`) REFERENCES `vat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ordere_ibfk_4` FOREIGN KEY (`discount_id`) REFERENCES `discount` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `price`
--
ALTER TABLE `price`
  ADD CONSTRAINT `price_ibfk_1` FOREIGN KEY (`object_id`) REFERENCES `object` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `price_ibfk_2` FOREIGN KEY (`attributeValue_id`) REFERENCES `attribute_value` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `printer`
--
ALTER TABLE `printer`
  ADD CONSTRAINT `printer_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `suborder`
--
ALTER TABLE `suborder`
  ADD CONSTRAINT `suborder_ibfk_2` FOREIGN KEY (`ordere_id`) REFERENCES `ordere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `suborder_ibfk_3` FOREIGN KEY (`attributeValue_id`) REFERENCES `attribute_value` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `suborder_ibfk_4` FOREIGN KEY (`object_id`) REFERENCES `object` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  ADD CONSTRAINT `fk_sub_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `supplement`
--
ALTER TABLE `supplement`
  ADD CONSTRAINT `supplement_ibfk_1` FOREIGN KEY (`suborder_id`) REFERENCES `suborder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplement_ibfk_2` FOREIGN KEY (`supplementObject_id`) REFERENCES `object` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplement_ibfk_3` FOREIGN KEY (`ordere_id`) REFERENCES `ordere` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplement_ibfk_4` FOREIGN KEY (`supplementSuborderID`) REFERENCES `suborder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `telegram_users`
--
ALTER TABLE `telegram_users`
  ADD CONSTRAINT `fk_tg_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tg_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `type_charge`
--
ALTER TABLE `type_charge`
  ADD CONSTRAINT `type_charge_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_3` FOREIGN KEY (`printer_id`) REFERENCES `printer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_category`
--
ALTER TABLE `user_category`
  ADD CONSTRAINT `user_category_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
