<?

/*
 * DEMO ROBOT SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){
  // Only show robot select if the player has more than two robots
  if (mmrpg_prototype_robots_unlocked('dr-light') > 3){
    // Print out the demo mode's robot select screen for Dr. Light
    echo '<div class="option_wrapper option_wrapper_hidden" data-condition="this_player_token=dr-light">'."\n";
    if (defined('MMRPG_SCRIPT_REQUEST')){
      echo $prototype_data['demo']['robots_markup']."\n";
    } else {
      echo '<a class="option option_sticky option_1x4 option_this-team-select option_disabled block_9" data-parent="true" data-token="" style=""><div class="platform"><div class="chrome"><div class="inset"><label class="has_image" style="width: 100px; margin-left: 70%; padding-left: 0;"><span class="single"><span class="count">0/8 Select</span><span class="arrow">&nbsp;</span></span></label></div></div></div></a>'."\n";
    }
    echo '</div>'."\n";
    
  }
  
}
/*
 * NORMAL ROBOT SELECT
 */
else {
  // Only print out Light's data if conditions allow or do not exist
  if (empty($this_data_condition) || in_array('this_player_token=dr-light', $this_data_condition)){
    // Print out the normal mode's robot select screen for Dr. Light
    if ($unlock_flag_light){
      echo '<div class="option_wrapper option_wrapper_hidden" data-condition="this_player_token=dr-light">'."\n";
      if (defined('MMRPG_SCRIPT_REQUEST')){
        echo $prototype_data['dr-light']['robots_markup']."\n";
      } else {
        echo '<a class="option option_sticky option_1x4 option_this-team-select option_disabled block_9" data-parent="true" data-token="" style=""><div class="platform"><div class="chrome"><div class="inset"><label class="has_image" style=""><span class="single"><span class="count">0/8 Select</span><span class="arrow">&nbsp;</span></span></label></div></div></div></a>'."\n";
      }
      echo '</div>'."\n";
    }
    
  }
  
  // Only print out Light's data if conditions allow or do not exist
  if (empty($this_data_condition) || in_array('this_player_token=dr-wily', $this_data_condition)){
    // Print out the normal mode's robot select screen for Dr. Wily
    if ($unlock_flag_wily){
      echo '<div class="option_wrapper option_wrapper_hidden" data-condition="this_player_token=dr-wily">'."\n";
      if (defined('MMRPG_SCRIPT_REQUEST')){
        echo $prototype_data['dr-wily']['robots_markup']."\n";
      } else {
        echo '<a class="option option_sticky option_1x4 option_this-team-select option_disabled block_9" data-parent="true" data-token="" style=""><div class="platform"><div class="chrome"><div class="inset"><label class="has_image" style=""><span class="single"><span class="count">0/8 Select</span><span class="arrow">&nbsp;</span></span></label></div></div></div></a>'."\n";
      }
      echo '</div>'."\n";
    }
    
  }
  
  // Only print out Light's data if conditions allow or do not exist
  if (empty($this_data_condition) || in_array('this_player_token=dr-cossack', $this_data_condition)){
    // Print out the normal mode's robot select screen for Dr. Cossack
    if ($unlock_flag_cossack){
      echo '<div class="option_wrapper option_wrapper_hidden" data-condition="this_player_token=dr-cossack">'."\n";
      if (defined('MMRPG_SCRIPT_REQUEST')){
        echo $prototype_data['dr-cossack']['robots_markup']."\n";
      } else {
        echo '<a class="option option_sticky option_1x4 option_this-team-select option_disabled block_9" data-parent="true" data-token="" style=""><div class="platform"><div class="chrome"><div class="inset"><label class="has_image" style=""><span class="single"><span class="count">0/8 Select</span><span class="arrow">&nbsp;</span></span></label></div></div></div></a>'."\n";
      }
      echo '</div>'."\n";
    }
    
  }
  
}

?>