<?php

namespace sequence\module\typekit {

	use sequence as s;
	use sequence\functions as f;

	class typekit extends s\module {

		use s\listener;

		/**
		 *
		 * @param s\root $root
		 * @param string $binding
		 */
		public function __construct(s\root $root, $binding = '') {
			$this->bind($root, $binding);

			$this->listen([$this, 'template'], 'template', 'application', -10);
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
			$settings = $root->settings;

			if (isset($settings['typekit_kit_id'])) {
				$template = $root->template;

				$template->script("//use.typekit.net/$settings[typekit_kit_id].js", false);
				$template->script(['body' => 'try{Typekit.load();}catch(e){}'], false);
			}
		}
	}
}
