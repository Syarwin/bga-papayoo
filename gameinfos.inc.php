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

  'presentation' => [
    totranslate('Get rid of your Payoo!'),
    totranslate('There are no jacks, queens or kings in Papayoo – just an unusual die and a fifth suit called Payoo. The aim is to score the fewest points possible; to do so, try to avoid collecting those dreadful Payoos and especially the Papayoo, that confounded 7, whose suit changes with each new hand. That cursed die!'),
    totranslate("If you are unhappy with your hand, don't fret; just give your hand to the player on your left before starting. Be sure to make the right choice because you'll be getting the player's cards on your right. Then play your hand with no trumps or qualms. The best player doesn't always win!")
  ],
  'tags' => [2, 11, 200],

//////// BGA SANDBOX ONLY PARAMETERS (DO NOT MODIFY)

// simple : A plays, B plays, C plays, A plays, B plays, ...
// circuit : A plays and choose the next player C, C plays and choose the next player D, ...
// complex : A+B+C plays and says that the next player is A+B
'is_sandbox' => false,
'turnControl' => 'simple'
////////
];
