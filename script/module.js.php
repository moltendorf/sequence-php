/**
 * Document    : module.js.php
 * Created on  : ‎‎March 06, 2016, 15:11:52
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Sequence base module class.
 */

/**
 * @type Module
 */
var Module;

(function () {
	let tag = "[module]";

	class Module {
		constructor() {
			this.requireWebSocket = false;

			sequence.registerModule(this);
		}
	}

	//export { module, module as default };
	window.Module = Module;
})();
