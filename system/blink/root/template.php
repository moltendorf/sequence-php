<?php

namespace blink\root {

	use blink as b;
	use blink\functions as f;

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

			$this->defaultDirectory = $root->path->template . '/default';

			if (isset($root->settings['template'])) {
				$this->currentDirectory = $root->path->template . '/' . $root->settings['template'];
			} else {
				$this->isDefaultTemplate = true;

				$this->currentDirectory = $this->defaultDirectory;
			}

			$this->useCustomizations = (boolean) $root->settings['template_custom'];

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

			$l = $this->root->language;
			$v = $this->variable;

			http_response_code($v['status']);

			ob_start();

			require $this->path($this->file);

			return ob_get_clean();
		}

		/**
		 *
		 * @param string $file
		 *
		 * @return string|boolean
		 */
		public function path($file) {
			if ($this->useCustomizations) {
				$path = $this->currentDirectory . '/custom/' . $file . '.php';

				if (file_exists($path)) {
					return $path;
				}
			}

			$path = $this->currentDirectory . '/' . $file . '.php';

			if (file_exists($path)) {
				return $path;
			}

			if (!$this->isDefaultTemplate) {
				if ($this->useCustomizations) {
					$path = $this->defaultDirectory . '/custom/' . $file . '.php';

					if (file_exists($path)) {
						return $path;
					}
				}

				$path = $this->defaultDirectory . '/' . $file . '.php';

				if (file_exists($path)) {
					return $path;
				}
			}

			return false;
		}

		/**
		 *
		 */
		public function error(\Exception $exception = null) {
			$this->clear();

			$v = & $this->variable;

			if ($exception) {
				$v['exception'] = $exception;

				if ($exception instanceof b\StatusException) {
					$v['status'] = $exception->getStatus();
				}

				$v['type']    = get_class($exception);
				$v['message'] = $exception->getMessage();
				$v['file']    = $exception->getFile();
				$v['line']    = $exception->getLine();
				$v['trace']   = nl2br(str_replace([' ', "\t"], ['&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;'], htmlspecialchars($exception->getTraceAsString(), ENT_COMPAT | ENT_DISALLOWED | ENT_HTML5)));
			}

			if (ob_get_length()) {
				$v['contents'] = f\text_format(f\text_normalize(ob_get_contents()));

				//ob_clean();
			}

			if ($v['status'] == 200) {
				$v['status'] = 500;
			}

			if (b\debug) {
				$this->file = 'error_debug';
			} else {
				$this->file = 'error';
			}
		}
	}
}
