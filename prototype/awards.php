<?

// Collect the prototype awards from the session if they are available
$this_prototype_awards = !empty($_SESSION[$session_token]['values']['prototype_awards']) ? $_SESSION[$session_token]['values']['prototype_awards'] : array();

// Ensure we're not in the demo before we worry about ranking stuff
if (empty($_SESSION[$session_token]['DEMO'])){

  
  /*
   * LEADERBOARD ACHIEVEMENTS
   */
  
  // -- FIRST PLACE!!! -- //
  // If this user is in first place (YAY!) make sure there's a flag to remember it
  if (!isset($this_prototype_awards['ranking_first_place']) && $this_boardinfo['board_rank'] == 1){
    $this_prototype_awards['ranking_first_place'] = time();
    if (!isset($this_prototype_awards['ranking_second_place'])){
      $this_prototype_awards['ranking_second_place'] = time();
      if (!isset($this_prototype_awards['ranking_third_place'])){
        $this_prototype_awards['ranking_third_place'] = time();
      }
    }
  }
  
  // -- SECOND PLACE!!! -- //
  // If this user is in second place make sure there's a flag to remember it
  if (!isset($this_prototype_awards['ranking_second_place']) && $this_boardinfo['board_rank'] == 2){
    $this_prototype_awards['ranking_second_place'] = time();
    if (!isset($this_prototype_awards['ranking_third_place'])){
      $this_prototype_awards['ranking_third_place'] = time();
    }
  }
  
  // -- THIRD PLACE!!! -- //
  // If this user is in third place make sure there's a flag to remember it
  if (!isset($this_prototype_awards['ranking_third_place']) && $this_boardinfo['board_rank'] == 3){
    $this_prototype_awards['ranking_third_place'] = time();
  }
  
  
  /*
   * PROTOTYPE ACHIEVEMENTS
   */
  
  // -- LIGHT COMPLETE!!! -- //
  // If this user has completed Dr. Light's campaign, make sure there's a flag to remember it
  if (!isset($this_prototype_awards['prototype_complete_light']) && mmrpg_prototype_complete('dr-light')){
    $this_prototype_awards['prototype_complete_light'] = time();
  }
  
  // -- WILY COMPLETE!!! -- //
  // If this user has completed Dr. Wily's campaign, make sure there's a flag to remember it
  if (!isset($this_prototype_awards['prototype_complete_wily']) && mmrpg_prototype_complete('dr-wily')){
    $this_prototype_awards['prototype_complete_wily'] = time();
  }
  
  // -- COSSACK COMPLETE!!! -- //
  // If this user has completed Dr. Cossack's campaign, make sure there's a flag to remember it
  if (!isset($this_prototype_awards['prototype_complete_cossack']) && mmrpg_prototype_complete('dr-cossack')){
    $this_prototype_awards['prototype_complete_cossack'] = time();
  }
  
  // -- PROTOTYPE COMPLETE!!! -- //
  // If this user has completed all three campaigns, make sure there's a flag to remember it
  if (!isset($this_prototype_awards['prototype_complete_all'])
    && !empty($this_prototype_awards['prototype_complete_light'])
    && !empty($this_prototype_awards['prototype_complete_wily'])
    && !empty($this_prototype_awards['prototype_complete_cossack'])){
    $this_prototype_awards['prototype_complete_all'] = time();
  }
  
}

// Update the prototype awards session variable with recent changes
$_SESSION[$session_token]['values']['prototype_awards'] = $this_prototype_awards;

?>