<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $allowed_edit_players, $allowed_edit_fields, $global_allow_editing;
global $allowed_edit_data_count, $allowed_edit_player_count, $first_player_token;
global $key_counter, $player_key, $player_counter, $player_rewards, $player_field_rewards, $player_item_rewards, $temp_player_totals, $player_options_markup;
global $mmrpg_database_robots, $mmrpg_database_items;
$session_token = mmrpg_game_token();

// If either fo empty, return error
if (empty($player_info)){ return 'error:player-empty'; }

// Collect the approriate database indexes
if (empty($mmrpg_database_robots)){ $mmrpg_database_robots = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token'); }
if (empty($mmrpg_database_items)){ $mmrpg_database_items = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_class = 'item' AND ability_flag_complete = 1;", 'ability_token'); }

// Define the quick-access variables for later use
$player_token = $player_info['player_token'];
if (!isset($first_player_token)){ $first_player_token = $player_token; }

// Define the player's image and size if not defined
$player_info['player_image'] = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
$player_info['player_image_size'] = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;

// Define the player's battle points total, battles complete, and other details
$player_info['player_points'] = mmrpg_prototype_player_points($player_token);
$player_info['player_battles_complete'] = mmrpg_prototype_battles_complete($player_token);
$player_info['player_battles_complete_total'] = mmrpg_prototype_battles_complete($player_token, false);
$player_info['player_battles_failure'] = mmrpg_prototype_battles_failure($player_token);
$player_info['player_battles_failure_total'] = mmrpg_prototype_battles_failure($player_token, false);
$player_info['player_robots_count'] = 0;
$player_info['player_abilities_count'] = mmrpg_prototype_abilities_unlocked($player_token);
$player_info['player_field_stars'] = mmrpg_prototype_stars_unlocked($player_token, 'field');
$player_info['player_fusion_stars'] = mmrpg_prototype_stars_unlocked($player_token, 'fusion');
$player_info['player_screw_counter'] = 0;
$player_info['player_heart_counter'] = 0;
// Define the player's experience points total
$player_info['player_experience'] = 0;
// Collect this player's current defined omega item list
if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
  //$debug_experience_sum = $player_token.' : ';
  foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $temp_player => $temp_player_info){
    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'])){
      $temp_player_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'];
      $temp_player_robot_settings = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'];
      if (empty($temp_player_robot_rewards) || empty($temp_player_robot_settings)){
        unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots']);
        unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots']);
        continue;
      }
      foreach ($temp_player_robot_rewards AS $temp_key => $temp_robot_info){
        if (empty($temp_robot_info['robot_token'])){
          unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_key]);
          unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_key]);
          continue;
        }
        $temp_robot_settings = $temp_player_robot_settings[$temp_robot_info['robot_token']];
        $temp_robot_rewards = $temp_player_robot_settings[$temp_robot_info['robot_token']];
        // If this robot is not owned by the player, skip it as it doesn't count towards their totals
        if (empty($temp_robot_settings['original_player']) && $temp_player != $player_token){ continue; }
        elseif (empty($temp_robot_settings['original_player'])){ $temp_robot_settings['original_player'] = $temp_player; }
        if ($temp_robot_settings['original_player'] != $player_token){ continue; }
        //$debug_experience_sum .= $temp_robot_info['robot_token'].', ';
        $player_info['player_robots_count']++;
        if (!empty($temp_robot_info['robot_level'])){ $player_info['player_experience'] += $temp_robot_info['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
        if (!empty($temp_robot_info['robot_experience'])){ $player_info['player_experience'] += $temp_robot_info['robot_experience']; }
      }
    }
  }
  //die($debug_experience_sum);
}

// Collect this player's current field selection from the omega session
$temp_session_key = $player_info['player_token'].'_target-robot-omega_prototype';
$player_info['target_robot_omega'] = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
$player_info['player_fields_current'] = array();
//die('<pre>$player_info[\'target_robot_omega\'] = '.print_r($player_info['target_robot_omega'], true).'</pre>');
if (count($player_info['target_robot_omega']) == 2){ $player_info['target_robot_omega'] = array_shift($player_info['target_robot_omega']); }
foreach ($player_info['target_robot_omega'] AS $key => $info){
  $field = mmrpg_field::get_index_info($info['field']);
  if (empty($field)){ continue; }
  $player_info['player_fields_current'][] = $field;
}

// Define this player's stat type boost for display purposes
$player_info['player_stat_type'] = '';
if (!empty($player_info['player_energy'])){ $player_info['player_stat_type'] = 'energy'; }
elseif (!empty($player_info['player_attack'])){ $player_info['player_stat_type'] = 'attack'; }
elseif (!empty($player_info['player_defense'])){ $player_info['player_stat_type'] = 'defense'; }
elseif (!empty($player_info['player_speed'])){ $player_info['player_stat_type'] = 'speed'; }

// Define whether or not field switching is enabled
$temp_allow_field_switch = mmrpg_prototype_complete($player_info['player_token']) || mmrpg_prototype_complete();

// Collect a temp robot object for printing items
if ($player_info['player_token'] == 'dr-light'){ $robot_info = mmrpg_robot::parse_index_info($mmrpg_database_robots['mega-man']); }
elseif ($player_info['player_token'] == 'dr-wily'){ $robot_info = mmrpg_robot::parse_index_info($mmrpg_database_robots['bass']); }
elseif ($player_info['player_token'] == 'dr-cossack'){ $robot_info = mmrpg_robot::parse_index_info($mmrpg_database_robots['proto-man']); }

// Start the output buffer
ob_start();

// DEBUG
//die(print_r($player_field_rewards, true));

  ?>
  <div class="event event_double event_<?= $player_key == $first_player_token ? 'visible' : 'hidden' ?>" data-token="<?=$player_info['player_token'].'_'.$player_info['player_token']?>">
    <div class="this_sprite sprite_left" style="height: 40px;">
      <? $temp_margin = -1 * ceil(($player_info['player_image_size'] - 40) * 0.5); ?>
      <div style="margin-top: <?= $temp_margin ?>px; margin-bottom: <?= $temp_margin * 3 ?>px; background-image: url(i/p/<?= !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'] ?>/mr<?= $player_info['player_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_player sprite_player_sprite sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?> sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>_mug player_status_active player_position_active"><?=$player_info['player_name']?></div>
    </div>
    <div class="header header_left player_type player_type_<?= !empty($player_info['player_stat_type']) ? $player_info['player_stat_type'] : 'none' ?>" style="margin-right: 0;"><?=$player_info['player_name']?>&#39;s Data <span class="player_type"><?= !empty($player_info['player_stat_type']) ? ucfirst($player_info['player_stat_type']) : 'Neutral' ?> Type</span></div>
    <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">
      <table class="full" style="margin-bottom: 5px;">
        <colgroup>
          <col width="48.5%" />
          <col width="1%" />
          <col width="48.5%" />
        </colgroup>
        <tbody>

          <tr>
            <td  class="right">
              <label style="display: block; float: left;">Name :</label>
              <span class="player_name player_type player_type_none"><?=$player_info['player_name']?></span>
            </td>
            <td class="center">&nbsp;</td>
            <td class="right">
              <label style="display: block; float: left;">Bonus :</label>
              <?
                // Display any special boosts this player has
                if (!empty($player_info['player_stat_type'])){ echo '<span class="player_name player_type player_type_'.$player_info['player_stat_type'].'">Robot '.ucfirst($player_info['player_stat_type']).' +'.$player_info['player_'.$player_info['player_stat_type']].'%</span>'; }
                else { echo '<span class="player_name player_type player_type_none">None</span>'; }
              ?>
            </td>
          </tr>

          <tr>
            <td  class="right">
              <label style="display: block; float: left;">Exp Points :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_experience']) ? 'cutter' : 'none' ?>"><?= number_format($player_info['player_experience'], 0, '.', ',') ?> EXP</span>
            </td>
            <td class="center">&nbsp;</td>
            <td  class="right">
              <label style="display: block; float: left;">Unlocked Robots :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_robots_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_robots_count'].' '.($player_info['player_robots_count'] == 1 ? 'Robot' : 'Robots') ?></span>
            </td>
          </tr>
          <tr>
            <td  class="right">
              <label style="display: block; float: left;">Battle Points :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_points']) ? 'cutter' : 'none' ?>"><?= number_format($player_info['player_points'], 0, '.', ',') ?> BP</span>
            </td>
            <td class="center">&nbsp;</td>
            <td  class="right">
              <label style="display: block; float: left;">Unlocked Abilities :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_abilities_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_abilities_count'].' '.($player_info['player_abilities_count'] == 1 ? 'Ability' : 'Abilities') ?></span>
            </td>
          </tr>

          <tr>
            <td  class="right">
              <label style="display: block; float: left;">Missions Completed :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_complete']) ? 'energy' : 'none' ?>"><?= $player_info['player_battles_complete'] ?> Missions</span>
            </td>
            <td class="center">&nbsp;</td>
            <td  class="right">
              <label style="display: block; float: left;">Total Victories :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_complete_total']) ? 'energy' : 'none' ?>"><?= $player_info['player_battles_complete_total'] ?> Victories</span>
            </td>
          </tr>
          <tr>
            <td  class="right">
              <label style="display: block; float: left;">Missions Failed :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_failure']) ? 'attack' : 'none' ?>"><?= $player_info['player_battles_failure'] ?> Missions</span>
            </td>
            <td class="center">&nbsp;</td>
            <td  class="right">
              <label style="display: block; float: left;">Total Defeats :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_failure_total']) ? 'attack' : 'none' ?>"><?= $player_info['player_battles_failure_total'] ?> Defeats</span>
            </td>
          </tr>

          <tr>
            <td  class="right">
              <? if(!empty($player_info['player_field_stars'])): ?>
              <label style="display: block; float: left;">Field Stars :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_field_stars']) ? 'electric' : 'empty' ?>"><?= $player_info['player_field_stars'].' '.($player_info['player_field_stars'] == 1 ? 'Star' : 'Stars') ?></span>
              <? else: ?>
              <label style="display: block; float: left; opacity: 0.5; filter: alpha(opacity=50); ">??? :</label>
              <span class="player_stat player_type player_type_empty" style=" opacity: 0.5; filter: alpha(opacity=50); ">0</span>
              <? endif; ?>
            </td>
            <td class="center">&nbsp;</td>
            <td  class="right">
              <? if(!empty($player_info['player_fusion_stars'])): ?>
              <label style="display: block; float: left;">Fusion Stars :</label>
              <span class="player_stat player_type player_type_<?= !empty($player_info['player_fusion_stars']) ? 'time' : 'empty' ?>"><?= $player_info['player_fusion_stars'].' '.($player_info['player_fusion_stars'] == 1 ? 'Star' : 'Stars') ?></span>
              <? else: ?>
              <label style="display: block; float: left; opacity: 0.5; filter: alpha(opacity=50); ">??? :</label>
              <span class="player_stat player_type player_type_empty" style=" opacity: 0.5; filter: alpha(opacity=50); ">0</span>
              <? endif; ?>
            </td>
          </tr>

        </tbody>
      </table>



      <? if(false && !empty($player_item_rewards)){ ?>

        <table class="full">
          <colgroup>
            <col width="100%" />
          </colgroup>
          <tbody>
            <tr>
              <td class="right" style="padding-top: 4px;">
              <label class="item_header">Player Items :</label>
                <div class="item_container" style="height: auto;">
                <?

                // Define the array to hold ALL the reward option markup
                $item_rewards_options = '';
                // Collect this player's item rewards and add them to the dropdown
                //$player_item_rewards = !empty($player_rewards['player_items']) ? $player_rewards['player_items'] : array();
                //if (!empty($player_item_rewards)){ sort($player_item_rewards); }

                // DEBUG
                //$debug_tokens = array();
                //foreach ($player_item_rewards AS $info){ $debug_tokens[] = $info['ability_token']; }
                //echo 'before:'.implode(',', array_keys($debug_tokens)).'<br />';

                // Sort the item index based on item group
                uasort($player_item_rewards, array('mmrpg_player', 'items_sort_for_editor'));

                // DEBUG
                //echo 'after:'.implode(',', array_keys($player_item_rewards)).'<br />';

                // DEBUG
                //$debug_tokens = array();
                //foreach ($player_item_rewards AS $info){ $debug_tokens[] = $info['ability_token']; }
                //echo 'after:'.implode(',', $debug_tokens).'<br />';

                // Dont' bother generating option dropdowns if editing is disabled
                if ($global_allow_editing){
                  $player_item_rewards_options = array();
                  foreach ($player_item_rewards AS $temp_item_key => $temp_item_info){
                    if (empty($temp_item_info['ability_token'])){ continue; }
                    $temp_token = $temp_item_info['ability_token'];
                    $temp_item_info = mmrpg_ability::parse_index_info($mmrpg_database_items[$temp_token]);
                    $temp_option_markup = mmrpg_ability::print_editor_option_markup($robot_info, $temp_item_info);
                    if (!empty($temp_option_markup)){ $player_item_rewards_options[] = $temp_option_markup; }
                  }
                  $player_item_rewards_options = '<optgroup label="Player Items">'.implode('', $player_item_rewards_options).'</optgroup>';
                  $item_rewards_options .= $player_item_rewards_options;
                  /*
                  // Collect this robot's item rewards and add them to the dropdown
                  $player_item_rewards = !empty($player_rewards['player_items']) ? $player_rewards['player_items'] : array();
                  $player_item_settings = !empty($player_settings['player_items']) ? $player_settings['player_items'] : array();
                  foreach ($player_item_settings AS $token => $info){ if (empty($player_item_rewards[$token])){ $player_item_rewards[$token] = $info; } }
                  if (!empty($player_item_rewards)){ sort($player_item_rewards); }
                  $player_item_rewards_options = array();
                  foreach ($player_item_rewards AS $temp_item_info){
                    if (empty($temp_item_info['ability_token'])){ continue; }
                    $temp_token = $temp_item_info['ability_token'];
                    $temp_item_info = mmrpg_ability::parse_index_info($mmrpg_database_items[$temp_token]);
                    $temp_option_markup = mmrpg_ability::print_editor_option_markup($robot_info, $temp_item_info);
                    if (!empty($temp_option_markup)){ $player_item_rewards_options[] = $temp_option_markup; }
                  }
                  $player_item_rewards_options = '<optgroup label="Player Items">'.implode('', $player_item_rewards_options).'</optgroup>';
                  $item_rewards_options .= $player_item_rewards_options;
                  */

                  // Add an option at the bottom to remove the ability
                  $item_rewards_options .= '<optgroup label="Item Actions">';
                  $item_rewards_options .= '<option value="" title="">- Remove Item -</option>';
                  $item_rewards_options .= '</optgroup>';
                  }

                // Loop through the robot's current items and list them one by one
                $empty_item_counter = 0;
                $temp_string = array();
                $temp_inputs = array();
                $item_key = 0;
                if (!empty($player_info['player_items_current'])){
                  // DEBUG
                  //echo 'robot-ability:';
                  foreach ($player_info['player_items_current'] AS $key => $player_item){
                    if (empty($player_item['ability_token'])){ continue; }
                    elseif ($player_item['ability_token'] == '*'){ continue; }
                    elseif ($player_item['ability_token'] == 'ability'){ continue; }
                    elseif ($item_key > 7){ continue; }
                    $this_item = mmrpg_ability::parse_index_info($mmrpg_database_items[$player_item['ability_token']]);
                    if (empty($this_item)){ continue; }
                    $this_item_token = $this_item['ability_token'];
                    $this_item_name = $this_item['ability_name'];
                    $this_item_type = !empty($this_item['ability_type']) ? $this_item['ability_type'] : false;
                    $this_item_type2 = !empty($this_item['ability_type2']) ? $this_item['ability_type2'] : false;
                    if (!empty($this_item_type) && !empty($mmrpg_index['types'][$this_item_type])){
                      $this_item_type = $mmrpg_index['types'][$this_item_type]['type_name'].' Type';
                      if (!empty($this_item_type2) && !empty($mmrpg_index['types'][$this_item_type2])){
                        $this_item_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_item_type2]['type_name'].' Type', $this_item_type);
                      }
                    } else {
                      $this_item_type = '';
                    }
                    $this_item_energy = isset($this_item['ability_energy']) ? $this_item['ability_energy'] : 4;
                    $this_item_damage = !empty($this_item['ability_damage']) ? $this_item['ability_damage'] : 0;
                    $this_item_damage2 = !empty($this_item['ability_damage2']) ? $this_item['ability_damage2'] : 0;
                    $this_item_damage_percent = !empty($this_item['ability_damage_percent']) ? true : false;
                    $this_item_damage2_percent = !empty($this_item['ability_damage2_percent']) ? true : false;
                    if ($this_item_damage_percent && $this_item_damage > 100){ $this_item_damage = 100; }
                    if ($this_item_damage2_percent && $this_item_damage2 > 100){ $this_item_damage2 = 100; }
                    $this_item_recovery = !empty($this_item['ability_recovery']) ? $this_item['ability_recovery'] : 0;
                    $this_item_recovery2 = !empty($this_item['ability_recovery2']) ? $this_item['ability_recovery2'] : 0;
                    $this_item_recovery_percent = !empty($this_item['ability_recovery_percent']) ? true : false;
                    $this_item_recovery2_percent = !empty($this_item['ability_recovery2_percent']) ? true : false;
                    if ($this_item_recovery_percent && $this_item_recovery > 100){ $this_item_recovery = 100; }
                    if ($this_item_recovery2_percent && $this_item_recovery2 > 100){ $this_item_recovery2 = 100; }
                    $this_item_accuracy = !empty($this_item['ability_accuracy']) ? $this_item['ability_accuracy'] : 0;
                    $this_item_description = !empty($this_item['ability_description']) ? $this_item['ability_description'] : '';
                    $this_item_description = str_replace('{DAMAGE}', $this_item_damage, $this_item_description);
                    $this_item_description = str_replace('{RECOVERY}', $this_item_recovery, $this_item_description);
                    $this_item_description = str_replace('{DAMAGE2}', $this_item_damage2, $this_item_description);
                    $this_item_description = str_replace('{RECOVERY2}', $this_item_recovery2, $this_item_description);
                    $this_item_title = mmrpg_ability::print_editor_title_markup($robot_info, $this_item);
                    $this_item_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_item_title));
                    $this_item_title_tooltip = htmlentities($this_item_title, ENT_QUOTES, 'UTF-8');
                    $this_item_title_html = str_replace(' ', '&nbsp;', $this_item_name);
                    $temp_select_options = str_replace('value="'.$this_item_token.'"', 'value="'.$this_item_token.'" selected="selected" disabled="disabled"', $item_rewards_options);
                    $this_item_title_html = '<label style="background-image: url(i/a/'.$this_item_token.'/il40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_item_title_html.'</label>';
                    if ($global_allow_editing){ $this_item_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'">'.$temp_select_options.'</select>'; }
                    $temp_string[] = '<a class="ability_name ability_type ability_type_'.(!empty($this_item['ability_type']) ? $this_item['ability_type'] : 'none').(!empty($this_item['ability_type2']) ? '_'.$this_item['ability_type2'] : '').'" style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-item="'.$this_item_token.'" title="'.$this_item_title_plain.'" data-tooltip="'.$this_item_title_tooltip.'">'.$this_item_title_html.'</a>';
                    $item_key++;
                  }

                  if ($item_key <= 7){
                    for ($item_key; $item_key <= 7; $item_key++){
                      $empty_item_counter++;
                      if ($empty_item_counter >= 2){ $empty_item_disable = true; }
                      else { $empty_item_disable = false; }
                      $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $item_rewards_options);
                      $this_item_title_html = '<label>-</label>';
                      if ($global_allow_editing){ $this_item_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" '.($empty_item_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                      $temp_string[] = '<a class="ability_name " style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_item_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-item="" title="" data-tooltip="">'.$this_item_title_html.'</a>';
                    }
                  }


                } else {

                  for ($item_key = 0; $item_key <= 7; $item_key++){
                    $empty_item_counter++;
                    if ($empty_item_counter >= 2){ $empty_item_disable = true; }
                    else { $empty_item_disable = false; }
                    $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $item_rewards_options);
                    $this_item_title_html = '<label>-</label>';
                    if ($global_allow_editing){ $this_item_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_item_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                    $temp_string[] = '<a class="ability_name " style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_item_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="">'.$this_item_title_html.'</a>';
                  }

                }
                // DEBUG
                //echo 'temp-string:';
                echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                // DEBUG
                //echo '<br />temp-inputs:';
                echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';
                // DEBUG
                //echo '<br />';



                ?>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

      <? } ?>

      <? if(!empty($player_field_rewards) && mmrpg_prototype_complete($player_info['player_token'])){ ?>

        <table class="full">
          <colgroup>
            <col width="100%" />
          </colgroup>
          <tbody>
            <tr>
              <td class="right" style="padding-top: 4px;">
                <label class="field_header"><?= $global_allow_editing ? 'Edit ' : '' ?>Player Fields :</label>
                <div class="field_container" style="height: auto;">
                <?

                // Define the array to hold ALL the reward option markup
                $field_rewards_options = '';
                // Collect this player's field rewards and add them to the dropdown
                //$player_field_rewards = !empty($player_rewards['player_fields']) ? $player_rewards['player_fields'] : array();
                //if (!empty($player_field_rewards)){ sort($player_field_rewards); }

                // DEBUG
                //echo 'start:player_field_rewards:<pre style="font-size: 80%;">'.print_r($player_field_rewards, true).'</pre><br />';

                // DEBUG
                //echo 'before:player_field_rewards(keys):'.implode(',', array_keys($player_field_rewards)).'<br />';

                // DEBUG
                //$debug_tokens = array();
                //foreach ($player_field_rewards AS $info){ $debug_tokens[] = $info['field_token']; }
                //echo 'before:player_field_rewards(field_tokens):'.implode(',', array_values($debug_tokens)).'<br />';

                // Sort the field index based on field number
                uasort($player_field_rewards, array('mmrpg_player', 'fields_sort_for_editor'));

                // DEBUG
                //echo 'after:player_field_rewards(keys):'.implode(',', array_keys($player_field_rewards)).'<br />';

                // DEBUG
                //$debug_tokens = array();
                //foreach ($player_field_rewards AS $info){ $debug_tokens[] = $info['field_token']; }
                //echo 'after:player_field_rewards(field_tokens):'.implode(',', array_values($debug_tokens)).'<br />';

                // Don't bother generating the option markup if disabled editing
                if ($global_allow_editing){
                  // Define the field group index for displau
                  $temp_group_index = array('MMRPG' => 'Mega Man RPG Fields', 'MM00' => 'Mega Man Bonus Fields', 'MM01' => 'Mega Man 1 Fields', 'MM02' => 'Mega Man 2 Fields', 'MM03' => 'Mega Man 3 Fields', 'MM04' => 'Mega Man 4 Fields', 'MM05' => 'Mega Man 5 Fields', 'MM06' => 'Mega Man 6 Fields', 'MM07' => 'Mega Man 7 Fields', 'MM08' => 'Mega Man 8 Fields', 'MM09' => 'Mega Man 9 Fields', 'MM10' => 'Mega Man 10 Fields');
                  // Loop through the group index and display any fields that match
                  $player_field_rewards_backup = $player_field_rewards;
                  foreach ($temp_group_index AS $group_key => $group_name){
                    $player_field_rewards_options = array();
                    foreach ($player_field_rewards_backup AS $temp_field_key => $temp_field_info){
                      if (empty($temp_field_info['field_game']) || $temp_field_info['field_game'] != $group_key){ continue; }
                      $temp_option_markup = mmrpg_field::print_editor_option_markup($player_info, $temp_field_info);
                      if (!empty($temp_option_markup)){ $player_field_rewards_options[] = $temp_option_markup; }
                      unset($player_field_rewards_backup[$temp_field_key]);
                    }
                    if (empty($player_field_rewards_options)){ continue; }
                    $player_field_rewards_options = '<optgroup label="'.$group_name.'">'.implode('', $player_field_rewards_options).'</optgroup>';
                    $field_rewards_options .= $player_field_rewards_options;
                  }

                }



                // Add an option at the bottom to remove the field
                //$field_rewards_options .= '<optgroup label="Field Actions">';
                //$field_rewards_options .= '<option value="" title="">- Remove Field -</option>';
                //$field_rewards_options .= '</optgroup>';

                // Loop through the player's current fields and list them one by one
                $empty_field_counter = 0;
                $temp_string = array();
                $temp_inputs = array();
                $field_key = 0;
                if (!empty($player_info['player_fields_current'])){
                  // DEBUG
                  //echo 'player-field:';
                  $mmrpg_field_index = mmrpg_field::get_index();
                  $player_info['player_fields_current'] = $player_info['player_fields_current']; //array_reverse($player_info['player_fields_current']);
                  foreach ($player_info['player_fields_current'] AS $player_field){
                    if ($player_field['field_token'] == '*'){ continue; }
                    elseif (!isset($mmrpg_field_index[$player_field['field_token']])){ continue; }
                    elseif ($field_key > 7){ continue; }

                    $this_field = mmrpg_field::parse_index_info($mmrpg_field_index[$player_field['field_token']]);
                    $this_field_token = $this_field['field_token'];
                    $this_robot_token = $this_field['field_master'];
                    $this_robot = mmrpg_robot::parse_index_info($mmrpg_database_robots[$this_robot_token]);
                    $this_field_name = $this_field['field_name'];
                    $this_field_type = !empty($this_field['field_type']) ? $this_field['field_type'] : false;
                    $this_field_type2 = !empty($this_field['field_type2']) ? $this_field['field_type2'] : false;
                    if (!empty($this_field_type) && !empty($mmrpg_index['types'][$this_field_type])){
                      $this_field_type = $mmrpg_index['types'][$this_field_type]['type_name'].' Type';
                      if (!empty($this_field_type2) && !empty($mmrpg_index['types'][$this_field_type2])){
                        $this_field_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_field_type2]['type_name'].' Type', $this_field_type);
                      }
                    } else {
                      $this_field_type = '';
                    }
                    $this_field_description = !empty($this_field['field_description']) ? $this_field['field_description'] : '';
                    $this_field_title = mmrpg_field::print_editor_title_markup($player_info, $this_field);
                    $this_field_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_field_title));
                    $this_field_title_tooltip = htmlentities($this_field_title, ENT_QUOTES, 'UTF-8');
                    $this_field_title_html = str_replace(' ', '&nbsp;', $this_field_name);
                    $temp_select_options = str_replace('value="'.$this_field_token.'"', 'value="'.$this_field_token.'" selected="selected" disabled="disabled"', $field_rewards_options);
                    $temp_field_type_class = 'field_type_'.(!empty($this_field['field_type']) ? $this_field['field_type'] : 'none').(!empty($this_field['field_type2']) ? '_'.$this_field['field_type2'] : '');
                    if ($global_allow_editing && $temp_allow_field_switch){ $this_field_title_html = '<label class="field_type  '.$temp_field_type_class.'" style="">'.$this_field_title_html.'</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'">'.$temp_select_options.'</select>'; }
                    elseif (!$global_allow_editing && $temp_allow_field_switch){ $this_field_title_html = '<label class="field_type  '.$temp_field_type_class.'" style="cursor: default !important;">'.$this_field_title_html.'</label>'; }
                    else { $this_field_title_html = '<label class="field_type '.$temp_field_type_class.'" style="cursor: default !important;">'.$this_field_title_html.'</label>'; }
                    $temp_string[] = '<a class="field_name field_type '.$temp_field_type_class.'" style="background-image: url(i/f/'.$this_field_token.'/bfp.png?'.MMRPG_CONFIG_CACHE_DATE.'); '.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$temp_allow_field_switch || !$global_allow_editing ? 'cursor: default !important; ' : '').(!$temp_allow_field_switch ? 'opacity: 0.50; filter: alpha(opacity=50); ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="'.$this_field_token.'" data-tooltip="'.$this_field_title_tooltip.'">'.$this_field_title_html.'</a>';

                    $field_key++;
                  }

                  if ($field_key <= 7){
                    for ($field_key; $field_key <= 7; $field_key++){
                      $empty_field_counter++;
                      if ($empty_field_counter >= 2){ $empty_field_disable = true; }
                      else { $empty_field_disable = false; }
                      $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $field_rewards_options);
                      $this_field_title_html = '<label>-</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" '.($empty_field_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>';
                      $temp_string[] = '<a class="field_name " style="'.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_field_disable ? 'opacity:0.25; ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="" title="">'.$this_field_title_html.'</a>';
                    }
                  }


                } else {
                  for ($field_key = 0; $field_key <= 7; $field_key++){
                    $empty_field_counter++;
                    if ($empty_field_counter >= 2){ $empty_field_disable = true; }
                    else { $empty_field_disable = false; }
                    $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $field_rewards_options);
                    $this_field_title_html = '<label>-</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" '.($empty_field_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>';
                    $temp_string[] = '<a class="field_name " style="'.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_field_disable ? 'opacity:0.25; ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="" title="">'.$this_field_title_html.'</a>';
                  }

                }
                // DEBUG
                //echo 'temp-string:';
                echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                // DEBUG
                //echo '<br />temp-inputs:';
                echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';
                // DEBUG
                //echo '<br />';
                // Collect the available star counts for this player
                $temp_star_counts = mmrpg_prototype_player_stars_available($player_token);
                ?>
                <div class="field_stars">
                  <label class="label">stars</label>
                  <span class="star star_field" data-star="field"><?= $temp_star_counts['field'] ?> field</span>
                  <span class="star star_fusion" data-star="fusion"><?= $temp_star_counts['fusion'] ?> fusion</span>
                </div>
                <?
                // Print the sort wrapper and options if allowed
                if ($global_allow_editing){
                  ?>
                  <div class="field_tools">
                    <label class="label">tools</label>
                    <a class="tool tool_shuffle" data-tool="shuffle" data-player="<?= $player_token ?>">shuffle</a>
                    <a class="tool tool_randomize" data-tool="randomize" data-player="<?= $player_token ?>">randomize</a>
                  </div>
                  <?
                }
                ?>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

      <? }?>


    </div>
  </div>
  <?
  $key_counter++;

// Collect the outbut buffer contents
$this_markup = trim(ob_get_clean());

?>