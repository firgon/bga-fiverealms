<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Witch extends \FRMS\Models\Card
{
	public function anytimeEffect($playedRealm, $nthOfCards)
	{
		return $this->getRewards($playedRealm, $nthOfCards, [3, 5], 'witch');
	}

	protected function witch()
	{
		//add next_state
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

	public function isWitch()
	{
		return true;
	}
}
