<?

/*
 * DEMO PLAYER SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){

    // Print the error message as the demo mode has been deprecated
    echo('<p style="font-weight: normal; color: #dedede; text-align: center; padding: 50px; font-size: 16px; line-height: 1.3;">');
        echo(rpg_battle::random_negative_word().' ');
        echo('It looks like you were logged out of your account! <br /> This was either due to inactivity or the result of an action in another tab. <br /> Please reload your game if you want to continue playing.');
    echo('</p>'.PHP_EOL);

}
/*
 * NORMAL PLAYER SELECT
 */
else {

    // Start the option wrapper for these buttons
    echo('<div class="option_wrapper option_wrapper_start">');

    // Define the button size based on player count
    $this_button_size = '1x4';

    // Collect the player index
    $mmrpg_player_index = rpg_player::get_index();
    //error_log('$mmrpg_player_index = '.print_r($mmrpg_player_index, true));

    // Define a quick function for getting the current chapter text
    $get_current_chapter_text = function($player_token, $player_chapters_unlocked){
        //error_log('generating chapter text for '.$player_token.' w/ '.print_r($player_chapters_unlocked, true));
        $text_chapter_number = '0';
        $is_post_game = !empty($player_chapters_unlocked['4z']) ? true : false;
        $is_new_game_plus = mmrpg_prototype_new_game_plus($player_token) ? true : false;
        //$is_post_game = mmrpg_prototype_complete($player_token) ? true : false;
        //if ($is_new_game_plus && !$player_chapters_unlocked['4z']){ $is_post_game = false; }
        //$is_post_game = false;
        //$is_new_game_plus = false;
        //if (mmrpg_prototype_new_game_plus()){ $is_new_game_plus = true; }
        //if ($is_new_game_plus){ $is_post_game = mmrpg_prototype_complete_plus($player_token); }
        //elseif (!$is_new_game_plus){ $is_post_game = mmrpg_prototype_complete($player_token); }
        //error_log($player_token.'::$is_new_game_plus = '.($is_new_game_plus ? 'true' : 'false'));
        //error_log($player_token.'::$is_post_game = '.($is_post_game ? 'true' : 'false'));
        if ($is_post_game){ $text_chapter_number = 'X'; }
        elseif ($player_chapters_unlocked['4a']){ $text_chapter_number = '5'; }
        elseif ($player_chapters_unlocked['3']){ $text_chapter_number = '4'; }
        elseif ($player_chapters_unlocked['2']){ $text_chapter_number = '3'; }
        elseif ($player_chapters_unlocked['1']){ $text_chapter_number = '2'; }
        elseif ($player_chapters_unlocked['0']){ $text_chapter_number = '1'; }
        if ($is_new_game_plus){ $text_chapter_number = '&plus;'.$text_chapter_number; }
        $current_chapter_text = 'Chapter '.$text_chapter_number;
        //if ($is_new_game_plus){ $current_chapter_text = 'NG+ '.$current_chapter_text; }
        //$current_chapter_text = 'Chapter '.($is_new_game_plus ? 'X +' : '').$text_chapter_number;
        //$current_chapter_text = str_replace('X +X', 'ZX', $current_chapter_text);
        //error_log('chapter text for '.$player_token.' is '.$current_chapter_text);
        return $current_chapter_text;
        };

    // Define a quick function for getting the current limit heart markup
    $get_current_limit_hearts = function($player_token, $heart_icon = 'heart', $no_heart_icon = 'heart-broken'){
        $max_hearts = 1;
        $extra_hearts = 0;
        $num_hearts = mmrpg_prototype_limit_hearts_earned($player_token, $max_hearts, $extra_hearts);
        $num_hearts_to_show = $num_hearts - $extra_hearts;
        $max_hearts_to_show = $max_hearts - $extra_hearts;
        $hearts_markup = '';
        $anti_hearts_markup = '';
        $extra_hearts_markup = '';
        for ($i = 1; $i <= $max_hearts_to_show; $i++){
            if ($num_hearts_to_show >= $i){ $hearts_markup .= '<i class="fa fas fa-'.$heart_icon.'"></i>'; }
            else { $anti_hearts_markup .= '<i class="fa fas fa-'.$no_heart_icon.'"></i>'; }
        }
        if (!empty($extra_hearts)){
            for ($i = 1; $i <= $extra_hearts; $i++){
                $extra_hearts_markup .= '<i class="fa fas fa-'.$heart_icon.'"></i>';
            }
        }
        $limit_hearts_markup = '';
        if (!empty($extra_hearts_markup)){ $limit_hearts_markup .= '<span class="limit-hearts earned" style="font-size: 70%;">'.$extra_hearts_markup.'</span>'; }
        if (!empty($hearts_markup)){ $limit_hearts_markup .= '<span class="limit-hearts earned" style="font-size: 70%;">'.$hearts_markup.'</span>'; }
        if (!empty($anti_hearts_markup)){ $limit_hearts_markup .= '<span class="limit-hearts unclaimed" style="font-size: 40%; opacity: 0.6; position: relative; bottom: 2px; left: 2px; margin-right: 4px;">'.$anti_hearts_markup.'</span>'; }
        return $limit_hearts_markup;
        };

    // Define an inline function for getting a given doctor's sprite
    $get_doctor_sprite_markup = function($doctor_token, $doctor_info, $doctor_sprite_path, $doctor_is_away)
        use($session_token) {
        $temp_anim_duration = rpg_player::get_css_animation_duration($doctor_info);
        $temp_anim_classes = '';
        $temp_anim_styles = ' animation-duration: '.$temp_anim_duration.'s;';
        $temp_away_styles = ($doctor_is_away ? ' filter: brightness(0);' : '');
        $text_sprites_markup = '';
        $text_sprites_markup .= '<span class="sprite sprite_player sprite_40x40" style="top: -2px; right: 0; z-index: 60;">';
            $text_sprites_markup .= '<span class="sprite sprite_40x40 sprite_40x40_base'.$temp_anim_classes.'" style="background-image: url('.$doctor_sprite_path.');'.$temp_away_styles.$temp_anim_styles.'"></span>';
            if ($doctor_is_away){ $text_sprites_markup .= '<span class="endless"><i class="fa fas fa-infinity"></i></span>'; }
        $text_sprites_markup .= '</span>';
        $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites($doctor_token, $session_token, MMRPG_SETTINGS_MENUROBOTS_PERPLAYER);
        $text_sprites_markup = '<span class="battle_sprites">'.$text_sprites_markup.'</span>';
        return $text_sprites_markup;
        };

    // Collect any endless attack data so we can mess around with it
    $endless_attack_savedata = mmrpg_prototype_get_endless_sessions();

    // Define the button size for the first button
    $this_button_size = '1x4';

    // Print out the normal mode's player select screen for None (Free Roam)
    $doctor_token = 'player';
    $doctor_info = isset($mmrpg_player_index[$doctor_token]) ? $mmrpg_player_index[$doctor_token] : array();
    if (true){
        $heart_icons = array('dot-circle', 'draw-circle');
        $text_button_label = 'Free Roam'; // None (Free Roam)
        $text_player_hearts = $get_current_limit_hearts($doctor_token, $heart_icons[0], $heart_icons[1]);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' option_free-roam block_1 type_none';
        $last_world_token = !empty($_SESSION['WORLD']['last_world_token']) ? $_SESSION['WORLD']['last_world_token'] : '';
        $last_map_token = !empty($_SESSION['WORLD']['last_map_token']) ? $_SESSION['WORLD']['last_map_token'] : '';
        $text_world_location = 'Area Unknown'; // DOTNET Area 3
        if (!empty($last_world_token) && !empty($last_map_token)){
            $map_frags = explode('-', $last_map_token);
            $text_world_location = strtoupper($last_world_token).' // ';
            if (count($map_frags) === 2){ $text_world_location .= strtoupper($map_frags[0]).' '.ucfirst($map_frags[1]); }
            elseif (count($map_frags) >= 3){ $text_world_location .= strtoupper($map_frags[0]).' '.ucfirst($map_frags[1]).' '.strtoupper($map_frags[2]); }
        }
        $last_player_token = !empty($_SESSION['WORLD']['last_player_token']) ? $_SESSION['WORLD']['last_player_token'] : '';
        $last_player_sessions = !empty($_SESSION['WORLD']['player_sessions']) ? $_SESSION['WORLD']['player_sessions'] : array();
        $last_player_session = !empty($last_player_sessions[$last_player_token]) ? $last_player_sessions[$last_player_token] : array();
        //error_log('$last_player_token = '.print_r($last_player_token, true));
        //error_log('$last_player_sessions = '.print_r($last_player_sessions, true));
        //error_log('$last_player_session = '.print_r($last_player_session, true));
        if (!empty($last_player_token) && !empty($last_player_session)){
            $last_player_info = isset($mmrpg_player_index[$last_player_token]) ? $mmrpg_player_index[$last_player_token] : false;
            $last_player_name = !empty($last_player_info) && !empty($last_player_info['player_name']) ? $last_player_info['player_name'] : false;
            //error_log('$last_player_info = '.print_r($last_player_info, true));
            //error_log('$last_player_name = '.print_r($last_player_name, true));
            //$last_world_token = $last_player_session['last_world_token'];
            //$last_world_name = str_replace(' AREA ', ' Area ', strtoupper(str_replace('-', ' ', $last_world_token)));
            //$last_world_name = strtoupper(str_replace('-', ' ', $last_world_token));
            //$text_world_location = $last_world_name.(!empty($last_player_name) ? ' ('.$last_player_name.')' : '');
            if (!empty($last_player_name)){ $text_world_location .= ' ('.$last_player_name.')'; }
        }
        echo '<a class="'.$text_option_classes.'"  data-token="'.$doctor_token.'" data-token-id="0" data-next-href="world.php">';
            echo '<div class="platform"><div class="chrome"><div class="inset">';
                echo '<label class="has_image"><span class="multi"><span class="maintext">'.$text_button_label.'</span><span class="subtext">'.$text_world_location.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
            echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Define the button size for the next group of buttons
    $this_button_size = '1x2';

    // Define an empty template for when prototype data doesn't exist for a doctor
    $template_prototype_data = array('robots_unlocked' => 0, 'points_unlocked' => 0, 'battles_complete' => 0, 'prototype_complete' => false);

    // Print out the normal mode's player select screen for Dr. LaLinde
    $doctor_token = 'dr-lalinde';
    $doctor_info = isset($mmrpg_player_index[$doctor_token]) ? $mmrpg_player_index[$doctor_token] : array();
    if ($unlock_flag_lalinde){
        $doctor_is_away = isset($endless_attack_savedata[$doctor_token]) ? true : false;
        $doctor_settings = mmrpg_prototype_player_settings($doctor_token);
        $doctor_image = !empty($doctor_settings['player_image']) ? $doctor_settings['player_image'] : $doctor_token;
        $doctor_sprite_path = 'images/players/'.$doctor_image.'/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $doctor_current_chapter = mmrpg_prototype_player_currently_selected_chapter($doctor_token);
        $doctor_prototype_data = isset($prototype_data[$doctor_token]) ? $prototype_data[$doctor_token] : $template_prototype_data;
        $text_robots_unlocked = $doctor_prototype_data['robots_unlocked'].' Robot'.($doctor_prototype_data['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($doctor_prototype_data['points_unlocked'], 0, '.', ',').' Point'.($doctor_prototype_data['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $doctor_prototype_data['battles_complete'].' Mission'.($doctor_prototype_data['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $doctor_prototype_data['prototype_complete'] ? true : false;
        $text_sprites_markup = $get_doctor_sprite_markup($doctor_token, $doctor_info, $doctor_sprite_path, $doctor_is_away);
        //$text_player_music = mmrpg_prototype_get_player_mission_music($doctor_token, $session_token);
        $text_player_music = mmrpg_prototype_get_chapter_music($doctor_token, $doctor_current_chapter, $session_token);
        $text_player_chapter = $get_current_chapter_text($doctor_token, $chapters_unlocked_lalinde);
        $text_player_hearts = $get_current_limit_hearts($doctor_token);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_energy ';
        $text_button_label = str_replace('Dr. ', '', $doctor_info['player_name']).' Story';
        if (!empty($doctor_prototype_data['robots_unlocked'])){
            error_log('$doctor_prototype_data ('.$doctor_token.') = '.print_r($doctor_prototype_data, true));
            echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_lalinde.'" class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="'.$doctor_info['player_id'].'">';
                echo '<div class="platform"><div class="chrome"><div class="inset">';
                    echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">'.$text_button_label.(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_'.$doctor_token.'-complete" style="display: inline-block; position: relative; bottom: 2px;" title="LaLinde Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
                echo '</div></div></div>';
            echo '</a>'."\n";
        } else {
            $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_energy option_disabled';
            echo '<a class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="0">';
                echo '<div class="platform"><div class="chrome"><div class="inset">';
                    echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext"><s>'.$text_button_label.'</s></span><span class="subtext">&cross; Limit Heart Not Found</span><span class="subtext2">&cross; Init Core Not Found</span></span><span class="arrow">&nbsp;</span></label>';
                echo '</div></div></div>';
            echo '</a>'."\n";
        }
    } else {
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_energy option_disabled';
        echo '<a class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="0">';
            echo '<div class="platform"><div class="chrome"><div class="inset">';
                echo '<label class="has_image"><span class="multi"><span class="maintext">......</span><span class="subtext">...</span><span class="subtext2">...</span></span><span class="arrow">&nbsp;</span></label>';
            echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Print out the normal mode's player select screen for Dr. Light
    $doctor_token = 'dr-light';
    $doctor_info = isset($mmrpg_player_index[$doctor_token]) ? $mmrpg_player_index[$doctor_token] : array();
    if ($unlock_flag_light){
        $doctor_is_away = isset($endless_attack_savedata[$doctor_token]) ? true : false;
        $doctor_settings = mmrpg_prototype_player_settings($doctor_token);
        $doctor_image = !empty($doctor_settings['player_image']) ? $doctor_settings['player_image'] : $doctor_token;
        $doctor_sprite_path = 'images/players/'.$doctor_image.'/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $doctor_current_chapter = mmrpg_prototype_player_currently_selected_chapter($doctor_token);
        $doctor_prototype_data = isset($prototype_data[$doctor_token]) ? $prototype_data[$doctor_token] : $template_prototype_data;
        $text_robots_unlocked = $doctor_prototype_data['robots_unlocked'].' Robot'.($doctor_prototype_data['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($doctor_prototype_data['points_unlocked'], 0, '.', ',').' Point'.($doctor_prototype_data['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $doctor_prototype_data['battles_complete'].' Mission'.($doctor_prototype_data['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $doctor_prototype_data['prototype_complete'] ? true : false;
        $text_sprites_markup = $get_doctor_sprite_markup($doctor_token, $doctor_info, $doctor_sprite_path, $doctor_is_away);
        //$text_player_music = mmrpg_prototype_get_player_mission_music($doctor_token, $session_token);
        $text_player_music = mmrpg_prototype_get_chapter_music($doctor_token, $doctor_current_chapter, $session_token);
        $text_player_chapter = $get_current_chapter_text($doctor_token, $chapters_unlocked_light);
        $text_player_hearts = $get_current_limit_hearts($doctor_token);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_defense';
        $text_button_label = str_replace('Dr. ', '', $doctor_info['player_name']).' Story';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_light.'" class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="'.$doctor_info['player_id'].'">';
            echo '<div class="platform"><div class="chrome"><div class="inset">';
                echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">'.$text_button_label.(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_'.$doctor_token.'-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Light Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
            echo '</div></div></div>';
        echo '</a>'."\n";
    } else {
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_defense option_disabled';
        echo '<a class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="0">';
            echo '<div class="platform"><div class="chrome"><div class="inset">';
                echo '<label class="has_image"><span class="multi"><span class="maintext">......</span><span class="subtext">...</span><span class="subtext2">...</span></span><span class="arrow">&nbsp;</span></label>';
            echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Print out the normal mode's player select screen for Dr. Wily
    $doctor_token = 'dr-wily';
    $doctor_info = isset($mmrpg_player_index[$doctor_token]) ? $mmrpg_player_index[$doctor_token] : array();
    if ($unlock_flag_wily){
        $doctor_is_away = isset($endless_attack_savedata[$doctor_token]) ? true : false;
        $doctor_settings = mmrpg_prototype_player_settings($doctor_token);
        $doctor_image = !empty($doctor_settings['player_image']) ? $doctor_settings['player_image'] : $doctor_token;
        $doctor_sprite_path = 'images/players/'.$doctor_image.'/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $doctor_current_chapter = mmrpg_prototype_player_currently_selected_chapter($doctor_token);
        $doctor_prototype_data = isset($prototype_data[$doctor_token]) ? $prototype_data[$doctor_token] : $template_prototype_data;
        $text_robots_unlocked = $doctor_prototype_data['robots_unlocked'].' Robot'.($doctor_prototype_data['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($doctor_prototype_data['points_unlocked'], 0, '.', ',').' Point'.($doctor_prototype_data['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $doctor_prototype_data['battles_complete'].' Mission'.($doctor_prototype_data['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $doctor_prototype_data['prototype_complete'] ? true : false;
        $text_sprites_markup = $get_doctor_sprite_markup($doctor_token, $doctor_info, $doctor_sprite_path, $doctor_is_away);
        //$text_player_music = mmrpg_prototype_get_player_mission_music($doctor_token, $session_token);
        $text_player_music = mmrpg_prototype_get_chapter_music($doctor_token, $doctor_current_chapter, $session_token);
        $text_player_chapter = $get_current_chapter_text($doctor_token, $chapters_unlocked_wily);
        $text_player_hearts = $get_current_limit_hearts($doctor_token);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_attack';
        $text_button_label = str_replace('Dr. ', '', $doctor_info['player_name']).' Story';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_wily.'" class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="'.$doctor_info['player_id'].'">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">'.$text_button_label.(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_'.$doctor_token.'-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Light Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    } else {
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_attack option_disabled';
        echo '<a class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="0">';
            echo '<div class="platform"><div class="chrome"><div class="inset">';
                echo '<label class="has_image"><span class="multi"><span class="maintext">......</span><span class="subtext">...</span><span class="subtext2">...</span></span><span class="arrow">&nbsp;</span></label>';
            echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Print out the normal mode's player select screen for Dr. Cossack
    $doctor_token = 'dr-cossack';
    $doctor_info = isset($mmrpg_player_index[$doctor_token]) ? $mmrpg_player_index[$doctor_token] : array();
    if ($unlock_flag_cossack){
        $doctor_is_away = isset($endless_attack_savedata[$doctor_token]) ? true : false;
        $doctor_settings = mmrpg_prototype_player_settings($doctor_token);
        $doctor_image = !empty($doctor_settings['player_image']) ? $doctor_settings['player_image'] : $doctor_token;
        $doctor_sprite_path = 'images/players/'.$doctor_image.'/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $doctor_current_chapter = mmrpg_prototype_player_currently_selected_chapter($doctor_token);
        $doctor_prototype_data = isset($prototype_data[$doctor_token]) ? $prototype_data[$doctor_token] : $template_prototype_data;
        $text_robots_unlocked = $doctor_prototype_data['robots_unlocked'].' Robot'.($doctor_prototype_data['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($doctor_prototype_data['points_unlocked'], 0, '.', ',').' Point'.($doctor_prototype_data['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $doctor_prototype_data['battles_complete'].' Mission'.($doctor_prototype_data['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $doctor_prototype_data['prototype_complete'] ? true : false;
        $text_sprites_markup = $get_doctor_sprite_markup($doctor_token, $doctor_info, $doctor_sprite_path, $doctor_is_away);
        //$text_player_music = mmrpg_prototype_get_player_mission_music($doctor_token, $session_token);
        $text_player_music = mmrpg_prototype_get_chapter_music($doctor_token, $doctor_current_chapter, $session_token);
        $text_player_chapter = $get_current_chapter_text($doctor_token, $chapters_unlocked_cossack);
        $text_player_hearts = $get_current_limit_hearts($doctor_token);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_speed';
        $text_button_label = str_replace('Dr. ', '', $doctor_info['player_name']).' Story';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_cossack.'" class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="'.$doctor_info['player_id'].'">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">'.$text_button_label.(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_'.$doctor_token.'-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Light Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    } else {
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' block_1 type_speed option_disabled';
        echo '<a class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="0">';
            echo '<div class="platform"><div class="chrome"><div class="inset">';
                echo '<label class="has_image"><span class="multi"><span class="maintext">......</span><span class="subtext">...</span><span class="subtext2">...</span></span><span class="arrow">&nbsp;</span></label>';
            echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Define the button size for the final button
    $this_button_size = '1x4';

    // Print out the normal mode's player select screen for Proxy (Void Cauldron)
    if (!isset($unlock_flag_proxy)){ $unlock_flag_proxy = false; } // TODO: move this
    $doctor_token = 'proxy';
    $doctor_info = isset($mmrpg_player_index[$doctor_token]) ? $mmrpg_player_index[$doctor_token] : array();
    if (false && $unlock_flag_proxy){
        // TODO: update this so it actually does something
        //$maintext_string = 'Proxy Story';
        //$subtext_string = 'Rogue of Network // The Void Cauldron';
        //$subtext2_string = '[ <i class="fa fas">★★★★★☆☆☆☆☆</i> ]';
    } else {
        // Show progress toward unlocking this mode via vague icon usage
        // TODO: define a real unlock method for this instead of below
        //$num_robots_unlockable = 100; // TODO: hard-coded for now, but its been the same number for over a decade sooo....
        //$num_robots_unlocked = mmrpg_prototype_robots_unlocked();
        //if ($num_robots_unlocked > $num_robots_unlockable){ $num_robots_unlocked = $num_robots_unlockable; }
        //$num_star_icons_max = 10;
        //$num_star_icons_earned = floor(($num_robots_unlocked / $num_robots_unlockable) * $num_star_icons_max);
        $num_stars_unlocked = mmrpg_prototype_stars_unlocked();
        $num_stars_required = 100; // TODO: seems like a good number for a requirement but maybe don't hard-core forever
        $num_star_icons_max = 10; // this is just how many are displayed on-screen at once
        $num_star_icons_earned = floor(($num_stars_unlocked / $num_stars_required) * $num_star_icons_max);
        $num_star_icons_remaining = $num_star_icons_earned < $num_star_icons_max ? ($num_star_icons_max - $num_star_icons_earned) : 0;
        $star_icon_string = '';
        if (!empty($num_star_icons_earned)){ $star_icon_string .= str_repeat('★', $num_star_icons_earned); }
        if (!empty($num_star_icons_remaining)){ $star_icon_string .= str_repeat('☆', $num_star_icons_remaining); }
        $maintext_string = 'Proxy Story';
        $subtext_string = 'Rogue of Network // The Void Cauldron';
        $subtext2_string = '[ <i class="fa fas">'.$star_icon_string.'</i> ]';
        $maintext_string = preg_replace('/[^\s]/i', '-', $maintext_string);
        $subtext_string = preg_replace('/[^\s]/i', '-', $subtext_string);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_'.$doctor_token.' option_void-cauldron block_1 type_empty option_disabled';
        echo '<a class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="0">';
            echo '<div class="platform"><div class="chrome"><div class="inset">';
                echo '<label class="has_image"><span class="multi"><span class="maintext">'.$maintext_string.'</span><span class="subtext">'.$subtext_string.'</span><span class="subtext2">'.$subtext2_string.'</span></span><span class="arrow">&nbsp;</span></label>';
            echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // End the option wrapper for these buttons
    echo('</div>'.PHP_EOL);

}

?>