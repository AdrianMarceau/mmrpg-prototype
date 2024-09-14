<?php

class rpg_reset {

    private $user_id;
    private $session_token;
    private $session_data;

    public function __construct($user_id, $session_token) {
        $this->user_id = $user_id;
        $this->session_token = $session_token;
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
        error_log('rpg_reset->reset_missions() called');

        // Pull necessary indexes for this action
        $mmrpg_index_players = rpg_player::get_index(true);

        // Pull backups of the battles complete and failure
        $this_battle_complete = !empty($this->session_data['values']['battle_complete']) ? $this->session_data['values']['battle_complete'] : array();
        $this_battle_failure = !empty($this->session_data['values']['battle_failure']) ? $this->session_data['values']['battle_failure'] : array();
        $this_turns_total = !empty($this->session_data['counters']['battle_turns_total']) ? $this->session_data['counters']['battle_turns_total'] : 0;

        // Reset the battle complete and failure arrays to empty
        $this->session_data['values']['battle_index'] = array();
        $this->session_data['values']['battle_complete'] = array();
        $this->session_data['values']['battle_failure'] = array();
        $this->session_data['counters']['battle_turns_total'] = 0;

        // Reset player-specific battle settings
        foreach ($mmrpg_index_players as $ptoken => $info) {
            $pxtoken = str_replace('dr-', '', $ptoken);
            $this->session_data['counters']['battle_turns_' . $ptoken . '_total'] = 0;
            $temp_omega_key = $ptoken . '_target-robot-omega_prototype';
            $this->session_data['values'][$temp_omega_key] = array();
        }

        // Clear endless mode savestates if needed
        $db = cms_database::get_database();
        $db->update('mmrpg_challenges_waveboard',
            array('challenge_wave_savestate' => ''),
            array('user_id' => $this->user_id)
            );

        // Export changes to the session data
        $this->export();

        // Return true on success
        return true;
    }

    // RESET EVENTS
    // Reset all event-related flags and settings
    public function reset_events() {
        error_log('rpg_reset->reset_events() called');

        // Pull necessary indexes for this action
        $mmrpg_index_players = rpg_player::get_index(true);

        // Reset event flags for each player
        $clear_event_flags = array(
            '-event-97_phase-one-complete',
            '-event-97_phase-two-complete',
            '-event-97_phase-three-complete'
        );
        foreach ($mmrpg_index_players as $ptoken => $info) {
            $pxtoken = str_replace('dr-', '', $ptoken);
            foreach ($clear_event_flags as $event_flag) {
                $clear_event_flag = $ptoken . $event_flag;
                unset($this->session_data['flags']['events'][$clear_event_flag]);
            }
            for ($i = 0; $i <= 10; $i++) {
                $clear_event_flag = $ptoken . '_chapter-' . $i . '-unlocked';
                unset($this->session_data['flags']['events'][$clear_event_flag]);
            }
            $clear_event_flag = $pxtoken . '_current_chapter';
            $this->session_data['battle_settings'][$clear_event_flag] = 0;
        }

        // Clear chapter unlock flags in battle settings
        if (!empty($this->session_data['battle_settings']['flags'])) {
            foreach ($this->session_data['battle_settings']['flags'] as $flag => $value) {
                if (!preg_match('/^([a-z0-9]+)_unlocked_chapter_([0-9]+)$/i', $flag)) {
                    continue;
                }
                unset($this->session_data['battle_settings']['flags'][$flag]);
            }
        }

        unset($this->session_data['battle_settings']['this_player_token']);

        // Export changes to the session data
        $this->export();

        // Return true on success
        return true;
    }

    // RESET ROBOTS
    // Reset robots to level 1 with 999 experience and move them back to their original owners
    public function reset_robots() {
        error_log('rpg_reset->reset_robots() called');

        // Pull necessary indexes for this action
        $mmrpg_index_players = rpg_player::get_index(true);
        $mmrpg_index_robots = rpg_robot::get_index(true);

        // Loop through players and reset their robots
        foreach ($mmrpg_index_players as $ptoken => $info) {

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
        $mmrpg_index_players = rpg_player::get_index(true);
        $mmrpg_index_robots = rpg_robot::get_index(true);

        // First we pull all robots into a single array
        $session_robots = array();
        if (!empty($this->session_data['values']['battle_rewards'])){
            foreach ($mmrpg_index_players as $ptoken => $pinfo){
                if (!empty($this->session_data['values']['battle_rewards'][$ptoken]['player_robots'])){
                    foreach ($this->session_data['values']['battle_rewards'][$ptoken]['player_robots'] as $rtoken => $rewards){
                        if (!isset($session_robots[$rtoken])){ $session_robots[$rtoken] = array(); }
                        if (isset($session_robots[$rtoken]['rewards'])){ $rewards = array_merge($session_robots[$rtoken]['rewards'], $rewards); }
                        $session_robots[$rtoken]['rewards'] = $rewards;
                    }
                }
            }
        }
        if (!empty($this->session_data['values']['battle_settings'])){
            foreach ($mmrpg_index_players as $ptoken => $pinfo){
                if (!empty($this->session_data['values']['battle_settings'][$ptoken]['player_robots'])){
                    foreach ($this->session_data['values']['battle_settings'][$ptoken]['player_robots'] as $rtoken => $settings){
                        if (!isset($session_robots[$rtoken])){ $session_robots[$rtoken] = array(); }
                        $original_player = '';
                        if ($rtoken === 'mega-man' || $rtoken === 'roll') { $original_player = 'dr-light'; }
                        elseif ($rtoken === 'bass' || $rtoken === 'disco') { $original_player = 'dr-wily'; }
                        elseif ($rtoken === 'proto-man' || $rtoken === 'rhythm') { $original_player = 'dr-cossack'; }
                        else { $original_player = !empty($settings['original_player']) ? $settings['original_player'] : $ptoken; }
                        $settings['original_player'] = $original_player;
                        if (isset($session_robots[$rtoken]['settings'])){ $settings = array_merge($session_robots[$rtoken]['settings'], $settings); }
                        $session_robots[$rtoken]['settings'] = $settings;
                    }
                }
            }
        }
        //error_log('$session_robots = '.print_r($session_robots, true));

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
        foreach ($session_robots as $rtoken => $rdata){
            if (!isset($rdata['settings']['original_player'])){ continue; }
            else { $ptoken = $rdata['settings']['original_player']; }
            $rewards = !empty($rdata['rewards']) ? $rdata['rewards'] : array();
            $settings = !empty($rdata['settings']) ? $rdata['settings'] : array();
            $new_battle_rewards[$ptoken]['player_robots'][$rtoken] = $rewards;
            $new_battle_settings[$ptoken]['player_robots'][$rtoken] = $settings;
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
