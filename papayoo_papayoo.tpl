{OVERALL_GAME_HEADER}

<div id="gamezone"></div>

<div id="myhand_wrap" class="whiteblock">
    <h3>{MY_HAND}</h3>
    <div id="myhand"></div>
</div>
<script type="text/javascript">
var jstpl_playertables = '<div id="playertables" style="width: ${w}px; height: ${h}px;"></div>';

var jstpl_playertable = `
<div id="playertable-\${player_id}" class="playertable" style="width:\${w}px; height:\${h}px; left: \${x}px; bottom: \${y}px;">
  <div class="playertablename" style="color:#\${player_color}">
    \${player_name}
  </div>
  <div class="player_info" id="player_nbr_trick_\${player_id}">\${nbr_of_tricks}</div>
  <div class="playertablecard" id="playertablecard_\${player_id}"></div>
</div>`;

var jstpl_cardontable = '<div class="cardontable" id="cardontable_${player_id}" style="background-position:-${x}px -${y}px"></div>';


var jstpl_dicevalue = `<div id="dicevalue" class="whiteblock" data-value="\${c}">
  <div class="camera">
    <div class="dice">
      <div class="face1"></div>
      <div class="face2"></div>
      <div class="face3"></div>
      <div class="face4"></div>
      <div class="face5"></div>
      <div class="face6"></div>
      <div class="face7"></div>
      <div class="face8"></div>
    </div>
  </div>
</div>`;
</script>

{OVERALL_GAME_FOOTER}
