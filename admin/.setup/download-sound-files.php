<?

// Prevent game-related logic from running
define('MMRPG_EXCLUDE_GAME_LOGIC', true);

// Require the top file for paths and stuff
$setup_dir = str_replace('\\', '/', dirname(__FILE__)).'/';
$base_dir = dirname(dirname($setup_dir)).'/';
require($base_dir.'top.php');

// Require the repository index for looping
require(MMRPG_CONFIG_ROOTDIR.'content/index.php');

// Define the header type so it's easier to display stuff
header('Content-type: text/plain;');

// ONLY allow this file to run in CLI mode
if (php_sapi_name() !== 'cli'){
    die('This setup script can ONLY be run in CLI mode!!!');
}

// Start the output buffer now, we'll flush manually as we go
ob_implicit_flush(true);
ob_start();

// Define a quick function for immediately printing an echo statement
function ob_echo($echo, $silent = false){ if (!$silent){ echo($echo.PHP_EOL); } ob_flush(); }
function ob_echo_nobreak($echo, $silent = false){ if (!$silent){ echo($echo); } ob_flush(); }

// Define a quick function for executing a shell command and printing the output
function ob_echo_shell_exec($cmd){ ob_echo('$ '.$cmd); $output = shell_exec($cmd); ob_echo($output); }

// Define a function for cleaning a path of the root dir for printing
function clean_path($path){ return str_replace(MMRPG_CONFIG_ROOTDIR, '/', $path); }
function clean_cdn_path($path){ global $cdn_sounds_url; return str_replace($cdn_sounds_url, '/', $path); }

// Print the script header for display purposes
ob_echo('');
ob_echo('========================');
ob_echo('| DOWNLOAD SOUND FILES |');
ob_echo('========================');
ob_echo('');


// -- DOWNLOAD SOUNDS FILES -- //

// Check to see if a limit has been defined, else propose a default
$download_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? trim($_REQUEST['limit']) : 20;

// Define the local and CDN base paths for sounds
$cdn_sounds_url = MMRPG_CONFIG_CDN_ROOTURL.'prototype/sounds/index';
$local_sounds_dir = MMRPG_CONFIG_ROOTDIR.'sounds/';
if (!file_exists($local_sounds_dir)){ mkdir($local_sounds_dir); }

// Collect a list of sound files to import from the live CDN
ob_echo_nobreak('Requesting list of sound files from the CDN...');
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_URL, $cdn_sounds_url);
$result = curl_exec($ch);
curl_close($ch);
// If results were empty, exit now, else decode and continue
if (empty($result)){ ob_echo('Failure!'); die('The response from the CDN server was empty!'); }
else { ob_echo('Success!'); $cdn_sounds_list = json_decode($result, true); }
ob_echo('');

//ob_echo('$cdn_sounds_list = '.print_r($cdn_sounds_list, true));

// Now let's loop through the list of folders/files and download them locally
if (!empty($cdn_sounds_list['data'])){

    // Count the number of FILES vs PATHS in the list
    $total_sound_dir_num = 0;
    $total_sound_file_num = 0;
    foreach ($cdn_sounds_list['data'] AS $sound_key => $sound_path){
        if (substr($sound_path, -1, 1) === '/'){ $total_sound_dir_num++; }
        else { $total_sound_file_num++; }
    }
    ob_echo('The CDN returned the following:');
    ob_echo('- '.$total_sound_dir_num.' directory paths');
    ob_echo('- '.$total_sound_file_num.' sound file paths');
    ob_echo('');

    // Loop through the data files and import them into the database
    $num_sound_file_paths = count($cdn_sounds_list['data']);
    ob_echo('Looping through sound paths list and importing them to local directories:');
    ob_echo('');
    $current_dir_num = 0;
    $current_file_num = 0;
    $current_dir_created_num = 0;
    $current_file_downloaded_num = 0;
    $current_file_downloaded_failed_num = 0;
    $download_limit_reached = false;
    foreach ($cdn_sounds_list['data'] AS $sound_key => $sound_path){
        // If this is a folder we must recreate it locally
        if (substr($sound_path, -1, 1) === '/'){
            $current_dir_num++;
            $current_dir_percent = round((($current_dir_num / $total_sound_dir_num) * 100), 2).'%';
            ob_echo('-----');
            $new_sounds_dir = $local_sounds_dir.$sound_path;
            if (!file_exists($new_sounds_dir)){
                ob_echo_nobreak('Creating new sounds directory at "'.clean_path($new_sounds_dir).'" ... ');
                mkdir($new_sounds_dir);
                if (file_exists($new_sounds_dir)){ ob_echo('Success!'); $current_dir_created_num++; }
                else { ob_echo('Failure!'); }
                //ob_echo(' ('.$current_dir_percent.')');
            } else {
                ob_echo('Sounds directory "'.clean_path($new_sounds_dir).'" already exists! ');
            }
        }
        // Otherwise this is a file and we should download it
        else {
            $current_file_num++;
            $current_file_percent = round((($current_file_num / $total_sound_file_num) * 100), 2).'%';
            $prepend_counter = '('.$current_file_num.'/'.$total_sound_file_num.')';
            $cdn_sound_file_url = $cdn_sounds_url.$sound_path;
            $local_sound_file_dir = $local_sounds_dir.$sound_path;
            if (!file_exists($local_sound_file_dir)){
                ob_echo_nobreak($prepend_counter.' Downloading new sound file "'.clean_cdn_path($cdn_sound_file_url).'" from CDN ... ');
                file_put_contents($local_sound_file_dir, fopen($cdn_sound_file_url, 'r'));
                if (file_exists($local_sound_file_dir)){ ob_echo('Success!'); $current_file_downloaded_num++; }
                else { ob_echo('Failure!'); $current_file_downloaded_failed_num++; }
            } else {
                ob_echo($prepend_counter.' Sound file "'.clean_path($local_sound_file_dir).'" already exists! ');
            }
        }
        if (($download_limit > 0)
            && ($current_file_downloaded_num >= $download_limit
                || $current_file_downloaded_failed_num >= $download_limit)){
            $download_limit_reached = true;
            break;
        }
    }
    ob_echo('-----');
    if ($download_limit_reached){
        $percent = round((($current_file_num / $total_sound_file_num) * 100), 2);
        $remaining = $total_sound_file_num - max($current_file_num, $current_file_downloaded_num);
        ob_echo('Stopping early as per download limit of '.$download_limit.' (use limit=X for more)');
        ob_echo('Download Progress: '.$percent.'% ('.$remaining.' remaining)');
        ob_echo('-----');
    }
    ob_echo('');

} else {

    // Print out an error message as nothing was found
    ob_echo('Unable to find any sound files in provided list...');
    ob_echo('');

}

ob_echo('...Done!');
ob_echo('');

exit();

?>