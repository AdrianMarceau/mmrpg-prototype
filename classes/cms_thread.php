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


    // Define a function for returning a list of threads from the database
    public static function get_community_threads($filters = array(), $pagination = array(), $sorting = array(), $fetch_all_fields = false, $index_by_id = false) {

        $db = cms_database::get_database();

        $select_fields = self::get_index_fields(false, 'threads');

        if (!$fetch_all_fields) {
            $remove = array('threads.thread_body', 'threads.thread_frame', 'threads.thread_colour');
            $select_fields = array_filter($select_fields, function($val) use($remove){ return !in_array($val, $remove); });
        }

        $select_fields = array_merge($select_fields, array(
            'categories.category_name',
            'categories.category_token',
            '(CASE WHEN categories.category_id = 0 THEN \'private\' ELSE \'public\' END) AS category_kind',
            'categories.category_order',
            'users.user_id AS author_id',
            'users.user_name AS author_name',
            'users.user_name_clean AS author_name_clean',
            'users.user_name_public AS author_name_public',
            'users.user_image_path AS author_image_path',
            'users.user_colour_token AS author_colour_token',
            'users.user_colour_token2 AS author_colour_token2',
            'COUNT(posts.post_id) AS thread_post_count'
            ));

        if ($fetch_all_fields) {
            $select_fields[] = 'threads.thread_body';
            $select_fields[] = 'threads.thread_frame';
            $select_fields[] = 'threads.thread_colour';
        }

        $select_fields_imploded = implode(', ', $select_fields);

        $sql = "SELECT
                {$select_fields_imploded}
                FROM mmrpg_threads AS threads
                LEFT JOIN mmrpg_categories AS categories ON categories.category_id = threads.category_id
                LEFT JOIN mmrpg_posts AS posts ON posts.thread_id = threads.thread_id
                LEFT JOIN mmrpg_users AS users ON users.user_id = threads.user_id
                WHERE threads.thread_published = 1 ";

        if (!empty($filters)) {
            if (isset($filters['category_kind'])) {
                if ($filters['category_kind'] === 'public') { $sql .= "AND categories.category_id != 0 "; }
                elseif ($filters['category_kind'] === 'private') { $sql .= "AND categories.category_id = 0 "; }
            }
            if (isset($filters['category_id'])) { $sql .= "AND categories.category_id = {$filters['category_id']} "; }
            if (isset($filters['thread_target'])) { $sql .= "AND threads.thread_target = {$filters['thread_target']} "; }
            if (isset($filters['user_id'])) { $sql .= "AND threads.user_id = {$filters['user_id']} "; }
            // Add any other filters here as needed
        }

        $sql .= "GROUP BY threads.thread_id ";

        if (!empty($sorting)) {
            $sorting_array = array();
            foreach ($sorting as $field => $direction) {
                $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
                $sorting_array[] = "{$field} {$direction}";
            }
            $sorting_string = implode(', ', $sorting_array);
            $sql .= "ORDER BY {$sorting_string} ";
        } else {
            $sql .= "ORDER BY categories.category_order ASC, threads.thread_date DESC ";
        }


        if (!empty($pagination) && isset($pagination['limit']) && isset($pagination['offset'])) {
            $sql .= "LIMIT {$pagination['limit']} OFFSET {$pagination['offset']} ";
        }

        $sql .= "; ";

        //error_log('get_community_threads()::$sql = '.print_r($sql, true));

        if ($index_by_id){
            $community_threads_index = $db->get_array_list($sql, 'thread_id');
        } else {
            $community_threads_index = $db->get_array_list($sql);
        }


        if (empty($community_threads_index)) {
            $community_threads_index = array();
        }

        return $community_threads_index;
    }


    // Define a function for returning an indexed list of threads from the database
    public static function get_community_threads_index($filters = array(), $pagination = array(), $sorting = array(), $fetch_all_fields = false) {
        return self::get_community_threads($filters, $pagination, $sorting, $fetch_all_fields, true);
    }


    // Generate markup for a single <option> element for a thread.
    public static function generate_thread_option_markup($thread_info, $include_user_name = true, $include_thread_date = true)
    {
        $thread_id = $thread_info['thread_id'];
        $user_name = !empty($thread_info['author_name_public']) && $thread_info['author_name_public'] !== $thread_info['author_name']
            ? $thread_info['author_name_public'] . ' / ' . $thread_info['author_name']
            : $thread_info['author_name_public'] ?: $thread_info['author_name'];
        $thread_name = !empty($thread_info['thread_name']) ? $thread_info['thread_name'] : 'Unknown ID '.$thread_id;
        $thread_label = $thread_name;
        if ($include_user_name) {
            $thread_label .= ' (by '.$user_name.')';
        }
        if ($include_thread_date) {
            $thread_label .= ' ('.date('Y-m-d', $thread_info['thread_date']).')';
        }
        return '<option value="'.$thread_id.'">'.$thread_label.'</option>';
    }

    // Generate markup for a <select> element containing options for all threads.
    public static function generate_thread_options_markup($community_threads_index, $include_user_name = true, $include_thread_date = true)
    {
        // Pre-generate a list of all threads so we can re-use it over and over
        $last_category_id = false;
        $thread_options_markup = array();
        //$thread_options_markup[] = '<option value="">-</option>';
        foreach ($community_threads_index as $this_thread_key => $thread_info){
            if ($thread_info['category_id'] !== $last_category_id) {
                if (!empty($thread_options_markup)) { $thread_options_markup[] = '</optgroup>'; }
                $thread_options_markup[] = '<optgroup label="'.$thread_info['category_name'].'">';
                $last_category_id = $thread_info['category_id'];
            }
            $thread_options_markup[] = self::generate_thread_option_markup($thread_info, $include_user_name, $include_thread_date);
        }
        if (!empty($thread_options_markup)){ $thread_options_markup[] = '</optgroup>'; }
        $thread_options_markup = implode(PHP_EOL, $thread_options_markup);
        return $thread_options_markup;
    }


}
?>