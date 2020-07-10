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
 * papayoo.view.php
 *
 */

require_once( APP_BASE_PATH."view/common/game.view.php" );

class view_papayoo_papayoo extends game_view
{
  function getGameName()
  {
    return "papayoo";
  }

  function build_page( $viewArgs )
  {
    // Get players & players number
    $players = $this->game->loadPlayersBasicInfos();
    $players_nbr = count( $players );

    $this->tpl['MY_HAND'] = self::_("My hand");
  }
}
