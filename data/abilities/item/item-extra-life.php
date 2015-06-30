<?
// ITEM : EXTRA LIFE
$ability = array(
  'ability_name' => 'Extra Life',
  'ability_token' => 'item-extra-life',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Energy',
  'ability_class' => 'item',
  'ability_subclass' => 'consumable',
  'ability_image_sheets' => 3,
  'ability_type' => 'energy',
  'ability_type2' => 'weapons',
  'ability_description' => 'A backup program that revives one disabled robot on the user\'s side of the field with {RECOVERY}% life and weapon energy.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_recovery' => 50,
  'ability_accuracy' => 100,
  'ability_target' => 'select_this_disabled',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Automatically change this ability's image based on player
    if ($this_player->player_token == 'dr-light'){ $this_ability->ability_image = 'item-extra-life'; }
    elseif ($this_player->player_token == 'dr-wily'){ $this_ability->ability_image = 'item-extra-life-2'; }
    elseif ($this_player->player_token == 'dr-cossack'){ $this_ability->ability_image = 'item-extra-life-3'; }

    // Allow this robot to show on the canvas again so we can revive it
    unset($target_robot->flags['apply_disabled_state']);
    unset($target_robot->flags['hidden']);
    unset($target_robot->robot_attachments['ability_attachment-defeat']);
    $target_robot->robot_frame = 'defeat';
    $target_robot->update_session();

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'defeat',
      'success' => array(0, 40, -2, 99,
        $this_player->print_player_name().' uses an item from the inventory&hellip; <br />'.
        $target_robot->print_robot_name().' is given the '.$this_ability->print_ability_name().'!'
        )
      ));
    $target_robot->trigger_target($target_robot, $this_ability);

    // Restore the target robot's health and weapons back to their full amounts
    $target_robot->robot_status = 'active';
    $target_robot->robot_energy = 0; //$target_robot->robot_base_energy;
    $target_robot->robot_weapons = 0; //$target_robot->robot_base_weapons;
    $target_robot->robot_attack = $target_robot->robot_base_attack;
    $target_robot->robot_defense = $target_robot->robot_base_defense;
    $target_robot->robot_speed = $target_robot->robot_base_speed;
    $target_robot->update_session();

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(0, 40, -2, 10,
        $target_robot->print_robot_name().'&#39;s battle data was restored!<br />'.
        $target_robot->print_robot_name().'&#39;s is no longer disabled!'
        )
      ));
    $target_robot->trigger_target($target_robot, $this_ability);

    // Increase this robot's life energy stat
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'percent' => true,
      'modifiers' => false,
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s life energy was fully restored!'),
      'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s life energy was not affected&hellip;')
      ));
    $energy_recovery_amount = ceil($target_robot->robot_base_energy * ($this_ability->ability_recovery / 100));
    $target_robot->trigger_recovery($target_robot, $this_ability, $energy_recovery_amount);

    // Increase this robot's weapon energy stat
    $this_ability->recovery_options_update(array(
      'kind' => 'weapons',
      'percent' => true,
      'modifiers' => false,
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s weapon energy was fully restored!'),
      'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s weapon energy was not affected&hellip;')
      ));
    $weapons_recovery_amount = ceil($target_robot->robot_base_weapons * ($this_ability->ability_recovery / 100));
    $target_robot->trigger_recovery($target_robot, $this_ability, $weapons_recovery_amount);

    /*
    // Increase this robot's life energy stat
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'percent' => true,
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s life energy was restored!'),
      'failure' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s life energy was not affected&hellip;')
      ));
    $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($this_ability->ability_recovery / 100));
    $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);
    */

    // Return true on success
    return true;

  }
  );
?>