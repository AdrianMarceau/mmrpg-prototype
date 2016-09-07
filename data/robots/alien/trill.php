<?
// TRILL
$robot = array(
    'robot_number' => 'EXN-00X',
    'robot_class' => 'boss',
    'robot_game' => 'MMEXE',
    'robot_name' => 'Trill',
    'robot_token' => 'trill',
    'robot_core' => 'space',
    'robot_description' => 'Galactic Assistant Robot',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array(),
    'robot_resistances' => array('space', 'water', 'electric'),
    'robot_affinities' => array('freeze', 'flame'),
    'robot_immunities' => array('copy'),
    'robot_abilities' => array(
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
            array('level' => 15, 'token' => 'trill-aura'),
            array('level' => 30, 'token' => 'trill-slasher'),
            array('level' => 45, 'token' => 'trill-teranova')
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