<?

// Require common git functions and variables if not exist already
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/git_common_variables.php');
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/git_common_functions.php');

// Ensure the user is actually logged in as an admin
if (!defined('MMRPG_CONFIG_ADMIN_MODE')
    || MMRPG_CONFIG_ADMIN_MODE !== true){
    exit_action('error|user not logged in or not admin');
}

// Collect the required kind and subkind details from the query headers
//debug_echo('$_REQUEST = '.print_r($_REQUEST, true));
$request_kind = !empty($_REQUEST['kind']) && preg_match('/^[\.\-_a-z0-9]+$/', $_REQUEST['kind']) ? $_REQUEST['kind'] : false;
$request_subkind = !empty($_REQUEST['subkind']) && preg_match('/^[\.\-_a-z0-9]+$/', $_REQUEST['subkind']) ? $_REQUEST['subkind'] : false;
if (empty($request_kind) || !in_array($request_kind, $allowed_kinds)){ exit_action('error|request_kind empty or not valid'); }
if (empty($allow_empty_subkind) && isset($allowed_subkinds[$request_kind]) && (empty($request_subkind) || !in_array($request_subkind, $allowed_subkinds[$request_kind]))){ exit_action('error|request_subkind empty or not provided'); }
//debug_echo('$request_kind = '.$request_kind);
//debug_echo('$request_subkind = '.$request_subkind);
$request_kind_singular = false;
if (!empty($request_kind)){
    if (preg_match('/ies$/', $request_kind)){ $request_kind_singular = substr($request_kind, 0, -3).'y';  }
    elseif (preg_match('/ses$/', $request_kind)){ $request_kind_singular = substr($request_kind, 0, -2);  }
    else { $request_kind_singular = rtrim($request_kind, 's'); }
    //debug_echo('$request_kind_singular = '.$request_kind_singular);
}
$request_subkind_singular = false;
if (!empty($request_subkind)){
    if (preg_match('/ies$/', $request_subkind)){ $request_subkind_singular = substr($request_subkind, 0, -3).'y';  }
    elseif (preg_match('/ses$/', $request_subkind)){ $request_subkind_singular = substr($request_subkind, 0, -2);  }
    else { $request_subkind_singular = rtrim($request_subkind, 's'); }
    //debug_echo('$request_subkind_singular = '.$request_subkind_singular);
}

// Collect the required source argument (event though we know there's only one)
$request_source = !empty($_REQUEST['source']) && preg_match('/^[\.\-_a-z0-9]+$/', $_REQUEST['source']) ? $_REQUEST['source'] : false;
if (empty($request_source) || !in_array($request_source, $allowed_sources)){ exit_action('error|request_source empty or not valid'); }
//debug_echo('$request_source = '.$request_source);

// Collect the required token from the query headers
$request_token = !empty($_REQUEST['token']) && preg_match('/^[\.\-_a-z0-9\/]+$/', $_REQUEST['token']) ? $_REQUEST['token'] : false;
if (empty($request_token)){ exit_action('error|request_token empty or not valid'); }
//debug_echo('$request_token = '.$request_token);

// Collect the contributors index as we're likely to need it the create a name-to-id subindex
$mmrpg_contributors_index = cms_admin::get_contributors_index($request_kind_singular);
$mmrpg_contributors_name_to_id = array();
foreach ($mmrpg_contributors_index AS $id => $info){ $mmrpg_contributors_name_to_id[$info['user_name_clean']] = $id; }
//debug_echo('$mmrpg_contributors_name_to_id = '.print_r($mmrpg_contributors_name_to_id, true).'');

// Define the image editor fields given the object type for later reference
$image_editor_fields = array(
    $request_kind_singular.'_image_editor',
    $request_kind_singular.'_image_editor2'
    );
//debug_echo('$image_editor_fields = '.print_r($image_editor_fields, true).'');

// Collect details regarding who we'll be commiting as for publish actions
$admin_userid = (int)($_SESSION['admin_id']);
$admin_details = $db->get_array("SELECT user_name, user_name_public, user_email_address FROM mmrpg_users WHERE user_id = {$admin_userid};");
$git_publish_name = trim(!empty($admin_details['user_name_public']) ? $admin_details['user_name_public'] : $admin_details['user_name']);
$git_publish_email = trim($admin_details['user_email_address']);
//debug_echo('$admin_details = '.print_r($admin_details, true).'');
//debug_echo('$git_publish_name = '.print_r($git_publish_name, true).'');
//debug_echo('$git_publish_email = '.print_r($git_publish_email, true).'');

// Require the global content type index for reference, make sure required data is present
require_once(MMRPG_CONFIG_CONTENT_PATH.'index.php');
if (!isset($content_types_index[$request_kind])){ exit_action('error|Request kind "'.$request_kind.'" does not appear in the content types index'); }
elseif (empty($content_types_index[$request_kind]['database_table'])){ exit_action('error|Request kind "'.$request_kind.'" does not have a database_table set'); }
elseif (empty($content_types_index[$request_kind]['primary_key'])){ exit_action('error|Request kind "'.$request_kind.'" does not have a primary_key set'); }
$content_type_info = $content_types_index[$request_kind];
//debug_echo('$content_type_info = '.print_r($content_type_info, true).'');

// Define the table name and token field for this object
$object_table_name = $content_type_info['database_table'];
$object_token_field = $request_kind_singular.'_'.$content_type_info['primary_key'];
//debug_echo('$object_table_name = '.print_r($object_table_name, true).'');
//debug_echo('$object_token_field = '.print_r($object_token_field, true).'');

?>