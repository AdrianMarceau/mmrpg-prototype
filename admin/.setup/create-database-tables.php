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
ob_echo('==========================');
ob_echo('| CREATE DATABASE TABLES |');
ob_echo('==========================');
ob_echo('');

// Collect a list of all the database table definitions in the setup directory
$table_sql_dir = MMRPG_CONFIG_ROOTDIR.'admin/.sql/tables/';
$table_sql_files = scandir($table_sql_dir);
$table_sql_files = array_filter($table_sql_files, function($s){ if ($s !== '.' && $s !== '..' && substr($s, -4, 4) === '.sql'){ return true; } else { return false; } });

// Check to make sure datbase table definitions were actually collected
if (!empty($table_sql_files)){

    // Print out the list of tables that will be created
    ob_echo('The following database table defintions were found:');
    ob_echo('- '.implode(PHP_EOL.'- ', $table_sql_files));
    ob_echo('');

    // Loop through the table files and run them against the database if not exist
    ob_echo('Looping through tables and importing into the database:');
    foreach ($table_sql_files AS $sql_key => $sql_file){
        $table_name = str_replace('.sql', '', $sql_file);
        $echo_text = '- Importing database table "'.$table_name.'" ... ';
        if (!$db->table_exists($table_name)){
            $db->import_sql_file($table_sql_dir.$sql_file); // attempt to import the file
            $db->table_list(); // auto-refresh cached table list
            if ($db->table_exists($table_name)){ $echo_text .= 'Table created!'; }
            else { $echo_text .= 'Table NOT created!'; }
        } else {
            $echo_text .= 'Table already exists!';
        }
        ob_echo($echo_text);
    }
    ob_echo('');

} else {

    // Print out an error message as nothing was found
    ob_echo('Unable to find any table definitions in '.clean_path($table_sql_dir).'...');
    ob_echo('');

}

ob_echo('...Done!');
ob_echo('');

exit();

?>