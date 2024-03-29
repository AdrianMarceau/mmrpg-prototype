<?php
/**
 * Mega Man RPG Prototype
 * <p>The global prototype for the Mega Man RPG Prototype.</p>
 */
class rpg_prototype {

    /**
     * Create a new RPG prototype object.
     * This is a wrapper class for static functions,
     * so object initialization is not necessary.
     */
    public function __construct(){ }

    // Define a function for calculating required experience points to the next level
    public static function calculate_experience_required($this_level, $max_level = 100, $min_experience = 1000){

        $last_level = $this_level - 1;
        $level_mod = $this_level / $max_level;
        $this_experience = round($min_experience + ($last_level * $level_mod * $min_experience));

        return $this_experience;
    }

    // Define a function for calculating required experience points to the next level
    public static function calculate_level_by_experience($this_experience, $max_level = 100, $min_experience = 1000){
        $temp_total_experience = 0;
        for ($this_level = 1; $this_level < $max_level; $this_level++){
            $temp_experience = rpg_prototype::calculate_experience_required($this_level, $max_level, $min_experience);
            $temp_total_experience += $temp_experience;
            if ($temp_total_experience > $this_experience){
                return $this_level - 1;
            }
        }
        return $max_level;
    }

    // Define a function for checking a player has completed the prototype
    public static function campaign_complete($player_token = ''){

        // Pull in global variables
        global $mmrpg_index_players;
        if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }
        $session_token = rpg_game::session_token();

        // If the player token was provided, do a quick check
        if (!empty($player_token)){
            // Return the prototype complete flag for this player
            if (!empty($_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'])){ return 1; }
            else { return 0; }
        }
        // Otherwise loop through all players and check each
        else {
            // Loop through unlocked robots and return true if any are found to be completed
            $complete_count = 0;
            foreach ($mmrpg_index_players AS $player_token => $player_info){
                if (rpg_game::player_unlocked($player_token)){
                    if (!empty($_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'])){
                        $complete_count += 1;
                    }
                }
            }
            // Otherwise return false by default
            return $complete_count;
        }
    }

    // Define a function for checking the battle's prototype points total
    public static function event_complete($event_token){
        // Return the current point total for thisgame
        $session_token = rpg_game::session_token();
        if (!empty($_SESSION[$session_token]['flags']['events'][$event_token])){ return 1; }
        else { return 0; }
    }

    // Define a function for checking if a prototype battle has been failured
    public static function battle_failure($player_token, $battle_token){
        // Check if this battle has been failured and return true is it was
        $session_token = rpg_game::session_token();
        return isset($_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token] : false;
    }

    // Define the field star image function for use in other parts of the game
    public static function star_image($type){
        static $type_order = array('none', 'copy', 'crystal', 'cutter', 'earth',
            'electric', 'explode', 'flame', 'freeze', 'impact',
            'laser', 'missile', 'nature', 'shadow', 'shield',
            'space', 'swift', 'time', 'water', 'wind');
        $type_sheet = 1;
        $type_frame = array_search($type, $type_order);
        if ($type_frame >= 10){
            $type_sheet = 2;
            $type_frame = $type_frame - 10;
        } elseif ($type_frame < 0){
            $type_sheet = 1;
            $type_frame = 0;
        }
        $temp_array = array('sheet' => $type_sheet, 'frame' => $type_frame);
        return $temp_array;
    }

    // Define the type arrow image function for use in other parts of the game
    public static function type_arrow_image($kind, $type){
        static $type_order = array('none', 'copy', 'crystal', 'cutter', 'earth',
            'electric', 'explode', 'flame', 'freeze', 'impact',
            'laser', 'missile', 'nature', 'shadow', 'shield',
            'space', 'swift', 'time', 'water', 'wind');

        $type_sheet = 1;
        $type_frame = array_search($type, $type_order);
        if ($type_frame >= 10){
            $type_sheet = 2;
            $type_frame = $type_frame - 10;
        } elseif ($type_frame < 0){
            $type_sheet = 1;
            $type_frame = 0;
        }
        $type_image = '_effects/type-arrows_'.$kind.'-'.$type_sheet;
        $temp_array = array('image' => $type_image, 'frame' => $type_frame);
        return $temp_array;
    }

    // Define a function for calculating a given player's progress through the story
    public static function calculate_player_progress($player_token, &$chapters_unlocked){

        // Collect counters and flags for this specific player
        $session_token = rpg_game::session_token();
        $player_unlocked = mmrpg_prototype_player_unlocked($player_token);
        $battle_complete_counter = $player_unlocked ? mmrpg_prototype_battles_complete($player_token) : 0;
        $battle_failure_counter = $player_unlocked ? mmrpg_prototype_battles_failure($player_token) : 0;
        $prototype_complete_flag = $player_unlocked ? mmrpg_prototype_complete($player_token) : false;
        $prototype_complete_count = mmrpg_prototype_complete();
        //$battle_star_counter = mmrpg_prototype_stars_unlocked();
        if ($player_unlocked && !$prototype_complete_flag && $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){
            $_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'] = $prototype_complete_flag = true;
        }

        // Predefine the variable to hold the unlocked chapter progress
        if (empty($chapters_unlocked) || !is_array($chapters_unlocked)){ $chapters_unlocked = array(); }

        // -- PHASE ONE -- //

        // Intro
        $chapters_unlocked['0'] = true;
        $chapters_unlocked['0b'] = $prototype_complete_flag || $battle_complete_counter >= (MMRPG_SETTINGS_CHAPTER0_MISSIONS + 1) ? true : false;
        $chapters_unlocked['0c'] = $prototype_complete_flag || $battle_complete_counter >= (MMRPG_SETTINGS_CHAPTER0_MISSIONS + 2) ? true : false;

        // Masters
        $chapters_unlocked['1'] = $prototype_complete_flag || $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER1_MISSIONCOUNT ? true : false;

        // Rivals
        $chapters_unlocked['2'] = $prototype_complete_flag || $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER2_MISSIONCOUNT ? true : false;


        // -- PHASE TWO -- //

        // Fusions
        $chapters_unlocked['3'] = $prototype_complete_flag || $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER3_MISSIONCOUNT ? true : false;

        // Finals
        $chapters_unlocked['4a'] = $prototype_complete_flag || $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT ? true : false;
        $chapters_unlocked['4b'] = $prototype_complete_flag || $battle_complete_counter >= (MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT + 1) ? true : false;
        $chapters_unlocked['4c'] = $prototype_complete_flag || $battle_complete_counter >= (MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT + 2) ? true : false;


        // -- BONUS PHASE -- //

        if ($prototype_complete_flag
            || $battle_complete_counter >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){

            // Random
            $chapters_unlocked['5'] = true;

            // Players
            $chapters_unlocked['6'] = mmrpg_prototype_item_unlocked('light-program') ? true : false;

            // Challenges
            $chapters_unlocked['8'] = mmrpg_prototype_item_unlocked('wily-program') ? true : false;

            // Stars
            $chapters_unlocked['7'] = mmrpg_prototype_item_unlocked('cossack-program') ? true : false;

        } else {

            // Random
            $chapters_unlocked['5'] = false;

            // Players
            $chapters_unlocked['6'] = false;

            // Challenges
            $chapters_unlocked['8'] = false; //$prototype_complete_count >= 3 ? true : false;

            // Stars
            $chapters_unlocked['7'] = false; //mmrpg_prototype_item_unlocked('cossack-program') ? true : false;

        }

    }

    // Define a function for getting the array of unlocked chapters for a given player
    public static function get_player_chapters_unlocked($player_token){

        // Calculate player progress and capture the chapters array
        $chapters_unlocked = array();
        self::calculate_player_progress($player_token, $chapters_unlocked);

        // Return the array of chapters unlocked
        return $chapters_unlocked;

    }


    // -- IN-GAME BATTLE MENU FUNCTIONS -- //

    // Define a static public function for calculating high/medium/low words to represent percentage tiers
    public static function calculate_percentage_tier($current, $total){
        $percent = ceil(($current / $total) * 100);
        if ($percent > 50){ $class = 'high'; }
        elseif ($percent > 25){ $class = 'medium';  }
        elseif ($percent > 0){ $class = 'low'; }
        else { $class = 'zero'; }
        return $class;
    }

    // Define a function for sorting robots in a given battle menu for display
    public static function sort_robots_for_battle_menu($info1, $info2){
        if ($info1['robot_position'] === 'active' && $info2['robot_position'] !== 'active'){ return -1; }
        elseif ($info1['robot_position'] !== 'active' && $info2['robot_position'] === 'active'){ return 1; }
        elseif ($info1['robot_key'] < $info2['robot_key']){ return -1; }
        elseif ($info1['robot_key'] > $info2['robot_key']){ return 1; }
        else { return 0; }
    }

    // Define a function for printing a robot's button code given the robot object and the context the button is for
    public static function print_robot_for_battle_menu($robot, $button_context = 'scan', $robot_key = 0, &$order_counter = 0, $extra = array()){

        // Collect any indexes we'll need to display the button
        $mmrpg_items_index = rpg_item::get_index();
        $mmrpg_abilities_index = rpg_ability::get_index();

        // Collect basic details about the robot we might need later
        $player_side = $robot->player->player_side;
        $robot_direction = $player_side == 'left' ? 'right' : 'left';
        $action_token = strstr($button_context, 'target_') ? 'target' : $button_context;
        $block_num = $robot_key + 1;

        // Collect the active robot for this player so we can cross-compare conditions
        $this_active_robot = isset($extra['this_active_robot']) ? $extra['this_active_robot'] : $robot->player->get_active_robot();

        // Default the allow button flag to true
        $allow_button = true;

        // Define the show abilities flag to false (for strategy)
        $show_abilities = false;

        // Define the show abilities flag to true (its visible anyway)
        $show_items = true;

        // Check to see if this SCAN button should be disabled for any reason
        if ($button_context === 'scan'){

            // If this robot is disabled, disable the button
            if ($robot->robot_status === 'disabled'){ $allow_button = false; }

        }
        // Check to see if this SWITCH button should be disabled for any reason
        elseif ($button_context === 'switch'){

            // We can show abilities and attached items for allies
            $show_abilities = true;
            $show_items = true;

            // Collect extra details about the context we need to generate buttons
            $this_switch_disabled = isset($extra['this_switch_disabled']) ? $extra['this_switch_disabled'] : false;
            $this_switch_required = isset($extra['this_switch_required']) ? $extra['this_switch_required'] : false;

            // Check if the switch should be disabled based on attachments on this robot
            $temp_switch_disabled = false;
            if (!$this_switch_required
                && $robot->robot_status != 'disabled'){
                $robot_attachments = $robot->get_current_attachments();
                if (!empty($robot_attachments)){
                    foreach ($robot_attachments AS $attachment_token => $attachment_info){
                        if (!empty($attachment_info['attachment_switch_disabled'])){
                            $temp_switch_disabled = true;
                            break;
                        }
                    }
                }
            }

            // If the switch is not disabled yet and the robot status isn't disabled, disable it
            if ($this_switch_disabled
                && !empty($this_active_robot)
                && $this_active_robot->robot_status != 'disabled'){
                $temp_switch_disabled = true;
            }

            // If this player has already used a switch this turn
            if (!empty($this_player->flags['switch_used_this_turn'])){ $temp_switch_disabled = true; }

            // If this robot is already out, disable the button
            if ($robot->robot_position == 'active'){ $allow_button = false; }

            // If this robot is disabled, disable the button
            if ($robot->robot_status === 'disabled'){ $allow_button = false; }

            // If this robot is the "active" one (maybe it was a force switch?)
            if ($robot->player->counters['robots_active'] >= 2
                && !empty($this_active_robot)
                && $robot->robot_id == $this_active_robot->robot_id){
                $allow_button = false;
            }

            // If the current robot has switching disabled
            if ($temp_switch_disabled){ $allow_button = false; }

        }
        // Check to see if this TARGET_TARGET button should be disabled for any reason
        elseif ($button_context === 'target_target'){

            // If this robot is disabled, disable the button
            if ($robot->robot_status === 'disabled'){ $allow_button = false; }

        }
        // Check to see if this TARGET_THIS button should be disabled for any reason
        elseif ($button_context === 'target_this'){

            // If this robot is disabled, disable the button
            if ($robot->robot_status === 'disabled'){ $allow_button = false; }

        }
        // Check to see if this TARGET_ALLY button should be disabled for any reason
        elseif ($button_context === 'target_this_ally'){

            // If this robot is disabled, disable the button
            if ($robot->robot_status === 'disabled'){ $allow_button = false; }

            // If this robot is not on the bench, disable the button
            if ($robot->robot_position !== 'bench'){ $allow_button = false; }

        }
        // Check to see if this TARGET_DISABLED button should be disabled for any reason
        elseif ($button_context === 'target_this_disabled'){

            // If this robot is disabled, disable the button
            if ($robot->robot_status !== 'disabled'){ $allow_button = false; }

        }

        // Define the title hover for the robot
        $robot_title = $robot->robot_name.'  (Lv. '.$robot->robot_level.')';
        //$robot_title .= ' | '.$robot->robot_id.'';
        $robot_title .= ' <br />'.(!empty($robot->robot_core) ? ucfirst($robot->robot_core).' Core' : 'Neutral Core').' | '.ucfirst($robot->robot_position).' Position';

        // Display the robot's item if it exists
        $robot_item_image = '';
        $robot_item_sprite = '';
        if ($show_items
            && !empty($robot->robot_item)
            && empty($robot->counters['item_disabled'])
            && !empty($mmrpg_items_index[$robot->robot_item])){
            $robot_title .= ' | + '.$mmrpg_items_index[$robot->robot_item]['item_name'].' ';
            $robot_item_image = 'images/items/'.$robot->robot_item.'/icon_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
            $robot_item_sprite = '<span class="sprite sprite_item sprite_40x40" style="background-image: url('.$robot_item_image.');"></span>';
        }

        // Display the robot's life and weapon energy current and base
        $robot_title .= ' <br />'.$robot->robot_energy.' / '.$robot->robot_base_energy.' LE';
        $robot_title .= ' | '.$robot->robot_weapons.' / '.$robot->robot_base_weapons.' WE';
        if ($robot_direction == 'right' && $robot->robot_class != 'mecha'){
            $robot_title .= ' | '.$robot->robot_experience.' / 1000 EXP';
        }
        $robot_title .= ' <br />'.$robot->robot_attack.' / '.$robot->robot_base_attack.' AT';
        $robot_title .= ' | '.$robot->robot_defense.' / '.$robot->robot_base_defense.' DF';
        $robot_title .= ' | '.$robot->robot_speed.' / '.$robot->robot_base_speed.' SP';

        // Loop through this robot's current abilities and list them as well
        if ($show_abilities
            && !empty($robot->robot_abilities)){
            $robot_title .= ' <br />';
            foreach ($robot->robot_abilities AS $key => $token){
                if (!isset($mmrpg_abilities_index[$token])){ continue; }
                if ($key > 0 && $key % 4 != 0){ $robot_title .= '&nbsp;|&nbsp;'; }
                if ($key > 0 && $key % 4 == 0){ $robot_title .= '<br /> '; }
                $info = $mmrpg_abilities_index[$token];
                $robot_title .= $info['ability_name'];
            }
        }

        // Encode the tooltip for markup insertion and create a plain one too
        $robot_title_plain = strip_tags(str_replace('<br />', '&#10;', $robot_title));
        $robot_title_tooltip = htmlentities($robot_title, ENT_QUOTES, 'UTF-8');

        // Collect the robot's core types for display
        $robot_core_type = !empty($robot->robot_core) ? $robot->robot_core : 'none';
        $robot_core2 = !empty($robot->robot_core2) ? $robot->robot_core2 : '';
        if (!empty($robot->robot_item) && preg_match('/-core$/', $robot->robot_item)){
            $item_core_type = preg_replace('/-core$/', '', $robot->robot_item);
            if (empty($robot_core2) && $robot_core_type != $item_core_type){ $robot_core2 = $item_core_type; }
        }
        $robot_core = !empty($robot->robot_core) ? $robot->robot_core : '';
        $robot_core2 = !empty($robot->robot_core2) ? $robot->robot_core2 : '';
        $robot_core_or_none = !empty($robot_core) ? $robot_core : 'none';
        if ($robot_core_or_none === 'none' && !empty($robot_core2)){ $robot_core_or_none = $robot_core2; }

        // Collect the energy and weapon percent so we know how they're doing
        $energy_class = rpg_prototype::calculate_percentage_tier($robot->robot_energy, $robot->robot_base_energy);
        $weapons_class = rpg_prototype::calculate_percentage_tier($robot->robot_weapons, $robot->robot_base_weapons);

        // Define the robot button text variables
        $robot_label = '<span class="multi">';
            $robot_label .= '<span class="maintext">'.$robot->robot_name.' <sup class="level">Lv. '.$robot->robot_level.'</sup></span>';
            $robot_label .= '<span class="subtext">';
                $robot_label .= '<span class="stat_is_'.$energy_class.'"><strong>'.$robot->robot_energy.'</strong>/'.$robot->robot_base_energy.' LE</span>';
            $robot_label .= '</span>';
            $robot_label .= '<span class="subtext">';
                $robot_label .= '<span class="stat_is_'.$weapons_class.'"><strong>'.$robot->robot_weapons.'</strong>/'.$robot->robot_base_weapons.' WE</span>';
            $robot_label .= '</span>';
        $robot_label .= '</span>';

        // Define the robot sprite variables
        $robot_sprite = array();
        $robot_sprite['name'] = $robot->robot_name;
        $robot_sprite['image'] = $robot->robot_image;
        $robot_sprite['image_size'] = $robot->robot_image_size;
        $robot_sprite['image_size_text'] = $robot_sprite['image_size'].'x'.$robot_sprite['image_size'];
        $robot_sprite['image_size_zoom'] = $robot->robot_image_size * 2;
        $robot_sprite['image_size_zoom_text'] = $robot_sprite['image_size'].'x'.$robot_sprite['image_size'];
        $robot_sprite['url'] = 'images/robots/'.$robot->robot_image.'/sprite_'.$robot_direction.'_'.$robot_sprite['image_size_text'].'.png';
        $robot_sprite['preload'] = 'images/robots/'.$robot->robot_image.'/sprite_'.$robot_direction.'_'.$robot_sprite['image_size_zoom_text'].'.png';
        $robot_sprite['class'] = 'sprite sprite_robot sprite_'.$robot_sprite['image_size_text'].' sprite_'.$robot_sprite['image_size_text'].'_'.($robot->robot_energy > 0 ? ($robot->robot_energy > ($robot->robot_base_energy/2) ? 'base' : 'defend') : 'defeat').' ';
        $robot_sprite['style'] = 'background-image: url('.$robot_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.'); ';
        if ($robot->robot_position == 'active'){ $robot_sprite['style'] .= 'border-color: #ababab; '; }
        $robot_sprite['class'] .= 'sprite_'.$robot_sprite['image_size_text'].'_energy_'.$energy_class.' ';
        $robot_sprite['markup'] = '<span class="'.$robot_sprite['class'].'" style="'.$robot_sprite['style'].'">'.$robot_sprite['name'].'</span>';

        // Update the order button if necessary
        $order_button_markup = $allow_button ? 'data-order="'.$order_counter.'"' : '';
        $order_counter += $allow_button ? 1 : 0;

        // Check to see if this robot should be shown as "new" to the player
        static $session_robot_database;
        $show_as_new = false;
        if ($player_side === 'right'){
            if (empty($session_robot_database)){
                $session_token = rpg_game::session_token();
                $session_robot_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();
            }
            //error_log('$session_robot_database = '.print_r($session_robot_database, true));
            if (empty($session_robot_database[$robot->robot_token])){
                $show_as_new = true;
            } elseif (empty($session_robot_database[$robot->robot_token]['robot_unlocked'])){
                if (empty($session_robot_database[$robot->robot_token]['robot_scanned'])){
                    $show_as_new = true;
                }
            }
        }

        // Now use the new object to generate a snapshot of this switch button
        $btn_type = 'robot_type robot_type_'.(!empty($robot->robot_core) ? $robot->robot_core : 'none').(!empty($robot->robot_core2) ? '_'.$robot->robot_core2 : '');
        $btn_class = 'button action_'.$action_token.' '.$action_token.'_'.$robot->robot_token.' '.$btn_type.' block_'.$block_num.' ';
        $btn_action = $action_token.'_'.$robot->robot_id.'_'.$robot->robot_token;
        $btn_info_new = $show_as_new ? ' new' : '';
        $btn_info_circle = '<span class="info color'.$btn_info_new.'" data-click-tooltip="'.$robot_title_tooltip.'" data-tooltip-type="'.$btn_type.'">';
            $btn_info_circle .= '<i class="fa fas fa-info-circle color '.$robot_core_or_none.'"></i>';
            //if (!empty($robot_core2)){ $btn_info_circle .= '<i class="fa fas fa-info-circle color '.$robot_core2.'"></i>'; }
        $btn_info_circle .= '</span>';

        // Now that everything is ready, we can actually generate the button markup
        ob_start();
        if ($allow_button){
            echo('<a type="button" class="'.$btn_class.'" data-action="'.$btn_action.'" data-preload="'.$robot_sprite['preload'].'" data-position="'.$robot->robot_position.'/'.$robot->robot_key.'" '.$order_button_markup.'>'.
                    '<label>'.
                        $btn_info_circle.
                        '<span class="sprite_container '.$player_side.'">'.
                            $robot_item_sprite.
                            $robot_sprite['markup'].
                        '</span>'.
                        $robot_label.
                    '</label>'.
                '</a>');
        } else {
            $btn_class .= 'button_disabled ';
            echo('<a type="button" class="'.$btn_class.'" data-position="'.$robot->robot_position.'/'.$robot->robot_key.'">'.
                    '<label>'.
                        $btn_info_circle.
                        '<span class="sprite_container '.$player_side.'">'.
                            $robot_item_sprite.
                            $robot_sprite['markup'].
                        '</span>'.
                        $robot_label.
                    '</label>'.
                '</a>');
        }
        $robot_button_markup = ob_get_clean();

        // Return the generated markup
        return $robot_button_markup;

    }


    // -- IN-GAME "NEW" SEEN/UNSEEN MENU/CONTENT FUNCTIONS -- //

    // Define a function for getting the list of menu frames that have been seen
    public static function get_menu_frames_seen(){
        //error_log('rpg_prototype::get_menu_frames_seen()');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Return the list of seen menu frames
        $settings_token = 'menu_frames_seen';
        $menu_frames_seen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        $menu_frames_seen = strstr($menu_frames_seen, '|') ? explode('|', $menu_frames_seen) : array($menu_frames_seen);
        //error_log('$menu_frames_seen  = '.print_r($menu_frames_seen, true));
        return $menu_frames_seen;
    }

    // Define a function for marking a menu frame as unseen by removing it from the history array
    public static function mark_menu_frame_as_unseen($frame_token){
        //error_log('rpg_prototype::mark_menu_frame_as_unseen($frame_token = '.$frame_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Remove the requested frame from the seen history array if exists so that it appears with the "new" indicator
        $settings_token = 'menu_frames_seen';
        $menu_frames_seen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        $menu_frames_seen = strstr($menu_frames_seen, '|') ? explode('|', $menu_frames_seen) : array($menu_frames_seen);
        if (($key = array_search($frame_token, $menu_frames_seen)) !== false){ unset($menu_frames_seen[$key]); }
        $menu_frames_seen = implode('|', array_filter($menu_frames_seen));
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frames_seen;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frames_seen);
    }

    // Define a function for marking a menu frame as seen by adding it to the history array
    public static function mark_menu_frame_as_seen($frame_token){
        //error_log('rpg_prototype::mark_menu_frame_as_seen($frame_token = '.$frame_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Add the requested frame to the seen history array if not already present
        $settings_token = 'menu_frames_seen';
        $menu_frames_seen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        $menu_frames_seen = strstr($menu_frames_seen, '|') ? explode('|', $menu_frames_seen) : array($menu_frames_seen);
        if (!in_array($frame_token, $menu_frames_seen)){ $menu_frames_seen[] = $frame_token; }
        $menu_frames_seen = implode('|', array_filter($menu_frames_seen));
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frames_seen;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frames_seen);
    }

    // Define a function for getting the list of menu frame content that is unseen
    public static function get_menu_frame_content_unseen($frame_token){
        //error_log('rpg_prototype::get_menu_frame_content_unseen($frame_token = '.$frame_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Return the list of unseen menu frame content
        $settings_token = 'menu_frame_'.str_replace('_', '-', $frame_token).'_unseen';
        $menu_frame_content_unseen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        if (!empty($menu_frame_content_unseen)){ $menu_frame_content_unseen = strstr($menu_frame_content_unseen, '|') ? explode('|', $menu_frame_content_unseen) : array($menu_frame_content_unseen); }
        else { $menu_frame_content_unseen = array(); }
        //error_log('$menu_frame_content_unseen  = '.print_r($menu_frame_content_unseen, true));
        return $menu_frame_content_unseen;
    }

    // Define a function for clearing the list of menu frame content that is unseen
    public static function clear_menu_frame_content_unseen($frame_token){
        //error_log('rpg_prototype::clear_menu_frame_content_unseen($frame_token = '.$frame_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Clear the list of unseen menu frame content
        $settings_token = 'menu_frame_'.str_replace('_', '-', $frame_token).'_unseen';
        $menu_frame_content_unseen = '';
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frame_content_unseen;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frame_content_unseen);
    }

    // Define a function for removing only a single value from the meny frame content that is unseen
    public static function remove_menu_frame_content_unseen($frame_token, $content_token){
        //error_log('rpg_prototype::remove_menu_frame_content_unseen($frame_token = '.$frame_token.', $content_token = '.$content_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Remove the requested frame from the unseen queue array if exists so that it appears with the "new" indicator
        $settings_token = 'menu_frame_'.str_replace('_', '-', $frame_token).'_unseen';
        $menu_frame_content_unseen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        $menu_frame_content_unseen = strstr($menu_frame_content_unseen, '|') ? explode('|', $menu_frame_content_unseen) : array($menu_frame_content_unseen);
        if (($key = array_search($content_token, $menu_frame_content_unseen)) !== false){ unset($menu_frame_content_unseen[$key]); }
        $menu_frame_content_unseen = implode('|', array_filter($menu_frame_content_unseen));
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frame_content_unseen;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frame_content_unseen);
    }

    // Define a function for marking menu frame content as unseen by adding it to the queue array
    public static function mark_menu_frame_content_as_unseen($frame_token, $content_token){
        //error_log('rpg_prototype::mark_menu_frame_content_as_unseen($frame_token = '.$frame_token.', $content_token = '.$content_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Add the requested frame to the unseen queue array if not already present
        $settings_token = 'menu_frame_'.str_replace('_', '-', $frame_token).'_unseen';
        $menu_frame_content_unseen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        $menu_frame_content_unseen = strstr($menu_frame_content_unseen, '|') ? explode('|', $menu_frame_content_unseen) : array($menu_frame_content_unseen);
        if (!in_array($content_token, $menu_frame_content_unseen)){ $menu_frame_content_unseen[] = $content_token; }
        $menu_frame_content_unseen = implode('|', array_filter($menu_frame_content_unseen));
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frame_content_unseen;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frame_content_unseen);
        // If this is for the "edit_robots" frame token, we should add the token to an array with same structure with $settings_token "robots_pending_entrance_animations"
        if ($frame_token === 'edit_robots'){
            self::mark_robot_as_pending_entrance_animation($content_token);
            /*
            // Add the requested frame to the unseen queue array if not already present
            $settings_token = 'robots_pending_entrance_animations';
            $menu_frame_content_unseen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
            $menu_frame_content_unseen = strstr($menu_frame_content_unseen, '|') ? explode('|', $menu_frame_content_unseen) : array($menu_frame_content_unseen);
            if (!in_array($content_token, $menu_frame_content_unseen)){ $menu_frame_content_unseen[] = $content_token; }
            $menu_frame_content_unseen = implode('|', array_filter($menu_frame_content_unseen));
            $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frame_content_unseen;
            //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frame_content_unseen);
            */
        }
    }

    // Define a function for marking menu frame content as seen by removing from to the queue array
    public static function mark_menu_frame_content_as_seen($frame_token, $content_token){
        //error_log('rpg_prototype::mark_menu_frame_content_as_seen($frame_token:'.$frame_token.', $content_token = '.$content_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Remove the requested frame from the unseen queue array if exists so that it appears with the "new" indicator
        $settings_token = 'menu_frame_'.str_replace('_', '-', $frame_token).'_unseen';
        $menu_frame_content_unseen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        $menu_frame_content_unseen = strstr($menu_frame_content_unseen, '|') ? explode('|', $menu_frame_content_unseen) : array($menu_frame_content_unseen);
        if (($key = array_search($content_token, $menu_frame_content_unseen)) !== false){ unset($menu_frame_content_unseen[$key]); }
        $menu_frame_content_unseen = implode('|', array_filter($menu_frame_content_unseen));
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frame_content_unseen;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frame_content_unseen);
    }

    // Define a subfunction for above that allows us to specifically markup a given robot token as pending an entrance animation
    public static function mark_robot_as_pending_entrance_animation($robot_token){
        //error_log('rpg_prototype::mark_robot_as_pending_entrance_animation($robot_token = '.$robot_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Add the requested frame to the unseen queue array if not already present
        $settings_token = 'robots_pending_entrance_animations';
        $menu_frame_content_unseen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        $menu_frame_content_unseen = strstr($menu_frame_content_unseen, '|') ? explode('|', $menu_frame_content_unseen) : array($menu_frame_content_unseen);
        if (!in_array($robot_token, $menu_frame_content_unseen)){ $menu_frame_content_unseen[] = $robot_token; }
        $menu_frame_content_unseen = implode('|', array_filter($menu_frame_content_unseen));
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frame_content_unseen;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frame_content_unseen);
    }

    // Define a function for getting the list of robots with pending entrance animations
    public static function get_robots_pending_entrance_animations(){
        //error_log('rpg_prototype::get_menu_frame_content_unseen($frame_token = '.$frame_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Return the list of unseen menu frame content
        $settings_token = 'robots_pending_entrance_animations';
        $robots_pending_entrance_animations = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        if (!empty($robots_pending_entrance_animations)){ $robots_pending_entrance_animations = strstr($robots_pending_entrance_animations, '|') ? explode('|', $robots_pending_entrance_animations) : array($robots_pending_entrance_animations); }
        else { $robots_pending_entrance_animations = array(); }
        //error_log('$robots_pending_entrance_animations  = '.print_r($robots_pending_entrance_animations, true));
        return $robots_pending_entrance_animations;
    }

    // Define a function for clearing the list of robots with pending entrance animations
    public static function clear_robots_pending_entrance_animations(){
        //error_log('rpg_prototype::clear_menu_frame_content_unseen($frame_token = '.$frame_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Clear the list of unseen menu frame content
        $settings_token = 'robots_pending_entrance_animations';
        $robots_pending_entrance_animations = '';
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $robots_pending_entrance_animations;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$robots_pending_entrance_animations);
    }

    // Define a subfunction for above that allows us to specifically markup a given player token as pending an entrance animation
    public static function mark_player_as_pending_entrance_animation($player_token){
        //error_log('rpg_prototype::mark_player_as_pending_entrance_animation($player_token = '.$player_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Add the requested frame to the unseen queue array if not already present
        $settings_token = 'players_pending_entrance_animations';
        $menu_frame_content_unseen = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        $menu_frame_content_unseen = strstr($menu_frame_content_unseen, '|') ? explode('|', $menu_frame_content_unseen) : array($menu_frame_content_unseen);
        if (!in_array($player_token, $menu_frame_content_unseen)){ $menu_frame_content_unseen[] = $player_token; }
        $menu_frame_content_unseen = implode('|', array_filter($menu_frame_content_unseen));
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $menu_frame_content_unseen;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$menu_frame_content_unseen);
    }

    // Define a function for getting the list of players with pending entrance animations
    public static function get_players_pending_entrance_animations(){
        //error_log('rpg_prototype::get_menu_frame_content_unseen($frame_token = '.$frame_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Return the list of unseen menu frame content
        $settings_token = 'players_pending_entrance_animations';
        $players_pending_entrance_animations = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        if (!empty($players_pending_entrance_animations)){ $players_pending_entrance_animations = strstr($players_pending_entrance_animations, '|') ? explode('|', $players_pending_entrance_animations) : array($players_pending_entrance_animations); }
        else { $players_pending_entrance_animations = array(); }
        //error_log('$players_pending_entrance_animations  = '.print_r($players_pending_entrance_animations, true));
        return $players_pending_entrance_animations;
    }

    // Define a function for clearing the list of players with pending entrance animations
    public static function clear_players_pending_entrance_animations(){
        //error_log('rpg_prototype::clear_menu_frame_content_unseen($frame_token = '.$frame_token.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Clear the list of unseen menu frame content
        $settings_token = 'players_pending_entrance_animations';
        $players_pending_entrance_animations = '';
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $players_pending_entrance_animations;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$players_pending_entrance_animations);
    }

    // Define a new function for checking if star support has been unlocked yet
    public static function star_support_unlocked(){
        //error_log('rpg_prototype::star_support_unlocked()');
        $star_support_unlocked = false;
        $session_token = rpg_game::session_token();
        $settings_token = 'star_support_cooldown';
        if (isset($_SESSION[$session_token]['battle_settings'][$settings_token])){ $star_support_unlocked = true; }
        //elseif (MMRPG_CONFIG_SERVER_ENV === 'local' || MMRPG_CONFIG_SERVER_ENV === 'dev'){ $star_support_unlocked = true; }
        //error_log('$star_support_unlocked = '.($star_support_unlocked ? 'true' : 'false'));
        return $star_support_unlocked;
    }

    // Define a new function for getting the max star support cooldown value for reference and calc
    public static function get_star_support_cooldown_max(){
        //error_log('rpg_prototype::get_star_support_cooldown_max()');
        return 100;
    }

    // Define a new function for resetting the value of the battle settings called "star_support_cooldown" to its max value
    public static function reset_star_support_cooldown(){
        //error_log('rpg_prototype::reset_star_support_cooldown()');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Set the value of the "star_support_cooldown" setting
        $star_support_cooldown = self::get_star_support_cooldown_max();
        $settings_token = 'star_support_cooldown';
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $star_support_cooldown;
        //error_log('$star_support_cooldown  = '.$star_support_cooldown);
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$star_support_cooldown);
    }

    // Define a new function for getting the value of the battle settings called "star_support_cooldown" and returning it
    public static function get_star_support_cooldown(){
        //error_log('rpg_prototype::get_star_support_cooldown()');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Return the value of the "star_support_cooldown" setting
        $settings_token = 'star_support_cooldown';
        $star_support_cooldown = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : 0;
        //error_log('$star_support_cooldown  = '.$star_support_cooldown);
        return $star_support_cooldown;
    }

    // Define a new function for setting the value of the battle settings called "star_support_cooldown" and returning it
    public static function set_star_support_cooldown($value){
        //error_log('rpg_prototype::set_star_support_cooldown($value = '.$value.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Set the value of the "star_support_cooldown" setting
        $settings_token = 'star_support_cooldown';
        $star_support_cooldown = is_numeric($value) ? $value : 0;
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $star_support_cooldown;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$star_support_cooldown);
    }

    // Define a new function for decrementing the value of star support cooldown by a defined amount representing a percentage
    public static function decrease_star_support_cooldown($percent = 1){
        //error_log('rpg_prototype::decrement_star_support_cooldown($percent = '.$percent.')');
        // Ensure the session array exists before continuing
        $session_token = rpg_game::session_token();
        // Get the current value of the "star_support_cooldown" setting
        $settings_token = 'star_support_cooldown';
        $star_support_cooldown = isset($_SESSION[$session_token]['battle_settings'][$settings_token]) ? $_SESSION[$session_token]['battle_settings'][$settings_token] : '';
        //error_log('$star_support_cooldown  = '.$star_support_cooldown);
        // Decrement the value of the "star_support_cooldown" setting by the given percentage
        $star_support_cooldown = $star_support_cooldown - $percent; //($star_support_cooldown * ($percent / 100));
        if ($star_support_cooldown < 0){ $star_support_cooldown = 0; }
        else { $star_support_cooldown = round($star_support_cooldown, 2); }
        //error_log('$star_support_cooldown  = '.$star_support_cooldown);
        // Set the value of the "star_support_cooldown" setting
        $_SESSION[$session_token]['battle_settings'][$settings_token] = $star_support_cooldown;
        //error_log('$_SESSION[\''.$settings_token.'\'][\'battle_settings\'][\''.$settings_token.'\'] = '.$star_support_cooldown);
    }
    public static function dec_star_support_cooldown($percent = 1){
        //error_log('rpg_prototype::dec_star_support_cooldown($percent = '.$percent.')');
        return self::decrease_star_support_cooldown($percent);
    }

    // Define a function for calculating the star support charge given current cooldown vs max
    public static function get_star_support_charge(){
        //error_log('rpg_prototype::get_star_support_charge()');
        $star_support_cooldown = self::get_star_support_cooldown();
        $star_support_cooldown_max = self::get_star_support_cooldown_max();
        $star_support_charge = $star_support_cooldown_max - $star_support_cooldown;
        //error_log('$star_support_charge  = '.$star_support_charge);
        return $star_support_charge;
    }

    // Define a function for collecting the current star support force given collected starforce
    public static function get_star_support_force(){
        //error_log('rpg_prototype::get_star_support_force()');
        $num_stars_unlocked = mmrpg_prototype_stars_unlocked();
        $num_stars_total = count(mmrpg_prototype_possible_stars());
        $force_amount = round((($num_stars_unlocked / $num_stars_total) * 100), 2);
        //error_log('$num_stars_unlocked = '.$num_stars_unlocked);
        //error_log('$num_stars_total = '.$num_stars_total);
        //error_log('$force_amount = '.$force_amount);
        return $force_amount;
    }

}
?>