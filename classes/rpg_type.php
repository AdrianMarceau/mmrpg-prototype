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
            'type_flag_hidden',
            'type_flag_published',
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
     * Get a list of all JSON-based player index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @return mixed
     */
    public static function get_json_index_fields($implode = false){

        // Define the various json index fields for player objects
        $json_index_fields = array(
            'type_colour_dark',
            'type_colour_light'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $json_index_fields = implode(', ', $json_index_fields);
        }

        // Return the index fields, array or string
        return $json_index_fields;

    }

    /**
     * Get the entire type index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false, $include_special = true, $include_pseudo_special = true){
        //error_log('rpg_type::get_index()');

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND type_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND type_flag_published = 1 '; }
        if (!$include_special){ $temp_where .= 'AND type_class <> \'special\' '; }
        if (!$include_pseudo_special){ $temp_where .= 'AND type_token <> \'none\' AND type_token <> \'copy\' '; }
        elseif ($include_pseudo_special === true){ $temp_where .= "OR type_token IN ('none','copy') "; }

        // Define a static array for cached queries
        static $index_cache = array();

        // Define the static token for this query
        $cache_token = md5($temp_where);

        // If already found, return the collected index directly, else collect from DB
        if (!empty($index_cache[$cache_token])){ return $index_cache[$cache_token]; }

        // Otherwise attempt to collect the index from the cache
        $cached_index = rpg_object::load_cached_index('types', $cache_token);
        if (!empty($cached_index)){
            $index_cache[$cache_token] = $cached_index;
            return $index_cache[$cache_token];
        }

        // Collect every type's info from the database index
        //error_log('(!) generating a new types index array for '.MMRPG_CONFIG_CACHE_DATE);
        $type_fields = self::get_index_fields(true);
        $type_index = $db->get_array_list("SELECT
            {$type_fields}
            FROM `mmrpg_index_types`
            WHERE
            `type_id` <> 0
            AND `type_class` <> 'system'
            {$temp_where}
            ORDER BY
            `type_order` ASC
            ;", 'type_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($type_index)){ $type_index = self::parse_index($type_index); }
        else { $type_index = array(); }

        // Return the cached index array
        rpg_object::save_cached_index('types', $cache_token, $type_index);
        $index_cache[$cache_token] = $type_index;
        return $index_cache[$cache_token];

    }

    // Define a function for pulling only the tokens for a given index request
    public static function get_index_tokens($include_hidden = false, $include_unpublished = false, $include_special = true, $include_pseudo_special = true){
        $index = self::get_index($include_hidden, $include_unpublished, $include_special, $include_pseudo_special);
        return array_keys($index);
    }

    // Define a function for pulling a custom index given a list of tokens
    public static function get_index_custom($tokens = array()){
        if (empty($tokens)){ return array(); }
        $index = self::get_index();
        foreach ($index AS $token => $info){
            if (!in_array($token, $tokens)){
                unset($index[$token]);
            }
        }
        return $index;
    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($type_token){
        static $type_index;
        if (empty($type_index)){ $type_index = self::get_index(true, false, true); }
        return isset($type_index[$type_token]) ? $type_index[$type_token] : array();
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
        $temp_fields = self::get_json_index_fields();
        foreach ($temp_fields AS $field_name){
            if (!empty($type_info[$field_name])){ $type_info[$field_name] = json_decode($type_info[$field_name], true); }
            else { $type_info[$field_name] = array(); }
        }

        // Return the parsed type info
        return $type_info;
    }

    // Define a static function that returns only stat-related typings
    public static function get_stat_types(){
        return array('energy', 'weapons', 'attack', 'defense', 'speed');
    }


    // Define a static function for collecting the possible hidden power types
    public static function get_hidden_powers($kind = 'elements'){

        // If the ELEMENTS kind was requested
        if ($kind == 'elements'){

            // Define the type possibilities for elemental hidden powers
            $hidden_power_types = array('cutter', 'impact', 'freeze', 'explode', 'flame', 'electric', 'time', 'earth', 'wind', 'water', 'swift', 'nature', 'missile', 'crystal', 'shadow', 'space', 'shield', 'laser');

        }
        // Else if the STATS kind was requested
        elseif ($kind == 'stats'){

            // Define the type possibilities for elemental hidden powers
            $hidden_power_types = array('energy', 'weapons', 'attack', 'defense', 'speed');

        }

        // Return the hidden power types as an array list
        return $hidden_power_types;

    }

    // Define a function for printing this type's name span
    public static function print_span($type_token, $type_text = '', $multi_glue = ' and '){
        $type_tokens = array();
        if (is_array($type_token)){ $type_tokens = $type_token; }
        elseif (is_string($type_token)){ $type_tokens[] = $type_token; }
        else { return $type_token; }
        $type_tokens = array_filter($type_tokens, function($value) { return $value !== ''; });
        if (empty($type_tokens)){ $type_tokens[] = 'none'; }
        $type_tokens = array_unique($type_tokens);
        $type_tokens = array_values($type_tokens);
        $span_markup = '';
        foreach ($type_tokens AS $type_key => $type_token){
            $span_text = !empty($type_text) ? $type_text : ($type_token === 'none' ? 'Neutral' : ucwords(str_replace('_', ' ', $type_token)));
            if ($type_key > 0){ $span_markup .= $multi_glue; }  // Used the $multi_glue parameter instead of hardcoded ' and '
            $span_markup .= '<span class="ability_name ability_type ability_type_'.$type_token.'">'.$span_text.'</span>';
        }
        return $span_markup;
    }



}
?>