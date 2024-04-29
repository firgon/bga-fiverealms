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

trait RecruitTrait
{
	public function argRecruit()
	{
		[$cards, $choosableCards, $choosablePlaces] = Players::getActive()->getChoosableCardsAndPlaces();
		return [
			'cards' => $cards,
			'choosableCards' => $choosableCards->ui(),
			'availablePlaces' => $choosablePlaces,
			'suffix' => count($choosableCards) ? '' : 'impossible'
		];
	}

	public function actChooseCharacter($cardId, $placeId = null)
	{ // get infos

		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction('actChooseCharacter');

		$currentPlayer = Players::get($pId);

		$args = $this->getArgs();
		if (!in_array($cardId, $args['cards']->getIds())) {
			throw new \BgaVisibleSystemException("You can't choose this card. " . $cardId);
		}

		$card = Cards::get($cardId);

		if (!$card->isTitan() && !in_array($placeId, $args['availablePlaces'])) {
			throw new \BgaVisibleSystemException("You can't choose this place. " . $placeId);
		}

		//if discard a card is needed
		if (!$card->isTitan()) {
			$discardCard = Cards::getInLocationPId(COUNCIL, $pId, $placeId)->first();
			if (!is_null($discardCard)) {
				$discardCard->setLocation(DISCARD);
				Notifications::discard($currentPlayer, $discardCard);
			}
		}

		$card->setLocation($card->isTitan() ? TITANS : COUNCIL);
		$card->setPlayerId($pId);
		$card->setState($placeId);

		Notifications::chooseCharacter($currentPlayer, $card, $placeId);

		$card->recruitEffect();

		$currentPlayer->discardCardInHand();

		$this->gamestate->nextState(END_TURN);
	}



	public function actPassChooseCharacter()
	{ // get infos

		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction('actPassChooseCharacter');

		$currentPlayer = Players::get($pId);

		$currentPlayer->discardCardInHand();
		Notifications::passChooseCharacter($currentPlayer);

		$this->gamestate->nextState(END_TURN);
	}
}
