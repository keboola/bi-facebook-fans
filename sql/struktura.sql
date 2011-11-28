
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `bi_connectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_connectors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `templateUri` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bi_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_invitations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idUserConnector` int(10) unsigned NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `role` enum('editor','dashboard only') NOT NULL,
  `text` text,
  `isSent` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idUserConnector` (`idUserConnector`),
  CONSTRAINT `bi_invitations_ibfk_1` FOREIGN KEY (`idUserConnector`) REFERENCES `bi_rUsersConnectors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bi_ordersHistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_ordersHistory` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(10) unsigned NOT NULL,
  `idPlan` int(10) unsigned NOT NULL,
  `price` decimal(6,2) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idUser` (`idUser`),
  KEY `idPlan` (`idPlan`),
  CONSTRAINT `bi_ordersHistory_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `bi_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bi_ordersHistory_ibfk_2` FOREIGN KEY (`idPlan`) REFERENCES `bi_pricingPlans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bi_paymentsHistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_paymentsHistory` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(10) unsigned NOT NULL,
  `idPlan` int(10) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idUser` (`idUser`),
  KEY `idPlan` (`idPlan`),
  CONSTRAINT `bi_paymentsHistory_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `bi_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bi_paymentsHistory_ibfk_2` FOREIGN KEY (`idPlan`) REFERENCES `bi_pricingPlans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bi_pricingPlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_pricingPlans` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `accountsCount` int(10) unsigned NOT NULL,
  `usersCount` int(10) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bi_rUsersConnectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_rUsersConnectors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(10) unsigned NOT NULL,
  `idConnector` int(10) unsigned NOT NULL,
  `idPlan` int(10) unsigned DEFAULT NULL,
  `idSubscription` varchar(19) DEFAULT NULL,
  `paidUntil` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idUser` (`idUser`),
  KEY `idConnector` (`idConnector`),
  KEY `idPlan` (`idPlan`),
  CONSTRAINT `bi_rUsersConnectors_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `bi_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bi_rUsersConnectors_ibfk_2` FOREIGN KEY (`idConnector`) REFERENCES `bi_connectors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bi_rUsersConnectors_ibfk_3` FOREIGN KEY (`idPlan`) REFERENCES `bi_pricingPlans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bi_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT '',
  `salt` varchar(50) DEFAULT '',
  `isActivated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `changePasswordUntil` datetime DEFAULT NULL,
  `idGD` varchar(40) DEFAULT NULL,
  `export` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

