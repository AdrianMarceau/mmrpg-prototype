<?
// If the session token has not been set
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

// Collect the field stars from the session variable
$this_battle_stars = !empty($_SESSION[$session_token]['values']['battle_stars']) ? $_SESSION[$session_token]['values']['battle_stars'] : array();
$this_battle_stars_count = !empty($this_battle_stars) ? count($this_battle_stars) : 0;
$this_battle_stars_field_count = 0;
$this_battle_stars_fusion_count = 0;

// Loop through the star index and increment the various type counters
$this_star_force = array();
$this_star_force_strict = array();
$this_star_force_total = 0;
$this_star_kind_counts = array();
foreach ($this_battle_stars AS $temp_key => $temp_data){
    $star_kind = $temp_data['star_kind'];
    $star_type = !empty($temp_data['star_type']) ? $temp_data['star_type'] : '';
    $star_type2 = !empty($temp_data['star_type2']) ? $temp_data['star_type2'] : '';
    if ($star_kind == 'field'){ $this_battle_stars_field_count++; }
    elseif ($star_kind == 'fusion'){ $this_battle_stars_fusion_count++; }
    if (!empty($star_type)){
        if (!isset($this_star_force[$star_type])){ $this_star_force[$star_type] = 0; }
        if (!isset($this_star_force_strict[$star_type])){ $this_star_force_strict[$star_type] = 0; }
        if (!isset($this_star_kind_counts[$star_kind][$star_type])){ $this_star_kind_counts[$star_kind][$star_type] = 0; }
        $this_star_force[$star_type]++;
        $this_star_force_strict[$star_type]++;
        $this_star_kind_counts[$star_kind][$star_type]++;
        $this_star_force_total++;
    }
    if (!empty($star_type2)){
        if (!isset($this_star_force[$star_type2])){ $this_star_force[$star_type2] = 0; }
        if (!isset($this_star_force_strict[$star_type2])){ $this_star_force_strict[$star_type2] = 0; }
        if (!isset($this_star_kind_counts[$star_kind][$star_type2])){ $this_star_kind_counts[$star_kind][$star_type2] = 0; }
        $this_star_force[$star_type2]++;
        if ($star_type != $star_type2){
            $this_star_force_strict[$star_type2]++;
            $this_star_kind_counts[$star_kind][$star_type2]++;
        }
        $this_star_force_total++;
    }
}
asort($this_star_force);
$this_star_force = array_reverse($this_star_force);
$_SESSION[$session_token]['values']['star_force'] = $this_star_force;
$_SESSION[$session_token]['values']['star_force_strict'] = $this_star_force_strict;
//echo('<pre>$this_star_force = '.print_r($this_star_force, true).'</pre>'."\n\n");
//echo('<pre>$this_star_force_strict = '.print_r($this_star_force_strict, true).'</pre>'."\n\n");
//echo('<pre>$this_star_kind_counts = '.print_r($this_star_kind_counts, true).'</pre>'."\n\n");
//die();

?>