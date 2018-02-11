<?
// SNIPER JOE
$robot = array(
    'robot_number' => 'SJOE-001', // ROBOT : SNIPER JOE (1st Gen)
    'robot_game' => 'MM01',
    'robot_group' => 'MMRPG',
    'robot_name' => 'Sniper Joe',
    'robot_token' => 'sniper-joe',
    'robot_description' => 'Shield Sniper Mecha',
    'robot_image_editor' => 412,
    'robot_image_alts' => array(
        array('token' => 'alt', 'name' => 'Sniper Joe (No Shield)', 'summons' => 0),
        ),
    'robot_class' => 'mecha',
    'robot_core' => 'shield',
    'robot_field' => 'intro-field',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('explode', 'cutter'),
    'robot_resistances' => array('water', 'flame', 'electric', 'nature'),
    'robot_abilities' => array(
        'buster-shot', 'buster-charge',
        'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
        'attack-break', 'defense-break', 'speed-break', 'energy-break',
        'attack-mode', 'defense-mode', 'speed-mode', 'energy-mode',
        'light-buster', 'wily-buster', 'cossack-buster'
        ),
    'robot_rewards' => array(
        'abilities' => array(
                array('level' => 0, 'token' => 'joe-shot'),
                array('level' => 0, 'token' => 'joe-shield'),
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

        // Check if this robot has the shield attachment active
        $joe_shield_active = false;
        if (!empty($this_robot->robot_attachments)){
            foreach ($this_robot->robot_attachments AS $attachment_token => $attachment_info){
                if (strstr($attachment_token, 'ability_joe-shield_')){
                    $joe_shield_active = true;
                    break;
                    }
                }
            }

        // If this robot does not have the shield attachment, change alts
        $base_image_token = $this_robot->robot_token;
        $alt_image_token = $this_robot->robot_token.'_alt';
        if (!$joe_shield_active && $this_robot->robot_image != $alt_image_token){
            $this_robot->set_image($alt_image_token);
            } elseif ($joe_shield_active && $this_robot->robot_image != $base_image_token){
            $this_robot->set_image($base_image_token);
            }

        }
    );
?>