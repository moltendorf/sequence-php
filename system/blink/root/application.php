<?php

namespace blink\root {
	use blink as b;
	use blink\functions as f;

	class application extends b\bind {

		/*
		 * Implementation of b\bind.
		 */

		/**
		 *
		 */
		protected function construct() {

		}

		/*
		 * End implementation of b\bind.
		 */

		/**
		 * @param string $system
		 */
		public function start($system) {
			$root = $this->root;

			// Store this internally so it doesn't get tampered with.
			$start = $_SERVER['REQUEST_TIME_FLOAT']*1e6;

			// Load our settings.
			$settings = $this->settings($system);

			// Set up our paths.
			$root->path->settings($system, $settings['path']);

			// Connect to the database.
			$this->connect($settings['connection']);

			// Define our debugging constant.
			if (isset($root->settings['debug'])) {
				define('blink\\debug', (boolean) $root->settings['debug']);
			} else {
				define('blink\\debug', false);
			}

			// Parse the request.
			$root->handler->parse();

			// Run the module.
			$root->handler->load();

			// Calculate the total runtime of the script.
			$total = microtime(true)*1e6-$start;

			f\dump(number_format($total).'µs');
		}

		/**
		 *
		 * @param string $system
		 */
		private function settings($system) {
			$settings = f\file_get_json($system.'/settings.json');

			if ($settings === false) {
				throw new \Exception('NO_SETTINGS_FILE');
			}

			return $settings;
		}

		/**
		 *
		 * @param array $settings
		 */
		private function connect($settings) {
			$root = $this->root;

			// Construct the proper database abstraction layer.
			$class = __NAMESPACE__.'\\database\\'.$settings['type'];

			$root->database = new $class($root);
			$root->database->connect($settings);
		}
	}
}
