<?
// TRILL
$robot = array(
    'robot_number' => 'EXN-00X',
    'robot_class' => 'boss',
    'robot_game' => 'MMEXE',
    'robot_name' => 'Trill',
    'robot_token' => 'trill',
    'robot_image_editor' => 412,
    'robot_image_alts' => array(
        array('token' => 'alt', 'name' => 'Trill (Attack Alt)', 'summons' => 100),
        array('token' => 'alt2', 'name' => 'Trill (Defense Alt)', 'summons' => 200),
        array('token' => 'alt3', 'name' => 'Trill (Speed Alt)', 'summons' => 300),
        ),
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
        ),
    'robot_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect this robot's main stats for comparrison
        $attack = $this_robot->robot_attack;
        $defense = $this_robot->robot_defense;
        $speed = $this_robot->robot_speed;

        // Check if any of this robot's stats are higher than the others
        if ($attack > $defense && $attack > $speed){ $dominant_type = 'attack'; }
        elseif ($defense > $attack && $defense > $speed){ $dominant_type = 'defense'; }
        elseif ($speed > $attack && $speed > $defense){ $dominant_type = 'speed'; }
        else { $dominant_type = 'energy'; }

        // Define this robot's image token based on dominant stat
        if ($dominant_type == 'attack'){ $alt_image_token = $this_robot->robot_token.'_alt'; }
        elseif ($dominant_type == 'defense'){ $alt_image_token = $this_robot->robot_token.'_alt2'; }
        elseif ($dominant_type == 'speed'){ $alt_image_token = $this_robot->robot_token.'_alt3'; }
        else { $alt_image_token = $this_robot->robot_token; }

        // If this robot does not have the approriate image right now, change alts
        if ($this_robot->robot_image != $alt_image_token){
            $this_robot->set_image($alt_image_token);
            }

        }
    );
?>