/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_index_players_backups` (
  `backup_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Backup ID',
  `player_token` varchar(100) NOT NULL COMMENT 'Player Token',
  `player_number` varchar(10) NOT NULL COMMENT 'Player Number',
  `player_name` varchar(100) NOT NULL COMMENT 'Player Name',
  `player_game` varchar(10) NOT NULL COMMENT 'Player Game',
  `player_group` varchar(32) NOT NULL COMMENT 'Player Group',
  `player_class` varchar(32) NOT NULL COMMENT 'Player Class',
  `player_image` varchar(64) NOT NULL COMMENT 'Player Image',
  `player_image_size` smallint(3) NOT NULL DEFAULT '40' COMMENT 'Player Image Size',
  `player_image_editor` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'Player Image Editor',
  `player_image_editor2` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'Player Image Editor 2',
  `player_image_alts` text NOT NULL COMMENT 'Player Image Alts',
  `player_type` varchar(32) NOT NULL COMMENT 'Player Type',
  `player_type2` varchar(32) NOT NULL COMMENT 'Player Type2',
  `player_description` varchar(128) NOT NULL COMMENT 'Player Description',
  `player_description2` text NOT NULL COMMENT 'Player Description 2',
  `player_energy` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Energy',
  `player_weapons` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Weapons',
  `player_attack` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Attack',
  `player_defense` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Defense',
  `player_speed` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Speed',
  `player_abilities_rewards` text NOT NULL COMMENT 'Player Abilities Rewards',
  `player_abilities_compatible` text NOT NULL COMMENT 'Player Abilities Compatible',
  `player_robots_rewards` text NOT NULL COMMENT 'Player Robots Rewards',
  `player_robots_compatible` text NOT NULL COMMENT 'Player Robots Compatible',
  `player_quotes_start` varchar(256) NOT NULL COMMENT 'Player Quotes Start',
  `player_quotes_taunt` varchar(256) NOT NULL COMMENT 'Player Quotes Taunt',
  `player_quotes_victory` varchar(256) NOT NULL COMMENT 'Player Quotes Victory',
  `player_quotes_defeat` varchar(256) NOT NULL COMMENT 'Player Quotes Defeat',
  `player_functions` varchar(128) NOT NULL COMMENT 'Player Functions',
  `player_flag_hidden` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Player Flag Hidden',
  `player_flag_complete` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Player Flag Complete',
  `player_flag_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Player Flag Published',
  `player_order` smallint(8) NOT NULL DEFAULT '0' COMMENT 'Player Order',
  `backup_date_time` varchar(13) NOT NULL DEFAULT '00000000-0000' COMMENT 'Backup Date Time',
  PRIMARY KEY (`backup_id`),
  KEY `player_token` (`player_token`),
  KEY `backup_date_time` (`backup_date_time`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
