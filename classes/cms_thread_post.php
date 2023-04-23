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
        $thread_post_fields = array(
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
            foreach ($thread_post_fields AS $key => $field){
                $thread_post_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the table fields into a string if requested
        if ($implode){
            $thread_post_fields = implode(', ', $thread_post_fields);
        }

        // Return the table fields, array or string
        return $thread_post_fields;

    }

    // Define a function for returning a list of thread posts from the database
    public static function get_community_thread_posts($filters = array(), $pagination = array(), $sorting = array(), $fetch_all_fields = false, $index_by_id = false) {

        // Collect a reference to the database
        $db = cms_database::get_database();

        // Define an array to hold SELECT fields to run against the DB
        $select_fields = array();
        $remove_fields = array();

        // Pull table field names from the helper classes of each
        $thread_fields = cms_thread::get_index_fields(false, 'threads');
        $thread_post_fields = cms_thread_post::get_index_fields(false, 'posts');
        $thread_category_fields = cms_thread_category::get_index_fields(false, 'categories');

        // Merge in the thread post fields and then parse out ones we might not want
        $select_fields = array_merge($select_fields, $thread_post_fields, array(
            // ...
            ));
        if (!$fetch_all_fields) {
            $remove_fields[] = '`posts`.`post_body`';
        }

        // Merge in the parent thread fields and then parse out ones we might not want
        if ($fetch_all_fields) {
            $select_fields = array_merge($select_fields, array(
                '`threads`.`thread_name`',
                '`threads`.`thread_token`',
                '`threads`.`thread_date`',
                ));
        }

        // Merge in the thread category fields and then parse out ones we might not want
        if ($fetch_all_fields) {
            $select_fields = array_merge($select_fields, array(
                '`categories`.`category_name`',
                '`categories`.`category_token`',
                '(CASE WHEN `categories`.`category_id` = 0 THEN \'private\' ELSE \'public\' END) AS `category_kind`',
                '`categories`.`category_order`'
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

        // Remove any fields that should be removed based on filtering
        if (!empty($remove_fields)) {
            $select_fields = array_filter($select_fields, function($val) use($remove_fields){ return !in_array($val, $remove_fields); });
        }

        // Implode the select fields into a string
        $select_fields_imploded = implode(', ', $select_fields);

        // Define the base SQL query for this function
        $query_string = "SELECT
                {$select_fields_imploded}
                FROM `mmrpg_posts` AS `posts`
                LEFT JOIN `mmrpg_users` AS `users` ON `users`.`user_id` = `posts`.`user_id`
                LEFT JOIN `mmrpg_threads` AS `threads` ON `threads`.`thread_id` = `posts`.`thread_id`
                LEFT JOIN `mmrpg_categories` AS `categories` ON `categories`.`category_id` = `posts`.`category_id`
                WHERE 1 = 1 ";

        // Collect any filters that were passed to this function and add them to the query
        if (!empty($filters)) {
            if (isset($filters['post_id'])) {
                if (is_array($filters['post_id'])) {
                    $post_id_list = implode(',', $filters['post_id']);
                    $query_string .= "AND `posts`.`post_id` IN ({$post_id_list}) ";
                } else {
                    $query_string .= "AND `posts`.`post_id` = {$filters['post_id']} ";
                }
            } else {
                if (!isset($filters['post_deleted'])){
                    $filters['post_deleted'] = 0;
                }
            }
            if (isset($filters['category_id'])) { $query_string .= "AND `posts`.`category_id` = {$filters['category_id']} "; }
            if (isset($filters['thread_id'])) { $query_string .= "AND `posts`.`thread_id` = {$filters['thread_id']} "; }
            if (isset($filters['user_id'])) { $query_string .= "AND `posts`.`user_id` = {$filters['user_id']} "; }
            if (isset($filters['post_deleted'])) { $query_string .= "AND `posts`.`post_deleted` = {$filters['post_deleted']} "; }
            // Add any other filters here as needed
        }

        // Collect any sorting that was passed to this function and add it to the query
        if (!empty($sorting)) {
            $sorting_array = array();
            foreach ($sorting as $field => $direction) {
                $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
                $sorting_array[] = "{$field} {$direction}";
            }
            $sorting_string = implode(', ', $sorting_array);
            $query_string .= "ORDER BY {$sorting_string} ";
        } else {
            $query_string .= "ORDER BY posts.post_date DESC ";
        }

        // Collect any pagination that was passed to this function and add it to the query
        if (!empty($pagination) && isset($pagination['limit']) && isset($pagination['offset'])) {
            $query_string .= "LIMIT {$pagination['limit']} OFFSET {$pagination['offset']} ";
        }

        // Finish the query string
        $query_string .= "; ";

        // Execute the query string against the DB and format as requested
        $community_thread_posts_index = $db->get_array_list($query_string, 'post_id');
        if (!$index_by_id){ $community_thread_posts_index = array_values($community_thread_posts_index); }
        if (empty($community_thread_posts_index)){ $community_thread_posts_index = array(); }


        // If the thread posts index is not empty, make sure we add the URLs if we're allowed
        if ($fetch_all_fields && !empty($community_thread_posts_index)){
            // Pre-fetch thread info for the posts we found
            $required_thread_ids = array();
            foreach ($community_thread_posts_index AS $key => $post_info){ $required_thread_ids[] = $post_info['thread_id']; }
            $required_thread_info = cms_thread::get_community_threads_index(array('thread_id' => $required_thread_ids), array(), array(), true);
            // Loop through the posts and add the post URLs
            foreach ($community_thread_posts_index AS $key => $post_info){
                $thread_info = isset($required_thread_info[$post_info['thread_id']]) ? $required_thread_info[$post_info['thread_id']] : array('thread_id' => $post_info['thread_id']);
                $community_thread_posts_index[$key]['post_url'] = self::get_thread_post_url($thread_info, $post_info);
            }
        }

        // Return the community threads index
        return $community_thread_posts_index;
    }

    // Define a function for returning a list of thread posts from the database
    public static function get_community_thread_posts_count($filters = array()) {

        // Collect a reference to the database
        $db = cms_database::get_database();

        // Define an array to hold SELECT fields to run against the DB
        $remove_fields = array();

        // Pull table field names from the helper classes of each
        $thread_fields = cms_thread::get_index_fields(false, 'threads');
        $thread_post_fields = cms_thread_post::get_index_fields(false, 'posts');
        $thread_category_fields = cms_thread_category::get_index_fields(false, 'categories');

        // Define the base SQL query for this function
        $query_string = "SELECT
                COUNT(*) AS `num_posts`
                FROM `mmrpg_posts` AS `posts`
                LEFT JOIN `mmrpg_users` AS `users` ON `users`.`user_id` = `posts`.`user_id`
                LEFT JOIN `mmrpg_threads` AS `threads` ON `threads`.`thread_id` = `posts`.`thread_id`
                LEFT JOIN `mmrpg_categories` AS `categories` ON `categories`.`category_id` = `posts`.`category_id`
                WHERE 1 = 1 ";

        // Collect any filters that were passed to this function and add them to the query
        if (!empty($filters)) {
            if (isset($filters['post_id'])) {
                if (is_array($filters['post_id'])) {
                    $post_id_list = implode(',', $filters['post_id']);
                    $query_string .= "AND `posts`.`post_id` IN ({$post_id_list}) ";
                } else {
                    $query_string .= "AND `posts`.`post_id` = {$filters['post_id']} ";
                }
            } else {
                if (!isset($filters['post_deleted'])){
                    $filters['post_deleted'] = 0;
                }
            }
            if (isset($filters['category_id'])) { $query_string .= "AND `posts`.`category_id` = {$filters['category_id']} "; }
            if (isset($filters['thread_id'])) { $query_string .= "AND `posts`.`thread_id` = {$filters['thread_id']} "; }
            if (isset($filters['user_id'])) { $query_string .= "AND `posts`.`user_id` = {$filters['user_id']} "; }
            if (isset($filters['post_deleted'])) { $query_string .= "AND `posts`.`post_deleted` = {$filters['post_deleted']} "; }
            // Add any other filters here as needed
        }

        // Finish the query string
        $query_string .= "; ";

        // Execute the query string against the DB and to collect the amount
        $community_thread_posts_count = $db->get_value($query_string, 'num_posts');
        if (empty($community_thread_posts_count)){ $community_thread_posts_count = 0; }

        // Return the community threads count
        return $community_thread_posts_count;
    }

    // Define a function for returning an indexed list of thread posts from the database
    public static function get_community_thread_posts_index($filters = array(), $pagination = array(), $sorting = array(), $fetch_all_fields = false) {
        return self::get_community_thread_posts($filters, $pagination, $sorting, $fetch_all_fields, true);
    }

    // Define a function for generating select option markup given post info
    public static function generate_thread_post_option_markup($post_info, $include_user_name = true, $include_post_date = true)
    {
        $post_id = $post_info['post_id'];
        $user_name = !empty($post_info['author_name_public']) && $post_info['author_name_public'] !== $post_info['author_name']
            ? $post_info['author_name_public'] . ' / ' . $post_info['author_name']
            : $post_info['author_name_public'] ?: $post_info['author_name'];
        $post_label = "Post ID {$post_id}";
        if ($include_user_name) {
            $post_label .= ' (by ' . $user_name . ')';
        }
        if ($include_post_date) {
            $post_label .= ' (' . date('Y-m-d', $post_info['post_date']) . ')';
        }
        return '<option value="' . $post_id . '">' . $post_label . '</option>';
    }

    // Define a function for generating select options markup given an array of post info
    public static function generate_thread_post_options_markup($community_thread_posts_index, $include_user_name = true, $include_post_date = true)
    {
        $post_options_markup = array();
        //$post_options_markup[] = '<option value="">-</option>';
        foreach ($community_thread_posts_index as $this_post_key => $post_info) {
            $post_options_markup[] = self::generate_thread_post_option_markup($post_info, $include_user_name, $include_post_date);
        }
        $post_options_markup = implode(PHP_EOL, $post_options_markup);
        return $post_options_markup;
    }

    // Define a function for getting post info from the database given an ID or partial info
    public static function get_thread_post_info($post_id_or_info, $fetch_all_fields = false) {

        // Predefine post ID and info variables
        $post_id = null;
        $post_info = null;

        // Fetch post info from the database if we don't already have it
        if (!$post_info) {
            if (is_numeric($post_id_or_info)) {
                $post_id = (int) $post_id_or_info;
                $filters = array('post_id' => $post_id);
                $community_thread_posts = self::get_community_thread_posts($filters, array(), array(), $fetch_all_fields, true);
                $post_info = !empty($community_thread_posts) ? array_shift($community_thread_posts) : null;
            } elseif (is_array($post_id_or_info) && !empty($post_id_or_info['post_id'])) {
                $post_info = $post_id_or_info;
                $post_id = (int) $post_info['post_id'];
                $required_fields = array('post_body', 'post_date', 'user_id');
                if ($fetch_all_fields){ $required_fields = array_merge($required_fields, array('author_name', 'author_name_clean', 'author_name_public')); }
                $missing_fields = array_diff($required_fields, array_keys($post_info));
                if (!empty($missing_fields)) {
                    $filters = array('post_id' => $post_id);
                    $community_thread_posts = self::get_community_thread_posts($filters, array(), array(), $fetch_all_fields, true);
                    $post_info = !empty($community_thread_posts) ? array_shift($community_thread_posts) : null;
                }
            } else {
                return false;
            }
        }

        return $post_info;
    }

    // Define a function for calculating the front-end URL for a given thread post
    public static function get_thread_post_url($thread_id_or_info, $post_id_or_info) {

        // Get thread info if only thread ID is provided or an array containing only the 'thread_id' key
        if (is_numeric($thread_id_or_info) || (is_array($thread_id_or_info) && isset($thread_id_or_info['thread_id']) && count($thread_id_or_info) === 1)) {
            $thread_id = is_array($thread_id_or_info) ? $thread_id_or_info['thread_id'] : $thread_id_or_info;
            $thread_info = cms_thread::get_thread_info($thread_id);
        } else {
            $thread_info = $thread_id_or_info;
        }

        // Get post info if only post ID is provided or an array containing only the 'post_id' key
        if (is_numeric($post_id_or_info) || (is_array($post_id_or_info) && isset($post_id_or_info['post_id']) && count($post_id_or_info) === 1)) {
            $post_id = is_array($post_id_or_info) ? $post_id_or_info['post_id'] : $post_id_or_info;
            $post_info = self::get_thread_post_info($post_id);
        } else {
            $post_info = $post_id_or_info;
        }

        // Extract necessary thread and post info
        $category_token = $thread_info['category_token'];
        $thread_id = $thread_info['thread_id'];
        $thread_token = $thread_info['thread_token'];
        $thread_post_count = $thread_info['thread_post_count'];
        $thread_post_ids = $thread_info['thread_post_ids'];

        // Calculate the index of the current post
        $post_ids_array = explode(',', $thread_post_ids);
        $post_index = array_search($post_info['post_id'], $post_ids_array);

        // Calculate the paginated page number for the post
        $page_number = floor($post_index / MMRPG_SETTINGS_POSTS_PERPAGE) + 1;

        // Build the post URL
        $post_url = "community/{$category_token}/{$thread_id}/{$thread_token}/{$page_number}/#post-{$post_info['post_id']}";

        return $post_url;
    }




}
?>