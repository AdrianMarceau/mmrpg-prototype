<?

// -- STAT MAX LIMIT UPDATE -- //

// Define a patch function for applying the max robot stats update
$token = 'stat_max_limit_update';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Balancing Max Limits for Robot Stats';
$update_patch_details[$token] = "Robot stats are no longer capped at a max of 9999 and are instead based on each robot's base values.";
$update_patch_details[$token] .= "\nEach stat can now only be boosted to maximum of ".(MMRPG_SETTINGS_STATS_BONUS_MAX + 1)."x its base value at level 100.";
$update_patch_details[$token] .= "\n".'Any overflow of stats that are now considered too high will be converted to zenny for the player.';
function mmrpg_patch_stat_max_limit_update($_GAME){

    // Pull in global variables
    global $db;

    // Parse out robot data from the player rewards and make a copy
    $player_robots_tokens = array();
    $player_robots_rewards = array();
    if ($_GAME['values']['battle_rewards']){
        foreach ($_GAME['values']['battle_rewards'] AS $player_token => $player_rewards){
            if (!empty($player_rewards['player_robots'])){
                foreach ($player_rewards['player_robots'] AS $robot_token => $robot_rewards){
                    $player_robots_rewards[$player_token][$robot_token] = $robot_rewards;
                    $player_robots_tokens[] = $robot_token;
                }
            }
        }
    }

    //echo('<pre>----------------------------------------</pre>');
    //echo('<pre>$player_robots_rewards(before) = '.print_r($player_robots_rewards, true).'</pre>');
    //echo('<pre>----------------------------------------</pre>');

    // Define the stats to loop through and alter
    $stat_tokens = array('energy', 'attack', 'defense', 'speed');

    // Collect a mini robot index for the ones unlocked by the player
    $robot_index = rpg_robot::get_index($player_robots_tokens);

    //echo('<pre>$stat_tokens = '.print_r($stat_tokens, true).'</pre>');
    //echo('<pre>$robot_index = '.print_r($robot_index, true).'</pre>');

    // Define a variable for collecting total stat overflow
    $stat_overflow_total = 0;
    $stat_overflow_reward_total = 0;

    echo('<pre>');

    // Loop through each robot and see if it needs to be limited
    foreach ($player_robots_rewards AS $player_token => $player_robots){
        foreach ($player_robots AS $robot_token => $robot_rewards){
            if (isset($robot_index[$robot_token])){

                // Collect this robot's index info for reference
                $robot_info = $robot_index[$robot_token];
                echo("[b]Updating {$robot_info['robot_name']}[/b]\n");

                // Collect and calculate this robot's stat details
                $level = isset($robot_rewards['robot_level']) ? $robot_rewards['robot_level'] : 1;
                $robot_stats = rpg_robot::calculate_stat_values($level, $robot_info, $robot_rewards);

                //echo('$robot_stats => '.print_r($robot_stats, true)."\n");

                // Any of this robot's stat are over the limit, we need to adjust
                $stat_overflow = 0;
                $stat_overflow_reward = 0;
                foreach ($stat_tokens AS $key => $stat){
                    if (!empty($robot_stats[$stat]['over'])){
                        echo("\n");
                        echo("{$robot_info['robot_name']}'s {$stat} bonus of {$robot_rewards['robot_'.$stat]} is over the max by {$robot_stats[$stat]['over']}!\n");
                        $robot_rewards['robot_'.$stat] -= $robot_stats[$stat]['over'];
                        $player_robots_rewards[$player_token][$robot_token]['robot_'.$stat] = $robot_rewards['robot_'.$stat];
                        echo("- ".ucfirst($stat)." bonus has been capped at {$robot_stats[$stat]['bonus_max']}.\n");
                        echo("- {$robot_info['robot_name']}'s {$stat} stat is now at ".($robot_stats[$stat]['current'] - $robot_stats[$stat]['over']).".\n");
                        $stat_overflow += $robot_stats[$stat]['over'];
                    }
                }

                // If this robot had any overflow, we need to reward the player
                if (!empty($stat_overflow)){
                    echo("\n");
                    $stat_overflow_reward = $stat_overflow * 10;
                    echo("{$robot_info['robot_name']} had an overflow of ".number_format($stat_overflow, 0, '.', ',')." points.\n");
                    echo("{$robot_info['robot_name']} earned the player ".number_format($stat_overflow_reward, 0, '.', ',')."z!\n");
                    $stat_overflow_total += $stat_overflow;
                    $stat_overflow_reward_total += $stat_overflow_reward;
                } else {
                    echo("{$robot_info['robot_name']}'s stats are on target!\n");
                }

                // Print line break at end of robot
                echo("\n");

            } else {
                unset($player_robots_rewards[$player_token][$robot_token]);
                continue;
            }
        }
    }

    // Print out the totals for the overflow and rewards
    echo("[b]Updates Complete![/b]\n");
    echo("Stat Overflow Total    : ".number_format($stat_overflow_total, 0, '.', ',')."\n");
    echo("Overflow Reward Total  : ".number_format($stat_overflow_reward_total, 0, '.', ',')."z\n");

    // Loop through the modifed robot values and update the game
    foreach ($player_robots_rewards AS $player_token => $player_robots){
        foreach ($player_robots AS $robot_token => $robot_rewards){
            $_GAME['values']['battle_rewards'][$player_token]['player_robots'][$robot_token] = $robot_rewards;
        }
    }

    // And add the reward zenny to the player's total amount
    if (!isset($_GAME['counters']['battle_zenny'])){ $_GAME['counters']['battle_zenny'] = 0; }
    $_GAME['counters']['battle_zenny'] += $stat_overflow_reward_total;

    //echo("----------------------------------------\n");
    //echo("\$_GAME = ".print_r($_GAME, true)."\n");
    //echo("\$_GAME[values][battle_rewards] = ".print_r($_GAME['values']['battle_rewards'], true)."\n");
    //echo("----------------------------------------\n");
    //echo("\$player_robots_rewards(after) = ".print_r($player_robots_rewards, true)."\n");
    //echo("----------------------------------------\n");

    // Return the updated game array
    return $_GAME;

}


// -- BATTLE POINT REBOOT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'battle_point_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Battle Point Reboot of 2016';
$update_patch_details[$token] = "Battle points are no longer some arbituary number you can grind for all eternity.";
$update_patch_details[$token] .= "\nFrom now on, battle points will be equal to the total sum of all your *best* mission scores combined.";
$update_patch_details[$token] .= "\nWhile replaying missions for better scores is abolutely encouraged, it is no longer necessary to return over and over simply to grind points.";
function mmrpg_patch_battle_point_reboot_2k16($_GAME){

    // Pull in global variables
    global $db;

    // Define the variable to hold the current battle point totals
    $new_battle_points = 0;
    $new_player_battle_points = array();

    echo("Recalculating battle point totals...\n");

    // Define search and replace variables for later display
    $numerals_find = array('Iv', 'Iii', 'Ii');
    $numerals_replace = array('IV', 'III', 'II');

    // If the player has completed battles, loop through them and add up points
    $player_tokens = array('dr-light', 'dr-wily', 'dr-cossack');
    if (!empty($_GAME['values']['battle_complete'])){
        foreach ($player_tokens AS $key => $player_token){
            $new_player_battle_points[$player_token] = 0;
            if (empty($_GAME['values']['battle_complete'][$player_token])){ continue; }
            else { $player_battles = $_GAME['values']['battle_complete'][$player_token]; }
            if (!empty($player_battles)){
                $last_player = '';
                //asort($player_battles);
                foreach ($player_battles AS $battle_token => $battle_records){
                    $corrupt_mission = false;
                    if (!empty($battle_records['battle_max_points'])){
                        $max_points = $battle_records['battle_max_points'];
                        $battle_name = array_slice(explode('-', $battle_token), 1);
                        $player_name = array_shift($battle_name);
                        if ('dr-'.$player_name != $player_token){ $corrupt_mission = true; }
                        $player_name = 'Dr. '.ucfirst($player_name);
                        $phase_name = ucwords(array_shift($battle_name));
                        $battle_name = implode(' / ', $battle_name);
                        $battle_name = ucwords($battle_name);
                        $battle_name = str_replace($numerals_find, $numerals_replace, $battle_name);
                        if ($last_player != $player_name){ echo("\n"); }
                        if (!$corrupt_mission){
                            $new_battle_points += $max_points;
                            $new_player_battle_points[$player_token] += $max_points;
                            echo("+".print_r(number_format($max_points, 0, '.', ','), true)." | {$player_name} | {$phase_name} | {$battle_name} \n");
                        } else {
                            echo("[s]+0 | {$player_name} | {$phase_name} | {$battle_name}[/s] (?????) \n");
                            unset($_GAME['values']['battle_complete'][$player_token][$battle_token]);
                        }
                        $last_player = $player_name;
                    }
                }
            }
        }
        echo("\n");
    } else {
        echo("No battles have been completed...\n");
    }


    echo("----------------------------------------\n\n");

    // Define the variable to hold the current battle point totals
    $reward_battle_zenny = 0;
    $reward_player_battle_points = array();

    // Loop through the players and calculate their new battles points and rewards
    foreach ($new_player_battle_points AS $player => $points){
        $player_name = ucwords(str_replace('dr-', 'Dr. ', $player));
        $old_points = $_GAME['values']['battle_rewards'][$player]['player_points'];
        $new_points = $points;
        $points_diff = $old_points - $new_points;
        $zenny_reward = ceil($points_diff / 1000);
        echo("[b]{$player_name}[/b]\n");
        echo("Old battle points : ".print_r(number_format($old_points, 0, '.', ','), true)."\n");
        echo("New battle points : ".print_r(number_format($new_points, 0, '.', ','), true)."\n");
        echo("Difference : -".print_r(number_format($points_diff, 0, '.', ','), true)."\n");
        echo("Compensation : ".print_r(number_format($zenny_reward, 0, '.', ','), true)."z\n");
        echo("\n");
        $reward_player_battle_points[$player] = $zenny_reward;
        $reward_battle_zenny += $zenny_reward;
    }

    echo("[b]Battle Point Totals[/b]\n");
    $old_points = $_GAME['counters']['battle_points'];
    $new_points = $new_battle_points;
    $points_diff = $old_points - $new_points;
    echo("Old battle point total : ".print_r(number_format($old_points, 0, '.', ','), true)."\n");
    echo("New battle point total : ".print_r(number_format($new_points, 0, '.', ','), true)."\n");
    echo("Difference total : -".print_r(number_format($points_diff, 0, '.', ','), true)."\n");
    echo("Compensation total : [b]".print_r(number_format($reward_battle_zenny, 0, '.', ','), true)."z[/b]\n");
    echo("\n");

    echo("----------------------------------------\n\n");

    //echo("\$_GAME['counters'] = ".print_r($_GAME['counters'], true)."\n");
    //echo("\$_GAME['values']['battle_complete'] = ".print_r($_GAME['values']['battle_complete'], true)."\n");

    //echo("----------------------------------------\n");

    // Let's also fix other cached leaderboard values while we're here
    echo("Refreshing cached leaderboard values...\n\n");

    // Calculate the board robots for each player
    $board_robots = array();
    $board_robots_tokens = array();

    // Calculate the board abilities for each player
    $board_abilities = array();
    $board_abilities_tokens = array();

    // Loop through battle rewards and collect unique robot details
    if (!empty($_GAME['values']['battle_rewards'])){
        $battle_rewards = $_GAME['values']['battle_rewards'];
        foreach ($battle_rewards AS $player_token => $player_rewards){

            // Remove empty player data from the game arrays
            if (empty($player_token)){
                unset($_GAME['values']['battle_rewards'][$player_token]);
                unset($_GAME['values']['battle_settings'][$player_token]);
                unset($battle_rewards[$player_token]);
                continue;
            }

            // -- PLAYER ROBOT REWARDS -- //

            // Loop through player robots collecting unique robot details
            if (!empty($player_rewards['player_robots'])){
                $player_robots = $player_rewards['player_robots'];
                foreach ($player_robots AS $robot_token => $robot_rewards){

                    // Remove empty robot data from the game arrays
                    if (empty($robot_token)){
                        unset($_GAME['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]);
                        unset($_GAME['values']['battle_settings'][$player_token]['player_robots'][$robot_token]);
                        unset($player_robots[$robot_token]);
                        continue;
                    }

                    // Collect this robot's current level if set
                    $robot_level = !empty($robot_rewards['robot_level']) ? $robot_rewards['robot_level'] : 1;

                    // Add this robot's token to the global array if not already there
                    if (!in_array($robot_token, $board_robots_tokens)){ $board_robots_tokens[] = $robot_token; }

                    // If this robot is not yet added or is a duplicate at a higher level, use its data
                    if (!isset($board_robots[$robot_token]) || $robot_level > $board_robots[$robot_token]['robot_level']){

                        // Update the parent board robots array with this data
                        $board_robots[$robot_token] = array(
                            'player_token' => $player_token,
                            'robot_token' => $robot_token,
                            'robot_level' => $robot_level
                            );

                    }

                    // If this robot's data already exists but this player is not the owner...
                    if (isset($board_robots[$robot_token]) && $player_token != $board_robots[$robot_token]['player_token']){

                        // Remove this robot from the current player's arrays
                        unset($_GAME['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]);
                        unset($_GAME['values']['battle_settings'][$player_token]['player_robots'][$robot_token]);
                        unset($player_robots[$robot_token]);
                        continue;

                    }

                }
            }

            // -- PLAYER ABILITY REWARDS -- //

            // Loop through player abilities collecting unique ability details
            if (!empty($player_rewards['player_abilities'])){
                $player_abilities = $player_rewards['player_abilities'];
                foreach ($player_abilities AS $ability_token => $ability_rewards){

                    // Remove empty ability data from the game arrays
                    if (empty($ability_token)){
                        unset($_GAME['values']['battle_rewards'][$player_token]['player_abilities'][$ability_token]);
                        unset($_GAME['values']['battle_settings'][$player_token]['player_abilities'][$ability_token]);
                        unset($player_abilities[$ability_token]);
                        continue;
                    }

                    // Add this ability's token to the global array if not already there
                    if (!in_array($ability_token, $board_abilities_tokens)){ $board_abilities_tokens[] = $ability_token; }

                    // If this ability is not yet added use this data, else add this player token
                    if (!isset($board_abilities[$ability_token])){

                        // Update the parent board abilities array with this data
                        $board_abilities[$ability_token] = array(
                            'player_tokens' => array($player_token),
                            'ability_token' => $ability_token
                            );

                    } else {

                        // Update the parent board abilities array with this data
                        $board_abilities[$ability_token]['player_tokens'][] = $player_token;

                    }

                }
            }

        }
    }

    //echo("\$board_robots = ".print_r($board_robots, true)."\n");
    //echo("\$board_robots_tokens = ".print_r($board_robots_tokens, true)."\n");

    //echo("\$board_abilities = ".print_r($board_abilities, true)."\n");
    //echo("\$board_abilities_tokens = ".print_r($board_abilities_tokens, true)."\n");

    // Loop through robot values again and generate database strings
    $board_robots_array = array();
    $board_player_robots_array = array();
    foreach ($board_robots AS $robot_token => $robot_info){
        $robot_string = "[{$robot_token}:{$robot_info['robot_level']}]";
        $board_robots_array[] = $robot_string;
        $board_player_robots_array[$robot_info['player_token']][] = $robot_string;
    }

    // Loop through ability values again and generate database strings
    $board_abilities_count = 0;
    $board_player_abilities_count = array();
    foreach ($board_abilities AS $ability_token => $ability_info){
        $board_abilities_count += 1;
        foreach ($ability_info['player_tokens'] AS $player_token){
            if (!isset($board_player_abilities_count[$player_token])){ $board_player_abilities_count[$player_token] = 0; }
            $board_player_abilities_count[$player_token] += 1;
        }
    }

    //echo("\$board_robots_array = ".print_r($board_robots_array, true)."\n");
    //echo("\$board_player_robots_array = ".print_r($board_player_robots_array, true)."\n");

    //echo("\$board_abilities_count = ".print_r($board_abilities_count, true)."\n");
    //echo("\$board_player_abilities_count = ".print_r($board_player_abilities_count, true)."\n");

    // Compress the strings into comma-separated lists
    $board_robots_string = implode(',', $board_robots_array);
    $board_player_robots_string = array();
    foreach ($board_player_robots_array AS $player_token => $robots_array){
        $player_name = ucwords(str_replace('dr-', 'dr. ', $player_token));
        $board_player_robots_string[$player_token] = implode(',', $robots_array);
        echo("{$player_name} Robots : ".count($robots_array)."\n");
    }
    echo("Total Leaderboard Robots : [b]".count($board_robots_array)."[/b]\n\n");


    foreach ($board_player_abilities_count AS $player_token => $ability_count){
        $player_name = ucwords(str_replace('dr-', 'dr. ', $player_token));
        echo("{$player_name} Abilities : ".$ability_count."\n");
    }
    echo("Total Leaderboard Abilities : [b]".$board_abilities_count."[/b]\n\n");

    $board_missions_count = 0;
    $board_player_missions_count = array();
    foreach ($_GAME['values']['battle_complete'] AS $player_token => $battle_complete){
        $player_name = ucwords(str_replace('dr-', 'dr. ', $player_token));
        $mission_count = count($battle_complete);
        echo("{$player_name} Missions : ".$mission_count."\n");
        $board_player_missions_count[$player_token] = $mission_count;
        $board_missions_count += $mission_count;
    }
    echo("Total Leaderboard Missions : [b]".$board_missions_count."[/b]\n\n");

    //echo("\$board_robots_string = ".print_r($board_robots_string, true)."\n");
    //echo("\$board_player_robots_string = ".print_r($board_player_robots_string, true)."\n");

    //echo("----------------------------------------\n\n");

    // Update the game file and leadboard with these new changes
    $old_battle_points = $_GAME['counters']['battle_points'];
    $_GAME['counters']['battle_points'] = $new_battle_points;
    $board_updates = array();
    $board_updates[] = "board_points_legacy = {$old_battle_points}";
    $board_updates[] = "board_points = {$new_battle_points}";
    $board_updates[] = "board_robots = '{$board_robots_string}'";
    $board_updates[] = "board_abilities = {$board_abilities_count}";
    $board_updates[] = "board_missions = {$board_missions_count}";
    foreach ($player_tokens AS $key => $player_token){
        $old_points = $_GAME['values']['battle_rewards'][$player_token]['player_points'];
        $new_points = isset($new_player_battle_points[$player_token]) ? $new_player_battle_points[$player_token] : 0;
        $new_robots = isset($board_player_robots_string[$player_token]) ? $board_player_robots_string[$player_token] : '';
        $new_abilities = isset($board_player_abilities_count[$player_token]) ? $board_player_abilities_count[$player_token] : 0;
        $new_missions = isset($board_player_missions_count[$player_token]) ? $board_player_missions_count[$player_token] : 0;
        $dbplayer = str_replace('-', '_', $player_token);
        $_GAME['values']['battle_rewards'][$player_token]['player_points'] = $new_points;
        $board_updates[] = "board_points_{$dbplayer}_legacy = {$old_points}";
        $board_updates[] = "board_points_{$dbplayer} = {$new_points}";
        $board_updates[] = "board_robots_{$dbplayer} = '{$new_robots}'";
        $board_updates[] = "board_abilities_{$dbplayer} = {$new_abilities}";
        $board_updates[] = "board_missions_{$dbplayer} = {$new_missions}";
    }
    $db->query("UPDATE mmrpg_leaderboard SET
        ".implode(",\n", $board_updates)."
        WHERE user_id = {$_GAME['user_id']}
        ;");

    // Increment the player's zenny total with the compensation reward
    $_GAME['counters']['battle_zenny'] += $reward_battle_zenny;

    // Return the updated game array
    return $_GAME;

}


// -- PLAYER ABILITY MERGE 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'player_ability_merge_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Player Ability Merge of 2016';
$update_patch_details[$token] = "Abilities unlocked by any player can now be used by all players. ";
$update_patch_details[$token] .= "\nIt is no longer necessary to trade robots back and forth to customize ";
$update_patch_details[$token] .= "\nthe perfect moveset, and I hope everyone will enjoy this new change. :) ";
function mmrpg_patch_player_ability_merge_2k16($_GAME){

    // Pull in global variables
    global $db;

    // Collect a list of unlockable ability tokens
    $valid_ability_tokens = $db->get_array_list("SELECT ability_token FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_class = 'master'", 'ability_token');
    $valid_ability_tokens = !empty($valid_ability_tokens) ? array_keys($valid_ability_tokens) : array();

    // Create a new entry in the session for battle abilities
    $new_battle_abilities = array();
    $reward_battle_zenny = 0;
    $reward_value = 3000;

    echo("Scanning player and robot abilities...\n\n");

    // If the player or their robots have unlocked abilities, loop through and collects them
    $player_tokens = array('dr-light', 'dr-wily', 'dr-cossack');
    if (!empty($_GAME['values']['battle_rewards'])){
        foreach ($player_tokens AS $key => $player_token){

            $player_name = ucwords(str_replace('dr-', 'dr. ', $player_token));

            if (!isset($_GAME['values']['battle_rewards'][$player_token]) &&
                !isset($_GAME['values']['battle_settings'][$player_token])){
                continue;
            }

            if (empty($_GAME['values']['battle_rewards'][$player_token])){ $player_rewards = array(); }
            else { $player_rewards = $_GAME['values']['battle_rewards'][$player_token]; }

            if (empty($_GAME['values']['battle_settings'][$player_token])){ $player_settings = array(); }
            else { $player_settings = $_GAME['values']['battle_settings'][$player_token]; }

            // Loop through player and robot rewards, collecting and validating abilities
            if (!empty($player_rewards)){

                // If this player has unlocked their own abilities, collect them
                if (!empty($player_rewards['player_abilities'])){
                    $removed_ability = false;
                    $player_abilities = array_keys($player_rewards['player_abilities']);
                    $new_battle_abilities = array_merge($new_battle_abilities, $player_abilities);
                    echo("Collecting ability rewards from {$player_name}\n");
                    // Manually unset any invalid ability tokens
                    foreach ($player_rewards['player_abilities'] AS $ability_token => $ability_info){
                        $ability_name = ucwords(str_replace('-', ' ', $ability_token));
                        if (!in_array($ability_token, $valid_ability_tokens)){
                            unset($player_rewards['player_abilities'][$ability_token]);
                            unset($player_settings['player_abilities'][$ability_token]);
                            echo("- Removing legacy ability {$ability_name} (+{$reward_value}z)\n");
                            $reward_battle_zenny += $reward_value;
                            $removed_ability = true;
                        }
                    }
                    if (!$removed_ability){ echo("+ All clear!\n"); }
                    echo("\n");
                }


                // If this player has robots, loop through and collect their abilities
                if (!empty($player_rewards['player_robots'])){
                    foreach ($player_rewards['player_robots'] AS $robot_token => $robot_rewards){
                        $removed_ability = false;
                        $robot_name = ucwords(str_replace('-', ' ', $robot_token));
                        $robot_settings = isset($player_settings['player_robots'][$robot_token]) ? $player_settings['player_robots'][$robot_token] : array();
                        if (!empty($robot_rewards['robot_abilities'])){
                            $robot_abilities = array_keys($robot_rewards['robot_abilities']);
                            $new_battle_abilities = array_merge($new_battle_abilities, $robot_abilities);
                            echo("Collecting ability rewards from {$player_name}'s {$robot_name}\n");
                            // Manually unset any invalid ability tokens
                            foreach ($robot_rewards['robot_abilities'] AS $ability_token => $ability_info){
                                $ability_name = ucwords(str_replace('-', ' ', $ability_token));
                                if (!in_array($ability_token, $valid_ability_tokens)){
                                    unset($player_rewards['player_robots'][$robot_token]['robot_abilities'][$ability_token]);
                                    unset($player_settings['player_robots'][$robot_token]['robot_abilities'][$ability_token]);
                                    echo("- Removing legacy ability {$ability_name} (+{$reward_value}z)\n");
                                    $reward_battle_zenny += $reward_value;
                                    $removed_ability = true;
                                    // Give this robot the buster shot if they have no more abilities
                                    if (empty($player_settings['player_robots'][$robot_token]['robot_abilities'])){
                                        $player_settings['player_robots'][$robot_token]['robot_abilities']['buster_shot'] = array('ability_token' => 'buster_shot');
                                    }
                                }
                            }
                        }
                        if (!$removed_ability){ echo("+ All clear!\n"); }
                        echo("\n");
                    }
                }

            }

            // Update parent reward and settings arrays
            $_GAME['values']['battle_rewards'][$player_token] = $player_rewards;
            $_GAME['values']['battle_settings'][$player_token] = $player_settings;


        }
    }

    // Clean the unlocked ability array of duplicate values
    $new_battle_abilities = array_unique($new_battle_abilities);

    echo("Generating new global abilities array...\n\n");

    // Loop through global abilities and remove incomplete
    foreach ($new_battle_abilities AS $key => $ability_token){
        $ability_name = str_replace(' ', '', ucwords(str_replace('-', ' ', $ability_token)));
        $is_valid = in_array($ability_token, $valid_ability_tokens) ? true : false;
        if ($is_valid){ echo "+{$ability_name} "; }
        else { unset($new_battle_abilities[$key]); }
    }

    // Re-key the ability array now that duplicates are removed
    $new_battle_abilities = array_values($new_battle_abilities);

    echo("\n");

    //echo('<pre>$new_battle_abilities = '.print_r($new_battle_abilities, true).'</pre>');
    //echo('<pre>$valid_ability_tokens = '.print_r($valid_ability_tokens, true).'</pre>');

    // Update the parent game array with the new ability list
    $_GAME['values']['battle_abilities'] = $new_battle_abilities;

    echo("\n");

    echo("--------------------\n\n");

    echo("Ability merge complete. Thank you for your understanding. \n\n");

    echo("[b]Total Compensation : ".number_format($reward_battle_zenny, 0, '.', ',')."z[/b]\n\n");

    // Increment the player's zenny total with the compensation reward
    $_GAME['counters']['battle_zenny'] += $reward_battle_zenny;

    // Return the updated game array
    return $_GAME;

}


// -- PLAYER ITEM / ABILITY SPLIT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'ability_item_split_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Ability / Item Split of 2016';
$update_patch_details[$token] = "Abilities and items were technically the same thing in the coding of the game \n";
$update_patch_details[$token] .= "and it required a lot of extra coding to get them to work.  In an effort to \n";
$update_patch_details[$token] .= "optimize the game items are being split into their own separate category of \n";
$update_patch_details[$token] .= "objects. This patch simply re-organizes some of that data in your game file. ";
function mmrpg_patch_ability_item_split_2k16($_GAME){

    // Pull in global variables
    global $db;

    // Collect the player's items array
    $legacy_battle_items = !empty($_GAME['values']['battle_items']) ? $_GAME['values']['battle_items'] : array();

    echo("Scanning player battle items...\n\n");

    // Only bother with parsing if the player actually has items
    if (!empty($legacy_battle_items)){

        // Create arrays to hold the new item list data
        $new_battle_items = array();
        $new_battle_items_total_unique = 0;
        $new_battle_items_total_overall = 0;

        // Print out the player current list of items
        foreach ($legacy_battle_items AS $legacy_item_token => $item_quantity){

            // Create a clone of the item token and update it
            $new_item_token = $legacy_item_token;
            $new_item_token = preg_replace('/^item-/i', '', $new_item_token);
            $new_item_token = preg_replace('/^(screw|core)-([a-z]+)$/', '$2-$1', $new_item_token);

            // Print out the old and new tokens for the user
            echo('Legacy Token : "'.$legacy_item_token.'"');
            echo(' | New Token : "'.$new_item_token.'"');
            echo(' | Quantity : '.$item_quantity.'');
            echo("\n");

            // Add the new item info to the parent array
            $new_battle_items[$new_item_token] = $item_quantity;
            $new_battle_items_total_unique += 1;
            $new_battle_items_total_overall += $item_quantity;

        }


        // Print out the end of the scan messge with the results
        echo("\n");
        echo("--------------------\n\n");
        echo("Item separation complete.  Thank you.\n");
        echo("[b]Unique Item Total[/b] : {$new_battle_items_total_unique}\n");
        echo("[b]Overall Item Total[/b] : ".number_format($new_battle_items_total_overall, 0, '.', ',')."\n");
        echo("\n");

        // Update the player's battle item array with the new format
        $_GAME['values']['battle_items'] = $new_battle_items;


    }
    // Otherwise, if no items, the player does not need parsing
    else {

        // Print out the end of scan message an empty result
        echo("--------------------\n\n");
        echo("...oh, you have no items.  Sorry to bother you!\n\n");

    }

    // Print out debug info and exit now
    //header('Content-type: text/plain;');
    //echo('<pre>$new_battle_items = '.print_r($new_battle_items, true).'</pre>'."\n");
    //exit('ability_item_split_2k16()');

    // Return the updated game array
    return $_GAME;

}


// -- STAR FORCE REBOOT 2k16 -- //

// Define a patch function for applying the next update
$token = 'star_force_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Star Force Reboot of 2016';
$update_patch_details[$token] = "Field and Fusion stars now boost battle points instead of boosting elemental \n";
$update_patch_details[$token] .= "damage and recovery power in battle.  As a result, battle points for all players \n";
$update_patch_details[$token] .= "must be recalculated. ";
function mmrpg_patch_star_force_reboot_2k16($_GAME){

    // Pull in global variables
    global $db;

    // Collect the player's items array
    $battle_points_total = !empty($_GAME['counters']['battle_points']) ? $_GAME['counters']['battle_points'] : 0;
    $battle_stars = !empty($_GAME['values']['battle_stars']) ? $_GAME['values']['battle_stars'] : array();
    $battle_stars_count = !empty($battle_stars) ? count($battle_stars) : 0;

    echo("Recalculating player battle points...\n\n");

    // Only bother with parsing if the player actually has stars
    if (!empty($battle_stars_count)){

        // Print out the current battle points and star counts
        echo("--------------------\n\n");
        echo("Current Battle Points : ".number_format($battle_points_total, 0, '.', ',')."\n\n");
        echo("Current Star Count : ".number_format($battle_stars_count, 0, '.', ',')."\n\n");

        // Recalculate battle points and print out the new totals
        echo("--------------------\n\n");
        $battle_points_new = mmrpg_prototype_calculate_battle_points(true, $_GAME);
        echo("New Battle Points : ".number_format($battle_points_new, 0, '.', ',')."\n\n");
        echo("--------------------\n\n");

    }
    // Otherwise, if no items, the player does not need parsing
    else {

        // Print out the end of scan message an empty result
        echo("--------------------\n\n");
        echo("...oh, you don't have any stars.  Sorry to bother you!\n\n");

    }

    // Print out debug info and exit now
    //header('Content-type: text/plain;');
    //exit('star_force_reboot_2k16()');

    // Return the updated game array
    return $_GAME;

}


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

    define('MMRPG_REMOTE_GAME', $_GAME['user_id']);
    define('MMRPG_REMOTE_GAME_ID', $_GAME['user_id']);
    $session_token = rpg_game::session_token();
    $_SESSION[$session_token] = $_GAME;

    echo("Converting session objects into database objects for user ID {$_GAME['user_id']}... \n\n");

    // Trigger the session to database function to do all the work
    rpg_game::session_to_database(true);

    // Print the final message of success
    echo("...done! Thank you for your time. :) \n\n");

    // Print out debug info and exit now
    header('Content-type: text/plain;');
    exit('mmrpg_patch_db_user_objects_2k17() $_SESSION['.$session_token.']');

    // Return the updated game array
    unset($_SESSION[$session_token]);
    return $_GAME;

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

?>