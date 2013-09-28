<?php

namespace blink\root {

	use blink as b,
	 blink\functions as f;

	class template {

		use b\broadcaster;

		/**
		 *
		 */
		public function header() {
			$this->broadcast('header');
		}

		/**
		 *
		 */
		public function body() {
			$this->broadcast('body');
		}

		/**
		 *
		 */
		public function error() {
			$contents = ob_get_contents();
			ob_clean();

			echo $contents;
		}

	}

}
