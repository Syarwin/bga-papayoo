/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * papayoo implementation : © Guillaume NAVEL <guillaume.navel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * papayoo.js
 *
 * papayoo user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

var isDebug = window.location.host == 'studio.boardgamearena.com';
var debug = isDebug ? console.info.bind(window.console) : function () { };

define(["dojo","dojo/_base/declare","ebg/core/gamegui","ebg/counter","ebg/stock"],function (dojo, declare) {
  return declare("bgagame.papayoo", ebg.core.gamegui, {


/*
 * Constructor
 */
constructor: function(){
  debug('papayoo constructor');

  this.playerHand = null;
  this.players = null;
  this.player_number = 0;

  this.card_board_width = 72;
  this.card_board_height = 122;

  this.card_hand_width = 90;
  this.card_hand_height = 152;

  this.playertable_width = 105; // max 100
  this.playertable_height = 140;
  this.margin = 20;

  this.playertables_width = Math.min(740, (this.playertable_width + this.margin*2.5)*5);
  this.playertables_height = (this.playertable_height + this.margin*2.5)*2.5;

  this.nbr_cards_to_give = 0;

  // Configuration of played card position for each player configuration
  this.played_card_position = [];
  this.played_card_position[3] = [[0, 0.75],  [-0.75, -0.75], [0.75, -0.75]]; // [dx, dy] offset of player card position
  this.played_card_position[4] = [[0, 0.75], [-1.2, 0], [0, -0.75], [1.2, 0]];
  this.played_card_position[5] = [[0, 0.75], [-1.2, 0.4], [-0.75, -0.75], [0.75, -0.75], [1.2, 0.4]];
  this.played_card_position[6] = [[0, 0.75], [-1.2, 0.5], [-1.2, -0.5], [0, -0.75], [1.2, -0.5], [1.2, 0.5]];
  this.played_card_position[7] = [[0, 0.75], [-1, 0.5], [-2, 0], [-0.75, -0.75], [0.75, -0.75], [2, 0], [1, 0.5]];
  this.played_card_position[8] = [[0, 0.75], [-1, 0.5], [-2, 0], [-1, -0.5], [0, -0.75], [1, -0.5], [2, 0], [1, 0.5]];
},


/*
 * Setup:
 *  This method set up the game user interface according to current game situation specified in parameters
 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
 *
 * Params :
 *  - mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
 */
setup: function(gamedatas){
  this.nbr_cards_to_give = gamedatas.nbr_cards_to_give;

  // Add playertables div
  dojo.place(this.format_block( 'jstpl_playertables', {
    w: this.playertables_width,
    h: this.playertables_height,
  }), 'gamezone');

  var diceColor = gamedatas.dice_color;
  var dicetext = 'No papayoo';
  if (diceColor != 0) {
  dicetext = dojo.string.substitute(
    _("Papayoo is 7${s}"), {s: gamedatas.dice_colors[diceColor]['symbole']});
  }

  // Add dice div
  dojo.place(this.format_block( 'jstpl_dicevalue', {
      x: this.playertables_width/2-50 - this.margin,
      y: this.playertables_height/2-15 - this.margin,
      text: dicetext
  }), 'playertables');

  // Place payer zone
  this.players = gamedatas.players;
  this.player_number = Object.keys(this.players).length;

  var dx = 0;
  var dy = 0;
  var size_dx = this.playertable_width + this.margin*2;
  var size_dy = this.playertable_height + this.margin*2;

  var player_id = gamedatas.current_player_id;
  for(var i = 1; i <= this.player_number; i++) {
    var player = this.players[player_id];
    dx = this.played_card_position[this.player_number][i-1][0];
    dy = this.played_card_position[this.player_number][i-1][1];

    var x = this.playertables_width/2 +dx*size_dx - this.playertable_width/2 - this.margin;
    var y = this.playertables_height/2 - dy*size_dy - this.playertable_height/2 - this.margin;

    dojo.place(this.format_block( 'jstpl_playertable', {
        w: this.playertable_width,
        h: this.playertable_height,
        x: x,
        y: y,
        player_id: player_id,
        player_color: player['player_color'],
        player_name: player['player_name'],
        nbr_of_tricks: player['nbr_of_tricks_win']
    } ), 'playertables');

    player_id = gamedatas.next_players_id[player_id];
  }

  // Player hand
  this.playerHand = new ebg.stock();
  this.playerHand.create( this, $('myhand'), this.card_hand_width, this.card_hand_height );
  this.playerHand.setSelectionAppearance('class');
  this.playerHand.image_items_per_row = 10;
  dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' );

  // Create cards types:
  for(var color = 1; color <= 5;color++){
    var maxvalue = gamedatas.cards_colors[color]['valeur_max'];
    for( var value = 1; value <= maxvalue; value++ ){
      // Build card type id
      var card_type_id = this.getCardUniqueId(color, value);
      this.playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl+'img/cards.png', card_type_id );
    }
  }

  // Cards in player's hand
  for( var i in this.gamedatas.hand){
    var card = this.gamedatas.hand[i];
    var color = card.type;
    var value = card.type_arg;
    this.playerHand.addToStockWithId( this.getCardUniqueId(color, value), card.id );
  }

  // Cards played on table
  for( i in this.gamedatas.cards_on_table ){
    var card = this.gamedatas.cards_on_table[i];
    var color = card.type;
    var value = card.type_arg;
    var player_id = card.location_arg;
    this.playCardOnTable( player_id, color, value, card.id );
  }

  this.addTooltipToClass( "playertablecard", _("Card played on the table"), '' );

  // Setup game notifications to handle (see "setupNotifications" method below)
  this.setupNotifications();
//  this.ensureSpecificImageLoading( ['../common/point.png'] );
},



/*
 * onEnteringState:
 * 	this method is called each time we are entering into a new game state.
 * params:
 *  - str stateName : name of the state we are entering
 *  - mixed args : additional information
 */
onEnteringState: function (stateName, args) {
  debug('Entering state: ' + stateName, args);
  if (!this.isCurrentPlayerActive()) return;

  if(stateName == 'giveCards'){
    this.addTooltip( 'myhand', _('Cards in my hand'), _('Select a card') );
  }

  else if(stateName == 'playerTurn'){
    this.addTooltip( 'myhand', _('Cards in my hand'), _('Play a card') );
    dojo.query("#myhand .stockitem").addClass("disabled");
    args.args.cards.forEach(card => dojo.removeClass('myhand_item_' + card.id, "disabled"));
  }
},

/*
 * onLeavingState:
 * 	this method is called each time we are leaving a game state.
 *
 * params:
 *  - str stateName : name of the state we are leaving
 */
onLeavingState: function (stateName) {
  debug('Leaving state: ' + stateName);
  dojo.query("#myhand .stockitem").removeClass("disabled");
},


/*
 * onUpdateActionButtons:
 * 	called by BGA framework before onEnteringState
 *  in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
 */
onUpdateActionButtons: function (stateName, args,) {
},





////////////////////////////////
////////////////////////////////
/////    Cards selection    ////
////////////////////////////////
////////////////////////////////
onPlayerHandSelectionChanged: function(control_name, item_id)
{
  var state = this.gamedatas.gamestate.name;
  var items = this.playerHand.getSelectedItems();

  if(state == "giveCards"){
    this.removeActionButtons();
    if(!this.playerHand.isSelected(item_id))
      return;

    if(items.length > this.gamedatas.nbr_cards_to_give)
      this.playerHand.unselectItem(item_id);

    items = this.playerHand.getSelectedItems()
    if(items.length == this.gamedatas.nbr_cards_to_give)
      this.addActionButton( 'giveCards_button', _('Give selected cards'), 'onGiveCards' );
  }


    if( items.length > 0 )
    {
        if( this.checkAction( 'playCard', true ) )
        {
            // Can play a card

            var card_id = items[0].id;

            this.ajaxcall( "/papayoo/papayoo/playCard.html", {
                    id: card_id,
                    lock: true
                    }, this, function( result ) {  }, function( is_error) { } );

            this.playerHand.unselectAll();
            dojo.query('.stockitem').removeClass('receivedCard');
        }
        else if( this.checkAction( 'giveCards' ) )
        {
            // Can give cards => let the player select some cards
        }
        else
        {
            this.playerHand.unselectAll();
        }
    }
},



////////////////////////////////
////////////////////////////////
/////////    Utils    //////////
////////////////////////////////
////////////////////////////////

// Get card unique identifier based on its color and value
getCardUniqueId: function( color, value )
{
  return (color-1)*10+(value-1);
},


        playCardOnTable: function( player_id, color, value, card_id )
        {
            // player_id => direction
            var card_type_id = this.getCardUniqueId(color, value);
            dojo.place(
                this.format_block( 'jstpl_cardontable', {
                    x: this.card_board_width*(card_type_id%10),
                    y: this.card_board_height*(Math.floor(card_type_id/10)),
                    player_id: player_id
                } ), 'playertablecard_'+player_id );



            if( player_id != this.player_id )
            {
                // Some opponent played a card
                // Move card from player panel
                this.placeOnObject( 'cardontable_'+player_id, 'overall_player_board_'+player_id );
            }
            else
            {
                // You played a card. If it exists in your hand, move card from there and remove
                // corresponding item

                if( $('myhand_item_'+card_id) )
                {
                    this.placeOnObject( 'cardontable_'+player_id, 'myhand_item_'+card_id );
                    this.playerHand.removeFromStockById( card_id );
                }
            }

            // In any case: move it to its final destination
            this.slideToObject( 'cardontable_'+player_id, 'playertablecard_'+player_id ).play();

        },


        ///////////////////////////////////////////////////
        //// Player's action

        /*

            Here, you are defining methods to handle player's action (ex: results of mouse click on
            game objects).

            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server

        */



        onGiveCards: function()
        {
            if( this.checkAction( 'giveCards' ) )
            {
                var items = this.playerHand.getSelectedItems();

                if( items.length != this.nbr_cards_to_give)
                {
                    this.showMessage(dojo.string.substitute(
                            _("You must select exactly ${n} cards"), {n: this.nbr_cards_to_give})
                            , 'error');
                    return;
                }

                // Give these cards
                var to_give = '';
                for( var i in items )
                {
                    to_give += items[i].id+';';
                }
                this.ajaxcall( "/papayoo/papayoo/giveCards.html", { cards: to_give, lock: true }, this, function( result ) {
                }, function( is_error) { } );
            }
        },

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:

            In this method, you associate each of your game notifications with your local method to handle it.

            Note: game notification names correspond to your "notifyAllPlayers" and "notifyPlayer" calls in
                  your emptygame.game.php file.

        */

        setupNotifications: function()
        {
            dojo.subscribe( 'newHand', this, "notif_newHand" );
            dojo.subscribe( 'playCard', this, "notif_playCard" );
            dojo.subscribe( 'trickWin', this, "notif_trickWin" );
            this.notifqueue.setSynchronous( 'trickWin', 1000 );
            dojo.subscribe( 'giveAllCardsToPlayer', this, "notif_giveAllCardsToPlayer" );
            dojo.subscribe( 'newScores', this, "notif_newScores" );
            dojo.subscribe( 'giveCards', this, "notif_giveCards" );
            dojo.subscribe( 'takeCards', this, "notif_takeCards" );
            dojo.subscribe( 'throwDice', this, "notif_throwDice" );
        },

        // TODO: from this point and below, you can write your game notifications handling methods

        notif_newHand: function( notif )
        {
            // We received a new full hand of 13 cards.
            this.playerHand.removeAll();

            for( var i in notif.args.cards )
            {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId( this.getCardUniqueId( color, value ), card.id );
            }
        },

        notif_playCard: function( notif )
        {
            // Play a card on the table
            this.playCardOnTable( notif.args.player_id, notif.args.color, notif.args.value, notif.args.card_id );
        },
        notif_trickWin: function( notif )
        {
            // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
        },
        notif_giveAllCardsToPlayer: function( notif )
        {
            // Move all cards on table to given table, then destroy them
            var winner_id = notif.args.player_id;
            for( var player_id in this.gamedatas.players )
            {
                var anim = this.slideToObject( 'cardontable_'+player_id, 'player_nbr_trick_'+winner_id );
                dojo.connect( anim, 'onEnd', function( node ) { dojo.destroy(node);  } );
                anim.play();
            }
            $('player_nbr_trick_'+winner_id).innerHTML = notif.args.nbr_of_tricks;
        },
        notif_newScores: function( notif )
        {
            // Update players' scores

            for( var player_id in notif.args.newScores )
            {
                this.scoreCtrl[ player_id ].toValue( notif.args.newScores[ player_id ] );
                $('player_nbr_trick_'+player_id).innerHTML = 0;
            }
        },
        notif_giveCards: function( notif )
        {
            // Remove cards from the hand (they have been given)
            for( var i in notif.args.cards )
            {
                var card_id = notif.args.cards[i];
                this.playerHand.removeFromStockById( card_id );
            }
        },
        notif_takeCards: function( notif )
        {
            // Cards taken from some opponent
            for( var i in notif.args.cards )
            {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId( this.getCardUniqueId( color, value ), card.id );
                dojo.query('#'+this.playerHand.getItemDivId(card.id)).addClass('receivedCard');
            }
        },
        notif_throwDice: function( notif )
        {
            // Cards taken from some opponent
            if (notif.args.dice_value != 0){
                $('dicevalue').innerHTML = dojo.string.substitute(
                            _("Papayoo is 7${s}"), {s: notif.args.dice_symbole});
            } else {
                $('dicevalue').innerHTML = "";
            }
        }
   });
});
