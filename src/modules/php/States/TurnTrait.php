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
		$activePlayer = Players::getActive();
		$nextState = $activePlayer->getNextPendingAction();

		if ($nextState) {
			Game::goTo($nextState);
		} else {
			Cards::adjustAlkane();

			if (Cards::countInLocation(DECK) && $activePlayer->countTitans() != 5) {
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
		$nextCards = Cards::getTopOf(DECK, 2, false);

		$nextCard = $nextCards->first();

		$nextCards = $nextCards->toArray();

		$nextCard2 = (count($nextCards) == 2) ? $nextCards[1]->getUiData(false) : null;

		//safe but probably useless
		if (!$nextCard) {
			return [
				'possibleSpaceIds' => [],
				'deck' => null
			];
		}

		return [
			'possibleSpaceIds' => Cards::getPlayablePlaces($nextCard->getRealm()),
			'deck' => $nextCard->getUiData(false),
			'secondCardOnDeck' => $nextCard2
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

		//for each space ids, place the matching card in the influence column

		foreach ($influence as $playedRealm => $spaceIds) {
			$cards = [];
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
			Notifications::influence($currentPlayer, $spaceIds, $playedRealm, $influence, $cards);
		}

		//for each realm column where player added cards, activate council.
		$currentPlayer->activateCouncil($influence);

		$this->gamestate->nextState('');
	}

	public function stPreEndOfGame()
	{
		foreach (Players::getAll() as $pId => $player) {
			if ($player->countTitans() >= 5) {
				Notifications::message(clienttranslate('${player_name} has 5 titans and wins'), ['player' => $player]);
				$player->getOpponent()->setScore(0);
				$player->setScore(1);
				Game::transition();
				return;
			}
		}
		foreach (Players::getAll() as $pId => $player) {
			$player->activateCouncil([], true);
		}
		Game::transition();
	}
}
