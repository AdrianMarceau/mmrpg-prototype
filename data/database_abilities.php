<?
// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// ABILITY DATABASE

// Define the index of counters for robot types
$mmrpg_database_abilities_types = array();
foreach ($mmrpg_database_types AS $token => $info){
  $mmrpg_database_abilities_types[$token] = 0;
}

// Define the index of hidden abilities to not appear in the database
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$hidden_database_abilities = array();
$hidden_database_abilities = array_merge($hidden_database_abilities, array('ability', 'attachment-defeat', 'action-noweapons'));
$hidden_database_abilities = array_merge($hidden_database_abilities, array('sticky-bond', 'sticky-shot', 'roll-swing'));
//$hidden_database_abilities = array_merge($hidden_database_abilities, array('air-man', 'bubble-man', 'crash-man', 'flash-man', 'heat-man', 'metal-man', 'quick-man', 'wood-man'));
//$hidden_database_abilities = array_merge($hidden_database_abilities, array('needle-man', 'magnet-man', 'gemini-man', 'hard-man', 'top-man', 'snake-man', 'spark-man', 'shadow-man'));
$hidden_database_abilities_count = !empty($hidden_database_abilities) ? count($hidden_database_abilities) : 0;


// Define the hidden ability query condition
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$temp_condition = '';
$temp_condition .= "AND ability_class <> 'item' ";
if (!defined('DATA_DATABASE_SHOW_MECHAS')){
  $temp_condition .= "AND ability_class <> 'mecha' ";
}
if (!empty($hidden_database_abilities)){
  $temp_tokens = array();
  foreach ($hidden_database_abilities AS $token){ $temp_tokens[] = "'".$token."'"; }
  $temp_condition .= 'AND ability_token NOT IN ('.implode(',', $temp_tokens).') ';
}

// Collect the database abilities
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$mmrpg_database_abilities = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_published = 1 {$temp_condition}", 'ability_token');

// Remove unallowed abilities from the database, and increment counters
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
foreach ($mmrpg_database_abilities AS $temp_token => $temp_info){
  if (true){

    // Send this data through the ability index parser
    $temp_info = rpg_ability::parse_index_info($temp_info);

    // Ensure this ability's image exists, else default to the placeholder
    $temp_image_token = isset($temp_info['ability_image']) ? $temp_info['ability_image'] : $temp_token;
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/abilities/'.$temp_image_token.'/')){ $temp_info['ability_image'] = $temp_image_token; }
    elseif (file_exists(MMRPG_CONFIG_ROOTDIR.'images/abilities/'.$temp_token.'/')){ $temp_info['ability_image'] = $temp_token; }
    else { $temp_info['ability_image'] = 'ability'; }
    // DEBUG DEBUG DEBUG | HIDE INCOMPLETE
    if (false && $temp_info['ability_image'] == 'ability'){
      unset($mmrpg_database_abilities[$temp_token]);
      continue;
    }
    $temp_info['ability_speed'] = isset($temp_info['ability_speed']) ? $temp_info['ability_speed'] + 3 : 3;
    $temp_info['ability_energy'] = isset($temp_info['ability_energy']) ? $temp_info['ability_energy'] : 10;
    // Increment the corresponding type counter for this ability if not empty
    if (!empty($temp_info['ability_type'])){ $mmrpg_database_abilities_types[$temp_info['ability_type']]++; }
    else { $mmrpg_database_abilities_types['none']++; }
    if (!empty($temp_info['ability_type2'])){ $mmrpg_database_abilities_types[$temp_info['ability_type2']]++; }
    //else { $mmrpg_database_abilities_types['none']++; }

    // Update the main database array with the changes
    $mmrpg_database_abilities[$temp_token] = $temp_info;

  }
}

// Sort the ability index based on ability number
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$temp_pattern_first = array();
$temp_pattern_first[] = '/^(buster-shot)$/i';
$temp_pattern_first[] = '/^mega-(buster|ball|slide)$/i';
$temp_pattern_first[] = '/^bass-(buster|crush|baroque)$/i';
$temp_pattern_first[] = '/^proto-(buster|shield|strike)$/i';
$temp_pattern_first[] = '/^roll-(buster)$/i';
$temp_pattern_first[] = '/^disco-(buster)$/i';
$temp_pattern_first[] = '/^rhythm-(buster)$/i';
$temp_pattern_first[] = '/^light-buster$/i';
$temp_pattern_first[] = '/^wily-buster$/i';
$temp_pattern_first[] = '/^cossack-buster$/i';
//$temp_pattern_first = array_reverse($temp_pattern_first);
$temp_pattern_last = array();
$temp_pattern_last[] = '/^(energy|repair)-(boost|break|swap)$/i';
$temp_pattern_last[] = '/^(energy|repair)-(support)$/i';
$temp_pattern_last[] = '/^(energy|repair)-(assault|shuffle)$/i';
$temp_pattern_last[] = '/^(energy|repair)-(mode)$/i';
$temp_pattern_last[] = '/^(attack|weapon)-(boost|break|swap)$/i';
$temp_pattern_last[] = '/^(attack|weapon)-(support)$/i';
$temp_pattern_last[] = '/^(attack|weapon)-(assault|shuffle)$/i';
$temp_pattern_last[] = '/^(attack|weapon)-(mode)$/i';
$temp_pattern_last[] = '/^(defense|shield)-(boost|break|swap)$/i';
$temp_pattern_last[] = '/^(defense|shield)-(support)$/i';
$temp_pattern_last[] = '/^(defense|shield)-(assault|shuffle)$/i';
$temp_pattern_last[] = '/^(defense|shield)-(mode)$/i';
$temp_pattern_last[] = '/^(speed|mobility)-(boost|break|swap)$/i';
$temp_pattern_last[] = '/^(speed|mobility)-(support)$/i';
$temp_pattern_last[] = '/^(speed|mobility)-(assault|shuffle)$/i';
$temp_pattern_last[] = '/^(speed|mobility)-(mode)$/i';
$temp_pattern_last[] = '/^(energy|attack|defense|speed|repair|weapon|shield|mobility)-(booster|breaker)$/i';
$temp_pattern_last[] = '/^(energy|attack|defense|speed|repair|weapon|shield|mobility)-(burn|blaze)$/i';
$temp_pattern_last[] = '/^(damage|recovery|experience)-(boost|break|mode)$/i';
$temp_pattern_last[] = '/^(damage|recovery|experience)-(support|assault)$/i';
$temp_pattern_last[] = '/^(damage|recovery|experience)-(booster|breaker)$/i';
$temp_pattern_last[] = '/^(mecha|field)-(support|assault)$/i';
$temp_pattern_last = array_reverse($temp_pattern_last);
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
function mmrpg_index_sort_abilities($ability_one, $ability_two){
  // Pull in global variables
  global $temp_pattern_first, $temp_pattern_last;
  // Loop through all the temp patterns and compare them one at a time
  foreach ($temp_pattern_first AS $key => $pattern){
    // Check if either of these two abilities matches the current pattern
    if (preg_match($pattern, $ability_one['ability_token']) && !preg_match($pattern, $ability_two['ability_token'])){ return -1; }
    elseif (!preg_match($pattern, $ability_one['ability_token']) && preg_match($pattern, $ability_two['ability_token'])){ return 1; }
  }
  foreach ($temp_pattern_last AS $key => $pattern){
    // Check if either of these two abilities matches the current pattern
    if (preg_match($pattern, $ability_one['ability_token']) && !preg_match($pattern, $ability_two['ability_token'])){ return 1; }
    elseif (!preg_match($pattern, $ability_one['ability_token']) && preg_match($pattern, $ability_two['ability_token'])){ return -1; }
  }
  // If only one of the two abilities has a type, the one with goes first
  if (!empty($ability_one['ability_type']) && empty($ability_two['ability_type'])){ return 1; }
  elseif (empty($ability_one['ability_type']) && !empty($ability_two['ability_type'])){ return -1; }
  // If neither ability has a type, order albabetically
  elseif (empty($ability_one['ability_type']) && empty($ability_two['ability_type'])){
    if ($ability_one['ability_energy'] > $ability_two['ability_energy']){ return 1; }
    elseif ($ability_one['ability_energy'] < $ability_two['ability_energy']){ return -1; }
    elseif ($ability_one['ability_token'] > $ability_two['ability_token']){ return 1; }
    elseif ($ability_one['ability_token'] < $ability_two['ability_token']){ return -1; }
    else { return 0; }
  }
  // If the abilities have types, order them by their types alphabetically
  elseif ($ability_one['ability_type'] > $ability_two['ability_type']){ return 1; }
  elseif ($ability_one['ability_type'] < $ability_two['ability_type']){ return -1; }
  // If the abilities have the same first type, compare further
  elseif ($ability_one['ability_type'] == $ability_two['ability_type']){
    // If only one of the two abilities has a second type, that one goes last
    if (!empty($ability_one['ability_type2']) && empty($ability_two['ability_type2'])){ return 1; }
    elseif (empty($ability_one['ability_type2']) && !empty($ability_two['ability_type2'])){ return -1; }
    // Else if neither ability has a type, order alphabetically
    elseif (empty($ability_one['ability_type2']) && empty($ability_two['ability_type2'])){
      if ($ability_one['ability_energy'] > $ability_two['ability_energy']){ return 1; }
      elseif ($ability_one['ability_energy'] < $ability_two['ability_energy']){ return -1; }
      elseif ($ability_one['ability_token'] > $ability_two['ability_token']){ return 1; }
      elseif ($ability_one['ability_token'] < $ability_two['ability_token']){ return -1; }
      else { return 0; }
    }
    // Else if the abilities have second types, order them by their second types alphabetically
    elseif ($ability_one['ability_type2'] > $ability_two['ability_type2']){ return 1; }
    elseif ($ability_one['ability_type2'] < $ability_two['ability_type2']){ return -1; }
    // Else if the abilities have the same second type, order alphabetically
    elseif ($ability_one['ability_type2'] == $ability_two['ability_type2']){
      if ($ability_one['ability_energy'] > $ability_two['ability_energy']){ return 1; }
      elseif ($ability_one['ability_energy'] < $ability_two['ability_energy']){ return -1; }
      elseif ($ability_one['ability_token'] > $ability_two['ability_token']){ return 1; }
      elseif ($ability_one['ability_token'] < $ability_two['ability_token']){ return -1; }
      else { return 0; }
    }
  }
  else {
    // Return 0 by default
    return 0;
  }
}
uasort($mmrpg_database_abilities, 'mmrpg_index_sort_abilities');

// Determine the token for the very first ability in the database
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$temp_ability_tokens = array_values($mmrpg_database_abilities);
$first_ability_token = array_shift($temp_ability_tokens);
$first_ability_token = $first_ability_token['ability_token'];
unset($temp_ability_tokens);

// Count the number of abilities collected and filtered
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$mmrpg_database_abilities_count = count($mmrpg_database_abilities);

// Loop through the database and generate the links for these abilities
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$key_counter = 0;
$mmrpg_database_abilities_links = '';
$mmrpg_database_abilities_links_counter = 0;
$mmrpg_database_abilities_links_counter_incomplete = 0;
foreach ($mmrpg_database_abilities AS $ability_key => $ability_info){
  // If a type filter has been applied to the ability page
  $temp_ability_types = array();
  if (!empty($ability_info['ability_type'])){ $temp_ability_types[] = $ability_info['ability_type']; }
  if (!empty($ability_info['ability_type2'])){ $temp_ability_types[] = $ability_info['ability_type2']; }
  if (empty($temp_ability_types)){ $temp_ability_types[] = 'none'; }
  if (isset($this_current_filter) && !in_array($this_current_filter, $temp_ability_types)){ $key_counter++; continue; }
  // Collect the ability sprite dimensions
  $ability_image_size = !empty($ability_info['ability_image_size']) ? $ability_info['ability_image_size'] : 40;
  $ability_image_size_text = $ability_image_size.'x'.$ability_image_size;
  $ability_image_token = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];
  $ability_image_incomplete = $ability_image_token == 'ability' ? true : false;
  $ability_is_active = !empty($this_current_token) && $this_current_token == $ability_info['ability_token'] ? true : false;
  $ability_title_text = $ability_info['ability_name'].(!empty($ability_info['ability_type']) ? ' | '.ucfirst($ability_info['ability_type']).' Type' : ' | Neutral Type');
  if (!empty($ability_info['ability_type2'])){ $ability_title_text = str_replace('Type', '/ '.ucfirst($ability_info['ability_type2']).' Type', $ability_title_text); }
  $ability_image_path = 'images/abilities/'.$ability_image_token.'/icon_right_'.$ability_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $ability_title_text ?>" data-token="<?= $ability_info['ability_token'] ?>" class="float float_left float_link ability_type ability_type_<?= (!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : '') ?><?= $ability_image_incomplete  ? ' incomplete' : '' ?>">
    <a class="sprite sprite_ability_link sprite_ability sprite_ability_sprite sprite_40x40 sprite_40x40_mugshot sprite_size_<?= $ability_image_size_text ?>  ability_status_active ability_position_active <?= $ability_key == $first_ability_token ? 'sprite_ability_current ' : '' ?>" href="<?='database/abilities/'.$ability_info['ability_token']?>/" rel="<?= $ability_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($ability_image_token != 'ability'): ?>
        <img src="<?= $ability_image_path ?>" width="<?= $ability_image_size ?>" height="<?= $ability_image_size ?>" alt="<?= $ability_title_text ?>" />
      <? else: ?>
        <span><?= preg_replace('/\s+([a-z0-9]+)$/i', '<br />$1', $ability_info['ability_name']) ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  $mmrpg_database_abilities_links .= preg_replace('/\s+/', ' ', trim(ob_get_clean()))."\n";
  $mmrpg_database_abilities_links_counter++;
  if ($ability_image_incomplete){ $mmrpg_database_abilities_links_counter_incomplete++; }
  $key_counter++;
}
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

?>