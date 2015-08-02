<?
/*
 * INDEX PAGE : LEADERBOARD INDEX
 */

// Define the SEO variables for this page
$this_seo_title = 'Leaderboard | '.($this_current_num > 1 ? 'Page '.$this_current_num.' | ' : '').$this_seo_title;
$this_seo_description = 'The Mega Man RPG Prototype currently has '.(!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : '0 Players').' players and that number is growing all the time. During the course of the game, players collect Battle Points on completion of a mission and those points build up over time to unlock new abilities and other new content. Not all players are created equal, however, and some clearly stand above the rest in terms of their commitment to the game and their skill at exploiting the battle system\'s mechanics. In the spirit of competition, all players have been ranked by their total Battle Point scores and listed from from highest to lowest. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Battle Points Leaderboard';
$this_graph_data['description'] = 'The Mega Man RPG Prototype currently has '.(!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : '0 Players').' players and that number is growing all the time. During the course of the game, players collect Battle Points on completion of a mission and those points build up over time to unlock new abilities and other new content. Not all players are created equal, however, and some clearly stand above the rest in terms of their commitment to the game and their skill at exploiting the battle system\'s mechanics. In the spirit of competition, all players have been ranked by their total Battle Point scores and listed from from highest to lowest.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

//die('<pre>'.print_r($_GET, true).'</pre>');

// Update the GET variables with the current page num
$this_display_limit_default = 50;
$this_num_offset = $this_current_num - 1;
$_GET['start'] = 0 + ($this_num_offset * $this_display_limit_default);
$_GET['limit'] = $this_display_limit_default + ($this_num_offset * $this_display_limit_default);

// Require the leaderboard data file
require_once('data/leaderboard.php');

//die('<pre>'.print_r($this_leaderboard_online_players, true).'</pre>');

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Leaderboard';
$this_markup_counter = '<span class="count count_header">( '.(!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : '0 Players').($this_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$this_leaderboard_online_count.' Online</span>' : '').' )</span>';

// Start generating the page markup
ob_start();
?>
<h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Leaderboard Players Index</h2>
<div class="subbody">
  <p class="text">The <strong>Mega Man RPG Prototype</strong> currently has <?= !empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 player' : $this_leaderboard_count.' players') : 0 ?> and that number is growing all the time.  Throughout the course of the game, players collect Battle Points on completion of missions and those points build up to unlock new abilities and other new content.  Not all players are created equal, however, and some clearly stand above the rest in terms of their commitment to the game and their skill at exploiting the battle system's mechanics.  In the spirit of competition, all players have been ranked by their total Battle Point scores and listed from highest to lowest.  Use the numbered links at the top and bottom of the page to navigate and <a href="contact/">contact me</a> if you have any questions or concerns.</p>
</div>
<?
// Print out any online players if they exist
if (!empty($this_leaderboard_online_players)){
  ?>
  <div class="subbody online_players">
    <div class="text event players"><?= mmrpg_website_print_online($this_leaderboard_online_players) ?></div>
    <strong class="text label"><?= (count($this_leaderboard_online_players) == 1 ? '1 Player' : count($this_leaderboard_online_players).' Players').' Online' ?></strong>
  </div>
  <?
}
?>

<div class="leaderboard">
  <div class="wrapper">
  <?

  // Print out the generated leaderboard markup
  //echo $this_leaderboard_markup;
  //die('<pre>'.print_r($this_leaderboard_markup, true).'</pre>');
  //die('$this_start_key = '.$this_start_key.'; $this_display_limit = '.$this_display_limit.'; ');
  if (!empty($this_leaderboard_markup)){
    // COLLECT DATA

    // Start the output buffer and start looping
    ob_start();
    $last_key = 0;
    $this_start_key = $_GET['start'];
    $this_display_limit = $_GET['limit'];
    foreach ($this_leaderboard_markup AS $key => $leaderboard_markup){
      // If this key is below the start limit, don't display
      if ($key < $this_start_key){ continue; }
      // Update the last key variable
      $last_key = $key;
      // Display this save file's markup
      echo $leaderboard_markup;
      // Only show listings up to the display limit
      if ($key + 1 >= $this_display_limit){ break; }
    }
    // Define the start key for the next batch of players
    $start_key = $last_key + 1;

    // Collect the page listing markup
    $pagelisting_markup = trim(ob_get_clean());

    // HEADER PAGE LINKS

    // Print out the opening tag for the container dig
    echo '<div class="container" style="overflow: hidden; padding-bottom: 0; margin-top: -5px; margin-bottom: -32px; ">';

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
        echo '<a class="more" name="more_link" style="float: left;" href="leaderboard/'.$previous_page_num.'/" >&laquo; Back</a>';
      }
      // If not displaying all players, create a link to show more
      if ($this_display_limit < $this_leaderboard_count){
        $new_display_limit = $this_display_limit + $this_display_limit_default;
        if ($new_display_limit > $this_leaderboard_count){ $new_display_limit = $this_leaderboard_count; }
        $next_page_num = $this_current_num + 1;
        echo '<a class="more" name="more_link" style="float: right;" href="leaderboard/'.$next_page_num.'/" >Next &raquo;</a>';
      }
      // If we're already on the last page, display a link to go to the first
      if ($this_display_limit >= $this_leaderboard_count){
        echo '<a class="more" name="more_link" style="float: right;" href="leaderboard/">First &raquo;</a>';
      }

      // Collect the pagelink markup
      $pagelink_markup = trim(ob_get_clean());
      echo $pagelink_markup;

    // Print out the closing container div
    echo '</div>';


    // MAIN LEADEBOARD AREA

    // Print out the opening tag for the container dig
    echo '<div class="container container_numbers" style="text-align: center; margin-bottom: -32px;">';

      // Define the start and end pages based on total numbers
      $display_range = 2;
      $display_range2 = 8;
      $first_page_num = 1;
      $last_page_num = ceil($this_leaderboard_count / $this_display_limit_default);

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
          if ($this_current_num == $this_page_num){ echo '<a class="more '.$show_num_type.' active '.($show_online ? 'field_type field_type_nature' : '').'" name="page_link">'.$show_num_text.'</a>'; }
          else { echo '<a class="more '.$show_num_type.' '.($show_online ? 'field_type field_type_nature' : '').'" name="page_link" href="leaderboard/'.($this_page_num > 1 ? $this_page_num.'/' : '').'" >'.$show_num_text.'</a>'; }
        }
      }

      // Display the pregenerated pagelisting data
      echo $pagelisting_markup;

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
          if ($this_current_num == $this_page_num){ echo '<a class="more '.$show_num_type.' active '.($show_online ? 'field_type field_type_nature' : '').'" name="page_link">'.$show_num_text.'</a>'; }
          else { echo '<a class="more '.$show_num_type.' '.($show_online ? 'field_type field_type_nature' : '').'" name="page_link" href="leaderboard/'.($this_page_num > 1 ? $this_page_num.'/' : '').'" >'.$show_num_text.'</a>'; }
        }
      }

    // Print out the closing container div
    echo '</div>';

    // FOOTER PAGE LINKS

    // Print out the opening tag for the container dig
    echo '<div class="container" style="overflow: hidden; padding-bottom: 10px;">';
    // Display the pregenerated pagelink markup
    echo $pagelink_markup;
    // Print out the closing container div
    echo '</div>';


  }

  ?>

  </div>
</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>