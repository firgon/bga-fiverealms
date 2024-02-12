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
 * fiverealms.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in fiverealms_fiverealms.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");
require_once('modules/php/constants.inc.php');

class view_fiverealms_fiverealms extends game_view
{
    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "fiverealms";
    }

    function build_page($viewArgs)
    {
        // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count($players);

        /*********** Place your code below:  ************/
        global $g_user;
        $playerId = $g_user->get_id();
        if (!array_key_exists($playerId, $players)) {
            $playerId = array_values($players)[0]['player_id'];
        }
        $this->tpl['MY_ID'] = $playerId;
        $this->tpl['TYPE'] = $players[$playerId]['player_no'];
        $this->tpl['MY_FRMSOR'] = $players[$playerId]['player_color'];
        $this->tpl['MY_NAME'] = $players[$playerId]['player_name'];

        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */


        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 


        $this->page->begin_block("fiverealms_fiverealms", "playerBlock");
        foreach ($players as $player) {
            if ($player['player_id'] == $playerId) continue;
            $this->page->insert_block("playerBlock", array(
                "PLAYER_ID" => $player['player_id'],
                "TYPE" => $player['player_no'],
                "PLAYER_FRMSOR" => $player['player_color'],
                "PLAYER_NAME" => $player['player_name']
            ));
        }




        /*********** Do not change anything below this line  ************/
    }
}
