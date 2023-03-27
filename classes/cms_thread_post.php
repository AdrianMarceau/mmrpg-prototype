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

    // Define a function for returning a list of thread posts from the database
    public static function get_community_thread_posts($filters = array(), $pagination = array(), $sorting = array(), $fetch_all_fields = false, $index_by_id = false) {
        $db = cms_database::get_database();

        $select_fields = self::get_index_fields(false, 'posts');

        if (!$fetch_all_fields) {
            $remove = array('posts.post_body');
            $select_fields = array_filter($select_fields, function($val) use($remove){ return !in_array($val, $remove); });
        }

        $select_fields = array_merge($select_fields, array(
            'users.user_id AS author_id',
            'users.user_name AS author_name',
            'users.user_name_clean AS author_name_clean',
            'users.user_name_public AS author_name_public',
            'users.user_image_path AS author_image_path',
            'users.user_colour_token AS author_colour_token',
            'users.user_colour_token2 AS author_colour_token2'
        ));

        if ($fetch_all_fields) {
            $select_fields[] = 'posts.post_body';
        }

        $select_fields_imploded = implode(', ', $select_fields);

        $sql = "SELECT
                {$select_fields_imploded}
                FROM mmrpg_posts AS posts
                LEFT JOIN mmrpg_users AS users ON users.user_id = posts.user_id
                WHERE posts.post_deleted = 0 ";

        if (!empty($filters)) {
            if (isset($filters['category_id'])) { $sql .= "AND posts.category_id = {$filters['category_id']} "; }
            if (isset($filters['thread_id'])) { $sql .= "AND posts.thread_id = {$filters['thread_id']} "; }
            if (isset($filters['user_id'])) { $sql .= "AND posts.user_id = {$filters['user_id']} "; }
            // Add any other filters here as needed
        }

        if (!empty($sorting)) {
            $sorting_array = array();
            foreach ($sorting as $field => $direction) {
                $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
                $sorting_array[] = "{$field} {$direction}";
            }
            $sorting_string = implode(', ', $sorting_array);
            $sql .= "ORDER BY {$sorting_string} ";
        } else {
            $sql .= "ORDER BY posts.post_date DESC ";
        }

        if (!empty($pagination) && isset($pagination['limit']) && isset($pagination['offset'])) {
            $sql .= "LIMIT {$pagination['limit']} OFFSET {$pagination['offset']} ";
        }

        $sql .= "; ";

        if ($index_by_id){
            $community_thread_posts_index = $db->get_array_list($sql, 'post_id');
        } else {
            $community_thread_posts_index = $db->get_array_list($sql);
        }

        if (empty($community_thread_posts_index)) {
            $community_thread_posts_index = array();
        }

        return $community_thread_posts_index;
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


}
?>