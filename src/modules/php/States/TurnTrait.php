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
		Cards::adjustAlkane();
		$activePlayer = Players::getActive();
		$nextState = $activePlayer->getNextPendingAction();

		if ($nextState) {
			Game::goTo($nextState);
		} else {
			if (Cards::countInLocation(DECK)) {
				$this->activeNextPlayer();
				$this->giveExtraTime(Players::getActiveId());
				Game::transition(END_TURN);
			} else {
				Game::transition(END_GAME);
			}
		}
	}

	public function argPlay()
	{
		$nextCard = Cards::getTopOf(DECK);

		return [
			'possibleSpaceIds' => Cards::getPlayablePlaces($nextCard->getRealm()),
			'nextCard' => $nextCard->getUiData(false)
		];
	}

	public function actRecruit($spaceId, $realm)
	{ // get infos

		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction('actRecruit');

		$currentPlayer = Players::get($pId);

		$args = $this->getArgs();
		$realms = $args['possibleSpaceIds'][$spaceId] ?? null;
		if (is_null($realms)) {
			throw new \BgaVisibleSystemException("You can't play your card here.");
		}
		$spaceIds = $realms[$realm] ?? null;
		if (is_null($spaceIds)) {
			throw new \BgaVisibleSystemException("You can't recruit this realm $realm.");
		}

		$card = Cards::getTopOf(DECK);
		$card->placeOnAlkane($currentPlayer, $spaceId);

		$cards = [];
		foreach ($spaceIds as $spaceId) {
			$card = Cards::getCardFromSpaceId($spaceId);
			if (!$card) {
				throw new \BgaVisibleSystemException("There is no card on $spaceId.");
			}
			$currentPlayer->addCardInHand($card);
			$cards[] = $card;
		}

		Notifications::recruit($currentPlayer, $spaceIds, $realm, $cards);

		$currentPlayer->addActionToPendingAction(ST_RECRUIT);

		$this->gamestate->nextState(END_TURN);
	}

	public function actInfluence($spaceId, $realm, $influence)
	{
		// get infos

		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction('actInfluence');

		$currentPlayer = Players::get($pId);

		$args = $this->getArgs();

		$realms = $args['possibleSpaceIds'][$spaceId] ?? null;
		if (is_null($realms)) {
			throw new \BgaVisibleSystemException("You can't play your card here.");
		}
		$possibleSpaceIds = $realms[$realm] ?? null;
		if (is_null($possibleSpaceIds)) {
			throw new \BgaVisibleSystemException("You can't recruit this realm $realm.");
		}


		$card = Cards::getTopOf(DECK);
		$card->placeOnAlkane($currentPlayer, $spaceId);

		//for each space ids, place the matching card in the ???
		$cards = [];
		foreach ($influence as $playedRealm => $spaceIds) {
			foreach ($spaceIds as $spaceId) {
				if (!in_array($spaceId, $possibleSpaceIds)) {
					throw new \BgaVisibleSystemException("You can't take this card. Should not happen");
				}
				$card = Cards::getCardFromSpaceId($spaceId);
				if (($card->getRealm() != $playedRealm && $card->getRealm() != IMPERIAL) || $card->getRealm() == RELIGIOUS) {
					throw new \BgaVisibleSystemException("You can't place this card $card->id here.");
				}
				$cards[] = $card;
				$currentPlayer->addCardInInfluence($card, $playedRealm);
			}
		}

		Notifications::influence($currentPlayer, $spaceIds, $realm, $influence, $cards);

		//for each realm column where player added cards, activate council.
		$currentPlayer->activateCouncil($influence);

		$this->gamestate->nextState('');
	}
}
