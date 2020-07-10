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
  * papayoo.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once("modules/constants.inc.php");

class papayoo extends Table
{
	function __construct( )
	{
		parent::__construct();

		self::initGameStateLabels([
			"trickColor" => 10, // Color of the first card play
			"remaining_to_play" => 11, // Number of remaining cards to play in that deal
			"dealer_id" => 13, // Current dealer, dealer play first
			"dice_color" => 14, // Current color of the dice
			"number_of_deals" => 15, // Number of deal in the game

			"nbr_deals_mode" => 100, // Number of deals mode
		]);

		// Init deck of cards
		$this->cards = self::getNew( "module.common.deck" );
		$this->cards->init( "card" );
	}


	protected function getGameName( )
	{
		return "papayoo";
	}

	/*
	 * setupNewGame:
	 */
protected function setupNewGame( $players, $options = array() )
{
	/************ Player init *****/
	self::DbQuery("DELETE FROM player WHERE 1");
	$gameinfos = self::getGameinfos();
	$default_colors = $gameinfos['player_colors'];
	$nbr_players = count($players);
	$game_config = $this->game_configs[$nbr_players];

	$sql = "INSERT INTO player (player_no, player_id, player_score, player_color, player_canal, player_name, player_avatar) VALUES ";
	$values = [];
	$default_score = 0;
	$player_no = 1;
	foreach(array_rand($players, $nbr_players) as $player_id){
		$player = $players[$player_id];
		$color = array_shift( $default_colors );
		$values[] = "('$player_no','$player_id','$default_score','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
		$player_no++;
	}
	self::DbQuery( $sql . implode( $values, ',' ));
	self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
	self::reloadPlayersBasicInfos();

	/************ Start the game initialization *****/
	/*
	 *  Color of the first card played :
	 *  0 -> no card play
	 *  1 -> club
	 *  2 -> spade
	 *  3 -> heart
	 *  4 -> diamond
	 *  5 -> payoo
	 */
	self::setGameStateInitialValue( 'trickColor', 0 );
	// Number of remaining cards to play in that deal
	self::setGameStateInitialValue( 'remaining_to_play', 0 );
	// Current dealer, dealer play first
	self::setGameStateInitialValue( 'dealer_id', 0 );

	// Init number of deals
	$hands_option = self::getGameStateValue('nbr_deals_mode');
	$nbrHands = 0;
	if ($hands_option == JUST_ONE) $nbrHands = 1;
	else if ($hands_option == ONE_PER_PLAYER) $nbrHands = $nbr_players;
	else if ($hands_option == TWO_PER_PLAYER) $nbrHands = 2*$nbr_players;
	else if ($hands_option == THREE_PER_PLAYER) $nbrHands = 3*$nbr_players;

	self::setGameStateInitialValue( 'number_of_deals', $nbrHands);

	/*
	 *  Current color of the dice
	 *  0 -> dice not throw yet
	 *  1 -> club
	 *  2 -> spade
	 *  3 -> heart
	 *  4 -> diamond
	 */
	self::setGameStateInitialValue( 'dice_color', 0 );

	// Init game statistics
	// (note: statistics are defined in your stats.inc.php file)
	self::initStat( "table", "handNbr", 0 );
	self::initStat( "player", "nbrOfPayoo", 0 );
	self::initStat( "player", "nbrOfPapayoo", 0 );
	self::initStat( "player", "nbrOfTrick", 0 );
	self::initStat( "player", "nbrNoPointCards", 0 );

	// Create cards
	$cards = array();
	foreach( $this->cards_colors as  $color_id => $cards_color ){ // club, spade, heart, diamond, payoo
		if ($color_id != 5) { // No payoo, remove 1 cards for 7 and 8 players
			$min_value = $game_config['min_color_value'];
		} else {
			$min_value = 1;
		}

		$max_value = $cards_color['valeur_max']; // Max value depand of card color
		for( $value=$min_value; $value<=$max_value; $value++ ){   //  min value to max value
			$cards[] = array( 'type' => $color_id, 'type_arg' => $value, 'nbr' => 1);
		}
	}

	$this->cards->createCards( $cards, 'deck' );

	// Get the first dealer and the first player
	$dealer_id = self::activeNextPlayer(); // This player will be the first dealer
	self::setGameStateValue('dealer_id', $dealer_id);
	/************ End of the game initialization *****/
}


/*
 * getAllDatas:
 */
protected function getAllDatas()
{
	$result = [];
	$current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

	// Get information about players
	// Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
	$sql = "SELECT player_id, player_score, player_color, player_name, nbr_of_tricks_win FROM player ";
	$result['players'] = self::getCollectionFromDb( $sql );
	$result['current_player_id'] = self::getCurrentPlayerId();
	$result['next_players_id'] = self::createNextPlayerTable(array_keys(self::loadPlayersBasicInfos()));

	// Cards configuraiton
	$result['cards_colors'] = $this->cards_colors;
	// Cards in player hand
	$result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );
	// Cards played on the table
	$result['cards_on_table'] = $this->cards->getCardsInLocation( 'cards_on_table' );
	// Color of the dice
	$result['dice_color'] = self::getGameStateValue( 'dice_color' ) ;
	$result['dice_colors'] = $this->dice_colors;
	// Number of cards to give
	$game_config = $this->game_configs[self::getPlayersNumber()];
	$result['nbr_cards_to_give'] = $game_config['nbr_cards_to_pass'];

	return $result;
}

/*
 * getGameProgression:
 */
function getGameProgression()
{
	// Game progression: number of the deal
 	return 100 * (self::getStat('handNbr') - 1)/ self::getGameStateValue('number_of_deals');
}


//////////////////////////////////////////
//////////// Utility functions ///////////
//////////////////////////////////////////

function throwDice()
{
	$dice_value = bga_rand(1, 4);
	self::setGameStateValue('dice_color', $dice_value);
	return $dice_value;
}


////////////////////////////////////
////////////////////////////////////
//////////// New Hand //////////////
////////////////////////////////////
////////////////////////////////////
function stNewHand()
{
	self::incStat( 1, "handNbr" );
	self::setGameStateValue('dice_color', 0);
	self::notifyAllPlayers('throwDice', '', ['dice_value' => 0]);

	// Take back all cards (from any location => null) to deck
	$this->cards->moveAllCardsInLocation(null, "deck");
	$this->cards->shuffle('deck');

	// Deal cards to players : create deck, shuffle it and give 13 initial cards
	$game_config = $this->game_configs[self::getPlayersNumber()];
	$players = self::loadPlayersBasicInfos();
	foreach( $game_config['deal'] as $id => $nbr_card_to_deal) {
		foreach( $players as $player_id => $player ) {
			$this->cards->pickCards($nbr_card_to_deal, 'deck', $player_id );
		}
	}

	// Notify player about its cards
	foreach($players as $player_id => $player) {
		$cards = $this->cards->getCardsInLocation( 'hand', $player_id );
		self::notifyPlayer( $player_id, 'newHand', '', ['cards' => $cards]);
	}

	// Select first player
	$dealer_id = self::getGameStateValue('dealer_id');
	$next_player = self::getPlayerAfter($dealer_id);
	$this->gamestate->changeActivePlayer($next_player);
	self::setGameStateValue('dealer_id', $next_player);

	$this->gamestate->nextState("");
}



///////////////////////////////
///////// Give Cards //////////
///////////////////////////////
// The players all give {n} cards to their neighbour
///////////////////////////////

function stGiveCards()
{
		$this->gamestate->setAllPlayersMultiactive();
}

function argGiveCards()
{
	$game_config = $this->game_configs[self::getPlayersNumber()];
	return [
		"nbr_cards" => $game_config['nbr_cards_to_pass'],
	];
}

function giveCards($card_ids)
{
	self::checkAction( "giveCards" );

	$player_id = self::getCurrentPlayerId();
	$game_config = $this->game_configs[self::getPlayersNumber()];
	if( count($card_ids) != $game_config['nbr_cards_to_pass'])
		throw new feException(sprintf(self::_("You must give exactly %s cards"), $game_config['nbr_cards_to_pass']));

	// Check if these cards are in player hands
	$cards = $this->cards->getCards($card_ids);

	if(count($cards) != $game_config['nbr_cards_to_pass'])
		throw new feException(self::_("Some of these cards don't exist"));

	foreach( $cards as $card ) {
		if($card['location'] != 'hand' || $card['location_arg'] != $player_id)
			throw new feException(self::_("Some of these cards are not in your hand"));
	}

	// To which player should I give these cards ?
	$players = self::loadPlayersBasicInfos();
	$nextPlayerList = self::createNextPlayerTable(array_keys($players));
	$nextPlayer_id = $nextPlayerList[$player_id];
	if( !isset($nextPlayer_id))
		throw new feException(self::_("Next player doesn't existe"));

	// Allright, these cards can be given to this player
	// (note: we place the cards in some temporary location in order he can't see them before the hand starts)
	$this->cards->moveCards( $card_ids, "temporary", $nextPlayer_id );

	// Notify the player so we can make these cards disapear
	self::notifyPlayer( $player_id, "giveCards", "", array(
			"cards" => $card_ids
	) );

	// Make this player unactive now
	// (and tell the machine state to use transtion "giveCards" if all players are now unactive
	$this->gamestate->setPlayerNonMultiactive( $player_id, "giveCards" );
}


// Take cards given by the other player
function stTakeCards()
{
	$players = self::loadPlayersBasicInfos();
	foreach( $players as $player_id => $player ){
		// Each player takes cards in the "temporary" location and place it in his hand
		$cards = $this->cards->getCardsInLocation( "temporary", $player_id );
		$this->cards->moveAllCardsInLocation( "temporary", "hand", $player_id, $player_id );
		self::notifyPlayer( $player_id, "takeCards", "", ["cards" => $cards]);
	}

	$this->gamestate->nextState( "throwDice" );
}


function stThrowDice()
{
	$dice_value = self::throwDice();
	self::notifyAllPlayers( 'throwDice', clienttranslate('The dice value is ${dice_name} ${dice_symbole}'), [
		'dice_value' => $dice_value,
		'dice_name' => $this->dice_colors[$dice_value]['name'],
		'dice_symbole' => $this->dice_colors[$dice_value]['symbole'],
	]);
	$this->gamestate->nextState( "startHand" );
}




///////////////////////////////
///////// Start trick /////////
///////////////////////////////
function argPlayCard()
{
	$pId = self::	getActivePlayerId();
	$hand = array_values($this->cards->getCardsInLocation('hand', $pId));
	$currentTrickColor = self::getGameStateValue('trickColor');
	if($currentTrickColor != 0) {
		$cards = array_values(array_filter($hand, function($card) use ($currentTrickColor){
			return $card['type'] == $currentTrickColor;
		}));

		if(count($cards) > 0)
			$hand = $cards;
	}

	return ['cards' => $hand];
}

// Play a card from player hand
function playCard($card_id)
{
	self::checkAction( "playCard" );
	$arg = $this->argPlayCard();
	$cards = array_values(array_filter($arg['cards'], function($card) use ($card_id){
		return $card['id'] == $card_id;
	}));

	if(count($cards) != 1)
		throw new feException("You cannot play this card");

	$currentCard = $cards[0];

	// First card of the trick, save card color
	if(self::getGameStateValue('trickColor') == 0)
		self::setGameStateValue('trickColor', $currentCard['type']);

	$player_id = self::getActivePlayerId();
	$this->cards->moveCard( $card_id, 'cards_on_table', $player_id );
	self::notifyAllPlayers( 'playCard', clienttranslate('${player_name} plays ${value_displayed} ${color_displayed}'), [
		'i18n' => array( 'color_displayed', 'value_displayed' ),
		'card_id' => $card_id,
		'player_id' => $player_id,
		'player_name' => self::getActivePlayerName(),
		'value' => $currentCard['type_arg'],
		'value_displayed' => $currentCard['type_arg'],
		'color' => $currentCard['type'],
		'color_displayed' => $this->cards_colors[$currentCard['type']]['name']
	]);
	$this->gamestate->nextState( 'playCard' );
}




///////////////////////////////////////////////
//////////// Game state arguments
////////////

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////




    function stNewTrick()
    {
        // New trick: active the player who wins the last trick, or the player who own the club-2 card

        // Reset trick color to 0 (= no color)
        self::setGameStateInitialValue( 'trickColor', 0 );

        $this->gamestate->nextState();
    }
    function stNextPlayer()
    {
        $players = self::loadPlayersBasicInfos();
        $nbr_players = self::getPlayersNumber();

        // Active next player OR end the trick and go to the next trick OR end the hand
        if($this->cards->countCardInLocation( 'cards_on_table' ) == $nbr_players)
        {
            // This is the end of the trick
            // Who wins ?
            $cards_on_table = $this->cards->getCardsInLocation( 'cards_on_table' );
            $best_value = 0;
            $best_value_player_id = null;
            $currentTrickColor = self::getGameStateValue('trickColor');

            foreach($cards_on_table as $card) {
                if( $card['type'] == $currentTrickColor )   // Note: type = card color
                {
                    if( $card['type_arg'] > $best_value ) {
                        $best_value_player_id = $card['location_arg'];  // Note: location_arg = player who played this card on table
                        $best_value = $card['type_arg'];        // Note: type_arg = value of the card
                    }
                }
            }

            if( $best_value_player_id === null )
                throw new feException( self::_("Error, nobody wins the trick") );

            // Move all cards to "cards_won" of the given player
            $this->cards->moveAllCardsInLocation( 'cards_on_table', 'cards_won', null, $best_value_player_id );

            // Notify
            // Note: we use 2 notifications here in order we can pause the display during the first notification
            //  before we move all cards to the winner (during the second)
            self::notifyAllPlayers( 'trickWin', clienttranslate('${player_name} wins the trick'), array(
                'player_id' => $best_value_player_id,
                'player_name' => $players[ $best_value_player_id ]['player_name']
            ) );

            // Increase number of tricks win
            $sql = "UPDATE player SET nbr_of_tricks_win=nbr_of_tricks_win+1
                        WHERE player_id='$best_value_player_id' " ;
            self::DbQuery( $sql );

            $sql = "SELECT player_id, player_score, player_color, player_name, nbr_of_tricks_win FROM player ";
            $players = self::getCollectionFromDb( $sql );

            self::notifyAllPlayers( 'giveAllCardsToPlayer','', array(
                'player_id' => $best_value_player_id,
                'nbr_of_tricks' => $players[$best_value_player_id]['nbr_of_tricks_win']
            ) );

            // Active this player => he's the one who starts the next trick
            $this->gamestate->changeActivePlayer($best_value_player_id);

            if($this->cards->countCardInLocation( 'hand' ) == 0) {
                // End of the hand
                $this->gamestate->nextState( "endHand" );
            } else {
                // End of the trick
                $this->gamestate->nextState( "nextTrick" );
            }
        } else {
            // Standard case (not the end of the trick)
            // => just active the next player
            $player_id = self::activeNextPlayer();
            self::giveExtraTime( $player_id );

            $this->gamestate->nextState( 'nextPlayer' );
        }
    }
    function stEndHand()
    {
        // Count and score points, then end the game or go to the next hand.
        $players = self::loadPlayersBasicInfos();

        // Count points of each player
        $player_with_papayoo = null;
        $player_points = array();
        foreach( $players as $player_id => $player ) {
            $player_points[$player_id] = 0;
        }

        $diceColor = self::getGameStateValue('dice_color');
        $cards = $this->cards->getCardsInLocation( "cards_won" );
        foreach( $cards as $card )
        {
            $player_id = $card['location_arg'];

            if($card['type'] == $diceColor && $card['type_arg'] == 7 ) {
                // papayoo = 40pts
                $player_points[$player_id] -= 40;
                self::incStat(1, "nbrOfPapayoo", $player_id);
            } else if($card['type'] == 5) {
                // Payoo value = pts
                $player_points[$player_id] -= $card['type_arg'];
                 self::incStat(1, "nbrOfPayoo", $player_id);
            } else {
                // No point cards
                self::incStat(1, "nbrNoPointCards", $player_id);
            }
        }

        // Apply scores to player
        foreach($player_points as $player_id => $points )
        {
            $sql = "UPDATE player SET player_score=player_score+$points
                     WHERE player_id='$player_id' " ;
            self::DbQuery( $sql );

            if( $points != 0 )
            {
                // Now, notify about the point win.
                self::notifyAllPlayers( "points", clienttranslate( '${player_name} wins ${points} points' ), array(
                    'player_id' => $player_id,
                    'player_name' => $players[$player_id]['player_name'],
                    'points' => $points
                ) );
            } else {
                // No point lost (just notify)
                self::notifyAllPlayers( "points", clienttranslate( '${player_name} did not get any point' ), array(
                    'player_id' => $player_id,
                    'player_name' => $players[ $player_id ]['player_name']
                ) );
            }
        }

        // Clear nbr_of_tricks_win
        $sql = "UPDATE player SET nbr_of_tricks_win=0 WHERE 1 " ;
        self::DbQuery( $sql );

        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );
        self::notifyAllPlayers( "newScores", '', array( 'newScores' => $newScores ) );

        //////////// Display table window with results /////////////////
        $table = array();

        // Header line
        $firstRow = array( '' );
        foreach( $players as $player_id => $player )
        {
            $firstRow[] = array( 'str' => '${player_name}',
                                 'args' => array( 'player_name' => $player['player_name'] ),
                                 'type' => 'header'
                               );
        }
        $table[] = $firstRow;

        // Points of the hand
        $newRow = array( array( 'str' => clienttranslate('Hand points'), 'args' => array() ) );
        foreach( $player_points as $player_id => $points )
        {
            $newRow[] = $points;
        }
        $table[] = $newRow;

        // Total points
        $newRow = array( array( 'str' => clienttranslate('Total points'), 'args' => array() ) );
        foreach( $newScores as $player_id => $points )
        {
            $newRow[] = $points;
        }
        $table[] = $newRow;



        ///// Test if this is the end of the game
        if (self::getStat('handNbr') >= self::getGameStateValue('number_of_deals')){
            // Trigger the end of the game !
            $this->notifyAllPlayers( "tableWindow", '', array(
                "id" => 'finalScoring',
                "title" =>  sprintf(clienttranslate('Result of hand %d/%d'), self::getStat('handNbr'), self::getGameStateValue('number_of_deals')),
                "table" => $table,
                "closing" => clienttranslate( "End of game" )
            ) );
            $this->gamestate->nextState( "endGame" );
            return ;
        }

        $this->notifyAllPlayers( "tableWindow", '', array(
            "id" => 'finalScoring',
            "title" =>  sprintf(clienttranslate('Result of hand %d/%d'), self::getStat('handNbr'), self::getGameStateValue('number_of_deals')),
            "table" => $table,
            "closing" => clienttranslate( "Next hand" )
        ) );


        // Otherwise... new hand !
        $this->gamestate->nextState( "nextHand" );
    }


////////////////////////////////
//////////// Zombie ////////////
////////////////////////////////
  function zombieTurn( $state, $active_player )
  {
      throw new feException( "Zombie mode not supported for Papayoo" );
  }

/////////////////////////////////
////////// DB upgrade ///////////
/////////////////////////////////
	function upgradeTableDb( $from_version )
	{
	}
}
