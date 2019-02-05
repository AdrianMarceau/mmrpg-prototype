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


}
?>