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

    /*
    // Define the button size based on player count
    $this_button_size = '1x4';

    // Print out the normal mode's player select screen for Dr. Light
    if ($unlock_flag_light){
        $doctor_info = $mmrpg_player_index['dr-light'];
        $text_robots_unlocked = $prototype_data['demo']['robots_unlocked'].' Robot'.($prototype_data['demo']['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($prototype_data['demo']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['demo']['points_unlocked'] != 1 ? 's' : '');
        $text_player_hearts = ucfirst($doctor_info['player_type']).' +'.$doctor_info['player_'.$doctor_info['player_type']].'%';
        $text_battles_complete = $prototype_data['demo']['battles_complete'].' Mission'.($prototype_data['demo']['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $prototype_data['demo']['battles_complete'] >= 4 ? true : false;
        $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url(images/players/dr-light/sprite_right_40x40.png); top: -2px; right: 14px; z-index: 10;">Dr. Light</span>';
        $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites('dr-light', $session_token);
        $text_player_music = 'player-select';
        $text_player_chapter = $text_robots_unlocked;
        if ($prototype_data['demo']['battles_complete'] > 0){ $text_player_chapter .= ' <span class="pipe" style="color: #616161;">|</span> '.($prototype_data['demo']['battles_complete'] == 1 ? '1 Mission' : $prototype_data['demo']['battles_complete'].' Missions'); }
        //if ($ability_counter_light > 0){ $text_player_chapter .= ' <span class="pipe" style="color: #616161;">|</span> '.($ability_counter_light == 1 ? '1 Ability' : $ability_counter_light.' Abilities'); }
        //if ($star_counter_light > 0){ $text_player_chapter .= ' <span class="pipe" style="color: #616161;">|</span> '.($star_counter_light == 1 ? '1 Star' : $star_counter_light.' Stars'); }
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_light.'" class="option option_'.$this_button_size.' option_this-player-select option_this-dr-light-player-select option_dr-light block_1" data-token="dr-light">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">Dr. Light'.(!empty($text_player_special) ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! :D">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_hearts.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    }
    */

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
        $text_chapter_number = '0';
        if (mmrpg_prototype_complete($player_token)){ $text_chapter_number = 'X'; }
        elseif ($player_chapters_unlocked['4a']){ $text_chapter_number = '5'; }
        elseif ($player_chapters_unlocked['3']){ $text_chapter_number = '4'; }
        elseif ($player_chapters_unlocked['2']){ $text_chapter_number = '3'; }
        elseif ($player_chapters_unlocked['1']){ $text_chapter_number = '2'; }
        elseif ($player_chapters_unlocked['0']){ $text_chapter_number = '1'; }
        $current_chapter_text = 'Chapter '.$text_chapter_number;
        return $current_chapter_text;
        };

    // Define a quick function for getting the current limit heart markup
    $get_current_limit_hearts = function($player_token, $player_chapters_unlocked){
        $max_hearts = 1;
        $num_hearts = mmrpg_prototype_limit_hearts_earned($player_token, $max_hearts);
        $hearts_markup = '';
        $anti_hearts_markup = '';
        for ($i = 1; $i <= $max_hearts; $i++){
            if ($num_hearts >= $i){ $hearts_markup .= '<i class="fa fas fa-heart"></i>'; }
            else { $anti_hearts_markup .= '<i class="fa fas fa-heart-broken"></i>'; }
        }
        $limit_hearts_markup = '';
        $limit_hearts_markup .= '<span class="limit-hearts earned" style="font-size: 70%;">'.$hearts_markup.'</span>';
        $limit_hearts_markup .= '<span class="limit-hearts unclaimed" style="font-size: 40%; opacity: 0.6; position: relative; bottom: 2px; left: 2px;">'.$anti_hearts_markup.'</span>';
        return $limit_hearts_markup;
        };


    // Print out the normal mode's player select screen for Dr. Light
    if ($unlock_flag_light){
        $doctor_info = $mmrpg_player_index['dr-light'];
        $doctor_sprite_path = 'images/players/dr-light/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $text_robots_unlocked = $prototype_data['dr-light']['robots_unlocked'].' Robot'.($prototype_data['dr-light']['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($prototype_data['dr-light']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['dr-light']['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $prototype_data['dr-light']['battles_complete'].' Mission'.($prototype_data['dr-light']['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $prototype_data['dr-light']['prototype_complete'] ? true : false;
        $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url('.$doctor_sprite_path.'); top: -2px; right: 14px; z-index: 60;">Dr. Light</span>';
        $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites('dr-light', $session_token);
        $text_player_music = mmrpg_prototype_get_player_mission_music('dr-light', $session_token);
        $text_player_chapter = $get_current_chapter_text('dr-light', $chapters_unlocked_light);
        $text_player_hearts = $get_current_limit_hearts('dr-light', $chapters_unlocked_light);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_this-dr-light-player-select option_dr-light block_1';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_light.'" class="'.$text_option_classes.'" data-token="dr-light" data-token-id="'.$doctor_info['player_id'].'">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">Dr. Light'.(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_dr-light-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Light Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Print out the normal mode's player select screen for Dr. Wily
    if ($unlock_flag_wily){
        $doctor_info = $mmrpg_player_index['dr-wily'];
        $doctor_sprite_path = 'images/players/dr-wily/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $text_robots_unlocked = $prototype_data['dr-wily']['robots_unlocked'].' Robot'.($prototype_data['dr-wily']['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($prototype_data['dr-wily']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['dr-wily']['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $prototype_data['dr-wily']['battles_complete'].' Mission'.($prototype_data['dr-light']['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $prototype_data['dr-wily']['prototype_complete'] ? true : false;
        $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url('.$doctor_sprite_path.'); top: -2px; right: 14px; z-index: 60;">Dr. Wily</span>';
        $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites('dr-wily', $session_token);
        $text_player_music = mmrpg_prototype_get_player_mission_music('dr-wily', $session_token);
        $text_player_chapter = $get_current_chapter_text('dr-wily', $chapters_unlocked_wily);
        $text_player_hearts = $get_current_limit_hearts('dr-wily', $chapters_unlocked_wily);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_this-dr-wily-player-select option_dr-wily block_1';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_wily.'" class="'.$text_option_classes.'" data-token="dr-wily" data-token-id="'.$doctor_info['player_id'].'">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image">'.$text_sprites_markup.'<span class="multi"><span class="maintext">Dr. Wily'.(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_dr-wily-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Wily Campaign Complete!" data-tooltip-type="player_type player_type_attack">&clubs;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    }

    // Print out the normal mode's player select screen for Dr. Cossack
    if ($unlock_flag_cossack){
        $doctor_info = $mmrpg_player_index['dr-cossack'];
        $doctor_sprite_path = 'images/players/dr-cossack/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $text_robots_unlocked = $prototype_data['dr-cossack']['robots_unlocked'].' Robot'.($prototype_data['dr-cossack']['robots_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($prototype_data['dr-cossack']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['dr-cossack']['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = $prototype_data['dr-cossack']['battles_complete'].' Mission'.($prototype_data['dr-light']['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $prototype_data['dr-cossack']['prototype_complete'] ? true : false;
        $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url('.$doctor_sprite_path.'); top: -2px; right: 14px; z-index: 60;">Dr. Cossack</span>';
        $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites('dr-cossack', $session_token);
        $text_player_music = mmrpg_prototype_get_player_mission_music('dr-cossack', $session_token);
        $text_player_chapter = $get_current_chapter_text('dr-cossack', $chapters_unlocked_cossack);
        $text_player_hearts = $get_current_limit_hearts('dr-cossack', $chapters_unlocked_cossack);
        $text_option_classes = 'option option_'.$this_button_size.' option_this-player-select option_this-dr-cossack-player-select option_dr-cossack block_1';
        echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_cossack.'" class="'.$text_option_classes.'" data-token="dr-cossack" data-token-id="'.$doctor_info['player_id'].'">';
        echo '<div class="platform"><div class="chrome"><div class="inset">';
        echo '<label class="has_image">'.$text_sprites_markup.'<span class="multi"><span class="maintext">Dr. Cossack'.(!empty($text_player_special) ? ' <span class="sprite achievement_icon achievement_dr-cossack-complete" style="display: inline-block; position: relative; bottom: 2px;" title="Cossack Campaign Complete!" data-tooltip-type="player_type player_type_speed">&diams;</span>' : '').'</span><span class="subtext">'.$text_player_chapter.'</span><span class="subtext2">'.$text_player_hearts.'</span></span><span class="arrow">&#9658;</span></label>';
        echo '</div></div></div>';
        echo '</a>'."\n";
    }

}

?>