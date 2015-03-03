<?php

namespace sequence {

	/*
	 * Start application.
	 */
	main();

	function main() {
		/*
		 * Pre-configuration.
		 */

		$systemPath = __DIR__;
		$homePath = $_SERVER['HOME'] . '/system';
		$webPath = $_SERVER['HOME'] . '/live';

		// Attempt to auto-load files from
		set_include_path(implode(PATH_SEPARATOR, [$homePath, $systemPath]));

		// Classes are arranged by their namespace.
		spl_autoload_extensions('.php');
		spl_autoload_register();

		// We use UTC for everything internally.
		date_default_timezone_set('UTC');

		// Include additional functions.
		require $systemPath . '/functions.php';

		/*
		 * Create the root.
		 */
		$class = 'sequence\\root';

		spl_autoload($class);
		$root = new $class();

		// Run.
		$root->application->routine($systemPath, $homePath, $webPath);
	}
}
