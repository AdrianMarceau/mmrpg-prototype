<?
// If the session token has not been set
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }


// -- COLLECT ENVIRONMENT VARIABLES -- //

// Collect the field stars from the session variable
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
if (!isset($_SESSION[$session_token]['values']['battle_shops'])){ $_SESSION[$session_token]['values']['battle_shops'] = array(); }
$this_battle_shops = !empty($_SESSION[$session_token]['values']['battle_shops']) ? $_SESSION[$session_token]['values']['battle_shops'] : array();
$this_battle_shops_count = !empty($this_battle_shops) ? count($this_battle_shops) : 0;

// Define the array to hold all the item quantities
$global_item_quantities = array();
$global_item_prices = array();
$global_zenny_counter = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
$global_points_counter = !empty($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;

// Define the global counters for unlocked robot cores and ability types
$global_unlocked_robots = mmrpg_prototype_robot_tokens_unlocked();
$global_unlocked_abilities = mmrpg_prototype_ability_tokens_unlocked();
$global_unlocked_robots_cores = array();
$global_unlocked_abilities_types = array();

// Create type counters for each element
if (!empty($mmrpg_database_types)){
  foreach ($mmrpg_database_types AS $type => $info){
    $global_unlocked_robots_cores[$type] = 0;
    $global_unlocked_abilities_types[$type] = 0;
  }
}

// Loop through robots and count the number of cores represented
if (!empty($global_unlocked_robots)){
  foreach ($global_unlocked_robots AS $token){
    if (!isset($mmrpg_database_robots[$token])){ continue; }
    $info = $mmrpg_database_robots[$token];
    $core1 = !empty($info['robot_core']) ? $info['robot_core'] : 'none';
    $core2 = !empty($info['robot_core2']) ? $info['robot_core2'] : '';
    if (!empty($core1)){ $global_unlocked_robots_cores[$core1]++; }
    if (!empty($core2)){ $global_unlocked_robots_cores[$core2]++; }
  }
}

// Loop through abilities and count the number of types represented
if (!empty($global_unlocked_abilities)){
  foreach ($global_unlocked_abilities AS $token){
    if (!isset($mmrpg_database_abilities[$token])){ continue; }
    $info = $mmrpg_database_abilities[$token];
    $type1 = !empty($info['ability_type']) ? $info['ability_type'] : 'none';
    $type2 = !empty($info['ability_type2']) ? $info['ability_type2'] : '';
    if (!empty($type1)){ $global_unlocked_abilities_types[$type1]++; }
    if (!empty($type2)){ $global_unlocked_abilities_types[$type2]++; }
  }
}

/*
die('<hr />'.
  '<pre>$global_unlocked_robots = '.print_r($global_unlocked_robots, true).'</pre><hr />'.
  '<pre>$global_unlocked_robots_cores = '.print_r($global_unlocked_robots_cores, true).'</pre><hr />'.
  '<pre>$global_unlocked_abilities = '.print_r($global_unlocked_abilities, true).'</pre><hr />'.
  '<pre>$global_unlocked_abilities_types = '.print_r($global_unlocked_abilities_types, true).'</pre><hr />'
  );
*/


// -- DEFINE SHOP INDEXES -- //

// Define the array to hold all the shop data
$this_shop_index = array();

// AUTO'S SHOP
$this_shop_index['auto'] = array(
  'shop_token' => 'auto',
  'shop_name' => 'Auto\'s Shop',
  'shop_owner' => 'Auto',
  'shop_unlock' => 'Complete intro field as any player.',
  'shop_seeking' => 'screws',
  'shop_colour' => 'nature',
  'shop_field' => 'light-laboratory',
  'shop_player' => 'dr-light',
  'shop_number' => 'SHOP-001',
  'shop_kind_selling' => 'items',
  'shop_kind_buying' => 'items',
  'shop_quote_selling' => 'Welcome to Auto\'s Shop! I\'ve got lots of useful items for sale, so let me know if you need anything.',
  'shop_quote_buying' => 'So you wanna sell something, eh? Let\'s see what you\'ve collected so far! Hopefully lots of screws!',
  'shop_items' => array(
    'items_selling' => array(

      'item-energy-pellet' => 200,
      'item-weapon-pellet' => 200

      ),
    'items_selling2' => array(

      'item-energy-pellet' => 200,
      'item-weapon-pellet' => 200,
      'item-energy-capsule' => 400,
      'item-weapon-capsule' => 400

      ),
    'items_selling3' => array(

      'item-energy-pellet' => 200,
      'item-weapon-pellet' => 200,
      'item-energy-capsule' => 400,
      'item-weapon-capsule' => 400,
      'item-energy-tank' => 800,
      'item-weapon-tank' => 800

      ),
    'items_selling4' => array(

      'item-energy-pellet' => 200,
      'item-weapon-pellet' => 200,
      'item-energy-capsule' => 400,
      'item-weapon-capsule' => 400,
      'item-energy-tank' => 800,
      'item-weapon-tank' => 800,

      'item-attack-pellet' => 400,
      'item-defense-pellet' => 400,
      'item-speed-pellet' => 400,
      'item-super-pellet' => 1200,

      ),
    'items_selling5' => array(

      'item-energy-pellet' => 200,
      'item-weapon-pellet' => 200,
      'item-energy-capsule' => 400,
      'item-weapon-capsule' => 400,
      'item-energy-tank' => 800,
      'item-weapon-tank' => 800,

      'item-attack-pellet' => 400,
      'item-defense-pellet' => 400,
      'item-attack-capsule' => 800,
      'item-defense-capsule' => 800,
      'item-speed-pellet' => 400,
      'item-super-pellet' => 1200,
      'item-speed-capsule' => 800,
      'item-super-capsule' => 2400

      ),
    'items_selling6' => array(

      'item-energy-pellet' => 200,
      'item-weapon-pellet' => 200,
      'item-energy-capsule' => 400,
      'item-weapon-capsule' => 400,
      'item-energy-tank' => 800,
      'item-weapon-tank' => 800,

      'item-attack-pellet' => 400,
      'item-defense-pellet' => 400,
      'item-attack-capsule' => 800,
      'item-defense-capsule' => 800,
      'item-speed-pellet' => 400,
      'item-super-pellet' => 1200,
      'item-speed-capsule' => 800,
      'item-super-capsule' => 2400,

      'item-extra-life' => 1600,
      'item-yashichi' => 1600

      ),
    'items_selling7' => array(

      'item-energy-pellet' => 200,
      'item-weapon-pellet' => 200,
      'item-energy-capsule' => 400,
      'item-weapon-capsule' => 400,
      'item-energy-tank' => 800,
      'item-weapon-tank' => 800,

      'item-attack-pellet' => 400,
      'item-defense-pellet' => 400,
      'item-attack-capsule' => 800,
      'item-defense-capsule' => 800,
      'item-speed-pellet' => 400,
      'item-super-pellet' => 1200,
      'item-speed-capsule' => 800,
      'item-super-capsule' => 2400,

      'item-extra-life' => 1600,
      'item-yashichi' => 1600,

      'item-score-ball-red' => 5000,
      'item-score-ball-blue' => 10000,
      'item-score-ball-green' => 5000,
      'item-score-ball-purple' => 10000

      ),
    'items_buying' => array(

      'item-screw-small' => 100,
      'item-screw-large' => 1000,

      'item-energy-pellet' => 100,
      'item-weapon-pellet' => 100,
      'item-energy-capsule' => 200,
      'item-weapon-capsule' => 200,
      'item-energy-tank' => 400,
      'item-weapon-tank' => 400,

      'item-attack-pellet' => 200,
      'item-defense-pellet' => 200,
      'item-attack-capsule' => 400,
      'item-defense-capsule' => 400,
      'item-speed-pellet' => 200,
      'item-super-pellet' => 600,
      'item-speed-capsule' => 400,
      'item-super-capsule' => 1200,

      'item-extra-life' => 800,
      'item-yashichi' => 800

      )
    )
  );

// REGGAE'S SHOP
$this_shop_index['reggae'] = array(
  'shop_token' => 'reggae',
  'shop_name' => 'Reggae\'s Shop',
  'shop_owner' => 'Reggae',
  'shop_unlock' => 'Unlock all three playable characters.',
  'shop_seeking' => 'cores',
  'shop_colour' => 'explode',
  'shop_field' => 'wily-castle',
  'shop_player' => 'dr-wily',
  'shop_number' => 'SHOP-002',
  'shop_kind_selling' => 'abilities',
  'shop_kind_buying' => 'cores',
  'shop_quote_selling' => 'Reggae\'s Shop this is! Squawk! New abilities you want! Squaaawk! Give me your zenny! Squaaaawk!',
  'shop_quote_buying' => 'Reggae wants robot cores, robot cores! Squawk! No other items will do, will do! Squaaaaawk!',
  'shop_abilities' => array(
    'abilities_selling' => array(

      'buster-charge' => 3000, 'buster-relay' => 3000,

      'attack-mode' => 6000, 'defense-mode' => 6000, 'speed-mode' => 6000, 'repair-mode' => 6000,

      'field-support' => 12000, 'mecha-support' => 12000

      ),
    'abilities_selling_none' => array(

      'experience-booster' => 3000,
      'damage-booster' => 6000, 'recovery-booster' => 6000,
      'experience-breaker' => 9000,
      'damage-breaker' => 12000, 'recovery-breaker' => 12000

      ),
    'abilities_selling_cutter' => array(

      'cutter-shot' => 3000,
      'attack-hone' => 6000, 'defense-hone' => 6000, 'speed-hone' => 6000,
      'cutter-buster' => 9000,
      'attack-blunt' => 12000, 'defense-blunt' => 12000, 'speed-blunt' => 12000,
      'cutter-overdrive' => 15000

      ),
    'abilities_selling_impact' => array(

      'impact-shot' => 3000,
      'attack-temper' => 6000, 'defense-temper' => 6000, 'speed-temper' => 6000,
      'impact-buster' => 9000,
      'attack-hammer' => 12000, 'defense-hammer' => 12000, 'speed-hammer' => 12000,
      'impact-overdrive' => 15000

      ),
    'abilities_selling_freeze' => array(

      'freeze-shot' => 3000,
      'attack-cool' => 6000, 'defense-cool' => 6000, 'speed-cool' => 6000,
      'freeze-buster' => 9000,
      'attack-chill' => 12000, 'defense-chill' => 12000, 'speed-chill' => 12000,
      'freeze-overdrive' => 15000

      ),
    'abilities_selling_explode' => array(

      'explode-shot' => 3000,
      'attack-cool' => 6000, 'defense-cool' => 6000, 'speed-cool' => 6000,
      'explode-buster' => 9000,
      'attack-chill' => 12000, 'defense-chill' => 12000, 'speed-chill' => 12000,
      'explode-overdrive' => 15000

      ),
    'abilities_selling_flame' => array(

      'flame-shot' => 3000,
      'attack-blaze' => 6000, 'defense-blaze' => 6000, 'speed-blaze' => 6000,
      'flame-buster' => 9000,
      'attack-burn' => 12000, 'defense-burn' => 12000, 'speed-burn' => 12000,
      'flame-overdrive' => 15000

      ),
    'abilities_selling_electric' => array(

      'electric-shot' => 3000,
      'attack-charge' => 6000, 'defense-charge' => 6000, 'speed-charge' => 6000,
      'electric-buster' => 9000,
      'attack-shock' => 12000, 'defense-shock' => 12000, 'speed-shock' => 12000,
      'electric-overdrive' => 15000

      ),
    'abilities_selling_time' => array(

      'time-shot' => 3000,
      'attack-haste' => 6000, 'defense-haste' => 6000, 'speed-haste' => 6000,
      'time-buster' => 9000,
      'attack-slow' => 12000, 'defense-slow' => 12000, 'speed-slow' => 12000,
      'time-overdrive' => 15000

      ),
    'abilities_selling_earth' => array(

      'earth-shot' => 3000,
      'attack-harden' => 6000, 'defense-harden' => 6000, 'speed-harden' => 6000,
      'earth-buster' => 9000,
      'attack-crumble' => 12000, 'defense-crumble' => 12000, 'speed-crumble' => 12000,
      'earth-overdrive' => 15000

      ),
    'abilities_selling_wind' => array(

      'wind-shot' => 3000,
      'attack-breeze' => 6000, 'defense-breeze' => 6000, 'speed-breeze' => 6000,
      'wind-buster' => 9000,
      'attack-squall' => 12000, 'defense-squall' => 12000, 'speed-squall' => 12000,
      'wind-overdrive' => 15000

      ),
    'abilities_selling_water' => array(

      'water-shot' => 3000,
      'attack-douse' => 6000, 'defense-douse' => 6000, 'speed-douse' => 6000,
      'water-buster' => 9000,
      'attack-drench' => 12000, 'defense-drench' => 12000, 'speed-drench' => 12000,
      'water-overdrive' => 15000

      ),
    'abilities_selling_swift' => array(

      'swift-shot' => 3000,
      'attack-surge' => 6000, 'defense-surge' => 6000, 'speed-surge' => 6000,
      'swift-buster' => 9000,
      'attack-stall' => 12000, 'defense-stall' => 12000, 'speed-stall' => 12000,
      'swift-overdrive' => 15000

      ),
    'abilities_selling_nature' => array(

      'nature-shot' => 3000,
      'attack-growth' => 6000, 'defense-growth' => 6000, 'speed-growth' => 6000,
      'nature-buster' => 9000,
      'attack-decay' => 12000, 'defense-decay' => 12000, 'speed-decay' => 12000,
      'nature-overdrive' => 15000

      ),
    'abilities_selling_missile' => array(

      'missile-shot' => 3000,
      'attack-rocket' => 6000, 'defense-rocket' => 6000, 'speed-rocket' => 6000,
      'missile-buster' => 9000,
      'attack-torpedo' => 12000, 'defense-torpedo' => 12000, 'speed-torpedo' => 12000,
      'missile-overdrive' => 15000

      ),
    'abilities_selling_crystal' => array(

      'crystal-shot' => 3000,
      'attack-polish' => 6000, 'defense-polish' => 6000, 'speed-polish' => 6000,
      'crystal-buster' => 9000,
      'attack-tarnish' => 12000, 'defense-tarnish' => 12000, 'speed-tarnish' => 12000,
      'crystal-overdrive' => 15000

      ),
    'abilities_selling_shadow' => array(

      'shadow-shot' => 3000,
      'attack-charm' => 6000, 'defense-charm' => 6000, 'speed-charm' => 6000,
      'shadow-buster' => 9000,
      'attack-curse' => 12000, 'defense-curse' => 12000, 'speed-curse' => 12000,
      'shadow-overdrive' => 15000

      ),
    'abilities_selling_space' => array(

      'space-shot' => 3000,
      'attack-cosmos' => 6000, 'defense-cosmos' => 6000, 'speed-cosmos' => 6000,
      'space-buster' => 9000,
      'attack-chaos' => 12000, 'defense-chaos' => 12000, 'speed-chaos' => 12000,
      'space-overdrive' => 15000

      ),
    'abilities_selling_shield' => array(

      'shield-shot' => 3000,
      'attack-guard' => 6000, 'defense-guard' => 6000, 'speed-guard' => 6000,
      'shield-buster' => 9000,
      'attack-block' => 12000, 'defense-block' => 12000, 'speed-block' => 12000,
      'shield-overdrive' => 15000

      ),
    'abilities_selling_laser' => array(

      'laser-shot' => 3000,
      'attack-glow' => 6000, 'defense-glow' => 6000, 'speed-glow' => 6000,
      'laser-buster' => 9000,
      'attack-fade' => 12000, 'defense-fade' => 12000, 'speed-fade' => 12000,
      'laser-overdrive' => 15000

      )
    ),
  'shop_items' => array(
    'items_buying' => array(

      'item-core-none' => 1000, 'item-core-cutter' => 1000,
      'item-core-impact' => 1000,  'item-core-freeze' => 1000,
      'item-core-explode' => 1000, 'item-core-flame' => 1000,
      'item-core-electric' => 1000, 'item-core-time' => 1000,
      'item-core-earth' => 1000, 'item-core-wind' => 1000,
      'item-core-water' => 1000, 'item-core-swift' => 1000,
      'item-core-nature' => 1000, 'item-core-missile' => 1000,
      'item-core-crystal' => 1000, 'item-core-shadow' => 1000,
      'item-core-space' => 1000, 'item-core-shield' => 1000,
      'item-core-laser' => 1000, 'item-core-copy' => 1000

      )
    )
  );

// KALINKA'S SHOP
$this_shop_index['kalinka'] = array(
  'shop_token' => 'kalinka',
  'shop_name' => 'Kalinka\'s Shop',
  'shop_owner' => 'Kalinka',
  'shop_unlock' => 'Complete the game as all three playable characters.',
  'shop_seeking' => 'stars',
  'shop_colour' => 'electric',
  'shop_field' => 'cossack-citadel',
  'shop_player' => 'dr-cossack',
  'shop_number' => 'SHOP-003',
  'shop_kind_selling' => 'fields',
  'shop_kind_buying' => 'stars',
  'shop_quote_selling' => 'Greetings and welcome to Kalinka\'s Shop! I think you\'ll enjoy the new battle fields I\'m programming.',
  'shop_quote_buying' => 'Do you have any field or fusion stars? I\'m studying the effects of starforce and need some samples.',
  'shop_fields' => array(
    'fields_selling' => array(
      'construction-site' => 48000,
      'magnetic-generator' => 48000,
      'reflection-chamber' => 48000,
      'rocky-plateau' => 48000,
      'spinning-greenhouse' => 48000,
      'serpent-column' => 48000,
      'power-plant' => 48000,
      'septic-system' => 48000
      )
    ),
  'shop_stars' => array(
    'stars_buying' => array(
      'field' => 3000,
      'fusion' => 6000
      )
    )
  );



// -- UPDATE SHOP INVENTORY/PRICES -- //

// Loop through the shop index and prepare to create history arrays where necessary
if (!empty($this_shop_index)){
  foreach ($this_shop_index AS $shop_token => $shop_info){
    // Unlock the shop if the associated doctor has completed chapter one
    $shop_player = $shop_info['shop_player'];
    $shop_selling = $shop_info['shop_kind_selling'];
    $shop_buying = $shop_info['shop_kind_buying'];
    if (mmrpg_prototype_event_complete('completed-chapter_'.$shop_player.'_one')){
      // If the shop has not been created, define its defaults
      if (!isset($this_battle_shops[$shop_token])){
        $shop_array = array();
        $shop_array['shop_level'] = 1;
        $shop_array['shop_experience'] = 0;
        $shop_array['zenny_earned'] = 0;
        $shop_array['zenny_spent'] = 0;
        $shop_array[$shop_selling.'_sold'] = array();
        $shop_array[$shop_buying.'_bought'] = array();
        $this_battle_shops[$shop_token] = $shop_array;
      }
      // Otherwise, refresh the shop's level based on experience
      else {
        $shop_array = $this_battle_shops[$shop_token];
        $temp_experience = !empty($shop_array['shop_experience']) ? $shop_array['shop_experience'] : 1;
        $temp_level = mmrpg_prototype_calculate_level_by_experience($temp_experience);
        $temp_level = floor($temp_level);
        if ($temp_level > 100){ $temp_level = 100; }
        $shop_array['shop_level'] = $temp_level;
        $this_battle_shops[$shop_token] = $shop_array;
      }
      // Either way, update this shop's level from the session
      $this_shop_index[$shop_token]['shop_level'] = $this_battle_shops[$shop_token]['shop_level'];
    }
    // Otherwise set the shop level to zero, it's not unlocked yet
    else {
      // Remove this shop from the index to prevent errors
      unset($this_shop_index[$shop_token]);
      //// Update this shop's level in the index
      //$this_shop_index[$shop_token]['shop_level'] = 0;
    }

  }
}


// -- AUTO SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
if (!empty($this_shop_index['auto'])){

  // If Auto's Shop has reached sufficient levels, expand the inventory
  if ($this_shop_index['auto']['shop_level'] >= 2){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling2']; }
  if ($this_shop_index['auto']['shop_level'] >= 4){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling3']; }
  if ($this_shop_index['auto']['shop_level'] >= 8){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling4']; }
  if ($this_shop_index['auto']['shop_level'] >= 16){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling5']; }
  if ($this_shop_index['auto']['shop_level'] >= 32){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling6']; }
  if ($this_shop_index['auto']['shop_level'] >= 64){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling7']; }
  unset($this_shop_index['auto']['shop_items']['items_selling2']);
  unset($this_shop_index['auto']['shop_items']['items_selling3']);
  unset($this_shop_index['auto']['shop_items']['items_selling4']);
  unset($this_shop_index['auto']['shop_items']['items_selling5']);
  unset($this_shop_index['auto']['shop_items']['items_selling6']);
  unset($this_shop_index['auto']['shop_items']['items_selling7']);

  // Loop through Auto's shop and remove items you do not yet own
  $key_items = array('item-screw-small', 'item-screw-large');
  if (!empty($this_shop_index['auto']['shop_items']['items_buying'])){
    foreach ($this_shop_index['auto']['shop_items']['items_buying'] AS $token => $price){
      if (!isset($this_shop_index['auto']['shop_items']['items_selling'][$token]) && !in_array($token, $key_items)){
        unset($this_shop_index['auto']['shop_items']['items_buying'][$token]);
      }
    }
  }

}


// -- REGGAE SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
if (!empty($this_shop_index['reggae'])){

  // Loop through all the sold cores and add associated abilities
  if (!empty($this_battle_shops['reggae']['cores_bought'])){
    foreach ($this_battle_shops['reggae']['cores_bought'] AS $item_token => $item_quantity){
      $type_token = preg_replace('/^item-core-/i', '', $item_token);
      $type_info = $mmrpg_index['types'][$type_token];
      if (isset($this_shop_index['reggae']['shop_abilities']['abilities_selling_'.$type_token])){
        $ability_list = $this_shop_index['reggae']['shop_abilities']['abilities_selling_'.$type_token];
        unset($this_shop_index['reggae']['shop_abilities']['abilities_selling_'.$type_token]);
        $core_count = !empty($this_battle_shops['reggae']['cores_bought']['item-core-'.$type_token]) ? $this_battle_shops['reggae']['cores_bought']['item-core-'.$type_token] : 0;
        $level_discount = $this_battle_shops['reggae']['shop_level'] > 1 ? $this_battle_shops['reggae']['shop_level'] / 100 : 0;
        foreach ($ability_list AS $ability_token => $ability_price){
          $core_required = ceil($ability_price / 1000);
          if ($core_count < $core_required){ break; }
          $this_shop_index['reggae']['shop_abilities']['abilities_selling'][$ability_token] = $ability_price;
        }
      }
    }
  }

  // If Robots or Abilities have been unlocked, increase the core selling prices
  if (!empty($global_unlocked_robots_cores) || !empty($global_unlocked_abilities_types)){
      if (!empty($this_shop_index['reggae']['shop_items']['items_buying'])){
        $items_list = $this_shop_index['reggae']['shop_items']['items_buying'];
        foreach ($items_list AS $item_token => $item_price){
          $type_token = preg_replace('/^item-core-/', '', $item_token);
          $robot_boost = !empty($global_unlocked_robots_cores[$type_token]) ? $global_unlocked_robots_cores[$type_token] : 0;
          $ability_boost = !empty($global_unlocked_abilities_types[$type_token]) ? $global_unlocked_abilities_types[$type_token] : 0;
          $robot_price_boost = ceil($robot_boost * 100);
          $ability_price_boost = ceil($ability_boost * 10);
          $item_price += $robot_price_boost;
          $item_price += $ability_price_boost;
          $this_shop_index['reggae']['shop_items']['items_buying'][$item_token] = $item_price;
        }
      }
  }

  // If Reggae's Shop has reached sufficient levels, decrease his selling prices
  if ($this_shop_index['reggae']['shop_level'] > 1){
    $level_discount = $this_battle_shops['reggae']['shop_level'] / 100;
    if (!empty($this_shop_index['reggae']['shop_abilities']['abilities_selling'])){
      foreach ($this_shop_index['reggae']['shop_abilities']['abilities_selling'] AS $ability_kind => $ability_price){
        $ability_price -= round(($ability_price / 2) * $level_discount);
        $this_shop_index['reggae']['shop_abilities']['abilities_selling'][$ability_kind] = $ability_price;
      }
    }
  }

}


// -- KALINKA SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
if (!empty($this_shop_index['kalinka'])){

  // If Kalinka's Shop has reached sufficient levels, decrease her selling prices
  if ($this_shop_index['kalinka']['shop_level'] > 1){
    $level_discount = $this_battle_shops['kalinka']['shop_level'] / 100;
    if (!empty($this_shop_index['kalinka']['shop_fields']['fields_selling'])){
      foreach ($this_shop_index['kalinka']['shop_fields']['fields_selling'] AS $field_kind => $field_price){
        $field_price -= round(($field_price / 2) * $level_discount);
        $this_shop_index['kalinka']['shop_fields']['fields_selling'][$field_kind] = $field_price;
      }
    }
  }

}



// Update the session with any changes to the character shops
$_SESSION[$session_token]['values']['battle_shops'] = $this_battle_shops;

?>