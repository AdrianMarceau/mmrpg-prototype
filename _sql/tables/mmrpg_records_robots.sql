/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_records_robots` (
  `record_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
  `record_time` int(11) unsigned DEFAULT '0' COMMENT 'Record Time',
  `robot_token` varchar(128) NOT NULL DEFAULT '' COMMENT 'Robot Token',
  `robot_encountered` int(11) unsigned DEFAULT '0' COMMENT 'Robot Encountered',
  `robot_defeated` int(11) unsigned DEFAULT '0' COMMENT 'Robot Defeated',
  `robot_unlocked` int(11) unsigned DEFAULT '0' COMMENT 'Robot Unlocked',
  `robot_summoned` int(11) unsigned DEFAULT '0' COMMENT 'Robot Summoned',
  `robot_scanned` int(11) unsigned DEFAULT '0' COMMENT 'Robot Scanned',
  `robot_avatars` int(11) unsigned DEFAULT '0' COMMENT 'Robot Avatars',
  PRIMARY KEY (`record_id`),
  KEY `robot_token` (`robot_token`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
