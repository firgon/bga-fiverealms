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
		$player = $this->getPlayer();
		$player->increaseScore(1, $this);
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
