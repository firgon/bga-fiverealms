<?php

namespace FRMS\Models;

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
    ];

    protected $staticAttributes = [
        'type', 'realm'
    ];

    public function setCoord($coord)
    {
        $this->setX($coord[0]);
        $this->setY($coord[1]);
    }
}
