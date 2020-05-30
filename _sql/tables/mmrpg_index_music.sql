/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_index_music` (
  `music_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Music ID',
  `music_token` varchar(128) NOT NULL COMMENT 'Music Token',
  `music_album` varchar(64) NOT NULL COMMENT 'Music Album',
  `music_game` varchar(10) NOT NULL COMMENT 'Music Game',
  `music_name` varchar(256) NOT NULL COMMENT 'Music Name',
  `music_link` varchar(256) NOT NULL COMMENT 'Music Link',
  `music_order` smallint(4) NOT NULL DEFAULT '0' COMMENT 'Music Order',
  PRIMARY KEY (`music_id`),
  UNIQUE KEY `music_token_music_album` (`music_token`,`music_album`),
  KEY `music_token` (`music_token`),
  KEY `music_album` (`music_album`),
  KEY `music_game` (`music_game`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
