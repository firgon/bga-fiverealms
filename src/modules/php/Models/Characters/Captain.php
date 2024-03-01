<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Captain extends \FRMS\Models\Card
{
	public function anytimeEffect($playedRealm, $nthOfCards)
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
		return $this->majorityInEachRealm();
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
