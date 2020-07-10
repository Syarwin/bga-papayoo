<?php

$gameinfos = [
  'game_name' => "Papayoo",
  'designer' => '(Uncredited)',
  'artist' => '(Uncredited)',
  'year' => 2010,
  'publisher' => 'Gigamic',
  'publisher_website' => 'https://www.gigamic.com/',
  'publisher_bgg_id' => 155,
  'bgg_id' => 73365,

  'players' => array(3,4,5,6,7,8),
  'suggest_player_number' => null,
  'not_recommend_player_number' => null,

  'estimated_duration' => 30,
  'fast_additional_time' => 30,
  'medium_additional_time' => 40,
  'slow_additional_time' => 50,

  'tie_breaker_description' => "",

  'losers_not_ranked' => false,

  'is_beta' => 1,
  'is_coop' => 0,

  'complexity' => 1,
  'luck' => 3,
  'strategy' => 3,
  'diplomacy' => 0,

  'player_colors' => [ "ff0000", "008000", "0000ff", "ffa500", "773300", "32E0FF", "5D0075", "726E5E" ],
  'favorite_colors_support' => true,

  'game_interface_width' => [
    'min' => 740,
    'max' => null
  ],

  'presentation' => [],
  'tags' => [2, 11, 200],

//////// BGA SANDBOX ONLY PARAMETERS (DO NOT MODIFY)

// simple : A plays, B plays, C plays, A plays, B plays, ...
// circuit : A plays and choose the next player C, C plays and choose the next player D, ...
// complex : A+B+C plays and says that the next player is A+B
'is_sandbox' => false,
'turnControl' => 'simple'
////////
];
