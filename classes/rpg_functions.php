<?php
// Define a class for misc static functions
class rpg_functions {

    // Define the constructor class
    public function rpg_functions(){ }


    // -- STATIC CHANCE FUNCTIONS -- //

    /**
     * Return a random one of many options using a weighted chance mechanism
     * @param array $options
     * @param array $weights
     * @return string
     */
    public static function weighted_chance($options, $weights = array()){

        // Count the number of values passed
        $option_amount = count($options);

        // If no weights have been defined, auto-generate
        if (empty($weights)){
            $weights = array();
            for ($i = 0; $i < $option_amount; $i++){
                $weights[] = 1;
            }
        }

        // Generate a single weighted values array
        $weighted_values = array();
        foreach ($options AS $key => $option){ $weighted_values[$option] = $weights[$key]; }

        // Collect a random number and check which key it matches
        $random_int = mt_rand(1, array_sum($weighted_values));
        foreach ($weighted_values as $option => $weight) {
            $random_int -= $weight;
            if ($random_int <= 0) {
                return $option;
            }
        }

    }

    /**
     * Calculate whether or not this is a critical turn for a robot based on its level and held item
     * @param int $battle_turn
     * @param int $robot_level
     * @param string $robot_item (optional)
     * @return bool
     */
    public static function critical_turn($battle_turn, $robot_level, $robot_item = ''){

        // If the robot level and the current turn have the same last digits, it's critical
        $temp_turn_digit = substr($battle_turn, -1, 1);
        $temp_level_digit = substr($robot_level, -1, 1);
        $temp_flag_critical = $temp_level_digit == $temp_turn_digit ? true : false;
        $temp_flag_lucky = false;

        // If the robot is holding a chance module, also look at the first digit of their level
        if ($robot_item == 'fortune-module' && $temp_flag_critical == false){
            $temp_level_digit2 = substr($robot_level, 0, 1);
            $temp_flag_critical = $temp_level_digit2 == $temp_turn_digit ? true : false;
            $temp_flag_lucky = true;
        }

        // Return the final critical result
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->events_create(false, false, 'DEBUG_'.__LINE__, ' critical_turn | fortune_module = '.($temp_flag_lucky ? 'true' : 'false').' <br /> turn '.$battle_turn.' vs. level '.$robot_level.' | turn_digit '.$temp_turn_digit.' vs level_digit '.$temp_level_digit.(isset($temp_level_digit2) ? ' | level_digit2 = '.$temp_level_digit2 : '').' | critical = '.($temp_flag_critical ? 'true' : 'false')); }
        return $temp_flag_critical;

    }

    /**
     * Calculate a critical chance flag based on percentage and the random number generator
     * @param int $chance_percent (optional)
     * @return bool
     */
    public static function critical_chance($chance_percent = 10){

        // Invert if negative for some reason
        if ($chance_percent < 0){ $chance_percent = -1 * $chance_percent; }
        // Round up to a whole number
        $chance_percent = ceil($chance_percent);
        // If zero, automatically return false
        if ($chance_percent == 0){ return false; }
        // Return true of false at random
        $random_int = mt_rand(1, 100);
        return ($random_int <= $chance_percent) ? true : false;

    }

    // -- STATIC WORD FUNCTIONS -- //

    /**
     * Generate a randomized positive word for battle text
     * @return string
     */
    public static function get_random_positive_word(){
        $temp_text_options = array('Awesome!', 'Nice!', 'Fantastic!', 'Yeah!', 'Yay!', 'Yes!', 'Great!', 'Super!', 'Rock on!', 'Amazing!', 'Fabulous!', 'Wild!', 'Sweet!', 'Wow!');
        $temp_text = $temp_text_options[array_rand($temp_text_options)];
        return $temp_text;
    }

    /**
     * Generate a randomized victory quote for battle results
     * @return string
     */
    public static function get_random_victory_quote(){
        $temp_text_options = array('Awesome work!', 'Nice work!', 'Fantastic work!', 'Great work!', 'Super work!', 'Amazing work!', 'Fabulous work!');
        $temp_text = $temp_text_options[array_rand($temp_text_options)];
        return $temp_text;
    }

    /**
     * Generate a randomized negative word for battle text
     * @return string
     */
    public static function get_random_negative_word(){
        $temp_text_options = array('Yikes!', 'Oh no!', 'Ouch&hellip;', 'Awwwww&hellip;', 'Bummer&hellip', 'Boooo&hellip;', 'Harsh!');
        $temp_text = $temp_text_options[array_rand($temp_text_options)];
        return $temp_text;
    }

    /**
     * Generate a randomized defeat quote for battle results
     * @return string
     */
    public static function get_random_defeat_quote(){
        $temp_text_options = array('Maybe try again?', 'Bad luck maybe?', 'Maybe try another stage?', 'Better luck next time?', 'At least you tried&hellip; right?');
        $temp_text = $temp_text_options[array_rand($temp_text_options)];
        return $temp_text;
    }

    /**
     * Generate a damage word related to a specific robot stat
     * @param string $damage_kind
     * @return string
     */
    public static function get_stat_damage_words($damage_kind){

        // Define the system word based on the stat kind
        $damage_words = array();
        switch ($damage_kind){
            case 'attack':
                $damage_words['action'] = 'crippled';
                $damage_words['object'] = 'weapon';
                break;
            case 'defense':
                $damage_words['action'] = 'crippled';
                $damage_words['object'] = 'shield';
                break;
            case 'speed':
                $damage_words['action'] = 'crippled';
                $damage_words['object'] = 'mobility';
                break;
            case 'energy':
                $damage_words['action'] = 'drained';
                $damage_words['object'] = 'internal life';
                break;
            case 'weapon':
                $damage_words['action'] = 'depleted';
                $damage_words['object'] = 'internal ammo';
                break;
            default:
                $damage_words['action'] = 'crippled';
                $damage_words['object'] = 'internal';
                break;
        }

        // Return the generated words
        return $damage_words;

    }

    /**
     * Generate a recovery word related to a specific robot stat
     * @param string $recovery_kind
     * @return string
     */
    public static function get_stat_recovery_words($recovery_kind){

        // Define the system word based on the stat kind
        $recovery_words = array();
        switch ($recovery_kind){
            case 'attack':
                $recovery_words['action'] = 'improved';
                $recovery_words['object'] = 'weapon';
                break;
            case 'defense':
                $recovery_words['action'] = 'improved';
                $recovery_words['object'] = 'shield';
                break;
            case 'speed':
                $recovery_words['action'] = 'improved';
                $recovery_words['object'] = 'mobility';
                break;
            case 'energy':
                $recovery_words['action'] = 'repaired';
                $recovery_words['object'] = 'internal life';
                break;
            case 'weapon':
                $recovery_words['action'] = 'replenished';
                $recovery_words['object'] = 'internal ammo';
                break;
            default:
                $recovery_words['action'] = 'improved';
                $recovery_words['object'] = 'internal';
                break;
        }

        // Return the generated words
        return $recovery_words;

    }

    // -- STATIC MATH FUNCTIONS -- //

    /**
     * Round a number to a full int value but ceil if > 0 and < 1
     * @param float $amount
     * @return int
     */
    public static function round_ceil($amount){
        if ($amount > 0 && $amount < 1){ $amount = 1; }
        else { $amount = round($amount); }
        return $amount;
    }


    // -- STATIC MARKUP FUNCTIONS -- //

    /**
     * Generate the console markup for a star given it's kind, player, and robot data
     * @param array $options
     * @param array $player_data
     * @param array $robot_data
     * @return array
     */
    public static function get_star_console_markup($options, $player_data, $robot_data){

        // Define the variable to hold the console star data
        $this_data = array();

        // Collect the star image info from the index based on type
        $temp_star_kind = $options['star_kind'];
        $temp_field_type_1 = !empty($options['star_type']) ? $options['star_type'] : 'none';
        $temp_field_type_2 = !empty($options['star_type2']) ? $options['star_type2'] : $temp_field_type_1;
        $temp_star_back_info = rpg_prototype::star_image($temp_field_type_2);
        $temp_star_front_info = rpg_prototype::star_image($temp_field_type_1);

        // Define and calculate the simpler markup and positioning variables for this star
        $this_data['star_name'] = isset($options['star_name']) ? $options['star_name'] : 'Battle Star';
        $this_data['star_title'] = $this_data['star_name'];
        $this_data['star_token'] = $options['star_token'];
        $this_data['container_class'] = 'this_sprite sprite_left';
        $this_data['container_style'] = '';

        // Define the back star's markup
        $this_data['star_image'] = 'images/abilities/item-star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['star_markup_class'] = 'sprite sprite_star sprite_star_sprite sprite_40x40 sprite_40x40_'.str_pad($temp_star_back_info['frame'], 2, '0', STR_PAD_LEFT).' ';
        $this_data['star_markup_style'] = 'background-image: url('.$this_data['star_image'].'); margin-top: 5px; ';
        $temp_back_markup = '<div class="'.$this_data['star_markup_class'].'" style="'.$this_data['star_markup_style'].'" title="'.$this_data['star_title'].'">'.$this_data['star_title'].'</div>';

        // Define the back star's markup
        $this_data['star_image'] = 'images/abilities/item-star-base-'.$temp_star_front_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['star_markup_class'] = 'sprite sprite_star sprite_star_sprite sprite_40x40 sprite_40x40_'.str_pad($temp_star_front_info['frame'], 2, '0', STR_PAD_LEFT).' ';
        $this_data['star_markup_style'] = 'background-image: url('.$this_data['star_image'].'); margin-top: -42px; ';
        $temp_front_markup = '<div class="'.$this_data['star_markup_class'].'" style="'.$this_data['star_markup_style'].'" title="'.$this_data['star_title'].'">'.$this_data['star_title'].'</div>';

        // Generate the final markup for the console star
        $this_data['star_markup'] = '';
        $this_data['star_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
        $this_data['star_markup'] .= $temp_back_markup;
        $this_data['star_markup'] .= $temp_front_markup;
        $this_data['star_markup'] .= '</div>';

        // Return the star console data
        return $this_data;

    }

    /**
     * Calculate the default canvas offset for a given sprite based on key and position
     * @param int $key
     * @param string $position
     * @param int $size
     * @return array
     */
    public static function canvas_sprite_offset($key, $position, $size){

        // Define the data array to be returned later
        $data = array();

        // Define the base canvas offsets for this sprite
        $data['canvas_offset_x'] = 165;
        $data['canvas_offset_y'] = 55;
        $data['canvas_offset_z'] = $position == 'active' ? 5100 : 4900;
        $data['canvas_scale'] = $position == 'active' ? 1 : 0.5 + (((8 - $key) / 8) * 0.5);

        // If the robot is on the bench, calculate position offsets based on key
        if ($position == 'bench'){
            $data['canvas_offset_z'] -= 100 * $key;
            $position_modifier = ($key + 1) / 8;
            $position_modifier_2 = 1 - $position_modifier;
            $seed_1 = 40; //$size;
            $seed_2 = 20; //ceil($size / 2);
            $data['canvas_offset_x'] = (-1 * $seed_2) + ceil(($key + 1) * ($seed_1 + 2)) - ceil(($key + 1) * $seed_2);
            $seed_1 = $size;
            $seed_2 = ceil($size / 2);
            $data['canvas_offset_y'] = ($seed_1 + 6) + ceil(($key + 1) * 14) - ceil(($key + 1) * 7) - ($size - 40);
            $seed_3 = 0;
            if ($key == 0){ $seed_3 = -10; }
            elseif ($key == 1){ $seed_3 = 0; }
            elseif ($key == 2){ $seed_3 = 10; }
            elseif ($key == 3){ $seed_3 = 20; }
            elseif ($key == 4){ $seed_3 = 30; }
            elseif ($key == 5){ $seed_3 = 40; }
            elseif ($key == 6){ $seed_3 = 50; }
            elseif ($key == 7){ $seed_3 = 60; }
            if ($size > 40){ $seed_3 -= ceil(40 * $data['canvas_scale']); }
            $data['canvas_offset_x'] += $seed_3;
            $data['canvas_offset_x'] += 20;
        }
        // Otherwise, if the robot is in active position
        elseif ($position == 'active'){
            if ($size > 80){
                $data['canvas_offset_x'] -= 60;
            }
        }

        // Return the generated canvas data for this robot
        return $data;

    }

    /**
     * Sort an array of robots by their active status
     * @param array $info1
     * @param array $info2
     * @return int
     */
    public static function robot_sort_by_active($info1, $info2){
        if ($info1['robot_position'] == 'active'){ return -1; }
        elseif ($info1['robot_key'] < $info2['robot_key']){ return -1; }
        elseif ($info1['robot_key'] > $info2['robot_key']){ return 1; }
        else { return 0; }
    }


    /**
     * Sort an array of abilities for the editor by token
     * @param array $ability_one
     * @param array $ability_two
     * @return int
     */
    public static function abilities_sort_for_editor($ability_one, $ability_two){
        $ability_token_one = isset($ability_one['ability_token']) ? $ability_one['ability_token'] : $ability_one;
        $ability_token_two = isset($ability_two['ability_token']) ? $ability_two['ability_token'] : $ability_two;
        if ($ability_token_one > $ability_token_two){ return 1; }
        elseif ($ability_token_one < $ability_token_two){ return -1; }
        else { return 0; }
    }

    /**
     * Sort an array of fields for the editor by game and then token
     * @param array $ability_one
     * @param array $ability_two
     * @return int
     */
    public static function fields_sort_for_editor($field_one, $field_two){
        $rpg_fields_index = rpg_field::get_index();
        $field_token_one = $field_one['field_token'];
        $field_token_two = $field_two['field_token'];
        if (!isset($rpg_fields_index[$field_token_one])){ return 0; }
        if (!isset($rpg_fields_index[$field_token_two])){ return 0; }
        $field_one = $rpg_fields_index[$field_token_one];
        $field_two = $rpg_fields_index[$field_token_two];
        if ($field_one['field_game'] > $field_two['field_game']){ return 1; }
        elseif ($field_one['field_game'] < $field_two['field_game']){ return -1; }
        if ($field_one['field_token'] > $field_two['field_token']){ return 1; }
        elseif ($field_one['field_token'] < $field_two['field_token']){ return -1; }
        else { return 0; }
    }

    /**
     * Sort an array of items for the editor by kind and then token
     * @param array $item_one
     * @param array $item_two
     * @return int
     */
    public static function items_sort_for_editor($item_one, $item_two){
        global $mmrpg_index;
        $item_token_one = preg_match('/^([a-z0-9]+)-(a-z0-9+)$/i', $item_one['item_token']) ? $item_one['item_token'] : $item_one['item_token'].'-size';
        $item_token_two = preg_match('/^([a-z0-9]+)-(a-z0-9+)$/i', $item_two['item_token']) ? $item_two['item_token'] : $item_two['item_token'].'-size';
        list($kind_one, $size_one) = explode('-', $item_token_one);
        list($kind_two, $size_two) = explode('-', $item_token_two);
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


    /**
     * Print an array of values as if they were a list of directory paths
     * @param array $this_array
     * @param string $this_path (optional)
     * @return string
     */
    public static function print_array($this_array, $this_path = ''){
        $this_print = '';
        foreach ($this_array AS $key => $value){
            $path = $this_path.$key;
             if (is_bool($value)){
                $this_print .= $path.' = '.($value ? 'true' : 'false').PHP_EOL;
            } elseif (is_numeric($value)){
                $this_print .= $path.' = '.$value.PHP_EOL;
            } elseif (is_string($value)) {
                $this_print .= $path.' = "'.$value.'"'.PHP_EOL;
            } elseif (is_array($value)){
                if (empty($value)){
                    $this_print .= $path.' = []'.PHP_EOL;
                } elseif (isset($value[0]) && !self::is_multi_array($value[0])){
                    $this_print .= $path.' = '.json_encode($value).PHP_EOL;
                } else {
                    $this_print .= $path.' = []'.PHP_EOL;
                    $path .= '/';
                    $this_print .= self::print_array($value, $path);
                }
            }
        }
        return $this_print;
    }

    /**
     * Check if a given array of values is multi-dimensional or not
     * @param array $array
     * @return bool
     */
    public static function is_multi_array($array) {
        if (!is_array($array)){ return false; }
        foreach ($array as $value) { if (is_array($value)) return true; }
        return false;
    }

    /**
     * Generate and return array data in an easier to read format
     * @param array $variable
     * @param string $base (optional)
     * @return string
     */
    public static function print_r($variable, $base = '/'){
        ob_start();
        echo '<pre>';
        echo rpg_functions::print_r_recursive($variable, $base);
        echo '</pre>';
        return ob_get_clean();
    }

    /**
     * Print out array data in an easier to read format recursively
     * @param array $variable
     * @param string $base (optional)
     */
    public static function print_r_recursive($variable, $base = '/'){
        //if (is_array($variable)){ echo '<br /><br />'.$base;  }
        if (is_array($variable) && count($variable) > 1){ echo '<br /><br />'.$base;  }
        elseif (is_array($variable) && count($variable) == 1){ echo '<br />'.$base;  }
        else { echo $base; }
        if (is_bool($variable)){ echo ($variable == true ? 'true' : 'false').'<br />'; }
        elseif (!is_numeric($variable) && empty($variable)){ echo (is_array($variable) ? '=-' : '-').'<br />'; }
        elseif (!is_array($variable)){ echo $variable.'<br />'; }
        elseif (is_array($variable)){
            echo '=<br />';
            foreach ($variable AS $key => $value){
                echo rpg_functions::print_r_recursive($value, $base.$key.'/');
            }
            if (is_array($variable) && count($variable) > 1){ echo '<br />';  }
        }
    }

}
?>