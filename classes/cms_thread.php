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
        $thread_fields = array(
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
            foreach ($thread_fields AS $key => $field){
                $thread_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $thread_fields = implode(', ', $thread_fields);
        }

        // Return the table fields, array or string
        return $thread_fields;

    }


    // Define a function for returning a list of threads from the database
    public static function get_community_threads($filters = array(), $pagination = array(), $sorting = array(), $fetch_all_fields = false, $index_by_id = false) {

        // Collect a reference to the database
        $db = cms_database::get_database();

        // Define an array to hold SELECT fields to run against the DB
        $select_fields = array();
        $remove_fields = array();

        // Pull table field names from the helper classes of each
        $thread_fields = self::get_index_fields(false, 'threads');
        $thread_category_fields = cms_thread_category::get_index_fields(false, 'categories');
        $thread_post_fields = cms_thread_post::get_index_fields(false, 'posts');

        // Merge in the thread fields and then parse out ones we might not want
        $select_fields = array_merge($select_fields, $thread_fields, array(
            // ...
            ));
        if (!$fetch_all_fields) {
            $remove_fields[] = '`threads`.`thread_body`';
        }

        // Merge in the thread category fields and then parse out ones we might not want
        if ($fetch_all_fields) {
            $select_fields = array_merge($select_fields, $thread_category_fields, array(
                '(CASE WHEN `categories`.`category_id` = 0 THEN \'private\' ELSE \'public\' END) AS `category_kind`'
                ));
        }

        // Merge in the user fields and then parse out ones we might not want
        if ($fetch_all_fields) {
            $select_fields = array_merge($select_fields, array(
                '`users`.`user_id` AS `author_id`',
                '`users`.`user_name` AS `author_name`',
                '`users`.`user_name_clean` AS `author_name_clean`',
                '`users`.`user_name_public` AS `author_name_public`',
                '`users`.`user_image_path` AS `author_image_path`',
                '`users`.`user_colour_token` AS `author_colour_token`',
                '`users`.`user_colour_token2` AS `author_colour_token2`'
                ));
        }

        // Merge in the thread post fields if we're allowed to
        if ($fetch_all_fields) {
            $select_fields = array_merge($select_fields, array(
                'COUNT(`posts`.`post_id`) AS `thread_post_count`',
                'GROUP_CONCAT(`posts`.`post_id` ORDER BY `posts`.`post_date` ASC) AS `thread_post_ids`'
                ));
        }

        // Remove any fields that should be removed based on filtering
        if (!empty($remove_fields)) {
            $select_fields = array_filter($select_fields, function($val) use($remove_fields){ return !in_array($val, $remove_fields); });
        }

        // Implode the select fields into a string
        $select_fields_imploded = implode(', ', $select_fields);

        // Define the base SQL query for this function
        $query_string = "SELECT
                {$select_fields_imploded}
                FROM `mmrpg_threads` AS `threads`
                LEFT JOIN `mmrpg_categories` AS `categories` ON `categories`.`category_id` = `threads`.`category_id`
                LEFT JOIN `mmrpg_posts` AS `posts` ON `posts`.`thread_id` = `threads`.`thread_id`
                LEFT JOIN `mmrpg_users` AS `users` ON `users`.`user_id` = `threads`.`user_id`
                WHERE 1 = 1 ";

        // Collect any filters that were passed to this function and add them to the query
        if (!empty($filters)) {
            if (isset($filters['thread_id'])) {
                if (is_array($filters['thread_id'])) {
                    $thread_id_list = implode(',', $filters['thread_id']);
                    $query_string .= "AND `threads`.`thread_id` IN ({$thread_id_list}) ";
                } else {
                    $query_string .= "AND `threads`.`thread_id` = {$filters['thread_id']} ";
                }
            } else {
                if (!isset($filters['thread_published'])){
                    $filters['thread_published'] = 1;
                }
            }
            if (isset($filters['category_kind'])) {
                if ($filters['category_kind'] === 'public') { $query_string .= "AND `categories`.`category_id` != 0 "; }
                elseif ($filters['category_kind'] === 'private') { $query_string .= "AND `categories`.`category_id` = 0 "; }
            }
            if (isset($filters['category_id'])) { $query_string .= "AND `categories`.`category_id` = {$filters['category_id']} "; }
            if (isset($filters['thread_target'])) { $query_string .= "AND `threads`.`thread_target` = {$filters['thread_target']} "; }
            if (isset($filters['user_id'])) { $query_string .= "AND `threads`.`user_id` = {$filters['user_id']} "; }
            if (isset($filters['thread_published'])) { $query_string .= "AND `threads`.`thread_published` = {$filters['thread_published']} "; }
            // Add any other filters here as needed
        }

        // Collect any sorting that was passed to this function and add it to the query
        $query_string .= "GROUP BY `threads`.`thread_id` ";
        if (!empty($sorting)) {
            $sorting_array = array();
            foreach ($sorting as $field => $direction) {
                $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
                $sorting_array[] = "{$field} {$direction}";
            }
            $sorting_string = implode(', ', $sorting_array);
            $query_string .= "ORDER BY {$sorting_string} ";
        } else {
            $query_string .= "ORDER BY `categories`.`category_order` ASC, `threads`.`thread_date` DESC ";
        }

        // Collect any pagination that was passed to this function and add it to the query
        if (!empty($pagination) && isset($pagination['limit']) && isset($pagination['offset'])) {
            $query_string .= "LIMIT {$pagination['limit']} OFFSET {$pagination['offset']} ";
        }

        // Finish the query string
        $query_string .= "; ";

        // Execute the query string against the DB and format as requested
        $community_threads_index = $db->get_array_list($query_string, 'thread_id');
        if (!$index_by_id){ $community_threads_index = array_values($community_threads_index); }
        if (empty($community_threads_index)){ $community_threads_index = array(); }

        // If the threads index is not empty, make sure we add the URLs if we're allowed
        if ($fetch_all_fields && !empty($community_threads_index)){
            foreach ($community_threads_index AS $key => $info){
                $community_threads_index[$key]['thread_url'] = self::get_thread_url($info);
            }
        }

        // Return the community threads index
        return $community_threads_index;

    }


    // Define a function for returning an indexed list of threads from the database
    public static function get_community_threads_index($filters = array(), $pagination = array(), $sorting = array(), $fetch_all_fields = false) {
        return self::get_community_threads($filters, $pagination, $sorting, $fetch_all_fields, true);
    }


    // Generate markup for a single <option> element for a thread.
    public static function generate_thread_option_markup($thread_info, $include_user_name = true, $include_thread_date = true){
        $thread_id = $thread_info['thread_id'];
        $author_name_public = isset($thread_info['author_name_public']) ? $thread_info['author_name_public'] : '';
        $author_name = isset($thread_info['author_name']) ? $thread_info['author_name'] : '';
        if (!empty($author_name_public) && $author_name_public !== $author_name) {
            $user_name = $author_name_public . ' / ' . $author_name;
        } else {
            $user_name = !empty($author_name_public) ? $author_name_public : $author_name;
        }
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
    public static function generate_thread_options_markup($community_threads_index, $include_user_name = true, $include_thread_date = true){
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

    // Define a function for getting thread info from the database given an ID or partial info
    public static function get_thread_info($thread_id_or_info, $fetch_all_fields = false) {

        // Predefine thread ID and info variables
        $thread_id = null;
        $thread_info = null;

        // Fetch thread info from the database if we don't already have it
        if (!$thread_info) {
            if (is_numeric($thread_id_or_info)) {
                $thread_id = (int) $thread_id_or_info;
                $filters = array('thread_id' => $thread_id);
                $community_threads = self::get_community_threads($filters, array(), array(), $fetch_all_fields, true);
                $thread_info = !empty($community_threads) ? array_shift($community_threads) : null;
            } elseif (is_array($thread_id_or_info) && !empty($thread_id_or_info['thread_id'])) {
                $thread_info = $thread_id_or_info;
                $thread_id = (int) $thread_info['thread_id'];
                $required_fields = array('thread_name', 'thread_token', 'thread_date');
                if ($fetch_all_fields){ $required_fields = array_merge($required_fields, array('category_name', 'category_token', 'category_kind')); }
                $missing_fields = array_diff($required_fields, array_keys($thread_info));
                if (!empty($missing_fields)) {
                    $filters = array('thread_id' => $thread_id);
                    $community_threads = self::get_community_threads($filters, array(), array(), $fetch_all_fields, true);
                    $thread_info = !empty($community_threads) ? array_shift($community_threads) : null;
                }
            } else {
                return false;
            }
        }

        return $thread_info;
    }


    // Define a function for calculating the front-end URL for a given thread
    public static function get_thread_url($thread_id_or_info) {
        $thread_info = self::get_thread_info($thread_id_or_info);
        if (!$thread_info){ return false; }
        $category_token = $thread_info['category_token'];
        $thread_id = $thread_info['thread_id'];
        $thread_token = $thread_info['thread_token'];
        $thread_url = "community/{$category_token}/{$thread_id}/{$thread_token}/";
        return $thread_url;
    }


}
?>