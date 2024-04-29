<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Colonel extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		$this->getPlayer()->increaseScore($this->countNewLines($influence), $this);
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
