<?php

namespace blink {

	/**
	 *
	 * @property-read root\application     $application
	 * @property-read root\database        $database
	 * @property-read root\hook            $hook
	 * @property-read root\handler         $handler
	 * @property-read root\language        $language
	 * @property-read root\module          $module
	 * @property-read root\template        $template
	 * @property-read root\path            $path
	 * @property-read root\settings        $settings
	 */
	class root {

		/**
		 *
		 * @param string $name
		 *
		 * @return mixed
		 */
		public function __get($name) {
			$class       = 'blink\\root\\' . $name;
			$this->$name = new $class($this);

			return $this->$name;
		}
	}
}
