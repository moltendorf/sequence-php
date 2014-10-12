<?php

namespace sequence\root {

	use sequence as b;

	class handler {

		use b\listener;

		protected $normalized = null;

		protected $module = null;

		protected $request = null;

		protected $tree = [];

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->bind($root, $binding);

			$database = $root->database;
			$prefix   = $database->prefix();

			$statement = $database->prepare("
				select path_root, module_name, path_is_prefix, path_alias
				from {$prefix}paths
				join {$prefix}modules
					using (module_id)
				where	module_is_enabled = 1
					and	path_is_enabled = 1
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
			$root     = $this->root;
			$settings = $root->settings;
			$template = $root->template;

			if ($request === null) {
				$request = preg_replace('/[\\/][\\/]+/', '/', $_SERVER['REQUEST_URI']);
			}

			$length = strlen($settings['root']);

			if (substr($request, 0, $length) === $settings['root']) {
				$this->normalized = $length > 1 ? substr($request, $length) : $request;

				$length = strlen($this->normalized);

				if (!$length || $this->normalized[0] != '/') {
					$this->normalized = '/' . $this->normalized;

					$length = strlen($this->normalized);
				}

				if ($length > 1 && $this->normalized[$length - 1] == '/') {
					$this->normalized = substr($this->normalized, 0, -1);

					$length = strlen($this->normalized);
				}

				if ($length > 1) {
					$segments = explode('/', substr($this->normalized, 1));
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

				if (strlen($settings['root']) > 1) {
					$request = $settings['root'] . $this->normalized;
				} else {
					$request = $this->normalized;
				}

				if (isset($selected)) {
					$this->module  = $selected['module'];
					$this->request = substr($this->normalized, strlen($selected['path']));

					if (!strlen($this->request)) {
						$this->request = '/';
					}

					if ($_SERVER['REQUEST_URI'] != $request) {
						$template->redirect($request, 301);
					}

					return true;
				} else {
					if ($_SERVER['REQUEST_URI'] != $request) {
						$template->redirect($request, 301);

						return true;
					}
				}
			} else {
				// 500.
				throw new \Exception('HANDLER_NOT_LOADED_IN_CONFIGURED_ROOT');
			}

			$template->error(404);

			return false;
		}

		public function load() {
			$root   = $this->root;
			$module = $root->module;

			$module[$this->module]->request($this->request);
		}
	}
}
