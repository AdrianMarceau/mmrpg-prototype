<?
// ITEM : ENERGY PELLET
$item = array(
    'item_name' => 'Energy Pellet',
    'item_token' => 'energy-pellet',
    'item_game' => 'MM00',
    'item_group' => 'MM00/Items/Energy',
    'item_class' => 'item',
    'item_subclass' => 'consumable',
    'item_type' => 'energy',
    'item_description' => 'A small health pellet that restores 20% life energy to one robot on the user\'s side of the field.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_recovery' => 25,
    'item_recovery_percent' => true,
    'item_accuracy' => 100,
    'item_price' => 200,
    'item_target' => 'select_this',
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_item->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 40, -2, 99,
                $this_player->print_name().' uses an item from the inventory&hellip; <br />'.
                $target_robot->print_name().' is given the '.$this_item->print_name().'!'
                )
            ));
        $target_robot->trigger_target($target_robot, $this_item);

        // Increase this robot's life energy stat
        $this_item->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => false,
            'frame' => 'taunt',
            'success' => array(9, 0, 0, -9999, $target_robot->print_name().'&#39;s life energy was restored!'),
            'failure' => array(9, 0, 0, -9999, $target_robot->print_name().'&#39;s life energy was not affected&hellip;')
            ));
        $energy_recovery_amount = ceil($target_robot->robot_base_energy * ($this_item->item_recovery / 100));
        $target_robot->trigger_recovery($target_robot, $this_item, $energy_recovery_amount);

        // Return true on success
        return true;

    }
    );
?>