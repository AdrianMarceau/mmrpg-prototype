<?
// BOMB MAN
$robot = array(
    'robot_number' => 'DLN-006',
    'robot_game' => 'MM01',
    'robot_name' => 'Bomb Man',
    'robot_token' => 'bomb-man',
    'robot_image_editor' => 412,
    'robot_image_alts' => array(
        array('token' => 'alt', 'name' => 'Bomb Man (Green Alt)', 'summons' => 100, 'colour' => 'nature'),
        array('token' => 'alt2', 'name' => 'Bomb Man (Purple Alt)', 'summons' => 200, 'colour' => 'time'),
        array('token' => 'alt9', 'name' => 'Bomb Man (Darkness Alt)', 'summons' => 900, 'colour' => 'empty')
        ),
    'robot_core' => 'explode',
    'robot_description' => 'Hyper Explosive Robot',
    'robot_field' => 'orb-city',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('cutter', 'flame'),
    'robot_resistances' => array('water'),
    'robot_affinities' => array('explode'),
    'robot_abilities' => array(
        'hyper-bomb', 'danger-bomb',
        'buster-shot',
        'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
        'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
        'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
        'energy-boost', 'energy-break', 'energy-swap', 'energy-mode',
        'field-support', 'mecha-support',
        'light-buster', 'wily-buster', 'cossack-buster'
        ),
    'robot_attachments' => array(
        'ability_hyper-bomb' => array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => 'hyper-bomb',
            'ability_frame' => 1,
            'ability_frame_offset' => array('x' => -55, 'y' => 1, 'z' => -1)
            )
        ),
    'robot_rewards' => array(
        'abilities' => array(
                array('level' => 0, 'token' => 'hyper-bomb'),
                array('level' => 10, 'token' => 'danger-bomb')
            )
        ),
    'robot_quotes' => array(
        'battle_start' => '',
        'battle_taunt' => '',
        'battle_victory' => '',
        'battle_defeat' => ''
        ),
    'robot_flag_unlockable' => true
    );
?>