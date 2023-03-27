<? ob_start(); ?>

    <?

    // TEMP TEMP TEMP
    //$this_thread_class = 'private';
    //$this_thread_xclass = 'private';
    //$this_thread_class_name = 'Private Message';
    //$this_thread_xclass_name = 'Private Messages';
    //$this_thread_class = 'public';
    //$this_thread_xclass = 'public';
    //$this_thread_class_name = 'Public Thread';
    //$this_thread_xclass_name = 'Public Threads';

    // Ensure global thread values for this page are set
    if (!isset($this_thread_class)){ exit('$this_thread_class was undefined!'); }
    if (!isset($this_thread_xclass)){ exit('$this_thread_xclass was undefined!'); }
    if (!isset($this_thread_class_name)){ exit('$this_thread_class_name was undefined!'); }
    if (!isset($this_thread_xclass_name)){ exit('$this_thread_xclass_name was undefined!'); }
    $this_thread_class_name_uc = ucfirst($this_thread_class_name);
    $this_thread_xclass_name_uc = ucfirst($this_thread_xclass_name);
    $this_thread_subclass_name_uc = ucfirst($this_thread_subclass_name);
    $this_thread_xsubclass_name_uc = ucfirst($this_thread_xsubclass_name);

    // Pre-check access permissions before continuing
    $required_permission = $this_thread_class === 'private' ? 'edit-private-messages' : 'edit-community-threads';
    if (!rpg_user::current_user_has_permission('edit-private-messages')){
        $form_messages[] = array('error', 'You do not have permission to edit threads!');
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
        if ($this_thread_class === 'private' && $category_token !== 'personal'){ continue; }
        elseif ($this_thread_class !== 'private' && $category_token === 'personal'){ continue; }
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


    /* -- Form Setup Actions -- */

    // Define a function for exiting a thread edit action
    function exit_thread_edit_action($thread_id = false){
        global $this_thread_page_baseurl;
        if ($thread_id !== false){ $location = $this_thread_page_baseurl.'editor/thread_id='.$thread_id; }
        else { $location = $this_thread_page_baseurl.'search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = $this_thread_page_basename.' | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['thread_id'])){

        // Collect form data for processing
        $delete_data['thread_id'] = !empty($_GET['thread_id']) && is_numeric($_GET['thread_id']) ? trim($_GET['thread_id']) : '';

        // Let's delete all of this thread's data from the database
        if (!empty($delete_data['thread_id'])){
            $delete_data['thread_token'] = $db->get_value("SELECT thread_token FROM mmrpg_threads WHERE thread_id = {$delete_data['thread_id']};", 'thread_token');
            $db->delete('mmrpg_threads', array('thread_id' => $delete_data['thread_id']));
            $db->delete('mmrpg_posts', array('thread_id' => $delete_data['thread_id']));
            $form_messages[] = array('success', 'The requested thread and its comments have been deleted from the database');
            exit_form_action('success');
        } else {
            $form_messages[] = array('success', 'The requested thread does not exist in the database');
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
        $sort_data = array('name' => 'thread_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['thread_id'] = !empty($_GET['thread_id']) && is_numeric($_GET['thread_id']) ? trim($_GET['thread_id']) : '';
        $search_data['thread_name'] = !empty($_GET['thread_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['thread_name']) ? trim(strtolower($_GET['thread_name'])) : '';
        $search_data['thread_body'] = !empty($_GET['thread_body']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['thread_body']) ? trim($_GET['thread_body']) : '';
        $search_data['thread_content'] = !empty($_GET['thread_content']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['thread_content']) ? trim($_GET['thread_content']) : '';
        $search_data['thread_category'] = !empty($_GET['thread_category']) && is_numeric($_GET['thread_category']) ? (int)($_GET['thread_category']) : '';
        $search_data['thread_creator'] = !empty($_GET['thread_creator']) && is_numeric($_GET['thread_creator']) ? (int)($_GET['thread_creator']) : '';
        $search_data['thread_target'] = !empty($_GET['thread_target']) && is_numeric($_GET['thread_target']) ? (int)($_GET['thread_target']) : '';
        $search_data['thread_date_from'] = !empty($_GET['thread_date_from']) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $_GET['thread_date_from']) ? trim($_GET['thread_date_from']) : '';
        $search_data['thread_date_to'] = !empty($_GET['thread_date_to']) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $_GET['thread_date_to']) ? trim($_GET['thread_date_to']) : '';
        $search_data['thread_flag_published'] = isset($_GET['thread_flag_published']) && $_GET['thread_flag_published'] !== '' ? (!empty($_GET['thread_flag_published']) ? 1 : 0) : '';
        $search_data['thread_flag_locked'] = isset($_GET['thread_flag_locked']) && $_GET['thread_flag_locked'] !== '' ? (!empty($_GET['thread_flag_locked']) ? 1 : 0) : '';
        $search_data['thread_flag_sticky'] = isset($_GET['thread_flag_sticky']) && $_GET['thread_flag_sticky'] !== '' ? (!empty($_GET['thread_flag_sticky']) ? 1 : 0) : '';
        cms_admin::object_index_search_data_clean_query_values($search_data, 'thread', $backup_search_data);

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_thread_fields = cms_thread::get_index_fields(true, 'threads');
        $search_query = "SELECT
            {$temp_thread_fields},
            `posts`.`thread_posts` AS `thread_posts`
            FROM `mmrpg_threads` AS `threads`
            LEFT JOIN (
                SELECT `thread_id`, COUNT(*) AS `thread_posts`
                FROM `mmrpg_posts` GROUP BY `thread_id`
                ) AS `posts` ON `posts`.`thread_id` = `threads`.`thread_id`
            WHERE 1=1
            AND `threads`.`thread_token` <> 'thread'
            ";

        // Hide personal messages unless we're explicitly in that mode
        if ($this_thread_class === 'private'){ $search_query .= "AND category_id = 0 "; }
        else { $search_query .= "AND category_id <> 0 "; }
        $search_query .= "AND user_id <> ".MMRPG_SETTINGS_GUEST_ID." ";
        $search_query .= "AND user_id <> ".MMRPG_SETTINGS_TARGET_PLAYERID." ";

        // If the thread ID was provided, we can search by exact match
        if (!empty($search_data['thread_id'])){
            $thread_id = $search_data['thread_id'];
            $search_query .= "AND thread_id = {$thread_id} ";
            $search_results_limit = false;
        }

        // Else if the thread name was provided, we can use wildcards
        if (!empty($search_data['thread_name'])){
            $thread_name = $search_data['thread_name'];
            $thread_name = str_replace(array(' ', '*', '%'), '%', $thread_name);
            $thread_name = preg_replace('/%+/', '%', $thread_name);
            $thread_name = '%'.$thread_name.'%';
            $search_query .= "AND (thread_name LIKE '{$thread_name}' OR thread_token LIKE '{$thread_name}') ";
            $search_results_limit = false;
        }

        // Else if the thread flavour was provided, we can use wildcards
        if (!empty($search_data['thread_body'])){
            $thread_body = $search_data['thread_body'];
            $thread_body = str_replace(array(' ', '*', '%'), '%', $thread_body);
            $thread_body = preg_replace('/%+/', '%', $thread_body);
            $thread_body = '%'.$thread_body.'%';
            $search_query .= "AND (thread_body LIKE '{$thread_body}') ";
            $search_results_limit = false;
        }

        // Else if the thread content was provided, we can use wildcards
        if (!empty($search_data['thread_content'])){
            $thread_content = $search_data['thread_content'];
            $thread_content = str_replace(array(' ', '*', '%'), '%', $thread_content);
            $thread_content = preg_replace('/%+/', '%', $thread_content);
            $thread_content = '%'.$thread_content.'%';
            $search_query .= "AND (
                thread_name LIKE '{$thread_content}'
                OR thread_body LIKE '{$thread_content}'
                ) ";
            $search_results_limit = false;
        }

        // If the thread category was provided, we can search by exact match
        if (!empty($search_data['thread_category'])){
            $category_id = $search_data['thread_category'];
            $search_query .= "AND category_id = {$category_id} ";
            $search_results_limit = false;
        }

        // If the thread creator was provided, we can search by exact match
        if (!empty($search_data['thread_creator'])){
            $creator_id = $search_data['thread_creator'];
            $search_query .= "AND user_id = {$creator_id} ";
            $search_results_limit = false;
        }

        // If the thread target was provided, we can search by exact match
        if (!empty($search_data['thread_target'])){
            $target_id = $search_data['thread_target'];
            $search_query .= "AND thread_target = {$target_id} ";
            $search_results_limit = false;
        }

        // If the thread dates were provided, we can search by range match
        if (!empty($search_data['thread_date_from'])){
            $date_from = $search_data['thread_date_from'];
            $date_from_unix = strtotime($date_from);
            $search_query .= "AND thread_date >= {$date_from_unix} ";
            $search_results_limit = false;
        }
        if (!empty($search_data['thread_date_to'])){
            $date_to = $search_data['thread_date_to'];
            $date_to_unix = strtotime($date_to);
            $search_query .= "AND thread_date <= {$date_to_unix} ";
            $search_results_limit = false;
        }

        // If the thread published flag was provided
        if ($search_data['thread_flag_published'] !== ''){
            $search_query .= "AND thread_published = {$search_data['thread_flag_published']} ";
            $search_results_limit = false;
        }

        // If the thread locked flag was provided
        if ($search_data['thread_flag_locked'] !== ''){
            $search_query .= "AND thread_locked = {$search_data['thread_flag_locked']} ";
            $search_results_limit = false;
        }

        // If the thread sticky flag was provided
        if ($search_data['thread_flag_sticky'] !== ''){
            $search_query .= "AND thread_sticky = {$search_data['thread_flag_sticky']} ";
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "thread_name ASC";
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
        cms_admin::object_index_search_data_restore_backup_data($search_data, 'thread', $backup_search_data);

        // Collect a total number from the database
        $where = 'AND category_id '.($this_thread_class === 'private' ? '=' : '<>').' 0';
        $search_results_total = $db->get_value("SELECT COUNT(thread_id) AS total FROM `mmrpg_threads` WHERE 1=1 {$where};", 'total');

    }

    // If we're in editor mode, we should collect thread info from database
    $thread_data = array();
    $thread_data_is_new = false;
    $editor_data = array();
    if ($sub_action == 'editor'
        && isset($_GET['thread_id'])
        ){

        // Collect form data for processing
        $editor_data['thread_id'] = !empty($_GET['thread_id']) && is_numeric($_GET['thread_id']) ? trim($_GET['thread_id']) : '';


        /* -- Collect Thread Data -- */

        // Collect thread details from the database
        $temp_thread_threads = cms_thread::get_index_fields(true);
        if (!empty($editor_data['thread_id'])){
            $thread_data = $db->get_array("SELECT {$temp_thread_threads} FROM `mmrpg_threads` WHERE thread_id = {$editor_data['thread_id']};");
        } else {

            // Generate temp data structure for the new thread
            $thread_data_is_new = true;
            $admin_id = $_SESSION['admin_id'];
            $thread_data = array(
                'thread_id' => 0,
                'category_id' => 0,
                'user_id' => 0,
                'user_ip' => '',
                'thread_name' => '',
                'thread_token' => '',
                'thread_body' => '',
                'thread_frame' => '',
                'thread_color' => '',
                'thread_date' => time(),
                'thread_mod_date' => 0,
                'thread_mod_user' => 0,
                'thread_published' => 1,
                'thread_locked' => 0,
                'thread_sticky' => 0,
                'thread_views' => 0,
                'thread_votes' => 0,
                'thread_target' => 0
                );

            // Overwrite temp data with any backup data provided
            if (!empty($backup_form_data)){
                foreach ($backup_form_data AS $f => $v){
                    $thread_data[$f] = $v;
                }
            }

        }

        // If thread data could not be found, produce error and exit
        if (empty($thread_data)){ exit_thread_edit_action(); }

        // If this thread is private when we're in public mode, or public when we're in private mode
        if ($thread_data['category_id'] === 0 && $this_thread_class !== 'private'){
            $form_messages[] = array('error', 'This message is private! Redirecting to appropriate editor...');
            redirect_form_action('admin/edit-messages/editor/thread_id='.$thread_data['thread_id']);
        } elseif ($thread_data['category_id'] !== 0 && $this_thread_class !== 'public'){
            $form_messages[] = array('error', 'This thread is public! Redirecting to appropriate editor...');
            redirect_form_action('admin/edit-threads/editor/thread_id='.$thread_data['thread_id']);
        }

        // Collect the thread's name(s) for display
        $thread_name_display = $thread_data['thread_name'];
        if ($thread_data_is_new){ $this_page_tabtitle = 'New '.$this_thread_class_name_uc.' | '.$this_page_tabtitle; }
        else { $this_page_tabtitle = $thread_name_display.' | '.$this_page_tabtitle; }

        // If form data has been submit for this thread, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit-'.$this_thread_xclass_name){

            // COLLECT form data from the request and parse out simple rules

            $old_thread_token = !empty($_POST['old_thread_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_thread_token']) ? trim(strtolower($_POST['old_thread_token'])) : '';

            $form_data['thread_id'] = !empty($_POST['thread_id']) && is_numeric($_POST['thread_id']) ? trim($_POST['thread_id']) : 0;
            $form_data['category_id'] = !empty($_POST['category_id']) && is_numeric($_POST['category_id']) ? trim($_POST['category_id']) : 0;
            $form_data['user_id'] = !empty($_POST['user_id']) && is_numeric($_POST['user_id']) ? trim($_POST['user_id']) : 0;
            $form_data['user_ip'] = !empty($_POST['user_ip']) && preg_match('/^((([0-9]{1,3}\.){3}([0-9]{1,3}){1}),?\s?)+$/i', $_POST['user_ip']) ? trim($_POST['user_ip']) : '';
            $form_data['thread_token'] = !empty($_POST['thread_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['thread_token']) ? trim(strtolower($_POST['thread_token'])) : '';
            $form_data['thread_name'] = !empty($_POST['thread_name']) ? trim(strip_tags($_POST['thread_name'])) : '';
            $form_data['thread_body'] = !empty($_POST['thread_body']) ? trim(strip_tags($_POST['thread_body'])) : '';
            $form_data['thread_frame'] = !empty($_POST['thread_frame']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['thread_frame']) ? trim(strtolower($_POST['thread_frame'])) : '';
            $form_data['thread_colour'] = !empty($_POST['thread_colour']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['thread_colour']) ? trim(strtolower($_POST['thread_colour'])) : '';
            $form_data['thread_date'] = !empty($_POST['thread_date']) && preg_match('/^[-_0-9a-z\.\*\s\:]+$/i', $_POST['thread_date']) ? trim($_POST['thread_date']) : '';
            $form_data['thread_published'] = isset($_POST['thread_published']) && is_numeric($_POST['thread_published']) ? (int)(trim($_POST['thread_published'])) : 0;
            $form_data['thread_locked'] = isset($_POST['thread_locked']) && is_numeric($_POST['thread_locked']) ? (int)(trim($_POST['thread_locked'])) : 0;
            $form_data['thread_sticky'] = isset($_POST['thread_sticky']) && is_numeric($_POST['thread_sticky']) ? (int)(trim($_POST['thread_sticky'])) : 0;
            $form_data['thread_target'] = !empty($_POST['thread_target']) && is_numeric($_POST['thread_target']) ? trim($_POST['thread_target']) : 0;

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If this is a NEW thread, auto-generate the token when not provided
            if ($thread_data_is_new
                && empty($form_data['thread_token'])
                && !empty($form_data['thread_name'])){
                $auto_token = strtolower($form_data['thread_name']);
                $auto_token = preg_replace('/\s+/', '-', $auto_token);
                $auto_token = preg_replace('/[^-a-z0-9]+/i', '', $auto_token);
                $form_data['thread_token'] = $auto_token;
            }

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (!$thread_data_is_new && empty($form_data['thread_id'])){ $form_messages[] = array('error', $this_thread_class_name_uc.' ID was not provided'); $form_success = false; }
            if (empty($form_data['thread_token']) || (!$thread_data_is_new && empty($old_thread_token))){ $form_messages[] = array('error', $this_thread_class_name_uc.' URL was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['thread_name'])){ $form_messages[] = array('error', $this_thread_class_name_uc.' '.($this_thread_class === 'public' ? 'Title' : 'Subject').' was not provided or was invalid'); $form_success = false; }
            if ($this_thread_class === 'public'){
                if (empty($form_data['category_id'])){ $form_messages[] = array('error', $this_thread_class_name_uc.' Category was not provided or was invalid'); $form_success = false; }
                if (!empty($form_data['thread_target'])){ $form_messages[] = array('error', $this_thread_class_name_uc.' Target should not be provided for threads'); $form_success = false; }
            } elseif ($this_thread_class === 'private'){
                if (!empty($form_data['category_id'])){ $form_messages[] = array('error', $this_thread_class_name_uc.' Category should not be provided for messages'); $form_success = false; }
                if (empty($form_data['thread_target'])){ $form_messages[] = array('error', $this_thread_class_name_uc.' Recipient was not provided or was invalid'); $form_success = false; }
            }
            if (empty($form_data['thread_date'])){ $form_messages[] = array('error', $this_thread_class_name_uc.' Date was not provided or was invalid'); $form_success = false; }
            if (!$form_success){ exit_thread_edit_action($form_data['thread_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (!$thread_data_is_new && empty($form_data['thread_body'])){ $form_messages[] = array('warning', $this_thread_class_name_uc.' Body was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            // Reformat the provided date into a unix string, provided it's valid
            if (!empty($form_data['thread_date'])
                && strtotime($form_data['thread_date'])){
                $form_data['thread_date'] = strtotime($form_data['thread_date']);
            }

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$thread_data = '.print_r($thread_data, true).'</pre>');
            //foreach ($form_data AS $key => $value){ if ($form_data[$key] != $thread_data[$key]){ $form_messages[] = array('warning', 'Key '.$key.' did not pass the corruption check! <br /> ('.$form_data[$key].' != '.$thread_data[$key].')'); } }
            //exit_thread_edit_action($form_data['thread_id']);

            // Make a copy of the update data sans the thread ID
            $update_data = $form_data;
            unset($update_data['thread_id']);

            // If this is a new thread we insert, otherwise we update the existing
            if ($thread_data_is_new){

                // Update the main database index with changes to this thread's data
                $insert_results = $db->insert('mmrpg_threads', $update_data);

                // If we made it this far, the update must have been a success
                if ($insert_results !== false){ $form_success = true; $form_messages[] = array('success', $this_thread_class_name_uc.' data was created successfully!'); }
                else { $form_success = false; $form_messages[] = array('error', $this_thread_class_name_uc.' data could not be created...'); }

                // If the form was a success, collect the new ID for the redirect
                if ($form_success){
                    $new_thread_id = $db->get_value("SELECT MAX(thread_id) AS max FROM `mmrpg_threads`;", 'max');
                    $form_data['thread_id'] = $new_thread_id;
                }

            } else {

                // Update the main database index with changes to this thread's data
                $update_results = $db->update('mmrpg_threads', $update_data, array('thread_id' => $form_data['thread_id']));

                // If we made it this far, the update must have been a success
                if ($update_results !== false){ $form_messages[] = array('success', $this_thread_class_name_uc.' data was updated successfully!'); }
                else { $form_messages[] = array('error', $this_thread_class_name_uc.' data could not be updated...'); }

            }

            // If successful, we need to update the JSON file
            if ($form_success){
                if ($thread_data_is_new){ $thread_data['thread_id'] = $new_thread_id; }
            }

            // We're done processing the form, we can exit
            if (empty($form_data['thread_id'])){ exit_thread_edit_action(false); }
            else { exit_thread_edit_action($form_data['thread_id']); }

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }

    /*
    // If we're in groups mode, we need to preset vars and then include common file
    $object_group_kind = 'thread';
    $object_group_class = 'thread';
    $object_group_editor_url = 'admin/edit-'.$this_thread_xclass_name.'/groups/';
    $object_group_editor_name = 'Thread Groups';
    if ($sub_action == 'groups'){
        require('edit-groups_actions.php');
    }
    */

    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="<?= $this_thread_page_baseurl ?>"><?= $this_thread_page_basename ?></a>
        <? if ($sub_action == 'editor' && !empty($thread_data)): ?>
            &raquo; <a href="<?= $this_thread_page_baseurl ?>editor/thread_id=<?= $thread_data['thread_id'] ?>"><?= !empty($thread_name_display) ? $thread_name_display : 'New '.$this_thread_class_name_uc ?></a>
        <? elseif ($sub_action == 'groups'): ?>
            &raquo; <a href="<?= $object_group_editor_url ?>"><?= $object_group_editor_name ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-community edit-threads edit-<?= $this_thread_xclass_name ?>" data-baseurl="<?= $this_thread_page_baseurl ?>" data-object="thread" data-xobject="threads">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search <?= $this_thread_xclass_name_uc ?></h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <input type="hidden" name="subaction" value="search" />

                    <div class="field">
                        <strong class="label">By ID</strong>
                        <input class="textbox" type="text" name="thread_id" value="<?= !empty($search_data['thread_id']) ? $search_data['thread_id'] : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By <?= $this_thread_class_name === 'message' ? 'Subject' : 'Title' ?></strong>
                        <input class="textbox" type="text" name="thread_name" placeholder="" value="<?= !empty($search_data['thread_name']) ? htmlentities($search_data['thread_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By <?= $this_thread_class_name === 'message' ? 'Body' : 'Text' ?></strong>
                        <input class="textbox" type="text" name="thread_body" placeholder="" value="<?= !empty($search_data['thread_body']) ? htmlentities($search_data['thread_body'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Content</strong>
                        <input class="textbox" type="text" name="thread_content" placeholder="" value="<?= !empty($search_data['thread_content']) ? htmlentities($search_data['thread_content'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <? if ($this_thread_class === 'public'){ ?>
                        <div class="field">
                            <strong class="label">By Category</strong>
                            <select class="select" name="thread_category"><option value=""></option><?
                                foreach ($community_categories_index AS $category_id => $category_info){
                                    $option_label = $category_info['category_name'];
                                    ?><option value="<?= $category_id ?>"<?= !empty($search_data['thread_category']) && $search_data['thread_category'] === $category_id ? ' selected="selected"' : '' ?>><?= $option_label ?></option><?
                                    } ?>
                            </select><span></span>
                        </div>
                    <? } ?>

                    <div class="field">
                        <strong class="label">By Creator</strong>
                        <select class="select" name="thread_creator"><option value=""></option><?
                            foreach ($community_users_index AS $creator_id => $creator_info){
                                $option_label = $creator_info['user_name'];
                                if (!empty($creator_info['user_name_public']) && $creator_info['user_name_public'] !== $creator_info['user_name']){ $option_label = $creator_info['user_name_public'].' ('.$option_label.')'; }
                                ?><option value="<?= $creator_id ?>"<?= !empty($search_data['thread_creator']) && $search_data['thread_creator'] === $creator_id ? ' selected="selected"' : '' ?>><?= $option_label ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <? if ($this_thread_class === 'private'){ ?>
                        <div class="field">
                            <strong class="label">By Target</strong>
                            <select class="select" name="thread_target"><option value=""></option><?
                                foreach ($community_users_index AS $creator_id => $creator_info){
                                    $option_label = $creator_info['user_name'];
                                    if (!empty($creator_info['user_name_public']) && $creator_info['user_name_public'] !== $creator_info['user_name']){ $option_label = $creator_info['user_name_public'].' ('.$option_label.')'; }
                                    ?><option value="<?= $creator_id ?>"<?= !empty($search_data['thread_target']) && $search_data['thread_target'] === $creator_id ? ' selected="selected"' : '' ?>><?= $option_label ?></option><?
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
                            <input class="textbox" type="date" name="thread_date_from" value="<?= !empty($search_data['thread_date_from']) ? htmlentities($search_data['thread_date_from'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                        </div>
                        <div class="field autosize">
                            <input class="textbox" type="date" name="thread_date_to" value="<?= !empty($search_data['thread_date_to']) ? htmlentities($search_data['thread_date_to'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                        </div>
                    </div>

                    <div class="field fullsize has5cols flags">
                    <?
                    $flag_names = array();
                    if ($this_thread_class === 'public'){
                        $flag_names['published'] = array('icon' => 'fas fa-check-square', 'yes' => 'Published', 'no' => 'Unpublished');
                        $flag_names['locked'] = array('icon' => 'fas fa-lock', 'yes' => 'Locked', 'no' => 'Not Locked');
                        $flag_names['sticky'] = array('icon' => 'fas fa-thumbtack', 'yes' => 'Sticky', 'no' => 'Not Sticky');
                    } elseif ($this_thread_class === 'private'){
                        $flag_names['published'] = array('icon' => 'fas fa-check-square', 'yes' => 'Published', 'no' => 'Unpublished');
                    }
                    foreach ($flag_names AS $flag_token => $flag_info){
                        if (isset($flag_info['break'])){ echo('<div class="break"></div>'); continue; }
                        $flag_name = 'thread_flag_'.$flag_token;
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
                        <input class="button reset" type="reset" value="Reset" onclick="javascript:window.location.href='<?= $this_thread_page_baseurl ?>';" />
                        <a class="button new" href="<?= '<?= $this_thread_page_baseurl ?>editor/thread_id=0' ?>">New <?= ucwords($this_thread_class_name) ?></a>
                    </div>

                </form>

            </div>

            <? if (!empty($search_results)): ?>

                <!-- SEARCH RESULTS -->

                <div class="results">

                    <table class="list" style="width: 100%;">
                        <colgroup>
                            <col class="id" width="60" />
                            <? if ($this_thread_class === 'public'){ ?>
                                <col class="name" />
                                <col class="category" width="120" />
                                <col class="creator" width="120" />
                                <col class="count views" width="60" />
                                <col class="count posts" width="60" />
                                <col class="date created" width="80" />
                                <col class="date modified" width="80" />
                            <? } elseif ($this_thread_class === 'private'){ ?>
                                <col class="name" />
                                <col class="body" />
                                <col class="creator" width="110" />
                                <col class="target" width="110" />
                                <col class="count posts" width="60" />
                                <col class="date created" width="80" />
                                <col class="date modified" width="80" />
                            <? } else { ?>
                                <col class="name" />
                                <col class="body" />
                                <col class="category" width="120" />
                                <col class="creator" width="120" />
                                <col class="count views" width="60" />
                                <col class="count posts" width="60" />
                                <col class="date created" width="80" />
                                <col class="date modified" width="80" />
                                <col class="flag published" width="80" />
                                <col class="flag locked" width="75" />
                                <col class="flag sticky" width="70" />
                            <? } ?>
                            <col class="actions" width="130" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('thread_id', 'ID') ?></th>
                                <? if ($this_thread_class === 'public'){ ?>
                                    <th class="name"><?= cms_admin::get_sort_link('thread_name', 'Title') ?></th>
                                    <th class="category"><?= cms_admin::get_sort_link('category_id', 'Category') ?></th>
                                    <th class="creator"><?= cms_admin::get_sort_link('user_id', 'Creator') ?></th>
                                    <th class="count views"><?= cms_admin::get_sort_link('thread_views', 'Views') ?></th>
                                    <th class="count posts"><?= cms_admin::get_sort_link('thread_posts', ucfirst($this_thread_xsubclass_name)) ?></th>
                                    <th class="date created"><?= cms_admin::get_sort_link('thread_date', 'Created') ?></th>
                                    <th class="date modified"><?= cms_admin::get_sort_link('thread_mod_date', 'Updated') ?></th>
                                <? } elseif ($this_thread_class === 'private'){ ?>
                                    <th class="name"><?= cms_admin::get_sort_link('thread_name', 'Subject') ?></th>
                                    <th class="body"><?= cms_admin::get_sort_link('thread_name', 'Body') ?></th>
                                    <th class="creator"><?= cms_admin::get_sort_link('user_id', 'Creator') ?></th>
                                    <th class="target"><?= cms_admin::get_sort_link('thread_target', 'Target') ?></th>
                                    <th class="count posts"><?= cms_admin::get_sort_link('thread_posts', ucfirst($this_thread_xsubclass_name)) ?></th>
                                    <th class="date created"><?= cms_admin::get_sort_link('thread_date', 'Created') ?></th>
                                    <th class="date modified"><?= cms_admin::get_sort_link('thread_mod_date', 'Updated') ?></th>
                                <? } else { ?>
                                    <th class="name"><?= cms_admin::get_sort_link('thread_name', 'Name') ?></th>
                                    <th class="body"><?= cms_admin::get_sort_link('thread_name', 'Body') ?></th>
                                    <th class="category"><?= cms_admin::get_sort_link('category_id', 'Category') ?></th>
                                    <th class="creator"><?= cms_admin::get_sort_link('user_id', 'Creator') ?></th>
                                    <th class="target"><?= cms_admin::get_sort_link('user_id', 'Target') ?></th>
                                    <th class="count views"><?= cms_admin::get_sort_link('thread_views', 'Views') ?></th>
                                    <th class="count posts"><?= cms_admin::get_sort_link('thread_posts', ucfirst($this_thread_xsubclass_name)) ?></th>
                                    <th class="date created"><?= cms_admin::get_sort_link('thread_date', 'Created') ?></th>
                                    <th class="date modified"><?= cms_admin::get_sort_link('thread_mod_date', 'Updated') ?></th>
                                    <th class="flag published"><?= cms_admin::get_sort_link('thread_published', 'Published') ?></th>
                                    <th class="flag locked"><?= cms_admin::get_sort_link('thread_locked', 'Locked') ?></th>
                                    <th class="flag sticky"><?= cms_admin::get_sort_link('thread_sticky', 'Sticky') ?></th>
                                <? } ?>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <? if ($this_thread_class === 'public'){ ?>
                                    <th class="head name"></th>
                                    <th class="head category"></th>
                                    <th class="head creator"></th>
                                    <th class="head count views"></th>
                                    <th class="head count posts"></th>
                                    <th class="head date created"></th>
                                    <th class="head date modified"></th>
                                <? } elseif ($this_thread_class === 'private'){ ?>
                                    <th class="head name"></th>
                                    <th class="head body"></th>
                                    <th class="head creator"></th>
                                    <th class="head target"></th>
                                    <th class="head count posts"></th>
                                    <th class="head date created"></th>
                                    <th class="head date modified"></th>
                                <? } else { ?>
                                    <th class="head name"></th>
                                    <th class="head body"></th>
                                    <th class="head category"></th>
                                    <th class="head creator"></th>
                                    <th class="head target"></th>
                                    <th class="head count views"></th>
                                    <th class="head count posts"></th>
                                    <th class="head date created"></th>
                                    <th class="head date modified"></th>
                                    <th class="head flag published"></th>
                                    <th class="head flag locked"></th>
                                    <th class="head flag sticky"></th>
                                <? } ?>
                                <th class="head count"><?= cms_admin::get_totals_markup() ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <? if ($this_thread_class === 'public'){ ?>
                                    <td class="foot name"></td>
                                    <td class="foot category"></td>
                                    <td class="foot creator"></td>
                                    <td class="foot count views"></td>
                                    <td class="foot count posts"></td>
                                    <td class="foot date created"></td>
                                    <td class="foot date modified"></td>
                                <? } elseif ($this_thread_class === 'private'){ ?>
                                    <td class="foot name"></td>
                                    <td class="foot body"></td>
                                    <td class="foot creator"></td>
                                    <td class="foot target"></td>
                                    <td class="foot count posts"></td>
                                    <td class="foot date created"></td>
                                    <td class="foot date modified"></td>
                                <? } else { ?>
                                    <td class="foot name"></td>
                                    <td class="foot category"></td>
                                    <td class="foot creator"></td>
                                    <td class="foot count views"></td>
                                    <td class="foot count posts"></td>
                                    <td class="foot date created"></td>
                                    <td class="foot date modified"></td>
                                    <td class="foot flag published"></td>
                                    <td class="foot flag locked"></td>
                                    <td class="foot flag sticky"></td>
                                <? } ?>
                                <td class="foot count"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?

                            // Loop through collected results and display them to the admin
                            foreach ($search_results AS $key => $thread_data){

                                //error_log('$thread_data = '.print_r($thread_data, true));

                                // Collect the basic name, token, and ID for the thread
                                $thread_id = $thread_data['thread_id'];
                                $thread_token = $thread_data['thread_token'];
                                $thread_name = $thread_data['thread_name'];
                                $thread_body_preview = trim($thread_data['thread_body']);
                                if (strlen($thread_body_preview) > 100){ $thread_body_preview = substr($thread_body_preview, 0, 100).'&hellip;'; }

                                // Generate some icons for the name to represent statuses (locked, sticky, etc.)
                                $thread_name_icons = array();
                                if (!empty($thread_data['thread_locked'])){ $thread_name_icons[] = '<i class="fas fa-lock"></i>'; }
                                if (!empty($thread_data['thread_sticky'])){ $thread_name_icons[] = '<i class="fas fa-thumbtack"></i>'; }
                                $thread_name_icons = implode(' ', $thread_name_icons).(!empty($thread_name_icons) ? ' ' : '');

                                // Collect information about the thread category
                                $category_id = $thread_data['category_id'];
                                $category_is_active = !empty($search_data['thread_category']) && $search_data['thread_category'] === $category_id ? true : false;
                                $category_info = !empty($community_categories_index[$category_id]) ? $community_categories_index[$category_id] : array();
                                //$category_name = !empty($category_info['category_token']) ? ucfirst($category_info['category_token']) : 'Unknown';
                                $category_name = !empty($category_info['category_name']) ? $category_info['category_name'] : 'Unknown';
                                $category_token = !empty($category_info['category_token']) ? $category_info['category_token'] : 'unknown';
                                $thread_category_url = $this_thread_page_baseurl.'&subaction=search&thread_category='.$category_id;
                                if ($category_is_active){ $thread_category_url = $this_thread_page_baseurl.'&subaction=search'; }
                                $thread_category_link = '<a class="link sublink" href="'.$thread_category_url.'">'.$category_name.'</a>';

                                // Collect information about the thread creator
                                $thread_creator_id = !empty($thread_data['user_id']) ? $thread_data['user_id'] : 0;
                                $thread_creator_info = !empty($thread_creator_id) && !empty($community_users_index[$thread_creator_id]) ? $community_users_index[$thread_creator_id] : false;
                                $thread_creator_is_active = !empty($search_data['thread_creator']) && $search_data['thread_creator'] === $thread_creator_id ? true : false;
                                $thread_creator_name = '-';
                                $thread_creator_disabled = false;
                                if ($thread_creator_id < 1){ $thread_creator_name = 'Deleted'; $thread_creator_disabled = true; }
                                elseif ($thread_creator_id === MMRPG_SETTINGS_GUEST_ID){ $thread_creator_name = 'Guest'; $thread_creator_disabled = true; }
                                elseif ($thread_creator_id === MMRPG_SETTINGS_TARGET_PLAYERID){ $thread_creator_name = 'System'; $thread_creator_disabled = true; }
                                elseif (empty($thread_creator_info)){ $thread_creator_name = 'Unknown (ID: '.$thread_creator_id.')'; }
                                else { $thread_creator_name = !empty($thread_creator_info['user_name_public']) ? $thread_creator_info['user_name_public'] : $thread_creator_info['user_name']; }
                                $thread_creator_type = !empty($thread_creator_info) ? (!empty($thread_creator_info['user_colour_token']) ? $thread_creator_info['user_colour_token'] : 'none') : 'none';
                                $thread_creator_span = !empty($thread_creator_info) ? '<span class="type_span type_'.$thread_creator_type.' nowrap">'.$thread_creator_name.'</span>' : '-';
                                //$thread_creator_url = 'admin/edit-users/editor/user_id='.$thread_creator_id;
                                if (!$thread_creator_disabled){
                                    $thread_creator_url = $this_thread_page_baseurl.'&subaction=search&thread_creator='.$thread_creator_id;
                                    if ($thread_creator_is_active){ $thread_creator_url = $this_thread_page_baseurl.'&subaction=search'; }
                                    $thread_creator_link = '<a class="link sublink" href="'.$thread_creator_url.'">'.$thread_creator_name.'</a>';
                                } else {
                                    $thread_creator_link = '<span>'.$thread_creator_name.'</span>';
                                }

                                // Collect information about the thread target
                                $thread_target_id = !empty($thread_data['thread_target']) ? $thread_data['thread_target'] : 0;
                                $thread_target_info = !empty($thread_target_id) && !empty($community_users_index[$thread_target_id]) ? $community_users_index[$thread_target_id] : false;
                                $thread_target_is_active = !empty($search_data['thread_target']) && $search_data['thread_target'] === $thread_target_id ? true : false;
                                $thread_target_name = '-';
                                $thread_target_disabled = false;
                                if ($thread_target_id < 1){ $thread_target_name = 'Deleted'; $thread_target_disabled = true; }
                                elseif ($thread_target_id === MMRPG_SETTINGS_GUEST_ID){ $thread_target_name = 'Guest'; $thread_target_disabled = true; }
                                elseif (empty($thread_target_info)){ $thread_target_name = 'Unknown (ID: '.$thread_target_id.')'; }
                                else { $thread_target_name = !empty($thread_target_info['user_name_public']) ? $thread_target_info['user_name_public'] : $thread_target_info['user_name']; }
                                $thread_target_type = !empty($thread_target_info) ? (!empty($thread_target_info['user_colour_token']) ? $thread_target_info['user_colour_token'] : 'none') : 'none';
                                $thread_target_span = !empty($thread_target_info) ? '<span class="type_span type_'.$thread_target_type.' nowrap">'.$thread_target_name.'</span>' : '-';
                                //$thread_target_url = 'admin/edit-users/editor/user_id='.$thread_target_id;
                                if (!$thread_target_disabled){
                                    $thread_target_url = $this_thread_page_baseurl.'&subaction=search&thread_target='.$thread_target_id;
                                    if ($thread_target_is_active){ $thread_target_url = $this_thread_page_baseurl.'&subaction=search'; }
                                    $thread_target_link = '<a class="link sublink" href="'.$thread_target_url.'">'.$thread_target_name.'</a>';
                                } else {
                                    $thread_target_link = '<span>'.$thread_target_name.'</span>';
                                }

                                // Collect and format the created and modified dates for this thread
                                $thread_date_created = !empty($thread_data['thread_date']) ? date('Y-m-d', $thread_data['thread_date']) : '-';
                                $thread_date_created_full = !empty($thread_data['thread_date']) ? str_replace('@', 'at', date('Y-m-d @ g:s a', $thread_data['thread_date'])) : '-';
                                $thread_date_modified = !empty($thread_data['thread_mod_date']) ? date('Y-m-d', $thread_data['thread_mod_date']) : '-';
                                $thread_date_modified_full = !empty($thread_data['thread_mod_date']) ? str_replace('@', 'at', date('Y-m-d @ g:s a', $thread_data['thread_mod_date'])) : '-';

                                // Generate icon markup for the various flags based on status
                                $thread_flag_published = !empty($thread_data['thread_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $thread_flag_locked = !empty($thread_data['thread_locked']) ? '<i class="fas fa-lock"></i>' : '-';
                                $thread_flag_sticky = !empty($thread_data['thread_sticky']) ? '<i class="fas fa-thumbtack"></i>' : '-';

                                // Generate the edit and view URLs for this thread so we can link to it
                                $thread_edit_url = $this_thread_page_baseurl.'editor/thread_id='.$thread_id;
                                $thread_view_url = 'community/'.$category_token.'/'.$thread_id.'/'.$thread_token.'/';

                                // Collect info about how many thread views
                                $thread_views = !empty($thread_data['thread_views']) ? $thread_data['thread_views'] : 0;
                                $thread_views_formatted = number_format($thread_views, 0, '.', ',');

                                // Collect info about how many thread posts
                                $thread_posts = !empty($thread_data['thread_posts']) ? $thread_data['thread_posts'] : 0;
                                $thread_posts_formatted = number_format($thread_posts, 0, '.', ',');

                                // Generate the name link for this post, the most visible part
                                $thread_name_link = '<a class="link" href="'.$thread_edit_url.'">'.$thread_name.'</a>';
                                if (!empty($thread_name_icons)){ $thread_name_link = $thread_name_icons.' '.$thread_name_link; }
                                if (empty($thread_data['thread_published'])){ $thread_name_link = '<del>'.$thread_name_link.'</del>'; }

                                // Generate the thread links now that we have everything set and ready
                                $thread_actions = '';
                                if (!empty($thread_data['thread_published'])){
                                    $thread_actions .= '<a class="link view" href="'.$thread_view_url.'" target="_blank"><span>view</span></a>';
                                }
                                $thread_actions .= '<a class="link edit" href="'.$thread_edit_url.'"><span>edit</span></a>';
                                if (empty($thread_data['thread_protected'])){
                                    $thread_actions .= '<a class="link delete" data-delete="threads" data-thread-id="'.$thread_id.'"><span>delete</span></a>';
                                }

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$thread_id.'</div></td>'.PHP_EOL;
                                    if ($this_thread_class === 'public'){
                                        echo '<td class="name"><div class="wrap">'.$thread_name_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="category"><div class="wrap">'.$thread_category_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="creator"><div class="wrap" title="'.$thread_creator_name.'">'.$thread_creator_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="count views"><div class="wrap">'.$thread_views_formatted.'</div></td>'.PHP_EOL;
                                        echo '<td class="count posts"><div class="wrap">'.$thread_posts_formatted.'</div></td>'.PHP_EOL;
                                        echo '<td class="date created"><div class="wrap" title="'.$thread_date_created_full.'">'.$thread_date_created.'</div></td>'.PHP_EOL;
                                        echo '<td class="date modified"><div class="wrap" title="'.$thread_date_modified_full.'">'.$thread_date_modified.'</div></td>'.PHP_EOL;
                                    } elseif ($this_thread_class === 'private'){
                                        echo '<td class="name"><div class="wrap">'.$thread_name_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="body"><div class="wrap">'.$thread_body_preview.'</div></td>'.PHP_EOL;
                                        echo '<td class="creator"><div class="wrap" title="'.$thread_creator_name.'">'.$thread_creator_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="target"><div class="wrap" title="'.$thread_target_name.'">'.$thread_target_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="count posts"><div class="wrap">'.$thread_posts_formatted.'</div></td>'.PHP_EOL;
                                        echo '<td class="date created"><div class="wrap" title="'.$thread_date_created_full.'">'.$thread_date_created.'</div></td>'.PHP_EOL;
                                        echo '<td class="date modified"><div class="wrap" title="'.$thread_date_modified_full.'">'.$thread_date_modified.'</div></td>'.PHP_EOL;
                                    } else {
                                        echo '<td class="name"><div class="wrap">'.$thread_name_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="category"><div class="wrap">'.$thread_category_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="creator"><div class="wrap" title="'.$thread_creator_name.'">'.$thread_creator_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="target"><div class="wrap" title="'.$thread_target_name.'">'.$thread_target_link.'</div></td>'.PHP_EOL;
                                        echo '<td class="count views"><div class="wrap">'.$thread_views_formatted.'</div></td>'.PHP_EOL;
                                        echo '<td class="count posts"><div class="wrap">'.$thread_posts_formatted.'</div></td>'.PHP_EOL;
                                        echo '<td class="date created"><div class="wrap" title="'.$thread_date_created_full.'">'.$thread_date_created.'</div></td>'.PHP_EOL;
                                        echo '<td class="date modified"><div class="wrap" title="'.$thread_date_modified_full.'">'.$thread_date_modified.'</div></td>'.PHP_EOL;
                                        echo '<td class="flag published"><div>'.$thread_flag_published.'</div></td>'.PHP_EOL;
                                        echo '<td class="flag locked"><div>'.$thread_flag_locked.'</div></td>'.PHP_EOL;
                                        echo '<td class="flag sticky"><div>'.$thread_flag_sticky.'</div></td>'.PHP_EOL;
                                    }
                                    echo '<td class="actions"><div>'.$thread_actions.'</div></td>'.PHP_EOL;
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
            && isset($_GET['thread_id'])
            ){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= strstr($thread_data['thread_token'], '-subcore') ? str_replace('-subcore', '', $thread_data['thread_token']) : 'none' ?>" data-auto="field-type" data-field-type="thread_type,thread_type2">
                        <i class="fas <?= $this_thread_class === 'private' ? 'fa-envelope' : 'fa-comment-alt' ?>"></i>
                        <span class="title"><?= !empty($thread_name_display) ? 'Edit '.ucfirst($this_thread_class_name).' &quot;'.$thread_name_display.'&quot;' : 'Create New '.ucfirst($this_thread_class_name) ?></span>
                        <?

                        // If the thread is published, generate and display a preview link
                        if (!empty($thread_data['thread_flag_published'])){
                            //$preview_link = 'database/threads/';
                            //$preview_link .= $thread_data['thread_token'].'/';
                            //echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            //echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>

                    <? if (!$thread_data_is_new){ ?>
                        <div class="editor-tabs" data-tabgroup="thread">
                            <a class="tab active" data-tab="basic">Basic</a><span></span>
                            <a class="tab" data-tab="spacer">&nbsp;</a><span></span>
                        </div>
                    <? } ?>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit-<?= $this_thread_xclass_name ?>" />
                        <input type="hidden" name="subaction" value="editor" />

                        <div class="editor-panels" data-tabgroup="thread">

                            <div class="panel active" data-tab="basic">

                                <div class="field">
                                    <strong class="label"><?= $this_thread_class_name_uc ?> ID</strong>
                                    <input type="hidden" name="thread_id" value="<?= $thread_data['thread_id'] ?>" />
                                    <input class="textbox" type="text" name="thread_id" value="<?= $thread_data['thread_id'] ?>" disabled="disabled" />
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong><?= $this_thread_class_name_uc ?> URL</strong>
                                        <em>avoid changing</em>
                                    </div>
                                    <input type="hidden" name="old_thread_token" value="<?= $thread_data['thread_token'] ?>" />
                                    <input class="textbox" type="text" name="thread_token" value="<?= $thread_data['thread_token'] ?>" maxlength="128" />
                                </div>

                                <div class="field">
                                    <strong class="label"><?= $this_thread_class_name_uc ?> <?= $this_thread_class === 'public' ? 'Title' : 'Subject' ?></strong>
                                    <input class="textbox" type="text" name="thread_name" value="<?= $thread_data['thread_name'] ?>" maxlength="128" />
                                </div>

                                <div class="field">
                                    <div class="label"><?= $this_thread_class_name_uc ?> <?= $this_thread_class === 'private' ? 'Sender' : 'Creator' ?></div>
                                    <? $current_value = !empty($thread_data['user_id']) ? $thread_data['user_id'] : ''; ?>
                                    <select class="select" name="user_id">
                                        <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $user_options_markup) ?>
                                    </select><span></span>
                                </div>

                                <? if ($this_thread_class === 'private'){ ?>
                                    <input type="hidden" name="category_id" value="<?= $thread_data['category_id'] ?>" />
                                    <div class="field">
                                        <div class="label"><?= $this_thread_class_name_uc ?> <?= $this_thread_class === 'private' ? 'Recipient' : 'Target' ?></div>
                                        <? $current_value = !empty($thread_data['thread_target']) ? $thread_data['thread_target'] : ''; ?>
                                        <select class="select" name="thread_target">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $user_options_markup) ?>
                                        </select><span></span>
                                    </div>
                                <? } elseif ($this_thread_class === 'public'){ ?>
                                    <div class="field">
                                        <div class="label"><?= $this_thread_class_name_uc ?> Category</div>
                                        <? $current_value = !empty($thread_data['category_id']) ? $thread_data['category_id'] : ''; ?>
                                        <select class="select" name="category_id">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $category_options_markup) ?>
                                        </select><span></span>
                                    </div>
                                <? } ?>

                                <div class="field litepicker">
                                    <div class="label">
                                        <strong><?= $this_thread_class_name_uc ?> Date</strong>
                                    </div>
                                    <input class="textbox" type="datetime-local" name="thread_date" value="<?= !empty($thread_data['thread_date']) ? date('Y-m-d', $thread_data['thread_date']).'T'.date('H:i', $thread_data['thread_date']) : '' ?>" maxlength="128" />
                                </div>

                                <? if (!$thread_data_is_new){ ?>

                                    <hr />

                                    <div class="field fullsize">
                                        <div class="label">
                                            <strong><?= $this_thread_class_name_uc ?> <?= $this_thread_class === 'public' ? 'Text' : 'Body' ?></strong>
                                            <em><?= $this_thread_class_name ?> content containing the bulk of the post</em>
                                        </div>
                                        <textarea class="textarea" name="thread_body" rows="30"><?= htmlentities($thread_data['thread_body'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Custom Avatar Frame</strong>
                                            <em>optional</em>
                                        </div>
                                        <select class="select" name="thread_frame">
                                            <option value="" <?= empty($thread_data['thread_frame']) ? 'selected="selected"' : '' ?>>-</option>
                                            <?
                                            $temp_frames_index = explode('|', 'base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2');
                                            foreach ($temp_frames_index AS $key => $label){
                                                $value = str_pad($key, 2, '0', STR_PAD_LEFT);
                                                $label = ucfirst($label);
                                                $selected = $thread_data['thread_frame'] === $value ? 'selected="selected"' : '';
                                                echo('<option value="'.$value.'" title="'.$label.'" '.$selected.'>'.$label.'</option>');
                                            } ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Custom <?= $this_thread_class_name_uc ?> Colour</strong>
                                            <em>optional</em>
                                        </div>
                                        <select class="select" name="thread_colour">
                                            <option value="" <?= empty($thread_data['thread_colour']) ? 'selected="selected"' : '' ?>>-</option>
                                            <? foreach ($mmrpg_types_index AS $key => $type_info){
                                                if ($type_info['type_token'] === 'type'){ continue; }
                                                //if ($type_info['type_class'] !== 'normal'){ continue; }
                                                //elseif ($type_info['type_token'] === 'copy'){ continue; }
                                                $value = $type_info['type_token'];
                                                $selected = $type_info['type_token'] == $thread_data['thread_colour'] ? 'selected="selected"' : '';
                                                $label = $type_info['type_name'];
                                                echo('<option value="'.$value.'" title="'.$label.'" '.$selected.'>'.$label.'</option>');
                                            } ?>
                                        </select><span></span>
                                    </div>

                                    <hr />

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>IPv4 Address</strong>
                                            <em>address of user who first created <?= $this_thread_class_name ?></em>
                                        </div>
                                        <?
                                        // Only keep the last ten IP addresses to prevent var overflow
                                        $user_ip_addresses = $thread_data['user_ip'];
                                        $user_ip_addresses = !empty($user_ip_addresses) ? explode(',', $user_ip_addresses) : array($user_ip_addresses);
                                        $user_ip_addresses = !empty($user_ip_addresses) ? array_map('trim', $user_ip_addresses) : array();
                                        if (count($user_ip_addresses) > 10){ $user_ip_addresses = array_slice($user_ip_addresses, -10, 10); }
                                        $print_user_ip_addresses = implode(', ', $user_ip_addresses);
                                        $save_user_ip_addresses = implode(',', $user_ip_addresses);
                                        ?>
                                        <input class="hidden" type="hidden" name="user_ip" value="<?= $save_user_ip_addresses ?>" maxlength="256" />
                                        <input class="textbox" type="text" maxlength="256" disabled="disabled" value="<?= htmlentities($print_user_ip_addresses, ENT_QUOTES, 'UTF-8', true) ?>" />
                                    </div>

                                    <? if ($this_thread_class === 'public'){ ?>
                                        <div class="field">
                                            <div class="label">
                                                <strong>Total Views</strong>
                                                <em>times <?= $this_thread_class_name ?> has been viewed</em>
                                            </div>
                                            <input class="textbox" type="text" maxlength="256" disabled="disabled" value="<?= $thread_data['thread_views'] ?>" />
                                        </div>
                                    <? } ?>

                                <? } ?>

                            </div>

                        </div>

                        <hr />

                        <? if (!$thread_data_is_new){ ?>

                            <div class="options">

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Published</strong>
                                        <input type="hidden" name="thread_published" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="thread_published" value="1" <?= !empty($thread_data['thread_published']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_thread_class_name ?> <?= $this_thread_class === 'public' ? 'is visible on the site' : 'can still be viewed' ?></p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Locked</strong>
                                        <input type="hidden" name="thread_locked" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="thread_locked" value="1" <?= !empty($thread_data['thread_locked']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_thread_class_name ?> cannot be responded to</p>
                                </div>

                                <? if ($this_thread_class === 'public'){ ?>
                                    <div class="field checkwrap">
                                        <label class="label">
                                            <strong>Sticky</strong>
                                            <input type="hidden" name="thread_sticky" value="0" checked="checked" />
                                            <input class="checkbox" type="checkbox" name="thread_sticky" value="1" <?= !empty($thread_data['thread_sticky']) ? 'checked="checked"' : '' ?> />
                                        </label>
                                        <p class="subtext">This <?= $this_thread_class_name ?> is pinned to top of page</p>
                                    </div>
                                <? } ?>

                            </div>

                            <hr />

                        <? } ?>

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="<?= $thread_data_is_new ? 'Create '.$this_thread_class_name_uc : 'Save Changes' ?>" />
                                <? if (!$thread_data_is_new && empty($thread_data['thread_flag_protected'])){ ?>
                                    <input class="button delete" type="button" value="Delete <?= $this_thread_class_name_uc ?>" data-delete="threads" data-thread-id="<?= $thread_data['thread_id'] ?>" />
                                <? } ?>
                            </div>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/scripts/update-image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_thread_data = $thread_data;
                if (isset($debug_thread_data['thread_body'])){ $debug_thread_data['thread_body'] = str_replace(PHP_EOL, '\\n', $debug_thread_data['thread_body']); }
                echo('<pre style="display: none;">$thread_data = '.(!empty($debug_thread_data) ? htmlentities(print_r($debug_thread_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

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