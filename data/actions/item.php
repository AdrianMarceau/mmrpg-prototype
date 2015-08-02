<?
// Collect the temp item index
$temp_items_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

// Define all the available player items in a handy index array
$temp_player_items = $this_player->player_items;
//echo print_r($temp_player_items, true);
foreach ($temp_player_items AS $key => $token){
  $info = !empty($temp_items_index[$token]) ? $temp_items_index[$token] : false;
  if (empty($info) || $info['ability_subclass'] != 'consumable'){ unset($temp_player_items[$key]); }
}
//echo print_r($temp_player_items, true);
$temp_player_items_count = count($temp_player_items);
$temp_player_items_pages = $temp_player_items_count <= 8 ? 1 : ceil($temp_player_items_count / 8);

// Require the item database page for sorting purposes
require(MMRPG_CONFIG_ROOTDIR.'data/database_items.php');
// Generate the markup for the action item panel
ob_start();
  // Define and start the order counter
  $temp_order_counter = 1;
  // Display container for the main actions
  ?>
  <div class="main_actions main_actions_hastitle">
    <span class="main_actions_title <?= $target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID || !empty($this_player->flags['item_used_this_turn']) ? 'main_actions_title_disabled' : '' ?>">
      <?
      // If there were more than eight items, print the page numbers
      if ($temp_player_items_count > 8){
        $temp_selected_page = 1; //!empty($_SESSION['GAME']['battle_settings']['action_ability_page_num']) ? $_SESSION['GAME']['battle_settings']['action_ability_page_num'] : 1;
        echo '<span class="float_title">Select Item</span>';
        echo '<span class="float_links"><span class="page">Page</span>';
        for ($i = 1; $i <= $temp_player_items_pages; $i++){ echo '<a class="num'.($i == $temp_selected_page ? ' active' : '').'" href="#'.$i.'">'.$i.'</a>'; }
        echo '</span>';
      }
      // Otherwise, simply print the item select text label
      else {
        echo 'Select Item';
      }
      ?>
    </span>
    <?
    // Collect the items for this player, by whatever means
    $current_player_items = $temp_player_items;
    // If this player has more than eight items, slice to only eight
    if (count($current_player_items) > 8){
      //$current_player_items = array_slice($current_player_items, 0, 8);
      //$_SESSION['GAME']['values']['battle_items'] = $current_player_items;
    }
    // Ensure this player has items to display
    if (!empty($current_player_items)){
      // Count the total number of items
      $num_items = count($current_player_items);
      $item_direction = $this_player->player_side == 'left' ? 'right' : 'left';
      // Define the item display counter
      $equipped_items_count = 0;

      // DEBUG
      //echo('$current_player_items:<pre>'.preg_replace('/\s+/', ' ', print_r($current_player_items, true)).'</pre>');

      // Define the default button enabled option
      //if (!empty($_SESSION['GAME']['DEMO']) || $target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID || !isset($_SESSION['GAME']['values']['battle_items'])){ $temp_button_enabled_base = false; }
      if ($target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID){ $temp_button_enabled_base = false; }
      else { $temp_button_enabled_base = true; }
      // If this player has already used an item this turn
      if (!empty($this_player->flags['item_used_this_turn'])){ $temp_button_enabled_base = false; }

      // Loop through each ability and display its button
      $item_key = 0;
      $item_token_list = array_keys($mmrpg_database_items);
      foreach ($item_token_list AS $item_key => $item_token){
        // Ensure this is an actual ability in the index
        if (!empty($item_token) && in_array($item_token, $current_player_items)){

          // DEBUG
          //echo('$item_token:<pre>'.preg_replace('/\s+/', ' ', print_r($item_token, true)).'</pre>');

          // Define the amount of weapon energy for this ability
          $temp_item_quantity = !empty($_SESSION['GAME']['values']['battle_items'][$item_token]) ? $_SESSION['GAME']['values']['battle_items'][$item_token] : 0; // CHANGEME to zero!!! mt_rand(0, 10)
          $temp_item_cost = 1;

          // If this player has never aquired this item, do not display it
          if (!isset($_SESSION['GAME']['values']['battle_items'][$item_token])){ continue; }
          //$temp_item_quantity = 99; // CHANGEME! COMMENT ME OUT! ALSO UNCOMMENT ABOVE?

          // Increment the equipped items count
          $equipped_items_count++;

          // Define the button enabled flag
          $temp_button_enabled = $temp_button_enabled_base;

          // Create the ability object using the session/index data
          //$temp_iteminfo = array('ability_token' => $item_token);
          $temp_iteminfo = $temp_items_index[$item_token];
          $temp_iteminfo = mmrpg_ability::parse_index_info($temp_iteminfo);

          //$temp_iteminfo['ability_id'] = $this_player->player_id.str_pad($temp_iteminfo['ability_id'], 3, '0', STR_PAD_LEFT);
          $temp_item = new mmrpg_ability($this_battle, $this_player, $this_robot, $temp_iteminfo);
          $temp_type = $temp_item->ability_type;
          $temp_type2 = $temp_item->ability_type2;
          $temp_damage = $temp_item->ability_damage;
          $temp_damage2 = $temp_item->ability_damage2;
          $temp_damage_unit = $temp_item->ability_damage_percent ? '%' : '';
          $temp_damage2_unit = $temp_item->ability_damage2_percent ? '%' : '';
          $temp_recovery = $temp_item->ability_recovery;
          $temp_recovery2 = $temp_item->ability_recovery2;
          $temp_recovery_unit = $temp_item->ability_recovery_percent ? '%' : '';
          $temp_recovery2_unit = $temp_item->ability_recovery2_percent ? '%' : '';
          $temp_accuracy = $temp_item->ability_accuracy;
          $temp_kind = !empty($temp_damage) && empty($temp_recovery) ? 'damage' : (!empty($temp_recovery) && empty($temp_damage) ? 'recovery' : (!empty($temp_damage) && !empty($temp_recovery) ? 'multi' : ''));
          if (preg_match('/^item-score-ball/i', $item_token)){ $temp_kind = 'score'; }
          elseif (preg_match('/^item-core-/i', $item_token)){ $temp_kind = 'core'; }
          $temp_target = 'select_this';
          if ($temp_item->ability_target == 'select_target' && $target_player->counters['robots_active'] > 1){ $temp_target = 'select_target'; }
          elseif ($temp_item->ability_target == 'select_this' && $this_player->counters['robots_active'] > 1){ $temp_target = 'select_this'; }
          elseif ($temp_item->ability_target == 'select_this_disabled'){ $temp_target = 'select_this_disabled'; }
          elseif ($temp_item->ability_target == 'auto'){ $temp_target = 'auto'; }
          //elseif ($temp_item->ability_target == 'select_this_disabled' && $this_player->counters['robots_disabled'] >= 1){ $temp_target = 'select_this_disabled'; }
          //elseif ($temp_item->ability_target == 'select_this_disabled' && $this_player->counters['robots_disabled'] < 1){ $temp_button_enabled = false; }
          //$temp_target = $target_player->counters['robots_active'] > 1 ? $temp_item->ability_target : 'auto';

          $temp_multiplier = 1;
          //if (!empty($this_robot->robot_core) && ($this_robot->robot_core == $temp_type || $this_robot->robot_core == $temp_type2)){ $temp_multiplier = $temp_multiplier * 1.5; }
          //if (!empty($this_battle->battle_field->field_multipliers[$temp_type])){ $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers[$temp_type]; }
          //if (!empty($this_battle->battle_field->field_multipliers[$temp_type2])){ $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers[$temp_type2]; }

          /*
          $temp_damage = ceil($temp_damage * $temp_multiplier);
          if ($item_token != 'experience-booster' && !empty($this_battle->battle_field->field_multipliers['damage'])){ $temp_damage = ceil($temp_damage * $this_battle->battle_field->field_multipliers['damage']); }
          if ($temp_damage_unit == '%' && $temp_damage > 100){ $temp_damage = 100; }
          $temp_damage2 = ceil($temp_damage2 * $temp_multiplier);
          if ($item_token != 'experience-booster' && !empty($this_battle->battle_field->field_multipliers['damage'])){ $temp_damage2 = ceil($temp_damage2 * $this_battle->battle_field->field_multipliers['damage']); }
          if ($temp_damage2_unit == '%' && $temp_damage2 > 100){ $temp_damage2 = 100; }

          $temp_recovery = ceil($temp_recovery * $temp_multiplier);
          if ($item_token != 'experience-booster' && !empty($this_battle->battle_field->field_multipliers['recovery'])){ $temp_recovery = ceil($temp_recovery * $this_battle->battle_field->field_multipliers['recovery']); }
          if ($temp_recovery_unit == '%' && $temp_recovery > 100){ $temp_recovery = 100; }
          $temp_recovery2 = ceil($temp_recovery2 * $temp_multiplier);
          if ($item_token != 'experience-booster' && !empty($this_battle->battle_field->field_multipliers['recovery'])){ $temp_recovery2 = ceil($temp_recovery2 * $this_battle->battle_field->field_multipliers['recovery']); }
          if ($temp_recovery2_unit == '%' && $temp_recovery2 > 100){ $temp_recovery2 = 100; }
          */

          // Define the ability title details text
          $temp_item_details = $temp_item->ability_name.' <br />';
          //$temp_item_details .= ' ('.(!empty($temp_item->ability_type) ? $mmrpg_index['types'][$temp_item->ability_type]['type_name'] : 'Neutral');
          //if (!empty($temp_item->ability_type2)){ $temp_item_details .= ' / '.$mmrpg_index['types'][$temp_item->ability_type2]['type_name']; }
          //else { $temp_item_details .= ' Type'; }
          //$temp_item_details .= ') <br />';
          if ($temp_kind == 'damage'){ $temp_item_details .= $temp_damage.$temp_damage_unit.' Damage'; }
          elseif ($temp_kind == 'recovery'){ $temp_item_details .= $temp_recovery.$temp_recovery_unit.' Recovery'; }
          elseif ($temp_kind == 'multi'){ $temp_item_details .= $temp_damage.$temp_damage_unit.' Damage / '.$temp_recovery.$temp_recovery_unit.' Recovery'; }
          elseif ($temp_kind == 'core'){ $temp_item_details .= '10% Damage'; }
          else { $temp_item_details .= 'Support'; }
          //$temp_item_details .= ' | '.$temp_item->ability_accuracy.'% Accuracy';
          if (!empty($temp_item_quantity)){
            if ($temp_item_quantity != 1){ $temp_item_details .= ' | '.$temp_item_quantity.' Units'; }
            else { $temp_item_details .= ' | 1 Unit'; }
          }
          $temp_item_description = $temp_item->ability_description;
          $temp_item_description = str_replace('{DAMAGE}', $temp_damage, $temp_item_description);
          $temp_item_description = str_replace('{RECOVERY}', $temp_recovery, $temp_item_description);
          $temp_item_description = str_replace('{DAMAGE2}', $temp_damage2, $temp_item_description);
          $temp_item_description = str_replace('{RECOVERY2}', $temp_recovery2, $temp_item_description);
          $temp_item_details .= ' <br />'.$temp_item_description;
          $temp_item_details_plain = strip_tags(str_replace('<br />', '&#10;', $temp_item_details));
          $temp_item_details_tooltip = htmlentities($temp_item_details, ENT_QUOTES, 'UTF-8');

          //$temp_item_details .= ' | x'.$temp_multiplier.' '.$this_robot->robot_core.' '.count($this_battle->battle_field->field_multipliers);

          // Define the ability button text variables
          $temp_item_label = '<span class="multi">';
          $temp_item_label .= '<span class="maintext">'.$temp_item->ability_name.'</span>';
          $temp_item_label .= '<span class="subtext">';
	          if ($temp_kind == 'score'){
              $temp_item_label .= 'Reward Booster';
	          } elseif ($temp_kind == 'core'){
	          	$temp_item_label .= (!empty($temp_type) ? $mmrpg_index['types'][$temp_item->ability_type]['type_name'] : 'Neutral');
              $temp_item_label .= ' Damage';
	          	//$temp_item_label .= 'Field Multiplier';
	          } else {
	            $temp_item_label .= (!empty($temp_type) ? $mmrpg_index['types'][$temp_item->ability_type]['type_name'].' ' : 'Neutral ');
	            if (!empty($temp_type) && !empty($temp_type2)){ $temp_item_label .= ' / '.$mmrpg_index['types'][$temp_item->ability_type2]['type_name']; }
	            else { $temp_item_label .= ($temp_kind == 'damage' ? 'Damage' : ($temp_kind == 'recovery' ? 'Recovery' : ($temp_kind == 'multi' ? 'Effects' : 'Special'))); }
            }
          $temp_item_label .= '</span>';
          $temp_item_label .= '<span class="subtext">';
	          if ($temp_kind == 'score'){
	          	$temp_item_label .= '<span>Value : +'.$temp_recovery.$temp_recovery_unit.'</span>';
	          } elseif ($temp_kind == 'core'){
	          	//$temp_item_label .= '<span>Value : +'.$temp_recovery.$temp_recovery_unit.'</span>';
	          	$temp_item_label .= '<span>Power : 10%</span>';
	          } else {
              $temp_item_label .= '<span style="'.($temp_multiplier != 1 ? ($temp_multiplier > 1 ? 'color: rgb(161, 255, 124); ' : 'color: rgb(255, 150, 150); ') : '').'">Power : '.($temp_kind == 'damage' ? $temp_damage.$temp_damage_unit.' ' : ($temp_kind == 'recovery' ? $temp_recovery.$temp_recovery_unit.' ' : ($temp_kind == 'multi' ? $temp_damage.$temp_damage_unit.'/'.$temp_recovery.$temp_recovery_unit.' ' : '0'))).'</span>';
	          }
            $temp_item_label .= '&nbsp;';
            //$temp_item_label .= 'A:'.$temp_accuracy.'%';
          $temp_item_label .= '</span>';
          $temp_item_label .= '</span>';

          // Define whether or not this ability button should be enabled
          if ($temp_item_quantity < $temp_item_cost){ $temp_button_enabled = false; }
          //$temp_button_enabled = $temp_item_quantity >= $temp_item_cost ? true : false;
          // If this button is enabled, add it to the global ability options array
          //if ($temp_button_enabled){ $temp_player_ability_actions[] = $temp_item->ability_token; }

          // All items appear in YELLOW
          //$temp_item_type_backup = $temp_item->ability_type;
          //$temp_item->ability_type = 'electric';

          // Define the ability sprite variables
          $temp_item_sprite = array();
          $temp_item_sprite['name'] = $temp_item->ability_name;
          $temp_item_sprite['image'] = $temp_item->ability_image;
          if ($temp_item->ability_token == 'item-extra-life'){
            // Automatically change this ability's image based on player
            if ($this_player->player_token == 'dr-light'){ $temp_item_sprite['image'] = 'item-extra-life'; }
            elseif ($this_player->player_token == 'dr-wily'){ $temp_item_sprite['image'] = 'item-extra-life-2'; }
            elseif ($this_player->player_token == 'dr-cossack'){ $temp_item_sprite['image'] = 'item-extra-life-3'; }
            //elseif ($this_player->player_token == 'dr-wily'){ $temp_item_sprite['image'] = 'item-extra-life-2'; }
            //elseif ($this_player->player_token == 'dr-cossack'){ $temp_item_sprite['image'] = 'item-extra-life-3'; }
          }
          $temp_item_sprite['image_size'] = $temp_item->ability_image_size;
          $temp_item_sprite['image_size_text'] = $temp_item_sprite['image_size'].'x'.$temp_item_sprite['image_size'];
          $temp_item_sprite['image_size_zoom'] = $temp_item->ability_image_size * 2;
          $temp_item_sprite['image_size_zoom_text'] = $temp_item_sprite['image_size'].'x'.$temp_item_sprite['image_size'];
          $temp_item_sprite['url'] = 'images/abilities/'.$temp_item_sprite['image'].'/icon_'.$item_direction.'_'.$temp_item_sprite['image_size_text'].'.png';
          $temp_item_sprite['preload'] = 'images/abilities/'.$temp_item_sprite['image'].'/sprite_'.$item_direction.'_'.$temp_item_sprite['image_size_zoom_text'].'.png';
          $temp_item_sprite['class'] = 'sprite size'.$temp_item_sprite['image_size'].' base '; // ability_type ability_type_'.(!empty($temp_item->ability_type) ? $temp_item->ability_type : 'none');
          $temp_item_sprite['style'] = 'background-image: url('.$temp_item_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.');  top: 5px; left: 5px; ';
          $temp_item_sprite['markup'] = '<span class="'.$temp_item_sprite['class'].' ability" style="'.$temp_item_sprite['style'].'">'.$temp_item_sprite['name'].'</span>';
          $temp_item_sprite['markup'] .= '<span class="'.$temp_item_sprite['class'].' weapons" style="top: 35px; left: 5px; '.($temp_item_quantity > 1 ? '' : ($temp_item_quantity > 0 ? 'color: #AA9393; ' : 'color: #A77D7D; ')).'"><sup style="position: relative: bottom: 1px;">x</sup> '.$temp_item_quantity.'</span>';
          $temp_type_class = !empty($temp_item->ability_type) ? $temp_item->ability_type : 'none';
          if ($temp_type_class != 'none' && !empty($temp_item->ability_type2)){ $temp_type_class .= '_'.$temp_item->ability_type2; }
          elseif ($temp_type_class == 'none' && !empty($temp_item->ability_type2)){ $temp_type_class = $temp_item->ability_type2; }
          // Now use the new object to generate a snapshot of this ability button
          /*if ($temp_button_enabled){ ?><a data-order="<?=$temp_order_counter?>" class="button action_ability action_item ability_<?= $temp_item->ability_token ?> ability_type ability_type_electric block_<?= $equipped_items_count ?>" type="button" data-action="ability_<?= $temp_item->ability_id.'_'.$temp_item->ability_token ?>" title="<?= $temp_item_details_plain ?>" data-tooltip="<?= $temp_item_details_tooltip ?>" data-preload="<?= $temp_item_sprite['preload'] ?>" data-actualtarget="<?= $temp_item->ability_target ?>" data-target="<?= $temp_target ?>"><label class=""><?= $temp_item_sprite['markup'] ?><?= $temp_item_label ?></label></a><? }*/
          if ($temp_button_enabled){ ?><a data-order="<?=$temp_order_counter?>" class="button action_ability action_item ability_<?= $temp_item->ability_token ?> ability_type ability_type_<?= $temp_type_class ?> block_<?= $equipped_items_count ?>" type="button" data-action="ability_<?= $temp_item->ability_id.'_'.$temp_item->ability_token ?>" data-tooltip="<?= $temp_item_details_tooltip ?>" data-preload="<?= $temp_item_sprite['preload'] ?>" data-actualtarget="<?= $temp_item->ability_target ?>" data-target="<?= $temp_target ?>"><label class=""><?= $temp_item_sprite['markup'] ?><?= $temp_item_label ?></label></a><? }
          else { ?><a data-order="<?=$temp_order_counter?>" class="button button_disabled action_ability action_item ability_<?= $temp_item->ability_token ?> ability_type ability_type_<?= $temp_type_class ?> block_<?= $equipped_items_count ?>" type="button"><label class=""><?= $temp_item_sprite['markup'] ?><?= $temp_item_label ?></label></a><? }
          // Increment the order counter
          $temp_order_counter++;

          // All items appear in YELLOW
          //$temp_item->ability_type = $temp_item_type_backup;

        }
        $item_key++;
      }
      // If there were less than 8 abilities, fill in the empty spaces
      if ($equipped_items_count % 8 != 0){
        $temp_padding_amount = 8 - ($equipped_items_count % 8);
        $temp_last_key = $equipped_items_count + $temp_padding_amount;
        for ($i = $equipped_items_count; $i < $temp_last_key; $i++){
          // Display an empty button placeholder
          ?><a class="button action_ability action_item button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
        }
      }
    }
    ?>
  </div>
  <?
  // Display the back button by default
  ?><div class="sub_actions"><a data-order="<?=$temp_order_counter?>" class="button action_back" type="button" data-panel="battle"><label>Back</label></a></div><?
  // Increment the order counter
  $temp_order_counter++;
$actions_markup['item'] = trim(ob_get_clean());
$actions_markup['item'] = preg_replace('#\s+#', ' ', $actions_markup['item']);
?>