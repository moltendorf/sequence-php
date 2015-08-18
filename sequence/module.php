<?php

namespace sequence {

	abstract class module {

		/**
		 * Handle a query.
		 *
		 * @param array $query
		 *
		 * @return array|null
		 */
		public function query($query) {
			return null;
		}

		/**
		 * Handle a request for a normal page.
		 *
		 * @param string $request
		 * @param string $request_root
		 *
		 * @return array
		 */
		public function request($request, $request_root) {
			return 200;
		}

		/**
		 * Basic class instance setup.
		 *
		 * @param root   $root
		 * @param string $binding
		 */
		abstract protected function bind(root $root, $binding = '');
	}
}
