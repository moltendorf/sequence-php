<?php

namespace sequence\module\analytics {

	use sequence as s;
	use sequence\functions as f;

	class analytics extends s\module {

		use s\listener;

		/**
		 *
		 * @param s\root $root
		 * @param string $binding
		 */
		public function __construct(s\root $root, $binding = '') {
			$this->bind($root, $binding);

			$settings = $root->settings;

			if (isset($settings['analytics_tracking_id'])) {
				$this->listen([$this, 'template'], 'template', 'application', -10);
			}
		}

		/**
		 *
		 * @param string $request
		 * @param string $request_root
		 *
		 * @return array
		 */
		public function request($request, $request_root) {
		}

		/**
		 *
		 * @param array $query
		 *
		 * @return array
		 */
		public function query($query) {
		}

		/**
		 *
		 */
		public function template() {
			$root     = $this->root;
			$template = $root->template;

			$template->script('/static/script/module/analytics/analytics.js');
		}
	}
}
