<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Ouranos extends \FRMS\Models\Card
{

	//1
	public function recruitEffect()
	{
		return 1;
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
