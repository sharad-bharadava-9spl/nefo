-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 03, 2020 at 09:05 AM
-- Server version: 5.7.25-google-log
-- PHP Version: 7.3.6-1+0~20190531112735.39+stretch~1.gbp6131b7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ccs_betsaide`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` varchar(5000) NOT NULL DEFAULT '',
  `accepted` int(11) NOT NULL DEFAULT '0',
  `fulfilled` int(11) NOT NULL DEFAULT '0',
  `cita` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `banked`
--

CREATE TABLE `banked` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `userid` int(11) NOT NULL DEFAULT '0',
  `comment` varchar(5000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_catdiscounts`
--

CREATE TABLE `b_catdiscounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `categoryid` int(11) NOT NULL DEFAULT '0',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `happy_hour_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_categories`
--

CREATE TABLE `b_categories` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(200) NOT NULL DEFAULT '',
  `sortorder` int(11) NOT NULL DEFAULT '9999'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_inddiscounts`
--

CREATE TABLE `b_inddiscounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `purchaseid` int(11) NOT NULL DEFAULT '0',
  `discount` decimal(15,2) DEFAULT NULL,
  `fijo` decimal(15,2) DEFAULT NULL,
  `happy_hour_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_productmovements`
--

CREATE TABLE `b_productmovements` (
  `movementid` int(11) NOT NULL,
  `movementtime` datetime DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `purchaseid` int(11) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `movementTypeid` int(11) DEFAULT NULL,
  `comment` varchar(300) DEFAULT NULL,
  `doneAtRegistration` tinyint(1) NOT NULL DEFAULT '0',
  `provider` int(11) NOT NULL DEFAULT '0',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `paid` decimal(15,2) NOT NULL DEFAULT '0.00',
  `destination` varchar(15) NOT NULL DEFAULT '',
  `priceg` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stashMovementType` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_products`
--

CREATE TABLE `b_products` (
  `productid` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `category` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(1000) NOT NULL DEFAULT '',
  `price` decimal(15,2) DEFAULT NULL,
  `photoExt` varchar(4) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_providerpayments`
--

CREATE TABLE `b_providerpayments` (
  `paymentid` int(11) NOT NULL,
  `providerid` int(11) NOT NULL DEFAULT '0',
  `paymentTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comment` varchar(1000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_providers`
--

CREATE TABLE `b_providers` (
  `id` int(11) NOT NULL,
  `registered` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL DEFAULT '',
  `comment` varchar(1000) NOT NULL DEFAULT '',
  `providernumber` int(11) NOT NULL DEFAULT '0',
  `credit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `enlisted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_purchases`
--

CREATE TABLE `b_purchases` (
  `purchaseid` int(11) NOT NULL,
  `category` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `purchaseDate` datetime DEFAULT NULL,
  `purchasePrice` decimal(15,2) DEFAULT NULL,
  `salesPrice` decimal(15,2) DEFAULT NULL,
  `purchaseQuantity` int(11) DEFAULT NULL,
  `adminComment` varchar(1000) DEFAULT NULL,
  `estClosing` decimal(10,0) DEFAULT NULL,
  `closingComment` varchar(1000) DEFAULT NULL,
  `closedAt` decimal(10,0) DEFAULT NULL,
  `closedSales` decimal(10,0) DEFAULT NULL,
  `closedReloads` decimal(10,0) DEFAULT NULL,
  `closedTakeouts` decimal(10,0) DEFAULT NULL,
  `closedAdditions` decimal(10,0) DEFAULT NULL,
  `closingDate` datetime DEFAULT NULL,
  `inMenu` int(11) NOT NULL DEFAULT '0',
  `photoExt` varchar(5) NOT NULL DEFAULT '',
  `provider` int(11) NOT NULL DEFAULT '0',
  `paid` decimal(15,2) NOT NULL DEFAULT '0.00',
  `barCode` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_sales`
--

CREATE TABLE `b_sales` (
  `saleid` int(11) NOT NULL,
  `saletime` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `unitsTot` decimal(15,2) NOT NULL DEFAULT '0.00',
  `adminComment` varchar(1000) DEFAULT NULL,
  `creditBefore` decimal(15,2) DEFAULT NULL,
  `creditAfter` decimal(15,2) DEFAULT NULL,
  `direct` int(11) DEFAULT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discounteur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `operatorid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_salesdetails`
--

CREATE TABLE `b_salesdetails` (
  `salesdetailsid` int(11) NOT NULL,
  `saleid` int(11) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `purchaseid` int(11) NOT NULL DEFAULT '0',
  `discountType` int(11) NOT NULL DEFAULT '0',
  `discountPercentage` decimal(15,2) NOT NULL DEFAULT '0.00',
  `happyhourDiscount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `volumeDiscount` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_sales_discount`
--

CREATE TABLE `b_sales_discount` (
  `id` int(11) NOT NULL,
  `salesId` int(11) NOT NULL,
  `discountType` int(11) NOT NULL,
  `discountPercentage` decimal(15,2) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `b_volume_discounts`
--

CREATE TABLE `b_volume_discounts` (
  `id` int(11) NOT NULL,
  `purchaseid` int(11) NOT NULL DEFAULT '0',
  `units` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `card_purchase`
--

CREATE TABLE `card_purchase` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `paidTo` int(11) NOT NULL DEFAULT '0',
  `operatorid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `catdiscounts`
--

CREATE TABLE `catdiscounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `categoryid` int(11) NOT NULL DEFAULT '0',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `happy_hour_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(1000) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `sortorder` int(11) NOT NULL DEFAULT '9999',
  `icon` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `time`, `name`, `description`, `type`, `sortorder`, `icon`) VALUES
(1, '2019-08-08 07:53:22', 'Flowers', '', 0, 9999, ''),
(2, '2019-08-08 07:53:22', 'Extract', '', 0, 9999, '');

-- --------------------------------------------------------

--
-- Table structure for table `closing`
--

CREATE TABLE `closing` (
  `closingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `closingtime` datetime DEFAULT NULL,
  `shiftEnd` datetime DEFAULT NULL,
  `quantitySold` decimal(15,2) DEFAULT NULL,
  `soldtoday` decimal(15,2) DEFAULT NULL,
  `unitsSold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notpaidtoday` decimal(15,2) DEFAULT NULL,
  `closingbalance` decimal(15,2) DEFAULT NULL,
  `moneytaken` decimal(15,2) DEFAULT NULL,
  `takenduringday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cashintill` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `newmembers` int(11) DEFAULT NULL,
  `closedby` int(11) DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `expenses` decimal(15,2) DEFAULT NULL,
  `membershipFees` decimal(15,2) DEFAULT NULL,
  `estimatedTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `bankExpenses` decimal(15,2) DEFAULT NULL,
  `debtRepaid` decimal(15,2) DEFAULT NULL,
  `debtRepaidBank` decimal(15,2) DEFAULT NULL,
  `prodOpening` decimal(15,2) DEFAULT NULL,
  `prodAdded` decimal(15,2) DEFAULT NULL,
  `prodRemoved` decimal(15,2) DEFAULT NULL,
  `prodEstStock` decimal(15,2) DEFAULT NULL,
  `prodStock` decimal(15,2) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `prodStockFlower` decimal(15,2) DEFAULT NULL,
  `prodStockExtract` decimal(15,2) DEFAULT NULL,
  `income` decimal(15,2) DEFAULT NULL,
  `paraphernalia` decimal(15,2) DEFAULT NULL,
  `biscuits` decimal(15,2) DEFAULT NULL,
  `drinksandsnacks` decimal(15,2) DEFAULT NULL,
  `prerolls` decimal(15,2) DEFAULT NULL,
  `otherAdditions` decimal(15,2) DEFAULT NULL,
  `prodOpeningFlower` decimal(15,2) DEFAULT NULL,
  `prodOpeningExtract` decimal(15,2) DEFAULT NULL,
  `prodAddedFlower` decimal(15,2) DEFAULT NULL,
  `prodAddedExtract` decimal(15,2) DEFAULT NULL,
  `prodRemovedFlower` decimal(15,2) DEFAULT NULL,
  `prodRemovedExtract` decimal(15,2) DEFAULT NULL,
  `prodEstStockFlower` decimal(15,2) DEFAULT NULL,
  `prodEstStockExtract` decimal(15,2) DEFAULT NULL,
  `stockDeltaFlower` decimal(15,2) DEFAULT NULL,
  `stockDeltaExtract` decimal(15,2) DEFAULT NULL,
  `donations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bankDonations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `renewedMembers` int(11) NOT NULL DEFAULT '0',
  `bannedMembers` int(11) NOT NULL DEFAULT '0',
  `deletedMembers` int(11) NOT NULL DEFAULT '0',
  `expiredMembers` int(11) NOT NULL DEFAULT '0',
  `totalMembers` int(11) NOT NULL DEFAULT '0',
  `activeMembers` int(11) NOT NULL DEFAULT '0',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerweightNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extracttotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftNumberClosed` int(11) NOT NULL DEFAULT '0',
  `shiftTypeClosed` int(11) NOT NULL DEFAULT '0',
  `membershipfeesBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldtodayBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSoldBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalanceBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dayClosed` int(11) NOT NULL DEFAULT '0',
  `dayClosedBy` int(11) NOT NULL DEFAULT '0',
  `recClosed` int(11) NOT NULL DEFAULT '0',
  `recClosedBy` int(11) NOT NULL DEFAULT '0',
  `disClosed` int(11) NOT NULL DEFAULT '0',
  `disClosedBy` int(11) NOT NULL DEFAULT '0',
  `dayClosedNo` int(11) NOT NULL DEFAULT '0',
  `currentClosing` int(11) NOT NULL DEFAULT '0',
  `totCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dayOpened` int(11) NOT NULL DEFAULT '0',
  `recOpened` int(11) NOT NULL DEFAULT '0',
  `disOpened` int(11) NOT NULL DEFAULT '0',
  `recOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disOpenedBy` int(11) NOT NULL DEFAULT '0',
  `dayOpenedNo` int(11) NOT NULL DEFAULT '0',
  `dayOpenedBy` int(11) NOT NULL DEFAULT '0',
  `recShiftOpened` int(11) NOT NULL DEFAULT '0',
  `disShiftOpened` int(11) NOT NULL DEFAULT '0',
  `recShiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disShiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `recOpenedAt` datetime DEFAULT NULL,
  `disOpenedAt` datetime DEFAULT NULL,
  `recShiftOpenedAt` datetime DEFAULT NULL,
  `disShiftOpenedAt` datetime DEFAULT NULL,
  `quantitySoldReal` decimal(15,2) DEFAULT NULL,
  `soldTodayFlowerReal` decimal(15,2) DEFAULT '0.00',
  `soldTodayExtractReal` decimal(15,2) DEFAULT '0.00',
  `dis2Opened` int(11) DEFAULT NULL,
  `dis2OpenedBy` int(11) DEFAULT NULL,
  `dis2OpenedAt` datetime DEFAULT NULL,
  `barOpened` int(11) DEFAULT NULL,
  `barOpenedBy` int(11) DEFAULT NULL,
  `barOpenedAt` datetime DEFAULT NULL,
  `dis2Closed` int(11) DEFAULT NULL,
  `dis2ClosedBy` int(11) DEFAULT NULL,
  `dis2ClosedAt` datetime DEFAULT NULL,
  `barClosed` int(11) DEFAULT NULL,
  `barClosedBy` int(11) DEFAULT NULL,
  `barClosedAt` datetime DEFAULT NULL,
  `directDispensedCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directDispensedBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directBarCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directBarBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `chipincome` decimal(15,2) NOT NULL DEFAULT '0.00',
  `chipincomecard` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `closingdetails`
--

CREATE TABLE `closingdetails` (
  `closingdetailsid` int(11) NOT NULL,
  `closingid` int(11) NOT NULL DEFAULT '0',
  `category` int(11) NOT NULL DEFAULT '0',
  `categoryType` int(11) NOT NULL DEFAULT '0',
  `productid` int(11) NOT NULL DEFAULT '0',
  `purchaseid` int(11) NOT NULL DEFAULT '0',
  `weightToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `addedToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `takeoutsToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `weight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `weightEst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `weightDelta` decimal(15,2) NOT NULL DEFAULT '0.00',
  `specificComment` varchar(1000) DEFAULT NULL,
  `shakePercentage` int(11) NOT NULL DEFAULT '0',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `weightNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `inMenu` int(11) NOT NULL DEFAULT '0',
  `amountToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `value` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `closingother`
--

CREATE TABLE `closingother` (
  `id` int(11) NOT NULL,
  `closingid` int(11) DEFAULT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `categoryType` int(11) NOT NULL DEFAULT '0',
  `stockDelta` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantitySold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldtoday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodOpening` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodAdded` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodRemoved` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodEstStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantitySoldReal` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `closing_mails`
--

CREATE TABLE `closing_mails` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `contract`
--

CREATE TABLE `contract` (
  `cif` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `club` varchar(100) NOT NULL,
  `address` varchar(500) NOT NULL,
  `time` datetime NOT NULL,
  `image` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cuotas`
--

CREATE TABLE `cuotas` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `cuota` decimal(15,2) NOT NULL DEFAULT '0.00',
  `days` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `saleid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `street` varchar(100) NOT NULL DEFAULT '',
  `streetnumber` varchar(50) NOT NULL DEFAULT '',
  `flat` varchar(50) NOT NULL DEFAULT '',
  `postcode` varchar(20) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `telephone` varchar(50) NOT NULL DEFAULT '',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `donationid` int(11) NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `donationTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comment` varchar(1000) DEFAULT NULL,
  `creditBefore` decimal(15,2) DEFAULT NULL,
  `creditAfter` decimal(15,2) DEFAULT NULL,
  `donatedTo` int(11) NOT NULL DEFAULT '0',
  `operator` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `reg_id` int(10) UNSIGNED NOT NULL,
  `empno` varchar(50) NOT NULL DEFAULT '',
  `f_no1` tinyint(4) NOT NULL,
  `fptemplate1` varchar(3000) NOT NULL,
  `f_no2` tinyint(4) NOT NULL,
  `fptemplate2` varchar(3000) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `empfname` varchar(50) NOT NULL,
  `empsname` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `expensecategories`
--

CREATE TABLE `expensecategories` (
  `categoryid` int(11) NOT NULL,
  `nameen` varchar(100) NOT NULL DEFAULT '',
  `namees` varchar(100) NOT NULL DEFAULT '',
  `descriptionen` varchar(1000) DEFAULT NULL,
  `descriptiones` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `expensecategories`
--

INSERT INTO `expensecategories` (`categoryid`, `nameen`, `namees`, `descriptionen`, `descriptiones`) VALUES
(1, 'Admin', 'Administración', 'Constitution, ayuntamiento, license, signup contracts, consumption forms...', 'Constitución, ayuntamiento, licencia, contratos para socios nuevos, etc.'),
(2, 'Utilities', 'Utilidades', 'Dispense bags, lightbulbs, display jars, mop, bucket, broom, incense, bathroom soap, folders...', 'Chibatas, Bombias, Jarras, etc.'),
(3, 'Cleaning products', 'Productos de limpieza', 'Wipes, cloths, floor cleaner, multipurpose cleaner, window cleaner...', 'Trapos, Jabon, Escobas, etc.'),
(4, 'Drinks and snacks ', 'Bebida y Comida', 'Food, chocolate, beer, drinks...', 'Chocolate, Cerveza, Zumos, etc.'),
(5, 'Reloads', 'Recargas', 'Flowers and extracts', 'Flores y Extractos'),
(6, 'System', 'Sistema', 'Computers, display screens, cameras, chips...', 'Ordenadores, Cameras, Chips de ID, etc.'),
(7, 'Edibles', 'Comestibles', 'Biscuits, THC chocolate, caramels...', 'Galletas THC, Caramelos THC, etc.'),
(8, 'Extracts', 'Extractos', 'Products for making extracts; ice, gas, nets, bubble machine...', 'Productos para crear extractos: Hielo, Gas, Redes, etc.'),
(9, 'Workshops/classes ', 'Clases', 'Material for preparing and hosting workshops or classes', 'Materia para preparar clases y cursos'),
(11, 'Building and renovations', 'Construccion', 'Everything construction and building related', 'Todo relacionado a construccion'),
(12, 'Legal', 'Legal', 'Legal costs', 'Costes Legales'),
(13, 'Gestoria', 'Gestoria', 'Gestoria costs', 'Costes Gestorial'),
(14, 'Bills', 'Recibos', 'Internet, electricity, water...', 'Internet, Electricidad, Agua, etc.'),
(15, 'Grow', 'Crianza de producto', 'Fertilizers, soil, insecticide, pots, lamps...', 'Fertilizante, Insecticides, lamparas, etc.'),
(16, 'Smoking Products', 'Productos de Fumar', 'Papers, filters, cones...', 'Papels, Cartones, Conos, etc.'),
(17, 'Transport', 'Transporte', 'Taxi, bus, metro...', 'Taxi, Bus, Metro, etc.'),
(18, 'Entertainment', 'Entretenimiento', 'Playstation, speakers, TV, projector...', 'Altavoces, Videojuegos, Televisión, etc.'),
(19, 'Other', 'Otro', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expenseid` int(11) NOT NULL,
  `registertime` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `expensetype` varchar(100) DEFAULT NULL,
  `expense` varchar(100) DEFAULT NULL,
  `moneysource` int(11) DEFAULT NULL,
  `other` varchar(1000) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `shop` varchar(100) DEFAULT NULL,
  `comment` varchar(1000) DEFAULT NULL,
  `receipt` tinyint(1) DEFAULT NULL,
  `expensecategory` int(11) DEFAULT NULL,
  `invoice` tinyint(1) DEFAULT NULL,
  `photoext` varchar(5) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `extract`
--

CREATE TABLE `extract` (
  `extractid` int(11) NOT NULL,
  `registeredSince` datetime DEFAULT NULL,
  `extracttype` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(1000) NOT NULL DEFAULT '',
  `medicaldescription` varchar(1000) NOT NULL DEFAULT '',
  `extract` varchar(100) DEFAULT NULL,
  `THC` decimal(15,2) DEFAULT NULL,
  `CBD` decimal(15,2) DEFAULT NULL,
  `CBN` decimal(15,2) DEFAULT NULL,
  `extractnumber` int(11) NOT NULL DEFAULT '0',
  `sativaPercentage` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `flower`
--

CREATE TABLE `flower` (
  `flowerid` int(11) NOT NULL,
  `flowertype` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(1000) NOT NULL DEFAULT '',
  `medicaldescription` varchar(1000) NOT NULL DEFAULT '',
  `breed2` varchar(100) DEFAULT NULL,
  `sativaPercentage` decimal(15,2) DEFAULT NULL,
  `THC` decimal(15,2) DEFAULT NULL,
  `CBD` decimal(15,2) DEFAULT NULL,
  `CBN` decimal(15,2) DEFAULT NULL,
  `registeredSince` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `flowernumber` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_b_productmovements`
--

CREATE TABLE `f_b_productmovements` (
  `movementid` int(11) NOT NULL,
  `movementtime` datetime DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `purchaseid` int(11) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `movementTypeid` int(11) DEFAULT NULL,
  `comment` varchar(1000) DEFAULT NULL,
  `doneAtRegistration` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_b_sales`
--

CREATE TABLE `f_b_sales` (
  `saleid` int(11) NOT NULL,
  `saletime` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `unitsTot` decimal(15,2) NOT NULL DEFAULT '0.00',
  `adminComment` varchar(1000) DEFAULT NULL,
  `creditBefore` decimal(15,2) DEFAULT NULL,
  `creditAfter` decimal(15,2) DEFAULT NULL,
  `direct` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_b_salesdetails`
--

CREATE TABLE `f_b_salesdetails` (
  `salesdetailsid` int(11) NOT NULL,
  `saleid` int(11) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `purchaseid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_donations`
--

CREATE TABLE `f_donations` (
  `donationid` int(11) NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `donationTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comment` varchar(1000) DEFAULT NULL,
  `creditBefore` decimal(15,2) DEFAULT NULL,
  `creditAfter` decimal(15,2) DEFAULT NULL,
  `donatedTo` int(11) NOT NULL DEFAULT '0',
  `operator` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_log`
--

CREATE TABLE `f_log` (
  `id` int(11) NOT NULL,
  `logtype` int(11) NOT NULL DEFAULT '0',
  `logtime` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `operator` int(11) NOT NULL DEFAULT '0',
  `oldExpiry` varchar(20) DEFAULT NULL,
  `newExpiry` varchar(20) DEFAULT NULL,
  `oldCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `newCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comment` varchar(1000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_memberpayments`
--

CREATE TABLE `f_memberpayments` (
  `paymentid` int(11) NOT NULL,
  `paymentdate` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `amountPaid` decimal(15,2) DEFAULT NULL,
  `oldExpiry` datetime DEFAULT NULL,
  `newExpiry` datetime DEFAULT NULL,
  `paidTo` int(11) NOT NULL DEFAULT '0',
  `comment` varchar(1000) NOT NULL DEFAULT '',
  `completed` int(11) NOT NULL DEFAULT '0',
  `operator` int(11) NOT NULL DEFAULT '0',
  `creditBefore` decimal(15,2) NOT NULL DEFAULT '0.00',
  `creditAfter` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_productmovements`
--

CREATE TABLE `f_productmovements` (
  `movementid` int(11) NOT NULL,
  `movementtime` datetime DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `purchaseid` int(11) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `movementTypeid` int(11) DEFAULT NULL,
  `comment` varchar(1000) DEFAULT NULL,
  `doneAtRegistration` tinyint(1) NOT NULL DEFAULT '0',
  `noOfBags` int(11) NOT NULL DEFAULT '0',
  `provider` int(11) NOT NULL DEFAULT '0',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `paid` decimal(15,2) NOT NULL DEFAULT '0.00',
  `destination` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_sales`
--

CREATE TABLE `f_sales` (
  `saleid` int(11) NOT NULL,
  `saletime` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `amountpaid` decimal(15,2) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `units` decimal(15,2) NOT NULL DEFAULT '0.00',
  `adminComment` varchar(1000) DEFAULT NULL,
  `settled` datetime DEFAULT NULL,
  `settledTo` int(11) DEFAULT NULL,
  `dispensedFrom` int(11) DEFAULT NULL,
  `creditBefore` decimal(15,2) DEFAULT NULL,
  `creditAfter` decimal(15,2) DEFAULT NULL,
  `expiry` datetime DEFAULT NULL,
  `realQuantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `direct` int(11) DEFAULT NULL,
  `puesto` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `f_salesdetails`
--

CREATE TABLE `f_salesdetails` (
  `salesdetailsid` int(11) NOT NULL,
  `saleid` int(11) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `purchaseid` int(11) DEFAULT NULL,
  `e5bags` int(11) NOT NULL DEFAULT '0',
  `e10bags` int(11) NOT NULL DEFAULT '0',
  `realQuantity` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `global_happy_hour_discounts`
--

CREATE TABLE `global_happy_hour_discounts` (
  `id` int(11) NOT NULL,
  `discount_name` varchar(255) DEFAULT NULL,
  `discount_date` varchar(20) NOT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `discount_bar` decimal(15,2) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `growtypes`
--

CREATE TABLE `growtypes` (
  `growtypeid` int(11) NOT NULL,
  `growtype` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `growtypes`
--

INSERT INTO `growtypes` (`growtypeid`, `growtype`, `description`) VALUES
(1, 'Exterior', NULL),
(2, 'Interior', NULL),
(3, 'Hydro', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inddiscounts`
--

CREATE TABLE `inddiscounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `purchaseid` int(11) NOT NULL DEFAULT '0',
  `discount` decimal(15,2) DEFAULT NULL,
  `fijo` decimal(15,2) DEFAULT NULL,
  `happy_hour_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lockers`
--

CREATE TABLE `lockers` (
  `movementid` int(11) NOT NULL,
  `movementtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comment` varchar(1000) NOT NULL DEFAULT '',
  `oldWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `newWeight` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `logtype` int(11) NOT NULL DEFAULT '0',
  `logtime` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `operator` int(11) NOT NULL DEFAULT '0',
  `oldExpiry` varchar(20) DEFAULT NULL,
  `newExpiry` varchar(20) DEFAULT NULL,
  `oldCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `newCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comment` varchar(1000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `logins`
--

CREATE TABLE `logins` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `success` int(11) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '',
  `comment` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `logtypes`
--

CREATE TABLE `logtypes` (
  `id` int(11) NOT NULL,
  `nameen` varchar(100) NOT NULL DEFAULT '',
  `descriptionen` varchar(1000) NOT NULL DEFAULT '',
  `namees` varchar(100) NOT NULL DEFAULT '',
  `descriptiones` varchar(1000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `logtypes`
--

INSERT INTO `logtypes` (`id`, `nameen`, `descriptionen`, `namees`, `descriptiones`) VALUES
(1, 'Deleted dispense', '', 'Borrado retirada', ''),
(2, 'Deleted donation', '', 'Borrado donación', ''),
(3, 'Deleted periodical fee payment', '', 'Borrado pago de cuota', ''),
(4, 'Deleted bar sale', '', 'Borrado venta del bar', ''),
(5, 'Changed credit', '', 'Cambiado saldo', ''),
(6, 'Registered donation', '', 'Registrado donación', ''),
(7, 'Registered a fee payment', '', 'Registrado pago de cuota', ''),
(8, 'Changed member expiry date', '', 'Cambiado fecha de vencimiento', ''),
(9, 'Edited donation', '', 'Editado donación', ''),
(10, 'Dispensed product(s)', '', 'Dispensado producto(s)', ''),
(11, 'Sold bar product(s)', '', 'Venta del bar', ''),
(12, 'New member registered', '', 'Socio nuevo registrado', '');

-- --------------------------------------------------------

--
-- Table structure for table `memberpaymentparts`
--

CREATE TABLE `memberpaymentparts` (
  `partid` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paymentid` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `userid` int(11) NOT NULL DEFAULT '0',
  `comment` varchar(400) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `memberpayments`
--

CREATE TABLE `memberpayments` (
  `paymentid` int(11) NOT NULL,
  `paymentdate` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `amountPaid` decimal(15,2) DEFAULT NULL,
  `oldExpiry` datetime DEFAULT NULL,
  `newExpiry` datetime DEFAULT NULL,
  `paidTo` int(11) NOT NULL DEFAULT '0',
  `comment` varchar(1000) NOT NULL DEFAULT '',
  `completed` int(11) NOT NULL DEFAULT '0',
  `operator` int(11) NOT NULL DEFAULT '0',
  `creditBefore` decimal(15,2) NOT NULL DEFAULT '0.00',
  `creditAfter` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `newscan`
--

CREATE TABLE `newscan` (
  `scanid` int(11) NOT NULL,
  `chip` varchar(20) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `newvisits`
--

CREATE TABLE `newvisits` (
  `visitNo` int(11) NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `scanin` datetime DEFAULT NULL,
  `scanout` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `duration` int(11) NOT NULL DEFAULT '0',
  `warning` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `url` varchar(500) NOT NULL DEFAULT '',
  `msgread` int(11) NOT NULL DEFAULT '0',
  `done` int(11) NOT NULL DEFAULT '0',
  `notification` varchar(1500) NOT NULL DEFAULT '',
  `notification_es` varchar(1500) NOT NULL DEFAULT '',
  `notification_ca` varchar(1500) NOT NULL DEFAULT '',
  `notification_nl` varchar(1500) NOT NULL DEFAULT '',
  `notification_it` varchar(1500) NOT NULL DEFAULT '',
  `notification_fr` varchar(1500) NOT NULL DEFAULT '',
  `category` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `opening`
--

CREATE TABLE `opening` (
  `openingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillBalance` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `owedPlusTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `openedby` int(11) DEFAULT NULL,
  `prodStock` decimal(15,2) DEFAULT NULL,
  `stockDeltaExtract` decimal(15,2) DEFAULT NULL,
  `prodStockFlower` decimal(15,2) DEFAULT NULL,
  `prodStockExtract` decimal(15,2) DEFAULT NULL,
  `stockDeltaFlower` decimal(15,2) DEFAULT NULL,
  `shiftClosed` int(11) DEFAULT NULL,
  `dayClosed` int(11) DEFAULT NULL,
  `recClosed` int(11) DEFAULT NULL,
  `disClosed` int(11) DEFAULT NULL,
  `recClosedBy` int(11) DEFAULT NULL,
  `disClosedBy` int(11) DEFAULT NULL,
  `dayClosedNo` int(11) DEFAULT NULL,
  `dayClosedBy` int(11) DEFAULT NULL,
  `recShiftClosed` int(11) DEFAULT NULL,
  `disShiftClosed` int(11) DEFAULT NULL,
  `recShiftClosedBy` int(11) DEFAULT NULL,
  `disShiftClosedBy` int(11) DEFAULT NULL,
  `shiftClosedNo` int(11) DEFAULT NULL,
  `firstDisOpen` int(11) DEFAULT NULL,
  `firstDisOpenBy` int(11) DEFAULT NULL,
  `firstRecOpen` int(11) DEFAULT NULL,
  `firstRecOpenBy` int(11) DEFAULT NULL,
  `firstDayOpen` int(11) DEFAULT NULL,
  `firstDayOpenBy` int(11) DEFAULT NULL,
  `recClosedAt` datetime DEFAULT NULL,
  `disClosedAt` datetime DEFAULT NULL,
  `recShiftClosedAt` datetime DEFAULT NULL,
  `disShiftClosedAt` datetime DEFAULT NULL,
  `firstRecOpenAt` datetime DEFAULT NULL,
  `firstDisOpenAt` datetime DEFAULT NULL,
  `shiftClosedBy` int(11) DEFAULT NULL,
  `catShiftClosed` int(11) NOT NULL DEFAULT '0',
  `catShiftClosedBy` int(11) NOT NULL DEFAULT '0',
  `catShiftClosedAt` datetime DEFAULT NULL,
  `firstDis2Open` int(11) DEFAULT NULL,
  `firstDis2OpenBy` int(11) DEFAULT NULL,
  `firstBarOpen` int(11) DEFAULT NULL,
  `firstBarOpenBy` int(11) DEFAULT NULL,
  `firstDis2OpenAt` datetime DEFAULT NULL,
  `firstBarOpenAt` datetime DEFAULT NULL,
  `dis2Closed` int(11) DEFAULT NULL,
  `dis2ClosedBy` int(11) DEFAULT NULL,
  `dis2ClosedAt` datetime DEFAULT NULL,
  `barClosed` int(11) DEFAULT NULL,
  `barClosedBy` int(11) DEFAULT NULL,
  `barClosedAt` datetime DEFAULT NULL,
  `dis2ShiftClosed` int(11) DEFAULT NULL,
  `dis2ShiftClosedBy` int(11) DEFAULT NULL,
  `dis2ShiftClosedAt` datetime DEFAULT NULL,
  `barShiftClosed` int(11) DEFAULT NULL,
  `barShiftClosedBy` int(11) DEFAULT NULL,
  `barShiftClosedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `openingdetails`
--

CREATE TABLE `openingdetails` (
  `openingdetailsid` int(11) NOT NULL,
  `openingid` int(11) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `categoryType` int(11) NOT NULL DEFAULT '0',
  `productid` int(11) DEFAULT NULL,
  `purchaseid` int(11) DEFAULT NULL,
  `weight` decimal(15,2) DEFAULT NULL,
  `prodOpenComment` varchar(1000) DEFAULT NULL,
  `weightDelta` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `openingother`
--

CREATE TABLE `openingother` (
  `id` int(11) NOT NULL,
  `openingid` int(11) DEFAULT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `categoryType` int(11) NOT NULL DEFAULT '0',
  `prodStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stockDelta` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `productmovements`
--

CREATE TABLE `productmovements` (
  `movementid` int(11) NOT NULL,
  `movementtime` datetime DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `purchaseid` int(11) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `movementTypeid` int(11) DEFAULT NULL,
  `comment` varchar(1000) DEFAULT NULL,
  `doneAtRegistration` tinyint(1) NOT NULL DEFAULT '0',
  `noOfBags` int(11) NOT NULL DEFAULT '0',
  `provider` int(11) NOT NULL DEFAULT '0',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `paid` decimal(15,2) NOT NULL DEFAULT '0.00',
  `destination` varchar(15) NOT NULL DEFAULT '',
  `realquantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `priceg` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stashMovementType` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `category` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `productmovementtypes`
--

CREATE TABLE `productmovementtypes` (
  `movementTypeid` int(11) NOT NULL,
  `type` int(11) DEFAULT NULL,
  `movementNameen` varchar(100) DEFAULT NULL,
  `movementNamees` varchar(100) DEFAULT NULL,
  `movementDesc` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `productmovementtypes`
--

INSERT INTO `productmovementtypes` (`movementTypeid`, `type`, `movementNameen`, `movementNamees`, `movementDesc`) VALUES
(1, 1, 'Reload', 'Recarga', NULL),
(2, 1, 'From external stash', 'Desde almacen externo', NULL),
(3, 1, 'From display jar', 'Desde jarra de muestra', NULL),
(4, 2, 'Rewards', 'Premios', NULL),
(5, 2, 'Stashed internally', 'Almacenado internamente', NULL),
(6, 2, 'Stashed externally', 'Almacenado externamente', NULL),
(7, 2, 'Refining (eg. extracts)', 'Refenimiento (ed. extractos)', NULL),
(8, 2, 'Sample taste', 'Una muestra para probar', NULL),
(9, 2, 'Display jar', 'Jarra de muestra', NULL),
(10, 1, 'Other (specify in comments)', 'Otro (especifica en comentarios)', NULL),
(11, 2, 'Other (specify in comments)', 'Otro (especifica en comentarios)', NULL),
(12, 1, 'From internal stash', 'Desde almacen interno', NULL),
(13, 2, 'Manicure', 'Manicura', NULL),
(14, 2, 'Shake', 'Restos canabicos', NULL),
(15, 2, 'Pre-rolls', 'Porros pre-liados', 'Pre-rolled blunts, joints etc.'),
(16, 2, 'Sticks & stems', 'Palillos y tallos', NULL),
(17, 1, 'User reset of internal stash', 'Usuario a borrado el almacen interno', NULL),
(18, 2, 'User reset of internal stash', 'Usuario a borrado el almacen interno', NULL),
(19, 1, 'User reset of external stash', 'Usuario a borrado el almacen externo', NULL),
(20, 2, 'User reset of external stash', 'Usuario a borrado el almacen externo', NULL),
(21, 2, 'Add to Shake', 'Añadir a Grifo', NULL),
(22, 1, 'Shake added', 'Restos añadido', NULL),
(23, 1, '5&euro; bag', 'Bolsa 5&euro;', NULL),
(24, 1, '10&euro; bag', 'Bolsa 10&euro;', NULL),
(25, 2, '5&euro; bag', 'Bolsa 5&euro;', NULL),
(26, 2, '10&euro; bag', 'Bolsa 10&euro;', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productid` int(11) NOT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `registeredSince` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(1000) NOT NULL DEFAULT '',
  `medicaldescription` varchar(1000) NOT NULL DEFAULT '',
  `productnumber` int(11) NOT NULL DEFAULT '0',
  `flowertype` varchar(100) NOT NULL DEFAULT '',
  `breed2` varchar(100) NOT NULL DEFAULT '',
  `sativaPercentage` decimal(15,2) NOT NULL DEFAULT '0.00',
  `THC` decimal(15,2) NOT NULL DEFAULT '0.00',
  `CBD` decimal(15,2) NOT NULL DEFAULT '0.00',
  `CBN` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `providerpayments`
--

CREATE TABLE `providerpayments` (
  `paymentid` int(11) NOT NULL,
  `providerid` int(11) NOT NULL DEFAULT '0',
  `paymentTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comment` varchar(1000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `id` int(11) NOT NULL,
  `registered` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL DEFAULT '',
  `comment` varchar(1000) NOT NULL DEFAULT '',
  `providernumber` int(11) NOT NULL DEFAULT '0',
  `credit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `enlisted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchaseid` int(11) NOT NULL,
  `category` int(11) DEFAULT NULL,
  `batchno` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `purchaseDate` datetime DEFAULT NULL,
  `purchasePrice` decimal(15,2) DEFAULT NULL,
  `salesPrice` decimal(15,2) DEFAULT NULL,
  `salesPrice2` decimal(15,2) NOT NULL DEFAULT '0.00',
  `purchaseQuantity` decimal(15,2) DEFAULT NULL,
  `realQuantity` decimal(15,2) DEFAULT NULL,
  `adminComment` varchar(1000) DEFAULT NULL,
  `estClosing` decimal(15,2) DEFAULT NULL,
  `closingComment` varchar(1000) DEFAULT NULL,
  `closedAt` decimal(15,2) DEFAULT NULL,
  `closedSales` decimal(15,2) DEFAULT NULL,
  `closedReloads` decimal(15,2) DEFAULT NULL,
  `closedTakeouts` decimal(15,2) DEFAULT NULL,
  `closedAdditions` decimal(15,2) DEFAULT NULL,
  `closingDate` datetime DEFAULT NULL,
  `growType` int(11) NOT NULL DEFAULT '0',
  `inMenu` tinyint(1) NOT NULL DEFAULT '0',
  `photoExt` varchar(4) NOT NULL DEFAULT '',
  `tupperWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `workerDiscount` int(11) NOT NULL DEFAULT '0',
  `medicalDiscount` int(11) NOT NULL DEFAULT '0',
  `provider` int(11) NOT NULL DEFAULT '0',
  `paid` decimal(15,2) NOT NULL DEFAULT '0.00',
  `medDiscount` int(11) NOT NULL DEFAULT '0',
  `barCode` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `recclosing`
--

CREATE TABLE `recclosing` (
  `closingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `closingtime` datetime DEFAULT NULL,
  `shiftEnd` datetime DEFAULT NULL,
  `quantitySold` decimal(15,2) DEFAULT NULL,
  `soldtoday` decimal(15,2) DEFAULT NULL,
  `unitsSold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notpaidtoday` decimal(15,2) DEFAULT NULL,
  `closingbalance` decimal(15,2) DEFAULT NULL,
  `moneytaken` decimal(15,2) DEFAULT NULL,
  `takenduringday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cashintill` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `newmembers` int(11) DEFAULT NULL,
  `closedby` int(11) DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `expenses` decimal(15,2) DEFAULT NULL,
  `membershipFees` decimal(15,2) DEFAULT NULL,
  `estimatedTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `bankExpenses` decimal(15,2) DEFAULT NULL,
  `debtRepaid` decimal(15,2) DEFAULT NULL,
  `debtRepaidBank` decimal(15,2) DEFAULT NULL,
  `prodOpening` decimal(15,2) DEFAULT NULL,
  `prodAdded` decimal(15,2) DEFAULT NULL,
  `prodRemoved` decimal(15,2) DEFAULT NULL,
  `prodEstStock` decimal(15,2) DEFAULT NULL,
  `prodStock` decimal(15,2) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `prodStockFlower` decimal(15,2) DEFAULT NULL,
  `prodStockExtract` decimal(15,2) DEFAULT NULL,
  `income` decimal(15,2) DEFAULT NULL,
  `paraphernalia` decimal(15,2) DEFAULT NULL,
  `biscuits` decimal(15,2) DEFAULT NULL,
  `drinksandsnacks` decimal(15,2) DEFAULT NULL,
  `prerolls` decimal(15,2) DEFAULT NULL,
  `otherAdditions` decimal(15,2) DEFAULT NULL,
  `prodOpeningFlower` decimal(15,2) DEFAULT NULL,
  `prodOpeningExtract` decimal(15,2) DEFAULT NULL,
  `prodAddedFlower` decimal(15,2) DEFAULT NULL,
  `prodAddedExtract` decimal(15,2) DEFAULT NULL,
  `prodRemovedFlower` decimal(15,2) DEFAULT NULL,
  `prodRemovedExtract` decimal(15,2) DEFAULT NULL,
  `prodEstStockFlower` decimal(15,2) DEFAULT NULL,
  `prodEstStockExtract` decimal(15,2) DEFAULT NULL,
  `stockDeltaFlower` decimal(15,2) DEFAULT NULL,
  `stockDeltaExtract` decimal(15,2) DEFAULT NULL,
  `donations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bankDonations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `renewedMembers` int(11) NOT NULL DEFAULT '0',
  `bannedMembers` int(11) NOT NULL DEFAULT '0',
  `deletedMembers` int(11) NOT NULL DEFAULT '0',
  `expiredMembers` int(11) NOT NULL DEFAULT '0',
  `totalMembers` int(11) NOT NULL DEFAULT '0',
  `activeMembers` int(11) NOT NULL DEFAULT '0',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerweightNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extracttotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftNumberClosed` int(11) NOT NULL DEFAULT '0',
  `shiftTypeClosed` int(11) NOT NULL DEFAULT '0',
  `membershipfeesBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldtodayBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSoldBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalanceBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dayClosed` int(11) NOT NULL DEFAULT '0',
  `dayClosedBy` int(11) NOT NULL DEFAULT '0',
  `recClosed` int(11) NOT NULL DEFAULT '0',
  `recClosedBy` int(11) NOT NULL DEFAULT '0',
  `disClosed` int(11) NOT NULL DEFAULT '0',
  `disClosedBy` int(11) NOT NULL DEFAULT '0',
  `dayClosedNo` int(11) NOT NULL DEFAULT '0',
  `currentClosing` int(11) NOT NULL DEFAULT '0',
  `totCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dayOpened` int(11) NOT NULL DEFAULT '0',
  `recOpened` int(11) NOT NULL DEFAULT '0',
  `disOpened` int(11) NOT NULL DEFAULT '0',
  `recOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disOpenedBy` int(11) NOT NULL DEFAULT '0',
  `dayOpenedNo` int(11) NOT NULL DEFAULT '0',
  `dayOpenedBy` int(11) NOT NULL DEFAULT '0',
  `recShiftOpened` int(11) NOT NULL DEFAULT '0',
  `disShiftOpened` int(11) NOT NULL DEFAULT '0',
  `recShiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disShiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `recOpenedAt` datetime DEFAULT NULL,
  `disOpenedAt` datetime DEFAULT NULL,
  `recShiftOpenedAt` datetime DEFAULT NULL,
  `disShiftOpenedAt` datetime DEFAULT NULL,
  `quantitySoldReal` decimal(15,2) DEFAULT NULL,
  `soldTodayFlowerReal` decimal(15,2) DEFAULT '0.00',
  `soldTodayExtractReal` decimal(15,2) DEFAULT '0.00',
  `dis2Opened` int(11) DEFAULT NULL,
  `dis2OpenedBy` int(11) DEFAULT NULL,
  `dis2OpenedAt` datetime DEFAULT NULL,
  `barOpened` int(11) DEFAULT NULL,
  `barOpenedBy` int(11) DEFAULT NULL,
  `barOpenedAt` datetime DEFAULT NULL,
  `dis2Closed` int(11) DEFAULT NULL,
  `dis2ClosedBy` int(11) DEFAULT NULL,
  `dis2ClosedAt` datetime DEFAULT NULL,
  `barClosed` int(11) DEFAULT NULL,
  `barClosedBy` int(11) DEFAULT NULL,
  `barClosedAt` datetime DEFAULT NULL,
  `directDispensedCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directDispensedBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directBarCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directBarBank` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `recopening`
--

CREATE TABLE `recopening` (
  `openingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillBalance` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `openedby` int(11) DEFAULT NULL,
  `shiftClosed` int(11) DEFAULT NULL,
  `shiftClosedNo` int(11) DEFAULT NULL,
  `shiftClosedBy` int(11) DEFAULT NULL,
  `dayClosed` int(11) DEFAULT NULL,
  `dayClosedNo` int(11) DEFAULT NULL,
  `dayClosedBy` int(11) DEFAULT NULL,
  `firstDayOpen` int(11) DEFAULT NULL,
  `firstDayOpenBy` int(11) DEFAULT NULL,
  `shiftClosedAt` datetime DEFAULT NULL,
  `dayClosedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `recshiftclose`
--

CREATE TABLE `recshiftclose` (
  `closingid` int(11) NOT NULL,
  `closingtime` datetime DEFAULT NULL,
  `shiftStart` datetime DEFAULT NULL,
  `shiftEnd` datetime DEFAULT NULL,
  `quantitySold` decimal(15,2) DEFAULT NULL,
  `soldtoday` decimal(15,2) DEFAULT NULL,
  `notpaidtoday` decimal(15,2) DEFAULT NULL,
  `closingbalance` decimal(15,2) DEFAULT NULL,
  `moneytaken` decimal(15,2) DEFAULT NULL,
  `cashintill` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `newmembers` int(11) DEFAULT NULL,
  `closedby` int(11) DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `expenses` decimal(15,2) DEFAULT NULL,
  `membershipFees` decimal(15,2) DEFAULT NULL,
  `estimatedTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `bankExpenses` decimal(15,2) DEFAULT NULL,
  `debtRepaid` decimal(15,2) DEFAULT NULL,
  `debtRepaidBank` decimal(15,2) DEFAULT NULL,
  `prodOpening` decimal(15,2) DEFAULT NULL,
  `prodAdded` decimal(15,2) DEFAULT NULL,
  `prodRemoved` decimal(15,2) DEFAULT NULL,
  `prodEstStock` decimal(15,2) DEFAULT NULL,
  `prodStock` decimal(15,2) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `prodStockFlower` decimal(15,2) DEFAULT NULL,
  `prodStockExtract` decimal(15,2) DEFAULT NULL,
  `income` decimal(15,2) DEFAULT NULL,
  `paraphernalia` decimal(15,2) DEFAULT NULL,
  `biscuits` decimal(15,2) DEFAULT NULL,
  `drinksandsnacks` decimal(15,2) DEFAULT NULL,
  `prerolls` decimal(15,2) DEFAULT NULL,
  `otherAdditions` decimal(15,2) DEFAULT NULL,
  `prodOpeningFlower` decimal(15,2) DEFAULT NULL,
  `prodOpeningExtract` decimal(15,2) DEFAULT NULL,
  `prodAddedFlower` decimal(15,2) DEFAULT NULL,
  `prodAddedExtract` decimal(15,2) DEFAULT NULL,
  `prodRemovedFlower` decimal(15,2) DEFAULT NULL,
  `prodRemovedExtract` decimal(15,2) DEFAULT NULL,
  `prodEstStockFlower` decimal(15,2) DEFAULT NULL,
  `prodEstStockExtract` decimal(15,2) DEFAULT NULL,
  `stockDeltaFlower` decimal(15,2) DEFAULT NULL,
  `stockDeltaExtract` decimal(15,2) DEFAULT NULL,
  `donations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bankDonations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `renewedMembers` int(11) NOT NULL DEFAULT '0',
  `bannedMembers` int(11) NOT NULL DEFAULT '0',
  `deletedMembers` int(11) NOT NULL DEFAULT '0',
  `expiredMembers` int(11) NOT NULL DEFAULT '0',
  `totalMembers` int(11) NOT NULL DEFAULT '0',
  `activeMembers` int(11) NOT NULL DEFAULT '0',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerweightNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extracttotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `membershipfeesBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalanceBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldtodayBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `takenduringday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSoldBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftOpened` int(11) NOT NULL DEFAULT '0',
  `recOpened` int(11) NOT NULL DEFAULT '0',
  `disOpened` int(11) NOT NULL DEFAULT '0',
  `shiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `recOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disOpenedBy` int(11) NOT NULL DEFAULT '0',
  `shiftOpenedNo` int(11) NOT NULL DEFAULT '0',
  `recOpenedAt` datetime DEFAULT NULL,
  `disOpenedAt` datetime DEFAULT NULL,
  `quantitySoldReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlowerReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtractReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dis2ShiftOpened` int(11) DEFAULT NULL,
  `dis2ShiftOpenedBy` int(11) DEFAULT NULL,
  `dis2ShiftOpenedAt` datetime DEFAULT NULL,
  `barShiftOpened` int(11) DEFAULT NULL,
  `barShiftOpenedBy` int(11) DEFAULT NULL,
  `barShiftOpenedAt` datetime DEFAULT NULL,
  `dis2Opened` int(11) DEFAULT NULL,
  `dis2OpenedBy` int(11) DEFAULT NULL,
  `dis2OpenedAt` datetime DEFAULT NULL,
  `barOpened` int(11) DEFAULT NULL,
  `barOpenedBy` int(11) DEFAULT NULL,
  `barOpenedAt` datetime DEFAULT NULL,
  `directDispensedCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directDispensedBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directBarCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directBarBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftOpenedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `recshiftopen`
--

CREATE TABLE `recshiftopen` (
  `openingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillBalance` decimal(15,2) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `owedPlusTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `openedby` int(11) NOT NULL DEFAULT '0',
  `prodStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodStockFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodStockExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stockDeltaFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stockDeltaExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `closed` int(11) NOT NULL DEFAULT '0',
  `recClosed` int(11) NOT NULL DEFAULT '0',
  `disClosed` int(11) NOT NULL DEFAULT '0',
  `recClosedBy` int(11) NOT NULL DEFAULT '0',
  `disClosedBy` int(11) NOT NULL DEFAULT '0',
  `shiftClosedNo` int(11) NOT NULL DEFAULT '0',
  `shiftClosedBy` int(11) NOT NULL DEFAULT '0',
  `bankBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftClosed` int(11) NOT NULL DEFAULT '0',
  `recClosedAt` datetime DEFAULT NULL,
  `disClosedAt` datetime DEFAULT NULL,
  `catClosed` int(11) NOT NULL DEFAULT '0',
  `catClosedBy` int(11) NOT NULL DEFAULT '0',
  `catClosedAt` datetime DEFAULT NULL,
  `dis2Closed` int(11) DEFAULT NULL,
  `dis2ClosedBy` int(11) DEFAULT NULL,
  `dis2ClosedAt` datetime DEFAULT NULL,
  `shiftClosedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rejected`
--

CREATE TABLE `rejected` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `dni` varchar(50) NOT NULL DEFAULT '',
  `reason` varchar(5000) NOT NULL DEFAULT '',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `saleid` int(11) NOT NULL,
  `operatorid` int(11) NOT NULL DEFAULT '0',
  `saletime` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `amountpaid` decimal(15,2) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `units` decimal(15,2) NOT NULL DEFAULT '0.00',
  `adminComment` varchar(1000) DEFAULT NULL,
  `settled` datetime DEFAULT NULL,
  `settledTo` int(11) DEFAULT NULL,
  `dispensedFrom` int(11) DEFAULT NULL,
  `creditBefore` decimal(15,2) DEFAULT NULL,
  `creditAfter` decimal(15,2) DEFAULT NULL,
  `expiry` datetime DEFAULT NULL,
  `realQuantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `direct` int(11) DEFAULT NULL,
  `puesto` varchar(20) NOT NULL DEFAULT '',
  `discounteur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `fulfilled` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `salesdetails`
--

CREATE TABLE `salesdetails` (
  `salesdetailsid` int(11) NOT NULL,
  `saleid` int(11) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `purchaseid` int(11) DEFAULT NULL,
  `e5bags` int(11) NOT NULL DEFAULT '0',
  `e10bags` int(11) NOT NULL DEFAULT '0',
  `realQuantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discountType` int(11) NOT NULL DEFAULT '0',
  `discountPercentage` decimal(15,2) NOT NULL DEFAULT '0.00',
  `happyhourDiscount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `volumeDiscount` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sales_discount`
--

CREATE TABLE `sales_discount` (
  `id` int(11) NOT NULL,
  `salesId` int(11) NOT NULL,
  `discountType` int(11) NOT NULL,
  `discountPercentage` decimal(15,2) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `saveDispense_summary`
--

CREATE TABLE `saveDispense_summary` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gramsTOT` decimal(15,2) NOT NULL,
  `realgramsTOT` decimal(15,2) NOT NULL,
  `unitsTOT` decimal(15,2) NOT NULL,
  `credit` decimal(15,2) NOT NULL,
  `newcredit` decimal(15,2) NOT NULL,
  `eurcalcTOT` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `savesales_details`
--

CREATE TABLE `savesales_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `grams` decimal(15,2) NOT NULL,
  `euro` decimal(15,2) NOT NULL,
  `realGrams` decimal(15,2) NOT NULL,
  `grams2` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `scanhistory`
--

CREATE TABLE `scanhistory` (
  `scanid` int(11) NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `cardid` varchar(20) DEFAULT NULL,
  `scanTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `scanType` int(11) NOT NULL DEFAULT '0',
  `scanDesc` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `secclosing`
--

CREATE TABLE `secclosing` (
  `closingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `closingtime` datetime DEFAULT NULL,
  `shiftEnd` datetime DEFAULT NULL,
  `quantitySold` decimal(15,2) DEFAULT NULL,
  `soldtoday` decimal(15,2) DEFAULT NULL,
  `unitsSold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notpaidtoday` decimal(15,2) DEFAULT NULL,
  `closingbalance` decimal(15,2) DEFAULT NULL,
  `moneytaken` decimal(15,2) DEFAULT NULL,
  `takenduringday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cashintill` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `newmembers` int(11) DEFAULT NULL,
  `closedby` int(11) DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `expenses` decimal(15,2) DEFAULT NULL,
  `membershipFees` decimal(15,2) DEFAULT NULL,
  `estimatedTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `bankExpenses` decimal(15,2) DEFAULT NULL,
  `debtRepaid` decimal(15,2) DEFAULT NULL,
  `debtRepaidBank` decimal(15,2) DEFAULT NULL,
  `prodOpening` decimal(15,2) DEFAULT NULL,
  `prodAdded` decimal(15,2) DEFAULT NULL,
  `prodRemoved` decimal(15,2) DEFAULT NULL,
  `prodEstStock` decimal(15,2) DEFAULT NULL,
  `prodStock` decimal(15,2) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `prodStockFlower` decimal(15,2) DEFAULT NULL,
  `prodStockExtract` decimal(15,2) DEFAULT NULL,
  `income` decimal(15,2) DEFAULT NULL,
  `paraphernalia` decimal(15,2) DEFAULT NULL,
  `biscuits` decimal(15,2) DEFAULT NULL,
  `drinksandsnacks` decimal(15,2) DEFAULT NULL,
  `prerolls` decimal(15,2) DEFAULT NULL,
  `otherAdditions` decimal(15,2) DEFAULT NULL,
  `prodOpeningFlower` decimal(15,2) DEFAULT NULL,
  `prodOpeningExtract` decimal(15,2) DEFAULT NULL,
  `prodAddedFlower` decimal(15,2) DEFAULT NULL,
  `prodAddedExtract` decimal(15,2) DEFAULT NULL,
  `prodRemovedFlower` decimal(15,2) DEFAULT NULL,
  `prodRemovedExtract` decimal(15,2) DEFAULT NULL,
  `prodEstStockFlower` decimal(15,2) DEFAULT NULL,
  `prodEstStockExtract` decimal(15,2) DEFAULT NULL,
  `stockDeltaFlower` decimal(15,2) DEFAULT NULL,
  `stockDeltaExtract` decimal(15,2) DEFAULT NULL,
  `donations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bankDonations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `renewedMembers` int(11) NOT NULL DEFAULT '0',
  `bannedMembers` int(11) NOT NULL DEFAULT '0',
  `deletedMembers` int(11) NOT NULL DEFAULT '0',
  `expiredMembers` int(11) NOT NULL DEFAULT '0',
  `totalMembers` int(11) NOT NULL DEFAULT '0',
  `activeMembers` int(11) NOT NULL DEFAULT '0',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerweightNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extracttotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftNumberClosed` int(11) NOT NULL DEFAULT '0',
  `shiftTypeClosed` int(11) NOT NULL DEFAULT '0',
  `membershipfeesBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldtodayBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSoldBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalanceBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dayClosed` int(11) NOT NULL DEFAULT '0',
  `dayClosedBy` int(11) NOT NULL DEFAULT '0',
  `secClosed` int(11) NOT NULL DEFAULT '0',
  `secClosedBy` int(11) NOT NULL DEFAULT '0',
  `disClosed` int(11) NOT NULL DEFAULT '0',
  `disClosedBy` int(11) NOT NULL DEFAULT '0',
  `dayClosedNo` int(11) NOT NULL DEFAULT '0',
  `currentClosing` int(11) NOT NULL DEFAULT '0',
  `totCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dayOpened` int(11) NOT NULL DEFAULT '0',
  `secOpened` int(11) NOT NULL DEFAULT '0',
  `disOpened` int(11) NOT NULL DEFAULT '0',
  `secOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disOpenedBy` int(11) NOT NULL DEFAULT '0',
  `dayOpenedNo` int(11) NOT NULL DEFAULT '0',
  `dayOpenedBy` int(11) NOT NULL DEFAULT '0',
  `secShiftOpened` int(11) NOT NULL DEFAULT '0',
  `disShiftOpened` int(11) NOT NULL DEFAULT '0',
  `secShiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disShiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `secOpenedAt` datetime DEFAULT NULL,
  `disOpenedAt` datetime DEFAULT NULL,
  `secShiftOpenedAt` datetime DEFAULT NULL,
  `disShiftOpenedAt` datetime DEFAULT NULL,
  `quantitySoldReal` decimal(15,2) DEFAULT NULL,
  `soldTodayFlowerReal` decimal(15,2) DEFAULT '0.00',
  `soldTodayExtractReal` decimal(15,2) DEFAULT '0.00',
  `dis2Opened` int(11) DEFAULT NULL,
  `dis2OpenedBy` int(11) DEFAULT NULL,
  `dis2OpenedAt` datetime DEFAULT NULL,
  `barOpened` int(11) DEFAULT NULL,
  `barOpenedBy` int(11) DEFAULT NULL,
  `barOpenedAt` datetime DEFAULT NULL,
  `dis2Closed` int(11) DEFAULT NULL,
  `dis2ClosedBy` int(11) DEFAULT NULL,
  `dis2ClosedAt` datetime DEFAULT NULL,
  `barClosed` int(11) DEFAULT NULL,
  `barClosedBy` int(11) DEFAULT NULL,
  `barClosedAt` datetime DEFAULT NULL,
  `disectDispensedCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `disectDispensedBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `disectBarCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `disectBarBank` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `secopening`
--

CREATE TABLE `secopening` (
  `openingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillBalance` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `openedby` int(11) DEFAULT NULL,
  `shiftClosed` int(11) DEFAULT NULL,
  `shiftClosedNo` int(11) DEFAULT NULL,
  `shiftClosedBy` int(11) DEFAULT NULL,
  `dayClosed` int(11) DEFAULT NULL,
  `dayClosedNo` int(11) DEFAULT NULL,
  `dayClosedBy` int(11) DEFAULT NULL,
  `firstDayOpen` int(11) DEFAULT NULL,
  `firstDayOpenBy` int(11) DEFAULT NULL,
  `shiftClosedAt` datetime DEFAULT NULL,
  `dayClosedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `secshiftclose`
--

CREATE TABLE `secshiftclose` (
  `closingid` int(11) NOT NULL,
  `closingtime` datetime DEFAULT NULL,
  `shiftStart` datetime DEFAULT NULL,
  `shiftEnd` datetime DEFAULT NULL,
  `quantitySold` decimal(15,2) DEFAULT NULL,
  `soldtoday` decimal(15,2) DEFAULT NULL,
  `notpaidtoday` decimal(15,2) DEFAULT NULL,
  `closingbalance` decimal(15,2) DEFAULT NULL,
  `moneytaken` decimal(15,2) DEFAULT NULL,
  `cashintill` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `newmembers` int(11) DEFAULT NULL,
  `closedby` int(11) DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `expenses` decimal(15,2) DEFAULT NULL,
  `membershipFees` decimal(15,2) DEFAULT NULL,
  `estimatedTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `bankExpenses` decimal(15,2) DEFAULT NULL,
  `debtRepaid` decimal(15,2) DEFAULT NULL,
  `debtRepaidBank` decimal(15,2) DEFAULT NULL,
  `prodOpening` decimal(15,2) DEFAULT NULL,
  `prodAdded` decimal(15,2) DEFAULT NULL,
  `prodRemoved` decimal(15,2) DEFAULT NULL,
  `prodEstStock` decimal(15,2) DEFAULT NULL,
  `prodStock` decimal(15,2) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `prodStockFlower` decimal(15,2) DEFAULT NULL,
  `prodStockExtract` decimal(15,2) DEFAULT NULL,
  `income` decimal(15,2) DEFAULT NULL,
  `paraphernalia` decimal(15,2) DEFAULT NULL,
  `biscuits` decimal(15,2) DEFAULT NULL,
  `drinksandsnacks` decimal(15,2) DEFAULT NULL,
  `prerolls` decimal(15,2) DEFAULT NULL,
  `otherAdditions` decimal(15,2) DEFAULT NULL,
  `prodOpeningFlower` decimal(15,2) DEFAULT NULL,
  `prodOpeningExtract` decimal(15,2) DEFAULT NULL,
  `prodAddedFlower` decimal(15,2) DEFAULT NULL,
  `prodAddedExtract` decimal(15,2) DEFAULT NULL,
  `prodRemovedFlower` decimal(15,2) DEFAULT NULL,
  `prodRemovedExtract` decimal(15,2) DEFAULT NULL,
  `prodEstStockFlower` decimal(15,2) DEFAULT NULL,
  `prodEstStockExtract` decimal(15,2) DEFAULT NULL,
  `stockDeltaFlower` decimal(15,2) DEFAULT NULL,
  `stockDeltaExtract` decimal(15,2) DEFAULT NULL,
  `donations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bankDonations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `renewedMembers` int(11) NOT NULL DEFAULT '0',
  `bannedMembers` int(11) NOT NULL DEFAULT '0',
  `deletedMembers` int(11) NOT NULL DEFAULT '0',
  `expiredMembers` int(11) NOT NULL DEFAULT '0',
  `totalMembers` int(11) NOT NULL DEFAULT '0',
  `activeMembers` int(11) NOT NULL DEFAULT '0',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerweightNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extracttotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `membershipfeesBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalanceBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldtodayBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `takenduringday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSoldBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftOpened` int(11) NOT NULL DEFAULT '0',
  `secOpened` int(11) NOT NULL DEFAULT '0',
  `disOpened` int(11) NOT NULL DEFAULT '0',
  `shiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `secOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disOpenedBy` int(11) NOT NULL DEFAULT '0',
  `shiftOpenedNo` int(11) NOT NULL DEFAULT '0',
  `secOpenedAt` datetime DEFAULT NULL,
  `disOpenedAt` datetime DEFAULT NULL,
  `quantitySoldReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlowerReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtractReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dis2ShiftOpened` int(11) DEFAULT NULL,
  `dis2ShiftOpenedBy` int(11) DEFAULT NULL,
  `dis2ShiftOpenedAt` datetime DEFAULT NULL,
  `barShiftOpened` int(11) DEFAULT NULL,
  `barShiftOpenedBy` int(11) DEFAULT NULL,
  `barShiftOpenedAt` datetime DEFAULT NULL,
  `dis2Opened` int(11) DEFAULT NULL,
  `dis2OpenedBy` int(11) DEFAULT NULL,
  `dis2OpenedAt` datetime DEFAULT NULL,
  `barOpened` int(11) DEFAULT NULL,
  `barOpenedBy` int(11) DEFAULT NULL,
  `barOpenedAt` datetime DEFAULT NULL,
  `disectDispensedCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `disectDispensedBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `disectBarCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `disectBarBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftOpenedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `secshiftopen`
--

CREATE TABLE `secshiftopen` (
  `openingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillBalance` decimal(15,2) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `owedPlusTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `openedby` int(11) NOT NULL DEFAULT '0',
  `prodStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodStockFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodStockExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stockDeltaFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stockDeltaExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `closed` int(11) NOT NULL DEFAULT '0',
  `secClosed` int(11) NOT NULL DEFAULT '0',
  `disClosed` int(11) NOT NULL DEFAULT '0',
  `secClosedBy` int(11) NOT NULL DEFAULT '0',
  `disClosedBy` int(11) NOT NULL DEFAULT '0',
  `shiftClosedNo` int(11) NOT NULL DEFAULT '0',
  `shiftClosedBy` int(11) NOT NULL DEFAULT '0',
  `bankBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftClosed` int(11) NOT NULL DEFAULT '0',
  `secClosedAt` datetime DEFAULT NULL,
  `disClosedAt` datetime DEFAULT NULL,
  `catClosed` int(11) NOT NULL DEFAULT '0',
  `catClosedBy` int(11) NOT NULL DEFAULT '0',
  `catClosedAt` datetime DEFAULT NULL,
  `dis2Closed` int(11) DEFAULT NULL,
  `dis2ClosedBy` int(11) DEFAULT NULL,
  `dis2ClosedAt` datetime DEFAULT NULL,
  `shiftClosedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shiftclose`
--

CREATE TABLE `shiftclose` (
  `closingid` int(11) NOT NULL,
  `closingtime` datetime DEFAULT NULL,
  `shiftStart` datetime DEFAULT NULL,
  `shiftEnd` datetime DEFAULT NULL,
  `quantitySold` decimal(15,2) DEFAULT NULL,
  `soldtoday` decimal(15,2) DEFAULT NULL,
  `notpaidtoday` decimal(15,2) DEFAULT NULL,
  `closingbalance` decimal(15,2) DEFAULT NULL,
  `moneytaken` decimal(15,2) DEFAULT NULL,
  `cashintill` decimal(15,2) DEFAULT NULL,
  `bankBalance` decimal(15,2) DEFAULT NULL,
  `newmembers` int(11) DEFAULT NULL,
  `closedby` int(11) DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `expenses` decimal(15,2) DEFAULT NULL,
  `membershipFees` decimal(15,2) DEFAULT NULL,
  `estimatedTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `bankExpenses` decimal(15,2) DEFAULT NULL,
  `debtRepaid` decimal(15,2) DEFAULT NULL,
  `debtRepaidBank` decimal(15,2) DEFAULT NULL,
  `prodOpening` decimal(15,2) DEFAULT NULL,
  `prodAdded` decimal(15,2) DEFAULT NULL,
  `prodRemoved` decimal(15,2) DEFAULT NULL,
  `prodEstStock` decimal(15,2) DEFAULT NULL,
  `prodStock` decimal(15,2) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `prodStockFlower` decimal(15,2) DEFAULT NULL,
  `prodStockExtract` decimal(15,2) DEFAULT NULL,
  `income` decimal(15,2) DEFAULT NULL,
  `paraphernalia` decimal(15,2) DEFAULT NULL,
  `biscuits` decimal(15,2) DEFAULT NULL,
  `drinksandsnacks` decimal(15,2) DEFAULT NULL,
  `prerolls` decimal(15,2) DEFAULT NULL,
  `otherAdditions` decimal(15,2) DEFAULT NULL,
  `prodOpeningFlower` decimal(15,2) DEFAULT NULL,
  `prodOpeningExtract` decimal(15,2) DEFAULT NULL,
  `prodAddedFlower` decimal(15,2) DEFAULT NULL,
  `prodAddedExtract` decimal(15,2) DEFAULT NULL,
  `prodRemovedFlower` decimal(15,2) DEFAULT NULL,
  `prodRemovedExtract` decimal(15,2) DEFAULT NULL,
  `prodEstStockFlower` decimal(15,2) DEFAULT NULL,
  `prodEstStockExtract` decimal(15,2) DEFAULT NULL,
  `stockDeltaFlower` decimal(15,2) DEFAULT NULL,
  `stockDeltaExtract` decimal(15,2) DEFAULT NULL,
  `donations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bankDonations` decimal(15,2) NOT NULL DEFAULT '0.00',
  `renewedMembers` int(11) NOT NULL DEFAULT '0',
  `bannedMembers` int(11) NOT NULL DEFAULT '0',
  `deletedMembers` int(11) NOT NULL DEFAULT '0',
  `expiredMembers` int(11) NOT NULL DEFAULT '0',
  `totalMembers` int(11) NOT NULL DEFAULT '0',
  `activeMembers` int(11) NOT NULL DEFAULT '0',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerweightNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowertotalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractintStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractextStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extracttotalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `flowerDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractDispensed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `membershipfeesBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `openingBalanceBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldtodayBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `takenduringday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSoldBar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftOpened` int(11) NOT NULL DEFAULT '0',
  `recOpened` int(11) NOT NULL DEFAULT '0',
  `disOpened` int(11) NOT NULL DEFAULT '0',
  `shiftOpenedBy` int(11) NOT NULL DEFAULT '0',
  `recOpenedBy` int(11) NOT NULL DEFAULT '0',
  `disOpenedBy` int(11) NOT NULL DEFAULT '0',
  `shiftOpenedNo` int(11) NOT NULL DEFAULT '0',
  `recOpenedAt` datetime DEFAULT NULL,
  `disOpenedAt` datetime DEFAULT NULL,
  `quantitySoldReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayFlowerReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldTodayExtractReal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dis2ShiftOpened` int(11) DEFAULT NULL,
  `dis2ShiftOpenedBy` int(11) DEFAULT NULL,
  `dis2ShiftOpenedAt` datetime DEFAULT NULL,
  `barShiftOpened` int(11) DEFAULT NULL,
  `barShiftOpenedBy` int(11) DEFAULT NULL,
  `barShiftOpenedAt` datetime DEFAULT NULL,
  `dis2Opened` int(11) DEFAULT NULL,
  `dis2OpenedBy` int(11) DEFAULT NULL,
  `dis2OpenedAt` datetime DEFAULT NULL,
  `barOpened` int(11) DEFAULT NULL,
  `barOpenedBy` int(11) DEFAULT NULL,
  `barOpenedAt` datetime DEFAULT NULL,
  `directDispensedCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directDispensedBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directBarCash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `directBarBank` decimal(15,2) NOT NULL DEFAULT '0.00',
  `chipincome` decimal(15,2) NOT NULL DEFAULT '0.00',
  `chipincomecard` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shiftclosedetails`
--

CREATE TABLE `shiftclosedetails` (
  `closingdetailsid` int(11) NOT NULL,
  `closingid` int(11) NOT NULL DEFAULT '0',
  `category` int(11) NOT NULL DEFAULT '0',
  `categoryType` int(11) NOT NULL DEFAULT '0',
  `productid` int(11) NOT NULL DEFAULT '0',
  `purchaseid` int(11) NOT NULL DEFAULT '0',
  `weightToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `addedToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `takeoutsToday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `weight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `weightEst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `weightDelta` decimal(15,2) NOT NULL DEFAULT '0.00',
  `specificComment` varchar(1000) DEFAULT NULL,
  `shakePercentage` int(11) NOT NULL DEFAULT '0',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `weightNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalWeight` decimal(15,2) NOT NULL DEFAULT '0.00',
  `totalNoShake` decimal(15,2) NOT NULL DEFAULT '0.00',
  `inMenu` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shiftcloseother`
--

CREATE TABLE `shiftcloseother` (
  `id` int(11) NOT NULL,
  `closingid` int(11) DEFAULT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `categoryType` int(11) NOT NULL DEFAULT '0',
  `stockDelta` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantitySold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `soldtoday` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unitsSold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodOpening` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodAdded` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodRemoved` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodEstStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `intStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extStash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantitySoldReal` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shiftopen`
--

CREATE TABLE `shiftopen` (
  `openingid` int(11) NOT NULL,
  `openingtime` datetime DEFAULT NULL,
  `oneCent` int(11) DEFAULT NULL,
  `twoCent` int(11) DEFAULT NULL,
  `fiveCent` int(11) DEFAULT NULL,
  `tenCent` int(11) DEFAULT NULL,
  `twentyCent` int(11) DEFAULT NULL,
  `fiftyCent` int(11) DEFAULT NULL,
  `oneEuro` int(11) DEFAULT NULL,
  `twoEuro` int(11) DEFAULT NULL,
  `fiveEuro` int(11) DEFAULT NULL,
  `tenEuro` int(11) DEFAULT NULL,
  `twentyEuro` int(11) DEFAULT NULL,
  `fiftyEuro` int(11) DEFAULT NULL,
  `hundredEuro` int(11) DEFAULT NULL,
  `coinsTot` decimal(15,2) DEFAULT NULL,
  `notesTot` decimal(15,2) DEFAULT NULL,
  `tillBalance` decimal(15,2) DEFAULT NULL,
  `moneyOwed` decimal(15,2) DEFAULT NULL,
  `owedPlusTill` decimal(15,2) DEFAULT NULL,
  `tillDelta` decimal(15,2) DEFAULT NULL,
  `tillComment` varchar(1000) DEFAULT NULL,
  `stockDelta` decimal(15,2) DEFAULT NULL,
  `openedby` int(11) NOT NULL DEFAULT '0',
  `prodStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodStockFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `prodStockExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stockDeltaFlower` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stockDeltaExtract` decimal(15,2) NOT NULL DEFAULT '0.00',
  `closed` int(11) NOT NULL DEFAULT '0',
  `recClosed` int(11) NOT NULL DEFAULT '0',
  `disClosed` int(11) NOT NULL DEFAULT '0',
  `recClosedBy` int(11) NOT NULL DEFAULT '0',
  `disClosedBy` int(11) NOT NULL DEFAULT '0',
  `shiftClosedNo` int(11) NOT NULL DEFAULT '0',
  `shiftClosedBy` int(11) NOT NULL DEFAULT '0',
  `bankBalance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shiftClosed` int(11) NOT NULL DEFAULT '0',
  `recClosedAt` datetime DEFAULT NULL,
  `disClosedAt` datetime DEFAULT NULL,
  `catClosed` int(11) NOT NULL DEFAULT '0',
  `catClosedBy` int(11) NOT NULL DEFAULT '0',
  `catClosedAt` datetime DEFAULT NULL,
  `dis2Closed` int(11) DEFAULT NULL,
  `dis2ClosedBy` int(11) DEFAULT NULL,
  `dis2ClosedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shiftopendetails`
--

CREATE TABLE `shiftopendetails` (
  `openingdetailsid` int(11) NOT NULL,
  `openingid` int(11) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `categoryType` int(11) DEFAULT '0',
  `productid` int(11) DEFAULT NULL,
  `purchaseid` int(11) DEFAULT NULL,
  `weight` decimal(15,2) DEFAULT NULL,
  `prodOpenComment` varchar(1000) DEFAULT NULL,
  `weightDelta` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shiftopenother`
--

CREATE TABLE `shiftopenother` (
  `id` int(11) NOT NULL,
  `openingid` int(11) DEFAULT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `categoryType` int(11) NOT NULL DEFAULT '0',
  `prodStock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stockDelta` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `systemsettings`
--

CREATE TABLE `systemsettings` (
  `id` int(11) NOT NULL,
  `highRollerWeekly` decimal(10,2) NOT NULL DEFAULT '0.00',
  `consumptionPercentage` int(11) NOT NULL DEFAULT '0',
  `domain` varchar(20) NOT NULL DEFAULT '',
  `minAge` int(11) NOT NULL DEFAULT '0',
  `closingMail` int(11) NOT NULL DEFAULT '0',
  `dispensaryGift` int(11) NOT NULL DEFAULT '0',
  `barGift` int(11) NOT NULL DEFAULT '0',
  `menuType` int(11) NOT NULL DEFAULT '0',
  `logouttime` int(11) NOT NULL DEFAULT '0',
  `logoutredir` int(11) NOT NULL DEFAULT '0',
  `medicalDiscount` int(11) NOT NULL DEFAULT '0',
  `dispDonate` int(11) NOT NULL DEFAULT '0',
  `dispExpired` int(11) NOT NULL DEFAULT '0',
  `dispenseLimit` int(11) NOT NULL DEFAULT '0',
  `showAge` int(11) NOT NULL DEFAULT '0',
  `showGender` int(11) NOT NULL DEFAULT '0',
  `keepNumber` int(11) NOT NULL DEFAULT '0',
  `membershipFees` int(11) NOT NULL DEFAULT '0',
  `medicalDiscountPercentage` int(11) NOT NULL DEFAULT '0',
  `bankPayments` int(11) NOT NULL DEFAULT '0',
  `creditOrDirect` int(11) NOT NULL DEFAULT '0',
  `visitRegistration` int(11) NOT NULL DEFAULT '0',
  `cropOrNot` int(11) NOT NULL DEFAULT '0',
  `puestosOrNot` int(11) NOT NULL DEFAULT '0',
  `openAndClose` int(11) NOT NULL DEFAULT '0',
  `barMenuType` int(11) NOT NULL DEFAULT '0',
  `flowerLimit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractLimit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `realWeight` int(11) NOT NULL DEFAULT '0',
  `autologout` int(11) NOT NULL DEFAULT '0',
  `showStock` int(11) NOT NULL DEFAULT '0',
  `showOrigPrice` int(11) NOT NULL DEFAULT '0',
  `checkoutDiscount` int(11) NOT NULL DEFAULT '0',
  `consumptionMin` decimal(15,2) NOT NULL DEFAULT '0.00',
  `consumptionMax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `showStockBar` int(11) NOT NULL DEFAULT '0',
  `showOrigPriceBar` int(11) NOT NULL DEFAULT '0',
  `barTouchscreen` int(11) NOT NULL DEFAULT '0',
  `trialMode` int(11) NOT NULL DEFAULT '0',
  `contract` int(11) NOT NULL DEFAULT '0',
  `iPadReaders` int(11) NOT NULL DEFAULT '0',
  `cashdro` int(11) NOT NULL DEFAULT '0',
  `warning` int(11) NOT NULL DEFAULT '0',
  `cutoff` datetime DEFAULT NULL,
  `creditchange` int(11) NOT NULL DEFAULT '0',
  `expirychange` int(11) NOT NULL DEFAULT '0',
  `exentoset` int(11) NOT NULL DEFAULT '0',
  `menusortdisp` int(11) DEFAULT '0',
  `menusortbar` int(11) NOT NULL DEFAULT '0',
  `dispsig` int(11) NOT NULL DEFAULT '0',
  `barsig` int(11) NOT NULL DEFAULT '0',
  `openmenu` int(11) NOT NULL DEFAULT '0',
  `keypads` int(11) NOT NULL DEFAULT '0',
  `moneycount` int(11) NOT NULL DEFAULT '0',
  `customws` int(11) NOT NULL DEFAULT '0',
  `negcredit` int(11) NOT NULL DEFAULT '0',
  `language` int(11) NOT NULL DEFAULT '0',
  `nobar` int(11) NOT NULL DEFAULT '0',
  `sigtablet` int(11) NOT NULL DEFAULT '0',
  `entrysys` int(11) NOT NULL DEFAULT '0',
  `entrysysstay` int(11) NOT NULL DEFAULT '0',
  `entrysyssecs` int(11) NOT NULL DEFAULT '0',
  `dooropener` int(11) NOT NULL DEFAULT '0',
  `cuotaincrement` int(11) NOT NULL DEFAULT '0',
  `checkoutDiscountBar` int(11) NOT NULL DEFAULT '0',
  `contractFull` varchar(20000) NOT NULL DEFAULT '',
  `chipcost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `fingerprint` int(11) NOT NULL DEFAULT '0',
  `pagination` int(11) NOT NULL DEFAULT '200',
  `normalNumbers` int(11) NOT NULL DEFAULT '0',
  `dooropenfor` int(11) NOT NULL DEFAULT '0',
  `workertracking` int(11) NOT NULL DEFAULT '0',
  `fullmenu` int(11) NOT NULL DEFAULT '0',
  `barfullmenu` int(11) NOT NULL,
  `setting1` int(11) NOT NULL DEFAULT '0',
  `setting2` int(11) NOT NULL DEFAULT '0',
  `setting3` int(11) NOT NULL DEFAULT '0',
  `setting4` int(11) NOT NULL DEFAULT '0',
  `presignup` int(11) NOT NULL DEFAULT '0',
  `signupcode` varchar(20) NOT NULL DEFAULT '',
  `allowvisitors` int(11) NOT NULL DEFAULT '0',
  `flowerLimitPercentage` decimal(15,2) NOT NULL DEFAULT '0.00',
  `extractLimitPercentage` decimal(15,2) NOT NULL DEFAULT '0.00',
  `fastVisitor` int(11) NOT NULL DEFAULT '0',
  `saldoGift` int(11) NOT NULL DEFAULT '0',
  `services` int(11) NOT NULL DEFAULT '0',
  `minorder` decimal(15,2) NOT NULL,
  `clubname` varchar(50) NOT NULL DEFAULT '',
  `clubemail` varchar(50) NOT NULL DEFAULT '',
  `clubphone` varchar(50) NOT NULL DEFAULT '',
  `requiredniandsig` int(11) NOT NULL DEFAULT '0',
  `currencyoperator` varchar(20) NOT NULL DEFAULT '&euro;',
  `export_number_format` varchar(1) NOT NULL DEFAULT ',',
  `openinghour` varchar(5) NOT NULL DEFAULT '',
  `closinghour` varchar(5) NOT NULL DEFAULT '',
  `day1` int(11) NOT NULL DEFAULT '1',
  `day2` int(11) NOT NULL DEFAULT '1',
  `day3` int(11) NOT NULL DEFAULT '1',
  `day4` int(11) NOT NULL DEFAULT '1',
  `day5` int(11) NOT NULL DEFAULT '1',
  `day6` int(11) NOT NULL DEFAULT '1',
  `day7` int(11) NOT NULL DEFAULT '1',
  `multiple` int(11) NOT NULL DEFAULT '1',
  `minutes` int(11) NOT NULL DEFAULT '15',
  `hours` int(11) NOT NULL DEFAULT '0',
  `sameday` int(11) NOT NULL DEFAULT '1',
  `timeslots` int(11) NOT NULL DEFAULT '0',
  `deliveries` int(11) NOT NULL DEFAULT '0',
  `deliverycharge` decimal(15,2) NOT NULL DEFAULT '0.00',
  `deliverychargepct` decimal(15,2) NOT NULL DEFAULT '0.00',
  `appointments` int(11) NOT NULL DEFAULT '0',
  `nostocknodispense` int(11) NOT NULL DEFAULT '0',
  `openinghourcita` varchar(5) NOT NULL DEFAULT '',
  `closinghourcita` varchar(5) NOT NULL DEFAULT '',
  `citaday1` int(11) NOT NULL DEFAULT '1',
  `citaday2` int(11) NOT NULL DEFAULT '1',
  `citaday3` int(11) NOT NULL DEFAULT '1',
  `citaday4` int(11) NOT NULL DEFAULT '1',
  `citaday5` int(11) NOT NULL DEFAULT '1',
  `citaday6` int(11) NOT NULL DEFAULT '1',
  `citaday7` int(11) NOT NULL DEFAULT '1',
  `citamultiple` int(11) NOT NULL DEFAULT '1',
  `citaminutes` int(11) NOT NULL DEFAULT '15',
  `citahours` int(11) NOT NULL DEFAULT '0',
  `citasameday` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `systemsettings`
--

INSERT INTO `systemsettings` (`id`, `highRollerWeekly`, `consumptionPercentage`, `domain`, `minAge`, `closingMail`, `dispensaryGift`, `barGift`, `menuType`, `logouttime`, `logoutredir`, `medicalDiscount`, `dispDonate`, `dispExpired`, `dispenseLimit`, `showAge`, `showGender`, `keepNumber`, `membershipFees`, `medicalDiscountPercentage`, `bankPayments`, `creditOrDirect`, `visitRegistration`, `cropOrNot`, `puestosOrNot`, `openAndClose`, `barMenuType`, `flowerLimit`, `extractLimit`, `realWeight`, `autologout`, `showStock`, `showOrigPrice`, `checkoutDiscount`, `consumptionMin`, `consumptionMax`, `showStockBar`, `showOrigPriceBar`, `barTouchscreen`, `trialMode`, `contract`, `iPadReaders`, `cashdro`, `warning`, `cutoff`, `creditchange`, `expirychange`, `exentoset`, `menusortdisp`, `menusortbar`, `dispsig`, `barsig`, `openmenu`, `keypads`, `moneycount`, `customws`, `negcredit`, `language`, `nobar`, `sigtablet`, `entrysys`, `entrysysstay`, `entrysyssecs`, `dooropener`, `cuotaincrement`, `checkoutDiscountBar`, `contractFull`, `chipcost`, `fingerprint`, `pagination`, `normalNumbers`, `dooropenfor`, `workertracking`, `fullmenu`, `barfullmenu`, `setting1`, `setting2`, `setting3`, `setting4`, `presignup`, `signupcode`, `allowvisitors`, `flowerLimitPercentage`, `extractLimitPercentage`, `fastVisitor`, `saldoGift`, `services`, `minorder`, `clubname`, `clubemail`, `clubphone`, `requiredniandsig`, `currencyoperator`, `export_number_format`, `openinghour`, `closinghour`, `day1`, `day2`, `day3`, `day4`, `day5`, `day6`, `day7`, `multiple`, `minutes`, `hours`, `sameday`, `timeslots`, `deliveries`, `deliverycharge`, `deliverychargepct`, `appointments`, `nostocknodispense`, `openinghourcita`, `closinghourcita`, `citaday1`, `citaday2`, `citaday3`, `citaday4`, `citaday5`, `citaday6`, `citaday7`, `citamultiple`, `citaminutes`, `citahours`, `citasameday`) VALUES
(0, '50.00', 10, 'betsaide', 21, 0, 0, 0, 0, 0, 0, 0, 0, 1, 300, 1, 1, 1, 1, 0, 1, 1, 0, 1, 0, 3, 0, '0.10', '0.07', 0, 9, 0, 0, 0, '1.00', '60.00', 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '0.00', 0, 200, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, '0.00', '', '', '', 0, '&euro;', ',', '', '', 1, 1, 1, 1, 1, 1, 1, 1, 15, 0, 1, 1, 0, '0.00', '0.00', 0, 0, '', '', 1, 1, 1, 1, 1, 1, 1, 1, 15, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tillmovements`
--

CREATE TABLE `tillmovements` (
  `movementid` int(11) NOT NULL,
  `movementtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` int(11) NOT NULL DEFAULT '0',
  `tillMovementTypeid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comment` varchar(1000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tillmovementtypes`
--

CREATE TABLE `tillmovementtypes` (
  `tillMovementTypeid` int(11) NOT NULL,
  `movementType` int(11) NOT NULL DEFAULT '0',
  `movementName` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tillmovementtypes`
--

INSERT INTO `tillmovementtypes` (`tillMovementTypeid`, `movementType`, `movementName`) VALUES
(1, 1, 'Papers and paraphernalia'),
(2, 1, 'Biscuits & THC products'),
(3, 1, 'Drinks, snacks, food'),
(4, 1, 'Pre-rolled joints'),
(5, 1, 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `usergroups`
--

CREATE TABLE `usergroups` (
  `userGroup` int(11) NOT NULL,
  `groupName` varchar(100) DEFAULT NULL,
  `groupName_es` varchar(50) NOT NULL DEFAULT '',
  `groupDesc` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `usergroups`
--

INSERT INTO `usergroups` (`userGroup`, `groupName`, `groupName_es`, `groupDesc`) VALUES
(1, 'Administrador', '', 'Site-wide administrator. All privileges.'),
(2, 'Trabajador', '', NULL),
(3, 'Voluntario', '', 'General system access.'),
(4, 'Contacto profesional', '', 'Growers, staff of other associations, lawyers etc.'),
(5, 'Socio', '', 'Newly registered member.'),
(6, 'Visitante', '', 'User has announced interest. No priviliges.'),
(7, 'Expulsado', '', NULL),
(8, 'Borrado', '', NULL),
(9, 'Bajado', '', NULL),
(10, 'Pre-registered', 'Pre-registered', NULL),
(11, 'Pre-registered', 'Pre-registered', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usergroups2`
--

CREATE TABLE `usergroups2` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `usergroup_discounts`
--

CREATE TABLE `usergroup_discounts` (
  `id` int(11) NOT NULL,
  `usergroup_id` int(11) NOT NULL,
  `discount_price` decimal(15,2) NOT NULL,
  `discount_percentage` decimal(15,2) NOT NULL,
  `b_discount_price` decimal(15,2) NOT NULL,
  `b_discount_percentage` decimal(15,2) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `usernotes`
--

CREATE TABLE `usernotes` (
  `noteid` int(11) NOT NULL,
  `notetime` datetime DEFAULT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `note` varchar(1000) NOT NULL DEFAULT '',
  `club` int(11) NOT NULL DEFAULT '0',
  `worker` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `memberno` varchar(6) DEFAULT '',
  `registeredSince` datetime DEFAULT NULL,
  `userPass` varchar(30) DEFAULT NULL,
  `userGroup` int(11) DEFAULT NULL,
  `adminComment` varchar(1000) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `gender` varchar(12) DEFAULT NULL,
  `dni` varchar(30) DEFAULT NULL,
  `passport` varchar(30) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `streetnumber` int(11) DEFAULT NULL,
  `flat` varchar(20) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `telephone` varchar(40) DEFAULT NULL,
  `mconsumption` int(11) DEFAULT NULL,
  `usageType` int(11) NOT NULL DEFAULT '0',
  `signupsource` varchar(100) DEFAULT NULL,
  `cardid` varchar(30) DEFAULT NULL,
  `photoid` int(11) DEFAULT NULL,
  `docid` int(11) DEFAULT NULL,
  `doorAccess` tinyint(1) DEFAULT NULL,
  `friend` varchar(40) DEFAULT NULL,
  `friend2` varchar(40) NOT NULL DEFAULT '',
  `paidoct` int(11) DEFAULT NULL,
  `paidUntil` datetime DEFAULT NULL,
  `form1` tinyint(1) NOT NULL DEFAULT '0',
  `form2` tinyint(1) NOT NULL DEFAULT '0',
  `paymentWarning` tinyint(1) NOT NULL DEFAULT '0',
  `paymentWarningDate` datetime DEFAULT NULL,
  `credit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `banComment` varchar(1000) DEFAULT NULL,
  `banTime` datetime DEFAULT NULL,
  `deleteTime` datetime DEFAULT NULL,
  `creditEligible` tinyint(1) NOT NULL DEFAULT '0',
  `dniscan` int(11) NOT NULL DEFAULT '0',
  `discount` int(11) NOT NULL DEFAULT '0',
  `locker` decimal(15,2) NOT NULL DEFAULT '0.00',
  `province` varchar(100) NOT NULL DEFAULT '',
  `exento` tinyint(1) NOT NULL DEFAULT '0',
  `discountBar` int(11) NOT NULL DEFAULT '0',
  `dniext1` varchar(5) NOT NULL DEFAULT '',
  `dniext2` varchar(5) NOT NULL DEFAULT '',
  `photoext` varchar(5) NOT NULL DEFAULT '',
  `sigext` varchar(5) NOT NULL DEFAULT '',
  `workStation` int(11) NOT NULL DEFAULT '0',
  `domain` varchar(20) NOT NULL DEFAULT '',
  `lastDispense` datetime DEFAULT NULL,
  `bajaDate` datetime DEFAULT NULL,
  `memberType` int(11) NOT NULL DEFAULT '0',
  `starCat` int(11) NOT NULL DEFAULT '0',
  `interview` int(11) NOT NULL DEFAULT '0',
  `cardid2` varchar(20) NOT NULL DEFAULT '',
  `cardid3` varchar(20) NOT NULL DEFAULT '',
  `last_name2` varchar(100) NOT NULL DEFAULT '',
  `maxCredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `promoter` int(11) NOT NULL DEFAULT '0',
  `source` varchar(100) NOT NULL DEFAULT '',
  `oldNumber` int(11) NOT NULL DEFAULT '0',
  `fptemplate1` varchar(3) NOT NULL DEFAULT '',
  `fptemplate2` varchar(3) NOT NULL DEFAULT '',
  `f_no1` tinyint(4) NOT NULL DEFAULT '0',
  `f_no2` tinyint(4) NOT NULL DEFAULT '0',
  `usergroup2` int(11) NOT NULL DEFAULT '0',
  `visittime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuota` decimal(15,2) NOT NULL DEFAULT '0.00',
  `invited` int(11) NOT NULL DEFAULT '0',
  `citainvited` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `memberno`, `registeredSince`, `userPass`, `userGroup`, `adminComment`, `first_name`, `last_name`, `email`, `day`, `month`, `year`, `nationality`, `gender`, `dni`, `passport`, `street`, `streetnumber`, `flat`, `postcode`, `city`, `country`, `telephone`, `mconsumption`, `usageType`, `signupsource`, `cardid`, `photoid`, `docid`, `doorAccess`, `friend`, `friend2`, `paidoct`, `paidUntil`, `form1`, `form2`, `paymentWarning`, `paymentWarningDate`, `credit`, `banComment`, `banTime`, `deleteTime`, `creditEligible`, `dniscan`, `discount`, `locker`, `province`, `exento`, `discountBar`, `dniext1`, `dniext2`, `photoext`, `sigext`, `workStation`, `domain`, `lastDispense`, `bajaDate`, `memberType`, `starCat`, `interview`, `cardid2`, `cardid3`, `last_name2`, `maxCredit`, `promoter`, `source`, `oldNumber`, `fptemplate1`, `fptemplate2`, `f_no1`, `f_no2`, `usergroup2`, `visittime`, `cuota`, `invited`, `citainvited`) VALUES
(1, '0', '2019-04-22 10:22:11', 'bedoi0KAVtNhY', 1, '', 'Demo', 'Usuario', 'demo@user.com', 1, 1, 1980, 'Demo', 'Male', 'Demo', NULL, '', 0, '', '', '', '', '', 44, 0, '', '999', 0, 0, 0, '0', '0', NULL, '2019-04-22 10:22:11', 0, 0, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, '0.00', '', 1, 0, '', '', 'png', '', 0, 'kushtea2', NULL, NULL, 0, 0, 0, '', '', '', '0.00', 0, '', 0, '', '', 0, 0, 0, '2019-10-29 07:30:06', '0.00', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `volume_discounts`
--

CREATE TABLE `volume_discounts` (
  `id` int(11) NOT NULL,
  `purchaseid` int(11) NOT NULL,
  `units` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `workstations`
--

CREATE TABLE `workstations` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `dispensary` int(11) NOT NULL DEFAULT '0',
  `bar` int(11) NOT NULL DEFAULT '0',
  `reception` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banked`
--
ALTER TABLE `banked`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `b_catdiscounts`
--
ALTER TABLE `b_catdiscounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `b_categories`
--
ALTER TABLE `b_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `b_inddiscounts`
--
ALTER TABLE `b_inddiscounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `b_productmovements`
--
ALTER TABLE `b_productmovements`
  ADD PRIMARY KEY (`movementid`);

--
-- Indexes for table `b_products`
--
ALTER TABLE `b_products`
  ADD PRIMARY KEY (`productid`);

--
-- Indexes for table `b_providerpayments`
--
ALTER TABLE `b_providerpayments`
  ADD PRIMARY KEY (`paymentid`);

--
-- Indexes for table `b_providers`
--
ALTER TABLE `b_providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `b_purchases`
--
ALTER TABLE `b_purchases`
  ADD PRIMARY KEY (`purchaseid`);

--
-- Indexes for table `b_sales`
--
ALTER TABLE `b_sales`
  ADD PRIMARY KEY (`saleid`),
  ADD KEY `saletime` (`saletime`);

--
-- Indexes for table `b_salesdetails`
--
ALTER TABLE `b_salesdetails`
  ADD PRIMARY KEY (`salesdetailsid`),
  ADD KEY `saleid` (`saleid`),
  ADD KEY `purchaseid` (`purchaseid`);

--
-- Indexes for table `b_sales_discount`
--
ALTER TABLE `b_sales_discount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `b_volume_discounts`
--
ALTER TABLE `b_volume_discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `card_purchase`
--
ALTER TABLE `card_purchase`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `catdiscounts`
--
ALTER TABLE `catdiscounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `closing`
--
ALTER TABLE `closing`
  ADD PRIMARY KEY (`closingid`);

--
-- Indexes for table `closingdetails`
--
ALTER TABLE `closingdetails`
  ADD PRIMARY KEY (`closingdetailsid`);

--
-- Indexes for table `closingother`
--
ALTER TABLE `closingother`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `closing_mails`
--
ALTER TABLE `closing_mails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cuotas`
--
ALTER TABLE `cuotas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donationid`),
  ADD KEY `donationTime` (`donationTime`),
  ADD KEY `donatedTo` (`donatedTo`),
  ADD KEY `userid` (`userid`,`amount`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`empno`),
  ADD UNIQUE KEY `reg_id` (`reg_id`);

--
-- Indexes for table `expensecategories`
--
ALTER TABLE `expensecategories`
  ADD PRIMARY KEY (`categoryid`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expenseid`);

--
-- Indexes for table `extract`
--
ALTER TABLE `extract`
  ADD PRIMARY KEY (`extractid`);

--
-- Indexes for table `flower`
--
ALTER TABLE `flower`
  ADD PRIMARY KEY (`flowerid`);

--
-- Indexes for table `f_b_productmovements`
--
ALTER TABLE `f_b_productmovements`
  ADD PRIMARY KEY (`movementid`);

--
-- Indexes for table `f_b_sales`
--
ALTER TABLE `f_b_sales`
  ADD PRIMARY KEY (`saleid`);

--
-- Indexes for table `f_b_salesdetails`
--
ALTER TABLE `f_b_salesdetails`
  ADD PRIMARY KEY (`salesdetailsid`);

--
-- Indexes for table `f_donations`
--
ALTER TABLE `f_donations`
  ADD PRIMARY KEY (`donationid`);

--
-- Indexes for table `f_log`
--
ALTER TABLE `f_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `f_memberpayments`
--
ALTER TABLE `f_memberpayments`
  ADD PRIMARY KEY (`paymentid`);

--
-- Indexes for table `f_productmovements`
--
ALTER TABLE `f_productmovements`
  ADD PRIMARY KEY (`movementid`);

--
-- Indexes for table `f_sales`
--
ALTER TABLE `f_sales`
  ADD PRIMARY KEY (`saleid`);

--
-- Indexes for table `f_salesdetails`
--
ALTER TABLE `f_salesdetails`
  ADD PRIMARY KEY (`salesdetailsid`);

--
-- Indexes for table `global_happy_hour_discounts`
--
ALTER TABLE `global_happy_hour_discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `growtypes`
--
ALTER TABLE `growtypes`
  ADD PRIMARY KEY (`growtypeid`);

--
-- Indexes for table `inddiscounts`
--
ALTER TABLE `inddiscounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lockers`
--
ALTER TABLE `lockers`
  ADD PRIMARY KEY (`movementid`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logtime` (`logtime`),
  ADD KEY `logtime_2` (`logtime`);

--
-- Indexes for table `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logtypes`
--
ALTER TABLE `logtypes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `memberpaymentparts`
--
ALTER TABLE `memberpaymentparts`
  ADD PRIMARY KEY (`partid`);

--
-- Indexes for table `memberpayments`
--
ALTER TABLE `memberpayments`
  ADD PRIMARY KEY (`paymentid`),
  ADD KEY `paymentdate` (`paymentdate`),
  ADD KEY `paidTo` (`paidTo`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `newscan`
--
ALTER TABLE `newscan`
  ADD PRIMARY KEY (`scanid`);

--
-- Indexes for table `newvisits`
--
ALTER TABLE `newvisits`
  ADD PRIMARY KEY (`visitNo`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opening`
--
ALTER TABLE `opening`
  ADD PRIMARY KEY (`openingid`);

--
-- Indexes for table `openingdetails`
--
ALTER TABLE `openingdetails`
  ADD PRIMARY KEY (`openingdetailsid`);

--
-- Indexes for table `openingother`
--
ALTER TABLE `openingother`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productmovements`
--
ALTER TABLE `productmovements`
  ADD PRIMARY KEY (`movementid`),
  ADD KEY `purchaseid` (`purchaseid`),
  ADD KEY `movementtime` (`movementtime`);

--
-- Indexes for table `productmovementtypes`
--
ALTER TABLE `productmovementtypes`
  ADD PRIMARY KEY (`movementTypeid`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productid`);

--
-- Indexes for table `providerpayments`
--
ALTER TABLE `providerpayments`
  ADD PRIMARY KEY (`paymentid`);

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchaseid`),
  ADD KEY `category` (`category`),
  ADD KEY `productid` (`productid`);

--
-- Indexes for table `recclosing`
--
ALTER TABLE `recclosing`
  ADD PRIMARY KEY (`closingid`);

--
-- Indexes for table `recopening`
--
ALTER TABLE `recopening`
  ADD PRIMARY KEY (`openingid`);

--
-- Indexes for table `recshiftclose`
--
ALTER TABLE `recshiftclose`
  ADD PRIMARY KEY (`closingid`);

--
-- Indexes for table `recshiftopen`
--
ALTER TABLE `recshiftopen`
  ADD PRIMARY KEY (`openingid`);

--
-- Indexes for table `rejected`
--
ALTER TABLE `rejected`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`saleid`),
  ADD KEY `saletime` (`saletime`),
  ADD KEY `amount` (`amount`),
  ADD KEY `amount_2` (`amount`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `salesdetails`
--
ALTER TABLE `salesdetails`
  ADD PRIMARY KEY (`salesdetailsid`),
  ADD KEY `saleid` (`saleid`),
  ADD KEY `purchaseid` (`purchaseid`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `sales_discount`
--
ALTER TABLE `sales_discount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saveDispense_summary`
--
ALTER TABLE `saveDispense_summary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `savesales_details`
--
ALTER TABLE `savesales_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scanhistory`
--
ALTER TABLE `scanhistory`
  ADD PRIMARY KEY (`scanid`);

--
-- Indexes for table `secclosing`
--
ALTER TABLE `secclosing`
  ADD PRIMARY KEY (`closingid`);

--
-- Indexes for table `secopening`
--
ALTER TABLE `secopening`
  ADD PRIMARY KEY (`openingid`);

--
-- Indexes for table `secshiftclose`
--
ALTER TABLE `secshiftclose`
  ADD PRIMARY KEY (`closingid`);

--
-- Indexes for table `secshiftopen`
--
ALTER TABLE `secshiftopen`
  ADD PRIMARY KEY (`openingid`);

--
-- Indexes for table `shiftclose`
--
ALTER TABLE `shiftclose`
  ADD PRIMARY KEY (`closingid`);

--
-- Indexes for table `shiftclosedetails`
--
ALTER TABLE `shiftclosedetails`
  ADD PRIMARY KEY (`closingdetailsid`);

--
-- Indexes for table `shiftcloseother`
--
ALTER TABLE `shiftcloseother`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shiftopen`
--
ALTER TABLE `shiftopen`
  ADD PRIMARY KEY (`openingid`);

--
-- Indexes for table `shiftopendetails`
--
ALTER TABLE `shiftopendetails`
  ADD PRIMARY KEY (`openingdetailsid`);

--
-- Indexes for table `shiftopenother`
--
ALTER TABLE `shiftopenother`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `systemsettings`
--
ALTER TABLE `systemsettings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain` (`domain`,`trialMode`),
  ADD KEY `domain_2` (`domain`,`trialMode`);

--
-- Indexes for table `tillmovements`
--
ALTER TABLE `tillmovements`
  ADD PRIMARY KEY (`movementid`);

--
-- Indexes for table `tillmovementtypes`
--
ALTER TABLE `tillmovementtypes`
  ADD PRIMARY KEY (`tillMovementTypeid`);

--
-- Indexes for table `usergroups`
--
ALTER TABLE `usergroups`
  ADD PRIMARY KEY (`userGroup`);

--
-- Indexes for table `usergroups2`
--
ALTER TABLE `usergroups2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usergroup_discounts`
--
ALTER TABLE `usergroup_discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usernotes`
--
ALTER TABLE `usernotes`
  ADD PRIMARY KEY (`noteid`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `memberno` (`memberno`),
  ADD KEY `paidUntil` (`paidUntil`),
  ADD KEY `registeredSince` (`registeredSince`),
  ADD KEY `friend` (`friend`);

--
-- Indexes for table `volume_discounts`
--
ALTER TABLE `volume_discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workstations`
--
ALTER TABLE `workstations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `banked`
--
ALTER TABLE `banked`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_catdiscounts`
--
ALTER TABLE `b_catdiscounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_categories`
--
ALTER TABLE `b_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_inddiscounts`
--
ALTER TABLE `b_inddiscounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_productmovements`
--
ALTER TABLE `b_productmovements`
  MODIFY `movementid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_products`
--
ALTER TABLE `b_products`
  MODIFY `productid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_providerpayments`
--
ALTER TABLE `b_providerpayments`
  MODIFY `paymentid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_providers`
--
ALTER TABLE `b_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_purchases`
--
ALTER TABLE `b_purchases`
  MODIFY `purchaseid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_sales`
--
ALTER TABLE `b_sales`
  MODIFY `saleid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_salesdetails`
--
ALTER TABLE `b_salesdetails`
  MODIFY `salesdetailsid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_sales_discount`
--
ALTER TABLE `b_sales_discount`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_volume_discounts`
--
ALTER TABLE `b_volume_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `card_purchase`
--
ALTER TABLE `card_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `catdiscounts`
--
ALTER TABLE `catdiscounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `closing`
--
ALTER TABLE `closing`
  MODIFY `closingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `closingdetails`
--
ALTER TABLE `closingdetails`
  MODIFY `closingdetailsid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `closingother`
--
ALTER TABLE `closingother`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `closing_mails`
--
ALTER TABLE `closing_mails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cuotas`
--
ALTER TABLE `cuotas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `donationid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `reg_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `expensecategories`
--
ALTER TABLE `expensecategories`
  MODIFY `categoryid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expenseid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `extract`
--
ALTER TABLE `extract`
  MODIFY `extractid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `flower`
--
ALTER TABLE `flower`
  MODIFY `flowerid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_b_productmovements`
--
ALTER TABLE `f_b_productmovements`
  MODIFY `movementid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_b_sales`
--
ALTER TABLE `f_b_sales`
  MODIFY `saleid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_b_salesdetails`
--
ALTER TABLE `f_b_salesdetails`
  MODIFY `salesdetailsid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_donations`
--
ALTER TABLE `f_donations`
  MODIFY `donationid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_log`
--
ALTER TABLE `f_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_memberpayments`
--
ALTER TABLE `f_memberpayments`
  MODIFY `paymentid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_productmovements`
--
ALTER TABLE `f_productmovements`
  MODIFY `movementid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_sales`
--
ALTER TABLE `f_sales`
  MODIFY `saleid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `f_salesdetails`
--
ALTER TABLE `f_salesdetails`
  MODIFY `salesdetailsid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `global_happy_hour_discounts`
--
ALTER TABLE `global_happy_hour_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `growtypes`
--
ALTER TABLE `growtypes`
  MODIFY `growtypeid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `inddiscounts`
--
ALTER TABLE `inddiscounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `lockers`
--
ALTER TABLE `lockers`
  MODIFY `movementid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logins`
--
ALTER TABLE `logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logtypes`
--
ALTER TABLE `logtypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `memberpaymentparts`
--
ALTER TABLE `memberpaymentparts`
  MODIFY `partid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `memberpayments`
--
ALTER TABLE `memberpayments`
  MODIFY `paymentid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `newscan`
--
ALTER TABLE `newscan`
  MODIFY `scanid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `newvisits`
--
ALTER TABLE `newvisits`
  MODIFY `visitNo` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `opening`
--
ALTER TABLE `opening`
  MODIFY `openingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `openingdetails`
--
ALTER TABLE `openingdetails`
  MODIFY `openingdetailsid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `openingother`
--
ALTER TABLE `openingother`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `productmovements`
--
ALTER TABLE `productmovements`
  MODIFY `movementid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `productmovementtypes`
--
ALTER TABLE `productmovementtypes`
  MODIFY `movementTypeid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `providerpayments`
--
ALTER TABLE `providerpayments`
  MODIFY `paymentid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchaseid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `recclosing`
--
ALTER TABLE `recclosing`
  MODIFY `closingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `recopening`
--
ALTER TABLE `recopening`
  MODIFY `openingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `recshiftclose`
--
ALTER TABLE `recshiftclose`
  MODIFY `closingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `recshiftopen`
--
ALTER TABLE `recshiftopen`
  MODIFY `openingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rejected`
--
ALTER TABLE `rejected`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `saleid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `salesdetails`
--
ALTER TABLE `salesdetails`
  MODIFY `salesdetailsid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sales_discount`
--
ALTER TABLE `sales_discount`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `saveDispense_summary`
--
ALTER TABLE `saveDispense_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `savesales_details`
--
ALTER TABLE `savesales_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scanhistory`
--
ALTER TABLE `scanhistory`
  MODIFY `scanid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `secclosing`
--
ALTER TABLE `secclosing`
  MODIFY `closingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `secopening`
--
ALTER TABLE `secopening`
  MODIFY `openingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `secshiftclose`
--
ALTER TABLE `secshiftclose`
  MODIFY `closingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `secshiftopen`
--
ALTER TABLE `secshiftopen`
  MODIFY `openingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shiftclose`
--
ALTER TABLE `shiftclose`
  MODIFY `closingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shiftclosedetails`
--
ALTER TABLE `shiftclosedetails`
  MODIFY `closingdetailsid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shiftcloseother`
--
ALTER TABLE `shiftcloseother`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shiftopen`
--
ALTER TABLE `shiftopen`
  MODIFY `openingid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shiftopendetails`
--
ALTER TABLE `shiftopendetails`
  MODIFY `openingdetailsid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shiftopenother`
--
ALTER TABLE `shiftopenother`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tillmovements`
--
ALTER TABLE `tillmovements`
  MODIFY `movementid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tillmovementtypes`
--
ALTER TABLE `tillmovementtypes`
  MODIFY `tillMovementTypeid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `usergroups`
--
ALTER TABLE `usergroups`
  MODIFY `userGroup` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `usergroups2`
--
ALTER TABLE `usergroups2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `usergroup_discounts`
--
ALTER TABLE `usergroup_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `usernotes`
--
ALTER TABLE `usernotes`
  MODIFY `noteid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `volume_discounts`
--
ALTER TABLE `volume_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `workstations`
--
ALTER TABLE `workstations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
