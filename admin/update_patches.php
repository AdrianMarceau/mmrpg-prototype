<?

// -- STAT MAX LIMIT UPDATE -- //

// Define a patch function for applying the max robot stats update
$token = 'stat_max_limit_update';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Balancing Max Limits for Robot Stats';
$update_patch_details[$token] = "Robot stats are no longer capped at a max of 9999 and are instead based on each robot's base values.";
$update_patch_details[$token] .= "\nEach stat can now only be boosted to maximum of ".(MMRPG_SETTINGS_STATS_BONUS_MAX + 1)."x its base value at level 100.";
$update_patch_details[$token] .= "\n".'Any overflow of stats that are now considered too high will be converted to zenny for the player.';
function mmrpg_patch_stat_max_limit_update($_GAME){ /* removed */ }


// -- BATTLE POINT REBOOT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'battle_point_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Battle Point Reboot of 2016';
$update_patch_details[$token] = "Battle points are no longer some arbituary number you can grind for all eternity.";
$update_patch_details[$token] .= "\nFrom now on, battle points will be equal to the total sum of all your *best* mission scores combined.";
$update_patch_details[$token] .= "\nWhile replaying missions for better scores is abolutely encouraged, it is no longer necessary to return over and over simply to grind points.";
function mmrpg_patch_battle_point_reboot_2k16($_GAME){ /* removed */ }


// -- PLAYER ABILITY MERGE 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'player_ability_merge_2k16';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Player Ability Merge of 2016';
$update_patch_details[$token] = "Abilities unlocked by any player can now be used by all players. ";
$update_patch_details[$token] .= "\nIt is no longer necessary to trade robots back and forth to customize ";
$update_patch_details[$token] .= "\nthe perfect moveset, and I hope everyone will enjoy this new change. :) ";
function mmrpg_patch_player_ability_merge_2k16($_GAME){ /* removed */ }


// -- PLAYER ITEM / ABILITY SPLIT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'ability_item_split_2k16';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Ability / Item Split of 2016';
$update_patch_details[$token] = "Abilities and items were technically the same thing in the coding of the game \n";
$update_patch_details[$token] .= "and it required a lot of extra coding to get them to work.  In an effort to \n";
$update_patch_details[$token] .= "optimize the game items are being split into their own separate category of \n";
$update_patch_details[$token] .= "objects. This patch simply re-organizes some of that data in your game file. ";
function mmrpg_patch_ability_item_split_2k16($_GAME){ /* removed */ }


// -- STAR FORCE REBOOT 2k16 -- //

// Define a patch function for applying the next update
$token = 'star_force_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Star Force Reboot of 2016';
$update_patch_details[$token] = "Field and Fusion stars now boost battle points instead of boosting elemental \n";
$update_patch_details[$token] .= "damage and recovery power in battle.  As a result, battle points for all players \n";
$update_patch_details[$token] .= "must be recalculated. ";
function mmrpg_patch_star_force_reboot_2k16($_GAME){ /* removed */ }


// -- DB USER OBJECTS 2k17 -- //

// Define a patch function for applying the next update
$token = 'db_user_objects_2k17';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Database User Objects 2k17';
$update_patch_details[$token] = "The previous storage format for unlocked players, robots, and abilities \n";
$update_patch_details[$token] .= "was ineffecient and needed to be optimized.  This patch should have no  \n";
$update_patch_details[$token] .= "effect on user experience or gameplay and is only for internal purposes. ";
function mmrpg_patch_db_user_objects_2k17($_GAME){

    // Pull in global variables
    global $db;

    // Save the current game session to legacy database fields
    if (defined('MMRPG_UPDATE_GAME_RESET')){
        legacy_rpg_game::session_to_fields($_GAME, $_GAME['user_id']);
    }

    // Collect legacy session vars into current game array
    $legacy_session_vars = legacy_rpg_game::get_session_vars($_GAME['user_id']);
    $_GAME['flags'] = $legacy_session_vars['flags'];
    $_GAME['counters'] = $legacy_session_vars['counters'];
    $_GAME['values'] = $legacy_session_vars['values'];

    // Manually append MM1, MM2, and MM4 fields to unlock list they should have been unlocked
    legacy_rpg_game::fix_battle_fields_array($_GAME);

    // Manually fix the player rewards array by making sure its in the current format
    legacy_rpg_game::fix_player_rewards_array($_GAME);

    // Manually fix the player omega array by making sure it's in the current format
    legacy_rpg_game::fix_player_omega_array($_GAME);

    echo("Converting session objects into database objects for user ID {$_GAME['user_id']}... \n\n");

    // Trigger the session to database function to do all the work
    legacy_rpg_game::session_to_database($_GAME, true);

    // Print the final message of success
    echo("...done! Thank you for your time. :) \n\n");

    // Return the updated game array
    return $_GAME;

    /*

    // QUERIES TO RUN AFTER THE UPDATE!!!!

    -- FIND ALL USER ROBOTS WITH INCORRECT OR MISSING ORIGINAL PLAYERS
    SELECT
        urobots.user_id,
        irobots.robot_token,
        irobots.robot_game,
        urobots.robot_player,
        urobots.robot_player_original,
        iplayers.player_token AS correct_player_original
        FROM mmrpg_users_robots AS urobots
        LEFT JOIN mmrpg_index_robots AS irobots ON irobots.robot_token = urobots.robot_token
        LEFT JOIN mmrpg_index_players AS iplayers ON (
            iplayers.player_game = irobots.robot_game
            OR (iplayers.player_token = 'dr-light' AND irobots.robot_token = 'mega-man')
            OR (iplayers.player_token = 'dr-wily' AND irobots.robot_token = 'bass')
            OR (iplayers.player_token = 'dr-cossack' AND irobots.robot_token = 'proto-man')
            )
        WHERE
        irobots.robot_class = 'master'
        AND irobots.robot_game IN ('MM00', 'MM01', 'MM02', 'MM04')
        AND (
            urobots.robot_player_original = ''
            OR (
                urobots.robot_player_original <> iplayers.player_token
                AND irobots.robot_token IN ('mega-man', 'bass', 'proto-man', 'roll', 'disco', 'rhythm')
                )
            )
        ORDER BY
        urobots.user_id ASC,
        iplayers.player_order ASC,
        irobots.robot_order ASC
        ;


    -- FIX ALL USER ROBOTS WITH INCORRECT OR MISSING ORIGINAL PLAYERS
    UPDATE
        mmrpg_users_robots AS urobots
        LEFT JOIN mmrpg_index_robots AS irobots ON irobots.robot_token = urobots.robot_token
        LEFT JOIN mmrpg_index_players AS iplayers ON (
            iplayers.player_game = irobots.robot_game
            OR (iplayers.player_token = 'dr-light' AND irobots.robot_token = 'mega-man')
            OR (iplayers.player_token = 'dr-wily' AND irobots.robot_token = 'bass')
            OR (iplayers.player_token = 'dr-cossack' AND irobots.robot_token = 'proto-man')
            )
        SET urobots.robot_player_original = iplayers.player_token
        WHERE
        iplayers.player_token IS NOT NULL
        AND (
            urobots.robot_player_original = ''
            OR urobots.robot_player_original IS NULL
            OR (
                urobots.robot_player_original <> iplayers.player_token
                AND irobots.robot_token IN ('mega-man', 'bass', 'proto-man', 'roll', 'disco', 'rhythm')
                )
            )
        ;

     */

}


/*

// -- PATCH FUNCTION TEMPLATE -- //

// Define a patch function for applying the max robot stats update
$token = 'template';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Template Patch Name';
$update_patch_details[$token] = "Template patch details.  Use this area to describe the purpose and procedure.';
function mmrpg_patch_template($_GAME){

    // Pull in global variables
    global $db;


    // Return the updated game array
    return $_GAME;

}

*/



/*
 * --
 * LEGACY PATCH FUNCTION HELPERS
 * --
 */

class legacy_rpg_game {

    // Define a function for collecting the current GAME session flags
    public static function get_session_vars($this_userid = 0){
        global $db;

        // Collect game session variables from legacy db fields
        $session_vars = $db->get_array("SELECT
            saves.save_flags,
            saves.save_values,
            saves.save_counters,
            saves2.save_values_battle_complete,
            saves2.save_values_battle_failure,
            saves2.save_values_battle_rewards,
            saves2.save_values_battle_settings,
            saves2.save_values_battle_items,
            saves2.save_values_battle_abilities,
            saves2.save_values_battle_stars,
            saves2.save_values_robot_database,
            saves2.save_values_robot_alts,
            saves.save_settings
            FROM mmrpg_saves AS saves
            LEFT JOIN mmrpg_saves_legacy AS saves2 ON saves2.user_id = saves.user_id
            WHERE
            saves.user_id = {$this_userid}
            ;");

        //echo('$session_vars = '.print_r($session_vars, true).PHP_EOL);

        // Extract all the game flags/counters/values from the session
        $session_flags = !empty($session_vars['save_flags']) ? json_decode($session_vars['save_flags'], true) : array();
        $session_counters = !empty($session_vars['save_counters']) ? json_decode($session_vars['save_counters'], true) : array();
        $session_values = !empty($session_vars['save_values']) ? json_decode($session_vars['save_values'], true) : array();
        $session_values['battle_settings'] = !empty($session_vars['save_values_battle_settings']) ? json_decode($session_vars['save_values_battle_settings'], true) : array();
        $session_values['battle_rewards'] = !empty($session_vars['save_values_battle_rewards']) ? json_decode($session_vars['save_values_battle_rewards'], true) : array();
        $session_values['battle_abilities'] = !empty($session_vars['save_values_battle_abilities']) ? json_decode($session_vars['save_values_battle_abilities'], true) : array();
        $session_values['battle_items'] = !empty($session_vars['save_values_battle_items']) ? json_decode($session_vars['save_values_battle_items'], true) : array();
        $session_values['battle_stars'] = !empty($session_vars['save_values_battle_stars']) ? json_decode($session_vars['save_values_battle_stars'], true) : array();
        $session_values['robot_alts'] = !empty($session_vars['save_values_robot_alts']) ? json_decode($session_vars['save_values_robot_alts'], true) : array();
        $session_values['robot_database'] = !empty($session_vars['save_values_robot_database']) ? json_decode($session_vars['save_values_robot_database'], true) : array();
        $session_values['battle_fields'] = !empty($session_values['battle_fields']) ? $session_values['battle_fields'] : array();
        $session_settings = !empty($session_vars['save_settings']) ? json_decode($session_vars['save_settings'], true) : array();

        // Return parsed values in a single array
        return array(
            'flags' => $session_flags,
            'counters' => $session_counters,
            'values' => $session_values,
            'settings' => $session_settings
            );

    }

    // Define a function for retroactively "fixing" the player field unlocks
    public static function fix_battle_fields_array(&$_GAME){
        global $db;

        // Manually append MM1, MM2, and MM4 fields to unlock list they should have been unlocked
        if (!empty($_GAME['values']['battle_rewards'])){

            // Collect unlocked player tokens and their index info
            $unlocked_player_tokens = rpg_game::parse_player_tokens($_GAME['values']['battle_settings'], $_GAME['values']['battle_rewards']);
            $unlocked_player_index = rpg_player::get_index_custom($unlocked_player_tokens);

            // Create an array to hold the field backlog
            $prev_battle_fields = array();

            // Loop through unlocked players and collect fields to unlock for each
            foreach ($unlocked_player_index AS $player_token => $player_info){
                if (!empty($player_info['player_game'])){
                    $field_tokens = $db->get_array_list("SELECT field_token FROM mmrpg_index_fields WHERE field_game = '{$player_info['player_game']}' AND field_flag_complete = 1 ORDER BY field_order ASC;", 'field_token');
                    $field_tokens = !empty($field_tokens) ? array_keys($field_tokens) : array();
                    $prev_battle_fields = array_merge($prev_battle_fields, $field_tokens);
                }

            }

            // Merge the backlog and the current fields together into one
            if (!isset($_GAME['values']['battle_fields'])){ $_GAME['values']['battle_fields'] = array(); }
            $_GAME['values']['battle_fields'] = array_merge($prev_battle_fields, $_GAME['values']['battle_fields']);

        }

    }

    // Define a function for "fixing" the player rewards array formatting
    public static function fix_player_rewards_array(&$_GAME){

        // Collect unique player tokens from the settings and/or rewards
        $player_tokens = rpg_game::parse_player_tokens($_GAME['values']['battle_settings'], $_GAME['values']['battle_rewards']);
        //echo('$player_tokens = '.print_r($player_tokens, true).PHP_EOL);

        // Fix issues with legacy player rewards array
        if (!empty($player_tokens)){
            foreach ($player_tokens AS $player_key => $player_token){
                if (empty($player_token)){ continue; }
                if (!empty($_GAME['values']['battle_rewards'])){
                    foreach ($_GAME['values']['battle_rewards'] AS $player_token => $player_info){
                        // If new player robots array is empty but old is not, copy over
                        if (empty($player_info['player_robots']) && !empty($player_info['player_rewards']['robots'])){
                            // Loop through and collect robot data from the legacy rewards array
                            foreach ($player_info['player_rewards']['robots'] AS $key => $robot){
                                if (empty($robot['token'])){ continue; }
                                $robot_info = array();
                                $robot_info['robot_token'] = $robot['token'];
                                $robot_info['robot_level'] = !empty($robot['level']) ? $robot['level'] : 1;
                                $robot_info['robot_experience'] = !empty($robot['points']) ? $robot['points'] : 0;
                                $player_info['player_robots'][$robot['token']] = $robot_info;
                            }
                            // Kill the legacy rewards array to prevent confusion
                            unset($player_info['player_rewards']);
                        }
                        // If player robots are NOT empty, update in the parent array
                        if (!empty($player_info['player_robots'])){
                            $_GAME['values']['battle_rewards'][$player_token] = $player_info;
                        }
                        // Otherwise if no robots found, kill this player's data in both arrays
                        else {
                            unset($_GAME['values']['battle_settings'][$player_token]);
                            unset($_GAME['values']['battle_rewards'][$player_token]);
                        }
                    }
                }
            }
        }

    }

    // Define a function for "fixing" the player omega array formatting
    public static function fix_player_omega_array(&$_GAME){
        global $db;

        // Collect a backup index for missing omega field, robot, or type details
        $omega_index = $db->get_array_list("SELECT
            `fields`.field_token AS `field`,
            `fields`.field_master AS `robot`,
            `fields`.field_type AS `type`
            FROM mmrpg_index_fields AS `fields`
            WHERE
            `fields`.field_master <> ''
            AND `fields`.field_type <> ''
            ;", 'field');

        $omega_field_names = array();
        if (!empty($_GAME['values']['dr-light_target-robot-omega_prototype'])){ $omega_field_names[] = 'dr-light_target-robot-omega_prototype'; }
        if (!empty($_GAME['values']['dr-wily_target-robot-omega_prototype'])){ $omega_field_names[] = 'dr-wily_target-robot-omega_prototype'; }
        if (!empty($_GAME['values']['dr-cossack_target-robot-omega_prototype'])){ $omega_field_names[] = 'dr-cossack_target-robot-omega_prototype'; }
        foreach ($omega_field_names AS $omega_field_name){
            $this_omega_list = $_GAME['values'][$omega_field_name];

            // Ensure the list is in the right format, extracting if nested too deep
            if (!empty($this_omega_list)
                && count($this_omega_list) === 8
                && isset($this_omega_list[0]['field'])){
                // we're good!
            } elseif (!empty($this_omega_list)){
                $this_omega_list = array_pop($this_omega_list);
                if (!empty($this_omega_list)
                    && count($this_omega_list) === 8
                    && isset($this_omega_list[0]['field'])){
                    // we're good!
                } else {
                    unset($_GAME['values'][$omega_field_name]);
                    continue;
                }
            } else {
                unset($_GAME['values'][$omega_field_name]);
                continue;
            }

            // Loop through each omega factor and ensure all fields are set
            if (!empty($this_omega_list)){
                foreach ($this_omega_list AS $key => $factor){
                    if (!isset($factor['field'])){
                        unset($_GAME['values'][$factor_field_name]);
                        continue 2;
                    } else {
                        $index = $omega_index[$factor['field']];
                        if (!isset($factor['robot'])){ $factor['robot'] = $index['robot']; }
                        if (!isset($factor['type'])){ $factor['type'] = $index['type']; }
                        $this_omega_list[$key] = $factor;
                    }
                }
            }

            // Update the parent game values with the omega list changes
            $_GAME['values'][$omega_field_name] = $this_omega_list;

        }

    }

    // Define a function for saving current game session to legacy database fields
    public static function session_to_fields($_GAME, $this_userid){
        global $db;

        //echo('<pre>session_to_fields()</pre>'.PHP_EOL);

        // Collect global index variables
        $mmrpg_index_players = rpg_player::get_index();

        // Update the last saved value
        $_GAME['values']['last_save'] = time();

        // Collect the save info
        $_USER = $_GAME['USER'];
        $_USER['userid'] = $this_userid;

        // Collect the save info
        $this_cache_date = !empty($_GAME['CACHE_DATE']) ? $_GAME['CACHE_DATE'] : MMRPG_CONFIG_CACHE_DATE;
        $this_counters = !empty($_GAME['counters']) ? $_GAME['counters'] : array();
        $this_values = !empty($_GAME['values']) ? $_GAME['values'] : array();
        $this_flags = !empty($_GAME['flags']) ? $_GAME['flags'] : array();
        $this_settings = !empty($_GAME['settings']) ? $_GAME['settings'] : array();

        // Index the main user array
        if (true){

            // Define the user database update array and populate
            $this_user_array = array();
            $this_user_array['user_name'] = $_USER['username'];
            $this_user_array['user_name_clean'] = $_USER['username_clean'];
            $this_user_array['user_name_public'] = !empty($_USER['displayname']) ? $_USER['displayname'] : '';
            $this_user_array['user_profile_text'] = !empty($_USER['profiletext']) ? $_USER['profiletext'] : '';
            $this_user_array['user_credit_text'] = !empty($_USER['creditstext']) ? $_USER['creditstext'] : '';
            $this_user_array['user_credit_line'] = !empty($_USER['creditsline']) ? $_USER['creditsline'] : '';
            $this_user_array['user_image_path'] = !empty($_USER['imagepath']) ? $_USER['imagepath'] : '';
            $this_user_array['user_background_path'] = !empty($_USER['backgroundpath']) ? $_USER['backgroundpath'] : '';
            $this_user_array['user_colour_token'] = !empty($_USER['colourtoken']) ? $_USER['colourtoken'] : '';
            $this_user_array['user_gender'] = !empty($_USER['gender']) ? $_USER['gender'] : '';
            $this_user_array['user_omega'] = !empty($_USER['omega']) ? $_USER['omega'] : md5(MMRPG_SETTINGS_OMEGA_SEED.$_USER['username_clean']);
            $this_user_array['user_email_address'] = !empty($_USER['emailaddress']) ? $_USER['emailaddress'] : '';
            $this_user_array['user_website_address'] = !empty($_USER['websiteaddress']) ? $_USER['websiteaddress'] : '';
            $this_user_array['user_date_modified'] = time();
            $this_user_array['user_date_accessed'] = time();
            $this_user_array['user_date_birth'] = !empty($_USER['dateofbirth']) ? $_USER['dateofbirth'] : 0;
            $this_user_array['user_flag_approved'] = !empty($_USER['approved']) ? 1 : 0;

            // Update this user's info in the database
            //echo('<hr /><pre>FINAL DB USER UPDATE (user_id = '.$_USER['userid'].')</pre>');
            //echo('<pre>$this_user_array = '.print_r($this_user_array, true).'</pre>');
            $db->update('mmrpg_users', $this_user_array, 'user_id = '.$_USER['userid']);

        }

        // Index the main board array
        if (true){

            // Define the board database update array and populate
            $this_board_array = array();
            $this_board_array['board_points'] = !empty($this_counters['battle_points']) ? $this_counters['battle_points'] : 0;
            $this_board_array['board_robots'] = array();
            $this_board_array['board_battles'] = array();
            $this_board_array['board_stars'] = 0;
            $this_board_array['board_stars_dr_light'] = 0;
            $this_board_array['board_stars_dr_wily'] = 0;
            $this_board_array['board_stars_dr_cossack'] = 0;
            $this_board_array['board_abilities'] = 0;
            $this_board_array['board_abilities_dr_light'] = 0;
            $this_board_array['board_abilities_dr_wily'] = 0;
            $this_board_array['board_abilities_dr_cossack'] = 0;
            $this_board_array['board_missions'] = 0;
            $this_board_array['board_missions_dr_light'] = 0;
            $this_board_array['board_missions_dr_wily'] = 0;
            $this_board_array['board_missions_dr_cossack'] = 0;
            $this_board_array['board_awards'] = !empty($this_values['prototype_awards']) ? array_keys($this_values['prototype_awards']) : '';

            $temp_board_ability_tokens = array();
            if (!empty($this_values['battle_rewards'])){
                if (empty($this_values['battle_rewards'])){ $this_values['battle_rewards'] = array(); }
                //foreach ($this_values['battle_rewards'] AS $player_token => $player_array){
                foreach ($mmrpg_index_players AS $player_token => $player_array){
                    if ($player_token == 'player' || !mmrpg_prototype_player_unlocked($player_token)){ continue; }
                    $player_reward_array = !empty($this_values['battle_rewards'][$player_token]) ? $this_values['battle_rewards'][$player_token] : array();
                    $player_battles_array = !empty($this_values['battle_complete'][$player_token]) ? $this_values['battle_complete'][$player_token] : array();
                    $player_database_token = str_replace('-', '_', $player_token);
                    if (!empty($player_reward_array)){
                        $this_board_array['board_points_'.$player_database_token] = !empty($player_reward_array['player_points']) ? $player_reward_array['player_points'] : 0;
                        $this_board_array['board_robots_'.$player_database_token] = array();
                        $this_board_array['board_battles_'.$player_database_token] = array();
                        if (!empty($player_reward_array['player_robots'])){
                            foreach ($player_reward_array['player_robots'] AS $robot_token => $robot_array){
                                //if (!isset($robot_array['robot_token'])){ die('player_robots->'.print_r($robot_array, true)); }
                                $temp_token = !empty($robot_array['robot_token']) ? $robot_array['robot_token']: $robot_token;
                                $temp_level = !empty($robot_array['robot_level']) ? $robot_array['robot_level'] : 1;
                                $temp_robot_info = array('robot_token' => $temp_token, $temp_level);
                                $this_board_array['board_robots'][] = '['.$temp_token.':'.$temp_level.']';
                                $this_board_array['board_robots_'.$player_database_token][] = '['.$temp_token.':'.$temp_level.']';
                            }
                        }
                        if (!empty($player_reward_array['player_abilities'])){
                            foreach ($player_reward_array['player_abilities'] AS $ability_token => $ability_array){
                                //if (!isset($ability_array['ability_token'])){ die('player_abilities->'.print_r($ability_array, true)); }
                                $temp_token = !empty($ability_array['ability_token']) ? $ability_array['ability_token']: $ability_token;
                                $this_board_array['board_abilities_'.$player_database_token] += 1;
                                if (!in_array($temp_token, $temp_board_ability_tokens)){
                                    $this_board_array['board_abilities'] += 1;
                                    $temp_board_ability_tokens[] = $temp_token;
                                }
                            }
                        }
                        if (!empty($player_battles_array)){
                            foreach ($player_battles_array AS $battle_token => $battle_info){
                                $temp_token = $battle_info['battle_token'];
                                $this_board_array['board_battles'][] = '['.$temp_token.']';
                                $this_board_array['board_battles_'.$player_database_token][] = '['.$temp_token.']';
                                $this_board_array['board_missions'] += 1;
                                $this_board_array['board_missions_'.$player_database_token] += 1;
                            }
                        }
                    } else {
                        $this_board_array['board_points_'.$player_database_token] = 0;
                        $this_board_array['board_robots_'.$player_database_token] = array();
                        $this_board_array['board_battles_'.$player_database_token] = array();
                    }
                    $this_board_array['board_robots_'.$player_database_token] = !empty($this_board_array['board_robots_'.$player_database_token]) ? implode(',', $this_board_array['board_robots_'.$player_database_token]) : '';
                    $this_board_array['board_battles_'.$player_database_token] = !empty($this_board_array['board_battles_'.$player_database_token]) ? implode(',', $this_board_array['board_battles_'.$player_database_token]) : '';
                }
            }

            if (!empty($this_values['battle_stars'])){
                foreach ($this_values['battle_stars'] AS $temp_star_token => $temp_star_info){
                    $temp_star_player = str_replace('-', '_', $temp_star_info['star_player']);
                    $this_board_array['board_stars'] += 1;
                    $this_board_array['board_stars_'.$temp_star_player] += 1;
                }
            }

            //$this_board_array['board_robots'] = json_encode($this_board_array['board_robots']);
            $this_board_array['board_robots'] = !empty($this_board_array['board_robots']) ? implode(',', $this_board_array['board_robots']) : '';
            $this_board_array['board_battles'] = !empty($this_board_array['board_battles']) ? implode(',', $this_board_array['board_battles']) : '';
            $this_board_array['board_awards'] = !empty($this_board_array['board_awards']) ? implode(',', $this_board_array['board_awards']) : '';
            $this_board_array['board_date_modified'] = time();

            // DEBUG DEBUG DEBUG
            //die('<pre>$this_board_array : '.print_r($this_board_array, true).'</pre>');

            // Update this board's info in the database
            //echo('<hr /><pre>FINAL DB LEADERBOARD UPDATE (user_id = '.$_USER['userid'].')</pre>');
            //echo('<pre>$this_board_array = '.print_r($this_board_array, true).'</pre>');
            $db->update('mmrpg_leaderboard', $this_board_array, 'user_id = '.$_USER['userid']);

            // Clear any leaderboard data that exists in the session, forcing it to recache
            if (isset($_GAME['BOARD']['boardrank'])){ unset($_GAME['BOARD']['boardrank']); }

        }

        // Index the main save arrays
        if (true){

            // Define the save database update array and populate
            $this_save_array = array();
            $this_save_array['save_values_battle_complete'] = json_encode(!empty($this_values['battle_complete']) ? $this_values['battle_complete'] : array());
            $this_save_array['save_values_battle_failure'] = json_encode(!empty($this_values['battle_failure']) ? $this_values['battle_failure'] : array());
            $this_save_array['save_values_battle_rewards'] = json_encode(!empty($this_values['battle_rewards']) ? $this_values['battle_rewards'] : array());
            $this_save_array['save_values_battle_settings'] = json_encode(!empty($this_values['battle_settings']) ? $this_values['battle_settings'] : array());
            $this_save_array['save_values_battle_abilities'] = json_encode(!empty($this_values['battle_abilities']) ? $this_values['battle_abilities'] : array());
            $this_save_array['save_values_battle_items'] = json_encode(!empty($this_values['battle_items']) ? $this_values['battle_items'] : array());
            $this_save_array['save_values_battle_stars'] = json_encode(!empty($this_values['battle_stars']) ? $this_values['battle_stars'] : array());
            $this_save_array['save_values_robot_database'] = json_encode(!empty($this_values['robot_database']) ? $this_values['robot_database'] : array());
            $this_save_array['save_values_robot_alts'] = json_encode(!empty($this_values['robot_alts']) ? $this_values['robot_alts'] : array());

            $this_save_array['save_counters'] = !empty($this_counters) ? $this_counters : array();
            $this_save_array['save_values'] = !empty($this_values) ? $this_values : array();
            $this_save_array['save_flags'] = !empty($this_flags) ? $this_flags : array();
            $this_save_array['save_settings'] = !empty($this_settings) ? $this_settings : array();

            $this_save_array['save_cache_date'] = $this_cache_date;
            $this_save_array['save_date_modified'] = time();

            unset(
                $this_save_array['save_values']['battle_index'],
                $this_save_array['save_values']['battle_complete'],
                $this_save_array['save_values']['battle_failure'],
                $this_save_array['save_values']['battle_rewards'],
                $this_save_array['save_values']['battle_settings'],
                $this_save_array['save_values']['battle_abilities'],
                $this_save_array['save_values']['battle_items'],
                $this_save_array['save_values']['battle_stars'],
                $this_save_array['save_values']['robot_database'],
                $this_save_array['save_values']['robot_alts']
                );

            $this_save_array['save_counters'] = json_encode($this_save_array['save_counters']);
            $this_save_array['save_values'] = json_encode($this_save_array['save_values']);
            $this_save_array['save_flags'] = json_encode($this_save_array['save_flags']);
            $this_save_array['save_settings'] = json_encode($this_save_array['save_settings']);

            // Update this save's info in the database
            //echo('<hr /><pre>FINAL DB SAVES UPDATE (user_id = '.$_USER['userid'].')</pre>');
            //echo('<pre>$this_save_array = '.print_r($this_save_array, true).'</pre>');
            $db->update('mmrpg_saves', $this_save_array, 'user_id = '.$_USER['userid']);

        }

        //echo('LEGACY GAME has been saved!'.PHP_EOL);

        // Return true on success
        return true;

    }

    // Define a function for saving the current user session to the database
    public static function session_to_database($_GAME, $echo = false){
        global $db;

        // If there is not a user ID passed, exit
        if (!empty($_GAME['user_id'])){ $this_userid = $_GAME['user_id']; }
        else { return false; }

        //echo('legacy_rpg_game::session_to_database()'.PHP_EOL);
        //echo('$this_userid = '.print_r($this_userid, true).PHP_EOL);

        // Create index arrays for all players and robots to save
        $mmrpg_users_fields = array();
        $mmrpg_users_abilities = array();
        $mmrpg_users_players = array();
        $mmrpg_users_players_abilities = array();
        $mmrpg_users_players_omega = array();
        $mmrpg_users_robots = array();
        $mmrpg_users_robots_abilities = array();
        $mmrpg_users_robots_movesets = array();
        $mmrpg_users_robots_alts = array();
        $mmrpg_users_robots_records = array();
        $mmrpg_users_items = array();
        $mmrpg_users_stars = array();

        // Collect an index of VALID and UNLOCKABLE player, robot, and ability tokens to match against
        $allowed_players = rpg_game::get_allowed_players();
        $allowed_robots = rpg_game::get_allowed_robots();
        $allowed_abilities = rpg_game::get_allowed_abilities();
        //echo('$allowed_players = '.print_r($allowed_players, true).PHP_EOL);
        //echo('$allowed_robots = '.print_r($allowed_robots, true).PHP_EOL);
        //echo('$allowed_abilities = '.print_r($allowed_abilities, true).PHP_EOL);

        // Collect all the game session flags/counters/values
        $session_flags = $_GAME['flags'];
        $session_counters = $_GAME['counters'];
        $session_values = $_GAME['values'];
        //echo('$session_flags = '.print_r($session_flags, true).PHP_EOL);
        //echo('$session_counters = '.print_r($session_counters, true).PHP_EOL);
        //echo('$session_values = '.print_r($session_values, true).PHP_EOL);

        // Collect the global battle settings and rewards arrays
        $battle_settings = !empty($session_values['battle_settings']) ? $session_values['battle_settings'] : array();
        $battle_rewards = !empty($session_values['battle_rewards']) ? $session_values['battle_rewards'] : array();
        $battle_abilities = !empty($session_values['battle_abilities']) ? $session_values['battle_abilities'] : array();
        $battle_fields = !empty($session_values['battle_fields']) ? $session_values['battle_fields'] : array();
        $battle_items = !empty($session_values['battle_items']) ? $session_values['battle_items'] : array();
        $battle_stars = !empty($session_values['battle_stars']) ? $session_values['battle_stars'] : array();
        $robot_alts = !empty($session_values['robot_alts']) ? $session_values['robot_alts'] : array();
        $robot_database = !empty($session_values['robot_database']) ? $session_values['robot_database'] : array();
        //echo('$battle_settings = '.print_r($battle_settings, true).PHP_EOL);
        //echo('$battle_rewards = '.print_r($battle_rewards, true).PHP_EOL);
        //echo('$battle_abilities = '.print_r($battle_abilities, true).PHP_EOL);
        //echo('$battle_items = '.print_r($battle_items, true).PHP_EOL);
        //echo('$battle_stars = '.print_r($battle_stars, true).PHP_EOL);
        //echo('$robot_alts = '.print_r($robot_alts, true).PHP_EOL);
        //echo('$robot_database = '.print_r($robot_database, true).PHP_EOL);
        //echo('$battle_fields = '.print_r($battle_fields, true).PHP_EOL);

        // Collect any player omega arrays from the session
        $player_omega = rpg_game::parse_player_omega($session_values);
        //echo('$player_omega = '.print_r($player_omega, true).PHP_EOL);

        // Collect unique player tokens from the settings and/or rewards
        $player_tokens = rpg_game::parse_player_tokens($battle_settings, $battle_rewards);
        //echo('$player_tokens = '.print_r($player_tokens, true).PHP_EOL);

        // Generate database rows for the user's unlocked players
        rpg_game::parse_user_database_players($this_userid,
            $battle_settings, $battle_rewards,
            $allowed_players, $mmrpg_users_players
            );
        //echo('$mmrpg_users_players = '.print_r($mmrpg_users_players, true).PHP_EOL);

        // Generate database rows for the user's unlocked player robots
        rpg_game::parse_user_database_robots($this_userid,
            $battle_settings, $battle_rewards,
            $allowed_players, $mmrpg_users_players,
            $allowed_robots, $mmrpg_users_robots
            );
        //echo('$mmrpg_users_robots = '.print_r($mmrpg_users_robots, true).PHP_EOL);

        // Generate database rows for the user's unlocked abilities
        rpg_game::parse_user_database_abilities($this_userid,
            $battle_abilities,
            $allowed_players, $mmrpg_users_players,
            $allowed_robots, $mmrpg_users_robots,
            $allowed_abilities,
                $mmrpg_users_abilities,
                $mmrpg_users_players_abilities,
                $mmrpg_users_robots_abilities,
                $mmrpg_users_robots_movesets
                );
        //echo('$mmrpg_users_abilities = '.print_r($mmrpg_users_abilities, true).PHP_EOL);
        //echo('$mmrpg_users_players_abilities = '.print_r($mmrpg_users_players_abilities, true).PHP_EOL);
        //echo('$mmrpg_users_robots_abilities = '.print_r($mmrpg_users_robots_abilities, true).PHP_EOL);
        //echo('$mmrpg_users_robots_movesets = '.print_r($mmrpg_users_robots_movesets, true).PHP_EOL);

        // Generate database rows for the user's unlocked items
        rpg_game::parse_user_database_items($this_userid,
            $battle_items,
            $mmrpg_users_items
            );
        //echo('$mmrpg_users_items = '.print_r($mmrpg_users_items, true).PHP_EOL);

        // Generate database rows for the user's unlocked fields
        rpg_game::parse_user_database_fields($this_userid,
            $battle_fields,
            $mmrpg_users_fields
            );
        //echo('$mmrpg_users_fields = '.print_r($mmrpg_users_fields, true).PHP_EOL);

        // Generate database rows for the user's omega factors
        rpg_game::parse_user_database_omega($this_userid,
            $player_omega,
            $mmrpg_users_players_omega
            );
        //echo('$mmrpg_users_players_omega = '.print_r($mmrpg_users_players_omega, true).PHP_EOL);

        // Generate database rows for the user's collected stars
        rpg_game::parse_user_database_stars($this_userid,
            $battle_stars,
            $mmrpg_users_stars
            );
        //echo('$mmrpg_users_stars = '.print_r($mmrpg_users_stars, true).PHP_EOL);

        // Generate database rows for the user's unlocked alts
        rpg_game::parse_user_database_alts($this_userid,
            $robot_alts,
            $mmrpg_users_robots_alts
            );
        //echo('$mmrpg_users_robots_alts = '.print_r($mmrpg_users_robots_alts, true).PHP_EOL);

        // Generate database rows for the user's encounter records
        rpg_game::parse_user_database_records($this_userid,
            $robot_database,
            $allowed_robots, $mmrpg_users_robots_records
            );
        //echo('$mmrpg_users_robots_records = '.print_r($mmrpg_users_robots_records, true).PHP_EOL);

        // Clean and collapse user players and robots for database insertion
        rpg_game::prepare_user_database_players($mmrpg_users_players);
        rpg_game::prepare_user_database_robots($mmrpg_users_robots);
        //echo('$mmrpg_users_players = '.print_r($mmrpg_users_players, true).PHP_EOL);
        //echo('$mmrpg_users_robots = '.print_r($mmrpg_users_robots, true).PHP_EOL);


        // -- SAVE OBJECTS TO DATABASE -- //

        /*
        if ($echo){ echo('$mmrpg_users_players = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_players, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_abilities = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_abilities, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_players_abilities = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_players_abilities, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_abilities = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots_abilities, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_movesets = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots_movesets, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_items = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_items, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_stars = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_stars, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_alts = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots_alts, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_records = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots_records, '', true).'<hr />'.PHP_EOL); }
        */

        /*
        if ($echo){ echo('$mmrpg_users_players = '.print_r($mmrpg_users_players, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots = '.print_r($mmrpg_users_robots, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_abilities = '.print_r($mmrpg_users_abilities, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_players_abilities = '.print_r($mmrpg_users_players_abilities, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_abilities = '.print_r($mmrpg_users_robots_abilities, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_movesets = '.print_r($mmrpg_users_robots_movesets, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_items = '.print_r($mmrpg_users_items, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_stars = '.print_r($mmrpg_users_stars, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_alts = '.print_r($mmrpg_users_robots_alts, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_records = '.print_r($mmrpg_users_robots_records, true).'<hr />'.PHP_EOL); }
        */

        if ($echo){ echo('$mmrpg_users_players('.count($mmrpg_users_players).') = '.print_r(array_keys($mmrpg_users_players), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots('.count($mmrpg_users_robots).') = '.print_r(array_keys($mmrpg_users_robots), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_abilities('.count($mmrpg_users_abilities).') = '.print_r(array_keys($mmrpg_users_abilities), true).'<hr />'.PHP_EOL); }
        //if ($echo){ echo('$mmrpg_users_players_abilities('.array_sum(array_map('count', $mmrpg_users_players_abilities)).') = '.print_r(array_keys($mmrpg_users_players_abilities), true).'<hr />'.PHP_EOL); }
        //if ($echo){ echo('$mmrpg_users_robots_abilities('.array_sum(array_map('count', $mmrpg_users_robots_abilities)).') = '.print_r(array_keys($mmrpg_users_robots_abilities), true).'<hr />'.PHP_EOL); }
        //if ($echo){ echo('$mmrpg_users_robots_movesets('.array_sum(array_map('count', $mmrpg_users_robots_movesets)).') = '.print_r(array_keys($mmrpg_users_robots_movesets), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_items('.count($mmrpg_users_items).') = '.print_r(array_values(array_map(function($a){ return implode('/', array_values($a)); }, $mmrpg_users_items)), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_fields('.count($mmrpg_users_fields).') = '.print_r(array_keys($mmrpg_users_fields), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_players_omega('.count($mmrpg_users_players_omega).') = '.print_r(array_map(function($a){ return implode('/', array_column($a, 'field_token')); }, $mmrpg_users_players_omega), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_stars('.count($mmrpg_users_stars).') = '.print_r(array_keys($mmrpg_users_stars), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_alts('.array_sum(array_map('count', $mmrpg_users_robots_alts)).') = '.print_r(array_map(function($a){ return implode('/', array_keys($a)); }, $mmrpg_users_robots_alts), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_records('.count($mmrpg_users_robots_records).') = '.print_r(array_map(function($a){ return implode('/', array_values($a)); }, $mmrpg_users_robots_records), true).'<hr />'.PHP_EOL); }


        // Loop through players and update/insert them in the database
        $db_existing_players = $db->get_array_list("SELECT player_token FROM mmrpg_users_players WHERE user_id = {$this_userid};", 'player_token');
        $db_existing_players = !empty($db_existing_players) ? array_column($db_existing_players, 'player_token') : array();
        foreach ($mmrpg_users_players AS $player_token => $player_info){
            if (in_array($player_token, $db_existing_players)){
                $db->update('mmrpg_users_players', $player_info, array('user_id' => $this_userid, 'player_token' => $player_token));
            } else {
                $db->insert('mmrpg_users_players', $player_info);
            }
        }

        // Loop through robots and update/insert them in the database
        $db_existing_robots = $db->get_array_list("SELECT robot_token FROM mmrpg_users_robots WHERE user_id = {$this_userid};", 'robot_token');
        $db_existing_robots = !empty($db_existing_robots) ? array_column($db_existing_robots, 'robot_token') : array();
        foreach ($mmrpg_users_robots AS $robot_token => $robot_info){
            if (in_array($robot_token, $db_existing_robots)){
                $db->update('mmrpg_users_robots', $robot_info, array('user_id' => $this_userid, 'robot_token' => $robot_token));
            } else {
                $db->insert('mmrpg_users_robots', $robot_info);
            }
        }

        // Loop through global abilities and update/insert them in the database
        $db_existing_abilities = $db->get_array_list("SELECT ability_token FROM mmrpg_users_abilities WHERE user_id = {$this_userid};", 'ability_token');
        $db_existing_abilities = !empty($db_existing_abilities) ? array_column($db_existing_abilities, 'ability_token') : array();
        foreach ($mmrpg_users_abilities AS $ability_token => $ability_info){
            if (!in_array($ability_token, $db_existing_abilities)){
                $db->insert('mmrpg_users_abilities', $ability_info);
            }
        }

        // Loop through fields and update/insert them in the database
        $db_existing_fields = $db->get_array_list("SELECT field_token FROM mmrpg_users_fields WHERE user_id = {$this_userid};", 'field_token');
        $db_existing_fields = !empty($db_existing_fields) ? array_column($db_existing_fields, 'field_token') : array();
        foreach ($mmrpg_users_fields AS $field_token => $field_info){
            if (in_array($field_token, $db_existing_fields)){
                $db->update('mmrpg_users_fields', $field_info, array('user_id' => $this_userid, 'field_token' => $field_token));
            } else {
                $db->insert('mmrpg_users_fields', $field_info);
            }
        }

        // Loop through player abilities and update/insert them in the database
        foreach ($mmrpg_users_players_abilities AS $player_token => $player_abilities){
            $db_existing_players_abilities = $db->get_array_list("SELECT ability_token FROM mmrpg_users_players_abilities WHERE user_id = {$this_userid} AND player_token = '{$player_token}';", 'ability_token');
            $db_existing_players_abilities = !empty($db_existing_players_abilities) ? array_column($db_existing_players_abilities, 'ability_token') : array();
            foreach ($player_abilities AS $ability_token => $ability_info){
                if (!in_array($ability_token, $db_existing_players_abilities)){
                    $db->insert('mmrpg_users_players_abilities', $ability_info);
                }
            }
        }

        // Loop through player omega and update/insert them in the database
        foreach ($mmrpg_users_players_omega AS $player_token => $player_omega){
            $db->query("DELETE FROM mmrpg_users_players_omega WHERE user_id = {$this_userid} AND player_token = '{$player_token}';");
            foreach ($player_omega AS $omega_key => $omega_factor){
                $db->insert('mmrpg_users_players_omega', $omega_factor);
            }
        }

        // Loop through robot abilities and update/insert them in the database
        foreach ($mmrpg_users_robots_abilities AS $robot_token => $robot_abilities){
            $db_existing_robots_abilities = $db->get_array_list("SELECT ability_token FROM mmrpg_users_robots_abilities WHERE user_id = {$this_userid} AND robot_token = '{$robot_token}';", 'ability_token');
            $db_existing_robots_abilities = !empty($db_existing_robots_abilities) ? array_column($db_existing_robots_abilities, 'ability_token') : array();
            foreach ($robot_abilities AS $ability_token => $ability_info){
                if (!in_array($ability_token, $db_existing_robots_abilities)){
                    $db->insert('mmrpg_users_robots_abilities', $ability_info);
                }
            }
        }

        // Loop through equipped robot abilities and update/insert them in the database
        foreach ($mmrpg_users_robots_movesets AS $robot_token => $robot_abilities){
            $db->query("DELETE FROM mmrpg_users_robots_movesets WHERE user_id = {$this_userid} AND robot_token = '{$robot_token}';");
            foreach ($robot_abilities AS $ability_token => $ability_info){
                $db->insert('mmrpg_users_robots_movesets', $ability_info);
            }
        }

        // Loop through robot alts and update/insert them in the database
        foreach ($mmrpg_users_robots_alts AS $robot_token => $robot_alts){
            $db_existing_robots_alts = $db->get_array_list("SELECT alt_token FROM mmrpg_users_robots_alts WHERE user_id = {$this_userid} AND robot_token = '{$robot_token}';", 'alt_token');
            $db_existing_robots_alts = !empty($db_existing_robots_alts) ? array_column($db_existing_robots_alts, 'alt_token') : array();
            foreach ($robot_alts AS $alt_token => $alt_info){
                if (!in_array($alt_token, $db_existing_robots_alts)){
                    $db->insert('mmrpg_users_robots_alts', $alt_info);
                }
            }
        }

        // Loop through robots and update/insert them in the database
        $db_existing_robots = $db->get_array_list("SELECT robot_token FROM mmrpg_users_robots_records WHERE user_id = {$this_userid};", 'robot_token');
        $db_existing_robots = !empty($db_existing_robots) ? array_column($db_existing_robots, 'robot_token') : array();
        foreach ($mmrpg_users_robots_records AS $robot_token => $robot_info){
            if (in_array($robot_token, $db_existing_robots)){
                $db->update('mmrpg_users_robots_records', $robot_info, array('user_id' => $this_userid, 'robot_token' => $robot_token));
            } else {
                $db->insert('mmrpg_users_robots_records', $robot_info);
            }
        }

        // Loop through items and update/insert them in the database
        $db_existing_items = $db->get_array_list("SELECT item_token FROM mmrpg_users_items WHERE user_id = {$this_userid};", 'item_token');
        $db_existing_items = !empty($db_existing_items) ? array_column($db_existing_items, 'item_token') : array();
        foreach ($mmrpg_users_items AS $item_token => $item_info){
            if (in_array($item_token, $db_existing_items)){
                $db->update('mmrpg_users_items', $item_info, array('user_id' => $this_userid, 'item_token' => $item_token));
            } else {
                $db->insert('mmrpg_users_items', $item_info);
            }
        }

        // Delete existing stars for this user from the database, then loop through and re-insert current ones
        $db->query("DELETE FROM mmrpg_users_stars WHERE user_id = {$this_userid};");
        foreach ($mmrpg_users_stars AS $star_token => $star_info){
            $db->insert('mmrpg_users_stars', $star_info);
        }

        // Create index arrays for all players and robots to save
        if ($echo && defined('MMRPG_ADMIN_AJAX_REQUEST')){
            global $this_ajax_request_feedback;
            $this_ajax_request_feedback .= '$mmrpg_users_abilities('.count($mmrpg_users_abilities).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_players('.count($mmrpg_users_players).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_players_abilities('.count($mmrpg_users_players_abilities).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots('.count($mmrpg_users_robots).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots_abilities('.count($mmrpg_users_robots_abilities).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots_movesets('.count($mmrpg_users_robots_movesets).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots_alts('.count($mmrpg_users_robots_alts).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots_records('.count($mmrpg_users_robots_records).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_items('.count($mmrpg_users_items).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_stars('.count($mmrpg_users_stars).')'.PHP_EOL;
        }

        //exit();

    }


}

?>