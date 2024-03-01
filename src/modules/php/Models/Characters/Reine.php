<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Reine extends \FRMS\Models\Card
{
	public function anytimeEffect($playedRealm, $nthOfCards)
	{
		return $this->getRewards($playedRealm, $nthOfCards, [3, 4, 5], 1);
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
