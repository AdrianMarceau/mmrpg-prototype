<?
/*
 * INDEX PAGE : LEADERBOARD
 */

// If the current sub was not provided OR if a specific page was requested
if (empty($this_current_sub) || preg_match('/^([0-9]+)$/i', $this_current_sub)){
  // Require the index page
  require_once('leaderboard_index.php');
}
// If a player has been provided in the URL as a sub
elseif (!empty($this_current_sub) && preg_match('/^([-_a-z0-9]+)$/i', $this_current_sub)){

  // Attempt to collect data for this player from the database
  $temp_playerquery = "SELECT
    users.user_id,
    users.role_id,
    uroles.role_token,
    uroles.role_name,
    uroles.role_icon,
    users.user_name,
    users.user_name_clean,
    users.user_name_public,
    users.user_gender,
    users.user_profile_text,
    users.user_website_address,
    users.user_image_path,
    users.user_background_path,
    users.user_colour_token,
    users.user_email_address,
    users.user_date_created,
    users.user_date_accessed,
    users.user_date_modified,
    users.user_last_login,
    lboard.board_id,
    lboard.board_points,
    lboard.board_points_dr_light,
    lboard.board_points_dr_wily,
    lboard.board_points_dr_cossack,
    lboard.board_points_legacy,
    lboard.board_points_dr_light_legacy,
    lboard.board_points_dr_wily_legacy,
    lboard.board_points_dr_cossack_legacy,
    lboard.board_robots,
    lboard.board_robots_dr_light,
    lboard.board_robots_dr_wily,
    lboard.board_robots_dr_cossack,
    lboard.board_battles,
    lboard.board_battles_dr_light,
    lboard.board_battles_dr_wily,
    lboard.board_battles_dr_cossack,
    lboard.board_stars,
    lboard.board_stars_dr_light,
    lboard.board_stars_dr_wily,
    lboard.board_stars_dr_cossack,
    lboard.board_abilities,
    lboard.board_abilities_dr_light,
    lboard.board_abilities_dr_wily,
    lboard.board_abilities_dr_cossack,
    lboard.board_missions,
    lboard.board_missions_dr_light,
    lboard.board_missions_dr_wily,
    lboard.board_missions_dr_cossack,
    lboard.board_date_created,
    lboard.board_date_modified,
    saves.save_id,
    saves.save_flags,
    saves.save_counters,
    saves.save_values,
    saves.save_settings,
    saves2.save_values_battle_complete,
    saves2.save_values_battle_failure,
    saves2.save_values_battle_rewards,
    saves2.save_values_battle_stars,
    saves2.save_values_battle_items,
    saves2.save_values_robot_database,
    (SELECT COUNT(thread_id) FROM mmrpg_threads AS threads WHERE threads.user_id = users.user_id AND threads.thread_published = 1) AS thread_count,
    (SELECT COUNT(post_id) FROM mmrpg_posts AS posts WHERE posts.user_id = users.user_id AND posts.post_deleted = 0) AS post_count,
    (SELECT COUNT(battle_id) FROM mmrpg_battles AS battles WHERE (battles.this_user_id = users.user_id AND battles.this_player_result = 'victory') OR (battles.target_user_id = users.user_id AND battles.target_player_result = 'victory')) AS victory_count,
    (SELECT COUNT(battle_id) FROM mmrpg_battles AS battles2 WHERE (battles2.this_user_id = users.user_id AND battles2.this_player_result = 'defeat') OR (battles2.target_user_id = users.user_id AND battles2.target_player_result = 'defeat')) AS defeat_count,
    0 AS like_count
    FROM mmrpg_users AS users
    LEFT JOIN mmrpg_leaderboard AS lboard ON lboard.user_id = users.user_id
    LEFT JOIN mmrpg_roles AS uroles ON users.role_id = uroles.role_id
    LEFT JOIN mmrpg_saves AS saves ON saves.user_id = users.user_id
    LEFT JOIN mmrpg_saves_legacy AS saves2 ON saves2.user_id = users.user_id
    WHERE
    users.user_name_clean LIKE '{$this_current_sub}'
    ;";
  $this_playerinfo = $db->get_array($temp_playerquery);

  // If the userinfo exists in the database, display it
  if (!empty($this_playerinfo)){

    // Collect this player's info from the database... all of it
    $this_playerinfo['save_counters'] = !empty($this_playerinfo['save_counters']) ? json_decode($this_playerinfo['save_counters'], true) : array();
    $this_playerinfo['save_values'] = !empty($this_playerinfo['save_values']) ? json_decode($this_playerinfo['save_values'], true) : array();
    $this_playerinfo['save_values_battle_complete'] = !empty($this_playerinfo['save_values_battle_complete']) ? json_decode($this_playerinfo['save_values_battle_complete'], true) : array();
    $this_playerinfo['save_values_battle_failure'] = !empty($this_playerinfo['save_values_battle_failure']) ? json_decode($this_playerinfo['save_values_battle_failure'], true) : array();
    $this_playerinfo['save_values_battle_rewards'] = !empty($this_playerinfo['save_values_battle_rewards']) ? json_decode($this_playerinfo['save_values_battle_rewards'], true) : array();
    $this_playerinfo['save_values_battle_items'] = !empty($this_playerinfo['save_values_battle_items']) ? json_decode($this_playerinfo['save_values_battle_items'], true) : array();
    $this_playerinfo['save_values_battle_stars'] = !empty($this_playerinfo['save_values_battle_stars']) ? json_decode($this_playerinfo['save_values_battle_stars'], true) : array();
    $this_playerinfo['save_values_robot_database'] = !empty($this_playerinfo['save_values_robot_database']) ? json_decode($this_playerinfo['save_values_robot_database'], true) : array();
    $this_playerinfo['save_flags'] = !empty($this_playerinfo['save_flags']) ? json_decode($this_playerinfo['save_flags'], true) : array();
    $this_playerinfo['save_settings'] = !empty($this_playerinfo['save_settings']) ? json_decode($this_playerinfo['save_settings'], true) : array();

    // Require the player page
    require_once('leaderboard_player.php');

  }
  // Otherwise, redirect back to the index page
  else {

    header('Location: '.MMRPG_CONFIG_ROOTURL.'leaderboard/');
    exit();

  }

}
?>