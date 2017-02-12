<?php
/**
 * Mega Man RPG User Role
 * <p>The object class for all user roles in the Mega Man RPG World.</p>
 */
class rpg_user_role {

    /**
     * Create a new RPG role object
     * @param array $role_info (optional)
     * @return rpg_role
     */
    public function __construct($role_info = array()){

        // Return true on success
        return true;

    }


    // -- USER INDEX FUNCTIONS -- //

    /**
     * Get a list of all role fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for role objects
        $role_fields = array(
            'role_id',
            'role_name',
            'role_token',
            'role_level',
            'role_icon',
            'role_colour'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($role_fields AS $key => $field){
                $role_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $role_fields = implode(', ', $role_fields);
        }

        // Return the table fields, array or string
        return $role_fields;

    }

    // Define an alias function name for the above
    public static function get_fields($implode = false, $table = ''){
        return self::get_index_fields($implode, $table);
    }


    /**
     * Get the entire user role index as an array with parsed info
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index($index_field = 'role_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect every role's info from the database index
        $role_fields = self::get_fields(true);
        $role_index = $db->get_array_list("SELECT {$role_fields} FROM mmrpg_roles WHERE role_id <> 0;", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($role_index)){
            $role_index = self::parse_index($role_index);
            return $role_index;
        } else {
            return array();
        }

    }

    /**
     * Get the a custom set user roles from the index as an array with parsed info
     * @param array $role_list
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index_custom($role_list, $index_field = 'role_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the where string for the query and populate
        $where_string = array();
        foreach ($role_list AS $lookup){
            // If this is numeric, lookup by Role ID
            if (is_numeric($lookup)){ $where_string[] = "role_id = {$lookup}"; }
            // Otherwise if string, lookup by Role Token
            elseif (is_string($lookup)){ $where_string[] = "role_token = '{$lookup}'"; }
        }
        // Implode the lookup string with ORs in between
        $where_string = implode(' OR ', $where_string);

        // Collect the requested role's info from the database index
        $role_fields = self::get_fields(true);
        $role_index = $db->get_array_list("SELECT {$role_fields} FROM mmrpg_roles WHERE role_id <> 0 AND ({$where_string});", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($role_index)){
            $role_index = self::parse_index($role_index);
            return $role_index;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs or tokens for all roles in the global index
     * @param string $index_field
     * @return array
     */
    public static function get_field_values($index_field){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect an array of role tokens from the database
        $role_index = $db->get_array_list("SELECT DISTINCT {$index_field} FROM mmrpg_roles WHERE role_id <> 0;", $index_field);

        // Return the tokens if not empty, else nothing
        if (!empty($role_index)){
            $role_fields = array_keys($role_index);
            return $role_fields;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs for all roles in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_ids($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_role_field_values('role_id', $include_nologin, $include_unapproved);

    }

    /**
     * Get the tokens for all roles in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_tokens($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_role_field_values('role_token', $include_nologin, $include_unapproved);

    }

    /**
     * Collect the database info for a specific role by ID or token
     * @param bool $role_lookup (int or string)
     * @return array
     */
    public static function get_info($role_lookup){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this role's info from the database index
        $lookup = !is_numeric($role_lookup) ? "role_name_clean = '{$role_lookup}'" : "role_id = {$role_lookup}";
        $role_fields = self::get_index_fields(true);
        $role_index = $db->get_array("SELECT {$role_fields} FROM mmrpg_roles WHERE {$lookup};");

        // Parse and return the data if not empty, else nothing
        if (!empty($role_index)){
            $role_index = self::parse_index_info($role_index);
            return $role_index;
        } else {
            return array();
        }

    }

    /**
     * Parse the fields of a role index array in bulk
     * @param array $role_index
     * @return array
     */
    public static function parse_index($role_index){

        // Loop through each entry and parse its data
        foreach ($role_index AS $token => $info){
            $role_index[$token] = self::parse_info($info);
        }

        // Return the parsed index
        return $role_index;

    }

    /**
     * Reformat the raw fields of a role array into proper arrays
     * @param array $role_info
     * @return array
     */
    public static function parse_info($role_info){

        // Return false if empty
        if (empty($role_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($role_info['_parsed'])){ return $role_info; }
        else { $role_info['_parsed'] = true; }

        // Return the parsed role info
        return $role_info;
    }


}
?>