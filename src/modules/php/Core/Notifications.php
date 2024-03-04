<?php

namespace FRMS\Core;

use FRMS\Managers\Cards;
use FRMS\Managers\Players;
use FRMS\Helpers\Utils;
use FRMS\Core\Globals;

class Notifications
{

  public static function newAlkane()
  {
    $data = [
      'alkane' => Cards::getInLocation(ALKANE)->ui()
    ];

    static::notifyAll('newAlkane', clienttranslate('The Alkane square is completed'), $data);
  }

  public static function influence($currentPlayer, $spaceIds, $realm)
  {
    $data = [
      'player' => $currentPlayer,
      'spaceIds' => $spaceIds,
      'realm' => $realm,
      'nb' => count($spaceIds)
    ];
    $msg = clienttranslate('${player_name} add ${nb} ${realm} cards to his influence');
    static::notifyAll('influence', $msg, $data);
  }

  public static function getNewCastleCards($score, $card, $player)
  {
    switch ($card->getType()) {
      case KING:
        $name =  clienttranslate('King');
        break;

      case REINE:
        $name =  clienttranslate('Reine');
        break;

      case WITCH:
        $name =  clienttranslate('Witch');
        break;

      case WARRIOR:
        $name =  clienttranslate('Warrior');
        break;

      case TITAN:
        $name =  clienttranslate('Titan');
        break;


      case POPESS:
        $name =  clienttranslate('Popess');
        break;

      case WARRIOR_MONK:
        $name =  clienttranslate('Warrior Monk');
        break;

      case GAIA:
        $name =  clienttranslate('Gaia');
        break;

      case OURANOS:
        $name =  clienttranslate('Ouranos');
        break;


      case COLONEL:
        $name =  clienttranslate('Colonel');
        break;

      case GENERAL:
        $name =  clienttranslate('General');
        break;

      case CAPTAIN:
        $name =  clienttranslate('Captain');
        break;

      case MARSHAL:
        $name =  clienttranslate('Marshal');
        break;
    }
    $data = [
      'name' => $name,
      'player' => $player,
      'incScore' => $score,
      'card' => $card,
      'i18n' => ['name']
    ];

    $msg = $score > 0
      ? clienttranslate('Thanks to ${name}, ${player_name} receives ${incScore} new castle cards')
      : clienttranslate('Thanks to ${name}, ${player_name} looses ${incScore} new castle cards');

    static::notifyAll("newCastleCards", $msg, $data);
  }

  public static function refreshUI($data)
  {
    $currentPlayer = Players::getCurrent();
    $data['player'] = $currentPlayer;
    $msg = clienttranslate('${player_name} cancels his actions');
    static::notifyAll('refreshUi', $msg, $data);
  }

  /*************************
   **** GENERIC METHODS ****
   *************************/
  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data);
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('message', $txt, $args);
  }

  public static function messageTo($player, $txt, $args = [])
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::notify($pId, 'message', $txt, $args);
  }

  /*********************
   **** UPDATE ARGS ****
   *********************/

  private static function addDataCoord(&$data, $x, $y)
  {
    $data['x'] = $x;
    $data['y'] = $y;
    $data['displayX'] = $x + 1;
    $data['displayY'] = $y + 1;
  }

  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data)
  {
    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      unset($data['player']);
    }

    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }

    if (isset($data['card'])) {
      $data['value'] = $data['card']->getValue();
      $data['color'] = $data['card']->getColor();
      $data['cardId'] = $data['card']->getId();
      unset($data['card']);
      if (isset($data['card2'])) {
        $data['value2'] = $data['card2']->getValue();
        $data['color2'] = $data['card2']->getColor();
        $data['cardId2'] = $data['card2']->getId();
        unset($data['card2']);
        if (isset($data['card3'])) {
          $data['value3'] = $data['card3']->getValue();
          $data['color3'] = $data['card3']->getColor();
          $data['cardId3'] = $data['card3']->getId();
          unset($data['card3']);
        }
      }
    }
  }

  //          █████                          █████     ███                     
  //         ░░███                          ░░███     ░░░                      
  //  ██████  ░███████    ██████   ██████   ███████   ████  ████████    ███████
  // ███░░███ ░███░░███  ███░░███ ░░░░░███ ░░░███░   ░░███ ░░███░░███  ███░░███
  //░███ ░░░  ░███ ░███ ░███████   ███████   ░███     ░███  ░███ ░███ ░███ ░███
  //░███  ███ ░███ ░███ ░███░░░   ███░░███   ░███ ███ ░███  ░███ ░███ ░███ ░███
  //░░██████  ████ █████░░██████ ░░████████  ░░█████  █████ ████ █████░░███████
  // ░░░░░░  ░░░░ ░░░░░  ░░░░░░   ░░░░░░░░    ░░░░░  ░░░░░ ░░░░ ░░░░░  ░░░░░███
  //                                                                   ███ ░███
  //                                                                  ░░██████ 
  //                                                                   ░░░░░░  

  public static function cheat()
  {
    static::notifyAll('refresh', "", []);
  }

  public static function invitePlayersToAlpha($name, $message, $data)
  {
    static::notify(Players::getCurrent(), $name, $message, $data);
  }
}
