/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_rankings` (
  `board_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Board ID',
  `user_id` mediumint(8) NOT NULL COMMENT 'User ID',
  `save_id` mediumint(8) NOT NULL COMMENT 'Save ID',
  `board_points` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Battle Points',
  `board_items` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Items Collected',
  `board_robots` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Robots Unlocked',
  `board_abilities` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Abilities Unlocked',
  `board_stars` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Stars Collected',
  `board_awards` text NOT NULL COMMENT 'Board Awards',
  `board_date_created` int(8) NOT NULL DEFAULT '0' COMMENT 'Board Date Created',
  `board_date_modified` int(8) NOT NULL DEFAULT '0' COMMENT 'Board Date Created',
  `board_points_legacy` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Legacy Battle Points (2k16)',
  `board_points_legacy2` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Legacy Battle Points (2k19)',
  PRIMARY KEY (`board_id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `save_id` (`save_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
