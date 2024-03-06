<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Witch extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		return $this->getRewards($influence, [3, 5], 'witch');
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
