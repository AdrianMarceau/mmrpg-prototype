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
            foreach ($search_data AS $n => $v){ if ($v !== ''){ $arg_strings[] = $n.'='.urlencode($v); } }
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
        if ($search_results_count != $search_results_total){ $totals_markup .= ('<span class="total">'.$search_results_total.' Total</span>'); }
        $totals_markup .= '</div>';
        return $totals_markup;
    }

    // Define a function for checking if a given string of PHP code has valid syntax
    public static function is_valid_php_syntax($fileContent){
        $filename = tempnam('/tmp', '_');
        file_put_contents($filename, $fileContent);
        exec("php -l {$filename}", $output, $return);
        $output = trim(implode(PHP_EOL, $output));
        unlink($filename);
        return $return === 0 ? true : false;
    }

    // Define a function for easily getting a contributors index for back-end puruposes
    public static function get_contributors_index($object_kind, $image_editor_id_field = ''){
        global $db;
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
        // Return the generated list
        return $mmrpg_contributors_index;
    }


    // Define a function for printing the environment relationship header
    public static function print_env_rel_header($env1, $dir, $env2){
        if ($dir === '<' || $dir === 'left'){ $icon = 'angle-double-left'; }
        elseif ($dir === '>' || $dir === 'right'){ $icon = 'angle-double-right'; }
        else { $icon = 'question-circle'; }
        $markup = '';
            $markup .= '<span class="env-rel">'.PHP_EOL;
                if (true || MMRPG_CONFIG_SERVER_ENV !== 'local'){
                    $markup .= '<span class="env '.$env1.'">'.strtoupper($env1).'</span>'.PHP_EOL;
                    $markup .= '<i class="arrow fa fa-'.$icon.'"></i>'.PHP_EOL;
                    $markup .= '<span class="env '.$env2.'">'.strtoupper($env2).'</span>'.PHP_EOL;
                }
            $markup .= '</span>'.PHP_EOL;
        return $markup;
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
        foreach ($this_group_options AS $option_key => $option_info){
            $repo_status = '';
            $repo_status_icon = '';
            if (!empty($option_info['repo'])){
                $repo_config = $option_info['repo'];
                $repo_status = self::get_admin_home_group_option_status($repo_config, $git_changes);
                if (!empty($repo_status)){
                    $unique_changes = array();
                    foreach ($git_changes AS $k => $p){ list($t) = explode('/', $p); if (!in_array($t, $unique_changes)){ $unique_changes[] = $t; }  }
                    $unique_changes_count = count($unique_changes);
                    $class = 'status has_'.$repo_status;
                    $title = $unique_changes_count.' '.ucwords(str_replace('_', ' ', $repo_status));
                    $count = '<span class="count">('.$unique_changes_count.')</span>';
                    if ($repo_status === 'uncommitted_changes'){ $icon = '<i class="icon fa fa-asterisk"></i>'; }
                    elseif ($repo_status === 'committed_changes'){ $icon = '<i class="icon fa fa-check"></i>'; }
                    else { $icon = '<i class="icon fa fa-question"></i>'; }
                    if (!empty($option_info['link']['url'])){
                        $href = $option_info['link']['url'].'?subaction=search&'.$repo_config['data']['prefix'].'_flag_changed=1';
                        $repo_status_icon .= '<a class="'.$class.'" href="'.$href.'" title="'.$title.'">'.$icon.' '.$count.'</a>';
                    } else {
                        $repo_status_icon .= '<span class="'.$class.'" title="'.$title.'">'.$icon.' '.$count.'</span>';
                    }
                }
            }
            echo('<li class="item">'.PHP_EOL);
                $link_url = isset($option_info['link']['url']) ? ' href="'.$option_info['link']['url'].'"' : '';
                $link_target = isset($option_info['link']['target']) ? ' target="'.$option_info['link']['target'].'"' : '';
                $link_text = $option_info['link']['text'];
                echo('<div class="link"><a'.$link_url.$link_target.'>'.$link_text.'</a>'.$repo_status_icon.'</div>'.PHP_EOL);
                $desc_text = $option_info['desc'];
                echo('<div class="desc"><em>'.$desc_text.'</em></div>'.PHP_EOL);
                if (!empty($option_info['buttons'])){
                    $buttons_markup = array();
                    foreach ($option_info['buttons'] AS $button_key => $button_info){
                        if (isset($button_info['condition']['status']) && $button_info['condition']['status'] !== $repo_status){ continue; }
                        $button_text = $button_info['text'];
                        $button_attributes = '';
                        if (isset($button_info['action'])){ $button_attributes .= ' data-action="'.$button_info['action'].'"'; }
                        if (isset($button_info['attributes'])){ foreach ($button_info['attributes'] AS $a => $v){ $button_attributes .= ' '.$a.'="'.$v.'"'; } }
                        $buttons_markup[] = '<a class="button"'.$button_attributes.'">'.$button_text.'</a>';
                    }
                    if (!empty($buttons_markup)){
                        echo('<div class="buttons">'.PHP_EOL);
                            echo(trim(implode(PHP_EOL, $buttons_markup)).PHP_EOL);
                        echo('</div>'.PHP_EOL);
                    }
                }
            echo('</li>'.PHP_EOL);
        }
        $this_group_items_markup = trim(ob_get_clean());

        // If item markup was generated, wrap it in a list with the title on top
        ob_start();
        if (!empty($this_group_items_markup)){
            echo('<ul class="adminhome">'.PHP_EOL);
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

    // Define a function for checking if a given home page option has uncommit (unstage or untracked) changes
    public static function get_admin_home_group_option_status($repo_config, &$git_changes){
        if (empty($repo_config['path'])){ return false; }
        $git_changes = cms_admin::git_get_changes($repo_config['path']);
        if (!empty($repo_config['filter'])){ $git_changes = self::git_filter_list_by_data($git_changes, $repo_config['filter']); }
        if (!empty($git_changes)){ return 'uncommitted_changes'; }
        else { return ''; }
    }

    // Define a function for printing git change buttons and list status in an object editor form
    public static function print_object_editor_git_footer_buttons($repo_kind, $path_token, $mmrpg_git_changes, $mmrpg_git_changes_tokens = array()){

        // Compensate if individual tokens were not provided
        if (empty($mmrpg_git_changes_tokens)){
            $mmrpg_git_changes_tokens = array();
            foreach ($mmrpg_git_changes AS $key => $path){ list($token) = explode('/', $path); $mmrpg_git_changes_tokens[] = $token; }
            $mmrpg_git_changes_tokens = array_unique($mmrpg_git_changes_tokens);
        }

        // Break apart the repo kind/subkind if applicable
        if (strstr($repo_kind, '/')){ list($repo_kind, $repo_subkind) = explode('/', $repo_kind); }
        else { $repo_subkind = ''; }

        // Generate a list of print-friendly items showing the changes
        $print_changes = $mmrpg_git_changes;
        $print_changes = array_filter($print_changes, function($path) use($path_token){ list($token) = explode('/', $path); return $token === $path_token ? true : false; });
        $print_changes = array_map(function($path) use($path_token){ return str_replace($path_token.'/', '', $path); }, $print_changes);

        // Generate button markup if applicable to this object
        ob_start();
        if (in_array($path_token, $mmrpg_git_changes_tokens)){
            ?>
            <div class="buttons git-buttons" data-kind="<?= $repo_kind ?>" data-subkind="<?= $repo_subkind ?>" data-token="<?= $path_token ?>" data-source="github">
                <a class="button revert" data-action="revert" type="button">Revert Changes</a>
                <a class="button publish" data-action="publish" type="button">Commit &amp; Publish Changes</a>
                <div class="field git-changes">
                    <strong>Uncommitted Changes</strong>
                    <ul><li><?= implode('</li><li>', $print_changes) ?></li></ul>
                </div>
            </div>
            <?
        }
        $temp_markup = ob_get_clean();

        // Return the generate markup
        return $temp_markup;
    }


    /* -- Git Functions -- */

    // Define a function for checking if there are any unstaged and/or untracked files in a given repo (w/ optional path filter)
    public static function git_get_changes($repo_base_path, $filter_path = '', $filter_data = array()){
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
        if (!empty($filter_data)){ $changes = self::git_filter_data($changes, $filter_data); }
        return array_values($changes);
    }

    // Define a function for checking if there are any unstaged files in a given repo (w/ optional path filter)
    public static function git_get_unstaged($repo_base_path, $filter_path = ''){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $unstaged = shell_exec('cd '.$repo_base_path.' && git diff --name-only');
            //echo('$unstaged = '.print_r($unstaged, true).PHP_EOL);
            $unstaged = !empty($unstaged) ? explode("\n", trim($unstaged)) : array();
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
            $untracked = shell_exec('cd '.$repo_base_path.' && git ls-files --others --exclude-standard');
            //echo('$untracked = '.print_r($untracked, true).PHP_EOL);
            $untracked = !empty($untracked) ? explode("\n", trim($untracked)) : array();
            $index[$repo_base_path] = $untracked;
        } else {
            $untracked = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $untracked = self::git_filter_list_by_path($untracked, $filter_path); }
        return array_values($untracked);
    }

    // Define a function for checking if there are any commited (but unpushed) files in a given repo (w/ optional path filter)
    public static function git_get_committed($repo_base_path, $filter_path = ''){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!isset($index[$repo_base_path])){
            $committed = shell_exec('cd '.$repo_base_path.' && git log origin/master..HEAD --name-only --oneline');
            //echo('$committed = '.print_r($committed, true).PHP_EOL);
            $committed = !empty($committed) ? explode("\n", trim($committed)) : array();
            foreach ($committed AS $key => $path){ if (strstr($path, ' ')){ unset($committed[$key]); } }
            $index[$repo_base_path] = $committed;
        } else {
            $committed = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $committed = self::git_filter_list_by_path($committed, $filter_path); }
        return array_values($committed);
    }

    // Define a quick function for filtering a given list of git changes by base path
    public static function git_filter_list_by_path($list, $filter_path){
        if (empty($list)){ return array(); }
        elseif (empty($filter_path)){ return $list; }
        foreach ($list AS $key => $path){ if (substr($path, 0, strlen($filter_path)) !== $filter_path){ unset($list[$key]); } }
        return array_values($list);
    }

    // Define a quick function for filtering a given list of git changes by its data
    public static function git_filter_list_by_data($list, $filter_data){
        if (empty($list)){ return array(); }
        elseif (empty($filter_data)){ return $list; }
        elseif (empty($filter_data['table']) || empty($filter_data['token'])){ return $list; }
        global $db;
        $filter_by_table = $filter_data['table'];
        $filter_by_token = $filter_data['token'];
        $filter_by_extra = !empty($filter_data['extra']) ? $filter_data['extra'] : array();
        //echo('<pre>git_filter_list_by_data()</pre>'.PHP_EOL);
        //echo('<pre>$list = '.print_r($list, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_by_table = '.print_r($filter_by_table, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_by_token = '.print_r($filter_by_token, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_by_extra = '.print_r($filter_by_extra, true).'</pre>'.PHP_EOL);
        $filter_tokens = array();
        foreach ($list AS $key => $path){ list($token) = explode('/', $path); $filter_tokens[] = $token; }
        $filter_tokens_string = "'".implode("', '", array_unique($filter_tokens))."'";
        //echo('<pre>$filter_tokens = '.print_r($filter_tokens, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_tokens_string = '.print_r($filter_tokens_string, true).'</pre>'.PHP_EOL);
        $filter_extras = array();
        if (!empty($filter_by_extra)){ foreach ($filter_by_extra AS $f => $v){ $filter_extras[] = "{$f} = '".(is_numeric($v) ? $v : str_replace("'", "\\'", $v))."'"; } }
        $filter_extras_string = !empty($filter_extras) ? ' AND '.implode(' AND ', $filter_extras) : '';
        //echo('<pre>$filter_extras = '.print_r($filter_extras, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_extras_string = '.print_r($filter_extras_string, true).'</pre>'.PHP_EOL);
        $filter_query = "SELECT {$filter_by_token} FROM {$filter_by_table} WHERE {$filter_by_token} IN ($filter_tokens_string){$filter_extras_string};";
        $filter_query_results = $db->get_array_list($filter_query, $filter_by_token);
        //echo('<pre>$filter_query = '.print_r($filter_query, true).'</pre>'.PHP_EOL);
        //echo('<pre>$filter_query_results = '.print_r($filter_query_results, true).'</pre>'.PHP_EOL);
        $allowed_tokens = is_array($filter_query_results) ? array_keys($filter_query_results) : array();
        //echo('<pre>$allowed_tokens = '.print_r($allowed_tokens, true).'</pre>'.PHP_EOL);
        foreach ($list AS $key => $path){ list($token) = explode('/', $path); if (!in_array($token, $allowed_tokens)){ unset($list[$key]); } }
        $list = array_values($list);
        //echo('<pre>$list = '.print_r($list, true).'</pre>'.PHP_EOL);
        //exit();
        return array_values($list);
    }


}
?>