<?php

namespace blink {

	/*
	 * Start application.
	 */
	main();

	function main() {
		/*
		 * Pre-configuration.
		 */

		// We use UTC for everything internally.
		date_default_timezone_set('UTC');

		// Classes are arranged by their namespace.
		spl_autoload_extensions('.php');
		spl_autoload_register();

		// Include additional functions.
		require __DIR__ . '/functions.php';

		/*
		 * Create the root.
		 */
		$class = 'blink\\root';

		spl_autoload($class);
		$root = new $class();

		// Run.
		$root->application->routine(__DIR__);
	}
}
