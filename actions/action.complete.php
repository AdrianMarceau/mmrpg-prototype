<?php
// Generate the markup for the action switch panel
ob_start();
  // If the current robot is not disabled (WE WIN!)
  if ($this_player->counters['robots_active'] > 0){
    // Display available main actions
    ?><div class="main_actions"><?php
    ?><a class="button action_ability" data-action="prototype" type="button" data-order="1"><label>Mission Complete!</label></a><?php
    ?></div><?php
    // Display the available sub options
    ?><div class="sub_actions"><?php
    ?><a class="button action_scan button_disabled" type="button">&nbsp;</a><?php
    ?><a class="button action_item button_disabled" type="button">&nbsp;</a><?php
    ?><a class="button action_option button_disabled" type="button">&nbsp;</a><?php
    ?><a class="button action_switch button_disabled" type="button">&nbsp;</a><?php
    ?></div><?php
  }
  // Otherwise if this robot has been disabled (WE LOOSE!)
  else {
    // Display available main actions
    ?><div class="main_actions"><?php
    ?><a class="button action_ability button_disabled" type="button"><label>Mission Failure&hellip;</label></a><?php
    ?></div><?php
    // Display the available sub options
    ?><div class="sub_actions"><?php
    ?><a class="button action_scan button_disabled" type="button">&nbsp;</a><?php
    ?><a class="button action_item" data-action="prototype" type="button" data-order="1"><label>Exit Mission</label></a><?php
    ?><a class="button action_option" data-action="restart" type="button" data-order="2"><label>Restart Mission</label></a><?php
    ?><a class="button action_switch button_disabled" type="button">&nbsp;</a><?php
    ?></div><?php
  }
$actions_markup['battle'] = trim(ob_get_clean());
$actions_markup['battle'] = preg_replace('#\s+#', ' ', $actions_markup['battle']);
?>