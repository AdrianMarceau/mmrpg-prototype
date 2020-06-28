/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_config` (
  `config_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Config ID',
  `config_group` varchar(64) NOT NULL DEFAULT '' COMMENT 'Config Group',
  `config_name` varchar(128) NOT NULL DEFAULT '' COMMENT 'Config Name',
  `config_value` varchar(256) NOT NULL DEFAULT '' COMMENT 'Config Value',
  PRIMARY KEY (`config_id`),
  KEY `config_group` (`config_group`),
  KEY `config_name` (`config_name`),
  KEY `config_value` (`config_value`(255)),
  KEY `config_group_config_name` (`config_group`,`config_name`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
