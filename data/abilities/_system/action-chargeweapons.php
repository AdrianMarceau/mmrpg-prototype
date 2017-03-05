<?
// ACTION : CHARGE WEAPONS
$ability = array(
    'ability_name' => 'Recharging',
    'ability_token' => 'action-chargeweapons',
    'ability_class' => 'system',
    'ability_description' => 'The user enters a charging state that recovers weapon energy more quickly but leaves the user open to attack.',
    'ability_energy' => 0,
    'ability_damage' => 0,
    'ability_speed' => 10,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define the base attachment duration
        $base_attachment_duration = 1;
        $base_attachment_multiplier = 1.25;

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $base_attachment_duration,
            'attachment_damage_input_booster' => $base_attachment_multiplier,
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(2, -10, 0, -10,  ''),
                'failure' => array(2, -10, 0, -10, '')
                )
            );

        // Create the attachment object for this ability
        $this_attachment = rpg_game::get_ability($this_battle, $this_player, $this_robot, $this_attachment_info);

        // Target this robot's self and show the ability triggering
        $temp_weapons_current = $this_robot->robot_weapons;
        $temp_weapons_lost = $this_robot->robot_base_weapons - $this_robot->robot_weapons;
        $temp_weapons_recovery = ceil($this_robot->robot_base_weapons * 0.25);
        if ($temp_weapons_recovery > $temp_weapons_lost){ $temp_weapons_recovery = $temp_weapons_lost; }
        $temp_weapons_new = $temp_weapons_current + $temp_weapons_recovery;

        // Trigger the charging message and increase WE if applicable
        if ($temp_weapons_recovery > 0){ $this_robot->set_weapons($temp_weapons_new); }
        $this_ability->target_options_update(array(
            'frame' => 'defend',
            'success' => array(9, 0, 0, -10,
                $this_robot->print_name().' waits for weapon energy to recharge...'
                )
            ));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

        // Return true on success
        return true;

        }
    );
?>