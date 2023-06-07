<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags'];

/*
 * DEMO MISSION SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){

    // Do nothing, this shouldn't be here anymore
    // ...

}
/*
 * NORMAL MISSION SELECT
 */
else {

    // Collect the unlock flags for the various doctors
    $unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
    $unlock_flag_wily = mmrpg_prototype_player_unlocked('dr-wily');
    $unlock_flag_cossack = mmrpg_prototype_player_unlocked('dr-cossack');

    // Collect very basic counters for the Dr. Light, chapter progress comes next
    $battle_complete_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light') : 0;
    $battle_failure_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light') : 0;
    $point_counter_light = $unlock_flag_light ? mmrpg_prototype_player_points('dr-light') : 0;
    $robot_counter_light = $unlock_flag_light ? mmrpg_prototype_robots_unlocked('dr-light') : 0;

    // Collect very basic counters for the Dr. Wily, chapter progress comes next
    $battle_complete_counter_wily = $unlock_flag_wily ? mmrpg_prototype_battles_complete('dr-wily') : 0;
    $battle_failure_counter_wily = $unlock_flag_wily ? mmrpg_prototype_battles_failure('dr-wily') : 0;
    $point_counter_wily = $unlock_flag_wily ? mmrpg_prototype_player_points('dr-wily') : 0;
    $robot_counter_wily = $unlock_flag_wily ? mmrpg_prototype_robots_unlocked('dr-wily') : 0;

    // Collect very basic counters for the Dr. Cossack, chapter progress comes next
    $battle_complete_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_complete('dr-cossack') : 0;
    $battle_failure_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_failure('dr-cossack') : 0;
    $point_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_player_points('dr-cossack') : 0;
    $robot_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_robots_unlocked('dr-cossack') : 0;

    // Define which chapters should be unlocked for the three doctors based on missions complete
    $chapters_unlocked_light = rpg_prototype::get_player_chapters_unlocked('dr-light');
    $chapters_unlocked_wily = rpg_prototype::get_player_chapters_unlocked('dr-wily');
    $chapters_unlocked_cossack = rpg_prototype::get_player_chapters_unlocked('dr-cossack');

    // Count how many doctors have actually completed the prototype
    $prototype_complete_count = 0;
    $prototype_complete_flag_light = $unlock_flag_light ? mmrpg_prototype_complete('dr-light') : false;
    $prototype_complete_flag_wily = $chapters_unlocked_wily ? mmrpg_prototype_complete('dr-wily') : false;
    $prototype_complete_flag_cossack = $chapters_unlocked_cossack ? mmrpg_prototype_complete('dr-cossack') : false;
    if ($prototype_complete_flag_light){ $prototype_complete_count += 1; }
    if ($prototype_complete_flag_wily){ $prototype_complete_count += 1; }
    if ($prototype_complete_flag_cossack){ $prototype_complete_count += 1; }

    // Define an index to hold all the chapter unlocks for later reference
    $chapters_unlocked_index = array();
    $chapters_unlocked_index['dr-light'] = $chapters_unlocked_light;
    $chapters_unlocked_index['dr-wily'] = $chapters_unlocked_wily;
    $chapters_unlocked_index['dr-cossack'] = $chapters_unlocked_cossack;

    // If the player has manually unlocked any Dr. Light chapters via password, update their flags
    if (!$chapters_unlocked_light['6']){
        if ($chapters_unlocked_light['2']
            && (!empty($temp_game_flags['drlight_password_playerbattlebonus20130324'])
                || !empty($temp_game_flags['drlight_password_chaptergetplayerbattles']))){
                $chapters_unlocked_light['6'] = true;
            }
    }
    // If the player has manually unlocked any Dr. Wily chapters via password, update their flags
    if (!$chapters_unlocked_wily['6']){
        if ($chapters_unlocked_wily['2']
            && (!empty($temp_game_flags['drwily_password_playerbattlebonus20130324'])
                || !empty($temp_game_flags['drwily_password_chaptergetplayerbattles']))){
                $chapters_unlocked_wily['6'] = true;
            }
    }
    // If the player has manually unlocked any Dr. Cossack chapters via password, update their flags
    if (!$chapters_unlocked_cossack['6']){
        if ($chapters_unlocked_cossack['2']
            && (!empty($temp_game_flags['drcossack_password_playerbattlebonus20130324'])
                || !empty($temp_game_flags['drcossack_password_chaptergetplayerbattles']))){
                $chapters_unlocked_cossack['6'] = true;
            }
    }

}

// Count the number of players unlocked
$unlock_count_players = 0;
if (!empty($unlock_flag_light)){ $unlock_count_players++; }
if (!empty($unlock_flag_wily)){ $unlock_count_players++; }
if (!empty($unlock_flag_cossack)){ $unlock_count_players++; }

?>