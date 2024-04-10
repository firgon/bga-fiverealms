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
 * fiverealms.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */
$swdNamespaceAutoload = function ($class) {
    $classParts = explode('\\', $class);
    if ($classParts[0] == 'FRMS') {
        array_shift($classParts);
        $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            var_dump('Cannot find file : ' . $file);
        }
    }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');

require_once 'modules/php/constants.inc.php';

use FRMS\Managers\Players;
use FRMS\Managers\Cards;
use FRMS\Core\Globals;
use FRMS\Core\Preferences;
use FRMS\Core\Stats;
use FRMS\Core\CheatModule;
use FRMS\Core\Game;

// use FRMS\Helpers\Log;

class FiveRealms extends Table
{
    use FRMS\DebugTrait;
    use FRMS\States\TurnTrait;
    use FRMS\States\RecruitTrait;
    use FRMS\States\StealTrait;
    use FRMS\States\WitchTrait;

    public static $instance = null;
    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        self::$instance = $this;
        self::initGameStateLabels([
            'logging' => 10,
        ]);
        Stats::checkExistence();
        $this->bIndependantMultiactiveTable = true;
    }

    public static function get()
    {
        return self::$instance;
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "fiverealms";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        Players::setupNewGame($players, $options);
        Preferences::setupNewGame($players, $this->player_preferences);
        //Stats::checkExistence();
        Cards::setupNewGame($players, $options);

        $this->setGameStateInitialValue('logging', false);
        $this->activeNextPlayer();
        $this->giveExtraTime(Players::getActiveId());

        Globals::setupNewGame($players, $options, Players::getActiveId());
        // Log::enable();
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    public function getAllDatas()
    {
        $pId = self::getCurrentPId();
        return [
            'prefs' => Preferences::getUiData($pId),
            'players' => Players::getUiData($pId),
            'cards' => Cards::getUiData(),
            'cheatModule' => Globals::isCheatModule() ? CheatModule::getUIData() : []
        ];
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        return 0;
    }

    function actChangePreference($pref, $value)
    {
        Preferences::set($this->getCurrentPId(), $pref, $value);
    }
    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    /////////////////////////////////////
    //////////// Prevent deadlock ///////
    /////////////////////////////////////

    // Due to deadlock issues involving the playersmultiactive and player tables,
    //   standard tables are queried FOR UPDATE when any operation occurs -- AJAX or refreshing a game table.
    //
    // Otherwise at least two situations have been observed to cause deadlocks:
    //   * Multiple players in a live game with tabs open, two players trading multiactive state back and forth.
    //   * Two players trading multiactive state back and forth, another player refreshes their game page.
    // function queryStandardTables()
    // {
    //     // Query the standard global table.
    //     self::DbQuery('SELECT global_id, global_value FROM global WHERE 1 ORDER BY global_id FOR UPDATE');
    //     // Query the standard player table.
    //     self::DbQuery('SELECT player_id id, player_score score FROM player WHERE 1 ORDER BY player_id FOR UPDATE');
    //     // Query the playermultiactive  table. DO NOT USE THIS is you don't use $this->bIndependantMultiactiveTable=true
    //     // self::DbQuery(
    //     //     'SELECT ma_player_id player_id, ma_is_multiactive player_is_multiactive FROM playermultiactive ORDER BY player_id FOR UPDATE'
    //     // );

    //     // TODO should the stats table be queried as well?
    // }

    /** This is special function called by framework BEFORE any of your action functions */
    // protected function initTable()
    // {
    //     $this->queryStandardTables();
    // }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {

                case 'call':
                    $args = $this->argCall();
                    $callablePlayers = $args['callablePlayers'];
                    $pId2 = $callablePlayers[bga_rand(0, count($callablePlayers) - 1)]->getId();
                    $cardId = 0;
                    $color = [BLUE, YELLOW, RED, PURPLE, GREEN, GREY];
                    $choosenColor = null;
                    $choosenValue = null;
                    while (true) {
                        $choosenColor = $color[bga_rand(0, count($color) - 1)];
                        $choosenValue = bga_rand(1, 3);
                        foreach ($args['uncallableCards'] as $id => $card) {
                            if ($card->getColor() == $choosenColor && $card->getValue() == $choosenValue)
                                continue;
                        }
                        break;
                    }
                    $this->actCall($pId2, $choosenColor, $choosenValue, Players::getActiveId());
                    break;
                case 'giveCard':
                    $possibleCards = $this->argGiveCard()['_private']['active']['substitutes'];
                    $card = $possibleCards[bga_rand(0, count($possibleCards) - 1)];
                    $this->actGiveCard($card->getId(), Players::getActiveId());
                    break;


                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
     * upgradeTableDb
     *  - int $from_version : current version of this game database, in numerical form.
     *      For example, if the game was running with a release of your game named "140430-1345", $from_version is equal to 1404301345
     */
    public function upgradeTableDb($from_version)
    {
    }

    /////////////////////////////////////////////////////////////
    // Exposing protected methods, please use at your own risk //
    /////////////////////////////////////////////////////////////

    // Exposing protected method getCurrentPlayerId
    public function getCurrentPId()
    {
        return self::getCurrentPlayerId();
    }

    // Exposing protected method translation
    public function translate($text)
    {
        return self::_($text);
    }

    // Shorthand
    public function getArgs()
    {
        return $this->gamestate->state()['args'];
    }
}
