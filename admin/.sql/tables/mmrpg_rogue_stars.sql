/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_rogue_stars` (
  `star_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Star ID',
  `star_type` varchar(32) NOT NULL DEFAULT '' COMMENT 'Star Type',
  `star_from_date` varchar(10) NOT NULL DEFAULT '0000-00-00' COMMENT 'Star From-Date',
  `star_from_date_time` varchar(8) NOT NULL DEFAULT '00:00:00' COMMENT 'Star From-Date-Time',
  `star_to_date` varchar(10) NOT NULL DEFAULT '0000-00-00' COMMENT 'Star To-Date',
  `star_to_date_time` varchar(8) NOT NULL DEFAULT '00:00:00' COMMENT 'Star To-Date-Time',
  `star_power` smallint(4) unsigned NOT NULL DEFAULT '400' COMMENT 'Star Power',
  `star_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Star Active',
  `star_flag_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Star Flag Enabled',
  PRIMARY KEY (`star_id`),
  KEY `star_type` (`star_type`),
  KEY `star_from_date` (`star_from_date`),
  KEY `star_to_date` (`star_to_date`),
  KEY `star_active` (`star_active`),
  KEY `star_from_date_time` (`star_from_date_time`),
  KEY `star_to_date_time` (`star_to_date_time`),
  KEY `star_flag_enabled` (`star_flag_enabled`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
