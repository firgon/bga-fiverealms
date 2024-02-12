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
    protected static $customFields = ['extra_datas', 'type', 'realm', 'x', 'y', 'flipped'];

    protected static $autoreshuffle = true; // If true, a new deck is automatically formed with a reshuffled discard as soon at is needed
    // If defined, tell the name of the deck and what is the corresponding discard (ex : "mydeck" => "mydiscard")
    protected static $autoreshuffleCustom = ['deck' => 'discard'];

    protected static function cast($row)
    {
        // $type = '\FRMS\Models\Characters\\' . $row['type'];
        $type = '\FRMS\Models\Card';
        return new $type($row);
    }

    public static function getUiData()
    {
        return [
            'alkane' => [],
            'deckN' => static::countInLocation(DECK),
            'discard' => static::getTopOf(DISCARD),

        ];
    }

    /* Creation of the Cards */
    public static function setupNewGame($players, $options)
    {
        $cards = [];

        //create throne cards
        foreach (NORMAL_BANNERS as $banner) {
            $cards[] = [
                'location' => DECK . THRONE,
                'type' => THRONE,
                'realm' => $banner
            ];
        }

        //create normal banner cards
        foreach (ALL_BANNERS as $banner) {
            $characters = (in_array($banner, NORMAL_BANNERS))
                ? NORMAL_CHARACTERS
                : (($banner == IMPERIAL)
                    ? IMPERIAL_CHARACTERS
                    : RELIGIOUS_CHARACTERS);
            foreach ($characters as $character) {
                $cards[] = [
                    'location' => DECK,
                    'type' => $character,
                    'realm' => $banner
                ];
            }
        }

        static::create($cards);

        static::shuffle(DECK);
        static::shuffle(DECK . THRONE);

        foreach ($players as $pId => $player) {
            static::pickForLocation(1, DECK . THRONE, CONCIL, $pId);
        }

        static::generateAlkane(false);
    }

    public static function generateAlkane($notifNeeded = true)
    {
        $possiblePlaces = [
            [2, 1],
            [3, 1],
            [1, 2],
            [3, 2],
            [1, 3],
            [2, 3]
        ];
        $index = 0;
        $card = static::getInLocation(ALKANE)->first();
        if ($card) {
            $card->setCoord($possiblePlaces[$index++]);
        }
        for ($i = $index; $i < count($possiblePlaces); $i++) {
            $newCard = static::getTopOf(DECK);
            $newCard->setCoord($possiblePlaces[$index++]);
        }
        if ($notifNeeded) Notifications::newAlkane();
    }
}
