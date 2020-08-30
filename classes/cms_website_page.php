<?php
/**
 * Mega Man CMS Website Page
 * <p>The object class for all pages in the Mega Man CMS World.</p>
 */
class cms_website_page {

    /**
     * Create a new CMS page object
     * @param array $page_info (optional)
     * @return cms_website_page
     */
    public function __construct($page_info = array()){

        // Return true on success
        return true;

    }


    // -- PAGE INDEX FUNCTIONS -- //

    /**
     * Get a list of all page fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for page objects
        $page_fields = array(
            'parent_id',
            'page_id',
            'page_token',
            'page_name',
            'page_url',
            'page_title',
            'page_content',
            'page_seo_title',
            'page_seo_keywords',
            'page_seo_description',
            'page_date_created',
            'page_date_modified',
            'page_flag_hidden',
            'page_flag_published',
            'page_order'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($page_fields AS $key => $field){
                $page_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $page_fields = implode(', ', $page_fields);
        }

        // Return the table fields, array or string
        return $page_fields;

    }

    // Define an alias function name for the above
    public static function get_fields($implode = false, $table = ''){
        return self::get_index_fields($implode, $table);
    }

    /**
     * Get the entire page index as an array with parsed info
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false, $index_field = 'page_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND page_flag_hidden <> 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND page_flag_published = 1 '; }

        // Collect every page's info from the database index
        $page_fields = self::get_fields(true);
        $page_index = $db->get_array_list("SELECT {$page_fields} FROM mmrpg_website_pages WHERE page_id <> 0 {$temp_where};", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($page_index)){
            $page_index = self::parse_index($page_index);
            return $page_index;
        } else {
            return array();
        }

    }

    /**
     * Get the a custom set pages from the index as an array with parsed info
     * @param array $page_list
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index_custom($page_list, $index_field = 'page_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the where string for the query and populate
        $where_string = array();
        foreach ($page_list AS $lookup){
            // If this is numeric, lookup by Page ID
            if (is_numeric($lookup)){ $where_string[] = "page_id = {$lookup}"; }
            // Otherwise if string, lookup by Page Token
            elseif (is_string($lookup)){ $where_string[] = "page_token = '{$lookup}'"; }
        }
        // Implode the lookup string with ORs in between
        $where_string = implode(' OR ', $where_string);

        // Collect the requested page's info from the database index
        $page_fields = self::get_fields(true);
        $page_index = $db->get_array_list("SELECT {$page_fields} FROM mmrpg_website_pages WHERE page_id <> 0 AND ({$where_string});", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($page_index)){
            $page_index = self::parse_index($page_index);
            return $page_index;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs or tokens for all pages in the global index
     * @param string $index_field
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_field_values($index_field, $include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND page_flag_hidden <> 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND page_flag_published = 1 '; }

        // Collect an array of page tokens from the database
        $page_index = $db->get_array_list("SELECT DISTINCT {$index_field} FROM mmrpg_website_pages WHERE page_id <> 0 {$temp_where};", $index_field);

        // Return the tokens if not empty, else nothing
        if (!empty($page_index)){
            $page_fields = array_keys($page_index);
            return $page_fields;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs for all pages in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_ids($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_field_values('page_id', $include_nologin, $include_unapproved);

    }

    /**
     * Get the tokens for all pages in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_tokens($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_field_values('page_token', $include_nologin, $include_unapproved);

    }

    /**
     * Collect the database info for a specific page by ID or token
     * @param bool $page_lookup (int or string)
     * @return array
     */
    public static function get_info($page_lookup){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this page's info from the database index
        $lookup = !is_numeric($page_lookup) ? "page_token = '{$page_lookup}'" : "page_id = {$page_lookup}";
        $page_fields = self::get_fields(true);
        $page_index = $db->get_array("SELECT {$page_fields} FROM mmrpg_website_pages WHERE {$lookup};");

        // Parse and return the data if not empty, else nothing
        if (!empty($page_index)){
            $page_index = self::parse_index_info($page_index);
            return $page_index;
        } else {
            return array();
        }

    }

    /**
     * Parse the fields of a page index array in bulk
     * @param array $page_index
     * @return array
     */
    public static function parse_index($page_index){

        // Loop through each entry and parse its data
        foreach ($page_index AS $token => $info){
            $page_index[$token] = self::parse_page_info($info);
        }

        // Return the parsed index
        return $page_index;

    }

    /**
     * Reformat the raw fields of a page array into proper arrays
     * @param array $page_info
     * @return array
     */
    public static function parse_info($page_info){

        // Return false if empty
        if (empty($page_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($page_info['_parsed'])){ return $page_info; }
        else { $page_info['_parsed'] = true; }

        // Return the parsed page info
        return $page_info;
    }


}
?>