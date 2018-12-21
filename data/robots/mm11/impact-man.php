<?
// IMPACT MAN
$robot = array(
    'robot_number' => 'DWN-087',
    'robot_game' => 'MM11',
    'robot_name' => 'Impact Man',
    'robot_token' => 'impact-man',
    'robot_image_editor' => 0,
    'robot_core' => 'impact',
    'robot_field' => 'mountain-mines', // echo-field
    'robot_weaknesses' => array('water'),
    'robot_resistances' => array(),
    'robot_abilities' => array(
        'pile-driver',
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
                array('level' => 0, 'token' => 'pile-driver')
            )
        )
    );
?>