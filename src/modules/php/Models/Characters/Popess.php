<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Popess extends \FRMS\Models\Card
{
	public function anytimeEffect($playedRealm, $nthOfCards)
	{
		return $this->getRewards($playedRealm, $nthOfCards, [4], 2);
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
