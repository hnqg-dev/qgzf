-- MySQL dump 10.13  Distrib 8.0.35, for Linux (x86_64)
--
-- Host: localhost    Database: qgzf
-- ------------------------------------------------------
-- Server version	8.0.35

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aijz_qgaccount`
--

DROP TABLE IF EXISTS `aijz_qgaccount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aijz_qgaccount` (
  `aid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `aname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anumber` bigint DEFAULT NULL,
  `alogo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amoney` double DEFAULT NULL,
  `abcolor` char(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `etime` datetime DEFAULT NULL,
  `ctime` datetime DEFAULT NULL,
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aijz_qgaccount`
--

LOCK TABLES `aijz_qgaccount` WRITE;
/*!40000 ALTER TABLE `aijz_qgaccount` DISABLE KEYS */;
/*!40000 ALTER TABLE `aijz_qgaccount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aijz_qgbill`
--

DROP TABLE IF EXISTS `aijz_qgbill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aijz_qgbill` (
  `bid` bigint NOT NULL AUTO_INCREMENT,
  `lid` int DEFAULT NULL,
  `uid` int DEFAULT NULL,
  `aid` int DEFAULT NULL,
  `sid` int DEFAULT NULL,
  `szid` int DEFAULT NULL,
  `money` double DEFAULT NULL,
  `abstract` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `btime` datetime DEFAULT NULL,
  `ctime` datetime DEFAULT NULL,
  PRIMARY KEY (`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aijz_qgbill`
--

LOCK TABLES `aijz_qgbill` WRITE;
/*!40000 ALTER TABLE `aijz_qgbill` DISABLE KEYS */;
/*!40000 ALTER TABLE `aijz_qgbill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aijz_qgbill_sort`
--

DROP TABLE IF EXISTS `aijz_qgbill_sort`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aijz_qgbill_sort` (
  `sid` int NOT NULL AUTO_INCREMENT,
  `parentid` int DEFAULT NULL,
  `lid` int DEFAULT NULL,
  `uid` int DEFAULT NULL,
  `szid` tinyint DEFAULT NULL,
  `sname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sicon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sorder` int DEFAULT '999',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aijz_qgbill_sort`
--

LOCK TABLES `aijz_qgbill_sort` WRITE;
/*!40000 ALTER TABLE `aijz_qgbill_sort` DISABLE KEYS */;
/*!40000 ALTER TABLE `aijz_qgbill_sort` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aijz_qgledger`
--

DROP TABLE IF EXISTS `aijz_qgledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aijz_qgledger` (
  `lid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `lname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lsubtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lshare` tinyint(1) DEFAULT NULL,
  `etime` datetime DEFAULT NULL,
  `ctime` datetime DEFAULT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aijz_qgledger`
--

LOCK TABLES `aijz_qgledger` WRITE;
/*!40000 ALTER TABLE `aijz_qgledger` DISABLE KEYS */;
/*!40000 ALTER TABLE `aijz_qgledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aijz_qgsystem`
--

DROP TABLE IF EXISTS `aijz_qgsystem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aijz_qgsystem` (
  `id` tinyint(1) NOT NULL,
  `webtitle` char(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '',
  `mbname` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `weburl` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `urlssl` tinyint(1) DEFAULT '0',
  `regstate` tinyint(1) DEFAULT '0',
  `copyright` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `year` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `version` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aijz_qgsystem`
--

LOCK TABLES `aijz_qgsystem` WRITE;
/*!40000 ALTER TABLE `aijz_qgsystem` DISABLE KEYS */;
INSERT INTO `aijz_qgsystem` VALUES (1,'全哥账房','default/','192.168.1.1:1688',0,0,'无聊全哥','2025-2026','1.0.0');
/*!40000 ALTER TABLE `aijz_qgsystem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aijz_qgtransfer`
--

DROP TABLE IF EXISTS `aijz_qgtransfer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aijz_qgtransfer` (
  `tid` bigint NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `caid` int DEFAULT NULL,
  `raid` int DEFAULT NULL,
  `tmoney` double(15,2) DEFAULT NULL,
  `abstract` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ttime` datetime DEFAULT NULL,
  `ctime` datetime DEFAULT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aijz_qgtransfer`
--

LOCK TABLES `aijz_qgtransfer` WRITE;
/*!40000 ALTER TABLE `aijz_qgtransfer` DISABLE KEYS */;
/*!40000 ALTER TABLE `aijz_qgtransfer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aijz_qgusers`
--

DROP TABLE IF EXISTS `aijz_qgusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aijz_qgusers` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `uname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uiphone` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nname` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `upassword` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uface` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'boy04.jpeg',
  `ufacesort` tinyint(1) DEFAULT NULL,
  `ifadm` tinyint(1) DEFAULT '0',
  `ustatus` tinyint(1) DEFAULT '0',
  `ctime` datetime DEFAULT NULL,
  `etime` datetime DEFAULT NULL,
  `ltime` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aijz_qgusers`
--

LOCK TABLES `aijz_qgusers` WRITE;
/*!40000 ALTER TABLE `aijz_qgusers` DISABLE KEYS */;
/*!40000 ALTER TABLE `aijz_qgusers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'qgzf'
--

--
-- Dumping routines for database 'qgzf'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-03  3:46:02
