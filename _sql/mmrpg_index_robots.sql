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

-- Dumping structure for table pluto1_mmrpg2k11.mmrpg_index_robots
CREATE TABLE IF NOT EXISTS `mmrpg_index_robots` (
  `robot_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Robot ID',
  `robot_token` varchar(100) NOT NULL COMMENT 'Robot Token',
  `robot_number` varchar(10) NOT NULL COMMENT 'Robot Number',
  `robot_name` varchar(100) NOT NULL COMMENT 'Robot Name',
  `robot_game` varchar(10) NOT NULL COMMENT 'Robot Game',
  `robot_group` varchar(100) NOT NULL COMMENT 'Robot Group',
  `robot_field` varchar(100) NOT NULL COMMENT 'Robot Field',
  `robot_field2` varchar(100) NOT NULL COMMENT 'Robot Field 2',
  `robot_class` varchar(32) NOT NULL COMMENT 'Robot Class',
  `robot_gender` varchar(10) CHARACTER SET utf16 NOT NULL DEFAULT 'none' COMMENT 'Robot Gender',
  `robot_image` varchar(64) NOT NULL COMMENT 'Robot Image',
  `robot_image_size` smallint(3) NOT NULL DEFAULT '40' COMMENT 'Robot Image Size',
  `robot_image_editor` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'Robot Image Editor',
  `robot_image_alts` text NOT NULL COMMENT 'Robot Image Alts',
  `robot_core` varchar(32) NOT NULL COMMENT 'Robot Core',
  `robot_core2` varchar(32) NOT NULL COMMENT 'Robot Core 2',
  `robot_description` varchar(128) NOT NULL COMMENT 'Robot Description',
  `robot_description2` text NOT NULL COMMENT 'Robot Description 2',
  `robot_energy` smallint(6) NOT NULL DEFAULT '100' COMMENT 'Robot Energy',
  `robot_weapons` smallint(6) NOT NULL DEFAULT '10' COMMENT 'Robot Weapons',
  `robot_attack` smallint(6) NOT NULL DEFAULT '100' COMMENT 'Robot Attack',
  `robot_defense` smallint(6) NOT NULL DEFAULT '100' COMMENT 'Robot Defense',
  `robot_speed` smallint(6) NOT NULL DEFAULT '100' COMMENT 'Robot Speed',
  `robot_weaknesses` varchar(256) NOT NULL COMMENT 'Robot Weaknesses',
  `robot_resistances` varchar(256) NOT NULL COMMENT 'Robot Resistances',
  `robot_affinities` varchar(256) NOT NULL COMMENT 'Robot Affinities',
  `robot_immunities` varchar(256) NOT NULL COMMENT 'Robot Immunities',
  `robot_abilities_rewards` text NOT NULL COMMENT 'Robot Abilities Rewards',
  `robot_abilities_compatible` text NOT NULL COMMENT 'Robot Abilities Compatible',
  `robot_quotes_start` varchar(256) NOT NULL COMMENT 'Robot Quotes Start',
  `robot_quotes_taunt` varchar(256) NOT NULL COMMENT 'Robot Quotes Taunt',
  `robot_quotes_victory` varchar(256) NOT NULL COMMENT 'Robot Quotes Victory',
  `robot_quotes_defeat` varchar(256) NOT NULL COMMENT 'Robot Quotes Defeat',
  `robot_functions` varchar(128) NOT NULL COMMENT 'Robot Functions',
  `robot_flag_hidden` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Robot Flag Hidden',
  `robot_flag_complete` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Robot Flag Complete',
  `robot_flag_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Robot Flag Published',
  `robot_order` smallint(8) NOT NULL DEFAULT '0' COMMENT 'Robot Order',
  PRIMARY KEY (`robot_id`),
  KEY `robot_token` (`robot_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
