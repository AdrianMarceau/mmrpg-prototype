<?
/*
 * INDEX PAGE : CONTACT
 */

// Define the SEO variables for this page
$this_seo_title = 'Contact | '.$this_seo_title;
$this_seo_description = 'If you would like to get in contact with the webmaster and/or developers of the Mega Man RPG Prototype - you have one of three options. You can email the webmaster directly, post in the Community forums, or you can use the contact form on this page. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Contact and Feedback';
$this_graph_data['description'] = 'If you would like to get in contact with the webmaster and/or developers of the Mega Man RPG Prototype - you have one of three options. You can email the webmaster directly, post in the Community forums, or you can use the contact form on this page.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Contact &amp; Feedback';

// Define a function for sending email messages
function mmrpg_send_email($to_email, $from_email, $message_subject, $message_body, $reply_to_email = false, $email_type = 'text/html'){

  // preset the mail settings
  $from = '"Mega Man RPG Prototype" <info@megamanpoweredup.net>';
  ini_set( 'SMTP', 'smtp.gmail.com' );
  ini_set( 'SMTP_PORT', 465 );
  ini_set( 'sendmail_from', $from );

  // Parse the email type
  $email_type = strtolower($email_type);
  if (!in_array($email_type, array('text/html', 'text/plain'))){
    $email_type = 'text/html';
  }
  // Parse the TO email
  if (preg_match('/([^<>]*)<(.*)>/i', $to_email, $to_matches)){
    $to_name = $to_matches[1];
    $to_email = $to_matches[2];
  }elseif(preg_match('/([-_a-z0-9\.]*)@([^<>]*)/i', $to_email, $to_matches)){
    $to_name = $to_matches[1];
    $to_email = $to_matches[0];
  }else{
    die("[[mmrpg_core::email]] : A valid recipient email was not provided.");
    return false;
  }
  // Parse the FROM email
  if (preg_match('/([^<>]*)<(.*)>/i', $from_email, $from_matches)){
    $from_name = $from_matches[1];
    $from_email = $from_matches[2];
  }elseif(preg_match('/([-_a-z0-9\.]*)@([^<>]*)/i', $from_email, $from_matches)){
    $from_name = $from_matches[1];
    $from_email = $from_matches[0];
  }else{
    die("[[mmrpg_core::email]] : A valid sender email was not provided.");
    return false;
  }
  // Parse the REPLY-TO email
  if (preg_match('/([^<>]*)<(.*)>/i', $reply_to_email, $reply_to_matches)){
    $reply_to_name = $reply_to_matches[1];
    $reply_to_email = $reply_to_matches[2];
  }elseif(preg_match('/([-_a-z0-9\.]*)@([^<>]*)/i', $reply_to_email, $reply_to_matches)){
    $reply_to_name = $reply_to_matches[1];
    $reply_to_email = $reply_to_matches[0];
  }else{
    $reply_to_name = false;
    $reply_to_email = false;
  }
  // Define/set the email headers
  //ini_set('sendmail_from', $from_email);
  $message_header = '';
  $message_header .= "Return-Path: {$from_name} <{$from_email}>\r\n";
  $message_header .= "From: {$from}\r\n";
  if ($reply_to_name && $reply_to_email) { $message_header .= "Reply-To: {$reply_to_name} <{$reply_to_email}>\r\n"; }
  else { $message_header .= "Reply-To: {$from_name} <{$from_email}>\r\n"; }
  $message_header .= "Content-Type: {$email_type};\r\n";
  // Attempt to send the email message
  if (mail($to_email, $message_subject, ($email_type == 'text/html' ? "<html>\r\n<body>\r\n" : '').$message_body.($email_type == 'text/html' ? "</body>\r\n</html>\r\n" : ''), $message_header)){ return true; }
  else { die("[[mmrpg_core::email]] : An unknown error occured.  Mail was not sent."); return false; }

}

// If a formaction has been submit, process it
$this_formaction = !empty($_POST['formaction']) ? $_POST['formaction'] : false;
$this_formerrors = array();
while ($this_formaction == 'contact'){

  // Define the verified flag
  $verified = true;

  // Collect all submitted form data
  $formdata = array();
  $formdata['contact_name'] = isset($_POST['contact_name']) ? $_POST['contact_name'] : false;
  $formdata['contact_email'] = isset($_POST['contact_email']) ? $_POST['contact_email'] : false;
  $formdata['contact_message'] = isset($_POST['contact_message']) ? $_POST['contact_message'] : false;
  $formdata['contact_combee'] = isset($_POST['contact_combee']) ? $_POST['contact_combee'] : false;

  // Check to ensure mandatory fields are not left blank
  if (empty($formdata['contact_name'])){
    $this_formerrors[] = "Your name was not provided.";
    $verified = false;
  }
  if (empty($formdata['contact_email'])){
    $this_formerrors[] = "Your email was not provided.";
    $verified = false;
  } elseif (!preg_match('#[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}#i', $formdata['contact_email'])){
    $this_formerrors[] = "Your email was not valid.";
    $verified = false;
  }
  if (empty($formdata['contact_message'])){
    $this_formerrors[] = "Your message was not provided.";
    $verified = false;
  }
  if (!empty($formdata['contact_combee'])){
    $this_formerrors[] = "Ironically, only humans are allowed to complete this form.";
    $verified = false;
  }

  // If there are any errors, break
  if (!$verified){
    // Create the error flag to change markup
    define('EMAIL_SENT_SUCCESSFULLY', false);
    break;
  }

  // Create the emailinfo array
  $emailinfo = array();
  // Populate the relevant header fields
  $emailinfo['email_sender'] = "{$formdata['contact_name']} <{$formdata['contact_email']}>";
  $emailinfo['email_receiver'] = '"Adrian Marceau" <adrian.marceau@gmail.com>'; //Mega Man RPG Prototype <info@megamanpoweredup.net>";
  $emailinfo['email_subject'] = "Contact Message From Mega Man RPG Prototype";
  ob_start();
  ?>
  <p style="margin: 0 auto 10px;"><strong>Mega Man RPG Prototype,</strong></p>
  <p style="margin: 0 auto 10px;">A new message has been sent to you using the contact form on <a href="<?= MMRPG_CONFIG_ROOTURL ?>contact/">megamanpoweredup.net</a>.  Please find the details of the message below:</p>
  <p style="margin: 0 auto;">
  <span style="display: block; margin: 0 auto; text-align: left;"><strong>Name</strong> : <?= htmlentities($formdata['contact_name'], ENT_QUOTES, 'UTF-8', true) ?></span>
  <span style="display: block; margin: 0 auto; text-align: left;"><strong>Email</strong> : <?= htmlentities($formdata['contact_email'], ENT_QUOTES, 'UTF-8', true)?></span>
  </p>
  <?if(!empty($formdata['contact_message'])):?>
  <strong style="display: block; margin: 0 auto 4px; text-align: left;">Message : </strong>
  <p style="margin: 0 auto 5px;"><?=nl2br(htmlentities($formdata['contact_message'], ENT_QUOTES, 'UTF-8', true))?></p>
  <?endif;?>
  <?
  $emailinfo['email_body'] = ob_get_clean();

  // And now to send the actual email
  if (MMRPG_CONFIG_IS_LIVE){
    // Attempt to send the email using the default mail function
    mmrpg_send_email($emailinfo['email_receiver'], $emailinfo['email_sender'], $emailinfo['email_subject'], $emailinfo['email_body']);
    /*
    $from = '"Mega Man RPG Prototype" <info@megamanpoweredup.net>';
    ini_set( 'SMTP', 'smtp.gmail.com' );
    ini_set( 'SMTP_PORT', 465 );
    ini_set( 'sendmail_from', $from );
    $headers = 'From: ' . $from . PHP_EOL
       . 'Reply-To: ' . $from . PHP_EOL
       . 'X-Mailer: PHP/' . phpversion() . PHP_EOL
       . 'Content-type: text/html';
    $success = mail('"Adrian Marceau" <adrian.marceau@gmail.com>', $emailinfo['email_subject'], $emailinfo['email_body'], $headers );
    */
  }
  else {
    // Simply print out the email for local server
    $this_formerrors[] = $emailinfo['email_body'];
  }

  // Create the success flag to change markup
  define('EMAIL_SENT_SUCCESSFULLY', true);

  // Break out of the email loop
  break;
}



// Start generating the page markup
ob_start();
?>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Contact Methods</h2>
<div class="subbody">
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_05" style="background-image: url(images/robots/met/sprite_left_80x80.png);">Met</div></div>
  <p class="text">If you would like to get in contact with the webmaster and/or developers of the <strong>Mega Man RPG Prototype</strong> - you have one of <del>three</del> two options. You can email the webmaster directly at <a href="mailto:info@megamanpoweredup.net?subject=Mega%20Man%20RPG%20Prototype">info@megamanpoweredup.net</a> or you can post on the <a href="community/">community forums</a> (requires an account).  Messages sent to through email are replied to usually within a few days, but if you have a question about the game that needs a more immediate response you might have luck posting on the community instead.</p>
</div>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Feedback Form</h2>
<div class="subbody">
  <form class="form" action="contact/" method="post" style="">
    <?/*
    <p class="text" style="color: #E43131;">(!) The contact form is currently undergoing maintenance.  Please email me at the address above.</p>
    */?>
    <? if (defined('EMAIL_SENT_SUCCESSFULLY') && EMAIL_SENT_SUCCESSFULLY === true): ?>
      <p class="text" style="color: #65C054;">(!) Thank you, your message has been sent!</p>
      <? if (!empty($this_formerrors)){ foreach ($this_formerrors AS $error_text){ echo '<p class="text" style="color: #969696; font-size: 90%;">- '.$error_text.'</p>'; } } echo '<br />'; ?>
    <? elseif (defined('EMAIL_SENT_SUCCESSFULLY') && EMAIL_SENT_SUCCESSFULLY === false): ?>
      <p class="text" style="color: #E43131;">(!) Your message could not be sent. Please review and correct the errors below.</p>
      <? if (!empty($this_formerrors)){ foreach ($this_formerrors AS $error_text){ echo '<p class="text" style="color: #969696; font-size: 90%;">- '.$error_text.'</p>'; } } echo '<br />'; ?>
    <? endif;?>
    <? if (!defined('EMAIL_SENT_SUCCESSFULLY') || (defined('EMAIL_SENT_SUCCESSFULLY') && EMAIL_SENT_SUCCESSFULLY === false)): ?>
      <input type="hidden" class="hidden" name="formaction" value="contact" />
      <div class="field field_contact_name">
        <label class="label" for="contact_name">Your Name : <span class="mandatory">*</span></label>
        <input class="text" type="text" name="contact_name" value="<?= isset($_POST['contact_name']) ? htmlentities($_POST['contact_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
      </div>
      <div class="field field_contact_email">
        <label class="label" for="contact_email">Your Email : <span class="mandatory">*</span></label>
        <input class="text" type="text" name="contact_email" value="<?= isset($_POST['contact_email']) ? htmlentities($_POST['contact_email'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
      </div>
      <div class="field field_contact_message">
        <label class="label" for="contact_message">Your Message : <span class="mandatory">*</span></label>
        <textarea class="textarea" name="contact_message" rows="10"><?= isset($_POST['contact_message']) ? htmlentities($_POST['contact_message'], ENT_QUOTES, 'UTF-8', true) : '' ?></textarea>
      </div>
      <div class="field field_contact_combee" style="display: none;">
        <label class="label" for="contact_combee">Do Not Type Here : <span class="mandatory">*</span></label>
        <textarea class="textarea" name="contact_combee" rows="10"><?= isset($_POST['contact_combee']) ? htmlentities($_POST['contact_combee'], ENT_QUOTES, 'UTF-8', true) : '' ?></textarea>
      </div>
      <div class="buttons"></div>
    <? endif; ?>
  </form>
</div>


<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>