<?php
// Define a class for the shop objects
class rpg_shop {

    // Define the internal database cache
    public static $database_index = array();

    // Define the constructor class
    public function __construct(){ }


    // -- INDEX FUNCTIONS -- //

    /**
     * Get a list of all shop index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various index fields for shop objects
        $index_fields = array(
            'shop_id',
            'shop_token',
            'shop_name',
            'shop_class',
            'shop_colour',
            'shop_products_selling',
            'shop_products_buying',
            'shop_flag_hidden',
            'shop_flag_published',
            'shop_order'
            );

        // Add the table prefix if provided in the argument
        if (!empty($table)){
            $table = trim($table, ' .');
            foreach ($index_fields AS $k => $f){
                $index_fields[$k] = $table.'.'.$f;
            }
        }

        // Implode the index fields into a string if requested
        if ($implode){
            $index_fields = implode(', ', $index_fields);
        }

        // Return the index fields, array or string
        return $index_fields;

    }

    /**
     * Get the entire shop index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND shop_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND shop_flag_published = 1 '; }

        // Collect every shop's info from the database index
        $shop_fields = self::get_index_fields(true);
        $shop_index = $db->get_array_list("SELECT {$shop_fields} FROM mmrpg_index_shops WHERE shop_id <> 0 {$temp_where};", 'shop_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($shop_index)){
            $shop_index = self::parse_index($shop_index);
            return $shop_index;
        } else {
            return array();
        }

    }

    /**
     * Get the tokens for all shops in the global index
     * @return array
     */
    public static function get_index_tokens($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND shop_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND shop_flag_published = 1 '; }

        // Collect an array of shop tokens from the database
        $shop_index = $db->get_array_list("SELECT shop_token FROM mmrpg_index_shops WHERE shop_id <> 0 {$temp_where};", 'shop_token');

        // Return the tokens if not empty, else nothing
        if (!empty($shop_index)){
            $shop_tokens = array_keys($shop_index);
            return $shop_tokens;
        } else {
            return array();
        }

    }

    // Define a function for pulling a custom shop index
    public static function get_index_custom($shop_tokens = array()){

        // Pull in global variables
        $db = cms_database::get_database();

        // Generate a token string for the database query
        $shop_tokens_string = array();
        foreach ($shop_tokens AS $shop_token){ $shop_tokens_string[] = "'{$shop_token}'"; }
        $shop_tokens_string = implode(', ', $shop_tokens_string);

        // Collect the requested shop's info from the database index
        $shop_fields = self::get_index_fields(true);
        $shop_index = $db->get_array_list("SELECT {$shop_fields} FROM mmrpg_index_shops WHERE shop_token IN ({$shop_tokens_string});", 'shop_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($shop_index)){
            $shop_index = self::parse_index($shop_index);
            return $shop_index;
        } else {
            return array();
        }

    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($shop_token){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this shop's info from the database index
        $shop_fields = self::get_index_fields(true);
        $shop_index = $db->get_array("SELECT {$shop_fields} FROM mmrpg_index_shops WHERE shop_token = '{$shop_token}';", 'shop_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($shop_index)){
            $shop_index = self::parse_index_info($shop_index);
            return $shop_index;
        } else {
            return array();
        }

    }

    // Define a public function for parsing a shop index array in bulk
    public static function parse_index($shop_index){

        // Loop through each entry and parse its data
        foreach ($shop_index AS $token => $info){
            $shop_index[$token] = self::parse_index_info($info);
        }

        // Return the parsed index
        return $shop_index;

    }

    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($shop_info){

        // Return false if empty
        if (empty($shop_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($shop_info['_parsed'])){ return $shop_info; }
        else { $shop_info['_parsed'] = true; }

        // Define CSV fields that need to be exploded then process
        $csv_fields = array('shop_products_buying', 'shop_products_selling');
        foreach ($csv_fields AS $field){ $shop_info[$field] = !empty($shop_info[$field]) ? explode(',', $shop_info[$field]) : array(); }

        // Return the parsed shop info
        return $shop_info;
    }

}
?>