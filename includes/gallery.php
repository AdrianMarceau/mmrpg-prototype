<?

// Collect and define the display limit if set
$this_display_limit_default = !empty($this_display_limit_default) ? $this_display_limit_default : 200;
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

		$current_date_group = '';

		foreach ($this_file_index AS $this_path => $this_images){

			// Collect date details for this group
			$this_date_raw = $this_path;
			$this_date_year = substr($this_date_raw, 0, 4);
			$this_date_month = substr($this_date_raw, 4, 2);
			$this_date_month_name = date('F', mktime(0, 0, 0, ((int)($this_date_month)), 1));
			$this_date_day = substr($this_date_raw, 6, 2);
			$this_date_group = $this_date_year.'-'.$this_date_month;

			if ($this_current_page != 'home'
				&& (empty($current_date_group)
					|| $current_date_group != $this_date_group)){

				// Append markup for the new row, closing last if one already exists
				if (!empty($current_date_group)){ $this_gallery_markup[] = '</div>'.PHP_EOL; }
				$this_gallery_markup[] = '<div class="row">'.PHP_EOL;

				// Start the output buffer
				ob_start();
				echo '<a class="dateblock">'.PHP_EOL;
					echo '<span class="month">'.$this_date_month_name.'</span>';
					echo ' <span class="year">'.$this_date_year.'</span>';
					//echo '<span class="year">'.$this_date_year.'</span>';
					//echo '-<span class="month">'.$this_date_month.'</span>';
					//echo '-<span class="day">'.$this_date_day.'</span>'.PHP_EOL;
				echo '</a>'.PHP_EOL;
				$this_gallery_markup[] = preg_replace('/\s+/', ' ', ob_get_clean());

				// Update the current date group
				$current_date_group = $this_date_group;

			}

			// Loop through and print out the images in this folder
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
				echo '<a class="screenshot" style="" href="'.$this_href.'" target="_blank" rel="screenshots">'.PHP_EOL;
					echo '<img class="image" src="'.$this_thumb.'" alt="Mega Man RPG | '.$this_title.'" />'.PHP_EOL;
					//echo '<span class="count">'.$this_count.'</span>'.PHP_EOL;
					echo '<span class="title" title="'.$this_title.'">'.$this_title.'</span>'.PHP_EOL;
					echo '<span class="date">'.$temp_date_string.'</span>'.PHP_EOL;
				echo '</a>'.PHP_EOL;

				// Collect the output into the buffer
				$this_gallery_markup[] = trim(preg_replace('/\s+/', ' ', ob_get_clean()));

				// -- GALLERY XML -- //

				// Start the output buffer
				ob_start();

				// Display the user's save file listing
				echo '<screenshot>'.PHP_EOL;
					echo '<url>'.$this_href.'</url>'.PHP_EOL;
					echo '<descriptions>'.PHP_EOL;
						echo '<description lang="en">'.$this_title.'</description>'.PHP_EOL;
					echo '</descriptions>'.PHP_EOL;
				echo '</screenshot>'.PHP_EOL;

				// Collect the output into the buffer
				$this_gallery_xml[] = trim(ob_get_clean());

				// If we're over the limit we should break
				if ($image_counter >= $this_display_limit_default){ break; }

			}

		}

		if (!empty($current_date_group)){ $this_gallery_markup[] = '</div>'.PHP_EOL; }

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