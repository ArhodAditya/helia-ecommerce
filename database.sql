-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2026 at 09:56 AM
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
-- Database: `helia_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Admin', 'admin@helia.com', '$2y$10$TuTknvkkipxK.Nc8inQdleBH1FxZYB//XG3.KFBJ/g8scqQANJmze', '2026-11-18 09:16:32');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Electronics & Gadgets', '2026-05-01 03:00:00'),
(2, 'Sports & Fitness', '2026-05-01 03:05:00'),
(3, 'Fashion & Lifestyle', '2026-05-01 03:10:00'),
(4, 'Home & Kitchen', '2026-05-01 03:15:00'),
(5, 'Books & Stationery', '2026-05-01 03:20:00'),
(6, 'Health & Personal Care', '2026-05-01 03:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_percent`, `expiry_date`, `is_active`) VALUES
(1, 'HELIA10', 10, '2027-06-30', 1),
(2, 'DHAKA15', 15, '2027-01-31', 1),
(3, 'SPORTS20', 20, '2027-03-31', 1),
(4, 'CTG12', 12, '2026-12-31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Shipped','Delivered') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(30) DEFAULT '',
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'Cash on Delivery'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `total_amount`, `coupon_code`, `status`, `created_at`, `phone`, `shipping_address`, `payment_method`) VALUES
(1, 1, 'Tanvir Ahmed', 10950.00, NULL, 'Delivered', '2026-06-04 04:24:00', '01712458963', 'House 27, Road 9/A, Dhanmondi, Dhaka 1209', 'Cash on Delivery'),
(2, 5, 'Mahfuzur Rahman', 3240.00, NULL, 'Delivered', '2026-06-21 13:05:00', '01818204576', 'House 6, Road 2, CDA Residential Area, Agrabad, Chattogram 4100', 'bKash'),
(3, 2, 'Nusrat Jahan', 6120.00, 'HELIA10', 'Delivered', '2026-07-03 15:40:00', '01819336247', 'Flat 5B, House 14, Road 7, Sector 4, Uttara, Dhaka 1230', 'Nagad'),
(4, 7, 'Tahmid Hasan', 5800.00, NULL, 'Delivered', '2026-07-15 08:12:00', '01916448021', 'Flat 3A, Lane 4, O.R. Nizam Road, near GEC Circle, Chattogram 4000', 'Cash on Delivery'),
(5, 3, 'Nafis Sadik', 3210.00, NULL, 'Delivered', '2026-07-28 05:30:00', '01915672384', 'House 112, Road 3, Block C, Mirpur 10, Dhaka 1216', 'bKash'),
(6, 4, 'Sadia Islam', 4390.00, NULL, 'Delivered', '2026-08-06 14:18:00', '01611895430', 'House 45, Road 11, Banani, Dhaka 1213', 'Nagad'),
(7, 8, 'Sabrina Chowdhury', 8055.00, 'HELIA10', 'Delivered', '2026-08-14 10:45:00', '01675209814', 'House 19, Lane 7, Block K, Halishahar Housing Estate, Chattogram 4216', 'Cash on Delivery'),
(8, 9, 'Rakibul Islam', 5980.00, NULL, 'Delivered', '2026-08-29 03:50:00', '01722581397', 'House 63, Road 27 (Old), Dhanmondi, Dhaka 1209', 'bKash'),
(9, 6, 'Farhana Akter', 5412.00, 'CTG12', 'Delivered', '2026-09-08 12:22:00', '01734917268', 'House 8, Road 2, Nasirabad Housing Society, Chattogram 4209', 'Nagad'),
(10, 10, 'Ayesha Siddiqua', 6190.00, NULL, 'Delivered', '2026-09-19 07:05:00', '01817760352', 'House 9, Road 13, Sector 6, Uttara, Dhaka 1230', 'Cash on Delivery'),
(11, 11, 'Imran Hossain', 13200.00, 'SPORTS20', 'Delivered', '2026-09-27 16:10:00', '01993145768', 'House 21, Road 4, Section 2, Mirpur, Dhaka 1216', 'bKash'),
(12, 12, 'Tasnim Ferdous', 7352.50, 'DHAKA15', 'Delivered', '2026-10-05 06:40:00', '01638472915', 'House 31, Road 2, Block E, Banani, Dhaka 1213', 'Nagad'),
(13, 1, 'Tanvir Ahmed', 3450.00, NULL, 'Delivered', '2026-10-13 11:25:00', '01712458963', 'House 27, Road 9/A, Dhanmondi, Dhaka 1209', 'bKash'),
(14, 5, 'Mahfuzur Rahman', 4300.00, NULL, 'Delivered', '2026-10-22 04:15:00', '01818204576', 'House 6, Road 2, CDA Residential Area, Agrabad, Chattogram 4100', 'Cash on Delivery'),
(15, 2, 'Nusrat Jahan', 2170.00, NULL, 'Delivered', '2026-10-30 14:55:00', '01819336247', 'Flat 5B, House 14, Road 7, Sector 4, Uttara, Dhaka 1230', 'Nagad'),
(16, 7, 'Tahmid Hasan', 8280.00, 'SPORTS20', 'Delivered', '2026-10-26 09:35:00', '01916448021', 'Flat 3A, Lane 4, O.R. Nizam Road, near GEC Circle, Chattogram 4000', 'Cash on Delivery'),
(17, 3, 'Nafis Sadik', 4990.00, NULL, 'Shipped', '2026-11-16 05:20:00', '01915672384', 'House 112, Road 3, Block C, Mirpur 10, Dhaka 1216', 'bKash'),
(18, 8, 'Sabrina Chowdhury', 5340.00, NULL, 'Shipped', '2026-11-17 13:48:00', '01675209814', 'House 19, Lane 7, Block K, Halishahar Housing Estate, Chattogram 4216', 'Nagad'),
(19, 4, 'Sadia Islam', 7607.50, 'DHAKA15', 'Confirmed', '2026-11-18 04:12:00', '01611895430', 'House 45, Road 11, Banani, Dhaka 1213', 'Cash on Delivery'),
(20, 9, 'Rakibul Islam', 3480.00, NULL, 'Confirmed', '2026-11-18 15:30:00', '01722581397', 'House 63, Road 27 (Old), Dhanmondi, Dhaka 1209', 'bKash'),
(21, 6, 'Farhana Akter', 2940.00, NULL, 'Pending', '2026-11-19 03:05:00', '01734917268', 'House 8, Road 2, Nasirabad Housing Society, Chattogram 4209', 'bKash'),
(22, 10, 'Ayesha Siddiqua', 5100.00, NULL, 'Pending', '2026-11-19 05:40:00', '01817760352', 'House 9, Road 13, Sector 6, Uttara, Dhaka 1230', 'Cash on Delivery');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `color` varchar(20) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `color`) VALUES
(1, 1, 24, 1, 8500.00, '#1D4ED8'),
(2, 1, 18, 1, 2450.00, '#FFFFFF'),
(3, 2, 15, 1, 1790.00, '#CED4DA'),
(4, 2, 9, 1, 1450.00, '#343A40'),
(5, 3, 23, 1, 6800.00, '#9F1239'),
(6, 4, 22, 1, 3450.00, '#1F2937'),
(7, 4, 10, 1, 2350.00, '#1F2937'),
(8, 5, 13, 1, 1350.00, '#212529'),
(9, 5, 7, 2, 450.00, '#1E3A8A'),
(10, 5, 1, 3, 320.00, ''),
(11, 6, 2, 3, 380.00, ''),
(12, 6, 20, 1, 3250.00, '#F8F9FA'),
(13, 7, 21, 1, 8950.00, '#212529'),
(14, 8, 16, 1, 4990.00, '#212529'),
(15, 8, 3, 1, 990.00, '#F8F9FA'),
(16, 9, 17, 2, 2450.00, '#FAF7EF'),
(17, 9, 5, 1, 1250.00, '#6F4330'),
(18, 10, 6, 1, 1650.00, '#7048E8'),
(19, 10, 8, 1, 1290.00, '#F8F9FA'),
(20, 10, 20, 1, 3250.00, '#F8F9FA'),
(21, 11, 24, 1, 8500.00, '#15803D'),
(22, 11, 12, 3, 1850.00, '#006A4E'),
(23, 11, 18, 1, 2450.00, '#FFFFFF'),
(24, 12, 23, 1, 6800.00, '#0F766E'),
(25, 12, 19, 1, 1850.00, ''),
(26, 13, 22, 1, 3450.00, '#F8FAFC'),
(27, 14, 4, 2, 2150.00, '#1F2937'),
(28, 15, 15, 1, 1790.00, '#212529'),
(29, 15, 2, 1, 380.00, ''),
(30, 16, 24, 1, 8500.00, '#DC2626'),
(31, 16, 12, 1, 1850.00, '#006A4E'),
(32, 17, 16, 1, 4990.00, '#ADB5BD'),
(33, 18, 17, 1, 2450.00, '#1E3A8A'),
(34, 18, 11, 1, 2890.00, '#1E3A8A'),
(35, 19, 21, 1, 8950.00, '#F8F9FA'),
(36, 20, 14, 1, 2190.00, '#1F2937'),
(37, 20, 8, 1, 1290.00, '#F8F9FA'),
(38, 21, 19, 1, 1850.00, ''),
(39, 21, 7, 1, 450.00, '#14532D'),
(40, 21, 1, 2, 320.00, ''),
(41, 22, 22, 1, 3450.00, '#3B5BDB'),
(42, 22, 6, 1, 1650.00, '#0EA5E9');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `color_variants` varchar(255) DEFAULT '',
  `image` varchar(255) DEFAULT '',
  `is_trending` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `color_variants`, `image`, `is_trending`, `created_at`) VALUES
(1, 5, 'Premium Gel Pen Set', 'A set of twelve smooth 0.5mm gel pens in bright, fade-resistant colours that are perfect for class notes, bullet journals and colour-coding during SSC and HSC revision. The quick-drying ink does not smudge on most exercise khata pages, and the soft rubber grip stays comfortable through long writing sessions. Each pen has a clear barrel so you can always see how much ink is left.', 320.00, 85, '', 'uploads/product_1795078238_2703.jpg', 0, '2026-05-03 04:07:00'),
(2, 6, 'Herbal Neem & Turmeric Face Wash (150ml)', 'A gentle daily face wash made with neem leaf extract and turmeric to clear away the sweat, dust and excess oil that build up on humid Dhaka days. The soap-free gel lathers lightly, rinses clean and does not leave your skin feeling tight or dry. Suitable for normal to oily skin and gentle enough to use twice a day.', 380.00, 70, '', 'uploads/product_1795078275_5928.jpg', 0, '2026-05-04 05:14:00'),
(3, 4, 'Rechargeable LED Emergency Light', 'Stay lit through load-shedding with this rechargeable emergency light featuring 24 bright LEDs and two brightness modes. A full charge runs for up to 6 hours on the low setting, enough to cover the longest evening power cuts. The sturdy carry handle and wall hook make it easy to use in the kitchen, at the reading table or on a shop counter.', 990.00, 55, '#F8F9FA', 'uploads/product_1795078308_4202.jpg', 0, '2026-05-05 06:21:00'),
(4, 1, 'Mini UPS for WiFi Router (12V)', 'Keep your WiFi router and ONU online during power cuts with this compact DC mini UPS. It switches over instantly when the electricity goes, so video calls, online classes and mobile banking are never interrupted. Supports both 12V and 9V routers, and a full charge gives around 4 to 5 hours of backup for most home connections.', 2150.00, 18, '#1F2937', 'uploads/product_1795078347_3534.jpg', 0, '2026-05-06 07:28:00'),
(5, 3, 'Genuine Leather Men\'s Wallet', 'A slim bi-fold wallet crafted from full-grain cowhide leather tanned and finished in Bangladesh. It has six card slots, two full-length note sections that hold taka notes without folding, and a hidden pocket for receipts. Neat stitching and hand-burnished edges help it age beautifully with everyday use.', 1250.00, 40, '#6F4330,#1F1F1F', 'uploads/product_1795078391_6785.jpg', 0, '2026-05-07 08:35:00'),
(6, 2, 'Anti-Slip TPE Yoga Mat (6mm)', 'A 6mm thick yoga and exercise mat made from eco-friendly TPE with a textured anti-slip surface on both sides. It cushions knees and elbows on hard tiled floors and keeps its grip even when you sweat through a hot afternoon workout. Comes with a carry strap and wipes clean with a damp cloth after every session.', 1650.00, 30, '#7048E8,#0EA5E9,#212529', 'uploads/product_1795078427_5139.jpg', 0, '2026-05-08 09:42:00'),
(7, 5, 'A5 Hardcover Dotted Journal (192 Pages)', 'A sturdy A5 hardcover notebook with 192 pages of thick 100gsm dotted paper that handles gel pens and fountain pens without bleeding through. Ideal for bullet journaling, lecture notes, sketching or planning your week. Includes an elastic closure band, a ribbon bookmark and a back pocket for loose papers.', 450.00, 60, '#1E3A8A,#14532D,#7F1D1D', 'uploads/product_1795078483_6967.jpg', 0, '2026-05-09 10:49:00'),
(8, 6, 'Non-Contact Infrared Thermometer', 'Check body temperature in about one second without touching, ideal for homes with small children and elderly parents. The backlit display changes colour when a fever is detected, and the last 32 readings are saved automatically. Runs on two AAA batteries and switches itself off to save power.', 1290.00, 9, '#F8F9FA', 'uploads/product_1795078194_7491.jpg', 0, '2026-05-10 11:56:00'),
(9, 4, 'Non-Stick Granite Frying Pan (26cm)', 'A 26cm frying pan with a durable granite-style non-stick coating that is PFOA free, so you can fry eggs, parathas and fish with very little oil. The thick base heats evenly on gas stoves and induction cooktops alike. The heat-resistant wood-finish handle stays comfortable to hold while you cook.', 1450.00, 22, '#343A40,#7C2D12', 'uploads/product_1795078146_6288.jpg', 0, '2026-05-11 03:03:00'),
(10, 1, '20000mAh Fast-Charging Power Bank (22.5W)', 'A 20000mAh power bank with 22.5W fast charging that tops up most smartphones around four times on a single charge. Two USB-A ports and one USB-C port let you charge a phone, earbuds and a smartwatch together. The LED indicator shows exactly how much power is left, so long bus journeys and load-shedding never catch you off guard.', 2350.00, 0, '#1F2937,#F8F9FA', 'uploads/product_1795078112_8617.jpg', 0, '2026-05-12 04:10:00'),
(11, 3, 'Canvas Low-Top Sneakers', 'Classic low-top sneakers with a breathable cotton canvas upper and a vulcanised rubber sole that grips well on wet monsoon streets. The cushioned insole keeps them comfortable through a full day at university or the office. Available in sizes 39 to 44, so please check the size chart before ordering.', 2890.00, 83, '#1E3A8A,#F8F9FA,#212529', 'uploads/product_1795078038_4371.jpg', 1, '2026-05-13 05:17:00'),
(12, 2, 'Official Bangladesh Home Jersey (Dry-Fit Edition)', 'The official home jersey in bottle green and red, made from lightweight dry-fit polyester that pulls sweat away from your body during matches and futsal sessions. The heat-pressed crest and number stay sharp wash after wash. A regular athletic fit that works just as well on the field as it does cheering from the stands.', 1850.00, 2, '#006A4E,#F42A41,#FFFFFF', 'uploads/product_1795077993_3336.jpg', 1, '2026-05-14 06:24:00'),
(13, 5, 'Scientific Calculator (417 Functions)', 'A dependable scientific calculator with 417 functions covering fractions, statistics, trigonometry and equations for SSC, HSC and university admission tests. The natural textbook display shows expressions exactly as they appear in your book. Dual solar and battery power keeps it working through the longest exam sessions.', 1350.00, 0, '#212529', 'uploads/product_1795077886_9600.jpg', 0, '2026-05-15 07:31:00'),
(14, 6, 'Rechargeable Beard & Hair Trimmer', 'A cordless trimmer with self-sharpening stainless steel blades and 20 length settings for a clean beard line or a quick trim at home. One hour of charging gives up to 90 minutes of use, and it also runs while plugged in. Comes with two guide combs, a cleaning brush and a USB charging cable.', 2190.00, 19, '#1F2937,#1C7ED6', 'uploads/product_1795077833_8515.jpg', 0, '2026-05-16 08:38:00'),
(15, 4, 'Stainless Steel Electric Kettle (1.8L)', 'A 1.8 litre electric kettle with a food-grade stainless steel interior that boils water for cha, coffee or instant noodles in about four minutes. Auto shut-off and boil-dry protection make it safe for rushed mornings. The 360-degree cordless base and clear water window make it easy to use from any side.', 1790.00, 34, '#CED4DA,#212529', 'uploads/product_1795077771_8983.jpg', 0, '2026-05-17 09:45:00'),
(16, 1, 'AMOLED Smartwatch with Bluetooth Calling', 'A 1.43-inch AMOLED smartwatch with an always-on display that stays bright and readable even under the afternoon sun. Take Bluetooth calls straight from your wrist and track steps, heart rate, sleep and more than 100 sports modes. IP68 water resistance and up to 7 days of battery life make it a reliable everyday companion.', 4990.00, 3, '#212529,#ADB5BD,#B76E79', 'uploads/product_1795077725_1800.jpg', 1, '2026-05-18 10:52:00'),
(17, 3, 'Premium Cotton Panjabi (Eid Collection)', 'A soft, breathable cotton panjabi with a mandarin collar and delicate thread embroidery along the placket, tailored for Eid, weddings and Jummah prayers. The fabric is pre-washed to minimise shrinkage and stays comfortable through long, humid days. Available in sizes 38 to 46 with a regular fit.', 2450.00, 16, '#FAF7EF,#1E3A8A,#7F1D1D', 'uploads/product_1795077656_8858.jpg', 1, '2026-05-19 11:59:00'),
(18, 2, 'FIFA-Quality Match Football (Size 5)', 'A size 5 thermo-bonded match football built to FIFA Quality standards for consistent flight, bounce and touch. The textured PU outer gives a reliable grip in both dry and rainy conditions, and the butyl bladder holds air for weeks. Ideal for club matches, school tournaments and weekend games at the local field.', 2450.00, 25, '#FFFFFF,#F08C00', 'uploads/product_1795077497_1723.jpg', 1, '2026-05-20 03:06:00'),
(19, 5, 'Bangla Classic Literature Box Set', 'A boxed collection of five much-loved Bangla classic novels and story collections, printed on quality paper with sewn hardcover binding. The sturdy slipcase keeps the books safe on your shelf and makes a thoughtful gift for students and readers of every age. A perfect starting point for anyone building a home library.', 1850.00, 12, '', 'uploads/product_1795077480_2821.jpg', 0, '2026-05-21 04:13:00'),
(20, 6, 'Digital Blood Pressure Monitor (Upper Arm)', 'A fully automatic upper-arm blood pressure monitor that measures systolic, diastolic and pulse rate at the press of a single button. The large backlit display is easy for elderly users to read, and the memory stores 2 x 90 readings for two family members. Includes a wide-range cuff, a carry pouch and a USB power option.', 3250.00, 14, '#F8F9FA', 'uploads/product_1795077332_9973.png', 0, '2026-05-22 05:20:00'),
(21, 4, 'Digital Air Fryer (5.5L)', 'Make crispy beguni, chicken fry and french fries with up to 85% less oil in this 5.5 litre digital air fryer. Eight one-touch presets and an adjustable temperature range of 80 to 200 degrees take the guesswork out of cooking. The non-stick basket is dishwasher safe and large enough for a family of four to five.', 8950.00, 6, '#212529,#F8F9FA', 'uploads/product_1795077241_5177.jpg', 0, '2026-05-23 06:27:00'),
(22, 1, 'ANC Wireless Earbuds Pro (Bluetooth 5.3)', 'True wireless earbuds with active noise cancellation that shuts out traffic, bus horns and office chatter so you can focus on music and calls. Bluetooth 5.3 keeps the connection stable, and four microphones with noise reduction keep your voice clear on the go. Enjoy up to 8 hours of playback per charge and 32 hours in total with the USB-C charging case.', 3450.00, 42, '#1F2937,#F8FAFC,#3B5BDB', 'uploads/product_1795077181_9140.jpg', 1, '2026-05-24 07:34:00'),
(23, 3, 'Jamdani-Inspired Half-Silk Saree', 'A graceful half-silk saree woven with Jamdani-inspired motifs and a rich zari border by weavers in Rupganj, Narayanganj. The lightweight fabric drapes beautifully and stays comfortable through holud nights, wedding receptions and Pohela Boishakh celebrations. Comes with an unstitched blouse piece, and dry cleaning is recommended to preserve the shine.', 6800.00, 3, '#9F1239,#0F766E,#B45309', 'uploads/product_1795077058_8237.jpg', 1, '2026-05-25 08:41:00'),
(24, 2, 'Grade-A English Willow Cricket Bat', 'A full-size Grade-A English willow bat with 8 to 10 straight grains, a thick edge and a mid-to-low sweet spot built for powerful drives. It comes pre-knocked and oiled with a cane handle that absorbs shock on mistimed shots. Pick your grip colour, and give it a few more hours of knocking-in before facing a hard leather ball.', 8500.00, 7, '#1D4ED8,#DC2626,#15803D', 'uploads/product_1795076986_7611.jpg', 0, '2026-05-26 09:48:00');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_name`, `rating`, `comment`, `created_at`) VALUES
(1, 24, 'Tanvir Ahmed', 5, 'Knocked it in for two evenings before taking it to the nets near Dhanmondi Lake, and the ping off the middle is superb. The grains are straight and the grip stays firm even when my hands get sweaty. Worth every taka.', '2026-06-12 14:14:00'),
(2, 18, 'Tanvir Ahmed', 4, 'Holds its shape well after weeks on the rough field in our para. The bounce is true and the stitching has not loosened at all. It took a day to get the pressure right, so keep a pump handy.', '2026-06-15 12:42:00'),
(3, 15, 'Mahfuzur Rahman', 5, 'Boils a full jug for the whole family\'s morning cha in about four minutes. The auto shut-off works every time and there is no plastic smell. Delivery to Agrabad took just two days.', '2026-06-28 02:05:00'),
(4, 9, 'Mahfuzur Rahman', 4, 'Eggs and parathas slide right off with very little oil, and it heats evenly on our gas stove. The handle gets slightly warm during long fries, but nothing serious.', '2026-07-02 07:30:00'),
(5, 23, 'Nusrat Jahan', 5, 'Wore it to my cousin\'s holud and got so many compliments. The motifs are fine and the half-silk drapes beautifully without feeling heavy. The colour matched the photos exactly.', '2026-07-12 16:18:00'),
(6, 22, 'Tahmid Hasan', 5, 'The noise cancellation makes the bus ride from GEC to Patenga actually peaceful. Battery easily lasts my whole work day and the case charges quickly over USB-C.', '2026-07-22 13:47:00'),
(7, 10, 'Tahmid Hasan', 4, 'Saved me during a long load-shedding last week and charged my phone almost four times. A bit heavy for the pocket, but the fast charging is real.', '2026-07-25 15:10:00'),
(8, 13, 'Nafis Sadik', 5, 'Got it for my HSC exams. Every function our teachers asked for is there and the display is very clear. The solar backup is a nice bonus.', '2026-08-04 10:25:00'),
(9, 7, 'Nafis Sadik', 4, 'Thick paper and my fountain pen ink does not bleed through. The dotted grid is light enough to ignore when I just want to write.', '2026-08-05 17:02:00'),
(10, 20, 'Sadia Islam', 5, 'Bought it for my father. The readings match what his doctor gets at the chamber, and the large display is easy for him to read without glasses.', '2026-08-16 04:40:00'),
(11, 2, 'Sadia Islam', 3, 'Gentle and does not dry out my skin, but the scent is a little strong for my taste. Works fine for daily use in this humid weather.', '2026-08-20 03:15:00'),
(12, 21, 'Sabrina Chowdhury', 5, 'Makes crispy beguni and chicken fry with barely a spoon of oil. The basket is easy to wash and the presets are simple enough for my mother to use.', '2026-08-24 14:33:00'),
(13, 16, 'Rakibul Islam', 4, 'The AMOLED screen is bright even under the afternoon sun and calls connect clearly through the watch. Step count is accurate. I wish the strap had a couple more holes.', '2026-09-06 06:50:00'),
(14, 3, 'Rakibul Islam', 5, 'Runs around six hours on a full charge, which covers even our longest power cuts. Bright enough to light up the whole drawing room.', '2026-09-07 15:45:00'),
(15, 17, 'Farhana Akter', 5, 'Ordered two for my husband and my brother before a wedding. Soft cotton, neat stitching on the placket and the sizes were spot on.', '2026-09-16 11:20:00'),
(16, 5, 'Farhana Akter', 5, 'The leather feels genuinely premium and the stitching is very neat. My husband likes that taka notes fit without folding.', '2026-09-17 05:05:00'),
(17, 8, 'Ayesha Siddiqua', 4, 'Quick, consistent readings that match our old digital thermometer. Very handy for checking the kids at night without waking them up.', '2026-09-26 16:55:00'),
(18, 6, 'Ayesha Siddiqua', 4, 'Good grip even when I sweat, and the 6mm cushion is comfortable on our tiled floor. It had a light rubber smell for the first two days.', '2026-09-28 01:40:00'),
(19, 12, 'Imran Hossain', 5, 'The fabric is breathable and dries fast after futsal. The crest and number feel premium and have not faded after several washes.', '2026-10-04 12:12:00'),
(20, 18, 'Imran Hossain', 5, 'A proper match feel, much better than the cheap balls from the local market. Our weekend team in Mirpur loves it.', '2026-10-06 13:30:00'),
(21, 23, 'Tasnim Ferdous', 3, 'The weave and colour are lovely, but the blouse piece was shorter than I expected. Delivery to Banani was quick and the packaging was neat.', '2026-10-14 09:48:00'),
(22, 19, 'Tasnim Ferdous', 5, 'Beautiful hardcover editions with clear printing, and the box keeps them safe on my shelf. A perfect gift for any boi lover.', '2026-10-15 15:22:00'),
(23, 22, 'Tanvir Ahmed', 4, 'Bought these after trying a friend\'s pair. The bass is punchy and calls stay clear even on busy Mirpur Road. The touch controls take a day to get used to.', '2026-10-20 07:58:00'),
(24, 4, 'Mahfuzur Rahman', 5, 'Keeps our WiFi router and ONU running for almost five hours when the power goes. Setup took two minutes. Already ordered another one for the office.', '2026-10-29 04:36:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(30) DEFAULT '',
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `phone`, `address`) VALUES
(1, 'Tanvir Ahmed', 'tanvir.ahmed@gmail.com', '$2y$10$nlqpK/q5k3Ska.2sx.cJPOCXa8mfeVmai.8KDenEuODLDlUbwShJO', '2026-01-14 04:22:05', '01712458963', 'House 27, Road 9/A, Dhanmondi, Dhaka 1209'),
(2, 'Nusrat Jahan', 'nusrat.jahan@gmail.com', '$2y$10$xXS4E6r4uJWC9znotaHRlONeu3.iHXY2yFIp8f6h/I1lF0V1wDCxu', '2026-01-27 12:40:12', '01819336247', 'Flat 5B, House 14, Road 7, Sector 4, Uttara, Dhaka 1230'),
(3, 'Nafis Sadik', 'nafis.sadik@gmail.com', '$2y$10$LJfGT1OCtYtnguNmi8llh.Cmewo32ku.NQLdyYnX/Er1Tw6iNq05e', '2026-02-09 15:15:47', '01915672384', 'House 112, Road 3, Block C, Mirpur 10, Dhaka 1216'),
(4, 'Sadia Islam', 'sadia.islam@yahoo.com', '$2y$10$NrVcAa8QOtxMDG9vnBm2vehRdGf8bGUQ0Wa1o8oalyE0XDxEVKAUe', '2026-02-18 07:05:33', '01611895430', 'House 45, Road 11, Banani, Dhaka 1213'),
(5, 'Mahfuzur Rahman', 'mahfuzur.rahman@gmail.com', '$2y$10$nC8sWDRSqVJyZ.R4hKe10uSDNNNwkkabrIJSGyjfHvlbh2CFs2SoG', '2026-02-26 03:48:20', '01818204576', 'House 6, Road 2, CDA Residential Area, Agrabad, Chattogram 4100'),
(6, 'Farhana Akter', 'farhana.akter@gmail.com', '$2y$10$VhxevAv5H9joM0nwo0aiCOifd1Xz0TJe5bIlwGSyVubBdaYHbukHK', '2026-03-08 10:30:02', '01734917268', 'House 8, Road 2, Nasirabad Housing Society, Chattogram 4209'),
(7, 'Tahmid Hasan', 'tahmid.hasan@outlook.com', '$2y$10$uextirtNyMxJ39GZSiZIluxXliAnyc.dh6paCIPQNwpNTheWRIbty', '2026-03-17 14:12:58', '01916448021', 'Flat 3A, Lane 4, O.R. Nizam Road, near GEC Circle, Chattogram 4000'),
(8, 'Sabrina Chowdhury', 'sabrina.chowdhury@gmail.com', '$2y$10$HMlg76hrEjyPsZpJG1w9VOwbyrD6d4SPSQ0A892MuYEJqfTcWtO3K', '2026-03-29 05:27:41', '01675209814', 'House 19, Lane 7, Block K, Halishahar Housing Estate, Chattogram 4216'),
(9, 'Rakibul Islam', 'rakibul.islam@gmail.com', '$2y$10$J8Zm5ozjOMAJLQdJDzfYDuUCAcME8nZjJtikAV.lWf7J4BY.yfwXy', '2026-04-06 16:03:16', '01722581397', 'House 63, Road 27 (Old), Dhanmondi, Dhaka 1209'),
(10, 'Ayesha Siddiqua', 'ayesha.siddiqua@gmail.com', '$2y$10$EZjmu69wF/m0.kCs60ORs.0OJPUZmXSqObM8Ft9uM18bdl7gVOFSG', '2026-04-15 02:55:29', '01817760352', 'House 9, Road 13, Sector 6, Uttara, Dhaka 1230'),
(11, 'Imran Hossain', 'imran.hossain@yahoo.com', '$2y$10$rFwa7MC2ku3ZV7KyVI6vduueQ55AqzF2dBo7NfmRtTpUxBe380XLG', '2026-04-24 13:36:44', '01993145768', 'House 21, Road 4, Section 2, Mirpur, Dhaka 1216'),
(12, 'Tasnim Ferdous', 'tasnim.ferdous@gmail.com', '$2y$10$yLIPuN7wxb99SWZgIAPWvOf8reQV2v9ip/m6nwiyiZGC3HT5LjhlO', '2026-05-03 08:18:07', '01638472915', 'House 31, Road 2, Block E, Banani, Dhaka 1213');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
