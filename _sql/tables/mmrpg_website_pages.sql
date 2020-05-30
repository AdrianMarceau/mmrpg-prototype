/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `mmrpg_website_pages` (
  `parent_id` mediumint(8) unsigned NOT NULL COMMENT 'Parent Page ID',
  `page_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Page ID',
  `page_token` varchar(128) NOT NULL COMMENT 'Page Token',
  `page_name` varchar(128) NOT NULL COMMENT 'Page Name',
  `page_url` varchar(128) NOT NULL COMMENT 'Page URL',
  `page_title` varchar(128) NOT NULL COMMENT 'Page Title',
  `page_content` text NOT NULL COMMENT 'Page Content',
  `page_seo_title` varchar(64) NOT NULL COMMENT 'Page Title',
  `page_seo_keywords` varchar(128) NOT NULL COMMENT 'Page Title',
  `page_seo_description` varchar(256) NOT NULL COMMENT 'Page Description',
  `page_date_created` int(8) NOT NULL DEFAULT '0' COMMENT 'Page Date Created',
  `page_date_modified` int(8) NOT NULL DEFAULT '0' COMMENT 'Page Date Created',
  `page_flag_hidden` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Page Flag Hidden',
  `page_flag_published` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Page Flag Published',
  `page_order` smallint(8) NOT NULL DEFAULT '0' COMMENT 'Page Order',
  PRIMARY KEY (`page_id`),
  KEY `parent_id` (`parent_id`),
  KEY `page_token` (`page_token`),
  KEY `page_url` (`page_url`),
  KEY `page_flag_hidden` (`page_flag_hidden`),
  KEY `page_flag_published` (`page_flag_published`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
