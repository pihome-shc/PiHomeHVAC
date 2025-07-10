-- MariaDB dump 10.19  Distrib 10.6.16-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: localhost    Database: maxair
-- ------------------------------------------------------
-- Server version	10.6.16-MariaDB-0ubuntu0.22.04.1

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
/*!50001 CREATE VIEW `add_on_log_view` AS SELECT
 1 AS `id`,
  1 AS `sync`,
  1 AS `zone_id`,
  1 AS `name`,
  1 AS `type`,
  1 AS `start_datetime`,
  1 AS `stop_datetime`,
  1 AS `expected_end_date_time` */;
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
  `start_cause` char(50) DEFAULT NULL,
  `stop_datetime` timestamp NULL DEFAULT NULL,
  `stop_cause` char(50) DEFAULT NULL,
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
-- Table structure for table `auto_backup`
--

DROP TABLE IF EXISTS `auto_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `enabled` tinyint(4) NOT NULL,
  `frequency` char(50) DEFAULT NULL,
  `rotation` char(50) DEFAULT NULL,
  `destination` char(50) DEFAULT NULL,
  `email_backup` tinyint(4) NOT NULL,
  `email_confirmation` tinyint(4) NOT NULL,
  `last_backup` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_backup`
--

LOCK TABLES `auto_backup` WRITE;
/*!40000 ALTER TABLE `auto_backup` DISABLE KEYS */;
INSERT INTO `auto_backup` VALUES (1,0,0,0,'1 DAY','2 WEEK','/var/www/MySQL_Database/database_backups/',0,0,'2023-01-27 12:33:20');
/*!40000 ALTER TABLE `auto_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_image`
--

DROP TABLE IF EXISTS `auto_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `enabled` tinyint(4) NOT NULL,
  `frequency` char(50) DEFAULT NULL,
  `rotation` char(50) DEFAULT NULL,
  `destination` char(50) DEFAULT NULL,
  `email_confirmation` tinyint(4) NOT NULL,
  `last_image_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_image`
--

LOCK TABLES `auto_image` WRITE;
/*!40000 ALTER TABLE `auto_image` DISABLE KEYS */;
INSERT INTO `auto_image` VALUES (1,0,0,1,'1 DAY','4 WEEK','/mnt/pibackups/',1,'2025-07-10 02:00:01');
/*!40000 ALTER TABLE `auto_image` ENABLE KEYS */;
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
-- Table structure for table `battery`
--

DROP TABLE IF EXISTS `battery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `battery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` char(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `battery`
--

LOCK TABLES `battery` WRITE;
/*!40000 ALTER TABLE `battery` DISABLE KEYS */;
/*!40000 ALTER TABLE `battery` ENABLE KEYS */;
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
  `boost_button_state` tinyint(1) NOT NULL DEFAULT 0,
  `hvac_mode` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_boost_zone` (`zone_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boost`
--

LOCK TABLES `boost` WRITE;
/*!40000 ALTER TABLE `boost` DISABLE KEYS */;
INSERT INTO `boost` VALUES (19,0,0,0,38,'2021-11-23 13:54:41',25,60,0,0,0,0),(20,0,0,0,39,'2021-11-23 13:55:35',45,60,0,0,0,0);
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
  `name` char(50) DEFAULT NULL,
  `function` char(50) DEFAULT NULL,
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
INSERT INTO `button_page` VALUES (1,0,0,'Boost','boost',1,2),(2,0,0,'Override','override',2,2),(3,0,0,'Offset','offset',3,2),(4,0,0,'Night Climate','night_climate',4,2),(5,0,0,'Away','away',5,2),(6,0,0,'Holidays','holidays',6,2),(7,0,0,'Live Temperature','live_temp',7,2);
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
  `start_cause` char(50) DEFAULT NULL,
  `stop_datetime` timestamp NULL DEFAULT NULL,
  `stop_cause` char(50) DEFAULT NULL,
  `expected_end_date_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `controller_zone_logs`
--

LOCK TABLES `controller_zone_logs` WRITE;
/*!40000 ALTER TABLE `controller_zone_logs` DISABLE KEYS */;
INSERT INTO `controller_zone_logs` VALUES (133,0,0,0,'2025-07-10 16:41:14','Controller Script Started',NULL,'',NULL),(134,0,0,0,'2025-07-10 16:42:15','Controller Script Started',NULL,'',NULL),(135,0,0,0,'2025-07-10 16:43:16','Controller Script Started',NULL,'',NULL),(136,0,0,0,'2025-07-10 16:44:17','Controller Script Started',NULL,'',NULL),(137,0,0,0,'2025-07-10 16:45:18','Controller Script Started',NULL,'',NULL);
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
  `backup_name` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_backup`
--

LOCK TABLES `database_backup` WRITE;
/*!40000 ALTER TABLE `database_backup` DISABLE KEYS */;
INSERT INTO `database_backup` VALUES (2,0,0,0,'130821.sql','130821.sql'),(3,0,0,0,'170821.sql','170821.sql'),(4,0,0,0,'180821.sql','180821.sql'),(5,0,0,0,'270821.sql','270821.sql'),(6,0,0,0,'300821.sql','300821.sql'),(7,0,0,0,'060921.sql','060921.sql'),(8,0,0,0,'110921.sql','110921.sql'),(9,0,0,0,'180921.sql','180921.sql'),(10,0,0,0,'200921.sql','200921.sql'),(11,0,0,0,'250921.sql','250921.sql'),(12,0,0,0,'280921.sql','280921.sql'),(13,0,0,0,'031021.sql','031021.sql'),(14,0,0,0,'191021.sql','191021.sql'),(15,0,0,0,'211021.sql','211021.sql'),(16,0,0,0,'251021.sql','251021.sql'),(17,0,0,0,'261021.sql','261021.sql'),(18,0,0,0,'281021.sql','281021.sql'),(19,0,0,0,'301021.sql','301021.sql'),(20,0,0,0,'311021.sql','311021.sql'),(21,0,0,0,'061121.sql','061121.sql'),(22,0,0,0,'151121.sql','151121.sql'),(23,0,0,0,'211121.sql','211121.sql'),(24,0,0,0,'261121.sql','261121.sql'),(25,0,0,0,'221221.sql','221221.sql'),(26,0,0,0,'010122.sql','010122.sql'),(27,0,0,0,'150122.sql','150122.sql'),(28,0,0,0,'210122.sql','210122.sql'),(29,0,0,0,'230122.sql','230122.sql'),(30,0,0,0,'240122.sql','240122.sql'),(31,0,0,0,'110222_2.sql','110222_2.sql'),(32,0,0,0,'230222.sql','230222.sql'),(33,0,0,0,'240222.sql','240222.sql'),(34,0,0,0,'080322.sql','080322.sql'),(35,0,0,0,'260322.sql','260322.sql'),(36,0,0,0,'030422.sql','030422.sql'),(37,0,0,0,'120422.sql','120422.sql'),(38,0,0,0,'150422.sql','150422.sql'),(39,0,0,0,'190422.sql','190422.sql'),(40,0,0,0,'230422.sql','230422.sql'),(41,0,0,0,'260422.sql','260422.sql'),(42,0,0,0,'030522.sql','030522.sql'),(43,0,0,0,'040522.sql','040522.sql'),(44,0,0,0,'160522.sql','160522.sql'),(45,0,0,0,'170522.sql','170522.sql'),(46,0,0,0,'300522.sql','300522.sql'),(47,0,0,0,'310522_2.sql','310522_2.sql'),(48,0,0,0,'040622.sql','040622.sql'),(49,0,0,0,'060622.sql','060622.sql'),(50,0,0,0,'070622.sql','070622.sql'),(51,0,0,0,'110622.sql','110622.sql'),(52,0,0,0,'160622.sql','160622.sql'),(53,0,0,0,'270622.sql','270622.sql'),(54,0,0,0,'280622.sql','280622.sql'),(55,0,0,0,'120722.sql','120722.sql'),(56,0,0,0,'150722.sql','150722.sql'),(57,0,0,0,'200722.sql','200722.sql'),(58,0,0,0,'220722.sql','220722.sql'),(59,0,0,0,'230722.sql','230722.sql'),(60,0,0,0,'250722.sql','250722.sql'),(61,0,0,0,'260722.sql','260722.sql'),(62,0,0,0,'070822.sql','070822.sql'),(63,0,0,0,'230922.sql','230922.sql'),(64,0,0,0,'111022.sql','111022.sql'),(65,0,0,0,'141022.sql','141022.sql'),(66,0,0,0,'201022.sql','201022.sql'),(67,0,0,0,'221022.sql','221022.sql'),(68,0,0,0,'061122.sql','061122.sql'),(69,0,0,0,'081222.sql','081222.sql'),(70,0,0,0,'101222.sql','101222.sql'),(71,0,0,0,'131222.sql','131222.sql'),(72,0,0,0,'211222.sql','211222.sql'),(73,0,0,0,'210123.sql','210123.sql'),(74,0,0,0,'170223.sql','170223.sql'),(75,0,0,0,'180223.sql','180223.sql'),(76,0,0,0,'010323.sql','010323.sql'),(77,0,0,0,'090323.sql','090323.sql'),(78,0,0,0,'100323.sql','100323.sql'),(79,0,0,0,'140323.sql','140323.sql'),(80,0,0,0,'150323.sql.sql','150323.sql.sql'),(81,0,0,0,'170323.sql','170323.sql'),(82,0,0,0,'190323.sql','190323.sql'),(83,0,0,0,'150323.sql','150323.sql'),(84,0,0,0,'210323.sql','210323.sql'),(85,0,0,0,'230323.sql','230323.sql'),(86,0,0,0,'270323.sql','270323.sql'),(87,0,0,0,'310323.sql','310323.sql'),(88,0,0,0,'040923.sql','040923.sql'),(89,0,0,0,'250923.sql','250923.sql'),(90,0,0,0,'260923.sql','260923.sql'),(91,0,0,0,'071023.sql','071023.sql'),(92,0,0,0,'010124.sql','010124.sql'),(93,0,0,0,'020224.sql','020224.sql'),(94,0,0,0,'070224.sql','070224.sql'),(95,0,0,0,'110224.sql','110224.sql'),(96,0,0,0,'120224.sql','120224.sql'),(97,0,0,0,'130224.sql','130224.sql'),(98,0,0,0,'140224.sql','140224.sql'),(99,0,0,0,'150224.sql','150224.sql'),(100,0,0,0,'250224.sql','250224.sql'),(101,0,0,0,'200524.sql','200524.sql'),(102,0,0,0,'300524.sql','300524.sql'),(103,0,0,0,'260724.sql','260724.sql'),(104,0,0,0,'300824.sql','300824.sql'),(105,0,0,0,'020725.sql','020725.sql');
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
  `messages_in` char(50) DEFAULT NULL,
  `nodes_battery` char(50) DEFAULT NULL,
  `gateway_logs` char(50) DEFAULT NULL,
  `relay_logs` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_cleanup`
--

LOCK TABLES `db_cleanup` WRITE;
/*!40000 ALTER TABLE `db_cleanup` DISABLE KEYS */;
INSERT INTO `db_cleanup` VALUES (1,0,0,'3 DAY','3 MONTH','3 DAY','2 DAY');
/*!40000 ALTER TABLE `db_cleanup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ebus_device`
--

DROP TABLE IF EXISTS `ebus_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ebus_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) DEFAULT NULL COMMENT 'serial or wifi',
  `location` char(50) NOT NULL COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) NOT NULL COMMENT 'port number 5000 or baud rate 2048 for serial gateway',
  `timeout` char(50) NOT NULL,
  `pid` char(50) DEFAULT NULL,
  `pid_running_since` char(50) DEFAULT NULL,
  `manufacturer` char(10) DEFAULT NULL COMMENT 'device supplier',
  `device_id` char(10) DEFAULT NULL COMMENT 'device id',
  `sw_ver` char(10) DEFAULT NULL COMMENT 'software version',
  `hw_ver` char(10) DEFAULT NULL COMMENT 'hardwareware version',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ebus_device`
--

LOCK TABLES `ebus_device` WRITE;
/*!40000 ALTER TABLE `ebus_device` DISABLE KEYS */;
INSERT INTO `ebus_device` VALUES (2,1,1,0,'wifi','10.0.0.102','5000','3','2821','Wed Feb 12 14:30:32 2025','B5','E7C00','02.12','74.02');
/*!40000 ALTER TABLE `ebus_device` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ebus_messages`
--

DROP TABLE IF EXISTS `ebus_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ebus_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `message` char(50) DEFAULT NULL,
  `sensor_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `offset` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ebus_messages`
--

LOCK TABLES `ebus_messages` WRITE;
/*!40000 ALTER TABLE `ebus_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ebus_messages` ENABLE KEYS */;
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
  `smtp` char(50) DEFAULT NULL,
  `port` int(11) NOT NULL,
  `username` char(50) DEFAULT NULL,
  `password` char(50) DEFAULT NULL,
  `from` char(50) DEFAULT NULL,
  `to` char(50) DEFAULT NULL,
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
  `type` char(50) DEFAULT NULL COMMENT 'virtual, serial or wifi',
  `location` char(50) NOT NULL COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) NOT NULL COMMENT 'port number 5003 or baud rate115200 for serial gateway',
  `timout` char(50) NOT NULL,
  `heartbeat_timeout` char(50) NOT NULL,
  `pid` char(50) DEFAULT NULL,
  `pid_running_since` char(50) DEFAULT NULL,
  `reboot` tinyint(4) DEFAULT NULL,
  `find_gw` tinyint(4) DEFAULT NULL,
  `version` char(50) DEFAULT NULL,
  `enable_outgoing` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gateway`
--

LOCK TABLES `gateway` WRITE;
/*!40000 ALTER TABLE `gateway` DISABLE KEYS */;
INSERT INTO `gateway` VALUES (1,1,0,0,'virtual','/dev/ttyAMA0','115200','3','60','1809177','Thu Jul 10 17:41:06 2025',0,0,'1.0',1);
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
  `type` char(50) DEFAULT NULL COMMENT 'serial or wifi',
  `location` char(50) DEFAULT NULL COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) DEFAULT NULL COMMENT 'port number or baud rate for serial gateway',
  `pid` char(50) DEFAULT NULL,
  `pid_start_time` char(50) DEFAULT NULL,
  `pid_datetime` timestamp NULL DEFAULT NULL,
  `mqtt_sent` int(11) DEFAULT 0,
  `mqtt_recv` int(11) DEFAULT 0,
  `mysensors_sent` int(11) DEFAULT 0,
  `mysensors_recv` int(11) DEFAULT 0,
  `gpio_sent` int(11) DEFAULT 0,
  `gpio_recv` int(11) DEFAULT 0,
  `heartbeat` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gateway_logs`
--

LOCK TABLES `gateway_logs` WRITE;
/*!40000 ALTER TABLE `gateway_logs` DISABLE KEYS */;
INSERT INTO `gateway_logs` VALUES (2,0,0,'virtual','/dev/ttyAMA0','115200','129447','Wed Mar 22 07:55:55 2023','2023-03-22 07:55:56',0,0,0,0,0,0,'2025-07-10 16:41:01'),(3,0,0,'virtual','/dev/ttyAMA0','115200','129651','Wed Mar 22 07:57:16 2023','2023-03-22 07:57:16',0,0,0,0,0,0,'2025-07-10 16:41:01'),(4,0,0,'virtual','/dev/ttyAMA0','115200','1809177','Thu Jul 10 17:41:06 2025','2025-07-10 16:41:06',0,0,0,0,0,0,'2025-07-10 16:46:34');
/*!40000 ALTER TABLE `gateway_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gateway_transactions`
--

DROP TABLE IF EXISTS `gateway_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gateway_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `mqtt_sent` int(11) NOT NULL,
  `mqtt_recv` int(11) NOT NULL,
  `mysensor_sent` int(11) NOT NULL,
  `mysensor_recv` int(11) NOT NULL,
  `gpio_sent` int(11) NOT NULL,
  `gpio_recv` int(11) NOT NULL,
  `heartbeat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gateway_transactions`
--

LOCK TABLES `gateway_transactions` WRITE;
/*!40000 ALTER TABLE `gateway_transactions` DISABLE KEYS */;
INSERT INTO `gateway_transactions` VALUES (1,0,0,108,27,600,39,0,0,'2024-02-01 11:16:01');
/*!40000 ALTER TABLE `gateway_transactions` ENABLE KEYS */;
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
  `archive_enable` tinyint(1) NOT NULL DEFAULT 0,
  `archive_file` varchar(50) NOT NULL,
  `archive_pointer` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graphs`
--

LOCK TABLES `graphs` WRITE;
/*!40000 ALTER TABLE `graphs` DISABLE KEYS */;
INSERT INTO `graphs` VALUES (1,0,0,57,0,'','2025-07-09 17:41:01');
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
  `zone_id` int(11) NOT NULL,
  `node_id` char(50) CHARACTER SET utf16 COLLATE utf16_bin DEFAULT NULL,
  `message_type` char(50) DEFAULT NULL,
  `command` char(50) DEFAULT NULL,
  `parameter` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'controller','/var/www/cron/controller.php',0,0,'60','[36m2023-03-22 08:01:57[0m - Controller Script Started \n----------------------------------------------------------------------------------------------------\n[36m2023-03-22 08:01:57[0m - Controller Script Ended \n[32m****************************************************************************************************[0m  \n','2023-03-22 08:01:57'),(2,'db_cleanup','/var/www/cron/db_cleanup.php',1,0,'02:00','[36m\n           __  __                             _         \n          |  \\/  |                    /\\     (_)        \n          | \\  / |   __ _  __  __    /  \\     _   _ __  \n          | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n          | |  | | | (_| |  >  <   / ____ \\  | | | |    \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n                [45m S M A R T   T H E R M O S T A T [0m \n[31m*************************************************************\n* Database Cleanup Script Version 0.1 Build Date 13/05/2018 *\n* Update on 10/04/218                                       *\n*                                      Have Fun - PiHome.eu *\n*************************************************************\n [0m \n[36m2023-03-22 07:55:56[0m - Database Cleanup Script Started \n[36m2023-03-22 07:55:56[0m - Temperature Records Deleted from Tables \n[36m2023-03-22 07:55:56[0m - Node Battery Records Deleted from Tables \n[36m2023-03-22 07:55:56[0m - Orphaned Node Battery Records Deleted from Tables \n[36m2023-03-22 07:55:56[0m - Gateway Logs Records Deleted from Tables \n','2023-03-22 07:55:56'),(3,'check_gw','/var/www/cron/check_gw.php',1,0,'60','[36m\n           __  __                             _         \n          |  \\/  |                    /\\     (_)        \n          | \\  / |   __ _  __  __    /  \\     _   _ __  \n          | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n          | |  | | | (_| |  >  <   / ____ \\  | | | |    \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n                [45m S M A R T   T H E R M O S T A T [0m \n[31m********************************************************\n*   Gateway Script Version 0.3 Build Date 22/01/2018   *\n*          Last Modification Date 12/11/2022           *\n*                                Have Fun - PiHome.eu  *\n********************************************************\n [0m \n[36m2025-07-10 17:46:11[0m - Python Gateway Script Status Check Script Started \n[36m2025-07-10 17:46:11[0m - Gateway Connection Lost in Last 10 minutes: 1 \n[36m2025-07-10 17:46:12[0m - Python Gateway Script for Gateway is [0;32;40mRunning[0m \n[36m2025-07-10 17:46:12[0m - The PID is: [0;32;40m1809177[0m \n--------------------------------------------------------------------------\n[36m2025-07-10 17:46:12[0m - Python Gateway Script Status Check Script Ended \n[32m***************************************************************************[0m\n','2025-07-10 16:46:12'),(4,'system_c','/var/www/cron/system_c.php',1,0,'300','[36m\n       __  __                             _         \n      |  \\/  |                    /\\     (_)        \n      | \\  / |   __ _  __  __    /  \\     _   _ __  \n      | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n      | |  | | | (_| |  >  <   / ____ \\  | | | |    \n      |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n            [45m S M A R T   T H E R M O S T A T [0m \n[31m********************************************************\n* System Temperature Version 0.5 Build Date 31/03/2018 *\n* Update on 10/06/2021                                 *\n*                                 Have Fun - PiHome.eu *\n********************************************************\n [0m \n[36m2025-07-10 17:46:17[0m - System Temperature: 65\n','2025-07-10 16:46:17'),(5,'weather_update','/var/www/cron/weather_update.php',1,0,'1800','[36m\n           __  __                             _         \n          |  \\/  |                    /\\     (_)        \n          | \\  / |   __ _  __  __    /  \\     _   _ __  \n          | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n          | |  | | | (_| |  >  <   / ____ \\  | | | |    \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n                [45m S M A R T   T H E R M O S T A T [0m \n[31m************************************************************\n* Weather Update Script Version 0.11 Build Date 31/01/2018 *\n* Update on 27/01/2020                                     *\n*                                     Have Fun - PiHome.eu *\n************************************************************\n [0m \n[36m2023-03-22 07:55:56[0m - Weather Update Script Started \n[36m2023-03-22 07:55:56[0m - Weather Data Downloaded \n[36m2023-03-22 07:55:56[0m - Current Weather Temperature 6  \n[36m2023-03-22 07:55:56[0m - Database Updated \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-22 09:00:00 \n[1;33mMin Temperature for day: [0m0 5.91  \n[1;33mMax Temperature for day: [0m0 6.75 \n[1;33mWeather: [0m                Clouds - scattered clouds \n[1;33mCloud %: [0m                31 \n[1;33mWind Speed %: [0m           6.33 \n[1;33mHumidity : [0m              83 \n[1;33mIcon : [0m                  03d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-22 12:00:00 \n[1;33mMin Temperature for day: [0m1 6.4  \n[1;33mMax Temperature for day: [0m1 7.37 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                42 \n[1;33mWind Speed %: [0m           6.54 \n[1;33mHumidity : [0m              84 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-22 15:00:00 \n[1;33mMin Temperature for day: [0m2 7.98  \n[1;33mMax Temperature for day: [0m2 9.02 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                69 \n[1;33mWind Speed %: [0m           7.66 \n[1;33mHumidity : [0m              83 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-22 18:00:00 \n[1;33mMin Temperature for day: [0m3 7.08  \n[1;33mMax Temperature for day: [0m3 7.08 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                92 \n[1;33mWind Speed %: [0m           7.11 \n[1;33mHumidity : [0m              87 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-22 21:00:00 \n[1;33mMin Temperature for day: [0m4 5.35  \n[1;33mMax Temperature for day: [0m4 5.35 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                67 \n[1;33mWind Speed %: [0m           7.39 \n[1;33mHumidity : [0m              88 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-23 00:00:00 \n[1;33mMin Temperature for day: [0m5 3.74  \n[1;33mMax Temperature for day: [0m5 3.74 \n[1;33mWeather: [0m                Clouds - scattered clouds \n[1;33mCloud %: [0m                46 \n[1;33mWind Speed %: [0m           5.66 \n[1;33mHumidity : [0m              90 \n[1;33mIcon : [0m                  03n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-23 03:00:00 \n[1;33mMin Temperature for day: [0m6 6.79  \n[1;33mMax Temperature for day: [0m6 6.79 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                85 \n[1;33mWind Speed %: [0m           9.07 \n[1;33mHumidity : [0m              83 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-23 06:00:00 \n[1;33mMin Temperature for day: [0m7 7  \n[1;33mMax Temperature for day: [0m7 7 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                92 \n[1;33mWind Speed %: [0m           6.17 \n[1;33mHumidity : [0m              89 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-23 09:00:00 \n[1;33mMin Temperature for day: [0m8 8.07  \n[1;33mMax Temperature for day: [0m8 8.07 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                85 \n[1;33mWind Speed %: [0m           6.96 \n[1;33mHumidity : [0m              83 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-23 12:00:00 \n[1;33mMin Temperature for day: [0m9 10.51  \n[1;33mMax Temperature for day: [0m9 10.51 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                92 \n[1;33mWind Speed %: [0m           8.22 \n[1;33mHumidity : [0m              78 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-23 15:00:00 \n[1;33mMin Temperature for day: [0m10 9.92  \n[1;33mMax Temperature for day: [0m10 9.92 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                93 \n[1;33mWind Speed %: [0m           7.3 \n[1;33mHumidity : [0m              83 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-23 18:00:00 \n[1;33mMin Temperature for day: [0m11 7.83  \n[1;33mMax Temperature for day: [0m11 7.83 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                87 \n[1;33mWind Speed %: [0m           6.4 \n[1;33mHumidity : [0m              90 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-23 21:00:00 \n[1;33mMin Temperature for day: [0m12 6.55  \n[1;33mMax Temperature for day: [0m12 6.55 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                91 \n[1;33mWind Speed %: [0m           6.12 \n[1;33mHumidity : [0m              92 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-24 00:00:00 \n[1;33mMin Temperature for day: [0m13 4.43  \n[1;33mMax Temperature for day: [0m13 4.43 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                79 \n[1;33mWind Speed %: [0m           6.43 \n[1;33mHumidity : [0m              92 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-24 03:00:00 \n[1;33mMin Temperature for day: [0m14 4.07  \n[1;33mMax Temperature for day: [0m14 4.07 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                96 \n[1;33mWind Speed %: [0m           5.39 \n[1;33mHumidity : [0m              92 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-24 06:00:00 \n[1;33mMin Temperature for day: [0m15 5.55  \n[1;33mMax Temperature for day: [0m15 5.55 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                94 \n[1;33mWind Speed %: [0m           7.34 \n[1;33mHumidity : [0m              87 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-24 09:00:00 \n[1;33mMin Temperature for day: [0m16 7.86  \n[1;33mMax Temperature for day: [0m16 7.86 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                94 \n[1;33mWind Speed %: [0m           8.01 \n[1;33mHumidity : [0m              80 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-24 12:00:00 \n[1;33mMin Temperature for day: [0m17 8.37  \n[1;33mMax Temperature for day: [0m17 8.37 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                97 \n[1;33mWind Speed %: [0m           9.09 \n[1;33mHumidity : [0m              80 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-24 15:00:00 \n[1;33mMin Temperature for day: [0m18 8.41  \n[1;33mMax Temperature for day: [0m18 8.41 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                98 \n[1;33mWind Speed %: [0m           8.78 \n[1;33mHumidity : [0m              83 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-24 18:00:00 \n[1;33mMin Temperature for day: [0m19 6.97  \n[1;33mMax Temperature for day: [0m19 6.97 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                95 \n[1;33mWind Speed %: [0m           8.16 \n[1;33mHumidity : [0m              84 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-24 21:00:00 \n[1;33mMin Temperature for day: [0m20 6.39  \n[1;33mMax Temperature for day: [0m20 6.39 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                68 \n[1;33mWind Speed %: [0m           8.55 \n[1;33mHumidity : [0m              80 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-25 00:00:00 \n[1;33mMin Temperature for day: [0m21 6.68  \n[1;33mMax Temperature for day: [0m21 6.68 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                80 \n[1;33mWind Speed %: [0m           8.67 \n[1;33mHumidity : [0m              87 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-25 03:00:00 \n[1;33mMin Temperature for day: [0m22 6.92  \n[1;33mMax Temperature for day: [0m22 6.92 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                81 \n[1;33mWind Speed %: [0m           8.26 \n[1;33mHumidity : [0m              86 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-25 06:00:00 \n[1;33mMin Temperature for day: [0m23 5.38  \n[1;33mMax Temperature for day: [0m23 5.38 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                61 \n[1;33mWind Speed %: [0m           5.62 \n[1;33mHumidity : [0m              93 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-25 09:00:00 \n[1;33mMin Temperature for day: [0m24 7.8  \n[1;33mMax Temperature for day: [0m24 7.8 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                70 \n[1;33mWind Speed %: [0m           6.2 \n[1;33mHumidity : [0m              84 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-25 12:00:00 \n[1;33mMin Temperature for day: [0m25 10.47  \n[1;33mMax Temperature for day: [0m25 10.47 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                83 \n[1;33mWind Speed %: [0m           5.22 \n[1;33mHumidity : [0m              70 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-25 15:00:00 \n[1;33mMin Temperature for day: [0m26 9.48  \n[1;33mMax Temperature for day: [0m26 9.48 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                94 \n[1;33mWind Speed %: [0m           4.03 \n[1;33mHumidity : [0m              78 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-25 18:00:00 \n[1;33mMin Temperature for day: [0m27 8.47  \n[1;33mMax Temperature for day: [0m27 8.47 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                95 \n[1;33mWind Speed %: [0m           2.14 \n[1;33mHumidity : [0m              85 \n[1;33mIcon : [0m                  10d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-25 21:00:00 \n[1;33mMin Temperature for day: [0m28 4.98  \n[1;33mMax Temperature for day: [0m28 4.98 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                52 \n[1;33mWind Speed %: [0m           1.1 \n[1;33mHumidity : [0m              93 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-26 00:00:00 \n[1;33mMin Temperature for day: [0m29 3.79  \n[1;33mMax Temperature for day: [0m29 3.79 \n[1;33mWeather: [0m                Clouds - scattered clouds \n[1;33mCloud %: [0m                34 \n[1;33mWind Speed %: [0m           0.99 \n[1;33mHumidity : [0m              95 \n[1;33mIcon : [0m                  03n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-26 03:00:00 \n[1;33mMin Temperature for day: [0m30 3.27  \n[1;33mMax Temperature for day: [0m30 3.27 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                62 \n[1;33mWind Speed %: [0m           2.42 \n[1;33mHumidity : [0m              98 \n[1;33mIcon : [0m                  04n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-26 06:00:00 \n[1;33mMin Temperature for day: [0m31 3.94  \n[1;33mMax Temperature for day: [0m31 3.94 \n[1;33mWeather: [0m                Rain - light rain \n[1;33mCloud %: [0m                72 \n[1;33mWind Speed %: [0m           4.41 \n[1;33mHumidity : [0m              97 \n[1;33mIcon : [0m                  10n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-26 09:00:00 \n[1;33mMin Temperature for day: [0m32 4.62  \n[1;33mMax Temperature for day: [0m32 4.62 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                97 \n[1;33mWind Speed %: [0m           6.15 \n[1;33mHumidity : [0m              93 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-26 12:00:00 \n[1;33mMin Temperature for day: [0m33 6.59  \n[1;33mMax Temperature for day: [0m33 6.59 \n[1;33mWeather: [0m                Clouds - overcast clouds \n[1;33mCloud %: [0m                93 \n[1;33mWind Speed %: [0m           6.12 \n[1;33mHumidity : [0m              68 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-26 15:00:00 \n[1;33mMin Temperature for day: [0m34 8.43  \n[1;33mMax Temperature for day: [0m34 8.43 \n[1;33mWeather: [0m                Clouds - broken clouds \n[1;33mCloud %: [0m                59 \n[1;33mWind Speed %: [0m           7.73 \n[1;33mHumidity : [0m              58 \n[1;33mIcon : [0m                  04d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-26 18:00:00 \n[1;33mMin Temperature for day: [0m35 4.98  \n[1;33mMax Temperature for day: [0m35 4.98 \n[1;33mWeather: [0m                Clouds - scattered clouds \n[1;33mCloud %: [0m                39 \n[1;33mWind Speed %: [0m           5.86 \n[1;33mHumidity : [0m              62 \n[1;33mIcon : [0m                  03d \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-26 21:00:00 \n[1;33mMin Temperature for day: [0m36 0.04  \n[1;33mMax Temperature for day: [0m36 0.04 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                5 \n[1;33mWind Speed %: [0m           2.74 \n[1;33mHumidity : [0m              76 \n[1;33mIcon : [0m                  01n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-27 00:00:00 \n[1;33mMin Temperature for day: [0m37 -1.43  \n[1;33mMax Temperature for day: [0m37 -1.43 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                3 \n[1;33mWind Speed %: [0m           2.29 \n[1;33mHumidity : [0m              81 \n[1;33mIcon : [0m                  01n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-27 03:00:00 \n[1;33mMin Temperature for day: [0m38 -2.06  \n[1;33mMax Temperature for day: [0m38 -2.06 \n[1;33mWeather: [0m                Clear - clear sky \n[1;33mCloud %: [0m                3 \n[1;33mWind Speed %: [0m           1.72 \n[1;33mHumidity : [0m              80 \n[1;33mIcon : [0m                  01n \n[31m------------------------------------------------------[0m \n[1;33mDate and Time: [0m          2023-03-27 06:00:00 \n[1;33mMin Temperature for day: [0m39 -2.42  \n[1;33mMax Temperature for day: [0m39 -2.42 \n[1;33mWeather: [0m                Clouds - few clouds \n[1;33mCloud %: [0m                21 \n[1;33mWind Speed %: [0m           0.86 \n[1;33mHumidity : [0m              80 \n[1;33mIcon : [0m                  02n \n \n[36m2023-03-22 07:55:57[0m - Weather Data Downloaded \n  \n[36m2023-03-22 07:55:57[0m - Weather Update Script Finished \n','2023-03-22 07:55:57'),(6,'reboot_wifi','/var/www/cron/reboot_wifi.sh',1,0,'120','           __  __                             _        \n          |  \\/  |                    /\\     (_)       \n          | \\  / |   __ _  __  __    /  \\     _   _ __ \n          | |\\/| |  / _\' | \\ \\/ /   / /\\ \\   | | |  __|\n          | |  | | | (_| |  >  <   / ____ \\  | | | |   \n          |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|   \n\n                S M A R T   T H E R M O S T A T \n*************************************************************************\n* MaxAir is LINUX  based Central Heating Control systems. It runs from  *\n* a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *\n* extent permitted by applicable law. I take no responsibility for any  *\n* loss or damage to you or your property.                               *\n* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTIL UNLESS YOU KNOW  *\n* WHAT YOU ARE DOING                                                    *\n*************************************************************************\n\n                                                       Have Fun - PiHome \n - Auto Reconnect Wi-Fi Status for wlan0 Script Started \n   Thu Jul 10 05:46:08 PM BST 2025\n\n*************************************************************************\n\nProcess still running, Lockfile valid\n','2025-07-10 16:46:08'),(7,'check_ds18b20','/var/www/cron/check_ds18b20.php',0,0,'60','','2021-11-23 13:43:24'),(8,'sw_install','/var/www/cron/sw_install.py',1,0,'10','[0;36;40m \n    __  __                             _         \n   |  \\/  |                    /\\     (_)        \n   | \\  / |   __ _  __  __    /  \\     _   _ __  \n   | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n   | |  | | | (_| |  >  <   / ____ \\  | | | |    \n   |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n \n        [3;30;45mS M A R T   T H E R M O S T A T [0m\n[0;31;40m \n********************************************************\n*       Background Installer for Add_On software       *\n*                                                      *\n*                                                      *\n*      Build Date: 03/03/2021                          *\n*      Version 0.01 - Last Modified 03/04/2021         *\n*                                 Have Fun - PiHome.eu *\n********************************************************\n [0m\n[0;36;40mThu Jul 10 17:46:26 2025[0m - Software Install Script Started\n--------------------------------------------------------------------\nNothing to Install\n[0;36;40mThu Jul 10 17:46:26 2025[0m - Software Install Script Ended\n--------------------------------------------------------------------\n','2025-07-10 16:46:26'),(9,'update_code','/var/www/cron/update_code.py',1,0,'00:00','[95m \n    __  __                             _         \n   |  \\/  |                    /\\     (_)        \n   | \\  / |   __ _  __  __    /  \\     _   _ __  \n   | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n   | |  | | | (_| |  >  <   / ____ \\  | | | |    \n   |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n \n        [3;30;45mS M A R T   T H E R M O S T A T [0m\n[0;31;40m \n********************************************************\n* Compare installed code against GITHUB repository and *\n* download any new or changed files, for later update. *\n*                                                      *\n*      Build Date: 02/08/2021                          *\n*      Version 0.04 - Last Modified 11/10/2021         *\n*                                 Have Fun - PiHome.eu *\n********************************************************\n [0m\n--------------------------------------------------------\n[0;36;40mWed Mar 22 07:56:16 2023[0m - Code Update Script Started\n--------------------------------------------------------\n/var/www/README.md\n/var/www/ajax.php\n/var/www/ajax_fetch_data.php\n/var/www/db.php\n/var/www/homelist.php\n/var/www/model.php\n/var/www/sensor.php\n/var/www/settingslist.php\n/var/www/setup.php\n/var/www/add_on/Image_Tools/install.sh\n/var/www/api/binarySet.php\n/var/www/cron/auto_image.py\n/var/www/cron/controller.php\n/var/www/cron/gateway.py\n/var/www/cron/gpio_ds18b20.py\n/var/www/cron/gpio_switch.py\n/var/www/cron/move_files.py\n/var/www/cron/ebus/ebus.py\n/var/www/js/request.js\n/var/www/languages/de.php\n/var/www/languages/en.php\n/var/www/languages/fr.php\n/var/www/languages/it.php\n/var/www/languages/lv.php\n/var/www/languages/pt.php\n/var/www/languages/ro.php\n/var/www/languages/sk.php\n/var/www/st_inc/db_config.ini\n--------------------------------------------------------\n[0;36;40mWed Mar 22 07:57:16 2023[0m - Code Update Script Ended\n--------------------------------------------------------\n','2023-03-22 07:57:16'),(10,'check_gpio_switch','/var/www/cron/check_gpio_switch.php',0,0,'60','','2021-11-23 13:43:25'),(11,'notice','/var/www/cron/notice.py',0,0,'60','','2023-01-29 12:33:18'),(12,'auto_backup','/var/www/cron/auto_backup.py',1,0,'01:00','[95m \n    __  __                             _         \n   |  \\/  |                    /\\     (_)        \n   | \\  / |   __ _  __  __    /  \\     _   _ __  \n   | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n   | |  | | | (_| |  >  <   / ____ \\  | | | |    \n   |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n \n             [3;30;45mS M A R T   THERMOSTAT [0m\n********************************************************\n*    Script Auto Backup Database to a gz file and      *\n*                send as an Email message.             *\n*                Build Date: 07/06/2022                *\n*      Version 0.04 - Last Modified 27/06/2022         *\n*                                 Have Fun - PiHome.eu *\n********************************************************\n \n [0m\n[36m2023-03-22 07:57:16[0;37;40m - Database Backup Script NOT Enabled \n\n------------------------------------------------------------------\n','2023-03-22 07:57:16'),(13,'ebus','/var/www/cron/check_ebus.php',0,0,'60','','2023-01-29 12:33:20'),(14,'check_sc','/var/www/cron/check_sc.php',1,0,'60','[36m\n      __  __                             _         \n     |  \\/  |                    /\\     (_)        \n     | \\  / |   __ _  __  __    /  \\     _   _ __  \n     | |\\/| |  / _` | \\ \\/ /   / /\\ \\   | | | \'__| \n     | |  | | | (_| |  >  <   / ____ \\  | | | |    \n     |_|  |_|  \\__,_| /_/\\_\\ /_/    \\_\\ |_| |_|    \n [0m \n          [45m S M A R T   T H E R M O S T A T [0m \n[31m********************************************************\n* Controller Script Version 0.1 Build Date 13/02/2023  *\n*          Last Modification Date 14/02/2023           *\n*                                Have Fun - PiHome.eu  *\n********************************************************\n [0m \n[36m2025-07-10 17:46:18[0m - Python Controller Script Status Check Script Started \n[36m2025-07-10 17:46:18[0m - Python Controller Script is [0;32;40mRunning[0m \n[36m2025-07-10 17:46:18[0m - The PID is: [0;32;40m1809449[0m \n\n[36m2025-07-10 17:46:18[0m - Python Controller Script Status Check Script Ended \n[32m***************************************************************************[0m\n','2025-07-10 16:46:18'),(15,'shutdown_reboot','/var/www/cron/shutdown_reboot.py',1,0,'15','','2023-03-22 07:58:11'),(16,'graphs_to_csv','/var/www/cron/graph_csv.py',1,0,'00:00','','0000-00-00 00:00:00');
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
  `node_id` char(15) DEFAULT NULL,
  `child_id` tinyint(4) DEFAULT NULL,
  `sub_type` int(11) DEFAULT NULL,
  `payload` decimal(10,2) DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages_in`
--

LOCK TABLES `messages_in` WRITE;
/*!40000 ALTER TABLE `messages_in` DISABLE KEYS */;
INSERT INTO `messages_in` VALUES (9,0,0,'0',0,0,42.00,'2023-03-22 07:55:56'),(10,0,0,'1',0,0,6.00,'2023-03-22 07:55:56'),(11,0,0,'0',0,0,40.00,'2023-03-22 08:00:57'),(12,0,0,'0',0,0,65.00,'2025-07-10 16:41:16'),(13,0,0,'0',0,0,65.00,'2025-07-10 16:46:17');
/*!40000 ALTER TABLE `messages_in` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `messages_in_view_1h`
--

DROP TABLE IF EXISTS `messages_in_view_1h`;
/*!50001 DROP VIEW IF EXISTS `messages_in_view_1h`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `messages_in_view_1h` AS SELECT
 1 AS `id`,
  1 AS `node_id`,
  1 AS `child_id`,
  1 AS `datetime`,
  1 AS `payload` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `messages_in_view_24h`
--

DROP TABLE IF EXISTS `messages_in_view_24h`;
/*!50001 DROP VIEW IF EXISTS `messages_in_view_24h`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `messages_in_view_24h` AS SELECT
 1 AS `id`,
  1 AS `node_id`,
  1 AS `child_id`,
  1 AS `datetime`,
  1 AS `payload` */;
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
  `n_id` int(11) NOT NULL,
  `node_id` char(50) CHARACTER SET utf16 COLLATE utf16_bin NOT NULL,
  `child_id` int(11) NOT NULL COMMENT 'Child Sensor',
  `sub_type` int(11) NOT NULL COMMENT 'Command Type',
  `ack` int(11) NOT NULL COMMENT 'Ack Req/Resp',
  `type` int(11) NOT NULL COMMENT 'Type',
  `payload` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Payload',
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
INSERT INTO `messages_out` VALUES (29,0,0,23,'0',15,1,1,2,'0',1,'2025-07-10 16:41:07',0),(30,0,0,23,'0',13,1,1,2,'0',1,'2025-07-10 16:45:19',38),(32,0,0,23,'0',11,1,1,2,'0',1,'2025-07-10 16:45:19',39);
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
  `name` varchar(50) NOT NULL,
  `ip` varchar(39) NOT NULL,
  `port` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
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
  `name` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `mqtt_topic` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL COMMENT 'Relay payload for on command',
  `on_payload` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL COMMENT 'Relay payload for on command',
  `off_payload` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL COMMENT 'Relay payload for on command',
  `attribute` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL COMMENT 'Sensor JSON attribute',
  `last_seen` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `notice_interval` int(11) NOT NULL DEFAULT 0,
  `min_value` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_mqtt_node_child_nodes` (`nodes_id`),
  CONSTRAINT `FK_mqtt_node_child_nodes` FOREIGN KEY (`nodes_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `interface_type` char(50) DEFAULT NULL,
  `mac_address` char(50) DEFAULT NULL,
  `hostname` char(50) DEFAULT NULL,
  `ip_address` char(50) DEFAULT NULL,
  `gateway_address` char(50) DEFAULT NULL,
  `net_mask` char(50) DEFAULT NULL,
  `dns1_address` char(50) DEFAULT NULL,
  `dns2_address` char(50) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `type` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `node_id` char(50) NOT NULL,
  `max_child_id` int(11) NOT NULL,
  `sub_type` int(11) NOT NULL,
  `name` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `notice_interval` int(11) NOT NULL,
  `min_value` int(11) DEFAULT NULL,
  `status` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `ms_version` char(50) DEFAULT NULL,
  `sketch_version` char(50) DEFAULT NULL,
  `repeater` tinyint(4) DEFAULT NULL COMMENT 'Repeater Feature Enabled=1 or Disable=0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodes`
--

LOCK TABLES `nodes` WRITE;
/*!40000 ALTER TABLE `nodes` DISABLE KEYS */;
INSERT INTO `nodes` VALUES (23,0,0,'GPIO','0',0,0,'GPIO Controller','2025-07-10 16:45:19',0,0,'Active','0','0',0),(24,0,0,'MySensor','21',0,0,'Temperature Sensor','2020-10-20 15:45:28',0,0,'Active','2.3.2','0.32',0),(25,0,0,'MySensor','28',0,0,'Temperature Sensor','2021-11-23 13:44:50',30,0,'Active','2.3.2','0.35',0),(26,0,0,'MySensor','34',0,0,'Temperature Sensor','2021-11-23 13:44:55',30,0,'Active','2.3.2','0.35',0);
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
  `node_id` char(50) DEFAULT NULL,
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
  `message` varchar(200) DEFAULT NULL,
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
-- Table structure for table `notice_sensors`
--

DROP TABLE IF EXISTS `notice_sensors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notice_sensors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL,
  `sensor_id` int(11) DEFAULT NULL,
  `min` decimal(10,2) DEFAULT NULL,
  `max` decimal(10,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_notice-sensors_sensors` (`sensor_id`),
  CONSTRAINT `FK_notice-sensors_sensors` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notice_sensors`
--

LOCK TABLES `notice_sensors` WRITE;
/*!40000 ALTER TABLE `notice_sensors` DISABLE KEYS */;
/*!40000 ALTER TABLE `notice_sensors` ENABLE KEYS */;
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
  `protocol` varchar(50) DEFAULT NULL,
  `url` varchar(50) DEFAULT NULL,
  `script` char(50) DEFAULT NULL,
  `api_key` varchar(200) DEFAULT NULL,
  `version` char(50) DEFAULT NULL,
  `build` char(50) DEFAULT NULL,
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
  `picurl` char(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_type` char(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `http_code` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `header_size` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `request_size` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `filetime` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ssl_verify_result` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `redirect_count` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_time` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `connect_time` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pretransfer_time` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `size_upload` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `size_download` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `speed_download` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `speed_upload` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `download_content_length` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `upload_content_length` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `starttransfer_time` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `primary_port` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `local_port` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `start_time` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `end_time` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `n_tables` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `records` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `piconnect_logs`
--

LOCK TABLES `piconnect_logs` WRITE;
/*!40000 ALTER TABLE `piconnect_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `piconnect_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relay_logs`
--

DROP TABLE IF EXISTS `relay_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relay_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `relay_id` int(11) NOT NULL,
  `relay_name` char(50) DEFAULT NULL,
  `message` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Sent To Relay',
  `zone_name` char(50) DEFAULT NULL,
  `zone_mode` char(50) NOT NULL,
  `datetime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relay_logs`
--

LOCK TABLES `relay_logs` WRITE;
/*!40000 ALTER TABLE `relay_logs` DISABLE KEYS */;
INSERT INTO `relay_logs` VALUES (1,0,0,49,'Central Heating','OFF','Central Heating','Idle - Stopped','2023-03-22 07:57:17'),(2,0,0,50,'Gas Boiler','OFF','System Controller','State Change','2023-03-22 07:57:17'),(3,0,0,48,'Hot Water','OFF','Hot Water','Idle - Stopped','2023-03-22 07:57:17');
/*!40000 ALTER TABLE `relay_logs` ENABLE KEYS */;
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
  `name` char(50) DEFAULT NULL,
  `type` tinyint(1) NOT NULL,
  `on_trigger` tinyint(1) NOT NULL,
  `lag_time` int(11) NOT NULL DEFAULT 0,
  `user_display` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `state` tinyint(1) DEFAULT 0 COMMENT '0 = OFF, 1 = ON',
  `fail_timeout` int(11) NOT NULL,
  `last_seen` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_relays_nodes` (`relay_id`),
  CONSTRAINT `FK_relays_nodes` FOREIGN KEY (`relay_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relays`
--

LOCK TABLES `relays` WRITE;
/*!40000 ALTER TABLE `relays` DISABLE KEYS */;
INSERT INTO `relays` VALUES (48,0,0,23,11,'Hot Water',0,1,0,0,0,0,NULL),(49,0,0,23,13,'Central Heating',0,1,0,0,0,0,NULL),(50,0,0,23,15,'Gas Boiler',1,1,0,0,0,0,NULL);
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
  `name` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repository`
--

LOCK TABLES `repository` WRITE;
/*!40000 ALTER TABLE `repository` DISABLE KEYS */;
INSERT INTO `repository` VALUES (1,0,0,1,'pihome-shc/PiHomeHVAC'),(2,0,0,0,'twa127/PiHomeHVAC'),(3,0,0,0,'dvdcut/PiHomeHVAC'),(4,0,0,0,'JSa1987/PiHomeHVAC'),(5,0,0,0,'mjhumphrey/PiHomeHVAC'),(6,0,0,0,'sandreialexandru/PiHomeHVAC');
/*!40000 ALTER TABLE `repository` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reset`
--

DROP TABLE IF EXISTS `reset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `type` text DEFAULT NULL COMMENT 'WEB/MAN/AUTO',
  `reset_count` int(4) NOT NULL,
  `start_datetime` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_datetime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19122 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reset`
--

LOCK TABLES `reset` WRITE;
/*!40000 ALTER TABLE `reset` DISABLE KEYS */;
INSERT INTO `reset` VALUES (19000,0,0,0,'MAN',0,'2022-11-16 11:03:32','2022-11-16 11:03:32'),(19001,0,0,0,'MAN',0,'2022-11-16 11:03:52','2022-11-16 11:03:52'),(19002,0,0,0,'MAN',0,'2022-11-16 11:04:03','2022-11-16 11:04:03'),(19003,0,0,0,'MAN',0,'2022-11-16 11:04:23','2022-11-16 11:04:23'),(19004,0,0,0,'MAN',0,'2022-11-16 11:04:34','2022-11-16 11:04:34'),(19005,0,0,0,'MAN',0,'2022-11-16 11:04:51','2022-11-16 11:04:51'),(19006,0,0,0,'MAN',0,'2022-11-16 11:05:02','2022-11-16 11:05:02'),(19007,0,0,0,'MAN',0,'2022-11-16 11:15:47','2022-11-16 11:15:47'),(19008,0,0,0,'MAN',0,'2022-11-16 11:16:08','2022-11-16 11:16:08'),(19009,0,0,0,'MAN',0,'2022-11-16 11:16:19','2022-11-16 11:16:19'),(19010,0,0,0,'WEB',0,'2024-10-02 12:08:58','2024-10-02 12:08:58'),(19011,0,0,0,'AUTO',1,'2024-07-15 17:05:45','2024-07-15 17:05:45'),(19012,0,0,0,'AUTO',1,'2024-07-18 17:59:03','2024-07-18 17:59:03'),(19013,0,0,0,'AUTO',3,'2024-07-18 18:38:10','2024-07-18 18:38:10'),(19014,0,0,0,'AUTO',1,'2024-07-18 18:47:24','2024-07-18 18:47:24'),(19015,0,0,0,'AUTO',1,'2024-07-18 18:59:35','2024-07-18 18:59:35'),(19016,0,0,0,'AUTO',1,'2024-07-18 19:14:18','2024-07-18 19:14:18'),(19017,0,0,0,'AUTO',7,'2024-08-06 16:19:42','2024-08-06 16:19:42'),(19018,0,0,0,'AUTO',2,'2024-08-06 16:37:06','2024-08-06 16:37:06'),(19019,0,0,0,'AUTO',1,'2024-08-08 05:38:30','2024-08-08 05:38:30'),(19020,0,0,0,'AUTO',1,'2024-08-08 10:59:57','2024-08-08 10:59:57'),(19021,0,0,0,'AUTO',1,'2024-08-08 11:27:56','2024-08-08 11:27:56'),(19022,0,0,0,'AUTO',1,'2024-08-23 06:31:39','2024-08-23 06:31:39'),(19023,0,0,0,'AUTO',1,'2024-08-23 07:28:25','2024-08-23 07:28:25'),(19024,0,0,0,'AUTO',1,'2024-08-29 10:23:28','2024-08-29 10:23:28'),(19025,0,0,0,'AUTO',1,'2024-08-31 15:33:30','2024-08-31 15:33:30'),(19026,0,0,0,'AUTO',4,'2024-08-31 16:03:32','2024-08-31 16:03:32'),(19027,0,0,0,'AUTO',1,'2024-08-31 16:15:08','2024-08-31 16:15:08'),(19028,0,0,0,'AUTO',2,'2024-09-01 06:35:41','2024-09-01 06:35:41'),(19029,0,0,0,'AUTO',1,'2024-09-01 06:52:51','2024-09-01 06:52:51'),(19030,0,0,0,'AUTO',2,'2024-09-28 16:26:01','2024-09-28 16:26:01'),(19031,0,0,0,'AUTO',5,'2024-09-28 17:16:18','2024-09-28 17:16:18'),(19032,0,0,0,'AUTO',1,'2024-09-28 19:41:29','2024-09-28 19:41:29'),(19033,0,0,0,'AUTO',9,'2024-09-28 21:21:49','2024-09-28 21:21:49'),(19036,0,0,0,'AUTO',3,'2024-09-29 19:11:50','2024-09-29 19:11:50'),(19037,0,0,0,'AUTO',1,'2024-10-02 12:10:52','2024-10-02 12:10:52'),(19038,0,0,0,'AUTO',3,'2024-10-05 16:30:51','2024-10-05 16:30:51'),(19039,0,0,0,'WEB',0,'2024-10-05 16:25:27','2024-10-05 16:25:27'),(19040,0,0,0,'AUTO',1,'2024-10-08 08:27:37','2024-10-08 08:27:37'),(19041,0,0,0,'WEB',0,'2024-10-08 09:41:49','2024-10-08 09:41:49'),(19042,0,0,0,'AUTO',1,'2024-11-04 06:44:03','2024-11-04 06:44:03'),(19043,0,0,0,'AUTO',1,'2024-11-04 07:41:37','2024-11-04 07:41:37'),(19044,0,0,0,'AUTO',1,'2024-11-15 16:55:12','2024-11-15 16:55:12'),(19045,0,0,0,'AUTO',9,'2024-11-15 18:29:33','2024-11-15 18:29:33'),(19047,0,0,0,'AUTO',1,'2024-11-16 06:38:28','2024-11-16 06:38:28'),(19048,0,0,0,'WEB',0,'2024-11-18 16:26:06','2024-11-18 16:26:06'),(19049,0,0,0,'WEB',0,'2024-11-18 16:26:31','2024-11-18 16:26:31'),(19050,0,0,0,'WEB',0,'2024-11-18 16:30:29','2024-11-18 16:30:29'),(19051,0,0,0,'AUTO',2,'2024-11-18 16:36:18','2024-11-18 16:36:18'),(19052,0,0,0,'WEB',0,'2024-11-18 16:31:19','2024-11-18 16:31:19'),(19053,0,0,0,'AUTO',1,'2024-11-19 16:09:21','2024-11-19 16:09:21'),(19054,0,0,0,'AUTO',1,'2024-11-20 11:28:36','2024-11-20 11:28:36'),(19055,0,0,0,'WEB',0,'2024-11-20 12:09:47','2024-11-20 12:09:47'),(19056,0,0,0,'WEB',0,'2024-11-20 12:25:36','2024-11-20 12:25:36'),(19057,0,0,0,'AUTO',1,'2024-11-20 12:26:28','2024-11-20 12:26:28'),(19058,0,0,0,'WEB',0,'2024-11-20 12:28:30','2024-11-20 12:28:30'),(19059,0,0,0,'WEB',0,'2024-11-20 12:31:25','2024-11-20 12:31:25'),(19060,0,0,0,'WEB',0,'2024-11-23 12:53:05','2024-11-23 12:53:05'),(19061,0,0,0,'WEB',0,'2024-11-23 12:53:51','2024-11-23 12:53:51'),(19062,0,0,0,'WEB',0,'2024-11-23 13:14:41','2024-11-23 13:14:41'),(19063,0,0,0,'WEB',0,'2024-11-23 14:11:51','2024-11-23 14:11:51'),(19064,0,0,0,'WEB',0,'2024-11-23 14:17:32','2024-11-23 14:17:32'),(19065,0,0,0,'WEB',0,'2024-11-27 09:11:45','2024-11-27 09:11:45'),(19066,0,0,0,'WEB',0,'2024-11-27 09:14:59','2024-11-27 09:14:59'),(19067,0,0,0,'WEB',0,'2024-11-27 09:18:15','2024-11-27 09:18:15'),(19068,0,0,0,'AUTO',1,'2024-11-27 09:20:32','2024-11-27 09:20:32'),(19069,0,0,0,'WEB',0,'2024-11-27 09:27:31','2024-11-27 09:27:31'),(19070,0,0,0,'WEB',0,'2024-11-28 08:57:44','2024-11-28 08:57:44'),(19071,0,0,0,'WEB',0,'2024-11-28 13:27:09','2024-11-28 13:27:09'),(19072,0,0,0,'WEB',0,'2024-11-28 13:35:37','2024-11-28 13:35:37'),(19073,0,0,0,'WEB',0,'2024-11-28 13:56:24','2024-11-28 13:56:24'),(19074,0,0,0,'WEB',0,'2024-11-28 14:02:09','2024-11-28 14:02:09'),(19075,0,0,0,'WEB',0,'2024-11-28 14:02:55','2024-11-28 14:02:55'),(19076,0,0,0,'WEB',0,'2024-11-28 14:07:59','2024-11-28 14:07:59'),(19077,0,0,0,'WEB',0,'2024-11-28 14:14:03','2024-11-28 14:14:03'),(19078,0,0,0,'WEB',0,'2024-11-28 15:01:30','2024-11-28 15:01:30'),(19079,0,0,0,'MAN',0,'2024-12-05 09:15:05','2024-12-05 09:15:05'),(19080,0,0,0,'AUTO',1,'2024-12-21 09:51:37','2024-12-21 09:51:37'),(19081,0,0,0,'AUTO',1,'2024-12-21 16:08:47','2024-12-21 16:08:47'),(19082,0,0,0,'AUTO',1,'2025-01-02 16:15:55','2025-01-02 16:15:55'),(19083,0,0,0,'WEB',0,'2025-01-02 17:20:36','2025-01-02 17:20:36'),(19084,0,0,0,'WEB',0,'2025-01-02 17:22:04','2025-01-02 17:22:04'),(19085,0,0,0,'WEB',0,'2025-01-02 17:42:38','2025-01-02 17:42:38'),(19086,0,0,0,'AUTO',1,'2025-01-10 11:17:08','2025-01-10 11:17:08'),(19087,0,0,0,'AUTO',1,'2025-01-12 09:43:14','2025-01-12 09:43:14'),(19088,0,0,0,'WEB',0,'2025-01-12 09:46:39','2025-01-12 09:46:39'),(19089,0,0,0,'WEB',0,'2025-01-12 09:48:50','2025-01-12 09:48:50'),(19090,0,0,0,'WEB',0,'2025-01-12 09:54:53','2025-01-12 09:54:53'),(19091,0,0,0,'WEB',0,'2025-01-12 09:59:26','2025-01-12 09:59:26'),(19092,0,0,0,'WEB',0,'2025-01-12 10:01:57','2025-01-12 10:01:57'),(19093,0,0,0,'AUTO',1,'2025-01-22 07:53:17','2025-01-22 07:53:17'),(19094,0,0,0,'WEB',0,'2025-01-22 08:05:15','2025-01-22 08:05:15'),(19095,0,0,0,'WEB',0,'2025-01-22 08:14:04','2025-01-22 08:14:04'),(19096,0,0,0,'WEB',0,'2025-01-22 08:34:53','2025-01-22 08:34:53'),(19097,0,0,0,'WEB',0,'2025-01-22 08:46:31','2025-01-22 08:46:31'),(19098,0,0,0,'AUTO',1,'2025-01-22 08:53:45','2025-01-22 08:53:45'),(19099,0,0,0,'AUTO',1,'2025-01-22 08:59:23','2025-01-22 08:59:23'),(19100,0,0,0,'AUTO',1,'2025-01-31 16:38:14','2025-01-31 16:38:14'),(19101,0,0,0,'WEB',0,'2025-01-31 16:52:56','2025-01-31 16:52:56'),(19102,0,0,0,'WEB',0,'2025-01-31 17:30:04','2025-01-31 17:30:04'),(19103,0,0,0,'WEB',0,'2025-02-02 09:58:18','2025-02-02 09:58:18'),(19104,0,0,0,'WEB',0,'2025-02-02 10:15:09','2025-02-02 10:15:09'),(19105,0,0,0,'WEB',0,'2025-02-02 10:30:46','2025-02-02 10:30:46'),(19106,0,0,0,'AUTO',1,'2025-02-09 16:20:26','2025-02-09 16:20:26'),(19107,0,0,0,'WEB',0,'2025-02-09 18:06:43','2025-02-09 18:06:43'),(19108,0,0,0,'WEB',0,'2025-02-09 18:12:10','2025-02-09 18:12:10'),(19109,0,0,0,'WEB',0,'2025-02-09 18:14:45','2025-02-09 18:14:45'),(19110,0,0,0,'AUTO',2,'2025-02-09 18:22:35','2025-02-09 18:22:35'),(19111,0,0,0,'WEB',0,'2025-02-09 18:17:52','2025-02-09 18:17:52'),(19112,0,0,0,'WEB',0,'2025-02-09 18:20:17','2025-02-09 18:20:17'),(19113,0,0,0,'WEB',0,'2025-02-09 18:27:46','2025-02-09 18:27:46'),(19114,0,0,0,'WEB',0,'2025-02-09 18:30:41','2025-02-09 18:30:41'),(19115,0,0,0,'WEB',0,'2025-02-09 18:33:38','2025-02-09 18:33:38'),(19116,0,0,0,'WEB',0,'2025-02-09 18:36:34','2025-02-09 18:36:34'),(19117,0,0,0,'WEB',0,'2025-02-11 08:55:15','2025-02-11 08:55:15'),(19118,0,0,0,'WEB',0,'2025-02-11 09:00:39','2025-02-11 09:00:39'),(19119,0,0,0,'AUTO',2,'2025-02-11 09:06:28','2025-02-11 09:06:28'),(19120,0,0,0,'AUTO',1,'2025-02-11 13:18:17','2025-02-11 13:18:17'),(19121,0,0,0,'WEB',0,'2025-02-11 14:12:25','2025-02-11 14:12:25');
/*!40000 ALTER TABLE `reset` ENABLE KEYS */;
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
  `run_time` int(11) NOT NULL,
  `sch_name` varchar(200) DEFAULT NULL,
  `type` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_daily_time`
--

LOCK TABLES `schedule_daily_time` WRITE;
/*!40000 ALTER TABLE `schedule_daily_time` DISABLE KEYS */;
INSERT INTO `schedule_daily_time` VALUES (50,0,0,1,'06:30:00',0,0,0,'09:30:00',0,0,0,60,10800,'WeekDays AM',0);
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
  `disabled` tinyint(4) NOT NULL,
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
INSERT INTO `schedule_daily_time_zone` VALUES (50,0,0,1,50,38,19.5,0,0,0),(51,0,0,1,50,39,40,0,0,0);
/*!40000 ALTER TABLE `schedule_daily_time_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `schedule_daily_time_zone_view`
--

DROP TABLE IF EXISTS `schedule_daily_time_zone_view`;
/*!50001 DROP VIEW IF EXISTS `schedule_daily_time_zone_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `schedule_daily_time_zone_view` AS SELECT
 1 AS `time_id`,
  1 AS `time_status`,
  1 AS `sch_type`,
  1 AS `start`,
  1 AS `start_sr`,
  1 AS `start_ss`,
  1 AS `start_offset`,
  1 AS `end`,
  1 AS `end_ss`,
  1 AS `end_sr`,
  1 AS `end_offset`,
  1 AS `WeekDays`,
  1 AS `tz_sync`,
  1 AS `tz_id`,
  1 AS `tz_status`,
  1 AS `zone_id`,
  1 AS `index_id`,
  1 AS `zone_name`,
  1 AS `type`,
  1 AS `category`,
  1 AS `temperature`,
  1 AS `holidays_id`,
  1 AS `coop`,
  1 AS `disabled`,
  1 AS `sch_name`,
  1 AS `max_c`,
  1 AS `sensor_type_id`,
  1 AS `stype` */;
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
/*!50001 CREATE VIEW `schedule_night_climat_zone_view` AS SELECT
 1 AS `time_id`,
  1 AS `time_status`,
  1 AS `start`,
  1 AS `end`,
  1 AS `WeekDays`,
  1 AS `tz_sync`,
  1 AS `tz_id`,
  1 AS `tz_status`,
  1 AS `zone_id`,
  1 AS `index_id`,
  1 AS `zone_name`,
  1 AS `type`,
  1 AS `category`,
  1 AS `zone_status`,
  1 AS `min_temperature`,
  1 AS `max_temperature`,
  1 AS `max_c`,
  1 AS `sensor_type_id`,
  1 AS `stype` */;
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
-- Table structure for table `schedule_time_temp_offset`
--

DROP TABLE IF EXISTS `schedule_time_temp_offset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_time_temp_offset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `schedule_daily_time_id` int(11) DEFAULT NULL,
  `low_temperature` float NOT NULL,
  `high_temperature` float NOT NULL,
  `start_time_offset` int(11) DEFAULT NULL,
  `sensors_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_schedule_time_temp_offset_schedule_daily_time` (`schedule_daily_time_id`),
  CONSTRAINT `FK_schedule_time_temp_offset_schedule_daily_time` FOREIGN KEY (`schedule_daily_time_id`) REFERENCES `schedule_daily_time` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_time_temp_offset`
--

LOCK TABLES `schedule_time_temp_offset` WRITE;
/*!40000 ALTER TABLE `schedule_time_temp_offset` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_time_temp_offset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_graphs`
--

DROP TABLE IF EXISTS `sensor_graphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11) DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `type` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `node_id` char(15) DEFAULT NULL,
  `child_id` tinyint(4) DEFAULT NULL,
  `sub_type` int(11) DEFAULT NULL,
  `payload` decimal(10,2) DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_graphs`
--

LOCK TABLES `sensor_graphs` WRITE;
/*!40000 ALTER TABLE `sensor_graphs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_graphs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_limits`
--

DROP TABLE IF EXISTS `sensor_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL,
  `sensor_id` int(11) DEFAULT NULL,
  `min` decimal(10,2) DEFAULT NULL,
  `max` decimal(10,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_sensors_limits_sensors` (`sensor_id`),
  CONSTRAINT `FK_sensor_limits_sensors` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_limits`
--

LOCK TABLES `sensor_limits` WRITE;
/*!40000 ALTER TABLE `sensor_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensor_messages`
--

DROP TABLE IF EXISTS `sensor_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sensor_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `message_id` decimal(10,2) DEFAULT NULL,
  `message` char(10) DEFAULT NULL,
  `status_color` char(10) DEFAULT NULL,
  `sub_type` tinyint(4) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_messages`
--

LOCK TABLES `sensor_messages` WRITE;
/*!40000 ALTER TABLE `sensor_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensor_messages` ENABLE KEYS */;
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
  `type` char(50) DEFAULT NULL,
  `units` char(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensor_type`
--

LOCK TABLES `sensor_type` WRITE;
/*!40000 ALTER TABLE `sensor_type` DISABLE KEYS */;
INSERT INTO `sensor_type` VALUES (1,0,0,'Temperature','&deg;'),(2,0,0,'Humidity','%'),(3,0,0,'Binary',''),(4,0,0,'Message','');
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
  `name` char(50) DEFAULT NULL,
  `graph_num` tinyint(4) NOT NULL,
  `show_it` tinyint(1) NOT NULL,
  `min_max_graph` tinyint(1) NOT NULL DEFAULT 0,
  `message_in` int(4) NOT NULL DEFAULT 1,
  `frost_temp` int(11) NOT NULL,
  `frost_controller` int(11) NOT NULL,
  `fail_timeout` int(11) NOT NULL,
  `mode` tinyint(4) NOT NULL,
  `timeout` int(11) NOT NULL,
  `resolution` decimal(1,1) NOT NULL,
  `current_val_1` decimal(10,2) NOT NULL,
  `current_val_2` decimal(10,2) NOT NULL,
  `last_seen` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_display` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_sensors_nodes` (`sensor_id`),
  KEY `FK_sensors_zone` (`zone_id`),
  CONSTRAINT `FK_sensors_nodes` FOREIGN KEY (`sensor_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensors`
--

LOCK TABLES `sensors` WRITE;
/*!40000 ALTER TABLE `sensors` DISABLE KEYS */;
INSERT INTO `sensors` VALUES (55,0,0,38,24,0,0.00,1,1,0,'Central Heating',0,1,0,1,3,49,0,0,0,0.2,0.00,0.00,'2025-07-10 16:45:45',0),(56,0,0,39,25,0,0.00,1,2,0,'Hot Water',0,1,0,1,0,0,0,0,0,0.2,0.00,0.00,'2025-07-10 16:45:52',0),(57,0,0,0,26,0,0.00,1,3,0,'Bedroom 1',0,1,0,1,0,0,0,0,0,0.2,0.00,0.00,'2025-07-10 16:45:58',0);
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
  `restart_schedule` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `name` varchar(50) DEFAULT NULL,
  `version` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `build` varchar(50) DEFAULT NULL,
  `country` char(2) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `language` char(10) DEFAULT NULL,
  `city` char(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `zip` char(100) DEFAULT NULL,
  `openweather_api` char(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `backup_email` char(100) DEFAULT NULL,
  `ping_home` bit(1) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `shutdown` tinyint(4) DEFAULT NULL,
  `reboot` tinyint(4) DEFAULT NULL,
  `c_f` tinyint(4) NOT NULL COMMENT '0=C, 1=F',
  `mode` tinyint(4) DEFAULT NULL,
  `max_cpu_temp` int(11) NOT NULL,
  `page_refresh` tinyint(4) NOT NULL,
  `theme` tinyint(4) DEFAULT NULL,
  `test_mode` tinyint(4) NOT NULL DEFAULT 0,
  `test_run_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES (2,1,0,'MaxAir - Smart Thermostat','2.07','270123','IE','en','Portlaoise',NULL,'aa22d10d34b1e6cb32bd6a5f2cb3fb46','','','Europe/Dublin',0,0,0,0,50,1,1,0,NULL);
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
  `max_operation_time` tinyint(4) unsigned NOT NULL DEFAULT 0,
  `overrun` smallint(6) DEFAULT NULL,
  `datetime` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `sc_mode` tinyint(4) DEFAULT NULL,
  `sc_mode_prev` tinyint(4) DEFAULT NULL,
  `heat_relay_id` int(11) DEFAULT NULL,
  `cool_relay_id` int(11) DEFAULT NULL,
  `fan_relay_id` int(11) DEFAULT NULL,
  `hvac_relays_state` tinyint(4) NOT NULL,
  `weather_factoring` tinyint(1) NOT NULL,
  `weather_sensor_id` int(11) NOT NULL,
  `pid` char(50) CHARACTER SET utf16 COLLATE utf16_bin DEFAULT NULL,
  `pid_running_since` char(50) CHARACTER SET utf16 COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_controller`
--

LOCK TABLES `system_controller` WRITE;
/*!40000 ALTER TABLE `system_controller` DISABLE KEYS */;
INSERT INTO `system_controller` VALUES (1,0,0,0,1,0,'Gas Boiler',0,3,60,2,'2025-07-10 16:45:18',0,0,50,0,0,0,1,0,'1809449','Thu Jul 10 17:45:18 2025');
/*!40000 ALTER TABLE `system_controller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `system_controller_view`
--

DROP TABLE IF EXISTS `system_controller_view`;
/*!50001 DROP VIEW IF EXISTS `system_controller_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `system_controller_view` AS SELECT
 1 AS `status`,
  1 AS `sync`,
  1 AS `purge`,
  1 AS `active_status`,
  1 AS `name`,
  1 AS `controller_type`,
  1 AS `relay_id`,
  1 AS `relay_child_id`,
  1 AS `hysteresis_time`,
  1 AS `max_operation_time`,
  1 AS `overrun`,
  1 AS `heat_relay_id`,
  1 AS `cool_relay_id`,
  1 AS `fan_relay_id` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `theme`
--

DROP TABLE IF EXISTS `theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` char(50) DEFAULT NULL,
  `row_justification` char(50) DEFAULT NULL,
  `color` char(50) DEFAULT NULL,
  `text_color` char(50) DEFAULT NULL,
  `tile_size` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `theme`
--

LOCK TABLES `theme` WRITE;
/*!40000 ALTER TABLE `theme` DISABLE KEYS */;
INSERT INTO `theme` VALUES (1,0,0,'Blue Left','left','blue','text-white',0),(2,0,0,'Blue Center','center','blue','text-white',0),(3,0,0,'Orange Left','left','orange','text-white',0),(4,0,0,'Orange Center','center','orange','text-white',0),(5,0,0,'Red Left','left','red','text-white',0),(6,0,0,'Red Center','center','red','text-white',0),(7,0,0,'Amber Left','left','amber','text-white',0),(8,0,0,'Amber Center','center','amber','text-white',0),(9,0,0,'Violet Left','left','violet','text-white',0),(10,0,0,'Violet Center','center','violet','text-white',0),(11,0,0,'Teal Left','left','teal','text-white',0),(12,0,0,'Teal Center','center','teal','text-white',0),(13,0,0,'Dark Left','left','black','text-white',0),(14,0,0,'Dark Center','center','black','text-white',0),(15,0,0,'Burnt Orange Left','left','orange-red','text-white',0),(16,0,0,'Burnt Orange Center','center','orange-red','text-white',0);
/*!40000 ALTER TABLE `theme` ENABLE KEYS */;
UNLOCK TABLES;

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
  `persist` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,'Administrator','admin','','0f5f9ba0136d5a8588b3fc70ec752869','2021-11-23 14:02:32','2021-11-23 13:43:09',1,0);
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
  `logged_out` timestamp NULL DEFAULT NULL,
  `audit` tinytext DEFAULT NULL,
  `ipaddress` tinytext DEFAULT NULL,
  `s_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
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
  `location` varchar(50) DEFAULT NULL,
  `c` tinyint(4) DEFAULT NULL,
  `wind_speed` varchar(50) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `sunrise` varchar(50) DEFAULT NULL,
  `sunset` varchar(50) DEFAULT NULL,
  `img` varchar(50) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp() COMMENT 'Last weather update',
  `enable_archive` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weather`
--

LOCK TABLES `weather` WRITE;
/*!40000 ALTER TABLE `weather` DISABLE KEYS */;
INSERT INTO `weather` VALUES (1,0,'Portlaoise',6,'2','Clear','clear sky','1679466473','1679510666','01d','2023-03-22 07:55:56',0);
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
  `name` char(50) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `max_operation_time` smallint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zone_type_id` (`type_id`),
  CONSTRAINT `FK_zone_type_id` FOREIGN KEY (`type_id`) REFERENCES `zone_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
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
  `mode_prev` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT NULL,
  `status_prev` tinyint(1) DEFAULT NULL,
  `schedule` tinyint(4) DEFAULT NULL,
  `sch_time_id` int(11) DEFAULT NULL,
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
  `hysteresis` tinyint(1) DEFAULT 0,
  `add_on_toggle` tinyint(1) DEFAULT 0,
  `log_it` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone_current_state`
--

LOCK TABLES `zone_current_state` WRITE;
/*!40000 ALTER TABLE `zone_current_state` DISABLE KEYS */;
INSERT INTO `zone_current_state` VALUES (38,0,0,38,0,0,0,0,0,50,0.0,0.0,19.1,0.0,0,'2025-07-10 16:45:19',0,NULL,'2025-07-10 16:45:45',0,0,0,0),(39,0,0,39,0,0,0,0,0,50,0.0,0.0,39.1,0.0,0,'2025-07-10 16:45:19',0,NULL,'2025-07-10 16:45:52',0,0,0,0);
/*!40000 ALTER TABLE `zone_current_state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `zone_log_view`
--

DROP TABLE IF EXISTS `zone_log_view`;
/*!50001 DROP VIEW IF EXISTS `zone_log_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `zone_log_view` AS SELECT
 1 AS `id`,
  1 AS `sync`,
  1 AS `zone_id`,
  1 AS `type`,
  1 AS `start_datetime`,
  1 AS `stop_datetime`,
  1 AS `expected_end_date_time` */;
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
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
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
  `default_m` tinyint(1) NOT NULL,
  `hysteresis_time` tinyint(4) DEFAULT NULL,
  `sp_deadband` float NOT NULL,
  `zone_sensor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zone_sensors_zone` (`zone_id`),
  KEY `FK_zone_sensors_sensors` (`zone_sensor_id`),
  CONSTRAINT `FK_zone_sensors_sensors` FOREIGN KEY (`zone_sensor_id`) REFERENCES `sensors` (`id`),
  CONSTRAINT `FK_zone_sensors_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone_sensors`
--

LOCK TABLES `zone_sensors` WRITE;
/*!40000 ALTER TABLE `zone_sensors` DISABLE KEYS */;
INSERT INTO `zone_sensors` VALUES (38,0,0,38,0,25,20,0,3,0.5,55),(39,0,0,39,0,45,40,0,3,0.5,56);
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
  `type` char(50) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zone_type`
--

LOCK TABLES `zone_type` WRITE;
/*!40000 ALTER TABLE `zone_type` DISABLE KEYS */;
INSERT INTO `zone_type` VALUES (2,0,0,'Heating',0),(3,0,0,'Water',0),(4,0,0,'Immersion',1),(5,0,0,'Switch',2),(6,0,0,'HVAC',3),(7,0,0,'Humidity',1),(9,0,0,'HVAC-M',4),(10,0,0,'Cooling',5);
/*!40000 ALTER TABLE `zone_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `zone_view`
--

DROP TABLE IF EXISTS `zone_view`;
/*!50001 DROP VIEW IF EXISTS `zone_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `zone_view` AS SELECT
 1 AS `status`,
  1 AS `zone_state`,
  1 AS `sync`,
  1 AS `id`,
  1 AS `index_id`,
  1 AS `name`,
  1 AS `type`,
  1 AS `category`,
  1 AS `graph_num`,
  1 AS `min_c`,
  1 AS `max_c`,
  1 AS `default_c`,
  1 AS `max_operation_time`,
  1 AS `hysteresis_time`,
  1 AS `sp_deadband`,
  1 AS `sensors_id`,
  1 AS `sensor_child_id`,
  1 AS `relay_type`,
  1 AS `relay_id`,
  1 AS `relay_child_id`,
  1 AS `last_seen`,
  1 AS `ms_version`,
  1 AS `sketch_version` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `add_on_log_view`
--

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
-- Final view structure for view `messages_in_view_1h`
--

/*!50001 DROP VIEW IF EXISTS `messages_in_view_1h`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `messages_in_view_1h` AS select `messages_in`.`id` AS `id`,`messages_in`.`node_id` AS `node_id`,`messages_in`.`child_id` AS `child_id`,`messages_in`.`datetime` AS `datetime`,`messages_in`.`payload` AS `payload` from `messages_in` where `messages_in`.`datetime` > current_timestamp() - interval 1 hour order by `messages_in`.`id` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `messages_in_view_24h`
--

/*!50001 DROP VIEW IF EXISTS `messages_in_view_24h`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `messages_in_view_24h` AS select `messages_in`.`id` AS `id`,`messages_in`.`node_id` AS `node_id`,`messages_in`.`child_id` AS `child_id`,`messages_in`.`datetime` AS `datetime`,`messages_in`.`payload` AS `payload` from `messages_in` where `messages_in`.`datetime` > current_timestamp() - interval 24 hour order by `messages_in`.`id` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `schedule_daily_time_zone_view`
--

/*!50001 DROP VIEW IF EXISTS `schedule_daily_time_zone_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `schedule_daily_time_zone_view` AS select `sdtz`.`schedule_daily_time_id` AS `time_id`,`sdt`.`status` AS `time_status`,`sdt`.`type` AS `sch_type`,`sdt`.`start` AS `start`,`sdt`.`start_sr` AS `start_sr`,`sdt`.`start_ss` AS `start_ss`,`sdt`.`start_offset` AS `start_offset`,`sdt`.`end` AS `end`,`sdt`.`end_ss` AS `end_ss`,`sdt`.`end_sr` AS `end_sr`,`sdt`.`end_offset` AS `end_offset`,`sdt`.`WeekDays` AS `WeekDays`,`sdtz`.`sync` AS `tz_sync`,`sdtz`.`id` AS `tz_id`,`sdtz`.`status` AS `tz_status`,`sdtz`.`zone_id` AS `zone_id`,`z`.`index_id` AS `index_id`,`z`.`name` AS `zone_name`,`zt`.`type` AS `type`,`zt`.`category` AS `category`,`sdtz`.`temperature` AS `temperature`,`sdtz`.`holidays_id` AS `holidays_id`,`sdtz`.`coop` AS `coop`,`sdtz`.`disabled` AS `disabled`,`sdt`.`sch_name` AS `sch_name`,`zs`.`max_c` AS `max_c`,ifnull(`s`.`sensor_type_id`,0) AS `sensor_type_id`,`st`.`type` AS `stype` from ((((((`schedule_daily_time_zone` `sdtz` join `zone` `z` on(`sdtz`.`zone_id` = `z`.`id`)) join `zone_type` `zt` on(`zt`.`id` = `z`.`type_id`)) left join `zone_sensors` `zs` on(`zs`.`zone_id` = `z`.`id`)) left join `sensors` `s` on(`s`.`id` = `zs`.`zone_sensor_id`)) left join `sensor_type` `st` on(`st`.`id` = `s`.`sensor_type_id`)) join `schedule_daily_time` `sdt` on(`sdt`.`id` = `sdtz`.`schedule_daily_time_id`)) where `sdtz`.`purge` = 0 order by `z`.`index_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `schedule_night_climat_zone_view`
--

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

/*!50001 DROP VIEW IF EXISTS `zone_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`maxairdbadmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `zone_view` AS select `zone`.`status` AS `status`,`zone`.`zone_state` AS `zone_state`,`zone`.`sync` AS `sync`,`zone`.`id` AS `id`,`zone`.`index_id` AS `index_id`,`zone`.`name` AS `name`,`ztype`.`type` AS `type`,`ztype`.`category` AS `category`,`ts`.`graph_num` AS `graph_num`,`zs`.`min_c` AS `min_c`,`zs`.`max_c` AS `max_c`,`zs`.`default_c` AS `default_c`,`zone`.`max_operation_time` AS `max_operation_time`,`zs`.`hysteresis_time` AS `hysteresis_time`,`zs`.`sp_deadband` AS `sp_deadband`,`sid`.`node_id` AS `sensors_id`,`ts`.`sensor_child_id` AS `sensor_child_id`,`ctype`.`type` AS `relay_type`,`r`.`relay_id` AS `relay_id`,`r`.`relay_child_id` AS `relay_child_id`,ifnull(`lasts`.`last_seen`,`lasts_2`.`last_seen`) AS `last_seen`,ifnull(`msv`.`ms_version`,`msv_2`.`ms_version`) AS `ms_version`,ifnull(`skv`.`sketch_version`,`skv_2`.`sketch_version`) AS `sketch_version` from (((((((((((((`zone` left join `zone_sensors` `zs` on(`zone`.`id` = `zs`.`zone_id`)) left join `sensors` `ts` on(`zs`.`zone_sensor_id` = `ts`.`id`)) left join `zone_relays` `zr` on(`zone`.`id` = `zr`.`zone_id`)) left join `relays` `r` on(`zr`.`zone_relay_id` = `r`.`id`)) join `zone_type` `ztype` on(`zone`.`type_id` = `ztype`.`id`)) left join `nodes` `sid` on(`ts`.`sensor_id` = `sid`.`id`)) left join `nodes` `ctype` on(`r`.`relay_id` = `ctype`.`id`)) left join `nodes` `lasts` on(`ts`.`sensor_id` = `lasts`.`id`)) left join `nodes` `lasts_2` on(`r`.`relay_id` = `lasts_2`.`id`)) left join `nodes` `msv` on(`ts`.`sensor_id` = `msv`.`id`)) left join `nodes` `msv_2` on(`r`.`relay_id` = `msv_2`.`id`)) left join `nodes` `skv` on(`ts`.`sensor_id` = `skv`.`id`)) left join `nodes` `skv_2` on(`r`.`relay_id` = `skv_2`.`id`)) where `zone`.`purge` = '0' */;
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

-- Dump completed on 2025-07-10 17:46:35
