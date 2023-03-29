<? ob_start(); ?>

    <?

    // TEMP TEMP TEMP
    //$this_post_class = 'private';
    //$this_post_xclass = 'private';
    //$this_post_class_name = 'Private Message';
    //$this_post_xclass_name = 'Private Messages';
    //$this_post_class = 'public';
    //$this_post_xclass = 'public';
    //$this_post_class_name = 'Public Post';
    //$this_post_xclass_name = 'Public Posts';

    // Ensure global post values for this page are set
    if (!isset($this_post_class)){ exit('$this_post_class was undefined!'); }
    if (!isset($this_post_xclass)){ exit('$this_post_xclass was undefined!'); }
    if (!isset($this_post_class_name)){ exit('$this_post_class_name was undefined!'); }
    if (!isset($this_post_xclass_name)){ exit('$this_post_xclass_name was undefined!'); }
    $this_post_creator_label = $this_post_class === 'private' ? 'sender' : 'creator';
    $this_post_target_label = $this_post_class === 'private' ? 'recipient' : 'target';
    $this_post_class_name_uc = ucfirst($this_post_class_name);
    $this_post_xclass_name_uc = ucfirst($this_post_xclass_name);
    $this_post_parentclass_name_uc = ucfirst($this_post_parentclass_name);
    $this_post_xparentclass_name_uc = ucfirst($this_post_xparentclass_name);
    $this_post_creator_label_uc = ucfirst($this_post_creator_label);
    $this_post_target_label_uc = ucfirst($this_post_target_label);

    // Pre-check access permissions before continuing
    $required_permission = $this_post_class === 'private' ? 'edit-private-messages' : 'edit-community-threads';
    if (!rpg_user::current_user_has_permission('edit-private-messages')){
        $form_messages[] = array('error', 'You do not have permission to edit posts!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Dependant Indexes -- */

    // Collect indexes for required object types
    $mmrpg_types_index = cms_admin::get_types_index();

    // Collect indexes for required object types
    $community_categories_index = cms_thread_category::get_index(true, true, true);

    // Create a temporary index of username to be used below
    $community_users_index = array();
    $temp_index_fields = rpg_user::get_index_fields(true, 'users');
    $temp_index_query = "SELECT
        {$temp_index_fields},
        TRIM(CONCAT(`users`.`user_name_public`, `users`.`user_name`)) AS `user_name_sort`,
        (CASE WHEN `creators`.`num_threads` THEN `creators`.`num_threads` ELSE 0 END) AS `user_creat_count`,
        (CASE WHEN `targets`.`num_threads` THEN `targets`.`num_threads` ELSE 0 END) AS `user_target_count`
        FROM `mmrpg_users` AS `users`
        LEFT JOIN (
            SELECT `user_id`, COUNT(*) AS `num_threads` FROM `mmrpg_threads` GROUP BY `user_id`
            ) AS `creators` ON `creators`.`user_id` = `users`.`user_id`
        LEFT JOIN (
            SELECT `thread_target`, COUNT(*) AS `num_threads` FROM `mmrpg_threads` GROUP BY `thread_target`
            ) AS `targets` ON `targets`.`thread_target` = `users`.`user_id`
        WHERE `users`.`user_id` > 0 AND (`creators`.`num_threads` > 0 OR `targets`.`num_threads` > 0)
        ORDER BY `user_name_sort` ASC
        ;";
    $community_users_index = $db->get_array_list($temp_index_query, 'user_id');
    if (empty($community_users_index)){ $community_users_index = array(); }
    //error_log('$temp_index_query = '.$temp_index_query);
    //error_log('$community_users_index = '.print_r($community_users_index, true));

    /*
    // Collect a temporary index of threads to be used below
    $filter_array = array();
    $filter_array['category_kind'] = $this_post_class;
    $community_threads_index = cms_thread::get_community_threads_index($filter_array);
    */


    /* -- Page Script/Style Dependencies  -- */

    // Require codemirror scripts and styles for this page
    $admin_include_common_styles[] = 'codemirror';
    $admin_include_common_scripts[] = 'codemirror';


    /* -- Generate Select Option Markup -- */

    // Pre-generate a list of all categories so we can re-use it over and over
    $last_option_group = false;
    $category_options_markup = array();
    //$category_options_markup[] = '<option value="">-</option>';
    foreach ($community_categories_index AS $this_category_key => $category_info){
        $category_id = $category_info['category_id'];
        $category_token = $category_info['category_token'];
        if ($category_info['category_token'] == 'chat'){ continue; }
        if ($this_post_class === 'private' && $category_token !== 'personal'){ continue; }
        elseif ($this_post_class !== 'private' && $category_token === 'personal'){ continue; }
        $category_name = !empty($category_info['category_name']) ? $category_info['category_name'] : 'Unknown ID '.$category_id;
        $category_options_markup[] = '<option value="'.$category_id.'">'.$category_name.'</option>';
    }
    $category_options_count = count($category_options_markup);
    $category_options_markup = implode(PHP_EOL, $category_options_markup);

    // Pre-generate a list of all creators so we can re-use it over and over
    $last_option_group = false;
    $user_options_markup = array();
    //$user_options_markup[] = '<option value="">-</option>';
    foreach ($community_users_index AS $this_creator_key => $creator_info){
        $creator_id = $creator_info['user_id'];
        $creator_name = !empty($creator_info['user_name_public']) ? $creator_info['user_name_public'] : $creator_info['user_name'];
        if ($creator_name !== $creator_info['user_name']){ $creator_name .= ' ('.$creator_info['user_name'].')'; }
        $creator_name .= ' (ID: '.$creator_info['user_id'].')';
        $user_options_markup[] = '<option value="'.$creator_id.'">'.$creator_name.'</option>';
    }
    $creator_options_count = count($user_options_markup);
    $user_options_markup = implode(PHP_EOL, $user_options_markup);

    /*
    // Pre-generate a list of all threads so we can re-use it over and over
    $thread_options_markup = cms_thread::generate_thread_options_markup($community_threads_index);
    $thread_options_count = count($community_threads_index);
    */


    /* -- Form Setup Actions -- */

    // Define a function for exiting a post edit action
    function exit_post_edit_action($post_id = false){
        global $this_post_page_baseurl;
        if ($post_id !== false){ $location = $this_post_page_baseurl.'editor/post_id='.$post_id; }
        else { $location = $this_post_page_baseurl.'search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = $this_post_page_basename.' | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['post_id'])){

        // Collect form data for processing
        $delete_data['post_id'] = !empty($_GET['post_id']) && is_numeric($_GET['post_id']) ? trim($_GET['post_id']) : '';

        // Let's delete all of this post's data from the database
        if (!empty($delete_data['post_id'])){
            $db->delete('mmrpg_posts', array('post_id' => $delete_data['post_id']));
            $db->delete('mmrpg_posts', array('post_id' => $delete_data['post_id']));
            $form_messages[] = array('success', 'The requested post and its comments have been deleted from the database');
            exit_form_action('success');
        } else {
            $form_messages[] = array('success', 'The requested post does not exist in the database');
            exit_form_action('error');
        }

    }

    // If we're in search mode, we might need to scan for results
    $search_data = array();
    $search_query = '';
    $search_results = array();
    $search_results_count = 0;
    $search_results_limit = 200;
    if ($sub_action == 'search'){

        // Collect the sorting order and direction
        $sort_data = array('name' => 'post_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['post_id'] = !empty($_GET['post_id']) && is_numeric($_GET['post_id']) ? trim($_GET['post_id']) : '';
        $search_data['post_body'] = !empty($_GET['post_body']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['post_body']) ? trim($_GET['post_body']) : '';
        $search_data['post_content'] = !empty($_GET['post_content']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['post_content']) ? trim($_GET['post_content']) : '';
        $search_data['post_category'] = !empty($_GET['post_category']) && is_numeric($_GET['post_category']) ? (int)($_GET['post_category']) : '';
        $search_data['post_thread'] = !empty($_GET['post_thread']) && is_numeric($_GET['post_thread']) ? (int)($_GET['post_thread']) : '';
        $search_data['post_creator'] = !empty($_GET['post_creator']) && is_numeric($_GET['post_creator']) ? (int)($_GET['post_creator']) : '';
        $search_data['post_target'] = !empty($_GET['post_target']) && is_numeric($_GET['post_target']) ? (int)($_GET['post_target']) : '';
        $search_data['post_date_from'] = !empty($_GET['post_date_from']) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $_GET['post_date_from']) ? trim($_GET['post_date_from']) : '';
        $search_data['post_date_to'] = !empty($_GET['post_date_to']) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $_GET['post_date_to']) ? trim($_GET['post_date_to']) : '';
        $search_data['post_flag_deleted'] = isset($_GET['post_flag_deleted']) && $_GET['post_flag_deleted'] !== '' ? (!empty($_GET['post_flag_deleted']) ? 1 : 0) : '';
        cms_admin::object_index_search_data_clean_query_values($search_data, 'post', $backup_search_data);

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_post_fields = cms_thread_post::get_index_fields(true, 'posts');
        $search_query = "SELECT
            {$temp_post_fields},
            `threads`.`thread_token` AS `thread_token`,
            `threads`.`thread_name` AS `thread_name`
            FROM `mmrpg_posts` AS `posts`
            LEFT JOIN (
                SELECT `thread_id`, `thread_token`, `thread_name`
                FROM `mmrpg_threads`
                ) AS `threads` ON `threads`.`thread_id` = `posts`.`thread_id`
            WHERE 1=1
            AND `posts`.`post_id` <> 0
            ";

        // Hide personal messages unless we're explicitly in that mode
        if ($this_post_class === 'private'){ $search_query .= "AND category_id = 0 "; }
        else { $search_query .= "AND category_id <> 0 "; }
        $search_query .= "AND user_id <> ".MMRPG_SETTINGS_GUEST_ID." ";
        $search_query .= "AND user_id <> ".MMRPG_SETTINGS_TARGET_PLAYERID." ";

        // If the post ID was provided, we can search by exact match
        if (!empty($search_data['post_id'])){
            $post_id = $search_data['post_id'];
            $search_query .= "AND post_id = {$post_id} ";
            $search_results_limit = false;
        }

        // Else if the post flavour was provided, we can use wildcards
        if (!empty($search_data['post_body'])){
            $post_body = $search_data['post_body'];
            $post_body = str_replace(array(' ', '*', '%'), '%', $post_body);
            $post_body = preg_replace('/%+/', '%', $post_body);
            $post_body = '%'.$post_body.'%';
            $search_query .= "AND (post_body LIKE '{$post_body}') ";
            $search_results_limit = false;
        }

        // If the post category was provided, we can search by exact match
        if (!empty($search_data['post_category'])){
            $category_id = $search_data['post_category'];
            $search_query .= "AND category_id = {$category_id} ";
            $search_results_limit = false;
        }

        // If the post category was provided, we can search by exact match
        if (!empty($search_data['post_thread'])){
            $thread_id = $search_data['post_thread'];
            $search_query .= "AND posts.thread_id = {$thread_id} ";
            $search_results_limit = false;
        }

        // If the post creator was provided, we can search by exact match
        if (!empty($search_data['post_creator'])){
            $creator_id = $search_data['post_creator'];
            $search_query .= "AND user_id = {$creator_id} ";
            $search_results_limit = false;
        }

        // If the post target was provided, we can search by exact match
        if (!empty($search_data['post_target'])){
            $target_id = $search_data['post_target'];
            $search_query .= "AND post_target = {$target_id} ";
            $search_results_limit = false;
        }

        // If the post dates were provided, we can search by range match
        if (!empty($search_data['post_date_from'])){
            $date_from = $search_data['post_date_from'];
            $date_from_unix = strtotime($date_from);
            $search_query .= "AND post_date >= {$date_from_unix} ";
            $search_results_limit = false;
        }
        if (!empty($search_data['post_date_to'])){
            $date_to = $search_data['post_date_to'];
            $date_to_unix = strtotime($date_to);
            $search_query .= "AND post_date <= {$date_to_unix} ";
            $search_results_limit = false;
        }

        // If the post published flag was provided
        if ($search_data['post_flag_deleted'] !== ''){
            $search_query .= "AND post_deleted = {$search_data['post_flag_deleted']} ";
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "post_body ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string} ";

        // Impose a limit on the search results
        if (!empty($search_results_limit)){ $search_query .= "LIMIT {$search_results_limit} "; }

        // End the query now that we're done
        $search_query .= ";";

        // Collect search results from the database
        //error_log($search_query);
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;
        cms_admin::object_index_search_data_restore_backup_data($search_data, 'post', $backup_search_data);

        // Collect a total number from the database
        $where = 'AND category_id '.($this_post_class === 'private' ? '=' : '<>').' 0';
        $search_results_total = $db->get_value("SELECT COUNT(post_id) AS total FROM `mmrpg_posts` WHERE 1=1 {$where};", 'total');

    }

    // If we're in editor mode, we should collect post info from database
    $post_data = array();
    $post_thread_data = array();
    $post_data_is_new = false;
    $editor_data = array();
    if ($sub_action == 'editor'
        && isset($_GET['post_id'])
        ){

        // Collect form data for processing
        $editor_data['post_id'] = !empty($_GET['post_id']) && is_numeric($_GET['post_id']) ? trim($_GET['post_id']) : '';


        /* -- Collect Post Data -- */

        // Collect post details from the database
        $temp_post_fields = cms_thread_post::get_index_fields(true);
        $temp_post_thread_fields = cms_thread::get_index_fields(true);
        if (!empty($editor_data['post_id'])){
            $post_data = $db->get_array("SELECT {$temp_post_fields} FROM `mmrpg_posts` WHERE `post_id` = {$editor_data['post_id']};");
            //$post_thread_data = $db->get_array("SELECT {$temp_post_thread_fields} FROM `mmrpg_threads` WHERE `thread_id` = {$post_data['thread_id']};");
            $post_thread_data = cms_thread::get_thread_info($post_data['thread_id'], true);
        } else {

            // Generate temp data structure for the new post
            $post_data_is_new = true;
            $admin_id = $_SESSION['admin_id'];
            $post_data = array(
                'post_id' => 0,
                'category_id' => 0,
                'thread_id' => 0,
                'user_id' => 0,
                'user_ip' => '',
                'post_body' => '',
                'post_frame' => '',
                'post_date' => time(),
                'post_mod' => 0,
                'post_deleted' => 0,
                'post_votes' => 0,
                'post_target' => 0
                );

            // Overwrite temp data with any backup data provided
            if (!empty($backup_form_data)){
                foreach ($backup_form_data AS $f => $v){
                    $post_data[$f] = $v;
                }
            }

        }

        // If post data could not be found, produce error and exit
        if (empty($post_data)){ exit_post_edit_action(); }

        // If this post is private when we're in public mode, or public when we're in private mode
        if ($post_data['category_id'] === 0 && $this_post_class !== 'private'){
            $form_messages[] = array('error', 'This message is private! Redirecting to appropriate editor...');
            redirect_form_action('admin/edit-message-replies/editor/post_id='.$post_data['post_id']);
        } elseif ($post_data['category_id'] !== 0 && $this_post_class !== 'public'){
            $form_messages[] = array('error', 'This post is public! Redirecting to appropriate editor...');
            redirect_form_action('admin/edit-thread-comments/editor/post_id='.$post_data['post_id']);
        }

        // Collect the post's name(s) for display
        $post_name_display = $this_post_class_name_uc.' ID '.$post_data['post_id'];
        if ($post_data_is_new){ $this_page_tabtitle = 'New '.$this_post_class_name_uc.' | '.$this_page_tabtitle; }
        else { $this_page_tabtitle = $post_name_display.' | '.$this_page_tabtitle; }

        // If form data has been submit for this post, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit-'.$this_post_parentclass_name.'-'.$this_post_xclass_name){

            // COLLECT form data from the request and parse out simple rules

            $form_data['post_id'] = !empty($_POST['post_id']) && is_numeric($_POST['post_id']) ? trim($_POST['post_id']) : 0;
            $form_data['category_id'] = !empty($_POST['category_id']) && is_numeric($_POST['category_id']) ? trim($_POST['category_id']) : 0;
            $form_data['thread_id'] = !empty($_POST['thread_id']) && is_numeric($_POST['thread_id']) ? trim($_POST['thread_id']) : 0;
            $form_data['user_id'] = !empty($_POST['user_id']) && is_numeric($_POST['user_id']) ? trim($_POST['user_id']) : 0;
            $form_data['user_ip'] = !empty($_POST['user_ip']) && preg_match('/^((([0-9]{1,3}\.){3}([0-9]{1,3}){1}),?\s?)+$/i', $_POST['user_ip']) ? trim($_POST['user_ip']) : '';
            $form_data['post_body'] = !empty($_POST['post_body']) ? trim(strip_tags($_POST['post_body'])) : '';
            $form_data['post_frame'] = !empty($_POST['post_frame']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['post_frame']) ? trim(strtolower($_POST['post_frame'])) : '';
            $form_data['post_date'] = !empty($_POST['post_date']) && preg_match('/^[-_0-9a-z\.\*\s\:]+$/i', $_POST['post_date']) ? trim($_POST['post_date']) : '';
            $form_data['post_deleted'] = isset($_POST['post_deleted']) && is_numeric($_POST['post_deleted']) ? (int)(trim($_POST['post_deleted'])) : 0;
            $form_data['post_target'] = !empty($_POST['post_target']) && is_numeric($_POST['post_target']) ? trim($_POST['post_target']) : 0;

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (!$post_data_is_new && empty($form_data['post_id'])){ $form_messages[] = array('error', $this_post_class_name_uc.' ID was not provided'); $form_success = false; }
            if ($this_post_class === 'public'){
                if (empty($form_data['category_id'])){ $form_messages[] = array('error', $this_post_class_name_uc.' Category was not provided or was invalid'); $form_success = false; }
                if (!empty($form_data['post_target'])){ $form_messages[] = array('error', $this_post_class_name_uc.' '.$this_post_target_label_uc.' should not be provided for posts'); $form_success = false; }
            } elseif ($this_post_class === 'private'){
                if (!empty($form_data['category_id'])){ $form_messages[] = array('error', $this_post_class_name_uc.' Category should not be provided for messages'); $form_success = false; }
                if (empty($form_data['post_target'])){ $form_messages[] = array('error', $this_post_class_name_uc.' Recipient was not provided or was invalid'); $form_success = false; }
            }
            if (empty($form_data['thread_id'])){ $form_messages[] = array('error', $this_post_class_name_uc.' '.$this_post_parentclass_name_uc.' ID was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['post_body'])){ $form_messages[] = array('error', $this_post_class_name_uc.' Body was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['post_date'])){ $form_messages[] = array('error', $this_post_class_name_uc.' Date was not provided or was invalid'); $form_success = false; }
            if (!$form_success){ exit_post_edit_action($form_data['post_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (!$post_data_is_new && empty($form_data['post_body'])){ $form_messages[] = array('warning', $this_post_class_name_uc.' Body was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            // Reformat the provided date into a unix string, provided it's valid
            if (!empty($form_data['post_date'])
                && strtotime($form_data['post_date'])){
                $form_data['post_date'] = strtotime($form_data['post_date']);
            }

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$post_data = '.print_r($post_data, true).'</pre>');
            //foreach ($form_data AS $key => $value){ if ($form_data[$key] != $post_data[$key]){ $form_messages[] = array('warning', 'Key '.$key.' did not pass the corruption check! <br /> ('.$form_data[$key].' != '.$post_data[$key].')'); } }
            //exit_post_edit_action($form_data['post_id']);

            // Make a copy of the update data sans the post ID
            $update_data = $form_data;
            unset($update_data['post_id']);

            // If this is a new post we insert, otherwise we update the existing
            if ($post_data_is_new){

                // Update the main database index with changes to this post's data
                $insert_results = $db->insert('mmrpg_posts', $update_data);

                // If we made it this far, the update must have been a success
                if ($insert_results !== false){ $form_success = true; $form_messages[] = array('success', $this_post_class_name_uc.' data was created successfully!'); }
                else { $form_success = false; $form_messages[] = array('error', $this_post_class_name_uc.' data could not be created...'); }

                // If the form was a success, collect the new ID for the redirect
                if ($form_success){
                    $new_post_id = $db->get_value("SELECT MAX(post_id) AS max FROM `mmrpg_posts`;", 'max');
                    $form_data['post_id'] = $new_post_id;
                }

            } else {

                // Update the main database index with changes to this post's data
                $update_results = $db->update('mmrpg_posts', $update_data, array('post_id' => $form_data['post_id']));

                // If we made it this far, the update must have been a success
                if ($update_results !== false){ $form_messages[] = array('success', $this_post_class_name_uc.' data was updated successfully!'); }
                else { $form_messages[] = array('error', $this_post_class_name_uc.' data could not be updated...'); }

            }

            // If successful, we need to update the JSON file
            if ($form_success){
                if ($post_data_is_new){ $post_data['post_id'] = $new_post_id; }
            }

            // We're done processing the form, we can exit
            if (empty($form_data['post_id'])){ exit_post_edit_action(false); }
            else { exit_post_edit_action($form_data['post_id']); }

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }

    /*
    // If we're in groups mode, we need to preset vars and then include common file
    $object_group_kind = 'post';
    $object_group_class = 'post';
    $object_group_editor_url = 'admin/edit-'.$this_post_xclass_name.'/groups/';
    $object_group_editor_name = 'Post Groups';
    if ($sub_action == 'groups'){
        require('edit-groups_actions.php');
    }
    */

    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="<?= $this_post_page_baseurl ?>"><?= $this_post_page_basename ?></a>
        <? if ($sub_action == 'editor' && !empty($post_data)): ?>
            &raquo; <a href="<?= $this_post_page_baseurl ?>editor/post_id=<?= $post_data['post_id'] ?>"><?= !empty($post_name_display) ? $post_name_display : 'New '.$this_post_class_name_uc ?></a>
        <? elseif ($sub_action == 'groups'): ?>
            &raquo; <a href="<?= $object_group_editor_url ?>"><?= $object_group_editor_name ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-community edit-posts edit-<?= $this_post_xclass_name ?>" data-baseurl="<?= $this_post_page_baseurl ?>" data-object="post" data-xobject="posts">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search <?= $this_post_xclass_name_uc ?></h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <input type="hidden" name="subaction" value="search" />

                    <div class="field">
                        <strong class="label">By ID</strong>
                        <input class="textbox" type="text" name="post_id" value="<?= !empty($search_data['post_id']) ? $search_data['post_id'] : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By <?= $this_post_parentclass_name_uc ?> ID</strong>
                        <input class="textbox" type="text" name="post_thread" value="<?= !empty($search_data['post_thread']) ? $search_data['post_thread'] : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Content</strong>
                        <input class="textbox" type="text" name="post_body" placeholder="" value="<?= !empty($search_data['post_body']) ? htmlentities($search_data['post_body'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <? if ($this_post_class === 'public'){ ?>
                        <div class="field">
                            <strong class="label">By Category</strong>
                            <select class="select" name="post_category"><option value=""></option><?
                                foreach ($community_categories_index AS $category_id => $category_info){
                                    $option_label = $category_info['category_name'];
                                    ?><option value="<?= $category_id ?>"<?= !empty($search_data['post_category']) && $search_data['post_category'] === $category_id ? ' selected="selected"' : '' ?>><?= $option_label ?></option><?
                                    } ?>
                            </select><span></span>
                        </div>
                    <? } ?>

                    <div class="field">
                        <strong class="label">By <?= $this_post_creator_label_uc ?></strong>
                        <select class="select" name="post_creator"><option value=""></option><?
                            foreach ($community_users_index AS $creator_id => $creator_info){
                                $option_label = $creator_info['user_name'];
                                if (!empty($creator_info['user_name_public']) && $creator_info['user_name_public'] !== $creator_info['user_name']){ $option_label = $creator_info['user_name_public'].' ('.$option_label.')'; }
                                ?><option value="<?= $creator_id ?>"<?= !empty($search_data['post_creator']) && $search_data['post_creator'] === $creator_id ? ' selected="selected"' : '' ?>><?= $option_label ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <? if ($this_post_class === 'private'){ ?>
                        <div class="field">
                            <strong class="label">By <?= $this_post_target_label_uc ?></strong>
                            <select class="select" name="post_target"><option value=""></option><?
                                foreach ($community_users_index AS $creator_id => $creator_info){
                                    $option_label = $creator_info['user_name'];
                                    if (!empty($creator_info['user_name_public']) && $creator_info['user_name_public'] !== $creator_info['user_name']){ $option_label = $creator_info['user_name_public'].' ('.$option_label.')'; }
                                    ?><option value="<?= $creator_id ?>"<?= !empty($search_data['post_target']) && $search_data['post_target'] === $creator_id ? ' selected="selected"' : '' ?>><?= $option_label ?></option><?
                                    } ?>
                            </select><span></span>
                        </div>
                    <? } ?>

                    <div class="field halfsize">
                        <div class="label">
                            <strong>By Date Range</strong>
                            <em>YYYY-MM-DD</em>
                        </div>
                        <div class="field autosize">
                            <input class="textbox" type="date" name="post_date_from" value="<?= !empty($search_data['post_date_from']) ? htmlentities($search_data['post_date_from'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                        </div>
                        <div class="field autosize">
                            <input class="textbox" type="date" name="post_date_to" value="<?= !empty($search_data['post_date_to']) ? htmlentities($search_data['post_date_to'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                        </div>
                    </div>

                    <div class="field fullsize has5cols flags">
                    <?
                    $flag_names = array();
                    if ($this_post_class === 'public'){
                        $flag_names['deleted'] = array('icon' => 'fas fa-trash', 'yes' => 'Deleted', 'no' => 'Not Deleted');
                    } elseif ($this_post_class === 'private'){
                        $flag_names['deleted'] = array('icon' => 'fas fa-trash', 'yes' => 'Deleted', 'no' => 'Not Deleted');
                    }
                    foreach ($flag_names AS $flag_token => $flag_info){
                        if (isset($flag_info['break'])){ echo('<div class="break"></div>'); continue; }
                        $flag_name = 'post_flag_'.$flag_token;
                        $flag_label = ucfirst($flag_token);
                        ?>
                        <div class="subfield">
                            <strong class="label"><?= $flag_label ?> <span class="<?= $flag_info['icon'] ?>"></span></strong>
                            <select class="select" name="<?= $flag_name ?>">
                                <option value=""<?= !isset($search_data[$flag_name]) || $search_data[$flag_name] === '' ? ' selected="selected"' : '' ?>></option>
                                <option value="1"<?= isset($search_data[$flag_name]) && $search_data[$flag_name] === 1 ? ' selected="selected"' : '' ?>><?= $flag_info['yes'] ?></option>
                                <option value="0"<?= isset($search_data[$flag_name]) && $search_data[$flag_name] === 0 ? ' selected="selected"' : '' ?>><?= $flag_info['no'] ?></option>
                            </select><span></span>
                        </div>
                        <?
                    }
                    ?>
                    </div>

                    <div class="buttons">
                        <input class="button search" type="submit" value="Search" />
                        <input class="button reset" type="reset" value="Reset" onclick="javascript:window.location.href='<?= $this_post_page_baseurl ?>';" />
                        <a class="button new" href="<?= '<?= $this_post_page_baseurl ?>editor/post_id=0' ?>">New <?= ucwords($this_post_class_name) ?></a>
                    </div>

                </form>

            </div>

            <? if (!empty($search_results)): ?>

                <!-- SEARCH RESULTS -->

                <div class="results">

                    <table class="list" style="width: 100%;">
                        <colgroup>
                            <col class="id" width="60" />
                            <? if ($this_post_class === 'public'){ ?>
                                <col class="body" />
                                <col class="thread" />
                                <col class="creator" width="120" />
                                <col class="category" width="120" />
                                <col class="date created" width="140" />
                            <? } elseif ($this_post_class === 'private'){ ?>
                                <col class="body" />
                                <col class="thread" />
                                <col class="creator" width="110" />
                                <col class="target" width="110" />
                                <col class="date created" width="140" />
                            <? } else { ?>
                                <col class="body" />
                                <col class="thread" />
                                <col class="creator" width="120" />
                                <col class="target" width="110" />
                                <col class="category" width="120" />
                                <col class="date created" width="80" />
                                <col class="flag deleted" width="80" />
                            <? } ?>
                            <col class="actions" width="130" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('post_id', 'ID') ?></th>
                                <? if ($this_post_class === 'public'){ ?>
                                    <th class="body"><?= cms_admin::get_sort_link('post_body', $this_post_class_name_uc) ?></th>
                                    <th class="thread"><?= cms_admin::get_sort_link('post_thread', $this_post_parentclass_name_uc) ?></th>
                                    <th class="creator"><?= cms_admin::get_sort_link('user_id', $this_post_creator_label_uc) ?></th>
                                    <th class="category"><?= cms_admin::get_sort_link('category_id', 'Category') ?></th>
                                    <th class="date created"><?= cms_admin::get_sort_link('post_date', 'Date & Time') ?></th>
                                <? } elseif ($this_post_class === 'private'){ ?>
                                    <th class="body"><?= cms_admin::get_sort_link('post_body', $this_post_class_name_uc) ?></th>
                                    <th class="thread"><?= cms_admin::get_sort_link('post_thread', $this_post_parentclass_name_uc) ?></th>
                                    <th class="creator"><?= cms_admin::get_sort_link('user_id', $this_post_creator_label_uc) ?></th>
                                    <th class="target"><?= cms_admin::get_sort_link('post_target', $this_post_target_label_uc) ?></th>
                                    <th class="date created"><?= cms_admin::get_sort_link('post_date', 'Date & Time') ?></th>
                                <? } else { ?>
                                    <th class="body"><?= cms_admin::get_sort_link('post_body', $this_post_class_name_uc) ?></th>
                                    <th class="thread"><?= cms_admin::get_sort_link('post_thread', $this_post_parentclass_name_uc) ?></th>
                                    <th class="creator"><?= cms_admin::get_sort_link('user_id', $this_post_creator_label_uc) ?></th>
                                    <th class="target"><?= cms_admin::get_sort_link('user_id', $this_post_target_label_uc) ?></th>
                                    <th class="category"><?= cms_admin::get_sort_link('category_id', 'Category') ?></th>
                                    <th class="date created"><?= cms_admin::get_sort_link('post_date', 'Date & Time') ?></th>
                                    <th class="flag deleted"><?= cms_admin::get_sort_link('post_deleted', 'Deleted') ?></th>
                                <? } ?>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <? if ($this_post_class === 'public'){ ?>
                                    <th class="head body"></th>
                                    <th class="head thread"></th>
                                    <th class="head creator"></th>
                                    <th class="head category"></th>
                                    <th class="head date created"></th>
                                <? } elseif ($this_post_class === 'private'){ ?>
                                    <th class="head body"></th>
                                    <th class="head thread"></th>
                                    <th class="head creator"></th>
                                    <th class="head target"></th>
                                    <th class="head date created"></th>
                                <? } else { ?>
                                    <th class="head body"></th>
                                    <th class="head thread"></th>
                                    <th class="head creator"></th>
                                    <th class="head target"></th>
                                    <th class="head category"></th>
                                    <th class="head date created"></th>
                                    <th class="head flag deleted"></th>
                                <? } ?>
                                <th class="head count"><?= cms_admin::get_totals_markup() ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <? if ($this_post_class === 'public'){ ?>
                                    <td class="foot body"></td>
                                    <td class="foot thread"></td>
                                    <td class="foot creator"></td>
                                    <td class="foot category"></td>
                                    <td class="foot date created"></td>
                                <? } elseif ($this_post_class === 'private'){ ?>
                                    <td class="foot body"></td>
                                    <td class="foot thread"></td>
                                    <td class="foot creator"></td>
                                    <td class="foot target"></td>
                                    <td class="foot date created"></td>
                                <? } else { ?>
                                    <td class="foot body"></td>
                                    <td class="foot thread"></td>
                                    <td class="foot creator"></td>
                                    <td class="foot target"></td>
                                    <td class="foot category"></td>
                                    <td class="foot date created"></td>
                                    <td class="foot flag deleted"></td>
                                <? } ?>
                                <td class="foot count"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?

                            // Loop through collected results and display them to the admin
                            foreach ($search_results AS $key => $post_data){

                                //error_log('$post_data = '.print_r($post_data, true));

                                // Collect the basic name, token, and ID for the post
                                $post_id = $post_data['post_id'];
                                $thread_id = $post_data['thread_id'];
                                $thread_token = $post_data['thread_token'];
                                $thread_name = $post_data['thread_name'];
                                $post_body = trim($post_data['post_body']);
                                $post_body_preview = trim($post_body);
                                $post_body_preview = mmrpg_formatting_decode($post_body_preview);
                                $post_body_preview = strip_tags(str_replace('<br />', ' ', $post_body_preview));
                                //$post_body_preview = preg_replace('/\([^\]\])\{[a-z0-9]+\}/', '$1', $post_body_preview);
                                //$post_body_preview = preg_replace('/[\{\}\[\]]+/', '', $post_body_preview);
                                if (strlen($post_body_preview) > 100){ $post_body_preview = substr($post_body_preview, 0, 100).'&hellip;'; }
                                if (strlen($post_body_preview) < 10){ $post_body_preview = substr($post_body, 0, 100); }
                                if (strlen($post_body_preview) < 1){ $post_body_preview .= '&hellip;'; }

                                // Generate some icons for the name to represent statuses (locked, sticky, etc.)
                                $post_body_icons = array();
                                if (!empty($post_data['post_deleted'])){ $post_body_icons[] = '<i class="fas fa-trash"></i>'; }
                                $post_body_icons = implode(' ', $post_body_icons).(!empty($post_body_icons) ? ' ' : '');

                                // Collect information about the post category
                                $category_id = $post_data['category_id'];
                                $category_is_active = !empty($search_data['post_category']) && $search_data['post_category'] === $category_id ? true : false;
                                $category_info = !empty($community_categories_index[$category_id]) ? $community_categories_index[$category_id] : array();
                                //$category_name = !empty($category_info['category_token']) ? ucfirst($category_info['category_token']) : 'Unknown';
                                $category_name = !empty($category_info['category_name']) ? $category_info['category_name'] : 'Unknown';
                                $category_token = !empty($category_info['category_token']) ? $category_info['category_token'] : 'unknown';
                                $post_category_url = $this_post_page_baseurl.'&subaction=search&post_category='.$category_id;
                                if ($category_is_active){ $post_category_url = $this_post_page_baseurl.'&subaction=search'; }
                                $post_category_link = '<a class="link sublink" href="'.$post_category_url.'">'.$category_name.'</a>';

                                // Collect information about the post creator
                                $post_thread_id = !empty($post_data['thread_id']) ? $post_data['thread_id'] : 0;
                                $post_thread_info = !empty($post_thread_id) && !empty($community_users_index[$post_thread_id]) ? $community_users_index[$post_thread_id] : false;
                                $post_thread_is_active = !empty($search_data['post_thread']) && $search_data['post_thread'] === $post_thread_id ? true : false;
                                $post_thread_name = '-';
                                $post_thread_disabled = false;
                                if ($post_thread_id < 1 || empty($post_data['thread_name'])){ $post_thread_name = 'Deleted'; $post_thread_disabled = true; }
                                else { $post_thread_name = !empty($post_data['thread_name']) ? $post_data['thread_name'] : $this_post_class_name_uc.' ID '.$post_data['thread_id']; }
                                $post_thread_type = 'none';
                                $post_thread_span = !empty($post_thread_info) ? '<span class="type_span type_'.$post_thread_type.'">'.$post_thread_name.'</span>' : '-';
                                //$post_thread_url = 'admin/edit-users/editor/user_id='.$post_thread_id;
                                if (!$post_thread_disabled){
                                    $post_thread_url = $this_post_page_baseurl.'&subaction=search&post_thread='.$post_thread_id;
                                    if ($post_thread_is_active){ $post_thread_url = $this_post_page_baseurl.'&subaction=search'; }
                                    $post_thread_link = '<a class="link sublink" href="'.$post_thread_url.'">'.$post_thread_name.'</a>';
                                } else {
                                    $post_thread_link = '<span>'.$post_thread_name.'</span>';
                                }

                                // Collect information about the post creator
                                $post_creator_id = !empty($post_data['user_id']) ? $post_data['user_id'] : 0;
                                $post_creator_info = !empty($post_creator_id) && !empty($community_users_index[$post_creator_id]) ? $community_users_index[$post_creator_id] : false;
                                $post_creator_is_active = !empty($search_data['post_creator']) && $search_data['post_creator'] === $post_creator_id ? true : false;
                                $post_creator_name = '-';
                                $post_creator_disabled = false;
                                if ($post_creator_id < 1){ $post_creator_name = 'Deleted'; $post_creator_disabled = true; }
                                elseif ($post_creator_id === MMRPG_SETTINGS_GUEST_ID){ $post_creator_name = 'Guest'; $post_creator_disabled = true; }
                                elseif ($post_creator_id === MMRPG_SETTINGS_TARGET_PLAYERID){ $post_creator_name = 'System'; $post_creator_disabled = true; }
                                elseif (empty($post_creator_info)){ $post_creator_name = 'Unknown (ID: '.$post_creator_id.')'; }
                                else { $post_creator_name = !empty($post_creator_info['user_name_public']) ? $post_creator_info['user_name_public'] : $post_creator_info['user_name']; }
                                $post_creator_type = !empty($post_creator_info) ? (!empty($post_creator_info['user_colour_token']) ? $post_creator_info['user_colour_token'] : 'none') : 'none';
                                $post_creator_span = !empty($post_creator_info) ? '<span class="type_span type_'.$post_creator_type.'">'.$post_creator_name.'</span>' : '-';
                                //$post_creator_url = 'admin/edit-users/editor/user_id='.$post_creator_id;
                                if (!$post_creator_disabled){
                                    $post_creator_url = $this_post_page_baseurl.'&subaction=search&post_creator='.$post_creator_id;
                                    if ($post_creator_is_active){ $post_creator_url = $this_post_page_baseurl.'&subaction=search'; }
                                    $post_creator_link = '<a class="link sublink" href="'.$post_creator_url.'">'.$post_creator_name.'</a>';
                                } else {
                                    $post_creator_link = '<span>'.$post_creator_name.'</span>';
                                }

                                // Collect information about the post target
                                $post_target_id = !empty($post_data['post_target']) ? $post_data['post_target'] : 0;
                                $post_target_info = !empty($post_target_id) && !empty($community_users_index[$post_target_id]) ? $community_users_index[$post_target_id] : false;
                                $post_target_is_active = !empty($search_data['post_target']) && $search_data['post_target'] === $post_target_id ? true : false;
                                $post_target_name = '-';
                                $post_target_disabled = false;
                                if ($post_target_id < 1){ $post_target_name = 'Deleted'; $post_target_disabled = true; }
                                elseif ($post_target_id === MMRPG_SETTINGS_GUEST_ID){ $post_target_name = 'Guest'; $post_target_disabled = true; }
                                elseif (empty($post_target_info)){ $post_target_name = 'Unknown (ID: '.$post_target_id.')'; }
                                else { $post_target_name = !empty($post_target_info['user_name_public']) ? $post_target_info['user_name_public'] : $post_target_info['user_name']; }
                                $post_target_type = !empty($post_target_info) ? (!empty($post_target_info['user_colour_token']) ? $post_target_info['user_colour_token'] : 'none') : 'none';
                                $post_target_span = !empty($post_target_info) ? '<span class="type_span type_'.$post_target_type.'">'.$post_target_name.'</span>' : '-';
                                //$post_target_url = 'admin/edit-users/editor/user_id='.$post_target_id;
                                if (!$post_target_disabled){
                                    $post_target_url = $this_post_page_baseurl.'&subaction=search&post_target='.$post_target_id;
                                    if ($post_target_is_active){ $post_target_url = $this_post_page_baseurl.'&subaction=search'; }
                                    $post_target_link = '<a class="link sublink" href="'.$post_target_url.'">'.$post_target_name.'</a>';
                                } else {
                                    $post_target_link = '<span>'.$post_target_name.'</span>';
                                }

                                // Collect and format the created and modified dates for this post
                                $post_date_created = !empty($post_data['post_date']) ? date('Y-m-d', $post_data['post_date']) : '-';
                                $post_date_created_full = !empty($post_data['post_date']) ? str_replace('@', 'at', date('Y-m-d @ g:s a', $post_data['post_date'])) : '-';
                                $post_date_modified = !empty($post_data['post_mod_date']) ? date('Y-m-d', $post_data['post_mod_date']) : '-';
                                $post_date_modified_full = !empty($post_data['post_mod_date']) ? str_replace('@', 'at', date('Y-m-d @ g:s a', $post_data['post_mod_date'])) : '-';

                                // Generate icon markup for the various flags based on status
                                $post_flag_deleted = !empty($post_data['post_deleted']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $post_flag_locked = !empty($post_data['post_locked']) ? '<i class="fas fa-lock"></i>' : '-';
                                $post_flag_sticky = !empty($post_data['post_sticky']) ? '<i class="fas fa-thumbtack"></i>' : '-';

                                // Generate the edit and view URLs for this post so we can link to it
                                $post_edit_url = $this_post_page_baseurl.'editor/post_id='.$post_id;
                                $post_view_url = 'community/'.$category_token.'/'.$thread_id.'/'.$thread_token.'/#'.$post_id;

                                // Generate the name link for this post, the most visible part
                                $post_body_link = '<a class="link" href="'.$post_edit_url.'">'.$post_body_preview.'</a>';
                                if (!empty($post_body_icons)){ $post_body_link = $post_body_icons.' '.$post_body_link; }
                                if (!empty($post_data['post_deleted'])){ $post_body_link = '<del>'.$post_body_link.'</del>'; }

                                // Generate the post links now that we have everything set and ready
                                $post_actions = '';
                                if (empty($post_data['post_deleted'])){
                                    $post_actions .= '<a class="link view" href="'.$post_view_url.'" target="_blank"><span>view</span></a>';
                                }
                                $post_actions .= '<a class="link edit" href="'.$post_edit_url.'"><span>edit</span></a>';
                                if (empty($post_data['post_protected'])){
                                    $post_actions .= '<a class="link delete" data-delete="posts" data-post-id="'.$post_id.'"><span>delete</span></a>';
                                }

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$post_id.'</div></td>'.PHP_EOL;
                                    if ($this_post_class === 'public'){
                                        echo '<td class="body"><div class="wrap">'.$post_body_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="creator"><div class="wrap" title="'.$post_creator_name.'">'.$post_creator_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="thread"><div class="wrap" title="'.$post_thread_name.'">'.$post_thread_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="category"><div class="wrap">'.$post_category_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="date created"><div class="wrap" title="'.$post_date_created_full.'">'.$post_date_created_full.'</div></td>'.PHP_EOL;
                                    } elseif ($this_post_class === 'private'){
                                        echo '<td class="body"><div class="wrap">'.$post_body_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="creator"><div class="wrap" title="'.$post_creator_name.'">'.$post_creator_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="thread"><div class="wrap" title="'.$post_thread_name.'">'.$post_thread_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="target"><div class="wrap" title="'.$post_target_name.'">'.$post_target_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="date created"><div class="wrap" title="'.$post_date_created_full.'">'.$post_date_created_full.'</div></td>'.PHP_EOL;
                                    } else {
                                        echo '<td class="body"><div class="wrap">'.$post_body_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="creator"><div class="wrap" title="'.$post_creator_name.'">'.$post_creator_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="target"><div class="wrap" title="'.$post_target_name.'">'.$post_target_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="thread"><div class="wrap" title="'.$post_thread_name.'">'.$post_thread_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="category"><div class="wrap">'.$post_category_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="date created"><div class="wrap" title="'.$post_date_created_full.'">'.$post_date_created.'</div></td>'.PHP_EOL;
                                        echo '<td class="flag deleted"><div>'.$post_flag_deleted.'</div></td>'.PHP_EOL;

                                    }
                                    echo '<td class="actions"><div>'.$post_actions.'</div></td>'.PHP_EOL;
                                echo '</tr>'.PHP_EOL;

                            }

                            // Unset the temp users index as we only needed it here
                            unset($community_users_index);

                            ?>
                        </tbody>
                    </table>

                </div>

            <? endif; ?>

            <?

            //echo('<pre>$search_query = '.(!empty($search_query) ? htmlentities($search_query, ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
            //echo('<pre>$search_results = '.print_r($search_results, true).'</pre>');

            ?>

        <? endif; ?>

        <?
        if ($sub_action == 'editor'
            && isset($_GET['post_id'])
            ){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_none" data-auto="field-type" data-field-type="post_type,post_type2">
                        <i class="fas <?= $this_post_class === 'private' ? 'fa-envelope' : 'fa-comment-alt' ?>"></i>
                        <i class="fas fa-stream"></i>
                        <span class="title"><?= !empty($post_name_display) ? 'Edit '.ucfirst($this_post_class_name).' &quot;'.$post_name_display.'&quot;' : 'Create New '.ucfirst($this_post_class_name) ?></span>
                        <?

                        // If the post is published, generate and display a preview link
                        if (!empty($post_data['post_flag_deleted'])){
                            //$preview_link = 'database/posts/';
                            //$preview_link .= $thread_data['thread_token'].'/';
                            //echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            //echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>


                    <?php
                    // Collect posts for this thread if any exist (we need the list and count for later)
                    $filter_array = array('thread_id' => $post_thread_data['thread_id']);
                    $sorting_array = array('posts.post_date' => 'ASC');
                    $community_thread_posts_index = cms_thread_post::get_community_thread_posts_index($filter_array, null, $sorting_array, true);
                    $community_thread_posts_array = array_values($community_thread_posts_index);
                    $community_thread_posts_count = count($community_thread_posts_array);
                    ?>

                    <? if (!$post_data_is_new){ ?>
                        <div class="editor-tabs" data-tabgroup="post">
                            <a class="tab active" data-tab="basic">Basic</a><span></span>
                            <a class="tab" data-tab="thread">View in Context</a><span></span>
                            <a class="tab" data-tab="spacer">&nbsp;</a><span></span>
                        </div>
                    <? } ?>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit-<?= $this_post_parentclass_name.'-'.$this_post_xclass_name ?>" />
                        <input type="hidden" name="subaction" value="editor" />

                        <div class="editor-panels" data-tabgroup="post">

                            <div class="panel active" data-tab="basic">

                                <div class="field">
                                    <strong class="label"><?= $this_post_class_name_uc ?> ID</strong>
                                    <input type="hidden" name="post_id" value="<?= $post_data['post_id'] ?>" />
                                    <input class="textbox" type="text" name="post_id" value="<?= $post_data['post_id'] ?>" disabled="disabled" />
                                </div>

                                <div class="field">
                                    <div class="label"><?= $this_post_class_name_uc ?> <?= $this_post_creator_label_uc ?></div>
                                    <? $current_value = !empty($post_data['user_id']) ? $post_data['user_id'] : ''; ?>
                                    <select class="select" name="user_id">
                                        <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $user_options_markup) ?>
                                    </select><span></span>
                                </div>

                                <? if ($this_post_class === 'private'){ ?>
                                    <input type="hidden" name="category_id" value="<?= $post_data['category_id'] ?>" />
                                    <div class="field">
                                        <div class="label"><?= $this_post_class_name_uc ?> <?= $this_post_target_label_uc ?></div>
                                        <? $current_value = !empty($post_data['post_target']) ? $post_data['post_target'] : ''; ?>
                                        <select class="select" name="post_target">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $user_options_markup) ?>
                                        </select><span></span>
                                    </div>
                                <? } elseif ($this_post_class === 'public'){ ?>
                                    <div class="field">
                                        <div class="label"><?= $this_post_class_name_uc ?> Category</div>
                                        <? $current_value = !empty($post_data['category_id']) ? $post_data['category_id'] : ''; ?>
                                        <select class="select" name="category_id">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $category_options_markup) ?>
                                        </select><span></span>
                                    </div>
                                <? } ?>

                                <?
                                // Collect a temporary index of threads to be used below
                                $filter_array = array();
                                $filter_array['category_kind'] = $this_post_class;
                                $filter_array['category_id'] = $post_data['category_id'];
                                $community_threads_index = cms_thread::get_community_threads_index($filter_array, null, null, true);
                                // Pre-generate a list of all threads so we can re-use it over and over
                                $thread_options_markup = cms_thread::generate_thread_options_markup($community_threads_index);
                                $thread_options_count = count($community_threads_index);
                                ?>

                                <div class="field">
                                    <div class="label"><?= $this_post_class_name_uc ?> <?= $this_post_parentclass_name_uc ?></div>
                                    <? $current_value = !empty($post_data['thread_id']) ? $post_data['thread_id'] : ''; ?>
                                    <select class="select" name="thread_id">
                                        <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $thread_options_markup) ?>
                                    </select><span></span>
                                </div>

                                <div class="field litepicker">
                                    <div class="label">
                                        <strong><?= $this_post_class_name_uc ?> Date</strong>
                                    </div>
                                    <input class="textbox" type="datetime-local" name="post_date" value="<?= !empty($post_data['post_date']) ? date('Y-m-d', $post_data['post_date']).'T'.date('H:i', $post_data['post_date']) : '' ?>" maxlength="128" />
                                </div>

                                <? if (!$post_data_is_new){ ?>

                                    <hr />

                                    <div class="field fullsize">
                                        <div class="label">
                                            <strong><?= $this_post_class_name_uc ?> <?= $this_post_class === 'public' ? 'Text' : 'Body' ?></strong>
                                            <em><?= $this_post_class_name ?> content containing the bulk of the post</em>
                                        </div>
                                        <textarea class="textarea" name="post_body" rows="30"><?= htmlentities($post_data['post_body'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Custom Avatar Frame</strong>
                                            <em>optional</em>
                                        </div>
                                        <select class="select" name="post_frame">
                                            <option value="" <?= empty($post_data['post_frame']) ? 'selected="selected"' : '' ?>>-</option>
                                            <?
                                            $temp_frames_index = explode('|', 'base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2');
                                            foreach ($temp_frames_index AS $key => $label){
                                                $value = str_pad($key, 2, '0', STR_PAD_LEFT);
                                                $label = ucfirst($label);
                                                $selected = $post_data['post_frame'] === $value ? 'selected="selected"' : '';
                                                echo('<option value="'.$value.'" title="'.$label.'" '.$selected.'>'.$label.'</option>');
                                            } ?>
                                        </select><span></span>
                                    </div>

                                    <hr />

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>IPv4 Address</strong>
                                            <em>address of user who first created <?= $this_post_class_name ?></em>
                                        </div>
                                        <?
                                        // Only keep the last ten IP addresses to prevent var overflow
                                        $user_ip_addresses = $post_data['user_ip'];
                                        $user_ip_addresses = !empty($user_ip_addresses) ? explode(',', $user_ip_addresses) : array($user_ip_addresses);
                                        $user_ip_addresses = !empty($user_ip_addresses) ? array_map('trim', $user_ip_addresses) : array();
                                        if (count($user_ip_addresses) > 10){ $user_ip_addresses = array_slice($user_ip_addresses, -10, 10); }
                                        $print_user_ip_addresses = implode(', ', $user_ip_addresses);
                                        $save_user_ip_addresses = implode(',', $user_ip_addresses);
                                        ?>
                                        <input class="hidden" type="hidden" name="user_ip" value="<?= $save_user_ip_addresses ?>" maxlength="256" />
                                        <input class="textbox" type="text" maxlength="256" disabled="disabled" value="<?= htmlentities($print_user_ip_addresses, ENT_QUOTES, 'UTF-8', true) ?>" />
                                    </div>

                                <? } ?>

                            </div>

                            <div class="panel active" data-tab="thread">

                                <div class="field fullsize">
                                    <strong class="label"><?= 'Initial '.$this_post_parentclass_name_uc.' w/ '.$community_thread_posts_count.' '.($community_thread_posts_count === 1 ? $this_post_class_name_uc : $this_post_xclass_name_uc) ?></strong>
                                    <div class="posts-list">
                                        <ul>
                                            <?
                                            $thread_id = $post_thread_data['thread_id'];
                                            $thread_author = $community_users_index[$post_thread_data['user_id']];
                                            $thread_author_username = $thread_author['user_name_clean'];
                                            $thread_author_url = 'admin/edit-users/editor/user_id=' . $post_thread_data['user_id'];
                                            $thread_body = htmlspecialchars($post_thread_data['thread_body']);
                                            $post_thread_edit_url = $this_post_thread_page_baseurl . 'editor/thread_id=' . $thread_id;
                                            $post_thread_view_url = !empty($post_thread_data['thread_url']) ? $post_thread_data['thread_url'] : '';
                                            ?>
                                            <li class="<?= !$post_thread_data['thread_published'] ? 'deleted' : '' ?>">
                                                <div class="post-author">
                                                    By: <a href="<?= $thread_author_url ?>" class="author"><?= $thread_author_username ?></a>
                                                </div>
                                                <div class="post-date">
                                                    On: <ins><?= !empty($post_thread_data['thread_date']) ? str_replace('@', 'at', date('Y-m-d @ g:s a', $post_thread_data['thread_date'])) : '-' ?></ins>
                                                </div>
                                                <div class="post-key">
                                                    <ins>Initial <?= $this_post_parentclass_name_uc ?></ins>
                                                </div>
                                                <div class="post-actions">
                                                    <a href="<?= $post_thread_edit_url ?>"><i class="fas fa-pencil-alt"></i><strong>edit in admin</strong></a>
                                                    <? if ($this_post_class == 'public' && $post_thread_data['thread_published'] && !empty($post_thread_view_url)){ ?>
                                                        <a href="<?= $post_thread_view_url ?>" target="_blank"><i class="fas fa-external-link-alt"></i><strong>view on site</strong></a>
                                                    <? } ?>
                                                </div>
                                                <div class="post-body">
                                                    <?= $thread_body ?>
                                                </div>
                                            </li>
                                            <?php
                                            // If threads were found, we should print them out now
                                            if (!empty($community_thread_posts_array)){
                                                $post_num = 0;
                                                // If this post has been deleted, it's not going to be in the data and we have to re-add it manually
                                                if ($post_data['post_deleted']){
                                                    $insert_post_info = cms_thread_post::get_thread_post_info($post_data['post_id'], true);
                                                    $community_thread_posts_array[] = $insert_post_info;
                                                    usort($community_thread_posts_array, function($a, $b) { return $a['post_date'] > $b['post_date']; });
                                                }
                                                foreach ($community_thread_posts_array as $post) {
                                                    $post_num++;
                                                    $post_id = $post['post_id'];
                                                    //if ($post_id !== $post_data['post_id']){ echo('<li></li>'); continue; }
                                                    $post_body = htmlspecialchars($post['post_body']);
                                                    $post_edit_url = $this_post_thread_page_baseurl . 'editor/post_id=' . $post_id;
                                                    $post_view_url = !empty($post['post_url']) ? $post['post_url'] : '';
                                                    $post_author_username = htmlspecialchars($post['author_name']);
                                                    $post_author_url = 'admin/edit-users/editor/user_id=' . $post['author_id'];
                                                    ?>
                                                    <li class="<?= $post_id === $post_data['post_id'] ? 'focus ' : ''?><?= !$post_thread_data['thread_published'] || $post['post_deleted'] ? 'deleted ' : ''?>">
                                                        <div class="post-author">
                                                            By: <a href="<?= $post_author_url ?>" class="author"><?= $post_author_username ?></a>
                                                        </div>
                                                        <div class="post-date">
                                                            On: <ins><?= !empty($post['post_date']) ? str_replace('@', 'at', date('Y-m-d @ g:s a', $post['post_date'])) : '-' ?></ins>
                                                        </div>
                                                        <div class="post-key">
                                                            <?= $this_post_class_name_uc ?> No. <ins><?= (!$post['post_deleted'] ? $post_num : '?').' of '.$community_thread_posts_count ?></ins>
                                                        </div>
                                                        <div class="post-actions">
                                                            <? if ($post_id !== $post_data['post_id']){ ?>
                                                                <a href="<?= $post_edit_url ?>"><i class="fas fa-pencil-alt"></i><strong>edit in admin</strong></a>
                                                            <? } ?>
                                                            <? if ($this_post_class == 'public' && !$post['post_deleted'] && !empty($post_view_url)){ ?>
                                                                <a href="<?= $post_view_url ?>" target="_blank"><i class="fas fa-external-link-alt"></i><strong>view on site</strong></a>
                                                            <? } ?>
                                                        </div>
                                                        <div class="post-body">
                                                            <?= $post_body ?>
                                                        </div>
                                                    </li>
                                                    <?
                                                    if ($post['post_deleted']){ $post_num--; }
                                                }
                                            } else {
                                                ?>
                                                <li>
                                                    &hellip;
                                                </li>
                                                <?
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>

                            </div>



                        </div>

                        <hr />

                        <? if (!$post_data_is_new){ ?>

                            <div class="options">

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Deleted</strong>
                                        <input type="hidden" name="post_deleted" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="post_deleted" value="1" <?= !empty($post_data['post_deleted']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_post_class_name ?> can no longer be viewed</p>
                                </div>

                            </div>

                            <hr />

                        <? } ?>

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="<?= $post_data_is_new ? 'Create '.$this_post_class_name_uc : 'Save Changes' ?>" />
                                <? if (!$post_data_is_new && empty($post_data['post_flag_protected'])){ ?>
                                    <input class="button delete" type="button" value="Delete <?= $this_post_class_name_uc ?>" data-delete="posts" data-post-id="<?= $post_data['post_id'] ?>" />
                                <? } ?>
                            </div>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/scripts/update-image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_post_data = $post_data;
                if (isset($debug_post_data['post_body'])){ $debug_post_data['post_body'] = str_replace(PHP_EOL, '\\n', $debug_post_data['post_body']); }
                echo('<pre style="display: none;">$post_data = '.(!empty($debug_post_data) ? htmlentities(print_r($debug_post_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                ?>

                <?
                $temp_edit_markup = ob_get_clean();
                echo($temp_edit_markup).PHP_EOL;
            }

        }
        ?>

        <?
        if ($sub_action == 'groups'){
            require('edit-groups_markup.php');
        }
        ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>