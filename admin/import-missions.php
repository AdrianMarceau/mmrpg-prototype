<?php

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while importing!');  }

// Collect any extra request variables for the import
$this_import_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;
$this_truncate_existing = isset($_REQUEST['truncate']) && $_REQUEST['truncate'] === 'false' ? false : true;

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=import-missions&limit=<?= $this_import_limit?>">Update Mission Database</a> &raquo;
</div>
<?php
$this_page_markup .= ob_get_clean();



// ---------------------------------------- //
// MISSION VARIABLES / FUNCTIONS
// ---------------------------------------- //

// Define a variable to hold all database missions
$mmrpg_database_missions = array();

// Define a quick insert function for new missions
function mmrpg_insert_mission($this_mission){
    global $mmrpg_database_missions;

    // Define defaults for required mission keys
    if (!isset($this_mission['phase'])){ $this_mission['phase'] = 0; }
    if (!isset($this_mission['chapter'])){ $this_mission['chapter'] = 0; }
    if (!isset($this_mission['group'])){ $this_mission['group'] = ''; }
    if (!isset($this_mission['field'])){ $this_mission['field'] = ''; }
    if (!isset($this_mission['player'])){ $this_mission['player'] = ''; }

    // Generate a unique token for this missing using keys
    $token = array();
    //if (!empty($this_mission['phase'])){ $token[] = 'p'.$this_mission['phase']; }
    if (!empty($this_mission['chapter'])){ $token[] = 'c'.$this_mission['chapter']; }
    if (!empty($this_mission['group'])){ $token[] = $this_mission['group']; }
    if (!empty($this_mission['field']) && $this_mission['field'] != 'field'){ $token[] = $this_mission['field']; }
    if (!empty($this_mission['player']) && $this_mission['player'] != 'player'){ $token[] = $this_mission['player']; }
    $token = implode('_', $token);
    $this_mission['token'] = $token;

    // Define defaults for all other mission fields
    if (!isset($this_mission['field_type'])){ $this_mission['field_type'] = ''; }
    if (!isset($this_mission['field_type2'])){ $this_mission['field_type2'] = ''; }
    if (!isset($this_mission['field_music'])){ $this_mission['field_music'] = ''; }
    if (!isset($this_mission['field_background'])){ $this_mission['field_background'] = ''; }
    if (!isset($this_mission['field_foreground'])){ $this_mission['field_foreground'] = ''; }
    if (!isset($this_mission['target_player'])){ $this_mission['target_player'] = ''; }
    if (!isset($this_mission['target_robots'])){ $this_mission['target_robots'] = array(); }
    if (!isset($this_mission['target_mooks'])){ $this_mission['target_mooks'] = array(); }
    if (!isset($this_mission['level_start'])){ $this_mission['level_start'] = 0; }
    if (!isset($this_mission['level_limit'])){ $this_mission['level_limit'] = 0; }
    if (!isset($this_mission['button_size'])){ $this_mission['button_size'] = '1x1'; }
    if (!isset($this_mission['button_order'])){ $this_mission['button_order'] = 0; }

    // Implode any array-based fields into strings
    foreach ($this_mission AS $field => $value){
        if (is_array($value)){
            $value = implode(',', $value);
            $this_mission[$field] = $value;
        }
    }

    // Compensate for duplicated field type values
    if ($this_mission['field_type2'] == $this_mission['field_type']){
        $this_mission['field_type2'] = '';
    }

    // Compensate for mission level limit variable
    if (empty($this_mission['level_limit'])){
        $this_mission['level_limit'] = $this_mission['level_start'];
    }

    // Append the "mission" prefix to all fields then insert
    $this_backup = $this_mission;
    $this_mission = array();
    foreach ($this_backup AS $f => $v){ $this_mission['mission_'.$f] = $v; }
    $mmrpg_database_missions[] = $this_mission;

}



// ------------------------- //
// MISSION SEEDS / TOKENS
// ------------------------- //

// Define the OMEGA FACTORS we'll be generating missions with
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

// Define the CACHE FACTORS we'll be generating missions with
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
$mmrpg_player_target_mooks_tokens = array('roll', 'disco', 'rhythm');

// Define the RIVAL-PLAYER+FIELD tokens we'll be generating missions for
$mmrpg_rival_tokens = array('dr-wily', 'dr-cossack', 'dr-light');

// Define the MECHA-JOE tokens that we'll be using in our missions
$mmrpg_mecha_joe_tokens = array('sniper-joe', 'skeleton-joe', 'crystal-joe');

// Define the KILLER-ROBOT tokens that we'll be using in our missions
$mmrpg_killer_robot_tokens = array('enker', 'punk', 'ballade');

// Define the DARKNESS-ROBOT tokens that we'll be using in our missions
$mmrpg_darkness_robot_tokens = array('dark-man', 'dark-man-2', 'dark-man-3');

// Define the GENESIS-ROBOT tokens that we'll be using in our missions
$mmrpg_genesis_robot_tokens = array('buster-rod-g', 'mega-water-s', 'hyper-storm-h');

// Define the STARDROID-ROBOT tokens that we'll be using in our missions
$mmrpg_stardroid_robot_tokens = array('terra', 'mercury', 'venus', 'mars', 'jupiter', 'saturn', 'uranus', 'pluto', 'neptune');

// Define the MISSION-LEVEL counters that the entire game will use for missions
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

// Define the MISSION-GROUP-COUNTERS that the entire game will use for missions
$mmrpg_mission_group_counters = array(
    1 => array(3),              // Chapter One   (Intro Battles)
    2 => array(8, 1),           // Chapter Two   (Master Battles)
    3 => array(3),              // Chapter Three (Rival Battles)
    4 => array(4, 1),           // Chapter Four  (Fusion Battles)
    5 => array(3),              // Chapter Five  (Darkness Battles)
    6 => array(3),              // Chapter Six   (Stardroid Battles)
    7 => array(3),              // Chapter Seven (Final Battles)
    8 => array(8, 1)            // Chapter Eight (Cache Battles)
    );

// Define the MISSION-BUTTON-SIZE counters that the entire game will use for missions
$mmrpg_mission_button_sizes = array(
    1 => array(1, 1, 1),       // Chapter One   (Intro Battles)
    2 => array(4, 4, 1),       // Chapter Two   (Master Battles)
    3 => array(1, 1, 1),       // Chapter Three (Rival Battles)
    4 => array(2, 2, 1),       // Chapter Four  (Fusion Battles)
    5 => array(1, 1, 1),       // Chapter Five  (Darkness Battles)
    6 => array(1, 1, 1),       // Chapter Six   (Stardroid Battles)
    7 => array(1, 1, 1),       // Chapter Seven (Final Battles)
    8 => array(4, 4, 1)        // Chapter Eight (Cache Battles)
    );



// ---------------------------------------- //
// MISSION GENERATION : INIT
// ---------------------------------------- //

// Define the global PHASE and CHAPTER variables at zero
$this_mission_phase = 0;
$this_mission_chapter = 0;



// ---------------------------------------- //
// MISSION GENERATION : PHASE ONE
// ---------------------------------------- //

// PHASE 1 START
$this_mission_phase++;

// CHAPTER ONE / Intro Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
$this_mission_group_counters = $mmrpg_mission_group_counters[$this_mission_chapter];
$this_mission_button_sizes = $mmrpg_mission_button_sizes[$this_mission_chapter];
foreach ($mmrpg_player_tokens AS $player_key => $player_token){
    $button_order = 0;

    $rival_token = $mmrpg_rival_tokens[$player_key];
    $rival_key = array_search($rival_token, $mmrpg_player_tokens);
    $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

    // vs MET
    $button_order++;
    $group_token = 'intro-battle';
    $field_token = 'intro-field';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array('met');
    $target_mooks = array();
    $level_start = $this_mission_levels[0];
    $button_size = '1x'.$this_mission_button_sizes[0];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs MECHA JOE
    $button_order++;
    $group_token = 'intro-battle';
    $field_token = $mmrpg_player_field_tokens[$player_key];
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array($mmrpg_mecha_joe_tokens[$player_key]);
    $target_mooks = array();
    $level_start = $this_mission_levels[1];
    $button_size = '1x'.$this_mission_button_sizes[1];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs TRILL
    $button_order++;
    $group_token = 'intro-battle';
    $field_token = 'prototype-subspace';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array('trill_'.$mmrpg_player_stat_tokens[$rival_key]);
    $target_mooks = array();
    $level_start = $this_mission_levels[2];
    $button_size = '1x'.$this_mission_button_sizes[2];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

}



// ---------------------------------------- //
// MISSION GENERATION : PHASE TWO
// ---------------------------------------- //

// PHASE 2 START
$this_mission_phase++;

// CHAPTER TWO / Master Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
$this_mission_group_counters = $mmrpg_mission_group_counters[$this_mission_chapter];
$this_mission_button_sizes = $mmrpg_mission_button_sizes[$this_mission_chapter];
if (true){
    $button_order = 0;

    // vs MASTERS
    $button_order++;
    $level_start = $this_mission_levels[0];
    $level_limit = $level_start + (($this_mission_group_counters[0] - 1) * $this_mission_levels[1]);
    $button_size = '1x'.$this_mission_button_sizes[0];
    foreach ($mmrpg_omega_factors AS $omega_key => $omega_factor){
        $omega_factor['omega_mechas'] = !empty($omega_factor['omega_mechas']) ? json_decode($omega_factor['omega_mechas'], true) : array();

        $group_token = 'master-battle';
        $field_token = $omega_factor['omega_field'];
        $field_type = $omega_factor['omega_type'];
        $field_type2 = '';
        $field_music = $field_token;
        $field_background = $field_token;
        $field_foreground = $field_token;
        $player_token = '';
        $target_player = '';
        $target_robots = array($omega_factor['omega_robot']);
        $target_mooks = array_values($omega_factor['omega_mechas']);
        mmrpg_insert_mission(array(
            'phase' => $this_mission_phase,
            'chapter' => $this_mission_chapter,
            'group' => $group_token,
            'field' => $field_token,
            'player' => $player_token,
            'field_type' => $field_type,
            'field_type2' => $field_type2,
            'field_music' => $field_music,
            'field_background' => $field_background,
            'field_foreground' => $field_foreground,
            'target_player' => $target_player,
            'target_robots' => $target_robots,
            'target_mooks' => $target_mooks,
            'level_start' => $level_start,
            'level_limit' => $level_limit,
            'button_size' => $button_size,
            'button_order' => $button_order
            ));

    }

    // vs DOC-ROBOT
    $button_order++;
    foreach ($mmrpg_player_tokens AS $player_key => $player_token){

        $rival_token = $mmrpg_rival_tokens[$player_key];
        $rival_key = array_search($rival_token, $mmrpg_player_tokens);
        $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

        $group_token = 'fortress-battle';
        $field_token = 'xxx-field';
        $field_type = '';
        $field_type2 = '';
        $field_music = $field_token;
        $field_background = $field_token;
        $field_foreground = $field_token;
        $target_player = '';
        $target_robots = array('doc-robot');
        $target_mooks = array();
        $level_start = $this_mission_levels[2];
        $button_size = '1x'.$this_mission_button_sizes[2];
        mmrpg_insert_mission(array(
            'phase' => $this_mission_phase,
            'chapter' => $this_mission_chapter,
            'group' => $group_token,
            'field' => $field_token,
            'player' => $player_token,
            'field_type' => $field_type,
            'field_type2' => $field_type2,
            'field_music' => $field_music,
            'field_background' => $field_background,
            'field_foreground' => $field_foreground,
            'target_player' => $target_player,
            'target_robots' => $target_robots,
            'target_mooks' => $target_mooks,
            'level_start' => $level_start,
            'button_size' => $button_size,
            'button_order' => $button_order
            ));

    }

}

// CHAPTER THREE / Rival Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
$this_mission_group_counters = $mmrpg_mission_group_counters[$this_mission_chapter];
$this_mission_button_sizes = $mmrpg_mission_button_sizes[$this_mission_chapter];
foreach ($mmrpg_player_tokens AS $player_key => $player_token){
    $button_order = 0;

    $rival_token = $mmrpg_rival_tokens[$player_key];
    $rival_key = array_search($rival_token, $mmrpg_player_tokens);
    $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

    // vs RIVALS
    $button_order++;
    $group_token = 'rival-battle';
    $field_token = $mmrpg_player_field_tokens[$rival_key];
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = $rival_token;
    $target_robots = array($mmrpg_player_robot_master_tokens[$rival_key], $mmrpg_player_target_mooks_tokens[$rival_key]);
    $target_mooks = array();
    $level_start = $this_mission_levels[0];
    $button_size = '1x'.$this_mission_button_sizes[0];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs KILLERS
    $button_order++;
    $group_token = 'killer-battle';
    $field_token = 'xxx-field';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array($mmrpg_killer_robot_tokens[$player_key], 'quint');
    $target_mooks = array();
    $level_start = $this_mission_levels[1];
    $button_size = '1x'.$this_mission_button_sizes[1];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs ALIENS
    $button_order++;
    $group_token = 'alien-battle';
    $field_token = 'xxx-field';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array('sunstar','trill_'.$mmrpg_player_stat_tokens[$final_key]);
    $target_mooks = array();
    $level_start = $this_mission_levels[2];
    $button_size = '1x'.$this_mission_button_sizes[2];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

}



// ---------------------------------------- //
// MISSION GENERATION : PHASE THREE
// ---------------------------------------- //

// PHASE 3 START
$this_mission_phase++;

// CHAPTER FOUR / Fusion Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
$this_mission_group_counters = $mmrpg_mission_group_counters[$this_mission_chapter];
$this_mission_button_sizes = $mmrpg_mission_button_sizes[$this_mission_chapter];
if (true){
    $button_order = 0;

    // vs MASTERS
    $button_order++;
    $level_start = $this_mission_levels[0];
    $level_limit = $level_start + (($this_mission_group_counters[0] - 1) * $this_mission_levels[1]);
    $button_size = '1x'.$this_mission_button_sizes[0];
    foreach ($mmrpg_omega_factors AS $omega_key1 => $omega_factor1){
        foreach ($mmrpg_omega_factors AS $omega_key2 => $omega_factor2){

            if ($omega_key1 === $omega_key2){ continue; }

            $group_token = 'fusion-battle';
            $player_token = '';
            $field1 = $omega_factor1['omega_field'];
            $field2 = $omega_factor2['omega_field'];
            $mechas1 = !empty($omega_factor1['omega_mechas']) ? json_decode($omega_factor1['omega_mechas'], true) : array();
            $mechas2 = !empty($omega_factor2['omega_mechas']) ? json_decode($omega_factor2['omega_mechas'], true) : array();
            $field_left = preg_replace('/^([a-z0-9]+)-([a-z0-9]+)$/i', '$1', $omega_factor1['omega_field']);
            $field_right = preg_replace('/^([a-z0-9]+)-([a-z0-9]+)$/i', '$2', $omega_factor2['omega_field']);
            $field_token = $field_left.'-'.$field_right;
            $field_type = $omega_factor1['omega_type'];
            $field_type2 = $omega_factor2['omega_type'];
            $field_music = $omega_factor2['omega_field'];
            $field_background = $omega_factor1['omega_field'];
            $field_foreground = $omega_factor2['omega_field'];
            $target_player = '';
            $target_robots = array($omega_factor1['omega_robot'], $omega_factor2['omega_robot']);
            $target_mooks = array_filter(array_merge($mechas1, $mechas2));
            mmrpg_insert_mission(array(
                'phase' => $this_mission_phase,
                'chapter' => $this_mission_chapter,
                'group' => $group_token,
                'field' => $field_token,
                'player' => $player_token,
                'field_type' => $field_type,
                'field_type2' => $field_type2,
                'field_music' => $field_music,
                'field_background' => $field_background,
                'field_foreground' => $field_foreground,
                'target_player' => $target_player,
                'target_robots' => $target_robots,
                'target_mooks' => $target_mooks,
                'level_start' => $level_start,
                'level_limit' => $level_limit,
                'button_size' => $button_size,
                'button_order' => $button_order
                ));

            //break;

        }

        //break;

    }

    // vs KING + DOC-ROBOT
    $button_order++;
    foreach ($mmrpg_player_tokens AS $player_key => $player_token){

        $rival_token = $mmrpg_rival_tokens[$player_key];
        $rival_key = array_search($rival_token, $mmrpg_player_tokens);
        $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

        $group_token = 'fortress-battle';
        $field_token = 'xxx-field';
        $field_type = '';
        $field_type2 = '';
        $field_music = $field_token;
        $field_background = $field_token;
        $field_foreground = $field_token;
        $target_player = '';
        $target_robots = array('king', 'doc-robot');
        $target_mooks = array();
        $level_start = $this_mission_levels[2];
        $button_size = '1x'.$this_mission_button_sizes[2];
        mmrpg_insert_mission(array(
            'phase' => $this_mission_phase,
            'chapter' => $this_mission_chapter,
            'group' => $group_token,
            'field' => $field_token,
            'player' => $player_token,
            'field_type' => $field_type,
            'field_type2' => $field_type2,
            'field_music' => $field_music,
            'field_background' => $field_background,
            'field_foreground' => $field_foreground,
            'target_player' => $target_player,
            'target_robots' => $target_robots,
            'target_mooks' => $target_mooks,
            'level_start' => $level_start,
            'button_size' => $button_size,
            'button_order' => $button_order
            ));

    }

}

// CHAPTER FIVE / Darkness Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
$this_mission_group_counters = $mmrpg_mission_group_counters[$this_mission_chapter];
$this_mission_button_sizes = $mmrpg_mission_button_sizes[$this_mission_chapter];
foreach ($mmrpg_player_tokens AS $player_key => $player_token){
    $button_order = 0;

    $rival_token = $mmrpg_rival_tokens[$player_key];
    $rival_key = array_search($rival_token, $mmrpg_player_tokens);
    $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

    // vs DARKNESS
    $button_order++;
    $group_token = 'darkness-battle';
    $field_token = 'xxx-field';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = $rival_token;
    $target_robots = array($mmrpg_player_robot_master_tokens[$player_key].'-ds', $mmrpg_darkness_robot_tokens[$player_key]);
    $target_mooks = array();
    $level_start = $this_mission_levels[0];
    $button_size = '1x'.$this_mission_button_sizes[0];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs GENESIS
    $button_order++;
    $group_token = 'genesis-battle';
    $field_token = 'xxx-field';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array($mmrpg_genesis_robot_tokens[$player_key], $mmrpg_genesis_robot_tokens[$rival_key], $mmrpg_genesis_robot_tokens[$final_key]);
    $target_mooks = array();
    $level_start = $this_mission_levels[1];
    $button_size = '1x'.$this_mission_button_sizes[1];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs ALIENS
    $button_order++;
    $group_token = 'alien-battle';
    $field_token = 'xxx-field';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array('slur', 'sunstar', 'trill_'.$mmrpg_player_stat_tokens[$player_key], 'trill_'.$mmrpg_player_stat_tokens[$player_key]);
    $target_mooks = array();
    $level_start = $this_mission_levels[2];
    $button_size = '1x'.$this_mission_button_sizes[2];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

}



// ---------------------------------------- //
// MISSION GENERATION : PHASE FOUR
// ---------------------------------------- //

// PHASE 4 START
$this_mission_phase++;

// CHAPTER SIX / Stardroid Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
$this_mission_group_counters = $mmrpg_mission_group_counters[$this_mission_chapter];
$this_mission_button_sizes = $mmrpg_mission_button_sizes[$this_mission_chapter];
foreach ($mmrpg_player_tokens AS $player_key => $player_token){
    $button_order = 0;

    $rival_token = $mmrpg_rival_tokens[$player_key];
    $rival_key = array_search($rival_token, $mmrpg_player_tokens);
    $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

    $stardroid_tokens = $mmrpg_stardroid_robot_tokens;
    for ($i = 0; $i < $player_key; $i++){ array_push($stardroid_tokens, array_shift($stardroid_tokens)); }

    // vs STARDROID
    $button_order++;
    $group_token = 'stardroid-battle';
    $field_token = 'star-field';
    $field_type = 'space';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array($stardroid_tokens[0], $stardroid_tokens[1]);
    $target_mooks = array();
    $level_start = $this_mission_levels[0];
    $button_size = '1x'.$this_mission_button_sizes[0];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs STARDROID-2
    $button_order++;
    $group_token = 'stardroid-battle';
    $field_token = 'star-field-ii';
    $field_type = 'space';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array($stardroid_tokens[2], $stardroid_tokens[3], $stardroid_tokens[4]);
    $target_mooks = array();
    $level_start = $this_mission_levels[1];
    $button_size = '1x'.$this_mission_button_sizes[1];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs STARDROID-3
    $button_order++;
    $group_token = 'stardroid-battle';
    $field_token = 'star-field-iii';
    $field_type = 'space';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array($stardroid_tokens[5], $stardroid_tokens[6], $stardroid_tokens[7], $stardroid_tokens[8]);
    $target_mooks = array();
    $level_start = $this_mission_levels[2];
    $button_size = '1x'.$this_mission_button_sizes[2];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

}

// CHAPTER SEVEN / Final Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
$this_mission_group_counters = $mmrpg_mission_group_counters[$this_mission_chapter];
$this_mission_button_sizes = $mmrpg_mission_button_sizes[$this_mission_chapter];
foreach ($mmrpg_player_tokens AS $player_key => $player_token){
    $button_order = 0;

    $rival_token = $mmrpg_rival_tokens[$player_key];
    $rival_key = array_search($rival_token, $mmrpg_player_tokens);
    $final_key = isset($mmrpg_player_tokens[$rival_key + 1]) ? $rival_key + 1 : 0;

    // vs DARK-KILLERS
    $button_order++;
    $group_token = 'final-battle';
    $field_token = 'final-destination';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array('dark-man-4', $mmrpg_killer_robot_tokens[$player_key].'-sp');
    $target_mooks = array();
    $level_start = $this_mission_levels[0];
    $button_size = '1x'.$this_mission_button_sizes[0];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs OMEGA-SLUR
    $button_order++;
    $group_token = 'final-battle';
    $field_token = 'final-destination-ii';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array('omega-slur');
    $target_mooks = array('planet-man_water', 'planet-man_flame', 'planet-man_electric');
    $level_start = $this_mission_levels[1];
    $button_size = '1x'.$this_mission_button_sizes[1];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

    // vs COSMO-WEAPON
    $button_order++;
    $group_token = 'final-battle';
    $field_token = 'final-destination-iii';
    $field_type = '';
    $field_type2 = '';
    $field_music = $field_token;
    $field_background = $field_token;
    $field_foreground = $field_token;
    $target_player = '';
    $target_robots = array('cosmo-man', 'omega-weapon');
    $target_mooks = array('omega-battery_energy', 'omega-battery_attack', 'omega-battery_defense', 'omega-battery_speed');
    $level_start = $this_mission_levels[2];
    $button_size = '1x'.$this_mission_button_sizes[2];
    mmrpg_insert_mission(array(
        'phase' => $this_mission_phase,
        'chapter' => $this_mission_chapter,
        'group' => $group_token,
        'field' => $field_token,
        'player' => $player_token,
        'field_type' => $field_type,
        'field_type2' => $field_type2,
        'field_music' => $field_music,
        'field_background' => $field_background,
        'field_foreground' => $field_foreground,
        'target_player' => $target_player,
        'target_robots' => $target_robots,
        'target_mooks' => $target_mooks,
        'level_start' => $level_start,
        'button_size' => $button_size,
        'button_order' => $button_order
        ));

}


/*

// ---------------------------------------- //
// MISSION GENERATION : PHASE FIVE
// ---------------------------------------- //

// PHASE 5 START
$this_mission_phase++;

// CHAPTER EIGHT / Cache Battles
$this_mission_chapter++;
$this_mission_levels = $mmrpg_mission_levels[$this_mission_chapter];
$this_mission_group_counters = $mmrpg_mission_group_counters[$this_mission_chapter];
$this_mission_button_sizes = $mmrpg_mission_button_sizes[$this_mission_chapter];

// Cannot complete due to missing fields for Cache Numbers

*/



//$mmrpg_stardroid_robot_tokens

/*
*/

// DEBUG
//echo('<pre>$mmrpg_database_missions('.count($mmrpg_database_missions).') = '.print_r($mmrpg_database_missions, true).'</pre>');

// Generate page headers
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_missions</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_database_missions) ? count($mmrpg_database_missions) : 0).'<br />';
$this_page_markup .= '</p>';

// Truncate any robots currently in the database
if ($this_truncate_existing){
    $db->query('TRUNCATE TABLE mmrpg_index_missions');
}

// Loop through each of the mission info arrays
if (!empty($mmrpg_database_missions)){
    foreach ($mmrpg_database_missions AS $mission_key => $mission_data){

        // Generate the insert array based on this mission's data
        $mission_token = $mission_data['mission_token'];
        $temp_insert_array = $mission_data;

        // Check if this mission already exists in the database and either insert or update
        $temp_success = true;
        if (!$this_truncate_existing){ $temp_exists = $db->get_array("SELECT mission_token FROM mmrpg_index_missions WHERE mission_token LIKE '{$temp_insert_array['mission_token']}' LIMIT 1") ? true : false; }
        else { $temp_exists = false; }
        if (!$temp_exists){ $temp_success = $db->insert('mmrpg_index_missions', $temp_insert_array); }
        else { $temp_success = $db->update('mmrpg_index_missions', $temp_insert_array, array('mission_token' => $temp_insert_array['mission_token'])); }

        // Print out the generated insert array
        $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
        $this_page_markup .= '<strong>$mmrpg_database_missions['.$mission_token.']</strong><br />';
        $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
        $this_page_markup .= '</p><hr />';

    }
}
// Otherwise, if empty, we're done!
else {
    $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL MISSIONS HAVE BEEN GENERATED!</strong></p>';
}

?>