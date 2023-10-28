<?

/*
 * DEV TESTS / ENDLESS MODE
 */

// Define the constant that puts the front-end in compact mode
define('MMRPG_INDEX_COMPACT_MODE', true);

// Define the SEO variables for this page
$this_seo_title = 'Endless Mode Generator V3 | '.$this_seo_title;
$this_seo_description = 'An experimental endless mode generator for the MMRPG.';
$this_seo_robots = 'noindex,nofollow';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Endless Mode Generator V3';
$this_graph_data['description'] = 'An experimental endless mode mission/path generator for the MMRPG.';

?>
<div class="header">
    <div class="header_wrapper">
        <h1 class="title"><span class="brand">Mega Man RPG Endless Mode V3</span></h1>
    </div>
</div>
<h2 class="subheader field_type_<?= !empty($this_field_info['field_type']) ? $this_field_info['field_type'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    Endless Mode Path Generator
</h2>

<div class="subbody">

    <?

    // Generate a mission index using the collected robot and hazard data
    $mmrpg_endless_playlist = array();
    for ($mission_number = 1; $mission_number <= 400; $mission_number++){
        $mmrpg_endless_playlist[$mission_number] = rpg_mission_endless::generate_endless_mission_seed($mission_number, true);
    }
    for ($mission_number = 400; $mission_number <= 1000; $mission_number += 50){
        $mmrpg_endless_playlist[$mission_number] = rpg_mission_endless::generate_endless_mission_seed($mission_number, true);
    }

    echo('<pre style="font-size: 11px;">$mmrpg_endless_playlist = '.print_r($mmrpg_endless_playlist, true).'</pre>');
    //echo('<pre>$mmrpg_robots_cores = '.print_r($mmrpg_robots_cores, true).'</pre>');
    //echo('<pre>$mmrpg_robots_index_bycore = '.print_r($mmrpg_robots_index_bycore, true).'</pre>');
    //echo('<pre>$mmrpg_types_index = '.print_r($mmrpg_types_index, true).'</pre>');
    //echo('<pre>$mmrpg_items_index = '.print_r(array_keys($mmrpg_items_index), true).'</pre>');
    //echo('<pre>$mmrpg_items_index_bykind = '.print_r($mmrpg_items_index_bykind, true).'</pre>');
    //echo('<pre>$mmrpg_items_index_bykind = '.print_r(array_keys($mmrpg_items_index_bykind), true).'</pre>');
    //echo('<pre>$mmrpg_items_index = '.print_r($mmrpg_items_index, true).'</pre>');
    //echo('<pre>$mmrpg_robots_index = '.print_r(array_keys($mmrpg_robots_index), true).'</pre>');
    //echo('<pre style="max-height: none;">$mmrpg_robots_index = '.print_r($mmrpg_robots_index, true).'</pre>');

    /*
    $error_log = '';
    foreach ($mmrpg_endless_playlist AS $mission_number => $mission_info){
        $mission_info = json_decode($mission_info, true);
        if ($mission_info['types'][0] === 'water'){
            $error_log .= ('mission #'.$mission_number.PHP_EOL.' vs '.implode(', ', $mission_info['targets'])).PHP_EOL;
        }
    }
    error_log($error_log);
    */

    ?>

</div>