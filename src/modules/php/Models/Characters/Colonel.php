<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Colonel extends \FRMS\Models\Card
{
	public function anytimeEffect($playedRealm, $nthOfCards)
	{
		return $this->getRewards($playedRealm, $nthOfCards, 'line', 1);
	}

	//
	public function recruitEffect()
	{
		return 0;
	}

	public function endEffect()
	{
		return $this->majorityOfLines();
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
