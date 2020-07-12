
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- papayoo implementation : © Guillaume NAVEL <guillaume.navel@gmail.com>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

ALTER TABLE `player` ADD `nbr_of_tricks_win` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `hand_points` int(11) NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
