<?

/*
 * COMMON UNLOCKABLES
 */


/* -- UNLOCKABLE ITEMS -- */

// Unlock the OMEGA SEED after all three Drs. have completed the prototype
if (!mmrpg_prototype_item_unlocked('omega-seed')
    && mmrpg_prototype_complete() >= 3
    ){

    // Unlock the Omega Seed and generate the required event details
    mmrpg_game_unlock_item('omega-seed', array(
        'positive_word' => 'What\'s this?',
        'event_text' => 'A new item appears to have been unlocked...'
        ));

}



?>