<?
// TORCH MAN
$robot = array(
    'robot_number' => 'DWN-086',
    'robot_game' => 'MM11',
    'robot_name' => 'Torch Man',
    'robot_token' => 'torch-man',
    'robot_image_editor' => 0,
    'robot_core' => 'flame',
    'robot_field' => 'steel-mill', // echo-field
    'robot_weaknesses' => array('freeze'),
    'robot_resistances' => array(),
    'robot_abilities' => array(
        'blazing-torch',
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
                array('level' => 0, 'token' => 'blazing-torch')
            )
        )
    );
?>