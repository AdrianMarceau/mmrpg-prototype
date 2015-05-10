<?



// Define a function for collecting a spreadsheet from google
function google_csv_spreadsheet($google_url, $sheet_name = ''){

  // Define the file token for this spreadsheet
  $cache_token = md5($google_url);
  $cache_filename = 'cache.'.(!empty($sheet_name) ? $sheet_name : $cache_token).'.'.MMRPG_CONFIG_CACHE_DATE.'.csv';
  $cache_filepath = MMRPG_CONFIG_ROOTDIR.'data/cache/'.$cache_filename;
  $cache_modified = file_exists($cache_filepath) ? filemtime($cache_filepath) : 0;
  $cache_timeout = !empty($cache_modified) ? time() - $cache_modified : 0;
  $google_sheet = array();
  $google_sheet2 = array();

  // If the file does not exist or is too old
  if (!file_exists($cache_filepath) || $cache_timeout > (60 * 60)){

    //$google_url = 'https://docs.google.com/spreadsheet/pub?key='.$key.'&single=true&gid=0&output=csv';
    $ch = curl_init();

    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $google_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // grab URL and pass it to the browser
    $google_sheet = curl_exec($ch);
    curl_close($ch);

    // Write the index to a cache file, if caching is enabled
    $cache_fileobject = @fopen($cache_filepath, 'w');
    if (!empty($cache_fileobject)){
      @fwrite($cache_fileobject, $google_sheet);
      @fclose($cache_fileobject);
    }

  }
  // Otherwise, collect the file directory
  else {

    // open the file and collect its contents
    $google_sheet = file_get_contents($cache_filepath);

  }

  //die('$google_sheet = '.$google_url.'<br /><pre>'.print_r($google_sheet, true).'</pre>');

  $google_sheet = !empty($google_sheet) ? explode("\n", $google_sheet) : array();
  foreach ($google_sheet AS $key => $string){ $google_sheet[$key] = str_getcsv($string); }
  $header_fields = array_shift($google_sheet);
  $header_find = array(' ', '+'); $header_replace = array('_', 'plus');
  foreach ($header_fields AS $key => $name){ $header_fields[$key] = str_replace($header_find, $header_replace, strtolower($name)); }
  foreach ($google_sheet AS $key => $array){
    $array2 = array();
    foreach ($array AS $key2 => $value){
      $key3 = isset($header_fields[$key2]) ? $header_fields[$key2] : '_'.$key2;
      $array2[$key3] = trim($value);
    }
    $google_sheet[$key] = $array2;
  }

  //die('$google_sheet = '.$google_url.'<br /><pre>'.print_r($google_sheet, true).'</pre>');

  // return nodes if not empty
  return !empty($google_sheet) ? $google_sheet : array();

}

// -- ROBOT STATS -- //
// Define a function for collecting robot stats from CSV
function mmrpg_spreadsheet_robot_stats(){

  $key = '0AhQWV_m4SqtwdHlhUmtpa05yVHY1aHo0Tk5VN0Q5RHc'; // Mega Man RPG Robot Stats
  $google_url = 'https://docs.google.com/spreadsheet/pub?key='.$key.'&single=true&gid=0&output=csv&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'robot_stats');
  //die($google_url.'<br /><pre>'.print_r($rawsheet, true).'</pre>');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['robot_token'])){ $newsheet[$info['robot_token']] = $info; } }
  return $newsheet;

}

// -- MECHA STATS -- //
// Define a function for collecting mecha stats from CSV
function mmrpg_spreadsheet_mecha_stats(){

  $key = '0AhQWV_m4SqtwdGtnZmE5SGJuVTRPSm9CRllPX3JCMGc'; // Mega Man RPG Mecha Stats
  $google_url = 'https://docs.google.com/spreadsheet/pub?key='.$key.'&single=true&gid=0&output=csv&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'mecha_stats');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['mecha_token'])){ $newsheet[$info['mecha_token']] = $info; } }
  return $newsheet;

}

// -- BOSS STATS -- //
// Define a function for collecting boss stats from CSV
function mmrpg_spreadsheet_boss_stats(){

  $key = '0AhQWV_m4SqtwdGtnZmE5SGJuVTRPSm9CRllPX3JCMGc'; // Mega Man RPG Boss Stats
  $google_url = 'https://docs.google.com/spreadsheet/pub?key='.$key.'&single=true&gid=0&output=csv&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'boss_stats');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['boss_token'])){ $newsheet[$info['boss_token']] = $info; } }
  return $newsheet;

}

// -- ROBOT QUOTES -- //
// Define a function for collecting robot quotes from CSV
function mmrpg_spreadsheet_robot_quotes(){

  $key = '1xkjRzbWTeqmYX2A3VbYUssDrb2I00PLnU587IZSMKMA'; // Mega Man RPG Robot Quotes
  $google_url = 'https://docs.google.com/spreadsheets/d/'.$key.'/export?format=csv&id&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'robot_quotes');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['robot_token'])){ $newsheet[$info['robot_token']] = $info; } }
  return $newsheet;

}

// -- MECHA QUOTES -- //
// Define a function for collecting mecha quotes from CSV
function mmrpg_spreadsheet_mecha_quotes(){

  $key = '1Z4-He2of0r5roEzLewobsk7BfA7bO_wyF3aZOMliTQc'; // Mega Man RPG Mecha Quotes
  $google_url = 'https://docs.google.com/spreadsheets/d/'.$key.'/export?format=csv&id&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'mecha_quotes');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['mecha_token'])){ $newsheet[$info['mecha_token']] = $info; } }
  return $newsheet;

}

// -- BOSS QUOTES -- //
// Define a function for collecting boss quotes from CSV
function mmrpg_spreadsheet_boss_quotes(){

  $key = '1Z4-He2of0r5roEzLewobsk7BfA7bO_wyF3aZOMliTQc'; // Mega Man RPG Boss Quotes
  $google_url = 'https://docs.google.com/spreadsheets/d/'.$key.'/export?format=csv&id&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'boss_quotes');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['boss_token'])){ $newsheet[$info['boss_token']] = $info; } }
  return $newsheet;

}

// -- ROBOT DESCRIPTIONS -- //
// Define a function for collecting robot descriptions from CSV
function mmrpg_spreadsheet_robot_descriptions(){

  $key = '1wr7EL0lV55g67JJjGENl8V6coxiq11C2mRu8CuH5jKk'; // Mega Man RPG Robot Descriptions
  $google_url = 'https://docs.google.com/spreadsheets/d/'.$key.'/export?format=csv&id&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'robot_descriptions');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['robot_token'])){ $newsheet[$info['robot_token']] = $info; } }
  return $newsheet;

}

// -- MECHA DESCRIPTIONS -- //
// Define a function for collecting mecha descriptions from CSV
function mmrpg_spreadsheet_mecha_descriptions(){

  $key = '14xbg8tuhz_l4AofN9Ek7OWuPfRQaSFPuRLB1DcvHlF4'; // Mega Man RPG Mecha Descriptions
  $google_url = 'https://docs.google.com/spreadsheets/d/'.$key.'/export?format=csv&id&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'mecha_descriptions');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['mecha_token'])){ $newsheet[$info['mecha_token']] = $info; } }
  return $newsheet;

}

// -- BOSS DESCRIPTIONS -- //
// Define a function for collecting boss descriptions from CSV
function mmrpg_spreadsheet_boss_descriptions(){

  $key = '14xbg8tuhz_l4AofN9Ek7OWuPfRQaSFPuRLB1DcvHlF4'; // Mega Man RPG Boss Descriptions
  $google_url = 'https://docs.google.com/spreadsheets/d/'.$key.'/export?format=csv&id&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'boss_descriptions');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['boss_token'])){ $newsheet[$info['boss_token']] = $info; } }
  return $newsheet;

}

// -- FIELD STATS -- //
// Define a function for collecting field stats from CSV
function mmrpg_spreadsheet_field_stats(){

  $key = '0AhQWV_m4SqtwdGtiWHpHU3pzWGtYbHpHT1g4dUdWMGc'; // Mega Man RPG Field Stats
  $google_url = 'https://docs.google.com/spreadsheet/pub?key='.$key.'&single=true&gid=0&output=csv&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'field_stats');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){
    if (!empty($info['field_token'])){
      $multipliers = array();
      for ($i = 1; $i <= 4; $i++){
        if (isset($info['field_multiplier_'.$i])){
          $raw = $info['field_multiplier_'.$i];
          if (!empty($raw)){
            $raw = preg_replace('/\sx\s/i', ' | ', $raw);
            if (!strstr($raw, '|')){ echo $info['field_token'].' : '.$raw.'<br />'; }
            list($type, $value) = explode('|', preg_replace('/\s+/', '', strtolower($raw)));
            $multipliers[$type] = $value;
          }
          //unset($info['field_multiplier_'.$i]);
        }
      }
      $info['field_multipliers'] = $multipliers;
      $newsheet[$info['field_token']] = $info;
    }
  }
  return $newsheet;

}

// -- FIELD DESCRIPTIONS -- //
// Define a function for collecting field descriptions from CSV
function mmrpg_spreadsheet_field_descriptions(){

  $key = '0AhQWV_m4SqtwdHZ4cjQzLUFUYzcxNWV6WUxrWEtSa0E'; // Mega Man RPG Field Descriptions
  $google_url = 'https://docs.google.com/spreadsheet/pub?key='.$key.'&single=true&gid=0&output=csv&ndplr=1';
  $rawsheet = google_csv_spreadsheet($google_url, 'field_descriptions');
  $newsheet = array();
  foreach ($rawsheet AS $key => $info){ if (!empty($info['field_token'])){ $newsheet[$info['field_token']] = $info; } }
  return $newsheet;

}


?>