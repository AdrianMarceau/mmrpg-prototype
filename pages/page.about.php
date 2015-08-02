<?
/*
 * INDEX PAGE : ABOUT
 */

//  If this is a STORY page request, include the appropriate file
if ($this_current_sub == 'story'){
  // Require the about story file
  require('page.about_story.php');
}
//  If this is a MECHANICS page request, include the appropriate file
elseif ($this_current_sub == 'mechanics'){
  // Require the about mechanics file
  require('page.about_mechanics.php');
}
//  If this is a RESOURCES page request, include the appropriate file
elseif ($this_current_sub == 'resources'){
  // Require the about index file
  require('page.about_resources.php');
}
//  If this is a CREDITS page request, include the appropriate file
elseif ($this_current_sub == 'credits'){
  // Require the about index file
  require('page.about_credits.php');
}
//  If this is a LINKS page request, include the appropriate file
elseif ($this_current_sub == 'links'){
  // Require the links index file
  require('page.about_links.php');
}
//  Otherwise, include the INDEX file if the request is empty or invalid
else {
  // Require the about index file
  require('page.about_index.php');
}

?>