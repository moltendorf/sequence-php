<?php

namespace sequence\module\resume {

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
		}

		/**
		 *
		 * @param string $request
		 * @param string $request_root
		 */
		public function request($request, $request_root) {
			$root     = $this->root;
			$template = $root->template;

			$template->file = 'resume:index.html';
		}
	}
}
