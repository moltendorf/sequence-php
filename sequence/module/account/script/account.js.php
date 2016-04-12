/**
 * Document    : account.js
 * Created on  : January 03, ‎2016, ‏‎14:12:48
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Account management scripts.
 */

/**
 * @type Account
 */
var account;

(function () {
	let tag = "[account]";

	//import { Module } from '../../module';

	//import { sequence } from '../../sequence';

	class Account extends Module {
		constructor() {
			super();

			this.requireWebSocket = true;
		}
	}

	//export { account, account as default };
	window.account = new Account();
})();
