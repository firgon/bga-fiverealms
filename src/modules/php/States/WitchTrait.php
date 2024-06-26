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

trait WitchTrait
{
	public function argWitch()
	{
		[$cards, $choosableCards, $choosablePlaces, $fullCouncil] = Players::getActive()->getChoosableCardsAndPlaces(true);
		$influencableCards = $cards->filter(fn ($card) => $card->getRealm() != RELIGIOUS);
		return [
			'cards' => $cards,
			'influencableCards' => $influencableCards, //for influence
			'choosableCards' => $choosableCards->ui(), //for recruit
			'availablePlaces' => $choosablePlaces,
			'canInfluence' => count($influencableCards) > 0,
			'fullCouncil' => $fullCouncil,
			'suffix' => count($influencableCards) > 0 || count($choosableCards) > 0 ? "" : "impossible",
		];
	}

	public function actInfluenceWitch($influence, $bCheating = false)
	{
		// get infos
		$pId = Game::get()->getCurrentPlayerId();
		if (!$bCheating) self::checkAction('actInfluenceWitch');

		$currentPlayer = Players::get($pId);

		//for each space ids, place the matching card in the influence column
		$newInfluence = [];
		foreach ($influence as $playedRealm => $cardIds) {
			if (!is_array($cardIds)) {
				$cardIds = [$cardIds];
			}
			$newInfluence[$playedRealm] = $cardIds;

			$cards = [];
			foreach ($cardIds as $cardId) {
				$card = Cards::get($cardId);
				if ($card->getLocation() != DISCARD) {
					throw new \BgaVisibleSystemException("You can't influence this card. $cardId. Should not happen");
				}
				if (($card->getRealm() != $playedRealm && $card->getRealm() != IMPERIAL) || $card->getRealm() == RELIGIOUS) {
					throw new \BgaVisibleSystemException("You can't place this card $card->id here.");
				}
				$cards[] = $card;
				$currentPlayer->addCardInInfluence($card, $playedRealm);
			}

			Notifications::influenceWitch($currentPlayer, $playedRealm, $influence, $cards);
		}

		//for each realm column where player added cards, activate council.
		$currentPlayer->activateCouncil($newInfluence);

		$this->gamestate->nextState('');
	}
}
