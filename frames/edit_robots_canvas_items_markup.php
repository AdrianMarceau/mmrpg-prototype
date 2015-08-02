<?

// Start the output buffer
ob_start();

echo '<div class="wrapper">';
  echo '<div class="wrapper_header player_type player_type_experience">Select Item</div>';
  echo '<div class="wrapper_overflow">';
    //echo '<table><tr>';

    // Print out the remove item option
    echo '<a class="item_name" style="" data-id="0" data-key="0" data-player="player" data-robot="robot" data-ability="" title="" data-tooltip=""><label>- Remove Item -</label></a>';

    // Loop through and print items
    $key_counter = 0;
    if (!empty($mmrpg_database_items)){
      $row_count = 4;
      $column_count = ceil(count($mmrpg_database_items) / $row_count);

      // Collect this player's item rewards and add them to the dropdown
      if (!empty($_SESSION[$session_token]['values']['battle_items'])){ $player_item_rewards = $_SESSION[$session_token]['values']['battle_items']; }
      elseif (!empty($player_rewards['player_items'])){ $player_item_rewards = array(); }

      // Create a fake player and robot to pass the info check
      $player_info = $mmrpg_index['players']['player']; //mmrpg_player::get_index_info('player'); //array('player_token' => 'player', 'player_name' => 'Player');
      $robot_info = mmrpg_robot::get_index_info('robot'); //array('robot_token' => 'robot', 'robot_name' => 'Robot');

      // Sort the item rewards based on item number and such
      uasort($player_item_rewards, array('mmrpg_player', 'abilities_sort_for_editor'));

      //die(print_r($player_item_rewards, true));

      // Collect the item reward options to be used on all selects
      $item_rewards_options = $global_allow_editing ? mmrpg_item::print_editor_options_list_markup($player_item_rewards, null, $player_info, $robot_info) : '';

      foreach ($mmrpg_database_items AS $item_token => $item_info){
        if (!isset($player_item_rewards[$item_token])){ continue; }
        if ($item_info['ability_subclass'] != 'holdable'){ continue; }
        //if ($key_counter > 0 && $key_counter % 5 == 0){ echo '</tr><tr>'; }
        //echo '<td>';

        $temp_select_markup = mmrpg_item::print_editor_select_markup($item_rewards_options, $player_info, $robot_info, $item_info, $key_counter);

        //echo $item_token.'<br />';
        //echo $item_info['ability_subclass'].'<br />';
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