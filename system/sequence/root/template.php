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


			$file = $this->path($this->file);

			if (file_exists($file)) {
				ob_start();

				try {
					require $file;
				} catch (\Exception $exception) {
					ob_end_clean();

					throw $exception;
				}

				return ob_get_clean();
			} else {
				throw new \Exception('TEMPLATE_FILE_NOT_EXIST');
			}
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
				$v['trace']   = nl2br(str_replace([' ', "\t"], ['&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;'], htmlspecialchars($status->getTraceAsString(), ENT_COMPAT | ENT_DISALLOWED | ENT_HTML5)));
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
				$this->file = 'error';
			} else {
				$this->file = 'error_debug';
			}
		}
	}
}
