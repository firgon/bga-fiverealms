<?php

namespace FRMS\Managers;

use FRMS\Helpers\Utils;
use FRMS\Helpers\Collection;
use FRMS\Core\Notifications;
use FRMS\Core\Stats;

/* Class to manage all the Cards for Col */

class Cards extends \FRMS\Helpers\Pieces
{
    protected static $table = 'cards';
    protected static $prefix = 'card_';
    protected static $autoIncrement = true;
    protected static $autoremovePrefix = false;
    protected static $customFields = ['extra_datas', 'value', 'color'];

    protected static $autoreshuffle = true; // If true, a new deck is automatically formed with a reshuffled discard as soon at is needed
    // If defined, tell the name of the deck and what is the corresponding discard (ex : "mydeck" => "mydiscard")
    protected static $autoreshuffleCustom = ['deck' => 'discard'];

    protected static function cast($row)
    {
        $data = self::getCards()[$row[static::$prefix . 'id']];
        return new \FRMS\Models\Card($row, $data);
    }

    public static function getUiData()
    {
        return [
            'deck_count' => static::countInLocation('deck'),
            'discard_count' => static::countInLocation('discard'),
            'discard' => static::getTopOf('discard')
        ];
    }

    /* Creation of the Cards */
    public static function setupNewGame($players, $options)
    {
        // $cards = [];
        // // Create the deck
        // foreach (self::getCards() as $id => $card) {

        //     $cards[] = [
        //         'id' => $id,
        //         'location' => 'deck',
        //         'value' => $card['value'],
        //         'color' => $card['color']
        //     ];
        // }

        // static::create($cards);

        // static::shuffle('deck');

        // $nbCards = 4;
        // foreach ($players as $pId => $player) {
        //     static::pickForLocation($nbCards, 'deck', 'hand', $pId);
        //     static::pickOneForLocation('deck', 'table', $pId);
        //     $nbCards++;
        // }
    }

    public function getCards()
    {
        $f = function ($data) {
            return [
                'color' => $data[0],
                'value' => $data[1],
                'action' => $data[2],
            ];
        };

        return [
            // 1 => $f([GREEN, 1, NUGGETS]),
        ];
    }
}
