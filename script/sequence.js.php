/**
 * Document    : sequence.js
 * Created on  : ‎‎October ‎17, ‎2015, ‏‎21:58:49
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Sequence engine core.
 */

/**
 * @type Sequence
 */
var sequence;

(function () {
	let tag = "[sequence]";

	//import { language } from 'language';

	class Sequence {
		/**
		 * @param data
		 */
		constructor(data) {
			this.webSocketActive   = false;
			this.registeredModules = {};

			if (!("version" in data)) {
				data.version = "unknown";
			}

			if (!("query" in data)) {
				data.query = "/query/";
			}

			data.query = (location.protocol === "https:" ? "wss://" : "ws://") + data.query;

			console.log(tag, language("CONSOLE_INITIALIZE", data.version));

			this.data = data;
		}

		/**
		 * @param {Module} module
		 */
		registerModule(module) {
			let runnable = function () {
				// Check if the module was registered already.
				if (module.constructor.name in this.registeredModules) {
					console.warn(tag, language("CONSOLE_WARN_MODULE_REGISTERED", module.constructor.name));

					return;
				}

				this.registeredModules[module.constructor.name] = module;

				// Start our universal connection to the server if the module uses it and we haven't started it yet.
				if (!this.webSocketActive && module.requireWebSocket) {
					setTimeout(this.activateWebSocket.bind(this), 0);

					this.webSocketActive = true;
				}

				console.log(tag, language("CONSOLE_MODULE_REGISTER", module.constructor.name));
			};

			setTimeout(runnable.bind(this), 0);
		}

		activateWebSocket() {
			console.log(tag, language("CONSOLE_ACTIVATE_WEBSOCKET", this.data.query));

			var ws = new WebSocket("wss://query.element.town/");
		}
	}

	//export { sequence, sequence as default };
	window.sequence = new Sequence(<?= $v['core_script_data'] ?>);
})();
