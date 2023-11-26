<?

// CANVAS MARKUP : ROBOTS

// Start the output buffer
ob_start();

// Include the necessary database files
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
//require(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
$mmrpg_database_robots = rpg_robot::get_index(true, false);

// Collect the array of unseen menu frame robots if there is one, then clear it
$frame_token = 'edit_robots';
$menu_frame_content_unseen = rpg_prototype::get_menu_frame_content_unseen($frame_token);
//error_log('$menu_frame_content_unseen = '.print_r($menu_frame_content_unseen, true));
//rpg_prototype::clear_menu_frame_content_unseen($frame_token); // do not clear these here we will extract individual tokens later

// Loop through the allowed edit data for all players
$key_counter = 0;
$player_counter = 0;
$player_keys = array_keys($allowed_edit_data);
foreach($allowed_edit_data AS $player_token => $player_info){

    // Increment the player counter
    $player_counter++;

    // Default the player colour to energy but adapt if there are any other stats
    $player_colour = 'energy';
    if (!empty($player_info['player_attack'])){ $player_colour = 'attack'; }
    elseif (!empty($player_info['player_defense'])){ $player_colour = 'defense'; }
    elseif (!empty($player_info['player_speed'])){ $player_colour = 'speed'; }

    // Check for any robots that are locked in the endless attack or otherwise
    $player_robots_locked = array();
    $player_robots_endless = array();
    $endless_attack_savedata = mmrpg_prototype_get_endless_sessions($player_token);
    //error_log('$endless_attack_savedata for '.$player_token.': '.print_r(array_keys($endless_attack_savedata), true));
    if (!empty($endless_attack_savedata)
        && !empty($endless_attack_savedata['robots'])){
        $player_robots_endless = $endless_attack_savedata['robots'];
        $player_robots_locked = array_merge($player_robots_locked, $player_robots_endless);
        $player_robots_locked = array_unique($player_robots_locked);
    }

    //echo '<td style="width: '.floor(100 / $allowed_edit_player_count).'%;">'."\n";
        echo '<div class="wrapper wrapper_'.($player_counter % 2 != 0 ? 'left' : 'right').' wrapper_'.$player_token.'" data-select="robots" data-player="'.$player_info['player_token'].'" style="width: '.(floor(100 / $allowed_edit_player_count) - ($allowed_edit_player_count)).'%; margin-right: 0.5%;">'."\n";
            echo '<div class="wrapper_header player_type player_type_'.$player_colour.'">'.$player_info['player_name'].' <span class="count">'.count($player_info['player_robots']).'</span></div>';
            echo '<div class="wrapper_overflow">';
                $player_canvas_robots = array();
                $player_canvas_robots_locked = array();
                foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
                    if (!isset($mmrpg_database_robots[$robot_token])){ continue; }
                    if (!in_array($robot_token, $player_robots_locked)){ $player_canvas_robots[$robot_token] = $robot_info; }
                    else { $player_canvas_robots_locked[$robot_token] = $robot_info; }
                }
                $player_canvas_robots_sorted = array_merge($player_canvas_robots, $player_canvas_robots_locked);
                foreach ($player_canvas_robots_sorted AS $robot_token => $robot_info){
                    $robot_key = $key_counter;
                    if (!isset($mmrpg_database_robots[$robot_token])){ continue; }
                    //if (in_array($robot_token, $player_robots_locked)){ continue; }

                    // Collect rewards and settings for this robot form the session
                    $temp_robot_rewards = array();
                    $temp_robot_settings = array();
                    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])){
                        $temp_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
                    }
                    if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
                        $temp_robot_settings = $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token];
                    }

                    // Merge in stray-data from other players if it's there
                    foreach ($player_keys AS $this_player_key){
                        if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$this_player_key]['player_robots'][$robot_token])){
                            $temp_array = $_SESSION[$session_token]['values']['battle_rewards'][$this_player_key]['player_robots'][$robot_token];
                            $temp_robot_rewards = array_merge($temp_robot_rewards, $temp_array);
                        }
                        if (!empty($_SESSION[$session_token]['values']['battle_settings'][$this_player_key]['player_robots'][$robot_token])){
                            $temp_array = $_SESSION[$session_token]['values']['battle_settings'][$this_player_key]['player_robots'][$robot_token];
                            $temp_robot_settings = array_merge($temp_robot_settings, $temp_array);
                        }
                    }

                    // Update the session values with the merged data if allowed
                    if (!empty($temp_robot_rewards) && $global_allow_editing){
                        $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token] = $temp_robot_rewards;
                    }
                    if (!empty($temp_robot_settings) && $global_allow_editing){
                        $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token] = $temp_robot_settings;
                    }

                    //error_log('$temp_robot_rewards = '.print_r($temp_robot_rewards, true));
                    //error_log('$temp_robot_settings = '.print_r($temp_robot_settings, true));

                    // If this has a persona right now, make sure we apply it
                    $has_persona_applied = false;
                    if (!empty($temp_robot_settings['robot_persona'])
                        && !empty($temp_robot_settings['robot_abilities']['copy-style'])){
                        //error_log($robot_info['robot_token'].' has a persona: '.$temp_robot_settings['robot_persona']);
                        $persona_token = $temp_robot_settings['robot_persona'];
                        $persona_image_token = !empty($temp_robot_settings['robot_persona_image']) ? $temp_robot_settings['robot_persona_image'] : $temp_robot_settings['robot_persona'];
                        $persona_index_info = $mmrpg_database_robots[$persona_token];
                        rpg_robot::apply_persona_info($robot_info, $persona_index_info, $temp_robot_settings);
                        //error_log('new $robot_info = '.print_r($robot_info, true));
                        $has_persona_applied = true;
                    }

                    //$temp_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
                    $robot_info['robot_level'] = !empty($temp_robot_rewards['robot_level']) ? $temp_robot_rewards['robot_level'] : 1;
                    $robot_info['robot_experience'] = !empty($temp_robot_rewards['robot_experience']) ? $temp_robot_rewards['robot_experience'] : 0;
                    if ($robot_info['robot_level'] >= 100){ $robot_info['robot_experience'] = '&#8734;'; }

                    $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                    $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
                    $robot_image_offset_x = -5 - $robot_image_offset;
                    $robot_image_offset_y = -10 - $robot_image_offset;
                    $robot_tooltip_text = $robot_info['robot_name'].' ('.(!empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']).' Core' : 'Neutral Core').') &lt;br /&gt;Lv '.$robot_info['robot_level'].' | '.$robot_info['robot_experience'].' Exp';
                    $robot_is_new = in_array($robot_token, $menu_frame_content_unseen) ? true : false;

                    $robot_link_styles = 'background-image: none;';
                    $robot_link_classes = 'sprite sprite_robot sprite_robot_'.$player_token.' sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == 0 ? 'sprite_robot_current sprite_robot_'.$player_token.'_current ' : '').' robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').' ';

                    if (in_array($robot_token, $player_robots_locked)){ $robot_link_classes .= 'locked disabled '; }
                    $robot_is_endless = in_array($robot_token, $player_robots_endless) ? true : false;

                    $robot_sprite_styles = 'background-image: url(images/robots/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mug_right_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: '.$robot_image_offset_x.'px '.$robot_image_offset_y.'px;';
                    $robot_sprite_classes = 'sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot';

                    echo '<a data-number="'.$robot_info['robot_number'].
                        '" data-level="'.$robot_info['robot_level'].
                        '" data-token="'.$player_info['player_token'].'_'.$robot_info['robot_token'].
                        '" data-robot="'.$robot_info['robot_token'].
                        '" data-player="'.$player_info['player_token'].
                        '" title="'.$robot_info['robot_name'].
                        '" data-tooltip="'.$robot_tooltip_text.
                        '" style="'.$robot_link_styles.
                        '" class="'.$robot_link_classes
                        .'">'.
                        '<span class="'.$robot_sprite_classes.'" style="'.$robot_sprite_styles.'"></span>'.
                        '<span class="name">'.$robot_info['robot_name'].'</span>'.
                        ($robot_is_new ? '<i class="new type electric"></i>' : '').
                        ($robot_is_endless ? '<span class="endless"><i class="fa fas fa-infinity"></i></span>' : '').
                        '</a>'."\n";
                    $key_counter++;
                }
            echo '</div>'."\n";
            if ($global_allow_editing){
                ?>
                <div class="sort_wrapper">
                    <label class="label">sort</label>
                    <a class="sort sort_level" data-sort="level" data-order="asc" data-player="<?= $player_info['player_token'] ?>">level</a>
                    <a class="sort sort_number" data-sort="number" data-order="asc" data-player="<?= $player_info['player_token'] ?>">number</a>
                    <a class="sort sort_core" data-sort="core" data-order="asc" data-player="<?= $player_info['player_token'] ?>">core</a>
                </div>
                <?php
            }
        echo '</div>'."\n";
    //echo '</td>'."\n";
}

// Collect the contents of the buffer
$edit_canvas_markup = ob_get_clean();
$edit_canvas_markup = preg_replace('/\s+/', ' ', trim($edit_canvas_markup));
exit($edit_canvas_markup);

?>