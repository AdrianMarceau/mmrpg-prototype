<?
// Define the class that will act as the mmrpg index file
class cms_admin {

    // Define a function for checking the current sort
    public static function is_sort_link_active($name, $dir = ''){
        global $sort_data;
        if (empty($name)){ return false; }
        if ($name != $sort_data['name']){ return false; }
        if (!empty($dir) && $dir != $sort_data['dir']){ return false; }
        return true;
    }

    // Define a function for generating an href sort link
    public static function get_sort_link_href($name, $dir){
        global $this_page_action, $search_data;
        $sort_link = 'admin/'.$this_page_action.'/search/';
        if (!empty($search_data)){
            $arg_strings = array();
            foreach ($search_data AS $n => $v){
                if (is_array($v)){ if (!empty($v)){ $arg_strings[] = $n.'='.urlencode(implode(',',$v)); } }
                else { if ($v !== ''){ $arg_strings[] = $n.'='.urlencode($v); } }
            }
            $sort_link .= '&'.implode('&', $arg_strings);
        }
        $sort_link .= '&order='.$name.':'.$dir;
        return $sort_link;
    }

    // Define a function for generating sort link markup
    public static function get_sort_link($name, $text = ''){
        global $sort_data;
        if (empty($text)){ $text = ucfirst($name); }
        $active = self::is_sort_link_active($name) ? true : false;
        $curr_dir = $active ? $sort_data['dir'] : '';
        $new_dir = $active && $curr_dir == 'asc' ? 'desc' : 'asc';
        $class = 'sort'.($active ? ' active '.$curr_dir : '');
        $href = self::get_sort_link_href($name, $new_dir);
        $link = '<a class="'.$class.'" href="'.$href.'"><span>'.$text.'</span></a>';
        return $link;
    }

    // Define a function for printing the totals in a table header/footer
    public static function get_totals_markup(){
        global $search_results_limit, $search_results_count, $search_results_total;
        $totals_markup = '';
        $totals_markup .= '<div class="totals_div">';
        if (!empty($search_results_limit)){ $totals_markup .= ('<span class="showing">Showing '.($search_results_count == 1 ? '1 Row' : $search_results_count.' Rows').'</span>');  }
        else {  $totals_markup .= ('<span class="showing">'.($search_results_count == 1 ? '1 Result' : $search_results_count.' Results').'</span>');  }
        if ($search_results_count != $search_results_total){ $totals_markup .= ('<span class="total">'.number_format($search_results_total, 0, '.', ',').' Total</span>'); }
        $totals_markup .= '</div>';
        return $totals_markup;
    }

    // Define a function for checking if a given string of PHP code has valid syntax
    public static function is_valid_php_syntax($file_content){
        $file_name = tempnam('/tmp', '_');
        file_put_contents($file_name, $file_content);
        //error_log('cms_admin::is_valid_php_syntax($file_content)');
        /*
        $command = "which php";
        exec($command, $output, $return);
        $output = trim(implode(PHP_EOL, $output));
        error_log('$command = '.print_r($command, true));
        error_log('$output = '.print_r($output, true));
        error_log('$return = '.print_r($return, true));
        */
        $command = "php -l {$file_name}";
        exec($command, $output, $return);
        $output = trim(implode(PHP_EOL, $output));
        //error_log('$command = '.print_r($command, true));
        //error_log('$output = '.print_r($output, true));
        //error_log('$return = '.print_r($return, true));
        unlink($file_name);
        return $return === 0 ? true : false;
    }

    // Define a function for checking if a given string of JSON code has valid syntax (array or object only)
    public static function is_valid_json_syntax($string){
        if (empty($string)){ return false; }
        elseif (strlen($string) < 2){ return false; }
        $fchar = substr($string, 0, 1); $lchar = substr($string, -1, 1);
        if (!($fchar === '[' && $lchar === ']') && !($fchar === '{' && $lchar === '}')){ return false; }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    // Define a function for collecting the types index, sorted, for admin use
    public static function get_roles_index(){
        global $db;
        $mmrpg_roles_fields = rpg_user_role::get_index_fields(true);
        $mmrpg_roles_index = $db->get_array_list("SELECT {$mmrpg_roles_fields} FROM mmrpg_roles WHERE role_level <> 0 ORDER BY role_level ASC", 'role_id');
        return $mmrpg_roles_index;
    }

    // Define a function for easily getting a contributors index for back-end puruposes
    public static function get_contributors_index($object_kind, $image_editor_id_field = ''){
        global $db;
        // Ensure we do not re-collect the same index multiple times in a given run
        static $index_cache;
        if (!is_array($index_cache)){ $index_cache = array(); }
        if (isset($index_cache[$object_kind.'/'.$image_editor_id_field])){
            $mmrpg_contributors_index = $index_cache[$object_kind.'/'.$image_editor_id_field];
        } else {
            // If not provided, get the image editor ID field from the global constant
            if (empty($image_editor_id_field)){ $image_editor_id_field = MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD; }
            // Ensure the provided object kind as allowed and determine the plural form
            $allowed_kinds = array('player', 'robot', 'field', 'ability', 'item');
            $count_object = in_array($object_kind, $allowed_kinds) ? $object_kind : $allowed_kinds[0];
            $count_object_plural = preg_match('/y$/i', $count_object) ? substr($count_object, 0, -1).'ies' : $count_object.'s';
            // Pull the contributor index different depending on the global constant (pre/post migration check)
            if ($image_editor_id_field === 'contributor_id'){
                $mmrpg_contributors_index = $db->get_array_list("SELECT
                    contributors.contributor_id AS contributor_id,
                    contributors.user_name AS user_name,
                    contributors.user_name_public AS user_name_public,
                    contributors.user_name_clean AS user_name_clean,
                    users.user_date_created,
                    users.user_date_accessed,
                    users.user_date_modified,
                    users.user_last_login,
                    uroles.role_level AS user_role_level,
                    (CASE WHEN editors.{$count_object}_image_count IS NOT NULL THEN editors.{$count_object}_image_count ELSE 0 END) AS user_image_count,
                    (CASE WHEN editors2.{$count_object}_image_count2 IS NOT NULL THEN editors2.{$count_object}_image_count2 ELSE 0 END) AS user_image_count2
                    FROM
                    mmrpg_users_contributors AS contributors
                    LEFT JOIN mmrpg_users AS users ON users.user_name_clean = contributors.user_name_clean
                    LEFT JOIN mmrpg_roles AS uroles ON uroles.role_id = users.role_id
                    LEFT JOIN (SELECT
                            {$count_object}_image_editor AS {$count_object}_user_id,
                            COUNT({$count_object}_image_editor) AS {$count_object}_image_count
                            FROM mmrpg_index_{$count_object_plural}
                            GROUP BY {$count_object}_image_editor) AS editors ON editors.{$count_object}_user_id = contributors.contributor_id
                    LEFT JOIN (SELECT
                            {$count_object}_image_editor2 AS {$count_object}_user_id,
                            COUNT({$count_object}_image_editor2) AS {$count_object}_image_count2
                            FROM mmrpg_index_{$count_object_plural}
                            GROUP BY {$count_object}_image_editor2) AS editors2 ON editors2.{$count_object}_user_id = contributors.contributor_id
                    WHERE
                    contributors.contributor_id <> 0
                    ORDER BY
                    uroles.role_level DESC,
                    contributors.user_name_clean ASC
                    ;", 'contributor_id');
            } else {
                $mmrpg_contributors_index = $db->get_array_list("SELECT
                    users.user_id AS user_id,
                    users.user_name AS user_name,
                    users.user_name_public AS user_name_public,
                    users.user_name_clean AS user_name_clean,
                    users.user_date_created,
                    users.user_date_accessed,
                    users.user_date_modified,
                    users.user_last_login,
                    uroles.role_level AS user_role_level,
                    (CASE WHEN editors.{$count_object}_image_count IS NOT NULL THEN editors.{$count_object}_image_count ELSE 0 END) AS user_image_count,
                    (CASE WHEN editors2.{$count_object}_image_count2 IS NOT NULL THEN editors2.{$count_object}_image_count2 ELSE 0 END) AS user_image_count2
                    FROM
                    mmrpg_users AS users
                    LEFT JOIN mmrpg_roles AS uroles ON uroles.role_id = users.role_id
                    LEFT JOIN (SELECT
                            {$count_object}_image_editor AS {$count_object}_user_id,
                            COUNT({$count_object}_image_editor) AS {$count_object}_image_count
                            FROM mmrpg_index_{$count_object_plural}
                            GROUP BY {$count_object}_image_editor) AS editors ON editors.{$count_object}_user_id = users.user_id
                    LEFT JOIN (SELECT
                            {$count_object}_image_editor2 AS {$count_object}_user_id,
                            COUNT({$count_object}_image_editor2) AS {$count_object}_image_count2
                            FROM mmrpg_index_{$count_object_plural}
                            GROUP BY {$count_object}_image_editor2) AS editors2 ON editors2.{$count_object}_user_id = users.user_id
                    WHERE
                    users.user_id <> 0
                    AND (uroles.role_level > 3
                        OR users.user_credit_line <> ''
                        OR users.user_credit_text <> ''
                        OR editors.{$count_object}_image_count IS NOT NULL
                        OR editors2.{$count_object}_image_count2 IS NOT NULL)
                    ORDER BY
                    uroles.role_level DESC,
                    users.user_name_clean ASC
                    ;", 'user_id');
            }
            $index_cache[$object_kind.'/'.$image_editor_id_field] = $mmrpg_contributors_index;
        }
        // Return the generated list
        return !empty($mmrpg_contributors_index) ? $mmrpg_contributors_index : array();
    }

    // Define a function for collecting the types index, sorted, for admin use
    public static function get_types_index(){
        global $db;
        $mmrpg_types_fields = rpg_type::get_index_fields(true);
        $mmrpg_types_index = $db->get_array_list("SELECT {$mmrpg_types_fields} FROM mmrpg_index_types ORDER BY type_order ASC", 'type_token');
        return !empty($mmrpg_types_index) ? $mmrpg_types_index : array();
    }

    // Define a function for collecting the players index, sorted, for admin use
    public static function get_players_index(){
        global $db;
        $mmrpg_players_fields = rpg_player::get_index_fields(true, 'players');
        $mmrpg_players_index = $db->get_array_list("SELECT
            {$mmrpg_players_fields},
            groups.group_token AS player_group,
            tokens.token_order AS player_order
            FROM mmrpg_index_players AS players
            LEFT JOIN mmrpg_index_players_groups_tokens AS tokens ON tokens.player_token = players.player_token
            LEFT JOIN mmrpg_index_players_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE players.player_id <> 0 AND players.player_token <> 'player'
            ORDER BY
            players.player_class ASC,
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'player_token');
        return !empty($mmrpg_players_index) ? $mmrpg_players_index : array();
    }

    // Define a function for collecting the robots index, sorted, for admin use
    public static function get_robots_index(){
        global $db;
        $mmrpg_robots_fields = rpg_robot::get_index_fields(true, 'robots');
        $mmrpg_robots_index = $db->get_array_list("SELECT
            {$mmrpg_robots_fields},
            groups.group_token AS robot_group,
            tokens.token_order AS robot_order
            FROM mmrpg_index_robots AS robots
            LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
            LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE robots.robot_id <> 0 AND robots.robot_token <> 'robot'
            ORDER BY
            FIELD(robots.robot_class, 'master', 'mecha', 'boss'),
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'robot_token');
        return !empty($mmrpg_robots_index) ? $mmrpg_robots_index : array();
    }

    // Define a function for collecting the abilities index, sorted, for admin use
    public static function get_abilities_index(){
        global $db;
        $mmrpg_abilities_fields = rpg_ability::get_index_fields(true, 'abilities');
        $mmrpg_abilities_index = $db->get_array_list("
            SELECT
            {$mmrpg_abilities_fields},
            groups.group_token AS ability_group,
            tokens.token_order AS ability_order
            FROM mmrpg_index_abilities AS abilities
            LEFT JOIN mmrpg_index_abilities_groups_tokens AS tokens ON tokens.ability_token = abilities.ability_token
            LEFT JOIN mmrpg_index_abilities_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE abilities.ability_id <> 0 AND abilities.ability_token <> 'ability' AND abilities.ability_class <> 'system'
            ORDER BY
            FIELD(abilities.ability_class, 'master', 'mecha', 'boss'),
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'ability_token');
        return !empty($mmrpg_abilities_index) ? $mmrpg_abilities_index : array();
    }

    // Define a function for collecting the items index, sorted, for admin use
    public static function get_items_index(){
        global $db;
        $mmrpg_items_fields = rpg_item::get_index_fields(true, 'items');
        $mmrpg_items_index = $db->get_array_list("SELECT
            {$mmrpg_items_fields},
            groups.group_token AS item_group,
            tokens.token_order AS item_order
            FROM mmrpg_index_items AS items
            LEFT JOIN mmrpg_index_items_groups_tokens AS tokens ON tokens.item_token = items.item_token
            LEFT JOIN mmrpg_index_items_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE items.item_id <> 0 AND items.item_token <> 'item' AND items.item_class <> 'system'
            ORDER BY
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'item_token');
        return !empty($mmrpg_items_index) ? $mmrpg_items_index : array();
    }

    // Define a function for collecting the skills index, sorted, for admin use
    public static function get_skills_index(){
        global $db;
        $mmrpg_skills_fields = rpg_skill::get_index_fields(true, 'skills');
        $mmrpg_skills_index = $db->get_array_list("SELECT
            {$mmrpg_skills_fields},
            groups.group_token AS skill_group,
            tokens.token_order AS skill_order
            FROM mmrpg_index_skills AS skills
            LEFT JOIN mmrpg_index_skills_groups_tokens AS tokens ON tokens.skill_token = skills.skill_token
            LEFT JOIN mmrpg_index_skills_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE skills.skill_id <> 0 AND skills.skill_token <> 'skill' AND skills.skill_class <> 'system'
            ORDER BY
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'skill_token');
        return !empty($mmrpg_skills_index) ? $mmrpg_skills_index : array();
    }

    // Define a function for collecting the fields index, sorted, for admin use
    public static function get_fields_index(){
        global $db;
        $mmrpg_fields_fields = rpg_field::get_index_fields(true, 'fields');
        $mmrpg_fields_index = $db->get_array_list("SELECT
            {$mmrpg_fields_fields},
            groups.group_token AS field_group,
            tokens.token_order AS field_order
            FROM mmrpg_index_fields AS fields
            LEFT JOIN mmrpg_index_fields_groups_tokens AS tokens ON tokens.field_token = fields.field_token
            LEFT JOIN mmrpg_index_fields_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE fields.field_id <> 0 AND fields.field_token <> 'field' AND fields.field_class <> 'system'
            ORDER BY
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'field_token');
        return !empty($mmrpg_fields_index) ? $mmrpg_fields_index : array();
    }

    // Define a function for collecting the music index, sorted, for admin use
    public static function get_music_index(){
        static $mmrpg_music_index;
        if (empty($mmrpg_music_index)){
            $mmrpg_music_index = array();
            $cdn_music_index = rpg_game::get_music_index();
            //error_log('$cdn_music_index = '.print_r($cdn_music_index, true));
            if (!empty($cdn_music_index['index'])){
                // Collect the raw music index from cdn data before sorting
                $raw_index_music = $cdn_music_index['index'];
                // Collect the raw source index and make a ref for ordering
                $source_orders = array();
                $raw_source_index = rpg_game::get_source_index();
                foreach ($raw_source_index AS $info){ $source_orders[$info['source_token']] = $info['source_order']; }
                // Sort the data by its source game first, then default to provided
                usort($raw_index_music, function($a, $b) use($source_orders){
                    $a_order = $source_orders[$a['music_game']];
                    $b_order = $source_orders[$b['music_game']];
                    if ($a_order < $b_order){ return -1; }
                    elseif ($a_order > $b_order){ return 1; }
                    else { return 0; }
                    });
                // Now loop through the index and create a new one w/ path as key
                foreach ($raw_index_music AS $key => $info){
                    $path = $info['music_path'];
                    $mmrpg_music_index[$path] = $info;
                }
            }
        }
        //error_log('$mmrpg_music_index = '.print_r($mmrpg_music_index, true));
        return !empty($mmrpg_music_index) ? $mmrpg_music_index : array();
    }

    // Define a function for printing the full name of a given environment
    public static function print_env_name($env, $ucfirst = false){
        if ($env === 'prod'){ $name = 'production'; }
        elseif ($env === 'stage'){ $name = 'staging'; }
        elseif ($env === 'dev'){ $name = 'developer'; }
        else { $name = $env; }
        return $ucfirst ? ucfirst($name) : $name;
    }

    // Define a function for printing admin home group options, given the list isn't empty
    public static function print_admin_home_group_options($this_group_name, $this_group_options = array(), $this_group_name_subtext = ''){

        // If the options were empty, return an empty string now
        if (empty($this_group_options)){ return ''; }

        // Loop through the option and generate item markup for each of them
        ob_start();
        $repo_change_kinds = self::git_get_change_kinds();
        foreach ($this_group_options AS $option_key => $option_info){
            $repo_icons = '';
            $repo_changes = array();
            $repo_debug = array();
            if (!empty($option_info['repo'])){
                $repo_config = $option_info['repo'];
                self::admin_home_group_get_repo_changes($repo_config, $repo_changes, true);
                //$repo_debug['$repo_changes'] = $repo_changes;
                foreach ($repo_change_kinds AS $kind_info){
                    $token = $kind_info['token'];
                    $icon = $kind_info['icon'];
                    $list = isset($repo_changes[$token]) ? $repo_changes[$token] : false;
                    if ($token === 'uncommitted'){
                        foreach ($list AS $subkey => $sublist){
                            if (!empty($sublist)){
                                $status = $token.'_'.$subkey;
                                $count = count($sublist);
                                $icon_span = '<i class="icon fa fa-'.($subkey === 'deletes' ? 'times' : $icon).'"></i>';
                                $count_span = ' <span class="count">'.$count.'</span>';
                                $title_attr = ' title="'.($count.' '.ucfirst($token).' '.ucfirst($count === 1 ? rtrim($subkey, 's') : $subkey)).'"';
                                $class_attr = ' class="status has_'.$status.'"';
                                if (!empty($option_info['link']['url']) && $subkey !== 'deletes'){
                                    $href_attr = ' href="'.$option_info['link']['url'].'?subaction=search&'.$repo_config['data']['prefix'].'_flag_'.$token.'_changes=1"';
                                    $repo_icons .= '<a'.$class_attr.$title_attr.$href_attr.'>'.$icon_span.$count_span.'</a>';
                                } else {
                                    $repo_icons .= '<span'.$class_attr.$title_attr.'>'.$icon_span.$count_span.'</span>';
                                }
                            }
                        }
                    } else {
                        if (!empty($list)){
                            $status = $token.'_changes';
                            $count = count($list);
                            $icon_span = '<i class="icon fa fa-'.$icon.'"></i>';
                            $count_span = ' <span class="count">'.$count.'</span>';
                            $title_attr = ' title="'.($count.' '.ucfirst($token).' '.($count === 1 ? 'Change' : 'Changes')).'"';
                            $class_attr = ' class="status has_'.$status.'"';
                            if (!empty($option_info['link']['url']) && $token !== 'committed'){
                                $href_attr = ' href="'.$option_info['link']['url'].'?subaction=search&'.$repo_config['data']['prefix'].'_flag_'.$token.'_changes=1"';
                                $repo_icons .= '<a'.$class_attr.$title_attr.$href_attr.'>'.$icon_span.$count_span.'</a>';
                            } else {
                                $repo_icons .= '<span'.$class_attr.$title_attr.'>'.$icon_span.$count_span.'</span>';
                            }
                        }
                    }
                }
            }
            $item_class = 'item';
            if (isset($option_info['link']['class'])){ $item_class .= ' '.$option_info['link']['class']; }
            elseif (isset($option_info['link']['text'])){ $item_class .= ' '.trim(preg_replace('/[^-_a-z0-9]+/', '-', strtolower($option_info['link']['text'])), '-'); }
            echo('<li class="'.$item_class.'">'.PHP_EOL);
                $link_url = isset($option_info['link']['url']) ? ' href="'.$option_info['link']['url'].'"' : '';
                $link_target = isset($option_info['link']['target']) ? ' target="'.$option_info['link']['target'].'"' : '';
                $link_class = isset($option_info['link']['class']) ? ' class="'.$option_info['link']['class'].'"' : '';
                $link_bullet = isset($option_info['link']['bullet']) ? '<i class="bullet icon fa fa-fw fa-'.$option_info['link']['bullet'].'"></i>' : '';
                $link_text = $option_info['link']['text'];
                $link_icons = '';
                $icon_tokens = array();
                if (isset($option_info['link']['icon'])){ $icon_tokens = array($option_info['link']['icon']); }
                elseif (isset($option_info['link']['icons'])){ $icon_tokens = $option_info['link']['icons']; }
                if (!empty($icon_tokens)){ foreach ($icon_tokens AS $token){ $link_icons .= '<i class="icon fa fa-'.$token.'"></i>'; } }
                echo('<div class="link">'.$link_bullet.'<a'.$link_url.$link_target.$link_class.'>'.$link_text.'</a>'.$link_icons.$repo_icons.'&nbsp;</div>'.PHP_EOL);
                $desc_text = $option_info['desc'];
                echo('<div class="desc">:&nbsp;<em>'.$desc_text.'</em></div>'.PHP_EOL);
                if (!empty($option_info['buttons'])){
                    $buttons_markup = array();
                    foreach ($option_info['buttons'] AS $button_key => $button_info){
                        if (isset($button_info['condition']['uncommitted']) && $button_info['condition']['uncommitted'] !== !empty($repo_changes['uncommitted'])){ continue; }
                        if (isset($button_info['condition']['committed']) && $button_info['condition']['committed'] !== !empty($repo_changes['committed'])){ continue; }
                        if (isset($button_info['condition']['permissions']) && !rpg_user::current_user_has_all_permissions($button_info['condition']['permissions'])){ continue; }
                        $button_text = $button_info['text'];
                        $button_attributes = '';
                        if (isset($button_info['action'])){ $button_attributes .= ' data-action="'.$button_info['action'].'"'; }
                        if (isset($button_info['attributes'])){ foreach ($button_info['attributes'] AS $a => $v){ $button_attributes .= ' '.$a.'="'.$v.'"'; } }
                        $buttons_markup[] = '<a class="button"'.$button_attributes.'">'.$button_text.'</a>';
                    }
                    if (!empty($buttons_markup)){
                        $count = count($buttons_markup);
                        $class = 'buttons';
                        echo('<div class="'.$class.'" data-count="'.$count.'">'.PHP_EOL);
                            echo(trim(implode(PHP_EOL, $buttons_markup)).PHP_EOL);
                        echo('</div>'.PHP_EOL);
                    }
                }
                if (!empty($repo_debug)){ echo('<pre class="repo_debug">'.print_r($repo_debug, true).'</pre>'); }
            echo('</li>'.PHP_EOL);
        }
        $this_group_items_markup = trim(ob_get_clean());

        // If item markup was generated, wrap it in a list with the title on top
        ob_start();
        if (!empty($this_group_items_markup)){
            echo('<ul class="adminhome '.strtolower(preg_replace('/\s+/', '-', $this_group_name)).'">'.PHP_EOL);
                echo('<li class="top">'.PHP_EOL);
                    echo('<strong>'.$this_group_name.'</strong>'.PHP_EOL);
                    if (!empty($this_group_name_subtext)){ echo($this_group_name_subtext.PHP_EOL); }
                echo('</li>'.PHP_EOL);
                echo($this_group_items_markup.PHP_EOL);
            echo('</ul>'.PHP_EOL);
        }
        $this_group_list_markup = trim(ob_get_clean());

        // Return generated group list markup, assuming it's not empty
        return $this_group_list_markup;

    }

    // Define functions for starting/stopping shell-exec timers
    static $shell_exec_total_time = 0;
    public static function shell_exec_total_time($flush = true){
        $total_time = self::$shell_exec_total_time;
        if ($flush){ self::$shell_exec_total_time = 0; }
        return $total_time;
    }

    // Define an abstraction function for running shell commands and returning input
    private static function shell_exec($command, $as_per_function){
        static $init_counter = array();
        if (!isset($init_counter[$as_per_function])){ $init_counter[$as_per_function] = 0; }
        $init_counter[$as_per_function]++;
        $start_time = microtime(true);
        $output = shell_exec($command);
        $end_time = microtime(true);
        $exec_time = (($end_time - $start_time) * 1000);
        self::$shell_exec_total_time += $exec_time;
        //$log = PHP_EOL.$as_per_function.' [call #'.$init_counter[$as_per_function].'] ['.ceil($exec_time).' ms]';
        //$log .= PHP_EOL.'shell_exec(\''.$command.'\')';
        //if (!empty($output)){ $log .= PHP_EOL.$output; }
        //error_log($log);
        return $output;
    }


    /* -- Git Functions -- */

    // Define a function for looping through all content directories and scanning for changes
    public static function git_scan_content_directory($content_path, $return_subkey = '', $use_cache = true){
        static $cache = array();
        if (isset($cache[$content_path]) && $use_cache){
            $cache_data = $cache[$content_path];
        } else {
            // Predefine arrays to hold all file statues
            $cache_data = array();
            $cache_data['modified'] = array();
            $cache_data['deleted'] = array();
            $cache_data['new'] = array();
            $cache_data['committed'] = array();
            // Define the cd prefix for all shell commands
            $src_func = 'git_scan_content_directories()';
            // Collect all MODIFIED and DELETED and NEW files in this directory
            $diff_cmd = 'cd '.$content_path.' ';
            $diff_cmd .= '&& echo "#MODIFIED" 2>&1 ';
            $diff_cmd .= '&& git diff --name-status ';
            $diff_cmd .= '&& echo "#UNTRACKED" 2>&1 ';
            $diff_cmd .= '&& git ls-files --others --exclude-standard ';
            $diff_cmd .= '&& echo "#COMMITTED" 2>&1 ';
            $diff_cmd .= '&& git log origin/master..HEAD --name-only --oneline ';
            $diff_return = self::shell_exec($diff_cmd, $src_func);
            if (!empty($diff_return)){
                $diff_list = array_filter(explode("\n", normalize_line_endings($diff_return)));
                $diff_list = array_map(function($str){ return trim($str, '" '); }, $diff_list);
                if (!empty($diff_list)){
                    $mode = false;
                    foreach ($diff_list AS $str){
                        if (substr($str, 0, 1) === '#'){ $mode = trim($str, '#'); continue; }
                        elseif (strstr($str, '.DS_Store') || strstr($str, 'Thumbs.db')){ continue; }
                        if ($mode === 'MODIFIED'){
                            list($stat, $path) = explode('||', preg_replace('/^([a-z])\s+(.*)$/i', '$1||$2', $str));
                            if (strtoupper($stat) === 'M'){ $cache_data['modified'][] = $path; }
                            elseif (strtoupper($stat) === 'D'){ $cache_data['deleted'][] = $path; }
                        } elseif ($mode === 'UNTRACKED'){
                            $cache_data['new'][] = $str;
                        } elseif ($mode === 'COMMITTED'){
                            if (preg_match('/([^\.]+)\.([a-z0-9]{3,})$/i', $str)){
                                $cache_data['committed'][] = $str;
                            }
                        }
                    }
                }
            }
            // Make sure there aren't any duplicates in each array
            foreach ($cache_data AS $k => $a){ $cache_data[$k] = array_unique($a); }
            // Cache the data that we've collected
            if ($use_cache){ $cache[$content_path] = $cache_data; }
        }
        // Return the cached data (or the sub-array if requested)
        if (!empty($return_subkey)){
            return !empty($cache_data[$return_subkey]) ? $cache_data[$return_subkey] : array();
        } else {
            return $cache_data;
        }
    }

    // Define a function for looping through all content directories and scanning for changes
    public static function git_scan_content_directories($index_by = '', $index_key = '', $force_refresh = false){
        static $content_cache = array();
        static $content_cache_by = array();
        static $content_types_index = array();
        if (empty($content_types_index)){ require(MMRPG_CONFIG_CONTENT_PATH.'index.php'); }
        if (empty($content_cache) || $force_refresh === true){
            foreach ($content_types_index AS $content_token => $content_info){
                if (empty($content_info['content_path'])){ continue; }
                $content_path = MMRPG_CONFIG_CONTENT_PATH.rtrim($content_info['content_path'], '/').'/';
                $cache_data = self::git_scan_content_directory($content_path);
                $content_cache[$content_token] = $cache_data;
                $content_cache_by['token'][$content_token] = &$content_cache[$content_token];
                $content_cache_by['path'][$content_path] = &$content_cache[$content_token];
            }
        }
        if (!empty($index_by) && !empty($index_key)){
            return !empty($content_cache_by[$index_by][$index_key]) ? $content_cache_by[$index_by][$index_key] : array();
        } elseif (!empty($index_by)){
            return !empty($content_cache_by[$index_by]) ? $content_cache_by[$index_by] : array();
        } else {
            return !empty($content_cache) ? $content_cache : array();
        }
    }

    // Define a function for returning the types of
    public static function git_get_change_kinds(){
        static $change_kinds;
        if (!is_array($change_kinds)){
            $change_kinds = array();
            $change_kinds[] = array('token' => 'uncommitted', 'icon' => 'asterisk');
            $change_kinds[] = array('token' => 'committed', 'icon' => 'check');
        }
        return $change_kinds;
    }

    // Define a function for checking if there are any unstaged and/or untracked files in a given repo (w/ optional path filter)
    public static function git_get_changes($repo_base_path, $filter_path = '', $filter_data = array()){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $unstaged = self::git_get_unstaged($repo_base_path);
            $untracked = self::git_get_untracked($repo_base_path);
            $deleted = self::git_get_deleted($repo_base_path);
            $changes = array_merge($unstaged, $untracked, $deleted);
            $index[$repo_base_path] = $changes;
        } else {
            $changes = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $changes = self::git_filter_list_by_path($changes, $filter_path); }
        if (!empty($filter_data)){ $changes = self::git_filter_list_by_data($changes, $filter_data); }
        return array_values($changes);
    }

    // Define a function for checking if there are any uncommitted (unstaged and/or untracked) changes in a given repo (w/ optional path filter)
    public static function git_get_uncommitted_changes($repo_base_path, $filter_path = '', $filter_data = array()){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $unstaged = self::git_get_unstaged($repo_base_path);
            $untracked = self::git_get_untracked($repo_base_path);
            $changes = array_merge($unstaged, $untracked);
            $index[$repo_base_path] = $changes;
        } else {
            $changes = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $changes = self::git_filter_list_by_path($changes, $filter_path); }
        if (!empty($filter_data)){ $changes = self::git_filter_list_by_data($changes, $filter_data); }
        return array_values($changes);
    }

    // Define a function for checking if there are any committed changes in a given repo (w/ optional path filter)
    public static function git_get_committed_changes($repo_base_path, $filter_path = '', $filter_data = array()){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $committed = self::git_get_committed($repo_base_path);
            $changes = $committed; //array_merge($committed, $foobar);
            $index[$repo_base_path] = $changes;
        } else {
            $changes = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $changes = self::git_filter_list_by_path($changes, $filter_path); }
        if (!empty($filter_data)){ $changes = self::git_filter_list_by_data($changes, $filter_data); }
        return array_values($changes);
    }

    // Define a function for checking if there are any deleted files in a given repo (w/ optional path filter)
    public static function git_get_deleted($repo_base_path, $filter_path = ''){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $deleted = self::git_scan_content_directory($repo_base_path, 'deleted');
            $index[$repo_base_path] = $deleted;
        } else {
            $deleted = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $deleted = self::git_filter_list_by_path($deleted, $filter_path); }
        return array_values($deleted);
    }

    // Define a function for checking if there are any unstaged files in a given repo (w/ optional path filter)
    public static function git_get_unstaged($repo_base_path, $filter_path = ''){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $modified = self::git_scan_content_directory($repo_base_path, 'modified');
            $new = self::git_scan_content_directory($repo_base_path, 'new');
            $unstaged = array_merge($modified, $new);
            $index[$repo_base_path] = $unstaged;
        } else {
            $unstaged = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $unstaged = self::git_filter_list_by_path($unstaged, $filter_path); }
        return array_values($unstaged);
    }

    // Define a function for checking if there are any untracked files in a given repo (w/ optional path filter)
    public static function git_get_untracked($repo_base_path, $filter_path = ''){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $untracked = self::git_scan_content_directory($repo_base_path, 'untracked');
            $index[$repo_base_path] = $untracked;
        } else {
            $untracked = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $untracked = self::git_filter_list_by_path($untracked, $filter_path); }
        return array_values($untracked);
    }

    // Define a function for checking if there are any committed (but unpushed) files in a given repo (w/ optional path filter)
    public static function git_get_committed($repo_base_path, $filter_path = ''){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $committed = self::git_scan_content_directory($repo_base_path, 'committed');
            $index[$repo_base_path] = $committed;
        } else {
            $committed = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $committed = self::git_filter_list_by_path($committed, $filter_path); }
        return array_values($committed);
    }

    // Define a function for getting a string-padded git ID token (for folder names)
    public static function git_get_id_token($kind, $id){
        return $kind.'-'.str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    // Define a function for getting a string-replaced git URL token (for folder names)
    public static function git_get_url_token($kind, $url){
        return str_replace('/', '_', trim($url, '/'));
    }

    // Define a function for updating the git remote status w/ cache to speed up repeat requests
    public static function git_update_remote($repo_base_path, $use_cache = true){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!$use_cache || !isset($index[$repo_base_path])){
            $last_updated = self::git_update_remote_get_time($repo_base_path);
            $update_timeout = 60 * 60; // 60 mins (one hour)
            if ((time() - $last_updated) >= $update_timeout){
                $index[$repo_base_path] = self::shell_exec('cd '.$repo_base_path.' && git remote update', 'git_update_remote()');
                self::git_update_remote_set_time($repo_base_path);
            } else {
                $index[$repo_base_path] = true;
            }
        }
    }

    // Define a function for getting the last updated time for a given repo base path
    public static function git_update_remote_get_times($use_cache = true){
        $cache_filename = MMRPG_CONFIG_CACHE_PATH.'indexes/cache.git_remote-update-times.json';
        static $update_times;
        if (!$use_cache || !is_array($update_times)){
            $update_times = array();
            if (file_exists($cache_filename)){
                $cache_json = file_get_contents($cache_filename);
                if (!empty($cache_json)){ $update_times = json_decode($cache_json, true); }
            }
        }
        return $update_times;
    }

    // Define a function for getting the last updated time for a given repo base path
    public static function git_update_remote_get_time($repo_base_path){
        $repo_base_path = str_replace(MMRPG_CONFIG_ROOTDIR, '/', $repo_base_path);
        $update_times = self::git_update_remote_get_times();
        if (isset($update_times[$repo_base_path])){ return $update_times[$repo_base_path]; }
        else { return 0; }
    }

    // Define a function for setting the last updated time for a given repo base path
    public static function git_update_remote_set_time($repo_base_path){
        $repo_base_path = str_replace(MMRPG_CONFIG_ROOTDIR, '/', $repo_base_path);
        $cache_filename = MMRPG_CONFIG_CACHE_PATH.'indexes/cache.git_remote-update-times.json';
        $update_times = self::git_update_remote_get_times(false);
        $update_times[$repo_base_path] = time();
        $fh = fopen($cache_filename, 'w');
        fwrite($fh, json_encode($update_times, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
        fclose($fh);
    }

    // Define a quick function for filtering a given list of git changes by base path
    public static function git_filter_list_by_path($list, $filter_path){
        if (empty($list)){ return array(); }
        elseif (empty($filter_path)){ return $list; }
        foreach ($list AS $key => $path){ if (substr($path, 0, strlen($filter_path)) !== $filter_path){ unset($list[$key]); } }
        return array_values($list);
    }

    // Define a quick function for filtering a given list of git changes by its data
    public static function git_filter_list_by_data($list, $filter_data, $pk_kind = 'token'){
        //error_log('<pre>git_filter_list_by_data($list, $filter_data)</pre>'.PHP_EOL);
        //error_log('<pre>$list = '.print_r($list, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_data = '.print_r($filter_data, true).'</pre>'.PHP_EOL);
        //echo('<pre>$pk_kind = '.print_r($pk_kind, true).'</pre>'.PHP_EOL);
        if (empty($list)){ return array(); }
        elseif (empty($filter_data)){ return $list; }
        elseif (empty($filter_data['table'])){ return $list; }
        elseif (empty($filter_data[$pk_kind])){ return $list; }

        $filter_by_table = $filter_data['table'];
        //echo('<pre>$filter_by_table = '.print_r($filter_by_table, true).'</pre>'.PHP_EOL);
        $filter_by_field = $filter_data[$pk_kind];
        //echo('<pre>$filter_by_field = '.print_r($filter_by_field, true).'</pre>'.PHP_EOL);

        $filter_values = array();
        foreach ($list AS $key => $path){
            list($folder) = explode('/', $path);
            if ($pk_kind === 'id'){ $value = intval(preg_replace('/^[a-z0-9]+-/i', '', $folder)); }
            elseif ($pk_kind === 'url'){ $value = trim(str_replace('_', '/', $folder), '/').'/'; }
            else { $value = $folder; }
            $filter_values[] = $value;
        }
        $filter_values = array_unique($filter_values);
        //echo('<pre>$filter_values = '.print_r($filter_values, true).'</pre>'.PHP_EOL);

        if ($pk_kind === 'id'){ $filter_values_string = implode(", ", array_unique($filter_values)); }
        else { $filter_values_string = "'".implode("', '", array_unique($filter_values))."'"; }
        //echo('<pre>$filter_values_string = '.print_r($filter_values_string, true).'</pre>'.PHP_EOL);

        $filter_by_extra = !empty($filter_data['extra']) ? $filter_data['extra'] : array();
        //echo('<pre>$filter_by_extra = '.print_r($filter_by_extra, true).'</pre>'.PHP_EOL);
        $filter_extras = array();
        if (!empty($filter_by_extra)){ foreach ($filter_by_extra AS $f => $v){ $filter_extras[] = "{$f} = '".(is_numeric($v) ? $v : str_replace("'", "\\'", $v))."'"; } }
        $filter_extras_string = !empty($filter_extras) ? ' AND '.implode(' AND ', $filter_extras) : '';
        //echo('<pre>$filter_extras = '.print_r($filter_extras, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_extras_string = '.print_r($filter_extras_string, true).'</pre>'.PHP_EOL);

        global $db;
        $filter_query = "SELECT {$filter_by_field} FROM {$filter_by_table} WHERE {$filter_by_field} IN ($filter_values_string){$filter_extras_string};";
        $filter_query_results = $db->get_array_list($filter_query, $filter_by_field);
        //echo('<pre>$filter_query = '.print_r($filter_query, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_query_results = '.print_r($filter_query_results, true).'</pre>'.PHP_EOL);

        $allowed_values = is_array($filter_query_results) ? array_keys($filter_query_results) : array();
        if (!empty($filter_by_extra)){ $allowed_values[] = '_groups/'.implode('', array_slice(array_values($filter_by_extra), 0, 1)); }
        elseif (isset($filter_data['token'])){ $allowed_values[] = '_groups/'.str_replace('_token', '', $filter_data['token']); }
        //echo('<pre>$allowed_values = '.print_r($allowed_values, true).'</pre>'.PHP_EOL);
        $allowed_folder_names = $allowed_values;
        if ($pk_kind === 'id'){ foreach ($allowed_folder_names AS $key => $id){ $allowed_folder_names[$key] = self::git_get_id_token(substr($filter_by_field, 0, -3), $id); } }
        elseif ($pk_kind === 'url'){ foreach ($allowed_folder_names AS $key => $url){ $allowed_folder_names[$key] = self::git_get_url_token(substr($filter_by_field, 0, -3), $url); } }
        //echo('<pre>$allowed_folder_names = '.print_r($allowed_folder_names, true).'</pre>'.PHP_EOL);

        //error_log('<pre>$list(before) = '.print_r($list, true).'</pre>'.PHP_EOL);
        foreach ($list AS $key => $path){
            if (!strstr($path, '/')){ unset($list[$key]); continue; }
            $folders = array_slice(explode('/', $path), 0, -1);
            $folder = $folders[0];
            $folder2 = isset($folders[1]) ? $folders[0].'/'.$folders[1] : false;
            if (!in_array($folder, $allowed_folder_names)
                && !in_array($folder2, $allowed_folder_names)){
                unset($list[$key]);
                }
        }
        $list = array_values($list);
        //error_log('<pre>$list(after) = '.print_r($list, true).'</pre>'.PHP_EOL);
        //exit();
        //echo('--------------------'.PHP_EOL);

        return array_values($list);
    }

    // Define a function for checking to see if a given git repo needs to be pulled
    public static function git_pull_required($repo_base_path, $use_cache = true){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if ($use_cache && isset($index[$repo_base_path])){
            // Collect the branch status from the existing cache
            $branch_status = $index[$repo_base_path];
        } else {
            // Update the branch with any changes on remote
            self::git_update_remote($repo_base_path);
            // Check the branch status so we can parse info
            $status_output = self::shell_exec('cd '.$repo_base_path.' && git status -uno', 'git_pull_required()');
            // If the status was empty, assume up-to-date and return false (no pull required)
            if (empty($status_output)){ return false; }
            // Otherwise, we can explode the branch status in lines for parsing
            $status_output = str_replace(array("\r\n", "\r", "\n"), PHP_EOL, $status_output);
            $status_output = array_filter(explode(PHP_EOL, $status_output));
            // If the first line doesan't start with "On branch" the format is unknown... (return false)
            if (!strstr($status_output[0], 'On branch ')){ return false; }
            // Collect the branch name from the first line as we know it's there
            $branch_name = trim(str_replace('On branch ', '', $status_output[0]));
            // Collect the branch status using known strings present in the second line
            if (strstr($status_output[1], "Your branch is behind 'origin/{$branch_name}' by")){ $branch_status = 'behind'; }
            elseif (strstr($status_output[1], "Your branch is ahead of 'origin/{$branch_name}' by")){ $branch_status = 'ahead'; }
            elseif (strstr($status_output[1], "Your branch and 'origin/{$branch_name}' have diverged,")){ $branch_status = 'diverged'; }
            else { $branch_status = 'up-to-date'; }
            //echo('<pre>$status_output = '.print_r($status_output, true).'</pre>'.PHP_EOL);
            //echo('<pre>$branch_name = '.print_r($branch_name, true).'</pre>'.PHP_EOL);
            $index[$repo_base_path] = $branch_status;
        }
        // Return true if branch status requires a pull, else return false
        //echo('<pre>$branch_status = '.print_r($branch_status, true).'</pre>'.PHP_EOL);
        if (in_array($branch_status, array('behind', 'diverged'))){ return true; }
        else { return false; }
    }

    // Define a function for checking to see if a given git repo needs can be pulled
    public static function git_pull_allowed($repo_base_path, $use_cache = true){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if ($use_cache && isset($index[$repo_base_path])){
            // Collect the allowed flag from the cache array
            $pull_allowed = $index[$repo_base_path];
        } else {
            // Check to make sure this branch does NOT have an uncommitted changes pending
            $unstaged = self::git_get_unstaged($repo_base_path);
            $untracked = self::git_get_untracked($repo_base_path);
            $pull_allowed = empty($unstaged) && empty($untracked) ? true : false;
            $index[$repo_base_path] = $pull_allowed;
        }
        // Return whether or not this branch is allowed to be pulled right now
        //echo('<pre>$pull_allowed = '.print_r($pull_allowed, true).'</pre>'.PHP_EOL);
        return $pull_allowed;
    }


    /* -- Git Functions for Admin Home -- */

    // Define a function for checking if a given home page option has uncommitted changes or unpulled updates
    public static function admin_home_group_get_repo_changes($repo_config, &$repo_changes, $filter_unique = false){
        if (empty($repo_config['path'])){ return false; }
        $repo_changes = array();

        // Collect uncommitted changes, uncommitted deletes, and committed changes/deletes
        $uncommitted_changes = cms_admin::git_get_uncommitted_changes($repo_config['path']);
        $uncommitted_deletes = cms_admin::git_get_deleted($repo_config['path']);
        $all_committed = cms_admin::git_get_committed_changes($repo_config['path']);

        // If any of the uncommitted changes are actually deletes, remove them from the first list
        if (!empty($uncommitted_deletes)){ $uncommitted_changes = array_diff($uncommitted_changes, $uncommitted_deletes); }

        // Filter each of the result arrays as necessary (if provided)
        if (!empty($uncommitted_changes) && !empty($repo_config['filter'])){ $uncommitted_changes = self::git_filter_list_by_data($uncommitted_changes, $repo_config['filter']); }
        if (!empty($uncommitted_deletes) && !empty($repo_config['filter'])){ $uncommitted_deletes = self::git_filter_list_by_data($uncommitted_deletes, $repo_config['filter']); }
        if (!empty($all_committed) && !empty($repo_config['filter'])){ $all_committed = self::git_filter_list_by_data($all_committed, $repo_config['filter']); }

        // Filter out unique values to ensure no duplicates (if requested)
        if (!empty($uncommitted_changes) && $filter_unique){ $unique = array(); foreach ($uncommitted_changes AS $k => $p){ list($t) = explode('/', $p); if (!in_array($t, $unique)){ $unique[] = $t; }  } $uncommitted_changes = $unique; }
        if (!empty($uncommitted_deletes) && $filter_unique){ $unique = array(); foreach ($uncommitted_deletes AS $k => $p){ list($t) = explode('/', $p); if (!in_array($t, $unique)){ $unique[] = $t; }  } $uncommitted_deletes = $unique; }
        if (!empty($all_committed) && $filter_unique){ $unique = array(); foreach ($all_committed AS $k => $p){ list($t) = explode('/', $p); if (!in_array($t, $unique)){ $unique[] = $t; }  } $all_committed = $unique; }

        // Add the results to the repo changes array
        $repo_changes['uncommitted'] = array();
        if (!empty($uncommitted_changes)){ $repo_changes['uncommitted']['changes'] = $uncommitted_changes; }
        if (!empty($uncommitted_deletes)){ $repo_changes['uncommitted']['deletes'] = $uncommitted_deletes; }
        $repo_changes['committed'] = $all_committed;

    }


    /* -- Git Functions for Admin Object Indexes -- */

    // Define a function for appending git-related status flags to a given object index flag list
    public static function object_index_flag_names_append_git_statuses(&$flag_names){
        $flag_names[] = array('break' => true);
        $change_kinds = self::git_get_change_kinds();
        foreach ($change_kinds AS $kind_info){
            $token = $kind_info['token'];
            $name = ucfirst($token);
            $icon = $kind_info['icon'];
            $flag_names[$token.'_changes'] = array(
                'icon' => 'fas fa-'.$icon,
                'yes' => $name.' Changes',
                'no' => 'No '.$name.' Changes'
                );
        }
    }

    // Define a function for appending git-related status flags to a given object index's search data
    public static function object_index_search_data_append_git_statuses(&$search_data, $object_kind){
        $change_kinds = self::git_get_change_kinds();
        foreach ($change_kinds AS $kind_info){
            $token = $kind_info['token'];
            $icon = $kind_info['icon'];
            $flag_name = $object_kind.'_flag_'.$token.'_changes';
            $search_data[$flag_name] = isset($_GET[$flag_name]) && $_GET[$flag_name] !== '' ? (!empty($_GET[$flag_name]) ? 1 : 0) : '';
        }
    }

    // Define a function for X to a given object index's search data
    public static function object_index_search_data_clean_query_values(&$search_data, $object_kind, &$backup_search_data){
        $backup_search_data = $search_data;
        foreach ($search_data AS $key => $value){
            if (is_string($value)){
                $clean_value = $value;
                $clean_value = cms_database::clean_query_value($clean_value);
                $search_data[$key] = $clean_value;
            }
        }
    }

    // Define a function for X to a given object index's search data
    public static function object_index_search_data_restore_backup_data(&$search_data, $object_kind, &$backup_search_data){
        $search_data = $backup_search_data;
        return ($search_data === $backup_search_data) ? true : false;
    }

    // Define a function for appending git-related status icons to a given object index link
    public static function object_index_links_append_git_statues(&$object_link, $object_token, $git_file_arrays){
        $change_kinds = self::git_get_change_kinds();
        foreach ($change_kinds AS $kind_info){
            $token = $kind_info['token'];
            $name = ucfirst($token);
            $icon = $kind_info['icon'];
            $array_name = 'mmrpg_git_'.$token.'_changes_tokens';
            if (isset($git_file_arrays[$array_name])
                && in_array($object_token, $git_file_arrays[$array_name])){
                $object_link .= ' <span class="status has_'.$token.'_changes" title="Has '.$name.' Changes"><i class="icon fa fa-'.$icon.'"></i></span>';
            }
        }
    }

    // Define a function for filtering git-related status flags from a given object index's search result data
    public static function object_index_search_results_filter_git_statuses(&$search_results, &$search_results_count, $search_data, $object_kind, $git_file_arrays = array()){
        $change_kinds = self::git_get_change_kinds();
        foreach ($change_kinds AS $kind_info){
            $token = $kind_info['token'];
            $name = ucfirst($token);
            $icon = $kind_info['icon'];
            $flag_name = $object_kind.'_flag_'.$token.'_changes';
            $array_name = 'mmrpg_git_'.$token.'_changes_tokens';
            if (isset($git_file_arrays[$array_name])
                && !empty($search_results)
                && $search_data[$flag_name] !== ''){
                foreach ($search_results AS $key => $data){
                    if ($object_kind === 'star' || $object_kind === 'challenge'){ $search_token = self::git_get_id_token($object_kind, $data[$object_kind.'_id']); }
                    elseif ($object_kind === 'page'){ $search_token = git_get_url_token($object_kind, $data[$object_kind.'_url']); }
                    else { $search_token = $data[$object_kind.'_token']; }
                    if ($search_data[$flag_name] && !in_array($search_token, $git_file_arrays[$array_name])){ unset($search_results[$key]); }
                    elseif (!$search_data[$flag_name] && in_array($search_token, $git_file_arrays[$array_name])){ unset($search_results[$key]); }
                }
                $search_results = array_values($search_results);
                $search_results_count = count($search_results);
            }
        }
    }


    /* -- Git Functions for Admin Object Editors -- */

    // Define a function for echoing git-related status flags in the header of a given object editor
    public static function object_editor_header_echo_git_statues($object_token, $git_file_arrays){
        $change_kinds = self::git_get_change_kinds();
        foreach ($change_kinds AS $kind_info){
            $token = $kind_info['token'];
            $name = ucfirst($token);
            $icon = $kind_info['icon'];
            $array_name = 'mmrpg_git_'.$token.'_changes_tokens';
            if (isset($git_file_arrays[$array_name])
                && in_array($object_token, $git_file_arrays[$array_name])){
                echo('<span class="status has_'.$token.'_changes" title="Has '.$name.' Changes"><i class="fas fa-'.$icon.'"></i></span>'.PHP_EOL);
            }
        }
    }

    // Define a function for printing git change buttons and list status in an object editor form
    public static function object_editor_print_git_footer_buttons($repo_kind, $path_token, $git_file_arrays = array()){

        // Break apart the repo kind/subkind if applicable
        if (strstr($repo_kind, '/')){ list($repo_kind, $repo_subkind) = explode('/', $repo_kind); }
        else { $repo_subkind = ''; }

        // Collect a list of change kinds so we can loop over 'em later
        $change_kinds = self::git_get_change_kinds();

        // Loop through and create filtered lists of changes applicable to this object
        $has_changes = false;
        $has_changes_kinds = array();
        $filtered_git_changes = array();
        $print_git_changes = array();
        $print_kind_icons = array();
        foreach ($change_kinds AS $kind_info){
            $token = $kind_info['token'];
            $name = ucfirst($token);
            $icon = $kind_info['icon'];
            $array_name = 'mmrpg_git_'.$token.'_changes';
            $array_tokens_name = 'mmrpg_git_'.$token.'_changes_tokens';
            if (!empty($git_file_arrays[$array_name])){
                $filtered_git_changes[$token] = array_filter($git_file_arrays[$array_name], function($path) use($path_token){
                    $folders = array_slice(explode('/', $path), 0, -1);
                    $folder = $folders[0];
                    $folder2 = isset($folders[1]) ? $folders[0].'/'.$folders[1] : false;
                    return ($folder === $path_token || $folder2 === $path_token) ? true : false;
                    });
                if (!empty($filtered_git_changes[$token])){
                    $has_changes = true;
                    $has_changes_kinds[$token] = true;
                    $print_git_changes[$token] = array_map(function($path) use($path_token){ return str_replace($path_token.'/', '', $path); }, $filtered_git_changes[$token]);
                    $print_git_changes[$token] = array_unique($print_git_changes[$token]);
                    $print_kind_icons[$token] = $icon;
                }
            }
        }

        // Generate button and/or list markup this object had changes of any kind
        ob_start();
        if ($has_changes){
            ?>
            <div class="buttons git-buttons" data-kind="<?= $repo_kind ?>" data-subkind="<?= $repo_subkind ?>" data-token="<?= $path_token ?>" data-source="github">
                <? if (!empty($has_changes_kinds['uncommitted'])){ ?>
                    <? if (rpg_user::current_user_has_permission('revert-changes')){ ?>
                        <a class="button revert" data-action="revert" type="button">Revert Changes</a>
                    <? } ?>
                    <? if (rpg_user::current_user_has_permission('commit-changes')){ ?>
                        <a class="button commit" data-action="commit" type="button">Commit Changes</a>
                    <? } ?>
                <? } ?>
                <? if (!empty($print_git_changes)){ ?>
                    <? foreach ($print_git_changes AS $kind => $list){ ?>
                        <div class="field git-list <?= $kind ?>">
                            <div class="title"><i class="fas fa fa-<?= $print_kind_icons[$kind] ?>"></i> <strong><?= ucfirst($kind) ?> Changes</strong></div>
                            <ul><li><?= implode('</li><li>', $list) ?></li></ul>
                        </div>
                    <? } ?>
                <? } ?>
            </div>
            <?
        }
        $temp_markup = ob_get_clean();

        // Return the generate markup
        return $temp_markup;
    }

    // Define a function for collecting a list of changes and updates for a given git repo
    public static function object_editor_get_git_file_arrays($repo_path, $repo_filters = array(), $pk_kind = 'token'){
        // Define an array to hold the various change lists
        $git_file_arrays = array();
        // Collect the change kinds and loop through to generate lists
        $change_kinds = self::git_get_change_kinds();
        foreach ($change_kinds AS $kind_info){
            $token = $kind_info['token'];
            $func_name = 'git_get_'.$token.'_changes';
            $array_name = 'mmrpg_git_'.$token.'_changes';
            $tokens_array_name = 'mmrpg_git_'.$token.'_changes_tokens';
            // Collect an index of changes files via git
            $changes_array = call_user_func(array(__CLASS__, $func_name), $repo_path);
            if (!empty($repo_filters)){ $changes_array = self::git_filter_list_by_data($changes_array, $repo_filters, $pk_kind); }
            // Now collect relevant player tokens from the list for matching
            $changes_array_tokens = array();
            foreach ($changes_array AS $key => $path){ list($token) = explode('/', $path); $changes_array_tokens[] = $token; }
            $changes_array_tokens = array_unique($changes_array_tokens);
            // Add the collected lists to the git file arrays
            $git_file_arrays[$array_name] = $changes_array;
            $git_file_arrays[$tokens_array_name] = $changes_array_tokens;
        }
        // Return the git path array to be exploded in the file
        return $git_file_arrays;
    }

    // Define a function for updating a given json file if the old and new contents are different
    public static function object_editor_update_json_data_file($object_kind, $updated_json_data, $ignore_fields_on_compare = array()){
        $object_xkind = substr($object_kind, -1, 1) === 'y' ? substr($object_kind, 0, -1).'ies' : $object_kind.'s';

        // Define the data base and token directories given object kind then append to form full path
        $json_data_base_dir = constant('MMRPG_CONFIG_'.strtoupper($object_xkind).'_CONTENT_PATH');
        if (in_array($object_kind, array('star', 'challenge'))){ $json_data_token_dir = $object_kind.'-'.str_pad($updated_json_data[$object_kind.'_id'], 4, '0', STR_PAD_LEFT); }
        elseif (in_array($object_kind, array('page'))){ $json_data_token_dir = str_replace('/', '_', trim($updated_json_data[$object_kind.'_url'], '/')); }
        else { $json_data_token_dir = $updated_json_data[$object_kind.'_token']; }
        $json_data_full_path = $json_data_base_dir.$json_data_token_dir.'/data.json';

        // Clean the new json data with settings specific to the object kind
        $onclean_remove_id_field = true;
        $onclean_remove_functions_field = true;
        $onclean_remove_protected_field = true;
        $onclean_encoded_sub_fields = array();
        if (in_array($object_kind, array('star', 'challenge', 'page'))){ $onclean_remove_id_field = false; }
        if ($object_kind === 'challenge'){ $onclean_encoded_sub_fields = array('challenge_field_data', 'challenge_target_data', 'challenge_reward_data'); }
        $new_json_data = self::object_editor_clean_json_content_array($object_kind, $updated_json_data, $onclean_remove_id_field, $onclean_remove_functions_field, $onclean_encoded_sub_fields);

        // Pull the old json data for comparrison with the new stuff
        $old_json_data = file_exists($json_data_full_path) ? json_decode(file_get_contents($json_data_full_path), true) : array();

        // Check to see if there are fields we need to remove before export
        if (method_exists('rpg_'.$object_kind, 'get_fields_excluded_from_json_export')){
            $skip_fields_on_json_export = call_user_func(array('rpg_'.$object_kind, 'get_fields_excluded_from_json_export'));
            if (!empty($skip_fields_on_json_export)){ foreach ($skip_fields_on_json_export AS $field){ unset($new_json_data[$field], $old_json_data[$field]); } }
        }

        // Always remove the "protected" flag fields as those are environment-specific
        if ($onclean_remove_protected_field){
            unset($new_json_data[$object_kind.'_flag_protected']);
            unset($old_json_data[$object_kind.'_flag_protected']);
        }

        // If this is a page request, extract and collect new/old html content separately
        if ($object_kind === 'page'){
            $html_content_full_path = $json_data_base_dir.$json_data_token_dir.'/content.html';
            $new_html_content = normalize_file_markup($new_json_data[$object_kind.'_content']);
            $old_html_content = file_exists($html_content_full_path) ? normalize_file_markup(file_get_contents($html_content_full_path)) : '';
            unset($new_json_data[$object_kind.'_content']);
        }

        // If old json doesn't exist or different than the new, update file
        $old_and_new_arrays_match = false;
        if (!empty($old_json_data) && !empty($new_json_data)){
            if (!empty($ignore_fields_on_compare)){
                $filtered_old_json_data = $old_json_data;
                $filtered_new_json_data = $new_json_data;
                if (is_string($ignore_fields_on_compare)){ $ignore_fields_on_compare = array($ignore_fields_on_compare); }
                foreach ($ignore_fields_on_compare AS $field){ unset($filtered_old_json_data[$field], $filtered_new_json_data[$field]); }
                $old_and_new_arrays_match = arrays_match($filtered_old_json_data, $filtered_new_json_data);
            } else {
                $old_and_new_arrays_match = arrays_match($old_json_data, $new_json_data);
            }
        }
        if (!$old_and_new_arrays_match){
            if (file_exists($json_data_full_path)){ unlink($json_data_full_path); }
            if (!file_exists(dirname($json_data_full_path))){ mkdir(dirname($json_data_full_path)); }
            $h = fopen($json_data_full_path, 'w');
            $new_json_data_markup = json_encode($new_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
            $new_json_data_markup = normalize_file_markup($new_json_data_markup);
            fwrite($h, $new_json_data_markup);
            fclose($h);
        }

        // Again, if this is a page request, we need to compare and update html files separately
        if ($object_kind === 'page'){
            if (empty($old_html_content) || (trim($old_html_content) !== trim($new_html_content))){
                if (file_exists($html_content_full_path)){ unlink($html_content_full_path); }
                if (!file_exists(dirname($html_content_full_path))){ mkdir(dirname($html_content_full_path)); }
                $h = fopen($html_content_full_path, 'w');
                $new_html_content_markup = trim($new_html_content);
                $new_html_content_markup = normalize_file_markup($new_html_content_markup);
                fwrite($h, $new_html_content_markup);
                fclose($h);
            }
        }

    }

    // Define a function for deleting a given json file from the relevant content repo
    public static function object_editor_delete_json_data_file($object_kind, $git_object_token, $delete_sibling_files = true){
        $object_xkind = substr($object_kind, -1, 1) === 'y' ? substr($object_kind, 0, -1).'ies' : $object_kind.'s';

        // Define a counter for the files deleted by this action
        $files_deleted = 0;

        // Define the data base and token directories given object kind then append to form full path
        $json_data_base_dir = constant('MMRPG_CONFIG_'.strtoupper($object_xkind).'_CONTENT_PATH');
        if (in_array($object_kind, array('star', 'challenge'))){ $json_data_token_dir = cms_admin::git_get_id_token($object_kind, $git_object_token); }
        elseif (in_array($object_kind, array('page'))){ $json_data_token_dir = cms_admin::git_get_url_token($object_kind, $git_object_token);  }
        else { $json_data_token_dir = $git_object_token; }
        $json_data_full_path = $json_data_base_dir.$json_data_token_dir.'/data.json';

        // Assuming it exists, delete the json data file from the repo
        if (file_exists($json_data_full_path)){
            //error_log('delete '.$json_data_full_path);
            unlink($json_data_full_path);
            $files_deleted++;
        }

        // If this is a page request, extract and collect new/old html content separately
        if ($delete_sibling_files
            && $object_kind === 'page'){
            $html_content_full_path = $json_data_base_dir.$json_data_token_dir.'/content.html';
            if (file_exists($html_content_full_path)){
                //error_log('delete '.$html_content_full_path);
                unlink($html_content_full_path);
                $files_deleted++;
            }
        }

        // If allowed, check for and delete any other files in this directory
        if ($delete_sibling_files){
            $other_files = getDirContents($json_data_base_dir.$json_data_token_dir);
            if (!empty($other_files)){
                foreach ($other_files AS $path){
                    if (is_dir($path)){
                        //error_log('delete '.$path);
                        deleteDir($path);
                        $files_deleted++;
                    } elseif (is_file($path)) {
                        //error_log('delete '.$path);
                        unlink($path);
                        $files_deleted++;
                    }
                }
            }
        }

        // Return the number of files deleted by this action
        return $files_deleted;

    }

    // Define a function for encoding an object array into json-compatible format for git export
    public static function object_editor_clean_json_content_array($kind, $src_json_data, $remove_id_field = true, $remove_functions_field = true, $encoded_sub_fields = array()){
        // Make a copy of the original JSON data
        $cleaned_json_data = $src_json_data;
        // Remove any known unnecessary or deprecated fields from the data
        if ($remove_id_field){ unset($cleaned_json_data[$kind.'_id']); }
        if ($remove_functions_field){ unset($cleaned_json_data[$kind.'_functions']); }
        // If not empty, loop through any encoded sub-fields and auto-expand them
        if (empty($encoded_sub_fields)
            && method_exists('rpg_'.$kind, 'get_json_index_fields')){
            $encoded_sub_fields = call_user_func(array('rpg_'.$kind, 'get_json_index_fields'));
        }
        if (!empty($encoded_sub_fields)){
            foreach ($encoded_sub_fields AS $sub_field_name){
                $sub_field_value = isset($cleaned_json_data[$sub_field_name]) ? $cleaned_json_data[$sub_field_name] : '';
                if (!empty($sub_field_value)){ $sub_field_value = json_decode($sub_field_value, true); }
                else { $sub_field_value = array(); }
                $cleaned_json_data[$sub_field_name] = $sub_field_value;
            }
        }
        // Collect an index of editor IDs to usernames for translation
        static $editor_ids_to_usernames;
        if (empty($editor_ids_to_usernames)){
            $contributor_index = self::get_contributors_index($kind);
            $editor_ids_to_usernames = array();
            foreach ($contributor_index AS $key => $data){
                $editor_ids_to_usernames[$data[MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD]] = $data['user_name_clean'];
            }
        }
        // If there are an image editor fields, translate them to contributor IDs
        $image_fields = array($kind.'_image_editor', $kind.'_image_editor2', $kind.'_creator');
        foreach ($image_fields AS $image_field){
            if (!isset($cleaned_json_data[$image_field])){ continue; }
            if (!empty($cleaned_json_data[$image_field])){
                $user_id = $cleaned_json_data[$image_field];
                if (!empty($editor_ids_to_usernames[$user_id])){
                    $contributor_username = $editor_ids_to_usernames[$user_id];
                    $cleaned_json_data[$image_field] = $contributor_username;
                }
            } else {
                $cleaned_json_data[$image_field] = '';
            }
        }
        // Return the cleaned JSON data
        return $cleaned_json_data;
    }


    /* -- OBJECT GROUPING FUNCTIONS -- */

    // Define a function for generating object group arrays from index data (will only by used during migration)
    public static function generate_object_groups_from_index($object_index, $kind){

        // Generate the plural form of the provided kind token
        if (substr($kind, -1, 1) === 'y'){ $xkind = substr($kind, 0, -1).'ies'; }
        elseif (substr($kind, -2, 2) === 'ss'){ $xkind = $kind.'es'; }
        else { $xkind = $kind.'s'; }

        // Sort the provided index by object order field (assuming it's there)
        uasort($object_index, function($a1, $a2) use($kind){
            if (empty($a1[$kind.'_flag_hidden']) && !empty($a2[$kind.'_flag_hidden'])){ return -1; }
            elseif (!empty($a1[$kind.'_flag_hidden']) && empty($a2[$kind.'_flag_hidden'])){ return 1; }
            else { return $a1[$kind.'_order'] < $a2[$kind.'_order'] ? -1 : 1; }
            });

        // If object of a specific type, user different class/group values
        $class_field = $kind.'_class';
        $group_field = $kind.'_group';
        if ($kind === 'player' || $kind === 'item' || $kind === 'field'){ $class_field = false; }
        if ($kind === 'field'){ $group_field = $kind.'_game'; }

        // Filter object data for known sorting issues (for migration, I guess)
        $group_append = '';
        $group_overrides = array();
        if ($kind === 'field'){
            // Re-group the various non-master fields in the game
            $group = 'MMRPG/Intro';
            $tokens = array('gentle-countryside', 'maniacal-hideaway', 'wintry-forefront');
            foreach ($tokens AS $token){ $group_overrides[$token] = $group; }
            $group = 'MMRPG/Home';
            $tokens = array('light-laboratory', 'wily-castle', 'cossack-citadel');
            foreach ($tokens AS $token){ $group_overrides[$token] = $group; }
            $group = 'MMRPG/Finale';
            $tokens = array('final-destination', 'final-destination-2', 'final-destination-3');
            foreach ($tokens AS $token){ $group_overrides[$token] = $group; }
            $group = 'MMRPG/Bonus';
            $tokens = array('prototype-complete');
            foreach ($tokens AS $token){ $group_overrides[$token] = $group; }
            $group = 'MM99/Master';
            $tokens = array('crystal-catacombs', 'gemstone-cavern');
            foreach ($tokens AS $token){ $group_overrides[$token] = $group; }
            // Append a string to end of all master fields in the game
            $group_append = '/Master';
        }

        // Loop through and generate the different groups given existing object data
        $object_groups = array();
        foreach ($object_index AS $object_key => $object_info){
            if ($object_info[$kind.'_class'] === 'system'){ continue; }
            // Collect token for this object
            $token = $object_info[$kind.'_token'];
            // Collect group for this object, mod if necessary
            $group = !empty($group_field) && isset($object_info[$group_field]) ? $object_info[$group_field] : 'MMRPG';
            if (!empty($object_info[$kind.'_flag_hidden'])){ $group = 'Hidden'; }
            elseif (!empty($group_overrides[$token])){ $group = $group_overrides[$token]; }
            elseif (!empty($group_append)){ $group .= $group_append; }
            if (empty($group)){ $group = 'MMRPG'; }
            // Collect class for this object, mod if necessary
            $class = !empty($class_field) && isset($object_info[$class_field]) ? $object_info[$class_field] : $kind;
            if (!isset($object_groups[$class])){ $object_groups[$class] = array(); }
            // Add group w/ info to array if not already there
            if (!isset($object_groups[$class][$group])){
                $group_info = array();
                $group_info['group_class'] = $class;
                $group_info['group_token'] = $group;
                $group_info['group_order'] = count($object_groups[$class]);
                $group_info['group_child_tokens'] = array();
                } else {
                $group_info = $object_groups[$class][$group];
                }
            // Append this object's token to the list of child tokens
            $group_info['group_child_tokens'][] = $token;
            // Update this group's data in the parent array w/ changes
            $object_groups[$class][$group] = $group_info;
        }

        // Return the generated object groups
        return $object_groups;

    }

    // Define a function for pulling object groups from the database (will be used post-migration)
    public static function get_object_groups_from_database($kind, $class = '', $filter_empty = false){

        // Generate the plural form of the provided kind token
        if (substr($kind, -1, 1) === 'y'){ $xkind = substr($kind, 0, -1).'ies'; }
        elseif (substr($kind, -2, 2) === 'ss'){ $xkind = $kind.'es'; }
        else { $xkind = $kind.'s'; }

        // Pull in the global database object
        global $db;

        // Check to see whether we should respect sub-classes for this type
        $force_kind_as_class = !in_array($kind, array('robot', 'ability')) ? true : false;

        // Collect the group and group tokens from the database
        $select_where = !empty($class) ? "WHERE groups.group_class = '{$class}'" : '';
        $raw_groups = $db->get_array_list("SELECT
            groups.group_id, groups.group_class, groups.group_token, groups.group_order
            FROM mmrpg_index_{$xkind}_groups AS groups
            {$select_where}
            ORDER BY groups.group_order ASC
            ;", 'group_id');
        $join_on = "groups.group_token = tokens.group_token ";
        $join_on .= "AND ".(!$force_kind_as_class ? "groups.group_class = {$xkind}.{$kind}_class" : "groups.group_class = '{$class}'");
        $raw_groups_tokens = $db->get_array_list("SELECT
            tokens.token_id, groups.group_class, tokens.group_token, tokens.{$kind}_token, tokens.token_order
            FROM mmrpg_index_{$xkind}_groups_tokens AS tokens
            LEFT JOIN mmrpg_index_{$xkind} AS {$xkind} ON {$xkind}.{$kind}_token = tokens.{$kind}_token
            LEFT JOIN mmrpg_index_{$xkind}_groups AS groups ON {$join_on}
            {$select_where}
            ORDER BY groups.group_order ASC, tokens.token_order ASC
            ;", 'token_id');

        // Collect a list of all relevant object tokens from the database
        $select_where = '';
        if (!empty($class) && !$force_kind_as_class){ $select_where = "AND {$xkind}.{$kind}_class = '{$class}'"; }
        $select_as_class = !$force_kind_as_class ? "{$xkind}.{$kind}_class" : "'{$kind}'";
        $raw_tokens_list = $db->get_array_list("SELECT
            {$xkind}.{$kind}_token AS token,
            {$select_as_class} AS class
            FROM mmrpg_index_{$xkind} AS {$xkind}
            WHERE {$xkind}.{$kind}_class <> 'system' {$select_where}
            ORDER BY {$xkind}.{$kind}_class ASC, {$xkind}.{$kind}_token ASC
            ;");

        // Collect the official index of all objects for name, flag, etc. values
        $object_index = call_user_func(array('rpg_'.$kind, 'get_index'), true, true);

        // Define arrays for existing tokens vs used ones so we can keep track
        $existing_tokens_list = array();
        $parsed_tokens_list = array();
        if (!empty($raw_tokens_list)){
            foreach ($raw_tokens_list AS $info){
                $info_class = !$force_kind_as_class ? $info['class'] : $kind;
                if (!isset($existing_tokens_list[$info_class])){ $existing_tokens_list[$info_class] = array(); }
                if (!isset($parsed_tokens_list[$info_class])){ $parsed_tokens_list[$info_class] = array(); }
                $existing_tokens_list[$info_class][] = $info['token'];
            }
        }

        // Define an array to hold all the parsed group details
        $object_groups = array();

        // Loop through raw groups and add them to the final array one-by-one
        if (!empty($raw_groups)){
            $group_order = 0;
            foreach ($raw_groups AS $group_id => $group_info){
                $group_class = $group_info['group_class'];
                $group_token = $group_info['group_token'];
                $object_groups[$group_class][$group_token] = array(
                    'group_class' => $group_class,
                    'group_token' => $group_token,
                    'group_order' => $group_order++,
                    'group_child_tokens' => array()
                    );
            }
        }

        // Loop through parent array to create any missing required groups
        if (!empty($object_groups)){
            foreach ($object_groups AS $group_class => $group_data){

                // Create an array to hold the required groups at end of list
                $required_groups = array();

                // Define the next "order" value for the required groups
                $next_order = count($object_groups[$group_class]) - 1;

                // Create missing "hidden" group if not exists for orphan tokens if not exists
                if (!isset($object_groups[$group_class]['Hidden'])){
                    $object_groups[$group_class]['Hidden'] = array(
                        'group_class' => $group_class,
                        'group_token' => 'Hidden',
                        'group_order' => 0,
                        'group_child_tokens' => array()
                        );
                }
                $group_data = $object_groups[$group_class]['Hidden'];
                $group_data['group_order'] = $next_order++;
                unset($object_groups[$group_class]['Hidden']);
                $required_groups['Hidden'] = $group_data;

                // Create missing "unsorted" group if not exists for orphan tokens if not exists
                if (!isset($object_groups[$group_class]['Unsorted'])){
                    $object_groups[$group_class]['Unsorted'] = array(
                        'group_class' => $group_class,
                        'group_token' => 'Unsorted',
                        'group_order' => 0,
                        'group_child_tokens' => array()
                        );
                }
                $group_data = $object_groups[$group_class]['Unsorted'];
                $group_data['group_order'] = $next_order++;
                unset($object_groups[$group_class]['Unsorted']);
                $required_groups['Unsorted'] = $group_data;

                // Merge in the required groups at the bottom of the parent list
                $object_groups[$group_class] = array_merge($object_groups[$group_class], $required_groups);

            }
        }

        // Loop through the raw group tokens and add them to the parent arrays one-by-one
        if (!empty($raw_groups_tokens)){
            foreach ($raw_groups_tokens AS $token_id => $token_info){
                $group_class = !$force_kind_as_class ? $token_info['group_class'] : $kind;
                $group_token = $token_info['group_token'];
                $object_token = $token_info[$kind.'_token'];
                $object_data = isset($object_index[$object_token]) ? $object_index[$object_token] : false;
                if (!isset($object_groups[$group_class][$group_token])){
                    if (!empty($object_data[$kind.'_flag_hidden'])){ $group_token = 'Hidden'; }
                    else { $group_token = 'Unsorted'; }
                }
                $object_groups[$group_class][$group_token]['group_child_tokens'][] = $object_token;
                if (!in_array($object_token, $parsed_tokens_list[$group_class])){
                    $parsed_tokens_list[$group_class][] = $object_token;
                }
            }
        }

        // If there were any tokens not accounted for, add them to an "unsorted" group
        if (!empty($existing_tokens_list)){
            foreach ($existing_tokens_list AS $class => $tokens){
                foreach ($tokens AS $token){
                    if (!in_array($token, $parsed_tokens_list[$class])){
                        $object_groups[$class]['Unsorted']['group_child_tokens'][] = $token;
                    }
                }
            }
        }

        // If requested, remove any groups that are empty
        if ($filter_empty){
            foreach ($object_groups AS $group_class => $group_list){
                // If the group list is empty, remove by class and continue
                if (empty($group_list)){
                    unset($object_groups[$group_class]);
                    continue;
                }
                // Loop through group list and if children empty, remove by token and continue
                foreach ($group_list AS $group_token => $group_info){
                    if (empty($group_info['group_child_tokens'])){
                        unset($object_groups[$group_class][$group_token]);
                        continue;
                    }
                }
            }
        }

        // Return generated object groups array
        return $object_groups;

    }

    // Define a function for saving generated object groups to the database (will be used post-migration as well)
    public static function save_object_groups_to_database($object_groups, $kind, $class = ''){

        // Generate the plural form of the provided kind token
        if (substr($kind, -1, 1) === 'y'){ $xkind = substr($kind, 0, -1).'ies'; }
        elseif (substr($kind, -2, 2) === 'ss'){ $xkind = $kind.'es'; }
        else { $xkind = $kind.'s'; }

        // Truncate existing data for relevant group database tables
        global $db;
        if (empty($class)){
            $db->query('TRUNCATE mmrpg_index_'.$xkind.'_groups;');
            $db->query('TRUNCATE mmrpg_index_'.$xkind.'_groups_tokens;');
        } else {
            $db->query("DELETE groups
                FROM mmrpg_index_{$xkind}_groups AS groups
                WHERE groups.group_class = '{$class}'
                ;");
            $db->query("DELETE tokens
                FROM mmrpg_index_{$xkind}_groups_tokens AS tokens
                WHERE tokens.group_class = '{$class}'
                ;");
        }

        // Loop through provided groups and insert data in database tables
        foreach ($object_groups AS $group_class => $group_list){
            $group_order = 0;
            foreach ($group_list AS $group_key => $group_data){
                if ($group_key === 'Unsorted'){ continue; }
                $group_child_tokens = !empty($group_data['group_child_tokens']) ? $group_data['group_child_tokens'] : array();
                unset($group_data['group_child_tokens']);
                $group_order++;
                $group_data['group_order'] = $group_order;
                $db->insert('mmrpg_index_'.$xkind.'_groups', $group_data);
                foreach ($group_child_tokens AS $child_key => $child_token){
                    $token_data = array();
                    $token_data['group_class'] = $group_class;
                    $token_data['group_token'] = $group_data['group_token'];
                    $token_data[$kind.'_token'] = $child_token;
                    $token_data['token_order'] = $child_key;
                    $db->insert('mmrpg_index_'.$xkind.'_groups_tokens', $token_data);
                }
            }
        }

        // Return true on success
        return true;

    }

    // Define a function for saving generated object groups to the database (will be used post-migration as well)
    public static function save_object_groups_to_json($object_groups, $kind, $class = ''){

        // Generate the plural form of the provided kind token
        if (substr($kind, -1, 1) === 'y'){ $xkind = substr($kind, 0, -1).'ies'; }
        elseif (substr($kind, -2, 2) === 'ss'){ $xkind = $kind.'es'; }
        else { $xkind = $kind.'s'; }

        // Define the base path and file name for the data JSON file
        $object_groups_path = MMRPG_CONFIG_CONTENT_PATH.$xkind.'/_groups/';
        //$object_groups_json_path = $object_groups_path.'data.json';

        // Remove if already exists then create the new directory
        if (empty($class) && file_exists($object_groups_path)){ deleteDir($object_groups_path); }
        if (!file_exists($object_groups_path)){ mkdir($object_groups_path); }

        // If a class was defined, nest the provided groups array one level
        if (!empty($class) && !isset($object_groups[$class])){ $object_groups = array($class => $object_groups); }

        // Loop through the various object group categories (we create files for each)
        $new_files_attempted = 0;
        $new_files_created = 0;
        foreach ($object_groups AS $group_class => $group_data){

            // Define the base path and file name for the data JSON file
            $object_group_class_path = $object_groups_path.$group_class.'/';
            $object_group_class_json_path = $object_group_class_path.'data.json';

            // Remove if already exists then create the new directory
            if (file_exists($object_group_class_path)){ deleteDir($object_group_class_path); }
            if (!file_exists($object_group_class_path)){ mkdir($object_group_class_path); }

            // Write the provided groups to a new JSON file and close
            $h = fopen($object_group_class_json_path, 'w');
            fwrite($h, normalize_file_markup(json_encode($group_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
            fclose($h);

            // Increment the return variable if exists
            $new_files_attempted += 1;
            $new_files_created += (file_exists($object_group_class_json_path) ? 1 : 0);

        }

        // Return true on success, false on failure
        return $new_files_created === $new_files_attempted ? true : false;

    }

    /* -- SQL IMPORT / EXPORT FUNCTIONS -- */

    // Define a function for exporting a given database table's data into an SQL file
    public static function export_table_data_to_sql($table_name, $export_file_path = '', $export_filter = array()){
        if (empty($export_file_path)){ $export_file_path = MMRPG_CONFIG_ROOTDIR.'content/.sql/data/'.$table_name.'.sql'; }
        $table_settings = array('name' => $table_name, 'export_table' => false, 'export_data' => true, 'export_filter' => $export_filter);
        $table_rows_sql = cms_database::get_insert_table_data_sql($table_name, $table_settings, true);
        //echo('<pre>$table_name = '.print_r($table_name, true).'</pre>');
        //echo('<pre>$table_rows_sql = '.print_r($table_rows_sql, true).'</pre>');
        //echo('<pre>$export_file_path = '.print_r($export_file_path, true).'</pre>');
        if (!empty($table_rows_sql)){
            $f = fopen($export_file_path, 'w');
            fwrite($f, $table_rows_sql);
            fclose($f);
            if (file_exists($export_file_path)){
                return true;
                }
        }
        return false;
    }


    /* -- ERROR LOG FUNCTIONS -- */

    /**
     * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
     * @author Torleif Berger, Lorenzo Stanco
     * @link http://stackoverflow.com/a/15025877/995958
     * @license http://creativecommons.org/licenses/by/3.0/
     */
    public static function get_last_lines_of_file($file_path, $lines = 1, $adaptive = true){

        // Open file
        $f = @fopen($file_path, "rb");
        if ($f === false) return false;

        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        if (!$adaptive) $buffer = 4096;
        else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") $lines -= 1;

        // Start reading
        $output = '';
        $chunk = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {

            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);

            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);

            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;

            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");

        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {

            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);

        }

        // Close file and return
        fclose($f);
        return trim($output);

    }

    // Count the number of lines in a given file
    // As per https://stackoverflow.com/a/2162528
    public static function count_lines_in_file($file_path){
        if (!file_exists($file_path)){ return 0; }
        $linecount = 0;
        $handle = fopen($file_path, "r");
        while(!feof($handle)){
          $line = fgets($handle);
          $linecount++;
        }
        fclose($handle);
        return $linecount;
    }


    // -- PERMISSIONS FUNCTIONS -- //

    // Define a recusive function for printing a user permissions table for the admin panel user-editor
    public static function print_user_permissions_table($permissions_table, $user_permission_tokens, $base_perm_token = 'allow'){
        static $row_odd_even;
        static $admin_permission_tokens;
        if (empty($permissions_table)){ return ''; }
        static $current_user_permission_tokens;
        if (empty($current_user_permission_tokens)){
            $current_user_permission_tokens = rpg_user::current_user_permission_tokens();
        }
        $permissions_markup = '';
        $permissions_markup .= '<ul>';
        foreach ($permissions_table AS $perm_token => $sub_permissions_table){
            $full_perm_token = (!empty($base_perm_token) ? $base_perm_token.'_' : '').$perm_token;
            if (!in_array($perm_token, $current_user_permission_tokens)){
                if (empty($sub_permissions_table)){ continue; }
                $sub_permissions_tokens = rpg_user::get_permission_tokens_from_table(array($perm_token => $sub_permissions_table));
                if (!rpg_user::current_user_has_any_permissions($sub_permissions_tokens)){
                    continue;
                } else {
                    $sub_permissions_markup = self::print_user_permissions_table($sub_permissions_table, $user_permission_tokens, $full_perm_token);
                    $sub_permissions_markup = substr(trim($sub_permissions_markup), 4, -5);
                    $permissions_markup .= $sub_permissions_markup;
                    continue;
                }
            }
            $row_odd_even = $row_odd_even !== 'odd' ? 'odd' : 'even';
            $label = ucwords(str_replace('-', ' ', $perm_token));
            $is_checked = in_array($perm_token, $user_permission_tokens) ? true : false;
            $is_disabled = !in_array($perm_token, $current_user_permission_tokens) ? true : false;
            $attr_checked = !$is_disabled && $is_checked ? ' checked="checked"' : '';
            $attr_disabled = $is_disabled ? ' disabled="disabled"' : '';
            $cls_disabled = $is_disabled ? ' disabled' : '';
            $permissions_markup .= '<li class="'.$row_odd_even.$cls_disabled.'">'.PHP_EOL;
                $permissions_markup .= '<div class="field checkwrap">'.PHP_EOL;
                    $permissions_markup .= '<label class="label">'.PHP_EOL;
                        $permissions_markup .= '<input class="hidden" type="hidden" name="user_access_permissions['.$full_perm_token.']" value="0"'.$attr_disabled.' />'.PHP_EOL;
                        $permissions_markup .= '<input class="checkbox" type="checkbox" name="user_access_permissions['.$full_perm_token.']" value="1"'.$attr_checked.$attr_disabled.' />'.PHP_EOL;
                        $permissions_markup .= '<strong>'.$label.'</strong>'.PHP_EOL;
                    $permissions_markup .= '</label>'.PHP_EOL;
                $permissions_markup .= '</div>'.PHP_EOL;
                if (!empty($sub_permissions_table)){
                    $permissions_markup .= self::print_user_permissions_table($sub_permissions_table, $user_permission_tokens, $full_perm_token);
                }
            $permissions_markup .= '</li>'.PHP_EOL;
        }
        $permissions_markup .= '</ul>';
        return $permissions_markup;
    }

}
?>