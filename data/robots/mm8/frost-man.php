<?
// FROST MAN
$robot = array(
    'robot_number' => 'DWN-062',
    'robot_game' => 'MM08',
    'robot_name' => 'Frost Man',
    'robot_token' => 'frost-man',
    'robot_image_editor' => 7469,
    'robot_image_size' => 80,
    'robot_core' => 'freeze',
    'robot_description' => 'Frozen Brute Robot',
    'robot_field' => 'arctic-jungle', // echo-field
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('explode', 'flame'),
    'robot_resistances' => array('freeze', 'water'),
    'robot_abilities' => array(
        'frost-wave',
        'buster-shot',
        'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
        'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
        'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
        'energy-boost', 'energy-break', 'energy-swap', 'energy-mode',
        'field-support', 'mecha-support',
        'light-buster', 'wily-buster', 'cossack-buster'
        ),
    'robot_rewards' => array(
        'abilities' => array(
                array('level' => 0, 'token' => 'frost-wave')
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