-- MariaDB dump 10.19  Distrib 10.5.12-MariaDB, for debian-linux-gnueabihf (armv7l)
--
-- Host: localhost    Database: maxair
-- ------------------------------------------------------
-- Server version	10.5.12-MariaDB-0+deb11u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Temporary table structure for view `add_on_log_view`
--

DROP TABLE IF EXISTS `add_on_log_view`;
/*!50001 DROP VIEW IF EXISTS `add_on_log_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `add_on_log_view` (
  `id` tinyint NOT NULL,
  `sync` tinyint NOT NULL,
  `zone_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `start_datetime` tinyint NOT NULL,
  `stop_datetime` tinyint NOT NULL,
  `expected_end_date_time` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `add_on_logs`
--

DROP TABLE IF EXISTS `add_on_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_on_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11) DEFAULT NULL,
  `start_datetime` timestamp NULL DEFAULT NULL,
  `start_cause` char(50) COLLATE utf16_bin DEFAULT NULL,
  `stop_datetime` timestamp NULL DEFAULT NULL,
  `stop_cause` char(50) COLLATE utf16_bin DEFAULT NULL,
  `expected_end_date_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `add_on_logs`
--

LOCK TABLES `add_on_logs` WRITE;
/*!40000 ALTER TABLE `add_on_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `add_on_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `away`
--

DROP TABLE IF EXISTS `away`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `away` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start_datetime` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `end_datetime` timestamp NULL DEFAULT NULL,
  `away_button_id` int(11) DEFAULT NULL,
  `away_button_child_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `away`
--

LOCK TABLES `away` WRITE;
/*!40000 ALTER TABLE `away` DISABLE KEYS */;
INSERT INTO `away` VALUES (2,0,0,0,'2021-11-23 13:43:24','2021-11-23 13:43:24',0,0);
/*!40000 ALTER TABLE `away` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boost`
--

DROP TABLE IF EXISTS `boost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `temperature` tinyint(4) DEFAULT NULL,
  `minute` tinyint(4) DEFAULT NULL,
  `boost_button_id` int(11) DEFAULT NULL,
  `boost_button_child_id` int(11) DEFAULT NULL,
  `hvac_mode` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_boost_zone` (`zone_id`),
  CONSTRAINT `FK_boost_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boost`
--

LOCK TABLES `boost` WRITE;
/*!40000 ALTER TABLE `boost` DISABLE KEYS */;
INSERT INTO `boost` VALUES (19,0,0,0,38,'2021-11-23 13:54:41',25,60,0,0,0),(20,0,0,0,39,'2021-11-23 13:55:35',45,60,0,0,0);
/*!40000 ALTER TABLE `boost` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `button_page`
--

DROP TABLE IF EXISTS `button_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `button_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` char(50) COLLATE utf16_bin DEFAULT NULL,
  `function` char(50) COLLATE utf16_bin DEFAULT NULL,
  `index_id` tinyint(4) DEFAULT NULL,
  `page` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `button_page`
--

LOCK TABLES `button_page` WRITE;
/*!40000 ALTER TABLE `button_page` DISABLE KEYS */;
INSERT INTO `button_page` VALUES (1,0,0,'Boost','boost',1,2),(2,0,0,'Override','override',2,2),(3,0,0,'Night Climate','night_climate',3,2),(4,0,0,'Away','away',4,2),(5,0,0,'Holidays','holidays',5,2);
/*!40000 ALTER TABLE `button_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `controller_zone_logs`
--

DROP TABLE IF EXISTS `controller_zone_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `controller_zone_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11) NOT NULL,
  `start_datetime` timestamp NULL DEFAULT NULL,
  `start_cause` char(50) COLLATE utf16_bin DEFAULT NULL,
  `stop_datetime` timestamp NULL DEFAULT NULL,
  `stop_cause` char(50) COLLATE utf16_bin DEFAULT NULL,
  `expected_end_date_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `controller_zone_logs`
--

LOCK TABLES `controller_zone_logs` WRITE;
/*!40000 ALTER TABLE `controller_zone_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `controller_zone_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_backup`
--

DROP TABLE IF EXISTS `database_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database_backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `backup_name` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_backup`
--

LOCK TABLES `database_backup` WRITE;
/*!40000 ALTER TABLE `database_backup` DISABLE KEYS */;
INSERT INTO `database_backup` VALUES (2,0,0,0,'130821.sql','130821.sql'),(3,0,0,0,'170821.sql','170821.sql'),(4,0,0,0,'180821.sql','180821.sql'),(5,0,0,0,'270821.sql','270821.sql'),(6,0,0,0,'300821.sql','300821.sql'),(7,0,0,0,'060921.sql','060921.sql'),(8,0,0,0,'110921.sql','110921.sql'),(9,0,0,0,'180921.sql','180921.sql'),(10,0,0,0,'200921.sql','200921.sql'),(11,0,0,0,'250921.sql','250921.sql'),(12,0,0,0,'280921.sql','280921.sql'),(13,0,0,0,'031021.sql','031021.sql'),(14,0,0,0,'191021.sql','191021.sql'),(15,0,0,0,'211021.sql','211021.sql'),(16,0,0,0,'251021.sql','251021.sql'),(17,0,0,0,'261021.sql','261021.sql'),(18,0,0,0,'281021.sql','281021.sql'),(19,0,0,0,'301021.sql','301021.sql'),(20,0,0,0,'311021.sql','311021.sql'),(21,0,0,0,'061121.sql','061121.sql'),(22,0,0,0,'151121.sql','151121.sql'),(23,0,0,0,'211121.sql','211121.sql');
/*!40000 ALTER TABLE `database_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `db_cleanup`
--

DROP TABLE IF EXISTS `db_cleanup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_cleanup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `messages_in` char(50) COLLATE utf16_bin DEFAULT NULL,
  `nodes_battery` char(50) COLLATE utf16_bin DEFAULT NULL,
  `gateway_logs` char(50) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_cleanup`
--

LOCK TABLES `db_cleanup` WRITE;
/*!40000 ALTER TABLE `db_cleanup` DISABLE KEYS */;
INSERT INTO `db_cleanup` VALUES (1,0,0,'3 DAY','3 MONTH','3 DAY');
/*!40000 ALTER TABLE `db_cleanup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email`
--

DROP TABLE IF EXISTS `email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `smtp` char(50) COLLATE utf16_bin DEFAULT NULL,
  `port` int(11) NOT NULL,
  `username` char(50) COLLATE utf16_bin DEFAULT NULL,
  `password` char(50) COLLATE utf16_bin DEFAULT NULL,
  `from` char(50) COLLATE utf16_bin DEFAULT NULL,
  `to` char(50) COLLATE utf16_bin DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email`
--

LOCK TABLES `email` WRITE;
/*!40000 ALTER TABLE `email` DISABLE KEYS */;
/*!40000 ALTER TABLE `email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gateway`
--

DROP TABLE IF EXISTS `gateway`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gateway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf16_bin DEFAULT NULL COMMENT 'virtual, serial or wifi',
  `location` char(50) COLLATE utf16_bin NOT NULL COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) COLLATE utf16_bin NOT NULL COMMENT 'port number 5003 or baud rate115200 for serial gateway',
  `timout` char(50) COLLATE utf16_bin NOT NULL,
  `pid` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_running_since` char(50) COLLATE utf16_bin DEFAULT NULL,
  `reboot` tinyint(4) DEFAULT NULL,
  `find_gw` tinyint(4) DEFAULT NULL,
  `version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `enable_outgoing` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gateway`
--

LOCK TABLES `gateway` WRITE;
/*!40000 ALTER TABLE `gateway` DISABLE KEYS */;
INSERT INTO `gateway` VALUES (1,1,0,0,'serial','/dev/ttyAMA0','115200','3','3077','Tue Nov 23 13:43:25 2021',0,0,'1.0',1);
/*!40000 ALTER TABLE `gateway` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gateway_logs`
--

DROP TABLE IF EXISTS `gateway_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gateway_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf16_bin DEFAULT NULL COMMENT 'serial or wifi',
  `location` char(50) COLLATE utf16_bin DEFAULT NULL COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) COLLATE utf16_bin DEFAULT NULL COMMENT 'port number or baud rate for serial gateway',
  `pid` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_start_time` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_datetime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gateway_logs`
--

LOCK TABLES `gateway_logs` WRITE;
/*!40000 ALTER TABLE `gateway_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `gateway_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graphs`
--

DROP TABLE IF EXISTS `graphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `mask` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graphs`
--

LOCK TABLES `graphs` WRITE;
/*!40000 ALTER TABLE `graphs` DISABLE KEYS */;
INSERT INTO `graphs` VALUES (1,0,0,57);
/*!40000 ALTER TABLE `graphs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (1,0,0,0,'2021-11-23 13:54:41','2021-11-23 13:54:41');
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `http_messages`
--

DROP TABLE IF EXISTS `http_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `http_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_name` char(50) COLLATE utf16_bin DEFAULT NULL,
  `node_id` char(50) COLLATE utf16_bin DEFAULT NULL,
  `message_type` char(50) COLLATE utf16_bin DEFAULT NULL,
  `command` char(50) COLLATE utf16_bin DEFAULT NULL,
  `parameter` char(50) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `http_messages`
--

LOCK TABLES `http_messages` WRITE;
/*!40000 ALTER TABLE `http_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `http_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` char(50) NOT NULL,
  `script` char(100) NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `log_it` tinyint(1) DEFAULT NULL,
  `time` char(50) NOT NULL,
  `output` text NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'controller','/var/www/cron/controller.php',1,0,'60','[36m\n           __  __                             _         \n          |  \\/  |                    /\\     (_)        \n          | \\  / |   __ _  __  __    /  \\     _   _ __  \n          | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n          | |  | | | (_| |  >  <   / ____ \\  | | | |    \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n                [45m S M A R T   T H E R M O S T A T [0m \n[31m \n******************************************************************\n*   System Controller Script Version 0.01 Build Date 19/10/2020  *\n*   Update on 18/08/2021                                         *\n*                                        Have Fun - PiHome.eu    *\n******************************************************************\n [0m \n[36m2021-11-23 14:02:37[0m - Controller Script Started \n[36m2021-11-23 14:02:37[0m - Operating in Boiler Mode \n[36m2021-11-23 14:02:37[0m - Day of the Week: [41m2[0m \n------------------------------------------------------------------------------------------------------- \n[36m2021-11-23 14:02:37[0m - Zone: Type     [41mHeating[0m \n[36m2021-11-23 14:02:37[0m - Zone: Sensor Reading     [41m[0m \n[36m2021-11-23 14:02:37[0m - Zone: Weather Factor     [41m0.3[0m \n[36m2021-11-23 14:02:37[0m - Zone: DeadBand           [41m0.5[0m \n[36m2021-11-23 14:02:37[0m - Zone: Cut In Temperature        [41m0[0m \n[36m2021-11-23 14:02:37[0m - Zone: Cut Out Temperature       [41m0[0m \n[36m2021-11-23 14:02:37[0m - Zone: Mode       [41m0[0m \n[36m2021-11-23 14:02:37[0m - Zone ID: [41m38[0m \n[36m2021-11-23 14:02:37[0m - Zone: Central Heating Controller: [41m0[0m Controller Child: [41m13[0m Zone Status: [41m0[0m \n[36m2021-11-23 14:02:37[0m - Zone: Central Heating Stop Cause: System is OFF - Target C:[41m0[0m Zone C:[31m[0m \n------------------------------------------------------------------------------------------------------- \n[36m2021-11-23 14:02:37[0m - Temperature sensor communication timeout for This Zone. Last temperature reading: \n[36m2021-11-23 14:02:37[0m - Zone: Type     [41mWater[0m \n[36m2021-11-23 14:02:37[0m - Zone: Sensor Reading     [41m[0m \n[36m2021-11-23 14:02:37[0m - Zone: Weather Factor     [41m0.3[0m \n[36m2021-11-23 14:02:37[0m - Zone: DeadBand           [41m0.5[0m \n[36m2021-11-23 14:02:37[0m - Zone: Cut In Temperature        [41m0[0m \n[36m2021-11-23 14:02:37[0m - Zone: Cut Out Temperature       [41m0[0m \n[36m2021-11-23 14:02:37[0m - Zone: Mode       [41m10[0m \n[36m2021-11-23 14:02:37[0m - Zone ID: [41m39[0m \n[36m2021-11-23 14:02:37[0m - Zone: Hot Water Controller: [41m0[0m Controller Child: [41m11[0m Zone Status: [41m0[0m \n[36m2021-11-23 14:02:37[0m - Zone: Hot Water Stop Cause: Zone fault - Target C:[41m0[0m Zone C:[31m[0m \n------------------------------------------------------------------------------------------------------- \n[36m2021-11-23 14:02:37[0m - System Controller GIOP: [41m15[0m Status: [41m0[0m (1=On, 0=Off) \n[36m2021-11-23 14:02:37[0m - System Controller Active Status: [41m0[0m \n[36m2021-11-23 14:02:37[0m - System Controller Hysteresis Status: [41m0[0m \n------------------------------------------------------------------------------------------------------- \n[36m2021-11-23 14:02:37[0m - Purging marked records. \n------------------------------------------------------------------------------------------------------- \n[36m2021-11-23 14:02:38[0m - Controller Script Ended \n[32m*******************************************************************************************************[0m  \n\n','2021-11-23 14:02:38'),(2,'db_cleanup','/var/www/cron/db_cleanup.php',1,0,'02:00','[36m\n           __  __                             _         \n          |  \\/  |                    /\\     (_)        \n          | \\  / |   __ _  __  __    /  \\     _   _ __  \n          | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n          | |  | | | (_| |  >  <   / ____ \\  | | | |    \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n                [45m S M A R T   T H E R M O S T A T [0m \n[31m*************************************************************\n* Database Cleanup Script Version 0.1 Build Date 13/05/2018 *\n* Update on 10/04/218                                       *\n*                                      Have Fun - PiHome.eu *\n*************************************************************\n [0m \n[36m2021-11-23 13:43:25[0m - Database Cleanup Script Started \n[36m2021-11-23 13:43:25[0m - Temperature Records Deleted from Tables \n[36m2021-11-23 13:43:25[0m - Node Battery Records Deleted from Tables \n[36m2021-11-23 13:43:25[0m - Orphaned Node Battery Records Deleted from Tables \n[36m2021-11-23 13:43:25[0m - Gateway Logs Records Deleted from Tables \n[36m2021-11-23 13:43:25[0m - Database Cleanup Script Ended \n[32m**************************************************************[0m  \n','2021-11-23 13:43:25'),(3,'check_gw','/var/www/cron/check_gw.php',1,0,'60','[36m\n           __  __                             _         \n          |  \\/  |                    /\\     (_)        \n          | \\  / |   __ _  __  __    /  \\     _   _ __  \n          | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n          | |  | | | (_| |  >  <   / ____ \\  | | | |    \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n                [45m S M A R T   T H E R M O S T A T [0m \n[31m********************************************************\n*   Gateway Script Version 0.3 Build Date 22/01/2018   *\n*          Last Modification Date 24/04/2020           *\n*                                Have Fun - PiHome.eu  *\n********************************************************\n [0m \n[36m2021-11-23 14:02:40[0m - Python Gateway Script Status Check Script Started \n[36m2021-11-23 14:02:40[0m - Python Gateway Script for Gateway is [42mRunning[0m \n[36m2021-11-23 14:02:40[0m - The PID is: [42m3077[0m \n--------------------------------------------------------------------------\n[36m2021-11-23 14:02:40[0m - Python Gateway Script Status Check Script Ended \n[32m***************************************************************************[0m\n','2021-11-23 14:02:40'),(4,'system_c','/var/www/cron/system_c.php',1,0,'300','[36m\n       __  __                             _         \n      |  \\/  |                    /\\     (_)        \n      | \\  / |   __ _  __  __    /  \\     _   _ __  \n      | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n      | |  | | | (_| |  >  <   / ____ \\  | | | |    \n      |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n            [45m S M A R T   T H E R M O S T A T [0m \n[31m********************************************************\n* System Temperature Version 0.5 Build Date 31/03/2018 *\n* Update on 10/06/2021                                 *\n*                                 Have Fun - PiHome.eu *\n********************************************************\n [0m \n[36m2021-11-23 13:58:28[0m - System Temperature: 48.3\n','2021-11-23 13:58:28'),(5,'weather_update','/var/www/cron/weather_update.php',1,0,'1800','[36m\n           __  __                             _         \n          |  \\/  |                    /\\     (_)        \n          | \\  / |   __ _  __  __    /  \\     _   _ __  \n          | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n          | |  | | | (_| |  >  <   / ____ \\  | | | |    \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n                [45m S M A R T   T H E R M O S T A T [0m \n[31m************************************************************\n* Weather Update Script Version 0.11 Build Date 31/01/2018 *\n* Update on 27/01/2020                                     *\n*                                     Have Fun - PiHome.eu *\n************************************************************\n [0m \n[36m2021-11-23 13:43:26[0m - Weather Update Script Started \n[36m2021-11-23 13:43:26[0m - Weather Data Downloaded \n[36m2021-11-23 13:43:26[0m - Current Weather Temperature 6  \n[36m2021-11-23 13:43:26[0m - Database Updated \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-23 15:00:00 \n[1;33mMin Temperature for day: [0m0 5.69  \n[1;33mMax Temperature for day: [0m0 6.36 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                6 \n[1;33mWind Speed %: [0m           0.88 \n[1;33mHumidity : [0m              88 \n[1;33mIcon : [0m                  01d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-23 18:00:00 \n[1;33mMin Temperature for day: [0m1 2.89  \n[1;33mMax Temperature for day: [0m1 4.76 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                5 \n[1;33mWind Speed %: [0m           1.03 \n[1;33mHumidity : [0m              90 \n[1;33mIcon : [0m                  01n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-23 21:00:00 \n[1;33mMin Temperature for day: [0m2 2.14  \n[1;33mMax Temperature for day: [0m2 3.32 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                5 \n[1;33mWind Speed %: [0m           1.37 \n[1;33mHumidity : [0m              93 \n[1;33mIcon : [0m                  01n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-24 00:00:00 \n[1;33mMin Temperature for day: [0m3 1.76  \n[1;33mMax Temperature for day: [0m3 1.76 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                7 \n[1;33mWind Speed %: [0m           1.92 \n[1;33mHumidity : [0m              95 \n[1;33mIcon : [0m                  01n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-24 03:00:00 \n[1;33mMin Temperature for day: [0m4 1.68  \n[1;33mMax Temperature for day: [0m4 1.68 \n[1;33mWeather: [0m                Clouds - few clouds \n[1;33mCloud %: [0m                23 \n[1;33mWind Speed %: [0m           2.08 \n[1;33mHumidity : [0m              96 \n[1;33mIcon : [0m                  02n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-24 06:00:00 \n[1;33mMin Temperature for day: [0m5 4.39  \n[1;33mMax Temperature for day: [0m5 4.39 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                61 \n[1;33mWind Speed %: [0m           2.85 \n[1;33mHumidity : [0m              90 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-24 09:00:00 \n[1;33mMin Temperature for day: [0m6 4.51  \n[1;33mMax Temperature for day: [0m6 4.51 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                90 \n[1;33mWind Speed %: [0m           3.06 \n[1;33mHumidity : [0m              94 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-24 12:00:00 \n[1;33mMin Temperature for day: [0m7 5.69  \n[1;33mMax Temperature for day: [0m7 5.69 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                95 \n[1;33mWind Speed %: [0m           2.27 \n[1;33mHumidity : [0m              90 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-24 15:00:00 \n[1;33mMin Temperature for day: [0m8 5.41  \n[1;33mMax Temperature for day: [0m8 5.41 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                74 \n[1;33mWind Speed %: [0m           4.58 \n[1;33mHumidity : [0m              84 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-24 18:00:00 \n[1;33mMin Temperature for day: [0m9 1.89  \n[1;33mMax Temperature for day: [0m9 1.89 \n[1;33mWeather: [0m                Clouds - scattered clouds \n[1;33mCloud %: [0m                44 \n[1;33mWind Speed %: [0m           2.5 \n[1;33mHumidity : [0m              93 \n[1;33mIcon : [0m                  03n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-24 21:00:00 \n[1;33mMin Temperature for day: [0m10 1.11  \n[1;33mMax Temperature for day: [0m10 1.11 \n[1;33mWeather: [0m                Clouds - few clouds \n[1;33mCloud %: [0m                13 \n[1;33mWind Speed %: [0m           2.92 \n[1;33mHumidity : [0m              93 \n[1;33mIcon : [0m                  02n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-25 00:00:00 \n[1;33mMin Temperature for day: [0m11 2.41  \n[1;33mMax Temperature for day: [0m11 2.41 \n[1;33mWeather: [0m                Clouds - scattered clouds \n[1;33mCloud %: [0m                34 \n[1;33mWind Speed %: [0m           4.74 \n[1;33mHumidity : [0m              85 \n[1;33mIcon : [0m                  03n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-25 03:00:00 \n[1;33mMin Temperature for day: [0m12 2.27  \n[1;33mMax Temperature for day: [0m12 2.27 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                7 \n[1;33mWind Speed %: [0m           4.98 \n[1;33mHumidity : [0m              85 \n[1;33mIcon : [0m                  01n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-25 06:00:00 \n[1;33mMin Temperature for day: [0m13 2.47  \n[1;33mMax Temperature for day: [0m13 2.47 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                10 \n[1;33mWind Speed %: [0m           5.2 \n[1;33mHumidity : [0m              79 \n[1;33mIcon : [0m                  01n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-25 09:00:00 \n[1;33mMin Temperature for day: [0m14 2.18  \n[1;33mMax Temperature for day: [0m14 2.18 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                65 \n[1;33mWind Speed %: [0m           4.4 \n[1;33mHumidity : [0m              83 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-25 12:00:00 \n[1;33mMin Temperature for day: [0m15 4.87  \n[1;33mMax Temperature for day: [0m15 4.87 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                54 \n[1;33mWind Speed %: [0m           4.45 \n[1;33mHumidity : [0m              73 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-25 15:00:00 \n[1;33mMin Temperature for day: [0m16 4.42  \n[1;33mMax Temperature for day: [0m16 4.42 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                96 \n[1;33mWind Speed %: [0m           3.11 \n[1;33mHumidity : [0m              77 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-25 18:00:00 \n[1;33mMin Temperature for day: [0m17 1.73  \n[1;33mMax Temperature for day: [0m17 1.73 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                97 \n[1;33mWind Speed %: [0m           2.97 \n[1;33mHumidity : [0m              91 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-25 21:00:00 \n[1;33mMin Temperature for day: [0m18 1.82  \n[1;33mMax Temperature for day: [0m18 1.82 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                79 \n[1;33mWind Speed %: [0m           3.63 \n[1;33mHumidity : [0m              89 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-26 00:00:00 \n[1;33mMin Temperature for day: [0m19 4.25  \n[1;33mMax Temperature for day: [0m19 4.25 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                86 \n[1;33mWind Speed %: [0m           5.78 \n[1;33mHumidity : [0m              92 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-26 03:00:00 \n[1;33mMin Temperature for day: [0m20 6.88  \n[1;33mMax Temperature for day: [0m20 6.88 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                100 \n[1;33mWind Speed %: [0m           7.68 \n[1;33mHumidity : [0m              84 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-26 06:00:00 \n[1;33mMin Temperature for day: [0m21 7.97  \n[1;33mMax Temperature for day: [0m21 7.97 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                100 \n[1;33mWind Speed %: [0m           8.78 \n[1;33mHumidity : [0m              86 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-26 09:00:00 \n[1;33mMin Temperature for day: [0m22 7.27  \n[1;33mMax Temperature for day: [0m22 7.27 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                84 \n[1;33mWind Speed %: [0m           8.71 \n[1;33mHumidity : [0m              80 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-26 12:00:00 \n[1;33mMin Temperature for day: [0m23 7.63  \n[1;33mMax Temperature for day: [0m23 7.63 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                51 \n[1;33mWind Speed %: [0m           7.68 \n[1;33mHumidity : [0m              73 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-26 15:00:00 \n[1;33mMin Temperature for day: [0m24 6.73  \n[1;33mMax Temperature for day: [0m24 6.73 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                25 \n[1;33mWind Speed %: [0m           9.08 \n[1;33mHumidity : [0m              71 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-26 18:00:00 \n[1;33mMin Temperature for day: [0m25 3.6  \n[1;33mMax Temperature for day: [0m25 3.6 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                31 \n[1;33mWind Speed %: [0m           7.54 \n[1;33mHumidity : [0m              94 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-26 21:00:00 \n[1;33mMin Temperature for day: [0m26 3.04  \n[1;33mMax Temperature for day: [0m26 3.04 \n[1;33mWeather: [0m                Snow - snow \n[1;33mCloud %: [0m                92 \n[1;33mWind Speed %: [0m           9.3 \n[1;33mHumidity : [0m              75 \n[1;33mIcon : [0m                  13n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-27 00:00:00 \n[1;33mMin Temperature for day: [0m27 4.38  \n[1;33mMax Temperature for day: [0m27 4.38 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                92 \n[1;33mWind Speed %: [0m           9.97 \n[1;33mHumidity : [0m              81 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-27 03:00:00 \n[1;33mMin Temperature for day: [0m28 3.28  \n[1;33mMax Temperature for day: [0m28 3.28 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                85 \n[1;33mWind Speed %: [0m           8.08 \n[1;33mHumidity : [0m              76 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-27 06:00:00 \n[1;33mMin Temperature for day: [0m29 2.64  \n[1;33mMax Temperature for day: [0m29 2.64 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                84 \n[1;33mWind Speed %: [0m           6.89 \n[1;33mHumidity : [0m              77 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-27 09:00:00 \n[1;33mMin Temperature for day: [0m30 1.1  \n[1;33mMax Temperature for day: [0m30 1.1 \n[1;33mWeather: [0m                Snow - light snow \n[1;33mCloud %: [0m                80 \n[1;33mWind Speed %: [0m           8.13 \n[1;33mHumidity : [0m              79 \n[1;33mIcon : [0m                  13d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-27 12:00:00 \n[1;33mMin Temperature for day: [0m31 2.13  \n[1;33mMax Temperature for day: [0m31 2.13 \n[1;33mWeather: [0m                Snow - light snow \n[1;33mCloud %: [0m                74 \n[1;33mWind Speed %: [0m           8.13 \n[1;33mHumidity : [0m              67 \n[1;33mIcon : [0m                  13d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-27 15:00:00 \n[1;33mMin Temperature for day: [0m32 2  \n[1;33mMax Temperature for day: [0m32 2 \n[1;33mWeather: [0m                Snow - light snow \n[1;33mCloud %: [0m                87 \n[1;33mWind Speed %: [0m           8.5 \n[1;33mHumidity : [0m              74 \n[1;33mIcon : [0m                  13d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-27 18:00:00 \n[1;33mMin Temperature for day: [0m33 0.86  \n[1;33mMax Temperature for day: [0m33 0.86 \n[1;33mWeather: [0m                Snow - light snow \n[1;33mCloud %: [0m                87 \n[1;33mWind Speed %: [0m           7.83 \n[1;33mHumidity : [0m              74 \n[1;33mIcon : [0m                  13n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-27 21:00:00 \n[1;33mMin Temperature for day: [0m34 1.49  \n[1;33mMax Temperature for day: [0m34 1.49 \n[1;33mWeather: [0m                Clouds - scattered clouds \n[1;33mCloud %: [0m                35 \n[1;33mWind Speed %: [0m           8.04 \n[1;33mHumidity : [0m              66 \n[1;33mIcon : [0m                  03n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-28 00:00:00 \n[1;33mMin Temperature for day: [0m35 1.49  \n[1;33mMax Temperature for day: [0m35 1.49 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                60 \n[1;33mWind Speed %: [0m           6.66 \n[1;33mHumidity : [0m              68 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-28 03:00:00 \n[1;33mMin Temperature for day: [0m36 1.3  \n[1;33mMax Temperature for day: [0m36 1.3 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                99 \n[1;33mWind Speed %: [0m           5.39 \n[1;33mHumidity : [0m              71 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-28 06:00:00 \n[1;33mMin Temperature for day: [0m37 0.81  \n[1;33mMax Temperature for day: [0m37 0.81 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                100 \n[1;33mWind Speed %: [0m           4.39 \n[1;33mHumidity : [0m              77 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-28 09:00:00 \n[1;33mMin Temperature for day: [0m38 0.42  \n[1;33mMax Temperature for day: [0m38 0.42 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                77 \n[1;33mWind Speed %: [0m           3.99 \n[1;33mHumidity : [0m              85 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2021-11-28 12:00:00 \n[1;33mMin Temperature for day: [0m39 3.73  \n[1;33mMax Temperature for day: [0m39 3.73 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                55 \n[1;33mWind Speed %: [0m           5.04 \n[1;33mHumidity : [0m              72 \n[1;33mIcon : [0m                  04d \n \n[36m2021-11-23 13:43:27[0m - Weather Data Downloaded \n  \n[36m2021-11-23 13:43:27[0m - Weather Update Script Finished \n','2021-11-23 13:43:27'),(6,'reboot_wifi','/var/www/cron/reboot_wifi.sh',1,0,'120','           __  __                             _        \n          |  \\/  |                    /\\     (_)       \n          | \\  / |   __ _  __  __    /  \\     _   _ __ \n          | |\\/| |  / _\' | \\ \\/ /   / /\\ \\   | | |  __|\n          | |  | | | (_| |  >  <   / ____ \\  | | | |   \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|   \n\n                S M A R T   T H E R M O S T A T \n*************************************************************************\n* MaxAir is LINUX  based Central Heating Control systems. It runs from  *\n* a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *\n* extent permitted by applicable law. I take no responsibility for any  *\n* loss or damage to you or your property.                               *\n* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTIL UNLESS YOU KNOW  *\n* WHAT YOU ARE DOING                                                    *\n*************************************************************************\n\n                                                       Have Fun - PiHome \n - Auto Reconnect Wi-Fi Status for wlan0 Script Started \n   Tue 23 Nov 14:01:33 GMT 2021\n\n*************************************************************************\n\nProcess still running, Lockfile valid\n','2021-11-23 14:01:33'),(7,'check_ds18b20','/var/www/cron/check_ds18b20.php',0,0,'60','','2021-11-23 13:43:24'),(8,'sw_install','/var/www/cron/sw_install.py',1,0,'10','[0;36;40m \n    __  __                             _         \n   |  \\/  |                    /\\     (_)        \n   | \\  / |   __ _  __  __    /  \\     _   _ __  \n   | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n   | |  | | | (_| |  >  <   / ____ \\  | | | |    \n   |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n \n        [3;30;45mS M A R T   T H E R M O S T A T [0m\n[0;31;40m \n********************************************************\n*       Background Installer for Add_On software       *\n*                                                      *\n*                                                      *\n*      Build Date: 03/03/2021                          *\n*      Version 0.01 - Last Modified 03/04/2021         *\n*                                 Have Fun - PiHome.eu *\n********************************************************\n [0m\n[0;36;40mTue Nov 23 14:02:54 2021[0m - Software Install Script Started\n--------------------------------------------------------------------\nNothing to Install\n[0;36;40mTue Nov 23 14:02:54 2021[0m - Software Install Script Ended\n--------------------------------------------------------------------\n','2021-11-23 14:02:54'),(9,'update_code','/var/www/cron/update_code.py',1,0,'00:00','[95m \n    __  __                             _         \n   |  \\/  |                    /\\     (_)        \n   | \\  / |   __ _  __  __    /  \\     _   _ __  \n   | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n   | |  | | | (_| |  >  <   / ____ \\  | | | |    \n   |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n \n        [3;30;45mS M A R T   T H E R M O S T A T [0m\n[0;31;40m \n********************************************************\n* Compare installed code against GITHUB repository and *\n* download any new or changed files, for later update. *\n*                                                      *\n*      Build Date: 02/08/2021                          *\n*      Version 0.04 - Last Modified 11/10/2021         *\n*                                 Have Fun - PiHome.eu *\n********************************************************\n [0m\n--------------------------------------------------------\n[0;36;40mTue Nov 23 13:43:28 2021[0m - Code Update Script Started\n--------------------------------------------------------\n/var/www/scheduling.php\n/var/www/setup.php\n/var/www/zone.php\n/var/www/cron/controller.php\n/var/www/cron/gateway.py\n/var/www/documentation/microsoft_word_format/changelog.docx\n/var/www/documentation/microsoft_word_format/setup_guide_BOILER_mode.docx\n/var/www/documentation/microsoft_word_format/setup_guide_HVAC_mode.docx\n/var/www/documentation/microsoft_word_format/zone_types.docx\n/var/www/documentation/pdf_format/changelog.pdf\n/var/www/documentation/pdf_format/setup_guide_BOILER_mode.pdf\n/var/www/documentation/pdf_format/setup_guide_HVAC_mode.pdf\n/var/www/documentation/pdf_format/setup_guide_mqtt.pdf\n/var/www/documentation/pdf_format/zone_types.pdf\n/var/www/st_inc/db_config.ini\n/var/www/st_inc/functions.php\n--------------------------------------------------------\n[0;36;40mTue Nov 23 13:43:57 2021[0m - Code Update Script Ended\n--------------------------------------------------------\n','2021-11-23 13:43:57'),(10,'check_gpio_switch','/var/www/cron/check_gpio_switch.php',0,0,'60','','2021-11-23 13:43:25');
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livetemp`
--

DROP TABLE IF EXISTS `livetemp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `livetemp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `hvac_mode` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_livetemp_zone` (`zone_id`),
  CONSTRAINT `FK_livetemp_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livetemp`
--

LOCK TABLES `livetemp` WRITE;
/*!40000 ALTER TABLE `livetemp` DISABLE KEYS */;
INSERT INTO `livetemp` VALUES (1,0,0,0,38,0,0.0,0);
/*!40000 ALTER TABLE `livetemp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages_in`
--

DROP TABLE IF EXISTS `messages_in`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `node_id` char(15) COLLATE utf16_bin DEFAULT NULL,
  `child_id` tinyint(4) DEFAULT NULL,
  `sub_type` int(11) DEFAULT NULL,
  `payload` decimal(10,2) DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages_in`
--

LOCK TABLES `messages_in` WRITE;
/*!40000 ALTER TABLE `messages_in` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages_in` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `messages_in_view_24h`
--

DROP TABLE IF EXISTS `messages_in_view_24h`;
/*!50001 DROP VIEW IF EXISTS `messages_in_view_24h`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `messages_in_view_24h` (
  `node_id` tinyint NOT NULL,
  `child_id` tinyint NOT NULL,
  `datetime` tinyint NOT NULL,
  `payload` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `messages_out`
--

DROP TABLE IF EXISTS `messages_out`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `node_id` char(50) COLLATE utf32_bin NOT NULL COMMENT 'Node ID',
  `child_id` int(11) NOT NULL COMMENT 'Child Sensor',
  `sub_type` int(11) NOT NULL COMMENT 'Command Type',
  `ack` int(11) NOT NULL COMMENT 'Ack Req/Resp',
  `type` int(11) NOT NULL COMMENT 'Type',
  `payload` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT 'Payload',
  `sent` tinyint(1) NOT NULL COMMENT 'Sent Status 0 No - 1 Yes',
  `datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp() COMMENT 'Current datetime',
  `zone_id` int(11) NOT NULL COMMENT 'Zone ID related to this entery',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf32 COLLATE=utf32_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages_out`
--

LOCK TABLES `messages_out` WRITE;
/*!40000 ALTER TABLE `messages_out` DISABLE KEYS */;
INSERT INTO `messages_out` VALUES (29,0,0,'0',15,1,1,2,'0',1,'2021-11-23 14:02:38',0),(30,0,0,'0',13,1,1,2,'0',1,'2021-11-23 13:54:41',38),(32,0,0,'0',11,1,1,2,'0',1,'2021-11-23 13:57:29',39);
/*!40000 ALTER TABLE `messages_out` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mqtt`
--

DROP TABLE IF EXISTS `mqtt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mqtt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf16_bin NOT NULL,
  `ip` varchar(39) COLLATE utf16_bin NOT NULL,
  `port` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf16_bin NOT NULL,
  `password` varchar(50) COLLATE utf16_bin NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mqtt`
--

LOCK TABLES `mqtt` WRITE;
/*!40000 ALTER TABLE `mqtt` DISABLE KEYS */;
/*!40000 ALTER TABLE `mqtt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mqtt_devices`
--

DROP TABLE IF EXISTS `mqtt_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mqtt_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` tinyint(4) NOT NULL,
  `nodes_id` int(11) DEFAULT NULL,
  `type` tinyint(4) NOT NULL COMMENT '0 - Sensor, 1 - Relay',
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mqtt_topic` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Relay payload for on command',
  `on_payload` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Relay payload for on command',
  `off_payload` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Relay payload for on command',
  `attribute` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'Sensor JSON attribute',
  PRIMARY KEY (`id`),
  KEY `FK_mqtt_node_child_nodes` (`nodes_id`),
  CONSTRAINT `FK_mqtt_node_child_nodes` FOREIGN KEY (`nodes_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mqtt_devices`
--

LOCK TABLES `mqtt_devices` WRITE;
/*!40000 ALTER TABLE `mqtt_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `mqtt_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_settings`
--

DROP TABLE IF EXISTS `network_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `network_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `primary_interface` tinyint(4) DEFAULT NULL,
  `ap_mode` tinyint(1) DEFAULT NULL,
  `interface_num` tinyint(4) DEFAULT NULL,
  `interface_type` char(50) COLLATE utf16_bin DEFAULT NULL,
  `mac_address` char(50) COLLATE utf16_bin DEFAULT NULL,
  `hostname` char(50) COLLATE utf16_bin DEFAULT NULL,
  `ip_address` char(50) COLLATE utf16_bin DEFAULT NULL,
  `gateway_address` char(50) COLLATE utf16_bin DEFAULT NULL,
  `net_mask` char(50) COLLATE utf16_bin DEFAULT NULL,
  `dns1_address` char(50) COLLATE utf16_bin DEFAULT NULL,
  `dns2_address` char(50) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_settings`
--

LOCK TABLES `network_settings` WRITE;
/*!40000 ALTER TABLE `network_settings` DISABLE KEYS */;
INSERT INTO `network_settings` VALUES (3,0,0,1,0,0,'wlan0','','','10.0.0.100','10.0.0.1','255.255.255.0','','');
/*!40000 ALTER TABLE `network_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_id`
--

DROP TABLE IF EXISTS `node_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) DEFAULT NULL,
  `purge` tinyint(4) DEFAULT NULL,
  `node_id` int(11) DEFAULT NULL,
  `sent` tinyint(4) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_id`
--

LOCK TABLES `node_id` WRITE;
/*!40000 ALTER TABLE `node_id` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_id` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nodes`
--

DROP TABLE IF EXISTS `nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `node_id` char(50) COLLATE utf16_bin NOT NULL,
  `max_child_id` int(11) NOT NULL,
  `sub_type` int(11) NOT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `notice_interval` int(11) NOT NULL,
  `min_value` int(11) DEFAULT NULL,
  `status` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `ms_version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `sketch_version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `repeater` tinyint(4) DEFAULT NULL COMMENT 'Repeater Feature Enabled=1 or Disable=0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodes`
--

LOCK TABLES `nodes` WRITE;
/*!40000 ALTER TABLE `nodes` DISABLE KEYS */;
INSERT INTO `nodes` VALUES (23,0,0,'GPIO','0',0,0,'GPIO Controller','2021-11-23 13:43:24',0,0,'Active','0','0',0),(24,0,0,'MySensor','21',0,0,'Temperature Sensor','2020-10-20 15:45:28',0,0,'Active','2.3.2','0.32',0),(25,0,0,'MySensor','28',0,0,'Temperature Sensor','2021-11-23 13:44:50',30,0,'Active','2.3.2','0.35',0),(26,0,0,'MySensor','34',0,0,'Temperature Sensor','2021-11-23 13:44:55',30,0,'Active','2.3.2','0.35',0);
/*!40000 ALTER TABLE `nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nodes_battery`
--

DROP TABLE IF EXISTS `nodes_battery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nodes_battery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `node_id` int(11) DEFAULT NULL,
  `bat_voltage` decimal(10,2) DEFAULT NULL,
  `bat_level` decimal(10,2) DEFAULT NULL,
  `update` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodes_battery`
--

LOCK TABLES `nodes_battery` WRITE;
/*!40000 ALTER TABLE `nodes_battery` DISABLE KEYS */;
/*!40000 ALTER TABLE `nodes_battery` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notice`
--

DROP TABLE IF EXISTS `notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL,
  `datetime` timestamp NULL DEFAULT NULL,
  `message` varchar(200) COLLATE utf16_bin DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notice`
--

LOCK TABLES `notice` WRITE;
/*!40000 ALTER TABLE `notice` DISABLE KEYS */;
/*!40000 ALTER TABLE `notice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `override`
--

DROP TABLE IF EXISTS `override`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `override` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `time` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `temperature` tinyint(4) DEFAULT NULL,
  `hvac_mode` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_override_zone` (`zone_id`),
  CONSTRAINT `FK_override_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `override`
--

LOCK TABLES `override` WRITE;
/*!40000 ALTER TABLE `override` DISABLE KEYS */;
INSERT INTO `override` VALUES (13,0,0,0,38,'2021-11-23 13:54:41',25,0),(14,0,0,0,39,'2021-11-23 13:55:35',45,0);
/*!40000 ALTER TABLE `override` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `piconnect`
--

DROP TABLE IF EXISTS `piconnect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `piconnect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `protocol` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `url` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `script` char(50) COLLATE utf16_bin DEFAULT NULL,
  `api_key` varchar(200) COLLATE utf16_bin DEFAULT NULL,
  `version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `build` char(50) COLLATE utf16_bin DEFAULT NULL,
  `connect_datetime` datetime DEFAULT NULL,
  `delay` int(11) DEFAULT NULL,
  `away` bit(1) DEFAULT NULL,
  `boiler` bit(1) DEFAULT NULL,
  `boiler_logs` bit(1) DEFAULT NULL,
  `boost` bit(1) DEFAULT NULL,
  `email` bit(1) DEFAULT NULL,
  `frost_protection` bit(1) DEFAULT NULL,
  `gateway` bit(1) DEFAULT NULL,
  `gateway_log` bit(1) DEFAULT NULL,
  `holidays` bit(1) DEFAULT NULL,
  `messages_in` bit(1) DEFAULT NULL,
  `messages_out` bit(1) DEFAULT NULL,
  `mqtt` bit(1) DEFAULT NULL,
  `nodes` bit(1) DEFAULT NULL,
  `nodes_battery` bit(1) DEFAULT NULL,
  `notice` bit(1) DEFAULT NULL,
  `override` bit(1) DEFAULT NULL,
  `piconnect_logs` bit(1) DEFAULT NULL,
  `schedule` bit(1) DEFAULT NULL,
  `system` bit(1) DEFAULT NULL,
  `weather` bit(1) DEFAULT NULL,
  `zone` bit(1) DEFAULT NULL,
  `zone_logs` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `piconnect`
--

LOCK TABLES `piconnect` WRITE;
/*!40000 ALTER TABLE `piconnect` DISABLE KEYS */;
/*!40000 ALTER TABLE `piconnect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `piconnect_logs`
--

DROP TABLE IF EXISTS `piconnect_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `piconnect_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` int(11) DEFAULT NULL,
  `picurl` char(200) CHARACTER SET utf8mb4 DEFAULT NULL,
  `content_type` char(200) CHARACTER SET utf8mb4 DEFAULT NULL,
  `http_code` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `header_size` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `request_size` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `filetime` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `ssl_verify_result` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `redirect_count` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `total_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `connect_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `pretransfer_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `size_upload` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `size_download` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `speed_download` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `speed_upload` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `download_content_length` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `upload_content_length` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `starttransfer_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `primary_port` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `local_port` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `start_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `end_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `n_tables` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `records` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `piconnect_logs`
--

LOCK TABLES `piconnect_logs` WRITE;
/*!40000 ALTER TABLE `piconnect_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `piconnect_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relays`
--

DROP TABLE IF EXISTS `relays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `relay_id` int(11) DEFAULT NULL,
  `relay_child_id` int(11) DEFAULT NULL,
  `name` char(50) COLLATE utf8_bin DEFAULT NULL,
  `type` tinyint(1) NOT NULL,
  `on_trigger` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_relays_nodes` (`relay_id`),
  CONSTRAINT `FK_relays_nodes` FOREIGN KEY (`relay_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relays`
--

LOCK TABLES `relays` WRITE;
/*!40000 ALTER TABLE `relays` DISABLE KEYS */;
INSERT INTO `relays` VALUES (48,0,0,23,11,'Hot Water',0,1),(49,0,0,23,13,'Central Heating',0,1),(50,0,0,23,15,'Gas Boiler',1,1);
/*!40000 ALTER TABLE `relays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repository`
--

DROP TABLE IF EXISTS `repository`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repository` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) NOT NULL,
  `name` char(50) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repository`
--

LOCK TABLES `repository` WRITE;
/*!40000 ALTER TABLE `repository` DISABLE KEYS */;
INSERT INTO `repository` VALUES (1,0,0,1,'pihome-shc'),(2,0,0,0,'twa127'),(3,0,0,0,'dvdcut'),(4,0,0,0,'JSa1987'),(5,0,0,0,'mjhumphrey'),(6,0,0,0,'sandreialexandru');
/*!40000 ALTER TABLE `repository` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_daily_time`
--

DROP TABLE IF EXISTS `schedule_daily_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_daily_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start` time DEFAULT NULL,
  `start_sr` tinyint(1) NOT NULL,
  `start_ss` tinyint(1) NOT NULL,
  `start_offset` int(11) NOT NULL,
  `end` time DEFAULT NULL,
  `end_sr` tinyint(1) NOT NULL,
  `end_ss` tinyint(1) NOT NULL,
  `end_offset` int(11) NOT NULL,
  `WeekDays` smallint(6) NOT NULL,
  `sch_name` varchar(200) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_daily_time`
--

LOCK TABLES `schedule_daily_time` WRITE;
/*!40000 ALTER TABLE `schedule_daily_time` DISABLE KEYS */;
INSERT INTO `schedule_daily_time` VALUES (50,0,0,1,'06:30:00',0,0,0,'09:30:00',0,0,0,60,'WeekDays AM');
/*!40000 ALTER TABLE `schedule_daily_time` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_daily_time_zone`
--

DROP TABLE IF EXISTS `schedule_daily_time_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_daily_time_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `schedule_daily_time_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `temperature` float NOT NULL,
  `holidays_id` int(11) DEFAULT NULL,
  `coop` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_schedule_daily_time_zone_schedule_daily_time` (`schedule_daily_time_id`),
  KEY `FK_schedule_daily_time_zone_zone` (`zone_id`),
  CONSTRAINT `FK_schedule_daily_time_zone_schedule_daily_time` FOREIGN KEY (`schedule_daily_time_id`) REFERENCES `schedule_daily_time` (`id`),
  CONSTRAINT `FK_schedule_daily_time_zone_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_daily_time_zone`
--

LOCK TABLES `schedule_daily_time_zone` WRITE;
/*!40000 ALTER TABLE `schedule_daily_time_zone` DISABLE KEYS */;
INSERT INTO `schedule_daily_time_zone` VALUES (50,0,0,1,50,38,19.5,0,0),(51,0,0,1,50,39,40,0,0);
/*!40000 ALTER TABLE `schedule_daily_time_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `schedule_daily_time_zone_view`
--

DROP TABLE IF EXISTS `schedule_daily_time_zone_view`;
/*!50001 DROP VIEW IF EXISTS `schedule_daily_time_zone_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `schedule_daily_time_zone_view` (
  `time_id` tinyint NOT NULL,
  `time_status` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `start_sr` tinyint NOT NULL,
  `start_ss` tinyint NOT NULL,
  `start_offset` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `end_sr` tinyint NOT NULL,
  `end_ss` tinyint NOT NULL,
  `end_offset` tinyint NOT NULL,
  `WeekDays` tinyint NOT NULL,
  `tz_sync` tinyint NOT NULL,
  `tz_id` tinyint NOT NULL,
  `tz_status` tinyint NOT NULL,
  `zone_id` tinyint NOT NULL,
  `index_id` tinyint NOT NULL,
  `zone_name` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `category` tinyint NOT NULL,
  `temperature` tinyint NOT NULL,
  `holidays_id` tinyint NOT NULL,
  `coop` tinyint NOT NULL,
  `sch_name` tinyint NOT NULL,
  `max_c` tinyint NOT NULL,
  `sensor_type_id` tinyint NOT NULL,
  `stype` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `schedule_night_climat_zone`
--

DROP TABLE IF EXISTS `schedule_night_climat_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_night_climat_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `schedule_night_climate_id` int(11) DEFAULT NULL,
  `min_temperature` float NOT NULL,
  `max_temperature` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_schedule_zone_night_climat_zone` (`zone_id`),
  KEY `FK_schedule_zone_night_climat_schedule_night_climate` (`schedule_night_climate_id`),
  CONSTRAINT `FK_schedule_zone_night_climat_schedule_night_climate` FOREIGN KEY (`schedule_night_climate_id`) REFERENCES `schedule_night_climate_time` (`id`),
  CONSTRAINT `FK_schedule_zone_night_climat_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_night_climat_zone`
--

LOCK TABLES `schedule_night_climat_zone` WRITE;
/*!40000 ALTER TABLE `schedule_night_climat_zone` DISABLE KEYS */;
INSERT INTO `schedule_night_climat_zone` VALUES (13,0,0,0,38,2,18,21),(14,0,0,0,39,2,18,21);
/*!40000 ALTER TABLE `schedule_night_climat_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `schedule_night_climat_zone_view`
--

DROP TABLE IF EXISTS `schedule_night_climat_zone_view`;
/*!50001 DROP VIEW IF EXISTS `schedule_night_climat_zone_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `schedule_night_climat_zone_view` (
  `time_id` tinyint NOT NULL,
  `time_status` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `WeekDays` tinyint NOT NULL,
  `tz_sync` tinyint NOT NULL,
  `tz_id` tinyint NOT NULL,
  `tz_status` tinyint NOT NULL,
  `zone_id` tinyint NOT NULL,
  `index_id` tinyint NOT NULL,
  `zone_name` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `category` tinyint NOT NULL,
  `zone_status` tinyint NOT NULL,
  `min_temperature` tinyint NOT NULL,
  `max_temperature` tinyint NOT NULL,
  `max_c` tinyint NOT NULL,
  `sensor_type_id` tinyint NOT NULL,
  `stype` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `schedule_night_climate_time`
--

DROP TABLE IF EXISTS `schedule_night_climate_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_night_climate_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `WeekDays` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_night_climate_time`
--

LOCK TABLES `schedule_night_climate_time` WRITE;
/*!40000 ALTER TABLE `schedule_night_climate_time` DISABLE KEYS */;
INSERT INTO `schedule_night_climate_time` VALUES (2,0,0,0,'18:00:00','23:30:00',0);
/*!40000 ALTER TABLE `schedule_night_climate_time` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_type`
--

DROP TABLE IF EXISTS `sensor_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf8_bin DEFAULT NULL,
  `units` char(11) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_type`
--

LOCK TABLES `sensor_type` WRITE;
/*!40000 ALTER TABLE `sensor_type` DISABLE KEYS */;
INSERT INTO `sensor_type` VALUES (1,0,0,'Temperature','&deg;'),(2,0,0,'Humidity','%'),(3,0,0,'Binary','');
/*!40000 ALTER TABLE `sensor_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensors`
--

DROP TABLE IF EXISTS `sensors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11) DEFAULT NULL,
  `sensor_id` int(11) DEFAULT NULL,
  `sensor_child_id` int(11) DEFAULT NULL,
  `correction_factor` decimal(10,2) DEFAULT NULL,
  `sensor_type_id` int(11) DEFAULT NULL,
  `index_id` tinyint(4) NOT NULL,
  `pre_post` tinyint(1) NOT NULL,
  `name` char(50) COLLATE utf8_bin DEFAULT NULL,
  `graph_num` tinyint(4) NOT NULL,
  `show_it` tinyint(1) NOT NULL,
  `frost_temp` int(11) NOT NULL,
  `frost_controller` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_sensors_nodes` (`sensor_id`),
  KEY `FK_sensors_zone` (`zone_id`),
  CONSTRAINT `FK_sensors_nodes` FOREIGN KEY (`sensor_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensors`
--

LOCK TABLES `sensors` WRITE;
/*!40000 ALTER TABLE `sensors` DISABLE KEYS */;
INSERT INTO `sensors` VALUES (55,0,0,38,24,0,0.00,1,1,0,'Central Heating',0,1,3,49),(56,0,0,39,25,0,0.00,1,2,0,'Hot Water',0,1,0,0),(57,0,0,0,26,0,0.00,1,3,0,'Bedroom 1',0,1,0,0);
/*!40000 ALTER TABLE `sensors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sw_install`
--

DROP TABLE IF EXISTS `sw_install`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sw_install` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `script` char(100) NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `start_datetime` timestamp NULL DEFAULT NULL,
  `stop_datetime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sw_install`
--

LOCK TABLES `sw_install` WRITE;
/*!40000 ALTER TABLE `sw_install` DISABLE KEYS */;
/*!40000 ALTER TABLE `sw_install` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `version` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `build` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `country` char(2) CHARACTER SET latin1 DEFAULT NULL,
  `language` char(10) COLLATE utf16_bin DEFAULT NULL,
  `city` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `zip` char(100) COLLATE utf16_bin DEFAULT NULL,
  `openweather_api` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `backup_email` char(100) COLLATE utf16_bin DEFAULT NULL,
  `ping_home` bit(1) DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `shutdown` tinyint(4) DEFAULT NULL,
  `reboot` tinyint(4) DEFAULT NULL,
  `c_f` tinyint(4) NOT NULL COMMENT '0=C, 1=F',
  `mode` tinyint(4) DEFAULT NULL,
  `max_cpu_temp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES (2,1,0,'MaxAir - Smart Thermostat','1.3','211121','IE','en','Portlaoise',NULL,'aa22d10d34b1e6cb32bd6a5f2cb3fb46','','','Europe/Dublin',0,0,0,0,50);
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_controller`
--

DROP TABLE IF EXISTS `system_controller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_controller` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `mode` tinyint(1) NOT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `active_status` tinyint(4) DEFAULT NULL,
  `name` char(50) CHARACTER SET utf16 COLLATE utf16_bin DEFAULT NULL,
  `node_id` int(11) DEFAULT NULL,
  `hysteresis_time` tinyint(4) DEFAULT NULL,
  `max_operation_time` tinyint(4) DEFAULT NULL,
  `overrun` smallint(6) DEFAULT NULL,
  `datetime` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `sc_mode` tinyint(4) DEFAULT NULL,
  `sc_mode_prev` tinyint(4) DEFAULT NULL,
  `heat_relay_id` int(11) DEFAULT NULL,
  `cool_relay_id` int(11) DEFAULT NULL,
  `fan_relay_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_controller`
--

LOCK TABLES `system_controller` WRITE;
/*!40000 ALTER TABLE `system_controller` DISABLE KEYS */;
INSERT INTO `system_controller` VALUES (1,0,0,0,1,0,'Gas Boiler',0,3,60,2,'2021-11-23 13:52:04',0,0,50,0,0);
/*!40000 ALTER TABLE `system_controller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `system_controller_view`
--

DROP TABLE IF EXISTS `system_controller_view`;
/*!50001 DROP VIEW IF EXISTS `system_controller_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `system_controller_view` (
  `status` tinyint NOT NULL,
  `sync` tinyint NOT NULL,
  `purge` tinyint NOT NULL,
  `active_status` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `controller_type` tinyint NOT NULL,
  `relay_id` tinyint NOT NULL,
  `relay_child_id` tinyint NOT NULL,
  `hysteresis_time` tinyint NOT NULL,
  `max_operation_time` tinyint NOT NULL,
  `overrun` tinyint NOT NULL,
  `heat_relay_id` tinyint NOT NULL,
  `cool_relay_id` tinyint NOT NULL,
  `fan_relay_id` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_enable` tinyint(1) DEFAULT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `cpdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `account_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `admin_account` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,'Administrator','admin','','0f5f9ba0136d5a8588b3fc70ec752869','2021-11-23 14:02:32','2021-11-23 13:43:09',1);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userhistory`
--

DROP TABLE IF EXISTS `userhistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `audit` tinytext DEFAULT NULL,
  `ipaddress` tinytext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userhistory`
--

LOCK TABLES `userhistory` WRITE;
/*!40000 ALTER TABLE `userhistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `userhistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `weather`
--

DROP TABLE IF EXISTS `weather`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weather` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `location` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `c` tinyint(4) DEFAULT NULL,
  `wind_speed` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `title` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `description` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `sunrise` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `sunset` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `img` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp() COMMENT 'Last weather update',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weather`
--

LOCK TABLES `weather` WRITE;
/*!40000 ALTER TABLE `weather` DISABLE KEYS */;
INSERT INTO `weather` VALUES (1,0,'Portlaoise',6,'1','Clear','clear sky','1637654847','1637684651','01d','2021-11-23 13:43:26');
/*!40000 ALTER TABLE `weather` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zone`
--

DROP TABLE IF EXISTS `zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `zone_state` tinyint(4) DEFAULT NULL,
  `index_id` tinyint(4) DEFAULT NULL,
  `name` char(50) COLLATE utf8_bin DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `max_operation_time` smallint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zone_type_id` (`type_id`),
  CONSTRAINT `FK_zone_type_id` FOREIGN KEY (`type_id`) REFERENCES `zone_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone`
--

LOCK TABLES `zone` WRITE;
/*!40000 ALTER TABLE `zone` DISABLE KEYS */;
INSERT INTO `zone` VALUES (38,0,0,1,0,1,'Central Heating',2,60),(39,0,0,1,0,2,'Hot Water',3,60);
/*!40000 ALTER TABLE `zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zone_current_state`
--

DROP TABLE IF EXISTS `zone_current_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zone_current_state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `mode` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `temp_reading` decimal(4,1) DEFAULT NULL,
  `temp_target` decimal(4,1) DEFAULT NULL,
  `temp_cut_in` decimal(4,1) DEFAULT NULL,
  `temp_cut_out` decimal(4,1) DEFAULT NULL,
  `controler_fault` int(1) DEFAULT NULL,
  `controler_seen_time` timestamp NULL DEFAULT NULL,
  `sensor_fault` int(1) DEFAULT NULL,
  `sensor_seen_time` timestamp NULL DEFAULT NULL,
  `sensor_reading_time` timestamp NULL DEFAULT NULL,
  `overrun` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone_current_state`
--

LOCK TABLES `zone_current_state` WRITE;
/*!40000 ALTER TABLE `zone_current_state` DISABLE KEYS */;
INSERT INTO `zone_current_state` VALUES (38,0,0,38,0,0,0.0,0.0,0.0,0.0,0,NULL,0,NULL,NULL,0),(39,0,0,39,0,0,0.0,0.0,0.0,0.0,0,NULL,0,NULL,NULL,0);
/*!40000 ALTER TABLE `zone_current_state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zone_graphs`
--

DROP TABLE IF EXISTS `zone_graphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zone_graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11) DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `type` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `node_id` char(15) COLLATE utf16_bin DEFAULT NULL,
  `child_id` tinyint(4) DEFAULT NULL,
  `sub_type` int(11) DEFAULT NULL,
  `payload` decimal(10,2) DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone_graphs`
--

LOCK TABLES `zone_graphs` WRITE;
/*!40000 ALTER TABLE `zone_graphs` DISABLE KEYS */;
/*!40000 ALTER TABLE `zone_graphs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `zone_log_view`
--

DROP TABLE IF EXISTS `zone_log_view`;
/*!50001 DROP VIEW IF EXISTS `zone_log_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `zone_log_view` (
  `id` tinyint NOT NULL,
  `sync` tinyint NOT NULL,
  `zone_id` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `start_datetime` tinyint NOT NULL,
  `stop_datetime` tinyint NOT NULL,
  `expected_end_date_time` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `zone_relays`
--

DROP TABLE IF EXISTS `zone_relays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zone_relays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `state` tinyint(4) DEFAULT NULL,
  `current_state` tinyint(4) NOT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `zone_relay_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zone_relays_zone` (`zone_id`),
  CONSTRAINT `FK_zone_relays_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone_relays`
--

LOCK TABLES `zone_relays` WRITE;
/*!40000 ALTER TABLE `zone_relays` DISABLE KEYS */;
INSERT INTO `zone_relays` VALUES (88,0,0,0,0,38,49),(90,0,0,0,0,39,48);
/*!40000 ALTER TABLE `zone_relays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zone_sensors`
--

DROP TABLE IF EXISTS `zone_sensors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zone_sensors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11) DEFAULT NULL,
  `min_c` tinyint(4) DEFAULT NULL,
  `max_c` tinyint(4) DEFAULT NULL,
  `default_c` tinyint(4) DEFAULT NULL,
  `hysteresis_time` tinyint(4) DEFAULT NULL,
  `sp_deadband` float NOT NULL,
  `zone_sensor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zone_sensors_zone` (`zone_id`),
  KEY `FK_zone_sensors_sensors` (`zone_sensor_id`),
  CONSTRAINT `FK_zone_sensors_sensors` FOREIGN KEY (`zone_sensor_id`) REFERENCES `sensors` (`id`),
  CONSTRAINT `FK_zone_sensors_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone_sensors`
--

LOCK TABLES `zone_sensors` WRITE;
/*!40000 ALTER TABLE `zone_sensors` DISABLE KEYS */;
INSERT INTO `zone_sensors` VALUES (38,0,0,38,0,25,20,3,0.5,55),(39,0,0,39,0,45,40,3,0.5,56);
/*!40000 ALTER TABLE `zone_sensors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zone_type`
--

DROP TABLE IF EXISTS `zone_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zone_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf8_bin DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone_type`
--

LOCK TABLES `zone_type` WRITE;
/*!40000 ALTER TABLE `zone_type` DISABLE KEYS */;
INSERT INTO `zone_type` VALUES (2,0,0,'Heating',0),(3,0,0,'Water',0),(4,0,0,'Immersion',1),(5,0,0,'Switch',2),(6,0,0,'HVAC',3),(7,0,0,'Humidity',1),(8,0,0,'Binary',1);
/*!40000 ALTER TABLE `zone_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `zone_view`
--

DROP TABLE IF EXISTS `zone_view`;
/*!50001 DROP VIEW IF EXISTS `zone_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `zone_view` (
  `status` tinyint NOT NULL,
  `zone_state` tinyint NOT NULL,
  `sync` tinyint NOT NULL,
  `id` tinyint NOT NULL,
  `index_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `category` tinyint NOT NULL,
  `graph_num` tinyint NOT NULL,
  `min_c` tinyint NOT NULL,
  `max_c` tinyint NOT NULL,
  `default_c` tinyint NOT NULL,
  `max_operation_time` tinyint NOT NULL,
  `hysteresis_time` tinyint NOT NULL,
  `sp_deadband` tinyint NOT NULL,
  `sensors_id` tinyint NOT NULL,
  `sensor_child_id` tinyint NOT NULL,
  `sensor_type_id` tinyint NOT NULL,
  `relay_type` tinyint NOT NULL,
  `relay_id` tinyint NOT NULL,
  `relay_child_id` tinyint NOT NULL,
  `last_seen` tinyint NOT NULL,
  `ms_version` tinyint NOT NULL,
  `sketch_version` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `add_on_log_view`
--

/*!50001 DROP TABLE IF EXISTS `add_on_log_view`*/;
/*!50001 DROP VIEW IF EXISTS `add_on_log_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `add_on_log_view` AS select `add_on_logs`.`id` AS `id`,`add_on_logs`.`sync` AS `sync`,`add_on_logs`.`zone_id` AS `zone_id`,`zt`.`name` AS `name`,`ztype`.`type` AS `type`,`add_on_logs`.`start_datetime` AS `start_datetime`,`add_on_logs`.`stop_datetime` AS `stop_datetime`,`add_on_logs`.`expected_end_date_time` AS `expected_end_date_time` from ((`add_on_logs` join `zone` `zt` on(`add_on_logs`.`zone_id` = `zt`.`id`)) join `zone_type` `ztype` on(`zt`.`type_id` = `ztype`.`id`)) order by `add_on_logs`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `messages_in_view_24h`
--

/*!50001 DROP TABLE IF EXISTS `messages_in_view_24h`*/;
/*!50001 DROP VIEW IF EXISTS `messages_in_view_24h`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `messages_in_view_24h` AS select `messages_in`.`node_id` AS `node_id`,`messages_in`.`child_id` AS `child_id`,`messages_in`.`datetime` AS `datetime`,`messages_in`.`payload` AS `payload` from `messages_in` where `messages_in`.`datetime` > current_timestamp() - interval 24 hour */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `schedule_daily_time_zone_view`
--

/*!50001 DROP TABLE IF EXISTS `schedule_daily_time_zone_view`*/;
/*!50001 DROP VIEW IF EXISTS `schedule_daily_time_zone_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `schedule_daily_time_zone_view` AS select `ss`.`id` AS `time_id`,`ss`.`status` AS `time_status`,`sstart`.`start` AS `start`,`sstart_sr`.`start_sr` AS `start_sr`,`sstart_ss`.`start_ss` AS `start_ss`,`sstart_offset`.`start_offset` AS `start_offset`,`send`.`end` AS `end`,`send_sr`.`end_sr` AS `end_sr`,`send_ss`.`end_ss` AS `end_ss`,`send_offset`.`end_offset` AS `end_offset`,`sWeekDays`.`WeekDays` AS `WeekDays`,`sdtz`.`sync` AS `tz_sync`,`sdtz`.`id` AS `tz_id`,`sdtz`.`status` AS `tz_status`,`sdtz`.`zone_id` AS `zone_id`,`zone`.`index_id` AS `index_id`,`zone`.`name` AS `zone_name`,`ztype`.`type` AS `type`,`ztype`.`category` AS `category`,`sdtz`.`temperature` AS `temperature`,`sdtz`.`holidays_id` AS `holidays_id`,`sdtz`.`coop` AS `coop`,`ss`.`sch_name` AS `sch_name`,`zs`.`max_c` AS `max_c`,`s`.`sensor_type_id` AS `sensor_type_id`,`st`.`type` AS `stype` from ((((((((((((((((`schedule_daily_time_zone` `sdtz` join `schedule_daily_time` `ss` on(`sdtz`.`schedule_daily_time_id` = `ss`.`id`)) join `schedule_daily_time` `sstart` on(`sdtz`.`schedule_daily_time_id` = `sstart`.`id`)) join `schedule_daily_time` `sstart_sr` on(`sdtz`.`schedule_daily_time_id` = `sstart_sr`.`id`)) join `schedule_daily_time` `sstart_ss` on(`sdtz`.`schedule_daily_time_id` = `sstart_ss`.`id`)) join `schedule_daily_time` `sstart_offset` on(`sdtz`.`schedule_daily_time_id` = `sstart_offset`.`id`)) join `schedule_daily_time` `send_sr` on(`sdtz`.`schedule_daily_time_id` = `send_sr`.`id`)) join `schedule_daily_time` `send_ss` on(`sdtz`.`schedule_daily_time_id` = `send_ss`.`id`)) join `schedule_daily_time` `send_offset` on(`sdtz`.`schedule_daily_time_id` = `send_offset`.`id`)) join `schedule_daily_time` `send` on(`sdtz`.`schedule_daily_time_id` = `send`.`id`)) join `schedule_daily_time` `sWeekDays` on(`sdtz`.`schedule_daily_time_id` = `sWeekDays`.`id`)) join `zone` on(`sdtz`.`zone_id` = `zone`.`id`)) join `zone` `zt` on(`sdtz`.`zone_id` = `zt`.`id`)) left join `zone_sensors` `zs` on(`zone`.`id` = `zs`.`zone_id`)) left join `sensors` `s` on(`zs`.`zone_sensor_id` = `s`.`id`)) left join `sensor_type` `st` on(`s`.`sensor_type_id` = `st`.`id`)) join `zone_type` `ztype` on(`zone`.`type_id` = `ztype`.`id`)) where `sdtz`.`purge` = '0' order by `zone`.`index_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `schedule_night_climat_zone_view`
--

/*!50001 DROP TABLE IF EXISTS `schedule_night_climat_zone_view`*/;
/*!50001 DROP VIEW IF EXISTS `schedule_night_climat_zone_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `schedule_night_climat_zone_view` AS select `tnct`.`id` AS `time_id`,`tnct`.`status` AS `time_status`,`snct`.`start_time` AS `start`,`enct`.`end_time` AS `end`,`snct`.`WeekDays` AS `WeekDays`,`nctz`.`sync` AS `tz_sync`,`nctz`.`id` AS `tz_id`,`nctz`.`status` AS `tz_status`,`nctz`.`zone_id` AS `zone_id`,`zone`.`index_id` AS `index_id`,`zone`.`name` AS `zone_name`,`ztype`.`type` AS `type`,`ztype`.`category` AS `category`,`zone`.`status` AS `zone_status`,`nctz`.`min_temperature` AS `min_temperature`,`nctz`.`max_temperature` AS `max_temperature`,`zs`.`max_c` AS `max_c`,`s`.`sensor_type_id` AS `sensor_type_id`,`st`.`type` AS `stype` from (((((((((`schedule_night_climat_zone` `nctz` join `schedule_night_climate_time` `snct` on(`nctz`.`schedule_night_climate_id` = `snct`.`id`)) join `schedule_night_climate_time` `enct` on(`nctz`.`schedule_night_climate_id` = `enct`.`id`)) join `schedule_night_climate_time` `tnct` on(`nctz`.`schedule_night_climate_id` = `tnct`.`id`)) join `zone` on(`nctz`.`zone_id` = `zone`.`id`)) join `zone` `zt` on(`nctz`.`zone_id` = `zt`.`id`)) left join `zone_sensors` `zs` on(`zone`.`id` = `zs`.`zone_id`)) left join `sensors` `s` on(`zs`.`zone_sensor_id` = `s`.`id`)) left join `sensor_type` `st` on(`s`.`sensor_type_id` = `st`.`id`)) join `zone_type` `ztype` on(`zone`.`type_id` = `ztype`.`id`)) where `nctz`.`purge` = '0' order by `zone`.`index_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `system_controller_view`
--

/*!50001 DROP TABLE IF EXISTS `system_controller_view`*/;
/*!50001 DROP VIEW IF EXISTS `system_controller_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `system_controller_view` AS select `system_controller`.`status` AS `status`,`system_controller`.`sync` AS `sync`,`system_controller`.`purge` AS `purge`,`system_controller`.`active_status` AS `active_status`,`system_controller`.`name` AS `name`,`ctype`.`type` AS `controller_type`,`cr`.`relay_id` AS `relay_id`,`cr`.`relay_child_id` AS `relay_child_id`,`system_controller`.`hysteresis_time` AS `hysteresis_time`,`system_controller`.`max_operation_time` AS `max_operation_time`,`system_controller`.`overrun` AS `overrun`,`system_controller`.`heat_relay_id` AS `heat_relay_id`,`system_controller`.`cool_relay_id` AS `cool_relay_id`,`system_controller`.`fan_relay_id` AS `fan_relay_id` from ((`system_controller` join `relays` `cr` on(`system_controller`.`heat_relay_id` = `cr`.`id`)) join `nodes` `ctype` on(`cr`.`relay_id` = `ctype`.`id`)) where `system_controller`.`purge` = '0' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `zone_log_view`
--

/*!50001 DROP TABLE IF EXISTS `zone_log_view`*/;
/*!50001 DROP VIEW IF EXISTS `zone_log_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `zone_log_view` AS select `controller_zone_logs`.`id` AS `id`,`controller_zone_logs`.`sync` AS `sync`,`controller_zone_logs`.`zone_id` AS `zone_id`,`ztype`.`type` AS `type`,`controller_zone_logs`.`start_datetime` AS `start_datetime`,`controller_zone_logs`.`stop_datetime` AS `stop_datetime`,`controller_zone_logs`.`expected_end_date_time` AS `expected_end_date_time` from ((`controller_zone_logs` join `zone` `zt` on(`controller_zone_logs`.`zone_id` = `zt`.`id`)) join `zone_type` `ztype` on(`zt`.`type_id` = `ztype`.`id`)) order by `controller_zone_logs`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `zone_view`
--

/*!50001 DROP TABLE IF EXISTS `zone_view`*/;
/*!50001 DROP VIEW IF EXISTS `zone_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `zone_view` AS select `zone`.`status` AS `status`,`zone`.`zone_state` AS `zone_state`,`zone`.`sync` AS `sync`,`zone`.`id` AS `id`,`zone`.`index_id` AS `index_id`,`zone`.`name` AS `name`,`ztype`.`type` AS `type`,`ztype`.`category` AS `category`,`ts`.`graph_num` AS `graph_num`,`zs`.`min_c` AS `min_c`,`zs`.`max_c` AS `max_c`,`zs`.`default_c` AS `default_c`,`zone`.`max_operation_time` AS `max_operation_time`,`zs`.`hysteresis_time` AS `hysteresis_time`,`zs`.`sp_deadband` AS `sp_deadband`,`sid`.`node_id` AS `sensors_id`,`ts`.`sensor_child_id` AS `sensor_child_id`,`ts`.`sensor_type_id` AS `sensor_type_id`,`ctype`.`type` AS `relay_type`,`r`.`relay_id` AS `relay_id`,`r`.`relay_child_id` AS `relay_child_id`,ifnull(`lasts`.`last_seen`,`lasts_2`.`last_seen`) AS `last_seen`,ifnull(`msv`.`ms_version`,`msv_2`.`ms_version`) AS `ms_version`,ifnull(`skv`.`sketch_version`,`skv_2`.`sketch_version`) AS `sketch_version` from (((((((((((((`zone` left join `zone_sensors` `zs` on(`zone`.`id` = `zs`.`zone_id`)) left join `sensors` `ts` on(`zone`.`id` = `ts`.`zone_id`)) left join `zone_relays` `zr` on(`zone`.`id` = `zr`.`zone_id`)) left join `relays` `r` on(`zr`.`zone_relay_id` = `r`.`id`)) join `zone_type` `ztype` on(`zone`.`type_id` = `ztype`.`id`)) left join `nodes` `sid` on(`ts`.`sensor_id` = `sid`.`id`)) left join `nodes` `ctype` on(`r`.`relay_id` = `ctype`.`id`)) left join `nodes` `lasts` on(`ts`.`sensor_id` = `lasts`.`id`)) left join `nodes` `lasts_2` on(`r`.`relay_id` = `lasts_2`.`id`)) left join `nodes` `msv` on(`ts`.`sensor_id` = `msv`.`id`)) left join `nodes` `msv_2` on(`r`.`relay_id` = `msv_2`.`id`)) left join `nodes` `skv` on(`ts`.`sensor_id` = `skv`.`id`)) left join `nodes` `skv_2` on(`r`.`relay_id` = `skv_2`.`id`)) where `zone`.`purge` = '0' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-11-23 14:03:02
