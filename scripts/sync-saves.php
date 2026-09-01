<?
// SYNC SAVES SCRIPT

// Call this script to scan the saves directory for pending
// database updates/inserts and then carry them out now

// Pull in the top file first so we have access to everything
define('MMRPG_EXTERNAL_TOP_INCLUDE', true);
define('MMRPG_EXCLUDE_GAME_LOGIC', true);
require('../top.php');

// Define the root directory we'll be scanning for save data
$save_data_dir = MMRPG_CONFIG_ROOTDIR . '.saves/';

// Output formatting for browser testing
echo '<pre>';
echo "Starting Sync Saves Script...\n";
echo "Scanning directory: {$save_data_dir}\n";

// Guard clause: ensure directory exists
if (!file_exists($save_data_dir)){
    exit("Save directory does not exist. Nothing to sync.\n</pre>");
}

// Find all pending save files
$pending_saves = glob($save_data_dir . 'save_*.json');

// Guard clause: stop if empty
if (empty($pending_saves)){
    exit("No pending saves found.\n</pre>");
}

echo "Found " . count($pending_saves) . " pending save(s).\n\n";

// Loop through each file and process it
foreach ($pending_saves as $file_path){
    $filename = basename($file_path);
    echo "Processing: {$filename}...\n";

    // 1. ATOMIC LOCK: Rename the file immediately.
    // If a second cron job starts while this one is running, it won't grab the same file.
    $processing_file_path = $file_path . '.processing';
    if (!rename($file_path, $processing_file_path)){
        echo "- ERROR: Could not lock file (rename failed). Skipping.\n";
        continue;
    }

    // 2. Read and decode the JSON payload
    $json_data = file_get_contents($processing_file_path);
    $payload = json_decode($json_data, true);

    // Guard clause: validate payload
    if (empty($payload) || empty($payload['user_id'])){
        echo "- ERROR: Invalid or empty JSON payload. Skipping.\n";
        continue;
    }

    // Extract the data blocks
    $user_id = $payload['user_id'];
    $save_array = $payload['save_array'];
    $board_array = $payload['board_array'];
    $world_data = $payload['world_data'];
    $user_tables = $payload['user_tables'];
    $date_accessed = $payload['date_accessed'];
    $date_modified = $payload['date_modified'];
    $has_board = !empty($payload['has_board']) ? true : false;
    $has_world = !empty($payload['has_world']) ? true : false;

    // 3. EXECUTE DATABASE UPDATES

    if (!empty($date_accessed) && !empty($date_modified)){
        $db->update('mmrpg_users', array(
            'user_date_accessed' => $date_accessed,
            'user_date_modified' => $date_modified
            ), 'user_id = '.$user_id);
    }

    if (!empty($save_array)){
        $db->update('mmrpg_saves', $save_array, "user_id = {$user_id}");
        echo "- Updated mmrpg_saves\n";
    }

    if (!empty($board_array)){
        if ($has_board){
            $db->update('mmrpg_leaderboard', $board_array, "user_id = {$user_id}");
            echo "- Updated mmrpg_leaderboard\n";
        } else {
            // For brand new users, insert instead
            $db->insert('mmrpg_leaderboard', $board_array);
            echo "- Inserted mmrpg_leaderboard\n";
        }
    }

    if (!empty($world_data)){
        if ($has_world){
            $db->update('mmrpg_users_worlds', $world_data, "user_id = {$user_id}");
            echo "- Updated mmrpg_users_worlds\n";
        } else {
            // For brand new worlds, insert instead
            $db->insert('mmrpg_users_worlds', $world_data);
            echo "- Inserted mmrpg_users_worlds\n";
        }
    }

    // 4. UPDATE SUB-TABLES
    // We bypass mmrpg_save_game_session_user_tables() and call the static methods directly

    if (!empty($user_tables['counters'])){
        rpg_user::update_save_counters($user_id, $user_tables['counters']);
        echo "- Updated save counters\n";
    }
    if (!empty($user_tables['robot_database'])){
        rpg_user::update_robot_records($user_id, $user_tables['robot_database']);
        echo "- Updated robot records\n";
    }
    if (!empty($user_tables['battle_items'])){
        rpg_user::update_unlocked_items($user_id, $user_tables['battle_items']);
        echo "- Updated unlocked items\n";
    }
    if (!empty($user_tables['battle_abilities'])){
        rpg_user::update_unlocked_abilities($user_id, $user_tables['battle_abilities']);
        echo "- Updated unlocked abilities\n";
    }
    if (!empty($user_tables['battle_stars'])){
        rpg_user::update_unlocked_stars($user_id, $user_tables['battle_stars']);
        echo "- Updated unlocked stars\n";
    }

    // 5. CLEANUP
    // Delete the locked processing file now that the DB is fully synced
    unlink($processing_file_path);
    echo "- SUCCESS: Database synced and cache file removed.\n\n";
}

echo "Sync Complete!\n";
echo '</pre>';
?>