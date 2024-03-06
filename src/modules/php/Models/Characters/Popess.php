<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Popess extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		foreach ($influence as $realm => $spaceIds) {

			//determine if a forth card have been played 
			if (in_array(4, $this->getRangePlayedCards(count($spaceIds), $realm))) {
				Players::get($this->getPlayerId())->increaseScore(2, $this);
			}
		}
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
