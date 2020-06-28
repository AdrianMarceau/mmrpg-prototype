/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40000 ALTER TABLE `mmrpg_roles` DISABLE KEYS */;
INSERT INTO `mmrpg_roles` (`role_id`, `role_name`, `role_name_full`, `role_token`, `role_level`, `role_icon`, `role_colour`) VALUES
	(1, 'Developer', 'Game Developer', 'developer', 5, 'yashichi', 'flame'),
	(2, 'Contributor', 'Content Contributor', 'contributor', 4, 'energy-tank', 'nature'),
	(3, 'Member', 'Member', 'member', 3, 'energy-pellet', 'shield'),
	(5, 'Guest', 'Guest', 'guest', 0, 'weapon-pellet', 'purple'),
	(6, 'Administrator', 'Website Administrator', 'administrator', 5, 'extra-life', 'water'),
	(7, 'Moderator', 'Community Moderator', 'moderator', 4, 'weapon-tank', 'electric');
/*!40000 ALTER TABLE `mmrpg_roles` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
