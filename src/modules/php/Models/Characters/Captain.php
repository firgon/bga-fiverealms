<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Captain extends \FRMS\Models\Card
{

	//
	public function recruitEffect()
	{
		$this->countMajorities();
	}

	public function endEffect()
	{
		return $this->countMajorities();
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
