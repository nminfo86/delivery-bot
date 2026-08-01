-- phpMyAdmin SQL Dump
-- version 5.0.4deb2+deb11u2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 01, 2026 at 04:35 PM
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

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `category`, `prepare`, `display`, `available`, `supplement`, `acceptSupplement`, `color`, `categoryCover`, `minPrice`, `company_id`, `creationDate`, `updateDate`) VALUES
(50, 'Pizza', 1, 1, 1, 0, 1, '#9b59b6', '../../category-media/50/2022-08-06-11-17-27.jpg', 400, 6, '2022-02-17 19:47:12', '2026-06-04 21:44:37'),
(51, 'Tacos', 1, 6, 1, 0, 1, '#95a5a6', '../../category-media/51/2022-08-24-15-51-09.jpg', 600, 6, '2022-02-17 19:52:28', '2026-04-19 17:54:22'),
(55, 'Supplements', 0, 13, 1, 1, 0, '#2980b9', '../../category-media/55/2022-08-26-01-21-45.jpg', 100, 6, '2022-02-17 22:05:50', '2022-12-08 14:44:18'),
(56, 'Drinks', 0, 12, 1, 0, 0, '#2980b9', '../../category-media/56/2022-02-21-20-51-08.png', 30, 6, '2022-02-21 20:50:50', '2025-05-19 22:07:06'),
(58, 'Plats', 1, 3, 1, 0, 1, '#1abc9c', '../../category-media/58/2022-12-08-11-00-57.jpg', 500, 6, '2022-08-08 13:50:39', '2026-04-03 22:47:57'),
(59, 'Sandwich', 1, 9, 1, 0, 1, '#8b008b', '../../category-media/59/2022-08-08-13-55-42.jpg', 300, 6, '2022-08-08 13:55:03', '2026-05-30 21:31:56'),
(61, 'Dessert', 1, 11, 1, 0, 1, '#2c3e50', '../../category-media/61/2022-12-08-15-24-29.jpg', 250, 6, '2022-08-08 14:12:01', '2026-06-26 15:39:37'),
(62, 'Hamburgers', 1, 8, 1, 0, 1, '#d35400', '../../category-media/62/2022-08-14-22-12-58.png', 250, 6, '2022-08-14 22:12:27', '2025-05-31 21:56:07'),
(72, 'Shawarma', 1, 10, 1, 0, 1, '#2980b9', '../../category-media/72/2022-12-08-15-43-10.jpg', 250, 6, '2022-12-08 15:40:21', '2022-12-08 14:45:44'),
(74, '1/4_Pizza', 0, 11, 1, 1, 0, '#483d8b', NULL, 150, 6, '2025-09-03 22:45:10', '2026-05-31 20:13:49'),
(80, '1/2_Pizza', 0, 11, 1, 1, 0, '#483d8b', NULL, 250, 6, '2026-04-26 21:36:04', '2026-05-31 20:13:54');

-- --------------------------------------------------------

--
-- Table structure for table `category_attribute`
--

CREATE TABLE `category_attribute` (
  `category_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_attribute`
--

INSERT INTO `category_attribute` (`category_id`, `attribute_id`) VALUES
(50, 1),
(55, 1),
(56, 2),
(61, 3),
(74, 1),
(80, 1);

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

--
-- Dumping data for table `charge`
--

INSERT INTO `charge` (`id`, `amount`, `observation`, `dateTime`, `decaise`, `typeCharge_id`, `company_id`, `creationDate`, `updateDate`) VALUES
(518, 1000, 'expense yahia', '2026-04-03 00:00:00', 1, 6, 6, '2026-04-03 17:40:47', '2026-04-03 16:40:47'),
(519, 2500, 'Fromage', '2026-04-17 00:00:00', 1, 1, 6, '2026-04-17 17:53:40', '2026-04-17 16:53:40'),
(521, 1200, 'Fromage et viande', '2026-05-10 00:00:00', 1, 6, 6, '2026-05-10 17:50:57', '2026-05-10 16:50:57'),
(522, 2500, 'Viande Hachée', '2026-06-04 00:00:00', 1, 1, 6, '2026-06-04 22:26:21', '2026-06-04 21:26:21'),
(523, 700, 'Yacine', '2026-06-04 00:00:00', 1, 6, 6, '2026-06-04 22:29:10', '2026-06-04 21:29:10');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `companyName` varchar(200) NOT NULL,
  `companyDescription` varchar(500) DEFAULT NULL,
  `address` varchar(250) NOT NULL,
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
  `updateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id`, `companyName`, `companyDescription`, `address`, `phone`, `email`, `gps`, `latitude`, `longitude`, `day_off`, `is_open`, `consecutive_missed_orders`, `expires_at`, `subscription_status`, `companyCover`, `logo`, `carryCode`, `creationDate`, `updateDate`) VALUES
(1, 'Bouhezila', 'Bouhezila Computing', 'El Eulma', '0671017715', 'nassim.bouhezila@gmail.com', NULL, NULL, NULL, 'Friday', 1, 0, NULL, 'active', 'company-media/6/2026-05-29-18-43-25.png', 'company-media/6/2026-05-29-18-43-10.png', 0, '2020-11-20 00:00:00', '2026-05-29 17:43:25'),
(6, 'Lavida', 'Pizza Tacos Plats varies Crêpes', 'Cité El Houari-Boumediene El-Eulma', '0556 08 48 00', '', '', NULL, NULL, 'Friday', 1, 0, NULL, 'active', 'company-media/6/2026-05-29-18-43-25.png', 'company-media/6/2026-05-29-18-43-10.png', 5781, '2022-02-17 16:29:11', '2026-05-29 17:43:25');

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

--
-- Dumping data for table `licence`
--

INSERT INTO `licence` (`id`, `licenceName`, `licence`, `checked`, `adminUsers`, `chefUsers`, `waiterUsers`, `checkoutUsers`, `orderCapability`, `printChef`, `printClient`, `printArabicRecipe`, `cmsCurrency`, `cmsLanguage`, `backupBasePath`, `company_id`, `creationDate`, `updateDate`) VALUES
(5, 'Licence_LAVIDA', NULL, '2025-04-09 17:31:02', 1, 6, 2, 2, 1, 1, 1, 0, 'DA', 'fr', 'D:\\backup', 6, '2022-02-17 16:29:11', '2026-07-01 20:33:28'),
(6, 'Licence_eatSmartly', 'ZTQ2NTljN2Qx', '2026-07-29 21:12:12', 1, 6, 2, 2, 1, 1, 1, 0, 'DA', 'fr', 'D:\\backup', 1, '2025-04-09 22:21:09', '2026-07-29 20:12:12');

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

--
-- Dumping data for table `media`
--

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
-- Dumping data for table `object`
--

INSERT INTO `object` (`id`, `title`, `description`, `basePrice`, `baseCost`, `observation`, `category_id`, `objAvailable`, `company_id`, `creationDate`, `updateDate`) VALUES
(122, 'Tacos Poulet', 'Tacos Poulet', 600, 600, '', 51, 1, 6, '2022-02-21 20:39:50', '2026-04-19 17:54:22'),
(125, 'Pizza Vegetarienne', 'Pizza Vegetarienne', 600, 500, '', 50, 1, 6, '2022-02-24 11:12:52', '2026-04-19 17:55:09'),
(127, 'Pizza Thon', 'Thon', 500, 500, '', 50, 1, 6, '2022-02-24 11:19:24', '2026-04-26 18:56:43'),
(182, 'Tacos Viande Hachée', 'Tacos Viande', 800, 400, '', 51, 1, 6, '2022-02-27 14:26:47', '2026-04-10 14:50:30'),
(183, 'Tacos Mixte', 'Tacos Mixte', 900, 450, '', 51, 1, 6, '2022-02-27 14:30:11', '2026-04-10 14:25:18'),
(227, 'Eau', 'Eau', 30, 0, '', 56, 1, 6, '2022-03-02 20:47:40', '2022-12-09 13:59:17'),
(228, 'Pizza Merguez', 'Merguez, Poivron', 550, 550, '', 50, 1, 6, '2022-08-06 11:49:28', '2026-05-16 20:28:27'),
(229, 'Pizza Dinde Fume', 'Dine Fumé', 550, 550, '', 50, 1, 6, '2022-08-06 14:38:17', '2026-05-16 20:28:54'),
(242, 'Burger Simple', 'The main ingredients for a burger patty typically include ground beef, an egg, and seasonings like salt and pepper. Other common additions are breadcrumbs, cheese, and onions. ', 250, 150, '', 62, 1, 6, '2022-08-14 22:16:18', '2025-08-19 19:04:50'),
(263, 'Pizza 3 Fromages', 'Camambert, Fromage blanc', 600, 600, '', 50, 1, 6, '2022-08-24 11:52:13', '2026-05-16 20:29:26'),
(309, 'Lemonade', 'Lemonade Coca Pepsi Fanta', 100, 0, '', 56, 1, 6, '2022-08-25 00:18:00', '2025-08-19 19:08:22'),
(310, 'Jus', '', 100, 100, '', 56, 1, 6, '2022-08-25 00:25:15', '2026-04-10 13:42:36'),
(361, 'Suppl bordure fromage', 'Camembert, Gruyère, Cheddar, Gouda', 100, 100, '', 55, 1, 6, '2022-08-26 01:10:57', '2026-04-24 22:25:42'),
(362, 'Suppl crevèttes', 'Supplement crevettes', 800, 0, '', 55, 1, 6, '2022-08-26 01:19:33', '2025-05-01 17:14:56'),
(363, 'Shawarma libanais', 'Sandwich Shawarma Libanais', 300, 0, '', 72, 1, 6, '2022-08-28 18:34:11', '2022-12-08 21:05:42'),
(366, 'Mayonnaise', 'Mayonnaise', 0, 0, '', 55, 1, 6, '2022-08-28 22:54:07', '2022-08-28 22:02:15'),
(372, 'Barket frite', 'Barket frite', 100, 0, '', 55, 1, 6, '2022-09-04 22:13:15', '2022-12-08 20:42:24'),
(373, 'Pizza Champignons', 'Champignon', 500, 500, '', 50, 1, 6, '2022-12-08 11:44:17', '2026-05-16 20:29:52'),
(375, 'Pizza Boisee Poulet', 'Saumon Fumée', 500, 500, '', 50, 1, 6, '2022-12-08 12:17:33', '2026-05-16 21:07:03'),
(376, 'Plat steak hachee', '', 650, 0, '', 58, 1, 6, '2022-12-08 12:28:10', '2022-12-08 20:56:02'),
(377, 'Plat Entercote', '', 800, 0, '', 58, 1, 6, '2022-12-08 12:30:05', '2022-12-08 20:55:43'),
(378, 'Plat shawarma', '', 500, 0, '', 58, 1, 6, '2022-12-08 12:31:13', '2022-12-08 20:55:11'),
(379, 'Plat Merguez', '', 700, 0, '', 58, 1, 6, '2022-12-08 12:32:49', '2022-12-08 20:54:42'),
(380, 'Plat Kebda', '', 900, 0, '', 58, 1, 6, '2022-12-08 12:36:27', '2022-12-08 20:54:26'),
(381, 'Plat kintaké', '', 700, 0, '', 58, 1, 6, '2022-12-08 12:39:10', '2022-12-08 20:52:46'),
(382, 'Plat Royal L', '', 1400, 0, '', 58, 1, 6, '2022-12-08 12:39:58', '2022-12-08 11:39:58'),
(384, 'Plat poulet mariné', 'Plat poulet mariné', 600, 0, '', 58, 1, 6, '2022-12-08 12:44:46', '2022-12-08 20:56:54'),
(385, 'Plat poulet crispy', 'Plat poulet crispy', 700, 0, '', 58, 1, 6, '2022-12-08 12:47:26', '2022-12-08 21:00:17'),
(386, 'Plat poulet creme fraiche', '', 650, 0, '', 58, 1, 6, '2022-12-08 12:49:44', '2022-12-08 20:59:34'),
(387, 'Plat Escalope Griees', 'Plat Escalope Griees', 600, 0, '', 58, 1, 6, '2022-12-08 12:51:06', '2022-12-08 20:59:45'),
(389, 'Burger Doublee', '', 400, 200, '', 62, 1, 6, '2022-12-08 13:04:53', '2025-08-04 19:22:37'),
(390, 'Spècial', '', 350, 200, '', 62, 1, 6, '2022-12-08 13:07:37', '2025-08-04 19:22:18'),
(391, 'Burger Poulet', 'Chiken Burger', 250, 150, '', 62, 1, 6, '2022-12-08 13:08:42', '2025-08-04 19:22:48'),
(392, 'ساندويش كباب دجاج', '', 300, 150, '', 59, 1, 6, '2022-12-08 13:15:34', '2026-05-30 21:23:05'),
(393, 'ساندويش كباب لحم', '', 300, 130, '', 59, 1, 6, '2022-12-08 13:16:56', '2026-05-30 21:23:40'),
(395, 'ساندويش شيش طاووق', '', 350, 200, '', 59, 1, 6, '2022-12-08 13:24:32', '2026-05-30 21:24:25'),
(396, 'Suppl champignons', 'Doublé Champignons dans la pizza', 100, 0, '', 55, 1, 6, '2022-12-08 13:38:23', '2026-03-23 22:19:35'),
(399, 'Shawarma khobz', '', 250, 0, '', 72, 1, 6, '2022-12-08 15:45:44', '2022-12-08 14:45:44'),
(400, 'Shawarma Matlou3', '', 250, 0, '', 72, 1, 6, '2022-12-08 15:51:49', '2022-12-08 14:51:49'),
(401, 'Suppl camembert', 'Supplement Camembert', 100, 0, '', 55, 1, 6, '2022-12-08 21:46:01', '2022-12-08 20:46:01'),
(402, 'Suppl chedar', 'Supplement Chedar', 100, 0, '', 55, 1, 6, '2022-12-08 21:46:49', '2022-12-08 20:46:49'),
(403, 'Suppl poulet fumee', 'Supplement poulet fumee', 100, 0, '', 55, 1, 6, '2022-12-08 21:48:02', '2022-12-08 20:48:02'),
(442, 'Suppl saumon', 'Supplement saumon', 800, 0, '', 55, 1, 6, '2022-12-09 14:40:23', '2025-04-20 21:19:15'),
(443, 'Vitajus', 'Jus Vitajus', 30.5, 0, '', 56, 1, 6, '2022-12-09 15:04:07', '2023-02-20 18:11:30'),
(444, 'Tiramisus', 'Tiramisus  ', 250, 100, '', 61, 1, 6, '2025-08-18 20:57:00', '2025-08-18 19:57:00'),
(445, 'Crepes', 'Crepes', 250, 100, '', 61, 1, 6, '2025-08-18 21:03:43', '2025-08-18 20:03:54'),
(507, '-1/4 3 Fromages', 'Camambert, Fromage blanc', 150, 150, '', 74, 1, 6, '2026-05-16 22:07:54', '2026-05-16 21:07:54'),
(508, '-1/4 Boisee Poulet', 'Saumon Fumée', 150, 150, '', 74, 1, 6, '2026-05-16 22:07:54', '2026-05-16 21:07:54'),
(509, '-1/4 Champignons', 'Champignon', 150, 150, '', 74, 1, 6, '2026-05-16 22:07:54', '2026-05-16 21:07:54'),
(510, '-1/4 Dinde Fume', 'Dine Fumé', 150, 150, '', 74, 1, 6, '2026-05-16 22:07:54', '2026-05-16 21:07:54'),
(511, '-1/4 Merguez', 'Merguez, Poivron', 150, 150, '', 74, 1, 6, '2026-05-16 22:07:54', '2026-05-16 21:07:54'),
(512, '-1/4 Thon', 'Thon', 150, 150, '', 74, 1, 6, '2026-05-16 22:07:54', '2026-05-16 21:07:54'),
(513, '-1/4 Vegetarienne', 'Pizza Vegetarienne', 150, 150, '', 74, 1, 6, '2026-05-16 22:07:54', '2026-05-16 21:07:54'),
(514, '-1/4 Viande', '', 150, 150, '', 74, 1, 6, '2026-05-16 22:07:55', '2026-05-16 21:07:55'),
(515, '-1/2 3 Fromages', 'Camambert, Fromage blanc', 300, 300, '', 80, 1, 6, '2026-05-16 22:07:56', '2026-05-16 21:07:56'),
(516, '-1/2 Boisee Poulet', 'Saumon Fumée', 250, 250, '', 80, 1, 6, '2026-05-16 22:07:56', '2026-05-16 21:07:56'),
(517, '-1/2 Champignons', 'Champignon', 250, 250, '', 80, 1, 6, '2026-05-16 22:07:56', '2026-05-16 21:07:56'),
(518, '-1/2 Dinde Fume', 'Dine Fumé', 300, 300, '', 80, 1, 6, '2026-05-16 22:07:56', '2026-05-16 21:07:56'),
(519, '-1/2 Merguez', 'Merguez, Poivron', 300, 300, '', 80, 1, 6, '2026-05-16 22:07:56', '2026-05-16 21:07:56'),
(520, '-1/2 Thon', 'Thon', 250, 250, '', 80, 1, 6, '2026-05-16 22:07:56', '2026-05-16 21:07:56'),
(521, '-1/2 Vegetarienne', 'Pizza Vegetarienne', 300, 300, '', 80, 1, 6, '2026-05-16 22:07:56', '2026-05-16 21:07:56'),
(522, '-1/2 Viande', '', 250, 250, '', 80, 1, 6, '2026-05-16 22:07:57', '2026-05-16 21:07:57'),
(523, 'Pizza Viande', '', 500, 500, '', 50, 1, 6, '2026-05-30 21:33:47', '2026-05-30 20:33:47'),
(524, 'Pizza Poulet', '', 500, 500, '', 50, 1, 6, '2026-05-30 21:40:29', '2026-05-30 20:40:29'),
(525, 'Pizza peperoni', 'Pizza peperoni', 600, 600, '', 50, 1, 6, '2026-05-30 21:59:51', '2026-05-30 20:59:59'),
(526, 'Pizza Boisée Crevette', 'Pizza Boisée Crevette ', 600, 600, '', 50, 1, 6, '2026-05-30 22:05:20', '2026-05-30 21:05:20'),
(527, 'Pizza Boisée 4 Fromages', 'Pizza Boisée 4 Fromages', 600, 600, '', 50, 1, 6, '2026-05-30 22:06:49', '2026-05-30 21:06:49'),
(528, 'ساندويش كريسبي', '', 300, 150, '', 59, 1, 6, '2026-05-30 22:24:55', '2026-05-30 21:24:55'),
(529, 'ساندويش كبدة دجاج', '', 300, 150, '', 59, 1, 6, '2026-05-30 22:25:55', '2026-05-30 21:25:55'),
(530, 'ساندويش شاورما سورية', '', 300, 150, '', 59, 1, 6, '2026-05-30 22:28:49', '2026-05-30 21:28:49'),
(531, 'ساندويش فاهيتا', '', 300, 150, '', 59, 1, 6, '2026-05-30 22:29:42', '2026-05-30 21:29:42'),
(532, 'Pizza Simple', 'Pizza Simple', 400, 200, '', 50, 1, 6, '2026-06-04 22:44:37', '2026-06-04 21:44:37'),
(533, 'كنافة بالجبن', '', 400, 400, '', 61, 1, 6, '2026-06-26 16:58:01', '2026-06-26 15:58:01');

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
-- Dumping data for table `price`
--

INSERT INTO `price` (`object_id`, `attributeValue_id`, `price`, `cost`) VALUES
(125, 1, 600, 600),
(125, 2, 900, 900),
(127, 1, 500, 500),
(127, 2, 900, 900),
(127, 3, 1200, 1200),
(228, 1, 550, 550),
(228, 2, 900, 900),
(228, 3, 1300, 1300),
(229, 1, 550, 550),
(229, 2, 950, 950),
(229, 3, 1100, 1100),
(263, 1, 600, 600),
(263, 2, 900, 900),
(263, 3, 1250, 1250),
(309, 7, 150, 0),
(309, 8, 100, 0),
(309, 23, 100, 0),
(361, 1, 100, 100),
(361, 2, 150, 150),
(361, 3, 200, 200),
(373, 1, 500, 500),
(373, 2, 900, 900),
(373, 3, 1300, 1300),
(375, 1, 500, 500),
(375, 2, 900, 900),
(375, 3, 1200, 1200),
(396, 1, 100, 0),
(396, 2, 200, 0),
(396, 3, 300, 0),
(445, 9, 250, 100),
(445, 10, 300, 150),
(445, 12, 500, 250),
(507, 1, 150, 150),
(507, 2, 250, 250),
(507, 3, 350, 350),
(508, 1, 150, 150),
(508, 2, 250, 250),
(508, 3, 300, 300),
(509, 1, 150, 150),
(509, 2, 250, 250),
(509, 3, 350, 350),
(510, 1, 150, 150),
(510, 2, 250, 250),
(510, 3, 300, 300),
(511, 1, 150, 150),
(511, 2, 250, 250),
(511, 3, 350, 350),
(512, 1, 150, 150),
(512, 2, 250, 250),
(512, 3, 300, 300),
(513, 1, 150, 150),
(513, 2, 250, 250),
(514, 1, 150, 150),
(514, 2, 250, 250),
(514, 3, 300, 300),
(515, 1, 300, 300),
(515, 2, 450, 450),
(515, 3, 650, 650),
(516, 1, 250, 250),
(516, 2, 450, 450),
(516, 3, 600, 600),
(517, 1, 250, 250),
(517, 2, 450, 450),
(517, 3, 650, 650),
(518, 1, 300, 300),
(518, 2, 500, 500),
(518, 3, 550, 550),
(519, 1, 300, 300),
(519, 2, 450, 450),
(519, 3, 650, 650),
(520, 1, 250, 250),
(520, 2, 450, 450),
(520, 3, 600, 600),
(521, 1, 300, 300),
(521, 2, 450, 450),
(522, 1, 250, 250),
(522, 2, 450, 450),
(522, 3, 600, 600),
(523, 1, 500, 500),
(523, 2, 750, 750),
(523, 3, 1100, 1100),
(524, 1, 500, 500),
(524, 2, 700, 700),
(524, 3, 1000, 1000),
(525, 1, 600, 600),
(525, 2, 800, 800),
(525, 3, 1200, 1200),
(526, 1, 600, 600),
(526, 2, 900, 900),
(526, 3, 1400, 1400),
(527, 1, 600, 600),
(527, 2, 850, 850),
(527, 3, 1300, 1300);

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

--
-- Dumping data for table `printer`
--

INSERT INTO `printer` (`id`, `printerName`, `printerIP`, `printerPort`, `printerProtocole`, `labelSize`, `company_id`, `creationDate`, `updateDate`) VALUES
(2, 'Caisse_printer1', 'USB', '9100', 'ESC', '80', 6, '2022-01-15 15:25:32', '2026-05-11 18:31:19'),
(6, 'Printer-pizza', 'USB', '9100', 'ESC', '80', 6, '2022-01-15 15:25:32', '2026-06-26 15:38:40'),
(7, 'Printer-Plat-Sandwich', 'USB', '9100', 'ESC', '80', 6, '2022-01-15 15:25:32', '2026-06-26 15:38:44'),
(10, 'Printer-chawarma', 'USB', '9100', 'ESC', '80', 6, '2022-01-15 15:25:32', '2026-06-26 15:38:52'),
(14, 'printer-all', '192.168.1.100', '9100', 'TSPL', '40-20', 6, '2026-06-20 18:12:33', '2026-06-26 16:09:14'),
(15, 'Printer-dessert', 'USB', '9100', 'ESC', '80', 6, '2026-06-26 16:39:12', '2026-06-26 15:39:12');

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

--
-- Dumping data for table `system_migrations`
--

INSERT INTO `system_migrations` (`id`, `migration_name`, `applied_at`) VALUES
(1, '001_initial_schema.sql', '2026-04-30 18:23:10'),
(2, '002_performance_indexes.sql', '2026-07-01 20:05:09');

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

--
-- Dumping data for table `tabl`
--

INSERT INTO `tabl` (`id`, `tableName`, `tableCode`, `tableFree`, `creationDate`, `updateDate`) VALUES
(39, 'Table-01', 6731, 0, '2020-09-19 18:29:30', '2026-06-20 17:43:14'),
(40, 'Table-02', 7243, 0, '2020-09-19 18:29:39', '2026-04-22 21:06:05'),
(41, 'Table-03', 6109, 0, '2020-09-19 18:29:44', '2025-09-01 19:12:57'),
(42, 'Table-04', 3300, 0, '2020-09-19 18:29:50', '2026-04-04 21:06:34'),
(43, 'Table-05', 3177, 1, '2020-09-19 18:30:12', '2026-03-30 17:38:56'),
(44, 'Table-06', 5259, 0, '2020-09-19 18:30:19', '2025-05-01 19:23:09'),
(45, 'Table-07', 7380, 1, '2022-03-01 19:40:39', '2025-05-02 20:01:49'),
(46, 'Table-08', 8276, 1, '2022-03-01 19:40:54', '2025-07-20 12:18:50'),
(48, 'Table-09', 2415, 0, '2022-08-28 11:43:26', '2022-12-07 20:26:09'),
(49, 'Table-10', 1413, 0, '2022-08-28 11:43:38', '2022-12-31 00:29:16');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `name`, `familyName`, `email`, `connected`, `accessErrors`, `nextAccess`, `role_id`, `company_id`, `printer_id`, `creationDate`, `updateDate`) VALUES
(19, 'superAdmin', 'D96478671EC726974DFE7A766AD0B2C602E264DB9A21D5CC9F8EAFD9AC6478D1097B5502555ED06A6F7A1FB97B002973358EB1AA872D5ED23D9A5C847F2977F1', 'Nassim', 'Bouhezila', 'nassim.bouhezila@gmail.com', 0, 0, '1000-01-01 00:00:00', 6, 1, NULL, '2020-11-20 00:00:00', '2025-09-05 07:32:56'),
(27, 'caisse', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', 'Caissier', 'LaCantine', '', 0, 0, '1000-01-01 00:00:00', 3, 6, 2, '2022-02-17 20:41:21', '2026-07-07 18:55:39'),
(28, 'chef-plats-sandwich', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', 'Chef', 'Plats/Sandwich', '', 0, 0, '1000-01-01 00:00:00', 2, 6, 7, '2022-02-17 20:42:00', '2026-05-11 18:35:09'),
(33, 'chef-dessert', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', 'Dessert', 'Dessert', '', 0, 0, '1000-01-01 00:00:00', 2, 6, 15, '2022-08-26 00:26:21', '2026-06-26 15:40:19'),
(34, 'chef-chawarma', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', 'Chawarma', 'Chawarma', '', 0, 0, '1000-01-01 00:00:00', 2, 6, 10, '2022-08-26 00:27:44', '2022-08-25 23:52:25'),
(38, 'chef-all', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', 'chef', 'All', '', 0, 0, '1000-01-01 00:00:00', 2, 6, 6, '2025-04-15 06:10:08', '2026-07-02 11:15:23'),
(39, 'admin', '823d7b1a519d6455dd2c793c55992a7967e2bf4861914d97b75a07bbfe11fa1e55bfbcef4ca1c6f785bf1d0184d8df53e4e7e66fa29ca643e25c18f7d74188f0', 'Admin', 'Admin', '', 0, 0, '1000-01-01 00:00:00', 1, 6, NULL, '2025-04-21 20:53:47', '2026-07-07 18:55:47'),
(40, 'serveur', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', 'waiter', 'waiter', '', 0, 0, '1000-01-01 00:00:00', 4, 6, 2, '2025-05-02 20:25:33', '2026-07-02 11:15:32'),
(46, 'chef-pizz', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', 'chef', 'Pizza', '', 0, 0, '1000-01-01 00:00:00', 2, 6, 6, '2025-07-11 20:08:57', '2025-07-22 13:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `user_category`
--

CREATE TABLE `user_category` (
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_category`
--

INSERT INTO `user_category` (`user_id`, `category_id`) VALUES
(28, 58),
(28, 51),
(28, 62),
(28, 59),
(34, 72),
(46, 50),
(33, 61);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `charge`
--
ALTER TABLE `charge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=524;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1057;

--
-- AUTO_INCREMENT for table `object`
--
ALTER TABLE `object`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=534;

--
-- AUTO_INCREMENT for table `ordere`
--
ALTER TABLE `ordere`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=578;

--
-- AUTO_INCREMENT for table `ordere_archive`
--
ALTER TABLE `ordere_archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printer`
--
ALTER TABLE `printer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `suborder`
--
ALTER TABLE `suborder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1654;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `system_migrations`
--
ALTER TABLE `system_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tabl`
--
ALTER TABLE `tabl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `vat`
--
ALTER TABLE `vat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
