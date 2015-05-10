-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.17 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table pluto1_mmrpg2k11.mmrpg_battles
CREATE TABLE IF NOT EXISTS `mmrpg_battles` (
  `battle_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Battle ID',
  `battle_field_name` varchar(255) NOT NULL COMMENT 'Battle Field Name',
  `battle_field_background` varchar(255) NOT NULL COMMENT 'Battle Field Background',
  `battle_field_foreground` varchar(255) NOT NULL COMMENT 'Battle Field Foreground',
  `battle_turns` mediumint(8) NOT NULL COMMENT 'Battle Turns',
  `this_user_id` mediumint(8) NOT NULL COMMENT 'This User ID',
  `this_player_token` varchar(100) NOT NULL COMMENT 'This Player Token',
  `this_player_robots` text NOT NULL COMMENT 'This Player Robots',
  `this_player_points` int(16) NOT NULL COMMENT 'This Player Points',
  `this_player_result` varchar(100) NOT NULL COMMENT 'This Player Result',
  `this_reward_pending` smallint(1) NOT NULL DEFAULT '1' COMMENT 'This Reward Pending',
  `target_user_id` mediumint(8) NOT NULL COMMENT 'Target User ID',
  `target_player_token` varchar(100) NOT NULL COMMENT 'Target Player Token',
  `target_player_robots` text NOT NULL COMMENT 'Target Player Robots',
  `target_player_points` int(16) NOT NULL COMMENT 'Target Player Points',
  `target_player_result` varchar(100) NOT NULL COMMENT 'Target Player Result',
  `target_reward_pending` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Target Reward Pending',
  PRIMARY KEY (`battle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
