<?

// -- NEXT BATTLE ACTION -- //

// Pre-generate active robots string and save any buffs/debuffs/etc.
$active_robot_array = array();
$active_robot_array_first = array();
$_SESSION['ROBOTS_PRELOAD'] = array();
foreach ($this_player->values['robots_active'] AS $key => $robot){

    // Add this robot's ID + token to the list
    $robot_string = $robot['robot_id'].'_'.$robot['robot_token'];
    $active_robot_array[] = $robot_string;
    if (empty($active_robot_array_first)){
        $active_robot_array_first = array($robot['robot_id'], $robot['robot_token']);
    }

    // Save this robot's current energy, weapons, and attack/defense/speed mods
    $_SESSION['ROBOTS_PRELOAD'][$this_battle->battle_complete_redirect_token][$robot_string] = array(
        'robot_energy' => $robot['robot_energy'],
        'robot_weapons' => $robot['robot_weapons'],
        'robot_attack_mods' => $robot['counters']['attack_mods'],
        'robot_defense_mods' => $robot['counters']['defense_mods'],
        'robot_speed_mods' => $robot['counters']['speed_mods'],
        'robot_image' => $robot['robot_image'],
        'robot_item' => $robot['robot_item'],
        'robot_attachments' => $robot['robot_attachments']
        );

}
$active_robot_string = implode(',', $active_robot_array);

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();

// Generate the URL for the next mission with provided token
$next_missios_href = 'battle.php?wap='.($flag_wap ? 'true' : 'false');
$next_missios_href .= '&this_battle_id='.($this_battle->battle_id + 1);
$next_missios_href .= '&this_battle_token='.$this_battle->battle_complete_redirect_token;
$next_missios_href .= '&this_player_id='.$this_player->player_id;
$next_missios_href .= '&this_player_token='.$this_player->player_token;
$next_missios_href .= '&this_player_robots='.$active_robot_string;
$next_missios_href .= '&flag_skip_fadein=true';

// Redirect the user back to the next mission
$this_redirect = $next_missios_href;

/*

// Generate the URL for the next mission with provided token
$next_missios_href = 'battle_loop.php?wap='.($flag_wap ? 'true' : 'false');
$next_missios_href .= '&this_battle_id='.($this_battle->battle_id + 1);
$next_missios_href .= '&this_battle_token='.$this_battle->battle_complete_redirect_token;
$next_missios_href .= '&this_field_id='.$this_field_id;
$next_missios_href .= '&this_field_token='.$this_field_token;
$next_missios_href .= '&this_user_id='.$this_user_id;
$next_missios_href .= '&this_player_id='.$this_player->player_id;
$next_missios_href .= '&this_player_token='.$this_player->player_token;
$next_missios_href .= '&this_player_robots='.$active_robot_string;
$next_missios_href .= '&this_robot_id='.$active_robot_array_first[0];
$next_missios_href .= '&this_robot_token='.$active_robot_array_first[1];
$next_missios_href .= '&target_user_id='.$target_user_id;
$next_missios_href .= '&target_player_id='.$target_player_id;
$next_missios_href .= '&target_player_token='.$target_player_token;
$next_missios_href .= '&target_robot_id=auto';
$next_missios_href .= '&target_robot_token=auto';
$next_missios_href .= '&this_action=start';
$next_missios_href .= '&target_action=start';

// Redirect to a new battle loop with new targets
header('Location: '.$next_missios_href);
exit();

*/

?>