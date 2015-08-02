<?
// Generate the markup for the action battle panel
ob_start();
  // If the current robot is not disabled and is active
  if ($this_robot->robot_energy > 0 && $this_robot->robot_position == 'active'){
    // Define the order counter and start at one
    $dataOrder = 1;
    // Display available main actions
    ?><div class="main_actions"><?
      if (!empty($temp_player_ability_actions) || $this_robot->robot_class == 'mecha'){
        ?><a class="button action_ability" type="button" data-panel="ability" data-order="<?= $dataOrder ?>"><label>Ability</label></a><?
      } else {
        ?><a class="button button_disabled action_ability" type="button" data-action="ability_8_action-noweapons" data-order="<?= $dataOrder ?>"><label style="text-decoration: line-through;">Ability</label></a><?
      } $dataOrder++;
    ?></div><?
    // Display the available sub options
    ?><div class="sub_actions"><?
      
      // Display the SCAN option
      if ($target_player->counters['robots_active'] > 1){
        ?><a class="button action_scan" type="button" <?= $target_player->counters['robots_active'] > 1 ? 'data-panel="scan"' : 'data-action="scan_'.$target_robot->robot_id.'_'.$target_robot->robot_token.'"' ?> data-order="<?= $dataOrder ?>"><label>Scan</label></a><?
      } else {
        foreach ($target_player->values['robots_active'] AS $key => $info){
          if ($info['robot_position'] != 'active'){ continue; }
          ?><a class="button action_scan" type="button" data-action="scan_<?= $info['robot_id'].'_'.$info['robot_token'] ?>" data-order="<?= $dataOrder ?>"><label>Scan</label></a><?
          break;
        }
      }
      $dataOrder++;
      
      // Display the ITEM option
      $temp_disabled = false; //!empty($_SESSION['GAME']['DEMO']) || $target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID || !isset($_SESSION['GAME']['values']['battle_items']) ? true : false;
      ?><a class="button action_item <?= $temp_disabled ? 'button_disabled' : '' ?>" type="button" <?= !$temp_disabled ? 'data-panel="item"' : '' ?> <?= !$temp_disabled ? 'data-order="'.$dataOrder.'"' : '' ?>><label>Item</label></a><?
      if (!$temp_disabled){ $dataOrder++; }
      
      // Display the OPTION option
      ?><a class="button action_option" type="button" data-panel="option" data-order="<?= $dataOrder ?>"><label>Option</label></a><?
      $dataOrder++;
      
      // Display the SWITCH option
      ?><a class="button action_switch" type="button" data-panel="switch" data-order="<?= $dataOrder ?>"><label>Switch</label></a><?
      $dataOrder++;
    
    ?></div><?
  }
  // Otherwise if this robot has been disabled
  else {
    // Display available main actions
    ?><div class="main_actions"><?
    ?><a class="button action_ability button_disabled" type="button"><label>Ability</label></a><?
    ?></div><?
    // Display the available sub options
    ?><div class="sub_actions"><?
      ?><a class="button action_scan button_disabled" type="button"><label>Scan</label></a><?
      ?><a class="button action_item button_disabled" type="button"><label>Item</label></a><?
      ?><a class="button action_option" type="button" data-panel="option" data-order="1"><label>Option</label></a><?
      ?><a class="button action_switch" type="button" data-panel="switch" data-order="2"><label>Switch</label></a><?
    ?></div><?
  }
$actions_markup['battle'] = trim(ob_get_clean());
$actions_markup['battle'] = preg_replace('#\s+#', ' ', $actions_markup['battle']);
?>