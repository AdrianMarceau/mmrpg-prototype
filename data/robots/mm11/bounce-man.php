<?
// BOUNCE MAN
$robot = array(
    'robot_number' => 'DWN-088',
    'robot_game' => 'MM11',
    'robot_name' => 'Bounce Man',
    'robot_token' => 'bounce-man',
    'robot_image_editor' => 0,
    'robot_core' => 'swift',
    'robot_field' => 'spinning-greenhouse', // echo-field
    'robot_weaknesses' => array('impact'),
    'robot_resistances' => array(),
    'robot_abilities' => array(
        'bounce-ball',
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
                array('level' => 0, 'token' => 'bounce-ball')
            )
        )
    );
?>