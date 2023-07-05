<?php
/**
 * Mega Man RPG Music Track
 * <p>The object class for all music in the Mega Man RPG World.</p>
 */
class rpg_music_track {

    /**
     * Create a new RPG music object
     * @param array $music_info (optional)
     * @return rpg_music_track
     */
    public function __construct($music_info = array()){

        // Return true on success
        return true;

    }


    // -- MUSIC INDEX FUNCTIONS -- //

    /**
     * Get a list of all music fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for music objects
        $music_fields = array(
            'music_id',
            'music_token',
            'music_album',
            'music_game',
            'music_name',
            'music_link',
            'music_loop',
            'music_order',
            'legacy_music_token',
            'legacy_music_album',
            'legacy_music_game'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($music_fields AS $key => $field){
                $music_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $music_fields = implode(', ', $music_fields);
        }

        // Return the table fields, array or string
        return $music_fields;

    }

    /**
     * Get a list of all JSON-based music index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @return mixed
     */
    public static function get_json_index_fields($implode = false){

        // Define the various json index fields for player objects
        $json_index_fields = array(
            'music_loop'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $json_index_fields = implode(', ', $json_index_fields);
        }

        // Return the index fields, array or string
        return $json_index_fields;

    }

    // Define an alias function name for the above
    public static function get_fields($implode = false, $table = ''){
        return self::get_index_fields($implode, $table);
    }

    /**
     * Get the entire music index as an array with parsed info
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index($include_hidden = false, $include_disabled = false, $index_field = 'music_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        //if (!$include_hidden){ ... }
        //if (!$include_disabled){ $temp_where .= 'AND music_flag_enabled = 1 '; }

        // Collect every music's info from the database index
        $music_dbname = (method_exists(__CLASS__, 'get_dbname') ? self::get_dbname() : MMRPG_CONFIG_CDN_DBNAME);
        $music_fields = self::get_fields(true, 'music');
        $music_query = "SELECT
            {$music_fields}
            FROM {$music_dbname}.`mmrpg_index_music` AS `music`
            LEFT JOIN {$music_dbname}.`mmrpg_index_sources` AS `sources` ON `music`.`music_game` = `sources`.`source_token`
            WHERE `music_id` <> 0 {$temp_where}
            ORDER BY
            `sources`.`source_order` ASC,
            `music`.`music_order` ASC
            ;";
        $music_index = $db->get_array_list($music_query, $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($music_index)){
            $music_index = self::parse_index($music_index);
            return $music_index;
        } else {
            return array();
        }

    }

    /**
     * Get the a custom set music from the index as an array with parsed info
     * @param array $music_list
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index_custom($music_list, $index_field = 'music_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the where string for the query and populate
        $where_string = array();
        foreach ($music_list AS $lookup){
            // If this is numeric, lookup by Music ID
            if (is_numeric($lookup)){ $where_string[] = "music_id = {$lookup}"; }
            // Otherwise if string, lookup by Music Token
            elseif (is_string($lookup)){ $where_string[] = "music_token = '{$lookup}'"; }
        }
        // Implode the lookup string with ORs in between
        $where_string = implode(' OR ', $where_string);

        // Collect the requested music's info from the database index
        $music_dbname = (method_exists(__CLASS__, 'get_dbname') ? self::get_dbname() : MMRPG_CONFIG_CDN_DBNAME);
        $music_fields = self::get_fields(true);
        $music_index = $db->get_array_list("SELECT {$music_fields} FROM {$music_dbname}.mmrpg_index_music WHERE music_id <> 0 AND ({$where_string});", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($music_index)){
            $music_index = self::parse_index($music_index);
            return $music_index;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs or tokens for all music in the global index
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
        //if (!$include_disabled){ $temp_where .= 'AND music_flag_enabled = 1 '; }

        // Collect an array of music tokens from the database
        $music_dbname = (method_exists(__CLASS__, 'get_dbname') ? self::get_dbname() : MMRPG_CONFIG_CDN_DBNAME);
        $music_index = $db->get_array_list("SELECT DISTINCT {$index_field} FROM {$music_dbname}.mmrpg_index_music WHERE music_id <> 0 {$temp_where};", $index_field);

        // Return the tokens if not empty, else nothing
        if (!empty($music_index)){
            $music_fields = array_keys($music_index);
            return $music_fields;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs for all music in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_ids($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_field_values('music_id', $include_nologin, $include_unapproved);

    }

    /**
     * Collect the database info for a specific music by ID or token
     * @param bool $music_lookup (int or string)
     * @return array
     */
    public static function get_info($music_lookup){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this music's info from the database index
        $lookup = !is_numeric($music_lookup) ? "music_type = '{$music_lookup}'" : "music_id = {$music_lookup}";
        $music_dbname = (method_exists(__CLASS__, 'get_dbname') ? self::get_dbname() : MMRPG_CONFIG_CDN_DBNAME);
        $music_fields = self::get_fields(true);
        $music_index = $db->get_array("SELECT {$music_fields} FROM {$music_dbname}.mmrpg_index_music WHERE {$lookup};");

        // Parse and return the data if not empty, else nothing
        if (!empty($music_index)){
            $music_index = self::parse_index_info($music_index);
            return $music_index;
        } else {
            return array();
        }

    }

    /**
     * Parse the fields of a music index array in bulk
     * @param array $music_index
     * @return array
     */
    public static function parse_index($music_index){

        // Loop through each entry and parse its data
        foreach ($music_index AS $token => $info){
            $music_index[$token] = self::parse_info($info);
        }

        // Return the parsed index
        return $music_index;

    }

    /**
     * Reformat the raw fields of a music array into proper arrays
     * @param array $music_info
     * @return array
     */
    public static function parse_info($music_info){

        // Return false if empty
        if (empty($music_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($music_info['_parsed'])){ return $music_info; }
        else { $music_info['_parsed'] = true; }

        // Explode the weaknesses, resistances, affinities, and immunities into an array
        $temp_field_names = self::get_json_index_fields();
        foreach ($temp_field_names AS $field_name){
            if (!empty($music_info[$field_name])){ $music_info[$field_name] = json_decode($music_info[$field_name], true); }
            else { $music_info[$field_name] = array(); }
        }

        // Return the parsed music info
        return $music_info;
    }



    // -- MISC HELPER FUNCTIONS -- //


    /* ... */


}
?>