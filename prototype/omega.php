<?

/*
 * DEFINE OMEGA FACTORS
 */

// Define the base omega factors for ITEMS
$this_omega_factors_items = array('energy-pellet','energy-capsule','weapon-pellet','weapon-capsule', 'energy-tank', 'weapon-tank', 'yashichi', 'extra-life');

// Define the base omega factors for MEGA MAN 0 (BASE)
$this_omega_factors_system = array();
$this_omega_factors_system[] = array('robot' => 'mega-man', 'field' => 'light-laboratory', 'type' => '');
$this_omega_factors_system[] = array('robot' => 'bass', 'field' => 'wily-castle', 'type' => '');
$this_omega_factors_system[] = array('robot' => 'proto-man', 'field' => 'cossack-citadel', 'type' => '');

// Define an index of games keys vs the omega factor vars they represent
$omega_game_index = array(
    'MM01' => 'this_omega_factors_one',
    'MM02' => 'this_omega_factors_two',
    'MM04' => 'this_omega_factors_three',
    'MM03' => 'this_omega_factors_four',
    'MM05' => 'this_omega_factors_five',
    'MM06' => 'this_omega_factors_six',
    'MM07' => 'this_omega_factors_seven',
    'MM08' => 'this_omega_factors_eight',
    'MM085' => 'this_omega_factors_eight_two',
    'MM09' => 'this_omega_factors_nine',
    'MM10' => 'this_omega_factors_ten',
    'MM11' => 'this_omega_factors_eleven'
    );

// Collect a list of all "omega" field robots (basically everyone from MM01-MM11) and format them
if (!isset($db)){ global $db; }
$raw_game_string = "'".implode("', '", array_keys($omega_game_index))."'";
$raw_omega_factors = $db->get_array_list("SELECT
    robots.robot_token AS `robot`,
    robots.robot_field AS `field`,
    robots.robot_core AS `type`,
    robots.robot_game AS `game`
    FROM
    mmrpg_index_robots AS robots
    WHERE
    robots.robot_game IN ({$raw_game_string})
    AND robots.robot_class = 'master'
    AND robots.robot_flag_complete = 1
    ORDER BY
    robots.robot_game ASC,
    robots.robot_order ASC
    ;");
if (!empty($raw_omega_factors)){
    foreach ($raw_omega_factors AS $key => $omega){
        $factor_varname = $omega_game_index[$omega['game']];
        if (!isset($$factor_varname)){ $$factor_varname = array(); }
        array_push($$factor_varname, array('robot' => $omega['robot'], 'field' => $omega['field'], 'type' => $omega['type']));
    }
}

?>