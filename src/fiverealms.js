/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fiverealms implementation : © Emmanuel Albisser <emmanuel.albisser@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * fiverealms.js
 *
 * fiverealms user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

/**This help to console log differently on studio or on production */
var isDebug =
  window.location.host == "studio.boardgamearena.com" ||
  window.location.hash.indexOf("debug") > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
  g_gamethemeurl + "modules/js/Core/game.js",
  g_gamethemeurl + "modules/js/Core/modal.js",
  g_gamethemeurl + "modules/js/Utils/cheatModule.js",
], function (dojo, declare) {
  return declare(
    "bgagame.fiverealms",
    [customgame.game, fiverealms.cheatModule],
    {
      constructor() {
        this._inactiveStates = ["recruit", "play"];
        this._notifications = [
          ["clearTurn", 200],
          ["refreshUI", 200],
          ["placeCard", 1200],
          ["recruit", null],
          ["influence", null],
          ["chooseCharacter", 2400],
          ["passChooseCharacter", 1200],
          ["discardCharacter", 1000],
          ["newAlkane", 2000],
          ["adjustAlkane", null],
          ["newCastleCards", 1000],
          ["steal", 1000],
          ["destroy", 1000],
          ["refresh", 0],
        ];

        // Fix mobile viewport (remove CSS zoom)
        this.default_viewport = "width=800";
      },

      getSettingsConfig() {
        return {
          // confirmMode: { type: "pref", prefId: 103 },
        };
      },

      /**
       * Setup:
       *	This method set up the game user interface according to current game situation specified in parameters
       *	The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
       *
       * Params :
       *	- mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
       */
      setup(gamedatas) {
        debug("SETUP", gamedatas);
        this.setupPlayers();
        this.setupInfoPanel();
        this.setupCards();
        this.setupDiscardModal();
        this.cheatModuleSetup(gamedatas);

        this.inherited(arguments);
      },

      setupDiscardModal() {
        this._discardModal = new customgame.modal("discardDisplay", {
          class: "fiverealms_discard_popin",
          autoShow: false,
          closeIcon: "fa-times",
          closeAction: "hide",
          title: _("Discard"),
          verticalAlign: "flex-start",
          contentsTpl: `<div class='discard-modal' id='discard-cards-modal'></div><div id='discard-modal-footer'></div>`,
          scale: 0.9,
          breakpoint: 800,
          onStartShow: () => {
            this.closeCurrentTooltip();
            $(`discard-cards-modal`).insertAdjacentElement(
              "beforeend",
              $(`fiverealms-discard`),
            );
          },
          onStartHide: () => {
            this.closeCurrentTooltip();
            $(`discard-cards-holder`).insertAdjacentElement(
              "beforeend",
              $(`fiverealms-discard`),
            );
          },
          onShow: () => this.closeCurrentTooltip(),
        });
      },

      clearPossible() {
        document.querySelectorAll(".tmp-card").forEach((elt) => elt.remove());
        this.clearHighlights();
        if ($(`board-${this.player_id}`)) {
          $(`board-${this.player_id}`)
            .querySelectorAll(`.influence-realm`)
            .forEach((e) => delete e.dataset.n);
        }
        if (this._discardModal) {
          this._discardModal.hide();
        }

        this.inherited(arguments);
      },

      onEnteringState(stateName, args) {
        debug("Entering state: " + stateName, args);
        if (this.isFastMode() && ![].includes(stateName)) return;

        if (args.args && args.args.descSuffix) {
          this.changePageTitle(args.args.descSuffix);
        }

        if (args.args && args.args.optionalAction) {
          let base = args.args.descSuffix ? args.args.descSuffix : "";
          this.changePageTitle(base + "skippable");
        }

        if (
          !this._inactiveStates.includes(stateName) &&
          !this.isCurrentPlayerActive()
        )
          return;

        // Undo last steps
        if (args.args && args.args.previousSteps) {
          args.args.previousSteps.forEach((stepId) => {
            let logEntry = $("logs").querySelector(
              `.log.notif_newUndoableStep[data-step="${stepId}"]`,
            );
            if (logEntry) this.onClick(logEntry, () => this.undoToStep(stepId));

            logEntry = document.querySelector(
              `.chatwindowlogs_zone .log.notif_newUndoableStep[data-step="${stepId}"]`,
            );
            if (logEntry) this.onClick(logEntry, () => this.undoToStep(stepId));
          });
        }

        // Restart turn button
        if (
          args.args &&
          args.args.previousChoices &&
          args.args.previousChoices >= 1 &&
          !args.args.automaticAction
        ) {
          if (args.args && args.args.previousSteps) {
            let lastStep = Math.max(...args.args.previousSteps);
            if (lastStep > 0)
              this.addDangerActionButton(
                "btnUndoLastStep",
                _("Undo last step"),
                () => this.undoToStep(lastStep),
                "restartAction",
              );
          }

          // Restart whole turn
          this.addDangerActionButton(
            "btnRestartTurn",
            _("Restart turn"),
            () => {
              this.stopActionTimer();
              this.takeAction("actRestart");
            },
            "restartAction",
          );
        }

        // Call appropriate method
        var methodName =
          "onEnteringState" +
          stateName.charAt(0).toUpperCase() +
          stateName.slice(1);
        if (this[methodName] !== undefined) this[methodName](args.args);
      },

      onAddingNewUndoableStepToLog(notif) {
        if (!$(`log_${notif.logId}`)) return;
        let stepId = notif.msg.args.stepId;
        $(`log_${notif.logId}`).dataset.step = stepId;
        if ($(`dockedlog_${notif.mobileLogId}`))
          $(`dockedlog_${notif.mobileLogId}`).dataset.step = stepId;

        if (
          this.gamedatas &&
          this.gamedatas.gamestate &&
          this.gamedatas.gamestate.args &&
          this.gamedatas.gamestate.args.previousSteps &&
          this.gamedatas.gamestate.args.previousSteps.includes(parseInt(stepId))
        ) {
          this.onClick($(`log_${notif.logId}`), () => this.undoToStep(stepId));

          if ($(`dockedlog_${notif.mobileLogId}`))
            this.onClick($(`dockedlog_${notif.mobileLogId}`), () =>
              this.undoToStep(stepId),
            );
        }
      },

      onEnteringStateConfirmTurn(args) {
        this.addPrimaryActionButton("btnConfirmTurn", _("Confirm"), () => {
          this.stopActionTimer();
          this.takeAction("actConfirmTurn");
        });

        const OPTION_CONFIRM = 103;
        let n = args.previousChoices;
        let timer = Math.min(10 + 2 * n, 20);
        this.startActionTimer(
          "btnConfirmTurn",
          timer,
          this.prefs[OPTION_CONFIRM].value,
        );
      },

      undoToStep(stepId) {
        this.stopActionTimer();
        this.checkAction("actRestart");
        this.takeAction("actUndoToStep", { stepId }, false);
      },

      notif_clearTurn(n) {
        debug("Notif: restarting turn", n);
        this.cancelLogs(n.args.notifIds);
      },

      notif_refreshUI(n) {
        debug("Notif: refreshing UI", n);
        ["players", "cards"].forEach((value) => {
          this.gamedatas[value] = n.args.datas[value];
        });

        this.setupCards();
      },

      /////////////////////////
      //  ____  _
      // |  _ \| | __ _ _   _
      // | |_) | |/ _` | | | |
      // |  __/| | (_| | |_| |
      // |_|   |_|\__,_|\__, |
      //                |___/
      /////////////////////////
      getCell(x, y = null) {
        if (y == null) {
          t = x.split("_");
          x = t[0];
          y = t[1];
        }

        return $(`alkane-${x}-${y}`);
      },

      highlightSpaces(spaceIds, className = "") {
        spaceIds.forEach((spaceId) => {
          this.getCell(spaceId).classList.add("highlighted");
          if (className != "") this.getCell(spaceId).classList.add(className);
        });
      },

      clearHighlights() {
        $("alkane-container")
          .querySelectorAll(".highlighted")
          .forEach((cell) => cell.classList.remove("highlighted"));
      },

      onEnteringStatePlay(args) {
        if (args.deck) this.addCard(args.deck);
        if (!this.isCurrentPlayerActive()) return;

        Object.entries(args.possibleSpaceIds).forEach(([spaceId, infos]) => {
          this.onClick(this.getCell(spaceId), () => {
            this.clientState("playChooseRealm", _("You must choose a realm"), {
              cardId: args.deck.id,
              spaceId,
              realms: infos,
            });
          });
        });
      },

      onEnteringStatePlayChooseRealm(args) {
        this.addCancelStateBtn();

        // Duplicate card as "phantom"
        let oCard = $(`card-${args.cardId}`).cloneNode(true);
        oCard.id += "_copy";
        oCard.classList.add("tmp-card");
        this.getCell(args.spaceId).insertAdjacentElement("beforeend", oCard);

        let selectRealm = (realm) => {
          return () => {
            this.clientState(
              "playChooseAction",
              _("Do you want to recruit or influence?"),
              {
                cardId: args.cardId,
                spaceId: args.spaceId,
                realm,
                spaceIds: args.realms[realm],
              },
            );
          };
        };

        // Auto select if only one realm
        let realms = Object.keys(args.realms);
        if (realms.length == 1) {
          selectRealm(realms[0])();
          return;
        }

        const REALMS = {
          reptiles: _("Reptiles"),
          felines: _("Felines"),
          raptors: _("Raptors"),
          ursids: _("Ursids"),
          marines: _("Marines"),

          religious: _("Religious"),
          imperial: _("Imperial"),
        };

        // Add buttons
        Object.entries(args.realms).forEach(([realm, spaceIds]) => {
          this.addPrimaryActionButton(
            `btn${realm}`,
            REALMS[realm],
            selectRealm(realm),
          );
          this.connect($(`btn${realm}`), "mouseover", () =>
            this.highlightSpaces(spaceIds),
          );
          this.connect($(`btn${realm}`), "mouseout", () =>
            this.clearHighlights(),
          );

          spaceIds.forEach((spaceId) => {
            let cell = this.getCell(spaceId);
            this.onClick(cell, selectRealm(realm));
            this.connect(cell, "mouseover", () =>
              this.highlightSpaces(spaceIds),
            );
            this.connect(cell, "mouseout", () => this.clearHighlights());
          });
        });
      },

      onEnteringStatePlayChooseAction(args) {
        this.addCancelStateBtn();

        // Duplicate card as "phantom"
        let oCard = $(`card-${args.cardId}`).cloneNode(true);
        oCard.id += "_copy";
        oCard.classList.add("tmp-card");
        this.getCell(args.spaceId).insertAdjacentElement("beforeend", oCard);

        // Highlight cards
        this.highlightSpaces(args.spaceIds, "selected");

        // Add buttons
        this.addPrimaryActionButton("btnRecruit", _("Recruit"), () =>
          this.takeAction("actRecruit", {
            spaceId: args.spaceId,
            realm: args.realm,
          }),
        );
        this.addPrimaryActionButton("btnInfluence", _("Influence"), () => {
          if (args.realm == "imperial") {
            this.clientState(
              "chooseInfluenceColumns",
              _(
                "You must choose on which column(s) to place the Imperial cards",
              ),
              args,
            );
          } else {
            let influence = {};
            influence[args.realm] = args.spaceIds;

            this.takeAction("actInfluence", {
              spaceId: args.spaceId,
              realm: args.realm,
              influence: JSON.stringify(influence),
            });
          }
        });

        if (args.realm == "religious") {
          $("btnInfluence").classList.add("disabled");
        }
      },

      onEnteringStateChooseInfluenceColumns(args) {
        this.addCancelStateBtn();

        // Duplicate card as "phantom"
        let oCard = $(`card-${args.cardId}`).cloneNode(true);
        oCard.id += "_copy";
        oCard.classList.add("tmp-card");
        this.getCell(args.spaceId).insertAdjacentElement("beforeend", oCard);

        // Highlight cards
        this.highlightSpaces(args.spaceIds, "selected");

        const REALMS = ["reptiles", "felines", "raptors", "ursids", "marines"];
        let selection = {},
          totalSelected = 0,
          n = args.spaceIds.length;
        let updateSelection = () => {
          totalSelected = 0;
          REALMS.forEach((realm) => {
            $(`board-${this.player_id}`).querySelector(
              `.influence-realm.realm-${realm}`,
            ).dataset.n = selection[realm];
            totalSelected += selection[realm];
          });

          $("btnConfirm").classList.toggle("disabled", totalSelected < n);
          if (totalSelected > 0) {
            this.addSecondaryActionButton("btnReset", _("Reset"), () => {
              REALMS.forEach((realm) => {
                selection[realm] = 0;
              });
              updateSelection();
            });
          } else if ($("btnReset")) $("btnReset").remove();
        };

        REALMS.forEach((realm) => {
          selection[realm] = 0;

          // Cant place on empty col
          if (this._counters[this.player_id][realm].getValue() == 0) return;

          let influence = $(`board-${this.player_id}`).querySelector(
            `.influence-realm.realm-${realm}`,
          );
          this.onClick(influence, () => {
            if (totalSelected >= args.spaceIds.length) return;
            selection[realm] += 1;
            updateSelection();
          });
        });

        this.addPrimaryActionButton("btnConfirm", _("Confirm"), () => {
          let i = 0;
          let influence = {};
          REALMS.forEach((realm) => {
            if (selection[realm] == 0) return;
            influence[realm] = [];
            for (let j = 0; j < selection[realm]; j++) {
              influence[realm].push(args.spaceIds[i++]);
            }
          });

          this.takeAction("actInfluence", {
            spaceId: args.spaceId,
            realm: args.realm,
            influence: JSON.stringify(influence),
          });
        });
        updateSelection();
      },

      notif_placeCard(n) {
        debug("Notif: place a card", n);
        let card = n.args.card;
        this.clearPossible();
        this.slide(`card-${card.id}`, this.getCardContainer(card));
        if (n.args.deck) this.addCard(n.args.deck);
        this.decDeckCount();
      },

      notif_influence(n) {
        debug("Notif: choose to influence", n);
        let increases = {};
        Promise.all(
          n.args.cards.map((card, i) => {
            let realm = card.influenceColumn;
            increases[realm] = (increases[realm] || 0) + 1;
            return this.slide(`card-${card.id}`, this.getCardContainer(card), {
              delay: 100 * i,
            });
          }),
        ).then(() => {
          Object.entries(increases).forEach(([realm, inc]) => {
            this._counters[n.args.player_id][realm].incValue(inc);
          });
          this.notifqueue.setSynchronousDuration(800);
        });
      },

      notif_recruit(n) {
        debug("Notif: choose to recruit", n);

        $("resizable-central-board").classList.add("recruiting");
        Promise.all(
          n.args.cards.map((card, i) =>
            this.slide(`card-${card.id}`, "pending-recruit", {
              delay: 100 * i,
            }),
          ),
        ).then(() => {
          Promise.all(
            n.args.cards.map((card, i) => {
              let oCard = $(`card-${card.id}`);
              oCard.id += "_old";
              return this.wait(100 * i).then(() => {
                this.flipAndReplace(oCard, this.addCard(card));
                this.addCustomTooltip(
                  `card-${card.id}`,
                  this.getCardTooltip(card),
                  { forceRecreate: true },
                );
              });
            }),
          ).then(() => {
            this.notifqueue.setSynchronousDuration(100);
          });
        });
      },

      onEnteringStateRecruit(args) {
        $("resizable-central-board").classList.add("recruiting");
        if (!this.isCurrentPlayerActive()) return;

        this.addDangerActionButton("btnPass", _("Pass"), () =>
          this.takeAction("actPassChooseCharacter", {}),
        );

        args.choosableCards.forEach((card) => {
          this.onClick(`card-${card.id}`, () => {
            // TITAN
            if (
              card.type == "Titan" ||
              card.type == "Ouranos" ||
              card.type == "Gaia"
            ) {
              this.clientState(
                "recruitConfirmTitan",
                _("Please confirm Titan recruitment"),
                {
                  cardId: card.id,
                },
              );
            }
            // NORMAL
            else {
              this.clientState(
                "recruitChoosePlace",
                _("Where do you want to place that recruit?"),
                {
                  cardId: card.id,
                  places: args.availablePlaces,
                  fullCouncil: args.fullCouncil,
                },
              );
            }
          });
        });
      },

      onEnteringStateRecruitChoosePlace(args) {
        this.addCancelStateBtn();
        $(`card-${args.cardId}`).classList.add("selected");

        if (!args.fullCouncil) {
          let slot = args.places[0];
          this.takeAction("actChooseCharacter", {
            cardId: args.cardId,
            placeId: slot,
          });
          return;
        }

        args.places.forEach((slot) => {
          this.onClick(`throne-${this.player_id}-${slot}`, () => {
            this.takeAction("actChooseCharacter", {
              cardId: args.cardId,
              placeId: slot,
            });
          });
        });
      },

      onEnteringStateRecruitConfirmTitan(args) {
        this.addCancelStateBtn();
        $(`card-${args.cardId}`).classList.add("selected");

        this.addPrimaryActionButton("btnConfirm", _("Confirm"), () => {
          this.takeAction("actChooseCharacter", {
            cardId: args.cardId,
          });
        });
      },

      notif_chooseCharacter(n) {
        debug("Notif: choosing character to recruit", n);
        let card = n.args.card;
        this.slide(`card-${card.id}`, this.getCardContainer(card)).then(() => {
          $(`card-${card.id}`).classList.remove("selected");

          Promise.all(
            [...$("pending-recruit").querySelectorAll(".fiverealms-card")].map(
              (elt, i) =>
                this.slide(elt, "fiverealms-discard", {
                  delay: 100 * i,
                }),
            ),
          ).then(() =>
            $("resizable-central-board").classList.remove("recruiting"),
          );
        });
      },

      notif_passChooseCharacter(n) {
        debug("Notif: pass choosing character to recruit", n);

        Promise.all(
          [...$("pending-recruit").querySelectorAll(".fiverealms-card")].map(
            (elt, i) =>
              this.slide(elt, "fiverealms-discard", {
                delay: 100 * i,
              }),
          ),
        ).then(() =>
          $("resizable-central-board").classList.remove("recruiting"),
        );
      },

      onEnteringStateSteal(args) {
        // NOTHING TO STEAL/DESTROY
        if (!args.descSuffix == "impossible") {
          this.addSecondaryActionButton("btnPass", _("Pass"), () =>
            this.takeAction("actPass", {}),
          );
        }
        // STEAL/DESTROY
        else {
          if (args.canSteal) {
            this.addPrimaryActionButton(
              "btnSteal",
              this.formatString(_("Steal one <CASTLE>")),
              () => this.takeAction("actSteal", {}),
            );
          }

          args.destroyableCardsIds.forEach((cardId) => {
            this.onClick(`card-${cardId}`, () =>
              this.takeAction("actDestroy", { cardId }),
            );
          });
        }
      },

      notif_destroy(n) {
        debug("Notif: destroy character", n);
        this.slide(`card-${n.args.card.id}`, "fiverealms-discard");
      },

      notif_discardCharacter(n) {
        debug("Notif: discard character", n);
        this.slide(`card-${n.args.card.id}`, "fiverealms-discard");
      },

      onEnteringStateWitch(args) {
        this.addPrimaryActionButton("btnOpenDiscard", _("Open discard"), () =>
          this._discardModal.show(),
        );
        this._discardModal.show();

        let selectedCardId = null;
        let choosableCardIds = args.choosableCards.map((card) => card.id);
        let influencableCardIds = Object.keys(args.influencableCards).map((t) =>
          parseInt(t),
        );
        Object.values(args.cards).forEach((card) => {
          let cardId = card.id;
          if (!$(`card-${cardId}`)) {
            this.addCard(card);
          }
          let canBeRecruited = choosableCardIds.includes(cardId);
          let canBeInfluenced = influencableCardIds.includes(cardId);

          if (!canBeInfluenced && !canBeRecruited) return;

          this.onClick(`card-${cardId}`, () => {
            // Highlight
            if (selectedCardId) {
              $(`card-${selectedCardId}`).classList.remove("selected");
            }
            selectedCardId = cardId;
            $(`card-${selectedCardId}`).classList.add("selected");

            // RECRUIT
            this.addPrimaryActionButton(
              "btnRecruit",
              _("Recruit"),
              () =>
                this.clientState(
                  "witchRecruit",
                  _("Where do you want to recruit it?"),
                  {
                    cardId: selectedCardId,
                    places: args.availablePlaces,
                    fullCouncil: args.fullCouncil,
                  },
                ),
              "discard-modal-footer",
            );
            $("btnRecruit").classList.toggle("disabled", !canBeRecruited);

            // INFLUENCE
            this.addPrimaryActionButton(
              "btnInfluence",
              _("Influence"),
              () => {
                let realm = card.realm;
                // Imperial => choose column
                if (realm == "imperial") {
                  this.clientState(
                    "witchChooseInfluenceColumn",
                    _(
                      "You must choose on which column to place the Imperial card",
                    ),
                    args,
                  );
                }
                // Otherwise => auto
                else {
                  let influence = {};
                  influence[realm] = selectedCardId;

                  this.takeAction("actInfluenceWitch", {
                    influence: JSON.stringify(influence),
                  });
                }
              },
              "discard-modal-footer",
            );
            $("btnInfluence").classList.toggle("disabled", !canBeInfluenced);
          });
        });

        this.addSecondaryActionButton("btnPass", _("Pass"), () =>
          this.takeAction("actPass", {}),
        );
      },

      onEnteringStateWitchRecruit(args) {
        this.addCancelStateBtn();
        $(`card-${args.cardId}`).classList.add("selected");
        if (!args.fullCouncil) {
          let slot = args.places[0];
          this.takeAction("actChooseCharacter", {
            cardId: args.cardId,
            placeId: slot,
          });
          return;
        }

        args.places.forEach((slot) => {
          this.onClick(`throne-${this.player_id}-${slot}`, () => {
            this.takeAction("actChooseCharacter", {
              cardId: args.cardId,
              placeId: slot,
            });
          });
        });
      },

      onEnteringStateWitchChooseInfluenceColumn(args) {
        this.addCancelStateBtn();
        $(`card-${args.cardId}`).classList.add("selected");

        let getRealm = (realm) =>
          $(`board-${this.player_id}`).querySelector(
            `.influence-realm.realm-${realm}`,
          );
        const REALMS = ["reptiles", "felines", "raptors", "ursids", "marines"];
        let selectedRealm = null;

        REALMS.forEach((realm) => {
          // Cant place on empty col
          if (this._counters[this.player_id][realm].getValue() == 0) return;

          let influence = getRealm(realm);
          this.onClick(influence, () => {
            if (selectedRealm) {
              delete getRealm(selectedRealm).dataset.n;
            }
            selectedRealm = realm;
            getRealm(selectedRealm).dataset.n = 1;

            this.addPrimaryActionButton("btnConfirm", _("Confirm"), () => {
              let influence = {};
              influence[selectedRealm] = args.cardId;
              this.takeAction("actInfluenceWitch", {
                influence: JSON.stringify(influence),
              });
            });
          });
        });
      },

      ////////////////////////////////////////
      //  ____  _
      // |  _ \| | __ _ _   _  ___ _ __ ___
      // | |_) | |/ _` | | | |/ _ \ '__/ __|
      // |  __/| | (_| | |_| |  __/ |  \__ \
      // |_|   |_|\__,_|\__, |\___|_|  |___/
      //                |___/
      ////////////////////////////////////////

      setupPlayers() {
        let currentPlayerNo = 1;
        let nPlayers = 0;
        this._counters = {};
        this.forEachPlayer((player) => {
          let isCurrent = player.id == this.player_id;
          this.place(
            "tplPlayerPanel",
            player,
            `player_panel_content_${player.color}`,
            "after",
          );
          this.place("tplPlayerBoard", player, "fiverealms-main-container");

          let pId = player.id;
          this._counters[pId] = {};
          ["reptiles", "felines", "raptors", "ursids", "marines"].forEach(
            (realm) => {
              this._counters[pId][realm] = this.createCounter(
                `influence-${realm}-${pId}`,
                player.influence[realm],
              );
            },
          );

          // Useful to order boards
          nPlayers++;
          if (isCurrent) currentPlayerNo = player.no;
        });

        // Order them
        this.forEachPlayer((player) => {
          let order = ((player.no - currentPlayerNo + nPlayers) % nPlayers) + 1;
          $(`board-${player.id}`).style.order = order;
        });
      },

      getPlayerColor(pId) {
        return this.gamedatas.players[pId].color;
      },

      tplPlayerBoard(player) {
        debug(player);
        return `<div class='fiverealms-board' id='board-${player.id}' data-color='${player.color}'>
        <div class='player-board-fixed-size'>
          <div class='throne-row'>
            <div class='throne-slot' id='throne-${player.id}-0'></div>
            <div class='throne-slot' id='throne-${player.id}-1'></div>
            <div class='throne-slot' id='throne-${player.id}-2'></div>
            <div class='throne-slot' id='throne-${player.id}-3'></div>
            <div class='throne-slot' id='throne-${player.id}-4'></div>
          </div>
          <div class='influence-area'>  
            <div class='influence-realm realm-reptiles'><div class="influence-counter" id='influence-reptiles-${player.id}'></div></div>
            <div class='influence-realm realm-felines'><div class="influence-counter" id='influence-felines-${player.id}'></div></div>
            <div class='influence-realm realm-raptors'><div class="influence-counter" id='influence-raptors-${player.id}'></div></div>
            <div class='influence-realm realm-ursids'><div class="influence-counter" id='influence-ursids-${player.id}'></div></div>
            <div class='influence-realm realm-marines'><div class="influence-counter" id='influence-marines-${player.id}'></div></div>
          </div>
          <div class='titans-area'></div>
        </div>
      </div>`;
      },

      /**
       * Player panel
       */

      tplPlayerPanel(player) {
        return `<div class='fiverealms-panel'>
        <div class='fiverealms-player-infos'>
        </div>
      </div>`;
      },

      /**
       * Use this tpl for any counters that represent qty of meeples in "reserve", eg xtokens
       */
      tplResourceCounter(player, res, prefix = "") {
        return this.formatString(`
        <div class='player-resource resource-${res}'>
          <span id='${prefix}counter-${player.id}-${res}' 
            class='${prefix}resource-${res}'></span>${this.formatIcon(res)}
          <div class='reserve' id='${prefix}reserve-${player.id}-${res}'></div>
        </div>
      `);
      },

      gainLoseScore(pId, n, targetSource = null) {
        if (this.isFastMode()) {
          this.scoreCtrl[pId].incValue(n);
          return Promise.resolve();
        }

        let elem = `<div id='score-animation'>
        ${Math.abs(n)}
        <i class='svgicon-castle'></i>
      </div>`;
        $("page-content").insertAdjacentHTML("beforeend", elem);

        // Score animation
        if (n > 0) {
          return this.slide("score-animation", `player_score_${pId}`, {
            from: targetSource || this.getVisibleTitleContainer(),
            destroy: true,
            phantom: false,
            duration: 1100,
          }).then(() => this.scoreCtrl[pId].incValue(n));
        } else {
          this.scoreCtrl[pId].incValue(n);
          return this.slide(
            "score-animation",
            targetSource || this.getVisibleTitleContainer(),
            {
              from: `player_score_${pId}`,
              destroy: true,
              phantom: false,
              duration: 1100,
            },
          );
        }
      },

      notif_newCastleCards(n) {
        debug("Notif: gaining score", n);
        this.gainLoseScore(n.args.player_id, n.args.deltaScore);
      },

      notif_steal(n) {
        debug("Notif: stealing castle card", n);
        let victimId = n.args.player_id2;
        this.scoreCtrl[victimId].incValue(-n.args.incScore);
        this.gainLoseScore(
          n.args.player_id,
          n.args.incScore,
          `player_score_${victimId}`,
        );
      },

      ////////////////////////////////////////////////////////
      //    ____              _
      //   / ___|__ _ _ __ __| |___
      //  | |   / _` | '__/ _` / __|
      //  | |__| (_| | | | (_| \__ \
      //   \____\__,_|_|  \__,_|___/
      //////////////////////////////////////////////////////////

      setupCards() {
        // This function is refreshUI compatible
        let cards = this.gamedatas.cards;
        let allCards = [
          ...cards.alkane,
          cards.deck,
          ...cards.visible,
          cards.discard,
        ];
        let cardIds = allCards.map((card) => {
          if (!card) return null;
          if (!$(`card-${card.id}`)) {
            this.addCard(card);
          }

          let o = $(`card-${card.id}`);
          if (!o) return null;

          let container = this.getCardContainer(card);
          if (o.parentNode != $(container)) {
            dojo.place(o, container);
          }
          o.dataset.state = card.state;

          return card.id;
        });
        document
          .querySelectorAll('.fiverealms-card[id^="card-"]')
          .forEach((oCard) => {
            if (!cardIds.includes(parseInt(oCard.getAttribute("data-id")))) {
              this.destroy(oCard);
            }
          });

        $("fiverealms-deck").dataset.n = cards.deckN;
      },
      decDeckCount(n = 1) {
        $("fiverealms-deck").dataset.n = $("fiverealms-deck").dataset.n - n;
      },

      addCard(card, location = null) {
        if ($("card-" + card.id)) return;

        let o = this.place(
          "tplCard",
          card,
          location == null ? this.getCardContainer(card) : location,
        );
        let tooltipDesc = this.getCardTooltip(card);
        if (tooltipDesc != null) {
          this.addCustomTooltip(o.id, tooltipDesc);
        }

        return o;
      },

      getCardTooltip(card) {
        card.uid = card.id + "tooltip";

        let desc = "";
        const REALMS = {
          reptiles: _("Reptiles"),
          felines: _("Felines"),
          raptors: _("Raptors"),
          ursids: _("Ursids"),
          marines: _("Marines"),

          religious: _("Religious"),
          imperial: _("Imperial"),
        };
        desc += `<h4 class='realm-desc'>${_("Realm:")} ${
          REALMS[card.realm]
        }</h4>`;

        if (card.type == "King") {
          desc += `<h4>${_("King")}</h4>
          <p>
            ${_(
              "At the end of the game, if you strictly have more influence in the Realm of this King: earn 3 <CASTLE>.",
            )}
          </p>`;
        }
        if (card.type == "Reine") {
          desc += `<h4>${_("Queen")}</h4>
          <p>
            ${_(
              "Throughout the game, when you influence the 3rd , 4th and 5th Banner of the Realm of this Queen: earn 1 <CASTLE>.",
            )}
          </p>`;
        }
        if (card.type == "Witch") {
          desc += `<h4>${_("Witch")}</h4>
          <p>
            ${_(
              "Throughout the game, when you influence the 3rd and 5th Banner of the Realm of this Witch: look at all the cards in the discard pile, and pick 1 card that you may RECRUIT or INFLUENCE.",
            )}
          </p>`;
        }
        if (card.type == "Warrior") {
          desc += `<h4>${_("Warrior")}</h4>
          <p>
            ${_(
              "Throughout the game, when you influence the 4th Banner of the Realm of this Warrior: steal from your opponent or destroy an opposing Character or Titan. If destroyed, put it in the discard pile.",
            )}
          </p>`;
        }
        if (card.type == "Titan") {
          desc += `<h4>${_("Titan")}</h4>
          <p>
          ${_("When recruited, earn 1 <CASTLE>.")}
          <br />
          ${_(
            "Throughout the game, if you recruit a 5th different Titan: you immediately win the game.",
          )}
          </p>`;
        }

        if (card.type == "Popess") {
          desc += `<h4>${_("Popess")}</h4>
          <p>
          ${_(
            "Throughout the game, when you influence the 4th banner of any Realm: earn 2 <CASTLE>.",
          )}
          </p>`;
        }
        if (card.type == "Warrior Monk") {
          desc += `<h4>${_("Warrior Monk")}</h4>
          <p>
          ${_(
            "When recruited, steal from your opponent or destroy an opposing Character or Titan. If destroyed, put it in the discard pile.",
          )}
          <br />
          ${_(
            "At the end of the game, if you strictly have more Warriors and Warrior Monk, steal 2 <CASTLE> from your opponent.",
          )}
          </p>`;
        }
        if (card.type == "Gaia") {
          desc += `<h4>${_("Gaia (Titan)")}</h4>
          <p>
          ${_("When recruited, earn 1 <CASTLE>.")}
          <br />
          ${_(
            "Throughout the game, if you recruit a 5th different Titan: you immediately win the game.",
          )}
          <br />
          ${_(
            "At the end of the game, if you strictly have more Titans, Gaia included, earn 1 <CASTLE>.",
          )}
          </p>`;
        }
        if (card.type == "Ouranos") {
          desc += `<h4>${_("Ouranos (Titan)")}</h4>
          <p>
          ${_("When recruited, earn 1 <CASTLE>.")}
          <br />
          ${_(
            "Throughout the game, if you recruit a 5th different Titan: you immediately win the game.",
          )}
          </p>`;
        }

        if (card.type == "Colonel") {
          desc += `<h4>${_("Colonel")}</h4>
          <p>
          ${_(
            "Throughout the game, when you create 1 line of 5 influences: earn 1 <CASTLE>.",
          )}
          <br />
          ${_(
            "At the end of the game, if you strictly have more lines of influence: earn 2 <CASTLE>.",
          )}
          </p>`;
        }
        if (card.type == "General") {
          desc += `<h4>${_("General")}</h4>
          <p>
          ${_(
            "Throughout the game, when you create 1 line of 5 influences: Immediately play a new turn.",
          )}
          </p>`;
        }
        if (card.type == "Captain") {
          desc += `<h4>${_("Captain")}</h4>
          <p>
          ${_(
            "When recruited, earn 1 <CASTLE> by Realm for which you have strictly more influence.",
          )}
          <br />
          ${_(
            "At the end of the game, earn 1 <CASTLE> by Realm for which you have strictly more influence.",
          )}
          </p>`;
        }
        if (card.type == "Marshal") {
          desc += `<h4>${_("Marshal")}</h4>
          <p>
          ${_(
            "At the end of the game, if you strictly have more Characters from the Imperial Order: earn 3 <CASTLE>.",
          )}
          </p>`;
        }

        return `<div class='card-tooltip'>
        ${this.tplCard(card)}
        <div class='card-desc'>
          <h4 class='card-id'>Id: ${card.id}</h4>
          ${this.formatString(desc)}
        </div>
      </div>  
      `;
      },

      tplCard(card) {
        return `<div class="fiverealms-card" id="card-${card.id}" data-id="${card.id}" data-type="${card.type}" data-realm="${card.realm}"></div>`;
      },

      getCardContainer(card) {
        let t = card.location.split("_");
        if (card.location == "alkane") {
          return $(`alkane-${card.x}-${card.y}`);
        }
        if (card.location == "deck") {
          return $("fiverealms-deck");
        }
        if (card.location == "discard") {
          return $("fiverealms-discard");
        }
        if (card.location == "hand") {
          return $("pending-recruit");
        }
        if (card.location == "Throne" || card.location == "council") {
          return $(`throne-${card.playerId}-${card.state}`);
        }
        if (card.location == "influence") {
          return $(`influence-${card.influenceColumn}-${card.playerId}`)
            .parentNode;
        }
        if (card.location == "titans") {
          return $(`board-${card.playerId}`).querySelector(".titans-area");
        }

        console.error("Trying to get container of a card", card);
        return "game_play_area";
      },

      notif_newAlkane(n) {
        debug("Notif: new alkane cards", n);

        if (n.args.deck) {
          this.addCard(n.args.deck);
        }

        // First card of deck already exist
        this.decDeckCount();

        n.args.alkane.forEach((card, i) => {
          if (!$(`card-${card.id}`)) {
            this.addCard(card, "fiverealms-deck");
            this.decDeckCount();
          }

          this.wait(100 * i).then(() => {
            this.slide(`card-${card.id}`, this.getCardContainer(card));
          });
        });
      },

      notif_adjustAlkane(n) {
        debug("Notif: adjusting alkane", n);

        let moving = false;
        n.args.alkane.forEach((card) => {
          let container = this.getCardContainer(card);
          if (container != $(`card-${card.id}`).parentNode) {
            moving = true;
            this.slide(`card-${card.id}`, container);
          }
        });

        this.notifqueue.setSynchronousDuration(moving ? 1200 : 10);
      },

      ////////////////////////////////////////////////////////////
      // _____                          _   _   _
      // |  ___|__  _ __ _ __ ___   __ _| |_| |_(_)_ __   __ _
      // | |_ / _ \| '__| '_ ` _ \ / _` | __| __| | '_ \ / _` |
      // |  _| (_) | |  | | | | | | (_| | |_| |_| | | | | (_| |
      // |_|  \___/|_|  |_| |_| |_|\__,_|\__|\__|_|_| |_|\__, |
      //                                                 |___/
      ////////////////////////////////////////////////////////////

      /**
       * Replace some expressions by corresponding html formating
       */
      formatIcon(name, n = null, lowerCase = true) {
        let type = lowerCase ? name.toLowerCase() : name;

        return (n ? n + "&nbsp;" : "") + `<i class='svgicon-${type}'></i>`;
      },

      formatString(str) {
        const ICONS = [
          "CASTLE",
          "URSID",
          "MARINE",
          "FELINE",
          "REPTILE",
          "IMPERIAL",
          "RELIGIOUS",
        ];

        ICONS.forEach((name) => {
          // WITHOUT BONUS / WITH TEXT
          const regex = new RegExp("<" + name + ":([^>]+)>", "g");
          str = str.replaceAll(regex, this.formatIcon(name, "$1"));
          // WITHOUT TEXT
          str = str.replaceAll(
            new RegExp("<" + name + ">", "g"),
            this.formatIcon(name),
          );
        });

        return str;
      },

      /**
       * Format log strings
       *  @Override
       */
      format_string_recursive(log, args) {
        try {
          if (log && args && !args.processed) {
            args.processed = true;

            log = this.formatString(_(log));

            // if (args.amount_money !== undefined) {
            //   args.amount_money = this.formatIcon('money', args.amount_money);
            // }
          }
        } catch (e) {
          console.error(log, args, "Exception thrown", e.stack);
        }

        return this.inherited(arguments);
      },

      ////////////////////////////////////////////////////////
      //  ___        __         ____                  _
      // |_ _|_ __  / _| ___   |  _ \ __ _ _ __   ___| |
      //  | || '_ \| |_ / _ \  | |_) / _` | '_ \ / _ \ |
      //  | || | | |  _| (_) | |  __/ (_| | | | |  __/ |
      // |___|_| |_|_|  \___/  |_|   \__,_|_| |_|\___|_|
      ////////////////////////////////////////////////////////

      setupInfoPanel() {
        dojo.place(this.tplConfigPlayerBoard(), "player_boards", "first");

        let chk = $("help-mode-chk");
        dojo.connect(chk, "onchange", () => this.toggleHelpMode(chk.checked));
        this.addTooltip("help-mode-switch", "", _("Toggle help/safe mode."));

        this._settingsModal = new customgame.modal("showSettings", {
          class: "fiverealms_popin",
          closeIcon: "fa-times",
          title: _("Settings"),
          closeAction: "hide",
          verticalAlign: "flex-start",
          contentsTpl: `<div id='fiverealms-settings'>
           <div id='fiverealms-settings-header'></div>
           <div id="settings-controls-container"></div>
         </div>`,
        });
      },

      tplConfigPlayerBoard() {
        return `
 <div class='player-board' id="player_board_config">
   <div id="player_config" class="player_board_content">
     <div class="player_config_row">
      <div id="show-scoresheet">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
          <g class="fa-group">
            <path class="fa-secondary" fill="currentColor" d="M0 192v272a48 48 0 0 0 48 48h352a48 48 0 0 0 48-48V192zm324.13 141.91a11.92 11.92 0 0 1-3.53 6.89L281 379.4l9.4 54.6a12 12 0 0 1-17.4 12.6l-49-25.8-48.9 25.8a12 12 0 0 1-17.4-12.6l9.4-54.6-39.6-38.6a12 12 0 0 1 6.6-20.5l54.7-8 24.5-49.6a12 12 0 0 1 21.5 0l24.5 49.6 54.7 8a12 12 0 0 1 10.13 13.61zM304 128h32a16 16 0 0 0 16-16V16a16 16 0 0 0-16-16h-32a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16zm-192 0h32a16 16 0 0 0 16-16V16a16 16 0 0 0-16-16h-32a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16z" opacity="0.4"></path>
            <path class="fa-primary" fill="currentColor" d="M314 320.3l-54.7-8-24.5-49.6a12 12 0 0 0-21.5 0l-24.5 49.6-54.7 8a12 12 0 0 0-6.6 20.5l39.6 38.6-9.4 54.6a12 12 0 0 0 17.4 12.6l48.9-25.8 49 25.8a12 12 0 0 0 17.4-12.6l-9.4-54.6 39.6-38.6a12 12 0 0 0-6.6-20.5zM400 64h-48v48a16 16 0 0 1-16 16h-32a16 16 0 0 1-16-16V64H160v48a16 16 0 0 1-16 16h-32a16 16 0 0 1-16-16V64H48a48 48 0 0 0-48 48v80h448v-80a48 48 0 0 0-48-48z"></path>
          </g>
        </svg>
      </div>
      
       <div id="help-mode-switch">
         <input type="checkbox" class="checkbox" id="help-mode-chk" />
         <label class="label" for="help-mode-chk">
           <div class="ball"></div>
         </label>

         <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g></svg>
       </div>

       <div id="show-settings">
         <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
           <g>
             <path class="fa-secondary" fill="currentColor" d="M638.41 387a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4L602 335a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6 12.36 12.36 0 0 0-15.1 5.4l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 44.9c-29.6-38.5 14.3-82.4 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79zm136.8-343.8a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4l8.2-14.3a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6A12.36 12.36 0 0 0 552 7.19l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 45c-29.6-38.5 14.3-82.5 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79z" opacity="0.4"></path>
             <path class="fa-primary" fill="currentColor" d="M420 303.79L386.31 287a173.78 173.78 0 0 0 0-63.5l33.7-16.8c10.1-5.9 14-18.2 10-29.1-8.9-24.2-25.9-46.4-42.1-65.8a23.93 23.93 0 0 0-30.3-5.3l-29.1 16.8a173.66 173.66 0 0 0-54.9-31.7V58a24 24 0 0 0-20-23.6 228.06 228.06 0 0 0-76 .1A23.82 23.82 0 0 0 158 58v33.7a171.78 171.78 0 0 0-54.9 31.7L74 106.59a23.91 23.91 0 0 0-30.3 5.3c-16.2 19.4-33.3 41.6-42.2 65.8a23.84 23.84 0 0 0 10.5 29l33.3 16.9a173.24 173.24 0 0 0 0 63.4L12 303.79a24.13 24.13 0 0 0-10.5 29.1c8.9 24.1 26 46.3 42.2 65.7a23.93 23.93 0 0 0 30.3 5.3l29.1-16.7a173.66 173.66 0 0 0 54.9 31.7v33.6a24 24 0 0 0 20 23.6 224.88 224.88 0 0 0 75.9 0 23.93 23.93 0 0 0 19.7-23.6v-33.6a171.78 171.78 0 0 0 54.9-31.7l29.1 16.8a23.91 23.91 0 0 0 30.3-5.3c16.2-19.4 33.7-41.6 42.6-65.8a24 24 0 0 0-10.5-29.1zm-151.3 4.3c-77 59.2-164.9-28.7-105.7-105.7 77-59.2 164.91 28.7 105.71 105.7z"></path>
           </g>
         </svg>
       </div>
     </div>
   </div>
 </div>
 `;
      },

      updatePlayerOrdering() {
        this.inherited(arguments);
        dojo.place("player_board_config", "player_boards", "first");
      },

      onLoadingComplete() {
        this.updateLayout();
        this.inherited(arguments);
      },

      onScreenWidthChange() {
        if (this.settings) this.updateLayout();
      },

      updateLayout() {
        return; // TODO

        if (!this.settings) return;
        const ROOT = document.documentElement;

        const WIDTH = $("fiverealms-main-container").getBoundingClientRect()[
          "width"
        ];
        const HEIGHT =
          (window.innerHeight ||
            document.documentElement.clientHeight ||
            document.body.clientHeight) - 62;
        const BOARD_WIDTH = 1650;
        const BOARD_HEIGHT = 750;

        let widthScale =
            ((this.settings.boardWidth / 100) * WIDTH) / BOARD_WIDTH,
          heightScale = HEIGHT / BOARD_HEIGHT,
          scale = Math.min(widthScale, heightScale);
        ROOT.style.setProperty("--boardScale", scale);

        const PLAYER_BOARD_WIDTH = 1650;
        const PLAYER_BOARD_HEIGHT = 840;
        widthScale =
          ((this.settings.playerBoardWidth / 100) * WIDTH) / PLAYER_BOARD_WIDTH;
        heightScale = HEIGHT / PLAYER_BOARD_HEIGHT;
        scale = Math.min(widthScale, heightScale);
        ROOT.style.setProperty("--playerBoardScale", scale);
      },
    },
  );
});
