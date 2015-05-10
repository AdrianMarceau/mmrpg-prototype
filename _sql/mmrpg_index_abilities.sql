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

-- Dumping structure for table pluto1_mmrpg2k11.mmrpg_index_abilities
CREATE TABLE IF NOT EXISTS `mmrpg_index_abilities` (
  `ability_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Ability ID',
  `ability_token` varchar(100) NOT NULL COMMENT 'Ability Token',
  `ability_name` varchar(100) NOT NULL COMMENT 'Ability Name',
  `ability_game` varchar(10) NOT NULL COMMENT 'Ability Game',
  `ability_group` varchar(100) NOT NULL COMMENT 'Ability Group',
  `ability_class` varchar(32) NOT NULL COMMENT 'Ability Class',
  `ability_master` varchar(100) NOT NULL COMMENT 'Ability Master',
  `ability_number` varchar(10) NOT NULL COMMENT 'Ability Number',
  `ability_image` varchar(64) NOT NULL COMMENT 'Ability Image',
  `ability_image_sheets` smallint(3) NOT NULL DEFAULT '1' COMMENT 'Ability Image Sheets',
  `ability_image_size` smallint(3) NOT NULL DEFAULT '40' COMMENT 'Ability Image Size',
  `ability_image_editor` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'Ability Image Editor',
  `ability_type` varchar(32) NOT NULL COMMENT 'Ability Type',
  `ability_type2` varchar(32) NOT NULL COMMENT 'Ability Type 2',
  `ability_description` text NOT NULL COMMENT 'Ability Description',
  `ability_description2` text NOT NULL COMMENT 'Ability Description 2',
  `ability_speed` smallint(6) NOT NULL DEFAULT '1' COMMENT 'Ability Speed',
  `ability_energy` smallint(6) NOT NULL DEFAULT '1' COMMENT 'Ability Energy',
  `ability_energy_percent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ability Energy Percent',
  `ability_damage` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Ability Damage',
  `ability_damage_percent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ability Damage Percent',
  `ability_damage2` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Ability Damage 2',
  `ability_damage2_percent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ability Damage 2 Percent',
  `ability_recovery` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Ability Recovery',
  `ability_recovery_percent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ability Recovery Percent',
  `ability_recovery2` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Ability Recovery 2',
  `ability_recovery2_percent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ability Recovery 2 Percent',
  `ability_accuracy` smallint(3) NOT NULL DEFAULT '0' COMMENT 'Ability Accuracy',
  `ability_target` varchar(64) NOT NULL COMMENT 'Ability Target',
  `ability_frame` varchar(32) NOT NULL COMMENT 'Ability Frame',
  `ability_frame_animate` varchar(256) NOT NULL COMMENT 'Ability Frame Animate',
  `ability_frame_index` varchar(256) NOT NULL COMMENT 'Ability Frame Index',
  `ability_frame_offset` varchar(100) NOT NULL COMMENT 'Ability Frame Offset',
  `ability_frame_styles` varchar(100) NOT NULL COMMENT 'Ability Frame Styles',
  `ability_frame_classes` varchar(100) NOT NULL COMMENT 'Ability Frame Classes',
  `attachment_frame` varchar(32) NOT NULL COMMENT 'Attachment Frame',
  `attachment_frame_animate` varchar(256) NOT NULL COMMENT 'Attachment Frame Animate',
  `attachment_frame_index` varchar(256) NOT NULL COMMENT 'Attachment Frame Index',
  `attachment_frame_offset` varchar(100) NOT NULL COMMENT 'Attachment Frame Offset',
  `attachment_frame_styles` varchar(100) NOT NULL COMMENT 'Attachment Frame Styles',
  `attachment_frame_classes` varchar(100) NOT NULL COMMENT 'Attachment Frame Classes',
  `ability_functions` varchar(128) NOT NULL COMMENT 'Ability Functions',
  `ability_flag_hidden` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Ability Flag Hidden',
  `ability_flag_complete` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Ability Flag Complete',
  `ability_flag_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Ability Flag Published',
  `ability_order` smallint(8) NOT NULL DEFAULT '0' COMMENT 'Ability Order',
  PRIMARY KEY (`ability_id`),
  KEY `ability_token` (`ability_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
