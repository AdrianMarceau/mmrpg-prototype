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
    'MM11' => 'this_omega_factors_eleven',
    'MMRPG' => 'this_omega_factors_twelve'
    );

// Collect a list of all "omega" field robots (basically everyone from MM1-MM11) and format them
$cache_token = md5(implode(',', array_keys($omega_game_index)));
$cached_index = rpg_object::load_cached_index('omega', $cache_token);
if (!empty($cached_index)){
    $raw_omega_factors = $cached_index;
    unset($cached_index);
} else {
    if (!isset($db)){ global $db; }
    $raw_omega_factors = $db->get_array_list("SELECT
        fields.field_token AS `field`,
        robots.robot_token AS `robot`,
        fields.field_type AS `type`,
        (CASE WHEN robots.robot_game = 'MMPU' THEN 'MM1' ELSE robots.robot_game END) AS `game`
        FROM mmrpg_index_fields AS fields
        LEFT JOIN mmrpg_index_robots AS robots ON robots.robot_token = fields.field_master
        LEFT JOIN mmrpg_index_fields_groups_tokens AS tokens ON tokens.field_token = fields.field_token
        LEFT JOIN mmrpg_index_fields_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
        WHERE
        fields.field_type <> ''
        AND fields.field_class = 'master'
        AND fields.field_flag_published = 1
        AND fields.field_flag_complete = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;");
    rpg_object::save_cached_index('omega', $cache_token, $raw_omega_factors);
}
if (!empty($raw_omega_factors)){
    foreach ($raw_omega_factors AS $key => $omega){
        if (!empty($omega['game'])){ $factor_varname = $omega_game_index[$omega['game']]; }
        else { $factor_varname = $omega_game_index['MMRPG']; }
        if (!isset($$factor_varname)){ $$factor_varname = array(); }
        array_push($$factor_varname, array('robot' => $omega['robot'], 'field' => $omega['field'], 'type' => $omega['type']));
    }
}


?>