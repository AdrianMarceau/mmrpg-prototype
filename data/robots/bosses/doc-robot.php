<?
// DOC ROBOT
$robot = array(
    'robot_number' => 'SWK-176',
    'robot_game' => 'MMRPG',
    'robot_name' => 'Doc Robot',
    'robot_token' => 'doc-robot',
    'robot_image_editor' => 3842,
    'robot_image_size' => 80,
    'robot_class' => 'boss',
    'robot_image_editor' => 412,
    'robot_core' => 'copy',
    'robot_description' => 'All-Purpose Battle Robot',
    'robot_field' => 'final-destination-3',
    'robot_energy' => 100,
    'robot_weapons' => 32,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array(),
    'robot_resistances' => array(),
    'robot_affinities' => array(),
    'robot_abilities' => array(
        'buster-shot', 'buster-charge',
        'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
        'attack-break', 'defense-break', 'speed-break', 'energy-break',
        'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
        'attack-support', 'defense-support', 'speed-support', 'energy-support',
        'attack-assault', 'defense-assault', 'speed-assault', 'energy-assault',
        'attack-shuffle', 'defense-shuffle', 'speed-shuffle', 'energy-shuffle',
        'attack-mode', 'defense-mode', 'speed-mode', 'repair-mode',
        'experience-booster', 'recovery-booster', 'damage-booster',
        'experience-breaker', 'recovery-breaker', 'damage-breaker',
        'field-support', 'mecha-support',
        ),
    'robot_rewards' => array(
        'abilities' => array(
            array('level' => 0, 'token' => 'copy-shot')
            )
        ),
    'robot_quotes' => array(
        'battle_start' => '',
        'battle_taunt' => '',
        'battle_victory' => '',
        'battle_defeat' => ''
        ),
    'robot_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If Doc Robot used an ability the previous turn, change weaknesses
        if (!empty($this_robot->history['triggered_abilities'])){
            global $db;
            $triggered_abilities = $this_robot->history['triggered_abilities'];
            while (!empty($triggered_abilities)){
                $last_ability = array_pop($triggered_abilities);
                $last_ability_master = $db->get_array("SELECT robot_token, robot_weaknesses, robot_resistances, robot_affinities, robot_immunities FROM mmrpg_index_robots WHERE robot_abilities_rewards LIKE '%\"{$last_ability}\"%' AND robot_id <> 0 LIMIT 1;");
                if (!empty($last_ability_master)){
                    $this_robot->set_weaknesses(!empty($last_ability_master['robot_weaknesses']) ? json_decode($last_ability_master['robot_weaknesses'], true) : array());
                    $this_robot->set_resistances(!empty($last_ability_master['robot_resistances']) ? json_decode($last_ability_master['robot_resistances'], true) : array());
                    $this_robot->set_affinities(!empty($last_ability_master['robot_affinities']) ? json_decode($last_ability_master['robot_affinities'], true) : array());
                    $this_robot->set_immunities(!empty($last_ability_master['robot_immunities']) ? json_decode($last_ability_master['robot_immunities'], true) : array());
                    break;
                }
            }
        }
    }
);
?>