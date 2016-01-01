<?php
// Define a class for the type objects
class rpg_type {

    // Define the internal database cache
    public static $database_index = array();

    // Define the constructor class
    public function __construct(){ }


    // -- INDEX FUNCTIONS -- //

    /**
     * Get a list of all type index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @return mixed
     */
    public static function get_index_fields($implode = false){

        // Define the various index fields for type objects
        $index_fields = array(
            'type_id',
            'type_token',
            'type_name',
            'type_class',
            'type_colour_dark',
            'type_colour_light',
            'type_order'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $index_fields = implode(', ', $index_fields);
        }

        // Return the index fields, array or string
        return $index_fields;

    }

    /**
     * Get the entire type index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $this_database = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND type_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND type_flag_published = 1 '; }

        // Collect every type's info from the database index
        $type_fields = self::get_index_fields(true);
        $type_index = $this_database->get_array_list("SELECT {$type_fields} FROM mmrpg_index_types WHERE type_id <> 0 {$temp_where};", 'type_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($type_index)){
            $type_index = self::parse_index($type_index);
            return $type_index;
        } else {
            return array();
        }

    }

    /**
     * Get the tokens for all types in the global index
     * @return array
     */
    public static function get_index_tokens($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $this_database = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND type_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND type_flag_published = 1 '; }

        // Collect an array of type tokens from the database
        $type_index = $this_database->get_array_list("SELECT type_token FROM mmrpg_index_types WHERE type_id <> 0 {$temp_where};", 'type_token');

        // Return the tokens if not empty, else nothing
        if (!empty($type_index)){
            $type_tokens = array_keys($type_index);
            return $type_tokens;
        } else {
            return array();
        }

    }

    // Define a function for pulling a custom type index
    public static function get_index_custom($type_tokens = array()){

        // Pull in global variables
        $this_database = cms_database::get_database();

        // Generate a token string for the database query
        $type_tokens_string = array();
        foreach ($type_tokens AS $type_token){ $type_tokens_string[] = "'{$type_token}'"; }
        $type_tokens_string = implode(', ', $type_tokens_string);

        // Collect the requested type's info from the database index
        $type_fields = self::get_index_fields(true);
        $type_index = $this_database->get_array_list("SELECT {$type_fields} FROM mmrpg_index_types WHERE type_token IN ({$type_tokens_string});", 'type_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($type_index)){
            $type_index = self::parse_index($type_index);
            return $type_index;
        } else {
            return array();
        }

    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($type_token){

        // Pull in global variables
        $this_database = cms_database::get_database();

        // Collect this type's info from the database index
        $type_fields = self::get_index_fields(true);
        $type_index = $this_database->get_array_list("SELECT {$type_fields} FROM mmrpg_index_types WHERE type_token = '{$type_token}';", 'type_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($type_index)){
            $type_index = self::parse_index_info($type_index);
            return $type_index;
        } else {
            return array();
        }

    }

    // Define a public function for parsing a type index array in bulk
    public static function parse_index($type_index){

        // Loop through each entry and parse its data
        foreach ($type_index AS $token => $info){
            $type_index[$token] = self::parse_index_info($info);
        }

        // Return the parsed index
        return $type_index;

    }

    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($type_info){

        // Return false if empty
        if (empty($type_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($type_info['_parsed'])){ return $type_info; }
        else { $type_info['_parsed'] = true; }

        // Explode json encoded fields into expanded array objects
        $temp_fields = array('type_colour_dark', 'type_colour_light');
        foreach ($temp_fields AS $field_name){
            if (!empty($type_info[$field_name])){ $type_info[$field_name] = json_decode($type_info[$field_name], true); }
            else { $type_info[$field_name] = array(); }
        }

        // Return the parsed type info
        return $type_info;
    }

}
?>