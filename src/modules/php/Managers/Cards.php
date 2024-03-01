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

    public static function isSame($cardA, $cardB)
    {
        return $cardA->type == $cardB->type && $cardA->realm == $cardB->realm;
    }

    public static function isSameCharacter($cardA, $cardB)
    {
        return $cardA->type == $cardB->type;
    }

    //   █████████   ████  █████                                   
    //  ███░░░░░███ ░░███ ░░███                                    
    // ░███    ░███  ░███  ░███ █████  ██████   ████████    ██████ 
    // ░███████████  ░███  ░███░░███  ░░░░░███ ░░███░░███  ███░░███
    // ░███░░░░░███  ░███  ░██████░    ███████  ░███ ░███ ░███████ 
    // ░███    ░███  ░███  ░███░░███  ███░░███  ░███ ░███ ░███░░░  
    // █████   █████ █████ ████ █████░░████████ ████ █████░░██████ 
    //░░░░░   ░░░░░ ░░░░░ ░░░░ ░░░░░  ░░░░░░░░ ░░░░ ░░░░░  ░░░░░░  
    //                                                             
    //                                                             
    //                                                             

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
            $newCard->setLocation(ALKANE);
        }
        if ($notifNeeded) Notifications::newAlkane();
    }

    public static function getPlayablePlaces($realm)
    {
        $possiblePlaces = [];
        $grid = static::getAlkaneGrid();
        //zoneRealmsBySpaceId is all adjacent zone like '1_1' => ['1_1', '1_2']
        //$spaceIdByRealms is all same realm space id like 'RAPTORS' => ['1_1', '2_2']
        [$zoneRealmsBySpaceId, $spaceIdByRealms] = static::getRealms($grid);
        // var_dump([$zoneRealmsBySpaceId, $spaceIdByRealms]);
        foreach ($grid as $x => $column) {
            foreach ($column as $y => $card) {
                if (is_null($card)) {
                    //if card is null you can play here
                    $adjacentColors = [];
                    foreach (DIRECTIONS as $coord) {
                        //check if there is an adjacent card
                        if (isset($grid[$x + $coord[0]][$y + $coord[1]])) {
                            $adjacentCard = $grid[$x + $coord[0]][$y + $coord[1]];
                            //add adjacent zone to adjacent colors
                            if (isset($adjacentColors[$adjacentCard->getRealm()])) {
                                $adjacentColors[$adjacentCard->getRealm()] = array_merge($adjacentColors[$adjacentCard->getRealm()], $zoneRealmsBySpaceId[$adjacentCard->getSpaceId()]);
                            } else {
                                $adjacentColors[$adjacentCard->getRealm()] = $zoneRealmsBySpaceId[$adjacentCard->getSpaceId()];
                            }
                        }
                    }
                    //if there is at least one adjacent card
                    if ($adjacentColors) {
                        //if it's one of the adjacent colors
                        if (array_key_exists($realm, $adjacentColors)) {
                            //add current space id and all adjacent zones
                            $possiblePlaces[static::getSpaceId($x, $y)]['adjacent'] = array_values(array_unique(array_merge([static::getSpaceId($x, $y)], $adjacentColors[$realm])));
                        } else {
                            //else only add existing cards with that real
                            $possiblePlaces[static::getSpaceId($x, $y)]['different'] = $spaceIdByRealms[$realm] ?? [];
                        }
                    }
                }
            }
        }

        return $possiblePlaces;
    }

    protected static function getRealms($grid)
    {
        $alreadyUsed = [];
        $adjacentRealms = [];
        $spaceIdByRealms = [];
        foreach ($grid as $x => $column) {
            foreach ($column as $y => $card) {
                if (!is_null($card) && !in_array($card->getSpaceId(), $alreadyUsed)) {
                    $queue = [$card];
                    $currentZone = [];
                    while (!empty($queue)) {
                        $currentCard = array_shift($queue);

                        // var_dump($currentCard);
                        $currentZone[] = $currentCard;

                        $spaceIdByRealms[$card->getRealm()][] = $card->getSpaceId();
                        foreach (static::getNeighbours($currentCard->getX(), $currentCard->getY()) as $coord) {
                            if (isset($grid[$coord[0]][$coord[1]])) {
                                $candidate = $grid[$coord[0]][$coord[1]];
                                if ($currentCard->getRealm() == $candidate->getRealm() && !in_array($candidate, $currentZone, true)) {
                                    $queue[] = $candidate;
                                }
                            }
                        }
                    }
                    $currentSpaceIds = array_map(fn ($c) => $c->getSpaceId(), $currentZone);
                    foreach ($currentZone as $validatedCard) {
                        $adjacentRealms[$validatedCard->getSpaceId()] = $currentSpaceIds;
                    }
                    $alreadyUsed = array_merge($alreadyUsed, $currentSpaceIds);
                }
            }
        }
        return [$adjacentRealms, $spaceIdByRealms];
    }

    protected static function getNeighbours($x, $y)
    {
        $neighbours = [];
        foreach (DIRECTIONS as $coord) {
            $neighbours[] = [$x + $coord[0], $y + $coord[1]];
        }
        return $neighbours;
    }

    /**
     * replace alkane from starting position 1,1
     * return true if Alkane has been replaced
     */
    public static function adjustAlkane()
    {
        [$minX, $minY, $maxX, $maxY] = static::getAlkaneBorders();
        $cards = static::getInLocation(ALKANE);
        $toRefresh = false;
        if ($minX != 1) {
            $toRefresh = true;
            foreach ($cards as $card) {
                $card->incX(1 - $minX);
            }
        }
        if ($minY != 1) {
            $toRefresh = true;
            foreach ($cards as $card) {
                $card->incY(1 - $minY);
            }
        }
        return $toRefresh;
    }

    protected static function getAlkaneBorders()
    {
        $cards = static::getInLocation(ALKANE);
        $minX = 10;
        $maxX = 0;
        $minY = 10;
        $maxY = 0;
        foreach ($cards as $card) {
            $x = $card->getX();
            $y = $card->getY();
            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x);
            $maxY = max($maxY, $y);
        }

        return [$minX, $minY, $maxX, $maxY];
    }

    protected static function getAlkaneGrid()
    {
        [$minX, $minY, $maxX, $maxY] = static::getAlkaneBorders();
        $cards = static::getInLocation(ALKANE);
        $grid = [];
        for ($x = $maxX - 2; $x <= $minX + 2; $x++) {
            for ($y = $maxY - 2; $y <= $minY + 2; $y++) {
                $grid[$x][$y] = null;
            }
        }

        foreach ($cards as $card) {
            $grid[$card->getX()][$card->getY()] = $card;
        }
        return $grid;
    }

    public static function getSpaceId($x, $y)
    {
        return $x . '_' . $y;
    }

    public static function getCoord($spaceId)
    {
        return explode($spaceId, '_');
    }
}
