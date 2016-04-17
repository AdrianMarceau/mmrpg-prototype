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
    global $DB;

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
    $robot_index = mmrpg_robot::get_index($player_robots_tokens);

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
                $robot_stats = mmrpg_robot::calculate_stat_values($level, $robot_info, $robot_rewards);

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


/*

// -- PATCH FUNCTION TEMPLATE -- //

// Define a patch function for applying the max robot stats update
$token = 'template';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Template Patch Name';
$update_patch_details[$token] = "Template patch details.  Use this area to describe the purpose and procedure.';
function mmrpg_patch_template($_GAME){

    // Pull in global variables
    global $DB;


    // Return the updated game array
    return $_GAME;

}

*/

?>