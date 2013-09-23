<?php

namespace blink\root {

	use blink as b,
	 blink\functions as f;

	class application {

		use b\broadcaster;

		/**
		 *
		 * @var string
		 */
		protected $content;

		/**
		 *
		 * @var b\root
		 */
		protected $root;

		/**
		 * @param string $system
		 */
		public function routine($system) {
			$root = $this->root;

			// Store this internally so it doesn't get tampered with.
			$start = $_SERVER['REQUEST_TIME_FLOAT'] * 1e6;

			/*
			 * Output buffering is used to prevent accidental output.
			 * If there is any output, an error is thrown with the output dumped to the page in debug mode.
			 * If the output buffering level is different by the end of the script, and error is thrown.
			 */

			$level = ob_get_level();
			ob_start();

			// Load our settings.
			$settings = require $system . '/settings.php';

			if ($settings === false) {
				throw new \Exception('NO_SETTINGS_FILE');
			}

			// Set up our paths.
			$root->path->settings($system, $settings['path']);

			$this->broadcast('start');

			// Construct the proper database abstraction layer.
			$class = __NAMESPACE__ . '\\database\\' . (isset($settings['plugin']) ? $settings['plugin'] : 'pdo');

			$root->database = new $class($root, $settings['database']);

			// Define our debugging constant.
			if (isset($root->settings['debug'])) {
				define('blink\\debug', (boolean) $root->settings['debug']);
			} else {
				define('blink\\debug', false);
			}

			$this->broadcast('generate');

			// Parse the request.
			$root->handler->parse();
			$root->handler->load();

			$this->broadcast('parse');

			if (b\debug) {
				// This should be the only output statement.
				$this->parse();

				$this->broadcast('close');

				// Close connection to the database.
				$root->database->close();

				if (ob_get_length()) {
					$root->template->error(ob_get_contents());

					$this->headers();
				}

				// This will be about as accurate as it can be from within PHP.
				header('X-Debug-Execution-Time: ' . number_format(microtime(true) * 1e6 - $start) . utf8_decode('µs'));

				$this->output();

				// We do not call fastcgi_finish_request() to ensure every bit of detail makes its way out.
			} else {
				$this->parse();
				$this->output();

				fastcgi_finish_request();

				$this->broadcast('close');

				// Close connection to the database.
				$root->database->close();
			}
		}

		/**
		 *
		 */
		public function parse() {
			$root = $this->root;

			$root->template->header();
			$root->template->body();

			$this->headers();
		}

		/**
		 *
		 */
		public function headers() {
			$this->content = ob_get_contents();
			$digest = base64_encode(pack('H*', md5($this->content)));
			$this->content = gzencode($this->content);

			header('Content-Encoding: gzip');
			header('Content-Length: ' . mb_strlen($this->content, '8bit'));
			header('Content-MD5: ' . $digest);
			header('Content-Type: text/html; charset=utf-8');
		}

		/**
		 *
		 */
		public function output() {
			ob_end_clean();

			echo $this->content;
		}

	}

}
