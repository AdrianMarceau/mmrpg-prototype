<?
// TENGU MAN
$robot = array(
    'robot_number' => 'DWN-057',
    'robot_game' => 'MM08',
    'robot_name' => 'Tengu Man',
    'robot_token' => 'tengu-man',
    'robot_image_editor' => 3842,
    'robot_image_size' => 80,
    'robot_core' => 'wind',
    'robot_description' => 'Jet-Powered Flight Robot',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('freeze', 'missile'),
    'robot_immunities' => array('earth'),
    'robot_abilities' => array(
        'tornado-hold', 'tengu-blade',
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
                array('level' => 0, 'token' => 'tengu-blade'),
                array('level' => 10, 'token' => 'tornado-hold')
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