<?
// Define the console markup string
$this_markup = '';

// Ensure this side is allowed to be shown before generating any markup
if ($options['console_show_this'] != false){

    // Define the necessary text markup for the current player if allowed and exists
  if (!empty($eventinfo['this_player'])){
    // Collect the console data for this player
    $this_player_data = $eventinfo['this_player']->console_markup($options);
  } else {
    // Define empty console data for this player
    $this_player_data = array();
    $options['console_show_this_player'] = false;
  }
  // Define the necessary text markup for the current robot if allowed and exists
  if (!empty($eventinfo['this_robot'])){
    // Collect the console data for this robot
    $this_robot_data = $eventinfo['this_robot']->console_markup($options, $this_player_data);
  } else {
    // Define empty console data for this robot
    $this_robot_data = array();
    $options['console_show_this_robot'] = false;
  }
  // Define the necessary text markup for the current ability if allowed and exists
  if (!empty($options['this_ability'])){
    // Collect the console data for this ability
    $this_ability_data = $options['this_ability']->console_markup($options, $this_player_data, $this_robot_data);
  } else {
    // Define empty console data for this ability
    $this_ability_data = array();
    $options['console_show_this_ability'] = false;
  }
  // Define the necessary text markup for the current star if allowed and exists
  if (!empty($options['this_star'])){
    // Collect the console data for this star
    $this_star_data = $this->star_console_markup($options['this_star'], $this_player_data, $this_robot_data);
  } else {
    // Define empty console data for this star
    $this_star_data = array();
    $options['console_show_this_star'] = false;
  }

  // If no objects would found to display, turn the left side off
  if (empty($options['console_show_this_player'])
    && empty($options['console_show_this_robot'])
    && empty($options['console_show_this_ability'])
    && empty($options['console_show_this_star'])){
    // Automatically set the console option to false
    $options['console_show_this'] = false;
  }

}
// Otherwise, if this side is not allowed to be shown at all
else {

  // Default all of this side's objects to empty arrays
  $this_player_data = array();
  $this_robot_data = array();
  $this_ability_data = array();
  $this_star_data = array();

}


// Ensure the target side is allowed to be shown before generating any markup
if ($options['console_show_target'] != false){

  // Define the necessary text markup for the target player if allowed and exists
  if (!empty($eventinfo['target_player'])){
    // Collect the console data for this player
    $target_player_data = $eventinfo['target_player']->console_markup($options);
  } else {
    // Define empty console data for this player
    $target_player_data = array();
    $options['console_show_target_player'] = false;
  }
  // Define the necessary text markup for the target robot if allowed and exists
  if (!empty($eventinfo['target_robot'])){
    // Collect the console data for this robot
    $target_robot_data = $eventinfo['target_robot']->console_markup($options, $target_player_data);
  } else {
    // Define empty console data for this robot
    $target_robot_data = array();
    $options['console_show_target_robot'] = false;
  }
  // Define the necessary text markup for the target ability if allowed and exists
  if (!empty($options['target_ability'])){
    // Collect the console data for this ability
    $target_ability_data = $options['target_ability']->console_markup($options, $target_player_data, $target_robot_data);
  } else {
    // Define empty console data for this ability
    $target_ability_data = array();
    $options['console_show_target_ability'] = false;
  }

  // If no objects would found to display, turn the right side off
  if (empty($options['console_show_target_player'])
    && empty($options['console_show_target_robot'])
    && empty($options['console_show_target_ability'])){
    // Automatically set the console option to false
    $options['console_show_target'] = false;
  }

}
// Otherwise, if the target side is not allowed to be shown at all
else {

  // Default all of the target side's objects to empty arrays
  $target_player_data = array();
  $target_robot_data = array();
  $target_ability_data = array();

}

// Assign player-side based floats for the header and body if not set
if (empty($options['console_header_float']) && !empty($this_robot_data)){
  $options['console_header_float'] = $this_robot_data['robot_float'];
}
if (empty($options['console_body_float']) && !empty($this_robot_data)){
  $options['console_body_float'] = $this_robot_data['robot_float'];
}

// Append the generated console markup if not empty
if (!empty($eventinfo['event_header']) && !empty($eventinfo['event_body'])){

  // Define the container class based on height
  $event_class = 'event ';
  $event_style = '';
  if ($options['console_container_height'] == 1){ $event_class .= 'event_single '; }
  if ($options['console_container_height'] == 2){ $event_class .= 'event_double '; }
  if ($options['console_container_height'] == 3){ $event_class .= 'event_triple '; }
  if (!empty($options['console_container_classes'])){ $event_class .= $options['console_container_classes']; }
  if (!empty($options['console_container_styles'])){ $event_style .= $options['console_container_styles']; }

  // Generate the opening event tag
  $this_markup .= '<div class="'.$event_class.'" style="'.$event_style.'">';

  // Generate this side's markup if allowed
  if ($options['console_show_this'] != false){
    // Append this player's markup if allowed
    if ($options['console_show_this_player'] != false){ $this_markup .= $this_player_data['player_markup']; }
    // Otherwise, append this robot's markup if allowed
    elseif ($options['console_show_this_robot'] != false){ $this_markup .= $this_robot_data['robot_markup']; }
    // Otherwise, append this ability's markup if allowed
    elseif ($options['console_show_this_ability'] != false){ $this_markup .= $this_ability_data['ability_markup']; }
    // Otherwise, append this star's markup if allowed
    elseif ($options['console_show_this_star'] != false){ $this_markup .= $this_star_data['star_markup']; }
  }

  // Generate the target side's markup if allowed
  if ($options['console_show_target'] != false){
    // Append the target player's markup if allowed
    if ($options['console_show_target_player'] != false){ $this_markup .= $target_player_data['player_markup']; }
    // Otherwise, append the target robot's markup if allowed
    elseif ($options['console_show_target_robot'] != false){ $this_markup .= $target_robot_data['robot_markup']; }
    // Otherwise, append the target ability's markup if allowed
    elseif ($options['console_show_target_ability'] != false){ $this_markup .= $target_ability_data['ability_markup']; }
  }

  // Prepend the turn counter to the header if necessary
  if (!empty($this->counters['battle_turn']) && $this->battle_status != 'complete'){ $eventinfo['event_header'] = 'Turn #'.$this->counters['battle_turn'].' : '.$eventinfo['event_header']; }

  // Display the event header and event body
  $this_markup .= '<div class="header header_'.$options['console_header_float'].'">'.$eventinfo['event_header'].'</div>';
  $this_markup .= '<div class="body body_'.$options['console_body_float'].'">'.$eventinfo['event_body'].'</div>';

  // Displat the closing event tag
  $this_markup .= '</div>';

}
?>