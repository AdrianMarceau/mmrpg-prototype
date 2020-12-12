<?php
/**
 * Mega Man RPG Community Thread Post
 * <p>The object class for all thread posts in the Mega Man RPG community.</p>
 */
class cms_thread_post {

    /**
     * Get a list of all post fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for music objects
        $music_fields = array(
            'post_id',
            'category_id',
            'thread_id',
            'user_id',
            'user_ip',
            'post_body',
            'post_frame',
            'post_date',
            'post_mod',
            'post_deleted',
            'post_votes',
            'post_target'
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