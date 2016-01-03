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

-- Dumping structure for table pluto1_mmrpg2k15.mmrpg_threads
DROP TABLE IF EXISTS `mmrpg_threads`;
CREATE TABLE IF NOT EXISTS `mmrpg_threads` (
  `thread_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Topic ID',
  `category_id` mediumint(8) NOT NULL COMMENT 'Category ID',
  `user_id` mediumint(8) NOT NULL COMMENT 'User ID',
  `user_ip` varchar(255) NOT NULL DEFAULT '0.0.0.0' COMMENT 'User IP',
  `thread_name` varchar(255) NOT NULL COMMENT 'Thread Name',
  `thread_token` varchar(255) NOT NULL COMMENT 'Thread Token',
  `thread_body` text NOT NULL COMMENT 'Thread Body',
  `thread_frame` varchar(100) NOT NULL COMMENT 'Thread Frame',
  `thread_colour` varchar(100) NOT NULL COMMENT 'Thread Colour',
  `thread_date` int(8) NOT NULL DEFAULT '0' COMMENT 'Thread Date',
  `thread_mod_date` int(8) NOT NULL DEFAULT '0' COMMENT 'Thread Mod Date',
  `thread_mod_user` int(8) NOT NULL DEFAULT '0' COMMENT 'Thread Mod User',
  `thread_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Thread Published',
  `thread_locked` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Thread Locked',
  `thread_sticky` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Thread Sticky',
  `thread_views` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Thread Views',
  `thread_votes` mediumint(8) unsigned NOT NULL COMMENT 'Thread Votes',
  `thread_target` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Thread Target',
  PRIMARY KEY (`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
