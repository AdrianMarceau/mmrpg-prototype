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
                    if (!empty($list)){
                        $status = $token.'_changes';
                        $count = count($list);
                        $icon_span = '<i class="icon fa fa-'.$icon.'"></i>';
                        $count_span = ' <span class="count">'.$count.'</span>';
                        $title_attr = ' title="'.($count.' '.ucfirst($token).' '.($count === 1 ? 'Change' : 'Changes')).'"';
                        $class_attr = ' class="status has_'.$status.'"';
                        if (!empty($option_info['link']['url'])){
                            $href_attr = ' href="'.$option_info['link']['url'].'?subaction=search&'.$repo_config['data']['prefix'].'_flag_'.$token.'_changes=1"';
                            $repo_icons .= '<a'.$class_attr.$title_attr.$href_attr.'>'.$icon_span.$count_span.'</a>';
                        } else {
                            $repo_icons .= '<span'.$class_attr.$title_attr.'>'.$icon_span.$count_span.'</span>';
                        }
                    }
                }
            }
            echo('<li class="item">'.PHP_EOL);
                $link_url = isset($option_info['link']['url']) ? ' href="'.$option_info['link']['url'].'"' : '';
                $link_target = isset($option_info['link']['target']) ? ' target="'.$option_info['link']['target'].'"' : '';
                $link_text = $option_info['link']['text'];
                $link_icons = '';
                $icon_tokens = array();
                if (isset($option_info['link']['icon'])){ $icon_tokens = array($option_info['link']['icon']); }
                elseif (isset($option_info['link']['icons'])){ $icon_tokens = $option_info['link']['icons']; }
                if (!empty($icon_tokens)){ foreach ($icon_tokens AS $token){ $link_icons .= '<i class="icon fa fa-'.$token.'"></i>'; } }
                echo('<div class="link"><a'.$link_url.$link_target.'>'.$link_text.'</a>'.$link_icons.$repo_icons.'</div>'.PHP_EOL);
                $desc_text = $option_info['desc'];
                echo('<div class="desc"><em>'.$desc_text.'</em></div>'.PHP_EOL);
                if (!empty($option_info['buttons'])){
                    $buttons_markup = array();
                    foreach ($option_info['buttons'] AS $button_key => $button_info){
                        if (isset($button_info['condition']['uncommitted']) && $button_info['condition']['uncommitted'] !== !empty($repo_changes['uncommitted'])){ continue; }
                        if (isset($button_info['condition']['committed']) && $button_info['condition']['committed'] !== !empty($repo_changes['committed'])){ continue; }
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


    /* -- Git Functions -- */

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
            $changes = array_merge($unstaged, $untracked);
            $index[$repo_base_path] = $changes;
        } else {
            $changes = $index[$repo_base_path];
        }
        if (!empty($filter_path)){ $changes = self::git_filter_list_by_path($changes, $filter_path); }
        if (!empty($filter_data)){ $changes = self::git_filter_data($changes, $filter_data); }
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
        if (!empty($filter_data)){ $changes = self::git_filter_data($changes, $filter_data); }
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

    // Define a function for updating the git remote status w/ cache to speed up repeat requests
    public static function git_update_remote($repo_base_path, $use_cache = true){
        static $index;
        if (!is_array($index)){ $index = array(); }
        if (!$use_cache || !isset($index[$repo_base_path])){
            $last_updated = self::git_update_remote_get_time($repo_base_path);
            $update_timeout = 60 * 60; // one hour
            if ((time() - $last_updated) >= $update_timeout){
                $index[$repo_base_path] = shell_exec('cd '.$repo_base_path.' && git remote update');
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
            $status_output = shell_exec('cd '.$repo_base_path.' && git status -uno');
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
            // Check to make sure this branch does NOT have an uncommited changes pending
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
        // Check to see if there are changes, filter if necessary, and return if uncommitted
        $uncommitted_changes = cms_admin::git_get_uncommitted_changes($repo_config['path']);
        if (!empty($uncommitted_changes) && !empty($repo_config['filter'])){ $uncommitted_changes = self::git_filter_list_by_data($uncommitted_changes, $repo_config['filter']); }
        if ($filter_unique){ $unique = array(); foreach ($uncommitted_changes AS $k => $p){ list($t) = explode('/', $p); if (!in_array($t, $unique)){ $unique[] = $t; }  } $uncommitted_changes = $unique; }
        $repo_changes['uncommitted'] = $uncommitted_changes;
        // Check to see if there are any updates, filter if necessary, and return if unpulled
        $committed_changes = cms_admin::git_get_committed_changes($repo_config['path']);
        if (!empty($committed_changes) && !empty($repo_config['filter'])){ $committed_changes = self::git_filter_list_by_data($committed_changes, $repo_config['filter']); }
        if ($filter_unique){ $unique = array(); foreach ($committed_changes AS $k => $p){ list($t) = explode('/', $p); if (!in_array($t, $unique)){ $unique[] = $t; }  } $committed_changes = $unique; }
        $repo_changes['committed'] = $committed_changes;
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
                    if ($search_data[$flag_name] && !in_array($data[$object_kind.'_token'], $git_file_arrays[$array_name])){ unset($search_results[$key]); }
                    elseif (!$search_data[$flag_name] && in_array($data[$object_kind.'_token'], $git_file_arrays[$array_name])){ unset($search_results[$key]); }
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
                    list($token) = explode('/', $path);
                    return $token === $path_token ? true : false;
                    });
                if (!empty($filtered_git_changes[$token])){
                    $has_changes = true;
                    $has_changes_kinds[$token] = true;
                    $print_git_changes[$token] = array_map(function($path) use($path_token){ return str_replace($path_token.'/', '', $path); }, $filtered_git_changes[$token]);
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
                    <a class="button revert" data-action="revert" type="button">Revert Changes</a>
                    <a class="button commit" data-action="commit" type="button">Commit Changes</a>
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
    public static function object_editor_get_git_file_arrays($repo_path, $repo_filters = array()){
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
            if (!empty($repo_filters)){ $changes_array = self::git_filter_list_by_data($changes_array, $repo_filters); }
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


}
?>