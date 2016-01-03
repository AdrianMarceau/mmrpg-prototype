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

-- Dumping structure for table pluto1_mmrpg2k15.mmrpg_index_types
DROP TABLE IF EXISTS `mmrpg_index_types`;
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
  KEY `type_token` (`type_token`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

-- Dumping data for table pluto1_mmrpg2k15.mmrpg_index_types: 33 rows
DELETE FROM `mmrpg_index_types`;
/*!40000 ALTER TABLE `mmrpg_index_types` DISABLE KEYS */;
INSERT INTO `mmrpg_index_types` (`type_id`, `type_token`, `type_name`, `type_class`, `type_colour_dark`, `type_colour_light`, `type_flag_hidden`, `type_flag_published`, `type_order`) VALUES
	(1, 'none', 'None', 'special', '[111,111,111]', '[90,90,90]', 0, 1, 1),
	(2, 'cutter', 'Cutter', 'normal', '[109,109,109]', '[118,120,126]', 0, 1, 2),
	(3, 'impact', 'Impact', 'normal', '[117,112,64]', '[97,93,54]', 0, 1, 3),
	(4, 'freeze', 'Freeze', 'normal', '[83,184,184]', '[51,148,148]', 0, 1, 4),
	(5, 'explode', 'Explode', 'normal', '[197,128,24]', '[172,111,20]', 0, 1, 5),
	(6, 'flame', 'Flame', 'normal', '[192,57,38]', '[172,45,27]', 0, 1, 6),
	(7, 'electric', 'Electric', 'normal', '[167,161,42]', '[136,137,23]', 0, 1, 7),
	(8, 'time', 'Time', 'normal', '[106,77,119]', '[91,66,102]', 0, 1, 8),
	(9, 'earth', 'Earth', 'normal', '[104,83,54]', '[87,68,41]', 0, 1, 9),
	(10, 'wind', 'Wind', 'normal', '[101,121,61]', '[82,97,50]', 0, 1, 10),
	(11, 'water', 'Water', 'normal', '[61,124,190]', '[43,101,163]', 0, 1, 11),
	(12, 'swift', 'Swift', 'normal', '[180,71,43]', '[179,65,36]', 0, 1, 12),
	(13, 'nature', 'Nature', 'normal', '[37,155,51]', '[28,122,39]', 0, 1, 13),
	(14, 'missile', 'Missile', 'normal', '[182,161,76]', '[150,132,60]', 0, 1, 14),
	(15, 'crystal', 'Crystal', 'normal', '[189,110,169]', '[179,95,158]', 0, 1, 15),
	(16, 'shadow', 'Shadow', 'normal', '[63,57,73]', '[52,46,63]', 0, 1, 16),
	(17, 'space', 'Space', 'normal', '[61,65,102]', '[54,57,90]', 0, 1, 17),
	(18, 'shield', 'Shield', 'normal', '[102,146,120]', '[95,136,112]', 0, 1, 18),
	(19, 'laser', 'Laser', 'normal', '[172,76,95]', '[148,51,70]', 0, 1, 19),
	(20, 'copy', 'Copy', 'normal', '[147,137,177]', '[135,124,167]', 0, 1, 20),
	(21, 'energy', 'Energy', 'special', '[68,105,59]', '[89,138,78]', 1, 1, 21),
	(22, 'weapons', 'Weapons', 'special', '[61,124,190]', '[43,101,163]', 1, 1, 22),
	(23, 'attack', 'Attack', 'special', '[107,63,63]', '[139,80,80]', 1, 1, 23),
	(24, 'defense', 'Defense', 'special', '[62,76,105]', '[80,99,138]', 1, 1, 24),
	(25, 'speed', 'Speed', 'special', '[105,87,117]', '[139,115,155]', 1, 1, 25),
	(26, 'empty', 'Empty', 'special', '[22,22,22]', '[32,32,32]', 1, 1, 26),
	(27, 'light', 'Light', 'special', '[62,76,105]', '[80,99,138]', 1, 1, 27),
	(28, 'wily', 'Wily', 'special', '[107,63,63]', '[139,80,80]', 1, 1, 28),
	(29, 'cossack', 'Cossack', 'special', '[105,87,117]', '[139,115,155]', 1, 1, 28),
	(30, 'damage', 'Damage', 'special', '[107,63,63]', '[139,80,80]', 1, 1, 30),
	(31, 'recovery', 'Recovery', 'special', '[68,105,59]', '[89,138,78]', 1, 1, 31),
	(32, 'level', 'Level', 'special', '[167,161,42]', '[136,137,23]', 1, 1, 32),
	(33, 'experience', 'Experience', 'special', '[109,109,109]', '[118,120,126]', 1, 1, 33);
/*!40000 ALTER TABLE `mmrpg_index_types` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
