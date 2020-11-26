<?

ob_echo('');
ob_echo('=============================');
ob_echo('|   START SKILL MIGRATION   |');
ob_echo('=============================');
ob_echo('');

// Collect an index of all valid skills from the database
$skill_fields = rpg_skill::get_index_fields(true);
$skill_index = $db->get_array_list("SELECT
    {$skill_fields},
    (CASE WHEN skill_name LIKE '%Subcore' THEN 'Elemental Subcores' ELSE 'Misc Skills' END) AS skill_group,
    skill_id AS skill_order
    FROM mmrpg_index_skills
    ORDER BY
    skill_token ASC
    ;", 'skill_token');

// Manually add a template "skill" to match the other repos
$template_skill = $skill_index['none'];
$template_skill['skill_token'] = 'skill';
$template_skill['skill_name'] = 'Skill';
$template_skill['skill_class'] = 'system';
$template_skill['skill_description'] = '';
$template_skill['skill_description2'] = '';
$template_skill['skill_flag_hidden'] = 1;
$template_skill['skill_flag_complete'] = 0;
$template_skill['skill_flag_published'] = 0;
$template_skill['skill_flag_protected'] = 1;
$template_skill['skill_order'] = -1;
$skill_index['skill'] = $template_skill;

// Collect unnecessary fields to remove from the generated json data file
$skip_fields_on_json_export = rpg_skill::get_fields_excluded_from_json_export(false);

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_skill_index = $skill_index;
    $skill_index = array();
    foreach ($migration_filter AS $skill_token){
        if (isset($old_skill_index[$skill_token])){
            $skill_index[$skill_token] = $old_skill_index[$skill_token];
        }
    }
    unset($old_skill_index);
}

// Pre-define the base skill content dir
define('MMRPG_SKILLS_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/skills/');

// Count the number of skills that we'll be looping through
$skill_index_size = count($skill_index);
$count_pad_length = strlen($skill_index_size);

// Print out the stats before we start
ob_echo('Total Skills in Database: '.$skill_index_size);
ob_echo('');

sleep(1);

$skill_data_files_copied = array();

// MIGRATE ACTUAL SKILLS
$skill_key = -1; $skill_num = 0;
foreach ($skill_index AS $skill_token => $skill_data){
    $skill_key++; $skill_num++;
    $count_string = '('.$skill_num.' of '.$skill_index_size.')';

    ob_echo('----------');
    ob_echo('Processing skill "'.$skill_token.'" '.$count_string);
    ob_flush();

    $data_path = MMRPG_MIGRATE_OLD_DATA_DIR.$skill_token.'.php';
    //ob_echo('-- $data_path = '.clean_path($data_path));

    $content_path = MMRPG_SKILLS_NEW_CONTENT_DIR.($skill_token === 'skill' ? '.skill' : $skill_token).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);

    // Ensure the data file exists before attempting to extract functions from it
    if (true){
        $functions_file_markup = get_empty_functions_file_markup('skill');
        if (!empty($functions_file_markup)){
            $content_data_path = $content_path.'functions.php';
            //ob_echo('- write default functions into '.clean_path($content_data_path));
            $h = fopen($content_data_path, 'w');
            fwrite($h, $functions_file_markup);
            fclose($h);
        }
        $skill_data_files_copied[] = basename($data_path); // not actually copied but here for tracking
    }

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('skill', $skill_data);
    if (!empty($skip_fields_on_json_export)){ foreach ($skip_fields_on_json_export AS $field){ unset($content_json_data[$field]); } }
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, normalize_file_markup(json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
    fclose($h);

    if ($migration_limit && $skill_num >= $migration_limit){ break; }

}


ob_echo('----------');

ob_echo('');
ob_echo_nobreak('Generating skill groups data file... ');
$object_groups = cms_admin::generate_object_groups_from_index($skill_index, 'skill');
cms_admin::save_object_groups_to_json($object_groups, 'skill');
ob_echo('...done!');
ob_echo('');

ob_echo('----------');

ob_echo('');
ob_echo('Skill Data Files Copied: '.count($skill_data_files_copied).' / '.$skill_index_size);


sleep(1);

ob_echo('');
ob_echo('============================');
ob_echo('|    END SKILL MIGRATION   |');
ob_echo('============================');
ob_echo('');

?>