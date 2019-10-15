====================================================
== MMRPG Prototype API Now Available (2019/10/15) ==
====================================================
A simple data API is now available for the players, robots, types, etc. that appear in the game.
The API is accessed via GET and returns data in JSON format.

All endpoints follow the same basic URL format:
http://prototype.mmrpg-world.net/api/v2/{kind}
http://prototype.mmrpg-world.net/api/v2/{kind}/all
http://prototype.mmrpg-world.net/api/v2/{kind}/index
http://prototype.mmrpg-world.net/api/v2/{kind}/index/all
http://prototype.mmrpg-world.net/api/v2/{kind}/{token}

The endpoint variables accept the following values:
{kind} can be one of [players|robots|mechas|bosses|fields|abilities|items|types]
{token} can be any valid token for a player, robot, mecha, etc.

A list of example requests using the API:
http://prototype.mmrpg-world.net/api/v2/types
http://prototype.mmrpg-world.net/api/v2/types/all
http://prototype.mmrpg-world.net/api/v2/types/crystal
http://prototype.mmrpg-world.net/api/v2/robots
http://prototype.mmrpg-world.net/api/v2/robots/crystal-man
http://prototype.mmrpg-world.net/api/v2/bosses/all
http://prototype.mmrpg-world.net/api/v2/fields
http://prototype.mmrpg-world.net/api/v2/items

Basic usage of the API is as follows:
Make a GET request to any valid endpoint using your programming language of choice and receive a JSON payload in return.
Example: {"status":"success","updated":1570595460,"data":{"types":["none","cutter",...],"total":20}}
Decode the data via whichever method is available to your environment and then use it as desired.