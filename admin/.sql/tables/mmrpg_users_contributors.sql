/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_users_contributors` (
  `contributor_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Contributor ID',
  `role_id` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'Role ID',
  `user_name` varchar(128) NOT NULL COMMENT 'User Name',
  `user_name_clean` varchar(128) NOT NULL COMMENT 'User Name Clean',
  `user_name_public` varchar(128) NOT NULL COMMENT 'User Name Public',
  `user_gender` varchar(10) NOT NULL DEFAULT '' COMMENT 'User Gender',
  `user_colour_token` varchar(100) NOT NULL COMMENT 'User Colour Token',
  `user_image_path` varchar(255) NOT NULL COMMENT 'User Image Path',
  `user_background_path` varchar(255) NOT NULL COMMENT 'User Background Path',
  `user_credit_line` varchar(255) NOT NULL COMMENT 'User Credit Line',
  `user_credit_text` text NOT NULL COMMENT 'User Credit Text',
  `user_website_address` varchar(255) NOT NULL COMMENT 'User Website Address',
  `user_date_created` int(8) NOT NULL DEFAULT '0' COMMENT 'User Date Created',
  `user_date_modified` int(8) NOT NULL DEFAULT '0' COMMENT 'User Date Modified',
  `contributor_flag_showcredits` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Contributor Flag Show Credits',
  PRIMARY KEY (`contributor_id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  KEY `user_name_clean` (`user_name_clean`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
