/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_saves` (
  `save_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Save ID',
  `user_id` mediumint(8) NOT NULL COMMENT 'User ID',
  `save_counters` mediumtext NOT NULL COMMENT 'Save Counters',
  `save_values` mediumtext NOT NULL COMMENT 'Save Values',
  `save_values_battle_index` mediumtext NOT NULL COMMENT 'Battle Index',
  `save_values_battle_complete` mediumtext NOT NULL COMMENT 'Battle Complete',
  `save_values_battle_failure` mediumtext NOT NULL COMMENT 'Battle Failure',
  `save_values_battle_rewards` mediumtext NOT NULL COMMENT 'Battle Rewards',
  `save_values_battle_settings` mediumtext NOT NULL COMMENT 'Battle Settings',
  `save_values_battle_items` mediumtext NOT NULL COMMENT 'Battle Items',
  `save_values_battle_abilities` mediumtext NOT NULL COMMENT 'Battle Abilities',
  `save_values_battle_stars` mediumtext NOT NULL COMMENT 'Battle Stars',
  `save_values_robot_database` mediumtext NOT NULL COMMENT 'Robot Database',
  `save_values_robot_alts` mediumtext NOT NULL COMMENT 'Robot Alts',
  `save_values_battle_hearts` mediumtext NOT NULL COMMENT 'Battle Hearts',
  `save_flags` mediumtext NOT NULL COMMENT 'Save Flags',
  `save_settings` mediumtext NOT NULL COMMENT 'Save Settings',
  `save_cache_date` varchar(32) NOT NULL COMMENT 'Save Cache Date',
  `save_file_name` varchar(128) NOT NULL COMMENT 'Save File Name',
  `save_file_path` varchar(128) NOT NULL COMMENT 'Save File Path',
  `save_date_created` int(8) NOT NULL COMMENT 'Save Date Created',
  `save_date_accessed` int(8) NOT NULL COMMENT 'Save Date Accessed',
  `save_date_modified` int(8) NOT NULL COMMENT 'Save Date Modified',
  `save_patches_applied` text NOT NULL COMMENT 'Save Patches Applied',
  PRIMARY KEY (`save_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
