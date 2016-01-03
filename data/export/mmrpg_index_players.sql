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

-- Dumping structure for table pluto1_mmrpg2k15.mmrpg_index_players
DROP TABLE IF EXISTS `mmrpg_index_players`;
CREATE TABLE IF NOT EXISTS `mmrpg_index_players` (
  `player_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'Player ID',
  `player_token` varchar(100) NOT NULL COMMENT 'Player Token',
  `player_number` varchar(10) NOT NULL COMMENT 'Player Number',
  `player_name` varchar(100) NOT NULL COMMENT 'Player Name',
  `player_game` varchar(10) NOT NULL COMMENT 'Player Game',
  `player_class` varchar(32) NOT NULL COMMENT 'Player Class',
  `player_image` varchar(64) NOT NULL COMMENT 'Player Image',
  `player_image_size` smallint(3) NOT NULL DEFAULT '40' COMMENT 'Player Image Size',
  `player_image_editor` mediumint(8) NOT NULL DEFAULT '0' COMMENT 'Player Image Editor',
  `player_image_alts` text NOT NULL COMMENT 'Player Image Alts',
  `player_type` varchar(32) NOT NULL COMMENT 'Player Core',
  `player_description` varchar(128) NOT NULL COMMENT 'Player Description',
  `player_description2` text NOT NULL COMMENT 'Player Description 2',
  `player_energy` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Energy',
  `player_weapons` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Weapons',
  `player_attack` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Attack',
  `player_defense` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Defense',
  `player_speed` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Player Speed',
  `player_abilities_rewards` text NOT NULL COMMENT 'Player Abilities Rewards',
  `player_abilities_compatible` text NOT NULL COMMENT 'Player Abilities Compatible',
  `player_robots_rewards` text NOT NULL COMMENT 'Player Robots Rewards',
  `player_robots_compatible` text NOT NULL COMMENT 'Player Robots Compatible',
  `player_quotes_start` varchar(256) NOT NULL COMMENT 'Player Quotes Start',
  `player_quotes_taunt` varchar(256) NOT NULL COMMENT 'Player Quotes Taunt',
  `player_quotes_victory` varchar(256) NOT NULL COMMENT 'Player Quotes Victory',
  `player_quotes_defeat` varchar(256) NOT NULL COMMENT 'Player Quotes Defeat',
  `player_functions` varchar(128) NOT NULL COMMENT 'Player Functions',
  `player_flag_hidden` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Player Flag Hidden',
  `player_flag_complete` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Player Flag Complete',
  `player_flag_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Player Flag Published',
  `player_order` smallint(8) NOT NULL DEFAULT '0' COMMENT 'Player Order',
  PRIMARY KEY (`player_id`),
  KEY `player_token` (`player_token`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- Dumping data for table pluto1_mmrpg2k15.mmrpg_index_players: 5 rows
DELETE FROM `mmrpg_index_players`;
/*!40000 ALTER TABLE `mmrpg_index_players` DISABLE KEYS */;
INSERT INTO `mmrpg_index_players` (`player_id`, `player_token`, `player_number`, `player_name`, `player_game`, `player_class`, `player_image`, `player_image_size`, `player_image_editor`, `player_image_alts`, `player_type`, `player_description`, `player_description2`, `player_energy`, `player_weapons`, `player_attack`, `player_defense`, `player_speed`, `player_abilities_rewards`, `player_abilities_compatible`, `player_robots_rewards`, `player_robots_compatible`, `player_quotes_start`, `player_quotes_taunt`, `player_quotes_victory`, `player_quotes_defeat`, `player_functions`, `player_flag_hidden`, `player_flag_complete`, `player_flag_published`, `player_order`) VALUES
	(1, 'player', '0', 'Player', '', 'system', 'player', 40, 0, '', '', '', '', 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', 'players/player.php', 1, 1, 0, 0),
	(2, 'dr-light', '1', 'Dr. Light', 'MM01', 'doctor', 'dr-light', 40, 412, '', 'defense', 'World Renowned Scientist', 'Dr. Thomas Light is a famous scientist and is accredited for creating the first "living" robot, Blues. However, there was something wrong with Blues\'s generator and he left, while Dr. Light was left to mourn. He then created Rock and Roll from Blues\'s mistakes and he went on to make many others. After Dr. Wily started his quest to conquer the world, Dr. Light upgraded Rock to be Mega Man and continues to help Mega Man for his goal of peace. Dr. Light has created the First line of robots as well as the Ninths in the hopes they could benefit humanity. Lately, Dr. Light has started work on a "Prototype", but it is unknown what it is.', 0, 0, 0, 25, 0, '[{"points":0,"token":"buster-shot"},{"points":0,"token":"light-buster"}]', '["buster-shot","light-buster"]', '[{"points":0,"token":"mega-man"}]', '["mega-man","roll","cut-man","guts-man","\'ice-man","bomb-man","fire-man","elec-man","time-man","oil-man"]', 'Fight, my robots! Together we cannot lose!', 'Please surrender before anyone else gets hurt!', 'I couldn\'t have won without my wonderful robots!', 'I\'m so sorry everyone... I have failed you.', 'players/dr-light.php', 0, 1, 1, 0),
	(3, 'dr-wily', '2', 'Dr. Wily', 'MM01', 'doctor', 'dr-wily', 40, 412, '', 'attack', 'Legendary Mad Scientist', 'Dr. Wily was once Dr. Light\'s partner, but after Light\'s receiving of the credit of their projects, Wily decided to try to rule the world for fame. His full name is Albert W. Wily and he has a high intellect. Wily  created many robots and tactics to defeat his enemies but they always end in failure because of the heroic efforts of our heroes. He has worked together with many other evil beings over time, but usually is betrayed by them. Wily also has made various battle machines and capsules which also usually end in failure. Wily has used an extreme variety of nefarious attempts that the average mad scientist wouldn’t even consider, such as using alien bots and gods, robot-only diseases, energy from the stars, forging fake alliances, framing Dr. Light and Cossack, having a robot cause a revolution and destroy his castle just so he can control things from behind the scenes, and downright stealing and copying other’s robots and ideas. Recently, Dr. Wily heard about some mysterious Prototype from one of his spy-bots, and decided to see what was going on about that.', 0, 0, 25, 0, 0, '[{"points":0,"token":"buster-shot"},{"points":0,"token":"wily-buster"}]', '["buster-shot","wily-buster"]', '[{"points":0,"token":"bass"}]', '["bass","disco","air-man","bubble-man","crash-man","flash-man","heat-man","metal-man","quick-man","wood-man"]', 'With my robots by my side, I\'ll take over this world!', 'Your world is mine! Are you ready to be eliminated?', 'Feeling sorry for yourself? Mwhahaha!', 'F-Forgive me! I only wanted to test your abilities!', 'players/dr-wily.php', 0, 1, 1, 1),
	(4, 'dr-cossack', '3', 'Dr. Cossack', 'MM04', 'doctor', 'dr-cossack', 40, 412, '', 'speed', 'Russion Robotics Engineer', 'Dr. Mikhail Cossack is a famous scientist known throughout the world from Russia. A long time ago, Dr. Cossack sent out 8 robots to destroy Mega Man, his colleague\'s own robot. As it turned out, Dr. Wily was manipulating Dr. Cossack by holding his daughter, Kalinka hostage. Mega Man saved the day and everybody lived happily. Cossack expressed his gratitude by creating Beat, a robotic bird that has been a great asset to Mega Man, as well as upgrading his Mega Buster. Lately, Cossack has been allowing Proto Man to live with them, in the deal that he will protect his daughter from harm’s way. Dr. Cossack has also been seen with Dr. Light working on something called the Prototype, but it is unknown what they could possibly be working on.', 0, 0, 0, 0, 25, '[{"points":0,"token":"buster-shot"},{"points":0,"token":"cossack-buster"}]', '["buster-shot","cossack-buster"]', '[{"points":0,"token":"proto-man"}]', '["proto-man","rhythm","bright-man","toad-man","drill-man","pharaoh-man","ring-man","dust-man","dive-man","skull-man"]', 'Let\'s end this quickly - I have many other things to do.', 'Please do not waste my time - I\'ve important business elsewhere...', 'I had no doubt I would win. It was just a matter of timing.', 'There must be some mistake... Did I miscalculate?', 'players/dr-cossack.php', 0, 1, 1, 2),
	(5, 'dr-lalinde', '4', 'Dr. Lalinde', 'MMRPG', 'doctor', 'dr-lalinde', 40, 412, '', 'energy', 'Geoworks International Scientist', 'Dr. Noel Lalinde is a former colleague of Dr. Light currently working for "Geoworks International."  Dr. Lalinde once opposed Dr. Light in a great debate over the continued development of Advanced A.I., using the logic that robots are tools with no need to think or feel and that there is harm encouraging unnecessary emotional attachments to them.  It is during the course of this debate that Lalinde\'s reason for opposing advanced A.I. is revealed; her own creation, Quake Woman, suffered extreme damage during the course of her work and - feeling attached to the robot as if it was her own daughter - caused Lalinde so much emotional pain that she decided to remove Quake Woman\'s personality all-together.  Lalinde\'s involvment in the Prototype is purely coincidental, and it is currently unclear how she will influence the events that unfold...', 25, 0, 0, 0, 0, '[{"points":0,"token":"buster-shot"},{"points":0,"token":"lalinde-buster"}]', '["buster-shot","lalinde-buster"]', '[{"points":0,"token":"duo"}]', '["duo","quake-woman","vesper-woman"]', '', '', '', '', 'players/dr-lalinde.php', 1, 0, 0, 3);
/*!40000 ALTER TABLE `mmrpg_index_players` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
