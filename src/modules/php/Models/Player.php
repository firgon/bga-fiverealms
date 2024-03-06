<?php

namespace FRMS\Models;

use FRMS\Core\Notifications;
use FRMS\Core\Stats;
use FRMS\Core\Preferences;
use FRMS\Managers\Players;
use FRMS\Managers\Cards;
use FRMS\Managers\Cells;

/*
 * Player: all utility functions concerning a player
 */

class Player extends \FRMS\Helpers\DB_Model
{
  private $map = null;
  protected $table = 'player';
  protected $primary = 'player_id';
  protected $attributes = [
    'id' => ['player_id', 'int'],
    'no' => ['player_no', 'int'],
    'name' => 'player_name',
    'color' => 'player_color',
    'eliminated' => 'player_eliminated',
    'score' => ['player_score', 'int'],
    'scoreAux' => ['player_score_aux', 'int'],
    'canUndo' => ['player_can_undo', 'int'],
    'pendingActions' => ['player_pending_actions', 'obj'],
    'thronePlayed' => 'bool'
  ];

  public function getUiData($currentPlayerId = null)
  {
    $data = parent::getUiData();
    foreach (NORMAL_BANNERS as $banner) {
      $data[INFLUENCE][$banner] = $this->countSpecificBanner($banner);
    }
    // $isCurrent = $this->id == $currentPlayerId;

    return $data;
  }

  public function getCardsInHand($isCurrent = true)
  {
    return ($isCurrent) ? Cards::getInLocation('hand', $this->id) : Cards::countInLocation('hand', $this->id);
  }

  public function canUseThrone()
  {
    return $this->getThronePlayed() == 1;
  }

  public function useThrone()
  {
    return $this->setThronePlayed(1);
  }

  public function addCardInInfluence($card, $realm)
  {
    $card->setPlayerId($this->getId());
    $card->setLocation($realm);
    $card->setX(-10);
    $card->setY(-10);
  }


  public function countSpecificBanner($banner)
  {
    return Cards::getInLocationPId($banner, $this->getId())->count();
  }


  public function countWarrior()
  {
    return Cards::getInLocationQ(COUNCIL)
      ->where('player_id', $this->getId())
      ->get()->filter(fn ($card) => $card->isWarrior())->count();
  }


  public function countTitans()
  {
    return Cards::getInLocationQ(TITANS)
      ->where('player_id', $this->getId())
      ->get()->count();
  }

  public function countLines()
  {
    return max(array_map(fn ($realm) => $this->countSpecificBanner($realm), NORMAL_BANNERS));
  }

  public function activateCouncil($influence, $bEndOfGame = false)
  {
    $cards = Cards::getInLocationPId(COUNCIL, $this->getId());
    $witch = null;

    foreach ($cards as $cardId => $card) {
      //play witch at the end
      if ($card->isWitch()) {
        $witch = $card;
        continue;
      }
      if ($bEndOfGame) {
        $card->endEffect();
      } else {
        $card->anytimeEffect($influence);
      }
    }
    if (!is_null($witch)) {
      $witch->anytimeEffect($influence);
    }

    $throne = Cards::getInLocationPId(THRONE, $this->getId())->first();
    $throne->anytimeEffect($influence);
  }

  public function increaseScore($score, $card)
  {
    $this->incScore($score);
    Notifications::getNewCastleCards($score, $card, $this);
  }

  public function getOpponent()
  {
    foreach (Players::getAll() as $pId => $player) {
      if ($pId != $this->getId()) {
        return $player;
      }
    }
  }

  /*
     █████████                                          ███                  
    ███░░░░░███                                        ░░░                   
   ███     ░░░   ██████  ████████    ██████  ████████  ████   ██████   █████ 
  ░███          ███░░███░░███░░███  ███░░███░░███░░███░░███  ███░░███ ███░░  
  ░███    █████░███████  ░███ ░███ ░███████  ░███ ░░░  ░███ ░███ ░░░ ░░█████ 
  ░░███  ░░███ ░███░░░   ░███ ░███ ░███░░░   ░███      ░███ ░███  ███ ░░░░███
   ░░█████████ ░░██████  ████ █████░░██████  █████     █████░░██████  ██████ 
    ░░░░░░░░░   ░░░░░░  ░░░░ ░░░░░  ░░░░░░  ░░░░░     ░░░░░  ░░░░░░  ░░░░░░  
                                                                             
                                                                             
                                                                             
  */

  public function addActionToPendingAction($action, $bFirst = false)
  {
    $pendingActions = $this->getPendingActions();

    if ($bFirst) {
      array_unshift($pendingActions, $action);
    } else {
      array_push($pendingActions, $action);
    }

    $this->setPendingActions($pendingActions);
  }

  public function getNextPendingAction($bFirst = true, $bDestructive = true)
  {
    $pendingActions = $this->getPendingActions();

    if ($bFirst) {
      $action = array_shift($pendingActions);
    } else {
      $action = array_pop($pendingActions);
    }

    if ($bDestructive) {
      $this->setPendingActions($pendingActions);
    }
    return $action;
  }

  public function removeNextPendingAction()
  {
    $this->getNextPendingAction(true, true);
  }

  public function getPref($prefId)
  {
    return Preferences::get($this->id, $prefId);
  }

  public function getStat($name)
  {
    $name = 'get' . \ucfirst($name);
    return Stats::$name($this->id);
  }
}
