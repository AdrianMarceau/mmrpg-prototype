<?php


// If a search sub action was requested
if ($this_current_sub == 'search'){

    // Update the content type to plain text
    header('Content-type: text/plain; ');

    // Collect the search parameters from the request header
    $this_search_type = !empty($_REQUEST['type']) ? trim(strtolower($_REQUEST['type'])) : '';
    $this_search_text = !empty($_REQUEST['text']) ? $_REQUEST['text'] : '';
    $this_search_limit = 50;
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

    // Collect an array of elemental types
    $mmrpg_types = rpg_type::get_index(true);
    $mmrpg_types_tokens = array_keys($mmrpg_types);
    $mmrpg_types_tokens[] = 'neutral';

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
                user_email_address AS email,
                user_flag_approved AS complete
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
                    $this_search_query .= "HAVING id = {$id} ";
                    $this_search_query .= "ORDER BY id ASC ";
                    break;
                }
                // Else if we are seaching by User Name
                case 'name': {
                    // Limit the search query based on user name
                    $name = $this_search_text;
                    $this_search_query .= "HAVING (name4 LIKE '%{$name}%' OR name3 LIKE '%{$name}%') ";
                    $this_search_query .= "ORDER BY complete DESC, name4 LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
                // Else if we are seaching by User Email
                case 'email': {
                    // Limit the search query based on email address
                    $email = $this_search_text;
                    $this_search_query .= "HAVING email LIKE '%{$email}%' ";
                    $this_search_query .= "ORDER BY complete DESC, email LIKE '{$email}%' DESC, email ASC ";
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";

            // Collect any results from the database
            $this_search_results = $db->get_array_list($this_search_query);
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
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                $this_search_markup .= PHP_EOL.'<div class="list">';
                foreach ($this_search_results AS $key => $info){
                    // Collect the display fields from the array
                    $id = $info['id'];
                    $name = $info['name4'];
                    if ($info['name4'] != $info['name']){ $name .= ' / '.$info['name']; }
                    $email = !empty($info['email']) ? $info['email'] : '-';
                    $complete = $info['complete'] ? true : false;
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result <?= !$complete ? 'incomplete' : '' ?>" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= $id.' | '.$name.' | '.$email ?>" target="_editUser<?= $id ?>">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $email ?></span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
                $this_search_markup .= PHP_EOL.'</div>';
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

        // If this was a PLAYER search request
        case 'players': {

            // Define a search query for finding players
            $this_search_query = "SELECT
                player_id AS id,
                player_token AS token,
                player_name AS name,
                player_type AS type,
                player_number AS number,
                player_class AS class,
                player_flag_complete AS complete
                FROM mmrpg_index_players
                HAVING id <> 0 AND token <> 'player'
                ";

            // Define the search field based on text content
            if (is_numeric($this_search_text)){ $this_search_field = 'id'; }
            elseif (preg_match('/^([a-z]{3,})-/i', $this_search_text)){ $this_search_field = 'number'; }
            else { $this_search_field = 'name'; }

            // Define the search string based on text type
            switch ($this_search_field){
                // If we are seaching by Field ID
                case 'id': {
                    // Limit the search query based on player id
                    $id = $this_search_text;
                    $this_search_query .= "AND id = {$id} ";
                    $this_search_query .= "ORDER BY id ASC ";
                    break;
                }
                // Else if we are seaching by Field Number
                case 'number': {
                    // Limit the search query based on player number
                    $number = $this_search_text;
                    $this_search_query .= "AND number LIKE '%{$number}%' ";
                    $this_search_query .= "ORDER BY complete DESC, number LIKE '{$number}%' DESC, number ASC ";
                    break;
                }
                // Else if we are seaching by Field Name
                case 'name': {
                    // Limit the search query based on player name
                    $name = $this_search_text;
                    if (preg_match('/^(dr)([a-z0-9]+)$/i', $name)){ $name = preg_replace('/^(dr)([a-z0-9]+)$/i', '$1%$2', $name); }
                    $this_search_query .= "AND name LIKE '%{$name}%' ";
                    $this_search_query .= "ORDER BY complete DESC, name LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";
            //exit($this_search_query);

            // Collect any results from the database
            $this_search_results = $db->get_array_list($this_search_query);
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
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                $this_search_markup .= PHP_EOL.'<div class="list">';
                foreach ($this_search_results AS $key => $info){
                    // Collect the display fields from the array
                    $id = $info['id'];
                    $name = $info['name'];
                    if ($this_search_field == 'number'){
                        $number = $info['number'];
                        $extra = $number;
                    } else {
                        $type = !empty($info['type']) && !empty($mmrpg_types[$info['type']]) ? $mmrpg_types[$info['type']] : $mmrpg_types['none'];
                        $extra = '<span class="type '.$type['type_token'].'">'.$type['type_name'].'</span>';
                    }
                    $complete = $info['complete'] ? true : false;
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result <?= !$complete ? 'incomplete' : '' ?>" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= strip_tags($id.' | '.$name.' | '.$extra) ?>"  target="_editPlayer<?= $id ?>">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $extra ?></span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
                $this_search_markup .= PHP_EOL.'</div>';
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


        // If this was a MECHA/ROBOT/BOSS search request
        case 'mechas':
        case 'robots':
        case 'bosses': {

            // Define a search query for finding robots
            $this_search_query = "SELECT
                robot_id AS id,
                robot_token AS token,
                robot_name AS name,
                robot_core AS core,
                robot_core2 AS core2,
                robot_number AS number,
                robot_class AS class,
                robot_flag_complete AS complete
                FROM mmrpg_index_robots
                HAVING id <> 0 AND token NOT IN ('robot', 'mecha', 'master', 'boss')
                ";

            // Filter the search based on requested class
            if ($this_search_type == 'mechas'){
                $this_search_type_name = 'Mecha';
                $this_search_query .= "AND class = 'mecha' ";
            }
            elseif ($this_search_type == 'robots'){
                $this_search_type_name = 'Robot';
                $this_search_query .= "AND class = 'master' ";
            }
            elseif ($this_search_type == 'bosses'){
                $this_search_type_name = 'Boss';
                $this_search_query .= "AND class = 'boss' ";
            }

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
                    $this_search_query .= "AND id = {$id} ";
                    $this_search_query .= "ORDER BY id ASC ";
                    break;
                }
                // Else if we are seaching by Robot Number
                case 'number': {
                    // Limit the search query based on robot number
                    $number = $this_search_text;
                    $this_search_query .= "AND number LIKE '%{$number}%' ";
                    $this_search_query .= "ORDER BY complete DESC, number LIKE '{$number}%' DESC, number ASC ";
                    break;
                }
                // Else if we are seaching by Robot Name
                case 'name': {
                    // Limit the search query based on robot name
                    $name = $this_search_text;
                    if (preg_match('/^([a-z0-9]{3,})(man)$/i', $name)){ $name = preg_replace('/^([a-z0-9]{3,})(man)$/i', '$1%$2', $name); }
                    $this_search_query .= "AND name LIKE '%{$name}%' ";
                    $this_search_query .= "ORDER BY complete DESC, name LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
                // Else if we are seaching by Robot Type
                case 'core': {
                    // Limit the search query based on core core
                    $core = $this_search_text;
                    if ($core == 'none' || $core == 'neutral'){
                        $this_search_query .= "AND core = '' ";
                        $this_search_query .= "ORDER BY complete DESC, name ASC ";
                    } else {
                        $this_search_query .= "AND (core LIKE '%{$core}%' OR core2 LIKE '%{$core}%') ";
                        $this_search_query .= "ORDER BY complete DESC, core LIKE '{$core}%' DESC, core ASC, name ASC ";
                    }
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";
            //exit($this_search_query);

            // Collect any results from the database
            $this_search_results = $db->get_array_list($this_search_query);
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
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                $this_search_markup .= PHP_EOL.'<div class="list">';
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
                        $extra = '<span class="type '.$core['type_token'].'">'.$core['type_name'].'</span>'.(!empty($core2) ? ' / <span class="type '.$core2['type_token'].'">'.$core2['type_name'].'</span>' : '');
                    }
                    $complete = $info['complete'] ? true : false;
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result <?= !$complete ? 'incomplete' : '' ?>" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= strip_tags($id.' | '.$name.' | '.$extra) ?>"  target="_edit<?= $this_search_type_name.$id ?>">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $extra ?></span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
                $this_search_markup .= PHP_EOL.'</div>';
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

        // If this was an ABILITY search request
        case 'abilities': {

            // Define a search query for finding abilities
            $this_search_query = "SELECT
                ability_id AS id,
                ability_token AS token,
                ability_name AS name,
                ability_type AS type,
                ability_type2 AS type2,
                ability_class AS class,
                ability_flag_complete AS complete
                FROM mmrpg_index_abilities
                HAVING class <> 'system' AND token <> 'ability'
                ";

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
                    $this_search_query .= "AND id = {$id} ";
                    $this_search_query .= "ORDER BY complete DESC, id ASC ";
                    break;
                }
                // Else if we are seaching by Robot Name
                case 'name': {
                    // Limit the search query based on ability name
                    $name = $this_search_text;
                    $this_search_query .= "AND name LIKE '%{$name}%' ";
                    $this_search_query .= "ORDER BY complete DESC, name LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
                // Else if we are seaching by Robot Type
                case 'type': {
                    // Limit the search query based on type type
                    $type = $this_search_text;
                    if ($type == 'none' || $type == 'neutral'){
                        $this_search_query .= "AND type = '' ";
                        $this_search_query .= "ORDER BY complete DESC, name ASC ";
                    } else {
                        $this_search_query .= "AND (type = '{$type}' OR type2 = '{$type}') ";
                        $this_search_query .= "ORDER BY complete DESC, name LIKE '{$type}%' DESC, name ASC ";
                    }
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";
            //exit($this_search_query);

            // Collect any results from the database
            $this_search_results = $db->get_array_list($this_search_query);
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
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                $this_search_markup .= PHP_EOL.'<div class="list">';
                foreach ($this_search_results AS $key => $info){
                    // Collect the display fields from the array
                    $id = $info['id'];
                    $name = $info['name'];
                    $type = !empty($info['type']) && !empty($mmrpg_types[$info['type']]) ? $mmrpg_types[$info['type']] : $mmrpg_types['none'];
                    $type2 = !empty($info['type2']) && !empty($mmrpg_types[$info['type2']]) ? $mmrpg_types[$info['type2']] : false;
                    $extra = '<span class="type '.$type['type_token'].'">'.$type['type_name'].'</span>'.(!empty($info['type']) && !empty($type2) ? ' / <span class="type '.$type2['type_token'].'">'.$type2['type_name'].'</span>' : '');
                    $complete = $info['complete'] ? true : false;
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result <?= !$complete ? 'incomplete' : '' ?>" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= strip_tags($id.' | '.$name.' | '.$extra) ?>" target="_editAbility<?= $id ?>">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $extra ?></span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
                $this_search_markup .= PHP_EOL.'</div>';

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

        // If this was an ITEM search request
        case 'items': {

            // Define a search query for finding items
            $this_search_query = "SELECT
                item_id AS id,
                item_token AS token,
                item_name AS name,
                item_type AS type,
                item_type2 AS type2,
                item_class AS class,
                item_flag_complete AS complete
                FROM mmrpg_index_items
                HAVING class <> 'system' AND token <> 'item'
                ";

            // Define the search field based on text content
            if (is_numeric($this_search_text)){ $this_search_field = 'id'; }
            elseif (in_array($this_search_text, $mmrpg_types_tokens)){ $this_search_field = 'type'; }
            else { $this_search_field = 'name'; }

            // Define the search string based on text type
            switch ($this_search_field){
                // If we are seaching by Robot ID
                case 'id': {
                    // Limit the search query based on item id
                    $id = $this_search_text;
                    $this_search_query .= "AND id = {$id} ";
                    $this_search_query .= "ORDER BY complete DESC, id ASC ";
                    break;
                }
                // Else if we are seaching by Robot Name
                case 'name': {
                    // Limit the search query based on item name
                    $name = $this_search_text;
                    $this_search_query .= "AND name LIKE '%{$name}%' ";
                    $this_search_query .= "ORDER BY complete DESC, name LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
                // Else if we are seaching by Robot Type
                case 'type': {
                    // Limit the search query based on type type
                    $type = $this_search_text;
                    if ($type == 'none' || $type == 'neutral'){
                        $this_search_query .= "AND type = '' ";
                        $this_search_query .= "ORDER BY complete DESC, name ASC ";
                    } else {
                        $this_search_query .= "AND (type = '{$type}' OR type2 = '{$type}') ";
                        $this_search_query .= "ORDER BY complete DESC, name LIKE '{$type}%' DESC, name ASC ";
                    }
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";
            //exit($this_search_query);

            // Collect any results from the database
            $this_search_results = $db->get_array_list($this_search_query);
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
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                $this_search_markup .= PHP_EOL.'<div class="list">';
                foreach ($this_search_results AS $key => $info){
                    // Collect the display fields from the array
                    $id = $info['id'];
                    $name = $info['name'];
                    $type = !empty($info['type']) && !empty($mmrpg_types[$info['type']]) ? $mmrpg_types[$info['type']] : $mmrpg_types['none'];
                    $type2 = !empty($info['type2']) && !empty($mmrpg_types[$info['type2']]) ? $mmrpg_types[$info['type2']] : false;
                    $extra = '<span class="type '.$type['type_token'].'">'.$type['type_name'].'</span>'.(!empty($info['type']) && !empty($type2) ? ' / <span class="type '.$type2['type_token'].'">'.$type2['type_name'].'</span>' : '');
                    $complete = $info['complete'] ? true : false;
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result <?= !$complete ? 'incomplete' : '' ?>" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= strip_tags($id.' | '.$name.' | '.$extra) ?>" target="_editItem<?= $id ?>">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $extra ?></span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
                $this_search_markup .= PHP_EOL.'</div>';

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

        // If this was a FIELD search request
        case 'fields': {

            // Define a search query for finding fields
            $this_search_query = "SELECT
                field_id AS id,
                field_token AS token,
                field_name AS name,
                field_type AS type,
                field_number AS number,
                field_class AS class,
                field_flag_complete AS complete
                FROM mmrpg_index_fields
                HAVING id <> 0 AND token <> 'field'
                ";

            // Define the search field based on text content
            if (is_numeric($this_search_text)){ $this_search_field = 'id'; }
            elseif (preg_match('/^([a-z]{3,})-/i', $this_search_text)){ $this_search_field = 'number'; }
            else { $this_search_field = 'name'; }

            // Define the search string based on text type
            switch ($this_search_field){
                // If we are seaching by Field ID
                case 'id': {
                    // Limit the search query based on field id
                    $id = $this_search_text;
                    $this_search_query .= "AND id = {$id} ";
                    $this_search_query .= "ORDER BY id ASC ";
                    break;
                }
                // Else if we are seaching by Field Number
                case 'number': {
                    // Limit the search query based on field number
                    $number = $this_search_text;
                    $this_search_query .= "AND number LIKE '%{$number}%' ";
                    $this_search_query .= "ORDER BY complete DESC, number LIKE '{$number}%' DESC, number ASC ";
                    break;
                }
                // Else if we are seaching by Field Name
                case 'name': {
                    // Limit the search query based on field name
                    $name = $this_search_text;
                    if (preg_match('/^(dr)([a-z0-9]+)$/i', $name)){ $name = preg_replace('/^(dr)([a-z0-9]+)$/i', '$1%$2', $name); }
                    $this_search_query .= "AND name LIKE '%{$name}%' ";
                    $this_search_query .= "ORDER BY complete DESC, name LIKE '{$name}%' DESC, name ASC ";
                    break;
                }
            }

            // Limit the query results for display purposes
            $overflow_limit = $this_search_limit + 1;
            $this_search_query .= "LIMIT {$overflow_limit}; ";
            //exit($this_search_query);

            // Collect any results from the database
            $this_search_results = $db->get_array_list($this_search_query);
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
                    </div>
                <?
                // And append the markup to the parent var
                $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                $this_search_markup = trim($this_search_markup);

                // Generate the link markup for the results
                $this_search_markup .= PHP_EOL.'<div class="list">';
                foreach ($this_search_results AS $key => $info){
                    // Collect the display fields from the array
                    $id = $info['id'];
                    $name = $info['name'];
                    if ($this_search_field == 'number'){
                        $number = $info['number'];
                        $extra = $number;
                    } else {
                        $type = !empty($info['type']) && !empty($mmrpg_types[$info['type']]) ? $mmrpg_types[$info['type']] : $mmrpg_types['none'];
                        $extra = '<span class="type '.$type['type_token'].'">'.$type['type_name'].'</span>';
                    }
                    $complete = $info['complete'] ? true : false;
                    // Create the markup for this search result
                    ob_start();
                    ?>
                        <a class="link result <?= !$complete ? 'incomplete' : '' ?>" href="admin/<?= $this_search_type ?>/<?= $id ?>/" title="<?= strip_tags($id.' | '.$name.' | '.$extra) ?>" target="_editField<?= $id ?>">
                            <span class="id"><?= $id ?></span>
                            <span class="name"><?= $name ?></span>
                            <span class="extra"><?= $extra ?></span>
                        </a>
                    <?
                    // And append the markup to the parent var
                    $this_search_markup .= PHP_EOL.trim(preg_replace('/\s+/', ' ', ob_get_clean()));
                    $this_search_markup = trim($this_search_markup);
                }
                $this_search_markup .= PHP_EOL.'</div>';
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