<?php

namespace FRMS\Managers;

use FRMS\Core\Globals;
use FRMS\Helpers\Utils;
use FRMS\Helpers\Collection;
use FRMS\Core\Notifications;
use FRMS\Core\Stats;
use Col\Models\Cell;
use PhpParser\Node\Expr\Cast\Object_;

/*
 * Cells manager : allows to easily access cells ...
 *  a cell is an instance of Cell class
 */

class Cells extends \FRMS\Helpers\DB_Manager
{
	protected static $table = 'cells';
	// protected static $prefix = 'cell_';
	// protected static $autoIncrement = true;
	// protected static $autoremovePrefix = false;
	protected static $primary = 'cell_id';
	protected static $customFields = ['extra_datas', 'turn', 'player_id', 'sheet', 'zone', 'cell'];

	protected static function cast($row)
	{
		$data = self::getStaticData($row);
		return new \FRMS\Models\Cell($row, $data);
	}

	public function getUiData()
	{
		return static::getAllVisibleCells(Globals::getTurn());
	}

	public static function setupNewGame($players, $options)
	{
	}

	public static function getAllVisibleCells($turn)
	{
		$rows = self::DB()
			->where('turn', '<=', $turn)
			->get();

		$data = [];
		foreach ($rows as $rowId => $infos) {
			$data[] = (string)$infos;
		}

		return $data;
	}

	public static function getAll()
	{
		return self::DB()->get(false);
	}

	public static function get($cellId)
	{
		return self::DB()
			->where($cellId)
			->getSingle();
	}

	public function getStaticData($row)
	{
		// 	if ($row['cell'] >= 0) {
		// 		$data = ALL_CELLS[$row['sheet']][$row['zone']]['legs'][$row['cell']];
		// 		$data['cellType'] = CELL;
		// 	} else {
		// 		$data = ALL_CELLS[$row['sheet']][$row['zone']];
		// 		$data['cellType'] = SUPER_CELL;
		// 	}
		// 	return $data;
	}
}
