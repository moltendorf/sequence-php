<?php

namespace sequence\module\core {

	use sequence as b;

	class load {

		use b\listener;

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->bind($root, $binding);

			$this->listen([$this, 'template'], 'template', 'root/application');
		}

		public function template() {
			$root = $this->root;
			$s    = $root->settings;
			$v    = &$root->template->variable;

			// Site display.

			if ($s['site.display']) {
				$v['core.display'] = $s['site.display'];
			} else {
				$v['core.display'] = $_SERVER['HTTP_HOST'];
			}

			// Copyright display.

			if ($s['site.copyright']) {
				$v['core.copyright'] = true;

				if ($s['site.copyright.display']) {
					$v['core.copyright.display'] = $s['site.copyright.display'];
				} else {
					$v['core.copyright.display'] = $v['core.display'];
				}

				if ($s['site.copyright.date']) {
					$year = date('Y');

					if ($s['site.copyright.date'] < $year) {
						$v['core.copyright.date'] = $s['site.copyright.date'] . '-' . $year;
					} else {
						$v['core.copyright.date'] = $s['site.copyright.date'];
					}
				} else {
					$v['core.copyright.date'] = '';
				}
			} else {
				$v['core.copyright'] = false;
			}

			// Navigation data.

			$v['core.root'] = $s['root'];

			$handler = $root->handler;

			if ($handler->navigation) {
				$active = $root->handler->module();

				$v['core.navigation'] = [];

				foreach ($handler->navigation as $module) {
					$module['active'] = ($active == $module['module']);

					$v['core.navigation'][] = $module;
				}
			} else {
				$v['core.navigation'] = false;
			}
		}
	}
}
