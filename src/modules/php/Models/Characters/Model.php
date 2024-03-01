<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class {FILENAME_PC} extends \FRMS\Models\Card
{
	public function anytimeEffect($playedRealm, $nthOfCards){
		return $this->getRewards($playedRealm, $nthOfCards, {ANYTIME});
	}

	//{RECRUIT}
	public function recruitEffect(){
		return 0;
	}

	public function endEffect(){
		return $this->{END}();
	}

	public function isTitan(){
		return {ISTITAN};
	}

	public function isWarrior(){
		return {ISWARRIORS};
	}
}
