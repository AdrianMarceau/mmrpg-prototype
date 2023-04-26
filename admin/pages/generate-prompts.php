<? ob_start(); ?>

    <?

    // Define a function for collecting indexes based on kind and class
    $index_kinds = array('players', 'robots', 'abilities', 'items', 'fields');
    $index_kinds_singular = array('player', 'robot', 'ability', 'item', 'field');
    function get_mmrpg_index($kind, $class = null) {
        global $hidden;
        if ($kind === 'players') { return rpg_player::get_index($hidden, false); }
        elseif ($kind === 'robots') { return rpg_robot::get_index($hidden, false, $class); }
        elseif ($kind === 'abilities') { return rpg_ability::get_index($hidden, false, $class); }
        elseif ($kind === 'items') { return rpg_item::get_index($hidden, false); }
        elseif ($kind === 'fields') { return rpg_field::get_index($hidden, false); }
        else { return array(); }
    }

    $mmrpg_index_players = get_mmrpg_index('players');
    $mmrpg_index_robots = get_mmrpg_index('robots');

    // Predefine variables to hold markup for later
    $html_markup = '';
    $styles_markup = '';
    $scripts_markup = '';

    // Collect the URL arguments
    $allowed_prompt_kinds = array(
        'battle-quotes' => 'Generate Battle Quotes',
        'adventure-game' => 'Start a Text-Based Adventure'
        );
    $prompt_kind = !empty($_POST['kind']) && isset($allowed_prompt_kinds[$_POST['kind']]) ? $_POST['kind'] : '';
    $prompt_data = !empty($_POST['data']) && strlen($_POST['data']) <= 1024 && json_decode($_POST['data'], true) !== null ? trim($_POST['data']) : '';

    // Generate the markup for the prompt filters
    ob_start();
    ?>
        <form class="prompt-filters" action="/admin/generate-prompts/" method="post">
            <div class="section main-fields">
                <div class="field" data-field="kind">
                    <label for="kind">Prompt Kind:</label>
                    <select name="kind" id="kind">
                        <option value="">- Select One -</option>
                        <?php foreach ($allowed_prompt_kinds AS $allowed_kind => $allowed_kind_label): ?>
                            <option value="<?= $allowed_kind ?>" <?= $prompt_kind === $allowed_kind ? 'selected' : '' ?>><?= $allowed_kind_label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="section additional-fields">
                <div class="field" data-field="data">
                    <label for="data">Additional Data:</label>
                    <textarea name="data" id="data" rows="6" columns="30"><?= htmlentities($prompt_data, ENT_QUOTES, 'UTF-8', true) ?></textarea>
                </div>
            </div>
            <div class="section form-buttons">
                <button type="reset">Reset</button>
                <button type="submit"><?= !empty($prompt_kind) && !empty($prompt_data) ? 'Regenerate' : 'Generate' ?></button>
            </div>
        </form>
    <?
    $html_markup .= ob_get_clean();
    ob_start();
    ?>
        <style type="text/css">
            .prompt-filters {
                position: relative;
                display: block;
                font-family: Arial, sans-serif;
                font-size: 14px;
                margin-bottom: 1rem;
                padding-right: 150px;
                padding: 6px;
                background-color: #ebebeb;
            }
            .prompt-filters .section {
                display: block;
                margin: 0 0 0.5rem 0;
            }
            .prompt-filters .section:after {
                content: "";
                display: block;
                clear: both;
            }
            .prompt-filters .field {
                display: block;
                float: left;
                padding: 0 1rem 0 0;
                margin: 0 0 0.5rem 0;
            }
            .prompt-filters .field.disabled {
                opacity: 0.6;
            }
            .prompt-filters .field label {
                display: block;
                margin: 0 auto 0.2rem;
                clear: both;
            }
            .prompt-filters label {
                display: inline-block;
                font-weight: bold;
                margin-right: 0.5rem;
            }
            .prompt-filters select,
            .prompt-filters textarea,
            .prompt-filters input[type="text"],
            .prompt-filters input[type="number"] {
                border: 1px solid #ccc;
                border-radius: 4px;
                padding: 6px 9px;
                font-family: inherit;
                font-size: inherit;
                outline: none;
                transition: border-color 0.2s;
                max-width: 6rem;
            }
            .prompt-filters select:focus,
            .prompt-filters textarea:focus,
            .prompt-filters input[type="text"]:focus,
            .prompt-filters input[type="number"]:focus {
                border-color: #007bff;
            }

            .prompt-filters .section.main-fields {
                font-size: 120%;
            }
            .prompt-filters .section.main-fields .field[data-field="kind"] select {
                width: 16rem;
                min-width: 10rem;
                max-width: 100%;
            }

            .prompt-filters .section.additional-fields {
                font-size: 100%;
            }
            .prompt-filters .section.additional-fields input[type="number"] {
                max-width: 5rem;
            }
            .prompt-filters .section.additional-fields .field[data-field="data"] textarea {
                width: 30rem;
                min-width: 20rem;
                max-width: 100%;
            }

            .prompt-filters button {
                background-color: #4c8aab;
                border: 0 solid #427794;
                color: #fff;
                border-radius: 4px;
                padding: 6px 12px;
                font-family: inherit;
                font-size: inherit;
                line-height: 1.3;
                cursor: pointer;
                transition: background-color 0.2s;
                margin-left: auto;
                position: absolute;
                bottom: 6px;
                width: 140px;
            }
            .prompt-filters button[type="submit"] {
                font-size: 120%;
                right: 6px;
            }
            .prompt-filters button[type="reset"] {
                right: 150px;
                font-size: 110%;
                background-color: #c85151;
                border-color: #b63e3e;
                width: 100px;
            }
            .prompt-filters button:hover {
                background-color: #5d97b6;
            }
            .prompt-filters button[type="reset"]:hover {
                background-color: #d55c5c;
            }
            @media (max-width: 768px) {
                .prompt-filters button[type="submit"],
                .prompt-filters button[type="reset"] {
                    position: static;
                    width: auto;
                    padding: 0.5rem 3rem;
                    font-size: 120%;
                }
            }

        </style>
    <?
    $styles_markup .= trim(ob_get_clean());
    ob_start();
    ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                // Add some functionality to the reset button
                const resetButton = document.querySelector('.prompt-filters button[type="reset"]');
                resetButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    const formElement = event.target.closest('form');
                    const formAction = formElement.getAttribute('action');
                    window.location.href = formAction;
                });
            });
        </script>
    <?
    $scripts_markup .= trim(ob_get_clean());

    // If prompt data was actually provided, let's try to generate output
    if (!empty($prompt_kind) && !empty($prompt_data)) {

        // Predefine variables to hold the output
        $prompt_logic_markup = '';
        $prompt_output_markup = '';

        // Proceed based on what kind of prompt is being requested
        switch ($prompt_kind){

            case 'battle-quotes': {

                // Generate the markup for the battle-quotes prompt
                $prompt_logic_markup .= 'Let\'s generate some battle quotes!';

                // Define the template for the battle quotes prompt
                $prompt_output_sections = array();
                $prompt_output_sections['intro'] = array(
                    'title' => 'Intro',
                    'desc' => 'Hello! Please generate battle quotes for characters in our game.',
                    'data' => array()
                    );
                $prompt_output_sections['background'] = array(
                    'title' => 'Background',
                    'desc' => 'Mega Man RPG Prototype is a browser-based fangame where players assume the role of a human operator that commands '.
                        'a group of robots to fight against each other in turn based battles.  The good guys fight against powered-up copies of past '.
                        'robot masters in a virtual world that has been taken control of by the evil alien Slur and the Trill army they created. '
                    );
                $prompt_output_sections['context'] = array(
                    'title' => 'Context',
                    'desc' => 'The player has just sent out {PLAYER1_ACTIVE_ROBOT} as their active robot with {PLAYER1_BENCHED_ROBOTS} on their bench. '.
                        'The enemy robot is {PLAYER2_ACTIVE_ROBOT} and is backed up by {PLAYER2_BENCHED_ROBOTS} on their bench.'
                    );
                $prompt_output_sections['info'] = array(
                    'title' => 'Extra Information',
                    'desc' => 'Here\'s some extra information about the participants that should prove useful:',
                    'data' => array()
                    );
                $prompt_output_sections['info']['data'][] = array(
                    '{PLAYER1_ACTIVE_ROBOT_OVERVIEW}'
                    );
                $prompt_output_sections['info']['data'][] = array(
                    '{PLAYER2_ACTIVE_ROBOT_OVERVIEW}'
                    );
                $prompt_output_sections['request'] = array(
                    'title' => 'Request',
                    'desc' => 'Please come up with some replacement quotes/quips for both {PLAYER1_ACTIVE_ROBOT} and {PLAYER2_ACTIVE_ROBOT} to say to each other during battle. '.
                        'Provide three alternatives per quote type per robot so that I may shuffle them. The length of each quote should be <= 64 characters. '.
                        'Ensure quotes are context-sensitive with occasional jokes or references to background details, other robots, or lore. ',
                    'data' => array()
                    );
                $prompt_output_sections['request']['data'][] = array(
                    'The required quote types are as follows:',
                    '"Start" Quote (on battle start or when first sent into active position)',
                    '"Taunt" Quote (used randomly, includes taunting, gloating, passing thoughts, etc.)',
                    '"Victory" Quote (on battle success or when an opponent is knocked out)',
                    '"Defeat" Quote (on battle failure or when knocked out by an opponent)'
                    );
                $prompt_output_sections['request']['data'][] = array(
                    'The response must be in JSON format using the following structure (but with JSON_PRETTY_PRINT):',
                    json_encode(array(
                        'robot1' => array(
                            'quotes' => array(
                                'start' => array('Start Text 1', 'Start Text 2', 'Start Text 3'),
                                'taunt' => array('Taunt Text 1', 'Taunt Text 2', 'Taunt Text 3'),
                                'victory' => array('Victory Text 1', 'Victory Text 2', 'Victory Text 3'),
                                'defeat' => array('Default Text 1', 'Default Text 2', 'Default Text 3'),
                                )
                            ),
                        'robot2' => array(
                            'quotes' => array(
                                'start' => array('etc.'),
                                )
                            )
                        ))
                    );
                $prompt_output_sections['end'] = array(
                    'desc' => 'No commentary required, thanks! ',
                    );

                // Decode the prompt data and make sure we have a player1 and player2
                $prompt_valid = true;
                $prompt_errors = array();
                $prompt_data = json_decode($prompt_data, true);
                error_log('$prompt_data = '.print_r($prompt_data, true));
                if (empty($prompt_data['player1'])){ $prompt_errors[] = 'undefined player1'; $prompt_valid = false; }
                elseif (empty($prompt_data['player1']['robots'])){ $prompt_errors[] = 'undefined player1 robots'; $prompt_valid = false; }
                if (empty($prompt_data['player2'])){ $prompt_errors[] = 'undefined player2'; $prompt_valid = false; }
                elseif (empty($prompt_data['player2']['robots'])){ $prompt_errors[] = 'undefined player2 robots'; $prompt_valid = false; }
                if (!$prompt_valid){ $prompt_data = array(); }

                // If the prompt was valid, we can actually produce markup
                if ($prompt_valid){

                    // Pull data for player 1 and player 2 based on the provided prompt data
                    $player_template = array('operator' => '', 'robots' => array());
                    $mmrpg_player1 = $player_template;
                    $mmrpg_player1['operator'] = $mmrpg_index_players[$prompt_data['player1']['operator']];
                    foreach ($prompt_data['player1']['robots'] AS $robot_token){ $mmrpg_player1['robots'][] = $mmrpg_index_robots[$robot_token]; }
                    $mmrpg_player2 = $player_template;
                    $mmrpg_player2['operator'] = $mmrpg_index_players[$prompt_data['player2']['operator']];
                    foreach ($prompt_data['player2']['robots'] AS $robot_token){ $mmrpg_player2['robots'][] = $mmrpg_index_robots[$robot_token]; }

                    // Assign some template variable values based on the prompt data
                    $replace_in_markup = array();
                    $players = array('PLAYER1' => $mmrpg_player1, 'PLAYER2' => $mmrpg_player2);
                    foreach ($players AS $PLAYER => $player){
                        $active_robot = $player['robots'][0];
                        //error_log('$player = '.print_r($player, true));
                        //error_log('$active_robot = '.print_r($active_robot, true));

                        $ALT_PLAYER = ($PLAYER !== 'PLAYER2' ? 'PLAYER2' : 'PLAYER1');
                        $alt_player = $players[$ALT_PLAYER];
                        $alt_active_robot = $alt_player['robots'][0];
                        //error_log('$alt_player = '.print_r($alt_player, true));
                        //error_log('$alt_active_robot = '.print_r($alt_active_robot, true));

                        $replace_in_markup[$PLAYER.'_NAME'] = $player['operator']['player_name'];
                        $replace_in_markup[$PLAYER.'_ROBOTS'] = implode(', ', array_map(function($robot){ return $robot['robot_name']; }, array_slice($player['robots'], 0)));
                        $replace_in_markup[$PLAYER.'_BENCHED_ROBOTS'] = implode(', ', array_map(function($robot){ return $robot['robot_name']; }, array_slice($player['robots'], 1)));
                        $replace_in_markup[$PLAYER.'_ACTIVE_ROBOT'] = $active_robot['robot_name'];

                        $overview = array(
                            'name' => $active_robot['robot_name'],
                            'model' => $active_robot['robot_number'],
                            'class' => $active_robot['robot_description'],
                            'type' => (!empty($active_robot['robot_core']) ? ucfirst($active_robot['robot_core']) : 'Neutral')
                            );
                        if (!empty($active_robot['robot_weaknesses'])){ $overview['weak-to'] = array_map(function($str){ return ucfirst($str); }, $active_robot['robot_weaknesses']); }
                        if (!empty($active_robot['robot_immunities'])){ $overview['immune-to'] = array_map(function($str){ return ucfirst($str); }, $active_robot['robot_immunities']); }
                        $overview['description'] =  $active_robot['robot_description2'];
                        $overview['quotes'] =  array(
                            'start' => $active_robot['robot_quotes']['battle_start'],
                            'taunt' => $active_robot['robot_quotes']['battle_taunt'],
                            'victory' => $active_robot['robot_quotes']['battle_victory'],
                            'defeat' => $active_robot['robot_quotes']['battle_defeat']
                            );
                        foreach ($overview['quotes'] AS $type => $text){
                            $text = str_replace('{this_player}', $player['operator']['player_name'], $text);
                            $text = str_replace('{this_robot}', $active_robot['robot_name'], $text);
                            $text = str_replace('{target_player}', $alt_player['operator']['player_name'], $text);
                            $text = str_replace('{target_robot}', $alt_active_robot['robot_name'], $text);
                            $overview['quotes'][$type] = $text;
                        }
                        $replace_in_markup[$PLAYER.'_ACTIVE_ROBOT_OVERVIEW'] = json_encode($overview);

                    }

                    //error_log('$replace_in_markup = '.print_r($replace_in_markup, true));

                    // Create an index of keys and key aliases to replace with given values
                    $replace_in_markup_index = array();
                    foreach ($replace_in_markup AS $key => $value){
                        $replace_in_markup_index['#'.$key.'#'] = $value;
                        $replace_in_markup_index['{'.$key.'}'] = $value;
                        $replace_in_markup_index['{"'.$key.'"}'] = '"'.str_replace(', ', '", "', $value).'"';
                    }

                    error_log('$replace_in_markup_index = '.print_r($replace_in_markup_index, true));

                    // Define a quick function that will loop through a given string and search/replace $player1 and $player2 variables
                    $replace_player_variables = function($string) use ($replace_in_markup_index){
                        $string = str_replace(array_keys($replace_in_markup_index), array_values($replace_in_markup_index), $string);
                        return $string;
                    };

                    // Loop through the prompt output sections and add their contents to the prompt output markup variable, replacing variables as needed
                    $prompt_output_markup = array();
                    foreach ($prompt_output_sections AS $section_key => $section_info){
                        if (!empty($section_info['title'])){ $prompt_output_markup[] = '['.$section_info['title'].']'; }
                        $prompt_output_markup[] = $replace_player_variables($section_info['desc']);
                        if (!empty($section_info['data'])){
                            $prompt_output_markup[] = '';
                            foreach ($section_info['data'] AS $section_key => $section_data){
                                if ($section_key > 0){ $prompt_output_markup[] = ''; }
                                foreach ($section_data AS $section_data_line){
                                    $prompt_output_markup[] = $replace_player_variables($section_data_line);
                                }
                            }
                        }
                        $prompt_output_markup[] = '';
                    }
                    $prompt_output_markup = implode(PHP_EOL, $prompt_output_markup);

                }
                // Otherwise, just add the prompt errors to the markup directly
                else {
                    $prompt_output_markup = implode(PHP_EOL, $prompt_errors);
                }

                break;
            }
            default: {

                // Generate the markup for the undefined prompt
                $prompt_logic_markup .= 'Undefined prompt kind "'.$prompt_kind.'".';
                $prompt_output_markup .= '---';

                break;
            }

        }

        // Generate the markup for the prompt wrappers
        ob_start();
        ?>
            <div class="prompt-logic">
                <?= $prompt_logic_markup ?>
            </div>
            <div class="prompt-output">
                <pre><?= $prompt_output_markup ?></pre>
            </div>
        <?
        $html_markup .= trim(ob_get_clean());
        ob_start();
        ?>
            <style type="text/css">
                .prompt-output {
                    font-size: 110%;
                    text-align: left;
                    padding: 2rem;
                }
                .prompt-output pre {
                    display: block;
                    white-space: break-spaces;
                    background-color: #f5f5f5;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    padding: 1rem 4rem 1rem 2rem;
                    font-family: inherit;
                    font-size: inherit;
                    text-align: left;
                    outline: none;
                    transition: border-color 0.2s;
                    max-width: 100%;
                    overflow: auto;
                }
                .prompt-output pre:hover {
                    border-color: #999;
                }
            </style>
        <?
        $styles_markup .= trim(ob_get_clean());
        ob_start();
        ?>
            <script type="text/javascript">
                /* nothing yet */
            </script>
        <?
        $scripts_markup .= trim(ob_get_clean());

    }
    // Otherwise just print a message saying they need to select a prompt
    else {

        // Define a variable to hold instructions for seed data
        $example_seed_data = array();
        $required_seed_markup = '';
        if ($prompt_kind == 'battle-quotes'){
            $example_seed_data = array();
            $example_seed_data['player1'] = array('operator' => 'dr-light', 'robots' => ['ice-man', 'mega-man', 'roll']);
            $example_seed_data['player2'] = array('operator'=> 'dr-wily', 'robots' => ['sword-man', 'bass', 'disco']);
        }
        else {
            $example_seed_data = array('foo' => 'bar');
        }
        $required_seed_markup = json_encode($example_seed_data, JSON_PRETTY_PRINT);
        $required_seed_markup_encoded = htmlentities($required_seed_markup, ENT_QUOTES, 'UTF-8', true);

        // Generate the markup for the prompt wrappers
        ob_start();
        ?>
            <div class="prompt-feedback">
                <? if (empty($prompt_kind)){ ?>
                    <p class="intro">Please select a prompt kind from the dropdown at the top of the page.</p>
                <? } else {?>
                    <p class="intro">Now that you have selected a prompt kind, please provide the required seed data:</p>
                    <pre class="required"><?= $required_seed_markup_encoded ?></pre>
                <? } ?>
            </div>
        <?
        $html_markup .= trim(ob_get_clean());
        ob_start();
        ?>
            <style type="text/css">
                .prompt-feedback {
                    font-size: 120%;
                    text-align: left;
                    padding: 2rem;
                }
                .prompt-feedback .intro {
                    margin-bottom: 1rem;
                }
                .prompt-feedback pre.required {
                    display: inline-block;
                    background-color: #f5f5f5;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    padding: 1rem 4rem 1rem 2rem;
                    font-family: inherit;
                    font-size: inherit;
                    text-align: left;
                    outline: none;
                    transition: border-color 0.2s;
                    max-width: 100%;
                    overflow: auto;
                }
                .prompt-feedback pre.required:hover {
                    border-color: #999;
                }
            </style>
        <?
        $styles_markup .= trim(ob_get_clean());

    }

    echo($styles_markup.PHP_EOL);
    echo($scripts_markup.PHP_EOL);
    echo($html_markup.PHP_EOL);
    exit();

    ?>

<? $this_page_markup .= ob_get_clean(); ?>

