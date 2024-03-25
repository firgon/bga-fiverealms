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

  public static function adjustAlkane()
  {
    $data = [
      'alkane' => Cards::getInLocation(ALKANE)->ui()
    ];

    static::notifyAll('adjustAlkane', '', $data);
  }

  public static function placeCard($currentPlayer, $card, $x, $y)
  {
    $data = [
      'player' => $currentPlayer,
      'x' => $x,
      'y' => $y,
      'card' => $card,
      'realm' => $card->getRealm(),
    ];

    $msg = clienttranslate('${player_name} place a ${realm} in (${x}, ${y})');
    static::notifyAll('placeCard', $msg, $data);
  }

  public static function influence($currentPlayer, $spaceIds, $realm, $influence)
  {
    $data = [
      'player' => $currentPlayer,
      'spaceIds' => $spaceIds,
      'realm' => $realm,
      'influence' => $influence,
      'nb' => count($spaceIds)
    ];
    $msg = clienttranslate('${player_name} add ${nb} ${realm} to his influence');
    static::notifyAll('influence', $msg, $data);
  }

  public static function recruit($currentPlayer, $spaceIds, $realm, $cards)
  {
    $data = [
      'player' => $currentPlayer,
      'spaceIds' => $spaceIds,
      'realm' => $realm,
      'nb' => count($spaceIds),
      'cards' => $cards,
    ];
    $msg = clienttranslate('${player_name} collects ${nb} ${realm} and choose to recruit');
    static::notifyAll('recruit', $msg, $data);
  }

  public static function chooseCharacter($currentPlayer, $card, $placeId)
  {
    $data = [
      'player' => $currentPlayer,
      'card' => $card,
      'placeId' => $placeId,
      'titanN' => $currentPlayer->countTitans()
    ];
    $msg = $card->isTitan() ? clienttranslate('${player_name} recruit a ${cardName} card, now he has ${titanN} Titans')
      : clienttranslate('${player_name} recruit a ${cardName} card in his council');
    static::notifyAll('chooseCharacter', $msg, $data);
  }

  public function passChooseCharacter($player)
  {
    $data = [
      'player' => $player,
    ];
    $msg = clienttranslate('${player_name} doesn\'t recruit any card');
    static::notifyAll('passChooseCharacter', $msg, $data);
  }

  public static function discard($currentPlayer, $card)
  {
    $data = [
      'player' => $currentPlayer,
      'card' => $card,
    ];
    $msg = clienttranslate('${player_name} discard a ${cardName} card from his council');
    static::notifyAll('discardCharacter', $msg, $data);
  }

  public static function getNewCastleCards($score, $card, $player)
  {
    $data = [
      'player' => $player,
      'incScore' => $score,
      'card' => $card,
    ];

    $msg = $score > 0
      ? clienttranslate('Thanks to ${cardName} card, ${player_name} receives ${incScore} new castle cards')
      : clienttranslate('Thanks to ${cardName} card, ${player_name} looses ${incScore} new castle cards');

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
      switch ($data['card']->getType()) {
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
      $data['cardName'] = $name;
      $data['i18n'][] = 'cardName';
      $data['card'] = $data['card']->jsonSerialize();
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
}
