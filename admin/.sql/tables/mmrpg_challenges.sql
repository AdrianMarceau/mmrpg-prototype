/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_challenges` (
  `challenge_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Challenge ID',
  `challenge_kind` varchar(32) NOT NULL DEFAULT 'user' COMMENT 'Challenge Kind',
  `challenge_creator` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Creator',
  `challenge_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'Challenge Button',
  `challenge_description` varchar(256) NOT NULL DEFAULT '' COMMENT 'Challenge Description',
  `challenge_robot_limit` smallint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Robot Limit',
  `challenge_turn_limit` smallint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Turn Limit',
  `challenge_field_data` text NOT NULL COMMENT 'Challenge Field Data',
  `challenge_target_data` text NOT NULL COMMENT 'Challenge Target Data',
  `challenge_reward_data` text NOT NULL COMMENT 'Challenge Reward Data',
  `challenge_flag_published` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Challenge Flag (Published)',
  `challenge_flag_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Flag (Hidden)',
  `challenge_times_accessed` mediumint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Counter (Times Accessed)',
  `challenge_times_concluded` mediumint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Counter (Times Concluded)',
  `challenge_user_victories` mediumint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Counter (User Victories)',
  `challenge_user_defeats` mediumint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Counter (User Defeats)',
  `challenge_date_created` int(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Date Created',
  `challenge_date_modified` int(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Challenge Date Modified',
  PRIMARY KEY (`challenge_id`),
  KEY `challenge_creator` (`challenge_creator`),
  KEY `challenge_kind` (`challenge_kind`),
  KEY `challenge_flag_hidden` (`challenge_flag_hidden`),
  KEY `challenge_flag_published` (`challenge_flag_published`),
  KEY `challenge_published_hidden` (`challenge_flag_published`,`challenge_flag_hidden`),
  KEY `challenge_kind_published_hidden` (`challenge_kind`,`challenge_flag_published`,`challenge_flag_hidden`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
