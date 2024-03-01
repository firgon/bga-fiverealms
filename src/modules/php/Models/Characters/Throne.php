<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class Throne extends \FRMS\Models\Card
{
	public function anytimeEffect($playedRealm, $nthOfCards){
		return $this->getRewards($playedRealm, $nthOfCards, [2],2);
	}

	//
	public function recruitEffect(){
		return 0;
	}

	public function endEffect(){
		return $this->();
	}

	public function isTitan(){
		return false;
	}

	public function isWarrior(){
		return false;
	}
}
