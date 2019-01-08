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
    public function rpg_prototype(){ }

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
        //global $mmrpg_index;
        $mmrpg_index_players = rpg_player::get_index();
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


}
?>