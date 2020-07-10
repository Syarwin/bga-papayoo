<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * papayoo implementation : © Guillaume NAVEL <guillaume.navel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * papayoo game states description
 *
 */

$machinestates = [
  // The initial state. Please do not modify.
  ST_BGA_GAME_SETUP => [
    "name" => "gameSetup",
    "description" => clienttranslate("Game setup"),
    "type" => "manager",
    "action" => "stGameSetup",
    "transitions" => [ "" => ST_NEW_HAND]
  ],


  ST_NEW_HAND => [
    "name" => "newHand",
    "description" => "",
    "type" => "game",
    "action" => "stNewHand",
    "updateGameProgression" => true,
    "transitions" => [ "" => ST_GIVE_CARDS]
  ],

  ST_GIVE_CARDS => [
    "name" => "giveCards",
    "description" => clienttranslate('Some players must choose ${nbr_cards} cards to give to next player'),
    "descriptionmyturn" => clienttranslate('${you} must choose ${nbr_cards} cards to give to next player'),
    "type" => "multipleactiveplayer",
    "action" => "stGiveCards",
    "args" => "argGiveCards",
    "possibleactions" => [ "giveCards" ],
    "transitions" => [
      "giveCards" => ST_TAKE_CARDS,
      "skip" => ST_TAKE_CARDS,
    ]
  ],

  ST_TAKE_CARDS => [
    "name" => "takeCards",
    "description" => "",
    "type" => "game",
    "action" => "stTakeCards",
    "transitions" => [
      "throwDice" => ST_THROW_DICE,
      "skip" => ST_THROW_DICE
    ]
  ],

  ST_THROW_DICE => [
    "name" => "throwDice",
    "description" => "",
    "type" => "game",
    "action" => "stThrowDice",
    "transitions" => [
      "startHand" => ST_NEW_TRICK,
      "skip" => ST_NEW_TRICK
    ]
  ],


  // Trick
  ST_NEW_TRICK => [
    "name" => "newTrick",
    "description" => "",
    "type" => "game",
    "action" => "stNewTrick",
    "transitions" => [ "" => ST_PLAY_CARD]
  ],
  ST_PLAY_CARD => [
    "name" => "playerTurn",
    "description" => clienttranslate('${actplayer} must play a card'),
    "descriptionmyturn" => clienttranslate('${you} must play a card'),
    "type" => "activeplayer",
    "args" => "argPlayCard",
    "possibleactions" => [ "playCard" ],
    "transitions" => [ "playCard" => ST_NEXT_PLAYER ]
  ],
  ST_NEXT_PLAYER => [
    "name" => "nextPlayer",
    "description" => "",
    "type" => "game",
    "action" => "stNextPlayer",
    "transitions" => [
      "nextPlayer" => ST_PLAY_CARD,
      "nextTrick" => ST_NEW_TRICK,
      "endHand" => ST_END_HAND
    ]
  ],


  // End of the hand (scoring, etc...)
  ST_END_HAND => [
    "name" => "endHand",
    "description" => "",
    "type" => "game",
    "action" => "stEndHand",
    "transitions" => [
      "nextHand" => ST_NEW_HAND,
      "endGame" => ST_BGA_GAME_END
    ]
  ],

  // Final state.
  // Please do not modify.
  ST_BGA_GAME_END => [
    "name" => "gameEnd",
    "description" => clienttranslate("End of game"),
    "type" => "manager",
    "action" => "stGameEnd",
    "args" => "argGameEnd"
  ]
];
