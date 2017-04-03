<?
/*
 * INDEX PAGE : LEADERBOARD INDEX
 */

// Update the GET variables with the current page num
$this_display_limit_default = 50;
$this_num_offset = $this_current_num - 1;
$_GET['start'] = 0 + ($this_num_offset * $this_display_limit_default);
$_GET['limit'] = $this_display_limit_default + ($this_num_offset * $this_display_limit_default);

// Require the leaderboard data file
require_once(MMRPG_CONFIG_ROOTDIR.'includes/leaderboard.php');

// Define the rank title and description based on the sort parameter
$ranked_by_title = 'Battle Points';
if (!empty($leaderboard_sort_by)){ $ranked_by_title = $allowed_sort_types[$leaderboard_sort_by][1]; }
$ranked_page_title = ($leaderboard_sort_by != 'points' ? $allowed_sort_types[$leaderboard_sort_by][0].' ' : '').'Leaderboard';
$ranked_description_text1 = 'The <strong>Mega Man RPG Prototype</strong> currently has '.($this_leaderboard_count == 1 ? '1 user' : $this_leaderboard_count.' users').' and that number is growing all the time.';
$ranked_description_text2 = 'In the spirit of competition, all users have been ranked by their total '.$ranked_by_title.' and listed from highest to lowest.';
$ranked_description_text3 = 'Use the numbered links at the top and bottom of the page to navigate through users or use the dropdown below to rank them by other criteria.';

// Define the SEO variables for this page
$this_seo_title = $ranked_page_title.' | '.($this_current_num > 1 ? 'Page '.$this_current_num.' | ' : '').$this_seo_title;
$this_seo_description = $ranked_description_text1.' '.$ranked_description_text2;

// Define the Open Graph variables for this page
$this_graph_data['title'] = $ranked_page_title;
$this_graph_data['description'] = $ranked_description_text1.' '.$ranked_description_text2;

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Leaderboard';
$this_markup_counter = '<span class="count count_header">( '.(!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 User' : $this_leaderboard_count.' Users') : '0 Users').($this_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$this_leaderboard_online_count.' Online</span>' : '').' )</span>';

// Start generating the page markup
ob_start();
?>
<h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    <?= ($leaderboard_sort_by != 'points' ? $allowed_sort_types[$leaderboard_sort_by][0].' ' : '').'Leaderboard Index' ?>
</h2>
<div class="subbody" style="margin-bottom: 6px;">

        <p class="text">
            <?= $ranked_description_text1 ?>
            <?= $ranked_description_text2 ?>
            <?= $ranked_description_text3 ?>
        </p>

        <div class="text form leaderboard_options">
            <div class="field">
                <strong class="label">Ranked by</strong>
                <select class="select" name="board_rank_kind" onchange="location = this.value;">
                    <? foreach ($allowed_sort_types AS $type => $title){ ?>
                        <option value="leaderboard/<?= $type != 'points' ? $type.'/' : '' ?>" <?= $leaderboard_sort_by == $type ? 'selected="selected"' : '' ?>><?= $title[1] ?></a>
                    <? } ?>
                </select>
            </div>
        </div>

        <? if(!empty($this_leaderboard_online_players)):?>
                <p class="event text" style="min-height: 1px; text-align: right; font-size: 10px; line-height: 13px; margin-top: 30px; padding-bottom: 5px;">
                        <span><strong style="display: block; text-decoration: underline; margin-bottom: 6px;">Online Users</strong></span>
                        <? foreach ($this_leaderboard_online_players AS $key => $info){
                                if (empty($info['image'])){ $info['image'] = 'robots/mega-man/40'; }
                                list($path, $token, $size) = explode('/', $info['image']);
                                $frame = $info['placeint'] <= 3 ? 'victory' : 'base';
                                if ($key > 0 && $key % 5 == 0){ echo '<br />'; }
                                echo ' <a data-playerid="'.$info['id'].'" class="player_type player_type_'.$info['colour'].'" href="leaderboard/'.$info['token'].'/" style="text-decoration: none; line-height: 20px; padding-right: 12px; margin: 0 0 0 6px;">';
                                        echo '<span style="pointer-events: none; display: inline-block; width: 34px; height: 14px; position: relative;"><span class="sprite sprite_'.$size.'x'.$size.' sprite_'.$size.'x'.$size.'_'.$frame.'" style="margin: 0; position: absolute; left: '.($size == 40 ? -4 : -26).'px; bottom: 0; background-image: url(images/'.$path.'/'.$token.'/sprite_left_'.$size.'x'.$size.'.png?'.MMRPG_CONFIG_CACHE_DATE.');">&nbsp;</span></span>';
                                        echo '<span style="vertical-align: top; line-height: 18px;">'.strip_tags($info['place']).' : '.$info['name'].'</span>';
                                echo '</a>';
                        } ?>
                </p>
        <? endif; ?>

</div>

<div class="leaderboard">
        <div class="wrapper">
        <?

        // Print out the generated leaderboard markup
        //echo $this_leaderboard_markup;
        //die('<pre>'.print_r($this_leaderboard_markup, true).'</pre>');
        //die('$this_start_key = '.$this_start_key.'; $this_display_limit = '.$this_display_limit.'; ');
        if (!empty($this_leaderboard_markup)){

                // GENERATE MARKUP //

                // Define the start and end pages based on total numbers
                $display_range = 2;
                $display_range2 = 8;
                $first_page_num = 1;
                $last_page_num = ceil($this_leaderboard_count / $this_display_limit_default);

                // Define the variable to hold the pagelink markup
                $playerlink_markup = '';
                ob_start();

                    // Loop through and print out the leaderboard player links
                    $last_key = 0;
                    $this_start_key = $_GET['start'];
                    $this_display_limit = $_GET['limit'];
                    foreach ($this_leaderboard_markup AS $key => $leaderboard_markup){
                        // If this key is below the start limit, don't display
                        if (empty($leaderboard_markup)){ continue; }
                        // Update the last key variable
                        $last_key = $key;
                        // Display this save file's markup
                        echo $leaderboard_markup;
                    }
                    // Define the start key for the next batch of players
                    $start_key = $last_key + 1;

                // Collect the pagelink markup
                $playerlink_markup = trim(ob_get_clean());

                // Define the variable to hold the pagelink markup
                $pagelink_markup = '';
                ob_start();

                    // If we're not on the first page, create a link to go back one
                    if ($this_display_limit > $this_display_limit_default){
                        $new_display_limit = $this_display_limit - $this_display_limit_default;
                        $new_start_key = $start_key - $this_display_limit_default - $this_display_limit_default;
                        if ($new_display_limit < $this_display_limit_default){ $new_display_limit = 0; }
                        if ($new_start_key < 0){ $new_start_key = 0; }
                        $previous_page_num = $this_current_num - 1;
                        $previous_href = 'leaderboard/';
                        if ($leaderboard_sort_by != 'points'){ $previous_href .= $leaderboard_sort_by.'/'; }
                        $previous_href .= $previous_page_num.'/';
                        echo '<a class="link prev" href="'.$previous_href.'" >&laquo; Prev</a>';
                    }

                    // If not displaying all players, create a link to show more
                    if ($this_display_limit < $this_leaderboard_count){
                        $new_display_limit = $this_display_limit + $this_display_limit_default;
                        if ($new_display_limit > $this_leaderboard_count){ $new_display_limit = $this_leaderboard_count; }
                        $next_page_num = $this_current_num + 1;
                        $next_href = 'leaderboard/';
                        if ($leaderboard_sort_by != 'points'){ $next_href .= $leaderboard_sort_by.'/'; }
                        $next_href .= $next_page_num.'/';
                        echo '<a class="link next" href="'.$next_href.'" >Next &raquo;</a>';
                    }
                    // If we're already on the last page, display a link to go to the first
                    elseif ($this_display_limit >= $this_leaderboard_count){
                        $first_href = 'leaderboard/';
                        if ($leaderboard_sort_by != 'points'){ $first_href .= $leaderboard_sort_by.'/'; }
                        echo '<a class="link next" href="'.$first_href.'">First &raquo;</a>';
                    }

                    // Create links for all the page numbers one by one
                    if ($this_leaderboard_count > $this_display_limit_default){
                        // Loop through and generate the page number markup
                        for ($this_page_num = $first_page_num; $this_page_num <= $last_page_num; $this_page_num++){
                            $show_page_num = false;
                            if ($this_page_num == $this_current_num){ $show_page_num = true; }
                            elseif ($this_page_num <= $this_current_num + $display_range && $this_page_num >= $this_current_num - $display_range){ $show_page_num = true; }
                            elseif ($this_page_num <= $first_page_num + $display_range2 && $this_page_num >= $first_page_num - $display_range2){ $show_page_num = true; }
                            elseif ($this_page_num <= $last_page_num + $display_range2 && $this_page_num >= $last_page_num - $display_range2){ $show_page_num = true; }
                            $show_num_text = $show_page_num ? $this_page_num : '.';
                            $show_num_type = $show_page_num ? 'number' : 'bullet';
                            $show_online = in_array($this_page_num, $this_leaderboard_online_pages) ? true : false;
                            if ($this_current_num == $this_page_num){
                                echo '<a class="link '.$show_num_type.' active '.($show_online ? 'field_type field_type_nature' : '').'"><span>'.$this_page_num.'</span></a>';
                            } else {
                                $num_href = 'leaderboard/';
                                if ($leaderboard_sort_by != 'points'){ $num_href .= $leaderboard_sort_by.'/'; }
                                $num_href .= $this_page_num > 1 ? $this_page_num.'/' : '';
                                echo '<a class="link '.$show_num_type.' '.($show_online ? 'field_type field_type_nature' : '').'" href="'.$num_href.'" ><span>'.$this_page_num.'</span></a>';
                            }
                        }
                    }

                // Collect the pagelink markup
                $pagelink_markup = trim(ob_get_clean());


                // PRINT MARKUP //

                // Print out pagelinks for the header
                echo '<div class="pagelinks head">';
                    echo $pagelink_markup;
                echo '</div>';

                // Print out the opening tag for the container dig
                echo '<div class="container playerlinks">';
                    echo $playerlink_markup;
                echo '</div>';

                // Print out pagelinks for the footer
                echo '<div class="pagelinks foot">';
                    echo $pagelink_markup;
                echo '</div>';


            }

        ?>

        </div>
</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>