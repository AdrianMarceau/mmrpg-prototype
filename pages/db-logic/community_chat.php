<?

// Collect details for the general chat thread
$chat_thread_id = 1107;
$chat_thread_info = $db->get_array("SELECT
    categories.category_token AS cat,
    threads.thread_id AS id,
    threads.thread_token AS url
    FROM mmrpg_threads AS threads
    LEFT JOIN mmrpg_categories AS categories ON categories.category_id = threads.category_id
    WHERE threads.thread_id = {$chat_thread_id}
    LIMIT 1
    ;");

// Automatically redirect to the appropriate thread if found
if (!empty($chat_thread_info)){
    $chat_redirect_url = MMRPG_CONFIG_ROOTURL.'community/'.$chat_thread_info['cat'].'/'.$chat_thread_info['id'].'/'.$chat_thread_info['url'].'/';
    header('Location: '.$chat_redirect_url);
    exit();
}

?>