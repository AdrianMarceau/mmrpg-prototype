<?

// Require the top file for all admin scripts
require_once('common/top.php');

// Collect request type from $_GET
$request_type = isset($_GET['request']) ? $_GET['request'] : '';
// Validate $request_type
if (!preg_match('/^[a-zA-Z0-9-_]+$/', $request_type)) {
    exit_action('error|Invalid request value');
}

// Proceed based on $request_type
switch ($request_type) {

    // Collect threads if requested
    case 'get-threads': {

        // Collect the category ID and kind if they've been provided
        $category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : -1;
        $category_kind = isset($_GET['kind']) && ($_GET['kind'] === 'public' || $_GET['kind'] === 'private') ? $_GET['kind'] : '';
        if ($category_id === 0 || $category_kind === 'private'){ $category_id = 0; $category_kind = 'private'; }

        // Collect the pagination fields if provided
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : null;
        $offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? (int)$_GET['offset'] : null;

        // Collect the author ID if it's been provided
        $author_id = isset($_GET['author']) && is_numeric($_GET['author']) ? (int)$_GET['author'] : null;

        // Collect the target value if 'kind' is 'private' and the target is provided
        $thread_target = ($category_kind === 'private' && isset($_GET['target']) && is_numeric($_GET['target'])) ? (int)$_GET['target'] : null;

        // Collect the variable for fetching all fields if provided
        $fetch_all_fields = isset($_GET['full']) && $_GET['full'] === 'true' ? true : false;

        // Collect the index of threads given the provided category info
        $filter_array = array();
        $filter_array['category_kind'] = $category_kind;
        if (!empty($category_id) && $category_id > 0){ $filter_array['category_id'] = $category_id; }
        if ($author_id !== null) { $filter_array['user_id'] = $author_id; }
        if ($thread_target !== null) { $filter_array['thread_target'] = $thread_target; }

        // Prepare the pagination array based on provided values
        $pagination_array = array();
        if ($limit !== null) { $pagination_array['limit'] = $limit; }
        if ($offset !== null) { $pagination_array['offset'] = $offset; }

        // Define your sorting criteria
        $sorting_array = array('threads.thread_date' => 'DESC', 'categories.category_order' => 'ASC');

        // Call the function with filters and pagination parameters
        $community_threads_index = cms_thread::get_community_threads_index($filter_array, $pagination_array, $sorting_array, $fetch_all_fields);
        $community_threads_array = array_values($community_threads_index);
        $num_community_threads = count($community_threads_array);

        // Print out the success message now that we have our data and exit
        exit_action('success|found '.$num_community_threads.' threads', $community_threads_array);
        break;

    }

    // Collect thread comments if requested
    case 'get-threads-posts': {

        // Collect the category ID and kind if they've been provided
        $category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : -1;
        $category_kind = isset($_GET['kind']) && ($_GET['kind'] === 'public' || $_GET['kind'] === 'private') ? $_GET['kind'] : '';
        if ($category_id === 0 || $category_kind === 'private'){ $category_id = 0; $category_kind = 'private'; }

        // Collect the thread ID if it's been provided
        $thread_id = isset($_GET['thread']) && is_numeric($_GET['thread']) ? (int)$_GET['thread'] : null;

        // Collect the pagination fields if provided
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : null;
        $offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? (int)$_GET['offset'] : null;

        // Collect the author ID if it's been provided
        $author_id = isset($_GET['author']) && is_numeric($_GET['author']) ? (int)$_GET['author'] : null;

        // Collect the variable for fetching all fields if provided
        $fetch_all_fields = isset($_GET['full']) && $_GET['full'] === 'true' ? true : false;

        // Collect the index of posts given the provided thread and category info
        $filter_array = array();
        $filter_array['category_kind'] = $category_kind;
        if (!empty($category_id) && $category_id > 0){ $filter_array['category_id'] = $category_id; }
        if ($thread_id !== null) { $filter_array['thread_id'] = $thread_id; }
        if ($author_id !== null) { $filter_array['user_id'] = $author_id; }

        // Prepare the pagination array based on provided values
        $pagination_array = array();
        if ($limit !== null) { $pagination_array['limit'] = $limit; }
        if ($offset !== null) { $pagination_array['offset'] = $offset; }

        // Define your sorting criteria
        $sorting_array = array('posts.post_date' => 'ASC');

        // Call the function with filters and pagination parameters
        $community_thread_posts_index = cms_thread_post::get_community_thread_posts_index($filter_array, $pagination_array, $sorting_array, $fetch_all_fields);
        $community_thread_posts_array = array_values($community_thread_posts_index);
        $num_community_thread_posts = count($community_thread_posts_array);

        // Print out the success message now that we have our data and exit
        exit_action('success|found ' . $num_community_thread_posts . ' posts', $community_thread_posts_array);
        break;

    }


    // Undefined request type triggers error
    default: {

        // The request type was undefined so we must break
        exit_action('error|Invalid request type');

    }

}


// Print the success message with the returned output
exit_action('error|An invalid request was made');

?>