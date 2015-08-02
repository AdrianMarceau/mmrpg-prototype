<?
/*
 * ROBOT CLASS FUNCTION CONSOLE MARKUP
 * public function console_markup($options, $player_data){}
 */

// Define the variable to hold the console robot data
$this_data = array();

// Define and calculate the simpler markup and positioning variables for this robot
$this_data['robot_frame'] = !empty($this->robot_frame) ? $this->robot_frame : 'base';
$this_data['robot_key'] = !empty($this->robot_key) ? $this->robot_key : 0;
$this_data['robot_title'] = $this->robot_name;
$this_data['robot_token'] = $this->robot_token;
$this_data['robot_image'] = $this->robot_image;
$this_data['robot_float'] = $this->player->player_side;
$this_data['robot_direction'] = $this->player->player_side == 'left' ? 'right' : 'left';
$this_data['robot_status'] = $this->robot_status;
$this_data['robot_position'] = !empty($this->robot_position) ? $this->robot_position : 'bench';
$this_data['image_type'] = !empty($options['this_robot_image']) ? $options['this_robot_image'] : 'sprite';

// Calculate the energy bar amount and display properties
$this_data['energy_fraction'] = $this->robot_energy.' / '.$this->robot_base_energy;
$this_data['energy_percent'] = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
// Calculate the energy bar positioning variables based on float
if ($this_data['robot_float'] == 'left'){
  // Define the x position of the energy bar background
  if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -82; }
  elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -119 + floor(37 * ($this_data['energy_percent'] / 100));  }
  elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -119; }
  else { $this_data['energy_x_position'] = -120; }
  // Define the y position of the energy bar background
  if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; }
  elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -5;}
  else { $this_data['energy_y_position'] = -10; }
}
elseif ($this_data['robot_float'] == 'right'){
  // Define the x position of the energy bar background
  if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -40; }
  elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -3 - floor(37 * ($this_data['energy_percent'] / 100)); }
  elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -3; }
  else { $this_data['energy_x_position'] = -2; }
  // Define the y position of the energy bar background
  if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; }
  elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -5; }
  else { $this_data['energy_y_position'] = -10; }
}

// Calculate the weapons bar amount and display properties for both robots
if (true){
  // Define the fraction and percent text for the weapons
  $this_data['weapons_fraction'] = $this->robot_weapons.' / '.$this->robot_base_weapons;
  $this_data['weapons_percent'] = floor(($this->robot_weapons / $this->robot_base_weapons) * 100);
}

// Calculate the experience bar amount and display properties if a player robot
if ($this_data['robot_float'] == 'left'){
  // Define the fraction and percent text for the experience
  if ($this->robot_level < 100){
    $required_experience = mmrpg_prototype_calculate_experience_required($this->robot_level);
    $this_data['experience_fraction'] = $this->robot_experience.' / '.$required_experience;
    $this_data['experience_percent'] = floor(($this->robot_experience / $required_experience) * 100);
  } else {
    $this_data['experience_fraction'] = '&#8734;';
    $this_data['experience_percent'] = 100;
  }
}

// Define the rest of the display variables
$this_data['container_class'] = 'this_sprite sprite_'.$this_data['robot_float'];
$this_data['container_style'] = '';
//$this_data['robot_class'] = 'sprite sprite_robot_'.$this_data['robot_status'];
$this_data['robot_class'] = 'sprite sprite_robot sprite_robot_'.$this_data['image_type'].' ';
$this_data['robot_style'] = '';
$this_data['robot_size'] = $this->robot_image_size;
$this_data['robot_image'] = 'images/robots/'.$this_data['robot_image'].'/'.$this_data['image_type'].'_'.$this_data['robot_direction'].'_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
$this_data['robot_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].' sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_'.$this_data['robot_frame'].' ';
$this_data['robot_class'] .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
if ($this_data['image_type'] == 'mug'){ $this_data['robot_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_mugshot '; }
$this_data['robot_style'] .= 'background-image: url('.$this_data['robot_image'].'); ';
$this_data['energy_title'] = $this_data['energy_fraction'].' LE ('.$this_data['energy_percent'].'%)';
$this_data['robot_title'] .= ' <br />'.$this_data['energy_title'];
$this_data['weapons_title'] = $this_data['weapons_fraction'].' WE ('.$this_data['weapons_percent'].'%)';
$this_data['robot_title'] .= ' <br />'.$this_data['weapons_title'];
if ($this_data['robot_float'] == 'left'){
  $this_data['experience_title'] = $this_data['experience_fraction'].' EXP ('.$this_data['experience_percent'].'%)';
  $this_data['robot_title'] .= ' <br />'.$this_data['experience_title'];
}
$this_data['energy_class'] = 'energy';
$this_data['energy_style'] = 'background-position: '.$this_data['energy_x_position'].'px '.$this_data['energy_y_position'].'px;';

// Generate the final markup for the console robot
$this_data['robot_markup'] = '';
$this_data['robot_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
$this_data['robot_markup'] .= '<div class="'.$this_data['robot_class'].'" style="'.$this_data['robot_style'].'" title="'.$this_data['robot_title'].'">'.$this_data['robot_title'].'</div>';
if ($this_data['image_type'] != 'mug'){ $this_data['robot_markup'] .= '<div class="'.$this_data['energy_class'].'" style="'.$this_data['energy_style'].'" title="'.$this_data['energy_title'].'">'.$this_data['energy_title'].'</div>'; }
$this_data['robot_markup'] .= '</div>';

?>