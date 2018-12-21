<?
// FUSE MAN
$robot = array(
    'robot_number' => 'DWN-082',
    'robot_game' => 'MM11',
    'robot_name' => 'Fuse Man',
    'robot_token' => 'fuse-man',
    'robot_image_editor' => 0,
    'robot_core' => 'electric',
    'robot_field' => 'power-plant', // echo-field
    'robot_weaknesses' => array('swift'),
    'robot_resistances' => array(),
    'robot_abilities' => array(
        'scramble-thunder',
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
                array('level' => 0, 'token' => 'scramble-thunder')
            )
        )
    );
?>