<?

/*
 * -- MY SECRET TO-DO LIST I GUESS --
 *
 *
 *
 * Add Quint's sprites to the robot database
 * Add Plug Man's sprites to the robot database
 * Add Knight Man's sprites to the robot database
 * Add Quick Man's new sprites to the robot database
 * Add Skull Man's new sprites to the robot database
 *
 * Go through and update all the robot stats
 * Go through and update all the weaknesses
 *
 * Add recent robot bios to database
 * Add recent robot quotes to database
 *
 * Make it so that copy robots are a different  colour
 *
 * Make it so ability/robot unlocks in missions are NOT added if the reward has already been unlocked by that player
 *
 * Make it so the original/source/whatever (unlockable) robot masters appear in battle with some kind of visual attachment on them
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * -- MY FUTURE WISHLIST OF FEATURES --
 *
 * Imagine how cool it would be to have individual robots in a player's file indexed for use on the leaderboards
 * Picture an entire page of Bright Men all from different levels owned by different players (so cool).
 * Maybe players can trade after that? And you could filter the leaderboard by which robots they have or look for specific robots and offer a trade?
 * In order to entire people to trade, what would they be trading for?  All robots are basically the same of the specific except for stat training... right?
 * Maybe we should allow gifting?  I'm not sure, what about people who have not beat the intro level?  Or even more than that, what about the original player?
 * Add in an alternate costume for Roll where can wears the MM8 outfit, shouldn't be too hard...
 * Update mission/chapter progression for each player - make them separate so you don't have to jump between each?
 * Find a way to make player battle messages less annoying, maybe have a log? or a leaderboard section?
 * Condense player battle messages into one event, allow user to review elsewhere
 * Make it so there's at least one recovery move that works long term, maybe 20% per turn for 3 turns (Boost for Roll), tier 3 using 6 energy, "Energy Assist / Charm / Boon / Upload"
 * Make it so there's at least one damage move that works long term, maybe 20% per turn for 3 turns (Break for Disco), tier 3 using 6 energy, "Energy Impair / Curse / Blight / Download"
 * Make it so there's at least one draining move that works instantly, maybe 30% from one to the other (Swap for Rhythm), tier 3 using 6 energy, "Energy Siphon / Drain / Shift / Transfer"
 * Allow players to define four quotes in their user profile, and these quotes will appear in their player battles
 * Add a new tab for the main menu called "items" and it will simply list ALL items in the game with quantities, greyed out if the player has not found it yet
 * Add a new tab in the main menu called "abilities" and it will simply list ALL abilities in the game with which players have unlocked it (by initial), greyed out if the player has not found it yet
 * Allow players to change their password, make it so the save file doesn't... exist... ?  Or is just a backup maybe?
 * Update Elec Man's sprite to make his feet shift a bit, he looks too static (haha)
 * Allow developer to edit and delete community posts and not just threads
 * Allow all developer and contributor roles to act as moderators in community?  allow moderators to have the same editing option as developers, but make it very clear abusers will be removed?
 * Create demo-only robot unlocking passwords (which ties into above) and make it so the unlocked robot has randomly generated abilities at level 1 (DEMO ONLY!)
 * Create at least one demo-only secret mission with all the megaman killers and/or copy robots maybe?
 * Flesh out the password system a bit more - have it loop through all passwords at once and process then REMOVE them to save future resources, this will of course depend on a specific password syntax for pattern matching
 * Add a few more passwords for normal mode - try to come up with a password for every ability (even if they're easy to get in-game they may be useful to new players) and find ways to spinkle them thoughout the website and experience
 * Make Roll one pixel shorter somehow so she isn't taller than Mega Man
 * Update Player Editor with same wrapper/button changes as the shop so it's consistent with it and the robot editor
 * Instead of "Likes" on posts, have up/down votes, and then on the leaderboard profile have it say "+1 Reputation" or "-5 Reputation" so people can gage member relevance
 * Add about four more items to the shop, each doing something related to the attack/defense/speed stats and then something special I guess? Attack Core, Defense Core, Speed Core, Energy Core? whatever
 * Change Mirror Move to something like Copy Buster instead, make it do damage of the same type as the user (makes core shift useful)
 * Add the Mirror Move ability, a counterpart to copy-shot, make it trigger the effects of the move that last dealt damage to the user
 * Play through the game again and shorten a lot of the messages considerably, but maybe add quick intros to the other screens
 * Maybe create simple caching system for above and for robot database pages - should take expires argument and not overload the directory with duplicate files
 *
 *
 *
 *
 *
 *
 *
 *
 * -- MY SECRET COMPLETED LIST OMG YAY --
 *
 *
 * Add better image detection in the comments for block/inline images
 * Allow community posts to feature robot, ability, player, item, and field sprites with ease
 * Fix the [code][/code] formatting option so it actually exclused matching
 * Fix StarForce screen width in iframe landscape mode, wasn't expanding for me
 * Fix leaderboard iframe width or margin or whatever, it's overflowing the right side
 * Allow robots to benefit from player stat boosts even if traded, update battletips text, update editor colours
 * Allow robots and mechas to have their own drop rates for items somehow
 * Make enemies randomly drop large and small bolts when destroyed
 * Properly expand the height of the starforce screen with new banner reduction
 * Touch up the robot editor and enlarge the robot buttons now that we have more room
 * Mechas now yield half the bonus stats that masters do - it's only fair, especially post-game
 * Fix the abilities dropdown in the robot editor, nothing appears
 * Expand the prototype's main menu to the full with of the header
 * Fix the timeout issue thing with the tooltips, they are sometimes not going away
 * Expand the height of the player area maybe by adding some new information? (items derp)
 * Make loading panel on prototype menu the full height of the new banner-shrunk page
 * Fix the mecha support ability, in battle abilities are not showing
 * Allow players to post mechas on the forums as well as the other sprites
 * Make is so that copy core robots change colour when they use an attack (OMG I DID THIS)
 * Update the item action panel and make sure it respects the player's customized item decisions
 * Make it so that the level of your summoned mecha is equal to the number of times it's been summoned to battle (max 100, obviously)
 * Make it so that your summoned mecha's generation/power is determined by the order of its summoning, like Captain olimar, in sequence
 * Ensure the robot editor doesn't auto-unlock abilities for players if it's not their robot, only unlock for original player automatically (so that related mechanics aren't pointless)
 * Fix the issue where robots were not summoning the correct mecha for their battle field...
 * Fix the issue with mecha support where the summoing robot master gets stuck in the taunt frame forever it seems
 * Reduce the amount of html markup sent with each data request, at the very least test if action panels have actually changes before sending
 * Rename the Crystal Cave field to Photon Collider
 * Fix new bug where the landscape-to-portrait mode switch (or vice versa) causes the console to be too tall and push the actions panel out of view
 * Remove Neutral Cores from the database and ability list maybe?  I'm not sure they make sense....
 * Make it so the Flame/Freeze/etc. Cores both increase the field multiplier and transform the user into that type IF they are originally Copy type
 * Create elemental images for all copy core robots
 * Make it so Copy Core robots temporarily change colours as they are using abilities, just so it looks cooler :D
 * Add the new alternate robot colours to the avatar option list of the community
 * Make Disco one pixel shorter so she doesn't look so out of place beside Mega Man (thanks Rhythm_BCA!)
 * Make Bass one pixel taller so he makes sense when placed next to Mega Man (thanks again Rhythm_BCA!)
 * Update Photon Collider field background so it looks like one a bit more
 * Add option for "shop" to the main menu, ensure its iframe works
 * Add placeholder shop that has buttons for "BUY" and "SELL"
 * Add "Player Items" to the player editor, under the fields
 * Divide the shop into three sections, just like the player editor, but with Auto, Reggae, and Kalinka
 * The shop should have a global (not player based) "Zenny" counter (maybe lower z?) and items should be sold by zenny
 * The only way to get more zenny in the game is to sell items you've already found, making the smaller items waaay more useful than otherwise
 * In Auto's Shop, normal items can be bought (energy/weapon pellet/capsule/tank, extra-life, more?) and screws can be traded for zenny
 * In Reggae's Shop, robot abilities can be bought (energy-boost, attack-boost, etc.) and robot cores can be traded for zenny - abilities can be discounted if already owned by any players
 * In Kalinka's Shop, battle fields can be bought (reflection-chamber, gemstone-cavern, crystal-catacombs, etc.) and field/fusion stars can be traded for zenny
 * Add the new Kalinka from Rhythm_BCA to the game
 * Add the new Disco from Rhythm_BCA to the game
 * Add Rhythm_BCA and CHAOS_FANTAZY to the contributors list and make sure sprite credits reflect this
 * Create Light Laboratory field
 * Create Cossack Citadel field
 * Add the new Bass from Rhythm_BCA to the game
 * Flesh out the robot select screen in demo mode so it matches the in-game one (even though it's normally hidden)
 * Make it so the stage select music is dependent on what fields are present in Chapter 2 - majory rule's game will be played until switched
 * Allow core-shifted mega/bass/proto as avatar options when core is found in-game
 * Fix the leaderboard number display to accomodate 4-digits without eliipsis (check live website for reference)
 * Add the mechas to the main website's database, but obviously hide/edit some of the information that shows
 * Fix the frame height and width of the starforce and maybe other panels on the view game menu (index_base view), should be same as leaderboard
 * Add fields to the website database
 * Make sure fields link to the music creator if available
 * Add mechas to the website database
 * Add items to the website database
 * Create Mega Man 3 field music
 * Update the movesets for Mega Man, Bass, Proto Man to include the Copy Shot and the Mirror Shot and update the level-up points appropriately
 * Fix the player editor's player items inventory when viewed under read-only circumstances (leaderboard, etc.)
 * Allow players to access all items, sorted by quantity, allow for scrolling in window I guess somehow? :(    (no! page links instead! :D)
 * Make sure OLD players returning to their name game do not have a "default" item order, make it so it's based on collected time
 * Make it so mecha's DO count toward turns and use the predefined constant, same thing for points maybe?
 * Make it so the bonus fields have 3 and 6 robots respectively, so they're not TOO long (FD3 should be only 8vs8)
 * Make it so Field Stars are harder to get, the robots and mechas on the field should benefit from the starforce of the star (maybe using existing starforce player field)
 * Make it so the target player's field in a player battle is based on their current omega factors
 * Make sure Star Force is considered in player battles - we should have it available on both sides to make it challenging
 * At the very least, make sure the same player does not show up in the leaderboard repeatedly... somehow... unless there are no other options?
 * Update the chapter links and make it so bonus and player battle chapters are hidden until unlocked, so it looks like there are only five when starting out
 * Go into Firefox and at the very least fix the core-type header on all database and editor frames/pages, it's showing too low
 * Make it so players can "favourite" robots in the robot editor, ensuring that they appear in player battles more regularily
 * Make it so Final Destination 3's robots are affected by the Chapter Two omega factors
 * Add ability generation to the last fortress 8vs8 battle, they only have 1-2 weapons each
 * Make it so the last fortress stage's robots are determined by the 8 omega field factors
 * Remove Attack/Defense/Speed Burn/Blaze from FireMan and PharaohMan's level-up, they are only accessible in shop now
 * Fix the mecha support ability, there are lots of bugs when one wins or is defeated
 * Fix the Danger Bomb - start by changing the type to explode, testing with a neutral weakness robot, then apply the bomb and allow to heal
 * Update the shop with the correct MM9 music background
 * Update the player editor to have different music than the robot editor (anything really)
 * Make sure each shop is unlocked at the correct point (debug mode kalinka right now)
 * Create Mega Man 3 field graphics
 * Make it so the stages only appear in shop after scanning their associated robot master at least once somewhere else, so Reflection Chamber appears in shop once you've scanned one Gemini Man (from bonus I guess)
 * Make sure the different shops are slow-released as you play, first when you beat intro mission, next when you have all three chars, and last when you beat the prototype
 * Create Mega Man 3 field mecha sprites
 * Go through and update all the field multipliers
 * Go through and update all the mecha stats
 * Make sure the game tracks global stats for players, so have a global "robot database" with everyone's individual database entries so we can have records on-page for robots, etc.
 * Make it so that the Robot Core item not only changes the robot's current core, but also adds an invisible attachment with attachment_damage_booster_{type} = 0 as an immunity, remove on next core item
 * Make it so incompatible abilities (by core) are greyed out in the battle menu (making the cores more of a choice)
 * Update Mega Man 3 field mecha positions
 * Create Mega Man 3 field mecha abilities
 * Update fields in-game and make sure the music is as-advertised on the website, this means redoing the three doctor stages, final destination 1-3, and converting all 11 variations of the prototype complete theme
 * Fix mecha placement in fusion fields, seems to be off for some reason
 * Item panel in battle menu should remember the page you're currently on
 * Redo Monking R's damage sprite - put his arms up like in other sprites
 * Update Super Arm's sprite and make sure the MM3 field blocks are accurate
 * Add the Copy Buster ability, and have it act as another buster that takes on the type of the user's core and is compatible with only the heroes
 * Revise first-access message for robot editor,
 * Add first-access message for player editor
 * Add first-access message for star force viewer
 * Add first-access message to the robot database
 * Add first-access message to the shop (three parts, one for auto, one for reggae, one for kalinka)
 * Add first-access message to the save
 * Add first-access message to the leaderboard
 * Cache the main stylesheet, that shit kills the server being generated for every new visitor
 * Remove the custom items from the item action panel, I may have testing items in there right now
 * Turn off DEBUG MODE and comment out all the debug messages
 * Run through every page and every frame to check for console.log debug messages - turn them OFF
 *
 *
 *
 *
 *
 *
 *
 * -- MY SECRET SCRAPPED IDEA LIST --
 *
 * Allow players to select their player field types, up to two
 * Add a "pokemon pc box" so that we can have a 12 robot limit per player
 * Add a 12 robot limit per player and update save files
 * Add proper page-links to robot selector, maybe avoid the entire "box" idea (it would be really... stupid)
 * Make it so the first eight master battles in each chapter yield heart tanks
 * Make it so each robot on the field gets a percentage of the player's heart tank boost ceil((100 * hearts) / robots)
 * Make it so that the generation of your Met is based on it's level maybe? 1 - 33 for gen one, 34 - 66 for gen two, and 67 - 100 for gen three
 * Rename the Crystal Cave field to Dazzling Grotto
 * Replace all inline calls for the ability index with a public static function in its class
 * Replace all inline calls for the robot index with a public static function in its class
 * Convert the player index to database format like the others, clean up all old references
 * Convert the field index to database format like the others, clean up all old references
 * Add two more stages to the Met, like a Neo Met and whatever comes next - basically same but with more power
 * Add a "Like" function to forum threads and posts in the community
 * Add something to top-left of prototype banner, maybe star, and zenny totals?
 * Update the player items area, make sure it doesn't show items you have not aquired in a new file - also hide fields until prototype complete
 * Change Magnet Man to an Earth Core robot (sorry!) with an Earth / Missile type Magnet Missile ability, update Hard Man's primary weakness to Earth, change Magnetic Generator to Earth with electric boost
 * Make it so the stages only appear after defeating their associated robot master at least once somewhere else, so Reflection Chamber appears in shop once you've defeated one Gemini Man (from bonus I guess)
 * Add mecha shards to the game, make it so they are lesser version of the robot cores
 * Add mechas shards to the shop as a sellable item, affected by starforce like the cores, but at 100z base rather than 1000z base
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *  -- OTHER JUNK I THINK --
 *
 *  CHAOS_FANTAZY: You know the Guard Module from Mega Man 9? We can play off of that.
Guard Module: Instantly doubles defense. Plain and simple.
Sniper Module: For the duration of the fight, any attack that normally hit the guy in front can now be aimed at any person.
Overclock Module: Attacks that require charge (e.g., Mega Buster) can now be charged and fired on the same turn.
...And then another one relating to speed or evasion. That's what I'm thinking.
 *
 *
 *
 */

?>