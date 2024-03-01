<?php

namespace FRMS\Models;

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
        'extraDatas' => ['extra_datas', 'obj'],
        'flipped' => ['flipped', 'int'],
        'x' => ['x', 'int'],
        'y' => ['y', 'int'],
        'type' => 'type',
        'realm' => 'realm',
        'playerId' => ['player_id', 'int']
    ];

    protected $staticAttributes = [];

    public function setCoord($coord)
    {
        $this->setX($coord[0]);
        $this->setY($coord[1]);
    }

    public function getSpaceId()
    {
        return Cards::getSpaceId($this->getX(), $this->getY());
    }

    public function anytimeEffect($playedRealm, $nthOfCards)
    {
        return 0;
    }

    //TODO

    protected function getRewards($playedRealm, $nthOfCards, $thresold, $reward)
    {
        //test if $tresold is an array if not it's a function
        //test if $reward is a number if not it's a function
    }
    protected function majorityOfThisRealm()
    {
    }
    protected function majorityInEachRealm()
    {
    }
    protected function majorityOfWarriors()
    {
    }
    protected function majorityOfTitans()
    {
    }
    protected function majorityOfLines()
    {
    }
    protected function countMajorities()
    {
    }

    //
    public function recruitEffect()
    {
        return 0;
    }

    public function endEffect()
    {
        return 0;
    }

    public function isWitch()
    {
        return false;
    }
}
