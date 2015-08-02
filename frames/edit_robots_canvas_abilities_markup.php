<?

// Start the output buffer
ob_start();

echo '<div class="wrapper">';
  echo '<div class="wrapper_header player_type player_type_experience">Select Ability</div>';
  echo '<div class="wrapper_overflow">';
    //echo '<table><tr>';

    // Print out the remove ability option
    echo '<a class="ability_name" style="" data-id="0" data-key="0" data-player="player" data-robot="robot" data-ability="" title="" data-tooltip=""><label>- Remove Ability -</label></a>';

    // Loop through and print abilities
    $key_counter = 1;
    if (!empty($mmrpg_database_abilities)){
      $row_count = 4;
      $column_count = ceil(count($mmrpg_database_abilities) / $row_count);

      // Collect this player's ability rewards and add them to the dropdown
      if (!empty($_SESSION[$session_token]['values']['battle_abilities'])){ $player_ability_rewards = $_SESSION[$session_token]['values']['battle_abilities']; }
      elseif (!empty($player_rewards['player_abilities'])){ $player_ability_rewards = array('buster-shot' => array('ability_token' => 'buster-shot')); }

      // Create a fake player and robot to pass the info check
      $player_info = $mmrpg_index['players']['player']; //mmrpg_player::get_index_info('player'); //array('player_token' => 'player', 'player_name' => 'Player');
      $robot_info = mmrpg_robot::get_index_info('robot'); //array('robot_token' => 'robot', 'robot_name' => 'Robot');

      // Sort the ability rewards based on ability number and such
      uasort($player_ability_rewards, array('mmrpg_player', 'abilities_sort_for_editor'));
      $robot_ability_rewards = array();

      // Collect the ability reward options to be used on all selects
      $ability_rewards_options = $global_allow_editing ? mmrpg_ability::print_editor_options_list_markup($player_ability_rewards, $robot_ability_rewards, $player_info, $robot_info) : '';

      foreach ($mmrpg_database_abilities AS $ability_token => $ability_info){
        if (!mmrpg_prototype_ability_unlocked('', '', $ability_token)){ continue; }
        //if ($key_counter > 0 && $key_counter % 5 == 0){ echo '</tr><tr>'; }
        //echo '<td>';

        $temp_select_markup = mmrpg_ability::print_editor_select_markup($ability_rewards_options, $player_info, $robot_info, $ability_info, $key_counter);

        //echo $ability_token.'<br />';
        echo $temp_select_markup.' ';


        //echo '</td>';
        $key_counter++;
      }
    }

    //echo '</tr></table>';
  echo '</div>';
  if ($global_allow_editing){
    ?>
    <div class="sort_wrapper">
      <label class="label">sort</label>
      <a class="sort sort_level" data-sort="power" data-order="asc">power</a>
      <a class="sort sort_number" data-sort="number" data-order="asc">number</a>
      <a class="sort sort_core" data-sort="type" data-order="asc">type</a>
    </div>
    <?
  }
echo '</div>';

// Collect the contents of the buffer
$edit_canvas_markup = ob_get_clean();
$edit_canvas_markup = preg_replace('/\s+/', ' ', trim($edit_canvas_markup));
exit($edit_canvas_markup);

?>