<?

// Require the global content index for metadata
require(MMRPG_CONFIG_ROOTDIR.'content/index.php');
//echo('<pre>$content_types_index = '.print_r($base_dir, true).'</pre>');

// Define an array to hold the overall JSON data to return
$auto_parse_fields = isset($auto_parse_fields) ? $auto_parse_fields : true;
$allowed_indexes = isset($allowed_indexes) ? $allowed_indexes : array('types', 'players', 'robots', 'items', 'skills', 'abilities', 'fields');
$mmrpg_indexes = isset($mmrpg_indexes) ? $mmrpg_indexes : array();
$return_only = !empty($_REQUEST['return']) ? explode(',', trim($_REQUEST['return'])) : array();
foreach ($content_types_index AS $key => $content_info){
    $content_type = $content_info['token'];
    $content_xtype = $content_info['xtoken'];
    if (!in_array($content_xtype, $allowed_indexes)){ continue; }
    if (!empty($return_only) && !in_array($content_xtype, $return_only)){ continue; }
    $content_index = array();
    if ($content_xtype === 'types'){ $content_index = rpg_type::get_index(true); }
    if ($content_xtype === 'players'){ $content_index = rpg_player::get_index(true); }
    if ($content_xtype === 'robots'){ $content_index = rpg_robot::get_index(true); }
    if ($content_xtype === 'items'){ $content_index = rpg_item::get_index(true); }
    if ($content_xtype === 'skills'){ $content_index = rpg_skill::get_index(true); }
    if ($content_xtype === 'abilities'){ $content_index = rpg_ability::get_index(true); }
    if ($content_xtype === 'fields'){ $content_index = rpg_field::get_index(true); }
    $content_index = !empty($content_index) ? $content_index : array();
    $mmrpg_indexes[$content_xtype] = $content_index;
    if (empty($content_index) || !$auto_parse_fields){ continue; }
    foreach ($content_index AS $token => $old_info){
        $new_info = array();
        foreach ($old_info AS $old_field => $value){
            if ($old_field === '_parsed'){ continue; }
            $new_field = preg_replace('/^'.$content_type.'_/', '', $old_field);
            if (strstr($new_field, '_')){
                $lc = explode('_', $new_field);
                $uc = array_map('ucfirst', $lc);
                $new_field = $lc[0].implode('', Array_slice($uc, 1));
            }
            $new_info[$new_field] = $value;
        }
        $content_index[$token] = $new_info;
    }
    $mmrpg_indexes[$content_xtype] = $content_index;
}
//echo('<pre>$mmrpg_indexes = '.print_r($mmrpg_indexes, true).'</pre>');

?>