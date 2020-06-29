<?

// Prevent game-related logic from running
define('MMRPG_EXCLUDE_GAME_LOGIC', true);

// Require the top file for paths and stuff
$setup_dir = str_replace('\\', '/', dirname(__FILE__)).'/';
$base_dir = dirname(dirname($setup_dir)).'/';
require($base_dir.'top.php');

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

// Define a quick function for executing a shell command and printing the output
function ob_echo_shell_exec($cmd){ ob_echo('$ '.$cmd); $output = shell_exec($cmd); ob_echo($output); }

// Define a function for cleaning a path of the root dir for printing
function clean_path($path){ return str_replace(MMRPG_CONFIG_ROOTDIR, '/', $path); }

// Print the script header for display purposes
ob_echo('');
ob_echo('============================');
ob_echo('| POPULATE DATABASE TABLES |');
ob_echo('============================');
ob_echo('');

// Collect a list of all the seed data for the database tables
$data_sql_dir = MMRPG_CONFIG_ROOTDIR.'admin/.sql/data/';
$data_sql_files = scandir($data_sql_dir);
$data_sql_files = array_filter($data_sql_files, function($s){ if ($s !== '.' && $s !== '..' && substr($s, -4, 4) === '.sql'){ return true; } else { return false; } });

// Check to make sure seed data for the tables was actually collected
if (!empty($data_sql_files)){

    // Print out the list of tables that will be created
    ob_echo('Import data was found for the following database tables:');
    ob_echo('- '.implode(PHP_EOL.'- ', $data_sql_files));
    ob_echo('');

    // Loop through the data files and import them into the database
    ob_echo('Looping through data files and importing into database tables:');
    foreach ($data_sql_files AS $sql_key => $sql_file){
        $table_name = str_replace('.sql', '', $sql_file);
        $data_check_sql = "SELECT 1 FROM {$table_name} LIMIT 1;";
        $echo_text = '- Importing seed data for database table "'.$table_name.'" ... ';
        if (empty($db->get_array($data_check_sql))){
            $db->import_sql_file($data_sql_dir.$sql_file); // attempt to import the file
            if (!empty($db->get_array($data_check_sql))){ $echo_text .= 'Data imported!'; }
            else { $echo_text .= 'Data NOT imported!'; }
        } else {
            $echo_text .= 'Data already exists!';
        }
        ob_echo($echo_text);
    }
    ob_echo('');

} else {

    // Print out an error message as nothing was found
    ob_echo('Unable to find any data files in '.clean_path($data_sql_dir).'...');
    ob_echo('');

}

ob_echo('...Done!');
ob_echo('');

exit();

?>