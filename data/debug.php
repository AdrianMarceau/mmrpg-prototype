<?
// Define the debug checkpoint function
function mmrpg_debug_checkpoint($file, $line, $extra = ''){
  global $DB;
  static $last_memory_usage = 0;
  static $last_micro_time = 0;
  static $checkpoint = 0;
  $query = 'CHECKPOINT in '.str_replace(MMRPG_CONFIG_ROOTDIR, '', str_replace('\\', '/', $file)).' on line '.$line.' where memory is ';
  $mem_usage = memory_get_usage();
  $micro_time = microtime(true);
  if ($mem_usage < 1024){ $query .= $mem_usage.' B'; }
  elseif ($mem_usage < 1048576){ $query .= round($mem_usage/1024,2).' KB'; }
  else { $query .= round($mem_usage/1048576,2).' MB'; }
  $query .= ' ';
  $mem_colour = 'grey';
  $mem_diff = $mem_usage - $last_memory_usage;
  $time_diff = $micro_time - $last_micro_time;
  $last_memory_usage = $mem_usage;
  $last_micro_time = $micro_time;

  if ($mem_diff < 1){ $mem_diff = $mem_diff * -1; $mem_sign = '-'; $mem_colour = 'green'; }
  elseif ($mem_diff > 1){ $mem_sign = '+'; $mem_colour = 'red'; }
  else { $mem_sign = '+/-'; $mem_colour = 'grey'; }
  $query .= '(<span style="color: '.$mem_colour.';">'.$mem_sign;
  if ($mem_diff < 1024){ $query .= $mem_diff.' B'; }
  elseif ($mem_diff < 1048576){ $query .= round($mem_diff/1024,2).' KB'; }
  else { $query .= round($mem_diff/1048576,2).' MB'; }
  $query .= '</span>)'; //."\r\n";

  $query .= ' [<span style="color: grey;">+'.round($time_diff, 6).'s</span>]'."\r\n";

  if (!empty($extra)){ $query .= '<div style="font-size: 90%; padding: 5px 0 0 30px; margin: 0; color: #6D6D6D;">'.$extra.'</div>'; }
  $query = '<span style="color: #262626;">'.$query.'</span>';
  $DB->DEBUG['script_queries'][] = $query;

  //echo $query;
  $checkpoint++;
  //if ($mem_usage >= (1024 * 1024 * 50)){ unset($DB); exit("\n\n|| -- 50MB MEMORY OVERLOAD --||\n\n"); }
}
?>