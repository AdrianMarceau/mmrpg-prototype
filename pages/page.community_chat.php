<?
/*
 * COMMUNITY CATEGORY VIEW
 */

// Update the SEO variables for this page
$this_seo_title = $this_category_info['category_name'].' | '.$this_seo_title;
$this_seo_description = strip_tags($this_category_info['category_description']);

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Community Forums | '.$this_category_info['category_name'];
$this_graph_data['description'] = strip_tags($this_category_info['category_description']);
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Update the MARKUP variables for this page
//$this_markup_header = $this_thread_info['thread_name']; //.' | '.$this_markup_header;

// Require the leaderboard data file
require_once('data/leaderboard.php');
// Collect all the active sessions for this page
$temp_viewing_category = mmrpg_website_sessions_active('community/'.$this_category_info['category_token'].'/', 3, true);
$temp_viewing_userids = array();
foreach ($temp_viewing_category AS $session){ $temp_viewing_userids[] = $session['user_id']; }

// Create a chat name for them that's valid for the room
$user_chat_name = !empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name'];
$user_chat_name = preg_replace('/([^-_a-z0-9]+)/i', '', $user_chat_name);

?>
<h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
  <a class="link" style="display: inline;" href="<?= str_replace($this_category_info['category_token'].'/', '', $_GET['this_current_url']) ?>">Community</a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
  <a class="link" style="display: inline;" href="<?= $_GET['this_current_url'] ?>"><?= $this_category_info['category_name'] ?></a>
</h2>
<div class="subbody">
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_0<?= mt_rand(0, 2) ?>" style="background-image: url(images/robots/<?= MMRPG_SETTINGS_CURRENT_FIELDMECHA ?>/sprite_left_80x80.png);">Met</div></div>
  <p class="text"><?= $this_category_info['category_description'] ?></p>
  <p class="text" style="margin-bottom: 6px;">(!) Please log in to the chat using the same username and password that you created for your prototype account</p>
  <p class="text" style="margin-bottom: 6px;">(!) Please see <a href="community/general/1107/official-chat-help-and-guidelines/">this thread</a> for a more thorough overview of the rules for the chat room<br /> 
  <p class="text" style="margin-bottom: 6px;">(!) Please <a href="chat/" target="_blank">click here</a> if you would like to view the chat larger in a new window</p>
  <?/*If you need to use the legacy chat (for serious discussion and meetings) <a href="community/chat/&legacy=true">please click here</a>.*/?>
  </p>
  <?/* if(!empty($temp_viewing_userids)):?>
  <div style="clear:both;margin:0;height:0;">&nbsp;</div>
  <p class="event text" style="min-height: 1px; text-align: right; font-size: 10px; line-height: 13px; margin-top: 10px; padding-bottom: 5px;">
    <span><strong style="display: block; text-decoration: underline; margin-bottom: 6px;">Online Players</strong></span>
    <?= mmrpg_website_print_online($this_leaderboard_online_players, $temp_viewing_userids) ?>
  </p>
  <? endif; */?>
</div  
<div class="subbody" style="background-color: #f0f3ff;">
  <iframe style="border-color: #f0f3ff; width: 99% !important; height: 600px !important;" src="http://rpg.megamanpoweredup.net/chat/" width="100%" height="600"></iframe>
</div>
<?/*
<? if(!empty($_GET['legacy'])): ?>
  <div class="subbody" style="background-color: #f0f3ff;">
    <iframe style="border-color: #f0f3ff; width: 100% !important; height: 600px !important;" src="http://webchat.freenode.net?nick=<?= $user_chat_name ?>&channels=mmrpg&uio=Mj10cnVlJjk9dHJ1ZSYxMT0yMjY59" width="100%" height="600"></iframe>
  </div>
<? else: ?>
  <div class="subbody" style="">
    <embed wmode="transparent" src="http://www.xatech.com/web_gear/chat/chat.swf" quality="high" width="100%" height="480" name="chat" FlashVars="id=210661526" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://xat.com/update_flash.php" />
    <p class="text" style="margin: 0px 10px 12px; text-align: right;"><a class="link_inline" target="_blank" href="http://xat.com/web_gear/?cb">Chatbox by Xat</a> &nbsp;<span class="pipe">|</span>&nbsp; <a class="link_inline" target="_blank" href="http://xat.com/web_gear/chat/go_large.php?id=210661526">Open Chat in New Window</a></small><br>
  </div>
<? endif; ?>
*/?>

<?
?>