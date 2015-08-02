<?
/*
 * COMMUNITY THREAD VIEW
 */

// Return to loging if a guest
if (!empty($_SESSION[mmrpg_game_token()]['DEMO'])){
  header('Location: '.MMRPG_CONFIG_ROOTURL.'file/load/return='.$_GET['this_current_uri']);
  exit();
}
// Return to index if not enough points
if ($community_battle_points < MMRPG_SETTINGS_THREAD_MINPOINTS){
  header('Location: '.MMRPG_CONFIG_ROOTURL);
  exit();
}

// Update the SEO variables for this page
$this_seo_title = (!empty($_REQUEST['thread_id']) ? 'Edit' : 'New').' Discussion | '.$this_category_info['category_name'].' | '.$this_seo_title;

// Update the MARKUP variables for this page
//$this_markup_header = $this_thread_info['thread_name']; //.' | '.$this_markup_header;

//die('<pre>'.print_r($this_thread_info, true).'</pre>');

// Define this thread's session tracker token
$thread_session_token = (!empty($this_thread_info['thread_id']) ? $this_thread_info['thread_id'] : '0').'_';
$thread_session_token .= !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : (!empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : time());
// Check if this thread has already been viewed this session
$thread_session_viewed = in_array($thread_session_token, $_SESSION['COMMUNITY']['threads_viewed']) ? true : false;

// Define the temporary display variables
$temp_thread_name = !empty($this_thread_info['thread_name']) ? $this_thread_info['thread_name'] : '';
$temp_thread_author = !empty($this_thread_info['user_name_public']) ? $this_thread_info['user_name_public'] : $this_thread_info['user_name'];
$temp_thread_date = time();
$temp_thread_date = date('F jS, Y', $temp_thread_date).' at '.date('g:ia', $temp_thread_date);
$temp_thread_body = !empty($this_thread_info['thread_body']) ? $this_thread_info['thread_body']: '';

// Define the avatar class and path variables
$temp_avatar_frame = !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
$temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
$temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/intro-field';
list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
$temp_avatar_class = 'avatar avatar_40x40 float float_left ';
$temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
$temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_right_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
$temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;

// If a targetted message is being to sent through any category but personal, redirect it!
if (!empty($this_current_target) && $this_category_info['category_id'] != 0){
  header('Location: '.MMRPG_CONFIG_ROOTURL.'community/');
  exit();
}

// If the target is not empty, collect the user info
if (!empty($this_current_target)){
  $temp_user_name = strtolower(trim($this_current_target));
  $target_user_info = $DB->get_array("SELECT * FROM mmrpg_users WHERE user_name_clean LIKE '{$temp_user_name}' LIMIT 1");
} elseif (!empty($this_thread_info['thread_target'])){
  $temp_user_id = (int)($this_thread_info['thread_target']);
  $target_user_info = $DB->get_array("SELECT * FROM mmrpg_users WHERE user_id = {$temp_user_id} LIMIT 1");
}
// Ensure it's an array, even if it was empty (redirect if personal messsage, userinfo is required)
if (empty($target_user_info)){
  if (!empty($this_current_target)){
    header('Location: '.MMRPG_CONFIG_ROOTURL.'community/');
    exit();
  } else {
    $target_user_info = array();
  }
}

?>
<h2 class="subheader thread_name field_type_<?= !empty($temp_thread_colour) ? $temp_thread_colour : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="">
  <span class="thread_namewrapper" style="">
    <a class="link" style="" href="<?= str_replace($this_category_info['category_token'].'/'.$this_current_id.'/'.$this_current_token.'/', '', $_GET['this_current_url']) ?>">Community</a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
    <a class="link" style="" href="<?= str_replace($this_current_id.'/'.$this_current_token.'/', '', $_GET['this_current_url']) ?>"><?= $this_category_info['category_name'] ?></a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
    <a class="link" style="" href="<?= $_GET['this_current_url'].(!empty($this_current_target) ? $this_current_target.'/' : '' ) ?>" title="<?= $temp_thread_name ?>"><?= !empty($_REQUEST['thread_id']) ? 'Edit' : 'New' ?> <?= $this_category_info['category_id'] != 0 ? 'Discussion' : 'Message' ?><?= !empty($target_user_info) ? ' to '.(!empty($target_user_info['user_name_public']) ? $target_user_info['user_name_public'] : $target_user_info['user_name']) : '' ?></a>
  </span>
  <span style="float: right; opacity: 0.25;"><?= $temp_thread_date ?></span>
</h2>
<div id="discussion-form" class="posts_body">
  <div class="subbody thread_threads_form thread_subbody">
    <form class="form" action="<?= $_GET['this_current_url'].(!empty($this_current_target) ? $this_current_target.'/' : '' ).(!empty($_REQUEST['thread_id']) ? '&amp;action=edit&amp;thread_id='.$_REQUEST['thread_id'] : '') ?>" method="post">
      <? if (defined('DISCUSSION_POST_SUCCESSFUL') && DISCUSSION_POST_SUCCESSFUL === true): ?>
        <p class="text" style="color: #65C054; margin: 0;">(!) Thank you, your discussion has been <?= !empty($_REQUEST['thread_id']) ? 'edited' : 'created' ?>!<br />Would you like to <a style="color: #65C054;" href="community/<?= $this_category_info['category_token'].'/'.DISCUSSION_POST_SUCCESSFUL_URL ?>"><?= !empty($_REQUEST['thread_id']) ? 'reload the' : 'view the new' ?> thread</a>?</p>
        <? if (!empty($this_formerrors)){ foreach ($this_formerrors AS $error_text){ echo '<p class="text" style="color: #969696; font-size: 90%; margin: 0;">- '.$error_text.'</p>'; } } echo '<br />'; ?>
      <? elseif (defined('DISCUSSION_POST_SUCCESSFUL') && DISCUSSION_POST_SUCCESSFUL === false): ?>
        <p class="text" style="color: #E43131; margin: 0;">(!) <?= $formdata['category_id'] != 0 ? 'Your discussion could not be created.' : 'Your message could not be sent.' ?> Please review and correct the errors below.</p>
        <? if (!empty($this_formerrors)){ foreach ($this_formerrors AS $error_text){ echo '<p class="text" style="color: #969696; font-size: 90%; margin: 0;">- '.$error_text.'</p>'; } } echo '<br />'; ?>
      <? endif;?>
      <? if (!defined('DISCUSSION_POST_SUCCESSFUL') || (defined('DISCUSSION_POST_SUCCESSFUL') && DISCUSSION_POST_SUCCESSFUL === false)): ?>
        <?
        // Define and display the avatar variables
        $temp_avatar_guest = $this_userid == MMRPG_SETTINGS_GUEST_ID ? true : false;
        $temp_avatar_name = (!empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name']);
        $temp_avatar_title = '#'.$this_userid.' : '.$temp_avatar_name;

        // Define the avatar class and path variables
        $temp_avatar_path = !$temp_avatar_guest ? (!empty($this_userinfo['user_image_path']) ? $this_userinfo['user_image_path'] : 'robots/mega-man/40') : 'robots/robot/40';  //!empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
        list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
        $temp_avatar_size = $temp_avatar_size * 2;
        $temp_avatar_frames_count = $temp_avatar_kind == 'players' ? 6 : 10;
        $temp_avatar_frames = array();
        for ($i = 0; $i < $temp_avatar_frames_count; $i++){ $temp_avatar_frames[] = str_pad($i, 2, '0', STR_PAD_LEFT); }
        $temp_avatar_frames = implode(',', $temp_avatar_frames);
        $temp_avatar_frame = isset($_REQUEST['thread_frame']) ? $_REQUEST['thread_frame'] : '00';
        $temp_thread_id = isset($_REQUEST['thread_id']) ? $_REQUEST['thread_id'] : 0;
        $temp_thread_colour = isset($_REQUEST['thread_colour']) ? $_REQUEST['thread_colour'] : 'none';
        $temp_thread_name = isset($_POST['thread_name']) ? htmlentities($_POST['thread_name'], ENT_QUOTES, 'UTF-8', true) : '';
        $temp_thread_body = isset($_POST['thread_body']) ? htmlentities($_POST['thread_body'], ENT_QUOTES, 'UTF-8', true) : '';
        $temp_user_id = $this_userinfo['user_id'];
        $temp_thread_published = true;
        $temp_thread_locked = false;
        $temp_thread_sticky = false;
        if (!empty($temp_thread_id)){
          $temp_thread_info = $DB->get_array("SELECT * FROM mmrpg_threads WHERE thread_id = {$temp_thread_id}");
          $temp_user_id = !empty($temp_thread_info['user_id']) ? $temp_thread_info['user_id'] : $this_userinfo['user_id'];
          $temp_user_info = $DB->get_array("SELECT * FROM mmrpg_users WHERE user_id = {$temp_user_id}");
          $temp_thread_name = !empty($temp_thread_info['thread_name']) ? htmlentities($temp_thread_info['thread_name'], ENT_QUOTES, 'UTF-8', true) : '';
          $temp_thread_body = !empty($temp_thread_info['thread_body']) ? htmlentities($temp_thread_info['thread_body'], ENT_QUOTES, 'UTF-8', true) : '';
          $temp_avatar_frame = !empty($temp_thread_info['thread_frame']) ? $temp_thread_info['thread_frame'] : $temp_avatar_frame;
          $temp_thread_colour = !empty($temp_thread_info['thread_colour']) ? $temp_thread_info['thread_colour'] : $temp_thread_colour;
          $temp_thread_published = !empty($temp_thread_info['thread_published']) ? true : false;
          $temp_thread_locked = !empty($temp_thread_info['thread_locked']) ? true : false;
          $temp_thread_sticky = !empty($temp_thread_info['thread_sticky']) ? true : false;
          $temp_avatar_path = !$temp_avatar_guest ? (!empty($temp_user_info['user_image_path']) ? $temp_user_info['user_image_path'] : 'robots/mega-man/40') : 'robots/robot/40';
          list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
          $temp_avatar_size = $temp_avatar_size * 2;
          $temp_avatar_frames_count = $temp_avatar_kind == 'players' ? 6 : 10;
          $temp_avatar_frames = array();
          for ($i = 0; $i < $temp_avatar_frames_count; $i++){ $temp_avatar_frames[] = str_pad($i, 2, '0', STR_PAD_LEFT); }
          $temp_avatar_frames = implode(',', $temp_avatar_frames);
        }
        $temp_avatar_class = 'avatar avatar_80x80 float float_left avatar_selector ';
        $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
        $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_right_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;

        ?>
        <input type="hidden" class="hidden" name="formaction" value="thread" />
        <input type="hidden" class="hidden" name="category_id" value="<?= $this_category_info['category_id'] ?>" />
        <input type="hidden" class="hidden" name="category_token" value="<?= $this_category_info['category_token'] ?>" />
        <input type="hidden" class="hidden" name="thread_id" value="<?= $temp_thread_id ?>" />
        <input type="hidden" class="hidden" name="user_id" value="<?= $temp_user_id ?>" />
        <input type="hidden" class="hidden" name="user_ip" value="<?= !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0' ?>" />
        <input type="hidden" class="hidden" name="thread_frame" value="<?= $temp_avatar_frame ?>" />
        <input type="hidden" class="hidden" name="thread_time" value="<?= time() ?>" />
        <? if(!empty($target_user_info)): ?>
          <input type="hidden" class="hidden" name="thread_target" value="<?= $target_user_info['user_id'] ?>" />
        <? endif; ?>
        <div class="field field_thread_info" style="overflow: hidden; font-size: 11px;">
          <label class="label" style="float: left;"><?= !empty($target_user_info) ? 'Sending as ' : 'Posting as' ?> <strong><?= $temp_avatar_name ?></strong> :</label>
        </div>
        <div class="field field_thread_body">
          <div class="<?= $temp_avatar_class ?>" style="margin-top: 0;">
            <div class="<?= $temp_sprite_class ?>" data-frames="<?=$temp_avatar_frames?>" style="background-image: url(<?= $temp_sprite_path ?>); "><?= $temp_avatar_title ?></div>
            <a class="back">&#9668;</a>
            <a class="next">&#9658;</a>
            <div class="colour" style="">
              <? if(COMMUNITY_VIEW_MODERATOR && empty($target_user_info)): ?>
                <select class="select" name="thread_colour">
                  <option value="">- none -</option><?
                  // Collect the types from the index and append a few more
                  $temp_types_index = $mmrpg_index['types'];
                  if (COMMUNITY_VIEW_MODERATOR){
                    $temp_types_index[] = array('type_token' => 'energy');
                    $temp_types_index[] = array('type_token' => 'attack');
                    $temp_types_index[] = array('type_token' => 'defense');
                    $temp_types_index[] = array('type_token' => 'speed');
                  }
                  foreach ($temp_types_index AS $type){ echo '<option value="'.$type['type_token'].'"'.($type['type_token'] == $temp_thread_colour ? ' selected="selected"' : '').'>'.ucfirst($type['type_token']).'</option>'; }
                  ?>
                </select>
                <div class="checkbox checkbox_published">
                  <input class="input" type="checkbox" name="thread_published" value="true" <?= $temp_thread_published ? 'checked="checked"' : '' ?> />
                  <label class="label" for="thread_published">Published</label>
                </div>
                <div class="checkbox checkbox_locked">
                  <input class="input" type="checkbox" name="thread_locked" value="true" <?= $temp_thread_locked ? 'checked="checked"' : '' ?> />
                  <label class="label" for="thread_locked">Locked</label>
                </div>
                <div class="checkbox checkbox_sticky">
                  <input class="input" type="checkbox" name="thread_sticky" value="true" <?= $temp_thread_sticky ? 'checked="checked"' : '' ?> />
                  <label class="label" for="thread_sticky">Sticky</label>
                </div>
              <? else: ?>
                <input type="hidden" class="hidden" name="thread_colour" value="<?= $temp_thread_colour ?>" />
                <input type="hidden" class="hidden" name="thread_published" value="<?= $temp_thread_published ? 'true' : 'false' ?>" />
                <input type="hidden" class="hidden" name="thread_locked" value="<?= $temp_thread_locked ? 'true' : 'false' ?>" />
                <input type="hidden" class="hidden" name="thread_sticky" value="<?= $temp_thread_sticky ? 'true' : 'false' ?>" />
              <? endif; ?>
            </div>
          </div>
          <? if(!empty($temp_thread_id) && COMMUNITY_VIEW_MODERATOR && empty($target_user_info)): ?>
            <select class="select select2" name="new_category_id">
              <?
              // Collect the categories from the index
              foreach ($this_categories_index AS $cat_id => $cat_info){
                if ($cat_info['category_token'] == 'personal' || $cat_info['category_token'] == 'chat'){ continue; }
                echo '<option value="'.$cat_info['category_id'].'"'.($cat_info['category_id'] == $this_category_info['category_id'] ? ' selected="selected"' : '').'>Category : '.$cat_info['category_name'].'</option>';
              }
              ?>
            </select>
          <? else: ?>
            <input type="hidden" class="hidden" name="new_category_id" value="<?= $this_category_info['category_id'] ?>" />
          <? endif; ?>
          <input type="text" class="text" name="thread_name" value="<?= $temp_thread_name ?>" />
          <?/*<textarea class="textarea" name="thread_body" rows="15"><?= str_replace("\n", '\\n', $temp_thread_body) ?></textarea>*/?>
          <textarea class="textarea" name="thread_body" rows="15"><?= str_replace('\n', "\n", $temp_thread_body) ?></textarea>
        </div>
        <div class="field field_thread_info" style="clear: left; overflow: hidden; font-size: 11px;">
          <?= mmrpg_formatting_help() ?>
        </div>
        <?
        // Define the current maxlength based on board points
        $temp_maxlength = MMRPG_SETTINGS_DISCUSSION_MAXLENGTH;
        if (!empty($this_boardinfo['board_points']) && ceil($this_boardinfo['board_points'] / 1000) > MMRPG_SETTINGS_DISCUSSION_MAXLENGTH){ $temp_maxlength = ceil($this_boardinfo['board_points'] / 1000); }
        ?>
        <div class="buttons buttons_active" data-submit="<?= !empty($_REQUEST['thread_id']) ? 'Edit' : 'Create' ?> Discussion">
          <label class="counter"><span class="current">0</span> / <span class="maximum"><?= $temp_maxlength ?></span> Characters</label>
        </div>
      <? endif; ?>
    </form>
  </div>

</div>
<?

// Add this thread to the community session tracker array
if (!in_array($thread_session_token, $_SESSION['COMMUNITY']['threads_viewed'])){
  $_SESSION['COMMUNITY']['threads_viewed'][] = $thread_session_token;
}

?>