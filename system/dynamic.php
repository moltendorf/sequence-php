<?php

namespace blink {
	use blink\functions as f;

	require __DIR__ . '/functions.php';

	/*
	 * For debugging purposes.
	 */
	if ($_SERVER['REQUEST_URI'] === '/dynamic?info') {
		phpinfo();

		return;
	}



	/*
	 * Pre-configuration.
	 */
	// We use UTC for everything internally.
	date_default_timezone_set('UTC');

	// Classes are arranged by their namespace.
	spl_autoload_extensions('.php');
	spl_autoload_register();

	/*
	 * Start application.
	 */
	main();

	function main() {
		$root = new root();

		// Run.
		$root->application->routine(__DIR__);
	}
}
