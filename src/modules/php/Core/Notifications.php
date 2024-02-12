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

  public static function payCards($player, $card1, $card2)
  {
    $data = [
      'player' => $player,
      'card' => $card1,
      'card2' => $card2
    ];
    $msg = clienttranslate('${player_name} discard a ${value} and a ${value2} ${color} cards');
    static::notifyAll(PAY_WITH_CARDS, $msg, $data);
  }

  public static function discard($player, $card1, $card2, $card3)
  {
    $data = [
      'player' => $player,
      'card' => $card1,
      'card2' => $card2,
      'card3' => $card3
    ];
    $msg = clienttranslate('${player_name} discard entire ${color} family');
    static::notifyAll(DISCARD, $msg, $data);
  }

  public static function cardInTwice($givenCard, $caller, $calledPlayer)
  {
    $data = [
      'givenCard' => $givenCard,
      'player2' => $calledPlayer,
      'player' => $caller,
    ];
    $msg = clienttranslate('${player_name} can\'t have a same card twice, must discard it and receives a gold nugget as compensation');
    static::notifyAll("cardInTwice", $msg, $data);
  }

  public static function call($player, $calledPlayer, $color, $value)
  {
    $data = [
      'player' => $player,
      'player2' => $calledPlayer,
      'color' => $color,
      'value' => $value
    ];
    $msg = clienttranslate('${player_name} calls a ${value} ${color} card from ${player_name2}');
    static::message($msg, $data);
  }

  public static function giveMatchingCard($calledPlayer, $caller, $matching)
  {
    $data = [
      'player' => $calledPlayer,
      'player2' => $caller,
      'card' => $matching
    ];
    $msg = clienttranslate('${player_name} has the requested card and gives it to ${player_name2}');
    static::notifyAll('giveCard', $msg, $data);
  }

  public static function refreshUI($data)
  {
    $currentPlayer = Players::getCurrent();
    $data['player'] = $currentPlayer;
    $msg = clienttranslate('${player_name} cancels his actions');
    static::notifyAll('refreshUi', $msg, $data);
  }

  public static function noMatchingCard($calledPlayer, $caller)
  {
    $msg = clienttranslate('${player_name} has no corresponding cards, ${player_name2} picks at random');
    $data = [
      'player' => $calledPlayer,
      'player2' => $caller,
    ];
    static::message($msg, $data);
  }

  public static function giveRandomCard($calledPlayer, $caller, $card, $isInTwice)
  {
    $data = [
      'player' => $calledPlayer,
      'player2' => $caller,
      'card' => $card,
    ];
    $msg = clienttranslate('It\'s a ${value} ${color} !');
    if ($isInTwice) static::message($msg, $data); //because the move will be sent later
    else static::notifyAll('giveCard', $msg, $data);
  }

  public static function giveCard($calledPlayer, $caller, $card, $isInTwice)
  {
    $data = [
      'player' => $calledPlayer,
      'player2' => $caller,
      'card' => $card,
    ];
    $msg = clienttranslate('${player_name} gives a ${value} ${color} card to ${player_name2}');
    if ($isInTwice) static::message($msg, $data); //because the move will be sent later
    else static::notifyAll('giveCard', $msg, $data);
  }

  public static function tickFlag($player, $type, $cell)
  {
    $data = [
      'player' => $player,
      'ticked' => $cell,
      'type' => $type
    ];
    static::notifyAll(TICK, clienttranslate('${player_name} ticks a flag and discovers a new region'), $data);
  }

  public static function tickSaloon($player, $type, $cell)
  {
    $data = [
      'player' => $player,
      'ticked' => $cell,
      'type' => $type
    ];
    static::notifyAll(TICK, clienttranslate('${player_name} ticks a new saloon'), $data);
  }

  public static function tickWanted($player, $type, $cell)
  {
    $data = [
      'player' => $player,
      'ticked' => $cell,
      'type' => $type
    ];
    static::notifyAll(TICK, clienttranslate('${player_name} ticks a new wanted icon'), $data);
  }

  public static function tickLeg($player, $action, $cell)
  {
    $translatable = [
      NUGGETS => 'gets an extra nuggets action',
      CARDS => 'gets an extra cards action',
      FREE_CARD => 'can play an extra card',
      DISCOVERY => 'must tick a new flag',
      TIPI => 'must take a tipi bonus',
      NOTHING => 'ends their turn',
      REWARD => 'claims a reward'
    ];
    $data = [
      'player' => $player,
      'ticked' => $cell,
      'action' => $translatable[$action],
      'i18n' => ['action']
    ];
    static::notifyAll(TICK, clienttranslate('${player_name} ticks a new leg and ${action}'), $data);
  }

  public static function mainTick($currentPlayer, $cell, $score)
  {
    $data = [
      'player' => $currentPlayer,
      'ticked' => $cell,
      'score1' => $score,
    ];
    static::notifyAll(TICK, '', $data);
  }

  // //reward by completing a region
  // public static function reward($player, $reward, $zone)
  // {
  //   $message = clienttranslate('By completing a new ${zone}, ${player_name} gets ${reward} points');

  //   $translatable = [
  //     RED_COUNTRY => "red region",
  //     BLUE_COUNTRY => "blue region",
  //     YELLOW_COUNTRY => "yellow region",
  //     GREEN_COUNTRY => "green region",
  //   ];

  //   $data = [
  //     'player' => $player,
  //     'reward' => $reward,
  //     'zone' => $translatable[$zone],
  //     'i18n' => ['zone']
  //   ];
  //   static::notifyAll(REWARD, $message, $data);
  // }

  //reward for completing a scoring zone in main board
  public static function addReward($currentPlayer, $reward, $cell, $type)
  {
    //$type is defined if it's a player sheet reward
    if ($type) {
      $msg = clienttranslate('By completing a ${zone}, ${player_name} receives a new reward and gets ${reward} points');

      $translatable = [
        RED_COUNTRY => "red region",
        BLUE_COUNTRY => "blue region",
        YELLOW_COUNTRY => "yellow region",
        GREEN_COUNTRY => "green region",
        RAILROAD => "rail road"
      ];

      $data = [
        'player' => $currentPlayer,
        'ticked' => $cell,
        'reward' => $reward,
        'rewardId' => count($currentPlayer->getRewards()),
        'zone' => $translatable[$type],
        'i18n' => ['zone']
      ];
    } else {
      $data = [
        'player' => $currentPlayer,
        'ticked' => $cell,
        'reward' => $reward,
        'rewardId' => count($currentPlayer->getRewards()),
      ];
      $msg = clienttranslate('${player_name} receives a new reward and gets ${reward} points');
    }

    static::notifyAll(REWARD, $msg, $data);
  }

  public static function completeHand($player, $cards)
  {
    $message = clienttranslate('${player_name} completes their hand to 3 cards');
    $data = [
      'player' => $player,
      'card_nb' => count($cards), //hidden
      'deck' => Cards::countInLocation('deck'),
      'discard' => Cards::countInLocation('discard')
    ];
    self::notifyAll("completeOtherHand", $message, $data);
    $data = [
      'player' => $player,
      'cardIds' => $cards->getIds(),
      'deck' => Cards::countInLocation('deck'),
      'discard' => Cards::countInLocation('discard')
    ];
    self::notify($player, "completeHand", $message, $data);
  }

  public static function takeCards($player, $cards)
  {
    $message = clienttranslate('${player_name} takes 3 cards');
    $data = [
      'player' => $player,
      'card_nb' => 3, //hidden
      'deck' => Cards::countInLocation('deck'),
      'discard' => Cards::countInLocation('discard')
    ];
    self::notifyAll("completeOtherHand", $message, $data);
    $data = [
      'player' => $player,
      'cardIds' => $cards->getIds(),
      'deck' => Cards::countInLocation('deck'),
      'discard' => Cards::countInLocation('discard')
    ];
    self::notify($player, "completeHand", $message, $data);
  }

  public static function takeNugget($player)
  {
    $message = clienttranslate('${player_name} takes a nugget');
    $data = [
      'player' => $player,
    ];
    self::notifyAll("takeNugget", $message, $data);
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
