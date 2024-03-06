<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class General extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		return $this->getRewards($influence, 'line', 'replay');
	}

	protected function line()
	{
	}

	protected function replay()
	{
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
