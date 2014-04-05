<?php

namespace blink\root {

	use blink as b;

	class template {

		use b\broadcaster;

		/**
		 *
		 * @var b\root
		 */
		protected $root;

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
		 * @param b\root $root
		 */
		public function __construct(b\root $root) {
			$this->root = $root;

			$this->defaultDirectory = $root->path->template . '/default';

			if (isset($root->settings['template'])) {
				$this->currentDirectory = $root->path->template . '/' . $root->settings['template'];
			} else {
				$this->isDefaultTemplate = true;

				$this->currentDirectory = $this->defaultDirectory;
			}

			$this->useCustomizations = (boolean) $root->settings['template_custom'];
		}

		/**
		 *
		 */
		public function header() {
			$this->broadcast('header');
		}

		/**
		 *
		 */
		public function body() {
			$this->broadcast('body');
		}

		/**
		 *
		 */
		public function error(\Exception $exception = null) {
			$lang = $this->root->language;

			$contents = nl2br(htmlspecialchars(ob_get_contents(), ENT_COMPAT | ENT_DISALLOWED | ENT_HTML5));
			ob_clean();

			$status = 500;

			if ($exception) {
				if ($exception instanceof b\StatusException) {
					$status = $exception->getStatus();
				}

				$message = $exception->getMessage();

				$type = get_class($exception);

				$file = $exception->getFile();
				$line = $exception->getLine();

				$trace = nl2br(htmlspecialchars($exception->getTraceAsString(), ENT_COMPAT | ENT_DISALLOWED | ENT_HTML5));
			}

			if (b\debug) {
				require $this->path('error_debug');
			} else {
				require $this->path('error');
			}

			http_response_code($status);
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
	}
}
