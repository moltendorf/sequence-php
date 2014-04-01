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
		 * @param string $file
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
			$contents = nl2br(htmlspecialchars(ob_get_contents(), ENT_COMPAT | ENT_DISALLOWED | ENT_HTML5));
			ob_clean();

			if ($exception) {
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
		}

	}

}
