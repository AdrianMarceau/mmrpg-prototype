/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_challenges_waveboard` (
  `board_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Board ID',
  `user_id` mediumint(8) unsigned DEFAULT '0' COMMENT 'User ID',
  `challenge_result` varchar(32) DEFAULT 'victory' COMMENT 'Challenge Result',
  `challenge_robots_used` int(11) unsigned DEFAULT '0' COMMENT 'Challenge Robots Used',
  `challenge_turns_used` int(11) unsigned DEFAULT '0' COMMENT 'Challenge Turns Used',
  `challenge_team_config` text COMMENT 'Challenge Team Config',
  `challenge_waves_completed` int(11) unsigned DEFAULT '0' COMMENT 'Challenge Waves Completed',
  `challenge_date_firstclear` int(11) unsigned DEFAULT '0' COMMENT 'Challenge Date First Clear',
  `challenge_date_lastclear` int(11) unsigned DEFAULT '0' COMMENT 'Challenge Date Last Clear',
  `challenge_wave_savestate` mediumtext COMMENT 'Challenge Wave Savestate',
  PRIMARY KEY (`board_id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `challenge_result` (`challenge_result`),
  KEY `user_id_challenge_result` (`user_id`,`challenge_result`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
