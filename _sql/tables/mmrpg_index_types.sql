/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_index_types` (
  `type_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Type ID',
  `type_token` varchar(100) NOT NULL COMMENT 'Type Token',
  `type_name` varchar(100) NOT NULL COMMENT 'Type Name',
  `type_class` varchar(32) NOT NULL COMMENT 'Type Class',
  `type_colour_dark` varchar(32) NOT NULL COMMENT 'Type Colour Dark',
  `type_colour_light` varchar(32) NOT NULL COMMENT 'Type Colour Light',
  `type_flag_hidden` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Type Flag Hidden',
  `type_flag_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Type Flag Published',
  `type_order` smallint(8) NOT NULL DEFAULT '0' COMMENT 'Type Order',
  PRIMARY KEY (`type_id`),
  KEY `type_token` (`type_token`),
  KEY `type_class` (`type_class`),
  KEY `type_flag_published` (`type_flag_published`),
  KEY `type_flag_hidden` (`type_flag_hidden`),
  KEY `type_flag_hidden_published` (`type_flag_hidden`,`type_flag_published`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
