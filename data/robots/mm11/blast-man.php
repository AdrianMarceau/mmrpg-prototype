<?
// BLAST MAN
$robot = array(
    'robot_number' => 'DWN-083',
    'robot_game' => 'MM11',
    'robot_name' => 'Blast Man',
    'robot_token' => 'blast-man',
    'robot_image_editor' => 0,
    'robot_core' => 'explode',
    'robot_field' => 'pipe-station', // echo-field
    'robot_weaknesses' => array('flame'),
    'robot_resistances' => array(),
    'robot_abilities' => array(
        'chain-blast',
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
                array('level' => 0, 'token' => 'chain-blast')
            )
        )
    );
?>