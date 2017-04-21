<?
/*
 * DEV TESTS INDEX
 */

// Start generating the page markup
ob_start();

    // Check if the requested sub name actually exists before including
    $include_file_name = false;
    if (!empty($this_current_sub)){
        $include_file_name = 'dev_'.$this_current_sub.'.php';
        if (!file_exists(MMRPG_CONFIG_ROOTDIR.'pages/'.$include_file_name)){ $include_file_name = ''; }
    }

    // If we're NOT including a sub-page file, display the index list
    if (empty($include_file_name)){

        // Define the SEO variables for this page
        $this_seo_title = 'Dev Tests | '.$this_seo_title;
        $this_seo_description = 'This page has no description because it is supposed to be hidden.';
        $this_seo_robots = 'noindex,nofollow';

        // Define the Open Graph variables for this page
        $this_graph_data['title'] = 'Dev Tests';
        $this_graph_data['description'] = 'This page was supposed to be a secret...';

        ?>

        <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Developer Tests</h2>
        <div class="subbody">

            Tests and experiments go here!

            <ul class="text" style="padding-bottom: 10px;">
                <li>&raquo; <a class="link_inline" href="#">Experimental link</a></li>
                <li>&raquo; <a class="link_inline" href="dev/map-test/">Procedurally generated map experiment</a></li>
            </ul>

        </div>

        <?

    }
    // Otherwise include the requested sub-page instead
    else {

        // Require the requested sub-page file
        require_once($include_file_name);

    }


// Collect the buffer and define the page markup
$this_markup_body = trim(ob_get_clean());

?>