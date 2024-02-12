<?php

namespace FRMS\Models;

use FRMS\Managers\Players;

/*
 * Card
 */

class Cell extends \FRMS\Helpers\DB_Model
{
	protected $table = 'cells';
	protected $primary = 'cell_id';
	protected $attributes = [
		'id' => ['cell_id', 'int'],
		'extraDatas' => ['extra_datas', 'obj'],
		'playerId' => ['player_id', 'int'],
		'turn' => ['turn', 'int'],
		'sheet' => ['sheet', 'int'],
		'zone' => 'zone',
		'cell' => ['cell', 'int']
	];

	protected $staticAttributes = [
		'color',
		['value', 'int'],
		'action',
		'next'

	];

	public function __construct($row, $datas)
	{
		parent::__construct($row);
		foreach ($datas as $attribute => $value) {
			$this->$attribute = $value;
		}
	}

	public function __toString()
	{
		if ($this->sheet == COUNTRIES)	return join('_', [$this->playerId, $this->sheet, $this->zone, $this->cell]);
		else return join('_', [$this->sheet, $this->zone, $this->cell]);
	}

	public function isSupported($players, $options)
	{
		return true; // Useful for expansion/ban list/ etc...
	}
}
