<?

/*
 * DEFINE OMEGA FACTORS
 */

// Define the base omega factors for MEGA MAN 0 (BASE)
$this_omega_factors_system = array();
$this_omega_factors_system[] = array('robot' => 'mega-man', 'field' => 'light-laboratory', 'type' => '');
$this_omega_factors_system[] = array('robot' => 'bass', 'field' => 'wily-castle', 'type' => '');
$this_omega_factors_system[] = array('robot' => 'proto-man', 'field' => 'cossack-citadel', 'type' => '');

// Define an index of games keys vs the omega factor vars they represent
$omega_game_index = array(
    'MM1' => 'this_omega_factors_one',
    'MM2' => 'this_omega_factors_two',
    'MM4' => 'this_omega_factors_three',
    'MM3' => 'this_omega_factors_four',
    'MM5' => 'this_omega_factors_five',
    'MM6' => 'this_omega_factors_six',
    'MM7' => 'this_omega_factors_seven',
    'MM8' => 'this_omega_factors_eight',
    'RnF' => 'this_omega_factors_eight_two',
    'MM9' => 'this_omega_factors_nine',
    'MM10' => 'this_omega_factors_ten',
    'MM11' => 'this_omega_factors_eleven'
    );

// Collect a list of all "omega" field robots (basically everyone from MM01-MM11) and format them
if (!isset($db)){ global $db; }
$raw_game_string = "'".implode("', '", array_keys($omega_game_index))."'";
$raw_game_string .= ", 'MMPU'";
$raw_omega_factors = $db->get_array_list("SELECT
    robots.robot_token AS `robot`,
    robots.robot_field AS `field`,
    robots.robot_core AS `type`,
    (CASE WHEN robots.robot_game = 'MMPU' THEN 'MM1' ELSE robots.robot_game END) AS `game`
    FROM
    mmrpg_index_robots AS robots
    LEFT JOIN mmrpg_index_players AS players1 ON players1.player_robot_hero = robots.robot_token
    LEFT JOIN mmrpg_index_players AS players2 ON players2.player_robot_support = robots.robot_token
    WHERE
    robots.robot_game IN ({$raw_game_string})
    AND robots.robot_class = 'master'
    AND robots.robot_flag_complete = 1
    AND players1.player_robot_hero IS NULL
    AND players2.player_robot_support IS NULL
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