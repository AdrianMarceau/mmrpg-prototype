<?

// -- PROTOTYPE WORLD ACTION -- //

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();
$_SESSION['SKILLS'] = array();

// Redirect the user back to the prototype screen
$this_redirect = 'world.php?'.($flag_wap ? 'wap=true' : '');
if ($this_player && $this_player->player_token !== 'player'){
    $this_redirect .= '&player='.$this_player->player_token;
}

// As long as this is a non-competitive battle mode, we should save the team data to the history
if (!empty($this_player->player_token)
    && $this_player->player_token !== 'player'
    && empty($this_battle->flags['challenge_battle'])
    && empty($this_battle->flags['endless_battle'])){

    // If the player token is empty, we can't save the team data
    $session_token = rpg_game::session_token();
    //error_log('debug in '.basename(__FILE__).' on line '.__LINE__.' : we should save this player\'s team data to the history');
    //error_log('$this_player->player_token = '.print_r($this_player->player_token, true));
    //error_log('$this_player->player_robots = '.print_r($this_player->player_robots, true));
    $battle_history = !empty($_SESSION[$session_token]['values']['battle_history']) ? $_SESSION[$session_token]['values']['battle_history'] : array();
    if (!isset($battle_history[$this_player->player_token])){ $battle_history[$this_player->player_token] = array(); }
    if (!isset($battle_history[$this_player->player_token]['robots_summoned'])){ $battle_history[$this_player->player_token]['robots_summoned'] = array(); }
    $robots_summoned_history = $battle_history[$this_player->player_token]['robots_summoned'];
    $robots_summoned_tokens = array_map(function($robot){ return $robot['robot_token']; }, $this_player->player_robots);
    //error_log('$robots_summoned_history = '.print_r($robots_summoned_history, true));
    //error_log('$robots_summoned_tokens = '.print_r($robots_summoned_tokens, true));
    if (!empty($robots_summoned_tokens)){
        $robots_summoned_history = array_unique(array_merge($robots_summoned_tokens, $robots_summoned_history));
        //error_log('(new) $robots_summoned_history = '.print_r($robots_summoned_history, true));
        $battle_history[$this_player->player_token]['robots_summoned'] = $robots_summoned_history;
    }
    //error_log('(new) $battle_history = '.print_r($battle_history, true));
    $_SESSION[$session_token]['values']['battle_history'] = $battle_history;

    // Make sure we also save the robot's current damage, used-ammo, etc. values to the world session
    rpg_world::init_session();
    $WORLD_SESSION = &$_SESSION['WORLD'];
    $WORLD_ROBOT_SESSIONS = &$WORLD_SESSION['robot_sessions'];
    //error_log('Saving robot sessions for player '.$this_player->player_token.PHP_EOL.' w/ $this_player->player_robots = '.print_r($this_player->player_robots, true));
    foreach ($this_player->player_robots AS $robot_key => $robot_info){
        $robot_token = $robot_info['robot_token'];
        if (!isset($WORLD_ROBOT_SESSIONS[$robot_token])){ $WORLD_ROBOT_SESSIONS[$robot_token] = array(); }
        $robot_session = &$WORLD_ROBOT_SESSIONS[$robot_token];
        $robot_session['energy'] = $robot_info['robot_energy'] - $robot_info['robot_base_energy'];
        $robot_session['weapons'] = $robot_info['robot_weapons'] - $robot_info['robot_base_weapons'];
        $robot_session['attack'] = !empty($robot_info['counters']['attack_mods']) ? $robot_info['counters']['attack_mods'] : 0;
        $robot_session['defense'] = !empty($robot_info['counters']['defense_mods']) ? $robot_info['counters']['defense_mods'] : 0;
        $robot_session['speed'] = !empty($robot_info['counters']['speed_mods']) ? $robot_info['counters']['speed_mods'] : 0;
        //error_log('Saving robot session for '.$robot_token.' : '.print_r($robot_session, true));
        $WORLD_ROBOT_SESSIONS[$robot_token] = $robot_session;
    }

}

// If this battle was completed, check to see if we should be running any post-complete actions
if ($this_battle->battle_status == 'complete'){

    // This battle was completed so check to see if we should remove it from the index
    //error_log('Prototype battle '.$this_battle->battle_token.' completed by '.$this_player->player_token, 0);
    //error_log('Battle info = '.print_r($this_battle->battle_info, true), 0);
    if (!empty($this_battle->flags['remove_on_complete'])){
        //error_log('Removing prototype battle '.$this_battle->battle_token.' from index', 0);
        rpg_battle::unset_index_info($this_battle->battle_token);
        if (!empty($this_battle->values['multi_battle_tokens'])){
            foreach ($this_battle->values['multi_battle_tokens'] AS $remove_battle_token){
                rpg_battle::unset_index_info($remove_battle_token);
            }
        }
    }

}

?>