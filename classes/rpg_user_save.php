<?php
/**
 * Mega Man RPG User Save
 * <p>The object class for all user saves in the Mega Man RPG World.</p>
 */
class rpg_user_save {

    /**
     * Create a new RPG save object
     * @param array $save_info (optional)
     * @return rpg_save
     */
    public function __construct($save_info = array()){

        // Return true on success
        return true;

    }


    // -- USER SAVE INDEX FUNCTIONS -- //

    /**
     * Get a list of all save fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for save objects
        $index_fields = array(
            'save_id',
            'user_id',
            'save_flags',
            'save_counters',
            'save_values',
            'save_settings',
            'save_cache_date',
            'save_date_created',
            'save_date_accessed',
            'save_date_modified'
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

    /**
     * Get a list of all save fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_legacy_index_fields($implode = false, $table = ''){

        // Define the various table fields for save objects
        $index_fields = array(
            'save_id',
            'user_id',
            'save_values_battle_complete',
            'save_values_battle_failure',
            'save_file_name',
            'save_file_path',
            'save_patches_applied'
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

    // Define an alias function name for the above
    public static function get_legacy_fields($implode = false, $table = ''){
        return self::get_legacy_index_fields($implode, $table);
    }


    /**
     * Get the entire user save index as an array with parsed info
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index($index_field = 'save_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect every save's info from the database index
        $save_fields = self::get_fields(true);
        $save_index = $db->get_array_list("SELECT {$save_fields} FROM mmrpg_saves WHERE save_id <> 0;", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($save_index)){
            $save_index = self::parse_index($save_index);
            return $save_index;
        } else {
            return array();
        }

    }

    /**
     * Get the a custom set user saves from the index as an array with parsed info
     * @param array $save_list
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index_custom($save_list, $index_field = 'save_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the where string for the query and populate
        $where_string = array();
        foreach ($save_list AS $lookup){
            // If this is numeric, lookup by Save ID
            if (is_numeric($lookup)){ $where_string[] = "save_id = {$lookup}"; }
            // Otherwise if string, lookup by Save Token
            elseif (is_string($lookup)){ $where_string[] = "save_token = '{$lookup}'"; }
        }
        // Implode the lookup string with ORs in between
        $where_string = implode(' OR ', $where_string);

        // Collect the requested save's info from the database index
        $save_fields = self::get_fields(true);
        $save_index = $db->get_array_list("SELECT {$save_fields} FROM mmrpg_saves WHERE save_id <> 0 AND ({$where_string});", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($save_index)){
            $save_index = self::parse_index($save_index);
            return $save_index;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs or tokens for all saves in the global index
     * @param string $index_field
     * @return array
     */
    public static function get_field_values($index_field){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect an array of save tokens from the database
        $save_index = $db->get_array_list("SELECT DISTINCT {$index_field} FROM mmrpg_saves WHERE save_id <> 0;", $index_field);

        // Return the tokens if not empty, else nothing
        if (!empty($save_index)){
            $save_fields = array_keys($save_index);
            return $save_fields;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs for all saves in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_ids($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_save_field_values('save_id', $include_nologin, $include_unapproved);

    }

    /**
     * Get the tokens for all saves in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_tokens($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_save_field_values('save_token', $include_nologin, $include_unapproved);

    }

    /**
     * Collect the database info for a specific save by ID or token
     * @param bool $save_lookup (int or string)
     * @return array
     */
    public static function get_info($save_lookup){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this save's info from the database index
        $lookup = !is_numeric($save_lookup) ? "save_name_clean = '{$save_lookup}'" : "save_id = {$save_lookup}";
        $save_fields = self::get_index_fields(true);
        $save_index = $db->get_array("SELECT {$save_fields} FROM mmrpg_saves WHERE {$lookup};");

        // Parse and return the data if not empty, else nothing
        if (!empty($save_index)){
            $save_index = self::parse_index_info($save_index);
            return $save_index;
        } else {
            return array();
        }

    }

    /**
     * Parse the fields of a save index array in bulk
     * @param array $save_index
     * @return array
     */
    public static function parse_index($save_index){

        // Loop through each entry and parse its data
        foreach ($save_index AS $token => $info){
            $save_index[$token] = self::parse_info($info);
        }

        // Return the parsed index
        return $save_index;

    }

    /**
     * Reformat the raw fields of a save array into proper arrays
     * @param array $save_info
     * @return array
     */
    public static function parse_info($save_info){

        // Return false if empty
        if (empty($save_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($save_info['_parsed'])){ return $save_info; }
        else { $save_info['_parsed'] = true; }

        // Return the parsed save info
        return $save_info;
    }


}
?>