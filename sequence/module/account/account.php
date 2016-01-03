<?php
/**
 * Created by PhpStorm.
 * User: moltendorf
 * Date: 16/1/1
 * Time: 13:48
 */

namespace sequence\module\account {

	use sequence as s;
	use sequence\functions as f;

	class account extends s\module {

		use s\listener;

		/**
		 *
		 * @param s\root $root
		 * @param string $binding
		 */
		public function __construct(s\root $root, $binding = '') {
			$this->bind($root, $binding);
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
	}
}
