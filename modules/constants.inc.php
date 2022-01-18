<?php

/*
 * State constants
 */
define("ST_BGA_GAME_SETUP", 1);
define("ST_NEW_HAND", 20);
define("ST_GIVE_CARDS", 21);
define("ST_TAKE_CARDS", 22);
define("ST_THROW_DICE", 23);

define("ST_NEW_TRICK", 30);
define("ST_PLAY_CARD", 31);
define("ST_NEXT_PLAYER", 32);
define("ST_END_OF_TRICK", 33);

define("ST_END_HAND", 40);

define("ST_BGA_GAME_END", 99);

/*
 * Options constants
 */
define("OPTIONS_NBR_HANDS", 100);
define("JUST_ONE", 1);
define("ONE_PER_PLAYER", 2);
define("TWO_PER_PLAYER", 3);
define("THREE_PER_PLAYER", 4);

define("OPTIONS_SHIFTING_DISCARD", 101);
define("SHIFTING_OFF", 0);
define("SHIFTING_ON", 1);
?>
