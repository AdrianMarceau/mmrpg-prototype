<?
// MAGIC MAN
$robot = array(
    'robot_number' => 'KGN-006',
    'robot_game' => 'MM085',
    'robot_name' => 'Magic Man',
    'robot_token' => 'magic-man',
    'robot_image_editor' => 3842,
    'robot_image_size' => 80,
    'robot_core' => 'shadow',
    'robot_description' => 'Showy Illusionist Robot',
    'robot_field' => 'septic-system', // echo-field
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('cutter', 'flame'),
    'robot_resistances' => array('shadow', 'freeze'),
    'robot_abilities' => array(
        'magic-card',
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
                array('level' => 0, 'token' => 'magic-card')
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