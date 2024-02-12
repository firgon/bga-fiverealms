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
 * gameoptions.inc.php
 *
 * FiveRealms game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in fiverealms.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */
require_once 'modules/php/constants.inc.php';

$game_options = [

    OPTION_DEBUG => [
        'name' => totranslate('Cheat Module'),
        'values' => [
            OPTION_DEBUG_ON => [
                'name' => totranslate('With'),
                'description' => totranslate('Only for testing purpose'),
                'tmdisplay' => totranslate('With cheat module'),
                'alpha' => true
            ],
            OPTION_DEBUG_OFF => [
                'name' => totranslate('Without'),
                'description' => totranslate('Without cheat module'),
                'tmdisplay' => totranslate('Without cheat module'),
            ],
        ],
        'default' => OPTION_DEBUG_OFF,
    ],
];


$game_preferences = [];
