<?
/**
 * Mega Man RPG Console
 * <p>The console markup class for the Mega Man RPG Prototype.</p>
 */
class rpg_console {

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