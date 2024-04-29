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
		return $this->majorityOfThisRealmInCouncil();
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
