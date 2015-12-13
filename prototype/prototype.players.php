<?php

/*
 * DEMO PLAYER SELECT
 */
if (rpg_game::is_demo()){

  // Define the button size based on player count
  $this_button_size = '1x4';

  // Print out the normal mode's player select screen for Dr. Light
  if ($unlock_flag_light){
    echo rpg_prototype::player_select_markup($prototype_data['demo'], 'dr-light', $this_button_size);
  }

}
/*
 * NORMAL PLAYER SELECT
 */
else {
  // Define the button size based on player count
  $this_button_size = '1x4';

  // Print out the normal mode's player select screen for Dr. Light
  if ($unlock_flag_light){
    echo rpg_prototype::player_select_markup($prototype_data['dr-light'], 'dr-light', $this_button_size);
  }

  // Print out the normal mode's player select screen for Dr. Wily
  if ($unlock_flag_wily){
    echo rpg_prototype::player_select_markup($prototype_data['dr-wily'], 'dr-wily', $this_button_size);
  }

  // Print out the normal mode's player select screen for Dr. Cossack
  if ($unlock_flag_cossack){
    echo rpg_prototype::player_select_markup($prototype_data['dr-cossack'], 'dr-cossack', $this_button_size);
  }

}

?>