<?
/**
 * Mega Man RPG Console
 * <p>The console markup class for the Mega Man RPG Prototype.</p>
 */
class rpg_console {

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
        $this_data['robot_markup'] .= '<div class="'.$this_data['robot_class'].'" style="'.$this_data['robot_style'].'" title="'.$this_data['robot_title'].'">'.$this_data['robot_title'].'</div>';
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



}
?>