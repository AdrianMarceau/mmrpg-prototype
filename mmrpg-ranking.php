<?php
// Require the application top to access files
require_once('top.php');
// Update the document to to XML and print the header
header("Content-type: text/xml; charset=utf-8");
echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
?>
<browsergameshub version="0.1">
  <players><?php
    // Require the leaderboard data for display
    require_once('data/leaderboard.php');
    foreach ($this_leaderboard_xml AS $key => $xml){
      if ($key >= 30){ break; }
      echo $xml."\n";
    }
    ?></players>
</browsergameshub>