/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40000 ALTER TABLE `mmrpg_posts` DISABLE KEYS */;
INSERT INTO `mmrpg_posts` (`post_id`, `category_id`, `thread_id`, `user_id`, `user_ip`, `post_body`, `post_frame`, `post_date`, `post_mod`, `post_deleted`, `post_votes`, `post_target`) VALUES
    (1, 1, 1, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0),
    (2, 2, 2, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0),
    (3, 3, 3, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0),
    (4, 4, 4, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0),
    (5, 5, 5, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0),
    (6, 7, 6, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0),
    (7, 8, 7, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0),
    (8, 9, 8, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0),
    (9, 10, 9, 5, '0.0.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac commodo purus.', '00', 1590803529, 0, 0, 0, 0)
    ;
/*!40000 ALTER TABLE `mmrpg_posts` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
