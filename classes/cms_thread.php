<?php
/**
 * Mega Man RPG Community Thread
 * <p>The object class for all threads in the Mega Man RPG community.</p>
 */
class cms_thread {

    /**
     * Get a list of all thread fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various table fields for music objects
        $music_fields = array(
            'thread_id',
            'category_id',
            'user_id',
            'user_ip',
            'thread_name',
            'thread_token',
            'thread_body',
            'thread_frame',
            'thread_colour',
            'thread_date',
            'thread_mod_date',
            'thread_mod_user',
            'thread_published',
            'thread_locked',
            'thread_sticky',
            'thread_views',
            'thread_votes',
            'thread_target'
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