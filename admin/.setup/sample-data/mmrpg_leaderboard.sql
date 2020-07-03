/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40000 ALTER TABLE `mmrpg_leaderboard` DISABLE KEYS */;
INSERT INTO `mmrpg_leaderboard` (`board_id`, `user_id`, `save_id`, `board_points`, `board_points_dr_light`, `board_points_dr_wily`, `board_points_dr_cossack`, `board_points_pending`, `board_points_pending_dr_light`, `board_points_pending_dr_wily`, `board_points_pending_dr_cossack`, `board_points_legacy`, `board_points_legacy2`, `board_points_dr_light_legacy`, `board_points_dr_wily_legacy`, `board_points_dr_cossack_legacy`, `board_robots`, `board_robots_dr_light`, `board_robots_dr_wily`, `board_robots_dr_cossack`, `board_robots_count`, `board_battles`, `board_battles_dr_light`, `board_battles_dr_wily`, `board_battles_dr_cossack`, `board_awards`, `board_awards_dr_light`, `board_awards_dr_wily`, `board_awards_dr_cossack`, `board_stars`, `board_stars_dr_light`, `board_stars_dr_wily`, `board_stars_dr_cossack`, `board_items`, `board_abilities`, `board_abilities_dr_light`, `board_abilities_dr_wily`, `board_abilities_dr_cossack`, `board_missions`, `board_missions_dr_light`, `board_missions_dr_wily`, `board_missions_dr_cossack`, `board_date_created`, `board_date_modified`) VALUES
    (1, 1, 1, 114002, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '[mega-man:8],[roll:2]', '[mega-man:8],[roll:2]', '', '', 2, '', '', '', '', '', '', '', '', 0, 0, 0, 0, 0, 5, 0, 0, 0, 0, 0, 0, 0, 1590803529, 1593725372),
    (2, 2, 2, 114001, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '[mega-man:6],[roll:2]', '[mega-man:6],[roll:2]', '', '', 2, '', '', '', '', '', '', '', '', 0, 0, 0, 0, 0, 5, 0, 0, 0, 0, 0, 0, 0, 1590803362, 1593726722),
    (3, 3, 3, 112000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '[mega-man:4],[roll:2]', '[mega-man:4],[roll:2]', '', '', 2, '', '', '', '', '', '', '', '', 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 0, 0, 1590803744, 1593739987),
    (4, 4, 4, 106000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '[mega-man:3],[roll:2]', '[mega-man:3],[roll:2]', '', '', 2, '', '', '', '', '', '', '', '', 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 0, 0, 1590803661, 1593740189),
    (5, 5, 5, 94000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '[mega-man:2]', '[mega-man:2]', '', '', 1, '', '', '', '', '', '', '', '', 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 1590803398, 1593740442)
    ;
/*!40000 ALTER TABLE `mmrpg_leaderboard` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
