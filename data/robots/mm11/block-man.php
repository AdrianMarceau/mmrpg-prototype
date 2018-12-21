<?
// BLOCK MAN
$robot = array(
    'robot_number' => 'DWN-081',
    'robot_game' => 'MM11',
    'robot_name' => 'Block Man',
    'robot_token' => 'block-man',
    'robot_image_editor' => 4117,
    'robot_core' => 'earth',
    'robot_field' => 'mineral-quarry', // echo-field
    'robot_weaknesses' => array('explode'),
    'robot_resistances' => array(),
    'robot_abilities' => array(
        'block-dropper',
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
                array('level' => 0, 'token' => 'block-dropper')
            )
        )
    );
?>