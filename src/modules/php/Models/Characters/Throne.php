<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Throne extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		$this->getRewards($influence, [3], 2);
	}

	public function isTitan()
	{
		return false;
	}

	public function isWarrior()
	{
		return false;
	}
}
