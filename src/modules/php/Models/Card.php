<?php

namespace FRMS\Models;

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

        Notifications::placeCard($currentPlayer, $this->getRealm(), $coord[0], $coord[1]);
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

    protected function getPlayer()
    {
        return Players::get($this->getPlayerId());
    }

    protected function countNewLines($nthOfCards)
    {
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

    protected function majorityOfWarriors()
    {
        $player = $this->getPlayer();
        $opponent = $player->getOpponent();
        if ($player->countWarriors() > $opponent->countWarriors()) {
            $player->increaseScore(2, $this);
            $opponent->increaseScore(-2, $this);
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
            $player->increaseScore(3, $this);
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

        $player->increaseScore($majorities, $this);
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
}
