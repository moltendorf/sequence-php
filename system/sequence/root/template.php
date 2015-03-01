<?php

namespace sequence\root {

	use sequence as b;
	use sequence\functions as f;

	class template {

		use b\broadcaster;

		/**
		 *
		 * @var array
		 */
		public static $messages = ['body'];

		/**
		 *
		 * @var array
		 */
		public $variable;

		/**
		 *
		 * @var string
		 */
		public $file;

		/**
		 *
		 * @var string
		 */
		private $defaultDirectory;

		/**
		 *
		 * @var string
		 */
		private $currentDirectory;

		/**
		 *
		 * @var string
		 */
		private $moduleDirectory;

		/**
		 *
		 * @var boolean
		 */
		private $isDefaultTemplate = false;

		/**
		 *
		 * @var boolean
		 */
		private $useCustomizations = false;

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->bind($root, $binding);

			$path     = $root->path;
			$settings = $root->settings;

			$this->defaultDirectory = $path->template . '/default';

			if (isset($settings['template.default'])) {
				$this->currentDirectory = $path->template . '/' . $settings['template.default'];
			} else {
				$this->isDefaultTemplate = true;

				$this->currentDirectory = $this->defaultDirectory;
			}

			$this->moduleDirectory = $path->module;

			$this->useCustomizations = (boolean) $settings['template.customizations'];

			$this->clear();
		}

		/**
		 *
		 */
		public function clear() {
			$this->variable = [
				'status' => 200
			];
		}

		/**
		 *
		 */
		public function body() {
			$this->broadcast('body');

			$f = null; // We pass a reference of $f to itself, so we need to define it here.
			$l = $this->root->language;
			$v = $this->variable;

			$f = function ($file) use (& $f, $l, $v) {
				$path = $this->path($file);

				if ($path !== false) {
					include $path;
				} else {
					throw new \Exception('TEMPLATE_FILE_NOT_EXIST');
				}
			};

			http_response_code($v['status']);

			ob_start();

			try {
				$f($this->file);
			} catch (\Exception $exception) {
				ob_end_clean();

				throw $exception;
			}

			return ob_get_clean();
		}

		/**
		 *
		 * @param string $input
		 *
		 * @return string|boolean
		 */
		public function path($input) {
			$segments = explode(':', $input);

			if (count($segments) > 1) {
				list ($module, $file) = $segments;

				$file .= '.php';

				$segment = 'module/' . $module . '/' . $file;
			} else {
				$segment = $input . '.php';
			}

			if ($this->useCustomizations) {
				$path = $this->currentDirectory . '/custom/' . $segment;

				if (file_exists($path)) {
					return $path;
				}
			}

			$path = $this->currentDirectory . '/' . $segment;

			if (file_exists($path)) {
				return $path;
			}

			if (!$this->isDefaultTemplate) {
				if ($this->useCustomizations) {
					$path = $this->defaultDirectory . '/custom/' . $segment;

					if (file_exists($path)) {
						return $path;
					}
				}

				$path = $this->defaultDirectory . '/' . $segment;

				if (file_exists($path)) {
					return $path;
				}
			}

			if (isset($module)) {
				$path = $this->moduleDirectory . '/' . $module . '/template/' . $file;

				if (file_exists($path)) {
					return $path;
				}
			}

			return false;
		}

        /**
         * @param string $location
         * @param int $status
         */
		public function redirect($location, $status = 302) {
			$this->clear();

			$v = & $this->variable;

			$v['status']   = $status;
			$v['location'] = $location;

			$this->file = 'redirect';

			if (b\ship) {
				header('Location: ' . $location);
			}
		}

		/**
		 *
		 */
		public function error($status = null) {
			$this->clear();

			$v = & $this->variable;

			if ($status instanceof \Exception) {
				$v['exception'] = $status;

				if ($status instanceof b\StatusException) {
					$v['status'] = $status->getStatus();
				} else {
					$v['status'] = 500;
				}

				$v['type']    = get_class($status);
				$v['message'] = $status->getMessage();
				$v['file']    = $status->getFile();
				$v['line']    = $status->getLine();
				$v['trace']   = f\text_format(f\text_normalize($status->getTraceAsString()));
			} else {
				if (is_int($status)) {
					$v['status'] = $status;
				} else {
					$v['status'] = 404;
				}
			}

			if (ob_get_length()) {
				$v['contents'] = f\text_format(f\text_normalize(ob_get_contents()));
			}

			if (b\ship) {
				$this->file = 'error.html';
			} else {
				$this->file = 'error_debug.html';
			}
		}
	}
}
