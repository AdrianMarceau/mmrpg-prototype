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
        //echo $prototype_data['demo']['missions_markup']."\n";
        echo('<p style="font-weight: normal; color: #dedede; text-align: center; padding: 50px; font-size: 16px; line-height: 1.3;">');
            echo(rpg_battle::random_negative_word().' ');
            echo('It looks like you were logged out of your account! <br /> This was either due to inactivity or the result of an action in another tab. <br /> Please reload your game if you want to continue playing.');
        echo('</p>'.PHP_EOL);
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
        $this_rogue_star = mmrpg_prototype_get_current_rogue_star();

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

            if ($chapters_unlocked['8']  // Challenges
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_8'])){
                //unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_8'] = true;
            }

            if ($chapters_unlocked['7']  // Stars
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_7'])){
                //unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_7'] = true;
            }

            if ($chapters_unlocked['6']  // Players
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_6'])){
                //unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
                $_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_6'] = true;
            }

            if ($chapters_unlocked['5']  // Random
                && empty($_SESSION[$session_token]['battle_settings']['flags'][$ptoken.'_unlocked_chapter_5'])){
                //unset($_SESSION[$session_token]['battle_settings'][$ptoken.'_current_chapter']);
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
                //if ($chapters_unlocked['7']){ $temp_last_chapter = '7'; }
                //elseif ($chapters_unlocked['6']){ $temp_last_chapter = '6'; }
                //elseif ($chapters_unlocked['5']){ $temp_last_chapter = '5'; }
                //elseif ($chapters_unlocked['4a']){ $temp_last_chapter = '4'; }
                if ($chapters_unlocked['4a']){ $temp_last_chapter = '4'; }
                elseif ($chapters_unlocked['3']){ $temp_last_chapter = '3'; }
                elseif ($chapters_unlocked['2']){ $temp_last_chapter = '2'; }
                elseif ($chapters_unlocked['1']){ $temp_last_chapter = '1'; }
                elseif ($chapters_unlocked['0']){ $temp_last_chapter = '0'; }
            }

            /* -- Generate Chapter Links -- */

            // Collect the current star count for everyone
            $battle_star_counter = mmrpg_prototype_stars_unlocked();

            // Define markup for the player's current chapter marker
            $chapter_sprite_markup = '<img class="marker" src="images/players/'.$player_token.'/chapter-sprite.gif" />';

            // CHAPTER ONE(0) Intro
            if ($chapters_unlocked['0']){
                $chapters_display_count++;
                $chapter_title_markup = '<span>Chapter 1</span>';
                if (!$chapters_unlocked['1']){ $chapter_title_markup .= $chapter_sprite_markup; }
                echo '<a class="chapter_link '.($temp_last_chapter === '0' ? 'chapter_link_active ' : '').'" href="#" data-chapter="0">'.$chapter_title_markup.'</a>';
            } else {
                $chapters_display_count++;
                echo '<a class="chapter_link chapter_link_disabled">???</a>';
            }

            // CHAPTER TWO(1) Masters
            if ($chapters_unlocked['1']){
                $chapters_display_count++;
                $chapter_title_markup = '<span>Chapter 2</span>';
                if (!$chapters_unlocked['2']){ $chapter_title_markup .= $chapter_sprite_markup; }
                echo '<a class="chapter_link '.($temp_last_chapter === '1' ? 'chapter_link_active ' : '').'" href="#" data-chapter="1">'.$chapter_title_markup.'</a>';
            } else {
                $chapters_display_count++;
                echo '<a class="chapter_link chapter_link_disabled">???</a>';
            }

            // CHAPTER THREE(2) Rivals
            if ($chapters_unlocked['2']){
                $chapters_display_count++;
                $chapter_title_markup = '<span>Chapter 3</span>';
                if (!$chapters_unlocked['3']){ $chapter_title_markup .= $chapter_sprite_markup; }
                echo '<a class="chapter_link '.($temp_last_chapter === '2' ? 'chapter_link_active ' : '').'" href="#" data-chapter="2">'.$chapter_title_markup.'</a>';
            } else {
                $chapters_display_count++;
                echo '<a class="chapter_link chapter_link_disabled">???</a>';
            }

            // CHAPTER FOUR(3) Fusions
            if ($chapters_unlocked['3']){
                $chapters_display_count++;
                $chapter_title_markup = '<span>Chapter 4</span>';
                if (!$chapters_unlocked['4a']){ $chapter_title_markup .= $chapter_sprite_markup; }
                echo '<a class="chapter_link '.($temp_last_chapter === '3' ? 'chapter_link_active ' : '').'" href="#" data-chapter="3">'.$chapter_title_markup.'</a>';
            } else {
                $chapters_display_count++;
                echo '<a class="chapter_link chapter_link_disabled">???</a>';
            }

            // CHAPTER FIVE(4a-c) Finals
            if ($chapters_unlocked['4a']){
                $chapters_display_count++;
                $chapter_title_markup = '<span>Chapter 5</span>';
                if (true){ $chapter_title_markup .= $chapter_sprite_markup; }
                echo '<a class="chapter_link '.($temp_last_chapter === '4' ? 'chapter_link_active ' : '').'" href="#" data-chapter="4">'.$chapter_title_markup.'</a>';
            } else {
                $chapters_display_count++;
                echo '<a class="chapter_link chapter_link_disabled">???</a>';
            }

            // Pre-check to see how many extra tabs to add
            $num_extra = 0;
            $num_extra += $allow_bonus_fields = $chapters_unlocked['5'] ? 1 : 0;
            $num_extra += $allow_star_fields = $chapters_unlocked['7'] ? 1 : 0;
            $num_extra += $allow_player_battles = $chapters_unlocked['6'] ? 1 : 0;
            $num_extra += $allow_challenge_battles = $chapters_unlocked['8'] ? 1 : 0;

            // CHAPTER RANDOM(5)
            if ($allow_bonus_fields){
                $chapters_display_count++;
                echo '<a class="chapter_link extra first random '.($temp_last_chapter === '5' ? 'chapter_link_active ' : '').'" href="#" data-chapter="5" title="Bonus Chapter : Mission Randomizer || [[Face off against randomized mechas, robots, and bosses!]]">Random</a>';
                } elseif ($num_extra > 0){
                $chapters_display_count++;
                echo '<a class="chapter_link extra first random chapter_link_disabled">???</a>';
                }

            // CHAPTER STARS(7)
            if ($allow_star_fields){
                $chapters_display_count++;
                echo '<a class="chapter_link extra stars '.($temp_last_chapter === '7' ? 'chapter_link_active ' : '').'" href="#" data-chapter="7" title="Bonus Chapter : Star Fields || [[Collect elemental stars to power up your robots!]]">Stars'.($battle_star_counter >= MMRPG_SETTINGS_STARFORCE_STARTOTAL ? ' &check;' : '').'</a>';
                } elseif ($num_extra > 0){
                $chapters_display_count++;
                echo '<a class="chapter_link extra stars chapter_link_disabled">???</a>';
                }

            // CHAPTER PLAYERS(6)
            if ($allow_player_battles){
                $chapters_display_count++;
                echo '<a class="chapter_link extra players '.($temp_last_chapter === '6' ? 'chapter_link_active ' : '').'" href="#" data-chapter="6" title="Bonus Chapter : Player Battles || [[Fight against other players\' ghost data!]] || [[Limited robots + only hold items allowed!]]'.(!empty($this_rogue_star) ? ' || [[(Rogue Stars are disabled here!)]]' : '').'">Players</a>';
                } elseif ($num_extra > 0){
                $chapters_display_count++;
                echo '<a class="chapter_link extra players chapter_link_disabled">???</a>';
                }

            // CHAPTER CHALLENGES(8)
            if ($allow_challenge_battles){
                $chapters_display_count++;
                echo '<a class="chapter_link extra challenges '.($temp_last_chapter === '8' ? 'chapter_link_active ' : '').'" href="#" data-chapter="8" title="Bonus Chapter : Challenge Mode || [[Fight in unique challenges designed by MMRPG staff!]] || [[Limited turns and robots + only hold items allowed!]]'.(!empty($this_rogue_star) ? ' || [[(Rogue Stars are disabled here!)]]' : '').'">Challenges</a>';
                } elseif ($num_extra > 0){
                $chapters_display_count++;
                echo '<a class="chapter_link extra challenges chapter_link_disabled">???</a>';
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