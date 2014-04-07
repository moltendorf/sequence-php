<?php

namespace blink\root {

	use blink as b;

	/**
	 *
	 * @property-read array $debug
	 */
	class application {

		use b\broadcaster;

		/**
		 *
		 * @var array
		 */
		public static $messages = ['connect', 'module', 'template', 'close'];

		/**
		 *
		 * @var array
		 */
		public $settings = [];

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
		 *
		 * @var array
		 */
		private $debug = false;

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->root = $root;
		}

		/**
		 *
		 * @param string $name
		 *
		 * @return mixed
		 */
		public function __get($name) {
			switch ($name) {
				case 'debug':
					return $this->debug;
			}

			return null;
		}

		/**
		 *
		 * @param string $system
		 *
		 * @throws
		 */
		public function routine($system) {
			$root = $this->root;

			// Store this internally so it doesn't get tampered with.
			$start = $_SERVER['REQUEST_TIME_FLOAT'] * 1e6;

			/*
			 * Output buffering is used to prevent accidental output.
			 * If there is any output, an error is thrown with the output dumped to the page in debug mode.
			 * If the output buffering level is different by the end of the script, an error is thrown.
			 */

			ob_start();

			$level = ob_get_level();

			/*
			 * Set up the error handler so that all errors are handled one at a time in a clean fashion.
			 */

			set_error_handler(function ($errno, $errstr, $errfile, $errline) {
				throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
			});

			/*
			 * For debugging purposes.
			 */

			$debug = $system . '/debug';

			if (is_dir($debug)) {
				define('blink\\debug', true);

				$this->debug = [];

				foreach (glob($debug . '/*.php') as $file) {
					// Manually including each file as the namespace the classes are in would cause the autoloader to look in the wrong directory.
					require $file;

					$class = 'blink\\debug\\' . substr($file, strrpos($file, '/') + 1, -4);

					if (class_exists($class, false)) {
						// Instantiate the debug class.
						$this->debug[] = new $class($root);
					}
				}

				unset($class);
			} else {
				define('blink\\debug', false);
			}

			unset($debug);

			$this->bind($root);

			// Load our settings.
			$settings = require $system . '/settings.php';

			if ($settings === false) {
				throw new \Exception('NO_SETTINGS_FILE');
			}

			if ($settings['application']) {
				$this->settings = & $settings['application'];
			}

			// Set up our paths.
			$root->path->settings($system, $settings['path']);

			$this->broadcast('connect');

			// Construct the proper database abstraction layer.
			$class = __NAMESPACE__ . '\\database\\' . (isset($settings['plugin']) ? $settings['plugin'] : 'pdo');

			$root->database = new $class($root, $settings['database']);

			$this->broadcast('module');

			// Parse the request.
			$root->handler->parse();
			$root->handler->load();

			$this->broadcast('template');

			if (b\debug) {
				if (ob_get_level() != $level) {
					$root->template->error(new \Exception('OUTPUT_BUFFER_LEVEL_DIFFERS'));

					$this->headers();
				} else {
					if (ob_get_length()) {
						$root->template->error();

						$this->headers();
					} else {
						// This should be the only output statement.
						$this->parse();
					}
				}

				$this->broadcast('close');

				// Close connection to the database.
				$root->database->close();

				if (ob_get_length()) {
					$root->template->error();

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
		public function headers() {
			$this->content = ob_get_contents();
			ob_clean();

			$digest        = base64_encode(pack('H*', md5($this->content)));
			$this->content = gzencode($this->content);

			header('Content-Encoding: gzip');
			header('Content-Length: ' . mb_strlen($this->content, '8bit'));
			header('Content-MD5: ' . $digest);
			header('Content-Type: text/html; charset=utf-8');
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
		public function output() {
			ob_end_clean();

			echo $this->content;
		}
	}
}
