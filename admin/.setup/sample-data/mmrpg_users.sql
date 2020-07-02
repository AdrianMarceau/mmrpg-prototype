/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40000 ALTER TABLE `mmrpg_users` DISABLE KEYS */;
INSERT INTO `mmrpg_users` (`user_id`, `role_id`, `contributor_id`, `user_name`, `user_name_clean`, `user_name_public`, `user_password_encoded`, `user_omega`, `user_gender`, `user_profile_text`, `user_credit_text`, `user_admin_text`, `user_credit_line`, `user_image_path`, `user_background_path`, `user_colour_token`, `user_email_address`, `user_website_address`, `user_ip_addresses`, `user_game_difficulty`, `user_threads_upvoted`, `user_threads_downvoted`, `user_posts_upvoted`, `user_posts_downvoted`, `user_date_created`, `user_date_accessed`, `user_date_modified`, `user_date_birth`, `user_last_login`, `user_backup_login`, `user_flag_approved`, `user_flag_postpublic`, `user_flag_postprivate`, `user_flag_allowchat`) VALUES
	(-1, 3, 0, 'guest', 'guest', '', '', '20618f17e896961296207783cc960180', '', '', '', '', '', '', '', '', 'guest@mmrpg-world.net', '', '', 'normal', '', '', '', '', 1363231787, 1363231787, 1363231787, 0, 1363231787, 1363231787, 0, 1, 1, 1),
	(1, 1, 0, 'mmrpg_developer', 'mmrpgdeveloper', '', '94fd0b772db9610715f81e894a7bcd6c', '6b8d776234fcd5f8afddc46f36f93b45', '', '', '', '', '', '', '', '', 'developer@mmrpg-world.net', '', '', 'normal', '', '', '', '', 1590803529, 1590803534, 1590803622, 543906000, 0, 0, 1, 1, 1, 1),
    (2, 6, 0, 'mmrpg_admin', 'mmrpgadmin', '', 'db6bdd2e490689740b942a7e929e1192', 'dfc53addb7a2a9ff4796a2c967eb0402', '', '', '', '', '', '', '', '', 'admin@mmrpg-world.net', '', '', 'normal', '', '', '', '', 1590803362, 1590803369, 1590803574, 543906000, 0, 0, 1, 1, 1, 1),
    (3, 2, 0, 'mmrpg_contributor', 'mmrpgcontributor', '', 'd263952c4b47adbf041256484f0c6390', 'cdb21c2e92577a41714f69ba64b0ba53', '', '', '', '', '', '', '', '', 'contributor@mmrpg-world.net', '', '', 'normal', '', '', '', '', 1590803744, 1590803750, 1590803773, 543906000, 0, 0, 1, 1, 1, 1),
    (4, 7, 0, 'mmrpg_moderator', 'mmrpgmoderator', '', '859f2b9968407f72d12deb481d4425f5', 'ec642135a277359cf24156844b7da250', '', '', '', '', '', '', '', '', 'moderator@mmrpg-world.net', '', '', 'normal', '', '', '', '', 1590803661, 1590803668, 1590803706, 543906000, 0, 0, 1, 1, 1, 1),
	(5, 3, 0, 'mmrpg_member', 'mmrpgmember', '', '4e6a3811f962db1da736f17777dfb21d', 'ac7d2cb2e9841831f19c894642e794a2', '', '', '', '', '', '', '', '', 'member@mmrpg-world.net', '', '', 'normal', '', '', '', '', 1590803398, 1590803404, 1590803608, 543906000, 0, 0, 1, 1, 1, 1)
    ;
/*!40000 ALTER TABLE `mmrpg_users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
