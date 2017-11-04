<?php
/**
 * Mega Man RPG User
 * <p>The object class for all users in the Mega Man RPG World.</p>
 */
class rpg_user {

    /**
     * Create a new RPG user object
     * @param array $user_info (optional)
     * @return rpg_user
     */
    public function __construct($user_info = array()){

        // Return true on success
        return true;

    }


    // -- USER INDEX FUNCTIONS -- //

    /**
     * Get a list of all user fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for user objects
        $index_fields = array(
            'user_id',
            'role_id',
            'user_name',
            'user_name_clean',
            'user_name_public',
            'user_omega',
            'user_gender',
            'user_profile_text',
            'user_credit_text',
            'user_credit_line',
            'user_admin_text',
            'user_image_path',
            'user_background_path',
            'user_colour_token',
            'user_email_address',
            'user_website_address',
            'user_ip_addresses',
            'user_date_created',
            'user_date_accessed',
            'user_date_modified',
            'user_date_birth',
            'user_last_login',
            'user_backup_login',
            'user_flag_approved',
            'user_flag_postpublic',
            'user_flag_postprivate',
            'user_flag_allowchat'
            );

        // Add the table prefix if provided in the argument
        if (!empty($table)){
            $table = trim($table, ' .');
            foreach ($index_fields AS $k => $f){
                $index_fields[$k] = $table.'.'.$f;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $index_fields = implode(', ', $index_fields);
        }

        // Return the table fields, array or string
        return $index_fields;

    }

    // Define an alias function name for the above
    public static function get_fields($implode = false, $table = ''){
        return self::get_index_fields($implode, $table);
    }

    /**
     * Get the entire user index as an array with parsed info
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index($include_nologin = false, $include_unapproved = false, $index_field = 'user_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_nologin){ $temp_where .= 'AND user_last_login <> 0 '; }
        if (!$include_unapproved){ $temp_where .= 'AND user_flag_approved = 1 '; }

        // Collect every user's info from the database index
        $user_fields = self::get_fields(true);
        $user_index = $db->get_array_list("SELECT {$user_fields} FROM mmrpg_users WHERE user_id <> 0 {$temp_where};", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($user_index)){
            $user_index = self::parse_index($user_index);
            return $user_index;
        } else {
            return array();
        }

    }

    /**
     * Get the a custom set users from the index as an array with parsed info
     * @param array $user_list
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index_custom($user_list, $index_field = 'user_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the where string for the query and populate
        $where_string = array();
        foreach ($user_list AS $lookup){
            // If this is numeric, lookup by User ID
            if (is_numeric($lookup)){ $where_string[] = "user_id = {$lookup}"; }
            // Otherwise if string, lookup by User Token
            elseif (is_string($lookup)){ $where_string[] = "user_name_clean = '{$lookup}'"; }
        }
        // Implode the lookup string with ORs in between
        $where_string = implode(' OR ', $where_string);

        // Collect the requested user's info from the database index
        $user_fields = self::get_fields(true);
        $user_index = $db->get_array_list("SELECT {$user_fields} FROM mmrpg_users WHERE user_id <> 0 AND ({$where_string});", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($user_index)){
            $user_index = self::parse_index($user_index);
            return $user_index;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs or tokens for all users in the global index
     * @param string $index_field
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_field_values($index_field, $include_nologin = false, $include_unapproved = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_nologin){ $temp_where .= 'AND user_last_login <> 0 '; }
        if (!$include_unapproved){ $temp_where .= 'AND user_flag_approved = 1 '; }

        // Collect an array of user tokens from the database
        $user_index = $db->get_array_list("SELECT DISTINCT {$index_field} FROM mmrpg_users WHERE user_id <> 0 {$temp_where};", $index_field);

        // Return the tokens if not empty, else nothing
        if (!empty($user_index)){
            $user_fields = array_keys($user_index);
            return $user_fields;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs for all users in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_ids($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_field_values('user_id', $include_nologin, $include_unapproved);

    }

    /**
     * Get the tokens for all users in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_tokens($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_field_values('user_name_clean', $include_nologin, $include_unapproved);

    }

    /**
     * Collect the database info for a specific user by ID or token
     * @param bool $user_lookup (int or string)
     * @return array
     */
    public static function get_info($user_lookup){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this user's info from the database index
        $lookup = !is_numeric($user_lookup) ? "user_name_clean = '{$user_lookup}'" : "user_id = {$user_lookup}";
        $user_fields = self::get_fields(true);
        $user_index = $db->get_array("SELECT {$user_fields} FROM mmrpg_index_users WHERE {$lookup};");

        // Parse and return the data if not empty, else nothing
        if (!empty($user_index)){
            $user_index = self::parse_index_info($user_index);
            return $user_index;
        } else {
            return array();
        }

    }

    /**
     * Parse the fields of a user index array in bulk
     * @param array $user_index
     * @return array
     */
    public static function parse_index($user_index){

        // Loop through each entry and parse its data
        foreach ($user_index AS $token => $info){
            $user_index[$token] = self::parse_user_info($info);
        }

        // Return the parsed index
        return $user_index;

    }

    /**
     * Reformat the raw fields of a user array into proper arrays
     * @param array $user_info
     * @return array
     */
    public static function parse_info($user_info){

        // Return false if empty
        if (empty($user_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($user_info['_parsed'])){ return $user_info; }
        else { $user_info['_parsed'] = true; }

        // Return the parsed user info
        return $user_info;
    }

    /**
     * Define a function for checking if the current user is GUEST mode
     * @return bool
     */
    public static function is_guest(){

        // Check if there is a logged in session user
        if (empty($_SESSION['GAME']['USER']['userid'])
            || $_SESSION['GAME']['USER']['userid'] == MMRPG_SETTINGS_GUEST_ID){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Define a function for checking if the current user is MEMBER mode
     * @return bool
     */
    public static function is_member(){

        // Check if there is a logged in session user
        if (!empty($_SESSION['GAME']['USER']['userid'])
            && $_SESSION['GAME']['USER']['userid'] != MMRPG_SETTINGS_GUEST_ID){
            return true;
        } else {
            return false;
        }

    }

    /**
     * Define a function for generating a temporary GUEST user info array
     * @return bool
     */
    public static function guest_userinfo(){

        // Collect required fields for a userinfo array
        $user_fields = self::get_index_fields();

        // Generate a userinfo array with all index fields
        $guest_userinfo = array();
        foreach ($user_fields AS $field){ $guest_userinfo[$field] = substr($field, -3) === '_id' ? 0 : ''; }

        // Populate key fields with guest-specific values
        $now_time = time();
        $guest_userinfo['user_id'] = MMRPG_SETTINGS_GUEST_ID;
        $guest_userinfo['user_name'] = 'Guest';
        $guest_userinfo['user_name_clean'] = 'guest';
        $guest_userinfo['user_omega'] = '20618f17e896961296207783cc960180';
        $guest_userinfo['user_email_address'] = 'info@megamanpoweredup.net';
        $guest_userinfo['user_date_created'] = $now_time;
        $guest_userinfo['user_date_accessed'] = $now_time;
        $guest_userinfo['user_date_modified'] = $now_time;
        $guest_userinfo['user_last_login'] = $now_time;
        $guest_userinfo['user_backup_login'] = $now_time;

        // Return the generated guest userinfo
        return $guest_userinfo;

    }




    // -- GAME SETTINGS AND REWARDS -- //


    /**
     * Pull an array of unlocked players for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_players($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of unlocked players for this user from the database
        $raw_players = $db->get_array_list("SELECT
            `user_id`,
            `player_token`,
            `player_points`
            FROM mmrpg_users_players
            WHERE `user_id` = {$user_id}
            ;");
        if (empty($raw_players)){ return array(); }

        // Reformat to game-compatible array
        $user_players = $raw_players;

        // Return the final array
        return $user_players;

    }


    /**
     * Pull an array of unlocked player abilities for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_players_abilities($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of unlocked player abilities for this user from the database
        $raw_players_abilities = $db->get_array_list("SELECT
            `user_id`,
            `player_token`,
            `ability_token`
            FROM mmrpg_users_players_abilities
            WHERE `user_id` = {$user_id}
            ;");
        if (empty($raw_players_abilities)){ return array(); }

        // Reformat to game-compatible array
        $user_players_abilities = $raw_players_abilities;

        // Return the final array
        return $user_players_abilities;

    }


    /**
     * Pull an array of unlocked robot abilities for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_robots_abilities($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of unlocked robot abilities for this user from the database
        $raw_robots_abilities = $db->get_array_list("SELECT
            `user_id`,
            `robot_token`,
            `ability_token`
            FROM mmrpg_users_robots_abilities
            WHERE `user_id` = {$user_id}
            ;");
        if (empty($raw_robots_abilities)){ return array(); }

        // Reformat to game-compatible array
        $user_robots_abilities = $raw_robots_abilities;

        // Return the final array
        return $user_robots_abilities;

    }


    /**
     * Pull an array of unlocked robot movesets for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_robots_movesets($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of equipped robot abilities for this user from the database
        $raw_robots_movesets = $db->get_array_list("SELECT
            `user_id`,
            `robot_token`,
            `ability_token`,
            `slot_key`
            FROM mmrpg_users_robots_movesets
            WHERE `user_id` = {$user_id}
            ORDER BY
            `robot_token` ASC,
            `slot_key` ASC
            ;");
        if (empty($raw_robots_movesets)){ return array(); }

        // Reformat to game-compatible array
        $user_robots_movesets = $raw_robots_movesets;

        // Return the final array
        return $user_robots_movesets;

    }


    /**
     * Pull an array of unlocked robots for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_robots($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of unlocked robots for this user from the database
        $raw_robots = $db->get_array_list("SELECT
            `user_id`,
            `robot_token`,
            `robot_player`,
            `robot_player_original`,
            `robot_image`,
            `robot_core`,
            `robot_item`,
            `robot_level`,
            `robot_experience`,
            `robot_experience_total`,
            `robot_energy_bonuses`,
            `robot_energy_bonuses_pending`,
            `robot_weapons_bonuses`,
            `robot_weapons_bonuses_pending`,
            `robot_attack_bonuses`,
            `robot_attack_bonuses_pending`,
            `robot_defense_bonuses`,
            `robot_defense_bonuses_pending`,
            `robot_speed_bonuses`,
            `robot_speed_bonuses_pending`,
            `robot_flags`,
            `robot_counters`,
            `robot_values`
            FROM mmrpg_users_robots
            WHERE `user_id` = {$user_id}
            ;");
        if (empty($raw_robots)){ return array(); }

        // Reformat to game-compatible array
        $user_robots = $raw_robots;

        // Return the final array
        return $user_robots;

    }


    /**
     * Pull an array of unlocked abilities for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_battle_abilities($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of unlocked abilities for this user from the database
        $raw_battle_abilities = $db->get_array_list("SELECT
            `ability_token`
            FROM mmrpg_users_abilities
            WHERE `user_id` = {$user_id}
            ;");
        if (empty($raw_battle_abilities)){ return array(); }

        // Reformat to game-compatible array
        $user_battle_abilities = array_map(function($arr){ return $arr['ability_token']; }, $raw_battle_abilities);

        // Return the final array
        return $user_battle_abilities;

    }


    /**
     * Pull an array of unlocked items for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_battle_items($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of unlocked items for this user from the database
        $raw_battle_items = $db->get_array_list("SELECT
            `item_token`,
            `item_quantity`
            FROM mmrpg_users_items
            WHERE `user_id` = {$user_id}
            ;");
        if (empty($raw_battle_items)){ return array(); }

        // Reformat to game-compatible array
        $raw_battle_tokens = array_map(function($arr){ return $arr['item_token']; }, $raw_battle_items);
        $raw_battle_quantities = array_map(function($arr){ return $arr['item_quantity']; }, $raw_battle_items);
        $user_battle_items = array_combine($raw_battle_tokens, $raw_battle_quantities);

        // Return the final array
        return $user_battle_items;

    }


    /**
     * Pull an array of unlocked stars for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_battle_stars($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of unlocked stars for this user from the database
        $raw_battle_stars = $db->get_array_list("SELECT
            `star_token`,
            `star_name`,
            `star_kind`,
            `star_type`,
            `star_type2`,
            `star_field`,
            `star_field2`,
            `star_player`,
            `star_date`
            FROM mmrpg_users_stars
            WHERE `user_id` = {$user_id}
            ;", 'star_token');
        if (empty($raw_battle_stars)){ return array(); }

        // Collect into game-compatible array
        $user_battle_stars = $raw_battle_stars;

        // Return the final array
        return $user_battle_stars;

    }


    /**
     * Pull a complete array of battle vars (rewards + settings) for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_battle_vars($user_id, &$index_arrays = array()){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        //echo('<pre>$index_arrays = '.print_r($index_arrays, true).'</pre>');

        // Collect the players and robots for this user for all requests
        if (!isset($index_arrays['battle_players'])){ $index_arrays['battle_players'] = rpg_user::get_players($user_id); }
        if (!isset($index_arrays['battle_players_abilities'])){ $index_arrays['battle_players_abilities'] = rpg_user::get_players_abilities($user_id); }
        if (!isset($index_arrays['battle_robots'])){ $index_arrays['battle_robots'] = rpg_user::get_robots($user_id); }
        if (!isset($index_arrays['battle_robots_abilities'])){ $index_arrays['battle_robots_abilities'] = rpg_user::get_robots_abilities($user_id); }
        if (!isset($index_arrays['battle_robots_movesets'])){ $index_arrays['battle_robots_movesets'] = rpg_user::get_robots_movesets($user_id); }
        if (!isset($index_arrays['temp_robot_player_index'])){ $index_arrays['temp_robot_player_index'] = array(); }

        //echo('<pre>$index_arrays = '.print_r($index_arrays, true).'</pre>');

        // Create arrays to hold the battle rewards and settings
        $raw_battle_settings = rpg_user::get_battle_settings($user_id, $index_arrays);
        $raw_battle_rewards = rpg_user::get_battle_rewards($user_id, $index_arrays);

        //echo('<pre>$raw_battle_settings = '.print_r($raw_battle_settings, true).'</pre>');
        //echo('<pre>$raw_battle_rewards = '.print_r($raw_battle_rewards, true).'</pre>');

        // Collect into game-compatible array
        $user_battle_vars = array();
        $user_battle_vars['battle_settings'] = $raw_battle_settings;
        $user_battle_vars['battle_rewards'] = $raw_battle_rewards;

        // Return the final array
        return $user_battle_vars;

    }


    /**
     * Pull a complete array of battle settings for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_battle_settings($user_id, &$index_arrays = array()){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Collect the players and robots for this user for all requests
        if (!isset($index_arrays['battle_players'])){ $index_arrays['battle_players'] = rpg_user::get_players($user_id); }
        if (!isset($index_arrays['battle_players_abilities'])){ $index_arrays['battle_players_abilities'] = rpg_user::get_players_abilities($user_id); }
        if (!isset($index_arrays['battle_robots'])){ $index_arrays['battle_robots'] = rpg_user::get_robots($user_id); }
        if (!isset($index_arrays['battle_robots_abilities'])){ $index_arrays['battle_robots_abilities'] = rpg_user::get_robots_abilities($user_id); }
        if (!isset($index_arrays['battle_robots_movesets'])){ $index_arrays['battle_robots_movesets'] = rpg_user::get_robots_movesets($user_id); }
        if (!isset($index_arrays['temp_robot_player_index'])){ $index_arrays['temp_robot_player_index'] = array(); }

        //echo('<pre>$index_arrays['battle_players'] = '.print_r($index_arrays['battle_players'], true).'</pre>');
        //echo('<pre>$index_arrays['battle_players_abilities'] = '.print_r($index_arrays['battle_players_abilities'], true).'</pre>');
        //echo('<pre>$index_arrays['battle_robots'] = '.print_r($index_arrays['battle_robots'], true).'</pre>');
        //echo('<pre>$index_arrays['battle_robots_abilities'] = '.print_r($index_arrays['battle_robots_abilities'], true).'</pre>');
        //echo('<pre>$index_arrays['battle_robots_movesets'] = '.print_r($index_arrays['battle_robots_movesets'], true).'</pre>');

        // Create arrays to hold the battle settings
        $raw_battle_settings = array();

        // Loop through players and add them to the settings array
        if (!empty($index_arrays['battle_players'])){
            foreach ($index_arrays['battle_players'] AS $player_key => $player_info){

                // Collect the player token for reference
                $player_token = $player_info['player_token'];

                // Construct the player settings with define basic info
                $player_settings = array();
                $player_settings['player_token'] = $player_info['player_token'];

                // Define player settings list arrays to be populated later
                $player_settings['player_robots'] = array();
                $player_settings['player_fields'] = array();

                // Add this player's data to the parent settings array
                $raw_battle_settings[$player_token] = $player_settings;
            }

            // Loop through robots and add them to the settings array
            if (!empty($index_arrays['battle_robots'])){
                foreach ($index_arrays['battle_robots'] AS $robot_key => $robot_info){

                    // Collect the robot and player token for reference
                    $robot_token = $robot_info['robot_token'];
                    $player_token = $robot_info['robot_player'];
                    $index_arrays['temp_robot_player_index'][$robot_token] = $player_token;

                    // Construct the robot settings array with required info
                    $robot_settings = array();
                    $robot_settings['flags'] = !empty($robot_info['robot_flags']) ? json_decode($robot_info['robot_flags'], true) : array();
                    $robot_settings['values'] = !empty($robot_info['robot_values']) ? json_decode($robot_info['robot_values'], true) : array();
                    $robot_settings['counters'] = !empty($robot_info['robot_counters']) ? json_decode($robot_info['robot_counters'], true) : array();
                    $robot_settings['robot_token'] = $robot_info['robot_token'];
                    $robot_settings['robot_core'] = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
                    $robot_settings['robot_image'] = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : '';
                    $robot_settings['original_player'] = !empty($robot_info['original_player']) ? $robot_info['original_player'] : '';
                    $robot_settings['robot_abilities'] = array();

                    // Add this robot's data to the parent player settings array
                    $raw_battle_settings[$player_token]['player_robots'][$robot_token] = $robot_settings;

                }
            }

            // Loop through robot-equipped abilities and add them to the rewards array
            if (!empty($index_arrays['battle_robots_movesets'])){
                foreach ($index_arrays['battle_robots_movesets'] AS $ability_key => $ability_info){

                    // Collect the ability and player token for reference
                    $ability_token = $ability_info['ability_token'];
                    $robot_token = $ability_info['robot_token'];
                    $slot_key = $ability_info['slot_key'];
                    $player_token = $index_arrays['temp_robot_player_index'][$robot_token];

                    // Construct the ability settings array with required info
                    $ability_settings = array();
                    $ability_settings['ability_token'] = $ability_token;

                    // Add this ability's data to the parent player rewards array
                    $raw_battle_settings[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_settings;

                }
            }

        }

        // Collect into game-compatible array
        $user_battle_settings = $raw_battle_settings;

        // Return the final array
        return $user_battle_settings;

    }


    /**
     * Pull a complete array of battle rewards for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_battle_rewards($user_id, &$index_arrays = array()){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Collect the players and robots for this user for all requests
        if (!isset($index_arrays['battle_players'])){ $index_arrays['battle_players'] = rpg_user::get_players($user_id); }
        if (!isset($index_arrays['battle_players_abilities'])){ $index_arrays['battle_players_abilities'] = rpg_user::get_players_abilities($user_id); }
        if (!isset($index_arrays['battle_robots'])){ $index_arrays['battle_robots'] = rpg_user::get_robots($user_id); }
        if (!isset($index_arrays['battle_robots_abilities'])){ $index_arrays['battle_robots_abilities'] = rpg_user::get_robots_abilities($user_id); }
        if (!isset($index_arrays['temp_robot_player_index'])){ $index_arrays['temp_robot_player_index'] = array(); }

        //echo('<pre>$index_arrays['battle_players'] = '.print_r($index_arrays['battle_players'], true).'</pre>');
        //echo('<pre>$index_arrays['battle_players_abilities'] = '.print_r($index_arrays['battle_players_abilities'], true).'</pre>');
        //echo('<pre>$index_arrays['battle_robots'] = '.print_r($index_arrays['battle_robots'], true).'</pre>');
        //echo('<pre>$index_arrays['battle_robots_abilities'] = '.print_r($index_arrays['battle_robots_abilities'], true).'</pre>');
        //echo('<pre>$index_arrays['battle_robots_movesets'] = '.print_r($index_arrays['battle_robots_movesets'], true).'</pre>');

        // Create arrays to hold the battle rewards
        $raw_battle_rewards = array();

        // Loop through players and add them to the rewards array
        if (!empty($index_arrays['battle_players'])){
            foreach ($index_arrays['battle_players'] AS $player_key => $player_info){

                // Collect the player token for reference
                $player_token = $player_info['player_token'];

                // Construct the player rewards array with required info
                $player_rewards = array();
                $player_rewards['player_token'] = $player_info['player_token'];
                $player_rewards['player_points'] = $player_info['player_points'];

                // Define player rewards list arrays to be populated later
                $player_rewards['player_abilities'] = array();
                $player_rewards['player_robots'] = array();

                // Add this player's data to the parent rewards array
                $raw_battle_rewards[$player_token] = $player_rewards;
            }

            // Loop through player-unlocked abilities and add them to the rewards array
            if (!empty($index_arrays['battle_players_abilities'])){
                foreach ($index_arrays['battle_players_abilities'] AS $ability_key => $ability_info){

                    // Collect the ability and player token for reference
                    $ability_token = $ability_info['ability_token'];
                    $player_token = $ability_info['player_token'];

                    // Construct the ability rewards array with required info
                    $ability_rewards = array();
                    $ability_rewards['ability_token'] = $ability_token;

                    // Add this ability's data to the parent player rewards array
                    $raw_battle_rewards[$player_token]['player_abilities'][$ability_token] = $ability_rewards;

                }
            }

            // Loop through robots and add them to the rewards array
            if (!empty($index_arrays['battle_robots'])){
                foreach ($index_arrays['battle_robots'] AS $robot_key => $robot_info){

                    // Collect the robot and player token for reference
                    $robot_token = $robot_info['robot_token'];
                    $player_token = $robot_info['robot_player'];
                    $index_arrays['temp_robot_player_index'][$robot_token] = $player_token;

                    // Construct the robot rewards array with required info
                    $robot_rewards = array();
                    $robot_rewards['flags'] = !empty($robot_info['robot_flags']) ? json_decode($robot_info['robot_flags'], true) : array();
                    $robot_rewards['values'] = !empty($robot_info['robot_values']) ? json_decode($robot_info['robot_values'], true) : array();
                    $robot_rewards['counters'] = !empty($robot_info['robot_counters']) ? json_decode($robot_info['robot_counters'], true) : array();
                    $robot_rewards['robot_token'] = $robot_info['robot_token'];
                    $robot_rewards['robot_level'] = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : 1;
                    $robot_rewards['robot_experience'] = !empty($robot_info['robot_experience']) ? $robot_info['robot_experience'] : 0;
                    $robot_rewards['robot_energy'] = !empty($robot_info['robot_energy_bonuses']) ? $robot_info['robot_energy_bonuses'] : 0;
                    $robot_rewards['robot_energy_pending'] = !empty($robot_info['robot_energy_bonuses_pending']) ? $robot_info['robot_energy_bonuses_pending'] : 0;
                    $robot_rewards['robot_attack'] = !empty($robot_info['robot_attack_bonuses']) ? $robot_info['robot_attack_bonuses'] : 0;
                    $robot_rewards['robot_attack_pending'] = !empty($robot_info['robot_attack_bonuses_pending']) ? $robot_info['robot_attack_bonuses_pending'] : 0;
                    $robot_rewards['robot_defense'] = !empty($robot_info['robot_defense_bonuses']) ? $robot_info['robot_defense_bonuses'] : 0;
                    $robot_rewards['robot_defense_pending'] = !empty($robot_info['robot_defense_bonuses_pending']) ? $robot_info['robot_defense_bonuses_pending'] : 0;
                    $robot_rewards['robot_speed'] = !empty($robot_info['robot_speed_bonuses']) ? $robot_info['robot_speed_bonuses'] : 0;
                    $robot_rewards['robot_speed_pending'] = !empty($robot_info['robot_speed_bonuses_pending']) ? $robot_info['robot_speed_bonuses_pending'] : 0;
                    $robot_rewards['robot_abilities'] = array();

                    // Add this robot's data to the parent player rewards array
                    $raw_battle_rewards[$player_token]['player_robots'][$robot_token] = $robot_rewards;

                }
            }

            // Loop through robot-unlocked abilities and add them to the rewards array
            if (!empty($index_arrays['battle_robots_abilities'])){
                foreach ($index_arrays['battle_robots_abilities'] AS $ability_key => $ability_info){

                    // Collect the ability and player token for reference
                    $ability_token = $ability_info['ability_token'];
                    $robot_token = $ability_info['robot_token'];
                    $player_token = $index_arrays['temp_robot_player_index'][$robot_token];

                    // Construct the ability rewards array with required info
                    $ability_rewards = array();
                    $ability_rewards['ability_token'] = $ability_token;

                    // Add this ability's data to the parent player rewards array
                    $raw_battle_rewards[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_rewards;

                }
            }

        }

        // Collect into game-compatible array
        $user_battle_rewards = $raw_battle_rewards;

        // Return the final array
        return $user_battle_rewards;

    }


    /**
     * Pull an array of encounter records for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_robot_database($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of encountered targets for this user from the database
        $raw_robot_database = $db->get_array_list("SELECT
            `robot_token`,
            `robot_encountered`,
            `robot_summoned`,
            `robot_scanned`,
            `robot_defeated`,
            `robot_unlocked`
            FROM mmrpg_users_robots_records
            WHERE `user_id` = {$user_id}
            ;", 'robot_token');
        if (empty($raw_robot_database)){ return array(); }

        // Collect into game-compatible array
        $user_robot_database = $raw_robot_database;

        // Return the final array
        return $user_robot_database;

    }


    /**
     * Pull an array of unlocked robot alts for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_robot_alts($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Pull a list of unlocked robot alts for this user from the database
        $raw_robot_alts = $db->get_array_list("SELECT
            `robot_token`,
            `alt_token`
            FROM mmrpg_users_robots_alts
            WHERE `user_id` = {$user_id}
            ;");
        if (empty($raw_robot_alts)){ return array(); }

        // Reformat to game-compatible array
        $user_robot_alts = array();
        foreach ($raw_robot_alts AS $key => $alt_info){
            $robot_token = $alt_info['robot_token'];
            $alt_token = $alt_info['alt_token'];
            if (!isset($user_robot_alts[$robot_token])){ $user_robot_alts[$robot_token] = array(); }
            $user_robot_alts[$robot_token][] = $alt_token;
        }

        // Return the final array
        return $user_robot_alts;

    }


}
?>