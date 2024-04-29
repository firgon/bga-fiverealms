<?php

namespace FRMS\Models;

use Exception;
use FRMS\Core\Notifications;
use FRMS\Managers\Cards;
use FRMS\Managers\Players;

/*
 * Card
 */

class Card extends \FRMS\Helpers\DB_Model
{
    protected $table = 'cards';
    protected $primary = 'card_id';
    protected $attributes = [
        'id' => ['card_id', 'int'],
        'location' => 'card_location',
        'state' => ['card_state', 'int'],
        'influenceColumn' => 'influence_column',
        'extraDatas' => ['extra_datas', 'obj'],
        'flipped' => ['flipped', 'int'],
        'x' => ['x', 'int'],
        'y' => ['y', 'int'],
        'type' => 'type',
        'realm' => 'realm',
        'playerId' => ['player_id', 'int']
    ];

    public $bindedAttributes = [
        'type' => ''
    ];


    protected $staticAttributes = [];

    public function placeOnAlkane($currentPlayer, $coordOrSpaceId)
    {
        $coord = (is_array($coordOrSpaceId)) ? $coordOrSpaceId : Cards::getCoord($coordOrSpaceId);
        $this->setLocation(ALKANE);
        $this->setCoord($coord);

        Notifications::placeCard($currentPlayer, $this, $coord[0], $coord[1]);
    }

    public function setCoord($coord)
    {
        $this->setX($coord[0]);
        $this->setY($coord[1]);
    }

    public function getSpaceId()
    {
        return Cards::getSpaceId($this->getX(), $this->getY());
    }

    public function anytimeEffect($influence)
    {
        return 0;
    }

    protected function getPlayer(): Player
    {
        return Players::get($this->getPlayerId());
    }

    protected function countNewLines($influence)
    {

        $range = [];
        foreach ($influence as $realm => $data) {
            $tmpRange = $this->getRangePlayedCards(count($data), $realm);
            foreach ($tmpRange as $rank) {
                $range[$rank] = true;
            }
        }

        $nthOfCards = array_keys($range);

        $linesNb = $this->getPlayer()->countLines();

        //determine how many new lines have been created
        return count(array_filter($nthOfCards, fn ($nth) => $nth <= $linesNb));
    }

    protected function getRangePlayedCards($nbPlayedCards, $realm = null)
    {
        $influenceInRealm = Players::get($this->getPlayerId())->countSpecificBanner($realm ?? $this->getRealm());

        //determine which card have been added in the matching realm
        return range($influenceInRealm - $nbPlayedCards + 1, $influenceInRealm);
    }

    protected function getRewards($influence, $thresold, $reward)
    {
        //test if $thresold is an array if not it's a function
        //test if $reward is a number if not it's a function
        if (!array_key_exists($this->getRealm(), $influence)) return;

        $nthOfCards = $this->getRangePlayedCards(count($influence[$this->getRealm()]));

        if (is_array($thresold)) {
            $intersect = array_intersect($nthOfCards, $thresold);
            $count = count($intersect);
        } else {
            $count = $this->$thresold($nthOfCards);
        }

        if ($count == 0) return;

        if (is_string($reward)) {
            for ($i = 0; $i < $count; $i++) {
                $this->$reward();
            }
        } else {
            $this->getPlayer()->increaseScore($count * $reward, $this);
        }
    }

    protected function majorityOfThisRealm()
    {
        $player = $this->getPlayer();
        $opponent = $player->getOpponent();
        if ($player->countSpecificBanner($this->getRealm()) > $opponent->countSpecificBanner($this->getRealm())) {
            $player->increaseScore(3, $this);
        }
    }

    protected function majorityOfThisRealmInCouncil()
    {
        $player = $this->getPlayer();
        $opponent = $player->getOpponent();
        if ($player->countInCouncil($this->getRealm()) > $opponent->countInCouncil($this->getRealm())) {
            $player->increaseScore(3, $this);
        }
    }

    protected function majorityOfWarriors()
    {
        $player = $this->getPlayer();
        $opponent = $player->getOpponent();
        if ($player->countWarriors() > $opponent->countWarriors()) {
            $loss = min(2, $opponent->getScore());
            $player->increaseScore($loss, $this);
            $opponent->increaseScore(-$loss, $this);
        }
    }
    protected function majorityOfTitans()
    {
        $player = $this->getPlayer();
        $opponent = $player->getOpponent();
        if ($player->countTitans() > $opponent->countTitans()) {
            $player->increaseScore(1, $this);
        }
    }
    protected function majorityOfLines()
    {
        $player = $this->getPlayer();
        if ($player->countLines() > $player->getOpponent()->countLines()) {
            $player->increaseScore(2, $this);
        };
    }

    protected function countMajorities()
    {
        $player = $this->getPlayer();
        $opponent = $player->getOpponent();
        $majorities = 0;

        foreach (NORMAL_BANNERS as $realm) {
            if ($player->countSpecificBanner($realm) > $opponent->countSpecificBanner($realm)) {
                $majorities++;
            }
        }
        if ($majorities) $player->increaseScore($majorities, $this);
    }

    public function stealOrDestroy()
    {
        $this->getPlayer()->addActionToPendingAction(ST_STEAL_OR_DESTROY);
    }

    public function steal()
    {
        $player = $this->getPlayer();
        $opponent = $player->getOpponent();
        $loss = min(1, $opponent->getScore());
        $player->increaseScore($loss, $this);
        $opponent->increaseScore(-$loss, $this);
    }

    //
    public function recruitEffect()
    {
    }

    public function endEffect()
    {
    }

    public function isWitch()
    {
        return false;
    }

    public function isTitan()
    {
        return false;
    }

    public function getTranslatableName()
    {
        switch ($this->getType()) {
            case KING:
                return clienttranslate('King');
                break;

            case REINE:
                return clienttranslate('Reine');
                break;

            case WITCH:
                return clienttranslate('Witch');
                break;

            case WARRIOR:
                return clienttranslate('Warrior');
                break;

            case TITAN:
                return clienttranslate('Titan');
                break;

            case POPESS:
                return clienttranslate('Popess');
                break;

            case WARRIOR_MONK:
                return clienttranslate('Warrior Monk');
                break;

            case GAIA:
                return clienttranslate('Gaia');
                break;

            case OURANOS:
                return clienttranslate('Ouranos');
                break;


            case COLONEL:
                return clienttranslate('Colonel');
                break;

            case GENERAL:
                return clienttranslate('General');
                break;

            case CAPTAIN:
                return clienttranslate('the Captain');
                break;

            case MARSHAL:
                return clienttranslate('Marshal');
                break;
            case THRONE:
                return clienttranslate('Throne');
                break;
            default:
                die("error with " . $this->getType());
        }
    }

    public function getTranslatableRealm()
    {
        switch ($this->getRealm()) {
            case URSIDS:
                return clienttranslate('Ursids');
            case IMPERIAL:
                return clienttranslate('The Imperial Order');
            case REPTILES:
                return clienttranslate('Reptiles');
            case RAPTORS:
                return clienttranslate('Raptors');
            case FELINES:
                return clienttranslate('Felines');
            case MARINES:
                return clienttranslate('Marines');
            case RELIGIOUS:
                return clienttranslate('The Religious Order');

            default:
                die("error with " . $this->getRealms());
        }
    }

    public function getName()
    {
        return $this->getType() . "-" . $this->getRealm();
    }
}
