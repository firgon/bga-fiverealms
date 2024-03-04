<?php

namespace FRMS\States;

use PDO;
use FRMS\Core\Game;
use FRMS\Core\Globals;
use FRMS\Core\Notifications;
use FRMS\Core\Engine;
use FRMS\Core\Stats;
use FRMS\Managers\Cards;
use FRMS\Managers\Players;
use FRMS\Models\Player;

trait TurnTrait
{
	public function stNextPlayer()
	{
		$this->activeNextPlayer();
		Game::transition();
	}

	public function argPlay()
	{
		$activePlayer = Players::getActive();
		$nextCard = Cards::getTopOf(DECK);

		return [
			'possibleSpaceIds' => Cards::getPlayablePlaces($nextCard->getRealm()),
			'nextCard' => $nextCard->getUiData(false)
		];
	}

	public function stPlay()
	{
	}

	public function actInfluence($spaceId, $realm)
	{
		// get infos

		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction('actInfluence');

		$currentPlayer = Players::get($pId);

		$args = $this->getArgs();

		if (!in_array($spaceId, $args['possibleSpaceIds'])) {
			throw new \BgaVisibleSystemException("You can't play your card here.");
		}

		if (!in_array($realm, $args['possibleSpaceIds'][$realm])) {
			throw new \BgaVisibleSystemException("You can't influence this realm $realm.");
		}

		$spaceIds = $args['possibleSpaceIds'][$realm];

		foreach ($spaceIds as $spaceId) {
			$card = Cards::getCardFromSpaceId($spaceId);
			$currentPlayer->takeCardInInfluence($card);
		}

		Notifications::influence($currentPlayer, $spaceIds, $realm);

		$influenceInRealm = $currentPlayer->countSpecificBanner($realm);

		$currentPlayer->activateCouncil($realm, range($influenceInRealm - count($spaceIds) + 1, $influenceInRealm));

		$this->gamestate->nextState('');
	}
}
