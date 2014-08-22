<?php

namespace blink\root {

	use blink as b;

	class handler {

		protected $normalized = null;

		protected $module = null;

		protected $request = null;

		protected $tree = [];

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

			$database = $root->database;

			$statement = $database->prepare("
				select path_root, module_name, path_is_prefix, path_alias
				from {$database->table('paths')}
				join {$database->table('modules')}
					using (module_id)
				where path_is_enabled = 1
			");

			$statement->execute();

			foreach ($statement->fetchAll() as $row) {
				$path = $row[0];

				if (strlen($path) > 1) {
					$segments = explode('/', substr($path, 1));
				} else {
					$segments = [];
				}

				$branch = & $this->tree;

				foreach ($segments as $segment) {
					if (!isset($branch['branches'])) {
						$branch['branches'] = [];
					}

					if (!isset($branch['branches'][$segment])) {
						$branch['branches'][$segment] = [];
					}

					$branch = & $branch['branches'][$segment];
				}

				$branch['module'] = $row[1];
				$branch['path']   = $path;
				$branch['prefix'] = (boolean) $row[2];

				if (strlen($row[3])) {
					$branch['alias'] = $row[3];
				}

				unset($branch);
			}
		}

		public function parse($request = null) {
			if ($request === null) {
				$request = preg_replace('/[\\/][\\/]+/', '/', $_SERVER['REQUEST_URI']);
			}

			$settings = $this->root->settings;
			$template = $this->root->template;

			$length = strlen($settings['root']);

			if (substr($request, 0, $length) === $settings['root']) {
				$trimmed = $length > 1 ? substr($request, $length) : $request;

				$length = strlen($trimmed);

				if (!$length || $trimmed[0] != '/') {
					$trimmed = '/' . $trimmed;

					$length = strlen($trimmed);
				}

				if ($length > 1 && $trimmed[$length - 1] == '/') {
					$trimmed = substr($trimmed, 0, -1);

					$length = strlen($trimmed);
				}

				if ($length > 1) {
					$segments = explode('/', substr($trimmed, 1));
				} else {
					$segments = [];
				}

				$branch = $this->tree;

				$prefix   = $branch['prefix'];
				$selected = $branch;

				foreach ($segments as $segment) {
					if (!$prefix) {
						$prefix = true;

						unset($selected);
					}

					if (isset($branch['branches']) && isset($branch['branches'][$segment])) {
						$branch = $branch['branches'][$segment];

						if (isset($branch['module'])) {
							$prefix   = $branch['prefix'];
							$selected = $branch;
						}
					} else {
						break;
					}
				}

				if (isset($selected)) {
					$this->module     = $selected['module'];
					$this->request    = substr($trimmed, strlen($selected['path']));
					$this->normalized = $selected['path'] . $this->request;

					if (!strlen($this->request)) {
						$this->request = '/';
					}

					if ($this->normalized !== null) {
						if (strlen($settings['root']) > 1) {
							$normalized = $settings['root'] . $this->normalized;
						} else {
							$normalized = $this->normalized;
						}

						if ($_SERVER['REQUEST_URI'] != $normalized) {
							$template->redirect($normalized, 301);
						}
					}
				} else {
					$template->error(404);
				}
			} else {
				// 500.
				throw new \Exception('HANDLER_NOT_LOADED_IN_CONFIGURED_ROOT');
			}
		}

		public function load() {
			$settings = $this->root->settings;
			$template = $this->root->template;

			$template->error(500);
		}
	}
}
