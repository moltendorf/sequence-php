<?php

namespace sequence\root {

	use sequence as b;

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
		 * @param string  $system
		 * @param boolean $finish
		 */
		public function routine($system, $finish = true) {
			$root = $this->root;

			try {
				$this->setup($system);
				$this->module($finish);
			} catch (\Exception $exception) {
				$root->template->error($exception);

				$this->template();
				$this->output();
			}
		}

		/**
		 *
		 */
		public function setup($system) {
			$root = $this->root;

			/*
			 * Output buffering is used to prevent accidental output.
			 * If there is any output, an error is thrown with the output dumped to the page in debug mode.
			 * If the output buffering level is different by the end of the script, an error is thrown.
			 */

			// Cancel the default output buffer.
			if (ini_get('output_buffering') && ob_get_level() === 1) {
				if (ob_get_length()) {
					$buffer = ob_get_clean();
				} else {
					ob_end_clean();
				}
			}

			// Start our own output buffer.
			ob_start();

			if (isset($buffer)) {
				echo $buffer;
			}


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
				define('sequence\\debug', true);

				$this->debug = [];

				foreach (glob($debug . '/*.php') as $file) {
					// Manually including each file as the namespace the classes are in would cause the autoloader to look in the wrong directory.
					require $file;

					$class = 'sequence\\debug\\' . substr($file, strrpos($file, '/') + 1, -4);

					if (class_exists($class, false)) {
						// Instantiate the debug class.
						$this->debug[] = new $class($root);
					}
				}

				unset($class);
			} else {
				define('sequence\\debug', false);
			}

			define('sequence\\ship', !b\debug);

			unset($debug);

			$this->bind($root);

			// Load our settings.
			$settings = require $system . '/settings.php';

			if (isset($settings['application']) && is_array($settings['application'])) {
				$this->settings = $settings['application'];
			}

			// Set up our paths.
			$root->path->settings($system, $settings['path']);

			$root->database->connect($settings['database']);

			unset($settings);
		}

		/**
		 *
		 */
		public function module($finish = true) {
			$root = $this->root;

			$level = ob_get_level();

			// Parse the request.
			if ($root->handler->parse()) {
				$start = microtime(true) * 1e6;

				$root->module->load();

				$this->broadcast('module');

				$root->handler->load();

				// Calculate the time it took to run the module.
				$root->template->variable['runtime'] = $time = microtime(true) * 1e6 - $start;

				$this->broadcast('template');
			}

			if (b\ship) {
				$this->template();
				$this->output();

				if ($finish && function_exists('fastcgi_finish_request')) {
					fastcgi_finish_request();
				}

				$this->broadcast('close');
			} else {
				if (ob_get_level() != $level) {
					throw new \Exception('OUTPUT_BUFFER_LEVEL_DIFFERS');
				}

				if (ob_get_length()) {
					throw new \Exception('OUTPUT_BUFFER_NOT_EMPTY');
				}

				$this->template();

				if (isset($time)) {
					header('X-Debug-Execution-Time: ' . number_format($time) . utf8_decode('µs'));
				}

				$this->broadcast('close');

				if (ob_get_length()) {
					throw new \Exception('OUTPUT_BUFFER_NOT_EMPTY');
				}

				$this->output();
				// We do not call fastcgi_finish_request() to ensure every bit of detail makes its way out.
			}
		}

		/**
		 *
		 */
		public function template() {
			$this->content = $this->root->template->body();

			// Prevent issues if we're debugging.
			if (b\ship) {
				$digest        = base64_encode(pack('H*', md5($this->content)));
				$this->content = gzencode($this->content, 9);

				header('Content-Encoding: gzip');
				header('Content-Length: ' . mb_strlen($this->content, '8bit'));
				header('Content-MD5: ' . $digest);
			}

			header('Content-Type: text/html; charset=utf-8');
			header('Last-Modified: ' . (new \DateTime('now', new \DateTimeZone('UTC')))->format('D, d M Y H:i:s T'));
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
