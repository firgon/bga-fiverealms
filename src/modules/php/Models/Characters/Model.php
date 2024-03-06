<?php

namespace FRMS\Models\Characters;

use FRMS\Managers\Players;

/*
 * Throne
 */

class {FILENAME_PC} extends \FRMS\Models\Card
{
	public function anytimeEffect($influence){
		return $this->getRewards($influence, {ANYTIME});
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
