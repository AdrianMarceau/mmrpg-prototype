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

-- Dumping structure for table pluto1_mmrpg2k11.mmrpg_leaderboard
CREATE TABLE IF NOT EXISTS `mmrpg_leaderboard` (
  `board_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Board ID',
  `user_id` mediumint(8) NOT NULL COMMENT 'User ID',
  `save_id` mediumint(8) NOT NULL COMMENT 'Save ID',
  `board_points` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Points',
  `board_points_dr_light` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Points (Dr. Light)',
  `board_points_dr_wily` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Points (Dr. Wily)',
  `board_points_dr_cossack` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Points (Dr. Cossack)',
  `board_points_pending` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Points Pending',
  `board_points_pending_dr_light` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Points Pending (Dr. Light)',
  `board_points_pending_dr_wily` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Points Pending (Dr. Wily)',
  `board_points_pending_dr_cossack` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Points Pending (Dr. Cossack)',
  `board_robots` text NOT NULL COMMENT 'Board Robots',
  `board_robots_dr_light` text NOT NULL COMMENT 'Board Robots (Dr. Light)',
  `board_robots_dr_wily` text NOT NULL COMMENT 'Board Robots (Dr. Wily)',
  `board_robots_dr_cossack` text NOT NULL COMMENT 'Board Robots (Dr. Cossack)',
  `board_battles` text NOT NULL COMMENT 'Board Battles',
  `board_battles_dr_light` text NOT NULL COMMENT 'Board Battles (Dr. Light)',
  `board_battles_dr_wily` text NOT NULL COMMENT 'Board Battles (Dr. Wily)',
  `board_battles_dr_cossack` text NOT NULL COMMENT 'Board Battles (Dr. Cossack)',
  `board_awards` text NOT NULL COMMENT 'Board Awards',
  `board_awards_dr_light` text NOT NULL COMMENT 'Board Awards Dr. Light',
  `board_awards_dr_wily` text NOT NULL COMMENT 'Board Awards Dr. Light',
  `board_awards_dr_cossack` text NOT NULL COMMENT 'Board Awards Dr. Light',
  `board_stars` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Stars',
  `board_stars_dr_light` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Stars (Dr. Light)',
  `board_stars_dr_wily` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Stars (Dr. Wily)',
  `board_stars_dr_cossack` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Stars (Dr. Cossack)',
  `board_abilities` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Abilities',
  `board_abilities_dr_light` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Abilities (Dr. Light)',
  `board_abilities_dr_wily` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Abilities (Dr. Wily)',
  `board_abilities_dr_cossack` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Abilities (Dr. Cossack)',
  `board_missions` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Missions',
  `board_missions_dr_light` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Missions (Dr. Light)',
  `board_missions_dr_wily` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Missions (Dr. Wily)',
  `board_missions_dr_cossack` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'Board Missions (Dr. Cossack)',
  `board_date_created` int(8) NOT NULL DEFAULT '0' COMMENT 'Board Date Created',
  `board_date_modified` int(8) NOT NULL DEFAULT '0' COMMENT 'Board Date Created',
  PRIMARY KEY (`board_id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `save_id` (`save_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
