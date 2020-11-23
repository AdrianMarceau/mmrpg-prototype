/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40000 ALTER TABLE `mmrpg_users_permissions` DISABLE KEYS */;
INSERT INTO `mmrpg_users_permissions` (`user_id`, `allow_all`, `allow_edit-users`, `allow_edit-users_edit-user-accounts`, `allow_edit-users_edit-user-permissions`, `allow_edit-content`, `allow_edit-content_edit-sql`, `allow_edit-content_edit-types`, `allow_edit-content_edit-players`, `allow_edit-content_edit-robots`, `allow_edit-content_edit-robots_edit-robot-masters`, `allow_edit-content_edit-robots_edit-support-mechas`, `allow_edit-content_edit-robots_edit-fortress-bosses`, `allow_edit-content_edit-abilities`, `allow_edit-content_edit-abilities_edit-master-abilities`, `allow_edit-content_edit-abilities_edit-mecha-abilities`, `allow_edit-content_edit-abilities_edit-boss-abilities`, `allow_edit-content_edit-items`, `allow_edit-content_edit-fields`, `allow_edit-content_edit-battles`, `allow_edit-content_edit-challenges`, `allow_edit-content_edit-challenges_edit-user-challenges`, `allow_edit-content_edit-challenges_edit-event-challenges`, `allow_edit-content_edit-stars`, `allow_edit-content_edit-pages`, `allow_edit-content_commit-changes`, `allow_edit-content_revert-changes`, `allow_push-content`, `allow_pull-content`, `allow_pull-content_pull-content-updates`, `allow_pull-content_pull-core-updates`, `allow_pull-content_pull-user-data`, `allow_delete-cached-files`, `allow_refresh-leaderboard`, `allow_purge-bogus-users`, `allow_watch-error-logs`) VALUES
  (-1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
  (1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
  (2, 0, 1, 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 0, 0, 1),
  (3, 0, 1, 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 0, 0, 1)
  ;
/*!40000 ALTER TABLE `mmrpg_users_permissions` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;