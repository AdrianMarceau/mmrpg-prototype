<?php
// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
define('MMRPG_REMOTE_SKIP_DATABASE', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();

// Include the DATABASE file
//require_once('../data/database.php');
require(MMRPG_CONFIG_ROOTDIR.'data/prototype_omega.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_types.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_players.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_robots.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_abilities.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_fields.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_items.php');
require(MMRPG_CONFIG_ROOTDIR.'data/starforce.php');
// Collect the editor flag if set
$global_allow_editing = true;


// -- GENERATE EDITOR MARKUP

// Require the shop index so we can use it's data
require(MMRPG_CONFIG_ROOTDIR.'data/shop.php');

// Define which shops we're allowed to see
$allowed_edit_data = $this_shop_index;
//$prototype_player_counter = !empty($_SESSION[$session_token]['values']['battle_rewards']) ? count($_SESSION[$session_token]['values']['battle_rewards']) : 0;
//$prototype_complete_counter = mmrpg_prototype_complete();
//$prototype_battle_counter = mmrpg_prototype_battles_complete('dr-light');
if (!mmrpg_prototype_event_complete('completed-chapter_dr-light_one')){ unset($allowed_edit_data['auto']); }
if (!mmrpg_prototype_event_complete('completed-chapter_dr-wily_one')){ unset($allowed_edit_data['reggae']); }
if (!mmrpg_prototype_event_complete('completed-chapter_dr-cossack_one')){ unset($allowed_edit_data['kalinka']); }
//if ($prototype_complete_counter < 3){ unset($allowed_edit_data['kalinka']); }
//if ($prototype_player_counter < 3){ unset($allowed_edit_data['reggae']); }
//if ($prototype_battle_counter < 1){ unset($allowed_edit_data['auto']); }
$allowed_edit_data_count = count($allowed_edit_data);
//die('$allowed_edit_data_count = '.$allowed_edit_data_count.'; $allowed_edit_data = <pre>'.print_r($allowed_edit_data, true).'</pre>');

// HARD-CODE ZENNY FOR TESTING
//$_SESSION[$session_token]['counters']['battle_zenny'] = 500000;

// Define the array to hold all the item quantities
$global_item_quantities = array();
$global_item_prices = array();
$global_zenny_counter = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;


// -- PROCESS SHOP SELL ACTION -- //

// Check if an action request has been sent with an sell type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'sell'){
  // Collect the action variables from the request header, if they exist
  $temp_shop = !empty($_REQUEST['shop']) ? $_REQUEST['shop'] : '';
  $temp_kind = !empty($_REQUEST['kind']) ? $_REQUEST['kind'] : '';
  $temp_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
  $temp_token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
  $temp_quantity = !empty($_REQUEST['quantity']) ? $_REQUEST['quantity'] : 0;
  $temp_price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : 0;

  // If key variables are not provided, kill the script in error
  if (empty($temp_shop)){ die('error|request-error|shop-missing'); }
  if (empty($temp_kind)){ die('error|request-error|kind-missing'); }
  elseif (empty($temp_action)){ die('error|request-error|action-missing'); }
  elseif (empty($temp_token)){ die('error|request-error|token-missing'); }
  elseif (empty($temp_quantity)){ die('error|request-error|quantity-missing'); }
  elseif (empty($temp_price)){ die('error|request-error|price-missing'); }

  // Check if this is an ITEM based action
  if ($temp_kind == 'item'){
    // Ensure this item exists before continuing
    if (isset($_SESSION[$session_token]['values']['battle_items'][$temp_token])){
      // Collect a reference to the session variable amount
      $temp_is_shard = preg_match('/^item-shard-/i', $temp_token) ? true : false;
      $temp_is_core = preg_match('/^item-core-/i', $temp_token) ? true : false;
      if ($temp_is_shard){ $temp_max_quantity = MMRPG_SETTINGS_SHARDS_MAXQUANTITY; }
      elseif ($temp_is_core){ $temp_max_quantity = MMRPG_SETTINGS_CORES_MAXQUANTITY; }
      else { $temp_max_quantity = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;  }
      $temp_current_quantity = $_SESSION[$session_token]['values']['battle_items'][$temp_token];

      // Now make sure we actually have enough of this item to sell
      if ($temp_quantity <= $temp_current_quantity){
        // Remove this item's count from the global variable and recollect
        $_SESSION[$session_token]['values']['battle_items'][$temp_token] = $temp_current_quantity - $temp_quantity;
        $temp_current_quantity = $_SESSION[$session_token]['values']['battle_items'][$temp_token];

        // Increment the player's zenny count based on the provided price
        $_SESSION[$session_token]['counters']['battle_zenny'] = $global_zenny_counter + $temp_price;
        $global_zenny_counter = $_SESSION[$session_token]['counters']['battle_zenny'];

        // Update the shop history with the item bought by the shop keeper
        if ($temp_is_core){
          if (!isset($_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['cores_bought'][$temp_token])){ $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['cores_bought'][$temp_token] = 0; }
          $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['cores_bought'][$temp_token] += $temp_quantity;
        } else {
          if (!isset($_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['items_bought'][$temp_token])){ $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['items_bought'][$temp_token] = 0; }
          $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['items_bought'][$temp_token] += $temp_quantity;
        }
        $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['zenny_spent'] += $temp_price;
        $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['shop_experience'] += $temp_price + (($temp_quantity - 1) * ($temp_quantity - 1));

        // Save, produce the success message with the new field order
        mmrpg_save_game_session($this_save_filepath);
        exit('success|item-sold|'.$temp_current_quantity.'|'.$global_zenny_counter);

      }
      // Otherwise if the user requested more than they have
      else {

        // Print an error message and kill the script
        exit('error|insufficient-quantity|'.$temp_quantity);

      }

    }
    // Otherwise if this item does not exist
    else {

      // Print an error message and kill the script
      exit('error|invalid-item|'.$temp_token);

    }

  }
  // Check if this is an STAR based action
  elseif ($temp_kind == 'star'){
    // Collect the actual star token from the provided one
    $temp_actual_token = preg_replace('/^star-/i', '', $temp_token);

    // Ensure this star exists before continuing
    if (isset($_SESSION[$session_token]['values']['battle_stars'][$temp_actual_token])){
      // Remove this star's entry from the global arrayand define the new quantity
      unset($_SESSION[$session_token]['values']['battle_stars'][$temp_actual_token]);
      $temp_current_quantity = 0;

      // Increment the player's zenny count based on the provided price
      $_SESSION[$session_token]['counters']['battle_zenny'] = $global_zenny_counter + $temp_price;
      $global_zenny_counter = $_SESSION[$session_token]['counters']['battle_zenny'];

      // Update the shop history with the star bought by the shop keeper
      if (!isset($_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['stars_bought'][$temp_token])){ $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['stars_bought'][$temp_token] = 0; }
      $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['stars_bought'][$temp_token] += 1;
      $temp_new_quantity = $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['stars_bought'][$temp_token];
      $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['zenny_spent'] += $temp_price;
      $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['shop_experience'] += $temp_price - (($temp_new_quantity - 1) * ($temp_new_quantity - 1));

      // Save, produce the success message with the new field order
      mmrpg_save_game_session($this_save_filepath);
      exit('success|star-sold|'.$temp_current_quantity.'|'.$global_zenny_counter);

    }
    // Otherwise if this star does not exist
    else {

      // Print an error message and kill the script
      exit('error|invalid-star|'.$temp_actual_token);

    }

  }
  // Otherwise if undefined kind
  else {

    // Print an error message and kill the script
    exit('error|invalid-kind|'.$temp_kind);

  }

}


// -- PROCESS SHOP BUY ACTION -- //

// Check if an action request has been sent with an buy type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'buy'){
  // Collect the action variables from the request header, if they exist
  $temp_shop = !empty($_REQUEST['shop']) ? $_REQUEST['shop'] : '';
  $temp_kind = !empty($_REQUEST['kind']) ? $_REQUEST['kind'] : '';
  $temp_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
  $temp_token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
  $temp_quantity = !empty($_REQUEST['quantity']) ? $_REQUEST['quantity'] : 0;
  $temp_price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : 0;
  $temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';

  // If key variables are not provided, kill the script in error
  if (empty($temp_shop)){ die('error|request-error|shop-missing'); }
  if (empty($temp_kind)){ die('error|request-error|kind-missing'); }
  elseif (empty($temp_action)){ die('error|request-error|action-missing'); }
  elseif (empty($temp_token)){ die('error|request-error|token-missing'); }
  elseif (empty($temp_quantity)){ die('error|request-error|quantity-missing'); }
  elseif (empty($temp_price)){ die('error|request-error|price-missing'); }
  elseif ($temp_kind == 'ability' && empty($temp_player)){ die('error|request-error|player-missing'); }

  // Check if this is an ITEM based action
  if ($temp_kind == 'item'){
    // Ensure this item exists before continuing
    if (isset($mmrpg_database_items[$temp_token])){
      // Collect a reference to the session variable amount
      $temp_is_shard = preg_match('/^item-shard-/i', $temp_token) ? true : false;
      $temp_is_core = preg_match('/^item-core-/i', $temp_token) ? true : false;
      if ($temp_is_shard){ $temp_max_quantity = MMRPG_SETTINGS_SHARDS_MAXQUANTITY; }
      elseif ($temp_is_core){ $temp_max_quantity = MMRPG_SETTINGS_CORES_MAXQUANTITY; }
      else { $temp_max_quantity = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;  }
      $temp_current_quantity = !empty($_SESSION[$session_token]['values']['battle_items'][$temp_token]) ? $_SESSION[$session_token]['values']['battle_items'][$temp_token] : 0;

      // Now make sure we actually have enough of this item to buy
      if (($temp_current_quantity + $temp_quantity) <= $temp_max_quantity){
        // Remove this item's count from the global variable and recollect
        $_SESSION[$session_token]['values']['battle_items'][$temp_token] = $temp_current_quantity + $temp_quantity;
        $temp_current_quantity = $_SESSION[$session_token]['values']['battle_items'][$temp_token];

        // Increment the player's zenny count based on the provided price
        $_SESSION[$session_token]['counters']['battle_zenny'] = $global_zenny_counter - $temp_price;
        $global_zenny_counter = $_SESSION[$session_token]['counters']['battle_zenny'];

        // Update the shop history with this sold item under the given character
        if (!isset($_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['items_sold'][$temp_token])){ $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['items_sold'][$temp_token] = 0; }
        $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['items_sold'][$temp_token] += $temp_quantity;
        $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['zenny_earned'] += $temp_price;
        $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['shop_experience'] += $temp_price + (($temp_quantity - 1) * ($temp_quantity - 1));

        // Save, produce the success message with the new field order
        mmrpg_save_game_session($this_save_filepath);
        exit('success|item-bought|'.$temp_current_quantity.'|'.$global_zenny_counter);

      }
      // Otherwise if the user requested more than they have
      else {

        // Print an error message and kill the script
        exit('error|overkill-quantity|'.$temp_quantity);

      }

    }
    // Otherwise if this item does not exist
    else {

      // Print an error message and kill the script
      exit('error|invalid-item|'.$temp_token);

    }

  }
  // Check if this is an ABILITY based action
  elseif ($temp_kind == 'ability'){
    // Ensure this ability exists before continuing
    if (isset($mmrpg_database_abilities[$temp_token])){
      // Ensure the requested ability token was valid
      if (!empty($mmrpg_database_abilities[$temp_token])){

        // Collect the current ability's info from the database
        $ability_info = array('ability_token' => $temp_token); //mmrpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);

        // Unlock this ability for all playable characters
        mmrpg_game_unlock_ability(false, false, $ability_info);
        $temp_current_quantity = 1;

        // If the unlock was successful
        if (mmrpg_prototype_ability_unlocked('', '', $temp_token)){

          // Increment the player's zenny count based on the provided price
          $_SESSION[$session_token]['counters']['battle_zenny'] = $global_zenny_counter - $temp_price;
          $global_zenny_counter = $_SESSION[$session_token]['counters']['battle_zenny'];

          // Update the shop history with this sold item under the given character
          if (!isset($_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['abilities_sold'][$temp_token])){ $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['abilities_sold'][$temp_token] = 0; }
          $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['abilities_sold'][$temp_token] += 1;
          $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['zenny_earned'] += $temp_price;
          $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['shop_experience'] += $temp_price;

          // Save, produce the success message with the new ability order
          mmrpg_save_game_session($this_save_filepath);
          exit('success|ability-purchased|'.$temp_current_quantity.'|'.$global_zenny_counter);

        }
        // Otherwise, if the ability was not unlocked for some reason
        else {

          // Print an error message and kill the script
          exit('error|unlock-error|'.$temp_token);

        }

      }
      // Otherwise, produce an error
      else {

        // Print an error message and kill the script
        exit('error|invalid-player|'.$temp_token);

      }

    }
    // Otherwise if this star does not exist
    else {

      // Print an error message and kill the script
      exit('error|invalid-ability|'.$temp_token);

    }

  }
  // Check if this is an FIELD based action
  elseif ($temp_kind == 'field'){
    // Collect the actual field token from the provided one
    $temp_actual_token = preg_replace('/^field-/i', '', $temp_token);

    // Ensure this field exists before continuing
    if (isset($mmrpg_database_fields[$temp_actual_token])){
      // Remove this field's entry from the global arrayand define the new quantity
      $temp_unlocked_fields = !empty($_SESSION[$session_token]['values']['battle_fields']) ? $_SESSION[$session_token]['values']['battle_fields'] : array();
      $temp_unlocked_fields[] = $temp_actual_token;
      $temp_unlocked_fields = array_unique($temp_unlocked_fields);
      $_SESSION[$session_token]['values']['battle_fields'] = $temp_unlocked_fields;
      $temp_current_quantity = 1;

      // Increment the player's zenny count based on the provided price
      $_SESSION[$session_token]['counters']['battle_zenny'] = $global_zenny_counter - $temp_price;
      $global_zenny_counter = $_SESSION[$session_token]['counters']['battle_zenny'];

      // Update the shop history with this sold item under the given character
      if (!isset($_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['fields_sold'][$temp_token])){ $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['fields_sold'][$temp_token] = 0; }
      $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['fields_sold'][$temp_token] += 1;
      $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['zenny_earned'] += $temp_price;
      $_SESSION[$session_token]['values']['battle_shops'][$temp_shop]['shop_experience'] += $temp_price;

    // Save, produce the success message with the new field order
      mmrpg_save_game_session($this_save_filepath);
      exit('success|field-purchased|'.$temp_current_quantity.'|'.$global_zenny_counter);

    }
    // Otherwise if this star does not exist
    else {

      // Print an error message and kill the script
      exit('error|invalid-star|'.$temp_actual_token);

    }

  }
  // Otherwise if undefined kind
  else {

    // Print an error message and kill the script
    exit('error|invalid-kind|'.$temp_kind);

  }

}


// CANVAS MARKUP

// Generate the canvas markup for this page
if (true){
  // Start the output buffer
  ob_start();

  // Loop through the allowed edit data for all shops
  $key_counter = 0;
  $shop_counter = 0;
  foreach($allowed_edit_data AS $shop_token => $shop_info){
    $shop_counter++;
    //echo '<td style="width: '.floor(100 / $allowed_edit_shop_count).'%;">'."\n";
      echo '<div class="wrapper wrapper_'.($shop_counter % 2 != 0 ? 'left' : 'right').' player_type player_type_empty" data-select="shops" data-shop="'.$shop_info['shop_token'].'">'."\n";
      echo '<div class="wrapper_header player_type player_type_'.(!empty($shop_info['shop_colour']) ? $shop_info['shop_colour'] : 'none').'">'.$shop_info['shop_owner'].'</div>';
        $shop_key = $key_counter;
        $shop_info['shop_image'] = $shop_info['shop_token'];
        $shop_info['shop_image_size'] = 80;
        $shop_image_offset = 0;
        $shop_image_offset_x = -14 - $shop_image_offset;
        $shop_image_offset_y = -14 - $shop_image_offset;
        echo '<a data-token="'.$shop_info['shop_token'].'" data-shop="'.$shop_info['shop_token'].'" style="background-image: url(images/shops/'.(!empty($shop_info['shop_image']) ? $shop_info['shop_image'] : $shop_info['shop_token']).'/mug_right_'.$shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: '.$shop_image_offset_x.'px '.$shop_image_offset_y.'px;" class="sprite sprite_player sprite_shop_'.$shop_token.' sprite_shop_sprite sprite_'.$shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'].' sprite_'.$shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'].'_mugshot shop_status_active shop_position_active '.($shop_key == 0 ? 'sprite_shop_current ' : '').' player_type player_type_'.(!empty($shop_info['shop_colour']) ? $shop_info['shop_colour'] : 'none').'">'.$shop_info['shop_name'].'</a>'."\n";
        $key_counter++;
      //echo '<a class="sort" data-shop="'.$shop_info['shop_token'].'">sort</a>';
      echo '</div>'."\n";
    //echo '</td>'."\n";
  }

  // Collect the contents of the buffer
  $shop_canvas_markup = ob_get_clean();
  $shop_canvas_markup = preg_replace('/\s+/', ' ', trim($shop_canvas_markup));

}

// CONSOLE MARKUP

// Generate the console markup for this page
if (true){
  // Start the output buffer
  ob_start();

  // Loop through the shops in the field edit data
  foreach($allowed_edit_data AS $shop_token => $shop_info){

    // Update the player key to the current counter
    $shop_key = $key_counter;
    $shop_info['shop_image'] = $shop_info['shop_token'];
    $shop_info['shop_image_size'] = 40;

    // Collect a temp robot object for printing items
    $player_info = $mmrpg_index['players'][$shop_info['shop_player']];
    if ($shop_info['shop_player'] == 'dr-light'){ $robot_info = mmrpg_robot::parse_index_info($mmrpg_database_robots['mega-man']); }
    elseif ($shop_info['shop_player'] == 'dr-wily'){ $robot_info = mmrpg_robot::parse_index_info($mmrpg_database_robots['bass']); }
    elseif ($shop_info['shop_player'] == 'dr-cossack'){ $robot_info = mmrpg_robot::parse_index_info($mmrpg_database_robots['proto-man']); }

    // Collect the tokens for all this shop's selling and buying tabs
    $shop_selling_tokens = is_array($shop_info['shop_kind_selling']) ? $shop_info['shop_kind_selling'] : array($shop_info['shop_kind_selling']);
    $shop_buying_tokens = is_array($shop_info['shop_kind_buying']) ? $shop_info['shop_kind_buying'] : array($shop_info['shop_kind_buying']);

    // Collect and print the editor markup for this player
    ?>
    <div class="event event_double event_<?= $shop_key == 0 ? 'visible' : 'hidden' ?>" data-token="<?=$shop_info['shop_token']?>">
      <div class="this_sprite sprite_left" style="top: 4px; left: 4px; width: 36px; height: 36px; background-image: url(images/fields/<?=$shop_info['shop_field']?>/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center center; border: 1px solid #1A1A1A;">
        <div class="sprite sprite_player sprite_shop_sprite sprite_<?= $shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'] ?> sprite_<?= $shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'] ?>_00" style="margin-top: -4px; margin-left: -2px; background-image: url(images/shops/<?= !empty($shop_info['shop_image']) ? $shop_info['shop_image'] : $shop_info['shop_token'] ?>/sprite_right_<?= $shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); "><?=$shop_info['shop_name']?></div>
      </div>
      <div class="header header_left player_type player_type_<?= $shop_info['shop_colour'] ?>" style="margin-right: 0;">
        <?=$shop_info['shop_name']?>
        <span class="player_type"><?= ucfirst(rtrim($shop_info['shop_seeking'], 's')) ?> Seeker</span>
      </div>
      <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">

        <div class="shop_tabs_links" style="margin: 0 auto; color: #FFFFFF; ">
          <span class="tab_spacer"><span class="inset">&nbsp;</span></span>
          <?
          // Define a counter for the number of tabs
          $tab_counter = 0;
          // Loop through the selling tokens and display tabs for them
          foreach ($shop_selling_tokens AS $selling_token){
            ?>
            <span class="tab_spacer"><span class="inset">&nbsp;</span></span>
            <a class="tab_link tab_link_selling" href="#" data-tab="selling" data-tab-type="<?= $selling_token ?>"><span class="inset">Buy <?= ucfirst($selling_token) ?></span></a>
            <?
            $tab_counter++;
          }
          // Loop through the buying tokens and display tabs for them
          foreach ($shop_buying_tokens AS $buying_token){
            ?>
            <span class="tab_spacer"><span class="inset">&nbsp;</span></span>
            <a class="tab_link tab_link_buying" href="#" data-tab="buying" data-tab-type="<?= $buying_token ?>"><span class="inset">Sell <?= ucfirst($buying_token) ?></span></a>
            <?
            $tab_counter++;
          }
          // Define the tab width total
          $tab_width = $tab_counter * (1 + 20);
          $line_width = 96 - $tab_width;
          ?>
          <span class="tab_line" style="width: <?= $line_width ?>%;"><span class="inset">&nbsp;</span></span>
          <span class="tab_level"><span class="wrap">Level <?= $shop_info['shop_level'] ?></span></span>
        </div>

        <div class="shop_tabs_containers" style="margin: 0 auto 10px;">

          <?
          // Loop through the selling tokens and display tabs for them
          foreach ($shop_selling_tokens AS $selling_token){

            ?>
            <div class="tab_container tab_container_selling" data-tab="selling" data-tab-type="<?= $selling_token ?>">

              <div class="shop_quote shop_quote_selling">&quot;<?= isset($shop_info['shop_quote_selling'][$selling_token]) ? $shop_info['shop_quote_selling'][$selling_token] : $shop_info['shop_quote_selling']  ?>&quot;</div>

              <?
              // -- SHOP SELLING ITEMS -- //
              // If this shop has items to selling, print them out
              if (in_array($selling_token, array('items', 'cores')) && !empty($shop_info['shop_items']['items_selling'])){
                ?>
                <table class="full" style="margin-bottom: 5px;">
                  <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                  </colgroup>
                  <thead>
                    <tr>
                      <th class="left">
                        <span class="buy_button buy_button_header">&nbsp;</span>
                        <label class="item_quantity item_quantity_header">Own</label>
                        <label class="item_price item_price_header">Buy</label>
                      </th>
                      <th class="right">
                        <span class="buy_button buy_button_header">&nbsp;</span>
                        <label class="item_quantity item_quantity_header">Own</label>
                        <label class="item_price item_price_header">Buy</label>
                      </th>
                    </tr>
                  </thead>
                </table>
                <div class="scroll_wrapper">
                  <table class="full" style="margin-bottom: 5px;">
                    <colgroup>
                      <col width="50%" />
                      <col width="50%" />
                    </colgroup>
                    <tbody>
                      <tr>
                      <?
                      // Collect the items for buying and slice/shuffle if nessary
                      $item_list_array = $shop_info['shop_items']['items_selling'];
                      /*
                      if ($global_points_counter >= 0 && $global_points_counter < 1000){ $item_list_array = array_slice($item_list_array, 0, 6, true); }
                      elseif ($global_points_counter >= 1000 && $global_points_counter < 10000){ $item_list_array = array_slice($item_list_array, 0, 8, true); }
                      elseif ($global_points_counter >= 10000 && $global_points_counter < 100000){ $item_list_array = array_slice($item_list_array, 0, 10, true); }
                      elseif ($global_points_counter >= 100000 && $global_points_counter < 1000000){ $item_list_array = array_slice($item_list_array, 0, 12, true); }
                      elseif ($global_points_counter >= 1000000 && $global_points_counter < 10000000){ $item_list_array = array_slice($item_list_array, 0, 14, true); }
                      elseif ($global_points_counter >= 1000000 && $global_points_counter < 10000000){ $item_list_array = array_slice($item_list_array, 0, 16, true); }
                      elseif ($global_points_counter >= 1000000 && $global_points_counter < 10000000){ $item_list_array = array_slice($item_list_array, 0, 18, true); }
                      elseif ($global_points_counter >= 1000000 && $global_points_counter < 10000000){ $item_list_array = array_slice($item_list_array, 0, 20, true); }
                      else { $item_list_array = array_slice($item_list_array, 0, 20, true); }
                      */
                      // Loop through the items and print them one by one
                      $item_counter = 0;
                      $item_counter_total = count($item_list_array);
                      foreach ($item_list_array AS $token => $price){
                        if (isset($mmrpg_database_items[$token])){ $item_info = $mmrpg_database_items[$token]; }
                        else { continue; }
                        $item_counter++;
                        $item_info_token = $token;
                        $item_info_price = $price;
                        $item_info_name = $item_info['ability_name'];
                        $item_info_type = !empty($item_info['ability_type']) ? $item_info['ability_type'] : 'none';
                        if ($item_info_type != 'none' && !empty($item_info['ability_type2'])){ $item_info_type .= '_'.$item_info['ability_type2']; }
                        elseif ($item_info_type == 'none' && !empty($item_info['ability_type2'])){ $item_info_type = $item_info['ability_type2']; }
                        $item_info_quantity = !empty($_SESSION[$session_token]['values']['battle_items'][$token]) ? $_SESSION[$session_token]['values']['battle_items'][$token] : 0;
                        $global_item_quantities[$item_info_token] = $item_info_quantity;
                        $global_item_prices['buy'][$item_info_token] = $item_info_price;
                        $item_cell_float = $item_counter % 2 == 0 ? 'right' : 'left';
                        $temp_info_tooltip = mmrpg_ability::print_editor_title_markup($robot_info, $item_info, array('show_quantity' => false));
                        $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);

                        ?>
                        <td class="<?= $item_cell_float ?> item_cell" data-kind="item" data-action="buy" data-token="<?= $item_info_token ?>">
                          <span class="item_name ability_type ability_type_<?= $item_info_type ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $item_info_name ?></span>
                          <a class="buy_button ability_type ability_type_none" href="#">Buy</a>
                          <label class="item_quantity" data-quantity="0">x 0</label>
                          <label class="item_price" data-price="<?= $item_info_price ?>">&hellip; <?= $item_info_price ?>z</label>
                        </td>
                        <?
                        if ($item_cell_float == 'right' && $item_counter < $item_counter_total){ echo '</tr><tr>'; }
                      }
                      if ($item_counter % 2 != 0){
                        ?>
                        <td class="right item_cell item_cell_disabled">
                          &nbsp;
                        </td>
                        <?
                      }
                      ?>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <?
              }
              // -- SHOP SELLING ABILITIES -- //
              // If this shop has abilities to selling, print them out
              elseif (in_array($selling_token, array('abilities', 'weapons')) && (!empty($shop_info['shop_abilities']['abilities_selling']) || !empty($shop_info['shop_weapons']['weapons_selling']))){
                ?>
                <table class="full" style="margin-bottom: 5px;">
                  <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                  </colgroup>
                  <thead>
                    <tr>
                      <th class="left">
                        <span class="buy_button buy_button_header">&nbsp;</span>
                        <label class="item_quantity item_quantity_header">Own</label>
                        <label class="item_price item_price_header">Buy</label>
                      </th>
                      <th class="right">
                        <span class="buy_button buy_button_header">&nbsp;</span>
                        <label class="item_quantity item_quantity_header">Own</label>
                        <label class="item_price item_price_header">Buy</label>
                      </th>
                    </tr>
                  </thead>
                </table>
                <div class="scroll_wrapper">
                  <table class="full" style="margin-bottom: 5px;">
                    <colgroup>
                      <col width="50%" />
                      <col width="50%" />
                    </colgroup>
                    <tbody>
                      <tr>
                      <?
                      // Collect the abilities for buying and slice/shuffle if nessary
                      if ($selling_token == 'abilities'){ $ability_list_array = $shop_info['shop_abilities']['abilities_selling']; }
                      elseif ($selling_token == 'weapons'){ $ability_list_array = $shop_info['shop_weapons']['weapons_selling']; }
                      $ability_list_array_count = count($ability_list_array);
                      $ability_list_max = 20;

                      /*
                      if ($selling_token == 'weapons'){
                        exit('<pre>$ability_list_array('.count($ability_list_array).'/'.$ability_list_max.') = '.print_r($ability_list_array, true).'</pre>');
                      }
                      */

                      /*
                      $ability_list_max = 2;
                      if ($shop_info['shop_level'] >= 5){ $ability_list_max += 2; }
                      if ($shop_info['shop_level'] >= 10){ $ability_list_max += 2; }
                      if ($shop_info['shop_level'] >= 15){ $ability_list_max += 2; }
                      if ($shop_info['shop_level'] >= 20){ $ability_list_max += 2; }
                      if ($shop_info['shop_level'] >= 25){ $ability_list_max += 2; }
                      if ($shop_info['shop_level'] >= 30){ $ability_list_max += 2; }
                      if ($shop_info['shop_level'] >= 35){ $ability_list_max += 2; }
                      if ($shop_info['shop_level'] >= 40){ $ability_list_max += 2; }
                      if ($shop_info['shop_level'] >= 45){ $ability_list_max += 2; }
                      */

                      //echo '<td colspan="4">$ability_list_max = '.$ability_list_max.'</td></tr><tr>';
                      //echo '<td colspan="4">$ability_list_array_count = '.$ability_list_array_count.'</td></tr><tr>';

                      // Collect the unlocked abilities for all three players
                      $ability_list_unlocked = array();
                      if (!empty($_SESSION[$session_token]['values']['battle_rewards']['dr-light']['player_abilities'])){ $ability_list_unlocked['dr-light'] = array_keys($_SESSION[$session_token]['values']['battle_rewards']['dr-light']['player_abilities']); }
                      if (!empty($_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_abilities'])){ $ability_list_unlocked['dr-wily'] = array_keys($_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_abilities']); }
                      if (!empty($_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_abilities'])){ $ability_list_unlocked['dr-cossack'] = array_keys($_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_abilities']); }

                      // Loop through all the abilities and temporarily remove any that have been unlocked
                      //$backup_unlocked_abilities = array();

                      // Count how any of these abilities have been unlocked already
                      $ability_list_unlocked_completely = 0;
                      foreach ($ability_list_array AS $token => $price){
                        if (!isset($mmrpg_database_abilities[$token])){ unset($ability_list_array[$token]); continue; }
                        if (empty($mmrpg_database_abilities[$token]['ability_flag_complete']) || mmrpg_prototype_ability_unlocked('', '', $token)){
                          $ability_list_unlocked_completely += 1;
                          //$backup_unlocked_abilities[$token] = $price;
                          //unset($ability_list_array[$token]);
                        }
                      }

                      // Re-count the ability list after recent changes
                      $ability_list_array_count = count($ability_list_array);

                      /*
                      // If we have too many abilities, we should slice them
                      if ($ability_list_array_count > $ability_list_max){
                        $ability_list_array = array_slice($ability_list_array, 0, $ability_list_max, true);
                      }
                      */

                      // Reverse the order with newest on top
                      $ability_list_array = array_reverse($ability_list_array, true);

                      /*
                      // If the ability count is less than the max, pad with backups
                      if ($ability_list_array_count < $ability_list_max){
                        while ($ability_list_array_count < $ability_list_max){
                          if (empty($backup_unlocked_abilities)){ break; }
                          $token_list = array_keys($backup_unlocked_abilities);
                          $token = array_pop($token_list);
                          $price = $backup_unlocked_abilities[$token];
                          unset($backup_unlocked_abilities[$token]);
                          $ability_list_array[$token] = $price;
                          $ability_list_array_count = count($ability_list_array);
                        }
                      }
                      */

                      // Re-count the ability list after recent changes
                      $ability_list_array_count = count($ability_list_array);
                      $ability_list_array = array_reverse($ability_list_array, true);

                      // Loop through the items and print them one by one
                      $ability_counter = 0;
                      if (!empty($ability_list_array)){
                        foreach ($ability_list_array AS $token => $price){

                          $ability_info = $mmrpg_database_abilities[$token];
                          $ability_info_token = $token;
                          $ability_info_price = $price;
                          $ability_info_name = $ability_info['ability_name'];
                          $ability_info_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
                          if ($ability_info_type != 'none' && !empty($ability_info['ability_type2'])){ $ability_info_type .= '_'.$ability_info['ability_type2']; }
                          elseif ($ability_info_type == 'none' && !empty($ability_info['ability_type2'])){ $ability_info_type = $ability_info['ability_type2']; }
                          $ability_info_quantity = 0;
                          $ability_info_unlocked = array();
                          if (mmrpg_prototype_ability_unlocked('', '', $token)){
                            $ability_info_quantity = 3;
                            $ability_info_unlocked = array('dr-light', 'dr-wily', 'dr-cossack');
                            $ability_info_price = 0;
                          }
                          if (empty($ability_info['ability_flag_complete'])){
                            //$ability_info_name = '<del>'.$ability_info_name.'</del> ';
                            $ability_info_quantity = -1;
                            $ability_info_unlocked = array('coming-soon');
                            $ability_info_name = preg_replace('/[a-z0-9]/i', '?', $ability_info_name);
                            $ability_info_price = 0;
                          }
                          $global_item_quantities[$ability_info_token] = $ability_info_quantity;
                          $global_item_prices['buy'][$ability_info_token] = $ability_info_price;
                          $temp_info_tooltip = !empty($ability_info['ability_flag_complete']) ? mmrpg_ability::print_editor_title_markup($robot_info, $ability_info) : 'Coming Soon! <br /> <span style="font-size:80%;">This ability is still in development and cannot be purchased yet. <br /> Apologies for the inconveinece, and please check back later!</span>';
                          $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);
                          //if ($ability_info_quantity >= 3){ continue; }
                          //if ($ability_counter >= $ability_list_max){ break; }
                          $ability_counter++;
                          $ability_cell_float = $ability_counter % 2 == 0 ? 'right' : 'left';
                          ?>
                          <td class="<?= $item_cell_float ?> item_cell" data-kind="ability" data-action="buy" data-token="<?= $ability_info_token ?>" data-unlocked="<?= implode(',', $ability_info_unlocked) ?>">
                            <span class="item_name ability_type ability_type_<?= $ability_info_type ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $ability_info_name ?></span>
                            <a class="buy_button ability_type ability_type_none" href="#">Buy</a>
                            <label class="item_quantity" data-quantity="0"><?= !empty($ability_info_quantity) ? '&#10004;' : '-' ?></label>
                            <label class="item_price" data-price="<?= $ability_info_price ?>">&hellip; <?= $ability_info_price ?>z</label>
                          </td>
                          <?
                          if ($ability_cell_float == 'right'){ echo '</tr><tr>'; }
                        }
                        if ($ability_counter % 2 != 0){
                          ?>
                          <td class="right item_cell item_cell_disabled">
                            &nbsp;
                          </td>
                          <?
                        }
                      } else {
                        ?>
                        <td class="right item_cell item_cell_disabled" colspan="2" style="text-align: center">
                          <span class="item_name ability_type ability_type_empty" style="float: none; width: 100px; margin: 6px auto; text-align: center;">Sold Out!</span>
                        </td>
                        <?
                      }

                      /*
                      die('<hr />'.
                        '<pre>$backup_unlocked_abilities = '.print_r($backup_unlocked_abilities, true).'</pre><hr />'.
                        '<pre>$ability_list_array = '.print_r($ability_list_array, true).'</pre><hr />'
                        );
                      */

                      ?>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <?
              }
              // -- SHOP SELLING FIELDS -- //
              // If this shop has fields to selling, print them out
              if ($selling_token == 'fields' && !empty($shop_info['shop_fields']['fields_selling'])){
                ?>
                <table class="full" style="margin-bottom: 5px;">
                  <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                  </colgroup>
                  <thead>
                    <tr>
                      <th class="left">
                        <span class="buy_button buy_button_header">&nbsp;</span>
                        <label class="item_price item_price_header">Buy</label>
                      </th>
                      <th class="right">
                        <span class="buy_button buy_button_header">&nbsp;</span>
                        <label class="item_price item_price_header">Buy</label>
                      </th>
                    </tr>
                  </thead>
                </table>
                <div class="scroll_wrapper">
                  <table class="full" style="margin-bottom: 5px;">
                    <colgroup>
                      <col width="50%" />
                      <col width="50%" />
                    </colgroup>
                    <tbody>
                      <tr>
                      <?
                      // Collect the abilities for buying and slice/shuffle if nessary
                      $field_list_array = $shop_info['shop_fields']['fields_selling'];
                      // Collect the unlocked fields for this game file
                      $field_list_unlocked = !empty($_SESSION[$session_token]['values']['battle_fields']) ? $_SESSION[$session_token]['values']['battle_fields'] : array();

                      // Loop through the items and print them one by one
                      $field_counter = 0;
                      foreach ($field_list_array AS $token => $price){
                        if (isset($mmrpg_database_fields[$token])){ $field_info = mmrpg_field::parse_index_info($mmrpg_database_fields[$token]); }
                        else { continue; }
                        $field_info_token = $token;
                        $field_info_price = $price;
                        $field_info_name = $field_info['field_name'];
                        $field_info_master = array();
                        $field_info_type = !empty($field_info['field_type']) ? $field_info['field_type'] : 'none';
                        if (!empty($field_info['field_type2'])){ $field_info_type .= '_'.$field_info['field_type2']; }
                        if (!empty($field_info['field_master']) && !empty($mmrpg_database_robots[$field_info['field_master']])){ $field_info_master = $mmrpg_database_robots[$field_info['field_master']]; }
                        $field_info_unlocked = in_array($field_info_token, $field_list_unlocked) ? true : false;
                        $field_info_hidden = empty($_SESSION['GAME']['values']['robot_database'][$field_info_master['robot_token']]['robot_scanned']) ? true : false;
                        if ($field_info_hidden){
                          $field_info_name = preg_replace('/[a-z]{1}/i', '?', $field_info_name);
                          $global_item_quantities['field-'.$field_info_token] = 1;
                          $global_item_prices['buy']['field-'.$field_info_token] = 0;
                          $temp_master_name = 'an undisclosed robot';
                          //if ($field_info_master){ $temp_master_name = preg_match('/^(a|e|i|o|u)/i', $field_info_master['robot_name']) ? 'an '.$field_info_master['robot_name'] : 'a '.$field_info_master['robot_name']; }
                          if ($field_info_master){ $temp_master_name = preg_match('/^(a|e|i|o|u)/i', $field_info_master['robot_name']) ? 'an '.$field_info_master['robot_name'] : 'a '.$field_info_master['robot_name']; }
                          $temp_info_tooltip = 'My apologies, but I haven\'t finished this one yet. If you encounter '.$temp_master_name.' in battle, would you mind scanning its data for me?';
                        } else {
                          $global_item_quantities['field-'.$field_info_token] = $field_info_unlocked ? 1 : 0;
                          $global_item_prices['buy']['field-'.$field_info_token] = $field_info_unlocked ? 0 : $field_info_price;
                          $temp_info_tooltip = mmrpg_field::print_editor_title_markup($player_info, $field_info);
                          $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);
                        }
                        if ($field_info_unlocked){ $field_info_price = 0; }
                        //if ($field_info_unlocked){ continue; }
                        if ($field_counter >= 24){ break; }

                        $field_counter++;
                        $field_cell_float = $field_counter % 2 == 0 ? 'right' : 'left';
                        ?>
                        <td class="<?= $item_cell_float ?> item_cell" data-kind="field" data-action="buy" data-token="<?= 'field-'.$field_info_token ?>">
                          <span class="item_name field_type field_type_<?= $field_info_type ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $field_info_name ?></span>
                          <a class="buy_button field_type field_type_none" href="#">Buy</a>
                          <label class="item_quantity" data-quantity="0" style="display: none;">x 0</label>
                          <label class="item_price" data-price="<?= $field_info_price ?>">&hellip; <?= $field_info_price ?>z</label>
                        </td>
                        <?
                        if ($field_cell_float == 'right'){ echo '</tr><tr>'; }
                      }
                      if ($field_counter % 2 != 0){
                        ?>
                        <td class="right item_cell item_cell_disabled">
                          &nbsp;
                        </td>
                        <?
                      }
                      ?>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <?
              }
              ?>
              <table class="full" style="margin-bottom: 5px;">
                <colgroup>
                  <col width="100%" />
                </colgroup>
                <tbody>
                  <tr>
                    <td colspan="2" class="left item_cell_confirm" data-kind="" data-action="" data-token="" data-price="" data-quantity="">
                      <div class="placeholder">&hellip;</div>
                    </td>
                  </tr>
                </tbody>
              </table>

            </div>
            <?

          }
          // Loop through the buying tokens and display tabs for them
          foreach ($shop_buying_tokens AS $buying_token){

            ?>
            <div class="tab_container tab_container_buying" data-tab="buying" data-tab-type="<?= $buying_token ?>">

              <div class="shop_quote shop_quote_buying">&quot;<?= isset($shop_info['shop_quote_buying'][$buying_token]) ? $shop_info['shop_quote_buying'][$buying_token] : $shop_info['shop_quote_buying'] ?>&quot;</div>
              <?
              // -- SHOP BUYING ITEMS -- //
              // If this shop has items to buying, print them out
              if (in_array($buying_token, array('items', 'cores')) && !empty($shop_info['shop_items']['items_buying'])){
                ?>
                <table class="full" style="margin-bottom: 5px;">
                  <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                  </colgroup>
                  <thead>
                    <tr>
                      <th class="left">
                        <span class="sell_button sell_button_header">&nbsp;</span>
                        <label class="item_quantity item_quantity_header">Own</label>
                        <label class="item_price item_price_header">Sell</label>
                      </th>
                      <th class="right">
                        <span class="sell_button sell_button_header">&nbsp;</span>
                        <label class="item_quantity item_quantity_header">Own</label>
                        <label class="item_price item_price_header">Sell</label>
                      </th>
                    </tr>
                  </thead>
                </table>
                <div class="scroll_wrapper">
                  <table class="full" style="margin-bottom: 5px;">
                    <colgroup>
                      <col width="50%" />
                      <col width="50%" />
                    </colgroup>
                    <tbody>
                      <tr>
                      <?
                      // Collect the items for buying and slice/shuffle if nessary
                      $item_list_array = $shop_info['shop_items']['items_buying'];
                      /*
                      $item_list_array_screws = array_slice($item_list_array, -2, 2, true);
                      if ($global_points_counter >= 0 && $global_points_counter < 1000){ $item_list_array = array_slice($item_list_array, 0, 6, true); }
                      elseif ($global_points_counter >= 1000 && $global_points_counter < 10000){ $item_list_array = array_slice($item_list_array, 0, 8, true); }
                      elseif ($global_points_counter >= 10000 && $global_points_counter < 100000){ $item_list_array = array_slice($item_list_array, 0, 14, true); }
                      elseif ($global_points_counter >= 100000 && $global_points_counter < 1000000){ $item_list_array = array_slice($item_list_array, 0, 16, true); }
                      elseif ($global_points_counter >= 1000000 && $global_points_counter < 10000000){ $item_list_array = array_slice($item_list_array, 0, 18, true); }
                      elseif ($global_points_counter >= 10000000){ $item_list_array = array_slice($item_list_array, 0, -2, true); }
                      //else { $item_list_array = array_slice($item_list_array, 0, 18); }
                      $item_list_array = array_merge($item_list_array, $item_list_array_screws);
                      */
                      // Loop through the items and print them one by one
                      $item_counter = 0;
                      foreach ($item_list_array AS $token => $price){
                        if (isset($mmrpg_database_items[$token])){ $item_info = $mmrpg_database_items[$token]; }
                        else { continue; }
                        $item_counter++;
                        $item_info_token = $token;
                        $item_info_price = $price;
                        $item_info_name = $item_info['ability_name'];
                        $item_info_quantity = !empty($_SESSION[$session_token]['values']['battle_items'][$token]) ? $_SESSION[$session_token]['values']['battle_items'][$token] : 0;
                        $item_info_type = !empty($item_info['ability_type']) ? $item_info['ability_type'] : 'none';
                        if ($item_info_type != 'none' && !empty($item_info['ability_type2'])){ $item_info_type .= '_'.$item_info['ability_type2']; }
                        elseif ($item_info_type == 'none' && !empty($item_info['ability_type2'])){ $item_info_type = $item_info['ability_type2']; }

                        $global_item_quantities[$item_info_token] = $item_info_quantity;
                        $global_item_prices['sell'][$item_info_token] = $item_info_price;

                        $item_cell_float = $item_counter % 2 == 0 ? 'right' : 'left';
                        $temp_info_tooltip = mmrpg_ability::print_editor_title_markup($robot_info, $item_info, array('show_quantity' => false));
                        $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);

                        ?>
                        <td class="<?= $item_cell_float ?> item_cell" data-kind="item" data-action="sell" data-token="<?= $item_info_token ?>">
                          <span class="item_name ability_type ability_type_<?= $item_info_type ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $item_info_name ?></span>
                          <a class="sell_button ability_type ability_type_none" href="#">Sell</a>
                          <label class="item_quantity" data-quantity="0">x 0</label>
                          <label class="item_price" data-price="<?= $item_info_price ?>">&hellip; <?= $item_info_price ?>z</label>
                        </td>
                        <?
                        if ($item_cell_float == 'right'){ echo '</tr><tr>'; }
                      }
                      if ($item_counter % 2 != 0){
                        ?>
                        <td class="right item_cell item_cell_disabled">
                          &nbsp;
                        </td>
                        <?
                      }
                      ?>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <?
              }
              // -- SHOP BUYING STARS -- //
              // If this shop has items to buying, print them out
              if ($buying_token == 'stars' && !empty($shop_info['shop_stars']['stars_buying'])){
                ?>
                <table class="full" style="margin-bottom: 5px;">
                  <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                  </colgroup>
                  <thead>
                    <tr>
                      <th class="left">
                        <span class="buy_button buy_button_header">&nbsp;</span>
                        <label class="item_price item_price_header">Sell</label>
                      </th>
                      <th class="right">
                        <span class="buy_button buy_button_header">&nbsp;</span>
                        <label class="item_price item_price_header">Sell</label>
                      </th>
                    </tr>
                  </thead>
                </table>
                <div class="scroll_wrapper">
                  <table class="full" style="margin-bottom: 5px;">
                    <colgroup>
                      <col width="50%" />
                      <col width="50%" />
                    </colgroup>
                    <tbody>
                      <tr>
                      <?
                      // Collect the stars for buying and slice/shuffle if nessary
                      //$star_list_array = $shop_info['shop_stars']['stars_buying'];
                      //$star_list_array = array_keys($_SESSION[$session_token]['values']['battle_stars']);
                      $temp_star_counter = 4;
                      if ($global_points_counter >= 10000){ $temp_star_counter = 6; }
                      if ($global_points_counter >= 100000){ $temp_star_counter = 10; }
                      if ($global_points_counter >= 1000000){ $temp_star_counter = 16; }
                      if ($global_points_counter >= 10000000){ $temp_star_counter = 24; }
                      if ($global_points_counter >= 100000000){ $temp_star_counter = 34; }

                      // Collect the stars for buying and slice/shuffle if nessary
                      $temp_session_key = 'star_list_array_raw';
                      $star_list_array_raw = !empty($_SESSION[$session_token]['SHOP'][$temp_session_key]) ? $_SESSION[$session_token]['SHOP'][$temp_session_key] : array();
                      if (empty($star_list_array_raw) || $star_list_array_raw['date'] != date('Y-m-d')){
                        $star_list_array_raw = array();
                        $star_list_array_raw['date'] = date('Y-m-d');
                        $star_list_array_raw['today'] = array();

                        // Collect all the star tokens sorted by their kind
                        $temp_star_tokens = array();
                        $temp_base_tokens = array();

                        // Collect all possible base field tokens for seeking
                        foreach ($this_omega_factors_one AS $info){ $temp_base_tokens[] = $info['field']; }
                        foreach ($this_omega_factors_two AS $info){ $temp_base_tokens[] = $info['field']; }
                        foreach ($this_omega_factors_three AS $info){ $temp_base_tokens[] = $info['field']; }
                        $temp_unlocked_fields = !empty($_SESSION[$session_token]['values']['battle_fields']) ? $_SESSION[$session_token]['values']['battle_fields'] : array();
                        foreach ($this_omega_factors_four AS $key => $factor){ if (in_array($factor['field'], $temp_unlocked_fields)){ $temp_base_tokens[] = $factor['field']; } }
                        // Shuffle the order of the field stars
                        shuffle($temp_base_tokens);

                        // Define the first eight field star tokens
                        $temp_field_star_tokens = $temp_base_tokens;
                        $temp_field_star_tokens = array_slice($temp_field_star_tokens, 0, $temp_star_counter);

                        // Loop through and index collected star info
                        foreach ($temp_field_star_tokens AS $key => $token){

                          // Collect the info for this base field and create the star
                          $field_info = mmrpg_field::parse_index_info($mmrpg_database_fields[$token]);
                          if (isset($_SESSION[$session_token]['values']['battle_stars'][$token])){ $star_info = $_SESSION[$session_token]['values']['battle_stars'][$token]; }
                          else { $star_info = array('star_token' => $token, 'star_name' => $field_info['field_name'], 'star_kind' => 'field', 'star_type' => $field_info['field_type'], 'star_type2' => '', 'star_player' => '', 'star_date' => ''); }
                          $star_list_array_raw['today'][$star_info['star_token']] = $star_info;
                          $temp_star_tokens[] = $star_info['star_token'];

                          // Collect the two fusion field token info and create stars
                          $key2 = $key + $temp_star_counter;
                          $key3 = $key2 + $temp_star_counter;
                          $token2 = isset($temp_base_tokens[$key2]) ? $temp_base_tokens[$key2] : $temp_base_tokens[$key2 % count($temp_base_tokens)];
                          $token3 = isset($temp_base_tokens[$key3]) ? $temp_base_tokens[$key3] : $temp_base_tokens[$key3 % count($temp_base_tokens)];
                          $field_info2 = mmrpg_field::parse_index_info($mmrpg_database_fields[$token2]);
                          $field_info3 = mmrpg_field::parse_index_info($mmrpg_database_fields[$token3]);
                          $fusion_token = preg_replace('/-([a-z0-9]+)$/i', '', $token2).'-'.preg_replace('/^([a-z0-9]+)-/i', '', $token3);
                          $fusion_name = preg_replace('/\s+([a-z0-9]+)$/i', '', $field_info2['field_name']).' '.preg_replace('/^([a-z0-9]+)\s+/i', '', $field_info3['field_name']);
                          $fusion_type = !empty($field_info2['field_type']) ? $field_info2['field_type'] : '';
                          $fusion_type2 = !empty($field_info3['field_type']) ? $field_info3['field_type'] : '';
                          if (isset($_SESSION[$session_token]['values']['battle_stars'][$fusion_token])){ $star_info = $_SESSION[$session_token]['values']['battle_stars'][$fusion_token]; }
                          else { $star_info = array('star_token' => $fusion_token, 'star_name' => $fusion_name, 'star_kind' => 'fusion', 'star_type' => $fusion_type, 'star_type2' => $fusion_type2, 'star_player' => '', 'star_date' => ''); }
                          $star_list_array_raw['today'][$star_info['star_token']] = $star_info;
                          $temp_star_tokens[] = $star_info['star_token'];

                          //$temp_star_tokens[] = "\$key2 = {$key2}; \$key3 = {$key3};\n\$token2 = {$token2}; \$token3 = {$token3}; ";

                        }

                        /*
                        ob_end_clean();
                        die(
                        '<pre>$temp_base_tokens = '.print_r($temp_base_tokens, true).'</pre>'.
                        '<pre>$temp_star_tokens = '.print_r($temp_star_tokens, true).'</pre>'.
                        '<pre>$star_list_array_raw = '.print_r($star_list_array_raw, true).'</pre>'
                        );
                        */

                        // Update the session with the new array in raw format
                        $_SESSION[$session_token]['SHOP'][$temp_session_key] = $star_list_array_raw;

                      }

                      // Reformat the list arrays to what we need them for
                      $star_list_array = array_keys($star_list_array_raw['today']);

                      /*
                      ob_end_clean();
                      die(
                      '<pre>$star_list_array = '.print_r($star_list_array, true).'</pre>'.
                      '<pre>$star_list_array_raw = '.print_r($star_list_array_raw, true).'</pre>'
                      );
                      */

                      // Loop through the items and print them one by one
                      $star_counter = 0;
                      foreach ($star_list_array AS $key => $token){
                        $star_counter++;

                        $star_cell_float = $star_counter % 2 == 0 ? 'right' : 'left';

                        $star_info_token = $token;
                        $star_info = $star_list_array_raw['today'][$token];

                        $star_info_price = $shop_info['shop_stars']['stars_buying'][$star_info['star_kind']];
                        $star_info_name = $star_info['star_name'].' Star';
                        $star_info_date = !empty($star_info['star_date']) ? $star_info['star_date'] : 0;

                        $star_info_type = !empty($star_info['star_type']) ? $star_info['star_type'] : '';
                        $star_info_type2 = !empty($star_info['star_type2']) ? $star_info['star_type2'] : '';
                        $star_info_class = !empty($star_info_type) ? $star_info_type : 'none';
                        if (!empty($star_info_type2)){ $star_info_class .= '_'.$star_info_type2; }

                        if (!empty($star_info_type) && !empty($this_star_force[$star_info_type])){
                          $temp_force = $this_star_force[$star_info_type];
                          $star_info_price += round($temp_force * 10);
                        }
                        if (!empty($star_info_type2) && !empty($this_star_force[$star_info_type2])){
                          $temp_force2 = $this_star_force[$star_info_type2];
                          $star_info_price += round($temp_force2 * 10);
                        }

                        $global_item_quantities['star-'.$star_info_token] = !empty($_SESSION[$session_token]['values']['battle_stars'][$star_info_token]) ? 1 : 0;
                        $global_item_prices['sell']['star-'.$star_info_token] = $star_info_price;

                        $temp_info_tooltip = $star_info_name.'<br /> ';
                        $temp_info_tooltip .= '<span style="font-size:80%;">';
                        $temp_info_tooltip .= ucfirst($star_info['star_kind']).' Star | '.ucwords(str_replace('_', ' / ', $star_info_type)).' Type';
                        if (!empty($star_info_date)){ $temp_info_tooltip .= ' <br />Found '.date('Y/m/d', $star_info_date); }
                        $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);
                        $temp_info_tooltip .= '</span>';

                        ?>
                        <td class="<?= $star_cell_float ?> item_cell" data-kind="star" data-action="sell" data-token="<?= 'star-'.$star_info_token ?>">
                          <span class="item_name ability_type ability_type_<?= $star_info_class ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $star_info_name ?></span>
                          <a class="sell_button ability_type ability_type_none" href="#">Sell</a>
                          <label class="item_quantity" data-quantity="1" style="display: none;">x 1</label>
                          <label class="item_price" data-price="<?= $star_info_price ?>">&hellip; <?= $star_info_price ?>z</label>
                        </td>
                        <?
                        if ($star_cell_float == 'right'){ echo '</tr><tr>'; }
                      }
                      if ($star_counter % 2 != 0){
                        ?>
                        <td class="right item_cell item_cell_disabled">
                          &nbsp;
                        </td>
                        <?
                      }
                      ?>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <?
              }
              ?>
              <table class="full" style="margin-bottom: 5px;">
                <colgroup>
                  <col width="100%" />
                </colgroup>
                <tbody>
                  <tr>
                    <td colspan="2" class="left item_cell_confirm" data-kind="" data-action="" data-token="" data-price="" data-quantity="">
                      <div class="placeholder">&hellip;</div>
                    </td>
                  </tr>
                </tbody>
              </table>

            </div>
            <?

          }
          ?>

        </div>

      </div>
    </div>
    <?

    // Increment the key counter
    $key_counter++;

  }

  // Collect the contents of the buffer
  $shop_console_markup = ob_get_clean();
  $shop_console_markup = preg_replace('/\s+/', ' ', trim($shop_console_markup));

}

// Generate the edit markup using the battles settings and rewards
$this_shop_markup = '';
if (true){
  // Prepare the output buffer
  ob_start();

  // Determine the token for the very first player in the edit
  $temp_shop_tokens = array_keys($allowed_edit_data);
  $first_shop_token = array_shift($temp_shop_tokens);
  $first_shop_token = isset($first_shop_token['shop_token']) ? $first_shop_token['shop_token'] : $first_shop_token;
  unset($temp_shop_tokens);

  // Start generating the edit markup
  ?>

  <span class="header block_1">Item Shop (<span id="zenny_counter"><?= number_format($global_zenny_counter, 0, '.', ',') ?></span> Zenny)</span>

  <div style="float: left; width: 100%;">
  <table class="formatter" style="width: 100%; table-layout: fixed;">
    <colgroup>
      <col width="70" />
      <col width="" />
    </colgroup>
    <tbody>
      <tr>
        <td class="canvas" style="vertical-align: top;">

          <div id="canvas" class="shop_counter_<?= $shop_counter ?>" style="">
            <div id="links"></div>
          </div>

        </td>
        <td class="console" style="vertical-align: top;">

          <div id="console" class="noresize" style="height: auto;">
            <div id="shops" class="wrapper"></div>
          </div>

        </td>
      </tr>
    </tbody>
  </table>
  </div>

  <?

  // Collect the output buffer content
  $this_shop_markup = preg_replace('#\s+#', ' ', trim(ob_get_clean()));
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title><?= !MMRPG_CONFIG_IS_LIVE ? '@ ' : '' ?>View Shops | Mega Man RPG Prototype | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="shops" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link rel="shortcut icon" type="image/x-icon" href="images/assets/favicon<?= !MMRPG_CONFIG_IS_LIVE ? '-local' : '' ?>.ico">
<link type="text/css" href="styles/jquery.scrollbar.min.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/shop.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
  <div id="prototype" class="hidden" style="opacity: 0; <?= !$global_allow_editing ? 'width: 100% !important; ' : '' ?>">
    <div id="shop" class="menu" style="position: relative;">
      <div id="shop_overlay" style="border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background-color: rgba(0, 0, 0, 0.75); position: absolute; top: 50px; left: 6px; right: 4px; height: 340px; z-index: 9999; display: none;">&nbsp;</div>
      <?= $this_shop_markup ?>
    </div>
  </div>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/shop.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = <?= isset($_GET['fadein']) ? $_GET['fadein'] : 'true' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
gameSettings.autoScrollTop = false;
gameSettings.allowShopping = true;
// Update the player and player count by counting elements
thisShopData.unlockedPlayers = <?= json_encode(array_keys($_SESSION[$session_token]['values']['battle_rewards'])) ?>;
thisShopData.zennyCounter = <?= $global_zenny_counter ?>;
thisShopData.itemPrices = <?= json_encode($global_item_prices) ?>;
thisShopData.itemQuantities = <?= json_encode($global_item_quantities) ?>;
// Define the global arrays to hold the shop console and canvas markup
var shopCanvasMarkup = '<?= str_replace("'", "\'", $shop_canvas_markup) ?>';
var shopConsoleMarkup = '<?= str_replace("'", "\'", $shop_console_markup) ?>';
<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags']['events'])){ $_SESSION[$session_token]['flags']['events'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags']['events'];
$temp_event_shown = false;
// If this is the first time using the editor, display the introductory area
$temp_event_flag = 'unlocked-tooltip_shop-auto-intro';
if (!$temp_event_shown && !empty($allowed_edit_data['auto']) && empty($temp_game_flags[$temp_event_flag]) && $global_allow_editing){
  $temp_game_flags[$temp_event_flag] = true;
  $temp_event_shown = true;
  ?>
  // Generate a first-time event canvas that explains how the editor works
  gameSettings.windowEventsCanvas = [
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_foreground_base.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/shops/auto/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 20px; left: 250px; width: 80px; height: 80px;">&nbsp;</div>'+
    ''
    ];
  // Generate a first-time event message that explains how the editor works
  gameSettings.windowEventsMessages = [
    '<p>Congratulations! <strong>Auto\'s Shop</strong> has been unlocked! Items can be purchased and sold in Auto\'s Shop using a digital currency called Zenny, and the only ways to earn Zenny are by selling items you find in battle or by scoring overkill bonuses at the end of a mission.</p>'+
    '<p>Use the Buy or Sell tabs to switch between modes, and then click any of the Buy or Sell buttons to make your selection.  A confirmation box will appear below to finalize your request.  Clicking a button multiple times will increase the quantity, helpful for bulk transactions.</p>'+
    '<p>Auto has made himself available to our players out of devotion to his creator Dr. Light, but he\'s also on a secret mission to find more of his favourite thing - screws.  Bring him any screws you find and he\'ll likely pay a premium price.</p>'+
    ''
    ];
  // Push this event to the parent window and display to the user
  top.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
  <?
}
// If this is the first time using the editor, display the introductory area
$temp_event_flag = 'unlocked-tooltip_reggae-auto-intro';
if (!$temp_event_shown && !empty($allowed_edit_data['reggae']) && empty($temp_game_flags[$temp_event_flag]) && $global_allow_editing){
  $temp_game_flags[$temp_event_flag] = true;
  $temp_event_shown = true;
  ?>
  // Generate a first-time event canvas that explains how the editor works
  gameSettings.windowEventsCanvas = [
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/wily-castle/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/wily-castle/battle-field_foreground_base.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/shops/reggae/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 20px; left: 250px; width: 80px; height: 80px;">&nbsp;</div>'+
    ''
    ];
  // Generate a first-time event message that explains how the editor works
  gameSettings.windowEventsMessages = [
    '<p>Congratulations! <strong>Reggae\'s Shop</strong> has been unlocked! Abilities can be purchased in Reggae\'s Shop using a digital currency called Zenny, and the only way to earn Zenny is by selling the items or cores you find in battle.</p>'+
    '<p>Reggae has made himself available to our players out of devotion to his creator Dr. Wily, but he\'s also on a secret mission to collect robot cores - something he believes will be a very lucrative business in the near future.  Bring him any cores you find and he\'ll likely pay a premium price for them.</p>'+
    ''
    ];
  // Push this event to the parent window and display to the user
  top.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
  <?
}
// If this is the first time using the editor, display the introductory area
$temp_event_flag = 'unlocked-tooltip_kalinka-shop-intro';
if (!$temp_event_shown && !empty($allowed_edit_data['kalinka']) && empty($temp_game_flags[$temp_event_flag]) && $global_allow_editing){
  $temp_game_flags[$temp_event_flag] = true;
  $temp_event_shown = true;
  ?>
  // Generate a first-time event canvas that explains how the editor works
  gameSettings.windowEventsCanvas = [
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/cossack-citadel/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/cossack-citadel/battle-field_foreground_base.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/shops/kalinka/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 20px; left: 250px; width: 80px; height: 80px;">&nbsp;</div>'+
    ''
    ];
  // Generate a first-time event message that explains how the editor works
  gameSettings.windowEventsMessages = [
    '<p>Congratulations! <strong>Kalinka\'s Shop</strong> has been unlocked! New battle fields can be purchased in Kalinka\'s Shop using a digital currency called Zenny, and the only way to earn Zenny is by selling the items, cores, or stars you find in battle.</p>'+
    '<p>Kalinka has made herself available to our players out of devotion to her father Dr. Cossack, but she\'s also on a secret mission to collect field and fusion stars as she believes the mysterious starforce energy warrants further study.  Check back each day to see which stars she is currently seeking.</p>'+
    ''
    ];
  // Push this event to the parent window and display to the user
  top.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
  <?
}
?>
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_bottom.php');
// Unset the database variable
unset($DB);
?>
