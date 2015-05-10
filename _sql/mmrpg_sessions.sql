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

-- Dumping structure for table pluto1_mmrpg2k11.mmrpg_sessions
CREATE TABLE IF NOT EXISTS `mmrpg_sessions` (
  `session_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Session ID',
  `user_id` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'User ID',
  `session_key` varchar(255) NOT NULL COMMENT 'Session Key',
  `session_href` varchar(255) NOT NULL COMMENT 'Session Href',
  `session_start` int(8) NOT NULL COMMENT 'Session Start',
  `session_access` int(8) NOT NULL DEFAULT '0' COMMENT 'Session Access',
  `session_ip` varchar(100) NOT NULL DEFAULT '0.0.0.0' COMMENT 'Session IP',
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
