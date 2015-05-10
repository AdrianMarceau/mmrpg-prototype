<?

// Collect and define the display limit if set
$this_display_limit_default = 50;
$this_display_limit = !empty($_GET['limit']) ? trim($_GET['limit']) : $this_display_limit_default;
$this_start_key = !empty($_GET['start']) ? trim($_GET['start']) : 0;

// Define a function for saving the gallery cache
function mmrpg_save_gallery_markup($this_cache_filedir, $this_gallery_markup){
  // Generate the save data by serializing the session variable
  $this_cache_content = implode("\n", $this_gallery_markup);
  // Write the index to a cache file, if caching is enabled
  $this_cache_file = fopen($this_cache_filedir, 'w');
  fwrite($this_cache_file, $this_cache_content);
  fclose($this_cache_file);
  // Return true on success
  return true;
}
// Define a function for loading the gallery cache
function mmrpg_load_gallery_markup($this_cache_filedir){
  // Generate the save data by serializing the session variable
  $this_cache_content = file_get_contents($this_cache_filedir);
  $this_cache_content = explode("\n", $this_cache_content);
  // Return true on success
  return $this_cache_content;
}

// Loop through the save file directory and generate an index
$this_cache_stamp = MMRPG_CONFIG_CACHE_DATE; //.'_'.date('Ymd'); //201301012359
$this_cache_filename = 'cache.gallery.'.$this_cache_stamp.'.php';
$this_cache_filedir = $this_cache_dir.$this_cache_filename;
$this_file_index = array();
$this_file_count = count($this_file_index);
$this_gallery_markup = array();
$this_gallery_xml = array();
$this_screenshots_dir = MMRPG_CONFIG_ROOTDIR.'images/gallery/screenshots/';
if (MMRPG_CONFIG_CACHE_INDEXES && file_exists($this_cache_filedir)){

  $this_gallery_markup = mmrpg_load_gallery_markup(str_replace('.php', '.html.txt', $this_cache_filedir));
  $this_gallery_xml = mmrpg_load_gallery_markup(str_replace('.php', '.xml.txt', $this_cache_filedir));

} else {

  $this_dir_skip = array('.', '..', 'thumbs');
  $this_dir_handler = opendir($this_screenshots_dir);
  while (false !== ($dirname = readdir($this_dir_handler))){
    if (in_array($dirname, $this_dir_skip)){ continue; }
    $temp_date_token = $dirname;
    $this_file_index[$temp_date_token] = array();
    $temp_gallery_dir = $this_screenshots_dir.$dirname.'/';
    $temp_dir_handler = opendir($temp_gallery_dir);
    while (false !== ($filename = readdir($temp_dir_handler))){
      if (in_array($filename, $this_dir_skip)){ continue; }
      // Update the temp save dir with the filename
      $temp_image_path = $this_screenshots_dir.$filename;
      // Import the game content into the session
      $this_file_index[$temp_date_token][] = $filename;
    }
    $this_file_index[$temp_date_token] = array_reverse($this_file_index[$temp_date_token]);
    // Close the directory to prevent memory overload
    closedir($temp_dir_handler);
    // Shuffle the results to keep the gallery fresh(?)
    //shuffle($this_file_index[$temp_date_token]);
  }
  // Sort the array by date-keys
  ksort($this_file_index);
  $this_file_index = array_reverse($this_file_index, true);

  //die('<pre>'.print_r($this_file_index, true).'</pre>');

  // Count the total number of files/players
  $this_file_count = count($this_file_index);

  // If there are image files to display
  if (!empty($this_file_index)){
    $image_counter = 0;
    $column_counter = 1;
    $num_columns = 5;

    foreach ($this_file_index AS $this_path => $this_images){

      /*
      // Start the output buffer
      ob_start();

      $this_date_raw = $this_path;
      $this_date_year = substr($this_date_raw, 0, 4);
      $this_date_month = substr($this_date_raw, 2, 2);
      $this_date_day = substr($this_date_raw, 4, 2);
      echo '<a class="dateblock">'."\n";
      echo '<span class="year">'.$this_date_year.'</span>'."\n";
      echo '<span class="month">'.$this_date_month.'</span>'."\n";
      echo '<span class="day">'.$this_date_day.'</span>'."\n";
      echo '</a>'."\n";

      // Collect the output into the buffer
      $this_gallery_markup[] = preg_replace('/\s+/', ' ', ob_get_clean());
      */

      // Generate the date string for later use
      /*
      $this_date_raw = $this_path;
      $this_date_year = substr($this_date_raw, 0, 4);
      $this_date_month = substr($this_date_raw, 2, 2);
      $this_date_day = substr($this_date_raw, 4, 2);
      $this_date_string = $this_date_year.'/'.$this_date_month.'/'.$this_date_day;
      */

      foreach ($this_images AS $this_key => $this_name){

        // Increment the image counter
        $image_counter += 1;

        // If this image is over the column count, increment
        if ($num_columns > $num_columns && $num_columns % $num_columns == 0){ $column_counter++; }

        // Generate the markup for this gallery image
        $this_href = MMRPG_CONFIG_ROOTURL.'images/gallery/screenshots/'.$this_path.'/'.$this_name;
        $this_thumb = MMRPG_CONFIG_ROOTURL.'images/gallery/screenshots/thumbs/'.$this_path.'_'.preg_replace('/\.(png|gif|jpg)$/i', '_thumb.jpg', $this_name);
        $this_count = '#'.str_pad(($image_counter), 2, '0', STR_PAD_LEFT);
        $this_title = trim($this_name);
        $this_title = preg_replace('/^([0-9]+)-/i', '', $this_name);
        $this_title = str_replace('dr', 'dr.', $this_title);
        $this_title = trim($this_title, '-');
        $this_title = ucwords(str_replace('-', ' ', $this_title));
        $this_title = preg_replace('/.(png|jpg|gif)/i', '', $this_title);
        $temp_date_string = preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#', '$1/$2/$3', $this_path);

        // -- GALLERY MARKUP -- //

        // Start the output buffer
        ob_start();

        // Display the user's save file listing
        echo '<a class="screenshot" style="" href="'.$this_href.'" target="_blank" rel="screenshots">'."\n";
        echo '<img class="image" src="'.$this_thumb.'" alt="Mega Man RPG | '.$this_title.'" />'."\n";
        //echo '<span class="count">'.$this_count.'</span>'."\n";
        echo '<span class="title" title="'.$this_title.'">'.$this_title.'</span>'."\n";
        echo '<span class="date">'.$temp_date_string.'</span>'."\n";
        echo '</a>'."\n";

        // Collect the output into the buffer
        $this_gallery_markup[] = trim(preg_replace('/\s+/', ' ', ob_get_clean()));

        // -- GALLERY XML -- //

        // Start the output buffer
        ob_start();

        // Display the user's save file listing
        echo '<screenshot>'."\n";
          echo '<url>'.$this_href.'</url>'."\n";
          echo '<descriptions>'."\n";
            echo '<description lang="en">'.$this_title.'</description>'."\n";
          echo '</descriptions>'."\n";
        echo '</screenshot>'."\n";

        // Collect the output into the buffer
        $this_gallery_xml[] = trim(ob_get_clean());

      }

    }



  }

  // Update the session cache
  // $_SESSION['LEADERBOARD'][$this_cache_stamp] = $this_file_index;
  // Update the gallery cache files
  mmrpg_save_gallery_markup(str_replace('.php', '.html.txt', $this_cache_filedir), $this_gallery_markup);
  mmrpg_save_gallery_markup(str_replace('.php', '.xml.txt', $this_cache_filedir), $this_gallery_xml);

}

// Update the file count number
$this_file_count = !empty($image_counter) ? $image_counter : (!empty($this_gallery_markup) ? count($this_gallery_markup) : 0); //!empty($this_gallery_markup) ? count($this_gallery_markup) : 0;

?>