<?php
/**
 * Mega Man RPG Community Thread Category
 * <p>The object class for all thread categories in the Mega Man RPG community.</p>
 */
class cms_thread_category {

    /**
     * Get a list of all category fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for category objects
        $category_fields = array(
            'category_id',
            'category_level',
            'category_name',
            'category_token',
            'category_description',
            'category_published',
            'category_order'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($category_fields AS $key => $field){
                $category_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $category_fields = implode(', ', $category_fields);
        }

        // Return the table fields, array or string
        return $category_fields;

    }


    /**
     * Get the entire user category index as an array with parsed info
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index($index_field = 'category_id', $sort_index = false, $include_personal = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect every category's info from the database index
        $category_fields = self::get_index_fields(true);
        $where_string = '1 = 1 '.($include_personal ? '' : 'AND category_id <> 0 ');
        if (!is_string($index_field)){ $index_field = 'category_id'; }
        $category_index = $db->get_array_list("SELECT {$category_fields} FROM mmrpg_categories WHERE {$where_string};", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($category_index)){
            $category_index = self::parse_index($category_index);
            if ($sort_index){ uasort($category_index, function($a, $b){ return $a['category_order'] > $b['category_order']; }); }
            return $category_index;
        } else {
            return array();
        }

    }

    /**
     * Get the a custom set user categories from the index as an array with parsed info
     * @param array $category_list
     * @param string $index_field (optional)
     * @return array
     */
    public static function get_index_custom($category_list, $index_field = 'category_id'){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the where string for the query and populate
        $where_string = array();
        foreach ($category_list AS $lookup){
            // If this is numeric, lookup by Role ID
            if (is_numeric($lookup)){ $where_string[] = "category_id = {$lookup}"; }
            // Otherwise if string, lookup by Role Token
            elseif (is_string($lookup)){ $where_string[] = "category_token = '{$lookup}'"; }
        }
        // Implode the lookup string with ORs in between
        $where_string = implode(' OR ', $where_string);

        // Collect the requested category's info from the database index
        $category_fields = self::get_index_fields(true);
        $category_index = $db->get_array_list("SELECT {$category_fields} FROM mmrpg_categories WHERE category_id <> 0 AND ({$where_string});", $index_field);

        // Parse and return the data if not empty, else nothing
        if (!empty($category_index)){
            $category_index = self::parse_index($category_index);
            return $category_index;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs or tokens for all categories in the global index
     * @param string $index_field
     * @return array
     */
    public static function get_field_values($index_field){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect an array of category tokens from the database
        $category_index = $db->get_array_list("SELECT DISTINCT {$index_field} FROM mmrpg_categories WHERE category_id <> 0;", $index_field);

        // Return the tokens if not empty, else nothing
        if (!empty($category_index)){
            $category_fields = array_keys($category_index);
            return $category_fields;
        } else {
            return array();
        }

    }

    /**
     * Get the IDs for all categories in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_ids($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_category_field_values('category_id', $include_nologin, $include_unapproved);

    }

    /**
     * Get the tokens for all categories in the global index
     * @param bool $include_nologin (optional)
     * @param bool $include_unapproved (optional)
     * @return array
     */
    public static function get_tokens($include_nologin = false, $include_unapproved = false){

        // Redirect this shortcut request to full internal function
        return self::get_category_field_values('category_token', $include_nologin, $include_unapproved);

    }

    /**
     * Collect the database info for a specific category by ID or token
     * @param bool $category_lookup (int or string)
     * @return array
     */
    public static function get_info($category_lookup){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this category's info from the database index
        $lookup = !is_numeric($category_lookup) ? "category_name_clean = '{$category_lookup}'" : "category_id = {$category_lookup}";
        $category_fields = self::get_index_fields(true);
        $category_index = $db->get_array("SELECT {$category_fields} FROM mmrpg_categories WHERE {$lookup};");

        // Parse and return the data if not empty, else nothing
        if (!empty($category_index)){
            $category_index = self::parse_index_info($category_index);
            return $category_index;
        } else {
            return array();
        }

    }

    /**
     * Parse the fields of a category index array in bulk
     * @param array $category_index
     * @return array
     */
    public static function parse_index($category_index){

        // Loop through each entry and parse its data
        foreach ($category_index AS $token => $info){
            $category_index[$token] = self::parse_info($info);
        }

        // Return the parsed index
        return $category_index;

    }

    /**
     * Reformat the raw fields of a category array into proper arrays
     * @param array $category_info
     * @return array
     */
    public static function parse_info($category_info){

        // Return false if empty
        if (empty($category_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($category_info['_parsed'])){ return $category_info; }
        else { $category_info['_parsed'] = true; }

        // Return the parsed category info
        return $category_info;
    }

}
?>