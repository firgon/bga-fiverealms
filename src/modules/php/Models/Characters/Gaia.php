<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Gaia extends \FRMS\Models\Card
{

	//1
	public function recruitEffect()
	{
		return 1;
	}

	public function endEffect()
	{
		return $this->majorityOfTitans();
	}

	public function isTitan()
	{
		return true;
	}

	public function isWarrior()
	{
		return false;
	}
}
