<?php

namespace FRMS\Models;

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
    // $isCurrent = $this->id == $currentPlayerId;
    // $data['hand'] = $this->getCardsInHand($isCurrent);
    // $data['table'] = $this->getCardsOnTable();
    // $data['score1'] = Cells::getScore1($this->id);
    // $data['rewards'] = $this->getRewards();
    // $data['score2'] = array_sum($data['rewards']);
    // $data['score3'] = $this->getScore();

    return $data;
  }

  public function getCardsInHand($isCurrent = true)
  {
    return ($isCurrent) ? Cards::getInLocation('hand', $this->id) : Cards::countInLocation('hand', $this->id);
  }

  public function getCardsOnTable()
  {
    return Cards::getInLocation('table', $this->id);
  }

  public function canUseThrone()
  {
    return $this->getThronePlayed() == 1;
  }

  public function useThrone()
  {
    return $this->setThronePlayed(1);
  }

  public function getPlayableCardsIds($costMax = 3)
  {
    $result = [];
    $cards = $this->getCardsInHand();
    $unplayableCards = $this->getCardsOnTable();

    foreach ($cards as $id => $card) {
      if ($card->getValue() > $costMax) continue;
      $playable = true;
      foreach ($unplayableCards as $id => $unplayableCard) {
        if (
          $card->getValue() == $unplayableCard->getValue() &&
          $card->getColor() == $unplayableCard->getColor()
        ) {
          $playable = false;
          break;
        }
      }
      if ($playable) {
        $result[] = $card->getId();
      }
    }

    return $result;
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
    // $player = is_numeric($player) ? Players::get($player) : $player;
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
