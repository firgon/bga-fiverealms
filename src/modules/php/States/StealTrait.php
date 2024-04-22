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

trait StealTrait
{
	public function argSteal()
	{
		$opp = Players::getActive()->getOpponent();

		$canSteal = $opp->getScore() > 0;
		$destroyableCardsIds = Cards::getInLocationPId(COUNCIL, $opp->getId())->getIds();

		[$cards, $choosableCards, $choosablePlaces] = Players::getActive()->getChoosableCardsAndPlaces();
		return [
			'canSteal' => $canSteal,
			'destroyableCardsIds' => $destroyableCardsIds,
			'descSuffix' => $canSteal || $destroyableCardsIds ? "" : "impossible"
		];
	}

	public function actPass()
	{ // get infos

		self::checkAction('actPass');

		$args = $this->getArgs();

		if (!$args['suffix']) {
			throw new \BgaVisibleSystemException("You can't pass now " . $args);
		}

		$this->gamestate->nextState(END_TURN);
	}

	public function actDestroy($cardId)
	{
		$player = Players::getActive();
		Game::checkAction('actDestroy');

		$args = $this->getArgs();

		if (!in_array($cardId, $args['destroyableCardsIds'])) {
			throw new \BgaVisibleSystemException("You can't destroy this card now " . $cardId);
		}

		$card = Cards::get($cardId);

		$card->setLocation(DISCARD);

		Notifications::destroy($player, $card);

		Game::transition(END_TURN);
	}

	public function actSteal()
	{
		$player = Players::getActive();
		Game::checkAction('actSteal');

		$args = $this->getArgs();

		if (!$args['canSteal']) {
			throw new \BgaVisibleSystemException("You can't steal now " . $args);
		}

		Notifications::steal($player, 1);

		$player->incScore(1);
		$player->getOpponent()->incScore(-1);

		Game::transition(END_TURN);
	}
}
