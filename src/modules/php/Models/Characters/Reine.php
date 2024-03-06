<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Reine extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		return $this->getRewards($influence, [3, 4, 5], 1);
	}

	//
	public function recruitEffect()
	{
		return 0;
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
