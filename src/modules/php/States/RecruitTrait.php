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
		[$choosableCards, $choosablePlaces] = Players::getActive()->getChoosableCardsAndPlaces();
		return [
			'cards' => $choosableCards->ui(),
			'availablePlaces' => $choosablePlaces
		];
	}

	public function actChooseCharacter($cardId, $placeId)
	{ // get infos

		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction('actChooseCharacter');

		$currentPlayer = Players::get($pId);

		$args = $this->getArgs();
		if (!in_array($cardId, $args['cards']->getIds())) {
			throw new \BgaVisibleSystemException("You can't choose this card. " . $cardId);
		}
		if (!in_array($placeId, $args['availablePlaces'])) {
			throw new \BgaVisibleSystemException("You can't choose this place. " . $placeId);
		}

		//if discard a card is needed
		$discardCard = Cards::getInLocationPId(COUNCIL, $pId, $placeId);
		if (!is_null($discardCard)) {
			$discardCard->setLocation(DISCARD);
			Notifications::discard($currentPlayer, $discardCard);
		}

		$card = Cards::get($cardId);

		$card->setLocation($card->getType() == TITAN ? TITANS : COUNCIL);
		$card->setState($placeId);

		Notifications::chooseCharacter($currentPlayer, $card, $placeId);

		$card->recruitEffect();

		$this->gamestate->nextState(END_TURN);
	}
}
