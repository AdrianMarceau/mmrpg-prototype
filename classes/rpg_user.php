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
            uplayers.`user_id`,
            uplayers.`player_token`,
            uplayers.`player_points`,
            iplayers.`player_order`
            FROM mmrpg_users_players AS uplayers
            LEFT JOIN mmrpg_index_players AS iplayers ON iplayers.player_token = uplayers.player_token
            WHERE uplayers.`user_id` = {$user_id}
            ORDER BY
            iplayers.`player_order` ASC
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
            ORDER BY `robot_order` ASC
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
                    $robot_settings['robot_image'] = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : '';
                    $robot_settings['robot_core'] = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
                    $robot_settings['robot_item'] = !empty($robot_info['robot_item']) ? $robot_info['robot_item'] : '';
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


    /**
     * Pull an array of encounter records for a given user ID from the database
     * @param int $user_id
     * @param string $mission_result (optional)
     * @return array
     */
    public static function get_mission_records($user_id, $return_result = ''){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Compensate for empty or missing record kind
        $allowed_results = array('victory', 'defeat');
        if (!empty($return_result) && !in_array($return_result, $allowed_results)){ $return_result = ''; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Define an array to hold collected mission records
        $raw_mission_records = array();

        // Define the base query for pulling high score records
        $raw_mission_query = "SELECT
            m.row_id,
            m.user_id,
            m.player_token,
            m.mission_token,
            m.mission_result,
            m.mission_level,
            m.mission_points_earned,
            m.mission_turns_target,
            m.mission_turns_used,
            m.mission_robots_target,
            m.mission_robots_used,
            m.mission_points_base,
            m.mission_date,
            c.mission_count
            FROM mmrpg_users_missions_records m                             -- m from max
                LEFT JOIN mmrpg_users_missions_records b                    -- b from bigger
                    ON b.mission_token = m.mission_token                    -- match max row with bigger row by home
                    AND b.user_id = m.user_id                               -- must be the same user
                    AND b.mission_points_earned >= m.mission_points_earned  -- want bigger than max
                    AND b.row_id > m.row_id                                 -- want bigger than max
                LEFT JOIN (SELECT
                    c.user_id,
                    c.mission_token,
                    c.mission_result,
                    COUNT(*) AS mission_count
                    FROM mmrpg_users_missions_records AS c
                    GROUP BY
                    c.mission_token) AS c
                    ON c.user_id = m.user_id
                    AND c.mission_token = m.mission_token
                    AND c.mission_result = m.mission_result
            WHERE
                1 = 1
                AND b.mission_points_earned IS NULL          -- keep only if there is no bigger than max
                AND c.mission_count IS NOT NULL              -- keep only if mission count for given result
                AND m.user_id = {$user_id}
                AND m.mission_result = '{mission_result}'
            ORDER BY
                m.mission_token ASC
            ;";

        // Loop through result types and collect records for allowed
        foreach ($allowed_results AS $result_key => $result_token){

            // Add an entry to the parent array for this result token
            $raw_mission_records[$result_token] = array();

            // Collect all mission records with a matching result if allowed
            if (empty($return_result) || $result_token == $return_result){

                // Pull a list of high scores for all misions with a victory result
                $temp_mission_query = str_replace('{mission_result}', $result_token, $raw_mission_query);
                $temp_mission_results = $db->get_array_list($temp_mission_query, 'mission_token');
                if (empty($temp_mission_results)){ $temp_mission_results = array(); }

                // Loop through mission results and add to nested player arrays
                if (!empty($temp_mission_results)){
                    foreach ($temp_mission_results AS $mission_token => $mission_record){

                        // Collect the player token for indexing purposes
                        $mission_player = $mission_record['player_token'];

                        // Use the db record to generate a battle-compatible array
                        $battle_record = array(
                            'battle_token' => $mission_record['mission_token'],
                            'battle_count' => $mission_record['mission_count'],
                            'battle_level' => $mission_record['mission_level'],
                            'battle_turns_target' => $mission_record['mission_turns_target'],
                            'battle_turns_used' => $mission_record['mission_turns_used'],
                            'battle_robots_target' => $mission_record['mission_robots_target'],
                            'battle_robots_used' => $mission_record['mission_robots_used'],
                            'battle_points_base' => $mission_record['mission_points_base'],
                            'battle_points_earned' => $mission_record['mission_points_earned'],
                            'battle_date' => $mission_record['mission_date']
                            );

                        // Add these mission records to the parent record array
                        $raw_mission_records[$result_token][$mission_player][$mission_token] = $battle_record;

                    }
                }

            }

        }

        // Collect either whole or part of records into game-compatible array based on request
        if (empty($return_result)){
            $user_mission_records = $raw_mission_records;
        } elseif (!empty($return_result) && !empty($raw_mission_records[$return_result])){
            $user_mission_records = $raw_mission_records[$return_result];
        } elseif (!empty($return_result) && empty($raw_mission_records[$return_result])){
            $user_mission_records = array();
        }

        // Return the final array
        return $user_mission_records;

    }


    /**
     * Pull an array of shops for a given user ID from the database
     * @param int $user_id
     * @param string $return_shop (optional)
     * @return array
     */
    public static function get_shops($user_id, $return_shop = ''){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Compensate for empty or missing record kind
        $allowed_shops = rpg_shop::get_index_tokens(true, false);
        if (!empty($return_shop) && !in_array($return_shop, $allowed_shops)){ $return_shop = ''; }

        // Define the action key map for later looping
        $action_key_map = array('selling' => 'sold', 'buying' => 'bought');

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Define the query filter for this request
        $raw_where = '';
        if (!empty($return_shop)){ $raw_where .= "AND ushops.shop_token = '{$return_shop}' "; }
        else { $raw_where .= "AND ushops.shop_token IN ('".implode("','", $allowed_shops)."') "; }

        // Define the base query for pulling shop records
        $raw_shop_query = "SELECT
            ushops.row_id,
            ushops.user_id,
            ushops.shop_token,
            ushops.shop_level,
            ushops.shop_experience,
            ushops.shop_zenny_earned,
            ushops.shop_zenny_spent
            FROM mmrpg_users_shops AS ushops
                LEFT JOIN mmrpg_index_shops AS ishops ON ishops.shop_token = ushops.shop_token
            WHERE
                ushops.user_id = {$user_id}
                {$raw_where}
            ORDER BY
                ishops.shop_order
            ;";

        // Attempt to collect shops from the database with the above query
        $raw_shops = $db->get_array_list($raw_shop_query, 'shop_token');

        // If the results were not empty, loop through and collect into nested array
        $user_shops = array();
        if (!empty($raw_shops)){
            foreach ($raw_shops AS $shop_token => $raw_shop_info){

                // Define an array to hold parsed shop info
                $shop_info = array();

                // Collect basic fields for this shop from the raw records
                $shop_info['shop_token'] = $shop_token;
                $shop_info['shop_level'] = !empty($raw_shop_info['shop_level']) ? $raw_shop_info['shop_level'] : 1;
                $shop_info['shop_experience'] = !empty($raw_shop_info['shop_experience']) ? $raw_shop_info['shop_experience'] : 0;
                $shop_info['zenny_earned'] = !empty($raw_shop_info['shop_zenny_earned']) ? $raw_shop_info['shop_zenny_earned'] : 0;
                $shop_info['zenny_spent'] = !empty($raw_shop_info['shop_zenny_spent']) ? $raw_shop_info['shop_zenny_spent'] : 0;

                // Loop through the action map and predefine selling/buying arrays for records
                foreach ($action_key_map AS $action_key1 => $action_key2){
                    if (!empty($raw_shop_info['shop_products_'.$action_key1])){
                        foreach ($raw_shop_info['shop_products_'.$action_key1] AS $product_key){
                            $shop_info[$product.'_'.$action_key2] = array();
                        }
                    }
                }

                // Add this shop info to the global shop index
                $user_shops[$shop_token] = $shop_info;

            }
        }

        // Return either the whole shop array or a part of it based on the request
        if (!empty($return_shop) && !empty($user_shops[$return_shop])){ return $user_shops[$return_shop]; }
        elseif (!empty($return_shop) && empty($user_shops[$return_shop])){ return array(); }
        else { return $user_shops; }

    }


    /**
     * Pull an array of encounter records for a given user ID from the database
     * @param int $user_id
     * @param string $return_shop (optional)
     * @return array
     */
    public static function get_shop_records($user_id, $return_shop = ''){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Compensate for empty or missing record kind
        $allowed_shops = rpg_shop::get_index_tokens(true, false);
        if (!empty($return_shop) && !in_array($return_shop, $allowed_shops)){ $return_shop = ''; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Define the query filter for this request
        $raw_where = '';
        if (!empty($return_shop)){ $raw_where .= "AND urecords.shop_token = '{$return_shop}' "; }
        else { $raw_where .= "AND urecords.shop_token IN ('".implode("','", $allowed_shops)."') "; }

        // Define the base query for pulling shop records
        $raw_shop_query = "SELECT
            urecords.row_id,
            urecords.user_id,
            urecords.shop_token,
            urecords.shop_category,
            urecords.shop_action,
            urecords.shop_product,
            urecords.shop_quantity
            FROM mmrpg_users_shops_records AS urecords
                LEFT JOIN mmrpg_index_shops AS ishops ON ishops.shop_token = urecords.shop_token
            WHERE
                urecords.user_id = {$user_id}
                {$raw_where}
            ORDER BY
                ishops.shop_order ASC,
                urecords.shop_action ASC,
                urecords.shop_category ASC
            ;";

        // Attempt to collect shop records from the database with the above query
        $raw_shops_records = $db->get_array_list($raw_shop_query);

        // If the results were not empty, loop through and collect into nested array
        $user_shops_records = array();
        if (!empty($raw_shops_records)){
            foreach ($raw_shops_records AS $record_key => $raw_record_info){

                // Collect the shop token for this record
                $shop_token = $raw_record_info['shop_token'];

                // Define the record index key for appending the product
                $shop_category_action_key = $raw_record_info['shop_category'].'_'.$raw_record_info['shop_action'];

                // Collect the actual product token and record quantity
                $shop_product = $raw_record_info['shop_token'];
                $shop_quantity = $raw_record_info['shop_quantity'];

                // Create nested arrays that do not exist yet before adding
                if (!isset($user_shops_records[$shop_token])){ $user_shops_records[$shop_token] = array(); }
                if (!isset($user_shops_records[$shop_token][$shop_category_action_key])){ $user_shops_records[$shop_token][$shop_category_action_key] = array(); }

                // Add this shop info to the global shop index
                $user_shops_records[$shop_token][$shop_category_action_key][$shop_product] = $shop_quantity;

            }
        }

        // Return either the whole shop array or a part of it based on the request
        if (!empty($return_shop) && !empty($user_shops_records[$return_shop])){ return $user_shops_records[$return_shop]; }
        elseif (!empty($return_shop) && empty($user_shops_records[$return_shop])){ return array(); }
        else { return $user_shops_records; }

    }


    /**
     * Pull an array of shops stats with records for a given user ID from the database
     * @param int $user_id
     * @param string $return_shop (optional)
     * @return array
     */
    public static function get_battle_shops($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Collect the basic shop details for this user ID
        $user_shops = self::get_shops($user_id);
        if (empty($user_shops)){ return array(); }

        // Collect any shop sold/bought records for this user ID
        $users_shops_records = self::get_shop_records($user_id);
        if (empty($users_shops_records)){ return $user_shops; }

        // Loop through shops and records then merge into single array
        $user_battle_shops = array();
        foreach ($user_shops AS $shop_token => $shop_info){
            $user_battle_shops[$shop_token] = $shop_info;
        }
        foreach ($users_shops_records AS $shop_token => $shop_records){
            foreach ($shop_records AS $list_key => $list_records){
                $user_battle_shops[$shop_token][$list_key] = $list_records;
            }
        }

        // Return the consolodated battle shop array
        return $user_battle_shops;

    }


    /**
     * Pull an array of player omega factors for a given user ID from the database
     * @param int $user_id
     * @param string $return_player (optional)
     * @return array
     */
    public static function get_player_omega($user_id, $return_player = ''){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Compensate for empty or missing player token
        $allowed_players = rpg_player::get_index_tokens(true, false);
        if (!empty($return_player) && !in_array($return_player, $allowed_players)){ $return_player = ''; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Define the query filter for this request
        $raw_where = '';
        if (!empty($return_player)){ $raw_where .= "AND omega.player_token = '{$return_player}' "; }
        else { $raw_where .= "AND omega.player_token IN ('".implode("','", $allowed_players)."') "; }

        // Define the base query for pulling omega factors
        $raw_omega_query = "SELECT
            omega.row_id,
            omega.user_id,
            omega.player_token,
            omega.field_token,
            omega.robot_token,
            omega.type_token,
            omega.slot_key
            FROM mmrpg_users_players_omega AS omega
                LEFT JOIN mmrpg_index_players AS iplayers ON iplayers.player_token = omega.player_token
            WHERE
                omega.user_id = {$user_id}
                {$raw_where}
            ORDER BY
                iplayers.player_order ASC,
                omega.slot_key ASC
            ;";

        // Attempt to collect omega factors from the database with the above query
        $raw_player_omega = $db->get_array_list($raw_omega_query);

        // If the results were not empty, loop through and collect into nested array
        $user_player_omega = array();
        if (!empty($raw_player_omega)){
            foreach ($raw_player_omega AS $omega_key => $raw_omega_info){

                // Collect the player token for this omega factor
                $player_token = $raw_omega_info['player_token'];

                // Create an entry for this player if one does not exist yet
                if (!isset($user_player_omega[$player_token])){ $user_player_omega[$player_token] = array(); }

                // Generate the game-compatible array for this player's omega factor
                $omega_factor = array();
                $omega_factor['robot'] = $raw_omega_info['robot_token'];
                $omega_factor['field'] = $raw_omega_info['field_token'];
                $omega_factor['type'] = $raw_omega_info['type_token'];

                // Append this omega factor to the current player's global list array
                $user_player_omega[$player_token][] = $omega_factor;

            }
        }

        // Return either the whole omega array or a part of it based on the request
        if (!empty($return_player) && !empty($user_player_omega[$return_player])){ return $user_player_omega[$return_player]; }
        elseif (!empty($return_player) && empty($user_player_omega[$return_player])){ return array(); }
        else { return $user_player_omega; }

    }


    /**
     * Pull an array of target robot omega factors for a given user ID from the database
     * @param int $user_id
     * @return array
     */
    public static function get_target_robot_omega($user_id){

        // Collect player omega factors normally before reformatting
        $user_player_omega = self::get_player_omega($user_id);
        if (empty($user_player_omega)){ return array(); }

        // Loop through and create game-compatible target-robot versions for each list
        $user_target_robot_omega = array();
        foreach ($user_player_omega AS $player_token => $omega_factors){
            $target_robot__omega_key = $player_token.'_target-robot-omega_prototype';
            $user_target_robot_omega[$target_robot__omega_key] = $omega_factors;
        }

        // Return the reformatted target robot omega array
        return $user_target_robot_omega;

    }


    /**
     * Update target robot omega factors for a given user ID and player in the database
     * @param int $user_id
     * @param string $player_token
     * @param array $new_target_robot_omega
     * @return boolean
     */
    public static function set_target_robot_omega($user_id, $player_token, $new_target_robot_omega){

        // Return false on missing or invalid user ID, player token, or factors
        if (empty($user_id) || !is_numeric($user_id)){ return false; }
        else if (empty($player_token) || !is_string($player_token)){ return false; }
        else if (empty($new_target_robot_omega) || !is_array($new_target_robot_omega)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Delete existing omega factors for this player before inserting
        $db->delete('mmrpg_users_players_omega', array('user_id' => $user_id, 'player_token' => $player_token));

        // Generate a query inserting all these field factors into the database at once and execute
        $raw_insert_values = array();
        foreach ($new_target_robot_omega AS $slot_key => $omega_info){
            $insert = array();
            $insert[] = $user_id;
            $insert[] = "'".$player_token."'";
            $insert[] = "'".$omega_info['field']."'";
            $insert[] = "'".$omega_info['robot']."'";
            $insert[] = "'".$omega_info['type']."'";
            $insert[] = $slot_key;
            $raw_insert_values[] = '('.implode(', ', $insert).')';
        }
        $raw_insert_values = implode(",\n", $raw_insert_values);
        $success = $db->query("INSERT INTO mmrpg_users_players_omega
            (user_id, player_token, field_token, robot_token, type_token, slot_key)
            VALUES
            {$raw_insert_values}
            ;");

        // Return true on success
        return $success;

    }


    /**
     * Update a given user's save time in the database
     * @param int $user_id
     * @return boolean
     */
    public static function update_user_save_times($user_id){

        // Return false on missing or invalid user ID
        if (empty($user_id) || !is_numeric($user_id)){ return false; }

        // Get the global database object for querying
        $db = cms_database::get_database();

        // Update the access and modified times for this user and their save data
        $now_time = time();
        $db->update('mmrpg_saves', array('save_date_accessed' => $now_time, 'save_date_modified' => $now_time), array('user_id' => $user_id));
        $db->update('mmrpg_users', array('user_date_accessed' => $now_time, 'user_date_modified' => $now_time), array('user_id' => $user_id));

        // Return true on success
        return true;

    }


}
?>