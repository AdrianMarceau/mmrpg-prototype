-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.31-0ubuntu0.12.04.2 - (Ubuntu)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table mmrpg2k11.mmrpg_posts
CREATE TABLE IF NOT EXISTS `mmrpg_posts` (
  `post_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Post ID',
  `category_id` mediumint(8) NOT NULL COMMENT 'Category ID',
  `thread_id` mediumint(8) NOT NULL COMMENT 'Post ID',
  `user_id` mediumint(8) NOT NULL COMMENT 'User ID',
  `user_ip` varchar(255) NOT NULL DEFAULT '0.0.0.0' COMMENT 'User IP',
  `post_body` text NOT NULL COMMENT 'Post Body',
  `post_frame` varchar(100) NOT NULL COMMENT 'Post Frame',
  `post_date` int(8) NOT NULL DEFAULT '0' COMMENT 'Post Date',
  `post_mod` int(8) NOT NULL DEFAULT '0' COMMENT 'Post Mod',
  `post_deleted` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Post Deleted',
  `post_votes` mediumint(8) unsigned NOT NULL COMMENT 'Post Votes',
  `post_target` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Post Target',
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
