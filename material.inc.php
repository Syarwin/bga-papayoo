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
 * material.inc.php
 *
 * papayoo game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

require_once("modules/constants.inc.php");


$this->game_configs = [
  3 => [
    'deal' => [3, 3, 3, 3, 3, 3, 2],
    'min_color_value' => 1,
    'nbr_cards_to_pass' => 5
  ],
  4 => [
    'deal' => [3, 3, 3, 3, 3],
    'min_color_value' => 1,
    'nbr_cards_to_pass' => 5
  ],
  5 => [
    'deal' => [3, 3, 3, 3],
    'min_color_value' => 1,
    'nbr_cards_to_pass' => 4
  ],
  6 => [
    'deal' => [3, 3, 3, 1],
    'min_color_value' => 1,
    'nbr_cards_to_pass' => 3
  ],
  7 => [
    'deal' => [3, 3, 2],
    'min_color_value' => 2,
    'nbr_cards_to_pass' => 3
  ],
  8 => [
    'deal' => [3, 3, 1],
    'min_color_value' => 2,
    'nbr_cards_to_pass' => 3
  ],
];

$this->cards_colors = [
  1 => [
    'name' => clienttranslate('club'),
    'nametr' => self::_('club'),
    'valeur_max' => 10
  ],
  2 => [
    'name' => clienttranslate('spade'),
    'nametr' => self::_('spade'),
    'valeur_max' => 10
  ],
  3 => [
    'name' => clienttranslate('heart'),
    'nametr' => self::_('heart'),
    'valeur_max' => 10
  ],
  4 => [
    'name' => clienttranslate('diamond'),
    'nametr' => self::_('diamond'),
    'valeur_max' => 10
  ],
  5 => [
    'name' => clienttranslate('payoo'),
    'nametr' => self::_('payoo'),
    'valeur_max' => 20
  ]
];

$this->dice_colors = [
  1 => [
    'name' => clienttranslate('club'),
    'nametr' => self::_('club'),
    'symbole' => '&clubs;'
  ],
  2 => [
    'name' => clienttranslate('spade'),
    'nametr' => self::_('spade'),
    'symbole' => '&spades;'
  ],
  3 => [
    'name' => clienttranslate('heart'),
    'nametr' => self::_('heart'),
    'symbole' => '&hearts;'
  ],
  4 => [
    'name' => clienttranslate('diamond'),
    'nametr' => self::_('diamond'),
    'symbole' => '&diams;'
  ],
];
