<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
global $key_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
global $mmrpg_database_abilities, $mmrpg_database_items;
global $session_token;
// Collect values for potentially missing global variables
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

// If either fo empty, return error
if (empty($player_info)){ return 'error:player-empty'; }
if (empty($robot_info)){ return 'error:robot-empty'; }

// Collect the approriate database indexes
if (empty($mmrpg_database_abilities)){ $mmrpg_database_abilities = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_class <> 'item';", 'ability_token'); }
if (empty($mmrpg_database_items)){ $mmrpg_database_items = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_class = 'item';", 'ability_token'); }

// Define the quick-access variables for later use
$player_token = $player_info['player_token'];
$robot_token = $robot_info['robot_token'];
if (!isset($first_robot_token)){ $first_robot_token = $robot_token; }

// Start the output buffer
ob_start();

  // Check how many robots this player has and see if they should be able to transfer
  $counter_player_robots = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : false;
  $counter_player_missions = mmrpg_prototype_battles_complete($player_info['player_token']);
  $allow_player_selector = $allowed_edit_player_count > 1 && $counter_player_missions > 0 ? true : false;

  // If this player has fewer robots than any other player
  //$temp_flag_most_robots = true;
  //foreach ($temp_robot_totals AS $temp_player => $temp_total){
    //if ($temp_player == $player_token){ continue; }
    //elseif ($temp_total > $counter_player_robots){ $allow_player_selector = false; }
  //}

  // Update the robot key to the current counter
  $robot_key = $key_counter;
  // Make a backup of the player selector
  $allow_player_selector_backup = $allow_player_selector;
  // Collect or define the image size
  $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
  $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
  $robot_image_size_text = $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'];
  $robot_image_offset_top = -1 * $robot_image_offset;
  // Collect the robot level and experience
  $robot_info['robot_level'] = mmrpg_prototype_robot_level($player_info['player_token'], $robot_info['robot_token']);
  $robot_info['robot_experience'] = mmrpg_prototype_robot_experience($player_info['player_token'], $robot_info['robot_token']);
  // Collect the rewards for this robot
  $robot_rewards = mmrpg_prototype_robot_rewards($player_token, $robot_token);
  // Collect the settings for this robot
  $robot_settings = mmrpg_prototype_robot_settings($player_token, $robot_token);
  // Collect the database for this robot
  $robot_database = !empty($player_robot_database[$robot_token]) ? $player_robot_database[$robot_token] : array(); //mmrpg_prototype_robot_database($robot_token);
  // Collect the robot ability core if it exists
  $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
  // Check if this robot has the copy shot ability
  $robot_flag_copycore = $robot_ability_core == 'copy' ? true : false;

  // Make backups of the robot's original stats before rewards
  $robot_info['robot_energy_index'] = $robot_info['robot_energy'];
  $robot_info['robot_weapons_index'] = $robot_info['robot_weapons'];
  $robot_info['robot_attack_index'] = $robot_info['robot_attack'];
  $robot_info['robot_defense_index'] = $robot_info['robot_defense'];
  $robot_info['robot_speed_index'] = $robot_info['robot_speed'];

  // Collect this robot's ability rewards and add them to the dropdown
  $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
  $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
  foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }

  // If the robot's level is greater than one, apply stat boosts
  if ($robot_info['robot_level'] > 1){
    // Create the temp level by subtracting one (so we don't have level 1 boosts)
    $temp_level = $robot_info['robot_level'] - 1;
    // Update the robot energy with a small boost based on experience level
    $robot_info['robot_energy'] = $robot_info['robot_energy'] + ceil($temp_level * (0.05 * $robot_info['robot_energy']));
    // Update the robot attack with a small boost based on experience level
    $robot_info['robot_attack'] = $robot_info['robot_attack'] + ceil($temp_level * (0.05 * $robot_info['robot_attack']));
    // Update the robot defense with a small boost based on experience level
    $robot_info['robot_defense'] = $robot_info['robot_defense'] + ceil($temp_level * (0.05 * $robot_info['robot_defense']));
    // Update the robot speed with a small boost based on experience level
    $robot_info['robot_speed'] = $robot_info['robot_speed'] + ceil($temp_level * (0.05 * $robot_info['robot_speed']));
  }

  // Make backups of the robot's original stats before rewards
  $robot_info['robot_energy_base'] = $robot_info['robot_energy'];
  $robot_info['robot_attack_base'] = $robot_info['robot_attack'];
  $robot_info['robot_defense_base'] = $robot_info['robot_defense'];
  $robot_info['robot_speed_base'] = $robot_info['robot_speed'];

  // Apply any stat rewards for the robot's attack
  if (!empty($robot_rewards['robot_attack'])){
    $robot_info['robot_attack'] += $robot_rewards['robot_attack'];
  }
  // Apply any stat rewards for the robot's defense
  if (!empty($robot_rewards['robot_defense'])){
    $robot_info['robot_defense'] += $robot_rewards['robot_defense'];
  }
  // Apply any stat rewards for the robot's speed
  if (!empty($robot_rewards['robot_speed'])){
    $robot_info['robot_speed'] += $robot_rewards['robot_speed'];
  }

  // Make backups of the robot's original stats before rewards
  $robot_info['robot_attack_rewards'] = $robot_info['robot_attack'] - $robot_info['robot_attack_base'];
  $robot_info['robot_defense_rewards'] = $robot_info['robot_defense'] - $robot_info['robot_defense_base'];
  $robot_info['robot_speed_rewards'] = $robot_info['robot_speed'] - $robot_info['robot_speed_base'];

  // Only apply player bonuses if the robot is with it's original player
  //if (!empty($robot_info['original_player']) && $robot_info['original_player'] == $player_info['player_token']){}

  // Apply stat bonuses to this robot based on its current player's own stats
  if (true){

    // Apply any player special for the robot's attack
    if (!empty($player_info['player_attack'])){
      $robot_info['robot_attack'] += ceil($robot_info['robot_attack'] * ($player_info['player_attack'] / 100));
    }
    // Apply any player special for the robot's defense
    if (!empty($player_info['player_defense'])){
      $robot_info['robot_defense'] += ceil($robot_info['robot_defense'] * ($player_info['player_defense'] / 100));
    }
    // Apply any player special for the robot's speed
    if (!empty($player_info['player_speed'])){
      $robot_info['robot_speed'] += ceil($robot_info['robot_speed'] * ($player_info['player_speed'] / 100));
    }

  }

  // Make backups of the robot's original stats before rewards
  $robot_info['robot_attack_player'] = $robot_info['robot_attack'] - $robot_info['robot_attack_rewards'] - $robot_info['robot_attack_base'];
  $robot_info['robot_defense_player'] = $robot_info['robot_defense'] - $robot_info['robot_defense_rewards'] - $robot_info['robot_defense_base'];
  $robot_info['robot_speed_player'] = $robot_info['robot_speed'] - $robot_info['robot_speed_rewards'] - $robot_info['robot_speed_base'];

  // Limit stat digits for display purposes
  if ($robot_info['robot_energy'] > MMRPG_SETTINGS_STATS_MAX){ $robot_info['robot_energy'] = MMRPG_SETTINGS_STATS_MAX; }
  if ($robot_info['robot_attack'] > MMRPG_SETTINGS_STATS_MAX){ $robot_info['robot_attack'] = MMRPG_SETTINGS_STATS_MAX; }
  if ($robot_info['robot_defense'] > MMRPG_SETTINGS_STATS_MAX){ $robot_info['robot_defense'] = MMRPG_SETTINGS_STATS_MAX; }
  if ($robot_info['robot_speed'] > MMRPG_SETTINGS_STATS_MAX){ $robot_info['robot_speed'] = MMRPG_SETTINGS_STATS_MAX; }

  // Collect the summon count from the session if it exists
  $robot_info['robot_summoned'] = !empty($robot_database['robot_summoned']) ? $robot_database['robot_summoned'] : 0;

  // Collect the alt images if there are any that are unlocked
  $robot_alt_count = 1 + (!empty($robot_info['robot_image_alts']) ? count($robot_info['robot_image_alts']) : 0);
  $robot_alt_options = array();
  if (!empty($robot_info['robot_image_alts'])){
    foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
      if ($robot_info['robot_summoned'] < $alt_info['summons']){ continue; }
      $robot_alt_options[] = $alt_info['token'];
    }
  }

  // Collect the current unlock image token for this robot
  $robot_image_unlock_current = 'base';
  if (!empty($robot_settings['robot_image']) && strstr($robot_settings['robot_image'], '_')){
    list($token, $robot_image_unlock_current) = explode('_', $robot_settings['robot_image']);
  }

  // Define the offsets for the image tokens based on count
  $token_first_offset = 2;
  $token_other_offset = 6;
  if ($robot_alt_count == 1){ $token_first_offset = 17; }
  elseif ($robot_alt_count == 3){ $token_first_offset = 10; }

  // Loop through and generate the robot image display token markup
  $robot_image_unlock_tokens = '';
  $temp_total_alts_count = 0;
  for ($i = 0; $i < 6; $i++){
    $temp_enabled = true;
    $temp_active = false;
    if ($i + 1 > $robot_alt_count){ break; }
    if ($i > 0 && !isset($robot_alt_options[$i - 1])){ $temp_enabled = false; }
    if ($temp_enabled && $i == 0 && $robot_image_unlock_current == 'base'){ $temp_active = true; }
    elseif ($temp_enabled && $i >= 1 && $robot_image_unlock_current == $robot_alt_options[$i - 1]){ $temp_active = true; }
    $robot_image_unlock_tokens .= '<span class="token token_'.($temp_enabled ? 'enabled' : 'disabled').' '.($temp_active ? 'token_active' : '').'" style="left: '.($token_first_offset + ($i * $token_other_offset)).'px;">&bull;</span>';
    $temp_total_alts_count += 1;
  }
  $temp_unlocked_alts_count = count($robot_alt_options) + 1;
  $temp_image_alt_title = '';
  if ($temp_total_alts_count > 1){
    $temp_image_alt_title = '<strong>'.$temp_unlocked_alts_count.' / '.$temp_total_alts_count.' Outfits Unlocked</strong><br />';
    //$temp_image_alt_title .= '<span style="font-size: 90%;">';
      $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$robot_info['robot_name'].'</span><br />';
      foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
        if ($robot_info['robot_summoned'] >= $alt_info['summons']){
          $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$alt_info['name'].'</span><br />';
        } else {
          $temp_image_alt_title .= '&#9702; <span style="font-size: 90%;">???</span><br />';
        }
      }
    //$temp_image_alt_title .= '</span>';
    $temp_image_alt_title = htmlentities($temp_image_alt_title, ENT_QUOTES, 'UTF-8', true);
  }

  // Define whether or not this robot has coreswap enabled
  $temp_allow_coreswap = $robot_info['robot_level'] >= 100 ? true : false;

  //echo $robot_info['robot_token'].' robot_image_unlock_current = '.$robot_image_unlock_current.' | robot_alt_options = '.implode(',',array_keys($robot_alt_options)).'<br />';

  ?>
  <div class="event event_double event_<?= $robot_key == $first_robot_token ? 'visible' : 'hidden' ?> <?= false && $robot_info['robot_level'] >= 100 && $robot_info['robot_core'] != 'copy' ? 'event_has_subcore' : '' ?>" data-token="<?=$player_info['player_token'].'_'.$robot_info['robot_token']?>" data-player="<?= $player_info['player_token'] ?>" data-robot="<?= $robot_info['robot_token'] ?>" data-types="<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none' ?>">

    <div class="this_sprite sprite_left event_robot_mugshot" style="">
      <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
      <div class="sprite_wrapper robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="width: 33px;">
        <div class="sprite_wrapper robot_type robot_type_empty" style="position: absolute; width: 27px; height: 34px; left: 2px; top: 2px;"></div>
        <div style="left: <?= $temp_offset ?>; bottom: <?= $temp_offset ?>; background-image: url(i/r/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/mr<?= $robot_info['robot_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_mug robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
      </div>
    </div>

    <? if(false && $robot_info['robot_level'] >= 100 && $robot_info['robot_core'] != 'copy'): ?>
      <div class="this_sprite sprite_left event_robot_core2 ability_type ability_type_<?= !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : 'none' ?>" style="" >
        <div class="sprite_wrapper" style="">
          <? if($global_allow_editing): ?>
            <a class="robot_core2 <?= in_array($robot_token, $player_robot_favourites) ? 'robot_core_active ' : '' ?>" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" title="Equip Subcore?">
              <? if(!empty($robot_info['robot_core2'])): ?>
                <span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(i/a/item-core-<?= !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : 'none' ?>/il40.png);"></span>
              <? endif; ?>
            </a>
          <? else: ?>
            <span class="robot_core2 <?= in_array($robot_token, $player_robot_favourites) ? 'robot_core_active ' : '' ?>">
              <span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(i/a/item-core-<?= !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : 'none' ?>/il40.png);"></span>
            </span>
          <? endif; ?>
        </div>
      </div>
    <? endif; ?>

    <div class="this_sprite sprite_left event_robot_images" style="">
      <? if($global_allow_editing && !empty($robot_alt_options)): ?>
        <a class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
          <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
          <span class="sprite_wrapper" style="">
            <?= $robot_image_unlock_tokens ?>
            <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(i/r/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sr<?= $robot_info['robot_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
          </span>
        </a>
      <? else: ?>
        <span class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
          <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
          <span class="sprite_wrapper" style="">
            <?= $robot_image_unlock_tokens ?>
            <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(i/r/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sr<?= $robot_info['robot_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
          </span>
        </span>
      <? endif; ?>
    </div>

    <div class="this_sprite sprite_left event_robot_summons" style="">
      <div class="robot_summons">
        <span class="summons_count"><?= $robot_info['robot_summoned'] ?></span>
        <span class="summons_label"><?= $robot_info['robot_summoned'] == 1 ? 'Summon' : 'Summons' ?></span>
      </div>
    </div>

    <div class="this_sprite sprite_left event_robot_favourite" style="" >
      <? if($global_allow_editing): ?>
        <a class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" title="Toggle Favourite?">&hearts;</a>
      <? else: ?>
        <span class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>">&hearts;</span>
      <? endif; ?>
    </div>

    <?

    // Define the placehodler cells for the empty column in case it's needed
    ob_start();
    ?>
    <td class="right">
      <label style="display: block; float: left; color: #696969;">??? :</label>
      <span class="robot_stat" style="color: #696969; font-weight: normal;">???</span>
    </td>
    <?
    $empty_column_placeholder = ob_get_clean();

    // Define an array to hold all the data in the left and right columns
    $left_column_markup = array();
    $right_column_markup = array();

    // Check to see if the player has unlocked the ability to swap players
    $temp_player_swap_unlocked = mmrpg_prototype_player_unlocked('dr-wily'); // && mmrpg_prototype_event_unlocked('dr-wily', 'chapter_one_complete');
    // If this player has unlocked the ability to let robots swap players
    if ($temp_player_swap_unlocked){
      ob_start();
      ?>
      <td class="player_select_block right">
        <?
        $player_style = '';
        $robot_info['original_player'] = !empty($robot_info['original_player']) ? $robot_info['original_player'] : $player_info['player_token'];
        if ($player_info['player_token'] != $robot_info['original_player']){
          if ($counter_player_robots > 1){ $allow_player_selector = true; }
        }
        ?>
        <? if($robot_info['original_player'] != $player_info['player_token']): ?>
          <label title="<?= 'Transferred from Dr. '.ucfirst(str_replace('dr-', '', $robot_info['original_player'])) ?>"  class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
        <? else: ?>
          <label class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
        <? endif; ?>

        <?if($global_allow_editing && $allow_player_selector):?>
          <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>"><label style="background-image: url(i/p/<?=$player_info['player_token']?>/ml40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?=$player_info['player_name']?><span class="arrow">&#8711;</span></label></a>
        <?elseif(!$global_allow_editing && $allow_player_selector):?>
          <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="cursor: default; "><label style="background-image: url(i/p/<?=$player_info['player_token']?>/ml40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); cursor: default; "><?=$player_info['player_name']?></label></a>
        <?else:?>
          <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(i/p/<?=$player_info['player_token']?>/ml40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?=$player_info['player_name']?></label></a>
        <?endif;?>
      </td>
      <?
      $left_column_markup[] = ob_get_clean();
    }

    // Check to see if the player has unlocked the ability to hold items
    $temp_item_hold_unlocked = mmrpg_prototype_event_complete('completed-chapter_dr-cossack_one');
    $current_item_token = '';
    // If this player has unlocked the ability to let robots hold items
    if ($temp_item_hold_unlocked){
      // Collect the currently held item and token, if available
      $current_item_token = !empty($robot_info['robot_item']) ? $robot_info['robot_item'] : '';
      $current_item_info = !empty($mmrpg_database_items[$current_item_token]) ? $mmrpg_database_items[$current_item_token] : array();
      $current_item_name = !empty($current_item_info['ability_name']) ? $current_item_info['ability_name'] : 'No Item';
      $current_item_image = !empty($current_item_info['ability_image']) ? $current_item_info['ability_image'] : $current_item_token;
      $current_item_type = !empty($current_item_info['ability_type']) ? $current_item_info['ability_type'] : 'none';
      if (!empty($current_item_info['ability_type2'])){ $current_item_type = $current_item_type != 'none' ?  $current_item_type.'_'.$current_item_info['ability_type2'] : $current_item_info['ability_type2']; }
      if (empty($current_item_info)){ $current_item_token = ''; $current_item_image = 'ability'; }
      ob_start();
      ?>
      <td  class="right">
        <label style="display: block; float: left;">Item:</label>
        <? if($global_allow_editing): ?>
          <a title="Change Item?" class="item_name type <?= $current_item_type ?>"><label style="background-image: url(i/a/<?= $current_item_image ?>/il40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $current_item_name ?><span class="arrow">&#8711;</span></label></a>
        <? else: ?>
          <a class="item_name type <?= $current_item_type ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(i/a/<?= $current_item_image ?>/il40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $current_item_name ?></label></a>
        <? endif; ?>
      </td>
      <?
      $left_column_markup[] = ob_get_clean();
    }

    // Define the markup for the weakness
    if (true){
      ob_start();
      ?>
      <td  class="right">
        <label style="display: block; float: left;">Weaknesses :</label>
        <?
        if (!empty($robot_info['robot_weaknesses'])){
          $temp_string = array();
          foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
            $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.(!empty($robot_weakness) ? $robot_weakness : 'none').'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</span>';
          }
          echo implode(' ', $temp_string);
        } else {
          echo '<span class="robot_weakness">None</span>';
        }
        ?>
      </td>
      <?
      $left_column_markup[] = ob_get_clean();
    }

    // Define the markup for the resistance
    if (true){
      ob_start();
      ?>
      <td  class="right">
        <label style="display: block; float: left;">Resistances :</label>
        <?
        if (!empty($robot_info['robot_resistances'])){
          $temp_string = array();
          foreach ($robot_info['robot_resistances'] AS $robot_resistance){
            $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.(!empty($robot_resistance) ? $robot_resistance : 'none').'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</span>';
          }
          echo implode(' ', $temp_string);
        } else {
          echo '<span class="robot_resistance">None</span>';
        }
        ?>
      </td>
      <?
      $left_column_markup[] = ob_get_clean();
    }

    // Define the markup for the affinity
    if (true){
      ob_start();
      ?>
      <td  class="right">
        <label style="display: block; float: left;">Affinities :</label>
        <?
        if (!empty($robot_info['robot_affinities'])){
          $temp_string = array();
          foreach ($robot_info['robot_affinities'] AS $robot_affinity){
            $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.(!empty($robot_affinity) ? $robot_affinity : 'none').'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</span>';
          }
          echo implode(' ', $temp_string);
        } else {
          echo '<span class="robot_affinity">None</span>';
        }
        ?>
      </td>
      <?
      $left_column_markup[] = ob_get_clean();
    }

    // Define the markup for the immunity
    if (true){
      ob_start();
      ?>
      <td class="right">
        <label style="display: block; float: left;">Immunities :</label>
        <?
        if (!empty($robot_info['robot_immunities'])){
          $temp_string = array();
          foreach ($robot_info['robot_immunities'] AS $robot_immunity){
            $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.(!empty($robot_immunity) ? $robot_immunity : 'none').'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>';
          }
          echo implode(' ', $temp_string);
        } else {
          echo '<span class="robot_immunity">None</span>';
        }
        ?>
      </td>
      <?
      $left_column_markup[] = ob_get_clean();
    }

    // Define the markup for the level
    if (true){
      ob_start();
      ?>
      <td  class="right">
        <label style="display: block; float: left;">Level :</label>
        <? if($robot_info['robot_level'] >= 100){ ?>
          <a data-tooltip-align="center" data-tooltip="<?= htmlentities(('Congratulations! '.$robot_info['robot_name'].' has reached Level 100!<br /> <span style="font-size: 90%;">Stat bonuses will now be awarded immediately when this robot lands the finishing blow on a target! Try to max out each stat to its full potential!</span>'), ENT_QUOTES, 'UTF-8') ?>" class="robot_stat robot_type_electric"><?= $robot_info['robot_level'] ?> <span>&#9733;</span></a>
        <? } else { ?>
          <span class="robot_stat robot_level_reset robot_type_<?= !empty($robot_rewards['flags']['reached_max_level']) ? 'electric' : 'none' ?>"><?= !empty($robot_rewards['flags']['reached_max_level']) ? '<span>&#9733;</span>' : '' ?> <?= $robot_info['robot_level'] ?></span>
        <? } ?>
      </td>
      <?
      $right_column_markup[] = ob_get_clean();
    }

    // Define the markup for the experience
    if (true){
      ob_start();
      ?>
      <td  class="right">
        <label style="display: block; float: left;">Experience :</label>
        <? if ($robot_info['robot_level'] >= MMRPG_SETTINGS_LEVEL_MAX): ?>
          <span class="robot_stat robot_type_cutter">&#8734; / &#8734;</span>
        <? else: ?>
          <span class="robot_stat"><?= $robot_info['robot_experience'] ?> / <?= mmrpg_prototype_calculate_experience_required($robot_info['robot_level']) ?></span>
        <? endif; ?>
      </td>
      <?
      $right_column_markup[] = ob_get_clean();
    }

    // Define the markup for the energy
    if (true){
      ob_start();
      ?>
      <td class="right">
        <label style="display: block; float: left;">Energy :</label>

        <span class="robot_stat robot_type robot_type_energy" style="padding: 0 6px; margin-right: 3px;"><?
          echo MMRPG_SETTINGS_STATS_GET_ROBOTMIN($robot_info['robot_energy_index'], $robot_info['robot_level'])
        ?><span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> LE</span></span>

        <span class="robot_stat robot_type robot_type_weapons" style="padding: 0 6px;"><?
          echo $robot_info['robot_weapons_index']
        ?><span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> WE</span></span>

      </td>
      <?
      $right_column_markup[] = ob_get_clean();
    }

    // Define the markup for the attack
    if (true){
      ob_start();
      ?>
      <td class="right">
        <?
        // Print out the ATTACK stat
        $temp_stat = 'attack';
        $temp_stat_max = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($robot_info['robot_'.$temp_stat.'_index'], $robot_info['robot_level']);
        $temp_stat_maxed = $robot_info['robot_'.$temp_stat] >= $temp_stat_max ? true : false;
        $temp_title = $robot_info['robot_level'] >= 100 ? $robot_info['robot_'.$temp_stat].' / '.$temp_stat_max.' Max'.($temp_stat_maxed ? ' &#9733;' : '') : '';
        $temp_data_type = $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '';
        ?>
        <label class="<?= !empty($player_info['player_'.$temp_stat]) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;"><?= ucfirst($temp_stat) ?> :</label>
        <span class="robot_stat <?= $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '' ?>"><?
          echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
            echo '<span title="Base '.ucfirst($temp_stat).'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_base'].'</span> ';
            echo !empty($robot_info['robot_'.$temp_stat.'_rewards']) ? '+ <span title="Robot Bonuses" class="statboost_robot"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_rewards'].'</span> ' : '';
            echo !empty($robot_info['robot_'.$temp_stat.'_player']) ? '+ <span title="Player Bonuses" class="statboost_player_'.$player_info['player_token'].($temp_stat_maxed ? '2' : '').'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_player'].'</span> ' : '';
          echo ' = </span>';
          echo '<span'.(!empty($temp_title) ? ' title="'.$temp_title.'"' : '').(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>';
          echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$temp_stat], 4, '0', STR_PAD_LEFT));
          if ($temp_stat_maxed){ echo '<span>&nbsp;&#9733;</span>'; }
          echo '</span>';
        ?></span>
      </td>
      <?
      $right_column_markup[] = ob_get_clean();
    }

    // Define the markup for the defense
    if (true){
      ob_start();
      ?>
      <td class="right">
        <?
        // Print out the DEFENSE stat
        $temp_stat = 'defense';
        $temp_stat_max = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($robot_info['robot_'.$temp_stat.'_index'], $robot_info['robot_level']);
        $temp_stat_maxed = $robot_info['robot_'.$temp_stat] >= $temp_stat_max ? true : false;
        $temp_title = $robot_info['robot_level'] >= 100 ? $robot_info['robot_'.$temp_stat].' / '.$temp_stat_max.' Max'.($temp_stat_maxed ? ' &#9733;' : '') : '';
        $temp_data_type = $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '';
        ?>
        <label class="<?= !empty($player_info['player_'.$temp_stat]) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;"><?= ucfirst($temp_stat) ?> :</label>
        <span class="robot_stat <?= $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '' ?>"><?
          echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
            echo '<span title="Base '.ucfirst($temp_stat).'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_base'].'</span> ';
            echo !empty($robot_info['robot_'.$temp_stat.'_rewards']) ? '+ <span title="Robot Bonuses" class="statboost_robot"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_rewards'].'</span> ' : '';
            echo !empty($robot_info['robot_'.$temp_stat.'_player']) ? '+ <span title="Player Bonuses" class="statboost_player_'.$player_info['player_token'].($temp_stat_maxed ? '2' : '').'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_player'].'</span> ' : '';
          echo ' = </span>';
          echo '<span'.(!empty($temp_title) ? ' title="'.$temp_title.'"' : '').(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>';
          echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$temp_stat], 4, '0', STR_PAD_LEFT));
          if ($temp_stat_maxed){ echo '<span>&nbsp;&#9733;</span>'; }
          echo '</span>';
        ?></span>
      </td>
      <?
      $right_column_markup[] = ob_get_clean();
    }

    // Define the markup for the speed
    if (true){
      ob_start();
      ?>
      <td class="right">
        <?
        // Print out the SPEED stat
        $temp_stat = 'speed';
        $temp_stat_max = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($robot_info['robot_'.$temp_stat.'_index'], $robot_info['robot_level']);
        $temp_stat_maxed = $robot_info['robot_'.$temp_stat] >= $temp_stat_max ? true : false;
        $temp_title = $robot_info['robot_level'] >= 100 ? $robot_info['robot_'.$temp_stat].' / '.$temp_stat_max.' Max'.($temp_stat_maxed ? ' &#9733;' : '') : '';
        $temp_data_type = $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '';
        ?>
        <label class="<?= !empty($player_info['player_'.$temp_stat]) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;"><?= ucfirst($temp_stat) ?> :</label>
        <span class="robot_stat <?= $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '' ?>"><?
          echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
            echo '<span title="Base '.ucfirst($temp_stat).'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_base'].'</span> ';
            echo !empty($robot_info['robot_'.$temp_stat.'_rewards']) ? '+ <span title="Robot Bonuses" class="statboost_robot"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_rewards'].'</span> ' : '';
            echo !empty($robot_info['robot_'.$temp_stat.'_player']) ? '+ <span title="Player Bonuses" class="statboost_player_'.$player_info['player_token'].($temp_stat_maxed ? '2' : '').'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_player'].'</span> ' : '';
          echo ' = </span>';
          echo '<span'.(!empty($temp_title) ? ' title="'.$temp_title.'"' : '').(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>';
          echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$temp_stat], 4, '0', STR_PAD_LEFT));
          if ($temp_stat_maxed){ echo '<span>&nbsp;&#9733;</span>'; }
          echo '</span>';
        ?></span>
      </td>
      <?
      $right_column_markup[] = ob_get_clean();
    }

    ?>

    <div class="header header_left robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="margin-right: 0;">
      <span class="title robot_type"><?=$robot_info['robot_name']?></span>
      <span class="core robot_type">
        <span class="wrap"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(i/a/item-core-<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/il40.png);"></span></span>
        <span class="text"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?> Core</span>
      </span>
    </div>
    <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">
      <table class="full" style="margin-bottom: 5px;">
        <colgroup>
          <col width="64%" />
          <col width="1%" />
          <col width="35%" />
        </colgroup>
        <tbody>
          <tr>
            <?
            if (!empty($left_column_markup[0])){ echo $left_column_markup[0]; }
            else { echo $empty_column_placeholder; }
            ?>
            <td class="center">&nbsp;</td>
            <?
            if (!empty($right_column_markup[0])){ echo $right_column_markup[0]; }
            else { echo $empty_column_placeholder; }
            ?>
          </tr>
          <tr>
            <?
            if (!empty($left_column_markup[1])){ echo $left_column_markup[1]; }
            else { echo $empty_column_placeholder; }
            ?>
            <td class="center">&nbsp;</td>
            <?
            if (!empty($right_column_markup[1])){ echo $right_column_markup[1]; }
            else { echo $empty_column_placeholder; }
            ?>
          </tr>

          <tr>
            <?
            if (!empty($left_column_markup[2])){ echo $left_column_markup[2]; }
            else { echo $empty_column_placeholder; }
            ?>
            <td class="center">&nbsp;</td>
            <?
            if (!empty($right_column_markup[2])){ echo $right_column_markup[2]; }
            else { echo $empty_column_placeholder; }
            ?>
          </tr>
          <tr>
            <?
            if (!empty($left_column_markup[3])){ echo $left_column_markup[3]; }
            else { echo $empty_column_placeholder; }
            ?>
            <td class="center">&nbsp;</td>
            <?
            if (!empty($right_column_markup[3])){ echo $right_column_markup[3]; }
            else { echo $empty_column_placeholder; }
            ?>
          </tr>
          <tr>
            <?
            if (!empty($left_column_markup[4])){ echo $left_column_markup[4]; }
            else { echo $empty_column_placeholder; }
            ?>
            <td class="center">&nbsp;</td>
            <?
            if (!empty($right_column_markup[4])){ echo $right_column_markup[4]; }
            else { echo $empty_column_placeholder; }
            ?>
          </tr>
          <tr>
            <?
            if (!empty($left_column_markup[5])){ echo $left_column_markup[5]; }
            else { echo $empty_column_placeholder; }
            ?>
            <td class="center">&nbsp;</td>
            <?
            if (!empty($right_column_markup[5])){ echo $right_column_markup[5]; }
            else { echo $empty_column_placeholder; }
            ?>
          </tr>
        </tbody>
      </table>

      <table class="full">
        <colgroup>
          <col width="100%" />
        </colgroup>
        <tbody>
          <tr>
            <td class="right" style="padding-top: 4px;">
              <?/*<label style="display: block; float: left; font-size: 12px;">Abilities :</label>*/?>
              <?
              // Loop through all the abilities collected by the player and collect IDs
              $allowed_ability_ids = array();
              if (!empty($player_ability_rewards)){
                foreach ($player_ability_rewards AS $ability_token => $ability_info){

                  if (empty($ability_info['ability_token'])){ continue; }
                  elseif ($ability_info['ability_token'] == '*'){ continue; }
                  elseif ($ability_info['ability_token'] == 'ability'){ continue; }
                  elseif (!isset($mmrpg_database_abilities[$ability_info['ability_token']])){ continue; }
                  elseif (!mmrpg_robot::has_ability_compatibility($robot_info['robot_token'], $ability_token, $current_item_token)){ continue; }
                  $ability_info['ability_id'] = $mmrpg_database_abilities[$ability_info['ability_token']]['ability_id'];

                  $allowed_ability_ids[] = $ability_info['ability_id'];

                }
              }

              ?>
              <div class="ability_container" data-compatible="<?= implode(',', $allowed_ability_ids) ?>">
                <?

                // Sort the player ability index based on ability number
                uasort($player_ability_rewards, array('mmrpg_player', 'abilities_sort_for_editor'));

                // Sort the robot ability index based on ability number
                sort($robot_ability_rewards);

                // Collect the ability reward options to be used on all selects
                $ability_rewards_options = $global_allow_editing ? mmrpg_ability::print_editor_options_list_markup($player_ability_rewards, $robot_ability_rewards, $player_info, $robot_info) : '';

                // Loop through the robot's current abilities and list them one by one
                $empty_ability_counter = 0;
                if (!empty($robot_info['robot_abilities'])){
                  $temp_string = array();
                  $temp_inputs = array();
                  $ability_key = 0;

                  // DEBUG
                  //echo 'robot-ability:';
                  foreach ($robot_info['robot_abilities'] AS $robot_ability){

                    if (empty($robot_ability['ability_token'])){ continue; }
                    elseif ($robot_ability['ability_token'] == '*'){ continue; }
                    elseif ($robot_ability['ability_token'] == 'ability'){ continue; }
                    elseif (!isset($mmrpg_database_abilities[$robot_ability['ability_token']])){ continue; }
                    elseif ($ability_key > 7){ continue; }

                    $ability_token = $robot_ability['ability_token'];
                    $this_ability = mmrpg_ability::parse_index_info($mmrpg_database_abilities[$ability_token]);
                    if (empty($ability_token) || empty($this_ability)){ continue; }
                    elseif (!mmrpg_robot::has_ability_compatibility($robot_info['robot_token'], $ability_token, $current_item_token)){ continue; }

                    $temp_select_markup = mmrpg_ability::print_editor_select_markup($ability_rewards_options, $player_info, $robot_info, $this_ability, $ability_key);

                    $temp_string[] = $temp_select_markup;
                    $ability_key++;

                  }

                  if ($ability_key <= 7){
                    for ($ability_key; $ability_key <= 7; $ability_key++){
                      $empty_ability_counter++;
                      if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                      else { $empty_ability_disable = false; }
                      //$temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $ability_rewards_options);
                      $this_ability_title_html = '<label>-</label>';
                      //if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                      $temp_string[] = '<a class="ability_name " style="'.($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-id="0" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="" title="" data-tooltip="">'.$this_ability_title_html.'</a>';
                    }
                  }

                } else {

                  for ($ability_key = 0; $ability_key <= 7; $ability_key++){
                    $empty_ability_counter++;
                    if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                    else { $empty_ability_disable = false; }
                    //$temp_select_options = str_replace('value=""', 'value="" selected="selected"', $ability_rewards_options);
                    $this_ability_title_html = '<label>-</label>';
                    //if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                    $temp_string[] = '<a class="ability_name " style="'.($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-id="0" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="">'.$this_ability_title_html.'</a>';
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
    </div>
  </div>
  <?
  $key_counter++;

  // Return the backup of the player selector
  $allow_player_selector = $allow_player_selector_backup;

// Collect the outbut buffer contents
$this_markup = trim(ob_get_clean());
?>