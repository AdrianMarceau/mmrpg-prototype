<?php

// Require the global config file
require('../top.php');

// Return markup based on provided arguments
$is_logged_in = !rpg_user::is_guest();
if ($is_logged_in){

    // Collect the session token so we can find events
    $session_token = rpg_game::session_token();

    // Pre-sort the events to make sure they are in player order
    if (isset($_SESSION[$session_token]['EVENTS'])
        && is_array($_SESSION[$session_token]['EVENTS'])){

        // Manually define event priority so it's easier to sort stuff
        $event_priority_bracket = array(
            'critical-issue',
            'prototype-complete',
            'prototype-postgame',
            'new-player',
            'new-chapter',
            'new-robot',
            'new-ability',
            'new-shop',
            'new-item',
            'other'
            );
        $event_priority_bracket_tiers = count($event_priority_bracket);

        // Collect the list of player tokens so that we can sort by them
        $mmrpg_index_players = rpg_player::get_index();
        $mmrpg_index_players_tokens = array_keys($mmrpg_index_players);
        $mmrpg_index_players_tokens_count = count($mmrpg_index_players_tokens);

        // Start sorting the events now that we have everything set up
        usort($_SESSION[$session_token]['EVENTS'], function($event_a, $event_b) use (
            $event_priority_bracket, $event_priority_bracket_tiers,
            $mmrpg_index_players_tokens, $mmrpg_index_players_tokens_count
            ){

            // First sort by player so at least that's in order
            $a_position = isset($event_a['player_token']) ? array_search($event_a['player_token'], $mmrpg_index_players_tokens) : $mmrpg_index_players_tokens_count;
            $b_position = isset($event_b['player_token']) ? array_search($event_b['player_token'], $mmrpg_index_players_tokens) : $mmrpg_index_players_tokens_count;
            if ($a_position < $b_position){ return -1; }
            elseif ($a_position > $b_position){ return 1; }

            // If same player, sort by event priority
            $a_priority = isset($event_a['event_type']) ? array_search($event_a['event_type'], $event_priority_bracket) : $event_priority_bracket_tiers;
            $b_priority = isset($event_b['event_type']) ? array_search($event_b['event_type'], $event_priority_bracket) : $event_priority_bracket_tiers;
            if ($a_priority < $b_priority){ return -1; }
            elseif ($a_priority > $b_priority){ return 1; }

            // If either event has an 'event_sort' parameter, collect it and treat it like priority
            $a_sort = isset($event_a['event_sort']) ? $event_a['event_sort'] : 99;
            $b_sort = isset($event_b['event_sort']) ? $event_b['event_sort'] : 99;
            if ($a_sort < $b_sort){ return -1; }
            elseif ($a_sort > $b_sort){ return 1; }

            // If all else fails, return zero
            return 0;

            });
    }

    // Define arrays told the two markup variables
    $window_canvas_events = array();
    $window_canvas_messages = array();

    // If there were any prototype window events created, display them
    if (!empty($_SESSION[$session_token]['EVENTS'])){
        foreach ($_SESSION[$session_token]['EVENTS'] AS $temp_key => $temp_event){
            $meta_data = array();
            if (isset($temp_event['event_type'])){ $meta_data[] = '<meta name="event_type" content="'.$temp_event['event_type'].'" />'; }
            if (isset($temp_event['player_token'])){ $meta_data[] = '<meta name="player_token" content="'.$temp_event['player_token'].'" />'; }
            $meta_data = !empty($meta_data) ? implode('', $meta_data) : '';
            $window_canvas_events[] = $temp_event['canvas_markup'].$meta_data;
            $window_canvas_messages[] = $temp_event['console_markup']; //.$meta_data;
        }
    }

    // If there were any events in the session, automatically add remove them from the session
    //error_log("Make sure we clear the event session when we're done!");
    if (!empty($_SESSION[$session_token]['EVENTS'])){ $_SESSION[$session_token]['EVENTS'] = array(); }

    // Return the markup for the community formatting guide
    header('Content-type: text/json; charset=UTF-8');
    echo(json_encode(array(
        'status' => 'success',
        'updated' => time(),
        'data' => array(
            'events' => $window_canvas_events,
            'messages' => $window_canvas_messages,
            ),
        )));
    exit();

} else {

    // Return a 404 header as this is an undefined request
    http_response_code(404);
    exit();

}

?>
