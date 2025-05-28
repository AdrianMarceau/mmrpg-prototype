<?php

class rpg_reset {

    private $user_id;
    private $session_token;
    private $session_player;
    private $session_data;

    public function __construct($user_id, $session_token, $session_player = '') {
        $this->user_id = $user_id;
        $this->session_token = $session_token;
        $this->session_player = $session_player;
        $this->import();
    }

    private function import() {
        // Import session data
        $this->session_data = isset($_SESSION[$this->session_token]) ? $_SESSION[$this->session_token] : array();
    }

    public function export() {
        // Export session data back to the session
        $_SESSION[$this->session_token] = $this->session_data;
    }

    // RESET MISSIONS
    // Reset all missions and story progress but keep everything else
    public function reset_missions() {
        //error_log('rpg_reset->reset_missions() called');

        // Pull necessary indexes for this action
        $session_player = !empty($this->session_player) ? $this->session_player : false;
        $mmrpg_index_players = rpg_player::get_index(true);

        // Pull backups of the battles complete and failure
        $this_battle_complete = !empty($this->session_data['values']['battle_complete']) ? $this->session_data['values']['battle_complete'] : array();
        $this_battle_failure = !empty($this->session_data['values']['battle_failure']) ? $this->session_data['values']['battle_failure'] : array();
        $this_turns_total = !empty($this->session_data['counters']['battle_turns_total']) ? $this->session_data['counters']['battle_turns_total'] : 0;

        // Make sure required indexes actually exist before looping
        if (!isset($this->session_data['values']['battle_index'])){ $this->session_data['values']['battle_index'] = array(); }
        if (!isset($this->session_data['values']['battle_complete'])){ $this->session_data['values']['battle_complete'] = array(); }
        if (!isset($this->session_data['values']['battle_failure'])){ $this->session_data['values']['battle_failure'] = array(); }
        if (!isset($this->session_data['counters']['battle_turns_total'])){ $this->session_data['counters']['battle_turns_total'] = 0; }

        // Reset the battle complete and failure arrays to empty, either for everyone or only selected player
        foreach ($mmrpg_index_players as $ptoken => $info) {
            if (!empty($session_player) && $session_player !== $ptoken){ continue; }
            $pxtoken = str_replace('dr-', '', $ptoken);
            $pttoken = 'battle_turns_'.$ptoken.'_total';
            $pturns = !empty($this->session_data['counters'][$pttoken]) ? $this->session_data['counters'][$pttoken] : 0;
            $this->session_data['values']['battle_index'][$ptoken] = array();
            $this->session_data['values']['battle_complete'][$ptoken] = array();
            $this->session_data['values']['battle_failure'][$ptoken] = array();
            $this->session_data['counters']['battle_turns_'.$ptoken.'_total'] = 0;
            $this->session_data['counters']['battle_turns_total'] -= $pturns;
            if ($this->session_data['counters']['battle_turns_total'] < 0){
                $this->session_data['counters']['battle_turns_total'] = 0;
            }
        }

        // Clear endless mode savestates if needed, just-in-case player robots are still present there
        $db = cms_database::get_database();
        $update_condition = "user_id = {$this->user_id}";
        if (!empty($session_player)){ $update_condition .= " AND challenge_team_config LIKE '{$session_player}::%'"; }
        $db->update('mmrpg_challenges_waveboard',
            array('challenge_wave_savestate' => ''),
            $update_condition
            );

        // Export changes to the session data
        $this->export();

        // Return true on success
        return true;
    }

    // RESET EVENTS
    // Reset all event-related flags and settings
    public function reset_events() {
        //error_log('rpg_reset->reset_events() called');

        // Pull necessary indexes for this action
        $session_player = !empty($this->session_player) ? $this->session_player : false;
        $mmrpg_index_players = rpg_player::get_index(true);

        // Reset event flags for each player
        $clear_event_flags = array(
            '-event-97_phase-one-complete',
            '-event-97_phase-two-complete',
            '-event-97_phase-three-complete'
        );
        foreach ($mmrpg_index_players as $ptoken => $info) {
            if (!empty($session_player) && $session_player !== $ptoken){ continue; }
            $pxtoken = str_replace('dr-', '', $ptoken);
            // clear the phaseX complete flags first and foremost
            foreach ($clear_event_flags as $event_flag) {
                $clear_event_flag1 = $ptoken . $event_flag;
                unset($this->session_data['flags']['events'][$clear_event_flag1]);
            }
            // clear any of the chapter unlock flags in event flags and in battle settings
            // (no, i have no idea what this is duplicated but we'll figure that out after)
            for ($ch = 0; $ch <= 10; $ch++) {
                $clear_event_flag2 = $ptoken . '_chapter-' . $ch . '-unlocked';
                unset($this->session_data['flags']['events'][$clear_event_flag2]);
                $clear_event_flag3 = $ptoken . '_unlocked_chapter_' . $ch;
                unset($this->session_data['battle_settings']['flags'][$clear_event_flag3]);
            }
            // clear the current chapter flag in battle settings
            $clear_event_flag4 = $pxtoken . '_current_chapter';
            $this->session_data['battle_settings'][$clear_event_flag4] = 0;
        }

        // Always clear current player token when initiation new game +
        // regardless of who it is so the game reload at player select
        unset($this->session_data['battle_settings']['this_player_token']);

        // Export changes to the session data
        $this->export();

        // Return true on success
        return true;
    }

    // RESET ROBOTS
    // Reset robots to level 1 with 999 experience and move them back to their original owners
    public function reset_robots() {
        //error_log('rpg_reset->reset_robots() called');

        // Pull necessary indexes for this action
        $session_player = !empty($this->session_player) ? $this->session_player : false;
        $mmrpg_index_players = rpg_player::get_index(true);
        $mmrpg_index_robots = rpg_robot::get_index(true);

        // Loop through players and reset their robots
        foreach ($mmrpg_index_players as $ptoken => $info) {
            if (!empty($session_player) && $session_player !== $ptoken){ continue; }

            // Collect the current rewards and settings for this player
            $rewards = !empty($this->session_data['values']['battle_rewards'][$ptoken]) ? $this->session_data['values']['battle_rewards'][$ptoken] : array();
            $settings = !empty($this->session_data['values']['battle_settings'][$ptoken]) ? $this->session_data['values']['battle_settings'][$ptoken] : array();

            // Loop through this player's robots and reset their level and experience
            $probot_rewards = !empty($rewards['player_robots']) ? $rewards['player_robots'] : array();
            $probot_settings = !empty($settings['player_robots']) ? $settings['player_robots'] : array();
            if (empty($probot_rewards) && empty($probot_settings)) { continue; }

            foreach ($probot_rewards as $prtoken => $robot_data) {
                // Reset robot's level to 1 and experience to 999
                $this->session_data['values']['battle_rewards'][$ptoken]['player_robots'][$prtoken]['robot_level'] = 1;
                $this->session_data['values']['battle_rewards'][$ptoken]['player_robots'][$prtoken]['robot_experience'] = 999;
            }

            foreach ($probot_settings as $prtoken => $robot_data) {
                // Reset robot's level to 1 and experience to 999 in settings as well
                $this->session_data['values']['battle_settings'][$ptoken]['player_robots'][$prtoken]['robot_level'] = 1;
                $this->session_data['values']['battle_settings'][$ptoken]['player_robots'][$prtoken]['robot_experience'] = 999;
            }
        }

        // Loop through master robots array, reassigning robots to their original owners
        foreach ($mmrpg_index_robots as $rtoken => $rinfo) {
            $original_player = '';
            if ($rtoken === 'mega-man') { $original_player = 'dr-light'; }
            elseif ($rtoken === 'bass') { $original_player = 'dr-wily'; }
            elseif ($rtoken === 'proto-man') { $original_player = 'dr-cossack'; }
            if (!empty($original_player)) {
                if (!empty($session_player) && $session_player !== $original_player){ continue; }
                foreach ($mmrpg_index_players as $ptoken => $pinfo) {
                    // Move robots to their original owners
                    if ($ptoken === $original_player) {
                        // Move robot back to the original owner
                        if (isset($this->session_data['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken])) {
                            $this->session_data['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken]['robot_level'] = 1;
                            $this->session_data['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken]['robot_experience'] = 999;
                        }
                        if (isset($this->session_data['values']['battle_settings'][$ptoken]['player_robots'][$rtoken])) {
                            $this->session_data['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_level'] = 1;
                            $this->session_data['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_experience'] = 999;
                        }
                    }
                }
            }
        }

        // Define a quick, inline function that re-sorts a robot array given it's index position
        $sort_robots_by_index = function($robots) use ($mmrpg_index_robots){
            $new_robots = array();
            foreach ($mmrpg_index_robots AS $token => $info){
                if (!empty($robots[$token])){ $new_robots[$token] = $robots[$token]; }
                }
            return $new_robots;
            };

        // Loop through players again, but this time re-sort all robots by their index position
        foreach ($mmrpg_index_players as $ptoken => $info) {
            // Collect the current rewards and settings for this player
            $rewards = !empty($this->session_data['values']['battle_rewards'][$ptoken]) ? $this->session_data['values']['battle_rewards'][$ptoken] : array();
            $settings = !empty($this->session_data['values']['battle_settings'][$ptoken]) ? $this->session_data['values']['battle_settings'][$ptoken] : array();
            // Loop through this player's robots and re-sort them by their index position
            $probot_rewards = !empty($rewards['player_robots']) ? $rewards['player_robots'] : array();
            $probot_settings = !empty($settings['player_robots']) ? $settings['player_robots'] : array();
            if (empty($probot_rewards) && empty($probot_settings)) { continue; }
            $probot_rewards = $sort_robots_by_index($probot_rewards);
            $probot_settings = $sort_robots_by_index($probot_settings);
            $this->session_data['values']['battle_rewards'][$ptoken]['player_robots'] = $probot_rewards;
            $this->session_data['values']['battle_settings'][$ptoken]['player_robots'] = $probot_settings;
        }

        // Export changes to the session data
        $this->export();

        // Return true on success
        return true;
    }

    // REGROUP ROBOTS
    // Regroup robots by moving them back to their original owners
    public function regroup_robots() {
        //error_log('rpg_reset->regroup_robots() called');

        // Pull necessary indexes for this action
        $session_player = !empty($this->session_player) ? $this->session_player : false;
        $mmrpg_index_players = rpg_player::get_index(true);
        $mmrpg_index_robots = rpg_robot::get_index(true);

        // Create an array to keep track of which player had which robots initially
        $robot_to_current_player = array();
        $robot_to_original_player = array();
        $robots_by_current_player = array();
        $robots_by_original_player = array();

        // First we pull all relevant robots into a single array
        $session_robots = array();
        if (!empty($this->session_data['values']['battle_rewards'])){
            foreach ($mmrpg_index_players as $ptoken => $pinfo){
                if (!empty($this->session_data['values']['battle_rewards'][$ptoken]['player_robots'])){
                    if (!isset($robots_by_current_player[$ptoken])){ $robots_by_current_player[$ptoken] = array(); }
                    foreach ($this->session_data['values']['battle_rewards'][$ptoken]['player_robots'] as $rtoken => $rewards){
                        if (!in_array($rtoken, $robots_by_current_player[$ptoken])){ $robots_by_current_player[$ptoken][] = $rtoken; }
                        if (!isset($session_robots[$rtoken])){ $session_robots[$rtoken] = array(); }
                        if (isset($session_robots[$rtoken]['rewards'])){ $rewards = array_merge($session_robots[$rtoken]['rewards'], $rewards); }
                        $session_robots[$rtoken]['rewards'] = $rewards;
                        $robot_to_current_player[$rtoken] = $ptoken;
                    }
                }
            }
        }
        if (!empty($this->session_data['values']['battle_settings'])){
            foreach ($mmrpg_index_players as $ptoken => $pinfo){
                if (!empty($this->session_data['values']['battle_settings'][$ptoken]['player_robots'])){
                    if (!isset($robots_by_current_player[$ptoken])){ $robots_by_current_player[$ptoken] = array(); }
                    foreach ($this->session_data['values']['battle_settings'][$ptoken]['player_robots'] as $rtoken => $settings){
                        if (!in_array($rtoken, $robots_by_current_player[$ptoken])){ $robots_by_current_player[$ptoken][] = $rtoken; }
                        if (!isset($session_robots[$rtoken])){ $session_robots[$rtoken] = array(); }
                        $original_player = '';
                        if ($rtoken === 'mega-man' || $rtoken === 'roll') { $original_player = 'dr-light'; }
                        elseif ($rtoken === 'bass' || $rtoken === 'disco') { $original_player = 'dr-wily'; }
                        elseif ($rtoken === 'proto-man' || $rtoken === 'rhythm') { $original_player = 'dr-cossack'; }
                        else { $original_player = !empty($settings['original_player']) ? $settings['original_player'] : $ptoken; }
                        $settings['original_player'] = $original_player;
                        if (isset($session_robots[$rtoken]['settings'])){ $settings = array_merge($session_robots[$rtoken]['settings'], $settings); }
                        $session_robots[$rtoken]['settings'] = $settings;
                        if (!isset($robots_by_original_player[$original_player])){ $robots_by_original_player[$original_player] = array(); }
                        if (!in_array($rtoken, $robots_by_original_player[$original_player])){ $robots_by_original_player[$original_player][] = $rtoken; }
                        $robot_to_current_player[$rtoken] = $ptoken;
                        $robot_to_original_player[$rtoken] = $original_player;
                    }
                }
            }
        }
        //error_log('$session_robots = '.print_r($session_robots, true));
        //error_log('$session_robots(tokens)[x'.count($session_robots).'] = '.print_r(implode(', ', array_keys($session_robots)), true).'');
        //error_log('$robot_to_current_player = '.print_r($robot_to_current_player, true));
        //error_log('$robot_to_original_player = '.print_r($robot_to_original_player, true));
        //error_log('$robots_by_current_player = '.print_r($robots_by_current_player, true));
        //error_log('$robots_by_original_player = '.print_r($robots_by_original_player, true));

        // Start new rewards and settings arrays to populate from stored robots
        $new_battle_rewards = $this->session_data['values']['battle_rewards'];
        $new_battle_settings = $this->session_data['values']['battle_settings'];

        // Clear the robots from the battle rewards and settings arrays
        foreach ($mmrpg_index_players as $ptoken => $pinfo){
            if (!empty($new_battle_rewards[$ptoken]['player_robots'])){
                $new_battle_rewards[$ptoken]['player_robots'] = array();
            }
            if (!empty($new_battle_settings[$ptoken]['player_robots'])){
                $new_battle_settings[$ptoken]['player_robots'] = array();
            }
        }

        // Loop through master robots, in order, reassigning them to their original owners
        $move_method = !empty($session_player) ? 'select' : 'all';
        //error_log('$move_method = '.$move_method.' ($session_player = '.$session_player.')');
        foreach ($session_robots as $rtoken => $rdata){
            $curr_ptoken = !empty($robot_to_current_player[$rtoken]) ? $robot_to_current_player[$rtoken] : '';
            $orig_ptoken = !empty($robot_to_original_player[$rtoken]) ? $robot_to_original_player[$rtoken] : '';
            $new_ptoken = $curr_ptoken;
            if ($move_method === 'all'){ $new_ptoken = $orig_ptoken; }
            if ($move_method === 'select' && $orig_ptoken === $session_player){ $new_ptoken = $orig_ptoken; }
            //error_log($curr_ptoken.' robot '.$rtoken.' will '.($new_ptoken === $curr_ptoken ? 'stay with '.$curr_ptoken : 'move to '.$new_ptoken));
            $rewards = !empty($rdata['rewards']) ? $rdata['rewards'] : array();
            $settings = !empty($rdata['settings']) ? $rdata['settings'] : array();
            $new_battle_rewards[$new_ptoken]['player_robots'][$rtoken] = $rewards;
            $new_battle_settings[$new_ptoken]['player_robots'][$rtoken] = $settings;
        }

        // Reassign the new rewards and settings arrays to the session data
        $this->session_data['values']['battle_rewards'] = $new_battle_rewards;
        $this->session_data['values']['battle_settings'] = $new_battle_settings;
        //error_log('$new_battle_rewards = '.print_r($new_battle_rewards, true));
        //error_log('$new_battle_settings = '.print_r($new_battle_settings, true));

        // Define a quick, inline function that re-sorts a robot array given it's index position
        $sort_robots_by_index = function($robots) use ($mmrpg_index_robots){
            $new_robots = array();
            foreach ($mmrpg_index_robots AS $token => $info){
                if (!empty($robots[$token])){ $new_robots[$token] = $robots[$token]; }
                }
            return $new_robots;
            };

        // Loop through players again, but this time re-sort all robots by their index position
        foreach ($mmrpg_index_players as $ptoken => $info) {
            if (!empty($session_player) && $session_player !== $ptoken){ continue; }
            // Collect the current rewards and settings for this player
            $rewards = !empty($this->session_data['values']['battle_rewards'][$ptoken]) ? $this->session_data['values']['battle_rewards'][$ptoken] : array();
            $settings = !empty($this->session_data['values']['battle_settings'][$ptoken]) ? $this->session_data['values']['battle_settings'][$ptoken] : array();
            // Loop through this player's robots and re-sort them by their index position
            $probot_rewards = !empty($rewards['player_robots']) ? $rewards['player_robots'] : array();
            $probot_settings = !empty($settings['player_robots']) ? $settings['player_robots'] : array();
            if (empty($probot_rewards) && empty($probot_settings)) { continue; }
            $probot_rewards = $sort_robots_by_index($probot_rewards);
            $probot_settings = $sort_robots_by_index($probot_settings);
            $this->session_data['values']['battle_rewards'][$ptoken]['player_robots'] = $probot_rewards;
            $this->session_data['values']['battle_settings'][$ptoken]['player_robots'] = $probot_settings;
        }

        // Export changes to the session data
        $this->export();

        // Return true on success
        return true;
    }



}
