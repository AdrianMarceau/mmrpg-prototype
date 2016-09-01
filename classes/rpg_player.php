<?
/**
 * Mega Man RPG Player Object
 * <p>The base class for all player objects in the Mega Man RPG Prototype.</p>
 */
class rpg_player extends rpg_object {

    // Define the constructor class
    public function rpg_player(){

        // Update the session keys for this object
        $this->session_key = 'PLAYERS';
        $this->session_token = 'player_token';
        $this->session_id = 'player_id';
        $this->class = 'player';
        $this->multi = 'players';

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal battle pointer
        $this->battle = isset($args[0]) ? $args[0] : array();
        $this->battle_id = $this->battle->battle_id;
        $this->battle_token = $this->battle->battle_token;

        // Collect current player data from the function if available
        $this_playerinfo = isset($args[1]) ? $args[1] : array('player_id' => 0, 'player_token' => 'player');

        // Now load the player data from the session or index
        $this->player_load($this_playerinfo);

        // Return true on success
        return true;

    }

    // Define a public function for manually loading data
    public function player_load($this_playerinfo){
        // Pull in the global index
        global $mmrpg_index;

        // Collect current player data from the session if available
        $this_playerinfo_backup = $this_playerinfo;
        if (isset($_SESSION['PLAYERS'][$this_playerinfo['player_id']])){
            $this_playerinfo = $_SESSION['PLAYERS'][$this_playerinfo['player_id']];
        }
        // Otherwise, collect player data from the index
        else {
            // Copy over the base contents from the players index
            $this_playerinfo = $mmrpg_index['players'][$this_playerinfo['player_token']];
        }
        $this_playerinfo = array_replace($this_playerinfo, $this_playerinfo_backup);

        // Define the internal player values using the collected array
        $this->flags = isset($this_playerinfo['flags']) ? $this_playerinfo['flags'] : array();
        $this->counters = isset($this_playerinfo['counters']) ? $this_playerinfo['counters'] : array();
        $this->values = isset($this_playerinfo['values']) ? $this_playerinfo['values'] : array();
        $this->history = isset($this_playerinfo['history']) ? $this_playerinfo['history'] : array();
        $this->player_id = isset($this_playerinfo['player_id']) ? $this_playerinfo['player_id'] : 0;
        $this->player_name = isset($this_playerinfo['player_name']) ? $this_playerinfo['player_name'] : 'Robot';
        $this->player_token = isset($this_playerinfo['player_token']) ? $this_playerinfo['player_token'] : 'player';
        $this->player_image = isset($this_playerinfo['player_image']) ? $this_playerinfo['player_image'] : $this->player_token;
        $this->player_image_size = isset($this_playerinfo['player_image_size']) ? $this_playerinfo['player_image_size'] : 40;
        $this->player_description = isset($this_playerinfo['player_description']) ? $this_playerinfo['player_description'] : '';
        $this->player_energy = isset($this_playerinfo['player_energy']) ? $this_playerinfo['player_energy'] : 0;
        $this->player_attack = isset($this_playerinfo['player_attack']) ? $this_playerinfo['player_attack'] : 0;
        $this->player_defense = isset($this_playerinfo['player_defense']) ? $this_playerinfo['player_defense'] : 0;
        $this->player_speed = isset($this_playerinfo['player_speed']) ? $this_playerinfo['player_speed'] : 0;
        $this->player_base_energy = isset($this_playerinfo['player_base_energy']) ? $this_playerinfo['player_base_energy'] : $this->player_energy;
        $this->player_base_attack = isset($this_playerinfo['player_base_attack']) ? $this_playerinfo['player_base_attack'] : $this->player_attack;
        $this->player_base_defense = isset($this_playerinfo['player_base_defense']) ? $this_playerinfo['player_base_defense'] : $this->player_defense;
        $this->player_base_speed = isset($this_playerinfo['player_base_speed']) ? $this_playerinfo['player_base_speed'] : $this->player_speed;
        $this->player_robots = isset($this_playerinfo['player_robots']) ? $this_playerinfo['player_robots'] : array();
        $this->player_abilities = isset($this_playerinfo['player_abilities']) ? $this_playerinfo['player_abilities'] : array();
        $this->player_items = isset($this_playerinfo['player_items']) ? $this_playerinfo['player_items'] : array();
        $this->player_side = isset($this_playerinfo['player_side']) ? $this_playerinfo['player_side'] : 'left';
        $this->player_autopilot = isset($this_playerinfo['player_autopilot']) ? $this_playerinfo['player_autopilot'] : false;
        $this->player_quotes = isset($this_playerinfo['player_quotes']) ? $this_playerinfo['player_quotes'] : array();
        $this->player_rewards = isset($this_playerinfo['player_rewards']) ? $this_playerinfo['player_rewards'] : array();
        $this->player_starforce = isset($this_playerinfo['player_starforce']) ? $this_playerinfo['player_starforce'] : array();
        $this->player_frame = isset($this_playerinfo['player_frame']) ? $this_playerinfo['player_frame'] : 'base';
        $this->player_frame_index = isset($this_playerinfo['player_frame_index']) ? $this_playerinfo['player_frame_index'] : array('base','taunt','victory','defeat','command','damage');
        $this->player_frame_offset = isset($this_playerinfo['player_frame_offset']) ? $this_playerinfo['player_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $this->player_points = isset($this_playerinfo['player_points']) ? $this_playerinfo['player_points'] : 0;
        $this->player_switch = isset($this_playerinfo['player_switch']) ? $this_playerinfo['player_switch'] : 1;
        $this->player_next_action = isset($this_playerinfo['player_next_action']) ? $this_playerinfo['player_next_action'] : 'auto';
//    if (empty($this->player_id)){
//      $this->player_id = md5(substr(md5($this->player_side), 0, 10));
//    }

        // Define the internal player base values using the players index array
        $this->player_base_name = isset($this_playerinfo['player_base_name']) ? $this_playerinfo['player_base_name'] : $this->player_name;
        $this->player_base_token = isset($this_playerinfo['player_base_token']) ? $this_playerinfo['player_base_token'] : $this->player_token;
        $this->player_base_image = isset($this_playerinfo['player_base_image']) ? $this_playerinfo['player_base_image'] : $this->player_image;
        $this->player_base_image_size = isset($this_playerinfo['player_base_image_size']) ? $this_playerinfo['player_base_image_size'] : $this->player_image_size;
        $this->player_base_description = isset($this_playerinfo['player_base_description']) ? $this_playerinfo['player_base_description'] : $this->player_description;
        $this->player_base_robots = isset($this_playerinfo['player_base_robots']) ? $this_playerinfo['player_base_robots'] : $this->player_robots;
        $this->player_base_abilities = isset($this_playerinfo['player_base_abilities']) ? $this_playerinfo['player_base_abilities'] : $this->player_abilities;
        $this->player_base_items = isset($this_playerinfo['player_base_items']) ? $this_playerinfo['player_base_items'] : $this->player_items;
        $this->player_base_quotes = isset($this_playerinfo['player_base_quotes']) ? $this_playerinfo['player_base_quotes'] : $this->player_quotes;
        $this->player_base_rewards = isset($this_playerinfo['player_base_rewards']) ? $this_playerinfo['player_base_rewards'] : $this->player_rewards;
        $this->player_base_starforce = isset($this_playerinfo['player_base_starforce']) ? $this_playerinfo['player_base_starforce'] : $this->player_starforce;
        $this->player_base_points = isset($this_playerinfo['player_base_points']) ? $this_playerinfo['player_base_points'] : $this->player_points;
        $this->player_base_switch = isset($this_playerinfo['player_base_switch']) ? $this_playerinfo['player_base_switch'] : $this->player_switch;

        // Remove any abilities that do not exist in the index
        if (!empty($this->player_abilities)){
            foreach ($this->player_abilities AS $key => $token){
                if (empty($token)){ unset($this->player_abilities[$key]); }
            }
            $this->player_abilities = array_values($this->player_abilities);
        }

        /*
        // Remove any items that do not exist in the index
        if (!empty($this->player_items)){
            foreach ($this->player_items AS $key => $token){
                if (empty($token)){ unset($this->player_items[$key]); }
            }
            $this->player_items = array_values($this->player_items);
        }
        */

        // Pull in session starforce if available for human players
        if (empty($this->player_starforce) && $this->player_side == 'left'){
            if (!empty($_SESSION['GAME']['values']['star_force'])){
                $this->player_starforce = $_SESSION['GAME']['values']['star_force'];
            }
        }

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define a function for adding a new robot to this player's object data
    public function load_robot($this_robotinfo, $this_key, $apply_bonuses = false){
        //$GLOBALS['DEBUG']['checkpoint_line'] = 'class.player.php : line 107 <pre>'.print_r($this->player_robots, true).'</pre>';
        ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
        $this_robot = new rpg_robot($this->battle, $this, $this_robotinfo);
        ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
        if ($apply_bonuses){ $this_robot->apply_stat_bonuses(); }
        ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
        $this_export_array = $this_robot->export_array();
        ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
        $this->player_robots[$this_key] = $this_export_array;
        unset($this_robot);
        return true;
    }

    // Define public print functions for markup generation
    public function print_name(){ return '<span class="player_name player_type">'.$this->player_name.'</span>'; }
    public function print_token(){ return '<span class="player_token">'.$this->player_token.'</span>'; }
    public function print_description(){ return '<span class="player_description">'.$this->player_description.'</span>'; }
    public function print_quote($quote_type, $this_find = array(), $this_replace = array()){
        global $mmrpg_index;
        // Define the quote text variable
        $quote_text = '';
        // If the player is visible and has the requested quote text
        if ($this->player_token != 'player' && isset($this->player_quotes[$quote_type])){
            // Collect the quote text with any search/replace modifications
            $this_quote_text = str_replace($this_find, $this_replace, $this->player_quotes[$quote_type]);
            // Collect the text colour for this player
            $this_type_token = str_replace('dr-', '', $this->player_token);
            $this_text_colour = !empty($mmrpg_index['types'][$this_type_token]) ? $mmrpg_index['types'][$this_type_token]['type_colour_light'] : array(200, 200, 200);
            foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] += 20; }
            // Generate the quote text markup with the appropriate RGB values
            $quote_text = '<span style="color: rgb('.implode(',', $this_text_colour).');">&quot;<em>'.$this_quote_text.'</em>&quot;</span>';
        }
        return $quote_text;
    }

    // Define a function for checking if this player has a specific ability
    public function has_ability($ability_token){
        if (empty($this->player_abilities) || empty($ability_token)){ return false; }
        elseif (in_array($ability_token, $this->player_abilities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this player has a specific item
    public function has_item($item_token){
        if (empty($this->player_items) || empty($item_token)){ return false; }
        elseif (in_array($item_token, $this->player_items)){ return true; }
        else { return false; }
    }

    // Define a function for generating player canvas variables
    public function canvas_markup($options){

        // Delegate markup generation to the canvas class
        return rpg_canvas::player_markup($this, $options);

    }

    // Define a function for generating player console variables
    public function console_markup($options){

        // Delegate markup generation to the console class
        return rpg_console::player_markup($this, $options);

    }

    // Define a public function for sorting robots by their active status
    public static function robot_sort_by_active($info1, $info2){
        //$info1['robot_key'] = $info1['robot_key'] < 8 ? $info1['robot_key'] + 1 : 1;
        //$info2['robot_key'] = $info2['robot_key'] < 8 ? $info2['robot_key'] + 1 : 1;
        if ($info1['robot_position'] == 'active'){ return -1; }
        elseif ($info1['robot_key'] < $info2['robot_key']){ return -1; }
        elseif ($info1['robot_key'] > $info2['robot_key']){ return 1; }
        else { return 0; }
    }


    // Define a static function for printing out the robot's editor markup
    public static function abilities_sort_for_editor($ability_one, $ability_two){
        $ability_token_one = $ability_one['ability_token'];
        $ability_token_two = $ability_two['ability_token'];
        if ($ability_token_one > $ability_token_two){ return 1; }
        elseif ($ability_token_one < $ability_token_two){ return -1; }
        else { return 0; }
    }


    // Define a static function for printing out the robot's editor markup
    public static function fields_sort_for_editor($field_one, $field_two){
        static $mmrpg_fields_index;
        if (empty($mmrpg_fields_index)){ $mmrpg_fields_index = rpg_field::get_index(); }
        $field_token_one = $field_one['field_token'];
        $field_token_two = $field_two['field_token'];
        if (!isset($mmrpg_fields_index[$field_token_one])){ return 0; }
        if (!isset($mmrpg_fields_index[$field_token_two])){ return 0; }
        $field_one = $mmrpg_fields_index[$field_token_one];
        $field_two = $mmrpg_fields_index[$field_token_two];
        //die('<pre>'.print_r($field_one, true).'</pre>');
        if ($field_one['field_game'] > $field_two['field_game']){ return 1; }
        elseif ($field_one['field_game'] < $field_two['field_game']){ return -1; }
        if ($field_one['field_token'] > $field_two['field_token']){ return 1; }
        elseif ($field_one['field_token'] < $field_two['field_token']){ return -1; }
        else { return 0; }
    }

    // Define a static function for printing out the robot's editor markup
    public static function items_sort_for_editor($item_one, $item_two){
        global $mmrpg_index;
        $item_token_one = $item_one['item_token'];
        $item_token_two = $item_two['item_token'];
        list($x, $kind_one, $size_one) = explode('-', $item_token_one);
        list($x, $kind_two, $size_two) = explode('-', $item_token_two);

        if ($kind_one == 'energy' && $kind_two != 'energy'){ return -1; }
        elseif ($kind_one != 'energy' && $kind_two == 'energy'){ return 1; }
        elseif ($kind_one == 'weapon' && $kind_two != 'weapon'){ return -1; }
        elseif ($kind_one != 'weapon' && $kind_two == 'weapon'){ return 1; }
        elseif ($kind_one == 'core' && $kind_two != 'core'){ return -1; }
        elseif ($kind_one != 'core' && $kind_two == 'core'){ return 1; }

        elseif ($size_one == 'pellet' && $size_two != 'pellet'){ return -1; }
        elseif ($size_one != 'pellet' && $size_two == 'pellet'){ return 1; }
        elseif ($size_one == 'capsule' && $size_two != 'capsule'){ return -1; }
        elseif ($size_one != 'capsule' && $size_two == 'capsule'){ return 1; }
        elseif ($size_one == 'tank' && $size_two != 'tank'){ return -1; }
        elseif ($size_one != 'tank' && $size_two == 'tank'){ return 1; }

        elseif ($item_one['item_token'] > $item_two['item_token']){ return 1; }
        elseif ($item_one['item_token'] < $item_two['item_token']){ return -1; }
        else { return 0; }
    }

    public static function trigger_item_drop($this_battle, $target_player, $target_robot, $this_robot, $item_reward_key, $item_reward_token, $item_quantity_dropped = 1){
        global $mmrpg_index;

        // Create the temporary item object for event creation
        $item_reward_info = rpg_item::get_index_info($item_reward_token);
        $temp_item = new rpg_item($this_battle, $target_player, $target_robot, $item_reward_info);
        $temp_item->item_name = $item_reward_info['item_name'];
        $temp_item->item_image = $item_reward_info['item_token'];
        $temp_item->item_quantity = $item_quantity_dropped;
        $temp_item->update_session();

        // Collect or define the item variables
        $temp_item_token = $item_reward_info['item_token'];
        $temp_item_name = $item_reward_info['item_name'];
        $temp_item_colour = !empty($item_reward_info['item_type']) ? $item_reward_info['item_type'] : 'none';
        if (!empty($item_reward_info['item_type2'])){ $temp_item_colour .= '_'.$item_reward_info['item_type2']; }

        // Create the session variable for this item if it does not exist and collect its value
        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
        $temp_item_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];

        // If this item is already at the quantity limit, skip it entirely
        if ($temp_item_quantity >= MMRPG_SETTINGS_ITEMS_MAXQUANTITY){
            $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;
            $temp_item_quantity = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;
            return true;
        }

        // Display the robot reward message markup
        $event_header = $temp_item_name.' Item Drop';
        if ($item_quantity_dropped > 1){
            $event_body = rpg_battle::random_positive_word().' The disabled '.$this_robot->print_name().' dropped <strong>x'.$item_quantity_dropped.'</strong> '.' <span class="item_name item_type item_type_'.$temp_item_colour.'">'.$temp_item_name.($item_quantity_dropped > 1 ? 's' : '').'</span>!<br />';
            $event_body .= $target_player->print_name().' added the dropped items to the inventory.';
        } else {
            $event_body = rpg_battle::random_positive_word().' The disabled '.$this_robot->print_name().' dropped '.(preg_match('/^(a|e|i|o|u)/i', $temp_item_name) ? 'an' : 'a').' <span class="item_name item_type item_type_'.$temp_item_colour.'">'.$temp_item_name.'</span>!<br />';
            $event_body .= $target_player->print_name().' added the dropped item to the inventory.';
        }
        $event_options = array();
        $event_options['console_show_target'] = false;
        $event_options['this_header_float'] = $target_player->player_side;
        $event_options['this_body_float'] = $target_player->player_side;
        $event_options['this_item'] = $temp_item;
        $event_options['this_item_image'] = 'icon';
        $event_options['this_item_quantity'] = $item_quantity_dropped;
        $event_options['event_flag_victory'] = true;
        $event_options['console_show_this_player'] = false;
        $event_options['console_show_this_robot'] = false;
        $event_options['console_show_this_item'] = true;
        $event_options['canvas_show_this_item'] = true;
        $target_player->player_frame = $item_reward_key % 3 == 0 ? 'victory' : 'taunt';
        $target_player->update_session();
        $target_robot->robot_frame = $item_reward_key % 2 == 0 ? 'taunt' : 'base';
        $target_robot->update_session();
        $temp_item->item_frame = 'base';
        $temp_item->item_frame_offset = array('x' => 260, 'y' => 0, 'z' => 10);
        if ($item_quantity_dropped > 1){ $temp_item->item_name = $temp_item->item_base_name.'s'; }
        else { $temp_item->item_name = $temp_item->item_base_name; }
        $temp_item->update_session();
        $this_battle->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

        // Create and/or increment the session variable for this item increasing its quantity
        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] += $item_quantity_dropped;

        // If this item is not on the list of key items (un-equippable), don't add it
        $temp_key_items = array('large-screw', 'small-screw', 'heart', 'star');
        if (!in_array($temp_item_token, $temp_key_items)){
            // If there is room in this player's current item omega, add the new item
            $temp_session_token = $target_player->player_token.'_this-item-omega_prototype';
            if (!empty($_SESSION['GAME']['values'][$temp_session_token])){
                $temp_count = count($_SESSION['GAME']['values'][$temp_session_token]);
                if ($temp_count < 8 && !in_array($temp_item_token, $_SESSION['GAME']['values'][$temp_session_token])){
                    $_SESSION['GAME']['values'][$temp_session_token][] = $temp_item_token;
                    $target_player->player_items[] = $temp_item_token;
                    $target_player->update_session();
                }
            }
        }

        // Return true on success
        return true;

    }

    // Define a public function updating internal varibales
    public function update_variables(){

        // Update parent objects first
        //$this->battle->update_variables();

        // Calculate this robot's count variables
        $this->counters['abilities_total'] = count($this->player_abilities);
        $this->counters['items_total'] = count($this->player_items);

        // Create the current robot value for calculations
        $this->values['current_robot'] = false;
        $this->values['current_robot_enter'] = isset($this->values['current_robot_enter']) ? $this->values['current_robot_enter'] : false;

        // Create the flag variables if they don't exist

        // Create the counter variables and defeault to zero
        $this->counters['robots_total'] = 0;
        $this->counters['robots_active'] = 0;
        $this->counters['robots_disabled'] = 0;
        $this->counters['robots_positions'] = array(
            'active' => 0,
            'bench' => 0
            );

        // Create the value variables and default to empty
        $this->values['robots_active'] = array();
        $this->values['robots_disabled'] = array();
        $this->values['robots_positions'] = array(
            'active' => array(),
            'bench' => array()
            );

        // Ensure this player has robots to loop over
        if (!empty($this->player_robots)){

            // Loop through each of the player's robots and check status
            foreach ($this->player_robots AS $this_key => $this_robotinfo){
                // Ensure a token an idea are provided at least
                if (empty($this_robotinfo['robot_id']) || empty($this_robotinfo['robot_token'])){ continue; }
                // Define the current temp robot object using the loaded robot data
                $temp_robot = new rpg_robot($this->battle, $this, $this_robotinfo);
                $temp_robot->robot_key = $this_key;
                $temp_robot->update_session();
                // Check if this robot is in the active position
                if ($temp_robot->robot_position == 'active'){
                    $this->values['current_robot'] = $temp_robot->robot_string;
                }
                // Check if this robot is in active status
                if ($temp_robot->robot_status == 'active'){
                    // Increment the active robot counter
                    $this->counters['robots_active']++;
                    // Add this robot to the active robots array
                    $this->values['robots_active'][] = &$this->player_robots[$this_key]; //$this_info;
                    // Check if this robot is in the active position
                    if ($temp_robot->robot_position == 'active'){
                        // Increment the active robot counter
                        $this->counters['robots_positions']['active']++;
                        // Add this robot to the active robots array
                        $this->values['robots_positions']['active'][] = &$this->player_robots[$this_key]; //$this_info;
                    }
                    // Otherwise, if this robot is in benched position
                    elseif ($temp_robot->robot_position == 'bench'){
                        // Increment the bench robot counter
                        $this->counters['robots_positions']['bench']++;
                        // Add this robot to the bench robots array
                        $this->values['robots_positions']['bench'][] = &$this->player_robots[$this_key]; //$this_info;
                    }
                }
                // Otherwise, if this robot is in disabled status
                elseif ($temp_robot->robot_status == 'disabled'){
                    // Increment the disabled robot counter
                    $this->counters['robots_disabled']++;
                    // Add this robot to the disabled robots array
                    $this->values['robots_disabled'][] = &$this->player_robots[$this_key]; //$this_info;
                }

                // Increment the robot total by default
                $this->counters['robots_total']++;
                // Update or create this robot's session object
                //$temp_robot->update_session();
                unset($temp_robot);
            }

        }

        // If an active robot was not found, reset the turn counter
        if (empty($this->values['current_robot'])){
            $this->values['current_robot_enter'] = false;
        }

        // Now collect an export array for this object
        $this_data = $this->export_array();

        // Update the parent battle variable
        $this->battle->values['players'][$this->player_id] = $this_data;

        // Return true on success
        return true;

    }

    // Define a public, static function for resetting player values to base
    public static function reset_variables($this_data){
        $this_data['player_flags'] = array();
        $this_data['player_counters'] = array();
        $this_data['player_values'] = array();
        $this_data['player_history'] = array();
        $this_data['player_name'] = $this_data['player_base_name'];
        $this_data['player_token'] = $this_data['player_base_token'];
        $this_data['player_description'] = $this_data['player_base_description'];
        $this_data['player_robots'] = $this_data['player_base_robots'];
        $this_data['player_quotes'] = $this_data['player_base_quotes'];
        return $this_data;
    }

    // Define a public function for updating this player's session
    public function update_session(){

        // Update any internal counters
        $this->update_variables();

        // Request parent battle object to update as well
        //$this->battle->update_session();

        // Update the session with the export array
        $this_data = $this->export_array();
        $_SESSION['PLAYERS'][$this->player_id] = $this_data;
        $this->battle->values['players'][$this->player_id] = $this_data;

        // Return true on success
        return true;

    }

    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal player fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_token' => $this->battle_token,
            'player_id' => $this->player_id,
            'player_name' => $this->player_name,
            'player_token' => $this->player_token,
            'player_image' => $this->player_image,
            'player_image_size' => $this->player_image_size,
            'player_description' => $this->player_description,
            'player_energy' => $this->player_energy,
            'player_attack' => $this->player_attack,
            'player_defense' => $this->player_defense,
            'player_base_speed' => $this->player_base_speed,
            'player_base_energy' => $this->player_base_energy,
            'player_base_attack' => $this->player_base_attack,
            'player_base_defense' => $this->player_base_defense,
            'player_base_speed' => $this->player_base_speed,
            'player_robots' => $this->player_robots,
            'player_abilities' => $this->player_abilities,
            'player_items' => $this->player_items,
            'player_quotes' => $this->player_quotes,
            'player_rewards' => $this->player_rewards,
            'player_starforce' => $this->player_starforce,
            'player_points' => $this->player_points,
            'player_switch' => $this->player_switch,
            'player_next_action' => $this->player_next_action,
            'player_base_name' => $this->player_base_name,
            'player_base_token' => $this->player_base_token,
            'player_base_image' => $this->player_base_image,
            'player_base_image_size' => $this->player_base_image_size,
            'player_base_description' => $this->player_base_description,
            'player_base_robots' => $this->player_base_robots,
            'player_base_abilities' => $this->player_base_abilities,
            'player_base_items' => $this->player_base_items,
            'player_base_quotes' => $this->player_base_quotes,
            'player_base_rewards' => $this->player_base_rewards,
            'player_base_starforce' => $this->player_base_starforce,
            'player_base_points' => $this->player_base_points,
            'player_base_switch' => $this->player_base_switch,
            'player_side' => $this->player_side,
            'player_autopilot' => $this->player_autopilot,
            'player_frame' => $this->player_frame,
            'player_frame_index' => $this->player_frame_index,
            'player_frame_offset' => $this->player_frame_offset,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }

    // Define a static function for printing out the player's database markup
    public static function print_database_markup($player_info, $print_options = array()){

        // Define the markup variable
        $this_markup = '';

        // Define the global variables
        global $db;
        global $mmrpg_index, $this_current_uri, $this_current_url;
        global $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;

        // Define the print style defaults
        if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
        if ($print_options['layout_style'] == 'website'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = true; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = true; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'website_compact'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = true; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        }

        // Collect the player sprite dimensions
        $player_image_size = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;
        $player_image_size_text = $player_image_size.'x'.$player_image_size;
        $player_image_token = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
        $player_type_token = !empty($player_info['player_type']) ? $player_info['player_type'] : 'none';

        // Define the sprite sheet alt and title text
        $player_sprite_size = $player_image_size * 2;
        $player_sprite_size_text = $player_sprite_size.'x'.$player_sprite_size;
        $player_sprite_title = $player_info['player_name'];
        //$player_sprite_title = $player_info['player_number'].' '.$player_info['player_name'];
        //$player_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

        // Define the sprite frame index for robot images
        $player_sprite_frames = array('base','taunt','victory','defeat','command','damage');

        // Start the output buffer
        ob_start();
        ?>
        <div class="database_container database_player_container" data-token="<?=$player_info['player_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">
            <a class="anchor" id="<?=$player_info['player_token']?>">&nbsp;</a>
            <div class="subbody event event_triple event_visible" data-token="<?=$player_info['player_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

                <? if($print_options['show_mugshot']): ?>
                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <? if($print_options['show_key'] !== false): ?>
                            <div class="mugshot player_type player_type_<?= !empty($player_info['player_type']) ? $player_info['player_type'] : 'none' ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
                        <? endif; ?>
                        <div class="mugshot player_type player_type_<?= !empty($player_info['player_type']) ? $player_info['player_type'] : 'none' ?>"><div style="background-image: url(images/players/<?= $player_image_token ?>/mug_right_<?= $player_image_size_text ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_player sprite_40x40 sprite_40x40_mug sprite_size_<?= $player_image_size_text ?> sprite_size_<?= $player_image_size_text ?>_mug player_status_active player_position_active"><?=$player_info['player_name']?>'s Mugshot</div></div>
                    </div>
                <? endif; ?>


                <? if($print_options['show_basics']): ?>
                    <h2 class="header header_left player_type_<?= $player_type_token ?>" style="margin-right: 0;">
                        <? if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="database/players/<?= $player_info['player_token'] ?>/"><?= $player_info['player_name'] ?></a>
                        <? else: ?>
                            <?= $player_info['player_name'] ?>&#39;s Data
                        <? endif; ?>
                        <? if (!empty($player_info['player_type'])): ?>
                            <span class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($player_info['player_type']) ?> Type</span>
                        <? endif; ?>
                    </h2>
                    <div class="body body_left" style="margin-right: 0; padding: 2px 3px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="48%" />
                                <col width="1%" />
                                <col width="48%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Name :</label>
                                        <span class="player_name player_type"><?=$player_info['player_name']?></span>
                                    </td>
                                    <td class="middle">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Bonus :</label>
                                        <?
                                            // Display any special boosts this player has
                                            if (!empty($player_info['player_energy'])){ echo '<span class="player_name player_type player_type_energy">Robot Energy +'.$player_info['player_energy'].'%</span>'; }
                                            elseif (!empty($player_info['player_attack'])){ echo '<span class="player_name player_type player_type_attack">Robot Attack +'.$player_info['player_attack'].'%</span>'; }
                                            elseif (!empty($player_info['player_defense'])){ echo '<span class="player_name player_type player_type_defense">Robot Defense +'.$player_info['player_defense'].'%</span>'; }
                                            elseif (!empty($player_info['player_speed'])){ echo '<span class="player_name player_type player_type_speed">Robot Speed +'.$player_info['player_speed'].'%</span>'; }
                                            else { echo '<span class="player_name player_type player_type_none">None</span>'; }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <? if($print_options['show_quotes']): ?>
                            <table class="full" style="margin: 5px auto 10px;">
                                <colgroup>
                                    <col width="100%" />
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Start Quote : </label>
                                            <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_start']) ? $player_info['player_quotes']['battle_start'] : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Taunt Quote : </label>
                                            <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_taunt']) ? $player_info['player_quotes']['battle_taunt'] : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Victory Quote : </label>
                                            <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_victory']) ? $player_info['player_quotes']['battle_victory'] : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Defeat Quote : </label>
                                            <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_defeat']) ? $player_info['player_quotes']['battle_defeat'] : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <? endif; ?>
                    </div>
                <? endif; ?>

                <? if($print_options['show_description'] && !empty($player_info['player_description2'])): ?>

                    <h2 class="header header_left player_type_<?= $player_type_token ?>" style="margin-right: 0;">
                        <?= $player_info['player_name'] ?>&#39;s Description
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="player_description" style="text-align: justify; padding: 0 4px;"><?= $player_info['player_description2'] ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_sprites'] && (!isset($player_info['player_image_sheets']) || $player_info['player_image_sheets'] !== 0) && $player_image_token != 'player' ): ?>
                    <h2 id="sprites" class="header header_full player_type_<?= $player_type_token ?>" style="margin: 10px 0 0; text-align: left;">
                        <?=$player_info['player_name']?>&#39;s Sprites
                    </h2>
                    <div class="body body_full" style="margin: 0; padding: 10px; min-height: auto;">
                        <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
                            <?
                            // Show the player mugshot sprite
                            foreach (array('right', 'left') AS $temp_direction){
                                $temp_title = $player_sprite_title.' | Mugshot Sprite '.ucfirst($temp_direction);
                                $temp_label = 'Mugshot '.ucfirst(substr($temp_direction, 0, 1));
                                echo '<div style="'.($player_sprite_size <= 80 ? 'padding-top: 20px; ' : '').'float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$player_sprite_size.'px; height: '.$player_sprite_size.'px; overflow: hidden;">';
                                    echo '<img style="margin-left: 0;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/players/'.$player_image_token.'/mug_'.$temp_direction.'_'.$player_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                    echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                echo '</div>';
                            }
                            // Loop through the different frames and print out the sprite sheets
                            foreach ($player_sprite_frames AS $this_key => $this_frame){
                                $margin_left = ceil((0 - $this_key) * $player_sprite_size);
                                foreach (array('right', 'left') AS $temp_direction){
                                    $temp_title = $player_sprite_title.' | '.ucfirst($this_frame).' Sprite '.ucfirst($temp_direction);
                                    $temp_label = ucfirst($this_frame).' '.ucfirst(substr($temp_direction, 0, 1));
                                    echo '<div style="'.($player_sprite_size <= 80 ? 'padding-top: 20px; ' : '').'float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$player_sprite_size.'px; height: '.$player_sprite_size.'px; overflow: hidden;">';
                                        echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/players/'.$player_image_token.'/sprite_'.$temp_direction.'_'.$player_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                        echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <?
                        // Define the editor title based on ID
                        $temp_editor_title = 'Undefined';
                        if (!empty($player_info['player_image_editor'])){
                            if ($player_info['player_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
                            elseif ($player_info['player_image_editor'] == 110){ $temp_editor_title = 'MetalMarioX100 / EliteP1'; }
                            elseif ($player_info['player_image_editor'] == 18){ $temp_editor_title = 'Sean Adamson / MetalMan'; }
                        } else {
                            $temp_editor_title = 'Adrian Marceau / Ageman20XX';
                        }
                        ?>
                        <p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 10px; margin-top: 6px;">Sprite Editing by <strong><?= $temp_editor_title ?></strong> <span style="color: #565656;"> | </span> Original Artwork by <strong>Capcom</strong></p>
                    </div>
                <? endif; ?>

                <? if($print_options['show_abilities']): ?>
                    <h2 id="abilities" class="header header_full player_type_<?= $player_type_token ?>" style="margin: 10px 0 0; text-align: left;">
                        <?=$player_info['player_name']?>&#39;s Abilities
                    </h2>
                    <div class="body body_full" style="margin: 0; padding: 2px 3px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="ability_container">
                                        <?
                                        $index_player = $mmrpg_index['players'][$player_info['player_token']];
                                        $player_ability_core = !empty($index_player['player_type']) ? $index_player['player_type'] : false;
                                        $player_ability_list = !empty($index_player['player_abilities']) ? $index_player['player_abilities'] : array();
                                        $player_ability_rewards = !empty($player_info['player_rewards']['abilities']) ? $player_info['player_rewards']['abilities'] : array();
                                        $new_ability_rewards = array();
                                        foreach ($player_ability_rewards AS $this_info){
                                            $new_ability_rewards[$this_info['token']] = $this_info;
                                        }
                                        $player_copy_program = $player_ability_core == 'copy' ? true : false;
                                        //if ($player_copy_program){ $player_ability_list = $temp_all_ability_tokens; }
                                        $player_ability_core_list = array();
                                        if (!empty($player_ability_core)){
                                            foreach ($mmrpg_database_abilities AS $token => $info){
                                                if (!empty($info['ability_type']) && ($player_copy_program || $info['ability_type'] == $player_ability_core)){
                                                    $player_ability_list[] = $info['ability_token'];
                                                    $player_ability_core_list[] = $info['ability_token'];
                                                }
                                            }
                                        }
                                        foreach ($player_ability_list AS $this_token){
                                            if ($this_token == '*'){ continue; }
                                            if (!isset($new_ability_rewards[$this_token])){
                                                if (in_array($this_token, $player_ability_core_list)){ $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }
                                                else { $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }

                                            }
                                        }
                                        $player_ability_rewards = $new_ability_rewards;

                                        //die('<pre>'.print_r($player_ability_rewards, true).'</pre>');

                                        if (!empty($player_ability_rewards)){
                                            $temp_string = array();
                                            $ability_key = 0;
                                            $ability_method_key = 0;
                                            $ability_method = '';
                                            $temp_robot_info = rpg_robot::get_index_info('mega-man');
                                            $temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                                            foreach ($player_ability_rewards AS $this_info){
                                                $this_points = $this_info['points'];
                                                $this_ability = rpg_ability::parse_index_info($temp_abilities_index[$this_info['token']]);
                                                $this_ability_token = $this_ability['ability_token'];
                                                $this_ability_name = $this_ability['ability_name'];
                                                $this_ability_image = !empty($this_ability['ability_image']) ? $this_ability['ability_image']: $this_ability['ability_token'];
                                                $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                                                if (!empty($this_ability_type) && !empty($mmrpg_index['types'][$this_ability_type])){ $this_ability_type = $mmrpg_index['types'][$this_ability_type]['type_name'].' Type'; }
                                                else { $this_ability_type = ''; }
                                                $this_ability_damage = !empty($this_ability['ability_damage']) ? $this_ability['ability_damage'] : 0;
                                                $this_ability_damage2 = !empty($this_ability['ability_damage2']) ? $this_ability['ability_damage2'] : 0;
                                                $this_ability_damage_percent = !empty($this_ability['ability_damage_percent']) ? true : false;
                                                $this_ability_damage2_percent = !empty($this_ability['ability_damage2_percent']) ? true : false;
                                                if ($this_ability_damage_percent && $this_ability_damage > 100){ $this_ability_damage = 100; }
                                                if ($this_ability_damage2_percent && $this_ability_damage2 > 100){ $this_ability_damage2 = 100; }
                                                $this_ability_recovery = !empty($this_ability['ability_recovery']) ? $this_ability['ability_recovery'] : 0;
                                                $this_ability_recovery2 = !empty($this_ability['ability_recovery2']) ? $this_ability['ability_recovery2'] : 0;
                                                $this_ability_recovery_percent = !empty($this_ability['ability_recovery_percent']) ? true : false;
                                                $this_ability_recovery2_percent = !empty($this_ability['ability_recovery2_percent']) ? true : false;
                                                if ($this_ability_recovery_percent && $this_ability_recovery > 100){ $this_ability_recovery = 100; }
                                                if ($this_ability_recovery2_percent && $this_ability_recovery2 > 100){ $this_ability_recovery2 = 100; }
                                                $this_ability_accuracy = !empty($this_ability['ability_accuracy']) ? $this_ability['ability_accuracy'] : 0;
                                                $this_ability_description = !empty($this_ability['ability_description']) ? $this_ability['ability_description'] : '';
                                                $this_ability_description = str_replace('{DAMAGE}', $this_ability_damage, $this_ability_description);
                                                $this_ability_description = str_replace('{RECOVERY}', $this_ability_recovery, $this_ability_description);
                                                $this_ability_description = str_replace('{DAMAGE2}', $this_ability_damage2, $this_ability_description);
                                                $this_ability_description = str_replace('{RECOVERY2}', $this_ability_recovery2, $this_ability_description);
                                                //$this_ability_title_plain = $this_ability_name;
                                                //if (!empty($this_ability_type)){ $this_ability_title_plain .= ' | '.$this_ability_type; }
                                                //if (!empty($this_ability_damage)){ $this_ability_title_plain .= ' | '.$this_ability_damage.' Damage'; }
                                                //if (!empty($this_ability_recovery)){ $this_ability_title_plain .= ' | '.$this_ability_recovery.' Recovery'; }
                                                //if (!empty($this_ability_accuracy)){ $this_ability_title_plain .= ' | '.$this_ability_accuracy.'% Accuracy'; }
                                                //if (!empty($this_ability_description)){ $this_ability_title_plain .= ' | '.$this_ability_description; }
                                                $this_ability_title_plain = rpg_ability::print_editor_title_markup($temp_robot_info, $this_ability);

                                                $this_ability_method = 'points';
                                                $this_ability_method_text = 'Battle Points';
                                                $this_ability_title_html = '<strong class="name">'.$this_ability_name.'</strong>';
                                                if ($this_points > 1){ $this_ability_title_html .= '<span class="points">'.str_pad($this_points, 2, '0', STR_PAD_LEFT).' BP</span>'; }
                                                else { $this_ability_title_html .= '<span class="points">Start</span>'; }
                                                if (!empty($this_ability_type)){ $this_ability_title_html .= '<span class="type">'.$this_ability_type.'</span>'; }
                                                if (!empty($this_ability_damage)){ $this_ability_title_html .= '<span class="damage">'.$this_ability_damage.(!empty($this_ability_damage_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'D' : 'Damage').'</span>'; }
                                                if (!empty($this_ability_recovery)){ $this_ability_title_html .= '<span class="recovery">'.$this_ability_recovery.(!empty($this_ability_recovery_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'R' : 'Recovery').'</span>'; }
                                                if (!empty($this_ability_accuracy)){ $this_ability_title_html .= '<span class="accuracy">'.$this_ability_accuracy.'% Accuracy</span>'; }
                                                $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png';
                                                if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_ability_sprite_path)){ $this_ability_sprite_path = 'images/abilities/ability/icon_left_40x40.png'; }
                                                $this_ability_sprite_html = '<span class="icon"><img src="'.$this_ability_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_ability_name.' Icon" /></span>';
                                                $this_ability_title_html = '<span class="label">'.$this_ability_title_html.'</span>';
                                                //$this_ability_title_html = (is_numeric($this_points) && $this_points > 1 ? 'Lv '.str_pad($this_points, 2, '0', STR_PAD_LEFT).' : ' : $this_points.' : ').$this_ability_title_html;
                                                if ($ability_method != $this_ability_method){
                                                    $temp_separator = '<div class="ability_separator">'.$this_ability_method_text.'</div>';
                                                    $temp_string[] = $temp_separator;
                                                    $ability_method = $this_ability_method;
                                                    $ability_method_key++;
                                                }
                                                if ($this_points >= 0 || !$player_copy_program){
                                                    $temp_markup = '<a href="'.MMRPG_CONFIG_ROOTURL.'database/abilities/'.$this_ability['ability_token'].'/"  class="ability_name ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').'" title="'.$this_ability_title_plain.'" style="">';
                                                    $temp_markup .= '<span class="chrome">'.$this_ability_sprite_html.$this_ability_title_html.'</span>';
                                                    $temp_markup .= '</a>';
                                                    $temp_string[] = $temp_markup;
                                                    $ability_key++;
                                                    continue;
                                                }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="player_ability player_type_none"><span class="chrome">None</span></span>';
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="database/players/<?= $player_info['player_token'] ?>/" rel="permalink">+ Permalink</a>

                <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="database/players/<?= $player_info['player_token'] ?>/" rel="permalink">+ View More</a>

                <? endif; ?>

            </div>
        </div>
        <?
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }

    // Define a static function for printing out the player's editor markup
    public static function print_editor_markup($player_info){

        // Define the markup variable
        $this_markup = '';
        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $allowed_edit_players, $allowed_edit_fields, $global_allow_editing;
        global $allowed_edit_data_count, $allowed_edit_player_count, $first_player_token;
        global $key_counter, $player_key, $player_counter, $player_rewards, $player_field_rewards, $player_item_rewards, $temp_player_totals, $player_options_markup;
        global $mmrpg_database_robots, $mmrpg_database_items;
        $session_token = mmrpg_game_token();

        // If either fo empty, return error
        if (empty($player_info)){ return 'error:player-empty'; }

        // Collect the approriate database indexes
        if (empty($mmrpg_database_robots)){ $mmrpg_database_robots = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token'); }
        if (empty($mmrpg_database_items)){ $mmrpg_database_items = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_class = 'item' AND ability_flag_complete = 1;", 'ability_token'); }

        // Define the quick-access variables for later use
        $player_token = $player_info['player_token'];
        if (!isset($first_player_token)){ $first_player_token = $player_token; }

        // Define the player's image and size if not defined
        $player_info['player_image'] = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
        $player_info['player_image_size'] = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;

        // Define the player's battle points total, battles complete, and other details
        $player_info['player_points'] = mmrpg_prototype_player_points($player_token);
        $player_info['player_battles_complete'] = mmrpg_prototype_battles_complete($player_token);
        $player_info['player_battles_complete_total'] = mmrpg_prototype_battles_complete($player_token, false);
        $player_info['player_battles_failure'] = mmrpg_prototype_battles_failure($player_token);
        $player_info['player_battles_failure_total'] = mmrpg_prototype_battles_failure($player_token, false);
        $player_info['player_robots_count'] = 0;
        $player_info['player_abilities_count'] = mmrpg_prototype_abilities_unlocked($player_token);
        $player_info['player_field_stars'] = mmrpg_prototype_stars_unlocked($player_token, 'field');
        $player_info['player_fusion_stars'] = mmrpg_prototype_stars_unlocked($player_token, 'fusion');
        $player_info['player_screw_counter'] = 0;
        $player_info['player_heart_counter'] = 0;
        // Define the player's experience points total
        $player_info['player_experience'] = 0;
        // Collect this player's current defined omega item list
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
            //$debug_experience_sum = $player_token.' : ';
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $temp_player => $temp_player_info){
                if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'])){
                    $temp_player_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'];
                    $temp_player_robot_settings = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'];
                    if (empty($temp_player_robot_rewards) || empty($temp_player_robot_settings)){
                        unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots']);
                        unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots']);
                        continue;
                    }
                    foreach ($temp_player_robot_rewards AS $temp_key => $temp_robot_info){
                        if (empty($temp_robot_info['robot_token'])){
                            unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_key]);
                            unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_key]);
                            continue;
                        }
                        $temp_robot_settings = $temp_player_robot_settings[$temp_robot_info['robot_token']];
                        $temp_robot_rewards = $temp_player_robot_settings[$temp_robot_info['robot_token']];
                        // If this robot is not owned by the player, skip it as it doesn't count towards their totals
                        if (empty($temp_robot_settings['original_player']) && $temp_player != $player_token){ continue; }
                        if (!empty($temp_robot_settings['original_player']) && $temp_robot_settings['original_player'] != $player_token){ continue; }
                        //$debug_experience_sum .= $temp_robot_info['robot_token'].', ';
                        $player_info['player_robots_count']++;
                        if (!empty($temp_robot_info['robot_level'])){ $player_info['player_experience'] += $temp_robot_info['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL; }
                        if (!empty($temp_robot_info['robot_experience'])){ $player_info['player_experience'] += $temp_robot_info['robot_experience']; }
                    }
                }
            }
            //die($debug_experience_sum);
        }

        // Collect this player's current field selection from the omega session
        $temp_session_key = $player_info['player_token'].'_target-robot-omega_prototype';
        $player_info['target_robot_omega'] = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
        $player_info['player_fields_current'] = array();
        //die('<pre>$player_info[\'target_robot_omega\'] = '.print_r($player_info['target_robot_omega'], true).'</pre>');
        if (count($player_info['target_robot_omega']) == 2){ $player_info['target_robot_omega'] = array_shift($player_info['target_robot_omega']); }
        foreach ($player_info['target_robot_omega'] AS $key => $info){
            $field = rpg_field::get_index_info($info['field']);
            if (empty($field)){ continue; }
            $player_info['player_fields_current'][] = $field;
        }

        // Collect this player's current item selection from the omega session
        $temp_session_key = $player_info['player_token'].'_this-item-omega_prototype';
        $player_info['this_item_omega'] = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
        $player_info['player_items_current'] = array();
        //if (empty($player_info['this_item_omega']) && !empty($_SESSION[$session_token]['values']['battle_items'])){ $player_info['this_item_omega'] = array_slice(array_keys($_SESSION[$session_token]['values']['battle_items']), 0, 8); }
        //foreach ($player_info['this_item_omega'] AS $key => $token){ $player_info['player_items_current'][] = $mmrpg_database_items[$token]; }

        // Define this player's stat type boost for display purposes
        $player_info['player_stat_type'] = '';
        if (!empty($player_info['player_energy'])){ $player_info['player_stat_type'] = 'energy'; }
        elseif (!empty($player_info['player_attack'])){ $player_info['player_stat_type'] = 'attack'; }
        elseif (!empty($player_info['player_defense'])){ $player_info['player_stat_type'] = 'defense'; }
        elseif (!empty($player_info['player_speed'])){ $player_info['player_stat_type'] = 'speed'; }

        // Define whether or not field switching is enabled
        $temp_allow_field_switch = mmrpg_prototype_complete($player_info['player_token']) || mmrpg_prototype_complete();

        // Collect a temp robot object for printing items
        if ($player_info['player_token'] == 'dr-light'){ $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['mega-man']); }
        elseif ($player_info['player_token'] == 'dr-wily'){ $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['bass']); }
        elseif ($player_info['player_token'] == 'dr-cossack'){ $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['proto-man']); }

        // Start the output buffer
        ob_start();

        // DEBUG
        //die(print_r($player_field_rewards, true));

            ?>
            <div class="event event_double event_<?= $player_key == $first_player_token ? 'visible' : 'hidden' ?>" data-token="<?=$player_info['player_token'].'_'.$player_info['player_token']?>">
                <div class="this_sprite sprite_left" style="height: 40px;">
                    <? $temp_margin = -1 * ceil(($player_info['player_image_size'] - 40) * 0.5); ?>
                    <div style="margin-top: <?= $temp_margin ?>px; margin-bottom: <?= $temp_margin * 3 ?>px; background-image: url(images/players/<?= !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'] ?>/mug_right_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_player sprite_player_sprite sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?> sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>_mug player_status_active player_position_active"><?=$player_info['player_name']?></div>
                </div>
                <div class="header header_left player_type player_type_<?= !empty($player_info['player_stat_type']) ? $player_info['player_stat_type'] : 'none' ?>" style="margin-right: 0;"><?=$player_info['player_name']?>&#39;s Data <span class="player_type"><?= !empty($player_info['player_stat_type']) ? ucfirst($player_info['player_stat_type']) : 'Neutral' ?> Type</span></div>
                <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">
                    <table class="full" style="margin-bottom: 5px;">
                        <colgroup>
                            <col width="48.5%" />
                            <col width="1%" />
                            <col width="48.5%" />
                        </colgroup>
                        <tbody>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Name :</label>
                                    <span class="player_name player_type player_type_none"><?=$player_info['player_name']?></span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label style="display: block; float: left;">Bonus :</label>
                                    <?
                                        // Display any special boosts this player has
                                        if (!empty($player_info['player_stat_type'])){ echo '<span class="player_name player_type player_type_'.$player_info['player_stat_type'].'">Robot '.ucfirst($player_info['player_stat_type']).' +'.$player_info['player_'.$player_info['player_stat_type']].'%</span>'; }
                                        else { echo '<span class="player_name player_type player_type_none">None</span>'; }
                                    ?>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Exp Points :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_experience']) ? 'cutter' : 'none' ?>"><?= number_format($player_info['player_experience'], 0, '.', ',') ?> EXP</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Unlocked Robots :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_robots_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_robots_count'].' '.($player_info['player_robots_count'] == 1 ? 'Robot' : 'Robots') ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Battle Points :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_points']) ? 'cutter' : 'none' ?>"><?= number_format($player_info['player_points'], 0, '.', ',') ?> BP</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Unlocked Abilities :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_abilities_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_abilities_count'].' '.($player_info['player_abilities_count'] == 1 ? 'Ability' : 'Abilities') ?></span>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Missions Completed :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_complete']) ? 'energy' : 'none' ?>"><?= $player_info['player_battles_complete'] ?> Missions</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Total Victories :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_complete_total']) ? 'energy' : 'none' ?>"><?= $player_info['player_battles_complete_total'] ?> Victories</span>
                                </td>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Missions Failed :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_failure']) ? 'attack' : 'none' ?>"><?= $player_info['player_battles_failure'] ?> Missions</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Total Defeats :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_failure_total']) ? 'attack' : 'none' ?>"><?= $player_info['player_battles_failure_total'] ?> Defeats</span>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <? if(!empty($player_info['player_field_stars'])): ?>
                                    <label style="display: block; float: left;">Field Stars :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_field_stars']) ? 'electric' : 'empty' ?>"><?= $player_info['player_field_stars'].' '.($player_info['player_field_stars'] == 1 ? 'Star' : 'Stars') ?></span>
                                    <? else: ?>
                                    <label style="display: block; float: left; opacity: 0.5; filter: alpha(opacity=50); ">??? :</label>
                                    <span class="player_stat player_type player_type_empty" style=" opacity: 0.5; filter: alpha(opacity=50); ">0</span>
                                    <? endif; ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <? if(!empty($player_info['player_fusion_stars'])): ?>
                                    <label style="display: block; float: left;">Fusion Stars :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_fusion_stars']) ? 'time' : 'empty' ?>"><?= $player_info['player_fusion_stars'].' '.($player_info['player_fusion_stars'] == 1 ? 'Star' : 'Stars') ?></span>
                                    <? else: ?>
                                    <label style="display: block; float: left; opacity: 0.5; filter: alpha(opacity=50); ">??? :</label>
                                    <span class="player_stat player_type player_type_empty" style=" opacity: 0.5; filter: alpha(opacity=50); ">0</span>
                                    <? endif; ?>
                                </td>
                            </tr>

                        </tbody>
                    </table>



                    <? if(false && !empty($player_item_rewards)){ ?>

                        <table class="full">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right" style="padding-top: 4px;">
                                    <label class="item_header">Player Items :</label>
                                        <div class="item_container" style="height: auto;">
                                        <?

                                        // Define the array to hold ALL the reward option markup
                                        $item_rewards_options = '';

                                        // Collect this player's item rewards and add them to the dropdown
                                        //$player_item_rewards = !empty($player_rewards['player_items']) ? $player_rewards['player_items'] : array();
                                        //if (!empty($player_item_rewards)){ sort($player_item_rewards); }

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_item_rewards AS $info){ $debug_tokens[] = $info['ability_token']; }
                                        //echo 'before:'.implode(',', array_keys($debug_tokens)).'<br />';

                                        // Sort the item index based on item group
                                        uasort($player_item_rewards, array('rpg_player', 'items_sort_for_editor'));

                                        // DEBUG
                                        //echo 'after:'.implode(',', array_keys($player_item_rewards)).'<br />';

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_item_rewards AS $info){ $debug_tokens[] = $info['ability_token']; }
                                        //echo 'after:'.implode(',', $debug_tokens).'<br />';

                                        // Dont' bother generating option dropdowns if editing is disabled
                                        if ($global_allow_editing){
                                            $player_item_rewards_options = array();
                                            foreach ($player_item_rewards AS $temp_item_key => $temp_item_info){
                                                if (empty($temp_item_info['ability_token'])){ continue; }
                                                $temp_token = $temp_item_info['ability_token'];
                                                $temp_item_info = rpg_ability::parse_index_info($mmrpg_database_items[$temp_token]);
                                                $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_item_info);
                                                if (!empty($temp_option_markup)){ $player_item_rewards_options[] = $temp_option_markup; }
                                            }
                                            $player_item_rewards_options = '<optgroup label="Player Items">'.implode('', $player_item_rewards_options).'</optgroup>';
                                            $item_rewards_options .= $player_item_rewards_options;

                                            /*
                                            // Collect this robot's item rewards and add them to the dropdown
                                            $player_item_rewards = !empty($player_rewards['player_items']) ? $player_rewards['player_items'] : array();
                                            $player_item_settings = !empty($player_settings['player_items']) ? $player_settings['player_items'] : array();
                                            foreach ($player_item_settings AS $token => $info){ if (empty($player_item_rewards[$token])){ $player_item_rewards[$token] = $info; } }
                                            if (!empty($player_item_rewards)){ sort($player_item_rewards); }
                                            $player_item_rewards_options = array();
                                            foreach ($player_item_rewards AS $temp_item_info){
                                                if (empty($temp_item_info['ability_token'])){ continue; }
                                                $temp_token = $temp_item_info['ability_token'];
                                                $temp_item_info = rpg_ability::parse_index_info($mmrpg_database_items[$temp_token]);
                                                $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_item_info);
                                                if (!empty($temp_option_markup)){ $player_item_rewards_options[] = $temp_option_markup; }
                                            }
                                            $player_item_rewards_options = '<optgroup label="Player Items">'.implode('', $player_item_rewards_options).'</optgroup>';
                                            $item_rewards_options .= $player_item_rewards_options;
                                            */

                                            // Add an option at the bottom to remove the ability
                                            $item_rewards_options .= '<optgroup label="Item Actions">';
                                            $item_rewards_options .= '<option value="" title="">- Remove Item -</option>';
                                            $item_rewards_options .= '</optgroup>';

                                        }

                                        // Loop through the robot's current items and list them one by one
                                        $empty_item_counter = 0;
                                        $temp_string = array();
                                        $temp_inputs = array();
                                        $item_key = 0;
                                        if (!empty($player_info['player_items_current'])){

                                            // DEBUG
                                            //echo 'robot-ability:';
                                            foreach ($player_info['player_items_current'] AS $key => $player_item){
                                                if (empty($player_item['item_token'])){ continue; }
                                                elseif ($player_item['item_token'] == '*'){ continue; }
                                                elseif ($player_item['item_token'] == 'item'){ continue; }
                                                elseif ($item_key > 7){ continue; }
                                                $this_item = rpg_item::parse_index_info($mmrpg_database_items[$player_item['item_token']]);
                                                if (empty($this_item)){ continue; }
                                                $this_item_token = $this_item['item_token'];
                                                $this_item_name = $this_item['item_name'];
                                                $this_item_type = !empty($this_item['item_type']) ? $this_item['item_type'] : false;
                                                $this_item_type2 = !empty($this_item['item_type2']) ? $this_item['item_type2'] : false;
                                                if (!empty($this_item_type) && !empty($mmrpg_index['types'][$this_item_type])){
                                                    $this_item_type = $mmrpg_index['types'][$this_item_type]['type_name'].' Type';
                                                    if (!empty($this_item_type2) && !empty($mmrpg_index['types'][$this_item_type2])){
                                                        $this_item_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_item_type2]['type_name'].' Type', $this_item_type);
                                                    }
                                                } else {
                                                    $this_item_type = '';
                                                }
                                                $this_item_energy = isset($this_item['item_energy']) ? $this_item['item_energy'] : 4;
                                                $this_item_damage = !empty($this_item['item_damage']) ? $this_item['item_damage'] : 0;
                                                $this_item_damage2 = !empty($this_item['item_damage2']) ? $this_item['item_damage2'] : 0;
                                                $this_item_damage_percent = !empty($this_item['item_damage_percent']) ? true : false;
                                                $this_item_damage2_percent = !empty($this_item['item_damage2_percent']) ? true : false;
                                                if ($this_item_damage_percent && $this_item_damage > 100){ $this_item_damage = 100; }
                                                if ($this_item_damage2_percent && $this_item_damage2 > 100){ $this_item_damage2 = 100; }
                                                $this_item_recovery = !empty($this_item['item_recovery']) ? $this_item['item_recovery'] : 0;
                                                $this_item_recovery2 = !empty($this_item['item_recovery2']) ? $this_item['item_recovery2'] : 0;
                                                $this_item_recovery_percent = !empty($this_item['item_recovery_percent']) ? true : false;
                                                $this_item_recovery2_percent = !empty($this_item['item_recovery2_percent']) ? true : false;
                                                if ($this_item_recovery_percent && $this_item_recovery > 100){ $this_item_recovery = 100; }
                                                if ($this_item_recovery2_percent && $this_item_recovery2 > 100){ $this_item_recovery2 = 100; }
                                                $this_item_accuracy = !empty($this_item['item_accuracy']) ? $this_item['item_accuracy'] : 0;
                                                $this_item_description = !empty($this_item['item_description']) ? $this_item['item_description'] : '';
                                                $this_item_description = str_replace('{DAMAGE}', $this_item_damage, $this_item_description);
                                                $this_item_description = str_replace('{RECOVERY}', $this_item_recovery, $this_item_description);
                                                $this_item_description = str_replace('{DAMAGE2}', $this_item_damage2, $this_item_description);
                                                $this_item_description = str_replace('{RECOVERY2}', $this_item_recovery2, $this_item_description);
                                                $this_item_title = rpg_item::print_editor_title_markup($robot_info, $this_item);
                                                $this_item_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_item_title));
                                                $this_item_title_tooltip = htmlentities($this_item_title, ENT_QUOTES, 'UTF-8');
                                                $this_item_title_html = str_replace(' ', '&nbsp;', $this_item_name);
                                                $temp_select_options = str_replace('value="'.$this_item_token.'"', 'value="'.$this_item_token.'" selected="selected" disabled="disabled"', $item_rewards_options);
                                                $this_item_title_html = '<label style="background-image: url(images/items/'.$this_item_token.'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_item_title_html.'</label>';
                                                if ($global_allow_editing){ $this_item_title_html .= '<select class="item_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'">'.$temp_select_options.'</select>'; }
                                                $temp_string[] = '<a class="item_name item_type item_type_'.(!empty($this_item['item_type']) ? $this_item['item_type'] : 'none').(!empty($this_item['item_type2']) ? '_'.$this_item['item_type2'] : '').'" style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-item="'.$this_item_token.'" title="'.$this_item_title_plain.'" data-tooltip="'.$this_item_title_tooltip.'">'.$this_item_title_html.'</a>';
                                                $item_key++;
                                            }

                                            if ($item_key <= 7){
                                                for ($item_key; $item_key <= 7; $item_key++){
                                                    $empty_item_counter++;
                                                    if ($empty_item_counter >= 2){ $empty_item_disable = true; }
                                                    else { $empty_item_disable = false; }
                                                    $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $item_rewards_options);
                                                    $this_item_title_html = '<label>-</label>';
                                                    if ($global_allow_editing){ $this_item_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" '.($empty_item_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                                    $temp_string[] = '<a class="ability_name " style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_item_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-item="" title="" data-tooltip="">'.$this_item_title_html.'</a>';
                                                }
                                            }


                                        } else {

                                            for ($item_key = 0; $item_key <= 7; $item_key++){
                                                $empty_item_counter++;
                                                if ($empty_item_counter >= 2){ $empty_item_disable = true; }
                                                else { $empty_item_disable = false; }
                                                $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $item_rewards_options);
                                                $this_item_title_html = '<label>-</label>';
                                                if ($global_allow_editing){ $this_item_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_item_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                                $temp_string[] = '<a class="ability_name " style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_item_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="">'.$this_item_title_html.'</a>';
                                            }

                                        }
                                        // DEBUG
                                        //echo 'temp-string:';
                                        echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                                        // DEBUG
                                        //echo '<br />temp-inputs:';
                                        echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';
                                        // DEBUG
                                        //echo '<br />';



                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    <? } ?>

                    <? if(!empty($player_field_rewards) && mmrpg_prototype_complete($player_info['player_token'])){ ?>

                        <table class="full">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right" style="padding-top: 4px;">
                                        <label class="field_header">Player Fields :</label>
                                        <div class="field_container" style="height: auto;">
                                        <?

                                        // Define the array to hold ALL the reward option markup
                                        $field_rewards_options = '';

                                        // Collect this player's field rewards and add them to the dropdown
                                        //$player_field_rewards = !empty($player_rewards['player_fields']) ? $player_rewards['player_fields'] : array();
                                        //if (!empty($player_field_rewards)){ sort($player_field_rewards); }

                                        // DEBUG
                                        //echo 'before:'.implode(',', array_keys($player_field_rewards)).'<br />';

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_field_rewards AS $info){ $debug_tokens[] = $info['field_token']; }
                                        //echo 'before:'.implode(',', array_keys($debug_tokens)).'<br />';

                                        // Sort the field index based on field number
                                        uasort($player_field_rewards, array('rpg_player', 'fields_sort_for_editor'));

                                        // DEBUG
                                        //echo 'after:'.implode(',', array_keys($player_field_rewards)).'<br />';

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_field_rewards AS $info){ $debug_tokens[] = $info['field_token']; }
                                        //echo 'after:'.implode(',', $debug_tokens).'<br />';

                                        // Don't bother generating the option markup if disabled editing
                                        if ($global_allow_editing){

                                            // Define the field group index for displau
                                            $temp_group_index = array('MMRPG' => 'Mega Man RPG Fields', 'MM00' => 'Mega Man 0 Fields', 'MM01' => 'Mega Man 1 Fields', 'MM02' => 'Mega Man 2 Fields', 'MM03' => 'Mega Man 3 Fields', 'MM04' => 'Mega Man 4 Fields', 'MM05' => 'Mega Man 5 Fields', 'MM06' => 'Mega Man 6 Fields', 'MM07' => 'Mega Man 7 Fields', 'MM08' => 'Mega Man 8 Fields', 'MM09' => 'Mega Man 9 Fields', 'MM10' => 'Mega Man 10 Fields');
                                            // Loop through the group index and display any fields that match
                                            $player_field_rewards_backup = $player_field_rewards;
                                            foreach ($temp_group_index AS $group_key => $group_name){
                                                $player_field_rewards_options = array();
                                                foreach ($player_field_rewards_backup AS $temp_field_key => $temp_field_info){
                                                    if ($temp_field_info['field_game'] != $group_key){ continue; }
                                                    $temp_option_markup = rpg_field::print_editor_option_markup($player_info, $temp_field_info);
                                                    if (!empty($temp_option_markup)){ $player_field_rewards_options[] = $temp_option_markup; }
                                                    unset($player_field_rewards_backup[$temp_field_key]);
                                                }
                                                if (empty($player_field_rewards_options)){ continue; }
                                                $player_field_rewards_options = '<optgroup label="'.$group_name.'">'.implode('', $player_field_rewards_options).'</optgroup>';
                                                $field_rewards_options .= $player_field_rewards_options;
                                            }

                                        }



                                        // Add an option at the bottom to remove the field
                                        //$field_rewards_options .= '<optgroup label="Field Actions">';
                                        //$field_rewards_options .= '<option value="" title="">- Remove Field -</option>';
                                        //$field_rewards_options .= '</optgroup>';

                                        // Loop through the player's current fields and list them one by one
                                        $empty_field_counter = 0;
                                        $temp_string = array();
                                        $temp_inputs = array();
                                        $field_key = 0;
                                        if (!empty($player_info['player_fields_current'])){

                                            // DEBUG
                                            //echo 'player-field:';
                                            $mmrpg_field_index = rpg_field::get_index();
                                            $player_info['player_fields_current'] = $player_info['player_fields_current']; //array_reverse($player_info['player_fields_current']);
                                            foreach ($player_info['player_fields_current'] AS $player_field){

                                                if ($player_field['field_token'] == '*'){ continue; }
                                                elseif (!isset($mmrpg_field_index[$player_field['field_token']])){ continue; }
                                                elseif ($field_key > 7){ continue; }
                                                $this_field = rpg_field::parse_index_info($mmrpg_field_index[$player_field['field_token']]);
                                                $this_field_token = $this_field['field_token'];
                                                $this_robot_token = $this_field['field_master'];
                                                $this_robot = rpg_robot::parse_index_info($mmrpg_database_robots[$this_robot_token]);
                                                $this_field_name = $this_field['field_name'];
                                                $this_field_type = !empty($this_field['field_type']) ? $this_field['field_type'] : false;
                                                $this_field_type2 = !empty($this_field['field_type2']) ? $this_field['field_type2'] : false;
                                                if (!empty($this_field_type) && !empty($mmrpg_index['types'][$this_field_type])){
                                                    $this_field_type = $mmrpg_index['types'][$this_field_type]['type_name'].' Type';
                                                    if (!empty($this_field_type2) && !empty($mmrpg_index['types'][$this_field_type2])){
                                                        $this_field_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_field_type2]['type_name'].' Type', $this_field_type);
                                                    }
                                                } else {
                                                    $this_field_type = '';
                                                }
                                                $this_field_description = !empty($this_field['field_description']) ? $this_field['field_description'] : '';
                                                $this_field_title = rpg_field::print_editor_title_markup($player_info, $this_field);
                                                $this_field_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_field_title));
                                                $this_field_title_tooltip = htmlentities($this_field_title, ENT_QUOTES, 'UTF-8');
                                                $this_field_title_html = str_replace(' ', '&nbsp;', $this_field_name);
                                                $temp_select_options = str_replace('value="'.$this_field_token.'"', 'value="'.$this_field_token.'" selected="selected" disabled="disabled"', $field_rewards_options);
                                                $temp_field_type_class = 'field_type_'.(!empty($this_field['field_type']) ? $this_field['field_type'] : 'none').(!empty($this_field['field_type2']) ? '_'.$this_field['field_type2'] : '');
                                                if ($global_allow_editing && $temp_allow_field_switch){ $this_field_title_html = '<label class="field_type  '.$temp_field_type_class.'" style="">'.$this_field_title_html.'</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'">'.$temp_select_options.'</select>'; }
                                                elseif (!$global_allow_editing && $temp_allow_field_switch){ $this_field_title_html = '<label class="field_type  '.$temp_field_type_class.'" style="cursor: default !important;">'.$this_field_title_html.'</label>'; }
                                                else { $this_field_title_html = '<label class="field_type '.$temp_field_type_class.'" style="cursor: default !important;">'.$this_field_title_html.'</label>'; }
                                                $temp_string[] = '<a class="field_name field_type '.$temp_field_type_class.'" style="background-image: url(images/fields/'.$this_field_token.'/battle-field_preview.png?'.MMRPG_CONFIG_CACHE_DATE.'); '.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$temp_allow_field_switch || !$global_allow_editing ? 'cursor: default !important; ' : '').(!$temp_allow_field_switch ? 'opacity: 0.50; filter: alpha(opacity=50); ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="'.$this_field_token.'" data-tooltip="'.$this_field_title_tooltip.'">'.$this_field_title_html.'</a>';
                                                $field_key++;
                                            }

                                            if ($field_key <= 7){
                                                for ($field_key; $field_key <= 7; $field_key++){
                                                    $empty_field_counter++;
                                                    if ($empty_field_counter >= 2){ $empty_field_disable = true; }
                                                    else { $empty_field_disable = false; }
                                                    $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $field_rewards_options);
                                                    $this_field_title_html = '<label>-</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" '.($empty_field_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>';
                                                    $temp_string[] = '<a class="field_name " style="'.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_field_disable ? 'opacity:0.25; ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="" title="">'.$this_field_title_html.'</a>';
                                                }
                                            }


                                        } else {

                                            for ($field_key = 0; $field_key <= 7; $field_key++){
                                                $empty_field_counter++;
                                                if ($empty_field_counter >= 2){ $empty_field_disable = true; }
                                                else { $empty_field_disable = false; }
                                                $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $field_rewards_options);
                                                $this_field_title_html = '<label>-</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" '.($empty_field_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>';
                                                $temp_string[] = '<a class="field_name " style="'.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_field_disable ? 'opacity:0.25; ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="" title="">'.$this_field_title_html.'</a>';
                                            }

                                        }
                                        // DEBUG
                                        //echo 'temp-string:';
                                        echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                                        // DEBUG
                                        //echo '<br />temp-inputs:';
                                        echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';
                                        // DEBUG
                                        //echo '<br />';
                                        // Collect the available star counts for this player
                                        $temp_star_counts = mmrpg_prototype_player_stars_available($player_token);
                                        ?>
                                        <div class="field_stars">
                                            <label class="label">stars</label>
                                            <span class="star star_field" data-star="field"><?= $temp_star_counts['field'] ?> field</span>
                                            <span class="star star_fusion" data-star="fusion"><?= $temp_star_counts['fusion'] ?> fusion</span>
                                        </div>
                                        <?
                                        // Print the sort wrapper and options if allowed
                                        if ($global_allow_editing){
                                            ?>
                                            <div class="field_tools">
                                                <label class="label">tools</label>
                                                <a class="tool tool_shuffle" data-tool="shuffle" data-player="<?= $player_token ?>">shuffle</a>
                                                <a class="tool tool_randomize" data-tool="randomize" data-player="<?= $player_token ?>">randomize</a>
                                            </div>
                                            <?
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    <? }?>


                </div>
            </div>
            <?
            $key_counter++;

        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }

}
?>