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
        $user_fields = array(
            'user_id',
            'role_id',
            'contributor_id',
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
            'user_colour_token2',
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

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($user_fields AS $key => $field){
                $user_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $user_fields = implode(', ', $user_fields);
        }

        // Return the table fields, array or string
        return $user_fields;

    }

    // Define an alias function name for the above
    public static function get_fields($implode = false, $table = ''){
        return self::get_index_fields($implode, $table);
    }

    /**
     * Get a list of all user save fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_save_index_fields($implode = false, $table = ''){

        // Define the various table fields for user objects
        $save_fields = array(
            'save_id',
            'user_id',
            'save_counters',
            'save_values',
            'save_values_battle_index',
            'save_values_battle_complete',
            'save_values_battle_failure',
            'save_values_battle_rewards',
            'save_values_battle_settings',
            'save_values_battle_items',
            'save_values_battle_abilities',
            'save_values_battle_stars',
            'save_values_robot_database',
            'save_values_robot_alts',
            'save_flags',
            'save_settings',
            'save_cache_date',
            'save_file_name',
            'save_file_path',
            'save_date_created',
            'save_date_accessed',
            'save_date_modified',
            'save_patches_applied'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($save_fields AS $key => $field){
                $save_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $save_fields = implode(', ', $save_fields);
        }

        // Return the table fields, array or string
        return $save_fields;

    }

    /**
     * Get a list of all user contributor fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_contributor_index_fields($implode = false, $table = ''){

        // Define the various table fields for user objects
        $contributor_fields = array(
            'contributor_id',
            'role_id',
            'user_name',
            'user_name_clean',
            'user_name_public',
            'user_gender',
            'user_colour_token',
            'user_colour_token2',
            'user_image_path',
            'user_background_path',
            'user_credit_line',
            'user_credit_text',
            'user_website_address',
            'user_date_created',
            'user_date_modified',
            'contributor_flag_showcredits'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($contributor_fields AS $key => $field){
                $contributor_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $contributor_fields = implode(', ', $contributor_fields);
        }

        // Return the table fields, array or string
        return $contributor_fields;

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
            || empty($_SESSION['GAME']['USER']['username'])
            || $_SESSION['GAME']['USER']['userid'] == MMRPG_SETTINGS_GUEST_ID
            || $_SESSION['GAME']['USER']['username'] == 'guest'){
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
            && !empty($_SESSION['GAME']['USER']['username'])
            && $_SESSION['GAME']['USER']['userid'] != MMRPG_SETTINGS_GUEST_ID
            && $_SESSION['GAME']['USER']['username'] != 'guest'){
            return true;
        } else {
            return false;
        }

    }

    /**
     * Define a function for getting the current USER ID, returning 0 if not logged in
     * @return bool
     */
    public static function get_current_userid(){

        // Check if there is a logged in session user
        if (!empty($_SESSION['GAME']['USER']['userid'])
            && !rpg_user::is_guest()){
            return $_SESSION['GAME']['USER']['userid'];
        } elseif (!empty($_SESSION['admin_id'])){
            return $_SESSION['admin_id'];
        } else {
            return false;
        }

    }

    /**
     * Define a function for getting the current USER INFO, returning empty if not exists
     * @return bool
     */
    public static function get_current_userinfo(){

        // Check if there is a logged in session user
        if (!empty($_SESSION['GAME']['USER']['userinfo'])){
            return $_SESSION['GAME']['USER']['userinfo'];
        } else {
            return array();
        }

    }


    // -- USER PERMISSIONS FUNCTIONS -- //

    // Define a function for building a permissions table given current table structure
    public static function get_permissions_table(){
        global $db;
        static $permissions_table;
        if (empty($permissions_table)){
            $db_name = MMRPG_CONFIG_DBNAME;
            $tbl_name = 'mmrpg_users_permissions';
            $table_columns = $db->get_array_list("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = '{$db_name}' AND TABLE_NAME = '{$tbl_name}'
                ;", 'COLUMN_NAME');
            if (isset($table_columns['user_id'])){ unset($table_columns['user_id']); }
            $table_columns = !empty($table_columns) ? array_keys($table_columns) : array();
            if (empty($table_columns)){ return false; }
            $permissions_table = array();
            foreach ($table_columns AS $column_name){
                $temp_table = array();
                $parts = explode('_', preg_replace('/^allow_/i', '', $column_name));
                while ($bottom = array_pop($parts)){ $temp_table = array($bottom => $temp_table); }
                $permissions_table = array_merge_recursive($permissions_table, $temp_table);
            }
        }
        return $permissions_table;
    }

    // Define a function for getting all permission tokens in a given a permissions table
    public static function get_permission_tokens_from_table($permissions_table, &$permissions_tokens = array()){
        foreach ($permissions_table AS $key => $sub_permissions_table){
            if (!in_array($key, $permissions_tokens)){ $permissions_tokens[] = $key; }
            if (!empty($sub_permissions_table)){ self::get_permission_tokens_from_table($sub_permissions_table, $permissions_tokens); }
        }
        return $permissions_tokens;
    }

    // Define a function for getting inherited child permission tokens given a list of (possible) parents
    public static function inherit_permission_tokens_from_table(&$user_permission_tokens, $permissions_table = array()){
        if (empty($permissions_table)){ return; }
        foreach ($permissions_table AS $parent_token => $child_permissions_table){
            if (empty($child_permissions_table)){ continue; }
            if (in_array($parent_token, $user_permission_tokens)){
                $child_permission_tokens = array_keys($child_permissions_table);
                $user_permission_tokens = array_merge($user_permission_tokens, $child_permission_tokens);
            }
            self::inherit_permission_tokens_from_table($user_permission_tokens, $child_permissions_table);
        }
    }

    // Define a function for getting the permissions of a specific user
    private static $default_guest_permissions = array();
    private static $default_member_permissions = array();
    public static function get_user_permission_tokens($user_id, $refresh = false){
        global $db;
        static $user_permissions_index = array();
        // Check to see if we need to (re)generate now of if there's already a cached version
        if (empty($user_permissions_index[$user_id]) || $refresh){
            // Pull raw permission if available, else assign default based on member vs guest
            $raw_user_permissions = $db->get_array("SELECT * FROM mmrpg_users_permissions WHERE user_id = {$user_id};");
            //error_log('$raw_user_permissions('.$user_id.') = '.print_r($raw_user_permissions, true));
            if (!empty($raw_user_permissions)){
                unset($raw_user_permissions['user_id']);
                foreach ($raw_user_permissions AS $key => $value){ if (empty($value)){ unset($raw_user_permissions[$key]); } }
            } else {
                if ($user_id === MMRPG_SETTINGS_GUEST_ID){ $raw_user_permissions = self::$default_guest_permissions; }
                else { $raw_user_permissions = self::$default_member_permissions; }
            }
            //error_log('$raw_user_permissions('.$user_id.') = '.print_r($raw_user_permissions, true));
            // Loop through raw permissions and collect allowed tokens from the end of each string
            $user_permission_tokens = array();
            $raw_permission_tokens = array_keys($raw_user_permissions);
            foreach ($raw_permission_tokens AS $raw_token){
                $token_frags = explode('_', $raw_token);
                $user_permission_tokens[] = array_pop($token_frags);
            }
            //error_log('$user_permission_tokens('.$user_id.')(base) = '.print_r($user_permission_tokens, true));
            // Auto-inherit permissions of any child tokens based on the collected parents above
            $base_permissions_table = self::get_permissions_table();
            self::inherit_permission_tokens_from_table($user_permission_tokens, $base_permissions_table);
            //error_log('$user_permission_tokens('.$user_id.')(+inherited) = '.print_r($user_permission_tokens, true));
            // Assign the finalized permissions to the static index
            $user_permissions_index[$user_id] = $user_permission_tokens;
        }
        // Return the generated/cached permissions table for this user
        return $user_permissions_index[$user_id];
    }

    // Define a function for checking if the logged-in user has permissions to do something
    public static function current_user_permission_tokens(){
        $user_id = self::get_current_userid();
        if (empty($user_id)){ $user_id = MMRPG_SETTINGS_GUEST_ID; }
        return self::get_user_permission_tokens($user_id);
    }

    // Define a function for checking if a given user has permissions to do something
    public static function user_has_permission($user_id, $permission_token){
        $user_permission_tokens = self::get_user_permission_tokens($user_id);
        if ($permission_token === '*'){ $permission_token = 'all'; }
        if (in_array('all', $user_permission_tokens)){ return true; }
        elseif (in_array($permission_token, $user_permission_tokens)){ return true; }
        else { return false; }
    }

    // Define a function for checking if the logged-in user has permissions to do something
    public static function current_user_has_permission($permission_token){
        $user_id = self::get_current_userid();
        if (empty($user_id)){ $user_id = MMRPG_SETTINGS_GUEST_ID; }
        return self::user_has_permission($user_id, $permission_token);
    }

    // Define a function for checking if a given user has all the permissions listed in a a given array
    public static function user_has_all_permissions($user_id, $permission_tokens){
        if (is_string($permission_tokens)){ $permission_tokens = explode(',', $permission_tokens); }
        $user_permission_tokens = self::get_user_permission_tokens($user_id);
        if (in_array('all', $user_permission_tokens)){ return true; }
        else { foreach ($permission_tokens AS $permission_token){
            if ($permission_token === '*'){ $permission_token = 'all'; }
            if (!in_array($permission_token, $user_permission_tokens)){ return false; }
        } }
        return true;
    }

    // Define a function for checking if the logged-in user has all the permissions listed in a a given array
    public static function current_user_has_all_permissions($permission_tokens){
        $user_id = self::get_current_userid();
        if (empty($user_id)){ $user_id = MMRPG_SETTINGS_GUEST_ID; }
        return self::user_has_all_permissions($user_id, $permission_tokens);
    }

    // Define a function for checking if a given user has any of the permissions listed in a a given array
    public static function user_has_any_permissions($user_id, $permission_tokens){
        if (is_string($permission_tokens)){ $permission_tokens = explode(',', $permission_tokens); }
        $user_permission_tokens = self::get_user_permission_tokens($user_id);
        if (in_array('all', $user_permission_tokens)){ return true; }
        else { foreach ($permission_tokens AS $permission_token){
            if ($permission_token === '*'){ $permission_token = 'all'; }
            if (in_array($permission_token, $user_permission_tokens)){ return true; }
        } }
        return false;
    }

    // Define a function for checking if the logged-in user has any of the permissions listed in a a given array
    public static function current_user_has_any_permissions($permission_tokens){
        $user_id = self::get_current_userid();
        if (empty($user_id)){ $user_id = MMRPG_SETTINGS_GUEST_ID; }
        return self::user_has_any_permissions($user_id, $permission_tokens);
    }



    // -- SAVE DATA FUNCTIONS -- //

    // Define a function for pulling a given user's robot records from the database
    public static function pull_robot_records($user_id, &$user_robot_records = array()){
        global $db;

        if (empty($user_id)){ return false; }
        if (!is_array($user_robot_records)){ $user_robot_records = array(); }

        // Define the list of stat fields to collect/review
        $record_fields = array(
            'robot_encountered',
            'robot_defeated',
            'robot_unlocked',
            'robot_summoned',
            'robot_scanned'
            );

        // Collect a list of existing records for this user from the database
        $record_table_name = 'mmrpg_users_records_robots';
        $record_fields_string = implode(', ', array_map(function($s){ return '`records`.`'.$s.'`'; }, $record_fields));
        $existing_robot_records = $db->get_array_list("SELECT
            `records`.`robot_token`, {$record_fields_string}
            FROM `{$record_table_name}` AS `records`
            LEFT JOIN `mmrpg_index_robots` AS `index` ON `index`.`robot_token` = `records`.`robot_token`
            WHERE `records`.`user_id` = {$user_id} AND `index`.`robot_flag_published` = 1 AND `index`.`robot_flag_complete` = 1
            ORDER BY `records`.`record_id` ASC
            ;", 'robot_token');
        if (empty($existing_robot_records)){
            $existing_robot_records = array();
        }

        // If not empty, loop through database records and update local array
        if ($existing_robot_records){
            foreach ($existing_robot_records AS $robot_token => $robot_records){
                if (!isset($user_robot_records[$robot_token])){
                    $user_robot_records[$robot_token] = $robot_records;
                } else {
                    foreach ($record_fields AS $record){
                        if (!isset($user_robot_records[$robot_token][$record])
                            || $robot_records[$record] > $user_robot_records[$robot_token][$record]){
                            $user_robot_records[$robot_token][$record] = $robot_records[$record];
                        }
                    }
                }
            }
        }

        // Return true on success
        return true;

    }

    // Define a function for updating a given user's robot records in the database
    public static function update_robot_records($user_id, $user_robot_records){
        global $db;

        if (empty($user_id)){ return false; }
        if (empty($user_robot_records)){ return false; }

        // Define the list of stat fields to collect/review
        $record_fields = array(
            'robot_encountered',
            'robot_defeated',
            'robot_unlocked',
            'robot_summoned',
            'robot_scanned'
            );

        // Collect a list of existing records so we know when to update vs insert vs skip
        $record_table_name = 'mmrpg_users_records_robots';
        $record_fields_string = implode(', ', $record_fields);
        $existing_robot_records = $db->get_array_list("SELECT
            robot_token, {$record_fields_string}
            FROM `{$record_table_name}`
            WHERE user_id = {$user_id}
            ;", 'robot_token');
        if (empty($existing_robot_records)){
            $existing_robot_records = array();
        }

        // Loop through the provided list of records and update/insert/skip
        $insert_records = array();
        $update_records = array();
        foreach ($user_robot_records AS $robot_token => $robot_records){
            if (!isset($existing_robot_records[$robot_token])){
                $insert_record = array('user_id' => $user_id, 'robot_token' => $robot_token);
                foreach ($record_fields AS $record){ $insert_record[$record] = isset($robot_records[$record]) ? $robot_records[$record] : 0; }
                $insert_records[] = $insert_record;
            } else {
                $base_record = $existing_robot_records[$robot_token];
                $update_record = array();
                foreach ($record_fields AS $record){
                    if (isset($robot_records[$record])
                        && $robot_records[$record] > $base_record[$record]){
                        $update_record[$record] = $robot_records[$record];
                    }
                }
                if (!empty($update_record)){
                    $update_records[$robot_token] = $update_record;
                }
            }
        }

        // If there are insert records, process them now
        if (!empty($insert_records)){
            $insert_query = $db->get_bulk_insert_sql($record_table_name, $insert_records);
            $db->query($insert_query);
        }

        // If there are update records, process them now
        if (!empty($update_records)){
            foreach ($update_records AS $robot_token => $robot_records){
                $db->update($record_table_name, $robot_records, array('user_id' => $user_id, 'robot_token' => $robot_token));
            }
        }

        // Return true on success
        return true;

    }

    // Define a function for pulling a given user's unlocked items from the database
    public static function pull_unlocked_items($user_id, &$user_unlocked_items = array()){
        global $db;

        if (empty($user_id)){ return false; }
        if (!is_array($user_unlocked_items)){ $user_unlocked_items = array(); }

        // Collect a list of existing records for this user from the database
        $record_table_name = 'mmrpg_users_unlocked_items';
        $existing_item_records = $db->get_array_list("SELECT
            `unlocked`.`item_token`, `unlocked`.`item_quantity`
            FROM `{$record_table_name}` AS `unlocked`
            LEFT JOIN `mmrpg_index_items` AS `index` ON `index`.`item_token` = `unlocked`.`item_token`
            WHERE `unlocked`.`user_id` = {$user_id} AND `index`.`item_flag_published` = 1 AND `index`.`item_flag_complete` = 1
            ORDER BY `unlocked`.`record_id` ASC
            ;", 'item_token');
        if (empty($existing_item_records)){
            $existing_item_records = array();
        }

        // If not empty, loop through database records and update local array
        if ($existing_item_records){
            foreach ($existing_item_records AS $item_token => $item_record){
                $item_quantity = $item_record['item_quantity'];
                $user_unlocked_items[$item_token] = $item_quantity;
            }
        }

        // Return true on success
        return true;

    }

    // Define a function for updating a given user's unlocked items in the database
    public static function update_unlocked_items($user_id, $user_unlocked_items){
        global $db;

        if (empty($user_id)){ return false; }
        if (empty($user_unlocked_items)){ return false; }

        // Collect a list of existing records so we know when to update vs insert vs skip
        $record_table_name = 'mmrpg_users_unlocked_items';
        $existing_item_records = $db->get_array_list("SELECT
            item_token, item_quantity
            FROM `{$record_table_name}`
            WHERE user_id = {$user_id}
            ;", 'item_token');
        if (empty($existing_item_records)){
            $existing_item_records = array();
        }

        // Loop through the provided list of records and update/insert/skip
        $insert_records = array();
        $update_records = array();
        foreach ($user_unlocked_items AS $item_token => $item_quantity){
            if (strstr($item_token, '-shard') && $item_quantity > MMRPG_SETTINGS_SHARDS_MAXQUANTITY){ $item_quantity = MMRPG_SETTINGS_SHARDS_MAXQUANTITY; }
            elseif ($item_quantity > MMRPG_SETTINGS_ITEMS_MAXQUANTITY){ $item_quantity = MMRPG_SETTINGS_ITEMS_MAXQUANTITY; }
            $item_quantity = intval($item_quantity);
            if (!isset($existing_item_records[$item_token])){
                $insert_record = array('user_id' => $user_id, 'item_token' => $item_token, 'item_quantity' => $item_quantity);
                $insert_records[] = $insert_record;
            } else {
                $base_record = $existing_item_records[$item_token];
                $base_quantity = intval($base_record['item_quantity']);
                $update_record = array();
                if ($base_quantity !== $item_quantity){
                    $update_record['item_quantity'] = $item_quantity;
                }
                if (!empty($update_record)){
                    $update_records[$item_token] = $update_record;
                }
            }
        }

        // If there are insert records, process them now
        if (!empty($insert_records)){
            $insert_query = $db->get_bulk_insert_sql($record_table_name, $insert_records);
            $db->query($insert_query);
        }

        // If there are update records, process them now
        if (!empty($update_records)){
            foreach ($update_records AS $item_token => $item_records){
                $db->update($record_table_name, $item_records, array('user_id' => $user_id, 'item_token' => $item_token));
            }
        }

        // Return true on success
        return true;

    }

    // Define a function for pulling a given user's unlocked abilities from the database
    public static function pull_unlocked_abilities($user_id, &$user_unlocked_abilities = array()){
        global $db;

        if (empty($user_id)){ return false; }
        if (!is_array($user_unlocked_abilities)){ $user_unlocked_abilities = array(); }

        // Collect a list of existing records for this user from the database
        $record_table_name = 'mmrpg_users_unlocked_abilities';
        $existing_ability_records = $db->get_array_list("SELECT
            `unlocked`.`ability_token`
            FROM `{$record_table_name}` AS `unlocked`
            LEFT JOIN `mmrpg_index_abilities` AS `index` ON `index`.`ability_token` = `unlocked`.`ability_token`
            WHERE `unlocked`.`user_id` = {$user_id} AND `index`.`ability_flag_published` = 1 AND `index`.`ability_flag_complete` = 1
            ORDER BY `unlocked`.`record_id` ASC
            ;", 'ability_token');
        if (empty($existing_ability_records)){
            $existing_ability_records = array();
        }

        // If not empty, loop through database records and update local array
        if ($existing_ability_records){
            foreach ($existing_ability_records AS $ability_token => $ability_record){
                if (!in_array($ability_token, $user_unlocked_abilities)){
                    $user_unlocked_abilities[] = $ability_token;
                }
            }
        }

        // Return true on success
        return true;

    }

    // Define a function for updating a given user's unlocked abilities in the database
    public static function update_unlocked_abilities($user_id, $user_unlocked_abilities){
        global $db;

        if (empty($user_id)){ return false; }
        if (empty($user_unlocked_abilities)){ return false; }

        // Collect a list of existing records so we know when to update vs insert vs skip
        $record_table_name = 'mmrpg_users_unlocked_abilities';
        $existing_ability_records = $db->get_array_list("SELECT
            ability_token
            FROM `{$record_table_name}`
            WHERE user_id = {$user_id}
            ;", 'ability_token');
        if (empty($existing_ability_records)){
            $existing_ability_records = array();
        }

        // Loop through the provided list of records and update/insert/skip
        $insert_records = array();
        foreach ($user_unlocked_abilities AS $ability_key => $ability_token){
            if (!isset($existing_ability_records[$ability_token])){
                $insert_record = array('user_id' => $user_id, 'ability_token' => $ability_token);
                $insert_records[$ability_token] = $insert_record;
            }
        }

        // If there are insert records, process them now
        if (!empty($insert_records)){
            $insert_records = array_values($insert_records);
            $insert_query = $db->get_bulk_insert_sql($record_table_name, $insert_records);
            $db->query($insert_query);
        }

        // Return true on success
        return true;

    }

    // Define a function for pulling a given user's unlocked stars from the database
    public static function pull_unlocked_stars($user_id, &$user_unlocked_stars = array()){
        global $db;

        if (empty($user_id)){ return false; }
        if (!is_array($user_unlocked_stars)){ $user_unlocked_stars = array(); }

        // Define a list of compatible fields we can save for a given star
        $record_fields = array(
            'star_name',
            'star_kind',
            'star_type',
            'star_type2',
            'star_field',
            'star_field2',
            'star_player',
            'star_date'
            );

        // Collect a list of existing records for this user from the database
        $record_table_name = 'mmrpg_users_unlocked_stars';
        $record_fields_string = implode(', ', array_map(function($s){ return '`unlocked`.`'.$s.'`'; }, $record_fields));
        $existing_star_records = $db->get_array_list("SELECT
            `unlocked`.`star_token`, {$record_fields_string}
            FROM `{$record_table_name}` AS `unlocked`
            WHERE `unlocked`.`user_id` = {$user_id}
            ORDER BY `unlocked`.`record_id` ASC
            ;", 'star_token');
        if (empty($existing_star_records)){
            $existing_star_records = array();
        }

        // If not empty, loop through database records and update local array
        if ($existing_star_records){
            foreach ($existing_star_records AS $star_token => $star_record){
                if (empty($star_record['star_date'])){ unset($star_record['star_date']); }
                $user_unlocked_stars[$star_token] = $star_record;
            }
        }

        // Return true on success
        return true;

    }

    // Define a function for updating a given user's unlocked stars in the database
    public static function update_unlocked_stars($user_id, $user_unlocked_stars){
        global $db;

        if (empty($user_id)){ return false; }
        if (empty($user_unlocked_stars)){ return false; }

        // Define a list of compatible fields we can save for a given star
        $record_fields = array(
            'star_name',
            'star_kind',
            'star_type',
            'star_type2',
            'star_field',
            'star_field2',
            'star_player',
            'star_date'
            );

        // Collect a list of existing records so we know when to update vs insert vs skip
        $record_table_name = 'mmrpg_users_unlocked_stars';
        $record_fields_string = implode(', ', $record_fields);
        $existing_star_records = $db->get_array_list("SELECT
            star_token, {$record_fields_string}
            FROM `{$record_table_name}`
            WHERE user_id = {$user_id}
            ;", 'star_token');
        if (empty($existing_star_records)){
            $existing_star_records = array();
        }

        // Loop through the provided list of records and update/insert/skip
        $insert_records = array();
        $update_records = array();
        foreach ($user_unlocked_stars AS $star_token => $star_info){
            if (!isset($existing_star_records[$star_token])){
                $insert_record = array('user_id' => $user_id, 'star_token' => $star_token);
                foreach ($record_fields AS $field){
                    if (isset($star_info[$field])){ $insert_record[$field] = $star_info[$field]; }
                    else { $insert_record[$field] = strstr($field, '_date') ? 0 : ''; }
                }
                $insert_records[] = $insert_record;
            } else {
                $base_record = $existing_star_records[$star_token];
                $update_record = array();
                if (!empty($star_info['star_date'])){
                    $update_record['star_date'] = $star_info['star_date'];
                }
                if (!empty($update_record)){
                    $update_records[$star_token] = $update_record;
                }
            }
        }

        // If there are insert records, process them now
        if (!empty($insert_records)){
            $insert_query = $db->get_bulk_insert_sql($record_table_name, $insert_records);
            $db->query($insert_query);
        }

        // If there are update records, process them now
        if (!empty($update_records)){
            foreach ($update_records AS $star_token => $star_records){
                $db->update($record_table_name, $star_records, array('user_id' => $user_id, 'star_token' => $star_token));
            }
        }

        // Return true on success
        return true;

    }


}

?>
