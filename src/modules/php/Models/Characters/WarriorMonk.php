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
		return $this->steal();
	}

	protected function steal()
	{
		//TODO next state steal
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
