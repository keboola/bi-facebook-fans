
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
DROP TABLE IF EXISTS `fbi_age`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_age` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_days` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idPage` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `dau` int(10) unsigned NOT NULL,
  `mau` int(10) unsigned NOT NULL,
  `views` int(10) unsigned NOT NULL,
  `viewsUnique` int(10) unsigned NOT NULL,
  `viewsLogin` int(10) unsigned NOT NULL,
  `viewsLogout` int(10) unsigned NOT NULL,
  `viewsMale` int(10) unsigned NOT NULL,
  `viewsFemale` int(10) unsigned NOT NULL,
  `viewsUnknownSex` int(10) unsigned NOT NULL,
  `likesTotal` int(10) unsigned NOT NULL,
  `likesAdded` int(10) unsigned NOT NULL,
  `likesRemoved` int(10) unsigned NOT NULL,
  `contentLikesAdded` int(10) unsigned NOT NULL,
  `contentLikesRemoved` int(10) unsigned NOT NULL,
  `comments` int(10) unsigned NOT NULL,
  `feedViews` int(10) unsigned NOT NULL,
  `feedViewsUnique` int(10) unsigned NOT NULL,
  `wallPosts` int(10) unsigned NOT NULL,
  `wallPostsUnique` int(10) unsigned NOT NULL,
  `photos` int(10) unsigned NOT NULL,
  `photoViews` int(10) unsigned NOT NULL,
  `photoViewsUnique` int(10) unsigned NOT NULL,
  `videos` int(10) unsigned NOT NULL,
  `videoPlays` int(10) unsigned NOT NULL,
  `videoPlaysUnique` int(10) unsigned NOT NULL,
  `audioPlays` int(10) unsigned NOT NULL,
  `audioPlaysUnique` int(10) unsigned NOT NULL,
  `discussions` int(10) unsigned NOT NULL,
  `discussionsUnique` int(10) unsigned NOT NULL,
  `reviewsAdded` int(10) unsigned NOT NULL,
  `reviewsAddedUnique` int(10) unsigned NOT NULL,
  `reviewsModified` int(10) unsigned NOT NULL,
  `reviewsModifiedUnique` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idPage` (`idPage`),
  KEY `date` (`date`),
  CONSTRAINT `fbi_days_ibfk_1` FOREIGN KEY (`idPage`) REFERENCES `fbi_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_daysCountries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_daysCountries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idDay` int(10) unsigned NOT NULL,
  `country` char(2) NOT NULL,
  `views` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idDay` (`idDay`),
  CONSTRAINT `fbi_daysCountries_ibfk_1` FOREIGN KEY (`idDay`) REFERENCES `fbi_days` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_likes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idPage` int(10) unsigned NOT NULL,
  `month` date NOT NULL,
  `date` date NOT NULL,
  `male` int(10) unsigned NOT NULL,
  `female` int(10) unsigned NOT NULL,
  `unknownSex` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idPage` (`idPage`),
  CONSTRAINT `fbi_likes_ibfk_1` FOREIGN KEY (`idPage`) REFERENCES `fbi_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_likesCountries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_likesCountries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idLike` int(10) unsigned NOT NULL,
  `country` char(2) COLLATE utf8_czech_ci NOT NULL,
  `likes` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idPage` (`idLike`),
  CONSTRAINT `fbi_likesCountries_ibfk_1` FOREIGN KEY (`idLike`) REFERENCES `fbi_likes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(10) unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `idPage` varchar(20) NOT NULL,
  `idProject` varchar(40) NOT NULL,
  `token` varchar(90) DEFAULT NULL,
  `isActive` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isInGD` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_rDaysAge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_rDaysAge` (
  `idDay` int(10) unsigned NOT NULL,
  `idAge` int(10) unsigned NOT NULL,
  `views` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idDay`,`idAge`),
  KEY `idUserAge` (`idAge`),
  KEY `idDay` (`idDay`),
  CONSTRAINT `fbi_rDaysAge_ibfk_2` FOREIGN KEY (`idAge`) REFERENCES `fbi_age` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fbi_rDaysAge_ibfk_1` FOREIGN KEY (`idDay`) REFERENCES `fbi_days` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_rDaysCities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_rDaysCities` (
  `idDay` int(10) unsigned NOT NULL,
  `idCity` int(10) unsigned NOT NULL,
  `views` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idDay`,`idCity`),
  KEY `idCity` (`idCity`),
  KEY `idDay` (`idDay`),
  CONSTRAINT `fbi_rDaysCities_ibfk_1` FOREIGN KEY (`idDay`) REFERENCES `fbi_days` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fbi_rDaysCities_ibfk_2` FOREIGN KEY (`idCity`) REFERENCES `fbi_cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_rDaysReferrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_rDaysReferrals` (
  `idDay` int(10) unsigned NOT NULL,
  `idReferral` int(10) unsigned NOT NULL,
  `views` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idDay`,`idReferral`),
  KEY `idDay` (`idDay`),
  KEY `idReferral` (`idReferral`),
  CONSTRAINT `fbi_rDaysReferrals_ibfk_3` FOREIGN KEY (`idReferral`) REFERENCES `fbi_referrals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fbi_rDaysReferrals_ibfk_4` FOREIGN KEY (`idDay`) REFERENCES `fbi_days` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_rLikesAge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_rLikesAge` (
  `idLike` int(10) unsigned NOT NULL,
  `idAge` int(10) unsigned NOT NULL,
  `likes` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idLike`,`idAge`),
  KEY `idAge` (`idAge`),
  CONSTRAINT `fbi_rLikesAge_ibfk_3` FOREIGN KEY (`idLike`) REFERENCES `fbi_likes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fbi_rLikesAge_ibfk_2` FOREIGN KEY (`idAge`) REFERENCES `fbi_age` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_rLikesCities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_rLikesCities` (
  `idLike` int(10) unsigned NOT NULL,
  `idCity` int(10) unsigned NOT NULL,
  `likes` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idLike`,`idCity`),
  KEY `idCity` (`idCity`),
  CONSTRAINT `fbi_rLikesCities_ibfk_3` FOREIGN KEY (`idLike`) REFERENCES `fbi_likes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fbi_rLikesCities_ibfk_2` FOREIGN KEY (`idCity`) REFERENCES `fbi_cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_rPagesCities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_rPagesCities` (
  `idPage` int(10) unsigned NOT NULL,
  `idCity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idPage`,`idCity`),
  KEY `idCity` (`idCity`),
  CONSTRAINT `fbi_rPagesCities_ibfk_2` FOREIGN KEY (`idCity`) REFERENCES `fbi_cities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fbi_rPagesCities_ibfk_1` FOREIGN KEY (`idPage`) REFERENCES `fbi_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_rPagesReferrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_rPagesReferrals` (
  `idPage` int(10) unsigned NOT NULL,
  `idReferral` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idPage`,`idReferral`),
  KEY `idReferral` (`idReferral`),
  CONSTRAINT `fbi_rPagesReferrals_ibfk_1` FOREIGN KEY (`idPage`) REFERENCES `fbi_pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fbi_rPagesReferrals_ibfk_2` FOREIGN KEY (`idReferral`) REFERENCES `fbi_referrals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_referrals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('internal','external') NOT NULL,
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

