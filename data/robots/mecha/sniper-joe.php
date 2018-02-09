<?
// SNIPER JOE
$robot = array(
    'robot_number' => 'SJOE-001', // ROBOT : SNIPER JOE (1st Gen)
    'robot_game' => 'MM01',
    'robot_group' => 'MMRPG',
    'robot_name' => 'Sniper Joe',
    'robot_token' => 'sniper-joe',
    'robot_description' => 'Shield Sniper Mecha',
    'robot_image_editor' => 412,
    'robot_image_alts' => array(
        array('token' => 'alt', 'name' => 'Sniper Joe (No Shield)', 'summons' => 0),
        ),
    'robot_class' => 'mecha',
    'robot_core' => 'shield',
    'robot_field' => 'intro-field',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('explode', 'cutter'),
    'robot_resistances' => array('water', 'flame', 'electric', 'nature'),
    'robot_abilities' => array(
        'buster-shot', 'buster-charge',
        'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
        'attack-break', 'defense-break', 'speed-break', 'energy-break',
        'attack-mode', 'defense-mode', 'speed-mode', 'energy-mode',
        'light-buster', 'wily-buster', 'cossack-buster'
        ),
    'robot_rewards' => array(
        'abilities' => array(
                array('level' => 0, 'token' => 'joe-shot'),
                array('level' => 0, 'token' => 'joe-shield'),
            )
        ),
    'robot_quotes' => array(
        'battle_start' => '',
        'battle_taunt' => '',
        'battle_victory' => '',
        'battle_defeat' => ''
        )
    );
?>