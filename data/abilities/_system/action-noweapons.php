<?
// ACTION : NO WEAPONS
$ability = array(
    'ability_name' => 'Low Weapon Energy',
    'ability_token' => 'action-noweapons',
    'ability_class' => 'system',
    'ability_description' => 'Critically low on weapon energy and unable to use an ability, the active robot waits to recharge for one turn...',
    'ability_energy' => 0,
    'ability_damage' => 0,
    'ability_speed' => 10,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Print out the message showing the lack of weapon energy if applicable
        if ($this_robot->player->player_side == 'right'
            && $this_robot->robot_weapons <= ($this_robot->robot_base_weapons / 2)){
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(9, 0, 0, -999, $this_robot->print_name().' doesn\'t have enough weapon energy to attack!')));
            $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));
        }

        // Return true on success
        return true;

        }
    );
?>