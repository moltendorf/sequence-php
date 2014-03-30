<?php

namespace blink\root {

	use blink as b;

	class handler {

		protected $module = null;
		protected $request = null;

		/**
		 *
		 * @var b\root
		 */
		protected $root;

		/**
		 *
		 * @param b\root $root
		 */
		public function __construct(b\root $root) {
			$this->root = $root;
		}

		public function parse($request = null) {
			$settings = $this->root->settings;

			return;

			if ($request === null) {
				$request = $_SERVER['DOCUMENT_URI'];
			}

			$setup = $this->root->setup;

			if (substr($request, 0, $setup->root_length) === $setup->root) {
				$trimmed = substr($request, $setup->root_length + 1);

				if ($trimmed !== '' && $trimmed !== false) {
					$parts = explode('/', $trimmed);
				} else {
					$parts = [];
				}

				$length = count($parts);

				if ($setup->handler['module'] && ($setup->handler['prefix'] || !$length)) {
					$this->module = $setup->handler['module'];
					$this->request = '/';

					return;
				}

				$tree = $setup->handler['handler'];

				for ($i = 0; $i < $length; $i++) {
					$segment = & $parts[$i];

					if (isset($tree[$segment])) {
						if ($tree[$segment]['module'] && ($tree[$segment]['prefix'] || $i === $length)) {
							$this->module = $tree[$segment]['module'];
							$this->request = '/' . implode('/', array_slice($parts, $i + 1));
						}

						if (isset($tree[$segment]['handler'])) {
							$tree = $tree[$segment]['handler'];

							continue;
						}
					}

					break;
				}
			} else {
				// 500.
				throw new Exception('HANDLER_NOT_LOADED_IN_CONFIGURED_ROOT');
			}
		}

		public function load() {
			$root = $this->root;
			$path = $root->path;

			if ($this->module !== null) {
				$file = $path->module($this->module);

				if ($file) {
					require $file;

					$class = 'blink\\module_' . $this->module;
					$module = $root->module = new $class($root);

					$module->run();
				} else {
					// 500.
					throw new Exception('HANDLER_COULD_NOT_LOAD_REQUESTED_MODULE');
				}
			} else {
				// 404.
			}
		}

	}

}
