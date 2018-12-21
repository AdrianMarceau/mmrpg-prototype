<?
// TUNDRA MAN
$robot = array(
    'robot_number' => 'DWN-085',
    'robot_game' => 'MM11',
    'robot_name' => 'Tundra Man',
    'robot_token' => 'tundra-man',
    'robot_image_editor' => 0,
    'robot_core' => 'freeze',
    'robot_field' => 'arctic-jungle', // echo-field
    'robot_weaknesses' => array('electric'),
    'robot_resistances' => array(),
    'robot_abilities' => array(
        'tundra-storm',
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
                array('level' => 0, 'token' => 'tundra-storm')
            )
        )
    );
?>