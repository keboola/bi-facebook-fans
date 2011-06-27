
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `idPage` (`idPage`,`date`),
  KEY `date` (`date`),
  CONSTRAINT `fbi_days_ibfk_1` FOREIGN KEY (`idPage`) REFERENCES `fbi_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `idFacebook` varchar(20) NOT NULL,
  `token` varchar(90) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fbi_rDaysReferrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_rDaysReferrals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idDay` int(10) unsigned NOT NULL,
  `idReferral` int(10) unsigned NOT NULL,
  `views` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idDay` (`idDay`),
  KEY `idReferral` (`idReferral`),
  CONSTRAINT `fbi_rDaysReferrals_ibfk_3` FOREIGN KEY (`idReferral`) REFERENCES `fbi_referrals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fbi_rDaysReferrals_ibfk_4` FOREIGN KEY (`idDay`) REFERENCES `fbi_days` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
DROP TABLE IF EXISTS `fbi_userCountries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi_userCountries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idDay` int(10) unsigned NOT NULL,
  `country` char(2) NOT NULL,
  `views` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idDay` (`idDay`),
  CONSTRAINT `fbi_userCountries_ibfk_1` FOREIGN KEY (`idDay`) REFERENCES `fbi_days` (`id`) ON DELETE CASCADE
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

