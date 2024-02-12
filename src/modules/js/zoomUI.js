define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
	return declare("fiverealms.zoomUI", null, {
	  setupZoomUI() {
		this._propertiesUI = {
		//   "--map-size": {// css var properties
		// 	ratio: 1 / 4, //ratio between screen and card width
		// 	name: _("Map"),//displayable name
		//   },
		//   "--card-width": {
		// 	ratio: 1 / 12, //ratio between screen and card width
		// 	name: _("Card"),
		//   },
		};
  
		onresize = (event) => {
		  this.adaptWidth();
		};
  
		this.place("zoomPanel_tpl", null, "player_boards");
  
		let chk = $("help-mode-chk");
		//display initial value
		chk.checked = window.localStorage.getItem("helpMode") === "true";
		this.toggleHelpMode(chk.checked);
		
		dojo.connect(chk, "onchange", () => this.toggleHelpMode(chk.checked));
		this.addTooltip("help-mode-switch", "", _("Toggle help mode."));
  
		for (const property in this._propertiesUI) {
		  dojo.connect($("zoom_value" + property), "oninput", () => {
			// debug('zoom changed', $('zoom_value').value);
			window.localStorage.setItem(
			  "FRMS_zoom" + property,
			  $("zoom_value" + property).value
			);
			this.adaptWidth();
		  });
		}
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
  
	  //create tooltipable zones (if needed) and add curtomtooltip
	  createTooltip(card, score = -1) {
		debug(card);
		// if (!(cardId in this._cards)) {
		//   debug("Issue with cardId data, can't create tooltip");
		//   return;
		// }
		// const card = this._cards[cardId];
  
		this.addCustomTooltip(
		  this.getCardId(card),
		  "<div>Here, soon, something great</div>"
		);
	  },
  
	  zoomPanel_tpl() {
		let zooms = "";
		for (const property in this._propertiesUI) {
		  const initialValue =
			window.localStorage?.getItem("FRMS_zoom" + property) ?? 100;
		  zooms += `<div>${this._propertiesUI[property].name} :
		  <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM136 184c-13.3 0-24 10.7-24 24s10.7 24 24 24H280c13.3 0 24-10.7 24-24s-10.7-24-24-24H136z"/></svg>
		  <input type="range" min="50" max="200" value="${initialValue}" class="slider" id="zoom_value${property}">
		   <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM184 296c0 13.3 10.7 24 24 24s24-10.7 24-24V232h64c13.3 0 24-10.7 24-24s-10.7-24-24-24H232V120c0-13.3-10.7-24-24-24s-24 10.7-24 24v64H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h64v64z"/></svg>
		  </div>`;
		}
  
		return `<div class='player-board' id="player_board_config">
		<div class="player_config_row">
		 <div id="help-mode-switch">
		   <input type="checkbox" class="checkbox" id="help-mode-chk" />
		   <label class="label" for="help-mode-chk">
			 <div class="ball"></div>
		   </label>
  
		   <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g></svg>
		 </div>
		 ${zooms}
		 </div>
		</div>`;
	  },
  
	  /**
	   * Called each time the game is repaint to adapt width element
	   */
	  adaptWidth() {
		// debug("adaptWidth");
		const boxRect = $("page-content").getBoundingClientRect();
		const r = document.querySelector(":root");
  
		for (let property in this._propertiesUI) {
		  let value = boxRect.width * this._propertiesUI[property].ratio;
		  if ($("zoom_value" + property)) {
			value *= $("zoom_value" + property).value / 100;
		  }
		  r.style.setProperty(property, value + "px");
		}
  
		// r.style.setProperty("--card-in-hand-width", cardInHandWidth + "px");
		// r.style.setProperty("--card-on-table-width", cardInHandWidth + "px");
	  },
	});
  });
  