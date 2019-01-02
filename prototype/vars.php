<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags'];

/*
 * DEMO MISSION SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){

    // Collect the counters and flags for Dr. Light
    $unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
    $point_counter_light = $unlock_flag_light ? mmrpg_prototype_player_points('dr-light') : 0;
    $robot_counter_light = $unlock_flag_light ? mmrpg_prototype_robots_unlocked('dr-light') : 0;
    //$ability_counter_light = $unlock_flag_light ? mmrpg_prototype_abilities_unlocked('dr-light') : 0;
    //$star_counter_light = $unlock_flag_light ? mmrpg_prototype_stars_unlocked('dr-light') : 0;
    //$heart_counter_light = $unlock_flag_light ? mmrpg_prototype_hearts_unlocked('dr-light') : 0;
    $battle_complete_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light') : 0;
    $battle_failure_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light') : 0;
    //$battle_complete_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light', false) : 0;
    //$battle_failure_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light', false) : 0;
    $prototype_complete_flag_light = $unlock_flag_light ? mmrpg_prototype_complete('dr-light') : false;
    if ($unlock_flag_light && !$prototype_complete_flag_light && $battle_complete_counter_light >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){
        $_SESSION[$session_token]['flags']['prototype_events']['dr-light']['prototype_complete'] = $prototype_complete_flag_light = true;
    }

}
/*
 * NORMAL MISSION SELECT
 */
else {

    // Collect the counters and flags for Dr. Light
    $unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
    $point_counter_light = $unlock_flag_light ? mmrpg_prototype_player_points('dr-light') : 0;
    $robot_counter_light = $unlock_flag_light ? mmrpg_prototype_robots_unlocked('dr-light') : 0;
    if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-light', $this_data_condition)){
        $ability_counter_light = $unlock_flag_light ? mmrpg_prototype_abilities_unlocked('dr-light') : 0;
        $star_counter_light = $unlock_flag_light ? mmrpg_prototype_stars_unlocked('dr-light') : 0;
        //$heart_counter_light = $unlock_flag_light ? mmrpg_prototype_hearts_unlocked('dr-light') : 0;
    }
    $battle_complete_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light') : 0;
    $battle_failure_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light') : 0;
    //$battle_complete_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light', false) : 0;
    //$battle_failure_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light', false) : 0;
    $prototype_complete_flag_light = $unlock_flag_light ? mmrpg_prototype_complete('dr-light') : false;
    if ($unlock_flag_light && !$prototype_complete_flag_light && $battle_complete_counter_light >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){
        $_SESSION[$session_token]['flags']['prototype_events']['dr-light']['prototype_complete'] = $prototype_complete_flag_light = true;
    }

    // Collect the counters and flags for Dr. Wily
    $unlock_flag_wily = mmrpg_prototype_player_unlocked('dr-wily');
    $point_counter_wily = $unlock_flag_wily ? mmrpg_prototype_player_points('dr-wily') : 0;
    $robot_counter_wily = $unlock_flag_wily ? mmrpg_prototype_robots_unlocked('dr-wily') : 0;
    if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-wily', $this_data_condition)){
        $ability_counter_wily = $unlock_flag_wily ? mmrpg_prototype_abilities_unlocked('dr-wily') : 0;
        $star_counter_wily = $unlock_flag_wily ? mmrpg_prototype_stars_unlocked('dr-wily') : 0;
        //$heart_counter_wily = $unlock_flag_wily ? mmrpg_prototype_hearts_unlocked('dr-wily') : 0;
    }
    $battle_complete_counter_wily = $unlock_flag_wily ? mmrpg_prototype_battles_complete('dr-wily') : 0;
    $battle_failure_counter_wily = $unlock_flag_wily ? mmrpg_prototype_battles_failure('dr-wily') : 0;
    //$battle_complete_counter_wily_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-wily', false) : 0;
    //$battle_failure_counter_wily_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-wily', false) : 0;
    $prototype_complete_flag_wily = $unlock_flag_wily ? mmrpg_prototype_complete('dr-wily') : false;
    if ($unlock_flag_wily && !$prototype_complete_flag_wily && $battle_complete_counter_wily >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){
        $_SESSION[$session_token]['flags']['prototype_events']['dr-wily']['prototype_complete'] = $prototype_complete_flag_wily = true;
    }

    // Collect the counters and flags for Dr. Cossack
    $unlock_flag_cossack = mmrpg_prototype_player_unlocked('dr-cossack');
    $point_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_player_points('dr-cossack') : 0;
    $robot_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_robots_unlocked('dr-cossack') : 0;
    if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-cossack', $this_data_condition)){
        $ability_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_abilities_unlocked('dr-cossack') : 0;
        $star_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_stars_unlocked('dr-cossack') : 0;
        //$heart_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_hearts_unlocked('dr-cossack') : 0;
    }
    $battle_complete_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_complete('dr-cossack') : 0;
    $battle_failure_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_failure('dr-cossack') : 0;
    //$battle_complete_counter_cossack_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-cossack', false) : 0;
    //$battle_failure_counter_cossack_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-cossack', false) : 0;
    $prototype_complete_flag_cossack = $unlock_flag_cossack ? mmrpg_prototype_complete('dr-cossack') : false;
    if ($unlock_flag_cossack && !$prototype_complete_flag_cossack && $battle_complete_counter_cossack >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){
        $_SESSION[$session_token]['flags']['prototype_events']['dr-cossack']['prototype_complete'] = $prototype_complete_flag_cossack = true;
    }

    // Collect the global star count for everyone
    $battle_star_counter = mmrpg_prototype_stars_unlocked();

    // Define an inline function for calculating campaign progress for a given player
    function temp_calculate_player_progress(&$chapters_unlocked, &$battle_complete_counter, &$prototype_complete_flag){
        global $battle_star_counter;

        // -- PHASE ONE -- //

        // Intro
        $chapters_unlocked['0'] = true;

        // Masters
        $chapters_unlocked['1'] = $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER1_MISSIONCOUNT ? true : false;

        // Rivals
        $chapters_unlocked['2'] = $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER2_MISSIONCOUNT ? true : false;

        // -- PHASE TWO -- //

        if ($prototype_complete_flag
            || mmrpg_prototype_item_unlocked('cossack-program')
            || $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){

            // Fusions
            $chapters_unlocked['3'] = $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER3_MISSIONCOUNT ? true : false;

            // Finals
            $chapters_unlocked['4a'] = $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT ? true : false;
            $chapters_unlocked['4b'] = $battle_complete_counter >= (MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT + 1) ? true : false;
            $chapters_unlocked['4c'] = $battle_complete_counter >= (MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT + 2) ? true : false;

        } else {

            // Fusions
            $chapters_unlocked['3'] = false;

            // Finals
            $chapters_unlocked['4a'] = false;
            $chapters_unlocked['4b'] = false;
            $chapters_unlocked['4c'] = false;

        }

        // -- BONUS PHASE -- //

        if ($prototype_complete_flag
            || $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){

            // Stars
            $chapters_unlocked['7'] = true;

            // Player
            $chapters_unlocked['6'] = true;

            // Bonus
            $chapters_unlocked['5'] = true;

        } else {

            // Stars
            $chapters_unlocked['7'] = mmrpg_prototype_item_unlocked('cossack-program') ? true : false;

            // Player
            $chapters_unlocked['6'] = false;

            // Bonus
            $chapters_unlocked['5'] = false;

        }

    }

    // Define which chapters should be unlocked for Dr. Light based on missions complete
    $chapters_unlocked_light = array();
    temp_calculate_player_progress($chapters_unlocked_light, $battle_complete_counter_light, $prototype_complete_flag_light);

    // Define which chapters should be unlocked for Dr. Wily based on missions complete
    $chapters_unlocked_wily = array();
    temp_calculate_player_progress($chapters_unlocked_wily, $battle_complete_counter_wily, $prototype_complete_flag_wily);

    // Define which chapters should be unlocked for Dr. Cossack based on missions complete
    $chapters_unlocked_cossack = array();
    temp_calculate_player_progress($chapters_unlocked_cossack, $battle_complete_counter_cossack, $prototype_complete_flag_cossack);

    // Define an index to hold all the chapter unlocks for later reference
    $chapters_unlocked_index = array();
    $chapters_unlocked_index['dr-light'] = $chapters_unlocked_light;
    $chapters_unlocked_index['dr-wily'] = $chapters_unlocked_wily;
    $chapters_unlocked_index['dr-cossack'] = $chapters_unlocked_cossack;

    // If the player has manually unlocked any Dr. Light chapters via password, update their flags
    if (!$chapters_unlocked_light['6']){
        if ($battle_complete_counter_light > 0
            && (!empty($temp_game_flags['drlight_password_playerbattlebonus20130324'])
                || !empty($temp_game_flags['drlight_password_chaptergetplayerbattles']))){
                $chapters_unlocked_light['6'] = true;
            }
    }
    // If the player has manually unlocked any Dr. Wily chapters via password, update their flags
    if (!$chapters_unlocked_wily['6']){
        if ($battle_complete_counter_wily > 0
            && (!empty($temp_game_flags['drwily_password_playerbattlebonus20130324'])
                || !empty($temp_game_flags['drwily_password_chaptergetplayerbattles']))){
                $chapters_unlocked_wily['6'] = true;
            }
    }
    // If the player has manually unlocked any Dr. Cossack chapters via password, update their flags
    if (!$chapters_unlocked_cossack['6']){
        if ($battle_complete_counter_cossack > 0
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