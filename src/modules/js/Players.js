define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("fiverealms.players", null, {
    setupPlayers(gamedatas) {
      for (const playerId in gamedatas.players) {
        const player = gamedatas.players[playerId];

        // this.place("pawn_tpl", player, "board");

        // //build pyramids
        // this.buildPyramid(player.pyramid, player);
      }

      //add general tooltips
      // this.myUpdatePlayerOrdering("pyramid", "pyramids");

      // dojo.place(
      //   '<div id="firstPlayer" class="firstPlayer"></div>',
      //   "overall_player_board_" + gamedatas.firstPlayer
      // );

      // this.addTooltip("firstPlayer", _("First player"), "");
      // this.addTooltipToClass("nextIncome", _("Next Compass yield"), "");

      // this.updateScores(gamedatas.scores);
    },

    goToPlayerBoard(pId, evt = null) {
      if (evt) evt.stopPropagation();

      // Tabbed view
      this._focusedPlayer = pId;
      [...$("boreal-container").querySelectorAll(".boreal-gamezone")].forEach(
        (board) =>
          board.classList.toggle("active", board.id == `gamezone-${pId}`)
      );
    },

    setupChangeBoardArrows(pId) {
      let leftArrow = $(`gamezone-${pId}`).querySelector(".prev-player-board");
      if (leftArrow)
        leftArrow.addEventListener("click", () => this.switchPlayerBoard(-1));

      let rightArrow = $(`gamezone-${pId}`).querySelector(".next-player-board");
      if (rightArrow)
        rightArrow.addEventListener("click", () => this.switchPlayerBoard(1));
    },

    getDeltaPlayer(pId, delta) {
      let playerOrder = this.orderedPlayers;
      let index = playerOrder.findIndex((elem) => elem.id == pId);
      if (index == -1) return -1;

      let n = playerOrder.length;
      return playerOrder[(((index + delta) % n) + n) % n].id;
    },

    switchPlayerBoard(delta) {
      let pId = this.getDeltaPlayer(this._focusedPlayer, delta);
      if (pId == -1) return;
      this.goToPlayerBoard(pId);
    },

    board_tpl(player) {
      let arrows = player.name;

      if (player != null && !this.isSolo()) {
        arrows = `<div class='prev-player-board'><i class="fa fa-long-arrow-left"></i></div>${player.name}<div class='next-player-board'><i class="fa fa-long-arrow-right"></i></div>`;
      }

      return `<div id='gamezone-${player.id}' class='treos-gamezone' style='border-color:#${player.color}'>
			<div class='player-board-name' style='background-color:#${player.color}'>
				${arrows}
			</div>
			<div id='gamezone-cards-${player.id}' class='gamezone-cards'></div>
			<div class='treos-player-board-wrapper'>
				<div class='treos-player-board ${player.colorName}' id='player-board-${player.id}'>
        <div id='deck_${player.id}' class='deck RuneCard card back RuneCardBack'></div>
        <div id='possiblePlace_0_${player.id}' class='possiblePlace ${player.colorName}'></div>
        <div id='possiblePlace_1_${player.id}' class='possiblePlace ${player.colorName}'></div>
        <div id='possiblePlace_2_${player.id}' class='possiblePlace ${player.colorName}'></div>
        <div id='possiblePlace_3_${player.id}' class='possiblePlace ${player.colorName}'></div>
        <div id='quest_0_${player.id}' class='questPlace ${player.colorName}'></div>
        <div id='quest_1_${player.id}' class='questPlace ${player.colorName}'></div>
        <div id='quest_2_${player.id}' class='questPlace ${player.colorName}'></div>
        <div id='quest_3_${player.id}' class='questPlace ${player.colorName}'></div>
        <div id='possiblePlace_4_${player.id}' class='discard possiblePlace ${player.colorName}'>
          <div id='discard_${player.id}' class='deck RuneCard card back RuneCardBack'></div>
        </div>
        
      
				</div>
			</div>
		</div>`;
    },

    /*
     *   █████████                                          ███
     *  ███░░░░░███                                        ░░░
     * ███     ░░░   ██████  ████████    ██████  ████████  ████   ██████
     *░███          ███░░███░░███░░███  ███░░███░░███░░███░░███  ███░░███
     *░███    █████░███████  ░███ ░███ ░███████  ░███ ░░░  ░███ ░███ ░░░
     *░░███  ░░███ ░███░░░   ░███ ░███ ░███░░░   ░███      ░███ ░███  ███
     * ░░█████████ ░░██████  ████ █████░░██████  █████     █████░░██████
     *  ░░░░░░░░░   ░░░░░░  ░░░░ ░░░░░  ░░░░░░  ░░░░░     ░░░░░  ░░░░░░
     *
     *
     *
     */
    // place each player board in good order.
    myUpdatePlayerOrdering(elementName, container) {
      let index = 0;
      for (let i in this.gamedatas.playerorder) {
        const playerId = this.gamedatas.playerorder[i];
        dojo.place(elementName + "_" + playerId, container, index);
        index++;
      }
    },

    // semi generic
    tplPlayerPanel(player) {
      return `<div id='boreal-player-infos_${player.id}' class='player-infos'>
        <div class='initiative' data-initiative="${player.initiativeRank}"></div>
      </div>`;
    },

    getPlayers() {
      return Object.values(this.gamedatas.players);
    },

    getColoredName(pId) {
      let name = this.gamedatas.players[pId].name;
      return this.coloredPlayerName(name);
    },

    getPlayerColor(pId) {
      return this.gamedatas.players[pId].color;
    },

    isSolo() {
      return this.getPlayers().length == 1;
    },
  });
});
