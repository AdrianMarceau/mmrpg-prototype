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


// -- IMPORT SQL FILES -- //

ob_echo('IMPORT SQL FILES:');
ob_echo('');

// Collect a list of all the seed data for the database tables
$sql_data_dir = MMRPG_CONFIG_ROOTDIR.'admin/.sql/data/';
$sql_data_files = scandir($sql_data_dir);
$sql_data_files = array_filter($sql_data_files, function($f){ if ($f !== '.' && $f !== '..' && substr($f, -4, 4) === '.sql'){ return true; } else { return false; } });

// Check to make sure seed data for the tables was actually collected
if (!empty($sql_data_files)){

    // Print out the list of tables that will be created
    ob_echo('SQL import data was found for the following database tables:');
    ob_echo('- '.implode(PHP_EOL.'- ', $sql_data_files));
    ob_echo('');

    // Loop through the data files and import them into the database
    ob_echo('Looping through SQL data files and importing into database tables:');
    foreach ($sql_data_files AS $sql_key => $sql_file){
        $table_name = str_replace('.sql', '', $sql_file);
        $data_check_sql = "SELECT 1 FROM {$table_name} LIMIT 1;";
        $echo_text = '- Importing seed data for database table "'.$table_name.'" ... ';
        if (empty($db->get_array($data_check_sql))){
            $db->import_sql_file($sql_data_dir.$sql_file); // attempt to import the file
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
    ob_echo('Unable to find any data files in '.clean_path($sql_data_dir).'...');
    ob_echo('');

}

ob_echo('----------------------------');
ob_echo('');


// -- IMPORT JSON FILES -- //

ob_echo('IMPORT JSON FILES:');
ob_echo('');

// Require the content type index for looping
require($setup_dir.'_content-types-index.php');

// Loop through the content types one-by-one to check for JSON files
foreach ($content_types_index AS $content_key => $content_info){

    // Collect refs to the content type tokens, table, etc.
    $ctype_token = $content_info['token'];
    $ctype_xtoken = $content_info['xtoken'];
    $table_name = $content_info['database_table'];

    // If this content doesn't have a table, skip it
    if (empty($table_name)){ continue; }

    // Collect a list of all the seed data for the database tables
    $json_data_dir = MMRPG_CONFIG_ROOTDIR.$content_info['content_path'];
    $json_data_dirs = scandir($json_data_dir);
    $json_data_dirs = array_filter($json_data_dirs, function($d) use($json_data_dir){ if ($d !== '.' && $d !== '..' && file_exists($json_data_dir.$d.'/data.json')){ return true; } else { return false; } });

    // Check to make sure seed data for the tables was actually collected
    if (!empty($json_data_dirs)){

        // Print out the list of tables that will be created
        ob_echo('JSON import data was found for the following '.$ctype_xtoken.':');
        ob_echo('- '.implode(PHP_EOL.'- ', $json_data_dirs));
        ob_echo('');

        // Loop through the data files and import them into the database
        ob_echo('Looping through JSON data files and importing into database table "'.$table_name.'":');
        foreach ($json_data_dirs AS $object_key => $object_token){
            $json_file = $object_token.'/data.json';
            $json_markup = file_get_contents($json_data_dir.$json_file);
            $json_data = json_decode($json_markup, true);
            $real_object_token = $json_data[$ctype_token.'_token'];
            $echo_text = '- Importing '.$ctype_token.' data for "'.$real_object_token.'" into database table "'.$table_name.'" ... ';
            $data_check_sql = "SELECT {$ctype_token}_token FROM {$table_name} WHERE {$ctype_token}_token = '{$real_object_token}';";
            if (empty($db->get_array($data_check_sql))){
                $db->insert($table_name, $json_data); // attempt to insert the data
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
        ob_echo('Unable to find any data files in '.clean_path($json_data_dir).'...');
        ob_echo('');

    }

}

ob_echo('----------------------------');
ob_echo('');

ob_echo('...Done!');
ob_echo('');

exit();

?>