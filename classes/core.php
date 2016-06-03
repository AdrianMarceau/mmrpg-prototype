<?php
// Define the plutocms_core class
class plutocms_core {
	
  // Define the public variables
  public $DOMAIN;
  public $ROOTDIR;
  public $ROOTURL;
  
  // Define the class constructor
  public function plutocms_core(){
    // Collect filesystem constants from the global scope
    $this->DOMAIN = MMRPG_CONFIG_DOMAIN;
    $this->ROOTDIR = MMRPG_CONFIG_ROOTDIR;
    $this->ROOTURL = MMRPG_CONFIG_ROOTURL;
  }
  
  // Define the shortcut function for encoding html entities
  public function htmlentity_encode($string, $encoding = ENT_QUOTES, $charset = 'UTF-8'){
    // Encode using UTF-8 and the ENT_QUOTES setting
    return htmlentities($string, $encoding, $charset);
  }
  // Define the shortcut function for decoding html entities
  public function htmlentity_decode($string, $encoding = ENT_QUOTES, $charset = 'UTF-8'){
    // Decode using UTF-8 and the ENT_QUOTES setting
    return html_entity_decode($string, $encoding, $charset);
  }
  // Define the shortcut function to padding numbers to 3 digits
  public function number_pad($number, $pad_length = 3, $pad_char = '0', $pad_direction = STR_PAD_LEFT){
    // Padd the number a default of three zeros to the left
    return str_pad($number, $pad_length, $pad_char, $pad_direction);
  }
  
  // Define a function for adding the ordinal suffix to any integer
  public function number_suffix($value, $concatenate = true, $superscript = false){
    if (!is_numeric($value) || !is_int($value)){ return false; }
    if (substr($value, -2, 2) == 11 || substr($value, -2, 2) == 12 || substr($value, -2, 2) == 13){ $suffix = "th"; }
    else if (substr($value, -1, 1) == 1){ $suffix = "st"; }
    else if (substr($value, -1, 1) == 2){ $suffix = "nd"; }
    else if (substr($value, -1, 1) == 3){ $suffix = "rd"; }
    else { $suffix = "th"; }
    if ($superscript){ $suffix = "<sup>".$suffix."</sup>"; }
    if ($concatenate){ return $value.$suffix; }
    else { return $suffix; }
  }
  
  // Define a function for easily switching the content-type to that of an HTML document
  public function header_html($cache_time = 0, $attachment = false){
    // Update the page headers for text/html
    header("Content-type: text/html; charset=UTF-8");
    header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Cache-control: public, max-age={$cache_time}, must-revalidate");
    header("Pragma: cache");
    // If the $attachment field is not false, set this as an attachment
    if (!empty($attachment) && is_string($attachment)){
      header("Content-Disposition: attachment; filename={$attachment}");
    }
    // Return true
    return true;
  }
  
  // Define a function for easily switching the content-type to that of a JS document
  public function header_js($cache_time = 0, $attachment = false){
    // Update the page headers for text/js
    header("Content-type: text/javascript; charset=UTF-8");
    header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Cache-control: public, max-age={$cache_time}, must-revalidate");
    header("Pragma: cache");
    // If the $attachment field is not false, set this as an attachment
    if (!empty($attachment) && is_string($attachment)){
      header("Content-Disposition: attachment; filename={$attachment}");
    }
    // Return true
    return true;
  }

  // Define a function for easily switching the content-type to that of a CSS document
  public function header_css($cache_time = 0, $attachment = false){
    // Update the page headers for text/css
    header("Content-type: text/css; charset=UTF-8");
    header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Cache-control: public, max-age={$cache_time}, must-revalidate");
    header("Pragma: cache");
    // If the $attachment field is not false, set this as an attachment
    if (!empty($attachment) && is_string($attachment)){
      header("Content-Disposition: attachment; filename={$attachment}");
    }
    // Return true
    return true;
  }
  
  // Define a function for easily switching the content-type to that of an XML document
  public function header_xml($cache_time = 0, $attachment = false){
    // Update the page headers for text/xml
    header("Content-type: text/xml; charset=UTF-8");
    header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Cache-control: public, max-age={$cache_time}, must-revalidate");
    header("Pragma: cache");
    // If the $attachment field is not false, set this as an attachment
    if (!empty($attachment) && is_string($attachment)){
      header("Content-Disposition: attachment; filename={$attachment}");
    }
    // Return true
    return true;
  }

  // Define a function for easily switching the content-type to that of a TXT document
  public function header_txt($cache_time = 0, $attachment = false){
    // Update the page headers for text/html
    header("Content-type: text/plain; charset=UTF-8");
    header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Cache-control: public, max-age={$cache_time}, must-revalidate");
    header("Pragma: cache");
    // If the $attachment field is not false, set this as an attachment
    if (!empty($attachment) && is_string($attachment)){
      header("Content-Disposition: attachment; filename={$attachment}");
    }
    // Return true
    return true;
  }
  
  // Define a function for easily switching the content-type to that of a CSV document
  public function header_csv($cache_time = 0, $attachment = false){
    // Update the page headers for text/html
    header("Content-type: text/csv; charset=UTF-8");
    header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
    header("Cache-control: public, max-age={$cache_time}, must-revalidate");
    header("Pragma: cache");
    // If the $attachment field is not false, set this as an attachment
    if (!empty($attachment) && is_string($attachment)){
      header("Content-Disposition: attachment; filename={$attachment}");
    }
    // Return true
    return true;
  }
  
  // Define a function for checking if an email address is valid format
  public function valid_email($email){
    $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
    $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
    $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
    $quoted_pair = '\\x5c[\\x00-\\x7f]';
    $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
    $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
    $domain_ref = $atom;
    $sub_domain = "($domain_ref|$domain_literal)";
    $word = "($atom|$quoted_string)";
    $domain = "$sub_domain(\\x2e$sub_domain)*";
    $local_part = "$word(\\x2e$word)*";
    $addr_spec = "$local_part\\x40$domain";
    return preg_match("!^$addr_spec$!", $email) ? true : false;
  }
  
}
?>