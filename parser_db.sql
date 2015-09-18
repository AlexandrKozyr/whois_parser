/*
SQLyog Ultimate v11.11 (32 bit)
MySQL - 5.6.25-log : Database - test
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `host` */

CREATE TABLE `host` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) NOT NULL,
  `whois_done` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=InnoDB AUTO_INCREMENT=22152 DEFAULT CHARSET=utf8;

/*Table structure for table `whois_info` */

CREATE TABLE `whois_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) unsigned NOT NULL,
  `status` varchar(255) NOT NULL,
  `mnt-by` varchar(255) DEFAULT NULL,
  `updated` varchar(255) DEFAULT NULL,
  `expires` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`,`host_id`),
  KEY `host_id` (`host_id`),
  CONSTRAINT `whois_info_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `host` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2218 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
