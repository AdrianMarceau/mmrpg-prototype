<?
/*
 * INDEX PAGE : LEADERBOARD
 */

// If the current sub was not provided OR if a specific page was requested
if (empty($this_current_sub) || preg_match('/^([0-9]+)$/i', $this_current_sub)){
  // Require the index page
  require_once('page.leaderboard_index.php');
}
// If a player has been provided in the URL as a sub
elseif (!empty($this_current_sub) && preg_match('/^([-_a-z0-9]+)$/i', $this_current_sub)){
  // Attempt to collect data for this player from the database
  $temp_playerquery = 'SELECT
    mmrpg_users.user_id,
    mmrpg_users.role_id,
    mmrpg_roles.role_token,
    mmrpg_roles.role_name,
    mmrpg_roles.role_icon,
    mmrpg_users.user_name,
    mmrpg_users.user_name_clean,
    mmrpg_users.user_name_public,
    mmrpg_users.user_gender,
    mmrpg_users.user_profile_text,
    mmrpg_users.user_website_address,
    mmrpg_users.user_image_path,
    mmrpg_users.user_background_path,
    mmrpg_users.user_colour_token,
    mmrpg_users.user_email_address,
    mmrpg_users.user_date_created,
    mmrpg_users.user_date_accessed,
    mmrpg_users.user_date_modified,
    mmrpg_users.user_last_login,
    mmrpg_leaderboard.board_id,
    mmrpg_leaderboard.board_points,
    mmrpg_leaderboard.board_points_dr_light,
    mmrpg_leaderboard.board_points_dr_wily,
    mmrpg_leaderboard.board_points_dr_cossack,
    mmrpg_leaderboard.board_robots,
    mmrpg_leaderboard.board_robots_dr_light,
    mmrpg_leaderboard.board_robots_dr_wily,
    mmrpg_leaderboard.board_robots_dr_cossack,
    mmrpg_leaderboard.board_battles,
    mmrpg_leaderboard.board_battles_dr_light,
    mmrpg_leaderboard.board_battles_dr_wily,
    mmrpg_leaderboard.board_battles_dr_cossack,
    mmrpg_leaderboard.board_stars,
    mmrpg_leaderboard.board_stars_dr_light,
    mmrpg_leaderboard.board_stars_dr_wily,
    mmrpg_leaderboard.board_stars_dr_cossack,
    mmrpg_leaderboard.board_abilities,
    mmrpg_leaderboard.board_abilities_dr_light,
    mmrpg_leaderboard.board_abilities_dr_wily,
    mmrpg_leaderboard.board_abilities_dr_cossack,
    mmrpg_leaderboard.board_missions,
    mmrpg_leaderboard.board_missions_dr_light,
    mmrpg_leaderboard.board_missions_dr_wily,
    mmrpg_leaderboard.board_missions_dr_cossack,
    mmrpg_leaderboard.board_date_created,
    mmrpg_leaderboard.board_date_modified,
    mmrpg_saves.save_id,
    mmrpg_saves.save_counters,
    mmrpg_saves.save_values,
    mmrpg_saves.save_values_battle_complete,
    mmrpg_saves.save_values_battle_failure,
    mmrpg_saves.save_values_battle_rewards,
    mmrpg_saves.save_values_battle_stars,
    mmrpg_saves.save_values_robot_database,
    mmrpg_saves.save_flags,
    mmrpg_saves.save_settings,
    (SELECT COUNT(thread_id) FROM mmrpg_threads WHERE mmrpg_threads.user_id = mmrpg_users.user_id AND thread_published = 1 AND thread_target = 0) AS thread_count,
    (SELECT COUNT(post_id) FROM mmrpg_posts WHERE mmrpg_posts.user_id = mmrpg_users.user_id AND mmrpg_posts.post_deleted = 0 AND mmrpg_posts.category_id <> 0) AS post_count,
    (SELECT COUNT(battle_id) FROM mmrpg_battles WHERE (mmrpg_battles.this_user_id = mmrpg_users.user_id AND mmrpg_battles.this_player_result = \'victory\') OR (mmrpg_battles.target_user_id = mmrpg_users.user_id AND mmrpg_battles.target_player_result = \'victory\')) AS victory_count,
    (SELECT COUNT(battle_id) FROM mmrpg_battles WHERE (mmrpg_battles.this_user_id = mmrpg_users.user_id AND mmrpg_battles.this_player_result = \'defeat\') OR (mmrpg_battles.target_user_id = mmrpg_users.user_id AND mmrpg_battles.target_player_result = \'defeat\')) AS defeat_count,
    0 AS like_count
    FROM mmrpg_users
    LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_users.user_id
    LEFT JOIN mmrpg_roles ON mmrpg_users.role_id = mmrpg_roles.role_id
    LEFT JOIN mmrpg_saves ON mmrpg_saves.user_id = mmrpg_users.user_id
    WHERE mmrpg_users.user_name_clean LIKE \''.$this_current_sub.'\';';
  $this_playerinfo = $DB->get_array($temp_playerquery);
  // If the userinfo exists in the database, display it
  if (!empty($this_playerinfo)){
    // Collect this player's info from the database... all of it
    $this_playerinfo['save_counters'] = !empty($this_playerinfo['save_counters']) ? json_decode($this_playerinfo['save_counters'], true) : array();
    $this_playerinfo['save_values'] = !empty($this_playerinfo['save_values']) ? json_decode($this_playerinfo['save_values'], true) : array();
    //$this_playerinfo['save_values_battle_index'] = !empty($this_playerinfo['save_values_battle_index']) ? json_decode($this_playerinfo['save_values_battle_index'], true) : array();
    $this_playerinfo['save_values_battle_complete'] = !empty($this_playerinfo['save_values_battle_complete']) ? json_decode($this_playerinfo['save_values_battle_complete'], true) : array();
    $this_playerinfo['save_values_battle_failure'] = !empty($this_playerinfo['save_values_battle_failure']) ? json_decode($this_playerinfo['save_values_battle_failure'], true) : array();
    $this_playerinfo['save_values_battle_rewards'] = !empty($this_playerinfo['save_values_battle_rewards']) ? json_decode($this_playerinfo['save_values_battle_rewards'], true) : array();
    //$this_playerinfo['save_values_battle_settings'] = !empty($this_playerinfo['save_values_battle_settings']) ? json_decode($this_playerinfo['save_values_battle_settings'], true) : array();
    //$this_playerinfo['save_values_battle_items'] = !empty($this_playerinfo['save_values_battle_items']) ? json_decode($this_playerinfo['save_values_battle_items'], true) : array();
    $this_playerinfo['save_values_battle_stars'] = !empty($this_playerinfo['save_values_battle_stars']) ? json_decode($this_playerinfo['save_values_battle_stars'], true) : array();
    $this_playerinfo['save_values_robot_database'] = !empty($this_playerinfo['save_values_robot_database']) ? json_decode($this_playerinfo['save_values_robot_database'], true) : array();
    $this_playerinfo['save_flags'] = !empty($this_playerinfo['save_flags']) ? json_decode($this_playerinfo['save_flags'], true) : array();
    $this_playerinfo['save_settings'] = !empty($this_playerinfo['save_settings']) ? json_decode($this_playerinfo['save_settings'], true) : array();
    // Require the player page
    require_once('page.leaderboard_player.php');
  }
  // Otherwise, redirect back to the index page
  else {
    header('Location: '.MMRPG_CONFIG_ROOTURL.'leaderboard/');
    exit();
  }
}

?>