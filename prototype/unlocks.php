<?

// Require the unlocks for Dr. Light if applicable
if (mmrpg_prototype_player_unlocked('dr-light')){ require('unlocks_dr-light.php'); }

// Require the unlocks for Dr. Wily if applicable
if (mmrpg_prototype_player_unlocked('dr-wily')){ require('unlocks_dr-wily.php'); }

// Require the unlocks for Dr. Cossack if applicable
if (mmrpg_prototype_player_unlocked('dr-cossack')){ require('unlocks_dr-cossack.php'); }

// Always require the common unlocks
require('unlocks_common.php');

// Always require the multiplayer unlocks
require('unlocks_multiplayer.php');

?>