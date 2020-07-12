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

  this.playertable_width = 130; // max 100
  this.playertable_height = 165;
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
  debug("SETUP:", gamedatas);
  this.nbr_cards_to_give = gamedatas.nbr_cards_to_give;

  // Add playertables div
  dojo.place(this.format_block( 'jstpl_playertables', {
    w: this.playertables_width,
    h: this.playertables_height,
  }), 'gamezone');

  // Add dice div
  dojo.place( '<div class="player-board" id="gameinfo"><div id="hand-counter"></div></div>', 'player_boards');
  dojo.place(this.format_block( 'jstpl_dicevalue', {
      // x:this.playertables_width/2-100 - this.margin,
      // y:this.playertables_height/2-100 - this.margin,
      c: gamedatas.dice_color,
  }), 'gameinfo');


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

    var x = this.playertables_width/2 +dx*size_dx - this.playertable_width/2;
    var y = this.playertables_height/2 - dy*size_dy - this.playertable_height/2 - this.margin;

    dojo.place(this.format_block( 'jstpl_playertable', {
        w: this.playertable_width,
        h: this.playertable_height,
        x: x,
        y: y,
        player_id: player_id,
        player_color: player['player_color'],
        player_name: (player['player_name'].length > 10? (player['player_name'].substr(0,10) + "...") : player['player_name']),
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

updateGameInfos: function(){
  for(var pId in this.gamedatas.players){
    this.scoreCtrl[pId].toValue( this.gamedatas.players[pId].player_score);
  }

  $('hand-counter').innerHTML = this.gamedatas.handNbr + " / " + this.gamedatas.handTotal;
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
  this.updateGameInfos();

  if(stateName == 'giveCards'){
    if (!this.isCurrentPlayerActive()) return;
    this.addTooltip( 'myhand', _('Cards in my hand'), _('Select a card') );
    dojo.addClass("playertablecard_" + args.args.dealer, "dealer");
  }

  else if(stateName == 'playerTurn'){
    dojo.addClass("playertable-" + args.args.pId, "active");
    if (!this.isCurrentPlayerActive()) return;

    this.addTooltip( 'myhand', _('Cards in my hand'), _('Play a card') );
    dojo.query("#myhand .stockitem").addClass("disabled");
    args.args._private.cards.forEach(card => dojo.removeClass('myhand_item_' + card.id, "disabled"));
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
  dojo.query(".playertable").removeClass("active");
  dojo.query(".playertablecard").removeClass("dealer");
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
  if(typeof item_id == "undefined") return;
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

  else if(state == "playerTurn"){
    this.playerHand.unselectAll();
    if(!this.checkAction('playCard', true))
      return;
    if(dojo.hasClass('myhand_item_' + item_id, 'disabled'))
      return;

    this.takeAction("playCard", { id: item_id })
//            dojo.query('.stockitem').removeClass('receivedCard');
  }
},



onGiveCards: function(){
  if(!this.checkAction('giveCards'))
    return;

  var items = this.playerHand.getSelectedItems();
  // Should be useless now
  if(items.length != this.nbr_cards_to_give){
    this.showMessage(dojo.string.substitute(_("You must select exactly ${n} cards"), {n: this.nbr_cards_to_give}), 'error');
    return;
  }

  this.takeAction("giveCards", {
    cards: items.map(item => item.id).join(';')
  });
},


notif_giveCards: function( notif )
{
  // Remove cards from the hand (they have been given)
  for( var i in notif.args.cards ){
    var card_id = notif.args.cards[i];
    this.playerHand.removeFromStockById( card_id );
  }
},

notif_takeCards: function( notif )
{
  // Cards taken from some opponent
  for( var i in notif.args.cards){
    var card = notif.args.cards[i];
    var color = card.type;
    var value = card.type_arg;
    this.playerHand.addToStockWithId( this.getCardUniqueId( color, value ), card.id );
    dojo.addClass(this.playerHand.getItemDivId(card.id), 'receivedCard');
  }
},


////////////////////////////////
////////////////////////////////
/////////    Utils    //////////
////////////////////////////////
////////////////////////////////

takeAction: function (action, data, callback) {
  data = data || {};
  data.lock = true;
  callback = callback || function (res) { };
  this.ajaxcall("/papayoo/papayoo/" + action + ".html", data, this, callback);
},

// Get card unique identifier based on its color and value
getCardUniqueId: function( color, value )
{
  return (color-1)*10+(value-1);
},


playCardOnTable: function(player_id, color, value, card_id){
  // player_id => direction
  var card_type_id = this.getCardUniqueId(color, value);
  dojo.place(this.format_block( 'jstpl_cardontable', {
    x: this.card_board_width*(card_type_id%10),
    y: this.card_board_height*(Math.floor(card_type_id/10)),
    player_id: player_id
  }), 'playertablecard_'+player_id );

  // Some opponent played a card : move card from player panel
  if( player_id != this.player_id )
    this.placeOnObject( 'cardontable_'+player_id, 'overall_player_board_'+player_id );
  // You played a card. If it exists in your hand, move card from there and remove corresponding item
  else {
    if($('myhand_item_'+card_id)){
      this.placeOnObject( 'cardontable_'+player_id, 'myhand_item_'+card_id );
      this.playerHand.removeFromStockById( card_id );
    }
  }

  // In any case: move it to its final destination
  this.slideToObject( 'cardontable_'+player_id, 'playertablecard_'+player_id ).play();
},


notif_newScores: function(n){
  debug("Notif: updating scores", n);
  n.args.scores.forEach(player => this.gamedatas.players[player.id].player_score = player.score );
  this.updateGameInfos();
},


///////////////////////////////////////////////////
//////   Reaction to cometD notifications   ///////
///////////////////////////////////////////////////

/*
 * setupNotifications:
 *  In this method, you associate each of your game notifications with your local method to handle it.
 *	Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" in the santorini.game.php file.
 */
setupNotifications: function () {
  var notifs = [
    ['newHand', 1],
    ['startingNewHand', 1],
    ['playCard', 1000],
    ['giveAllCardsToPlayer', 1000],
    ['newScores', 1],
    ['giveCards', 1],
    ['takeCards', 1],
    ['throwDice', 3000],
  ];

  var _this = this;
  notifs.forEach(function (notif) {
    dojo.subscribe(notif[0], _this, "notif_" + notif[0]);
    _this.notifqueue.setSynchronous(notif[0], notif[1]);
  });
},




notif_newHand: function(n){
  // We received a new full hand of 13 cards.
  debug("Notif : new hand", n);

  this.playerHand.removeAll();
  n.args.cards.forEach(card => {
    var color = card.type;
    var value = card.type_arg;
    this.playerHand.addToStockWithId( this.getCardUniqueId(color, value), card.id);
  });
},

notif_startingNewHand: function(n){
  dojo.query(".playertable .player_info").forEach(div => div.innerHTML = "0");
  this.gamedatas.handNbr = n.args.handNbr;
  this.updateGameInfos();
},


notif_throwDice: function(n){
  debug("Notif : new value for the dice", n);
  if(n.args.dice_value == 0)
    dojo.attr('dicevalue', 'data-value', '0');
  else {
    dojo.attr('dicevalue', 'data-value', n.args.dice_value + 4);
    dojo.addClass("dicevalue", "roll");
    setTimeout( () => {
      dojo.attr('dicevalue', 'data-value', n.args.dice_value);
      dojo.removeClass("dicevalue", "roll");
    }, 2000);
  }
},


notif_playCard: function( notif ){
  // Play a card on the table
  this.playCardOnTable( notif.args.player_id, notif.args.color, notif.args.value, notif.args.card_id );
},

notif_giveAllCardsToPlayer: function(n){
  debug("Notif: trick is finished", n);
  var winner_id = n.args.player_id;

  // Move all cards on table to given table, then destroy them
  for(var player_id in this.gamedatas.players){
    this.slideToObjectAndDestroy('cardontable_'+player_id, 'player_nbr_trick_'+winner_id );
  }
  $('player_nbr_trick_'+winner_id).innerHTML = n.args.nbr_of_tricks;
  this.displayScoring( 'player_nbr_trick_' + winner_id, this.gamedatas.players[winner_id].color, n.args.score, 1000);
},


  });
});
