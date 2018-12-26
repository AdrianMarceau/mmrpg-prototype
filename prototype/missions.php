<?
/*
 * DEMO MISSION SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){

    // DEBUG
    //echo 'checkpoint_'.__LINE__.'<br />';

    // Only print out Light's data if conditions allow or do not exist
    if (empty($this_data_condition) || in_array('this_player_token=dr-light', $this_data_condition)){

        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';

        // Print out the demo mode's mission select screen for Dr. Light
        echo '<div class="option_wrapper option_wrapper_hidden '.($prototype_data['demo']['battles_complete'] >= 4 ? 'option_wrapper_complete ' : 'option_wrapper_default ').'" data-condition="this_player_token=dr-light" data-music="'.$prototype_data['demo']['missions_music'].'">'."\n";
        echo $prototype_data['demo']['missions_markup']."\n";
        echo '<a class="option option_1x4 option_spacer" style="visibility: hidden;">&nbsp;</a>'."\n";
        echo '</div>'."\n";

    }

}
/*
 * NORMAL MISSION SELECT
 */
else {

    // DEBUG
    //echo 'checkpoint_'.__LINE__.'<br />';

    // Define an inline function for generating mission select buttons for a given player
    function temp_generate_player_mission_markup($player_token, &$chapters_unlocked, &$prototype_data){
        global $session_token;
        global $prototype_start_link;
        $battle_complete_counter = mmrpg_prototype_battles_complete($player_token);

        // DEBUG
        //echo 'checkpoint_'.__LINE__.('$prototype_start_link = '.$prototype_start_link.'').'<br />';

        // Generate the two types of player toke
        $ptoken = str_replace('dr-', '', $player_token);
        $player_token = 'dr-'.$ptoken;

        // Start generating chapter markup
        $chapters_display_count = 0;
        $chapters_display_markup = '';
        ob_start();

            /* -- Calculate Chapter Unlocks -- */

            if ($chapters_unlocked['7']
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_7'])){
                unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_7'] = true;
            }

            if ($chapters_unlocked['6']
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_6'])){
                unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_6'] = true;
            }

            if ($chapters_unlocked['5']
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_5'])){
                unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_5'] = true;
            }

            if ($chapters_unlocked['4a']
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_4'])){
                unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_4'] = true;
            }

            if ($chapters_unlocked['3']
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_3'])){
                unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_3'] = true;
            }

            if ($chapters_unlocked['2']
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_2'])){
                unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_2'] = true;
            }

            if ($chapters_unlocked['1']
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_1'])){
                unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_1'] = true;
            }

            if ($chapters_unlocked['0']
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_0'])){
                unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_0'] = true;
            }

            if (isset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter'])){
                $temp_last_chapter = $_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter'];
            } else {
                $temp_last_chapter = false;
            }

            if ($temp_last_chapter === false){
                if ($chapters_unlocked['7']){ $temp_last_chapter = '7'; }
                elseif ($chapters_unlocked['6']){ $temp_last_chapter = '6'; }
                elseif ($chapters_unlocked['5']){ $temp_last_chapter = '5'; }
                elseif ($chapters_unlocked['4a']){ $temp_last_chapter = '4'; }
                elseif ($chapters_unlocked['3']){ $temp_last_chapter = '3'; }
                elseif ($chapters_unlocked['2']){ $temp_last_chapter = '2'; }
                elseif ($chapters_unlocked['1']){ $temp_last_chapter = '1'; }
                elseif ($chapters_unlocked['0']){ $temp_last_chapter = '0'; }
            }

            /* -- Generate Chapter Links -- */

            // Collect the current star count for everyone
            $battle_star_counter = mmrpg_prototype_stars_unlocked();

            // CHAPTER ONE(0) Intro
            if ($chapters_unlocked['0']){
                $chapters_display_count++;
                echo '<a class="chapter_link '.($temp_last_chapter === '0' ? 'chapter_link_active ' : '').'" href="#" data-chapter="0">Chapter 1</a>';
            } else {
                $chapters_display_count++;
                echo '<a class="chapter_link chapter_link_disabled">???</a>';
            }

            // CHAPTER TWO(1) Masters
            if ($chapters_unlocked['1']){
                $chapters_display_count++;
                echo '<a class="chapter_link '.($temp_last_chapter === '1' ? 'chapter_link_active ' : '').'" href="#" data-chapter="1">Chapter 2</a>';
            } else {
                $chapters_display_count++;
                echo '<a class="chapter_link chapter_link_disabled">???</a>';
            }

            // CHAPTER THREE(2) Rivals
            if ($chapters_unlocked['2']){
                $chapters_display_count++;
                echo '<a class="chapter_link '.($temp_last_chapter === '2' ? 'chapter_link_active ' : '').'" href="#" data-chapter="2">Chapter 3</a>';
            } else {
                $chapters_display_count++;
                echo '<a class="chapter_link chapter_link_disabled">???</a>';
            }

            // CHAPTER FOUR(3) Fusions
            if ($chapters_unlocked['3']){
                $chapters_display_count++;
                echo '<a class="chapter_link '.($temp_last_chapter === '3' ? 'chapter_link_active ' : '').'" href="#" data-chapter="3">Chapter 4</a>';
            } else {
                $chapters_display_count++;
                if ($battle_star_counter > 0 && $chapters_unlocked['2'] && $battle_complete_counter > MMRPG_SETTINGS_CHAPTER2_MISSIONCOUNT){
                    $text = '<span>'.
                    '&#9733; &times; '.MMRPG_SETTINGS_CHAPTER4_STARLOCK.
                    '</span>';
                } else {
                    $text = '???';
                }
                echo '<a class="chapter_link chapter_link_disabled">'.$text.'</a>';
            }

            // CHAPTER FIVE(4a-c) Finals
            if ($chapters_unlocked['4a']){
                $chapters_display_count++;
                echo '<a class="chapter_link '.($temp_last_chapter === '4' ? 'chapter_link_active ' : '').'" href="#" data-chapter="4">Chapter 5</a>';
            } else {
                $chapters_display_count++;
                if ($battle_star_counter > 0 && $chapters_unlocked['3']){
                    $text = '<span>'.
                    '&#9733; &times; '.MMRPG_SETTINGS_CHAPTER5_STARLOCK.
                    '</span>';
                } else {
                    $text = '???';
                }
                echo '<a class="chapter_link chapter_link_disabled">'.$text.'</a>';
            }

            // Pre-check to see how many extra tabs to add
            $num_extra = 0;
            $num_extra += $allow_player_battles = $chapters_unlocked['6'] ? 1 : 0;
            $num_extra += $allow_bonus_fields = $chapters_unlocked['5'] ? 1 : 0;
            $num_extra += $allow_star_fields = $chapters_unlocked['7'] ? 1 : 0;

            // CHAPTER STARS(7)
            if ($allow_star_fields){
                $chapters_display_count++;
                if ($battle_star_counter >= MMRPG_SETTINGS_STARFORCE_CURRENTMAX){ echo '<a class="chapter_link extra chapter_link_disabled"><del>Stars</del></a>'; }
                else { echo '<a class="chapter_link extra stars '.($temp_last_chapter === '7' ? 'chapter_link_active ' : '').'" href="#" data-chapter="7">Stars</a>'; }
                } elseif ($num_extra > 0){
                $chapters_display_count++;
                echo '<a class="chapter_link extra chapter_link_disabled">???</a>';
                }

            // CHAPTER PLAYER(6)
            if ($allow_player_battles){
                $chapters_display_count++;
                echo '<a class="chapter_link extra players '.($temp_last_chapter === '6' ? 'chapter_link_active ' : '').'" href="#" data-chapter="6">Players</a>';
                } elseif ($num_extra > 0){
                $chapters_display_count++;
                echo '<a class="chapter_link extra chapter_link_disabled">???</a>';
                }

            // CHAPTER BONUS(5)
            if ($allow_bonus_fields){
                $chapters_display_count++;
                echo '<a class="chapter_link extra bonus '.($temp_last_chapter === '5' ? 'chapter_link_active ' : '').'" href="#" data-chapter="5">Random</a>';
                } elseif ($num_extra > 0){
                $chapters_display_count++;
                echo '<a class="chapter_link extra chapter_link_disabled">???</a>';
                }

        // Collect generated chapter markup
        $chapters_display_markup = ob_get_clean();

        // Start the output buffer again to collect final markup
        ob_start();

            echo '<div class="option_wrapper option_wrapper_missions option_wrapper_hidden '.($prototype_data[$player_token]['prototype_complete'] ? 'option_wrapper_complete ' : 'option_wrapper_default ').'" data-condition="this_player_token='.$player_token.'" data-music="'.$prototype_data[$player_token]['missions_music'].'">'."\n";
            echo '<div class="chapter_select chapter_select_'.$chapters_display_count.'" data-player="'.$ptoken.'">';
                echo $chapters_display_markup;
            echo '</div>';
            if ($prototype_start_link == 'home' && !defined('MMRPG_SCRIPT_REQUEST') && empty($_SESSION[$session_token]['battle_settings']['this_player_token'])){
                // DEBUG
                //echo 'checkpoint_'.__LINE__.'<br />';

                echo $prototype_data[$player_token]['missions_markup']."\n";
            }
            elseif ($prototype_start_link == 'home' && !defined('MMRPG_SCRIPT_REQUEST') && !empty($_SESSION[$session_token]['battle_settings']['this_player_token']) && $_SESSION[$session_token]['battle_settings']['this_player_token'] == $player_token){
                // DEBUG
                //echo 'checkpoint_'.__LINE__.'<br />';

                echo $prototype_data[$player_token]['missions_markup']."\n";
            }
            elseif (defined('MMRPG_SCRIPT_REQUEST')){
                // DEBUG
                //echo 'checkpoint_'.__LINE__.'<br />';

                echo $prototype_data[$player_token]['missions_markup']."\n";
            }
            else {
                // DEBUG
                //echo 'checkpoint_'.__LINE__.'<br />';

                echo '';
            }
            echo '<a class="option option_1x4 option_spacer" style="visibility: hidden;">&nbsp;</a>'."\n";
            echo '</div>'."\n";

        // Collect the final markup and return
        $player_missions_markup = ob_get_clean();
        return $player_missions_markup;

    }


    // Only print out Light's data if conditions allow or do not exist
    if (empty($this_data_condition) || in_array('this_player_token=dr-light', $this_data_condition)){

        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';

        // Print out the normal mode's mission select screen for Dr. Light
        if ($unlock_flag_light){

            // Generate player mission markup and print out
            $mission_markup = temp_generate_player_mission_markup('dr-light', $chapters_unlocked_light, $prototype_data);
            echo $mission_markup;

        }

    }

    // Only print out Wily's data if conditions allow or do not exist
    if (empty($this_data_condition) || in_array('this_player_token=dr-wily', $this_data_condition)){

        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';

        // Print out the normal mode's mission select screen for Dr. Wily
        if ($unlock_flag_wily){

            // Generate player mission markup and print out
            $mission_markup = temp_generate_player_mission_markup('dr-wily', $chapters_unlocked_wily, $prototype_data);
            echo $mission_markup;

        }

    }

    // Only print out Cossack's data if conditions allow or do not exist
    if (empty($this_data_condition) || in_array('this_player_token=dr-cossack', $this_data_condition)){

        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';

        // Print out the normal mode's mission select screen for Dr. Cossack
        if ($unlock_flag_cossack){

            // Generate player mission markup and print out
            $mission_markup = temp_generate_player_mission_markup('dr-cossack', $chapters_unlocked_cossack, $prototype_data);
            echo $mission_markup;

        }

    }

}

?>