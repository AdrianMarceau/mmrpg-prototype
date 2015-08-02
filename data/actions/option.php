<?
// Generate the markup for the action option panel
ob_start();
  // Define the markup for the option buttons
  $temp_options = array();
  // Display the option for returning to the main prototype menu
  $temp_options[] = '<a data-order="1" class="button action_option block_1 ability_type_space" type="button" data-action="prototype"><label><span class="multi">Return&nbsp;To<br />Main&nbsp;Menu</span></label></a>';
  // Display an option for restarting this battle from scratch
  $temp_options[] = '<a data-order="2" class="button action_option block_2 ability_type_space" type="button" data-action="restart"><label><span class="multi">Restart<br />Mission</span></label></a>';
  // Display an option for changing config settings
  //$temp_options[] = '<a class="button action_option block_3" type="button" data-panel="settings"><label><span class="multi">Settings<br />Menu</span></label></a>';
  $temp_options[] = '<a data-order="3" class="button action_option block_3 ability_type_space" type="button" data-panel="settings_eventTimeout"><label><span class="multi">Message<br />Speed</span></label></a>';
  $temp_options[] = '<a data-order="4" class="button action_option block_4 ability_type_space" type="button" onclick="parent.mmrpg_music_load(\'fields/'.$this_field->field_music.'/battle-field_background_music\', true);"><label><span class="multi">Restart<br />Music</span></label></a>';
  // Display the toggle options for debug mode and stuff
  $current_debug_value = !empty($_SESSION['GAME']['debug_mode']) ? 1 : 0;
  $temp_options[] = '<a data-order="5" class="button action_option block_5 ability_type_space" type="button" onclick="mmrpg_toggle_debug_mode(this);" data-value="'.$current_debug_value.'"><label><span class="multi"><span class="title">Toggle Debug</span><br /><span class="value type '.($current_debug_value ? 'nature' : 'flame').'">'.($current_debug_value ? 'ON' : 'OFF').'</span></span></label></a>';
  /*
  if (empty($_SESSION['GAME']['DEMO'])
    && $this_battle->battle_status != 'complete'
    && $target_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID
    && isset($_SESSION['GAME']['values']['battle_items'])
    ){
    $temp_options[] = '<a data-order="5" class="button action_option block_5 ability_type_electric" type="button" data-panel="item"><label><span class="multi">Item<br />Inventory</span></label></a>';
    //$temp_options[] = '<a data-order="6" class="button action_option block_6 ability_type_nature" type="button" data-action="ability_8_action-recharge-energy"><label><span class="multi">Recharge<br />Energy</span></label></a>';
    //$temp_options[] = '<a data-order="7" class="button action_option block_7 ability_type_water" type="button" data-action="ability_9_action-recharge-weapons"><label><span class="multi">Recharge<br />Weapons</span></label></a>';
    //$temp_options[] = '<a data-order="8" class="button button_disabled action_option block_8 ability_type_swift" type="button" data-panel="item"><label><span class="multi">Call For<br />Help!</span></label></a>';
    //$temp_options[] = '<a class="button action_option block_5" type="button" onclick="mmrpg_toggle_animation();"><label><span class="multi">Toggle<br />Animation</span></label></a>';
    //$temp_options[] = '<a class="button action_option block_3" type="button" data-panel="settings_volumeControl"><label><span class="multi">Volume<br />Control</span></label></a>';
    //$temp_options[] = '<a class="button action_option block_3" type="button" data-panel="settings_autoScan"><label><span class="multi">Auto<br />Scan</span></label></a>';
  }
  */


  // Display container for the main actions
  ?><div class="main_actions main_actions_hastitle"><span class="main_actions_title">Select Option</span><?
  // Ensure there are options to display
  if (!empty($temp_options)){
    // Count the total number of options
    $num_options = count($temp_options);
    // Loop through each option and display its button markup
    foreach ($temp_options AS $key => $option_markup){
      // Display the option button's generated markup
      echo $option_markup;
    }
    // If there were less than 6 options, fill in the empty spaces
    if ($num_options < 8){
      for ($i = $num_options; $i < 8; $i++){
        // Display an empty button placeholder
        ?><a class="button action_option button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
      }
    }
  }
  // End the main action container tag
  ?></div><?
  // Display the back button by default
  ?><div class="sub_actions"><a data-order="7" class="button action_back" type="button" data-panel="battle"><label>Back</label></a></div><?
$actions_markup['option'] = trim(ob_get_clean());
$actions_markup['option'] = preg_replace('#\s+#', ' ', $actions_markup['option']);
?>