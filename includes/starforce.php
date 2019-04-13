<?
// If the session token has not been set
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

// Collect the field stars from the session variable
$this_battle_stars = !empty($_SESSION[$session_token]['values']['battle_stars']) ? $_SESSION[$session_token]['values']['battle_stars'] : array();
$this_battle_stars_count = !empty($this_battle_stars) ? count($this_battle_stars) : 0;
$this_battle_stars_field_count = 0;
$this_battle_stars_fusion_count = 0;
$this_battle_stars_perfect_fusion_count = 0;

// Loop through the star index and increment the various type counters
$this_star_force = array();
$this_star_force_strict = array();
$this_star_force_total = 0;
$this_star_kind_counts = array();
foreach ($this_battle_stars AS $temp_key => $temp_data){

    $star_kind = $temp_data['star_kind'];
    $star_type = !empty($temp_data['star_type']) ? $temp_data['star_type'] : '';
    $star_type2 = !empty($temp_data['star_type2']) ? $temp_data['star_type2'] : '';

    if ($star_kind == 'fusion' && $star_type != $star_type2){ $star_kind = 'fusion'; }
    elseif ($star_kind == 'fusion' && $star_type == $star_type2){ $star_kind = 'perfect-fusion'; }

    if ($star_kind == 'field'){ $this_battle_stars_field_count++; }
    elseif ($star_kind == 'fusion'){ $this_battle_stars_fusion_count++; }
    elseif ($star_kind == 'perfect-fusion'){ $this_battle_stars_perfect_fusion_count++; }

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

// Unless we're specifically disabled, include Rogue Star power now
if (!isset($include_rogue_star_power)){ $include_rogue_star_power = true; }
if ($include_rogue_star_power){

    // Check to see if a Rogue Star is currently in orbit
    $this_rogue_star = mmrpg_prototype_get_current_rogue_star();
    if (!empty($this_rogue_star)){
        $star_type = $this_rogue_star['star_type'];
        $star_power = $this_rogue_star['star_power'];
        if (!isset($this_star_force[$star_type])){ $this_star_force[$star_type] = 0; }
        $this_star_force[$star_type] += $star_power;
        $this_star_kind_counts['rogue'][$star_type] = 1;
    }

}

// Update the session with current starforce values
$_SESSION[$session_token]['values']['star_force'] = $this_star_force;
$_SESSION[$session_token]['values']['star_force_strict'] = $this_star_force_strict;

//echo('<pre>$this_star_force = '.print_r($this_star_force, true).'</pre>'."\n\n");
//echo('<pre>$this_star_force_strict = '.print_r($this_star_force_strict, true).'</pre>'."\n\n");
//echo('<pre>$this_star_kind_counts = '.print_r($this_star_kind_counts, true).'</pre>'."\n\n");
//$player_starforce = rpg_game::starforce_unlocked();
//echo('<pre>$player_starforce = '.print_r($player_starforce, true).'</pre>'."\n\n");
//die();

?>