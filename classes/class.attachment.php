<?php
/**
 * Mega Man RPG Attachment
 * <p>The object class for all attachments in the Mega Man RPG Prototype.</p>
 */
class rpg_attachment extends rpg_ability {

  function rpg_attachment() {

    // Update the session keys for this object
    $this->session_key = 'ATTACHMENTS';
    $this->session_token = 'ability_token';
    $this->session_id = 'ability_id';
    $this->class = 'ability';

    // Collect any provided arguments
    $args = func_get_args();

    // Define the internal battle pointer
    $this->battle = rpg_battle::get_battle();
    $this->battle_id = $this->battle->battle_id;
    $this->battle_token = $this->battle->battle_token;

    // Define the internal battle pointer
    $this->field = rpg_field::get_field;
    $this->field_id = $this->field->field_id;
    $this->field_token = $this->field->field_token;

    // Define the internal player values using the provided array
    $this->player = isset($args[0]) && is_a($args[0], 'rpg_player') ? $args[0] : new rpg_player();
    $this->player_id = $this->player->player_id;
    $this->player_token = $this->player->player_token;

    // Define the internal robot values using the provided array
    $this->robot = isset($args[1]) && is_a($args[1], 'rpg_robot') ? $args[0] : new rpg_robot();
    $this->robot_id = $this->robot->robot_id;
    $this->robot_token = $this->robot->robot_token;

    // Collect current ability data from the function if available
    $attachment_info = isset($args[2]) && is_array($args[2]) && !empty($args[2]) ? $args[2] : array('ability_id' => 0, 'ability_token' => 'ability');
    // Load the ability data based on the ID and fallback token
    $attachment_info = $this->ability_load($attachment_info['ability_id'], $attachment_info['ability_token']);

    // Now load the ability data from the session or index
    if (empty($attachment_info)){
      // Attachment data could not be loaded
      die('Attachment data could not be loaded :<br />$attachment_info = <pre>'.print_r($attachment_info, true).'</pre>');
      return false;
    }

    // Update the session variable
    $this->update_session();

    // Return true on success
    return true;

  }

  /**
   * Generate an attachment ID based on the robot owner and the attachment token
   * @param int $robot_id
   * @param int $attachment_token (optional)
   * @return int
   */
  public static function generate_id($robot_id, $attachment_token = 'attachment'){
    $attachment_hash = substr(md5(str_replace('attachment_', '', $attachment_token)), 0, 3);
    $attachment_id = $robot_id.$attachment_hash;
    return $attachment_id;
  }

}
?>