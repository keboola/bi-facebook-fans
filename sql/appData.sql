
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

REPLACE INTO `bi_connectors` (`id`, `name`, `templateUri`) VALUES (1,'Facebook','/projectTemplates/KBFacebook/1/');

REPLACE INTO `bi_pricePlans` (`id`, `accountsCount`, `usersCount`, `price`) VALUES (1,1,1,19);
REPLACE INTO `bi_pricePlans` (`id`, `accountsCount`, `usersCount`, `price`) VALUES (2,6,4,99);
REPLACE INTO `bi_pricePlans` (`id`, `accountsCount`, `usersCount`, `price`) VALUES (3,20,10,299);
REPLACE INTO `bi_pricePlans` (`id`, `accountsCount`, `usersCount`, `price`) VALUES (4,40,20,499);
REPLACE INTO `bi_pricePlans` (`id`, `accountsCount`, `usersCount`, `price`) VALUES (5,100,30,899);
REPLACE INTO `bi_pricePlans` (`id`, `accountsCount`, `usersCount`, `price`) VALUES (6,200,50,1499);
REPLACE INTO `bi_pricePlans` (`id`, `accountsCount`, `usersCount`, `price`) VALUES (7,400,75,2799);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
