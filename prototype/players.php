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

    // Define the button size based on player count
    $this_button_size = '1x4';

    // Collect the player index
    $mmrpg_player_index = rpg_player::get_index();

    // Define a quick function for getting the current chapter text
    $get_current_chapter_text = function($player_token, $player_chapters_unlocked){
        //error_log('generating chapter text for '.$player_token);
        $text_chapter_number = '0';
        $is_post_game = false;
        $is_new_game_plus = false;
        if (mmrpg_prototype_new_game_plus()){ $is_new_game_plus = true; }
        if ($is_new_game_plus){ $is_post_game = mmrpg_prototype_complete_plus($player_token); }
        elseif (!$is_new_game_plus){ $is_post_game = mmrpg_prototype_complete($player_token); }
        //error_log('$is_post_game = '.($is_post_game ? 'true' : 'false'));
        //error_log('$is_new_game_plus = '.($is_new_game_plus ? 'true' : 'false'));
        if ($is_post_game){ $text_chapter_number = 'X'; }
        elseif ($player_chapters_unlocked['4a']){ $text_chapter_number = '5'; }
        elseif ($player_chapters_unlocked['3']){ $text_chapter_number = '4'; }
        elseif ($player_chapters_unlocked['2']){ $text_chapter_number = '3'; }
        elseif ($player_chapters_unlocked['1']){ $text_chapter_number = '2'; }
        elseif ($player_chapters_unlocked['0']){ $text_chapter_number = '1'; }
        $current_chapter_text = 'Chapter '.$text_chapter_number;
        if ($is_new_game_plus){ $current_chapter_text = 'NG+ '.$current_chapter_text; }
        //$current_chapter_text = 'Chapter '.($is_new_game_plus ? 'X +' : '').$text_chapter_number;
        //$current_chapter_text = str_replace('X +X', 'ZX', $current_chapter_text);
        //error_log('chapter text for '.$player_token.' is '.$current_chapter_text);
        return $current_chapter_text;
        };

    // Define a quick function for getting the current limit heart markup
    $get_current_limit_hearts = function($player_token, $player_chapters_unlocked){
        $max_hearts = 1;
        $extra_hearts = 0;
        $num_hearts = mmrpg_prototype_limit_hearts_earned($player_token, $max_hearts, $extra_hearts);
        $num_hearts_to_show = $num_hearts - $extra_hearts;
        $max_hearts_to_show = $max_hearts - $extra_hearts;
        $hearts_markup = '';
        $anti_hearts_markup = '';
        $extra_hearts_markup = '';
        for ($i = 1; $i <= $max_hearts_to_show; $i++){
            if ($num_hearts_to_show >= $i){ $hearts_markup .= '<i class="fa fas fa-heart"></i>'; }
            else { $anti_hearts_markup .= '<i class="fa fas fa-heart-broken"></i>'; }
        }
        if (!empty($extra_hearts)){
            for ($i = 1; $i <= $extra_hearts; $i++){
                $extra_hearts_markup .= '<i class="fa fas fa-heart"></i>';
            }
        }
        $limit_hearts_markup = '';
        if (!empty($extra_hearts_markup)){ $limit_hearts_markup .= '<span class="limit-hearts earned" style="font-size: 70%;">'.$extra_hearts_markup.'</span>'; }
        if (!empty($hearts_markup)){ $limit_hearts_markup .= '<span class="limit-hearts earned" style="font-size: 70%;">'.$hearts_markup.'</span>'; }
        if (!empty($anti_hearts_markup)){ $limit_hearts_markup .= '<span class="limit-hearts unclaimed" style="font-size: 40%; opacity: 0.6; position: relative; bottom: 2px; left: 2px; margin-right: 4px;">'.$anti_hearts_markup.'</span>'; }
        return $limit_hearts_markup;
        };

    // Collect any endless attack data so we can mess around with it
    $endless_attack_savedata = mmrpg_prototype_get_endless_sessions();

    // Print out the normal mode's player select screen for Dr. Light
    if ($unlock_flag_light){
        $doctor_token = 'dr-light';
        $doctor_info = $mmrpg_player_index[$doctor_token];
        $doctor_is_away = isset($endless_attack_savedata[$doctor_token]) ? true : false;
        $doctor_settings = mmrpg_prototype_player_settings($doctor_token);
        $doctor_image = !empty($doctor_settings['player_image']) ? $doctor_settings['player_image'] : $doctor_token;
        $doctor_sprite_path = 'images/players/'.$doctor_image.'/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $doctor_current_chapter = mmrpg_prototype_player_currently_selected_chapter($doctor_token);
        $text_robots_unlocked = $prototype_data[$doctor_token]['robots_unlocked'].' Robot'.($prototype_data[$doctor_token]['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($prototype_data[$doctor_token]['points_unlocked'], 0, '.', ',').' Point'.($prototype_data[$doctor_token]['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $prototype_data[$doctor_token]['battles_complete'].' Mission'.($prototype_data[$doctor_token]['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $prototype_data[$doctor_token]['prototype_complete'] ? true : false;
        $text_sprites_markup = '';
        $text_sprites_markup .= '<span class="sprite sprite_player sprite_40x40" style="top: -2px; right: 0; z-index: 60;">';
            $text_sprites_markup .= '<span class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url('.$doctor_sprite_path.');'.($doctor_is_away ? ' filter: brightness(0);' : '').'"></span>';
            if ($doctor_is_away){ $text_sprites_markup .= '<span class="endless"><i class="fa fas fa-infinity"></i></span>'; }
        $text_sprites_markup .= '</span>';
        $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites($doctor_token, $session_token);
        $text_sprites_markup = '<span class="battle_sprites">'.$text_sprites_markup.'</span>';
        //$text_player_music = mmrpg_prototype_get_player_mission_music($doctor_token, $session_token);
        $text_player_music = mmrpg_prototype_get_chapter_music($doctor_token, $doctor_current_chapter, $session_token);
        $text_player_chapter = $get_current_chapter_text($doctor_token, $chapters_unlocked_light);
        $text_player_hearts = $get_current_limit_hearts($doctor_token, $chapters_unlocked_light);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_this-'.$doctor_token.'-player-select option_'.$doctor_token.' block_1';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_light.'" class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="'.$doctor_info['player_id'].'">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">'.$doctor_info['player_name'].(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_'.$doctor_token.'-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Light Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Print out the normal mode's player select screen for Dr. Wily
    if ($unlock_flag_wily){
        $doctor_token = 'dr-wily';
        $doctor_info = $mmrpg_player_index[$doctor_token];
        $doctor_is_away = isset($endless_attack_savedata[$doctor_token]) ? true : false;
        $doctor_settings = mmrpg_prototype_player_settings($doctor_token);
        $doctor_image = !empty($doctor_settings['player_image']) ? $doctor_settings['player_image'] : $doctor_token;
        $doctor_sprite_path = 'images/players/'.$doctor_image.'/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $doctor_current_chapter = mmrpg_prototype_player_currently_selected_chapter($doctor_token);
        $text_robots_unlocked = $prototype_data[$doctor_token]['robots_unlocked'].' Robot'.($prototype_data[$doctor_token]['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($prototype_data[$doctor_token]['points_unlocked'], 0, '.', ',').' Point'.($prototype_data[$doctor_token]['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $prototype_data[$doctor_token]['battles_complete'].' Mission'.($prototype_data[$doctor_token]['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $prototype_data[$doctor_token]['prototype_complete'] ? true : false;
        $text_sprites_markup = '';
        $text_sprites_markup .= '<span class="sprite sprite_player sprite_40x40" style="top: -2px; right: 0; z-index: 60;">';
            $text_sprites_markup .= '<span class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url('.$doctor_sprite_path.');'.($doctor_is_away ? ' filter: brightness(0);' : '').'"></span>';
            if ($doctor_is_away){ $text_sprites_markup .= '<span class="endless"><i class="fa fas fa-infinity"></i></span>'; }
        $text_sprites_markup .= '</span>';
        $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites($doctor_token, $session_token);
        $text_sprites_markup = '<span class="battle_sprites">'.$text_sprites_markup.'</span>';
        //$text_player_music = mmrpg_prototype_get_player_mission_music($doctor_token, $session_token);
        $text_player_music = mmrpg_prototype_get_chapter_music($doctor_token, $doctor_current_chapter, $session_token);
        $text_player_chapter = $get_current_chapter_text($doctor_token, $chapters_unlocked_wily);
        $text_player_hearts = $get_current_limit_hearts($doctor_token, $chapters_unlocked_wily);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_this-'.$doctor_token.'-player-select option_'.$doctor_token.' block_1';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_wily.'" class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="'.$doctor_info['player_id'].'">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">'.$doctor_info['player_name'].(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_'.$doctor_token.'-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Light Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Print out the normal mode's player select screen for Dr. Cossack
    if ($unlock_flag_cossack){
        $doctor_token = 'dr-cossack';
        $doctor_info = $mmrpg_player_index[$doctor_token];
        $doctor_is_away = isset($endless_attack_savedata[$doctor_token]) ? true : false;
        $doctor_settings = mmrpg_prototype_player_settings($doctor_token);
        $doctor_image = !empty($doctor_settings['player_image']) ? $doctor_settings['player_image'] : $doctor_token;
        $doctor_sprite_path = 'images/players/'.$doctor_image.'/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $doctor_current_chapter = mmrpg_prototype_player_currently_selected_chapter($doctor_token);
        $text_robots_unlocked = $prototype_data[$doctor_token]['robots_unlocked'].' Robot'.($prototype_data[$doctor_token]['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($prototype_data[$doctor_token]['points_unlocked'], 0, '.', ',').' Point'.($prototype_data[$doctor_token]['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $prototype_data[$doctor_token]['battles_complete'].' Mission'.($prototype_data[$doctor_token]['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $prototype_data[$doctor_token]['prototype_complete'] ? true : false;
        $text_sprites_markup = '';
        $text_sprites_markup .= '<span class="sprite sprite_player sprite_40x40" style="top: -2px; right: 0; z-index: 60;">';
            $text_sprites_markup .= '<span class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url('.$doctor_sprite_path.');'.($doctor_is_away ? ' filter: brightness(0);' : '').'"></span>';
            if ($doctor_is_away){ $text_sprites_markup .= '<span class="endless"><i class="fa fas fa-infinity"></i></span>'; }
        $text_sprites_markup .= '</span>';
        $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites($doctor_token, $session_token);
        $text_sprites_markup = '<span class="battle_sprites">'.$text_sprites_markup.'</span>';
        //$text_player_music = mmrpg_prototype_get_player_mission_music($doctor_token, $session_token);
        $text_player_music = mmrpg_prototype_get_chapter_music($doctor_token, $doctor_current_chapter, $session_token);
        $text_player_chapter = $get_current_chapter_text($doctor_token, $chapters_unlocked_cossack);
        $text_player_hearts = $get_current_limit_hearts($doctor_token, $chapters_unlocked_cossack);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_this-'.$doctor_token.'-player-select option_'.$doctor_token.' block_1';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_cossack.'" class="'.$text_option_classes.'" data-token="'.$doctor_token.'" data-token-id="'.$doctor_info['player_id'].'">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">'.$doctor_info['player_name'].(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_'.$doctor_token.'-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Light Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    }

}

?>