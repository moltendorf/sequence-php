/**
 * Document    : language.js
 * Created on  : ‎‎‎March ‎6, ‎2016, ‏‎15:05:16
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Sequence language core.
 */

/**
 *
 * @param {string} key The language key to use.
 * @param {...*} data Additional data to pass to the format function.
 * @returns {string}
 */
function language(key, ...data) {
	let tag = "[language]";

	let map = <?= $l->json() ?>;

	if (key in map) {
		if (data.length > 0) {
			return vsprintf(map[key], data);
		} else {
			return map[key];
		}
	} else {
		map[key] = key;

		console.warn(tag, language("CONSOLE_WARN_LANGUAGE_NOT_EXIST", key));

		return key;
	}
}

//export { language, language as default };
