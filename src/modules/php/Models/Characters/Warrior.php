<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Warrior extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		$this->getRewards($influence, [4], 'stealOrDestroy');
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
		return true;
	}
}
