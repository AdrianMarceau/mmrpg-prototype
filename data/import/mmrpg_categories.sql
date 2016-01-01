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

-- Dumping structure for table pluto1_mmrpg2k15.mmrpg_categories
DROP TABLE IF EXISTS `mmrpg_categories`;
CREATE TABLE IF NOT EXISTS `mmrpg_categories` (
  `category_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Category ID',
  `category_level` mediumint(8) NOT NULL DEFAULT '5' COMMENT 'Category Level',
  `category_name` varchar(255) NOT NULL COMMENT 'Category Name',
  `category_token` varchar(255) NOT NULL COMMENT 'Category Token',
  `category_description` text CHARACTER SET latin1 NOT NULL COMMENT 'Category Description',
  `category_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Category Published',
  `category_order` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'Category Order',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- Dumping data for table pluto1_mmrpg2k15.mmrpg_categories: 10 rows
DELETE FROM `mmrpg_categories`;
/*!40000 ALTER TABLE `mmrpg_categories` DISABLE KEYS */;
INSERT INTO `mmrpg_categories` (`category_id`, `category_level`, `category_name`, `category_token`, `category_description`, `category_published`, `category_order`) VALUES
	(0, 3, 'Personal Messages', 'personal', '', 1, -1),
	(1, 4, 'News & Updates', 'news', 'The <strong>News & Updates</strong> board contains an archive of all website and prototype-related updates that have been posted to the community.  These include feature additions, mechanics tweaks, new pages, and more.  The <strong>News & Updates</strong> board exists primarily as a means to chronicle the game\'s development and to allow timely feedback from players on the changes made to the game.  Naturally, discussions in this category can only be created by website admins and contributors and are not open to user submission.', 1, 0),
	(2, 3, 'General Discussion', 'general', 'Use the <strong>General Discussion</strong> board for all topics that do not fit into the other categories.  As time goes on, more categories will be added to the community focusing on specific aspects of the game - but until then all topics <em>not</em> involving updates, bug reports, game mechanics or development will stay on this board.  Please stay on-topic, and use common sense when choosing your language and subject matter - this project is and always will be family friendly and adult and/or offensive comments will be removed without notice.  Thank you for your co-operation and please enjoy the community.  :)', 1, 1),
	(3, 4, 'Game Development', 'development', 'The <strong>Game Development</strong> section will be used to display and discuss various aspects of the prototype\'s mechanics and artwork - including feature additions, sprite editing, stat balancing, and anything else about the game\'s creation that would benefit from feedback or collaboration.  Please try to stay on topic, and only create new threads if it\'s necessary.  We look forward to seeing you in the Prototype Devroom discussions!', 1, 5),
	(4, 3, 'Bug Reports', 'bugs', 'Please use the <strong>Bug Reports</strong> section to discuss any and all bugs, errors, or other oddities you notice while playing the game.  By submitting a bug report to this discussion board, others have the opportunity to help or contribute in a way that will making fixing it that much easier on the developers.  Additionally, developers can reply to questions and keep users up-to-date on the status of the patch or fix.  If you have a bug that is not listed on this page, please create a new thread and try to provide as much information as you can so we can better replicate and troubleshoot the issue.   Thank you for your help, and try not to break the game <em>too</em> much. ;)', 1, 6),
	(5, 5, 'Game Mechanics', 'mechanics', 'The <strong>Game Mechanics</strong> section will be used by the developers to catalogue and document the various battle mechanics and other technical aspects of the game that require thorough explanation.  Helpful guides on field multipliers, weaknesses, robot cores, and more can be found here and discussed freely.  Only developers can create threads here, but posts are welcome from all users.', 1, 4),
	(7, 3, 'Chat Room', 'chat', 'Use the <strong>Chat Room</strong> to do exactly that - chat with your fellow community members about whatever is on your mind.  Obviously discussions about the game are encouraged, but not mandatory.  Please be nice to each other and use common sense when choosing your language and subject matter - this community is and always will be family friendly and adult and/or offensive comments will result in a kick or a ban depending on the severity.  Thank you for your co-operation and please enjoy the chat.  :)', 1, 7),
	(8, 3, 'Strategy Discussion', 'strategies', 'Use the <strong>Strategy Discussion</strong> board for all tips, tricks, and guides for playing the Prototype.  This includes easy ways to grind for items and experience, the fastest ways to collect starforce, and anything else you think is worth sharing.  Please stay on topic and know that inappropriate or miscategorized threads may be deleted or moved without notice. Thank you for your co-operation and please enjoy the community.  :)', 1, 3),
	(9, 3, 'Roleplay Discussions', 'roleplay', 'Use the <strong>Roleplay Discussion</strong> board for all your play-by-post role-playing-game needs! While it is not necessary for threads to be related to the prototype per-se, they should be related to Mega Man classic in some form.  Standard community etiquette applies, but please also take note of any thread-specific rules or requirements and be prepared to have your posts deleted by mods if you do not follow them.  Thank you for your co-operation and have fun! :D', 1, 2),
	(10, 3, 'Search Discussions', 'search', 'Use the search form below to filter through the posts and threads of the Mega Man RPG Prototype community and find what you\'re looking for.', 1, 9);
/*!40000 ALTER TABLE `mmrpg_categories` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
