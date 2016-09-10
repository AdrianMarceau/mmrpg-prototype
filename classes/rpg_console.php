<?
/**
 * Mega Man RPG Console
 * <p>The console markup class for the Mega Man RPG Prototype.</p>
 */
class rpg_console {

    // Define a function for generating player console variables
    public static function player_markup($this_player, $options){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this player
        $this_data['player_frame'] = !empty($this_player->player_frame) ? $this_player->player_frame : 'base';
        $this_data['player_frame'] = str_pad(array_search($this_data['player_frame'], $this_player->player_frame_index), 2, '0', STR_PAD_LEFT);
        $this_data['player_title'] = $this_player->player_name;
        $this_data['player_token'] = $this_player->player_token;
        $this_data['player_float'] = $this_player->player_side;
        $this_data['player_direction'] = $this_player->player_side == 'left' ? 'right' : 'left';
        $this_data['player_position'] = 'active';

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['player_float'];
        $this_data['container_style'] = '';
        $this_data['player_class'] = 'sprite ';
        $this_data['player_style'] = '';
        $this_data['player_size'] = $this_player->player_image_size;
        $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/'.(!empty($options['this_player_image']) ? $options['this_player_image'] : 'sprite').'_'.$this_data['player_direction'].'_'.$this_data['player_size'].'x'.$this_data['player_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['player_class'] .= 'sprite_'.$this_data['player_size'].'x'.$this_data['player_size'].' sprite_'.$this_data['player_size'].'x'.$this_data['player_size'].'_'.$this_data['player_frame'].' ';
        $this_data['player_class'] .= 'player_position_'.$this_data['player_position'].' ';
        $this_data['player_style'] .= 'background-image: url('.$this_data['player_image'].'); ';

        // Generate the final markup for the console player
        $this_data['player_markup'] = '';
        // If this was an undefined player, do not create markup
        if ($this_player->player_token != 'player'){
            $this_data['player_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
            $this_data['player_markup'] .= '<div class="'.$this_data['player_class'].'" style="'.$this_data['player_style'].'" title="'.$this_data['player_title'].'" data-tooltip-align="'.$this_data['player_float'].'">'.$this_data['player_title'].'</div>';
            $this_data['player_markup'] .= '</div>';
        }

        // Return the player console data
        return $this_data;

    }

    // Define a function for generating robot console variables
    public static function robot_markup($this_robot, $options, $player_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this robot
        $this_data['robot_frame'] = !empty($this_robot->robot_frame) ? $this_robot->robot_frame : 'base';
        $this_data['robot_key'] = !empty($this_robot->robot_key) ? $this_robot->robot_key : 0;
        $this_data['robot_title'] = $this_robot->robot_name;
        $this_data['robot_token'] = $this_robot->robot_token;
        $this_data['robot_image'] = $this_robot->robot_image;
        $this_data['robot_float'] = $this_robot->player->player_side;
        $this_data['robot_direction'] = $this_robot->player->player_side == 'left' ? 'right' : 'left';
        $this_data['robot_status'] = $this_robot->robot_status;
        $this_data['robot_position'] = !empty($this_robot->robot_position) ? $this_robot->robot_position : 'bench';
        $this_data['image_type'] = !empty($options['this_robot_image']) ? $options['this_robot_image'] : 'sprite';

        // Calculate the energy bar amount and display properties
        $this_data['energy_fraction'] = $this_robot->robot_energy.' / '.$this_robot->robot_base_energy;
        $this_data['energy_percent'] = ceil(($this_robot->robot_energy / $this_robot->robot_base_energy) * 100);
        // Calculate the energy bar positioning variables based on float
        if ($this_data['robot_float'] == 'left'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -82; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -119 + floor(37 * ($this_data['energy_percent'] / 100));  }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -119; }
            else { $this_data['energy_x_position'] = -120; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -5;}
            else { $this_data['energy_y_position'] = -10; }
        }
        elseif ($this_data['robot_float'] == 'right'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -40; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -3 - floor(37 * ($this_data['energy_percent'] / 100)); }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -3; }
            else { $this_data['energy_x_position'] = -2; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -5; }
            else { $this_data['energy_y_position'] = -10; }
        }

        // Calculate the weapons bar amount and display properties for both robots
        if (true){
            // Define the fraction and percent text for the weapons
            $this_data['weapons_fraction'] = $this_robot->robot_weapons.' / '.$this_robot->robot_base_weapons;
            $this_data['weapons_percent'] = floor(($this_robot->robot_weapons / $this_robot->robot_base_weapons) * 100);
        }

        // Calculate the experience bar amount and display properties if a player robot
        if ($this_data['robot_float'] == 'left'){
            // Define the fraction and percent text for the experience
            if ($this_robot->robot_level < 100){
                $this_data['experience_fraction'] = $this_robot->robot_experience.' / 1000';
                $this_data['experience_percent'] = floor(($this_robot->robot_experience / 1000) * 100);
            } else {
                $this_data['experience_fraction'] = '&#8734;';
                $this_data['experience_percent'] = 100;
            }
        }

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['robot_float'];
        $this_data['container_style'] = '';
        //$this_data['robot_class'] = 'sprite sprite_robot_'.$this_data['robot_status'];
        $this_data['robot_class'] = 'sprite sprite_robot sprite_robot_'.$this_data['image_type'].' ';
        $this_data['robot_type_class'] = !empty($this_robot->robot_core) ? $this_robot->robot_core : 'none';
        if (!empty($this_robot->robot_core) && !empty($this_robot->robot_core2)){ $this_data['robot_type_class'] .= '_'.$this_robot->robot_core2; }
        $this_data['robot_style'] = '';
        $this_data['robot_size'] = $this_robot->robot_image_size;
        $this_data['robot_image'] = 'images/robots/'.$this_data['robot_image'].'/'.$this_data['image_type'].'_'.$this_data['robot_direction'].'_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['robot_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].' sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_'.$this_data['robot_frame'].' ';
        $this_data['robot_class'] .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
        if ($this_data['image_type'] == 'mug'){ $this_data['robot_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_mugshot '; }
        $this_data['robot_style'] .= 'background-image: url('.$this_data['robot_image'].'); ';
        $this_data['energy_title'] = $this_data['energy_fraction'].' LE ('.$this_data['energy_percent'].'%)';
        $this_data['robot_title'] .= ' <br />'.$this_data['energy_title'];
        $this_data['weapons_title'] = $this_data['weapons_fraction'].' WE ('.$this_data['weapons_percent'].'%)';
        $this_data['robot_title'] .= ' <br />'.$this_data['weapons_title'];
        if ($this_data['robot_float'] == 'left'){
            $this_data['experience_title'] = $this_data['experience_fraction'].' EXP ('.$this_data['experience_percent'].'%)';
            $this_data['robot_title'] .= ' <br />'.$this_data['experience_title'];
        }
        $this_data['energy_class'] = 'energy';
        $this_data['energy_style'] = 'background-position: '.$this_data['energy_x_position'].'px '.$this_data['energy_y_position'].'px;';

        // Generate the final markup for the console robot
        $this_data['robot_markup'] = '';
        $this_data['robot_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
        $this_data['robot_markup'] .= '<div class="'.$this_data['robot_class'].'" style="'.$this_data['robot_style'].'" title="'.$this_data['robot_title'].'" data-tooltip-type="robot_type type_'.$this_data['robot_type_class'].'">'.$this_data['robot_title'].'</div>';
        if ($this_data['image_type'] != 'mug'){ $this_data['robot_markup'] .= '<div class="'.$this_data['energy_class'].'" style="'.$this_data['energy_style'].'" title="'.$this_data['energy_title'].'">'.$this_data['energy_title'].'</div>'; }
        $this_data['robot_markup'] .= '</div>';

        // Return the robot console data
        return $this_data;
    }

    // Define a function for generating ability console variables
    public static function ability_markup($this_ability, $options, $player_data, $robot_data){

        // Define the variable to hold the console ability data
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this ability
        $this_data['ability_name'] = isset($options['ability_name']) ? $options['ability_name'] : $this_ability->ability_name;
        $this_data['ability_title'] = $this_data['ability_name'];
        $this_data['ability_token'] = $this_ability->ability_token;
        $this_data['ability_direction'] = !empty($robot_data['robot_id']) && $robot_data['robot_id'] == $this_ability->robot_id ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['ability_float'] = !empty($robot_data['robot_id']) && $robot_data['robot_id'] == $this_ability->robot_id ? $robot_data['robot_float'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['ability_size'] = $this_ability->ability_image_size;
        $this_data['ability_frame'] = isset($options['ability_frame']) ? $options['ability_frame'] : $this_ability->ability_frame;
        if (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] >= 0){ $this_data['ability_frame'] = str_pad($this_data['ability_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] < 0){ $this_data['ability_frame'] = ''; }
        $this_data['image_type'] = !empty($options['this_ability_image']) ? $options['this_ability_image'] : 'icon';

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['ability_float'];
        $this_data['container_style'] = '';
        $this_data['ability_markup_class'] = 'sprite sprite_ability sprite_ability_'.$this_data['image_type'].' ';
        $this_data['ability_markup_style'] = '';
        if (empty($this_data['ability_image']) || !preg_match('/^images/i', $this_data['ability_image'])){ $this_data['ability_image'] = 'images/abilities/'.(!empty($this_data['ability_image']) ? $this_data['ability_image'] : $this_data['ability_token']).'/'.$this_data['image_type'].'_'.$this_data['ability_direction'].'_'.$this_data['ability_size'].'x'.$this_data['ability_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE; }
        $this_data['ability_markup_class'] .= 'sprite_'.$this_data['ability_size'].'x'.$this_data['ability_size'].' sprite_'.$this_data['ability_size'].'x'.$this_data['ability_size'].'_'.$this_data['ability_frame'].' ';
        $this_data['ability_markup_style'] .= 'background-image: url('.$this_data['ability_image'].'); ';

        // Generate the final markup for the console ability
        $this_data['ability_markup'] = '';
        $this_data['ability_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
        $this_data['ability_markup'] .= '<div class="'.$this_data['ability_markup_class'].'" style="'.$this_data['ability_markup_style'].'" title="'.$this_data['ability_title'].'">'.$this_data['ability_title'].'</div>';
        $this_data['ability_markup'] .= '</div>';

        // Return the ability console data
        return $this_data;

    }

    // Define a function for generating item console variables
    public static function item_markup($this_item, $options, $player_data, $robot_data){

        // Define the variable to hold the console item data
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this item
        $this_data['item_name'] = isset($options['item_name']) ? $options['item_name'] : $this_item->item_name;
        $this_data['item_title'] = $this_data['item_name'];
        $this_data['item_token'] = $this_item->item_token;
        $this_data['item_direction'] = 'right';
        $this_data['item_float'] = !empty($robot_data['robot_id']) && $robot_data['robot_id'] == $this_item->robot_id ? $robot_data['robot_float'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['item_size'] = $this_item->item_image_size;
        $this_data['item_frame'] = isset($options['item_frame']) ? $options['item_frame'] : $this_item->item_frame;
        if (is_numeric($this_data['item_frame']) && $this_data['item_frame'] >= 0){ $this_data['item_frame'] = str_pad($this_data['item_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['item_frame']) && $this_data['item_frame'] < 0){ $this_data['item_frame'] = ''; }
        $this_data['image_type'] = !empty($options['this_item_image']) ? $options['this_item_image'] : 'icon';
        $this_data['item_quantity'] = !empty($options['this_item_quantity']) ? $options['this_item_quantity'] : 1;

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['item_float'];
        $this_data['container_style'] = '';
        $this_data['item_markup_class'] = 'sprite sprite_item sprite_item_'.$this_data['image_type'].' ';
        $this_data['item_markup_style'] = '';
        if (empty($this_data['item_image']) || !preg_match('/^images/i', $this_data['item_image'])){ $this_data['item_image'] = 'images/items/'.(!empty($this_data['item_image']) ? $this_data['item_image'] : $this_data['item_token']).'/'.$this_data['image_type'].'_'.$this_data['item_direction'].'_'.$this_data['item_size'].'x'.$this_data['item_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE; }
        $this_data['item_markup_class'] .= 'sprite_'.$this_data['item_size'].'x'.$this_data['item_size'].' sprite_'.$this_data['item_size'].'x'.$this_data['item_size'].'_'.$this_data['item_frame'].' ';

        // Generate the final markup for the console item
        $this_data['item_markup'] = '';
        $this_data['item_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
            // Check if the item should be printed multiple times or not
            if ($this_data['item_quantity'] > 1){
                $start_offset = (2 - (2 * ($this_data['item_quantity'] - 1)));
                $class = $this_data['item_markup_class'];
                $style = $this_data['item_markup_style'];
                $this_data['item_markup'] .= '<div class="'.$class.'" style="'.$style.'" title="'.$this_data['item_title'].'">'.$this_data['item_title'].'</div>';
                $this_data['item_markup_style'] .= 'background-image: url('.$this_data['item_image'].'); ';
                for ($i = 0; $i < $this_data['item_quantity']; $i++){
                    $offset = $start_offset + (4 * $i);
                    $class = $this_data['item_markup_class'];
                    $class = str_replace('sprite_item_icon ', '', $class);
                    $style = $this_data['item_markup_style'].' ';
                    $style .= 'position: absolute; top: '.$offset.'px; left: '.$offset.'px; ';
                    $this_data['item_markup'] .= '<div class="'.$class.'" style="'.$style.'"></div>';
                }
            } else {
                $this_data['item_markup_style'] .= 'background-image: url('.$this_data['item_image'].'); ';
                $this_data['item_markup'] .= '<div class="'.$this_data['item_markup_class'].'" style="'.$this_data['item_markup_style'].'" title="'.$this_data['item_title'].'">'.$this_data['item_title'].'</div>';
            }

        // Close the item markup container
        $this_data['item_markup'] .= '</div>';

        // Return the item console data
        return $this_data;

    }

    // Define a function for generating console message markup
    public static function battle_markup($this_battle, $eventinfo, $options){

        // Define the console markup string
        $this_markup = '';

        // Ensure this side is allowed to be shown before generating any markup
        if ($options['console_show_this'] != false){

                // Define the necessary text markup for the current player if allowed and exists
            if (!empty($eventinfo['this_player'])){
                // Collect the console data for this player
                $this_player_data = $eventinfo['this_player']->console_markup($options);
            } else {
                // Define empty console data for this player
                $this_player_data = array();
                $options['console_show_this_player'] = false;
            }
            // Define the necessary text markup for the current robot if allowed and exists
            if (!empty($eventinfo['this_robot'])){
                // Collect the console data for this robot
                $this_robot_data = $eventinfo['this_robot']->console_markup($options, $this_player_data);
            } else {
                // Define empty console data for this robot
                $this_robot_data = array();
                $options['console_show_this_robot'] = false;
            }
            // Define the necessary text markup for the current ability if allowed and exists
            if (!empty($options['this_ability'])){
                // Collect the console data for this ability
                $this_ability_data = $options['this_ability']->console_markup($options, $this_player_data, $this_robot_data);
            } else {
                // Define empty console data for this ability
                $this_ability_data = array();
                $options['console_show_this_ability'] = false;
            }
            // Define the necessary text markup for the current item if allowed and exists
            if (!empty($options['this_item'])){
                // Collect the console data for this item
                $this_item_data = $options['this_item']->console_markup($options, $this_player_data, $this_robot_data);
            } else {
                // Define empty console data for this item
                $this_item_data = array();
                $options['console_show_this_item'] = false;
            }
            // Define the necessary text markup for the current star if allowed and exists
            if (!empty($options['this_star'])){
                // Collect the console data for this star
                $this_star_data = $this_battle->star_console_markup($options['this_star'], $this_player_data, $this_robot_data);
                //die('FINALLY : '.implode(' | ', $this_star_data));
            } else {
                // Define empty console data for this star
                $this_star_data = array();
                $options['console_show_this_star'] = false;
            }

            // If no objects would found to display, turn the left side off
            if (empty($options['console_show_this_player'])
                && empty($options['console_show_this_robot'])
                && empty($options['console_show_this_ability'])
                && empty($options['console_show_this_item'])
                && empty($options['console_show_this_star'])){
                // Automatically set the console option to false
                $options['console_show_this'] = false;
            }

        }
        // Otherwise, if this side is not allowed to be shown at all
        else {

            // Default all of this side's objects to empty arrays
            $this_player_data = array();
            $this_robot_data = array();
            $this_ability_data = array();
            $this_star_data = array();

        }


        // Ensure the target side is allowed to be shown before generating any markup
        if ($options['console_show_target'] != false){

            // Define the necessary text markup for the target player if allowed and exists
            if (!empty($eventinfo['target_player'])){
                // Collect the console data for this player
                $target_player_data = $eventinfo['target_player']->console_markup($options);
            } else {
                // Define empty console data for this player
                $target_player_data = array();
                $options['console_show_target_player'] = false;
            }
            // Define the necessary text markup for the target robot if allowed and exists
            if (!empty($eventinfo['target_robot'])){
                // Collect the console data for this robot
                $target_robot_data = $eventinfo['target_robot']->console_markup($options, $target_player_data);
            } else {
                // Define empty console data for this robot
                $target_robot_data = array();
                $options['console_show_target_robot'] = false;
            }
            // Define the necessary text markup for the target ability if allowed and exists
            if (!empty($options['target_ability'])){
                // Collect the console data for this ability
                $target_ability_data = $options['target_ability']->console_markup($options, $target_player_data, $target_robot_data);
            } else {
                // Define empty console data for this ability
                $target_ability_data = array();
                $options['console_show_target_ability'] = false;
            }

            // If no objects would found to display, turn the right side off
            if (empty($options['console_show_target_player'])
                && empty($options['console_show_target_robot'])
                && empty($options['console_show_target_ability'])){
                // Automatically set the console option to false
                $options['console_show_target'] = false;
            }

        }
        // Otherwise, if the target side is not allowed to be shown at all
        else {

            // Default all of the target side's objects to empty arrays
            $target_player_data = array();
            $target_robot_data = array();
            $target_ability_data = array();

        }

        // Assign player-side based floats for the header and body if not set
        if (empty($options['console_header_float']) && !empty($this_robot_data)){
            $options['console_header_float'] = $this_robot_data['robot_float'];
        }
        if (empty($options['console_body_float']) && !empty($this_robot_data)){
            $options['console_body_float'] = $this_robot_data['robot_float'];
        }

        // Append the generated console markup if not empty
        if (!empty($eventinfo['event_header']) && !empty($eventinfo['event_body'])){

            // Define the container class based on height
            $event_class = 'event ';
            $event_style = '';
            if ($options['console_container_height'] == 1){ $event_class .= 'event_single '; }
            if ($options['console_container_height'] == 2){ $event_class .= 'event_double '; }
            if ($options['console_container_height'] == 3){ $event_class .= 'event_triple '; }
            if (!empty($options['console_container_classes'])){ $event_class .= $options['console_container_classes']; }
            if (!empty($options['console_container_styles'])){ $event_style .= $options['console_container_styles']; }

            // Generate the opening event tag
            $this_markup .= '<div class="'.$event_class.'" style="'.$event_style.'">';

            // Generate this side's markup if allowed
            if ($options['console_show_this'] != false){
                // Append this player's markup if allowed
                if ($options['console_show_this_player'] != false){ $this_markup .= $this_player_data['player_markup']; }
                // Otherwise, append this robot's markup if allowed
                elseif ($options['console_show_this_robot'] != false){ $this_markup .= $this_robot_data['robot_markup']; }
                // Otherwise, append this ability's markup if allowed
                elseif ($options['console_show_this_ability'] != false){ $this_markup .= $this_ability_data['ability_markup']; }
                // Otherwise, append this items's markup if allowed
                elseif ($options['console_show_this_item'] != false){ $this_markup .= $this_item_data['item_markup']; }
                // Otherwise, append this star's markup if allowed
                elseif ($options['console_show_this_star'] != false){ $this_markup .= $this_star_data['star_markup']; }
            }

            // Generate the target side's markup if allowed
            if ($options['console_show_target'] != false){
                // Append the target player's markup if allowed
                if ($options['console_show_target_player'] != false){ $this_markup .= $target_player_data['player_markup']; }
                // Otherwise, append the target robot's markup if allowed
                elseif ($options['console_show_target_robot'] != false){ $this_markup .= $target_robot_data['robot_markup']; }
                // Otherwise, append the target ability's markup if allowed
                elseif ($options['console_show_target_ability'] != false){ $this_markup .= $target_ability_data['ability_markup']; }
            }

            /*
            $eventinfo['event_body'] .= '<div>';
            $eventinfo['event_body'] .= 'console_show_this_player : '.($options['console_show_this_player'] != false ? 'true : '.$this_player_data['player_markup'] : 'false : -').'<br />';
            $eventinfo['event_body'] .= 'console_show_this_robot : '.($options['console_show_this_robot'] != false ? 'true : '.$this_robot_data['robot_markup'] : 'false : -').'<br />';
            $eventinfo['event_body'] .= 'console_show_this_ability : '.($options['console_show_this_ability'] != false ? 'true : '.$this_ability_data['ability_markup'] : 'false : -').'<br />';
            $eventinfo['event_body'] .= 'console_show_this_item : '.($options['console_show_this_item'] != false ? 'true : '.$this_item_data['item_markup'] : 'false : -').'<br />';
            $eventinfo['event_body'] .= '</div>';
            */

            // Prepend the turn counter to the header if necessary
            if (!empty($this_battle->counters['battle_turn']) && $this_battle->battle_status != 'complete'){ $eventinfo['event_header'] = 'Turn #'.$this_battle->counters['battle_turn'].' : '.$eventinfo['event_header']; }

            // Display the event header and event body
            $this_markup .= '<div class="header header_'.$options['console_header_float'].'">'.$eventinfo['event_header'].'</div>';
            $this_markup .= '<div class="body body_'.$options['console_body_float'].'">'.$eventinfo['event_body'].'</div>';

            // Displat the closing event tag
            $this_markup .= '</div>';

        }

        // Return the generated markup and robot data
        return $this_markup;
    }



}
?>