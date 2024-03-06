<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Captain extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		return $this->countMajorities();
	}

	//
	public function recruitEffect()
	{
		return 0;
	}

	public function endEffect()
	{
		return $this->countMajorities();
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
