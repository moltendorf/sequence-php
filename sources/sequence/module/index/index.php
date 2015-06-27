<?php

namespace sequence\module\index {

	use sequence as s;

	class index extends s\module {

		use s\listener;

		/**
		 * Handle a request for a normal page.
		 *
		 * @param string $request
		 * @param string $request_root
		 *
		 * @return int|null
		 */
		public function request($request, $request_root) {
			header('Cache-Control: s-maxage=14400, max-age=3600');

			return 200;
		}

	}
}
