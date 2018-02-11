<?

/*
 * MULTIPLAYER UNLOCKABLES
 */


/* -- UNLOCKABLE EVENTS -- */

// DYNAMIC EVENTS : PLAYER BATTLE REWARDS
// Collect any outstanding battle rewards from the database
$temp_battles_query = "SELECT mmrpg_battles.*, mmrpg_users.user_name AS this_user_name, mmrpg_users.user_name_clean AS this_user_name_clean, mmrpg_users.user_name_public AS this_user_name_public FROM mmrpg_battles ";
$temp_battles_query .= "LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_battles.this_user_id ";
$temp_battles_query .= "WHERE mmrpg_battles.target_user_id = {$this_userid} AND mmrpg_battles.target_reward_pending = 1";
$temp_battles_list = $db->get_array_list($temp_battles_query);
$temp_userinfo = $this_userinfo; //$db->get_array("SELECT users.*, roles.* FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");

// If the player has pending battle rewards, loop through and display window events
if (!empty($temp_battles_list)){

    // Loop through each of the battles and display it to the user
    foreach ($temp_battles_list AS $temp_key => $temp_battleinfo){

        // Collect temp playerinfo for this and the target
        $temp_this_playerinfo = $mmrpg_index['players'][$temp_battleinfo['this_player_token']];
        $temp_this_playername = !empty($temp_battleinfo['this_user_name_public']) ? $temp_battleinfo['this_user_name_public'] : $temp_battleinfo['this_user_name'];
        $temp_this_playerrobots = !empty($temp_battleinfo['this_player_robots']) ? explode(',', $temp_battleinfo['this_player_robots']) : array();
        if (!empty($temp_this_playerrobots)){ foreach ($temp_this_playerrobots AS $key => $info){ list($token, $level) = explode(':',trim($info,'[]')); $temp_this_playerrobots[$key] = array('token' => $token, 'level' => $level); } }
        $temp_target_playerinfo = $mmrpg_index['players'][$temp_battleinfo['target_player_token']];
        $temp_target_playername = !empty($temp_userinfo['user_name_public']) ? $temp_userinfo['user_name_public'] : $temp_userinfo['user_name'];
        $temp_target_playerrobots = !empty($temp_battleinfo['target_player_robots']) ? explode(',', $temp_battleinfo['target_player_robots']) : array();
        if (!empty($temp_target_playerrobots)){ foreach ($temp_target_playerrobots AS $key => $info){ list($token, $level) = explode(':',trim($info,'[]')); $temp_target_playerrobots[$key] = array('token' => $token, 'level' => $level); } }

        //die('<pre>'.print_r($temp_this_playerrobots, true).'</pre>');

        // Collect the appropriate player points to increment
        $temp_inc_zenny = ceil($temp_battleinfo['target_player_points'] / 1000);
        $temp_inc_player = str_replace('-', '_', $temp_target_playerinfo['player_token']);

        // Display the player battle reward message, showing the doctor and his robots
        $temp_console_markup = '';
        $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$temp_battleinfo['battle_field_background'].'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$temp_battleinfo['battle_field_name'].'</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$temp_battleinfo['battle_field_foreground'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$temp_battleinfo['battle_field_name'].'</div>';
        foreach ($temp_target_playerrobots AS $key => $info){ $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_'.$temp_battleinfo['target_player_result'].'" style="background-image: url(images/robots/'.$info['token'].'/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: '.(25 + $key * 3).'px; left: '.(-10 + $key * 20).'px; z-index: '.(10 - $key).';">'.$info['token'].' (Lv.'.$info['level'].')</div>'; }
        foreach ($temp_this_playerrobots AS $key => $info){ $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_'.$temp_battleinfo['this_player_result'].'" style="background-image: url(images/robots/'.$info['token'].'/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: '.(25 + $key * 3).'px; right: '.(-10 + $key * 20).'px; z-index: '.(10 - $key).';">'.$info['token'].' (Lv.'.$info['level'].')</div>'; }
        $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_'.$temp_battleinfo['target_player_result'].'" style="background-image: url(images/players/'.$temp_target_playerinfo['player_token'].'/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 25px; left: 110px; z-index: 20;">'.$temp_target_playerinfo['player_name'].'</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_'.$temp_battleinfo['this_player_result'].'" style="background-image: url(images/players/'.$temp_this_playerinfo['player_token'].'/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 25px; right: 110px; z-index: 20;">'.$temp_target_playerinfo['player_name'].'</div>';

            // If this player claimed victory in the battle
            if ($temp_battleinfo['target_player_result'] == 'victory'){
                //$temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_04" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 170px;">Mega Man</div>';
                $temp_console_markup .= '<p><strong class="player_type player_type_nature" style="display: block; padding: 1px 5px;">Congratulations! Your '.$temp_target_playerinfo['player_name'].' Claimed Victory in a Player Battle!</strong></p>';
                $temp_console_markup .= '<p>Another player by the name of <strong>'.$temp_this_playername.'</strong> challenged your '.$temp_target_playerinfo['player_name'].' to battle&hellip; and lost! Your team of robots were victorious!</p>';
                $temp_console_markup .= '<p>Your '.$temp_target_playerinfo['player_name'].' claimed victory in '.$temp_battleinfo['battle_turns'].' turns and as a reward for being such a tough opponent, collected <strong>'.number_format($temp_inc_zenny, 0, '.', ',').'</strong> zenny for his victory.</p>';
            }
            // Else if this player suffered defeat in the battle
            elseif ($temp_battleinfo['target_player_result'] == 'defeat'){
                //$temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_04" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 170px;">Mega Man</div>';
                $temp_console_markup .= '<p><strong class="player_type player_type_flame" style="display: block; padding: 1px 5px;">Condolences&hellip; Your '.$temp_target_playerinfo['player_name'].' Suffered Defeat in a Player Battle.</strong></p>';
                $temp_console_markup .= '<p>Another player by the name of <strong>'.$temp_this_playername.'</strong> challenged your '.$temp_target_playerinfo['player_name'].' to battle&hellip; and won! Your team of robots were defeated&hellip;</p>';
                $temp_console_markup .= '<p>Your '.$temp_target_playerinfo['player_name'].' avoided defeat for '.$temp_battleinfo['battle_turns'].' turns and as a reward for being such a good sport, collected <strong>'.number_format($temp_inc_zenny, 0, '.', ',').'</strong> zenny for his participation.</p>';
            }

        // Display the rest of the reward message
        if ($battle_complete_counter_light >= 29){ $temp_console_markup .= '<p>Now that you\'ve unlocked the Player Battle bonus mode, you too can challenge other players and earn even more experience and battle points!</p>'; }
        else { $temp_console_markup .= '<p>Complete all of '.$temp_target_playerinfo['player_name'].'\'s missions to beat the game and you too can challenge other players to battle for tons of experience and more battle points!</p><p>The phrase &quot;Chapter&nbsp;Get&nbsp;:&nbsp;Player&nbsp;Battles&quot; might also be of some use&hellip;</p>'; }

        // Add this mesage to the event session IF VICTORY (Only good news now!)
        if ($temp_battleinfo['target_player_result'] == 'victory'){
            array_unshift($_SESSION[$session_token]['EVENTS'], array(
                'canvas_markup' => $temp_canvas_markup,
                'console_markup' => $temp_console_markup
                ));
        }

        // DEBUG DEBUG DEBUG

        // Increment this user and player's battle zenny counters
        $_SESSION[$session_token]['counters']['battle_zenny'] += $temp_inc_zenny;

        // Update the leaderboard to remove the pending points and add them to the totals
        $db->update('mmrpg_battles', array(
            'target_reward_pending' => 0
            ), "battle_id = {$temp_battleinfo['battle_id']}");

    }

}



?>