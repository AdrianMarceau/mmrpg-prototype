<?
// BASS COPY
$robot = array(
    'robot_number' => 'DSN-0W2',
    'robot_class' => 'boss',
    'robot_game' => 'MM00',
    'robot_group' => 'MMRPG2',
    'robot_name' => 'Bass DS',
    'robot_token' => 'bass-ds',
    'robot_image_editor' => 412,
    'robot_core' => 'shadow',
    'robot_description' => 'Strongest Challenger Robot',
    'robot_field' => 'final-destination-2',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('flame', 'nature', 'wind', 'explode'), // first four robots by weakness
    'robot_resistances' => array('time', 'swift', 'cutter', 'water'), // last four robots by weakness
    'robot_affinities' => array('shadow'),
    'robot_abilities' => array(
        'buster-shot',
        'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
        'attack-break', 'defense-break', 'speed-break', 'energy-break',
        'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
        'attack-support', 'defense-support', 'speed-support', 'energy-support',
        'attack-assault', 'defense-assault', 'speed-assault', 'energy-assault',
        'attack-shuffle', 'defense-shuffle', 'speed-shuffle', 'energy-shuffle',
        'attack-mode', 'defense-mode', 'speed-mode', 'energy-mode',
        'experience-booster', 'recovery-booster', 'damage-booster',
        'experience-breaker', 'recovery-breaker', 'damage-breaker',
        'field-support', 'mecha-support',
        'mega-buster', 'proto-buster', 'roll-buster', 'disco-buster', 'rhythm-buster',
        'light-buster', 'wily-buster', 'cossack-buster',
        'mega-ball', 'mega-slide', 'proto-shield', 'proto-strike'
        ),
    'robot_rewards' => array(
        'abilities' => array(
            array('level' => 0, 'token' => 'buster-shot'),
            array('level' => 2, 'token' => 'bass-buster'),
            array('level' => 4, 'token' => 'bass-crush'),
            array('level' => 8, 'token' => 'bass-baroque')
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