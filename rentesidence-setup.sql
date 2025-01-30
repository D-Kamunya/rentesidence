-- MySQL dump 10.13  Distrib 8.0.36, for Linux (x86_64)
--
-- Host: localhost    Database: realestate
-- ------------------------------------------------------
-- Server version	8.0.35-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `agreement_histories`
--

LOCK TABLES `agreement_histories` WRITE;
/*!40000 ALTER TABLE `agreement_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `agreement_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `banks`
--

LOCK TABLES `banks` WRITE;
/*!40000 ALTER TABLE `banks` DISABLE KEYS */;
/*!40000 ALTER TABLE `banks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `core_pages`
--

LOCK TABLES `core_pages` WRITE;
/*!40000 ALTER TABLE `core_pages` DISABLE KEYS */;
INSERT INTO `core_pages` VALUES (1,'Dashboard','You can track all the tenant’s  billing information here','You can send group reminder from here can create new invoice and can track tenant’s name, property, billing date,\r\nbilling type, amount, payment status.','Can track overdue billing information,\r\nCan track paid billing information,\r\nCan track pending billing information',1,'2023-04-03 01:51:29','2023-04-03 02:28:00',NULL),(2,'Packages','Packages','You can track all the tenant’s packages information here','The available packages for the ,\r\nactive target DBMS are listed in the ,\r\nObject Browser under Attribute packages.',1,'2023-04-03 02:31:04','2023-04-03 02:31:33',NULL);
/*!40000 ALTER TABLE `core_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES (1,'USD','$','before','off','2023-02-02 11:17:04','2024-06-12 06:17:02',NULL),(2,'BDT','৳','before','off','2023-02-02 11:17:04','2024-06-12 06:17:02',NULL),(3,'INR','₹','before','off','2023-02-02 11:17:04','2024-06-12 06:17:02',NULL),(4,'GBP','£','after','off','2023-02-02 11:17:04','2024-06-12 06:17:02',NULL),(5,'MXN','$','before','off','2023-02-02 11:17:04','2024-06-12 06:17:02',NULL),(6,'SAR','SR','before','off','2023-02-02 11:17:04','2024-06-12 06:17:02',NULL),(7,'KSH','KSH','before','on','2024-03-15 10:00:30','2024-06-12 06:17:02',NULL);
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `email_templates`
--

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `expense_types`
--

LOCK TABLES `expense_types` WRITE;
/*!40000 ALTER TABLE `expense_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `expense_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'What is a property appraisal?','A property appraisal is an assessment of the value of a property, typically conducted by a professional appraiser. The appraisal takes into account factors such as the property\'s location, condition, size, and amenities to determine its market value.',1,'2023-04-03 02:46:12','2023-04-03 02:46:12',NULL),(2,'What is a home inspection?','A home inspection is a thorough examination of a property\'s condition, typically conducted by a professional home inspector. The inspection covers structural, mechanical, and electrical systems, as well as other important components of the property, to id',1,'2023-04-03 02:46:32','2023-04-03 02:48:42','2023-04-03 02:48:42'),(3,'What is a down payment?','A down payment is the initial payment made by a buyer toward the purchase of a property, typically a percentage of the total purchase price. The down payment is made at the time of purchase and reduces the amount of financing needed to complete the purcha',1,'2023-04-03 02:46:48','2023-04-03 02:48:46','2023-04-03 02:48:46'),(4,'What is a property management app?','A property management app is a mobile application that helps property owners and managers manage their properties more efficiently. The app typically includes features such as rent collection, maintenance requests, tenant screening, and lease management.',1,'2023-04-03 02:48:22','2023-04-03 02:48:22',NULL),(5,'How can a property management app help me as a landlord?','A property management app can help landlords streamline their rental operations by providing tools to manage rent payments, track maintenance requests, screen tenants, and communicate with tenants more easily.',1,'2023-04-03 02:49:02','2023-04-03 02:49:02',NULL),(6,'What is a real estate investing app?','A real estate investing app is a mobile application that helps investors research, analyze, and track real estate investment opportunities. The app typically includes features such as property listings, financial analysis tools, and portfolio management.',1,'2023-04-03 02:49:20','2023-04-03 02:49:20',NULL),(7,': How can a real estate investing app help me as an investor?','A real estate investing app can help investors find and evaluate potential investment opportunities more efficiently, as well as track the performance of their investment portfolio.',1,'2023-04-03 02:49:37','2023-04-03 02:49:37',NULL),(8,'What is a home search app?','A home search app is a mobile application that helps users search for properties for sale or rent in a specific area. The app typically includes features such as property listings, search filters, and mapping tools.',1,'2023-04-03 02:49:53','2023-04-03 02:49:53',NULL),(9,'What is a property management software?','Property management software is a type of application designed to help property managers automate tasks related to managing rental properties, such as rent collection, maintenance requests, and tenant screening.',1,'2023-04-03 02:50:27','2023-04-03 02:50:27',NULL),(10,'What is a real estate CRM?','A real estate CRM (customer relationship management) software is an application designed to help real estate professionals manage relationships with clients and potential clients, track leads, and manage marketing campaigns.',1,'2023-04-03 02:51:02','2023-04-03 02:51:02',NULL),(11,'What is a real estate listing website?','A real estate listing website is an online platform that allows real estate agents and property owners to list properties for sale or rent, and for potential buyers or renters to search and browse listings.',1,'2023-04-03 02:51:18','2023-04-03 02:51:18',NULL);
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `features`
--

LOCK TABLES `features` WRITE;
/*!40000 ALTER TABLE `features` DISABLE KEYS */;
INSERT INTO `features` VALUES (1,'Automate rent collection','TTired of chasing down rent & late fees? With DoorLoop, you don’t have to. Get tenants to pay you automatically on the 1st of each month. Make more money and spend less.',1,'2023-04-03 01:20:49','2023-04-03 01:20:49',NULL),(2,'Track paid or overdue rent','Tired of chasing down rent & late fees? With DoorLoop, you don’t have to. Get tenants to pay you automatically on the 1st of each month. Make more money and spend less.',1,'2023-04-03 01:22:50','2023-04-03 01:22:50',NULL),(3,'Track paid or overdue rent','TTired of chasing down rent & late fees? With DoorLoop, you don’t have to. Get tenants to pay you automatically on the 1st of each month. Make more money and spend less.',1,'2023-04-03 01:23:03','2023-04-03 01:23:03',NULL),(4,'Track paid or overdue rent','TTired of chasing down rent & late fees? With DoorLoop, you don’t have to. Get tenants to pay you automatically on the 1st of each month. Make more money and spend less.',1,'2023-04-03 01:23:21','2023-04-03 01:23:21',NULL);
/*!40000 ALTER TABLE `features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `file_managers`
--

LOCK TABLES `file_managers` WRITE;
/*!40000 ALTER TABLE `file_managers` DISABLE KEYS */;
INSERT INTO `file_managers` VALUES (1,'files/Gateway','paypal.png',NULL,'App\\Models\\Gateway',1,NULL,NULL,NULL),(2,'files/Gateway','stripe.png',NULL,'App\\Models\\Gateway',2,NULL,NULL,NULL),(3,'files/Gateway','razorpay.png',NULL,'App\\Models\\Gateway',3,NULL,NULL,NULL),(4,'files/Gateway','instamojo.png',NULL,'App\\Models\\Gateway',4,NULL,NULL,NULL),(5,'files/Gateway','mollie.png',NULL,'App\\Models\\Gateway',5,NULL,NULL,NULL),(6,'files/Gateway','paystack.png',NULL,'App\\Models\\Gateway',6,NULL,NULL,NULL),(7,'files/Gateway','sslcommerz.png',NULL,'App\\Models\\Gateway',7,NULL,NULL,NULL),(8,'files/Gateway','flutterwave.png',NULL,'App\\Models\\Gateway',8,NULL,NULL,NULL),(9,'files/Gateway','mercadopago.png',NULL,'App\\Models\\Gateway',9,NULL,NULL,NULL),(10,'files/Gateway','bank.png',NULL,'App\\Models\\Gateway',10,NULL,NULL,NULL),(11,'files/Gateway','mpesa.png',NULL,'App\\Models\\Gateway',11,NULL,NULL,NULL);
/*!40000 ALTER TABLE `file_managers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gateway_currencies`
--

LOCK TABLES `gateway_currencies` WRITE;
/*!40000 ALTER TABLE `gateway_currencies` DISABLE KEYS */;
/*!40000 ALTER TABLE `gateway_currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gateways`
--

LOCK TABLES `gateways` WRITE;
/*!40000 ALTER TABLE `gateways` DISABLE KEYS */;
INSERT INTO `gateways` VALUES 
(1, 1, 'Cash', 'cash', 'assets/images/gateway-icon/cash.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(2, 1, 'Paypal', 'paypal', 'assets/images/gateway-icon/paypal.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(3, 1, 'Mpesa', 'mpesa', 'assets/images/gateway-icon/mpesa.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(4, 1, 'Stripe', 'stripe', 'assets/images/gateway-icon/stripe.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(5, 1, 'Razorpay', 'razorpay', 'assets/images/gateway-icon/razorpay.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(6, 1, 'Instamojo', 'instamojo', 'assets/images/gateway-icon/instamojo.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(7, 1, 'Mollie', 'mollie', 'assets/images/gateway-icon/mollie.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(8, 1, 'Paystack', 'paystack', 'assets/images/gateway-icon/paystack.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(9, 1, 'Sslcommerz', 'sslcommerz', 'assets/images/gateway-icon/sslcommerz.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(10, 1, 'Flutterwave', 'flutterwave', 'assets/images/gateway-icon/flutterwave.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(11, 1, 'Mercadopago', 'mercadopago', 'assets/images/gateway-icon/mercadopago.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(12, 1, 'Bank', 'bank', 'assets/images/gateway-icon/bank.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(13, 2, 'Cash', 'cash', 'assets/images/gateway-icon/cash.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(14, 2, 'Paypal', 'paypal', 'assets/images/gateway-icon/paypal.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(15, 2, 'Mpesa', 'mpesa', 'assets/images/gateway-icon/mpesa.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(16, 2, 'Stripe', 'stripe', 'assets/images/gateway-icon/stripe.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(17, 2, 'Razorpay', 'razorpay', 'assets/images/gateway-icon/razorpay.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(18, 2, 'Instamojo', 'instamojo', 'assets/images/gateway-icon/instamojo.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(19, 2, 'Mollie', 'mollie', 'assets/images/gateway-icon/mollie.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(20, 2, 'Paystack', 'paystack', 'assets/images/gateway-icon/paystack.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(21, 2, 'Sslcommerz', 'sslcommerz', 'assets/images/gateway-icon/sslcommerz.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(22, 2, 'Flutterwave', 'flutterwave', 'assets/images/gateway-icon/flutterwave.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(23, 2, 'Mercadopago', 'mercadopago', 'assets/images/gateway-icon/mercadopago.jpg', 0, 2, '', '', '', NULL, NULL, NULL),
(24, 2, 'Bank', 'bank', 'assets/images/gateway-icon/bank.jpg', 0, 2, '', '', '', NULL, NULL, NULL);

/*!40000 ALTER TABLE `gateways` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `how_it_works`
--

LOCK TABLES `how_it_works` WRITE;
/*!40000 ALTER TABLE `how_it_works` DISABLE KEYS */;
INSERT INTO `how_it_works` VALUES (1,'Accounting  for non-accountants','Simplify your business and get all the features you need with one software, one login, and no add-ons.','Real time reporting,\r\nConnect with any bank and sync with QuickBooks,\r\nCustomizable chart of accounts',1,'2023-04-03 01:38:07','2023-04-03 01:40:57',NULL),(2,'Handle maintenance requests & vendors','Keep your tenants & vendors happy & make sure nothing falls through the cracks with an easy-to-use online portal.','Get maintenance requests online,\r\nAssign & track work orders & issue 1099 forms,\r\nAutomatically mail checks or wire money to vendors',1,'2023-04-03 01:42:31','2023-04-03 01:42:31',NULL),(3,'Market your listings online and get  acustom website','Find new tenants or owners faster, fill your vacancies in record time, screen tenants Sign lease agreements online.','Market your properties on Zillow Trulia Hotpads & more,\r\nReceive electronic rental applications & background checks,\r\nCBuild your own custom website',1,'2023-04-03 01:44:21','2023-04-03 01:46:07',NULL),(4,'Keep your  ownershappy with more transparency','Keep your owners and investors happy with an owner portal, reports, distributions, and ultimate transparency.','Share documents securely,\r\nFinancial transparency,\r\nUser friendly communication between you and the owners',1,'2023-04-03 01:47:35','2023-04-03 01:47:35',NULL);
/*!40000 ALTER TABLE `how_it_works` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `information`
--

LOCK TABLES `information` WRITE;
/*!40000 ALTER TABLE `information` DISABLE KEYS */;
/*!40000 ALTER TABLE `information` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `invoice_recurring_setting_items`
--

LOCK TABLES `invoice_recurring_setting_items` WRITE;
/*!40000 ALTER TABLE `invoice_recurring_setting_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_recurring_setting_items` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `invoice_recurring_settings`
--

LOCK TABLES `invoice_recurring_settings` WRITE;
/*!40000 ALTER TABLE `invoice_recurring_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_recurring_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `invoice_types`
--

LOCK TABLES `invoice_types` WRITE;
/*!40000 ALTER TABLE `invoice_types` DISABLE KEYS */;
INSERT INTO `invoice_types` VALUES (1,2,'Rent',0.000,1,'2024-03-28 09:55:34','2024-04-01 04:06:42',NULL);
/*!40000 ALTER TABLE `invoice_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `kyc_configs`
--

LOCK TABLES `kyc_configs` WRITE;
/*!40000 ALTER TABLE `kyc_configs` DISABLE KEYS */;
/*!40000 ALTER TABLE `kyc_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `kyc_verifications`
--

LOCK TABLES `kyc_verifications` WRITE;
/*!40000 ALTER TABLE `kyc_verifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `kyc_verifications` ENABLE KEYS */;
UNLOCK TABLES;


LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'English','en',NULL,NULL,0,1,1,'2023-02-02 11:17:04','2024-06-12 06:17:02',NULL);
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `listing_contacts`
--

LOCK TABLES `listing_contacts` WRITE;
/*!40000 ALTER TABLE `listing_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_contacts` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `listing_images`
--

LOCK TABLES `listing_images` WRITE;
/*!40000 ALTER TABLE `listing_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_images` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `listing_information`
--

LOCK TABLES `listing_information` WRITE;
/*!40000 ALTER TABLE `listing_information` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_information` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `listings`
--

LOCK TABLES `listings` WRITE;
/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
/*!40000 ALTER TABLE `listings` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `mail_histories`
--

LOCK TABLES `mail_histories` WRITE;
/*!40000 ALTER TABLE `mail_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail_histories` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `maintainers`
--

LOCK TABLES `maintainers` WRITE;
/*!40000 ALTER TABLE `maintainers` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintainers` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `maintenance_issues`
--

LOCK TABLES `maintenance_issues` WRITE;
/*!40000 ALTER TABLE `maintenance_issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_issues` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `maintenance_requests`
--

LOCK TABLES `maintenance_requests` WRITE;
/*!40000 ALTER TABLE `maintenance_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_requests` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `metas`
--

LOCK TABLES `metas` WRITE;
/*!40000 ALTER TABLE `metas` DISABLE KEYS */;
/*!40000 ALTER TABLE `metas` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `mpesa_accounts`
--

LOCK TABLES `mpesa_accounts` WRITE;
/*!40000 ALTER TABLE `mpesa_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpesa_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `notice_boards`
--

LOCK TABLES `notice_boards` WRITE;
/*!40000 ALTER TABLE `notice_boards` DISABLE KEYS */;
/*!40000 ALTER TABLE `notice_boards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `notice_user`
--

LOCK TABLES `notice_user` WRITE;
/*!40000 ALTER TABLE `notice_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `notice_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `oauth_access_tokens`
--

LOCK TABLES `oauth_access_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `oauth_auth_codes`
--

LOCK TABLES `oauth_auth_codes` WRITE;
/*!40000 ALTER TABLE `oauth_auth_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_auth_codes` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `oauth_clients`
--

LOCK TABLES `oauth_clients` WRITE;
/*!40000 ALTER TABLE `oauth_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `oauth_personal_access_clients`
--

LOCK TABLES `oauth_personal_access_clients` WRITE;
/*!40000 ALTER TABLE `oauth_personal_access_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_personal_access_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `oauth_refresh_tokens`
--

LOCK TABLES `oauth_refresh_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `owner_packages`
--

LOCK TABLES `owner_packages` WRITE;
/*!40000 ALTER TABLE `owner_packages` DISABLE KEYS */;
INSERT INTO `owner_packages` VALUES (1,2,1,'Trial',-1,0,50,0,-1,-1,1,1,0.00,0.00,0.00,0.00,'2024-03-02 10:00:47','3024-04-01 10:00:47','',1,1,'2024-03-02 07:00:47','2024-03-02 07:02:55',NULL,2,50);
/*!40000 ALTER TABLE `owner_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `owners`
--

LOCK TABLES `owners` WRITE;
/*!40000 ALTER TABLE `owners` DISABLE KEYS */;
INSERT INTO `owners` VALUES (1,2,1,'2023-03-27 12:21:38','2024-04-05 07:50:49',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `owners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
INSERT INTO `packages` VALUES (1,'Trial','Trial',-1,0,50,0,-1,-1,1,1,0.00,0.00,0.00,0.00,2,1,0,1,'2023-03-28 05:44:44','2024-03-14 08:25:58',NULL);
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payment_check`
--

LOCK TABLES `payment_check` WRITE;
/*!40000 ALTER TABLE `payment_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `properties`
--

LOCK TABLES `properties` WRITE;
/*!40000 ALTER TABLE `properties` DISABLE KEYS */;
/*!40000 ALTER TABLE `properties` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `property_details`
--

LOCK TABLES `property_details` WRITE;
/*!40000 ALTER TABLE `property_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `property_details` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `property_images`
--

LOCK TABLES `property_images` WRITE;
/*!40000 ALTER TABLE `property_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `property_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `property_units`
--

LOCK TABLES `property_units` WRITE;
/*!40000 ALTER TABLE `property_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `property_units` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'build_version','18',NULL,'2023-02-02 08:34:12','2023-02-02 08:34:12',NULL),(2,'current_version','3.5',NULL,'2023-02-02 08:34:12','2023-02-02 08:34:12',NULL),(3,'app_name','Rentesidence',NULL,'2023-02-02 14:51:06','2024-04-05 06:49:21',NULL),(4,'app_email','admin@zaiproty.com',NULL,'2023-02-02 14:51:06','2023-02-04 06:45:55',NULL),(5,'app_contact_number','0705075111',NULL,'2023-02-02 14:51:06','2024-04-05 06:49:21',NULL),(6,'app_location','Kenya',NULL,'2023-02-02 14:51:06','2024-04-05 06:49:21',NULL),(7,'app_copyright','Developed by KInc',NULL,'2023-02-02 14:51:06','2024-04-05 06:49:58',NULL),(8,'app_developed_by','Kinc',NULL,'2023-02-02 14:51:06','2024-04-05 06:49:58',NULL),(9,'currency_id','7',NULL,'2023-02-02 14:51:06','2024-03-21 07:13:41',NULL),(10,'language_id','1',NULL,'2023-02-02 14:51:06','2023-02-02 14:51:06',NULL),(11,'app_preloader_status','1',NULL,'2023-02-02 14:51:06','2024-04-03 04:13:13',NULL),(12,'sign_in_text_title','You are signing in Ziaproty',NULL,'2023-02-02 14:51:06','2023-02-04 06:45:55',NULL),(13,'sign_in_text_subtitle','You are signing in Ziaproty',NULL,'2023-02-02 14:51:06','2023-02-04 06:45:55',NULL),(14,'meta_keyword','Zaiproty',NULL,'2023-02-02 14:51:06','2023-02-04 06:45:55',NULL),(15,'meta_author','Zaiproty',NULL,'2023-02-02 14:51:06','2023-02-04 06:45:55',NULL),(16,'revisit',NULL,NULL,'2023-02-02 14:51:06','2023-02-02 14:51:06',NULL),(17,'sitemap_link',NULL,NULL,'2023-02-02 14:51:06','2023-02-02 14:51:06',NULL),(18,'meta_description','Zaiproty',NULL,'2023-02-02 14:51:06','2023-02-04 06:45:55',NULL),(23,'website_primary_color','#3686FC',NULL,'2023-02-04 06:46:20','2023-02-06 04:08:05',NULL),(24,'website_secondary_color','#8253FB',NULL,'2023-02-04 06:46:20','2023-02-06 04:08:14',NULL),(25,'button_primary_color','#3686FC',NULL,'2023-02-04 06:46:20','2023-02-06 04:08:21',NULL),(26,'button_hover_color','#0063E6',NULL,'2023-02-04 06:46:20','2023-02-06 04:08:26',NULL),(27,'website_color_mode','0',NULL,'2023-02-04 06:46:20','2023-02-06 04:08:05',NULL),(28,'gateway_settings','{\n        \"paypal\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Client ID\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret\", \"name\": \"secret\", \"is_show\": 1}],\n        \"stripe\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Public Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret Key\", \"name\": \"secret\", \"is_show\": 0}],\n        \"razorpay\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret\", \"name\": \"secret\", \"is_show\": 1}],\n        \"instamojo\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Api Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Auth Token\", \"name\": \"secret\", \"is_show\": 1}],\n        \"mollie\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Mollie Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret\", \"name\": \"secret\", \"is_show\": 0}],\n        \"paystack\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Public Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret Key\", \"name\": \"secret\", \"is_show\": 0}],\n        \"mercadopago\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Client ID\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Client Secret\", \"name\": \"secret\", \"is_show\": 1}],\n        \"sslcommerz\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Store ID\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Store Password\", \"name\": \"secret\", \"is_show\": 1}],\n        \"flutterwave\": [{\"label\": \"Hash\", \"name\": \"url\", \"is_show\": 1}, {\"label\": \"Public Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Client Secret\", \"name\": \"secret\", \"is_show\": 1}],\n        \"coinbase\": [{\"label\": \"Hash\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"API Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Client Secret\", \"name\": \"secret\", \"is_show\": 0}],\n        \"mpesa\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"API Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Public Key\", \"name\": \"public_key\", \"is_show\": 1}, {\"label\": \"Secret\", \"name\": \"secret\", \"is_show\": 1}]\n    }',NULL,'2023-02-22 17:49:54','2023-02-22 17:49:54',NULL),(29,'gateway_settings','{\n        \"paypal\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Client ID\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret\", \"name\": \"secret\", \"is_show\": 1}],\n        \"stripe\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Public Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret Key\", \"name\": \"secret\", \"is_show\": 0}],\n        \"razorpay\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret\", \"name\": \"secret\", \"is_show\": 1}],\n        \"instamojo\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Api Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Auth Token\", \"name\": \"secret\", \"is_show\": 1}],\n        \"mollie\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Mollie Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret\", \"name\": \"secret\", \"is_show\": 0}],\n        \"paystack\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Public Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Secret Key\", \"name\": \"secret\", \"is_show\": 0}],\n        \"mercadopago\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Client ID\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Client Secret\", \"name\": \"secret\", \"is_show\": 1}],\n        \"sslcommerz\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"Store ID\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Store Password\", \"name\": \"secret\", \"is_show\": 1}],\n        \"flutterwave\": [{\"label\": \"Hash\", \"name\": \"url\", \"is_show\": 1}, {\"label\": \"Public Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Client Secret\", \"name\": \"secret\", \"is_show\": 1}],\n        \"coinbase\": [{\"label\": \"Hash\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"API Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Client Secret\", \"name\": \"secret\", \"is_show\": 0}],\n        \"mpesa\": [{\"label\": \"Url\", \"name\": \"url\", \"is_show\": 0}, {\"label\": \"API Key\", \"name\": \"key\", \"is_show\": 1}, {\"label\": \"Public Key\", \"name\": \"public_key\", \"is_show\": 1}, {\"label\": \"Secret\", \"name\": \"secret\", \"is_show\": 1}]\n    }',NULL,'2023-02-22 17:49:54','2023-02-22 17:49:54',NULL),(30,'PROTYSAAS_build_version','9',NULL,'2024-02-29 08:32:04','2024-02-29 08:32:11',NULL),(31,'PROTYSAAS_current_version','1.7',NULL,'2024-02-29 08:32:04','2024-02-29 08:32:11',NULL),(34,'PROTYAGREEMENT_build_version','2',NULL,'2024-02-29 08:47:17','2024-02-29 08:47:17',NULL),(35,'PROTYAGREEMENT_current_version','1.1',NULL,'2024-02-29 08:47:17','2024-02-29 08:47:17',NULL),(38,'PROTYSMS_build_version','3',NULL,'2023-02-02 08:34:12','2024-02-29 08:52:55',NULL),(39,'PROTYSMS_current_version','1.2',NULL,'2023-06-02 08:34:12','2023-06-02 08:34:12',NULL),(40,'PROTYLISTING_build_version','1',NULL,'2024-02-29 08:57:20','2024-02-29 08:57:20',NULL),(41,'PROTYLISTING_current_version',NULL,NULL,'2024-02-29 08:57:20','2024-02-29 08:57:20',NULL),(42,'app_card_data_show','1',NULL,'2024-03-02 08:15:26','2024-03-02 08:15:26',NULL),(43,'app_preloader','12',NULL,'2024-03-02 08:15:26','2024-03-02 08:15:26',NULL),(45,'app_logo','18',NULL,'2024-03-11 09:03:17','2024-03-11 09:03:17',NULL),(46,'app_logo_white','19',NULL,'2024-03-12 02:31:54','2024-03-12 02:31:54',NULL),(47,'app_fav_icon','20',NULL,'2024-03-12 02:31:54','2024-03-12 02:31:54',NULL),(48,'LISTING_STATUS','0',NULL,'2024-03-19 09:20:35','2024-03-19 09:23:55',NULL),(49,'TWILIO_STATUS','0',NULL,'2024-03-20 04:01:58','2024-03-20 04:01:58',NULL),(50,'TWILIO_ACCOUNT_SID',NULL,NULL,'2024-03-20 04:01:58','2024-03-20 04:01:58',NULL),(51,'TWILIO_AUTH_TOKEN',NULL,NULL,'2024-03-20 04:01:58','2024-03-20 04:01:58',NULL),(52,'TWILIO_PHONE_NUMBER',NULL,NULL,'2024-03-20 04:01:58','2024-03-20 04:01:58',NULL),(53,'send_email_status','1',NULL,'2024-03-20 04:01:58','2024-03-21 10:22:18',NULL),(54,'email_verification_status','1',NULL,'2024-03-20 04:01:58','2024-03-21 10:22:18',NULL),(55,'frontend_status','1',NULL,'2024-04-05 06:49:58','2024-04-05 06:49:58',NULL),(56,'trail_duration','30',NULL,'2024-04-05 06:49:58','2024-04-05 06:49:58',NULL),(57,'app_footer_text',NULL,NULL,'2024-04-05 06:49:58','2024-04-05 06:49:58',NULL),(58,'facebook_url',NULL,NULL,'2024-04-05 06:49:58','2024-04-05 06:49:58',NULL),(59,'twitter_url',NULL,NULL,'2024-04-05 06:49:58','2024-04-05 06:49:58',NULL),(60,'linkedin_url',NULL,NULL,'2024-04-05 06:49:58','2024-04-05 06:49:58',NULL),(61,'skype_url',NULL,NULL,'2024-04-05 06:49:58','2024-04-05 06:49:58',NULL),(62,'ADVANTA_STATUS',1,NULL,'2024-03-19 09:20:35','2024-03-19 09:23:55',NULL),(63,'ADVANTA_API_KEY','e471ffd953aa7ed5e736df70104f5daf',NULL,'2024-03-19 09:20:35','2024-03-19 09:23:55',NULL),(64,'ADVANTA_PARTNER_ID','2871',NULL,'2024-03-19 09:20:35','2024-03-19 09:23:55',NULL),(65,'ADVANTA_SHORT_CODE','CRYLAC',NULL,'2024-03-19 09:20:35','2024-03-19 09:23:55',NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `sms_histories`
--

LOCK TABLES `sms_histories` WRITE;
/*!40000 ALTER TABLE `sms_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_histories` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `subscription_orders`
--

LOCK TABLES `subscription_orders` WRITE;
/*!40000 ALTER TABLE `subscription_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tax_settings`
--

LOCK TABLES `tax_settings` WRITE;
/*!40000 ALTER TABLE `tax_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `tax_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tenancies`
--

LOCK TABLES `tenancies` WRITE;
/*!40000 ALTER TABLE `tenancies` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenancies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tenant_details`
--

LOCK TABLES `tenant_details` WRITE;
/*!40000 ALTER TABLE `tenant_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'Johnson Hernandez','Businessman','have tried a few AI copywriting apps but so far the best output amet have been on Zaiwriteai If you need to create quality content  quickly, I would thoroughly recommend Zaiwriteai literally lorem sity speechless from the originality of the content',4,1,'2023-04-03 02:08:04','2023-04-03 02:16:41',NULL),(2,'Angelina  Cali','Teacher','have tried a few AI copywriting apps but so far the best output amet have been on Zaiwriteai If you need to create quality content  quickly, I would thoroughly recommend Zaiwriteai literally lorem sity speechless from the originality of the content',5,1,'2023-04-03 02:08:08','2023-04-03 02:15:37',NULL),(3,'Indiana Jasper','Web developer','A property application is a digital platform that provides a range of services related to buying, selling, renting, or managing real estate properties. These applications typically allow users to search for properties based on location, price range, and o',5,1,'2023-04-03 02:19:20','2023-04-03 02:19:20',NULL),(4,'Linda Macy','Software Engineering','A property application is a mobile or web-based platform that helps users find, rent, or buy properties. These applications typically offer a range of features such as property search filters, real-time property availability updates, virtual property tour',4,1,'2023-04-03 02:21:07','2023-04-03 02:21:07',NULL),(5,'Winona . Zuri','Software engineering?','A property is a physical or intangible asset that a person or organization owns. Property can include land, buildings, vehicles, stocks, patents, and other valuable assets. Owning property can provide financial benefits, such as rental income, capital gai',4,1,'2023-04-03 02:35:52','2023-04-03 02:35:52',NULL);
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `ticket_replies`
--

LOCK TABLES `ticket_replies` WRITE;
/*!40000 ALTER TABLE `ticket_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `ticket_topics`
--

LOCK TABLES `ticket_topics` WRITE;
/*!40000 ALTER TABLE `ticket_topics` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Mr','Admin','admin@gmail.com','2023-02-02 14:34:12','$2y$10$QJ79PGQOQgVetj9TVY/ow.qPyGm7nFD9N91y4Tl6pJy50C9wIFa/i','01951973806',1,NULL,4,NULL,'syzmR2LtTfSsDBuSvDcmnFz69U2KBAq51VvfZ3eqFfAMjJdEJqcMVxLsfzei',NULL,NULL,NULL,NULL,NULL,'2023-02-04 10:54:59',NULL,NULL),(2,'Mr','Owner','owner@gmail.com','2023-02-02 14:34:12','$2y$10$QJ79PGQOQgVetj9TVY/ow.qPyGm7nFD9N91y4Tl6pJy50C9wIFa/i','01952973806',1,NULL,1,2,'XiLMlT3CLCwGmMEqvJ2c0kjLBL5IUenmLcvhGoufxKhOigED9EexdhIKOX9y',NULL,NULL,NULL,NULL,NULL,'2024-03-25 16:30:58',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-06-12 12:24:31
