<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class WarriorMonk extends \FRMS\Models\Card
{

	//steal
	public function recruitEffect()
	{
		$this->stealOrDestroy();
	}

	public function endEffect()
	{
		return $this->majorityOfWarriors();
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
