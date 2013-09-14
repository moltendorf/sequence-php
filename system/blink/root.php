<?php

namespace blink {
	use blink\functions as f;

	/**
	 *
	 * @property-read database\common $database
	 * @property-read root\hook $hook
	 * @property-read root\handler $handler
	 * @property-read root\path $path
	 * @property-read root\settings $settings
	 */
	class root {

		/**
		 *
		 * @param string $name
		 * @return mixed
		 */
		public function __get($name) {
			$class = 'blink\\root\\'.$name;
			$this->$name = new $class($this);

			return $this->$name;
		}
	}
}
