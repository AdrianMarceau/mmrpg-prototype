<?
// If the session token has not been set
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

// Collect the field stars from the session variable
if (!isset($_SESSION[$session_token]['values']['battle_stars'])){ $_SESSION[$session_token]['values']['battle_stars'] = array(); }
$this_battle_stars = !empty($_SESSION[$session_token]['values']['battle_stars']) ? $_SESSION[$session_token]['values']['battle_stars'] : array();
$this_battle_stars_count = !empty($this_battle_stars) ? count($this_battle_stars) : 0;
$this_battle_stars_field_count = 0;
$this_battle_stars_fusion_count = 0;

// Loop through the star index and increment the various type counters
$this_star_force = array();
$this_star_force_strict = array();
$this_star_force_total = 0;
$this_star_force_strict_total = 0;
foreach ($this_battle_stars AS $temp_key => $temp_data){
  if ($temp_data['star_kind'] == 'field'){ $this_battle_stars_field_count++; }
  elseif ($temp_data['star_kind'] == 'fusion'){ $this_battle_stars_fusion_count++; }
  if (!empty($temp_data['star_type'])){
    if (!isset($this_star_force[$temp_data['star_type']])){ $this_star_force[$temp_data['star_type']] = 0; }
    if (!isset($this_star_force_strict[$temp_data['star_type']])){ $this_star_force_strict[$temp_data['star_type']] = 0; }
    $this_star_force[$temp_data['star_type']]++;
    $this_star_force_strict[$temp_data['star_type']]++;
    $this_star_force_strict_total++;
    $this_star_force_total++;
  }
  if (!empty($temp_data['star_type2'])){
    if (!isset($this_star_force[$temp_data['star_type2']])){ $this_star_force[$temp_data['star_type2']] = 0; }
    if (!isset($this_star_force_strict[$temp_data['star_type2']])){ $this_star_force_strict[$temp_data['star_type2']] = 0; }
    $this_star_force[$temp_data['star_type2']]++;
    if ($temp_data['star_type'] != $temp_data['star_type2']){ $this_star_force_strict[$temp_data['star_type2']]++; }
    $this_star_force_total++;
  }
}
asort($this_star_force);
$this_star_force = array_reverse($this_star_force);
$_SESSION[$session_token]['values']['star_force'] = $this_star_force;
$_SESSION[$session_token]['values']['star_force_strict'] = $this_star_force_strict;
//die('<pre>$this_star_force_strict = '.print_r($this_star_force_strict, true)."\n\n".'$this_star_force = '.print_r($this_star_force, true)."\n\n".'$this_star_force_total = '.$this_star_force_total."\n\n".'$this_star_force_strict_total = '.$this_star_force_strict_total.'</pre>');

?>