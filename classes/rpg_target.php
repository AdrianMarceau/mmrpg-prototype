<?
/**
 * Mega Man RPG Target
 * <p>The battle target class for the Mega Man RPG Prototype.</p>
 */
class rpg_target {

    // Define a trigger for using one of this robot's abilities in battle
    public static function trigger_ability_target($this_robot, $target_robot, $this_ability, $trigger_options = array()){
        global $db;

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_ability'] = $this_ability;
        $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
        $event_options['this_ability_target_key'] = $target_robot->robot_key;
        $event_options['this_ability_target_position'] = $target_robot->robot_position;
        $event_options['this_ability_results'] = array();
        $event_options['console_show_target'] = false;

        // Empty any text from the previous ability result
        $this_ability->ability_results['this_text'] = '';

        // Update this robot's history with the triggered ability
        $this_robot->history['triggered_targets'][] = $target_robot->robot_token;

        // Backup this and the target robot's frames to revert later
        $this_robot_backup_frame = $this_robot->robot_frame;
        $this_player_backup_frame = $this_robot->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_ability_backup_frame = $this_ability->ability_frame;

        // Update this robot's frames using the target options
        $this_robot->robot_frame = $this_ability->target_options['target_frame'];
        if ($this_robot->robot_id != $target_robot->robot_id){ $target_robot->robot_frame = 'defend'; }
        $this_robot->player->player_frame = 'command';
        $this_robot->player->update_session();
        $this_ability->ability_frame = $this_ability->target_options['ability_success_frame'];
        $this_ability->ability_frame_span = $this_ability->target_options['ability_success_frame_span'];
        $this_ability->ability_frame_offset = $this_ability->target_options['ability_success_frame_offset'];

        // If the target player is on the bench, alter the ability scale
        $temp_ability_styles_backup = $this_ability->ability_frame_styles;
        if ($target_robot->robot_position == 'bench' && $event_options['this_ability_target'] != $this_robot->robot_id.'_'.$this_robot->robot_token){
            $temp_scale = 1 - ($target_robot->robot_key * 0.06);
            $temp_translate = 20 + ($target_robot->robot_key * 20);
            $temp_translate2 = ceil($temp_translate / 10) * -1;
            $temp_translate = $temp_translate * ($target_robot->player->player_side == 'left' ? -1 : 1);
            //$this_ability->ability_frame_styles .= 'border: 1px solid red !important; ';
            $this_ability->ability_frame_styles .= 'transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -webkit-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -moz-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); ';
        }

        // Create a message to show the initial targeting action
        if ($this_robot->robot_id != $target_robot->robot_id && empty($trigger_options['prevent_default_text'])){
            $this_ability->ability_results['this_text'] .= "{$this_robot->print_robot_name()} targets {$target_robot->print_robot_name()}!<br />";
        } else {
            //$this_ability->ability_results['this_text'] .= ''; //"{$this_robot->print_robot_name()} targets itself&hellip;<br />";
        }

        // Append the targetting text to the event body
        $this_ability->ability_results['this_text'] .= $this_ability->target_options['target_text'];

        // Update the ability results with the the trigger kind
        $this_ability->ability_results['trigger_kind'] = 'target';
        $this_ability->ability_results['this_result'] = 'success';

        // Update the event options with the ability results
        $event_options['this_ability_results'] = $this_ability->ability_results;
        if (isset($trigger_options['canvas_show_this_ability'])){ $event_options['canvas_show_this_ability'] = $trigger_options['canvas_show_this_ability'];  }

        /*
        // If this is a non-transformed copy robot, change its colour
        $temp_image_changed = false;
        $temp_ability_type = !empty($this_ability->ability_type) && $this_ability->ability_type != 'copy' ? $this_ability->ability_type : '';
        if ($this_robot->robot_base_core == 'copy' && $this_robot->robot_core != $temp_ability_type){
            $this_backup_image = $this_robot->robot_image;
            $this_robot->robot_image = $this_robot->robot_base_image.'_'.$temp_ability_type;
            $this_robot->update_session();
            $temp_image_changed = true;
        }
        */

        // Create a new entry in the event log for the targeting event
        $this_robot->battle->events_create($this_robot, $target_robot, $this_ability->target_options['target_header'], $this_ability->ability_results['this_text'], $event_options);

        /*
        // If this is a non-transformed copy robot, change its colour
        if ($temp_image_changed){
            $this_robot->robot_image = $this_backup_image;
            $this_robot->update_session();
        }
        */

        // Update this ability's history with the triggered ability data and results
        $this_ability->history['ability_results'][] = $this_ability->ability_results;

        // Refresh the ability styles from any changes
        $this_ability->ability_frame_styles = ''; //$temp_ability_styles_backup;

        // restore this and the target robot's frames to their backed up state
        $this_robot->robot_frame = $this_robot_backup_frame;
        $this_robot->player->player_frame = $this_player_backup_frame;
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->player_frame = $target_player_backup_frame;
        $this_ability->ability_frame = $this_ability_backup_frame;
        $this_ability->target_options_reset();

        // Update internal variables
        $this_robot->update_session();
        $this_robot->player->update_session();
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        return $this_ability->ability_results;

    }

    // Define a trigger for using one of this robot's items in battle
    public static function trigger_item_target($this_robot, $target_robot, $this_item, $trigger_options = array()){
        global $db;

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_item'] = $this_item;
        $event_options['this_item_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
        $event_options['this_item_target_key'] = $target_robot->robot_key;
        $event_options['this_item_target_position'] = $target_robot->robot_position;
        $event_options['this_item_results'] = array();
        $event_options['console_show_target'] = false;

        // Empty any text from the previous item result
        $this_item->item_results['this_text'] = '';

        // Update this robot's history with the triggered item
        $this_robot->history['triggered_targets'][] = $target_robot->robot_token;

        // Backup this and the target robot's frames to revert later
        $this_robot_backup_frame = $this_robot->robot_frame;
        $this_player_backup_frame = $this_robot->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_item_backup_frame = $this_item->item_frame;

        // Update this robot's frames using the target options
        $this_robot->robot_frame = $this_item->target_options['target_frame'];
        if ($this_robot->robot_id != $target_robot->robot_id){ $target_robot->robot_frame = 'defend'; }
        $this_robot->player->player_frame = 'command';
        $this_robot->player->update_session();
        $this_item->item_frame = $this_item->target_options['item_success_frame'];
        $this_item->item_frame_span = $this_item->target_options['item_success_frame_span'];
        $this_item->item_frame_offset = $this_item->target_options['item_success_frame_offset'];

        // If the target player is on the bench, alter the item scale
        $temp_item_styles_backup = $this_item->item_frame_styles;
        if ($target_robot->robot_position == 'bench' && $event_options['this_item_target'] != $this_robot->robot_id.'_'.$this_robot->robot_token){
            $temp_scale = 1 - ($target_robot->robot_key * 0.06);
            $temp_translate = 20 + ($target_robot->robot_key * 20);
            $temp_translate2 = ceil($temp_translate / 10) * -1;
            $temp_translate = $temp_translate * ($target_robot->player->player_side == 'left' ? -1 : 1);
            //$this_item->item_frame_styles .= 'border: 1px solid red !important; ';
            $this_item->item_frame_styles .= 'transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -webkit-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -moz-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); ';
        }

        // Create a message to show the initial targeting action
        if ($this_robot->robot_id != $target_robot->robot_id && empty($trigger_options['prevent_default_text'])){
            $this_item->item_results['this_text'] .= "{$this_robot->print_robot_name()} targets {$target_robot->print_robot_name()}!<br />";
        } else {
            //$this_item->item_results['this_text'] .= ''; //"{$this_robot->print_robot_name()} targets itself&hellip;<br />";
        }

        // Append the targetting text to the event body
        $this_item->item_results['this_text'] .= $this_item->target_options['target_text'];

        // Update the item results with the the trigger kind
        $this_item->item_results['trigger_kind'] = 'target';
        $this_item->item_results['this_result'] = 'success';

        // Update the event options with the item results
        $event_options['this_item_results'] = $this_item->item_results;
        if (isset($trigger_options['canvas_show_this_item'])){ $event_options['canvas_show_this_item'] = $trigger_options['canvas_show_this_item'];  }

        /*
        // If this is a non-transformed copy robot, change its colour
        $temp_image_changed = false;
        $temp_item_type = !empty($this_item->item_type) && $this_item->item_type != 'copy' ? $this_item->item_type : '';
        if ($this_robot->robot_base_core == 'copy' && $this_robot->robot_core != $temp_item_type){
            $this_backup_image = $this_robot->robot_image;
            $this_robot->robot_image = $this_robot->robot_base_image.'_'.$temp_item_type;
            $this_robot->update_session();
            $temp_image_changed = true;
        }
        */

        // Create a new entry in the event log for the targeting event
        $this_robot->battle->events_create($this_robot, $target_robot, $this_item->target_options['target_header'], $this_item->item_results['this_text'], $event_options);

        /*
        // If this is a non-transformed copy robot, change its colour
        if ($temp_image_changed){
            $this_robot->robot_image = $this_backup_image;
            $this_robot->update_session();
        }
        */

        // Update this item's history with the triggered item data and results
        $this_item->history['item_results'][] = $this_item->item_results;

        // Refresh the item styles from any changes
        $this_item->item_frame_styles = ''; //$temp_item_styles_backup;

        // restore this and the target robot's frames to their backed up state
        $this_robot->robot_frame = $this_robot_backup_frame;
        $this_robot->player->player_frame = $this_player_backup_frame;
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->player_frame = $target_player_backup_frame;
        $this_item->item_frame = $this_item_backup_frame;
        $this_item->target_options_reset();

        // Update internal variables
        $this_robot->update_session();
        $this_robot->player->update_session();
        $target_robot->update_session();
        $this_item->update_session();

        // Return the item results
        return $this_item->item_results;

    }

}
?>