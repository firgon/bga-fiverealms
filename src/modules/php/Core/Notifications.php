<?php

namespace FRMS\Core;

use FRMS\Managers\Cards;
use FRMS\Managers\Players;
use FRMS\Helpers\Utils;
use FRMS\Core\Globals;

class Notifications
{
  public static function destroy($player, $card)
  {
    $data = [
      'player' => $player,
      'card' => $card,
      'player2' => $player->getOpponent()
    ];

    $msg = clienttranslate('${player_name} destroyed a ${cardName} card from ${realm} in the council of ${player_name2}');
    static::notifyAll('destroy', $msg, $data);
  }

  public static function newAlkane()
  {
    $data = [
      'alkane' => Cards::getInLocation(ALKANE)->ui(),
      'deck' => Cards::getNextCard(),
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
      'deck' => Cards::getNextCard()
    ];

    $msg = clienttranslate('${player_name} place a ${realm} card in (${x}, ${y})');
    static::notifyAll('placeCard', $msg, $data);
  }

  public static function influence($currentPlayer, $spaceIds, $realm, $influence, $cards)
  {
    $data = [
      'player' => $currentPlayer,
      'spaceIds' => $spaceIds,
      'realm' => $realm,
      'influence' => $influence,
      'nb' => count($spaceIds),
      'cards' => $cards,
    ];
    $msg = clienttranslate('${player_name} add ${nb} card(s) in his ${realm} influence');
    static::notifyAll('influence', $msg, $data);
  }

  public static function influenceWitch($currentPlayer, $realm, $influence, $cards)
  {
    $data = [
      'player' => $currentPlayer,
      'realm' => $realm,
      'influence' => $influence,
      'nb' => count($cards),
      'cards' => $cards,
    ];
    $msg = count($cards) == 1 ? clienttranslate('${player_name} add one card in his ${realm} influence')
      : clienttranslate('${player_name} add ${nb} cards in his ${realm} influence');
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
    $msg = clienttranslate('${player_name} collects ${nb} ${realm} cards and choose to recruit');
    static::notifyAll('recruit', $msg, $data);
  }

  public static function chooseCharacter($currentPlayer, $card, $placeId)
  {
    $data = [
      'player' => $currentPlayer,
      'card' => $card,
      'placeId' => $placeId,
      'titanN' => $currentPlayer->countTitans(),
      'preserve' => ['placeId']
    ];
    $msg = $card->isTitan() ? clienttranslate('${player_name} recruit a ${cardName} card from ${realm}, now he has ${titanN} Titan(s)')
      : clienttranslate('${player_name} recruit a ${cardName} card from ${realm} in his council');
    static::notifyAll('chooseCharacter', $msg, $data);
  }

  public static function passChooseCharacter($player)
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
    $msg = clienttranslate('${player_name} discard a ${cardName} card from ${realm} from his council');
    static::notifyAll('discardCharacter', $msg, $data);
  }

  public static function steal($player, $nb)
  {
    $data = [
      'player' => $player,
      'incScore' => $nb,
      'player2' => $player->getOpponent()
    ];

    $msg = clienttranslate('Thanks to a Warrior card, ${player_name} steal ${incScore} <CASTLE> to ${player_name2}');

    static::notifyAll("steal", $msg, $data);
  }

  public static function getNewCastleCards($score, $card, $player, $silent = false)
  {
    $data = [
      'player' => $player,
      'incScore' => abs($score),
      'card' => $card,
      'deltaScore' => $score,
    ];

    $msg = $score > 0
      ? clienttranslate('Thanks to ${cardName}, ${player_name} receives ${incScore} <CASTLE>')
      : clienttranslate('Due to ${cardName}, ${player_name} looses ${incScore} <CASTLE>');

    static::notifyAll("newCastleCards", $silent ? '' : $msg, $data);
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

      $data['cardName'] = $data['card']->getTranslatableName();
      $data['realm'] = $data['card']->getTranslatableRealm();
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
