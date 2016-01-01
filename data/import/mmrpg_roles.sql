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

-- Dumping structure for table pluto1_mmrpg2k15.mmrpg_roles
DROP TABLE IF EXISTS `mmrpg_roles`;
CREATE TABLE IF NOT EXISTS `mmrpg_roles` (
  `role_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
  `role_name` varchar(100) NOT NULL COMMENT 'Role Name',
  `role_token` varchar(100) NOT NULL COMMENT 'Role Token',
  `role_level` mediumint(8) NOT NULL COMMENT 'Role Level',
  `role_icon` varchar(32) DEFAULT NULL COMMENT 'Role Icon',
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- Dumping data for table pluto1_mmrpg2k15.mmrpg_roles: 6 rows
DELETE FROM `mmrpg_roles`;
/*!40000 ALTER TABLE `mmrpg_roles` DISABLE KEYS */;
INSERT INTO `mmrpg_roles` (`role_id`, `role_name`, `role_token`, `role_level`, `role_icon`) VALUES
	(1, 'Developer', 'developer', 5, 'yashichi'),
	(2, 'Contributor', 'contributor', 4, 'energy-tank'),
	(3, 'Member', 'member', 3, 'energy-pellet'),
	(5, 'Guest', 'guest', 0, 'weapon-pellet'),
	(6, 'Administrator', 'administrator', 5, 'extra-life'),
	(7, 'Moderator', 'moderator', 4, 'weapon-tank');
/*!40000 ALTER TABLE `mmrpg_roles` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
