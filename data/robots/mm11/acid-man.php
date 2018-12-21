<?
// ACID MAN
$robot = array(
    'robot_number' => 'DWN-084',
    'robot_game' => 'MM11',
    'robot_name' => 'Acid Man',
    'robot_token' => 'acid-man',
    'robot_image_editor' => 0,
    'robot_core' => 'water',
    'robot_field' => 'waterfall-institute', // echo-field
    'robot_weaknesses' => array('earth'),
    'robot_resistances' => array(),
    'robot_abilities' => array(
        'acid-barrier',
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
                array('level' => 0, 'token' => 'acid-barrier')
            )
        )
    );
?>