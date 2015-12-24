<?php


// If a search sub action was requested
if ($this_current_sub == 'search'){

    // Update the content type to plain text
    header('Content-type: text/plain; ');

    // Collect the search parameters from the request header
    $this_search_type = !empty($_REQUEST['type']) ? trim(strtolower($_REQUEST['type'])) : '';
    $this_search_text = !empty($_REQUEST['text']) ? $_REQUEST['text'] : '';
    $this_search_limit = 10;
    $this_search_data = array(
        'type' => $this_search_type,
        'text' => $this_search_text,
        'limit' => $this_search_limit
        );

    // Sanitize the search string to prevent exploitation
    if (!empty($this_search_text)){
        $this_search_text = trim($this_search_text);
        if (!is_numeric($this_search_text)){
            $this_search_text = strtolower($this_search_text);
            $this_search_text = preg_replace('/[^-a-z0-9*@]+/i', ' ', $this_search_text);
            $this_search_text = trim($this_search_text);
            $this_search_text = preg_replace('/(\*|\s)+/', '%', $this_search_text);
        }
    }

    // Define different search actions for different types
    switch ($this_search_type){

        // If this was a USER search request
        case 'users': {

            // Define a search query for finding users
            $this_search_query = "SELECT
                user_id AS id,
                user_name AS name,
                user_name_public AS name2,
                user_name_clean AS name3,
                (CASE WHEN user_name_public <> '' THEN user_name_public ELSE user_name END) AS name4,
                user_email_address AS email
                FROM mmrpg_users ";

            // Define the search field based on text content
            if (is_numeric($this_search_text)){ $this_search_field = 'id'; }
            elseif (strstr($this_search_text, '@')){ $this_search_field = 'email'; }
            else { $this_search_field = 'name'; }

            // Define the search string based on text type
            switch ($this_search_field){
                // If we are seaching by User ID
                case 'id': {
                    // Limit the search query based on user id
                    $id = $this_search_text;
                    $this_search_query .= "HAVING id = {$id} ORDER BY id ASC ";
                    break;
                }
                // Else if we are seaching by User Name
                case 'name': {
                    // Limit the search query based on user name
                    $name = $this_search_text;
                    $this_search_query .= "HAVING (name4 LIKE '%{$name}%' OR name3 LIKE '%{$name}%') ORDER BY name4 LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
                // Else if we are seaching by User Email
                case 'email': {
                    // Limit the search query based on email address
                    $email = $this_search_text;
                    $this_search_query .= "HAVING email LIKE '%{$email}%' ORDER BY email LIKE '{$email}%' DESC, email ASC ";
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";

            // Collect any results from the database
            $this_search_results = $this_database->get_array_list($this_search_query);
            $this_search_count = !empty($this_search_results) ? count($this_search_results) : 0;

            // Limit the result count if over the limit
            if ($this_search_count > $this_search_limit){
                // Update the message and slice the results to max
                $this_search_count_text = 'over '.$this_search_limit;
                $this_search_results = array_slice($this_search_results, 0, $this_search_limit);
                $this_search_count = $this_search_limit;
            } else {
                // Update the message with the result count
                $this_search_count_text = $this_search_count;
            }

            // Create the variable to hold search markup
            $this_search_markup = '';


            // Generate markup for the search results
            if (!empty($this_search_results)){

                // Generate the markup for the result header
                ob_start();
                ?>
                    <div class="head">
                        <span class="id">ID</span>
                        <span class="name">Name</span>
                        <span class="extra">Email</span>
                        <span class="arrow"></span>
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                foreach ($this_search_results AS $key => $info){
                    // Collect the display fields from the array
                    $id = $info['id'];
                    $name = $info['name4'];
                    if ($info['name4'] != $info['name']){ $name .= ' / '.$info['name']; }
                    $email = !empty($info['email']) ? $info['email'] : '-';
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= $id.' | '.$name.' | '.$email ?>" target="_<?= $this_search_type ?>Editor">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $email ?></span>
                            <span class="arrow">&raquo;</span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
            }

            /*
            exit('<pre>'.
                ' <br /> $this_search_type = '.$this_search_type.
                ' <br /> $this_search_text = '.$this_search_text.
                ' <br /> $this_search_query = '.$this_search_query.
                ' <br /> $this_search_results = '.print_r($this_search_results, true).
                '</pre>');
                */

            // Print out the results with a success
            exit('success'.PHP_EOL.
                'Search found '.$this_search_count_text.' '.($this_search_count == 1 ? 'user' : 'users').'.'.PHP_EOL.
                json_encode($this_search_data).PHP_EOL.
                $this_search_markup
                );

            // Break out of the switch just in case
            break;

        }

        // If this was a MECHA/ROBOT/BOSS search request
        case 'mechas':
        case 'robots':
        case 'bosses': {

            // Define a search query for finding robots
            $this_search_query = "SELECT
                robot_id AS id,
                robot_name AS name,
                robot_core AS core,
                robot_core2 AS core2,
                robot_number AS number,
                robot_class AS class
                FROM mmrpg_index_robots
                ";

            // Filter the search based on requested class
            if ($this_search_type == 'mechas'){ $this_search_query .= "HAVING class = 'mecha' "; }
            elseif ($this_search_type == 'robots'){ $this_search_query .= "HAVING class = 'master' "; }
            elseif ($this_search_type == 'bosses'){ $this_search_query .= "HAVING class = 'boss' "; }

            // Collect an array of elemental types
            $mmrpg_types = rpg_type::get_index();
            $mmrpg_types_tokens = array_keys($mmrpg_types);
            $mmrpg_types_tokens[] = 'neutral';

            // Define the search field based on text content
            if (is_numeric($this_search_text)){ $this_search_field = 'id'; }
            elseif (preg_match('/^([a-z]{3,})-/i', $this_search_text)){ $this_search_field = 'number'; }
            elseif (in_array($this_search_text, $mmrpg_types_tokens)){ $this_search_field = 'core'; }
            else { $this_search_field = 'name'; }

            // Define the search string based on text type
            switch ($this_search_field){
                // If we are seaching by Robot ID
                case 'id': {
                    // Limit the search query based on robot id
                    $id = $this_search_text;
                    $this_search_query .= "AND id = {$id} ORDER BY id ASC ";
                    break;
                }
                // Else if we are seaching by Robot Number
                case 'number': {
                    // Limit the search query based on robot number
                    $number = $this_search_text;
                    $this_search_query .= "AND number LIKE '%{$number}%' ORDER BY number LIKE '{$number}%' DESC, number ASC ";
                    break;
                }
                // Else if we are seaching by Robot Name
                case 'name': {
                    // Limit the search query based on robot name
                    $name = $this_search_text;
                    if (preg_match('/^([a-z0-9]+)(man|woman)$/i', $name)){ $name = preg_replace('/^([a-z0-9]+)(man|woman)$/i', '$1%$2', $name); }
                    $this_search_query .= "AND name LIKE '%{$name}%' ORDER BY name LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
                // Else if we are seaching by Robot Type
                case 'core': {
                    // Limit the search query based on core core
                    $core = $this_search_text;
                    if ($core == 'none' || $core == 'neutral'){
                        $this_search_query .= "AND core = '' ORDER BY name ASC ";
                    } else {
                        $this_search_query .= "AND (core LIKE '%{$core}%' OR core2 LIKE '%{$core}%') ORDER BY core LIKE '{$core}%' DESC, core ASC, name ASC ";
                    }
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";
            //exit($this_search_query);

            // Collect any results from the database
            $this_search_results = $this_database->get_array_list($this_search_query);
            $this_search_count = !empty($this_search_results) ? count($this_search_results) : 0;

            // Limit the result count if over the limit
            if ($this_search_count > $this_search_limit){
                // Update the message and slice the results to max
                $this_search_count_text = 'over '.$this_search_limit;
                $this_search_results = array_slice($this_search_results, 0, $this_search_limit);
                $this_search_count = $this_search_limit;
            } else {
                // Update the message with the result count
                $this_search_count_text = $this_search_count;
            }

            // Create the variable to hold search markup
            $this_search_markup = '';


            // Generate markup for the search results
            if (!empty($this_search_results)){

                // Generate the markup for the result header
                ob_start();
                ?>
                    <div class="head">
                        <span class="id">ID</span>
                        <span class="name">Name</span>
                        <span class="extra"><?= $this_search_field == 'number' ? 'Number' : 'Types' ?></span>
                        <span class="arrow"></span>
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                foreach ($this_search_results AS $key => $info){
                    // Collect the display fields from the array
                    $id = $info['id'];
                    $name = $info['name'];
                    if ($this_search_field == 'number'){
                        $number = $info['number'];
                        $extra = $number;
                    } else {
                        $core = !empty($info['core']) && !empty($mmrpg_types[$info['core']]) ? $mmrpg_types[$info['core']] : $mmrpg_types['none'];
                        $core2 = !empty($info['core2']) && !empty($mmrpg_types[$info['core2']]) ? $mmrpg_types[$info['core2']] : false;
                        $extra = $core['type_name'].(!empty($core2) ? ' / '.$core2['type_name'] : '');
                    }
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= $id.' | '.$name.' | '.$extra ?>" target="_<?= $this_search_type ?>Editor">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $extra ?></span>
                            <span class="arrow">&raquo;</span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
            }

            /*
            exit('<pre>'.
                ' <br /> $this_search_type = '.$this_search_type.
                ' <br /> $this_search_text = '.$this_search_text.
                ' <br /> $this_search_query = '.$this_search_query.
                ' <br /> $this_search_results = '.print_r($this_search_results, true).
                '</pre>');
                */

            // Print out the results with a success
            exit('success'.PHP_EOL.
                'Search found '.$this_search_count_text.' '.($this_search_count == 1 ? preg_replace('/e?s$/i', '', $this_search_type) : $this_search_type).'.'.PHP_EOL.
                json_encode($this_search_data).PHP_EOL.
                $this_search_markup
                );

            // Break out of the switch just in case
            break;

        }

        // If this was a ABILITY/ITEM search request
        case 'abilities':
        case 'items': {

            // Define a search query for finding abilities
            $this_search_query = "SELECT
                ability_id AS id,
                ability_name AS name,
                ability_type AS type,
                ability_type2 AS type2,
                ability_class AS class
                FROM mmrpg_index_abilities
                ";

            // Filter the search based on requested class
            if ($this_search_type == 'abilities'){ $this_search_query .= "HAVING class IN ('mecha','master','boss') "; }
            elseif ($this_search_type == 'items'){ $this_search_query .= "HAVING class IN ('item') "; }

            // Collect an array of elemental types
            $mmrpg_types = rpg_type::get_index();
            $mmrpg_types_tokens = array_keys($mmrpg_types);
            $mmrpg_types_tokens[] = 'neutral';

            // Define the search field based on text content
            if (is_numeric($this_search_text)){ $this_search_field = 'id'; }
            elseif (in_array($this_search_text, $mmrpg_types_tokens)){ $this_search_field = 'type'; }
            else { $this_search_field = 'name'; }

            // Define the search string based on text type
            switch ($this_search_field){
                // If we are seaching by Robot ID
                case 'id': {
                    // Limit the search query based on ability id
                    $id = $this_search_text;
                    $this_search_query .= "AND id = {$id} ORDER BY id ASC ";
                    break;
                }
                // Else if we are seaching by Robot Name
                case 'name': {
                    // Limit the search query based on ability name
                    $name = $this_search_text;
                    $this_search_query .= "AND name LIKE '%{$name}%' ORDER BY name LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
                // Else if we are seaching by Robot Type
                case 'type': {
                    // Limit the search query based on type type
                    $type = $this_search_text;
                    if ($type == 'none' || $type == 'neutral'){
                        $this_search_query .= "AND type = '' ORDER BY name ASC ";
                    } else {
                        $this_search_query .= "AND (type LIKE '%{$type}%' OR type2 LIKE '%{$type}%') ORDER BY type LIKE '{$type}%' DESC, type ASC, name ASC ";
                    }
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";
            //exit($this_search_query);

            // Collect any results from the database
            $this_search_results = $this_database->get_array_list($this_search_query);
            $this_search_count = !empty($this_search_results) ? count($this_search_results) : 0;

            // Limit the result count if over the limit
            if ($this_search_count > $this_search_limit){
                // Update the message and slice the results to max
                $this_search_count_text = 'over '.$this_search_limit;
                $this_search_results = array_slice($this_search_results, 0, $this_search_limit);
                $this_search_count = $this_search_limit;
            } else {
                // Update the message with the result count
                $this_search_count_text = $this_search_count;
            }

            // Create the variable to hold search markup
            $this_search_markup = '';


            // Generate markup for the search results
            if (!empty($this_search_results)){

                // Generate the markup for the result header
                ob_start();
                ?>
                    <div class="head">
                        <span class="id">ID</span>
                        <span class="name">Name</span>
                        <span class="extra">Types</span>
                        <span class="arrow"></span>
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                foreach ($this_search_results AS $key => $info){
                    // Collect the display fields from the array
                    $id = $info['id'];
                    $name = $info['name'];
                    $type = !empty($info['type']) && !empty($mmrpg_types[$info['type']]) ? $mmrpg_types[$info['type']] : $mmrpg_types['none'];
                    $type2 = !empty($info['type2']) && !empty($mmrpg_types[$info['type2']]) ? $mmrpg_types[$info['type2']] : false;
                    $extra = $type['type_name'].(!empty($info['type']) && !empty($type2) ? ' / '.$type2['type_name'] : '');
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= $id.' | '.$name.' | '.$extra ?>" target="_<?= $this_search_type ?>Editor">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $extra ?></span>
                            <span class="arrow">&raquo;</span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
            }

            /*
            exit('<pre>'.
                ' <br /> $this_search_type = '.$this_search_type.
                ' <br /> $this_search_text = '.$this_search_text.
                ' <br /> $this_search_query = '.$this_search_query.
                ' <br /> $this_search_results = '.print_r($this_search_results, true).
                '</pre>');
                */

            // Print out the results with a success
            exit('success'.PHP_EOL.
                'Search found '.$this_search_count_text.' '.($this_search_count == 1 ? preg_replace('/e?s$/i', '', $this_search_type) : $this_search_type).'.'.PHP_EOL.
                json_encode($this_search_data).PHP_EOL.
                $this_search_markup
                );

            // Break out of the switch just in case
            break;

        }

        // Else if this was an UNDEFINED request
        default : {

            // Exit with an error message
            die('error'.PHP_EOL.
                'Invalid search request!'.PHP_EOL.
                json_encode($this_search_data)
                );

        }

    }

}



?>