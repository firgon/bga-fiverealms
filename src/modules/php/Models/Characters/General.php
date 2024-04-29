<?php

namespace FRMS\Models\Characters;

use FRMS\Core\Notifications;
use FRMS\Managers\Players;

/*
 * Throne
 */

class General extends \FRMS\Models\Card
{
	public function anytimeEffect($influence)
	{
		$newLines = $this->countNewLines($influence);
		for ($i = 0; $i < $newLines; $i++) {
			$this->getPlayer()->addActionToPendingAction(ST_PLAY);
		}
		if ($newLines == 1) {

			Notifications::message(clienttranslate('Thanks to General, ${player_name} will play one extra turn'), ['player' => $this->getPlayer()]);
		} else if ($newLines > 1) {

			Notifications::message(clienttranslate('Thanks to General, ${player_name} will play ${n} extra turns'), ['player' => $this->getPlayer(), 'n' => $newLines]);
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
