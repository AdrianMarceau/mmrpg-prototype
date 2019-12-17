<?php
/**
 * Mega Man RPG Rogue Star
 * <p>The object class for all stars in the Mega Man RPG World.</p>
 */
class rpg_rogue_star {

    /**
     * Create a new RPG star object
     * @param array $star_info (optional)
     * @return rpg_rogue_star
     */
    public function __construct($star_info = array()){

        // Return true on success
        return true;

    }


    // -- STAR INDEX FUNCTIONS -- //

    /**
     * Get a list of all star fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for star objects
        $star_fields = array(
            'star_id',
            'star_type',
            'star_from_date',
            'star_from_date_time',
            'star_to_date',
            'star_to_date_time',
            'star_power',
            'star_flag_enabled'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($star_fields AS $key => $field){
                $star_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $star_fields = implode(', ', $star_fields);
        }

        // Return the table fields, array or string
        return $star_fields;

    }

    // Define an alias function name for the above
    public static function get_fields($implode = false, $table = ''){
        return self::get_index_fields($implode, $table);
    }

    /**
     * Get the entire star index as an array with parsed info
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index($include_hidden = false, $include_disabled = false, $index_field = 'star_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        //if (!$include_hidden){ ... }
        if (!$include_disabled){ $temp_where .= 'AND star_flag_enabled = 1 '; }

        // Collect every star's info from the database index
        $star_fields = self::get_fields(true);
        $star_index = $db->get_array_list("SELECT {$star_fields} FROM mmrpg_rogue_stars WHERE star_id <> 0 {$temp_where};", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($star_index)){
            $star_index = self::parse_index($star_index);
            return $star_index;
        } else {
            return array();
        }

    }

    /**
     * Get the a custom set stars from the index as an array with parsed info
     * @param array $star_list
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index_custom($star_list, $index_field = 'star_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the where string for the query and populate
        $where_string = array();
        foreach ($star_list AS $lookup){
            // If this is numeric, lookup by Star ID
            if (is_numeric($lookup)){ $where_string[] = "star_id = {$lookup}"; }
            // Otherwise if string, lookup by Star Token
            elseif (is_string($lookup)){ $where_string[] = "star_token = '{$lookup}'"; }
        }
        // Implode the lookup string with ORs in between
        $where_string = implode(' OR ', $where_string);

        // Collect the requested star's info from the database index
        $star_fields = self::get_fields(true);
        $star_index = $db->get_array_list("SELECT {$star_fields} FROM mmrpg_rogue_stars WHERE star_id <> 0 AND ({$where_string});", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($star_index)){
            $star_index = self::parse_index($star_index);
            return $star_index;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs or tokens for all stars in the global index
     * @param string $index_field
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_field_values($index_field, $include_hidden = false, $include_disabled = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        //if (!$include_hidden){ ... }
        if (!$include_disabled){ $temp_where .= 'AND star_flag_enabled = 1 '; }

        // Collect an array of star tokens from the database
        $star_index = $db->get_array_list("SELECT DISTINCT {$index_field} FROM mmrpg_rogue_stars WHERE star_id <> 0 {$temp_where};", $index_field);

        // Return the tokens if not empty, else nothing
        if (!empty($star_index)){
            $star_fields = array_keys($star_index);
            return $star_fields;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs for all stars in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_ids($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_field_values('star_id', $include_nologin, $include_unapproved);

    }

    /**
     * Collect the database info for a specific star by ID or token
     * @param bool $star_lookup (int or string)
     * @return array
     */
    public static function get_info($star_lookup){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this star's info from the database index
        $lookup = !is_numeric($star_lookup) ? "star_type = '{$star_lookup}'" : "star_id = {$star_lookup}";
        $star_fields = self::get_fields(true);
        $star_index = $db->get_array("SELECT {$star_fields} FROM mmrpg_rogue_stars WHERE {$lookup};");

        // Parse and return the data if not empty, else nothing
        if (!empty($star_index)){
            $star_index = self::parse_index_info($star_index);
            return $star_index;
        } else {
            return array();
        }

    }

    /**
     * Parse the fields of a star index array in bulk
     * @param array $star_index
     * @return array
     */
    public static function parse_index($star_index){

        // Loop through each entry and parse its data
        foreach ($star_index AS $token => $info){
            $star_index[$token] = self::parse_star_info($info);
        }

        // Return the parsed index
        return $star_index;

    }

    /**
     * Reformat the raw fields of a star array into proper arrays
     * @param array $star_info
     * @return array
     */
    public static function parse_info($star_info){

        // Return false if empty
        if (empty($star_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($star_info['_parsed'])){ return $star_info; }
        else { $star_info['_parsed'] = true; }

        // Return the parsed star info
        return $star_info;
    }

    /**
     * Given a star and end date, generate a date range string for a star
     * @param array $star_info
     * @return array
     */
    public static function get_date_range_string($star_from_date, $star_to_date){
        list($from_yyyy, $from_mm, $from_dd) = explode('-', $star_from_date);
        list($to_yyyy, $to_mm, $to_dd) = explode('-', $star_to_date);
        if ($star_from_date === $star_to_date){ $star_date_range = date('M jS Y', strtotime($star_from_date)); }
        elseif ($from_yyyy !== $to_yyyy){ $star_date_range = date('M jS Y', strtotime($star_from_date)).' &raquo; '.date('M jS Y', strtotime($star_to_date)); }
        else { $star_date_range = date('M jS', strtotime($star_from_date)).' &raquo; '.date('M jS Y', strtotime($star_to_date)); }
        return $star_date_range;
    }

}
?>