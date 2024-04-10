<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * FiveRealms implementation : © Emmanuel Albisser <emmanuel.albisser@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * FiveRealms game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

require_once 'modules/php/constants.inc.php';

$machinestates = [

    // The initial state. Please do not modify.
    ST_GAME_SETUP => [
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => [
            "" => ST_PLAY,
        ]
    ],

    ST_PLAY => [
        "name" => "play",
        "description" => clienttranslate('${actplayer} must place the first card from Banner deck in the Alkane square'),
        "descriptionmyturn" => clienttranslate('${you} must place the first card from Banner deck in the Alkane square'),
        "type" => ACTIVE_PLAYER,
        "args" => "argPlay",
        // "action" => "stPlay",
        "possibleactions" => ['actRecruit', 'actInfluence'],
        "transitions" => [
            END_TURN => ST_NEXT_PLAYER,
        ]
    ],

    ST_NEXT_PLAYER => [
        'name' => 'nextPlayer',
        'type' => GAME,
        'action' => 'stNextPlayer',
        'transitions' => [
            END_TURN => ST_PLAY,
            END_GAME => ST_PRE_END_OF_GAME
        ],
    ],

    ST_RECRUIT => [
        "name" => "recruit",
        "description" => clienttranslate('${actplayer} may choose one character to place in this council'),
        "descriptionmyturn" => clienttranslate('${you} may choose one character to place in this council'),
        "type" => ACTIVE_PLAYER,
        "args" => "argRecruit",
        // "action" => "stPlay",
        "possibleactions" => ['actChooseCharacter', 'actDiscard', 'actPassChooseCharacter'],
        "transitions" => [
            END_TURN => ST_NEXT_PLAYER,
        ]
    ],

    ST_STEAL_OR_DESTROY => [
        "name" => "steal",
        "description" => clienttranslate('${actplayer} can steal a Castle Card or destroy one of your titans or characters'),
        "descriptionmyturn" => clienttranslate('${you} can steal a Castle Card or destroy one of your titans or characters'),
        "descriptionimpossible" => clienttranslate('${actplayer} can\'t steal a Castle Card or destroy one of your titans or characters'),
        "descriptionmyturnimpossible" => clienttranslate('${you} cann\'t steal a Castle Card or destroy one of your titans or characters'),
        "type" => ACTIVE_PLAYER,
        "args" => "argSteal",
        // "action" => "stPlay",
        "possibleactions" => ['actSteal', 'actDestroy', 'actPass'],
        "transitions" => [
            END_TURN => ST_NEXT_PLAYER,
        ]
    ],

    ST_WITCH => [
        "name" => "witch",
        "description" => clienttranslate('${actplayer} can recruit or influence a card from discard'),
        "descriptionmyturn" => clienttranslate('${you} can recruit or influence a card from discard'),
        "type" => ACTIVE_PLAYER,
        "args" => "argWitch",
        // "action" => "stPlay",
        "possibleactions" => ['actPass', 'actChooseCharacter', 'actInfluenceWitch'],
        "transitions" => [
            END_TURN => ST_NEXT_PLAYER,
        ]
    ],

    ST_PRE_END_OF_GAME => [
        'name' => 'preEndOfGame',
        'type' => GAME,
        'action' => 'stPreEndOfGame',
        'transitions' => ['' => ST_END_GAME],
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    ST_END_GAME => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"

    ],

    ST_GENERIC_NEXT_PLAYER => [
        'name' => 'genericNextPlayer',
        'type' => 'game',
    ],
];
