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
		return $this->getRewards($influence, [2], 2);
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
