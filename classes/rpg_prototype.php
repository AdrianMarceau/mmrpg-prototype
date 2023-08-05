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

    // Define a function for checking if a prototype battle has been completed
    public static function battle_complete($player_token, $battle_token){
        // Check if this battle has been completed and return true is it was
        $session_token = rpg_game::session_token();
        if (!empty($player_token)){
            return isset($_SESSION[$session_token]['values']['battle_complete'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token][$battle_token] : false;
        } elseif (!empty($_SESSION[$session_token]['values']['battle_complete'])){
            foreach ($_SESSION[$session_token]['values']['battle_complete'] AS $player_token => $player_batles){
                if (isset($player_batles[$battle_token])){ return $player_batles[$battle_token]; }
                else { continue; }
            }
            return false;
        } else {
            return false;
        }
    }

    // Define a function for checking if a prototype battle has been failured
    public static function battle_failure($player_token, $battle_token){
        // Check if this battle has been failured and return true is it was
        $session_token = rpg_game::session_token();
        return isset($_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token] : false;
    }

    // Define a function for counting the number of completed prototype battles
    public static function battles_complete($player_token = '', $unique = true){
        // Define the game session helper var
        $session_token = rpg_game::session_token();
        // Collect the battle complete count from the session if set
        if (!empty($player_token)){
            $temp_battles_complete = isset($_SESSION[$session_token]['values']['battle_complete'][$player_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token] : array();
        } else {
            $temp_battles_complete = array();
            if (isset($_SESSION[$session_token]['values']['battle_complete'])){
                foreach ($_SESSION[$session_token]['values']['battle_complete'] AS $player_token => $battle_array){
                    $temp_battles_complete = array_merge($temp_battles_complete, $battle_array);
                }
            }
            $player_token = '';
        }
        //if (empty($player_token)){ die('$player_token = '.$player_token.', $unique = '.($unique ? 1 : 0).',  $count = '.count($temp_battles_complete).'<br />'.print_r($temp_battles_complete, true)); }
        // Check if only unique battles were requested or ALL battles
        if ($unique == true){
         $temp_count = count($temp_battles_complete);
         return $temp_count;
        } else {
         $temp_count = 0;
         foreach ($temp_battles_complete AS $info){ $temp_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
         return $temp_count;
        }
    }

    // Define a function for counting the number of failured prototype battles
    public static function battles_failure($player_token, $unique = true){
        // Define the game session helper var
        $session_token = rpg_game::session_token();
        // Collect the battle failure count from the session if set
        $temp_battle_failures = isset($_SESSION[$session_token]['values']['battle_failure'][$player_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token] : array();
        // Check if only unique battles were requested or ALL battles
        if (!empty($unique)){
         $temp_count = count($temp_battle_failures);
         return $temp_count;
        } else {
         $temp_count = 0;
         foreach ($temp_battle_failures AS $info){ $temp_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
         return $temp_count;
        }
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
        if ($show_items
            && !empty($robot->robot_item)
            && empty($robot->counters['item_disabled'])
            && !empty($mmrpg_items_index[$robot->robot_item])){
            $robot_title .= ' | + '.$mmrpg_items_index[$robot->robot_item]['item_name'].' ';
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
        $robot_core2_type = !empty($robot->robot_core2) ? $robot->robot_core2 : '';
        if (!empty($robot->robot_item) && preg_match('/-core$/', $robot->robot_item)){
            $item_core_type = preg_replace('/-core$/', '', $robot->robot_item);
            if (empty($robot_core2_type) && $robot_core_type != $item_core_type){ $robot_core2_type = $item_core_type; }
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
        $robot_sprite['class'] = 'sprite sprite_'.$robot_sprite['image_size_text'].' sprite_'.$robot_sprite['image_size_text'].'_'.($robot->robot_energy > 0 ? ($robot->robot_energy > ($robot->robot_base_energy/2) ? 'base' : 'defend') : 'defeat').' ';
        $robot_sprite['style'] = 'background-image: url('.$robot_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.');  top: 5px; left: 5px; ';
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
        $btn_type = 'robot_type robot_type_'.(!empty($robot->robot_core) ? $robot->robot_core : 'none').(!empty($robot->robot_core2_type) ? '_'.$robot->robot_core2_type : '');
        $btn_class = 'button action_'.$action_token.' '.$action_token.'_'.$robot->robot_token.' '.$btn_type.' block_'.$block_num.' ';
        $btn_action = $action_token.'_'.$robot->robot_id.'_'.$robot->robot_token;
        $btn_info_new = $show_as_new ? ' new' : '';
        $btn_info_circle = '<span class="info color'.$btn_info_new.'" data-click-tooltip="'.$robot_title_tooltip.'" data-tooltip-type="'.$btn_type.'">';
            $btn_info_circle .= '<i class="fa fas fa-info-circle color '.$robot_core_or_none.'"></i>';
            if (!empty($robot_core2)){ $btn_info_circle .= '<i class="fa fas fa-info-circle color '.$robot_core2.'"></i>'; }
        $btn_info_circle .= '</span>';

        // Now that everything is ready, we can actually generate the button markup
        ob_start();
        if ($allow_button){
            echo('<a type="button" class="'.$btn_class.'" data-action="'.$btn_action.'" data-preload="'.$robot_sprite['preload'].'" data-position="'.$robot->robot_position.'/'.$robot->robot_key.'" '.$order_button_markup.'>'.
                    '<label>'.
                        $btn_info_circle.
                        $robot_sprite['markup'].
                        $robot_label.
                    '</label>'.
                '</a>');
        } else {
            $btn_class .= 'button_disabled ';
            echo('<a type="button" class="'.$btn_class.'" data-position="'.$robot->robot_position.'/'.$robot->robot_key.'">'.
                    '<label>'.
                        $btn_info_circle.
                        $robot_sprite['markup'].
                        $robot_label.
                    '</label>'.
                '</a>');
        }
        $robot_button_markup = ob_get_clean();

        // Return the generated markup
        return $robot_button_markup;

    }




}
?>