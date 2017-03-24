<?php

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while importing!');  }

// Collect any extra request variables for the import
$this_import_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=import-missions&limit=<?= $this_import_limit?>">Update Mission Database</a> &raquo;
</div>
<?php
$this_page_markup .= ob_get_clean();



// ------------------------------ //
// MISSION VARIABLES / FUNCTIONS
// ------------------------------ //

// Define a variable to hold all database missions
$mmrpg_database_missions = array();

// Define a quick insert function for new missions
function mmrpg_insert_mission(
    $phase = '',
    $chapter = '',
    $group = '',
    $player = '',
    $field = '',
    $targets = array(),
    $level = 0,
    $max_level = 0,
    $order = 0
    ){
    global $mmrpg_database_missions;
    $targets = implode(',', $targets);
    $level = !empty($level) ? $level : 1;
    $max_level = !empty($max_level) ? $max_level : $level;
    $this_mission = array();
    $this_mission['mission_phase'] = $phase;
    $this_mission['mission_chapter'] = $chapter;
    $this_mission['mission_group'] = $group;
    $this_mission['mission_player'] = $player;
    $this_mission['mission_field'] = $field;
    $this_mission['mission_targets'] = $targets;
    $this_mission['mission_level'] = $level;
    $this_mission['mission_level_max'] = $max_level;
    $this_mission['mission_order'] = $order;
    $mmrpg_database_missions[] = $this_mission;
}



// ------------------------- //
// MISSION SEEDS / TOKENS
// ------------------------- //

// Define the ROBOT tokens we'll be generating missions for
$mmrpg_omega_factors = $db->get_array_list("SELECT
    irobots.robot_token AS omega_robot,
    irobots.robot_core AS omega_type,
    ifields.field_token AS omega_field,
    ifields.field_mechas AS omega_mechas,
    irobots.robot_game AS omega_group
    FROM mmrpg_index_robots AS irobots
    LEFT JOIN mmrpg_index_fields AS ifields ON ifields.field_token = irobots.robot_field
    WHERE
    irobots.robot_class = 'master'
    AND irobots.robot_core <> ''
    AND irobots.robot_core <> 'copy'
    AND irobots.robot_core <> 'empty'
    AND irobots.robot_flag_complete = 1
    AND ifields.field_flag_complete = 1
    ORDER BY
    irobots.robot_order ASC
    ;");

// Define the PLAYER+FIELD tokens we'll be generating missions for
$mmrpg_player_tokens = array('dr-light', 'dr-wily', 'dr-cossack');
$mmrpg_player_stat_tokens = array('defense', 'attack', 'speed');
$mmrpg_player_field_tokens = array('light-laboratory', 'wily-castle', 'cossack-citadel');
$mmrpg_player_robot_master_tokens = array('mega-man', 'bass', 'proto-man');
$mmrpg_player_robot_support_tokens = array('roll', 'disco', 'rhythm');

// Define the RIVAL-PLAYER+FIELD tokens we'll be generating missions for
$mmrpg_rival_tokens = array('dr-wily', 'dr-cossack', 'dr-light');

// Define the MECHA-JOE tokens that we'll be using in our missions
$mmrpg_mecha_joe_tokens = array('sniper-joe', 'skeleton-joe', 'crystal-joe');

// Define the KILLER-ROBOT tokens that we'll be using in our missions
$mmrpg_killer_robot_tokens = array('enker', 'punk', 'ballade');

// Define the MISSION-LEVELS variables that the entire game will use for missions
$mmrpg_mission_levels = array(
    1 => array(1, 2, 3),        // Chapter One   (Intro Battles)
    2 => array(4, 1, 12),       // Chapter Two   (Master Battles)
    3 => array(14, 16, 18),     // Chapter Three (Rival Battles)
    4 => array(20, 2, 30),      // Chapter Four  (Fusion Battles)
    5 => array(35, 40, 45),     // Chapter Five  (Darkness Battles)
    6 => array(50, 60, 70),     // Chapter Six   (Stardroid Battles)
    7 => array(80, 90, 100),    // Chapter Seven (Final Battles)
    8 => array(200, 100, 1000)  // Chapter Eight (Cache Battles)
    );

// Define the CHAPTER-MISSION-SIZE counters that the entire game will use for missions
$mmrpg_mission_sizes = array(
    1 => array(1, 1, 1),       // Chapter One   (Intro Battles)
    2 => array(4, 4, 1),       // Chapter Two   (Master Battles)
    3 => array(1, 1, 1),       // Chapter Three (Rival Battles)
    4 => array(2, 2, 1),       // Chapter Four  (Fusion Battles)
    5 => array(1, 1, 1),       // Chapter Five  (Darkness Battles)
    6 => array(1, 1, 1),       // Chapter Six   (Stardroid Battles)
    7 => array(1, 1, 1),       // Chapter Seven (Final Battles)
    8 => array(4, 4, 1)        // Chapter Eight (Cache Battles)
    );



// ------------------------------ //
// MISSION GENERATION : INIT
// ------------------------------ //

// Define the global PHASE and CHAPTER variables at zero
$this_mission_phase = 0;
$this_mission_chapter = 0;



// ------------------------------ //
// MISSION GENERATION : PHASE ONE
// ------------------------------ //

// PHASE 1 START
$this_mission_phase++;

// CHAPTER ONE / Intro Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
foreach ($mmrpg_player_tokens AS $player_key => $player_token){
    $order = 0;

    $rival_token = $mmrpg_rival_tokens[$player_key];
    $rival_key = array_search($rival_token, $mmrpg_player_tokens);
    $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

    // vs MET
    $order++;
    $group = 'intro-battle';
    $field = 'intro-field';
    $targets = array(
        'met'
        );
    $level = $this_mission_levels[0];
    $max_level = $level;
    mmrpg_insert_mission(
        $this_mission_phase,
        $this_mission_chapter,
        $group,
        $player_token,
        $field,
        $targets,
        $level,
        $max_level,
        $order
        );

    // vs MECHA JOE
    $order++;
    $group = 'intro-battle';
    $field = $mmrpg_player_field_tokens[$player_key];
    $targets = array(
        $mmrpg_mecha_joe_tokens[$player_key]
        );
    $level = $this_mission_levels[1];
    $max_level = $level;
    mmrpg_insert_mission(
        $this_mission_phase,
        $this_mission_chapter,
        $group,
        $player_token,
        $field,
        $targets,
        $level,
        $max_level,
        $order
        );

    // vs TRILL
    $order++;
    $group = 'intro-battle';
    $field = 'prototype-subspace';
    $targets = array(
        'trill_'.$mmrpg_player_stat_tokens[$rival_key]
        );
    $level = $this_mission_levels[2];
    $max_level = $level;
    mmrpg_insert_mission(
        $this_mission_phase,
        $this_mission_chapter,
        $group,
        $player_token,
        $field,
        $targets,
        $level,
        $max_level,
        $order
        );

}



// ------------------------------ //
// MISSION GENERATION : PHASE TWO
// ------------------------------ //

// PHASE 2 START
$this_mission_phase++;

// CHAPTER TWO / Master Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
if (true){
    $order = 0;

    // vs MASTERS
    $order++;
    $level = $this_mission_levels[0];
    $max_level = $this_mission_levels[2] - $this_mission_levels[1];
    foreach ($mmrpg_omega_factors AS $omega_key => $omega_factor){

        $group = 'single-battle';
        $player = 'global';
        $field = $omega_factor['omega_field'];
        $targets = array(
            $omega_factor['omega_robot']
            );
        mmrpg_insert_mission(
            $this_mission_phase,
            $this_mission_chapter,
            $group,
            $player,
            $field,
            $targets,
            $level,
            $max_level,
            $order
            );

    }

    // vs DOC-ROBOT
    $order++;
    foreach ($mmrpg_player_tokens AS $player_key => $player_token){

        $rival_token = $mmrpg_rival_tokens[$player_key];
        $rival_key = array_search($rival_token, $mmrpg_player_tokens);
        $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

        $group = 'fortress-battle';
        $field = 'xxx-field';
        $targets = array(
            'doc-robot'
            );
        $level = $this_mission_levels[2];
        $max_level = $level;
        mmrpg_insert_mission(
            $this_mission_phase,
            $this_mission_chapter,
            $group,
            $player_token,
            $field,
            $targets,
            $level,
            $max_level,
            $order
            );

    }

}

// CHAPTER THREE / Rival Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
foreach ($mmrpg_player_tokens AS $player_key => $player_token){
    $order = 0;

    $rival_token = $mmrpg_rival_tokens[$player_key];
    $rival_key = array_search($rival_token, $mmrpg_player_tokens);
    $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

    // vs RIVALS
    $order++;
    $group = 'rival-battle';
    $field = $mmrpg_player_field_tokens[$rival_key];
    $targets = array(
        $mmrpg_player_robot_master_tokens[$rival_key],
        $mmrpg_player_robot_support_tokens[$rival_key]
        );
    $level = $this_mission_levels[0];
    $max_level = $level;
    mmrpg_insert_mission(
        $this_mission_phase,
        $this_mission_chapter,
        $group,
        $player_token,
        $field,
        $targets,
        $level,
        $max_level,
        $order
        );

    // vs KILLERS
    $order++;
    $group = 'killer-battle';
    $field = 'xxx-field';
    $targets = array(
        $mmrpg_killer_robot_tokens[$player_key],
        'quint'
        );
    $level = $this_mission_levels[1];
    $max_level = $level;
    mmrpg_insert_mission(
        $this_mission_phase,
        $this_mission_chapter,
        $group,
        $player_token,
        $field,
        $targets,
        $level,
        $max_level,
        $order
        );

    // vs ALIENS
    $order++;
    $group = 'alien-battle';
    $field = 'xxx-field';
    $targets = array(
        'sunstar',
        'trill_'.$mmrpg_player_stat_tokens[$final_key]
        );
    $level = $this_mission_levels[2];
    $max_level = $level;
    mmrpg_insert_mission(
        $this_mission_phase,
        $this_mission_chapter,
        $group,
        $player_token,
        $field,
        $targets,
        $level,
        $max_level,
        $order
        );

}



// DEBUG
echo('<pre>$mmrpg_database_missions('.count($mmrpg_database_missions).') = '.print_r($mmrpg_database_missions, true).'</pre>');


// Truncate any robots currently in the database
$db->query('TRUNCATE TABLE mmrpg_index_missions');

// Loop through and insert these missions into the database
foreach ($mmrpg_database_missions AS $mission_key => $mission_info){

    $db->insert('mmrpg_index_missions', $mission_info);

}


exit();


// Generate page headers
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_missions</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_database_missions) ? count($mmrpg_database_missions) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_missions, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';

$spreadsheet_mission_stats = array(); //mmrpg_spreadsheet_mission_stats();
$spreadsheet_mission_descriptions = array(); //mmrpg_spreadsheet_mission_descriptions();


// Sort the mission index based on mission number
$temp_pattern_first = array();
$temp_pattern_first[] = '/^dr-light$/i';
$temp_pattern_first[] = '/^dr-wily$/i';
$temp_pattern_first[] = '/^dr-cossack/i';
//$temp_pattern_first = array_reverse($temp_pattern_first);
$temp_pattern_last = array();
//$temp_pattern_last = array_reverse($temp_pattern_last);
// Sort the mission index based on mission number
function mmrpg_index_sort_missions($mission_one, $mission_two){
    // Pull in global variables
    global $temp_pattern_first, $temp_pattern_last;
    // Loop through all the temp patterns and compare them one at a time
    foreach ($temp_pattern_first AS $key => $pattern){
        // Check if either of these two missions matches the current pattern
        if (preg_match($pattern, $mission_one['mission_token']) && !preg_match($pattern, $mission_two['mission_token'])){ return -1; }
        elseif (!preg_match($pattern, $mission_one['mission_token']) && preg_match($pattern, $mission_two['mission_token'])){ return 1; }
    }
    foreach ($temp_pattern_last AS $key => $pattern){
        // Check if either of these two missions matches the current pattern
        if (preg_match($pattern, $mission_one['mission_token']) && !preg_match($pattern, $mission_two['mission_token'])){ return 1; }
        elseif (!preg_match($pattern, $mission_one['mission_token']) && preg_match($pattern, $mission_two['mission_token'])){ return -1; }
    }
    if ($mission_one['mission_game'] > $mission_two['mission_game']){ return 1; }
    elseif ($mission_one['mission_game'] < $mission_two['mission_game']){ return -1; }
    elseif ($mission_one['mission_token'] > $mission_two['mission_token']){ return 1; }
    elseif ($mission_one['mission_token'] < $mission_two['mission_token']){ return -1; }
    elseif ($mission_one['mission_token'] > $mission_two['mission_token']){ return 1; }
    elseif ($mission_one['mission_token'] < $mission_two['mission_token']){ return -1; }
    else { return 0; }
}
uasort($mmrpg_database_missions, 'mmrpg_index_sort_missions');

// Loop through each of the mission info arrays
$mission_key = 0;
$mission_order = 0;
$temp_empty = $mmrpg_database_missions['mission'];
unset($mmrpg_database_missions['mission']);
array_unshift($mmrpg_database_missions, $temp_empty);
if (!empty($mmrpg_database_missions)){
    foreach ($mmrpg_database_missions AS $mission_token => $mission_data){

        // If this mission's image exists, assign it
        if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/missions/'.$mission_token.'/')){ $mission_data['mission_image'] = $mission_data['mission_token']; }
        else { $mission_data['mission_image'] = 'mission'; }

        // Define the insert array and start populating it with basic details
        $temp_insert_array = array();
        //$temp_insert_array['mission_id'] = isset($mission_data['mission_id']) ? $mission_data['mission_id'] : $mission_key;
        $temp_insert_array['mission_token'] = $mission_data['mission_token'];
        $temp_insert_array['mission_number'] = !empty($mission_data['mission_number']) ? $mission_data['mission_number'] : '';
        $temp_insert_array['mission_name'] = !empty($mission_data['mission_name']) ? $mission_data['mission_name'] : '';
        $temp_insert_array['mission_game'] = !empty($mission_data['mission_game']) ? $mission_data['mission_game'] : '';
        $temp_insert_array['mission_group'] = !empty($mission_data['mission_group']) ? $mission_data['mission_group'] : '';

        $temp_insert_array['mission_class'] = !empty($mission_data['mission_class']) ? $mission_data['mission_class'] : 'mission';

        $temp_insert_array['mission_image'] = !empty($mission_data['mission_image']) ? $mission_data['mission_image'] : '';
        $temp_insert_array['mission_image_size'] = !empty($mission_data['mission_image_size']) ? $mission_data['mission_image_size'] : 40;
        $temp_insert_array['mission_image_editor'] = !empty($mission_data['mission_image_editor']) ? $mission_data['mission_image_editor'] : 0;
        $temp_insert_array['mission_image_alts'] = json_encode(!empty($mission_data['mission_image_alts']) ? $mission_data['mission_image_alts'] : array());

        $temp_insert_array['mission_type'] = !empty($mission_data['mission_type']) ? $mission_data['mission_type'] : '';
        $temp_insert_array['mission_type2'] = !empty($mission_data['mission_type2']) ? $mission_data['mission_type2'] : '';

        $temp_insert_array['mission_description'] = !empty($mission_data['mission_description']) ? trim($mission_data['mission_description']) : '';
        $temp_insert_array['mission_description2'] = !empty($mission_data['mission_description2']) ? trim($mission_data['mission_description2']) : '';

        $temp_insert_array['mission_energy'] = !empty($mission_data['mission_energy']) ? $mission_data['mission_energy'] : 0;
        $temp_insert_array['mission_weapons'] = !empty($mission_data['mission_weapons']) ? $mission_data['mission_weapons'] : 0;
        $temp_insert_array['mission_attack'] = !empty($mission_data['mission_attack']) ? $mission_data['mission_attack'] : 0;
        $temp_insert_array['mission_defense'] = !empty($mission_data['mission_defense']) ? $mission_data['mission_defense'] : 0;
        $temp_insert_array['mission_speed'] = !empty($mission_data['mission_speed']) ? $mission_data['mission_speed'] : 0;

        // Define the rewardss for this mission
        $temp_insert_array['mission_robots_rewards'] = json_encode(!empty($mission_data['mission_rewards']['robots']) ? $mission_data['mission_rewards']['robots'] : array());
        $temp_insert_array['mission_abilities_rewards'] = json_encode(!empty($mission_data['mission_rewards']['abilities']) ? $mission_data['mission_rewards']['abilities'] : array());

        // Define compatibilities for this mission
        $temp_insert_array['mission_robots_compatible'] = json_encode(!empty($mission_data['mission_robots_unlockable']) ? $mission_data['mission_robots_unlockable'] : array());
        $temp_insert_array['mission_abilities_compatible'] = json_encode(!empty($mission_data['mission_abilities']) ? $mission_data['mission_abilities'] : array());

        // Define the battle quotes for this mission
        if (!empty($mission_data['mission_quotes'])){ foreach ($mission_data['mission_quotes'] AS $key => $quote){ $mission_data['mission_quotes'][$key] = html_entity_decode($quote, ENT_QUOTES, 'UTF-8'); } }
        $temp_insert_array['mission_quotes_start'] = !empty($mission_data['mission_quotes']['battle_start']) && $mission_data['mission_quotes']['battle_start'] != '...' ? $mission_data['mission_quotes']['battle_start'] : '';
        $temp_insert_array['mission_quotes_taunt'] = !empty($mission_data['mission_quotes']['battle_taunt']) && $mission_data['mission_quotes']['battle_taunt'] != '...' ? $mission_data['mission_quotes']['battle_taunt'] : '';
        $temp_insert_array['mission_quotes_victory'] = !empty($mission_data['mission_quotes']['battle_victory']) && $mission_data['mission_quotes']['battle_victory'] != '...' ? $mission_data['mission_quotes']['battle_victory'] : '';
        $temp_insert_array['mission_quotes_defeat'] = !empty($mission_data['mission_quotes']['battle_defeat']) && $mission_data['mission_quotes']['battle_defeat'] != '...' ? $mission_data['mission_quotes']['battle_defeat'] : '';


        $temp_insert_array['mission_functions'] = !empty($mission_data['mission_functions']) ? $mission_data['mission_functions'] : 'missions/mission.php';

        // Collect applicable spreadsheets for this mission
        $spreadsheet_stats = !empty($spreadsheet_mission_stats[$mission_data['mission_token']]) ? $spreadsheet_mission_stats[$mission_data['mission_token']] : array();
        $spreadsheet_descriptions = !empty($spreadsheet_mission_descriptions[$mission_data['mission_token']]) ? $spreadsheet_mission_descriptions[$mission_data['mission_token']] : array();

        // Collect any user-contributed data for this mission
        if (!empty($spreadsheet_descriptions['mission_description'])){ $temp_insert_array['mission_description2'] = trim($spreadsheet_descriptions['mission_description']); }

        // Define the flags
        $temp_insert_array['mission_flag_hidden'] = in_array($temp_insert_array['mission_token'], array('mission')) ? 1 : 0;
        $temp_insert_array['mission_flag_complete'] = $mission_data['mission_image'] != 'mission' ? 1 : 0;
        $temp_insert_array['mission_flag_published'] = 1;

        // Define the order counter
        if ($temp_insert_array['mission_class'] != 'system'){
            $temp_insert_array['mission_order'] = $mission_order;
            $mission_order++;
        } else {
            $temp_insert_array['mission_order'] = 0;
        }


        // Check if this mission already exists in the database
        $temp_success = true;
        $temp_exists = $db->get_array("SELECT mission_token FROM mmrpg_index_missions WHERE mission_token LIKE '{$temp_insert_array['mission_token']}' LIMIT 1") ? true : false;
        if (!$temp_exists){ $temp_success = $db->insert('mmrpg_index_missions', $temp_insert_array); }
        else { $temp_success = $db->update('mmrpg_index_missions', $temp_insert_array, array('mission_token' => $temp_insert_array['mission_token'])); }

        // Print out the generated insert array
        $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
        $this_page_markup .= '<strong>$mmrpg_database_missions['.$mission_token.']</strong><br />';
        //$this_page_markup .= '<pre>'.print_r($mission_data, true).'</pre><br /><hr /><br />';
        $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
        //$this_page_markup .= '<pre>'.print_r(rpg_mission::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
        $this_page_markup .= '</p><hr />';

        $mission_key++;

        //die('end');

    }
}
// Otherwise, if empty, we're done!
else {
    $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ROBOT HAVE BEEN IMPORTED UPDATED!</strong></p>';
}

?>