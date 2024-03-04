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

    protected function getRewards($playedRealm, $nthOfCards, $thresold, $reward)
    {
        //test if $thresold is an array if not it's a function
        //test if $reward is a number if not it's a function
        if ($this->getRealm() !=  $playedRealm) return;
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
