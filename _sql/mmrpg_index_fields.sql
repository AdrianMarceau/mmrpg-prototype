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

-- Dumping structure for table pluto1_mmrpg2k11.mmrpg_index_fields
CREATE TABLE IF NOT EXISTS `mmrpg_index_fields` (
  `field_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Field ID',
  `field_token` varchar(100) NOT NULL COMMENT 'Field Token',
  `field_number` varchar(10) NOT NULL COMMENT 'Field Number',
  `field_name` varchar(100) NOT NULL COMMENT 'Field Name',
  `field_game` varchar(10) NOT NULL COMMENT 'Field Game',
  `field_class` varchar(100) NOT NULL COMMENT 'Field Class',
  `field_master` varchar(100) NOT NULL COMMENT 'Field Master',
  `field_master2` varchar(100) NOT NULL COMMENT 'Field Master 2',
  `field_mechas` varchar(100) NOT NULL COMMENT 'Field Mechas',
  `field_editor` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'Field Editor',
  `field_type` varchar(32) NOT NULL COMMENT 'Field Type',
  `field_type2` varchar(32) NOT NULL COMMENT 'Field Type 2',
  `field_multipliers` varchar(256) NOT NULL COMMENT 'Field Multipliers',
  `field_description` varchar(128) NOT NULL COMMENT 'Field Description',
  `field_description2` text NOT NULL COMMENT 'Field Description 2',
  `field_music` varchar(100) NOT NULL COMMENT 'Field Music',
  `field_music_name` varchar(255) NOT NULL COMMENT 'Field Music Name',
  `field_music_link` varchar(255) NOT NULL COMMENT 'Field Music Link',
  `field_background` varchar(100) NOT NULL COMMENT 'Field Background',
  `field_background_frame` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Field Background Frame',
  `field_background_attachments` text NOT NULL COMMENT 'Field Background Attachments',
  `field_foreground` varchar(100) NOT NULL DEFAULT '100' COMMENT 'Field Foreground',
  `field_foreground_frame` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Field Foreground Frame',
  `field_foreground_attachments` text NOT NULL COMMENT 'Field Foreground Attachments',
  `field_functions` varchar(128) NOT NULL COMMENT 'Field Functions',
  `field_flag_hidden` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Field Flag Hidden',
  `field_flag_complete` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Field Flag Complete',
  `field_flag_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Field Flag Published',
  `field_order` smallint(8) NOT NULL DEFAULT '0' COMMENT 'Field Order',
  PRIMARY KEY (`field_id`),
  KEY `robot_token` (`field_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
