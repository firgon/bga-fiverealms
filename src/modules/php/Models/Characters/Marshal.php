<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Marshal extends \FRMS\Models\Card
{

	public function endEffect()
	{
		return $this->majorityOfThisRealm();
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
