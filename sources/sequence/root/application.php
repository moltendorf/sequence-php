<?php

namespace sequence\root {

	use sequence as s;

	use errorexception, exception;

	/**
	 *
	 * @property-read array $debug
	 */
	class application {

		use s\broadcaster;

		/**
		 * List of messages that this class can send.
		 *
		 * @var array
		 */
		const messages = ['ready', 'close'];

		/**
		 * Errors that occurred during setup.
		 *
		 * @var array
		 */
		public $errors = [];

		/**
		 * Settings that take effect if the database is unresponsive.
		 *
		 * @var array
		 */
		public $settings = [];

		/**
		 * Contents ready for output.
		 *
		 * @var string
		 */
		protected $content;

		/**
		 *
		 * @var array
		 */
		private $debug = false;

		/**
		 * Basic constructor. Store reference of root class instance.
		 *
		 * @param s\root $root
		 * @param string $systemPath
		 * @param array  $homePath
		 */
		public function __construct(s\root $root, $systemPath, $homePath) {
			$this->root = $root;

			/*
			 * Set up output buffering.
			 *
			 * Output buffering is used to prevent accidental output.
			 * If there is any output, an error is thrown with the output dumped to the page in debug mode.
			 * If the output buffering level is different by the end of the script, an error is thrown.
			 */

			// Cancel the default output buffer (we recommend having it on despite cancelling it).
			if (ini_get('output_buffering') && ob_get_level() === 1) {
				if (ob_get_length()) {
					$buffer = ob_get_clean();
				} else {
					ob_end_clean();
				}
			}

			ob_start();

			if (isset($buffer)) {
				echo $buffer;

				unset($buffer);
			}

			/*
			 * Set up the error handler.
			 *
			 * We only deal with exceptions.
			 */

			/**
			 * @param $code
			 * @param $message
			 * @param $file
			 * @param $line
			 *
			 * @throws errorexception
			 */
			$error_handler = function ($code, $message, $file, $line) {
				throw new errorexception($message, 0, $code, $file, $line);
			};

			set_error_handler($error_handler);

			/*
			 * Set up debugging code.
			 *
			 * Include any debug files, define the sequence\debug constant, and define the sequence\ship constant.
			 */

			$debug = "$systemPath/debug";

			if (is_dir($debug)) {
				$this->debug = [];

				foreach (glob("$debug/*.php") as $file) {
					if (is_file($file)) {
						try {
							// Manually including each file as the namespace the classes are in would fool the autoloader.
							include $file;

							$class = 'sequence\\debug\\'.basename($file, '.php');

							if (class_exists($class, false)) {
								// Instantiate the debug class.
								$this->debug[] = new $class($root);
							}
						} catch (exception $exception) {
							$this->errors[] = $exception;
						}
					}
				}

				unset($class);
			}

			// Allow debug files to set debugging constants.
			if (!defined($const = 'sequence\\debug')) {
				define($const, false);
			}

			if (!defined($const = 'sequence\\debug\\language')) {
				define($const, false);
			}

			unset($const);

			define('sequence\\ship', !s\debug);

			unset($debug);

			/*
			 * Bind this class for broadcasting and listening.
			 *
			 * This was performed late as it relies on the sequence\debug and sequence\ship constants to be defined.
			 */

			$this->bind($root);

			$database = $root->database;
			$path     = $root->path;

			/*
			 * Include settings files.
			 */

			$settingsFile = "$homePath/settings.php";

			if (is_file($settingsFile)) {
				try {
					$settings = include $settingsFile;
				} catch (exception $exception) {
					$this->errors[] = $exception;

					$settings = [];
				}
			} else {
				$this->errors[] = new exception('SETTINGS_FILE_NOT_FOUND');

				$settings = [];
			}

			if (isset($settings['application']) && is_array($settings['application'])) {
				$this->settings += $settings['application'];
			}

			$this->settings += require "$systemPath/settings.php";

			if (!isset($settings['path']) || !is_array($settings['path'])) {
				$settings['path'] = [];
			}

			// Set up our paths.
			$path->settings($systemPath, $homePath, $settings['path']);

			if (isset($settings['database']) && is_array($settings['database'])) {
				try {
					// Open database connection.
					$database->connect($settings['database']);
				} catch (exception $exception) {
					$this->errors[] = $exception;
				}
			} else {
				$this->errors[] = new exception('DATABASE_CONNECTION_FAILED');
			}

			unset($settings);

			$this->broadcast('ready');
		}

		/**
		 * Get a private value.
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
		 * Bind all classes in root to application identity.
		 *
		 * @return string
		 */
		protected function getBinding() {
			return 'application';
		}

		/**
		 * Start main routine.
		 * Run setup method. Load language strings. Parse the request address. Run the module(s). Configure the status
		 * and content-type. Generate, compress and send the output. Perform database updates and maintenance.
		 *
		 * @param boolean $finish
		 */
		public function routine($finish = true) {
			$root     = $this->root;
			$language = $root->language;
			$handler  = $root->handler;
			$module   = $root->module;
			$template = $root->template;

			try {
				$level = ob_get_level();

				if ($this->errors) {
					$language->load();

					throw $this->errors[0];
				}

				$module->load();
				$language->load();

				// Parse the request.
				if ($handler->parse()) {
					$start = microtime(true)*1e6;

					$handler->request();

					// Calculate the time it took to run the module.
					$template->add(['runtime' => $runtime = microtime(true)*1e6 - $start]);
				}

				if (s\ship) {
					$this->generate();

					ob_end_clean();
					echo $this->content;

					if ($finish && function_exists('fastcgi_finish_request')) {
						fastcgi_finish_request();
					}

					$this->broadcast('close');
				} else {
					if (ob_get_level() != $level) {
						throw new exception('OUTPUT_BUFFER_LEVEL_DIFFERS');
					}

					if (ob_get_length()) {
						throw new exception('OUTPUT_BUFFER_NOT_EMPTY');
					}

					$this->generate();
					$this->broadcast('close');

					if (ob_get_length()) {
						throw new exception('OUTPUT_BUFFER_NOT_EMPTY');
					}

					if (isset($runtime)) {
						if ($runtime > 1e3) {
							$runtime = number_format($runtime/1e3).'ms';
						} else {
							$runtime = number_format($runtime).utf8_decode('µs');
						}

						header("X-Debug-Module-Runtime: $runtime");
					}

					ob_end_clean();

					if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
						$runtime = microtime(true)*1e6 - $_SERVER['REQUEST_TIME_FLOAT']*1e6;

						// Create an extra header warning when runtime is greater than 100ms.
						if ($runtime > 1e5) {
							header('X-Debug-Warning-Runtime: Runtime is over 100ms!');
						}

						if ($runtime > 1e3) {
							$runtime = number_format($runtime/1e3).'ms';
						} else {
							$runtime = number_format($runtime).utf8_decode('µs');
						}

						header("X-Debug-Total-Runtime: $runtime");
					}

					echo $this->content;
					// We do not call fastcgi_finish_request() to ensure every bit of detail makes its way out.
				}
			} catch (exception $exception) {
				$handler->error($exception);

				$this->generate();

				ob_end_clean();
				echo $this->content;
			}
		}

		/**
		 * Generate output for request.
		 */
		public function generate() {
			$root    = $this->root;
			$handler = $root->handler;

			$this->content = $handler->output();

			// Prevent issues if we're debugging.
			if (s\ship) {
				$digest        = base64_encode(pack('H*', md5($this->content)));
				$this->content = gzencode($this->content, 9);

				header('Content-Encoding: gzip');
				header('Content-Length: '.mb_strlen($this->content, '8bit'));
				header("Content-MD5: $digest");
			}

			header('Content-Type: '.$handler->getTypeRaw());
			header('Last-Modified: '.(new \DateTime('now', new \DateTimeZone('GMT')))->format('D, d M Y H:i:s T'));
		}
	}
}
