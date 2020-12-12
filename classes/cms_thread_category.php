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

        // Define the various table fields for music objects
        $music_fields = array(
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

}
?>