<?php

namespace blink {

	trait listener {

		/**
		 *
		 * @param callable $method
		 * @param string   $message
		 * @param string   $binding
		 */
		final protected function listen(callable $method, $message, $binding = null) {
			$this->root->hook->listen($method, $message, $binding);
		}
	}
}
